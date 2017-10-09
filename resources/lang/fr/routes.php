<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Routes Language Lines
    |--------------------------------------------------------------------------
    */

    /** News **/

    'posts.index' => 'nouveautes',

    'posts.news' => 'actualites/news',

    'posts.type' => 'actualites/{type}',

    'posts.show' => 'article/{id}-{slug?}',

    'posts.show.old' => 'article/{id}/{slug?}',

    'posts.comment.store' => 'article/{id}-{slug?}/commentaire',

    'posts.comment.destroy' => 'article/{id}-{slug?}/{commentid}/destroy',

    /** Account **/

    'account.register' => 'inscription',

    'account.profile' => 'compte/profil',

    'account.login' => 'compte/connexion',

    'account.logout' => 'compte/deconnexion',

    'account.activation' => 'compte/activation/{ticket?}',

    'account.re_send_email' => 'compte/re-activation',

    'account.password_lost' => 'compte/mot-de-passe-oublie',

    'account.reset' => 'compte/reset/{ticket?}',

    'account.change_email' => 'compte/changement-email',

    'account.change_password' => 'compte/changement-mot-de-passe',

    'account.change_profile' => 'compte/editer-profil',

    'account.certify' => 'compte/certification',

    'account.valid_email' => 'compte/verification-email/{type}/{token}',

    /** Game Account **/

    'gameaccount.create' => 'compte/jeu/creation',

    'gameaccount.view' => 'compte/jeu/{server}/{accountId}',

    'gameaccount.edit' => 'compte/jeu/{server}/{accountId}/modifier',

    'gameaccount.transfert' => 'compte/jeu/{server}/{accountId}/transfert',

    'gameaccount.jetons' => 'compte/jeu/{server}/{accountId}/jetons',

    'gameaccount.gifts' => 'compte/jeu/{server}/{accountId}/cadeaux',

    /** Characters **/

    'characters.view' => 'personnages/{server}/{accountId}/{characterId}',

    'characters.recover' => 'personnages/{server}/{accountId}/{characterId}/recuperer',

    /** Shop **/

    'shop.payment.choose-country' => 'boutique/paiement/choix-pays',

    'shop.payment.choose-method' => 'boutique/paiement/{country?}/choix-mode-paiement',

    'shop.payment.choose-palier' => 'boutique/paiement/{country?}/{method?}/choix-offre',

    'shop.payment.get-code' => 'boutique/paiement/obtention-code',

    'shop.payment.process' => 'boutique/paiement/process',

    /** Vote **/

    'vote.index' => 'vote',

    'vote.confirm' => 'vote/confirmation',

    'vote.process' => 'vote/process',

    'vote.palier' => 'vote/palier/{id?}',

    'vote.object' => 'vote/objet/{item?}',

    'vote.go' => 'vote/go',

    'vote.callback' => 'vote/callback/{token?}',

    /** Lottery **/

    'lottery.index' => 'loterie',

    'lottery.servers' => 'loterie/serveurs/{id}',

    'lottery.draw' => 'loterie/tirage/{server}/{id}',

    'lottery.process' => 'loterie/tirage/go/{server}/{id}',

    /** Ladder **/

    'ladder.general' => 'classement/{server}/general',

    'ladder.pvp' => 'classement/{server}/pvp',

    'ladder.guild' => 'classement/{server}/guilde',

    /** Others **/

    'download' => 'telecharger',

    'servers' => 'serveurs',

    /** Events **/

    'event.st-patrick' => 'event/st-patrick',

];
