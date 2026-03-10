<?php

namespace App\Providers;

use App\Repositories\Contracts\RoomRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Contracts\VoteRepositoryInterface;
use App\Repositories\Contracts\VoteSessionRepositoryInterface;
use App\Repositories\RoomRepository;
use App\Repositories\UserRepository;
use App\Repositories\VoteRepository;
use App\Repositories\VoteSessionRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(RoomRepositoryInterface::class, RoomRepository::class);
        $this->app->bind(VoteSessionRepositoryInterface::class, VoteSessionRepository::class);
        $this->app->bind(VoteRepositoryInterface::class, VoteRepository::class);
    }
}
