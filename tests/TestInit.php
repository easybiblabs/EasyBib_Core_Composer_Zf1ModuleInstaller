<?php
$composerAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!file_exists($composerAutoload)) {
    echo "Please run `php composer.phar install` first!";
    exit(1);
}
require_once $composerAutoload;

require_once 'PHPUnit/Autoload.php';