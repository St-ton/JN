#!/usr/bin/env php

<?php

$deployPath = $argv[1];
$showOutput = isset($argv[2]) ?? false;

require_once "{$deployPath}/includes/globalinclude.php";

$time    = date('YmdHis');
$manager = new MigrationManager();

try {
    $migrations = $manager->migrate($time);

    if ($showOutput) {
        foreach ($migrations as $migration) {
            echo $migration;
        }
    }
} catch (Exception $e) {
    $migration = $manager->getMigrationById(array_pop(array_reverse($manager->getPendingMigrations())));
    $result    = new IOError('Migration: '.$migration->getName().' | Errorcode: '.$e->getMessage());

    echo $result->message;

    return 1;
}