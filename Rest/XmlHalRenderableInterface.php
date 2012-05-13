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
interface Pincrowd_Rest_XmlHalRenderableInterface
{
    /**
     * @return string
     */
    public function toXmlHal($baseUri = null);
}