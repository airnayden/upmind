<?php

/*
 * Magic here...
 */

// Bootstrap
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

require_once('vendor/autoload.php');

$isDevMode = true;
$dbConfig = Setup::createAnnotationMetadataConfiguration(array(__DIR__."/model"), $isDevMode);
$dbConn = array(
    'driver' => 'pdo_sqlite',
    'path' => __DIR__ . '/db.sqlite',
);
$entityManager = EntityManager::create($dbConn, $dbConfig);

return \Doctrine\ORM\Tools\Console\ConsoleRunner::createHelperSet($entityManager);