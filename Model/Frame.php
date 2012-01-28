<?php
/**
 * 
 * 
 * @author zircote
 * @property integer         $score  public
 * @property Pincrowd_Model_Throw $throw1 public
 * @property Pincrowd_Model_Throw $throw2 public
 * @property Pincrowd_Model_Throw $throw3 public
 * @property integer         $total  public
 */
class Pincrowd_Model_Frame extends Pincrowd_Model_ModelAbstract
{
    /**
     * 
     * 
     * @var array
     */
    protected $_params = array('score' => null, 'throw1' => null, 
    'throw2' => null, 'throw3' => null, 'total' => null);
    /**
     * (non-PHPdoc)
     * @see Pincrowd_Model_ModelAbstract::toArray()
     */
    public function toArray()
    {
        $data = parent::toArray();
        if($data['throw1'] instanceof Pincrowd_Model_Throw){
            $data['throw1']->toArray();
        }
        if($data['throw2'] instanceof Pincrowd_Model_Throw){
            $data['throw2']->toArray();
        } else {
            unset($data['throw2']);
        }
        if($data['throw3'] instanceof Pincrowd_Model_Throw){
            $data['throw3']->toArray();
        } else {
            unset($data['throw3']);
        }
        return $data;
    }
}