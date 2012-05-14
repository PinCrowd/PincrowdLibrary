<?php
/**
 *
 *
 * @category Pincrowd
 * @package Pincrowd_Rest
 * @subpackage Cache
 */
require_once 'Zend/Cache/Core.php';
/**
 *
 *
 * @category Pincrowd
 * @package Pincrowd_Rest
 * @subpackage Cache
 */
class Pincrowd_Rest_Cache_Page extends Zend_Cache_Core
{

    /**
     *
     * @var Zend_Controller_Response_Http
     */
    protected $_response;
    /**
     *
     * @var Zend_Controller_Request_Http
     */
    protected $_request;
    /**
     *
     * @var Zend_Cache_Backend_MongoDb
     */
    protected $_backend;
    /**
     * The desired tags to be applied to the page cached item
     * @var array
     */
    protected $_itemTags = array();
    /**
     *
     */
    public function __flush()
    {
        ob_implicit_flush();
    }
    /**
     * This frontend specific options
     *
     * @var array options
     */
    protected $_specificOptions = array(
        'debug_header' => false,
        'default_options' => array(
            'cache' => true,
            'specific_lifetime' => 3600,
            'tags' => array(),
            'priority' => null,
            'dnd' => false
        )
    );

    /**
     * Internal array to store some options
     *
     * @var array associative array of options
     */
    protected $_activeOptions = array();

    /**
     * If true, the page won't be cached
     *
     * @var boolean
     */
    protected $_cancel = true;

    /**
     * Constructor
     *
     * @param  array   $options                Associative array of options
     * @param  boolean $doNotTestCacheValidity If set to true, the cache validity won't be tested
     * @throws Zend_Cache_Exception
     * @return void
     */
    public function __construct(array $options = array())
    {
        foreach ($options as $name => $value) {
            $name = strtolower($name);
            $this->setOption($name, $value);
        }
        $this->setOption('automatic_serialization', true);
        $this->_activeOptions = $this->_specificOptions['default_options'];
    }

    /**
     * Start the cache
     *
     * @param  string     $id       A cache id (if you set a value here, maybe you have to use Output frontend instead)
     * @param  array|null $tags     Options array of tags for this page request.
     * @param  boolean    $doNotDie For unit testing only !
     * @return boolean              True if the cache is hit (false else)
     */
    public function start($id = false, $tags = null, $doNotDie = false)
    {
        if(!$this->_options['caching']){
            return false;
        }
        $capabilities = $this->_backend->getCapabilities();
        if(!$capabilities['tags']){
            Zend_Cache::throwException('Tags are required for this page cache');
        }
        /*
         * If this request is cached and has a valid requestor via etag we will
         * short circuit the request return 304 with no body and no additional
         * headers as specified by RFC-2616
         */
        if ($etag = $this->getRequest()->getHeader('if-none-match')) {
            if($this->_backend->getIdsMatchingTags(array('__etag__' . $etag))){
                $this->getResponse()->setHttpResponseCode(304)
                    ->setBody(null)
                    ->sendResponse();
                if (!$doNotDie && !$this->_specificOptions['default_options']['dnd'] &&
                    !defined('Pincrowd_PHPUNIT_TEST_ACTIVE')) {
                    die();
                } else {
                    return;
                }
            }
        }
        if($tags){
            $this->setItemTags($tags);
        }
        $this->_activeOptions = $this->_specificOptions['default_options'];
        if (!($this->_activeOptions['cache'])) {
            return false;
        }
        if (!$id) {
            $id = $this->_makeId();
            if (!$id) {
                return false;
            }
        }
        $array = $this->load($id);
        if ($array !== false) {
            $data = $array['data'];
            $headers = $array['headers'];
            if (!headers_sent()) {
                foreach ($headers as $value) {
                    $this->getResponse()->setHeader($value['name'], $value['value'], true);
                }
            }
            $this->getResponse()->setBody($data)
                ->setHttpResponseCode($array['statusCode']);
            if ($this->_specificOptions['debug_header']) {
                $this->getResponse()
                    ->setHeader('X-DEBUG-CACHE-HEADER', 'This is a cached page !');
            }
            $this->getResponse()->sendResponse();
            if (!$doNotDie && !$this->_specificOptions['default_options']['dnd'] && !defined('Pincrowd_PHPUNIT_TEST_ACTIVE')) {
                die();
            }
            return true;
        }
        ob_start(array($this, '_flush'));
        ob_implicit_flush(false);
        return false;
    }

    /**
     * Cancel the current caching process
     */
    public function cancel()
    {
        $this->_cancel = true;
    }

