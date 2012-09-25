<?php
namespace Pincrowd\Model;

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
 * @Document
 */
class Game
{

    /**
     * @var $mixed
     * @ReferenceOne(targetDocument="Game")
     */
    protected $gameId;
    /**
     * @var
     * @ReferenceOne(targetDocument="Lane")
     */
    protected $laneId;
    /**
     * @var
     * @ReferenceOne(targetDocument="Match")
     */
    protected $match;
    /**
     * @var
     * @ReferenceOne(targetDocument="User")
     */
    protected $username;
    /**
     * @var array
     * @EmbedMany
     */
    protected $frames = array();
    /**
     * @var array
     * @EmbedMany
     */
    protected $throws = array();
    /**
     * @var
     * @Field(type="integer")
     */
    protected $score;
    /**
     * @var
     * @Field(type="date")
     */
    protected $dateStarted;
    /**
     * @var
     * @Field(type="date")
     */
    protected $dateEnded;

    /**
     * @param $value
     *
     * @return int
     */
    protected function getValue($value)
    {
        if(in_array($value, array('X','/'))){
            return 10;
        } else {
            return $value;
        }
    }

    /**
     * @param $throws
     *
     * @return array
     */
    protected function buildFrames($throws)
    {
        $f = 0;
        for ($i = 0; $i < count($throws); $i++) {
            $score = null;
            ++$f;
            if($f < 10){
                if($throws[$i] == 'X'){
                    if(isset($throws[$i + 1]) && isset($throws[$i + 2])){
                        $score = 10 + $this->getValue($throws[$i + 1]);
                        if($throws[$i + 2] == 'X'){
                            $score = $score + $this->getValue($throws[$i + 2]);
                        }
                        elseif($throws[$i + 1] != 'X'){
                            $score = $score + ($this->getValue($throws[$i + 2]) - $this->getValue($throws[$i + 1]));
                        } else {
                            $score = $score + $this->getValue($throws[$i + 2]) ;
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
                        arraypush($throw,$throws[$i + 1]);
                    }
                    if($throws[$i + 1] == '/' && isset($throws[$i + 2])){
                        $score = 10 + $this->getValue($throws[$i + 2]);
                    } elseif(isset($throws[$i + 1])) {
                        $score = $this->getValue($throws[$i + 1]);
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
                    arraypush($throw,$throws[$i + 1]);
                }
                if(isset($throws[$i + 2])){
                    arraypush($throw,$throws[$i + 2]);
                }
                if(isset($throws[$i + 1]) && isset($throws[$i + 2])){
                    $score = 10 + $this->getValue($throws[$i + 1]) + $this->getValue($throws[$i + 2]);
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
        $throws = $this->params['throws'];
        $frames = $this->buildFrames($throws);
        foreach ($frames as $key => $frame) {
            $total = $total + $frame['score'];
            $frames[$key]['total'] = $total;
        }
        $this->params['frames'] = $frames;
        $this->params['total'] = $total;
    }

}
