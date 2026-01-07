<?php

namespace Controllers\FolderController;

function header(string $string, bool $replace = true, ?int $http_response_code = null): void
{
    $GLOBALS['__captured_headers'][] = $string;
}
