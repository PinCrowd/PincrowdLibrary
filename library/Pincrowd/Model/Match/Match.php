<?php
/**
 *
 *
 *
 * @category   Pincrowd
 * @package    Library
 * @subpackage Model
 */
/**
 *
 *
 * @category   Pincrowd
 * @package    Library
 * @subpackage Model
 *
 * @property MongoId $_id public
 * @property MongoDbRef $lane public;
 * @property array[MongoDBRef] $games public
 */
class Pincrowd_Model_Match_Match extends Pincrowd_Model_ModelAbstract
{
    protected $_params = array(
        '_id' => null,
        'lane' => null,
        'games' => array()
    );
    public function toArray()
    {
        $data = parent::toArray();
        if(null === $data['_id']){
            unset($data['_id']);
        }
        return $data;
    }
}