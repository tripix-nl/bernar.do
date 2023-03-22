---
title: Why use interfaces in Laravel
date: 2022-10-19
summary: To answer the question posed in the title; for testing purposes. But besides that it's good practice you can easily get more value from using interfaces in php. 
---

As a developer we learn new things all the time, right? I try to anyway, but it's not always that easy. For example, I really struggled getting the concept of git at first and also MVC-frameworks wasn't that easy for me. These days I still have lots of questions about concepts like domain-driven-development, composition over inheritance and other patterns and abstractions. One thing I (think) to have managed to understand is the use of interfaces. So let's dive into a simple example to illustrate a use for it.

## Big fat controller

```
<?php

namespace App\Http\Controllers;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class JobsController extends Controller
{
    public function index()
    {
        $apiKey = "6JPWMRqaU8tZtE632TXQbwCaNmdu7dnQ";
        
        $http = new Client();
        $http->sendRequest(new Request('GET', 'https://somerandomjobboardwebsite.com/api/v1/jobs', [
            'Content-Type' => 'application/json',
            'Authorization' => "Bearer {$apiKey}",
        ]))
        
        $jobs = json_decode($response->getBody()->getContents(), true);
        
        return view('jobs.index', ['jobs' => $jobs]);
    }
}
```

Not bad right? The somerandomjobboardwebsite we need to show the jobs from on our customers' website, has a nice API. We only need to make a GET request to the jobs endpoint with the provided API key. The response contains some JSON that looks like this:

```
[
    { id: 1, title: 'Junior Marketeer', description: 'Lorem ipsum dolor sit amet...' },
    { id: 2, title: 'Floor Manager', description: 'Lorem ipsum dolor sit amet...' },
    { id: 3, title: 'Assistant Accountant', description: 'Lorem ipsum dolor sit amet...' },
]
```

Decode it into an array and pass it to the view where we `foreach` the shit out of it. Done!

## So what?

Well nothing initially, everything has it's place and sometimes keeping it simple and concise like this may be fine. However there are always things to improve!

### Hard coding the API key

It's bad practice to hard code things like this. For security reasons since it will become of version control, but also because you might need a separate key for developing and testing. Deploying a new release of the application just when the code has to be changed is also not great.

To fix this:

1. Add `SOME_RANDOM_JOB_BOARD_API_KEY=6JPWMRqaU8tZtE632TXQbwCaNmdu7dnQ` to the `.env` file.
2. Create a `some-random-job-board.php` file in the config directory, which returns an array with `api_key` key containing `env('SOME_RANDOM_JOB_BOARD_API_KEY')`.
3. Now use `config('some-random-job-board.api_key')` to finally read the API key.

### Use value objects

In the example code we're simply passing the data from the API directly to our view. The array of jobs might contain anything, we wouldn't know. To make it a bit more predictable we can use a **value object** (or data transfer object).
