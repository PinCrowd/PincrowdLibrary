<?php
/**
 *
 *
 * @category Pincrowd
 * @package Pincrowd_Mapper
 */
/**
 *
 * @category Pincrowd
 * @package Pincrowd_Mapper
 *
 */
abstract class Pincrowd_Model_Mapper_AbstractDbMapper extends Pincrowd_Model_AbstractMapper
{
    /**
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected static $_defaultDB;
    /**
     *
     * @var Zend_Db_Select
     */
    protected $_select;
    /**
     *
     * @param Zend_Db_Adapter_Abstract $defaultDB
     */
    public static function setDefaultDB(Zend_Db_Adapter_Abstract $defaultDB)
    {
        self::$_defaultDB = $defaultDB;
    }
    /**
     * @return Zend_Db_Adapter_Abstract
     */
    public static function getDefaultDB()
    {
        return self::$_defaultDB;
    }
    /**
     *
     * @return Pincrowd_Model_Mapper_AbstractDbMapper
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
     *
     * @return Pincrowd_Model_Mapper_AbstractDbMapper
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
     * @return Pincrowd_Model_Mapper_AbstractDbMapper
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
     * @return Pincrowd_Model_Mapper_AbstractDbMapper
     */
    protected function _getFields()
    {
        $fields = $this->getAttribute('fields');
        if($this->_select instanceof Zend_Db_Select && (bool) $fields ){
            $this->_select->from(self::DB_TABLE, $fields);
        } else {
            $this->_select->from(self::DB_TABLE);
        }
        return $this;
    }
    /**
     *
     * @return Pincrowd_Model_Mapper_AbstractDbMapper
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