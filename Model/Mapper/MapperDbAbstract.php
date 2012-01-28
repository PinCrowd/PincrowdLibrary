<?php
abstract class Pincrowd_Model_Mapper_MapperDbAbstract
{
    /**
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;
    /**
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @return void
     */
    public function setDb (Zend_Db_Adapter_Abstract $db)
    {
        $this->_db = $db;
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
}