    /**
     * callback for output buffering
     * (shouldn't really be called manually)
     *
     * @param  string $data Buffered output
     * @return string Data to send to browser
     */
    public function _flush()
    {
        $data = $this->getResponse()->getBody(true);
        if(!isset($data['default']) || empty($data['default'])){
            $this->cancel();
            $data = null;
        }else {
            $data = $data['default'];
        }
        if (!$this->_options['caching'] ||
            $this->_cancel ||
            $this->getResponse()->isException() ||
            $this->getResponse()->isRedirect() ||
            $this->getResponse()->getHttpResponseCode() > 299
        ) {
            return $data;
        }
        $etag = md5($data);
        if(!headers_sent()){
            header('ETag: "'. $etag . '"');
        }
        $this->getResponse()->setHeader('ETag', '"'. $etag . '"', true);
        $this->setItemTags('__etag__'.$etag);
        $array = array(
            'data'       => $data,
            'headers'    => $this->getResponse()->getHeaders(),
            'statusCode' => $this->getResponse()->getHttpResponseCode(),
            'requestParams' => $this->getRequest()->getParams()
        );
        if(function_exists('apache_request_headers')){
            $array['requestHeaders'] = apache_request_headers();
        }
        if($this->getRequest()->getParam('controllerResource', false)){
            $this->setItemTags(
                '__resource__' . $this->getRequest()
                    ->getParam('controllerResource','NA')
            );
        }
        $this->save(
            $array,
            $this->_makeId(),
            $this->getItemTags(),
            $this->_activeOptions['specific_lifetime'],
            $this->_activeOptions['priority']
        );
        return $data;
    }
    /**
     * @return array
     */
    public function getItemTags(){
        return $this->_itemTags;
    }
    /**
     *
     * @param array $itemTags
     */
    public function setItemTags($itemTags)
    {
        if(is_array($itemTags)){
            $this->_itemTags = array_merge($this->_itemTags, $itemTags);
        }
        else{
            array_push($this->_itemTags, $itemTags);
        }
        return $this;
    }
    /**
     * Make an id depending on REQUEST_URI and superglobal arrays (depending on options)
     *
     * @return mixed|false a cache id (string), false if the cache should have not to be used
     */
    protected function _makeId()
    {
        $array = array();
        foreach($this->getRequest()->getParams() as $name => $value){
            array_push(
                $array, array(
                    'key' => rawurlencode($name),
                    'val' => rawurlencode($value)
                )
            );
        }
        $ordBytes = array();
        foreach($array as $param) {
            $bytes_str = null;
            $chars = str_split($param['key'].$param['val'],1);
                foreach($chars as $chr) {
                    $bytes_str .= dechex(ord($chr));
                }
                array_push($ordBytes, $bytes_str);
            }
        asort($ordBytes ,SORT_STRING);
        $retval = null;
        $len = count($array)-1;
        foreach($ordBytes as $index=>$value){
            $retval .= $array[$index]['key'].'='.$array[$index]['val'];
            if($len--) $retval .= '&';
        }
        $retval .= $this->getRequest()->getHeader('Accept');
        $id = sha1($retval);
        if ($this->_specificOptions['debug_header']) {
            $this->getResponse()
                ->setHeader('X-DEBUG-CACHE-STRING', $retval)
                ->setHeader('X-DEBUG-CACHE-ITEM-KEY', $id);
        }
        return $id;
    }
    /**
     *
     * @param string $etag
     * @return boolean
     */
    public function testETag($etag)
    {
        return (bool) $this->_backend
            ->getIdsMatchingTags(array('__etag__'.$etag));
    }
    /**
     *
     * @return Zend_Controller_Request_Http
     */
    public function getRequest()
    {
        if(!$this->_request instanceof Zend_Controller_Request_Abstract){
            $this->_request = Zend_Controller_Front::getInstance()->getRequest();
        }
        return $this->_request;
    }
    /**
     *
     * @return Zend_Controller_Response_Http
     */
    public function getResponse()
    {
        if(!$this->_response instanceof Zend_Controller_Response_Abstract){
            $this->_response = Zend_Controller_Front::getInstance()->getResponse();
        }
        return $this->_response;
    }
    /**
     *
     * @param Zend_Controller_Request_Abstract $request
     * @return Pincrowd_Rest_Cache_Page
     */
    public function setRequest(Zend_Controller_Request_Abstract $request)
    {
        $this->_request = $request;
        return $this;
    }
    /**
     *
     * @param Zend_Controller_Response_Abstract $response
     * @return Pincrowd_Rest_Cache_Page
     */
    public function setResponse(Zend_Controller_Response_Abstract $response)
    {
        $this->_response = $response;
        return $this;
    }
}
