<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\User;
use App\ForumAccount;

use Auth;

class AuthController extends Controller
{
    public function login()
    {
        return view('auth/login');
    }

    public function auth(Request $request)
    {
        $clientIp = $request->ip();
        $user = User::where('login', $request->input('email'))->first();
        $email = User::where('email', $request->input('email'))->first();

        if (($user != null || $email != null))
        {
			if($user == null)			
				$user = $email;
				
			if(($user->password ===  md5($request->input('password')))){
            if (!$user->active)
            {
                return view('account/re-send-email', ['user' => $user]);
            }
            if ($user->isBanned())
            {
                $request->session()->flash('notify', ['type' => 'warning', 'message' => "Votre compte est banni."]);
                return redirect()->back()->withErrors(['auth' => 'Votre compte est banni.'])->withInput();
            }
            $user->last_ip_address = $clientIp;
            $user->save();

            if($request->remember)
            {
                Auth::login($user, true);
            }
            else
            {
                Auth::login($user);
            }

            $request->session()->put('password', $user->password);


            return redirect()->route('profile');
        
			}else
            {
                      $request->session()->flash('notify', ['type' => 'error', 'message' => "Nom de compte ou mot de passe incorrecta."]);
            return redirect()->back()->withErrors(['auth' => 'Nom de compte ou mot de passe incorrect.'])->withInput();
       
            }
		}
        else
        {
            $request->session()->flash('notify', ['type' => 'error', 'message' => "Nom de compte ou mot de passe incorrect."]);
            return redirect()->back()->withErrors(['auth' => 'Nom de compte ou mot de passe incorrect.'])->withInput();
        }
    }

    public function logout()
    {
        if (Auth::check())
        {
           /* $forumAccount = ForumAccount::find(Auth::user()->forum_id);

            if ($forumAccount)
            {
                $forumAccount->member_login_key = '';
                $forumAccount->save();

                setcookie('ips4_member_id',       '', time()-3600, '/', config('dofus.forum.domain'));
                setcookie('ips4_pass_hash',       '', time()-3600, '/', config('dofus.forum.domain'));
                setcookie('ips4_IPSSessionFront', '', time()-3600, '/', config('dofus.forum.domain'));
            }*/

            Auth::logout();
        }
        return redirect('/');
    }
}
