<?php

namespace App\Providers;

use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\ServiceProvider;

class ResponseMacroServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Response::macro('collection', function ($data, $transformer, $status = 200) {
            return fractal($data, $transformer)
                ->withResourceName('data')
                ->parseIncludes(Request::input('include'))
                ->parseExcludes(Request::input('exclude'))
                ->respond($status);
        });
    }
}
