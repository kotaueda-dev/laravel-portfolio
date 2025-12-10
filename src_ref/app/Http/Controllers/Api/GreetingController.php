<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class GreetingController extends Controller
{
    public function greeting()
    {
        return "Hello Controller!";
    }
}
