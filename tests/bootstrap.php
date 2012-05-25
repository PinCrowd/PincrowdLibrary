<?php
// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(dirname(__FILE__) . '/../application'));

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'testing'));

$paths = array_merge(
    array(
        get_include_path(),
        realpath(APPLICATION_PATH . '/../library'),
    ),
    @include dirname(APPLICATION_PATH) .
        '/vendor/composer/autoload_namespaces.php' ? : array()
);
foreach ($paths as $key => $path) {
    if (is_array($path)) {
        foreach ($path as $p) {
            array_unshift($paths, $p);
        }
        unset($paths[$key]);
    }
}
Zend_Loader
// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, $paths));

require_once dirname(APPLICATION_PATH) . '/vendor/autoload.php';
/** Zend_Application */

// require_once 'AbstractControllerTestCase.php';
