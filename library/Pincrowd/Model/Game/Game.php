<?php
/**
 * @category   Pincrowd
 * @package    Library
 * @subpackage Model
 */
/**
 *
 * @category   Pincrowd
 * @package    Library
 * @subpackage Model
 *
 * @property MongoId    $_id  public
 * @property MongoDBRef $player      public
 * @property array      $frames      public
 * @property integer    $totalpublic
 * @property MongoDate  $dateStarted public
 * @property MongoDate  $dateEnded   public
 */
class Pincrowd_Model_Game_Game extends Pincrowd_Model_AbstractModel
{
    protected $_lastUpdatedField = 'dateEnded';
    protected $_halRel = 'game';
    protected $_halResource = '/games';
    protected $_identity = '_id';
    /**
     *
     * @var array
     */
    protected $_params = array(
        '_id' => null,
        'player' => null,
        'throws' => array(),
        'frames' => array(),
        'total' => null,
        'dateStarted' => null,
        'dateEnded' => null
    );

    /**
     *
     * @var array
     */
    protected $_types = array(
    );
}