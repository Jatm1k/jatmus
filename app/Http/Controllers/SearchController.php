<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        return Inertia::render('Search', []);
    }
}
