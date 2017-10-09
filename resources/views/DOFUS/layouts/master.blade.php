<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8" />
    <title>{{ config('dofus.title') }}@if (isset($page_name)) - {!! $page_name !!}@endif</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" />
    <meta name="description" content="{!! config('dofus.description') !!}" />
    @if (isset($og))
    {!! $og->renderTags() !!}
    @else
    <meta property="og:type" content="website" />
    <meta property="og:image" content="{{ URL::asset('imgs/cover.png') }}" />
    <meta property="og:description" content="{!! config('dofus.description') !!}" />
    <meta property="og:url" content="{{ Request::url() }}" />
    <meta property="og:site_name" content="{{ config('dofus.title') }} - @if (isset($page_name)){!! $page_name !!}@else{{ config('dofus.subtitle') }}@endif" />
    @endif
    <link rel="alternate" type="application/rss+xml" title="News RSS" href="{{ URL::to('news.rss') }}" />
    <link rel="shortcut icon" type="image/png" href="{{ URL::asset('imgs/Intel_simple.png') }}"/>
    <link rel="canonical" href="{{ Request::url() }}" />
    {!! Html::style('css/common.css') !!}
    {!! Html::style('css/toastr.min.css') !!}
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
    @yield('header')
    {!! Html::script('js/admin/toastr.min.js') !!}
    {!! Html::script('js/common.js') !!}
    @if (config('dofus.theme.animated'))
    {!! Html::style('imgs/carousel/'.config('dofus.theme.background').'/style.css') !!}
    @else
    <style type="text/css">
        body {
            background: url('{{ URL::asset('imgs/carousel/common/'.config('dofus.theme.background').'.jpg') }}')  center top no-repeat;
            background-color: {{ config('dofus.theme.color') }};
        }
    </style>
    @endif
    <script>
      (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
      (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
      m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
      })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');
      ga('create', 'UA-82860248-1', 'auto');
      ga('send', 'pageview');
    </script>
