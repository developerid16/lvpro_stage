<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class XssSanitization
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $input = $request->all();
       
        $allowd = ['value','description','answer','email_content','inapp_content','how_to_use','term_of_use'];
        array_walk_recursive($input, function (&$input,$key) use($allowd) {
            if(in_array($key,$allowd)){
                $input = $input;
            }else{
                $input = strip_tags($input);

            }
        });
        $request->merge($input);
        return $next($request);
    }
}
