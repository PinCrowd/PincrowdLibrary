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
 * @property      MongoId    $_id         public
 * @property      MongoDBRef $player      public
 * @property-read array      $frames      public
 * @property      array      $throws      public
 * @property      integer    $total       public
 * @property      MongoDate  $dateStarted public
 * @property      MongoDate  $dateEnded   public
 */
class Pincrowd_Model_Game_Game extends Pincrowd_Model_AbstractModel
{
    /**
     *
     * @var unknown_type
     */
    protected $_lastUpdatedField = 'dateEnded';
    /**
     *
     * @var unknown_type
     */
    protected $_halRel = 'game';
    /**
     *
     * @var unknown_type
     */
    protected $_halResource = '/games';
    /**
     *
     * @var unknown_type
     */
    protected $_identity = '_id';
    /**
     *
     * @var unknown_type
     */
    protected $_dbRef = false;
    /**
     *
     * @var Zend_Filter_Input
     */
    protected $_filter;
    /**
     *
     * @var unknown_type
     */
    protected $_validatorOptions = array(
        'player'      => 'Alnum',
        'total'       => 'Int'
    );
    protected $_filterOptions = array(
        'player'      => 'StringTrim',
        'total'       => 'StringTrim'
    );
    /**
     *
     * @var array
     */
    protected $_params = array(
        '_id'         => null,
        'player'      => null,
        'throws'      => array(),
        'frames'      => array(),
        'total'       => null,
        'dateStarted' => null,
        'dateEnded'   => null
    );
    public function __construct($params = array())
    {
        $this->_filter = new Zend_Filter_Input(
            $this->_filterOptions, $this->_validatorOptions, $params
        );
        parent::__construct($params);
    }
    /**
     *
     * @var array
     */
    protected $_types = array(
    );
    /**
     *
     * @see Pincrowd_Model_AbstractModel::__set()
     */
    public function __set($name, $value)
    {
        if(in_array($name, array('dateEnded','dateStarted')) && !$value instanceof MongoDate){
            $value = new MongoDate(strtotime($value));
        }
        if($name === '_id' && !$value instanceof MongoId){
            $value = new MongoId($value);
        }
        if($name === 'player'){
            if(!MongoDbRef::isRef($value)){
                $mongo = Pincrowd_Db_MongoAbstract::getDefaultMongo();
                $players = new Pincrowd_Model_Mapper_User_Users;
                $value = MongoDbRef::create(
                    'users', $value['username'], $mongo->selectDB($players->getDatabaseName())
                );
            }
        }
        if($name !== 'frames'){
            parent::__set($name, $value);
        }
    }
    /**
     *
     * @see Pincrowd_Model_AbstractModel::__get()
     */
    public function __get($name)
    {
        $value = parent::__get($name);
        if(in_array($name, array('dateEnded','dateStarted')) && $this->_params[$name] instanceof MongoDate){
            $value = date(DATE_W3C,$value->sec);
        }
        if($name === '_id'){
            $value = (string) $value;
        }
        if($name === 'player'){
            if(MongoDbRef::isRef($value) && !$this->_dbRef){
                $mongo = Pincrowd_Db_MongoAbstract::getDefaultMongo();
                $players = new Pincrowd_Model_Mapper_User_Users;
                $value = MongoDbRef::get($mongo->selectDB($players->getDatabaseName()), $value);
                $value['username'] = $value['_id'];
                unset($value['password'], $value['role'], $value['_id']);
            }
        }
        return $value;
    }
    /**
     *
     * @see Pincrowd_Model_AbstractModel::toArray()
     */
    public function toArray($dbRef = false)
    {
        $this->_dbRef = $dbRef;
        $this->calculateScore();
        return parent::toArray();
    }
    /**
     *
     * @param unknown_type $throw
     */
    public function pushThrow($throw)
    {
        array_push($this->_params['throw'], $throw);
    }
    protected function _getValue($value)
    {
        if(in_array($value, array('X','/'))){
            return 10;
        } else {
            return $value;
        }
    }
    protected function _buildFrames($throws)
    {
        $f = 0;
        for ($i = 0; $i < count($throws); $i++) {
            $score = null;
            ++$f;
            if($f < 10){
                if($throws[$i] == 'X'){
                    if(isset($throws[$i + 1]) && isset($throws[$i + 2])){
                        $score = 10 + $this->_getValue($throws[$i + 1]);
                        if($throws[$i + 2] == 'X'){
                            $score = $score + $this->_getValue($throws[$i + 2]);
                        }
                        elseif($throws[$i + 1] != 'X'){
                            $score = $score + ($this->_getValue($throws[$i + 2]) - $this->_getValue($throws[$i + 1]));
                        } else {
                            $score = $score + $this->_getValue($throws[$i + 2]) ;
                        }
                        $frames[] = array(
                            'frame' => $f,
                            'throw' => array('X'),
                            'score' => $score
                        );
                    }
                } else {
                    $throw = array($throws[$i]);
                    if(isset($throws[$i + 1])){
                        array_push($throw,$throws[$i + 1]);
                    }
                    if($throws[$i + 1] == '/' && isset($throws[$i + 2])){
                        $score = 10 + $this->_getValue($throws[$i + 2]);
                    } elseif(isset($throws[$i + 1])) {
                        $score = $this->_getValue($throws[$i + 1]);
                    }
                    $frames[] = array(
                        'frame' => $f,
                        'throw' => $throw,
                        'score' => $score
                    );
                    ++$i;
                }
            } elseif($f == 10){
                $throw = array($throws[$i]);
                if(isset($throws[$i + 1])){
                    array_push($throw,$throws[$i + 1]);
                }
                if(isset($throws[$i + 2])){
                    array_push($throw,$throws[$i + 2]);
                }
                if(isset($throws[$i + 1]) && isset($throws[$i + 2])){
                    $score = 10 + $this->_getValue($throws[$i + 1]) + $this->_getValue($throws[$i + 2]);
                }
                $frames[] = array(
                    'frame' => $f,
                    'throw' => $throw,
                    'score' => $score
                );
            }
        }
        return $frames;
    }
    /**
     *
     */
    public function calculateScore()
    {
        $total = 0;
        $throws = $this->_params['throws'];
        $frames = $this->_buildFrames($throws);
        foreach ($frames as $key => $frame) {
            $total = $total + $frame['score'];
            $frames[$key]['total'] = $total;
        }
        $this->_params['frames'] = $frames;
        $this->_params['total'] = $total;
    }
    /**
     *
     */
    public function setFilter()
    {

    }
}