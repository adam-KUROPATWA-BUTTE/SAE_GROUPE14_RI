<?php

namespace Controllers\FolderController;

function header(string $string, bool $replace = true, ?int $http_response_code = null): void
{
    // Throw to short-circuit before the real exit() in controller
    throw new \RuntimeException('header called: ' . $string);
}
