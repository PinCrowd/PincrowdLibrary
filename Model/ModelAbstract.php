<?php
/**
 *
 *
 * @author Robert Allen <rallen@ifbyphone.com>
 * @package
 * @subpackage
 *
 *
 */
abstract class Pincrowd_Model_ModelAbstract
{
    /**
     *
     * @var array
     */
    protected $_params = array();
    /**
     *
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->fromArray($params);
    }
    /**
     *
     * @param string $name
     * @return boolean
     */
    public function __isset($name)
    {
        return isset($this->_params[$name]);
    }
    /**
     *
     * @param string $name
     * @param mixed $value
     * @return void
     */
    public function __set($name, $value)
    {
        if(array_key_exists($name, $this->_params)){
            $this->_params[$name] = $value;
        }
    }
    /**
     *
     * @param string $name
     * @return multitype:NULL string |boolean
     */
    public function __get($name)
    {
        if(array_key_exists($name, $this->_params)){
            return $this->_params[$name];
        }
        return false;
    }
    /**
     *
     * @return array
     */
    public function toArray()
    {
        return $this->_params;
    }
    /**
     *
     * @param array $data
     * @return void
     */
    public function fromArray(array $data)
    {
        foreach ($data as $key => $value) {
            $this->__set($key, $value);
        }
    }
}