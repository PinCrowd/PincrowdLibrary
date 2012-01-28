<?php
/**
 * 
 * 
 * @author zircote
 *
 */
class Pincrowd_Auth_Credential
{
    /**
     * 
     * 
     * @var string
     */
    public static $CRYPT = '$2a$';
    /**
     * 
     * 
     * @var string
     */
    public static $LOAD = '15$';
    /**
     * @return string
     */
    public static function getSalt()
    {
        return substr(str_replace(
            '+', '.', base64_encode(sha1(microtime(true), true))
        ), 0, 22);
    }
    /**
     * 
     * 
     * @param string $password
     * @return string
     */
    public static function crypt($password)
    {
        return crypt($password, self::$CRYPT . self::$LOAD . self::getSalt());
    }
    /**
     * 
     * 
     * @param string $password
     * @param string $credential
     * @return boolean
     */
    public static function isValid($password, $credential)
    {
        return ($credential == crypt($password, $credential));
    }
}