</head>
<body class="@yield('background')">
    <header>
        <div class="ak-idbar">
            <div class="ak-idbar-content">
                <div class="ak-idbar-left">
                    <div class="ak-brand" data-set="ak-brand">
                        <a class="navbar-brand" href="{{ URL::route('home') }}"></a>
                    </div>
                    <a class="ak-support" href="{{ URL::to('support') }}">Support</a>
                    @if (!Auth::guest() && Auth::user()->isAdmin())
                    <a class="ak-admin" href="{{ URL::route('admin.dashboard') }}" target="_blank">Admin</a>
                    @endif
                </div>
                <div class="ak-idbar-right">
                    @if (Auth::guest())
                    <div class="ak-nav-not-logged">
                        <div class="ak-connect-links">
                            <a href="{{ URL::route('login') }}" class="login ak-modal-trigger">
                                <span>Connexion</span>
                                <img class="img-responsive" src="{{ URL::asset('imgs/avatar/default.jpg') }}" alt="Avatar">
                            </a>
                            <a href="{{ URL::route('register') }}" class="register">Inscription</a>
                        </div>
                    </div>
                    @else
                    <div class="ak-idbar-right">
                        <!--<a class="ak-nav-notifications ak-button-modal">
                            <span class="label label-danger">0</span>
                        </a>-->
                        <div class="ak-button-modal ak-nav-logged">
                            <a class="ak-logged-account" href="{{ URL::route('profile') }}">
                                <span class="ak-nickname">{{ Auth::user()->pseudo }}</span>
                                <span class="avatar">
                                    <img src="{{ URL::asset(Auth::user()->avatar) }}" alt="Avatar">
                                </span>
                            </a>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <nav class="navbar navbar-default" data-role="ak_navbar">
            <div class="navbar-container">

                <a class="ak-main-logo" href="{{ URL::route('home') }}"></a>

                <div class="navbar-header">
                    <a class="burger-btn" href="javascript:void(0)"><span></span></a>
                </div>

                <div class="navbar-collapse navbar-ex1-collapse collapse">
                    <ul class="nav navbar-nav">
                        <span class="ak-navbar-left-part">
                            <li class="lvl0 dropdown sep">
                                <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">INTEL <b class="caret"></b></a>
                                <ul class="dropdown-menu dropdown-menu-right" role="menu">
                                    <li class="lvl1">
                                        <ul>
                                            <li class="lvl2"><a href="{{ URL::route('posts') }}">Actualités</a></li>
                                            <li class="lvl2"><a href="{{ URL::route('servers') }}">Serveurs</a></li>
                                            <li class="lvl2"><a href="{{ URL::route('ladder.general', [config('dofus.default_server_ladder')]) }}">Classements</a></li>
                                            <li class="lvl2"><a href="{{ URL::route('lottery.index') }}">Loterie</a></li>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <li class="lvl0 sep"><a href="{{ URL::route('register') }}">Rejoindre</a></li>
                            <li class="lvl0 sep"><a href="{{ URL::route('shop.payment.country') }}">Boutique</a></li>
                        </span>
                        <li class="lvl0 ak-menu-brand">
                            <a class="navbar-brand" href="{{ URL::route('home') }}"></a>
                        </li>
                        <span class="ak-navbar-right-part">
                            <li class="lvl0 sep"><a href="{{ URL::route('vote.index') }}">Vote</a></li>
                            <li class="lvl0 sep"><a href="{{ config('dofus.social.forum') }}" target="_blank">Forum</a></li>
                            <li class="lvl0 sep"><a href="{{ URL::to('support') }}">Support</a></li>
                        </span>
                    </ul>

                </div>
            </div>
        </nav>

        <!-- Keep in order largest -> lowest device resolution -->
        <div class="largedesktop device-profile visible-lg" data-deviceprofile="largedesktop"></div>
        <div class="desktop device-profile visible-md" data-deviceprofile="desktop"></div>
        <div class="tablet device-profile visible-sm" data-deviceprofile="tablet"></div>
        <div class="mobile device-profile visible-xs" data-deviceprofile="mobile"></div>
    </header>

    <!--<div class="ak-beta"></div>-->

    @yield('page')

    <footer>
        <div class="ak-footer-content">
            <div class="row ak-block1">
                <div class="col-md-9 ak-block-links">
                    <div class="col-md-6 clearfix">
                        <div class="col-xs-6">
                            <div class="ak-list">
                                <div>
                                    <span class="ak-link-title">{{ config('dofus.title') }}</span>
                                </div>
                                <a href="{{ URL::route('posts') }}">Actualités</a>
                                <a href="{{ URL::route('download') }}">Télécharger</a>
                                <a href="{{ URL::route('register') }}">Créer un compte</a>
                                <a href="{{ URL::route('password-lost') }}">Mot de passe oublié ?</a>
                            </div>
                        </div>
                        <div class="col-xs-6">
                            <div class="ak-list">
                                <div>
                                    <span class="ak-link-title">Serveur</span>
                                </div>
                                <a href="{{ URL::route('servers') }}">Infos serveurs</a>
                                <a href="{{ URL::route('lottery.index') }}">Loterie</a>
                                <a href="{{ URL::route('ladder.general', [config('dofus.default_server_ladder')]) }}">Classements</a>
                                <a href="{{ URL::route('vote.index') }}">Cadeaux</a>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 clearfix">
                        <!--<div class="col-xs-6">
                            <div class="ak-list">
                                <div>
                                    <span class="ak-link-title">Tournois</span>
                                </div>
                                <a href="{{ URL::to('pvp/fights') }}">Combats</a>
                                <a href="{{ URL::to('pvp/champions') }}">Champion</a>
                                <a href="{{ URL::to('pvp/result') }}">Résultats</a>
                                <a href="{{ URL::to('pvp/reward') }}">Récompenses</a>
                            </div>
                        </div>-->
                        <div class="col-xs-6">
                            <div class="ak-list">
                                <div>
                                    <span class="ak-link-title">Support</span>
                                </div>
                                <a href="{{ URL::to('support') }}">Aide</a>
                                <a href="{{ config('dofus.social.forum') }}">Forum</a>
                                <a href="http://.us/contact">Contact</a>
                                <a href="http://forum.Intel.us/faq">FAQ</a>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="col-md-3 ak-block-download">
                    <a href="{{ URL::route('download') }}" class="btn btn-primary btn-lg">Télécharger le jeu</a>
                    <a class="ak-problem" href="{{ URL::to('support') }}">Un problème ? Contactez le support.        </a>
                    <div class="ak-social-network">
                        <a href="{{ config('dofus.social.facebook') }}" class="fb" target="_blank"></a>
                        <a href="{{ config('dofus.social.twitter') }}" class="tw" target="_blank"></a>
                        <a href="{{ config('dofus.social.youtube') }}" class="yo" target="_blank"></a>
                    </div>
                </div>
            </div>
            <div class="row ak_legal">
                <div id="col-md-12">
                    <div class="ak-legal">
                        <div class="row">
                            <div class="col-sm-1">
                                <a href="" class="ak-logo-Intel"></a>
                            </div>
                            <div class="col-sm-8">
                                <p>&copy; {{ date('Y') }} <a href="">{{ config('dofus.title') }}</a>. Tous droits réservés. <a href="{{ URL::to('cgu') }}" target="_blank">Conditions d'utilisation</a> - <a href="{{ URL::to('privacy') }}"target="_blank">Politique de confidentialité</a> - <a href="{{ URL::to('cgv') }}" target="_blank">Conditions Générales de Vente</a> - <a href="{{ URL::to('legal') }}" target="_blank">Mentions Légales</a></p>
                            </div>
                            <div class="col-sm-3"><span class="prevention"></span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </footer>

    @if (Session::has('popup'))
    {? $popup = Session::get('popup') ?}
    @endif

    @if (isset($popup))
    @include('popup.' . $popup)
    @endif

    @if (Session::has('notify'))
    {{ Toastr::add(Session::get('notify')['type'], str_replace("'", "\\'", Session::get('notify')['message'])) }}
    {!! Toastr::render() !!}
    @endif

    @if (!Auth::guest() && !Auth::user()->isStaff() && config('dofus.tawk.id'))
        <script type="text/javascript">
            var Tawk_API=Tawk_API||{}, Tawk_LoadStart=new Date();
            (function(){
                var s1=document.createElement("script"),s0=document.getElementsByTagName("script")[0];
                s1.async=true;
                s1.src='https://embed.tawk.to/{{ config('dofus.tawk.id') }}/default';
                s1.charset='UTF-8';
                s1.setAttribute('crossorigin','*');
                s0.parentNode.insertBefore(s1,s0);
                Tawk_API = Tawk_API || {};
                Tawk_API.visitor = {
                    name  : '{{ Auth::user()->pseudo }} - {{ Auth::user()->firstname }} {{ Auth::user()->lastname }}',
                    email : '{{ Auth::user()->email }}',
                    hash  : '{{ hash_hmac('sha256', Auth::user()->email, config('dofus.tawk.api') ) }}'
                };
            })();
        </script>
        @yield('scriptlogged')
    @endif

    @yield('bottom')

    <script type="text/javascript">
        var $ = require('jquery');

        $('.ui-dialog-titlebar-close').hide();

        setTimeout(function() {
            $('.ui-dialog-titlebar-close').fadeIn();
        }, 3000);

        $('.ui-dialog-titlebar-close').on('click', function() {
            $('.ui-dialog').fadeOut();
        });
    </script>
</body>
</html>
