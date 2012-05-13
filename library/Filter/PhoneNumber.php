<?php
/**
 * @todo This is very specific to this project and must be refactored to a new name
 * and tasked for what it is meant to be. Because phoneNumbers are semantically
 * very complex in composition and this is only filtering for the first 10 digits
 * in addition to the lack of character enforcements it should possess
 *
 * @author Robert Allen <rallen@Pincrowd.com>
 * @category Pincrowd
 * @package Pincrowd_Filter
 */
/**
 *
 */
require_once 'Zend/Filter/Interface.php';
class Pincrowd_Filter_PhoneNumber implements Zend_Filter_Interface
{
    /**
     * (non-PHPdoc)
     * @see Zend_Filter_Interface::filter()
     *
     * @assert ('17735551212') == '7735551212'
     */
    public function filter($phoneNumber)
    {
        return substr($phoneNumber, -10,10);
    }
}