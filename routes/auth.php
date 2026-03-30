<?php

use App\Models\User;
use Illuminate\Http\Request;

Route::middleware('guest')->group(function () {
    Route::view('login', 'auth.login')->name('login');
    Route::post('login', function (Request $request) {
        $validator = Validator::make($request->all(), [
            'username' => 'required|string',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
            return to_route('login')
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::where('username', $request->username)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()
                ->withErrors([
                    'username' => 'Username atau password salah',
                ])
                ->withInput();
        }

        Auth::login($user);
        $request->session()->regenerate();
        return redirect()->intended('/');
    })->name('login.action');
});

Route::post('/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/login');
})->name('logout')->middleware('auth');