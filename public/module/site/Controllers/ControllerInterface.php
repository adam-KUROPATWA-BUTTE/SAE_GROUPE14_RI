<?php

namespace Controllers;

/**
 * Interface ControllerInterface
 *
 * Contract that all controllers must adhere to.
 * Ensures a consistent structure for handling requests and routing within the application.
 */
interface ControllerInterface
{
    /**
     * Main method that executes the controller logic.
     *
     * This method is responsible for processing the request, interacting with models,
     * and rendering the appropriate view.
     *
     * @return void
     */
    public function control(): void;

    /**
     * Determines if the controller supports the requested page and HTTP method.
     *
     * This static method acts as a router guard, checking if the specific controller
     * is capable of handling the incoming request based on the URL path and HTTP verb.
     *
     * @param string $path   The requested page/route (e.g., 'login', 'dashboard').
     * @param string $method The HTTP method used (e.g., 'GET', 'POST').
     * @return bool True if the controller supports the request, false otherwise.
     */
    public static function support(string $path, string $method): bool;
}