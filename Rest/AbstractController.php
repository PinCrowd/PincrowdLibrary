<?php
/**
 *
 *
 * @category   Pincrowd
 * @package    Pincrowd_Rest
 * @subpackage Controller
 */
/**
 * - Method  URI                            Module_Controller::action
 * - GET     /v{?}/users                    Api_UsersController::getAction()
 * - POST    /v{?}/users                    Api_UsersController::postAction()
 * - GET     /v{?}/users[/:id]              Api_UsersIdController::getAction()
 * - PUT     /v{?}/users/:id                Api_UsersIdController::putAction()
 * - PATCH   /v{?}/users/:id                Api_UsersIdController::patchAction()
 * - OPTIONS /v{?}/users[/:id]              Api_Users[Id]Controller::optionsAction()
 * - HEAD    /v{?}/users[/:id]              Api_Users[Id]Controller::headAction()
 * - DELETE  /v{?}/users/:id                Api_UsersIdController::deleteAction()
 * - GET     /v{?}/users/:id?_method=put    Api_UsersIdController::putAction()
 * - GET     /v{?}/users/:id?_method=delete Api_UsersIdController::deleteAction()
 */
abstract class Pincrowd_Rest_AbstractController extends Zend_Controller_Action
{
    /**
     * This defines the base uri for rest resources
     *
     * @var string
     */
    protected $_baseUri;
    /**
     *
     * @var string
     */
    protected $_resource;
    /**
     * Definition attributes from the Request/Response Mappers
     *
     * @var array
     */
    protected $_attributes;
    /**
     * The OAuth2 Server Server Class implemented for OAuth aNa
     * @var OAuth2_MongoServer
     */
    protected $_oauth;
    /**
     *
     * @var array
     */
    protected $_options;
    /**
     * The Service Object responsible for this controller
     *
     * @var Pincrowd_Rest_AbstractService
     */
    protected $_service;
    /**
     * The last result from the service.
     *
     * @var Pincrowd_Model_AbstractModel|Pincrowd_Model_AbstractModelCollection
     */
    protected $_lastResponse;
    /**
     * Default Allow
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.7
     * @var array
     */
    protected $_allow = array('GET','POST','PUT','DELETE','OPTIONS','TRACE', 'PATCH');
    /**
     * <b>Three allowed parameters as defined in RFC2616
     *   - <b>type:</b> Media Type
     *   - <b>level:</b> Level
     *   - <b>quality:</b>Quality
     *
     * Example:
     * <code>
     * protected $_accept = array(
     *     'application/xml+hal' => array(
     *         'type' => 'application/xml+hal',
     *         'level' => '1',
     *         'q' => '0.3'
     *     )
     * );
     * </code>
     *
     * @link http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.1
     * @var array
     */
    protected $_accept = array(
        'application/json'     => array('type' => 'application/json'),
        'application/json+hal' => array('type' => 'application/json+hal'),
        'application/json-p'   => array('type' => 'application/json-p'),
        'application/xml'      => array('type' => 'application/xml'),
        'application/xml+hal'  => array('type' => 'application/xml+hal'),
        '*/*'                  => array('type' => 'application/json')
    );
    /**
     * The requested media; default is json.
     * @var string
     */
    protected $_requestedMedia = 'application/json';
    /**
     * The accept header string to be returned to the client.
     * @var string
     */
    protected $_acceptHeader;
    /**
     * This is a log, there are many like it but this one is mine.
     *
     * @var Zend_Log
     */
    protected $_log;
    /**
     *
     * @var Pincrowd_Rest_Cache_Page
     */
    protected $_cache;
    /**
     * @todo add support for config from Zend_Config
     * (non-PHPdoc)
     * @see Zend_Controller_Action::init()
     */
    public function init ()
    {
        $mongo = new Mongo();
//         $storage = new OAuth2_StorageMongo($mongo->selectDB('oauth2'));
//         $this->_oauth = new OAuth2_MongoServer($storage);
        /* @var $bootstrap Zend_Pincrowd_Rest_Bootstrap_Bootstrap */
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        if($bootstrap->hasResource('rest_controller')){
            $this->setOptions(
                $bootstrap->getResource('rest_controller')->getRestController()
            );
        }
        $this->_setUpCache();
        if($bootstrap->hasResource('log')){
            $this->setLog(
                $bootstrap->getResource('log')
            );
        }
        if($this->_service){
            $this->_service->setLog($this->getLog());
        }
        $this->setRequestedAcceptHeader();
    }
    /**
     * Sets the requested media type
     * @return Pincrowd_Rest_AbstractController
     */
    public function setRequestedAcceptHeader($acceptHeader = null)
    {
        if(!$acceptHeader && $this->getRequest()->getHeader('accept')){
            $this->_requestedMedia = $this->getRequest()->getHeader('accept');
        }
        return $this;
    }
    /**
     *
     * @param array|Zend_Config $options
     * @return Pincrowd_Rest_AbstractController
     */
    public function setOptions($options)
    {
        if($options instanceof Zend_Config){
            $options = $options->toArray();
        }
        $this->_options = $options;
        return $this;
    }
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        $this->_helper->viewRenderer->setNoRender();

