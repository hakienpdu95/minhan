<?php

namespace Modules\Auth\Actions\Auth;

use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;
use Modules\Auth\Data\Requests\LoginData;

class LoginUserAction
{
    use AsAction;

    public function handle(LoginData $data): bool
    {
        return Auth::attempt(
            ['email' => $data->email, 'password' => $data->password],
            $data->remember,
        );
    }
}
