<?php
/**
 *
 *
 *
 * @category   Organic
 * @package    Organic
 * @subpackage UnitTests
 */
defined('APPLICATION_PATH') ?: define('Pincrowd_PHPUNIT_TEST_ACTIVE',  true);

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(dirname(__DIR__) . '/../'),
    __DIR__,
    get_include_path(),
)));

require_once 'Zend/Loader/Autoloader.php';
Zend_Loader_Autoloader::getInstance()
    ->registerNamespace('Pincrowd')
    ->registerNamespace('Pincrowd')
    ->registerNamespace('OAuth2');

// require_once 'AbstractControllerTestCase.php';