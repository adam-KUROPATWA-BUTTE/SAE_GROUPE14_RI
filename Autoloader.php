<?php

// phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace

/**
 * Class Autoloader
 *
 * A custom autoloader implementation to handle legacy or specific project structures
 * that might not strictly follow PSR-4 standard folder mapping.
 */
class Autoloader
{
    /**
     * Mapping of namespace prefixes to base directories.
     *
     * @var array<string, string>
     */
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

    /**
     * Registers the autoloader with the SPL autoload stack.
     *
     * @return void
     */
    public static function register(): void
    {
        spl_autoload_register([self::class, 'autoload']);
    }

    /**
     * Internal autoload function.
     * Checks if the class uses one of the defined prefixes and requires the file.
     *
     * @param string $class The fully qualified class name to load.
     * @return bool True if the file was found and loaded, false otherwise.
     */
    private static function autoload(string $class): bool
    {
        foreach (self::$prefixes as $prefix => $baseDir) {
            // Check if the class uses this specific namespace prefix
            if (str_starts_with($class, $prefix)) {
                // Remove the prefix from the class name to get the relative path
                $relative = substr($class, strlen($prefix));

                // Construct the full file path (replacing namespace separators with directory separators)
                $file = $baseDir . str_replace('\\', DIRECTORY_SEPARATOR, $relative) . '.php';

                if (file_exists($file)) {
                    require_once $file;
                    return true;
                } else {
                    // Optional: Log missing files for debugging (can be noisy in production)
                    error_log("Autoloader: File not found for class $class at $file");
                }
            }
        }
        return false;
    }
}
