<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserTest extends TestCase
{
    public function test_it_returns_full_name_correctly()
    {
        $user = new User([
            'first_name' => 'Jean',
            'last_name' => 'Dupont'
        ]);

        $this->assertEquals('Jean Dupont', $user->getFullName());
    }
}
