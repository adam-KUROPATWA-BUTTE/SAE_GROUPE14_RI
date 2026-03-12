<?php

namespace Tests\Model\Entity;

use PHPUnit\Framework\TestCase;
use Model\Entity\User;

class UserTest extends TestCase
{
    public function testConstructorAndGetters(): void
    {
        $user = new User(1, "john@example.com", "12345", "password123", "student");

        $this->assertEquals(1,                  $user->getId());
        $this->assertEquals("john@example.com", $user->getEmail());
        $this->assertEquals("12345",            $user->getNumetu());
        $this->assertEquals("password123",      $user->getPassword());
        $this->assertEquals("student",          $user->getRole());
        $this->assertNull($user->getDepartement());
    }

    public function testSetters(): void
    {
        $user = new User(null, "a@a.com", null, "pass", "admin");

        $user->setId(10);
        $user->setEmail("new@example.com");
        $user->setNumetu("54321");
        $user->setPassword("newpass");
        $user->setRole("teacher");
        $user->setDepartement("Informatique");

        $this->assertEquals(10,               $user->getId());
        $this->assertEquals("new@example.com",$user->getEmail());
        $this->assertEquals("54321",          $user->getNumetu());
        $this->assertEquals("newpass",        $user->getPassword());
        $this->assertEquals("teacher",        $user->getRole());
        $this->assertEquals("Informatique",   $user->getDepartement());
    }
}