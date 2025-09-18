<?php

namespace Controllers;

interface Controller
{

    function control();

    static function resolve(string  $path): bool;

}