<?php

namespace App\Domain\Auth\Repositories;

use App\Models\User;
use Illuminate\Contracts\Hashing\Hasher;

class UserRepository
{
    public function __construct(
        private readonly Hasher $hasher,
    ) {
    }

    public function findByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->first();
    }

    public function create(string $name, string $email, string $password): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'password' => $this->hasher->make($password),
        ]);
    }
}

<?php

namespace App\Domain\Auth\Repositories;

use App\Models\User;

class UserRepository
{
    public function findByEmail(string $email): ?User
    {
        return User::query()->where('email', $email)->first();
    }
}


