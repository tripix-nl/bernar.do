@extends('layout')

@section('title', "{$post->title} - bernar.do")

@section('content')
    <div class="container mx-auto">
        <h1 class="mb-2 font-semibold text-5xl leading-none">{{ $post->title }}</h1>
        <div class="border-b-2 border-gray-700 mb-4 pb-4 text-xs text-gray uppercase">{{ $post->date->format('j F Y') }}</div>
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
    <meta name="twitter:creator" content="@bernardohulsman" />
@endsection
