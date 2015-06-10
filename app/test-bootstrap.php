<?php

if (isset($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'])) {
    passthru(sprintf(
        'php "%s/console" cache:clear --env=%s --no-warmup',
        __DIR__,
        $_ENV['BOOTSTRAP_CLEAR_CACHE_ENV']
    ));
    passthru(sprintf(
        'php "%s/console" doctrine:schema:create --env=test',
        __DIR__
    ));
    passthru(sprintf(
        'php "%s/console" doctrine:fixtures:load -n --env=test',
        __DIR__
    ));
}

/* force loading of class */
if (class_exists('PHPUnit_Util_ErrorHandler', true)) {
}

require_once(__DIR__ . '/bootstrap.php.cache');
require_once(__DIR__ . '/../vendor/symfony/symfony/src/Symfony/Bridge/PhpUnit/bootstrap.php');
?>
