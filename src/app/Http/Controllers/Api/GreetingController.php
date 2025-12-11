<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class GreetingController extends Controller
{
    public function greeting()
    {
        return 'Hello Controller!';
    }
}
