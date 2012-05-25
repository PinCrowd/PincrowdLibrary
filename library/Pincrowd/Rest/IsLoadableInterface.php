<?php
/**
 *
 *
 * @category   Pincrowd
 * @package    Pincrowd_Rest
 * @subpackage Controller
 */

/**
 *
 * @category   Pincrowd
 * @package    Pincrowd_Rest
 * @subpackage Controller
 */
interface Pincrowd_Rest_IsLoadableInterface
{

    /**
     * @return boolean
     */
    public function isLoaded();
    /**
     *
     * @param boolean $loaded
     */
    public function setIsLoaded($loaded);
}