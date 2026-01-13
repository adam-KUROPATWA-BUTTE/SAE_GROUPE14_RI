<?php

namespace Controllers\site\FolderController;

function header(string $string, bool $replace = true, ?int $http_response_code = null): void
{
    throw new \RuntimeException('header called: ' . $string);
}
