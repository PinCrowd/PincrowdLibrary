<?php
/**
 *
 *
 * @category   Pincrowd
 * @package    Pincrowd_Rest
 * @subpackage Service
 */
/**
 *
 *
 */
abstract class Pincrowd_Rest_AbstractService
{
    /**
     * Define the Mapper Class name to be utlized by this service
     *
     * @var string
     */
    protected $_mapperClass;
    /**
     * Mapper class
     *
     * @var Pincrowd_Model_Mapper_AbstractDbMapper
     */
    protected $_mapper;
    /**
     *
     * @var array
     */
    protected $_attributes = array();
    /**
     *
     * @var array
     */
    protected $_options;
    /**
     * @var Pincrowd_Rest_AbstractController
     */
    public $_controller;
    /**
     * Model class
     *
     * @var Pincrowd_Rest_LastUpdatedInterface
     */
    protected $_lastResult;
    /**
     *
     * @var Zend_Log
     */
    protected $_log;
    /**
     *
     * @var array
     */
    protected $_defaultOptions = array(
        'enforceTrailing' => array(
            'enabled' => true,
            'stackindex' => array(
                'pre' => 5
            ),
        ),
        'jsonp'           => array(
            'enabled' => true,
            'stackindex' => array(
                'post' => 15
            ),
        ),
        'lastUpdated'     => array(
            'enabled' => true,
            'stackindex' => array(
                'post' => 20
            ),
        ),
        'method' => array(
            'enabled' => true
        ),
        'logging'         => array(
            'enabled' => true,
            'stackindex' => array(
                'pre' => 20
            ),
        'enabled' => false,
            'format' => "METHOD:[%%METHOD%%] PATHINFO:[%%PATHINFO%%] PARAMS:[%%PARAMS%%]"
        ),
        'auth' => array(
            'enabled' => true,
            'identityKey' => 'acct_id'
        )
    );
    public function __construct($options, Pincrowd_Rest_AbstractController $controller)
    {
        $this->setOptions($options)
            ->setController($controller);
    }
    /**
     *
     * @return Pincrowd_Model_Mapper_AbstractDbMapper
     */
    public function getMapper()
    {
        if(!$this->_mapper instanceof Pincrowd_Model_Mapper_AbstractDbMapper
            && $this->_mapperClass){
            $reflected = new ReflectionClass($this->_mapperClass);
            $this->_mapper = $reflected->newInstance();
            $this->_mapper->setAttributes($this->getAttributes());
        }
        return $this->_mapper;
    }
    /**
     *
     * @param Pincrowd_Rest_AbstractController $controller
     * @return Pincrowd_Rest_AbstractService
     */
    public function setController(Pincrowd_Rest_AbstractController $controller)
    {
        $this->_controller = $controller;
        return $this;
    }
    /**
     *
     * @param Zend_Config|array $options
     */
    public function setOptions($options = array())
    {
        if($options instanceof Zend_Config){
            $options = $options->toArray();
        }
        $this->_options = array_merge_recursive(
            $this->_defaultOptions, is_array($options) ? $options: array()
        );
        return $this;
    }
    /**
     *
     * @param string $option
     */
    public function getOptions()
    {
        if(!$this->_options){
            $this->setOptions();
        }
        return $this->_options;
    }
    /**
     *
     * @param string $option
     */
    public function getOption($option)
    {
        if(!$this->_options){
            $this->setOptions();
        }
        return @$this->_options[$option] ? : false;
    }
    /**
     *
     * @param array $attr
     * @return Pincrowd_Rest_AbstractService
     */
    public function setAttributes($attr){
        $this->_attributes = $attr;
        return $this;
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
     * @see Zend_Controller_Action::preDispatch()
     */
    public function preDispatch()
    {
        $i=0;
        $pd = array();
        $reflected = new ReflectionClass($this);
        $options = $this->getOptions();
        foreach ($reflected->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
            if(strstr($method->getName(),'PreDispatch')){
                $value = preg_replace('/^_/',null,$method->getName());
                $value = str_replace('PreDispatch',null, $value);
                $pd[@$options[$value]['stackindex']['pre'] ?: ++$i] = $method;
            }
        }
        foreach ($pd as $method) {
            $method->setAccessible(true);
            $method->invoke($this);
        }
        $this->getMapper()->setAttributes($this->getAttributes());
    }
    /**
     *
     * @see Zend_Controller_Action::postDispatch()
     */
    public function postDispatch()
    {
        $this->setAttributes($this->getMapper()->getAttributes());
        if($this->_controller->getResponse()->getHttpResponseCode() == 405){
            return;
        }
        if($this->_controller->getResponse()->getHttpResponseCode() == 202){
            return;
        }
        $i=0;
        $pd = array();
        $reflected = new ReflectionClass($this);
        $options = $this->getOptions();
        foreach ($reflected->getMethods(ReflectionMethod::IS_PROTECTED) as $method) {
            if(strstr($method->getName(),'PostDispatch')){
                $value = preg_replace('/^_/',null,$method->getName());
                $value = str_replace('PostDispatch',null, $value);
                $pd[@$options[$value]['stackindex']['post'] ?: ++$i] = $method;
            }
        }
        foreach ($pd as $method) {
            $method->setAccessible(true);
            $method->invoke($this);
        }
    }
    /**
     *
     * @return Zend_Log
     */
    public function getLog()
    {
        if(!$this->_log){
            /* @var $bootstrap Zend_Pincrowd_Rest_Bootstrap_Bootstrap */
            $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
            if (!$bootstrap->hasResource('log')) {
                $writer = new Zend_Log_Writer_Null();
                $this->_log = new Zend_Log();
                $this->_log->addWriter($writer);
            } else {
                $this->_log = $bootstrap->getResource('log');
            }
        }
        return $this->_log;
    }
    public function setLog(Zend_Log $log)
    {
        $this->_log = $log;
        return $this;
    }
    /**
     *
     * @return string|boolean
     */
    public function methodRequest()
    {
        $options = $this->getOption('method');
        if($options['enabled']){
            if(($method = $this->_controller->getRequest()->getParam('_method', false))
            && $this->_controller->getRequest()->isGet()){
                return in_array(
                    strtoupper($method),
                    $this->_controller->_allow
                ) ? $method : false;
            }
        }
        return false;
    }
    /**
     *
     * Here we will enforce the trailing / is not present in get requests in the
     * event it is we will forward to its operational counterpart with a 301
     *
     * @return Pincrowd_Controller_RestControllerAbstract
     */
    protected function _enforceTrailingPreDispatch()
    {
        $options = $this->getOption('enforceTrailing');
        if($options['enabled']){
            if($this->_controller->getRequest()->isGet()){
                if(preg_match('/.+\/$/', $this->_controller->getRequest()->getPathInfo())){
                    $filter = new Zend_Filter_PregReplace(
                    array('match' => '/\/$/','replace' => '')
                    );
                    $request = $this->_controller->getRequest();
                    $this->_redirect(
                        $filter->filter(
                            $request->getPathInfo()
                        ) . '?' .
                        http_build_query($request->getQuery()), 301
                    );
                }
            }
        }
        return $this;
    }
    /**
     *
     * @return Pincrowd_Controller_RestControllerAbstract
     */
    protected function _fieldsPreDispatch()
    {
        $options = $this->getOption('fields');
        if($result = $this->_controller->getRequest()->getParam('fl', null)){
            $this->_attributes['fields'] = explode(',',$result);
        }
        return $this;
    }
    /**
     *
     * @return Pincrowd_Controller_RestControllerAbstract
     */
    protected function _pagingPreDispatch()
    {
        $options = $this->getOption('paging');
        $this->_attributes['paging'] = array(
            'offset' => $this->_controller->getRequest()
                ->getParam('start',@$options['defaults']['offset'] ?: 0) + 1,
            'limit' => $this->_controller->getRequest()
                ->getParam('rows', @$options['defaults']['limit'] ?: 10)
            );
        return $this;
    }
    /**
     *
     * @return Pincrowd_Controller_RestControllerAbstract
     */
    protected function _pagingPostDispatch()
    {
        $options = $this->getOption('paging');
        if(isset($this->_attributes['paging']['count'])) {
            $count = $limit = $offset = 0;
            extract($this->_attributes['paging']);
            if(($offset+$limit)){
                $start = (($limit * $offset) - $limit) + 1;
                $end = ($limit * $offset);
                $start = ($count > $start) ? $start : $count;
                $end = ($count > $end) ? $end : $count;
                if( !($start === 1 && $count === $end)){
                    $this->_controller->getResponse()->setHeader(
                        'Range', sprintf('%s-%s/%s',$start,$end,$count)
                    );
                    $this->_controller->getResponse()->setHttpResponseCode(206);
                }
            }
        }
        return $this;
    }
    /**
     *
     * @return Pincrowd_Controller_RestControllerAbstract
     */
    protected function _searchPreDispatch()
    {
        $options = $this->getOption('search');
        if($q =  $this->_controller->getRequest()
        ->getParam(@$options['searchKey'] ? : 'q', null)){
            $this->_attributes['search']['query'] = $q;
            $this->_parseSearchQuery(explode(',', $q));
        }
        return $this;
    }
    /**
     * @param array $queryParts
     * @return Pincrowd_Controller_RestControllerAbstract
     */
    protected function _parseSearchQuery(array $queryParts)
    {
        $result = array();
        foreach ($queryParts as $qPart) {
            $result = array_merge($result,$this->_parseQueryPart($qPart));
        }
        $this->_attributes['search'] = $result;
        return $this;
    }
    /**
     * @param array $qPart
     * @return array
     */
    protected function _parseQueryPart($qPart)
    {
        list($key,$val) = explode(':',$qPart);
        if(preg_match('/^-/', $key)){
            $key = preg_replace('/^-/',null, $key);
            $result = array($key => array());
            // Not Equals
            $result[$key]['op'] = '!=';
        } else {
            $key = preg_replace('/^\+/',null, $key);
            $result = array($key => array());
            $result[$key]['op'] = '=';
        }
        if(preg_match('/ TO /', $val)){
            unset($result[$key]['op']);
            $result[$key] = array_merge_recursive($result[$key],$this->_parseQueryRange($val));
        } elseif(preg_match('/(\?|\*)/', $val)){
            $result[$key]['op'] = ($result[$key]['op'] == '!=') ? 'NOT LIKE' : 'LIKE';
            $result[$key] = array_merge_recursive($result[$key],$this->_parseQueryLike($val));
        } else {
            $result[$key]['value'] = trim($val, '"');
        }
        return $result;
    }
    /**
     *
     *@param string $likePart
     * @return array
     */
    protected function _parseQueryLike($likePart)
    {
        $likePart = preg_replace('/(\?)/', '_', $likePart);
        $likePart = preg_replace('/(\*)/', '%', $likePart);
        return array('value' => $likePart);
    }
    /**
     * @param string $rangePart
     * @return array
     */
    protected function _parseQueryRange($rangePart)
    {
        list($left, $right) = explode(' TO ', $rangePart);
        if(!preg_match('/\*/', $right)){
            if(preg_match('/\]$/', $rangePart)){
                $result['right']['op'] = '<=';
            } elseif(preg_match('/\}$/', $rangePart)){
                $result['right']['op'] = '<';
            }
            $result['right']['value'] = preg_replace('/(\}|\])$/',null,$right,1);
        }
        if(!preg_match('/\*/', $left)){
            if(preg_match('/^\[/', $rangePart)){
                $result['left']['op'] = '>=';
            } elseif(preg_match('/^\{/', $rangePart)){
                $result['left']['op'] = '>';
            }
            $result['left']['value'] = preg_replace('/^(\{|\[)/',null,$left,1);
        }
        return $result;
    }
    /**
     *
     * @return Pincrowd_Controller_RestControllerAbstract
     */
    protected function _sortPreDispatch()
    {
        $options = $this->getOption('sort');
        if(null !== ($sort = $this->_controller->getRequest()->getParam('sort', null))){
            $this->_attributes['sort'] = $this->_getSort($sort);
        }
        return $this;
    }
    /**
     *
     * @param array $data
     */
    protected function _getSort($data)
    {
        $result = array();
        foreach (explode(',',$data) as $value) {
            $result[] = str_ireplace(
            array(' asc',' desc'), array(' ASC',' DESC'), $value
            );
        }
        return $result;
    }
    /**
     * @todo should create a filter chain to ensure it is a valid javascript
     * function:
     *  - name begins with alpha
     *  - contains only /a-zA-Z0-9_/
     *
     * @return Pincrowd_Controller_RestControllerAbstract
     */
//     protected function _jsonpPostDispatch()
//     {
//         $options = $this->getOption('jsonp');
//         if($options['enabled']){
//         }
//         return $this;
//     }
    /**
     *
     * @return Pincrowd_Controller_RestControllerAbstract
     */
    protected function _lastUpdatedPostDispatch()
    {
        $options = $this->getOption('lastUpdated');
        if($lastUpdated = $this->getLastUpdated()){
            $this->_controller->getResponse()->setHeader('Last-Modified', $lastUpdated);
        }
        return $this;
    }
    /**
     *
     * @param Pincrowd_Rest_LastUpdatedInterface $result
     */
    protected function setLastResult($result)
    {
        $this->_lastResult = $result;
    }
    /**
     * @return Pincrowd_Rest_LastUpdatedInterface
     */
    protected function getLastUpdated()
    {
        if($this->_lastResult){
            return $this->_lastResult->getLastUpdated();
        } else {
            return null;
        }
    }
    /**
     *
     * @return Pincrowd_Controller_RestControllerAbstract
     */
    protected function _loggingPreDispatch()
    {
        $options = $this->getOption('logging');
        $filter = new Zend_Filter();
        $filter->addFilter(new Zend_Filter_StripNewlines())
        ->addFilter(
            new Zend_Filter_PregReplace(
                array('match' => '/\s\s+/', 'replace' => '')
            )
        );
        $message = str_replace(
            array('%%METHOD%%','%%PATHINFO%%','%%PARAMS%%'),
            array($this->_controller->getRequest()->getMethod(),
                $this->_controller->getRequest()->getPathInfo(),
            $filter->filter(
                print_r($this->_controller->getRequest()->getUserParams(), true))
            ),
            $options['format']
        );
        $this->getLog()->info($message);
        return $this;
    }
    /**
     *
     * @param string $url
     * @param integer $code
     */
    protected function _redirect($url, $code = 301)
    {
        /* @var $redirector Zend_Controller_Action_Helper_Redirector */
        $redirector = Zend_Controller_Action_HelperBroker::getStaticHelper(
            'redirector'
        )->setCode($code);
        $redirector->gotoUrl($url);
    }

    /**
     * @return Pincrowd_Model_AbstractModelCollection
     */
    abstract public function getCollection();
    /**
     *
     * @param string $id
     * @return Pincrowd_Model_AbstractModel
     */
    abstract public function getOne($id);
    /**
     * @return int
     */
    abstract public function getCount();
    /**
     * @param Pincrowd_Model_AbstractModel $data
     */
    abstract public function createOne($data);
    /**
     *
     * @param integer $id
     * @return Pincrowd_Model_AbstractModel
     */
    abstract public function updateOne($id);
    /**
     *
     * @param integer $id
     * @return void
     */
    abstract public function deleteOne($id);
}