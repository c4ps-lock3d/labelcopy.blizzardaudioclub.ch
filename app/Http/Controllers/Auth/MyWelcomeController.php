<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Release;
use Inertia\Inertia;
use App\Mail\artistLabelcopyCreated;
use Illuminate\Support\Facades\Mail;
use Spatie\WelcomeNotification\WelcomeController as BaseWelcomeController;

class MyWelcomeController extends BaseWelcomeController
{
    public function showWelcomeForm(Request $request, \Illuminate\Foundation\Auth\User $user)
    {
        return Inertia::render('Auth/SetPassword', [
            'email' => $request->email,
            'user' => $user,
        ]);
    }

    public function savePassword(Request $request, \Illuminate\Foundation\Auth\User $user)
    {
        $request->validate($this->rules());

        $user->password = Hash::make($request->password);
        $user->welcome_valid_until = null;
        $user->save();

        auth()->login($user);

        return $this->sendPasswordSavedResponse();
    }

    protected function sendPasswordSavedResponse(): Response
    {
        $release = Release::whereHas('release_members', function ($query) {
            $query->where('email', auth()->user()->email);
        })->first();
    
        if ($release) {
            Mail::to(auth()->user()->email)->queue(new artistLabelcopyCreated($release));
        }
        //return redirect(route('dashboard', absolute: false));
        return redirect()->to($this->redirectPath())->with('status', __('Bienvenue ! Tu es maintenant connecté !'));

    }

    protected function rules()
    {
        return [
            'password' => 'required|confirmed|min:8',
        ];
    }

    public function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : '/dashboard';
    }
}