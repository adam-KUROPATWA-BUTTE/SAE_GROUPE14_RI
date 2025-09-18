<?php

namespace Model\DTO;

class User
{

    function __construct(public readonly string $username, private readonly string $password)
    {

    }

}