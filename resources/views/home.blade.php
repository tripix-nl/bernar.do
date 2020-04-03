@extends('layout')

@section('content')
    <div class="container mx-auto">
        <div>
            @foreach($posts as $post)
                <div class="mb-8 pb-8 {{ $loop->last ? '' : 'border-b-2 border-gray-700' }}">
                    <h2 class="mb-2 font-semibold text-4xl leading-none">
                        <a href="{{ url($post->slug) }}" class="block no-underline text-white hover:text-blue-300">
                            {{ $post->title }}
                        </a>
                    </h2>

                    <div class="mb-2 text-xs text-gray uppercase">
                        {{ $post->date->format('j F Y') }}
                    </div>

                    <div>
                        {{ $post->summary }}
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endsection
