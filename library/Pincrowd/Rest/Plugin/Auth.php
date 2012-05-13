<?php
/**
 *
 *
 * @author Robert Allen <rallen@Pincrowd.com>
 * @category   Pincrowd
 * @package    Pincrowd_Rest
 * @subpackage Plugin
 *
 *
 */
class Pincrowd_Rest_Plugin_Auth extends Zend_Controller_Plugin_Abstract
{
    /**
     * (non-PHPdoc)
     * @see Pincrowd_Rest_Plugin_RestAbstract::setOptions()
     */
    public function setOptions($options)
    {
        $this->_defaults = $options;
        return $this;
    }
    public function preDispatch($request)
    {
    }
}