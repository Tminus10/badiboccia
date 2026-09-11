<?php
// One-off CLI script to create the first admin account (or an additional one from the server).
// Usage: php scripts/create-admin.php <username> <password> <display name>

require __DIR__ . '/../src/autoload.php';

if ($argc < 4) {
    fwrite(STDERR, "Usage: php scripts/create-admin.php <username> <password> <display name>\n");
    exit(1);
}

[, $username, $password, ] = $argv;
$displayName = implode(' ', array_slice($argv, 3));

if (strlen($password) < 6) {
    fwrite(STDERR, "Password must be at least 6 characters.\n");
    exit(1);
}

if (Admin::findByUsername($username) !== null) {
    fwrite(STDERR, "An admin with username \"$username\" already exists.\n");
    exit(1);
}

$id = Admin::create($username, $password, $displayName);
echo "Created admin #$id ($displayName / $username).\n";
