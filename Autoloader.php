<?php

// phpcs:disable PSR1.Files.SideEffects
// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
class Autoloader
{
    private static array $prefixes = [
        'Controllers\\site\\' => __DIR__ . '/public/module/site/Controllers/',
        'Controllers\\' => __DIR__ . '/public/module/site/Controllers/',
        'Model\\' => __DIR__ . '/public/module/site/Model/',
        'Service\\' => __DIR__ . '/public/module/site/Service/',
        'View\\' => __DIR__ . '/public/module/site/View/',
        'Model\\Folder\\' => __DIR__ . '/public/module/site/Model/Folder/',
        'View\\Folder\\' => __DIR__ . '/public/module/site/View/Folder/',
        'Controllers\\FolderController\\' => __DIR__ . '/public/module/site/Controllers/FolderController/',
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
                $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

                if (file_exists($file)) {
                    require_once $file;
                    return true;
                } else {
                    echo "Fichier introuvable : $file<br>";
                    error_log("Fichier introuvable : $file");
                }
            }
        }
        return false;
    }
}

Autoloader::register();
