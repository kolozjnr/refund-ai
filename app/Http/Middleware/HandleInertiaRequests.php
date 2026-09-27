<?php
namespace App\Http\Middleware;

use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    protected $rootView = 'app';
    public function share(\Illuminate\Http\Request $request): array
    {
        return array_merge(parent::share($request), ['flash' => ['success' => fn () => $request->session()->get('success')]]);
    }
}
