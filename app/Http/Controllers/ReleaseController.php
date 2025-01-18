<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Release;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class ReleaseController extends Controller
{
    /**
     * Display the registration view.
     
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    } */

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'catalog' => 'required|string|max:255',
        ]);

        $release = Release::create([
            'catalog' => $request->catalog,
        ]);

        //event(new Registered($user));

        //Auth::login($user);

        //return redirect(route('dashboard', absolute: false));
    }
}
