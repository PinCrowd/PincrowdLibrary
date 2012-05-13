<?php
/**
 *
 *
 * @author Robert Allen <rallen@Pincrowd.com>
 * @category Pincrowd
 * @package Pincrowd_Db
 * @subpackage Schema
 */
/**
 *
 *
 */
abstract class Pincrowd_Db_Schema_AbstractChange
{
    /**
     *
     * @var integer
     */
    protected $_timestamp;
    /**
     *
     * @var Zend_Db_Adapter_Abstract
     */
    protected $_db;
    /**
     *
     * @var string
     */
    protected $_tablePrefix;
    /**
     *
     * @var string
     */
    protected $_dir;
    /**
     *
     * @param Zend_Db_Adapter_Abstract $db
     * @param string $tablePrefix
     */
    function __construct (Zend_Db_Adapter_Abstract $db, $tablePrefix = '')
    {
        $this->_db = $db;
        $this->_tablePrefix = $tablePrefix;
    }
    /**
     *
     * @param string $dir
     * @return Pincrowd_Db_Schema_AbstractChange
     */
    public function setDirectory($dir)
    {
        $this->_dir = $dir;
        return $this;
    }
    /**
     *
     * @throws Pincrowd_Db_Schema_Exception
     */
    protected function _processDataXml()
    {
        $dataFile = realpath(
            $this->_dir . DIRECTORY_SEPARATOR . 'data' .
            DIRECTORY_SEPARATOR . $this->_timestamp . '.xml'
        );
        if($dataFile){
            libxml_use_internal_errors(true);
            $data = simplexml_load_file($dataFile);
            if(!$data){
                $errors = null;
                foreach(libxml_get_errors() as $error) {
                    $errors .= PHP_EOL  . $error->message;
                }
                throw new Pincrowd_Db_Schema_Exception($errors);
            }
            return $this->_parseXmlSet($data);
        }
    }
    /**
     *
     * @param SimpleXMLElement $xml
     * @return array
     */
    protected function _parseXmlSet(SimpleXMLElement $xml)
    {
        $result = array();
        foreach ($xml as $record){
            $result[] = (array) $record;
        }
        return $result;
    }
    /**
     * Changes to be applied in this change
     */
    abstract function up ();
    /**
     * Rollback changes made in up()
     */
    abstract function down ();

}