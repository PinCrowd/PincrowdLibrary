<?php
class Pincrowd_Model_Mapper_Match_Matches extends Pincrowd_Db_MongoAbstract
{
    protected $_collectionName = 'matches';
    /**
     *
     * @param Pincrowd_Model_Match $match
     * @return Pincrowd_Model_Match
     */
    public function saveMatch(Pincrowd_Model_Match_Match $match)
    {
        return new Pincrowd_Model_Match_Match($this->insert($match->toArray()));
    }
    /**
     *
     * @param Pincrowd_Model_Match $match
     * @return Pincrowd_Model_Match
     */
    public function updateMatch(Pincrowd_Model_Match $match)
    {
        return new Pincrowd_Model_Match_Match($this->save($match->toArray()));
    }
    public function fetchMatches()
    {
        return (array) $this->find(array());
    }
    public function deleteMatch(Pincrowd_Model_Match $match)
    {
        return $this->remove(
            $match->_id,array('justOne' => true)
        );
    }

    protected function _getFields()
    {

    }
}