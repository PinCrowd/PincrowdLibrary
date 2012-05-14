<?php
/**
 *
 *
 * @author Robert Allen <rallen@Pincrowd.com>
 * @category Pincrowd
 * @package Pincrowd_Model
 */
/**
 *
 */
abstract class Pincrowd_Model_AbstractModel
implements Pincrowd_Rest_XmlRenderableInterface,
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
    /**
     * This defines the XML entity element encompassing the Model Item
     * @var string
     */
    protected $_itemKey = 'item';
    /**
     * The composition container defining the items that make up the model
     * @var array
     */
    protected $_params = array();
    /**
     * The property that identifies this model item
     * @var string
     */
    protected $_identity;
    /**
     * The field (if exists) that represents the last_updated date
     * @var string
     */
    protected $_lastUpdatedField;
    /**
     *
     * @var Pincrowd_Hal_Resource
     */
    protected $_hal;
    /**
     * The relationship identifier for the object
     * @var string
     */
    protected $_halRel;
    /**
     * The rest resource URI for the item
     * @var string
     */
    protected $_halResource;
    /**
     * Container for decriptive attributes and rules defining the composition of
     * the model.
     *
     * @var array
     */
    protected $_attributes = array(
        'fields' => null,
        'auth' => null
    );
    /**
     * This is the type dictionary for the properties of this model, definition
     * of a properties type is optional.
     * Example:
     * <code>
     * protected $_types = array(
     *     'a_boolean_prop' => 'bool',
     *     'an_integer_prop' => 'int'
     * );
     * </code>
     * @var array
     */
    protected $_types = array();
    /**
     *
     * @param string $attr
     * @param mixed $value
     * @return Pincrowd_Model_AbstractModel
     */
    public function setAttribute($attr, $value)
    {
        $this->_attributes[$attr] = $value;
        return $this;
    }
    /**
     *
     * @param array $attr
     * @return Pincrowd_Model_AbstractModel
     */
    public function setAttributes(array $attr)
    {
        foreach ($attr as $key => $value) {
            if(key_exists($key, $this->_attributes)){
                $this->_attributes[$key] = $value;
            }
        }
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
     *
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->fromArray($params);
    }
    /**
     * Returns all valid keys for the model in question.
     * @return array
     */
    public function getCols()
    {
        return array_keys($this->_params);
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
            $this->setIsLoaded(true);
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
            return $this->_castType($name, $this->_params[$name]);
        }
        return false;
    }
    /**
     *
     * @return array
     */
    public function toArray()
    {
        $result = array();
        if($this->_attributes['fields']){
            foreach ($this->_attributes['fields'] as $field) {
                $result[$field] = $this->__get($field);
            }
        } else {
            $result = $this->_params;
        }
        /* Cast Type */
        foreach ($result as $name => $value) {
            $value = $this->__get($name);
            $result[$name] = $this->_castType($name, $value);
        }
        return $result;
    }
    /**
     *
     * @param string $name
     * @param mixed $value
     */
    protected function _castType($name, $value)
    {
        if(isset($this->_types[$name])){
            settype($value,$this->_types[$name]);
        }
        return $value;
    }
    /**
     * A conveinance method to map an array into the model object.
     *
     * @param array $data
     * @return void
     */
    public function fromArray(array $data)
    {
        foreach ($data as $name => $value) {
            $this->__set($name, $this->_castType($name, $value));
        }
    }
    /**
     * Returns a Cookie formated date of the last_updated date field for the model
     *
     * @return string|boolean
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
     * Return the defined property name that defines the last_updated date.
     *
     * @return string|null
     */
    public function getLastUpdatedField()
    {
        return $this->_lastUpdatedField;
    }
    /**
     * Sets the name of the property that defines the last_updated field
     *
     * @param string $lastUpdatedField
     * @return Pincrowd_Model_AbstractDbMapper
     */
    public function setLastUpdatedField($lastUpdatedField)
    {
        $this->_lastUpdatedField = $lastUpdatedField;
        return $this;
    }
    /**
     * Provides a mechanism to discover the current value of the itemKey
     *
     * @return string
     */
    public function getItemKey()
    {
        return $this->_itemKey;
    }
    /**
     * Returns the HalResource Relation name
     * @return string
     */
    public function getHalRel()
    {
        return $this->_halRel;
    }
    /**
     * Sets the HAL Relation value for the model
     * @param string $halRel
     * @return Pincrowd_Model_AbstractModel
     */
    public function setHalRel($halRel)
    {
        $this->_halRel = $halRel;
        return $this;
    }
    /**
     * Sets the Rest resource URI for this model.
     * @param string $halResource
     */
    public function setHalResource($halResource)
    {
        $this->_halResource = $halResource;
    }
    /**
     * Returns the Rest URI for the model
     *
     * @return string
     */
    public function getResourceUri()
    {
        return rtrim($this->_halResource . '/' . $this->__get($this->_identity), '/');
    }
    /**
     * Fully Qualified:
     * <code>
     *  $model->toHal('http://api.zircote.com/v1');
     * </code>
     * Relative:
     * <code>
     *  $model->toHal('/v1');
     * </code>
     * @param string $baseUri The base URI of the Rest Resource location for the
     * model, this may be relative or fully qualifed with hostname.
     * @return Pincrowd_Hal_Resource
     */
    public function toHal($baseUri = null)
    {
        if(!$this->_hal){
            $this->_hal = new Pincrowd_Hal_Resource(
                $baseUri . $this->getResourceUri()
            );
            $this->_hal->setData($this->toArray());
        }
        return $this->_hal;
    }
    /**
     * Converts the object into a string representation [JSON] utilizing the
     * magic method said object may then be cast to string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }
    /**
     * (non-PHPdoc)
     * @see Pincrowd_Rest_XmlRenderableInterface::toXml()
     */
    public function toXml()
    {
        $xml = new SimpleXMLElement("<{$this->_itemKey}/>");
        /* @var $item Pincrowd_Model_AbstractModel */
        foreach ($this->toArray() as $key => $value) {
            $xml->addChild($key,$value);
        }
        return (string) $xml->asXML();
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