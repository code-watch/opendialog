<?php

namespace App\Http\Controllers;

class AdminController extends Controller
{
    public function handle()
    {
        $contents = view('admin');
        return response($contents)->header('Cache-Control', 'no-store');
    }
}
