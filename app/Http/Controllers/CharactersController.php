<?php

namespace App\Http\Controllers;

use App\Account;
use App\Character;
use App\Resell;
use App\Exceptions\GenericException;
use App\RecoverCharacter;
use App\WorldCharacter;
use App\ItemTemplate;
use App\AccountWorld;
use App\CharacterItems;
use Illuminate\Http\Request;

use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;

class CharactersController extends Controller
{
    private function isCharacterOwnedByMe($server, $accountId, $characterId)
    {
        $account = Account::on($server . '_auth')->where('Id', $accountId)->where('Email', Auth::user()->email)->first();

        if ($account)
        {
            $account->server = $server;
            $characters = $account->characters($characterId);

            if ($characters)
            {
                foreach ($characters as $character)
                {
                    if ($character && $characterId == $character->Id)
                    {
                        return true;
                    }
                }

                return false;
            }
        }
    }

    private function isCharacterDeletedOwnedByMe($server, $accountId, $characterId)
    {
        $account = Account::on($server . '_auth')->where('Id', $accountId)->where('Email', Auth::user()->email)->first();
        if ($account)
        {
            $account->server = $server;
            $characters = $account->DeletedCharacters(1);
            if ($characters)
            {
                foreach ($characters as $character)
                {
                    if ($characterId == $character->Id)
                        return true;
                }
                return false;
            }
        }
    }

    private function isServerExist($server)
    {
        if (!in_array($server, config('dofus.servers')))
        {
            return false;
        }

        return true;
    }
    public function sell_action(Request $request)
    {
        $server = 'hyrule';
        $validator = Validator::make($request->all(),['prix' => 'required|integer']);

        if ($validator->fails())
        {
            return redirect()->back()->withErrors($validator)->withInput();
        }
        $account = Account::on($server . '_auth')->where('Id', $request->input('acc'))->first();
        if($account == null)
        {
            throw new GenericException('invalid_server', $server);       
        }
        $character = Character::on($server .'_world')->where('Id', $request->input('Id'))->first();
        if($character == null)
        {
            throw new GenericException('invalid_server', $server);       
        }
        $resell = Resell::where('id_character',$character->Id)->first();
        if($resell != null)
        {
            $request->session()->flash('notify', ['type' => 'error', 'message' => "Ce personnage est déja mis en vente ! "]);
            return redirect()->back();
        }
        $accountWorld = Account::on($server.'_world')->where('ConnectedCharacter',$character->Id)->first();
        if($accountWorld != null)
        {
             $request->session()->flash('notify', ['type' => 'error', 'message' => "Vous devez vous deconnecter pour vendre ce personnage "]);
            return redirect()->back();
        }
        $price = $request->input('prix');
        $taxe = (($price * 15)/100);
        if($account->Tokens < (($price * 15)/100))
        {
            $request->session()->flash('notify', ['type' => 'warning', 'message' => "Vous n'avez pas de tokens suffisant pour mettre ce personnage en vente"]);
            return redirect()->back();
        }
        $account->Tokens = $account->Tokens - $taxe;
        $account->save();
        $resell = new Resell();
        $resell->id_account = $account->Id;
        $resell->id_character = $character->Id;
        $resell->price = $price;
        $resell->save();
        $world = WorldCharacter::on($server . '_auth')->where('CharacterId',$character->Id)->first();
        $world->AccountId = -1;
        $world->save();
        $request->session()->flash('notify', ['type' => 'success', 'message' => "Le personnage a été mis en vente ! "]);
        return redirect()->back();


        
    }
    public function buy_action(Request $request )
    {
        $server = 'hyrule';
        $accountId = $request->input('account');
        $accounts = Auth::User()->accounts();
        $account = null;
        foreach ($accounts as $k) {
            if($k->Id == $accountId)
            {
                $account = $k;
                break;
            }
        }
        if($account == null)
        {
                $request->session()->flash('notify', ['type' => 'error', 'message' => "Compte existe pas!"]);
                return redirect()->back();
        }

        $resell = Resell::where('id_character',$request->input('Id'))->first();
        if($resell == null)
        {
            $request->session()->flash('notify', ['type' => 'error', 'message' => "Personnage innexistant ! "]);
            return redirect()->back();   
        }
        
        if($account->Tokens < $resell->price)
        {
            $request->session()->flash('notify', ['type' => 'error', 'message' => "Vos ogrines sont insuffisant pour acheter ce personnage "]);
            return redirect()->back();  
        }
        $account->Tokens  = $account->Tokens - $resell->price;
        $account->save();
        $world = WorldCharacter::on($server . '_auth')->where('CharacterId', $resell->id_character)->first();
        $world->AccountId = $account->Id;
        $world->save();
        $resell->delete();
         $request->session()->flash('notify', ['type' => 'success', 'message' => "Vous avez acheter ce personnage !"]);
         return redirect()->to('/');


    }
    public function buyCharacter(Request $request,$CharacterId)
    {
        $resell = Resell::where('id_character',$CharacterId)->first();
        if($resell == null)
        {
            $request->session()->flash('notify', ['type' => 'error', 'message' => "Personnage innexistant ! "]);
            return redirect()->back();   
        }
        $server ='hyrule';
        $character = Character::on($server .'_world')->where('Id', $CharacterId)->first();
        $accounts = Auth::User()->accounts();
        return view('characters.buy', compact('accounts', 'character','server'));

    }
    public function sell(Request $request,$server, $accountId, $characterId)
    {

        if (!$this->isServerExist($server))
        {
            throw new GenericException('invalid_server', $server);
        }
        $account = Account::on($server . '_auth')->where('Id', $accountId)->first();
        if($account == null)
        {
            throw new GenericException('invalid_server', $server);       
        }
   
        $character = Character::on($server .'_world')->where('Id', $characterId)->first();
        if($character == null)
        {
            throw new GenericException('invalid_server', $server);       
        }
        return view('characters.sell', compact('account', 'character'));


    }
    public function view(Request $request,$server, $accountId, $characterId)
    {

        if (!$this->isServerExist($server))
        {
            throw new GenericException('invalid_server', $server);
        }

        $account = Account::on($server . '_auth')->where('Id', $accountId)->first();
        if($account == null)
        {
            throw new GenericException('invalid_server', $server);       
        }
        $ownAccounts = Auth::user()->accounts();  
        $character = Character::on($server .'_world')->where('Id', $characterId)->first();
        if($character == null)
        {
            throw new GenericException('invalid_server', $server);       
        }
        $characterSell = Resell::where('id_character',$character->Id)->first();
        $items = CharacterItems::on($server.'_world')->where('OwnerId',$character->Id)->get();
        $images = [];
        foreach ($items as $k) 
        {
            $item = ItemTemplate::on($server . '_world')->where('Id', $k->ItemId)->first();
            $item_image = $item->image();
            array_push($images, $item_image);
        }

        return view('characters.view', compact('account', 'character','ownAccounts','server','characterSell','images'));

        
    }

