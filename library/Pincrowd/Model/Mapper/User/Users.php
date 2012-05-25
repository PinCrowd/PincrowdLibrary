<?php
/**
 *
 *
 * @author zircote
 *
 */
class Pincrowd_Model_Mapper_User_Users extends Pincrowd_Db_MongoAbstract
{
    protected $_databaseName = 'pincrowd';
    protected $_collectionName = 'users';
    /**
     * @todo not decided I like the whole pass by reference game yet or not.
     * I find it generally a hack and unintuitive to say the least adding a level
     * of obsfucation I rather avoid for maintainability.
     *
     * @param Pincrowd_Model_User $player
     */
    public function getUser(Pincrowd_Model_User &$player)
    {
        $result = $this->getMongoDB($this->_databaseName)
            ->users->findOne(array('_id' => $player->_id));
        $player->fromArray($result);
    }
    protected function _getFields()
    {

    }
}