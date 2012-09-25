<?php
namespace Pincrowd\Model;

/**
 * @package
 * @category
 * @subcategory
 */
/**
 * @package
 * @category
 * @subcategory
 *
 * @Document
 */
class Lane extends AbstractDomain
{
    /**
     * @var mixed
     * @Id
     */
    protected $laneId;

    /**
     * @var array[Game]
     * @ReferenceMany(targetDocument="Game", mappedBy="laneId")
     */
    protected $games = array();
}
