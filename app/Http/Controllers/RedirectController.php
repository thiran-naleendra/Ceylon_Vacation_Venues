<?php

namespace App\Http\Controllers;

use App\Models\Redirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $path = '/'.ltrim($request->path(), '/');
        $redirect = Redirect::active()->where('source_path', $path)->firstOrFail();

        return redirect($redirect->destination_path, $redirect->status_code);
    }
}
