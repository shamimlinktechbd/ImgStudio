<?php

namespace App\Http\Controllers;

use App\Models\ImageAsset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email or password mileni.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();
        $this->attachGuestImages($request);

        return redirect()->intended(route('images.index'));
    }

    public function showRegister()
    {
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'min:8'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        $this->attachGuestImages($request);

        return redirect()->route('images.index');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('images.index');
    }

    private function attachGuestImages(Request $request)
    {
        $guestToken = $request->session()->get('guest_token');

        if ($guestToken && $request->user()) {
            ImageAsset::where('guest_token', $guestToken)
                ->whereNull('user_id')
                ->update(['user_id' => $request->user()->id]);
        }
    }
}
