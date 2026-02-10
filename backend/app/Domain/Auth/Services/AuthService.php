<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Repositories\UserRepository;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Hashing\Hasher;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly AuthFactory $auth,
        private readonly Hasher $hasher,
        private readonly UserRepository $users,
    ) {
    }

    /**
     * Пытается залогинить пользователя по email/паролю.
     *
     * @throws ValidationException
     */
    public function login(string $email, string $password): User
    {
        $user = $this->users->findByEmail($email);

        if (! $user || ! $this->hasher->check($password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Неверный логин или пароль'],
            ]);
        }

        $this->auth->guard('web')->login($user, true);

        return $user;
    }

    public function logout(): void
    {
        $this->auth->guard('web')->logout();
    }

    public function currentUser(): ?User
    {
        return $this->auth->guard('web')->user();
    }
}

<?php

namespace App\Domain\Auth\Services;

use App\Domain\Auth\Repositories\UserRepository;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly Guard $guard,
        private readonly UserRepository $users,
    ) {
    }

    public function login(Request $request): array
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if (!$this->guard->attempt($credentials)) {
            throw ValidationException::withMessages([
                'email' => 'Неверный логин или пароль',
            ]);
        }

        $request->session()->regenerate();

        $user = $this->guard->user();

        return [
            'id' => $user?->id,
            'name' => $user?->name,
            'email' => $user?->email,
        ];
    }

    public function logout(Request $request): void
    {
        $this->guard->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}


