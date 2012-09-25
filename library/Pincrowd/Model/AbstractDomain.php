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
 */
class AbstractDomain implements \ArrayAccess
{
    public function offsetExists($offset)
    {
        return isset($this->$offset);
    }

    public function offsetSet($offset, $value)
    {
        $this->$offset = $value;
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetUnset($offset)
    {
        $this->$offset = null;
    }
}
