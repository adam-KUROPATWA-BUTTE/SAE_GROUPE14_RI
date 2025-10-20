<?php

session_start();

function requireRole(string $role) {
    if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== $role) {
        header('Location: index.php?page=login');
        exit;
    }
}
