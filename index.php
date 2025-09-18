<?php

use Controllers\Register;
use Controllers\Login;

include __DIR__ . '/AutoLoader.php';

$requestUrl = $_SERVER['REQUEST_URI'];

/** @var \Controllers\Controller[] $paths **/
$paths = [new Login(), new Register()];

foreach ($paths as $path) {
    if ($path::resolve($requestUrl)) {
        $path->control();
        exit();
    }
}

echo 'NOT FOUND';
exit();