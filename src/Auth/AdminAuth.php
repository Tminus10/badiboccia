<?php

final class AdminAuth
{
    public static function current(): ?array
    {
        if (empty($_SESSION['admin_id'])) {
            return null;
        }
        return Admin::find((int) $_SESSION['admin_id']);
    }

    public static function attempt(string $username, string $password): bool
    {
        $admin = Admin::findByUsername($username);
        if ($admin === null || !password_verify($password, $admin['password_hash'])) {
            return false;
        }
        $_SESSION['admin_id'] = $admin['id'];
        return true;
    }

    public static function logout(): void
    {
        unset($_SESSION['admin_id']);
    }

    public static function requireLogin(): array
    {
        $admin = self::current();
        if ($admin === null) {
            header('Location: ' . url('/admin/login'));
            exit;
        }
        return $admin;
    }
}