    public function recover(Request $request, $server, $accountId, $characterId)
    {
        if (!$this->isServerExist($server))
        {
            throw new GenericException('invalid_server', $server);
        }

        if(!$this->isCharacterDeletedOwnedByMe($server, $accountId, $characterId))
        {
            throw new GenericException('owner_error');
        }

        $account = Account::on($server . '_auth')->where('Id', $accountId)->first();
        $account->server = $server;

        if ($request->all())
        {
            $deletedCharactersCount = count($account->DeletedCharacters(1));
            $character = Character::on($server . '_world')->where('Id', $characterId)->first();
            $price = $character->recoverPrice();
            $newname = $request->input('nickname');
            $oldname = $character->Name;

            $validator = Validator::make(['price_amount' => $price, 'nickname' => $newname], ['price_amount' => 'required|integer|min:1|max:' . Auth::user()->points, 'nickname' => array('required', 'regex:/^[A-Z][a-z]{2,9}(?:-[A-Za-z][a-z]{2,9}|[a-z]{1,10})$/')]);

            if ($validator->fails())
            {
                if($validator->errors()->has('price_amount'))
                {
                    $validator->errors()->add('price', "Vous n'avez pas assez d'ogrines pour faire cet achat. Pour en acheter, rendez-vous sur notre Boutique.");
                }
                return redirect()->back()->withErrors($validator)->withInput();
            }

            if(!Character::on($server . '_world')->where([['Name', '=', $newname], ['Id', '!=', $characterId],])->first())
            {
                if((WorldCharacter::on($server . '_auth')->where('AccountId', $accountId)->count() - $deletedCharactersCount) < config('dofus.characters_limit'))
                {
                    Auth::user()->points -= $price;
                    Auth::user()->save();
                    $character->DeletedDate = null;
                    $character->Name = $newname;
                    $character->save();

                    $recoverCharacter = new RecoverCharacter;
                    $recoverCharacter->user_id = Auth::user()->id;
                    $recoverCharacter->points = $price;
                    $recoverCharacter->characterId = $characterId;
                    $recoverCharacter->oldName = $oldname;
                    $recoverCharacter->newName = $newname;
                    $recoverCharacter->save();

                    Cache::forget('characters_' . $server . '_' . $accountId);
                    Cache::forget('characters_deleted_' . $server . '_' . $accountId);

                    $request->session()->flash('notify', ['type' => 'success', 'message' => "Le personnage a correctement été récupéré. Bon jeu!"]);
                    return redirect()->route('gameaccount.view', [$server, $accountId]);
                }
                else
                {
                    $request->session()->flash('notify', ['type' => 'error', 'message' => "Vous avez trop de personnages sur ce compte. Le maximum est de ".config('dofus.characters_limit')."."]);
                    return redirect()->back();
                }
            }
            else
            {
                $request->session()->flash('notify', ['type' => 'error', 'message' => "Le pseudo de ce personnage est déjà utilisé. Veuillez en choisir un autre ou le libérer en jeu"]);
                return redirect()->back()->withErrors(['nickname' => 'Le pseudo de ce personnage est déjà utilisé. Veuillez en choisir un autre ou le libérer en jeu'])->withInput();
            }
        }

        $character = Character::on($server . '_world')->where('Id', $characterId)->first();
        return view('gameaccount.recover', compact('account', 'character'));
    }
}
