<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    public function create(array $input): User
    {
        // RegisterRequest の要件に合わせる
        $rules = [
            'name' => ['required', 'string', 'max:20'],  // ← 修正！20文字
            'email' => [
                'required',
                'string',
                'email',
                Rule::unique(User::class),
            ],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $messages = [
            'name.required' => '名前を入力してください',
            'name.string' => '名前は文字で入力してください',
            'name.max' => '名前は255字以下で入力してください',
            'email.required' => 'メールアドレスを入力してください',
            'email.string' => 'メールアドレスは文字で入力してください',
            'email.email' => 'メール形式で入力してください',
            'email.unique' => 'こちらのメールアドレスはすでに登録されています',
            'password.required' => 'パスワードを入力してください',
            'password.string' => 'パスワードは文字列で入力してください',
            'password.min' => 'パスワードは8文字以上で入力してください',
            'password.confirmed' => 'パスワードと一致しません',
        ];

        Validator::make($input, $rules, $messages)->validate();

        return User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => Hash::make($input['password']),
        ]);
    }
}
