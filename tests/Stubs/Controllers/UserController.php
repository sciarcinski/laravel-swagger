<?php

namespace Sciarcinski\LaravelSwagger\Tests\Stubs\Controllers;

use Illuminate\Routing\Controller;
use Sciarcinski\LaravelSwagger\Tests\Stubs\Requests\UserRequest;

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
     * @param UserRequest $request
     * @return bool
     */
    public function store(UserRequest $request): bool
    {
        return true;
    }

    /**
     * @param int $id
     * @param UserRequest $request
     * @return true
     */
    public function update($id, UserRequest $request): bool
    {
        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function destroy(int $id): bool
    {
        return true;
    }

    /**
     * @param int $id
     * @return bool
     */
    public function ban(int $id): bool
    {
        return true;
    }
}
