<?php
namespace Pincrowd\Model;

/**
 * @category   Pincrowd
 * @package    Library
 * @subpackage Model
 */
/**
 * @category   Pincrowd
 * @package    Library
 * @subpackage Model
 *
 * @Document
 */
class Match
{
    /**
     * @var string
     * @Id
     */
    protected $id;

    /**
     * @var Lane
     * @ReferenceOne(targetDocument="Lane")
     */
    protected $lane;

    /**
     * @var array[Game]
     *
     * @ReferenceMany(targetDocument="Game")
     */
    protected $games = array();

}