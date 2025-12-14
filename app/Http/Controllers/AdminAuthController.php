<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\AdminLoginRequest;

class AdminAuthController extends Controller
{
    // 管理者用ログイン画面
    public function login() {
        return view('admin.admin_login');
    }

    // 管理者ログイン処理
    public function loginStore(AdminLoginRequest $request) {
        $request->authenticate();
        $request->session()->regenerate();
        return redirect()->route('admin.attendance.list');
    }
}

