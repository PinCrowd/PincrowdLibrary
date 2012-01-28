<?php
/**
 * 
 * 
 * @author zircote
 * @property MongoId          $_id         protected
 * @property MongoDBRef       $player      protected
 * @property Gaz_Model_Frames $frames      public
 * @property integer          $total       protected
 * @property MongoDate        $dateStarted protected
 * @property MongoDate        $dateEnded   protected 
 */
class Pincrowd_Model_Game extends Pincrowd_Model_ModelAbstract
{
    /**
     * 
     * @var array
     */
    protected $_params = array('_id' => null, 'player' => null, 
    'frames' => null, 'total' => null, 'dateStarted' => null, 
    'dateEnded' => null);
    /**
     * (non-PHPdoc)
     * @see Pincrowd_Model_ModelAbstract::toArray()
     */
    public function toArray()
    {
        $data = parent::toArray();
        $data['frames'] = $this->_params['frames']->toArray();
        return $data;
    }
}