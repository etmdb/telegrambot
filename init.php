<?php
/**
 * @author      EtMDB Devs (developers@etmdb.com)
 * @copyright   15/08/2017
 * @license     https://www.etmdb.com/license
 * @version     1.1.0
 */

// Composer autoload
require_once(__DIR__ . '/vendor/autoload.php');

$config = include(__DIR__ . '/conf/config.inc.php');

ini_set('error_log', __DIR__ . '/log/errors.log');
ini_set('display_errors', true);