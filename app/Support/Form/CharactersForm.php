<?php

namespace App\Support\Form;

use App\ModelCustom;
use App\Account;
use Auth;

class CharactersForm implements IForm
{
    public static function render($name, $field, $data, $params)
    {
        $server    = isset($params['server'])  ? $params['server']  : 'intel';
        $accountId = isset($params['account']) ? $params['account'] : 0;

        $account = ModelCustom::hasOneOnOneServer('auth', $server, Account::class, 'Id', $accountId);

        if ($account)
        {
            $characters = $account->characters(false, true);

            if (count($characters) > 0)
            {
                return view('support.form.characters', [
                    'name'       => $name,
                    'child'      => isset($field->child) ? $field->child : false,
                    'characters' => $characters,
                    'data'       => $data,
                    'field'      => $field,
                ]);
            }

            return "Aucun personnages<br>";
        }

        return "Compte introuvable<br>";
    }
}
