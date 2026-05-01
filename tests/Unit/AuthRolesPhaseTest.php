<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Http\Middleware\EnsureRole;
use App\Models\User;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class AuthRolesPhaseTest extends TestCase
{
    public function test_role_middleware_allows_matching_role(): void
    {
        $request = Request::create('/staff');
        $request->setUserResolver(fn () => new User(['role' => UserRole::OWNER]));

        $response = (new EnsureRole())->handle($request, fn () => response('ok'), 'OWNER');

        $this->assertSame('ok', $response->getContent());
    }

    public function test_role_middleware_blocks_wrong_role(): void
    {
        $this->expectException(HttpException::class);

        $request = Request::create('/staff');
        $request->setUserResolver(fn () => new User(['role' => UserRole::CASHIER]));

        (new EnsureRole())->handle($request, fn () => response('ok'), 'OWNER');
    }
}
