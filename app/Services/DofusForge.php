<?php

namespace App\Services;

use App\Character;
use App\Lang;
use Redis;

class DofusForge
{
    static public function asset($url)
    {
        return url('/') . '/forge/image/' . $url;
    }

    static public function image($request)
    {
        //$redis = Redis::connection();

        $format = pathinfo($request, PATHINFO_EXTENSION);
        switch ($format)
        {
            case 'png':
            default:
                header('Content-Type: image/png');
                break;
        }

        $url = "http://staticns.ankama.com/";
        $url .= $request;
        $hash = md5($request);

        //$data = $redis->get("dofus:forge:$hash");
        $data = @file_get_contents("forge/$hash.$format");

        if ($data)
        {
            return $data;
        }
        else
        {
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_COOKIESESSION, true);
            $result = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($code == 404)
            {
                header("HTTP/1.1 404 Not Found");
                header('Content-Type: plain/text');
                return $result;
            }
            else
            {
                //$redis->set("dofus:forge:$hash", $result);
                file_put_contents("forge/$hash.$format", $result);
                return $result;
            }

            curl_close($curl);
        }
    }

    static public function player($id, $server, $mode, $orientation, $sizeX, $sizeY)
    {
        $character = Character::on($server . '_world')->where('Id', $id)->first();

        if ($character)
        {
            $look = bin2hex($character->EntityLookString);
            return self::asset("dofus/renderer/look/$look/$mode/$orientation/{$sizeX}_{$sizeY}.png");
        }
    }

    static public function text($id, $server = null)
    {
        /*$redis = Redis::connection();

        $text = $redis->get("dofus:text:$id");

        if ($text)
        {
            return $text;
        }
        else
        {*/
            $lang = null;

            if ($server)
            {
                $lang = Lang::on($server . '_world')->select('French')->where('Id', $id)->first();
            }
            else
            {
                $lang = Lang::select('French')->where('Id', $id)->first();
            }

            $text = "Text not found.";

            if ($lang && $lang->French)
            {
                $text = $lang->French;
            }

            return $text;
        //}
    }
}
