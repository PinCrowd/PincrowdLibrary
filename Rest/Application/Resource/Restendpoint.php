<?php
/**
 *
 *
 * @author Robert Allen <zircote@zircote.com>
 * @category Pincrowd
 * @package Pincrowd_Rest
 * @subpackage Application
 */
/**
 *
 *
 */
class Pincrowd_Rest_Application_Resource_Restendpoint extends Zend_Application_Resource_ResourceAbstract
{
    protected $_defaults;
    /**
     *
     * @var Zend_Controller_Front
     */
    protected $_front;
    /**
     * (non-PHPdoc)
     * @see Zend_Application_Resource_Resource::init()
     */
    public function init()
    {
        $this->_front = Zend_Controller_Front::getInstance();
        $this->registerPlugins();
    }
    /**
     *
     */
    protected function registerPlugins()
    {
        $options = $this->getOptions();

        foreach ($options['plugin'] as $plugin) {
            if($plugin['enabled'] && isset($plugin['adapter'])){
                $_options = array_merge(
                    $options['defaults'], @$plugin['options'] ?: array()
                );
                $pluginNamespace = 'Pincrowd_Rest_Plugin';
                if (isset($config['pluginNamespace'])) {
                    if ($plugin['pluginNamespace'] != '') {
                        $pluginNamespace = $plugin['pluginNamespace'];
                    }
                    unset($config['pluginNamespace']);
                }
                $pluginName = $pluginNamespace . '_' . ucfirst($plugin['adapter']);
                $reflected = new ReflectionClass($pluginName);
                $this->_front->registerPlugin(
                    $reflected->newInstance()->setOptions($_options),
                    @$plugin['stackindex']?:null
                );
            }
        }
    }
}