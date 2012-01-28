<?php
/**
 * 
 * 
 * @author zircote
 * @property Pincrowd_Model_Frame $_1  protected
 * @property Pincrowd_Model_Frame $_2  protected
 * @property Pincrowd_Model_Frame $_3  protected
 * @property Pincrowd_Model_Frame $_4  protected
 * @property Pincrowd_Model_Frame $_5  protected
 * @property Pincrowd_Model_Frame $_6  protected
 * @property Pincrowd_Model_Frame $_7  protected
 * @property Pincrowd_Model_Frame $_8  protected
 * @property Pincrowd_Model_Frame $_9  protected
 * @property Pincrowd_Model_Frame $_10 protected
 */
class Pincrowd_Model_Frames extends Pincrowd_Model_ModelAbstract
{
    /**
     * 
     * 
     * @var array
     */
    protected $_params = array('_1' => null, '_2' => null, '_3' => null, 
    '_4' => null, '_5' => null, '_6' => null, '_7' => null, '_8' => null, 
    '_9' => null, '_10' => null);
    /**
     * (non-PHPdoc)
     * @see Pincrowd_Model_ModelAbstract::toArray()
     */
    public function toArray()
    {
        /* @var $value Pincrowd_Model_Frame|null */
        foreach ($this->_params as $key => $value) {
            if($value instanceof Pincrowd_Model_Frame){
                $data[$key] = $value->toArray();
            }
        }
        return $data;
    }
}