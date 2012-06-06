<?php
/**
 *
 *
 * @category   Pincrowd
 * @package    Library
 * @subpackage Model
 */
/**
 *
 * @category   Pincrowd
 * @package    Library
 * @subpackage Model
 */
abstract class Pincrowd_Model_AbstractModelCollection extends ArrayObject
implements Pincrowd_Rest_LastUpdatedInterface,
Pincrowd_Rest_XmlRenderableInterface,
Pincrowd_Rest_XmlHalRenderableInterface,
Pincrowd_Rest_JsonRenderableInterface,
Pincrowd_Rest_JsonHalRenderableInterface,
Pincrowd_Rest_IsLoadableInterface
{
    /**
     * Is the model loaded?
     *
     * @var bool
     */
    protected $_isLoaded = false;
    protected $_itemKey = 'item';
    protected $_lastUpdatedField;
    /**
     *
     * @var string
     */
    protected $_halResource;
    /**
     *
     * @var Pincrowd_Hal_Resource
     */
    protected $_hal;
    /**
     *
     * @var array
     */
    protected $_attributes = array(
        'fields' => null,
        'paging' => null,
        'search' => null,
        'sort' => null,
        'auth' => null
    );
    protected $_service;
    /**
     *
     * @param string $attr
     * @param mixed $value
     * @return Pincrowd_Model_AbstractModelCollection
     */
    public function setAttribute($attr, $value)
    {
        $this->_attributes[$attr] = $value;
        return $this;
    }
    /**
     *
     * @param array $attr
     * @return Pincrowd_Model_AbstractModelCollection
     */
    public function setAttributes(array $attr)
    {
        $this->_attributes = $attr;
        return $this;
    }
    /**
     *
     * @param string $attr
     * @return mixed
     */
    public function getAttribute($attr)
    {
        return @$this->_attributes[$attr] ?: null;
    }
    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }
    /**
     * Allows customication of the itemKey at runtime.
     * @param string $itemKey
     * @throws Zend_Validate_Exception
     * @return AbstractModelCollection
     */
    public function setItemKey($itemKey)
    {
        $validation = new Zend_Validate_Alpha();
        if(!$validation->isValid($itemKey)){
            throw new Zend_Validate_Exception(
                sprintf('string [%s] must be alpha [a-zA-Z]', $itemKey)
            );
        }
        $this->_itemKey = $itemKey;
        return $this;
    }
    /**
     * Provides a mechanism to discover the current value of the itemKey
     * @return string
     */
    public function getItemKey()
    {
        return $this->_itemKey;
    }
    /**
     * This will provide assurances that the appended item is of the corrrect
     * model type.
     * Example:
     * <code>
     * public function append(Pincrowd_Model_SomeModel $model)
     * {
     *     parent::append($model);
     * }
     * </code>
     * @param Pincrowd_Model_AbstractModel $item
     */
    public function append(Pincrowd_Model_AbstractModel $item){
        $this->setIsLoaded(true);
        parent::append($item);
    }
    /**
     * @throws RuntimeException
     * @return array
     */
    public function toArray($recurse = true)
    {
        if(!$recurse){
            return $this->getArrayCopy();
        }
        $result = array();
        foreach ($this->getArrayCopy() as $child) {
            if($child instanceof Pincrowd_Model_AbstractModel){
                    $result[] = $child->toArray();
            } else {
                throw new RuntimeException(
                    'child item is not of type [Pincrowd_Model_AbstractModel]'
                );
            }
        }
        return $result;
    }
    /**
     * (non-PHPdoc)
     * @see Pincrowd_Rest_LastUpdatedInterface::getLastUpdated()
     */
    public function getLastUpdated()
    {
        if($this->getLastUpdatedField()){
            $iso8601 = gmdate(
                DATE_COOKIE,
                strtotime($this->__get($this->getLastUpdatedField()))
            );
            return $iso8601;
        }
        return false;
    }
    /**
     * @return string|null
     */
    public function getLastUpdatedField()
    {
        return $this->_lastUpdatedField;
    }
    /**
     *
     * @param string $lastUpdatedField
     * @return Pincrowd_Model_Mapper_AbstractDbMapper
     */
    public function setLastUpdatedField($lastUpdatedField)
    {
        $this->_lastUpdatedField = $lastUpdatedField;
        return $this;
    }
    /**
     *
     * @param string $halResource
     */
    public function setHalResource($halResource)
    {
        $this->_halResource = $halResource;
    }
    /**
     *
     * @return string
     */
    public function getResourceUri()
    {
        return rtrim($this->_halResource, '/');
    }
    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
    /**
     * @param string $baseUri
     * @return Pincrowd_Hal_Resource
     */
    public function toHal($baseUri = null)
    {
        if(!$this->_hal){
            $this->_hal = new Pincrowd_Hal_Resource(
                $baseUri . $this->getResourceUri()
            );
            /* @var Pincrowd_Model_AbstractModel $item */
            foreach ($this->toArray(false) as $k => $item) {
                $this->_hal->setEmbedded(
                    $item->getHalRel(), $item->toHal($baseUri)
                );
            }
        }
        return $this->_hal;
    }
    /**
     * (non-PHPdoc)
     * @see Pincrowd_Rest_XmlHalRenderableInterface::toXmlHal()
     */
    public function toXmlHal($baseUri = null)
    {
        return (string) $this->toHal($baseUri)->getXML()->asXml();
    }
    /**
     * (non-PHPdoc)
     * @see Pincrowd_Rest_JsonHalRenderableInterface::toJsonHal()
     */
    public function toJsonHal($baseUri = null)
    {
        return (string) $this->toHal($baseUri)->__toJson();
    }
    /**
     * (non-PHPdoc)
     * @see Pincrowd_Rest_XmlRenderableInterface::toXml()
     */
    public function toXml()
    {
        $xml = new SimpleXMLElement("<{$this->_itemKey}s/>");
        /* @var $item Pincrowd_Model_AbstractModel */
        foreach ($this->getIterator() as $k => $item){
            $xml->addChild($this->_itemKey);
            foreach ($item->toArray() as $key => $value) {
                $xml->{$this->_itemKey}[$k]->addChild($key,$value);
            }
        }
        return (string) $xml->asXML();
    }
    /**
     * (non-PHPdoc)
     * @see Pincrowd_Rest_JsonRenderableInterface::toJson()
     */
    public function toJson()
    {
        return Zend_Json::encode($this->toArray());
    }
    /**
     *
     * @return boolean
     */
    public function isLoaded()
    {
        return $this->_isLoaded;
    }
    /**
     *
     * @param boolean $loaded
     */
    public function setIsLoaded($loaded)
    {
        $this->_isLoaded = (boolean) $loaded;
    }
}





