<?php
/**
 *
 * @category   Pincrowd
 * @package    Pincrowd_Application
 * @subpackage Resource
 */
use OAuth2\Storage\StorageInterface,
    OAuth2\Server\Server;
/**
 * Resource for initializing the OAuth2 Server and Storage
 *
 * @uses       Zend_Application_Resource_ResourceAbstract
 * @category   Pincrowd
 * @package    Pincrowd_Application
 */
class Pincrowd_Application_Resource_Oauth2
    extends Zend_Application_Resource_ResourceAbstract
{
    /**
     *
     * @var OAuth2\Server\Server
     */
    protected $_oauth2;
    /**
     *
     * @var OAuth2\Storage\StorageInterface
     */
    protected $_storage;
    /**
     *
     * @see Zend_Application_Resource_Resource::init()
     */
    public function init()
    {
        if($this->getBootstrap()->hasPluginResource('mongo')){
            $this->getBootstrap()->bootstrap('mongo');
        }
    }
    /**
     *
     * @param array $storage
     */
    public function setStorage($options)
    {
        if(!$this->_storage){
            if(isset($options['class'])){
                $reflect = new ReflectionClass($options['class']);
                /* @var  $storage Pincrowd_Auth_OAuth2_StorageMongo */
                $storage = $reflect->newInstance(array());
                if(isset($options['options']['registry_key']) &&
                    Zend_Registry::isRegistered($options['options']['registry_key'])){
                    $mongo = Zend_Registry::get($options['options']['registry_key']);
                }
                if(isset($mongo) && $mongo instanceof Mongo){
                    $storage->setMongoDB($mongo->selectDB($options['options']['database_name']));
                }
            }
        }
        $this->_storage = $storage;
    }
    /**
     *
     * @param Server $server
     */
    public function setServer(Server $server)
    {
        $this->_oauth2 = $server;
    }
    /**
     *
     * @return \OAuth2\Storage\StorageInterface
     */
    public function getStorage()
    {
        return $this->_storage;
    }
    /**
     *
     * @return \OAuth2\Server\Server
     */
    public function getServer()
    {
        return $this->_oauth2;
    }
}