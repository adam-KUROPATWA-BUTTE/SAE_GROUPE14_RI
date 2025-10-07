<?php

class Autoloader
{
    private static array $prefixes = [
        'Controllers\\Blog\\' => 'module/blog/Controllers/',
        'Controllers\\' => 'module/blog/Controllers/',
        'Model\\' => 'module/blog/Model/',
        'Config\\' => 'module/blog/Config/',
        'View\\' => 'module/blog/View/',
    ];

    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    private static function autoload(string $class): bool
    {
        foreach (self::$prefixes as $prefix => $baseDir) {
            if (str_starts_with($class, $prefix)) {
                $relative = substr($class, strlen($prefix));
                $file = __DIR__ . DIRECTORY_SEPARATOR . $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

                if (file_exists($file)) {
                    require_once $file;
                    return true;
                } else {
                    error_log("Fichier introuvable : $file");
                }
            }
        }
        return false;
    }
}

Autoloader::register();
