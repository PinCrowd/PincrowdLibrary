<?php 
/**
 * 
 * 
 * @author zircote
 * 
 * @property MongoId $_id public
 * @property array[MongoDBRef] $games public
 */
class Pincrowd_Model_Match extends Pincrowd_Model_ModelAbstract
{
    protected $_params = array(
        '_id' => null, 
        'games' => array()
    );
}