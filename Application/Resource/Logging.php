<?php
/**
 *
 * @category   Pincrowd
 * @package    Pincrowd_Application
 * @subpackage Resource
 */

/**
 * @see Zend_Application_Resource_ResourceAbstract
 */
require_once 'Zend/Application/Resource/ResourceAbstract.php';


/**
 * Resource for initializing the locale
 *
 * <code>
 * ;; See for options: http://us.php.net/manual/en/mongo.construct.php
 * resources.mongo.server = "mongodb://localhost:27017"
 * resources.mongo.options.replicaSet = "rs01"
 * resources.mongo.options.username = "user1"
 * resources.mongo.options.password = "sekret"
 * resources.mongo.options.connect = true
 * resources.mongo.options.database = "auth"
 * resources.mongo.options.timeout = 10
 * </code>
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   Pincrowd
 * @package    Pincrowd_Application
 */
class Pincrowd_Application_Resource_Logging
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     * @var Zend_Log
     */
    protected $_log;

    /**
     * Defined by Zend_Application_Resource_Resource
     *
     * @return Zend_Log
     */
    public function init()
    {
        if($this->getBootstrap()->hasPluginResource('mongo')){
            $this->getBootstrap()->bootstrap('mongo');
        }
        return $this->getLog();
    }

    /**
     * Attach logger
     *
     * @param  Zend_Log $log
     * @return Pincrowd_Application_Resource_Logging
     */
    public function setLog(Zend_Log $log)
    {
        $this->_log = $log;
        return $this;
    }
    /**
     * @return Zend_Log
     */
    public function getLog()
    {
        if (null === $this->_log) {
            try{
                $options = $this->getOptions();
                $log = Zend_Log::factory($options);
            } catch (Exception $e){
                unset($options['mongo']);
                $log = Zend_Log::factory($options);
            }
            $this->setLog($log);
        }
        return $this->_log;
    }
}
