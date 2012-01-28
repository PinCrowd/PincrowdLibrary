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
    public function findById($id, $fields = null)
    {
        $query = array('_id' => new MongoId($id));
        return self::getMongoDB()
            ->__get($this->_name)->findOne($query, $fields);
    }
    public function find($query)
    {
        return self::getMongoDB()->__get($this->_name)
            ->find($query);
    }
}
