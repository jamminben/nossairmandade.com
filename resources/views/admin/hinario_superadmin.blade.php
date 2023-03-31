@extends('admin.layouts.app')

@section('header_title')
    {{ $title }}
@endsection

@section('page_title')
    <h2 class="small display_table_cell_md">{{ $title }}</h2>
@endsection

@section('content')

<div class="container">
    @foreach($hinarios as $h)
        @php
            $hi = json_decode($h->preloaded_json);
        @endphp
        
        <div class='row' >
            <div class="col-md-4" >
                {{ $hi->name }}
            </div>
        </div>
        
    @endforeach
</div>


@endsection
