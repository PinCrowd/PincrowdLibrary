<?php
require_once 'Pincrowd/Db/MongoAbstract.php';
class Pincrowd_Application_Resource_Mongo extends
    Zend_Application_Resource_ResourceAbstract
{
    /**
     *
     *
     * @var Mongo
     */
    protected $_mongo;
    public function init()
    {
        $options = $this->getOptions();
        $this->_mongo = new Mongo($options['server'], $options['options']);
        Pincrowd_Db_MongoAbstract::setMongo($this->_mongo);
        if(isset($options['options']['database'])){
            Pincrowd_Db_MongoAbstract::setMongoDB(
                $this->_mongo->$options['options']['database']
            );
        }
    }
    /**
     * @return Mongo
     */
    public function getMongo()
    {
        return $this->_mongo;
    }
}