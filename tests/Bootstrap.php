<?php
date_default_timezone_set('UTC');

include 'init_autoloader.php';
define('RINDOW_TEST_CACHE',     __DIR__.'/cache');

if(!class_exists('PHPUnit\Framework\TestCase')) {
    include __DIR__.'/travis/patch55.php';
}
