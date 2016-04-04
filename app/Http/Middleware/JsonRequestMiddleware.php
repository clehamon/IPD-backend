<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class JsonRequestMiddleWare
{
    public function handle(Request $request, Closure $next)
    {
        if (in_array($request->method(), ['POST', 'PUT', 'DELETE', 'PATCH'])
        ) {
            $data = $request->json()->all();
            $request->request->replace(is_array($data) ? $data : []);
        }
        return $next($request);
    }
}