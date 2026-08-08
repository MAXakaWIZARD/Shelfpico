<?php

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Process\Process;

// Load test environment explicitly
$_SERVER['APP_ENV'] = $_ENV['APP_ENV'] = 'test';

require_once(__DIR__ . '/../../vendor/autoload.php');

(new Dotenv())->bootEnv(__DIR__.'/../../.env');

$path = realpath(__DIR__ . '/../../');
if ($path === false) {
    throw new \Exception('Failed to determine project path');
}

echo 'Clearing cache...' . PHP_EOL; 
$process = Process::fromShellCommandline('"' . $_SERVER['BIN_PHP'] . '" bin/console cache:clear --env=test', $path);
$process->run();
if (!$process->isSuccessful()) {
    throw new \Exception('Failed to clear cache for test env: ' . $process->getOutput() . $process->getErrorOutput());
}

echo 'Clearing the DB...' . PHP_EOL;
$db = new mysqli(
    $_SERVER['DATABASE_HOST'],
    $_SERVER['DATABASE_USER'],
    $_SERVER['DATABASE_PASSWORD'],
    $_SERVER['DATABASE_NAME'],
    $_SERVER['DATABASE_PORT']
);
$queries = [
    "DELETE FROM products WHERE title LIKE '%Test product%'",
    "DELETE FROM tags WHERE title LIKE '%Test tag%'",
];
foreach ($queries as $query) {
    $result = $db->query($query);
    if ($result !== true) {
        throw new \Exception('Failed to execute bootstrap query: ' . $query);
    }
}
