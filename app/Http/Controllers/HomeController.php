<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Sheets\Sheets;

class HomeController extends Controller
{
    public function __invoke(Request $request, Sheets $sheets)
    {
        return view('home', ['posts' => $sheets->collection('posts')->all()]);
    }
}
