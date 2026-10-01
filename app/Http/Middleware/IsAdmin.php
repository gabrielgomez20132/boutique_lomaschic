<?php

namespace App\Http\Middleware;

use Closure;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (auth()->check()) 
        {
            if (auth()->user()->IsAdmin()) 
            {
                if (auth()->user()->role_id == 2 ) {
                    $url = $request->url();

                    $pages=explode("/", $url);
                    $current_page=$pages[count($pages)-1];
                    $admin_page=$pages[count($pages)-2];

                    if( $admin_page == "admin" ) {
                        if( $current_page == "clientes" ) {
                            return abort(404);
                        } 

                        if( $current_page == "encargados" ) {
                            return abort(404);
                        } 

                        if( $current_page == "productos" ) {
                            return abort(404);
                        } 

                        if( $current_page == "categorias" ) {
                            return abort(404);
                        } 

                        if( $current_page == "control" ) {

                            if( $request->input('caja_abierta') != 1 ) {   // Para permitir cierre de caja
                                return abort(404);
                            }
                        } 
                    }
                }

                return $next($request);
            }
            else
            {
                return redirect('home');
            }
        }
        else
        {
            return redirect('home');
        }
    }
}
