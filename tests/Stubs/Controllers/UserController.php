<?php

namespace Tests\Stubs\Controllers;

use Illuminate\Routing\Controller;
use Tests\Stubs\Requests\UserStoreRequest;

class UserController extends Controller
{
    /**
     * @return bool
     */
    public function index(): bool
    {
        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function show(int $id): bool
    {
        return true;
    }

    /**
     * @param UserStoreRequest $request
     * @return bool
     */
    public function store(UserStoreRequest $request): bool
    {
        return true;
    }
}
