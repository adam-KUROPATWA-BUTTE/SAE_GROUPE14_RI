<?php

namespace Controllers\site;

function header(string $string, bool $replace = true, ?int $http_response_code = null): never
{
    throw new \RuntimeException('header called: ' . $string);
}
