<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

class AuthController extends Controller
{
    public function register() {
        return view('auth.register');
    }

    public function login() {
        return view('auth.login');
    }

    public function store(RegisterRequest $request) {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // 登録したユーザーでログインさせる
        Auth::login($user);

        // 明示的に送信
        $user->sendEmailVerificationNotification();

        // 認証誘導画面へ
        return redirect()->route('verification.notice');
    }

    public function loginStore(LoginRequest $request) {
        // ここで認証
        $request->authenticate();

        // 認証成功したらホーム
        return redirect()->route('attendance.index');
    }
}
