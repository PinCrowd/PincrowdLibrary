<?php
/**
 *
 *
 *
 * @category   Pincrowd
 * @package    Library
 * @subpackage Model
 */
/**
 *
 *
 *
 * @category   Pincrowd
 * @package    Library
 * @subpackage Model
 */
class Pincrowd_Model_Match_MatchCollection extends ArrayObject
{
    public function __construct($array)
    {
        $this->fromArray($array);
    }
    public function append(Pincrowd_Model_Match $match)
    {
        parent::append($match);
    }
    public function fromArray(array $array)
    {
        foreach ($array as $datum) {
            if(!$datum instanceof Pincrowd_Model_Match_Match){
                $datum = new Pincrowd_Model_Match_Match($datum);
            }
            $this->append($datum);
        }
    }
    public function toArray()
    {
        return $this->getArrayCopy();
    }
}