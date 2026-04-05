<?php

namespace App\Http\Controllers\web;

use App\Http\Controllers\Controller;
use App\Transformers\UserTransformer;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $userTransformer = new UserTransformer;
        $userData = $userTransformer->transform($user);

        return view('dashboard', compact('userData'));
    }
}
