<?php
namespace App\Http\Middleware;
use Closure;
use Illuminate\Http\Request;
class LocalPreview
{
    public function handle(Request $request, Closure $next)
    {
        abort_unless(config('logbook.demo') && app()->environment(['local', 'testing']), 503, 'Pratonton tempatan tidak diaktifkan. Sambungkan pengesahan identiti sebelum penggunaan sebenar.');
        if (! $request->routeIs('login', 'login.store') && ! session()->has('actor')) return redirect()->route('login');
        return $next($request);
    }
}
