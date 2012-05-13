<?php
/**
 *
 *
 * @category   Pincrowd
 * @package    Pincrowd_Tool
 * @subpackage Provider
 */
/**
 * @see Zend_Tool_Project_Provider_Abstract
 */
require_once 'Zend/Tool/Project/Provider/Abstract.php';
/**
 * @see Zend_Tool_Project_Provider_Exception
 */
require_once 'Zend/Tool/Project/Provider/Exception.php';
/**
 * Abstract class for Project providers that are log capable, config aware,
 * environment aware.
 *
 *
 *
 */
abstract class Pincrowd_Tool_Project_Provider_AbstractProvider extends Zend_Tool_Project_Provider_Abstract
{
    /**
     *
     * @var Zend_Log
     */
    protected $_log;
    /**
     *
     * @var array
     */
    protected $_config;
    /**
     *
     * @param Zend_Log $log
     * @return MlrResponderProvider
     */
    protected function _setLog(Zend_Log $log)
    {
        $this->_log = $log;
        return $this;
    }
    /**
     *
     * @return Zend_Log
     */
    protected function _getLog()
    {
        if(!$this->_log instanceof Zend_Log){
            $log = new Zend_Log();
            $log->addWriter(new Zend_Log_Writer_Null());
            $this->_setLog($log);
        $this->_getLog()->log(
            sprintf(
                'Request [%s]::Method [%s]',spl_object_hash($this), __METHOD__
            ),Zend_Log::DEBUG
        );
        }
        return $this->_log;
    }

    /**
     * Loads the config into the self::_config property.
     * @return MlrResponderProvider
     * @throws Zend_Tool_Project_Exception
     */
    protected function _loadConfig()
    {
        $profile = $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION);
        $appConfigFileResource = $profile->search('applicationConfigFile');
        if ($appConfigFileResource == false) {
            $e = new Zend_Tool_Project_Exception(
            'A project with an application config file is required to use '.
            'this provider.'
            );
            $this->_getLog()->log($e, Zend_Log::ERR);
            throw $e;
        }
        $appConfigFilePath = $appConfigFileResource->getPath();
        $this->_setConfig(
            new Zend_Config_Ini($appConfigFilePath, APPLICATION_ENV)
        );
        $this->_getLog()->log(
        sprintf(
        'Request [%s]::Method [%s]',spl_object_hash($this), __METHOD__
        ),Zend_Log::DEBUG
        );
        return $this;
    }
    /**
     *
     * @param array|Zend_Config $config
     * @return MlrResponderProvider
     */
    protected function _setConfig($config)
    {
        if($config instanceof Zend_Config){
            $config = $config->toArray();
        }
        $this->_config = $config;
        return $this;
    }
}