<?php

/**
 * @author Patrick Greyson
 *
 * Postmag - Postfix mail alias generator for Nextcloud
 * Copyright (C) 2021
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

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
