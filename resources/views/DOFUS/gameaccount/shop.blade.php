@extends('layouts.contents.default')
@include('layouts.menus.base')

@section('breadcrumbs')
{? $page_name = 'Marché des personnages' ?}

@stop

@section('content')
<div class="ak-container ak-main-center">
    <div class="ak-title-container ak-backlink">
        <h1><span class="ak-icon-big ak-bank"></span></a> Marché des personnages</h1>
  
    </div>
    <div class="ak-responsivetable-wrapper">
        <table border="1" class="ak-ladder ak-container ak-table ak-responsivetable" style="white-space: nowrap; visibility: visible;">
            <thead>
                <tr class="ak-ajaxloader">
                    <th>#</th>
                    <th>Nom</th>
                    <th>Classe</th>
                    <th class="ak-center">Niveau</th>
                    <th class="ak-xp-total">Prix</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($characters as $i => $character)
                <tr class="@if ($i == 0) ak-first-ladder @elseif ($i %2 == 0) ak-bg-even @else ak-bg-odd @endif">
                    <td class="ak-rank">
                        @if ($i < 3)
                        <span class="ak-icon-position ak-position-{{ $i+1 }}">{{ $i+1 }}</span>
                        @else
                        {{ $i+1 }}
                        @endif
                    </td>
                    <td class="ak-name">
                        <span class="ak-breed-icon breed{{ $character->Breed }}_{{ $character->Sex }}"></span>
                        <a href="{{URL::route('characters.view',[$server,$accounts[$character->Id],$character->Id])}}">{{ $character->Name }}</a>
                    </td>
                    <td class="ak-class">{{ $character->classe() }}</td>
                    <td class="ak-center">{{ $character->level($server) }}</td>
                  
                    <td class="ak-xp-total">{{ $price[$character->Id] }}</td>

                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@stop
