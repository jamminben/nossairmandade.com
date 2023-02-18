@extends('layouts.app')

@section('header_title')
    {{ !is_null($hinario) ? $hinario->name : null }}
@endsection

@section('panzer')
    <!-- Panzer -->
    <link href="/panzer/panzerlist.css" rel="stylesheet" media="all" />

    <script src="/panzer/panzerlist.js" type="text/javascript"></script>
@endsection

@section('extra_styles')
    <link rel="stylesheet" href="/css/hymns.css">
    <link rel="stylesheet" href="/css/hinarios.css">
@endsection

@section('page_title')
    @if(!is_null($hinario))

        @if (strlen($hinario->name) > 25)
            <div class="hinario-header">
                <h2 class="small display_table_cell_md">
                    {{ $hinario->name }}
                </h2>
            </div>
        @else
            <h2 class="small display_table_cell_md">
                {{ $hinario->name }}
        @endif

            <ol class="breadcrumb display_table_cell_md">
                @if ($hinario->name != $hinarioModel->getName())
                    <li>
                        {{ $hinarioModel->getName() }}
                    </li>
                @endif
                @if ($hinario->type_id == 1 && !empty($hinario->receivedBy))
                    <li>
                        {{ __('hymns.header.received_by') }} <a href="{{ url($hinario->receivedBy->slug) }}">{{ $hinario->receivedBy->display_name }}</a>
                    </li>
                @endif
                <li>
                    <a href="{{ url($hinario->slug . '/pdf') }}" target="_blank" style="color: #fac400;">{{ __('hinarios.pdf.link_text') }}</a>
                </li>
            </ol>

        @if (strlen($hinario->name) > 25)
            </h2>
        @endif

    @endif
@endsection

@section('content')
    <div class="col-sm-5 col-md-5 col-lg-5">
        <div class="mobile-active" style="padding-bottom: 20px;"><a href="#players">{{ __('hinarios.jump') }}</a></div>
        @php
            $sectionName = ''
        @endphp
        @if(!is_null($hinario))
            @foreach ($hinario->hymnHinarios as $hymnHinario)
                @if ($hinario->displaySections && $hymnHinario->section != $sectionName)
                    <div class="row">
                        <div class="hinario-section-name">{{ $hymnHinario->section }}</div>
                    </div>
                    @php
                        $sectionName = $hymnHinario->section
                    @endphp
                @endif
                <div class="hymn-list-name">
                    <a href="{{ url($hymnHinario->hymn->slug) }}">
                        {{ $hymnHinario->list_order }}.
                        @if (strlen($hymnHinario->hymn->name) > 30)
                            @php
                                $closestSpace = strpos($hymnHinario->hymn->name, ' ', 23);
                            @endphp
                            {!! mb_convert_case(substr_replace($hymnHinario->hymn->name, '<br>', $closestSpace, 0), MB_CASE_TITLE, "UTF-8") !!}
                        @else
                            {{ mb_convert_case($hymnHinario->hymn->name, MB_CASE_TITLE, "UTF-8") }}
                        @endif
                    </a>
                    @if (\Illuminate\Support\Facades\Auth::check())
                        <ul class="nav navbar-nav navbar-right add-hymn">
                            <li class="dropdown">
                                @include('layouts.partials.add_hymn_form', [ 'hymn' => $hymnHinario->hymn ])
                            </li>
                        </ul>
                    @endif
                </div>
            @endforeach
        @endif
    </div>
    <!--eof .col-sm-8 (main content)-->
    <!-- tabs -->
    <div class="col-sm-2 col-md-2 col-lg-2">
        <a name="players" class="name-anchor-mobile"></a>
        @include ('hinarios.partials.audio_block_display_nav')
    </div>

    <!-- sidebar -->
    <div class="col-sm-3 col-md-3 col-lg-3">
        <div class="row">
            @include ('hinarios.partials.audio_block_display_tabs')
        </div>
        <div class="row">
            @include('layouts.partials.other_media_hinario', [ 'entity' => $hinario ])
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12 padding-top-20">
                <!-- feedback form -->
                @if ( !is_null($hinario) && \Illuminate\Support\Facades\Auth::check())
                    @include('layouts.partials.feedback_form', [ 'entityType' => 'hinario', 'entityId' => $hinario->id ])
                @endif
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12 col-md-12 col-lg-12 padding-top-20">
                <!-- add media form -->
                @if ( !is_null($hinario) && \Illuminate\Support\Facades\Auth::check() && \Illuminate\Support\Facades\Auth::user()->hasRole('superadmin'))
                    @include('admin.layouts.partials.add_media_form', [ 'entityType' => 'hinario', 'entityId' => $hinario->id ])
                @endif
            </div>
        </div>
    </div>
    <!-- eof aside sidebar -->
@endsection

