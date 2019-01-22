@extends('layout')

@section('title', "{$post->title} - bernar.do")

@section('content')
    <div class="container mx-auto">
        <h1 class="mb-2 font-semibold text-5xl">{{ $post->title }}</h1>
        <div class="border-b-2 border-grey-darker mb-4 pb-4 text-xs text-grey uppercase">{{ $post->date->format('j F Y') }}</div>
        <div class="content">
            {!! $post->contents !!}
        </div>
    </div>
@endsection

@section('seo')
    <meta property="og:title" content="{{ $post->title }} - bernar.do" />
    <meta property="og:description" content="{{ $post->summary }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:description" content="{{ $post->summary }}" />
    <meta name="twitter:title" content="{{ $post->title }} - bernar.do" />
    <meta name="twitter:site" content="@bernardohulsman" />
    <meta name="twitter:image" content="{{ asset('images/bernar.do-twitter-image-1024.png') }}" />
    <meta name="twitter:creator" content="@bernardohulsman" />
@endsection
