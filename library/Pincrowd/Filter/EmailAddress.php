<?php
/**
 *
 *
 * @author Robert Allen <rallen@Pincrowd.com>
 * @category Pincrowd
 * @package Pincrowd_Filter
 */
/**
 *
 */
require_once 'Zend/Filter/Interface.php';
class Pincrowd_Filter_EmailAddress implements Zend_Filter_Interface
{
    const FILTER = '/([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)/';
    /**
     * (non-PHPdoc)
     * @see Zend_Filter_Interface::filter()
     *
     * @assert (' email@domain.com ') == 'email@domain.com'
     * @assert ('this is a test email@domain.com and no') == 'email@domain.com'
     * @assert ('"Test User<email@domain.com>"') == 'email@domain.com'
     */
    public function filter($address)
    {
        preg_match(self::FILTER, $address, $matches);
        return @$matches[0] ?: null;
    }
}