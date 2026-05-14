<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('ensure:database', function () {
    $connection = config('database.default');
    $cfg = config("database.connections.$connection");

    if (! is_array($cfg) || ($cfg['driver'] ?? null) !== 'mysql') {
        $this->error('Default DB connection is not mysql.');
        return 1;
    }

    $database = (string) ($cfg['database'] ?? '');
    $host = (string) ($cfg['host'] ?? '127.0.0.1');
    $port = (int) ($cfg['port'] ?? 3306);
    $username = (string) ($cfg['username'] ?? '');
    $password = (string) ($cfg['password'] ?? '');
    $charset = (string) ($cfg['charset'] ?? 'utf8mb4');
    $collation = (string) ($cfg['collation'] ?? 'utf8mb4_unicode_ci');

    if ($database === '') {
        $this->error('DB_DATABASE is empty.');
        return 1;
    }

    $escapedDb = str_replace('`', '``', $database);

    try {
        $pdo = new PDO(
            "mysql:host={$host};port={$port};charset={$charset}",
            $username,
            $password,
            [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]
        );

        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$escapedDb}` CHARACTER SET {$charset} COLLATE {$collation}");
    } catch (Throwable $e) {
        $this->error('Failed to create database: '.$e->getMessage());
        return 1;
    }

    $this->info("Database ensured: {$database}");
    return 0;
})->purpose('Ensure MySQL database exists based on current DB config');
