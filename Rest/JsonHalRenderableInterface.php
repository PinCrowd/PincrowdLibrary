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
interface Pincrowd_Rest_JsonHalRenderableInterface
{
    /**
     * @return string
     */
    public function toJsonHal($baseUri = null);
}