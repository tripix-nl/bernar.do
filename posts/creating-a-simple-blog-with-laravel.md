---
title: Creating a simple blog with Laravel
date: 2019-01-21
summary: Blogging is something I really wanted to do for a long while, but never really got to it. What better way to start blogging by showing how I put this simple blog together.
---

Blogging is something I really wanted to do for a long while, but never really got to it. What better way to start blogging by showing you guys how I put this simple blog together. It's nothing fancy, but it works and that is sometimes enough!

## Why not WordPress or any other solution?

WordPress is no stranger to me, that's how we (as a company) got into building websites for clients. For my own blog I just wanted something way more clean and simple. Laravel might come out of the box pretty packed, but we can get rid of any stuff we don't need, while keeping all possibilities open. WordPress doesn't let you do that very much and even solutions that allow for [using composer](https://roots.io/bedrock/) and [themes with MVC](https://roots.io/sage/) aren't perfect.

Besides all that; I wanted to do the posts in Markdown, which I prefer over working in any TinyMCE-like editor. In fact I don't want any backend to login to, the posts should simply reside in the code itself, in the repository. WordPress' strength definitively lies in it's CMS-features; the post types, categories and maybe more importantly the media library. But since I'm not going to need all of that, no need for WordPress right?

## Requirements

You will need a fresh Laravel installation to start with, version 5.7 at the time of writing. I'm assuming you are familiar with [creating a new Laravel project](https://laravel.com/docs/5.7#installation) and running it. There are plenty of options to run Laravel on your machine, I'm using [Homestead](https://laravel.com/docs/5.7/homestead) currently. My Homestead box is not completely up-to-date, so PHP 7.1 will have to do.

Be sure to also install [Node.js](https://nodejs.org/en/) in order to use `npm` later on.

## Looking good

Something a lot of developers are struggling with probably; the design of their own projects. It's nice to get to work on a project of your own, where you set the rules and decide how it will work and how it looks. The latter being an afterthought or at least not a priority.

There are plenty of examples around, lot's of other people's blog to look at. These are the basics I settled on:

- Dark theme
- Simply bernar.do in text as a logo, no logo designs in the header
- Links to Github/Twitter/etc. using icons in the header
- Some serif font for the post itself
- Link to RSS feed in the footer

## Cleaning up

Laravel comes with a [frontend preset](https://laravel.com/docs/5.7/frontend), which you can remove by running `php artisan preset none`, but that will still leave you with some stuff. For now I'm assuming you started with the basic preset and did not remove it. So remove the `js` and `sass` folders from the `resources` directory. Also remove the `welcome.blade.php` file from `resources/views`. By default `package.json`, specifically the `devDependencies`, will look like this:

```
{
    "devDependencies": {
        "axios": "^0.18",
        "bootstrap": "^4.0.0",
        "cross-env": "^5.1",
        "jquery": "^3.2",
        "laravel-mix": "^4.0.7",
        "lodash": "^4.17.5",
        "popper.js": "^1.12",
        "resolve-url-loader": "^2.3.1",
        "sass": "^1.15.2",
        "sass-loader": "^7.1.0",
        "vue": "^2.5.17"
    }
}
```

Since I'm not planning on using any JavaScript we can remove `axios`, `jquery`, `lodash`, `poppers.js` and `vue`. The `bootstrap` dependency can also be removed in favor of `tailwindcss`, more on that later. Instead of `sass` we'll be using `less` so let's get rid of `sass` and `sass-loader`. Which leaves us with just two dependencies.

```
{
    "devDependencies": {
        "cross-env": "^5.2.0",
        "laravel-mix": "^4.0.13"
    }
}
```

It's been a while since simply writing a `style.css` file and maybe `scripts.js` file was just it. These days pre- and post-processors rule the world. In our case it will be [Laravel Mix](https://laravel.com/docs/5.7/mix) to compile `less` into `css`. Both `cross-env` and `laravel-mix` are needed to compile our assets.

Run `npm install` to install the dependencies we're left with. 

## Tailwind

We're using [Tailwind CSS](https://tailwindcss.com/) these days for everything, coming from Bootstrap. Both have their own pros and cons, but I'm not going to dive fully into that now. For me not having to name every `div` (that might only occur once) is a great win. Especially layout/grid constructs that need a little margin or padding (for example); using Tailwind adding a `mb-8` class is all you have to do. Using components, partials and some classes should help to overcome the cons of a utility-based solution like Tailwind.

[Install Tailwind](https://tailwindcss.com/docs/installation) using `npm install tailwindcss --save-dev` and create a new config file by running `./node_modules/.bin/tailwind init`. The later creates a `tailwind.js` file. Take a look if you will, I made no initial changes, it's pretty nice with sensible defaults straight out of the box.

Create an `app.less` file in `resources/less` and add the [default stylesheet](https://tailwindcss.com/docs/installation#3-use-tailwind-in-your-css):

```
/**
 * This injects Tailwind's base styles, which is a combination of
 * Normalize.css and some additional base styles.
 *
 * You can see the styles here:
 * https://github.com/tailwindcss/tailwindcss/blob/master/css/preflight.css
 *
 * If using `postcss-import`, use this import instead:
 *
 * @import "tailwindcss/preflight";
 */
@tailwind preflight;

/**
 * This injects any component classes registered by plugins.
 *
 * If using `postcss-import`, use this import instead:
 *
 * @import "tailwindcss/components";
 */
@tailwind components;

/**
 * Here you would add any of your custom component classes; stuff that you'd
 * want loaded *before* the utilities so that the utilities could still
 * override them.
 *
 * Example:
 *
 * .btn { ... }
 * .form-input { ... }
 *
 * Or if using a preprocessor or `postcss-import`:
 *
 * @import "components/buttons";
 * @import "components/forms";
 */

/**
 * This injects all of Tailwind's utility classes, generated based on your
 * config file.
 *
 * If using `postcss-import`, use this import instead:
 *
 * @import "tailwindcss/utilities";
 */
@tailwind utilities;

/**
 * Here you would add any custom utilities you need that don't come out of the
 * box with Tailwind.
 *
 * Example :
 *
 * .bg-pattern-graph-paper { ... }
 * .skew-45 { ... }
 *
 * Or if using a preprocessor or `postcss-import`:
 *
 * @import "utilities/background-patterns";
 * @import "utilities/skew-transforms";
 */
```

To compile `app.less` into a CSS file you need to make some changes to `webpack.mix.js`. This file dictates how assets are compiled/copied/versioned/etc by Laravel Mix:

```
const mix = require('laravel-mix'),
    tailwindcss = require('tailwindcss');

mix
    .less('resources/less/app.less', 'public/css')
    .options({
        postCss: [
            tailwindcss('./tailwind.js'),
        ]
    })
    .version();
```

You can now run `npm run dev` to generate the `app.css` file. Check `public/css` to see if it's actually there.

## Layout

Let's put together a basic layout for our blog. We'll be using [Blade](https://laravel.com/docs/5.7/blade). Create a `layout.blade.php` file in `resources/views` with the following content:

```
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <!-- CSRF Token -->
        <meta name="csrf-token" content="{{ csrf_token() }}" />

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Styles -->
        <link href="{{ mix('css/app.css') }}" rel="stylesheet" />
    </head>
    <body class="flex flex-col h-screen bg-grey-darker font-sans font-normal leading-normal text-white">
        <div class="container mx-auto mb-8">
            <div class="flex justify-between pt-4">
                <a href="{{ url('/') }}" class="text-xl font-semibold text-white no-underline hover:text-blue-lighter">example.com</a>
                <div>
                    <a href="{{ url('/') }}" class="inline-block mr-4 text-white no-underline hover:underline">Home</a>
                    <a href="{{ url('about') }}" class="inline-block text-white no-underline hover:underline">About</a>
                </div>
                <div>
                    <a href="#" class="mr-2 text-white no-underline hover:text-blue-lighter" target="_blank" title="Github">Github</a>
                    <a href="#" class="text-white no-underline hover:text-blue-lighter" target="_blank" title="Twitter">Twitter</a>
                </div>
            </div>
        </div>
        <div class="flex-1 p-4">
            @yield('content')
        </div>
        <div class="p-4 text-center text-sm">
            <a href="#" class="inline-block text-white no-underline hover:underline" target="_blank">rss</a>
        </div>
    </body>
</html>
```

The layout consists of a header, content area and footer. The `flex`,  `flex-col` and `h-screen` classes on the `body`, together with `flex-1` on the content area, will make sure that the footer will always be at the bottom of the screen.

Be sure to replace the example.com logo and the links with something that you'd want there.

## Home

Our home page will simply display a list of blog posts, nothing more. Start by creating a new view file; `home.blade.php` in `resources/view`. Mine looks like this:

```
@extends('layout')

@section('content')
    <div class="container mx-auto">
        <div>
            @foreach($posts as $post)
                <div class="mb-8 pb-8 {{ $loop->last ? '' : 'border-b-2 border-grey-dark' }}">
                    <h2 class="mb-2 font-semibold text-4xl">
                        <a href="{{ url($post->slug) }}" class="block no-underline text-white hover:text-blue-lighter">
                            {{ $post->title }}
                        </a>
                    </h2>

                    <div class="mb-2 text-xs text-grey uppercase">
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
```

There is already some stuff in there that will render our posts later. 

Next step is to create a controller that will handle displaying the home page. Use the [artisan console](https://laravel.com/docs/5.7/artisan) to create a new controller; `php artisan make:controller HomeController`, or simply create the file yourself. Running the command will create a `HomeController.php` file in `app/Http/Controllers` containing a `HomeController` class. I added an `__invoke` method to mine, which makes it a [single action controller](https://laravel.com/docs/5.7/controllers#single-action-controllers). If you don't like this approach any method name would do, but `index` would probably be the best fit.

```
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view('home', ['posts' => []]);
    }
}
```

Notice I added the `posts` variable to the view, which is just an empty array. This will make sure our home page works, while we have no posts yet.

In `routes/web.php` we can remove any other existing route and add the route to our home page.

```
<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', 'HomeController');
```

## Hoorah!

By now, you should be able to visit your work-in-progress blog without any errors. Hoorah!

## Post Markdown files

We've finally arrived to the part it was all about! This is where things get a bit more interesting and even more opinionated I guess. Our posts are going to be static Markdown files, which we'll store along with the code for the blog.

There are several reasons to go for this approach:

- I'm using [PhpStorm](https://www.jetbrains.com/phpstorm/), which is a great Markdown editor, apart from line wrapping issues
- No need for login/auth features
- We can do without a database 
- Keeping it really simple

With this in mind, create a `lorem-ipsum.md` file in a new `posts` directory in the project root. To have some meta data available we'll use Markdown with YAML meta data:

```

---
title: Lorem ipsum dolor sit amet
date: 2019-01-11
summary: Lorem ipsum dolor sit amet.
---

Lorem ipsum dolor sit amet.

```

In order to read those Markdown files, we're going to use the [spatie/sheets](https://github.com/spatie/sheets) package. Besides reading Markdown files from a given directory it will also parse Markdown to HTML, which makes it a great solution for our blog. Install using composer by running `composer require spatie/sheets`. To publish the `sheets.php` config file also run `php artisan vendor:publish --provider="Spatie\Sheets\SheetsServiceProvider" --tag="config"`.

Open the `sheets.php` file in the `config` directory and change it to the following:

```
<?php

return [

    'default_collection' => 'posts',

    'collections' => [

        'posts' => [
            'disk' => 'posts',
            'sheet_class' => App\Models\Post::class,
            'path_parser' => Spatie\Sheets\PathParsers\SlugParser::class,
            'content_parser' => Spatie\Sheets\ContentParsers\MarkdownWithFrontMatterParser::class,
            'extension' => 'md',
        ],
    ],
];
```

The Markdown file starts with some meta data, so we have to use the `MarkdownWithFrontMatterParser` as `content_parser` so our meta data will be parsed and available in our `Post` model.

For this to work open `config/filesystems.php` file and add the following disk, after the default `local`, `public` and `s3` disks:

```php
'posts' => [
    'driver' => 'local',
    'root' => base_path('posts'),
],
```

## Post model

Another requirement is the `sheet_class` directive in the `sheets.php` config file. I like to pretend it's a normal model, with the difference that it's stored on disk instead of in the database. So create a `Post.php` file in the `app\Models` directory, that extends the `Sheet` class instead of `Eloquent`:

```
<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Spatie\Sheets\Sheet;

class Post extends Sheet implements Feedable
{
    public function getDateAttribute()
    {
        return Carbon::createFromTimestamp($this->attributes['date']);
    }
}
``` 

## Displaying the post

We can now move on to displaying the post, so let's create a `post.blade.php` file in `resources/views` with the following contents:

```
@extends('layout')

@section('content')
    <div class="container mx-auto">
        <h1 class="mb-2 font-semibold text-4xl">{{ $post->title }}</h1>
        <div class="border-b-2 border-grey-dark mb-4 pb-4 text-xs text-grey uppercase">{{ $post->date->format('j F Y') }}</div>
        <div class="content">
            {!! $post->contents !!}
        </div>
    </div>
@endsection
```

Next create a controller `PostController` that looks like this:

```
<?php

namespace App\Http\Controllers;

use App\Models\Post;

class PostController extends Controller
{
    public function __invoke(Post $post)
    {
        return view('post', ['post' => $post]);
    }
}
```

We'll add a route for that too:

```
Route::get('/{slug}', 'PostController')->where('slug', '[a-z0-9\-]+');
```

Make sure this route is at the bottom of the route file. It's sort of a catch all route, but we defined that `{slug}` can only contain alphanumeric characters and hyphens.

Still some work to do, plenty actually! Let's continue by opening the `RouteServiceProvider.php` file in `app/Providers`. There we will have to explicitly define how to find a `Post` based on `{slug}` from the route. The `boot` method would have to look like this:

```php
public function boot()
{
    //

    parent::boot();

    Route::bind('slug', function ($slug) {
        return $this->app->make(Sheets::class)
                ->collection('posts')
                ->get($slug) ?? abort(404);
    });
}
```

Be sure to import the `Spatie\Sheets\Sheets` class at the top of this file for this to work.

## We did it!

Well... almost. Remember the empty array `posts` variable in our home controller? Open the `HomeController.php` file and change it to the following:

```
<?php

namespace App\Http\Controllers;

use Spatie\Sheets\Sheets;

class HomeController extends Controller
{
    public function __invoke(Sheets $sheets)
    {
        return view('home', ['posts' => $sheets->collection('posts')->all()]);
    }
}
```

That should to the trick! Navigating to `/` should display the 'Lorem Ipsum' post and clicking it should show us the full blog post.

## Bonus points

To take it to the next level we can definitely improve on some things. We could add a nice font and some styling to our content, meta tags for SEO purposes might not be a bad idea and maybe you'd want to add comments. Next are a few extras to implement.

### 1. Using Commonmark to highlight code

You can [use Commonmark for code highlighting](https://sebastiandedeyne.com/highlighting-code-blocks-with-leaguecommonmark), which is done server side, not client side using JavaScript.
We're using the spatie/sheets package to read our Markdown files and parse them to HTML. Under the hood it's using the [thephpleague/commonmark](https://github.com/thephpleague/commonmark) package to actually parse Markdown to HTML.
 
First install the [spatie/commonmark-highlighter](https://github.com/spatie/commonmark-highlighter) package using `composer require spatie/commonmark-highlighter`. Then we need to override the `CommonMarkConverter` used in the `spatie/sheets` package to add the code highlighting. So open up the `AppServiceProvider.php` file in `app/Providers` and change the register method:

```php
$this->app->when([MarkdownParser::class, MarkdownWithFrontMatterParser::class])
    ->needs(CommonMarkConverter::class)
    ->give(function () {
        $environment = Environment::createCommonMarkEnvironment();
        $environment->addBlockRenderer(FencedCode::class, new FencedCodeRenderer(['html', 'php', 'js']));
        $environment->addBlockRenderer(IndentedCode::class, new IndentedCodeRenderer(['html', 'php', 'js']));
        return new CommonMarkConverter(['safe' => true], $environment);
    });
```

The spatie/sheets package does something similar in it's own service provider. In our own app service provider we override that to return a `CommenMarkConverter` instance with support for code highlighting. Make sure to import these classes at the top of the file.

If you inspect the HTML thats generated from a code block in Markdown, you can see that instead of just `code` and `pre` tags, some classes and elements are added. To actually add the fancy colors we'll need to get hold of some CSS; install the [`highlight.js`](https://github.com/highlightjs/highlight.js) package using `npm install highlight.js --save` and edit the `resources/less/app.less` file. At the bottom add the following:

```less
@import (css, inline) "~highlight.js/styles/darcula.css";
```

That should do it! There are many more [styles](https://highlightjs.org/static/demo/), so be sure to pick one that you like.

### 2. Response cache

In order to avoid parsing our Markdown files on every request we'll use the [spatie/laravel-responsecache](https://github.com/spatie/laravel-responsecache) package to speed things up. Simply follow instructions from the packages' readme file. The only thing I did in addition to installing and configuring it is adding `RESPONSE_CACHE_ENABLED=false` to the .env file on my development machine. That means it will simply work in production by not adding this statement, or setting it to `true` for that matter.

### 3. RSS feed

Personally I'm not using any RSS feed readers, but I understand why people like it. So let's add a feed to our blog. Like for everything humanity could possibly want, the guys at Spatie have a package for it: [spatie/laravel-feed](https://github.com/spatie/laravel-feed).

Simply follow the installation steps from the packages' readme and then change the `feed.php` config file like this:

```
<?php

return [
    'feeds' => [
        'main' => [
            /*
             * Here you can specify which class and method will return
             * the items that should appear in the feed. For example:
             * 'App\Model@getAllFeedItems'
             *
             * You can also pass an argument to that method:
             * ['App\Model@getAllFeedItems', 'argument']
             */
            'items' => 'App\Models\Post@getFeedItems',

            /*
             * The feed will be available on this url.
             */
            'url' => '/feed',

            'title' => 'My feed',

            /*
             * The view that will render the feed.
             */
            'view' => 'feed::feed',
        ],
    ],
];
```   

Also we need to add some methods to our `Post` model:

```
<?php

namespace App\Models;

use Illuminate\Support\Carbon;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Spatie\Sheets\Facades\Sheets;
use Spatie\Sheets\Sheet;

class Post extends Sheet implements Feedable
{
    public function toFeedItem()
    {
        return FeedItem::create([
            'id' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'updated' => $this->date,
            'link' => url($this->slug),
            'author' => 'Your Name',
        ]);
    }

    public static function getFeedItems()
    {
        return Sheets::collection('posts')->all();
    }

    public function getDateAttribute()
    {
        return Carbon::createFromTimestamp($this->attributes['date']);
    }
}
```

That should be all! Opening up `/feed` in your browser should display a feed of your posts. Be sure to change the link in the footer. 

## In closing

There are probably plenty of things to improve on this, let me know on Twitter! However, it was fun to write a blog post like this and I'll try to do so on a regular basis. Hopefully the blog itself improves a bit more to, over the course of a some new blog posts.
