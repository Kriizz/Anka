@extends('layouts.master')

@section('header')
    {!! Html::style('css/404.css') !!}
@stop

{? $page_name = '404' ?}

@section('page')
<div class="container ak-main-container ">
    <div class="ak-404">
        <div class="row">
            <div class="col-md-6"></div>
            <div class="col-md-6">
                <div class="ak-text"><div>404</div><div>Tous les chemins mènent au monde des douze...</div><div> sauf celui-là !</div></div>
                <div class="clearfix"></div>
                <br>
                <a class="btn btn-primary btn-lg" href="{{ URL::route('home') }}">Aller à l'accueil</a>
            </div>
        </div>
    </div>
</div>
@stop
