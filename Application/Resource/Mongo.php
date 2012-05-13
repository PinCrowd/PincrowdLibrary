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
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   Pincrowd
 * @package    Pincrowd_Application
 */
class Pincrowd_Application_Resource_Mongo
    extends Zend_Application_Resource_ResourceAbstract
{
    const DEFAULT_REGISTRY_KEY = 'MONGO';
    /**
     *
     * @var Mongo
     */
    protected $_mongo;
    public function init()
    {
        $this->getMongo();
    }

    /**
     * Attach logger
     *
     * @param  Mongo $mongo
     * @return Pincrowd_Application_Resource_Mongo
     */
    public function setMongo(Mongo $mongo)
    {
        $this->_mongo = $mongo;
        return $this;
    }
    /**
     * @return Mongo
     */
    public function getMongo()
    {
        if (!$this->_mongo instanceof Mongo) {
            if(!extension_loaded('mongo')){
                throw new RuntimeException('mongo extension is not loaded');
            }
            $config = $this->getOptions();
            $registry = @$config['registry_key'] ?: self::DEFAULT_REGISTRY_KEY;
            if(!isset($config['server'])){
                $server  = "mongodb://localhost:27017";
            } else {
                $server = $config['server'];
            }
            if(!isset($config['options']) || !is_array($config['options'])){
                $options = array();
            } else {
                $options = $config['options'];
            }
            $mongo = new Mongo($server, $options);
            Zend_Registry::set($registry, $mongo);
            $this->setMongo($mongo);
        }
        return $this->_mongo;
    }
}