        /**
         * Set headers to allow CORS (Cross Origin Resource Sharing) Javasript Requests.
         * For most requests these can be more general, such as wildcarding the Allowed Headers.
         * For an OPTIONS request, the headers must match exactly what is asked for, otherwise
         * the preflight request will fail.
         * http://www.w3.org/TR/2008/WD-access-control-20080912
         */
        // Allow any origin to make an API request.
        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', '*', true);

        /**
         * Allow the client to send us any custom headers. For instance, a JS library
         * might add a custom X-XMLHTTPREQUEST header, and we should not deny API access
         * because of this, so we wildcard the allowed headers.
         */
        $this->getResponse()
             ->setHeader('Access-Control-Allow-Headers', '*', true);

        // Allow a resource's prescribed methods
        $this->getResponse()
             ->setHeader('Access-Control-Allow-Methods', implode(', ', $this->getAllow()), true);

        // Allow cookies to be sent along with the ajax request
        $this->getResponse()
             ->setHeader('Access-Control-Allow-Credentials', 'true', true);

        // Allow caching of the preflight OPTIONS request (time unit is seconds)
        $this->getResponse()
             ->setHeader('Access-Control-Max-Age', '28800', true);

        if($this->_service instanceof Pincrowd_Rest_AbstractService){
            $this->_service->preDispatch();
        }
    }
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::postDispatch()
     */
    public function postDispatch()
    {
        if($this->_service instanceof Pincrowd_Rest_AbstractService){
            $this->_service->postDispatch();
        }
//         $this->_purgeCache();
        $this->_acceptPostDispatch();
    }
    /**
     *
     * @param Pincrowd_Rest_AbstractService $service
     * @return Pincrowd_Rest_AbstractController
     */
    public function setService(Pincrowd_Rest_AbstractService $service)
    {
        $this->_service = $service;
        return $this;
    }
    /**
     * On all requests there is no media type defined by your request you will
     * receive a 415
     */
    public function unAcceptableMediaAction() {
        $this->getResponse()->setHttpResponseCode(415);
    }
    /**
     * If your requested HTTP_METHOD is not defined in the controller this is
     * what you get.
     */
    public function notAllowedAction() {
        $this->getResponse()->setHttpResponseCode(405);
    }
    /**
     * If the default action 'index' is called this is your reward.
     */
    public final function indexAction() {
        return $this->__call('index',null);
    }
    /**
     * Default GET
     */
    public function getAction() {
        $this->notAllowedAction();
    }
    /**
     * Default PUT
     */
    public function putAction() {
        $this->notAllowedAction();
    }
    /**
     * Default PATCH
     */
    public function patchAction() {
        $this->notAllowedAction();
    }
    /**
     * Default POST
     */
    public function postAction() {
        $this->notAllowedAction();
    }
    /**
     * Default DELETE
     */
    public function deleteAction() {
        $this->notAllowedAction();
    }
    /**
     *
     * @see Pincrowd_Rest_AbstractController::headAction()
     */
    public function headAction()
    {
        $this->getAction();
    }
    /**
     * Default OPTIONS
     */
    public function optionsAction() {
        if(!$this->getResponse()->isException()){
            if(is_array($this->getAllow()) && (bool) $this->getAllow()){
                $this->getResponse()->setHeader(
                    'Allow', implode(', ', $this->getAllow())
                );
            }
        }

        /**
         * Set headers needed for CORS Javasript Requests.
         * They must match exactly what is asked for in the Options call,
         * otherwise the CORS preflight will fail and the second ajax call
         * will never be made after the initial options call.
         * See preDispatch() for more details.
         */
        $request = $this->getRequest();
        $this->getResponse()
             ->setHeader('Access-Control-Allow-Origin', $request->getHeader('Origin'), true);
        $this->getResponse()
             ->setHeader('Access-Control-Allow-Headers', $request->getHeader('Access-Control-Request-Headers'), true);
        $this->getResponse()
             ->setHeader('Access-Control-Allow-Methods', $request->getHeader('Access-Control-Request-Method'), true);

        $this->getResponse()->setBody(null);
    }
    /**
     * (non-PHPdoc)
     * @see Zend_Controller_Action::__call()
     */
    public function __call($method, $args = array())
    {
        $reflected = new ReflectionClass($this);
        $method = $this->_service
            ->methodRequest() ? : $this->getRequest()->getMethod();
        $method = strtolower($method) . 'Action';
        if ($reflected->hasMethod($method)) {
            return $reflected->getMethod($method)
            ->invokeArgs($this,is_array($args) ? $args : array());
        }
        $this->notAllowedAction();
    }    /**
     *
     * @return Pincrowd_Controller_RestControllerAbstract
     */
    protected function _acceptPostDispatch()
    {
        $this->getResponse()
            ->setHeader(
                'Accept',$this->_parseAcceptTypes($this->_accept)
            );
        return $this;
    }
    /**
     * This method will take the expected result and format it based on the
     * request headers.
     */
    protected function _getResultFormatted()
    {
        /**
         * If the response implements the IsLoadable interface we will test if the
         * model has data; if there is no data we return 404 and short circuit.
         */
        if($this->_lastResponse instanceof Pincrowd_Rest_IsLoadableInterface && !$this->_lastResponse->isLoaded()){
            $this->getResponse()->setHttpResponseCode(404);
            $this->getResponse()->setBody(null);
            return;
        }
        $callback = $this->getRequest()->getParam('callback', false);
        if($callback){
            $this->_requestedMedia = in_array(
                $this->_requestedMedia,
                array('application/json-p','application/json-p+hal')
            ) ? $this->_requestedMedia : 'application/json-p';
        }
        switch ($this->_requestedMedia) {
            case 'application/xml+hal':
                $result = $this->_toXmlHal();
            break;
            case 'application/xml':
                $result = $this->_toXml();
            break;
            case 'application/json-p':
                $result = $this->_toJsonp($callback);
            break;
            case 'application/json-p+hal':
                $result = $this->_toJsonpHal($callback);
            break;
            case 'application/json+hal':
                $result = $this->_toJsonHal();
            break;
            case 'application/json':
            default:
                $result = $this->_toJson();
            break;
        }
//         $this->_saveEtag($result);
        return $result;
    }
    /**
     *
     * @return string
     */
    protected function _toJsonHal()
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json+hal', true);
        return (string) $this->_lastResponse->toJsonHal($this->_baseUri);
    }
    /**
     *
     * @return string
     */
    protected function _toJson()
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'application/json', true);
        return (string) $this->_lastResponse->toJson();
    }
    /**
     *
     * @return string
     */
    protected function _toXmlHal()
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'application/xml+hal', true);
        return $this->_lastResponse->toXmlHal($this->_baseUri);
    }
    /**
     *
     * @return string
     */
    protected function _toXml()
    {
        $this->getResponse()
            ->setHeader('Content-Type', 'application/xml', true);
        return $this->_lastResponse->toXml();
    }
    /**
     *
     * @return string
     */
    protected function _toJsonp($callback)
    {
        $content = $this->_toJson();
        $this->getResponse()->setHeader(
            'Content-Type', 'application/json-p', true
        );
        return sprintf('%s(%s)',$callback,$content);
    }
    /**
     *
     * @return string
     */
    protected function _toJsonpHal($callback)
    {
        $content = $this->_toJsonHal();
        $this->getResponse()->setHeader(
            'Content-Type', 'application/json-p+hal', true
        );
        return sprintf('%s(%s)',$callback,$content);
    }
    /**
     *
     * @param array $types
     */
    protected function _parseAcceptTypes($types)
    {
        $result = array();
        foreach ($types as $type) {
            $result[$type['type']] = $this->_parseAcceptType($type);
        }
        return implode(',', $result);
    }
    /**
     *
     * @param string $type
     */
    protected function _parseAcceptType($type)
    {
        $result = $type['type'];
        if(isset($type['level'])){
            $result .= ';level=' . $type['level'];
        }
        if(isset($type['q'])){
            $result .= ';q=' . $type['q'];
        }
        return $result;
    }
    /**
     *
     * @return array
     */
    public function getParams()
    {
        return $this->getRequest()->getParams();
    }
    /**
     *
     * @return array
     */
    public function getAllow()
    {
        return $this->_allow;
    }
    /**
     * @return Zend_Log
     */
    public function getLog()
    {
        return $this->_log;
    }
    /**
     *
     * @param Zend_Log $log
     * @return Pincrowd_Rest_AbstractController
     */
    public function setLog(Zend_Log $log)
    {
        $this->_log = $log;
        return $this;
    }
    /**
     *
     * @return Pincrowd_Rest_Cache_Page
     */
    public function getCache()
    {
        return $this->_cache;
    }
    /**
     *
     * @param Pincrowd_Rest_Cache_Page $cache
     * @return Pincrowd_Rest_AbstractController
     */
    public function setCache(Pincrowd_Rest_Cache_Page $cache)
    {
        $this->_cache = $cache;
        return $this;
    }
    /**
     * @todo this will need to throw a Rest specific Exception for acceptable
     * exceptions and error handling/messaging.
     * @throws Exception
     */
    protected function _setUpCache()
    {
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        if($bootstrap->hasResource('cachemanager') &&
        (($cache = $bootstrap->getResource('cachemanager')
         ->getCache('page_cache'))  instanceof Pincrowd_Rest_Cache_Page)){
            $this->setCache($cache);
            if($this->_resource){
                $this->getCache()->setItemTags('__resource__'.$this->_resource);
            }
            if($this->getRequest()->isGet() || $this->getRequest()->isHead()){
                $this->getCache()->start();
            } elseif($this->getRequest()->getHeader('if-match') &&
            !$this->getCache()->testETag($this->getRequest()->getHeader('if-match'))){
                throw new Exception(
                    sprintf(
                        'ETag does not match [if-match:%s]',
                        $this->getRequest()->getHeader('if-match')
                    )
                );
            } else {
                $this->getCache()->cancel();
            }
        }
    }
    /**
     *
     */
    protected function _purgeCache()
    {
        if($this->getResponse()->isException()){
            return;
        }
        if($this->_resource && ($this->getRequest()->isPost() ||
            $this->getRequest()->isPut() || $this->getRequest()->isDelete())){
            /*
             * This needs to hook into auth asap.
             */
            $this->getCache()->clean(
                Zend_Cache::CLEANING_MODE_MATCHING_TAG,
                array(
                    '__resource__' . $this->_resource,
                    '__client_id__' . 1014
                )
            );
        }
    }
}
