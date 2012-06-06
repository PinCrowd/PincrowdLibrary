<?php
/**
 * @category   Pincrowd
 * @package    Pincrowd_Rest
 * @subpackage Model
 */
/**
 * @category   Pincrowd
 * @package    Pincrowd_Rest
 * @subpackage Model
 */
class Pincrowd_Rest_CountResult extends Pincrowd_Model_AbstractModel
{
    /**
     *
     * @var string
     */
    protected $_halRel = 'count';
    /**
     *
     * @var string
     */
    protected $_halResource = '/leadresponder/count';
    /**
     *
     * @var array
     */
    protected $_types = array('count' => 'int');
    /**
     *
     * @var array
     */
    protected $_params = array('count' => 0);
}
