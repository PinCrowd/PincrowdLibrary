<?php
/**
 * filecomment
 * package_declaration
 */
class Pincrowd_Loader_AutoLoader_Resource implements Zend_Loader_Autoloader_Interface
{
    public function autoload($class)
    {
        $classPath = str_replace(array('\\','_'), '/', $class) . '.php';
        if (Zend_Loader::isReadable($classPath)) {
            include_once $classPath;
        }
        return false;
    }
}