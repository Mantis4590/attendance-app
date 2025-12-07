<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminAuthController extends Controller
{
    // 管理者用ログイン画面
    public function login() {
        return view('admin.login');
    }

    // 管理者ログイン処理
    public function loginStore(Request $request) {
        
    }
}
