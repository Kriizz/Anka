@extends('layouts.contents.default')
@include('layouts.menus.base')

@section('breadcrumbs')
{? $page_name = 'Mon Compte' ?}
{!! Breadcrumbs::render('account') !!}
@stop

@section('content')
<div class="ak-container ak-main-center">
    <div class="ak-title-container">
        <h1><span class="ak-icon-big ak-bank"></span></a> Mon compte</h1>
    </div>

    <div class="ak-container ak-panel-stack">
        <div class="ak-container ak-panel ak-glue">
            <div class="ak-panel-content">
                <div class="panel-main profile">
                    @if(Auth::user()->certified == 0)
                    <!--<div class="pull-right">
                        <h3>Attention, votre compte est vulnérable.</h3>
                        <a class="btn btn-danger btn-lg" href="{{ URL::route('account.certify') }}">Certifier mon compte maintenant</a>
                    </div>-->
                    <div class="non-certified">
                        <a href="{{ URL::route('account.certify') }}">Compte non certifié</a>
                    </div>
                    @else
                    <div class="certified">
                        <span>Compte certifié</span>
                        <!--<button class="btn btn-info btn-lg" href="#">Voir les promotions</button>-->
                    </div>
                    @endif

                    <div class="row">
                        <!--<div class="col-xs-2">
                            <img src="{{ URL::asset(Auth::user()->avatar) }}" />
                        </div>-->
                        <div class="col-xs-5">
                            <b>Pseudo</b>: {{ Auth::user()->pseudo }}<br>
                            <b>Identité</b>: {{ Auth::user()->lastname }} {{ Auth::user()->firstname }}<br>
                            @if(Auth::user()->birthday)
                                <b>Date de naissance</b>: {{ Auth::user()->birthday->format('j F Y') }}<br>
                            @endif
                            <b>Email</b>: {{ Auth::user()->email }}
                        </div>
                        <div class="col-xs-4">
                        <?php 
                                $account = new App\Account();
                                $v = $account->on('intel_auth')->where('Email', Auth::user()->email)->get();
                                $c = 0;
                                foreach ($v as $k) {
                                    $c += $k->Tokens;
                                }

                                ?>
                            <b>Ogrines</b>: <?php echo Utils::format_price($c) ?> <span class="ak-icon-small ak-ogrines-icon"></span><br>
                            <b>Cadeaux</b>: {{ Utils::format_price(Auth::user()->votes / 10) }} <span class="ak-icon-small ak-gifts-icon"></span><br>
                            <b>Jetons</b>: {{ Utils::format_price(Auth::user()->jetons) }} <span class="ak-icon-small ak-votes-icon"></span><br>
                            <b>Votes</b>: {{ Utils::format_price(Auth::user()->votes) }}
                        </div>
                    </div>

                    <br>

                    @if(Auth::user()->certified == 0)
                    <a href="{{ URL::route('account.change_profile') }}" class="btn btn-default btn-sm">Éditer le profil</a>
                    @endif
                    <a href="{{ URL::route('account.change_email') }}" class="btn btn-default btn-sm">Changer d'email</a>
                    <a href="{{ URL::route('account.change_password') }}" class="btn btn-default btn-sm">Changer de mot de passe</a>
                    <div class="clearfix"></div>
                </div>
            </div>
            <div class="ak-panel-title">
                <span class="ak-panel-title-icon"></span> Mes comptes de jeu
            </div>
        </div>
    </div>

    <table class="ak-container ak-table">
        <tr>
            <th class="ak-center"></th>
            <th>Identifiant</th>
            <th>Pseudo</th>
            <th>Serveur</th>
            <th class="ak-center" style="width: 100px;">Personnages</th>
            <th class="ak-center" style="width: 200px;">Actions</th>
        </tr>
        @foreach ($accounts as $account)
        <tr>
            <td class="ak-rank"></td>
            <td><a href="{{ URL::route('gameaccount.view', [$account->server, $account->Id]) }}">{{ $account->Login }}</a>@if($account->isBanned())
                    <span class="ak-icon-small ak-banned"></span>
                @endif</td>
            <td><a href="{{ URL::route('gameaccount.view', [$account->server, $account->Id]) }}">{{ $account->Nickname }}</a></td>
            <td>{{ ucfirst($account->server) }}</td>
            <td class="ak-center">{{ count($account->characters()) }}</td>
            <td class="ak-center">
   
            </td>
        </tr>
        @endforeach
    </table>

    <div class="ak-container ak-panel-stack">
        <div class="ak-container ak-panel ak-glue">
            <div class="ak-panel-content">
                <div class="">
                    <a href="{{ URL::route('gameaccount.create') }}"><button class="btn btn-info btn-lg">Créer un nouveau compte</button></a>
                </div>
            </div>

            <div class="ak-panel-title">
                <span class="ak-panel-title-icon"></span> Mes 10 derniers achats
            </div>
        </div>
    </div>

    <table class="ak-container ak-table">
        <tr>
            <th class="ak-center">#</th>
            <th>Date d'achat</th>
            <th>Ogrines</th>
            <th>Code</th>
            <th>Statut</th>
        </tr>
        @foreach (Auth::user()->transactions(10) as $transaction)
        <tr>
            <td class="ak-center">{{ $transaction->id }}</td>
            <td>{{ $transaction->created_at->format('d/m/Y H:i:s') }}</td>
            <td><span class="ak-icon-small ak-ogrines-icon"></span> &nbsp; {{ Utils::format_price($transaction->points) }}</td>
            <td>{{ $transaction->code }}</td>
            <td>{{ Utils::transaction_status($transaction->state) }}</td>
        </tr>
        @endforeach
    </table>

    <div class="ak-container ak-panel-stack">
        <div class="ak-container ak-panel ak-glue">
            <div class="ak-panel-title">
                <br>
                <span class="ak-panel-title-icon"></span> Mes 10 derniers votes
            </div>
        </div>
    </div>

    <table class="ak-container ak-table">
        <tr>
            <th></th>
            <th>Date du vote</th>
            <th>Jetons</th>
        </tr>
        @foreach (Auth::user()->votes(10) as $vote)
        <tr>
            <td></td>
            <td>{{ $vote->created_at->format('d/m/Y H:i:s') }}</td>
            <td>+1 <span class="ak-icon-small ak-votes-icon"></span></td>
        </tr>
        @endforeach
    </table>
</div>
@stop
