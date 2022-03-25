<?php

use Symfony\Component\Dotenv\Dotenv;

if (file_exists('../vendor/autoload.php')) {
    require '../vendor/autoload.php';
} else {
    require '../../vendor/autoload.php';
}

if (file_exists(dirname(__DIR__).'/config/bootstrap.php')) {
    require dirname(__DIR__).'/config/bootstrap.php';
} elseif (method_exists(Dotenv::class, 'bootEnv')) {
    (new Dotenv())->bootEnv(dirname(__DIR__).'/../.env');
}
