<?php
/**
 *
 *
 * @author Robert Allen <zircote@zircote.com>
 * @category   Pincrowd
 * @package    Pincrowd_Rest
 * @subpackage Controller
 */
/**
 *
 *
 */
interface Pincrowd_Rest_LastUpdatedInterface
{
    /**
     * @return string
     */
    public function getLastUpdated();
    /**
     * @return string
     */
    public function getLastUpdatedField();
}