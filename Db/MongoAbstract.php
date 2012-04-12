<?php
/**
 *
 *
 * @author zircote
 *
 */
abstract class Pincrowd_Db_MongoAbstract
{
    protected $_name;
    /**
     *
     *
     * @var MongoDB
     */
    protected static $_mongoDB;
    /**
     *
     *
     * @var Mongo
     */
    protected static $_mongo;
    /**
     *
     *
     * @param MongoDB $mongoDB
     */
    public static function setMongoDB(MongoDB $mongoDB)
    {
        self::$_mongoDB = $mongoDB;
    }
    /**
     * @returm MongoDB
     */
    public static function getMongoDB()
    {
        return self::$_mongoDB;
    }
    /**
     *
     *
     * @param Mongo $mongo
     */
    public static function setMongo(Mongo $mongo)
    {
        self::$_mongo = $mongo;
    }
    /**
     * @return Mongo
     */
    public static function getMongo()
    {
        return self::$_mongo;
    }
    /**
     *
     *
     * @param array $data
     * @return array
     */
    public function insert($data)
    {
        self::getMongoDB()->__get($this->_name)
            ->insert($data);
        return $data;
    }
    /**
     *
     *
     * @param array $data
     * @return array
     */
    public function save($data)
    {
        self::getMongoDB()->__get($this->_name)
            ->save($data);
        return $data;
    }
    public function findById($id, $fields = array())
    {
        $query = array('_id' => new MongoId($id));
        return (array) self::getMongoDB()
            ->__get($this->_name)
            ->findOne($query, $fields);
    }
    public function find($query)
    {
        return self::getMongoDB()
            ->__get($this->_name)
            ->find($query);
    }
    public function remove($id, $options = array())
    {
        $query = array('_id' => new MongoId($id));
        return self::getMongoDB()
        ->__get($this->_name)
        ->remove($query,$options);
    }
    /**
     *
     * @var Zend_Log
     */
    protected static $_log;
    /**
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;
    /**
     *
     * @var Zend_Db_Select
     */
    protected $_select;
    /**
     *
     * @var string
     */
    protected $_lastUpdatedField;
    /**
     *
     * @param string $attr
     * @param mixed $value
     * @return Ifbyphone_Model_AbstractModelCollection
     */
    public function setAttribute($attr, $value)
    {
        $this->_attributes[$attr] = $value;
        return $this;
    }
    /**
     *
     * @param array $attr
     * @return Ifbyphone_Model_AbstractModelCollection
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
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return Ifbyphone_Model_Mapper_AbstractDbMapper
     */
    public function setDb (Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
        return $this;
    }
    /**
     *
     * @return Zend_Db_Adapter_Abstract
     */
    public function getDb ()
    {
        if (! $this->_db instanceof Zend_Db_Adapter_Abstract &&
        Zend_Db_Table::getDefaultAdapter() instanceof Zend_Db_Adapter_Abstract) {
            $this->_db = Zend_Db_Table::getDefaultAdapter();
        }
        return $this->_db;
    }
    /**
     *
     * @param Zend_Log $log
     */
    public static function setLog(Zend_Log $log)
    {
        self::$_log = $log;
    }
    /**
     *
     * @return Zend_Log
     */
    public static function getLog()
    {
        if(!self::$_log instanceof Zend_Log){
            $log = new Zend_Log();
            $log->addWriter(new Zend_Log_Writer_Null);
            self::setLog($log);
        }
        return self::$_log;
    }
    /**
     *
     * @return Ifbyphone_Model_Mapper_LeadResponder_MailRoute
     */
    protected function _getSort()
    {
        $sort = $this->getAttribute('sort');
        if($this->_select instanceof Zend_Db_Select && (bool) $sort){
            foreach ($sort as $field){
                $this->_select->order($field);
            }
        }
        return $this;
    }
    /**
     * @todo must validate somewhere that these are valid fields before
     *       constructing the query
     *
     * @return Ifbyphone_Model_Mapper_LeadResponder_MailRoute
     */
    abstract protected function _getFields();
    /**
     *
     * @return Ifbyphone_Model_Mapper_LeadResponder_MailRoute
     */
    protected function _getPaging()
    {
        $paging = $this->getAttribute('paging');
        if($this->_select instanceof Zend_Db_Select && (bool) $paging){
            $this->_select->limitPage($paging['offset'], $paging['limit']);
        }
        return $this;
    }
    /**
     *
     * @return Ifbyphone_Model_Mapper_LeadResponder_MailRoute
     */
    protected function _getAuth()
    {
        $auth = $this->getAttribute('auth');
        if($this->_select instanceof Zend_Db_Select && (bool) $auth){
            foreach ($auth as $key => $value) {
                $this->_select
                ->where(
                sprintf('`%s` = :%s', $key,$key),
                array($key => $value)
                );
            }
        }
        return $this;
    }
    /**
     *
     * @return Ifbyphone_Model_Mapper_LeadResponder_MailRoute
     */
    protected function _getSearch()
    {
        $search = $this->getAttribute('search');

        if($this->_select instanceof Zend_Db_Select && (bool) $search){
            foreach ($search as $field => $item) {
                if(isset($item['op']) && in_array($item['op'], array('=','!=','LIKE', 'NOT LIKE'))){
                    $this->_select
                    ->where(
                    sprintf('`%s` %s ?', $field, $item['op']),
                    $item['value']
                    );
                }
            }
            if(isset($item['left'])){
                $this->_select->where(
                sprintf('`%s` %s ?', $field, $item['left']['op']),
                $item['left']['value']
                );
            }
            if(isset($item['right'])){
                $this->_select->where(
                sprintf('`%s` %s ?', $field, $item['right']['op']),
                $item['right']['value']
                );
            }
        }
        return $this;
    }

}
