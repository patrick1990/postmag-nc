<?php

if (!defined('PHPUNIT_RUN')) {
    define('PHPUNIT_RUN', 1);
}

// Manually set path to Nextcloud via environment variable
if (getenv('NC_PATH')) {
    require_once getenv('NC_PATH').'/lib/base.php';
}
else {
    require_once __DIR__.'/../../../lib/base.php';
}

// Load specific PHPUnit printer
if (getenv('PHPUNIT_PRINTER')) {
    require_once getenv('PHPUNIT_PRINTER');
}
 
\OC::$loader->addValidRoot(OC::$SERVERROOT . '/tests');
\OC_App::loadApp('postmag');

OC_Hook::clear();
