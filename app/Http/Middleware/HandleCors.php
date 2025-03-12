<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class HandleCors
{
  public function handle(Request $request, Closure $next): Response
  {
    $response = $next($request);

    $response->headers->set('Access-Control-Allow-Origin', '*'); // Allow all origins
    $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
    $response->headers->set('Access-Control-Allow-Headers', '*');

    if ($request->isMethod('OPTIONS')) {
      return response('', 200)
        ->header('Access-Control-Allow-Origin', '*')
        ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
        ->header('Access-Control-Allow-Headers', '*');
    }

    return $response;
  }
}
