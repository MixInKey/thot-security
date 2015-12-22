<?php

require_once __DIR__.'/vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Symfony\Component\Yaml\Yaml;

$isDevMode = true;
$parameters = Yaml::parse(file_get_contents(__DIR__.'/config/database.yml'));
$db = $parameters['parameters'];

$entityManager = \Doctrine\ORM\EntityManager::create(
    ['url' => "mysql://{$db['user']}:{$db['password']}@{$db['host']}/{$db['database']}"],
    Setup::createAnnotationMetadataConfiguration(array(__DIR__.'/src'), $isDevMode, null, null, false)
);
