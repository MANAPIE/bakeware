<?php

namespace App\Http\Middleware;

use Closure;

class HttpsProtocol
{
    public function handle($request, Closure $next)
    {
		// 설정에 따라 HTTPS로 전환을 강제함
		if(\App\Setting::find('https')->content=='A'){
	        if(!$request->secure()){
	            return redirect()->secure($request->getRequestUri());
	        }
        }

        return $next($request); 

    }
}