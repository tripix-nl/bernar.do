@extends('layout')

@section('content')
    <div class="container mx-auto">
        <h1 class="mb-2 font-semibold text-5xl">{{ $post->title }}</h1>
        <div class="border-b-2 border-grey-darker mb-4 pb-4 text-xs text-grey uppercase">{{ $post->date->format('j F Y') }}</div>
        <div class="content">
            {!! $post->contents !!}
        </div>
    </div>
@endsection
