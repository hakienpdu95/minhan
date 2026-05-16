<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Shared\Tenancy\Models\Organization;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    public function create(array $input): User
    {
        Validator::make($input, [
            'organization_name' => ['required', 'string', 'max:255'],
            'name'              => ['required', 'string', 'max:255'],
            'email'             => ['required', 'string', 'email', 'max:255', Rule::unique(User::class)],
            'password'          => $this->passwordRules(),
        ], [
            'organization_name.required' => 'Tên tổ chức là bắt buộc.',
            'name.required'              => 'Họ và tên là bắt buộc.',
            'email.unique'               => 'Email này đã được sử dụng.',
        ])->validate();

        return DB::transaction(function () use ($input): User {
            // 1. Tạo Organization — slug tự động từ tên (trong Organization::boot())
            $organization = Organization::create([
                'name'     => $input['organization_name'],
                'status'   => 'active',
                'settings' => ['timezone' => 'Asia/Ho_Chi_Minh', 'locale' => 'vi'],
            ]);

            // 2. Tạo User chủ sở hữu
            $user = User::create([
                'name'            => $input['name'],
                'email'           => $input['email'],
                'password'        => Hash::make($input['password']),
                'organization_id' => $organization->id,
            ]);

            // 3. Đặt owner cho Organization
            $organization->update(['owner_id' => $user->id]);

            // 4. Gán role CEO
            $user->assignRole('CEO');

            return $user;
        });
    }
}
