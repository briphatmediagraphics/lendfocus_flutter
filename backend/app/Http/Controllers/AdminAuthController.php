<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'password' => 'required|string',
        ]);

        $password = env('ADMIN_PASSWORD', 'landfocus2026');

        if ($data['password'] === $password) {
            $request->session()->put('is_admin', true);

            return redirect()->route('admin.dashboard');
        }

        return back()->withErrors(['password' => 'Invalid password']);
    }

    public function logout(Request $request)
    {
        $request->session()->forget('is_admin');
        return redirect('/');
    }
}
