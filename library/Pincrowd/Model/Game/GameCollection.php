<?php
/**
 *
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
 * @property MongoId          $_id         protected
 * @property MongoDBRef       $player      protected
 * @property Gaz_Model_Frames $frames      public
 * @property integer          $total       protected
 * @property MongoDate        $dateStarted protected
 * @property MongoDate        $dateEnded   protected
 */
class Pincrowd_Model_Game_GameCollection extends Pincrowd_Model_AbstractModelCollection
{
    /**
     *
     * @var string
     */
    protected $_halResource = '/games';

    /**
     * This will provide assurances that the appended item is of the corrrect
     * model type.
     * Example:
     * <code>
     * public function append(Pincrowd_Model_SomeModel $model)
     * {
     *     parent::append($model);
     * }
     * </code>
     * @param Pincrowd_Model_Game $item
     */
    public function append(Pincrowd_Model_Game_Game $item){
        $this->setIsLoaded(true);
        parent::append($item);
    }
}