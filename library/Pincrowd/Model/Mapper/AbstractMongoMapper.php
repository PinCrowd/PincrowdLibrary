<?php
/**
 * @category   Pincrowd
 * @package    Library
 * @subpackage Model
 */
/**
 *
 * @category   Pincrowd
 * @package    Library
 * @subpackage Model
 *
 */
abstract class Pincrowd_Model_Mapper_AbstractMongoMapper extends Pincrowd_Model_AbstractMapper
{
    /**
     *
     * @var MongoDB
     */
    protected static $_defaultMongoDB;
    /**
     *
     * @param MongoDB $mongoDB
     */
    public static function setDefaultDB(MongoDB $defaultDB)
    {
        self::$_defaultMongoDB = $defaultDB;
    }
    /**
     * @return MongoDB
     */
    public static function getDefaultDB()
    {
        return self::$_defaultMongoDB;
    }
    /**
     *
     * @return Pincrowd_Model_Mapper_AbstractMongoMapper
     */
    protected function _getSort()
    {
        $sort = $this->getAttribute('sort');
        if(is_array($this->_query) && (bool) $sort){
            foreach ($sort as $field){
//                 $this->_query->order($field);
            }
        }
        return $this;
    }
    /**
     *
     * @return Pincrowd_Model_Mapper_AbstractMongoMapper
     */
    protected function _getPaging()
    {
        $paging = $this->getAttribute('paging');
        if(is_array($this->_query) && (bool) $paging){
//             $this->_query->limitPage($paging['offset'], $paging['limit']);
        }
        return $this;
    }
    /**
     *
     * @return Pincrowd_Model_Mapper_AbstractMongoMapper
     */
    protected function _getAuth()
    {
        $auth = $this->getAttribute('auth');
        if(is_array($this->_query) && (bool) $auth){
            foreach ($auth as $key => $value) {
//                 $this->_query
//                 ->where(
//                 sprintf('`%s` = :%s', $key,$key),
//                 array($key => $value)
//                 );
            }
        }
        return $this;
    }

    /**
     *
     * @return Pincrowd_Model_Mapper_AbstractMongoMapper
     */
    protected function _getFields()
    {
        $fields = $this->getAttribute('fields');
        if(is_array($this->_query) && (bool) $fields ){
//             $this->_query->from(self::DB_TABLE, $fields);
        } else {
//             $this->_query->from(self::DB_TABLE);
        }
        return $this;
    }
    /**
     *
     * @return Pincrowd_Model_Mapper_AbstractMongoMapper
     */
    protected function _getSearch()
    {
        $search = $this->getAttribute('search');

        if(is_array($this->_query) && (bool) $search){
            foreach ($search as $field => $item) {
                if(isset($item['op']) && in_array($item['op'], array('=','!=','LIKE', 'NOT LIKE'))){
//                     $this->_query
//                     ->where(
//                     sprintf('`%s` %s ?', $field, $item['op']),
//                     $item['value']
//                     );
                }
            }
            if(isset($item['left'])){
//                 $this->_query->where(
//                 sprintf('`%s` %s ?', $field, $item['left']['op']),
//                 $item['left']['value']
//                 );
            }
            if(isset($item['right'])){
//                 $this->_query->where(
//                 sprintf('`%s` %s ?', $field, $item['right']['op']),
//                 $item['right']['value']
//                 );
            }
        }
        return $this;
    }
}