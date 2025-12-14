<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AdminLoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'email'=> ['required', 'email'],
            'password' => ['required'],
        ];
    }

    public function messages() {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'email.email' => 'メールアドレスの形式で入力してください',
            'password.required' => 'パスワードを入力してください',
        ];
    }

    public function authenticate() {

        // 管理者ガードで認証すること！
        if (! Auth::guard('admin')->attempt($this->only('email', 'password'))) {

            throw ValidationException::withMessages([
            'email' => 'ログイン情報が登録されていません',
            ]);
        }
    }
}
