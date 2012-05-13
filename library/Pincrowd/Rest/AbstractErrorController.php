<?php
/**
 *
 *
 * @author Robert Allen <zircote@zircote.com>
 * @category   Pincrowd
 * @package    Pincrowd_Rest
 * @subpackage Controller
 */
/**
 *
 *
 */
class Pincrowd_Rest_AbstractErrorController extends Zend_Controller_Action
{

    /**
     *
     * @var Zend_Log
     */
    protected $_log;
    /**
     * @todo add support for config from Zend_Config
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init ()
    {
        $this->_helper->viewRenderer->setNoRender();
        /* @var $bootstrap Zend_Pincrowd_Rest_Bootstrap_Bootstrap */
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        if($bootstrap->hasResource('rest_controller')){
            $this->setOptions(
            $bootstrap->getResource('rest_controller')->getRestController()
            );
        }
    }
    public function postDispatch()
    {
        if($this->getResponse()->isException()){
            $haystack = array('true' => true, 'false' => false, '1' => true, '0' => false);
            $val = strtolower($this->getRequest()->getParam('suppress_error_codes', 0));
            if(in_array($val, $haystack)){
                $val = $haystack[$val];
            }
            if(true === $val){
                $this->getResponse()->setHttpResponseCode(200);
            }
        }
    }
}