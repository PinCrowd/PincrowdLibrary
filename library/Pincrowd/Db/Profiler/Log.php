<?php
/**
 *
 *
 * @author Robert Allen <rallen@Pincrowd.com>
 * @category Pincrowd
 * @package Pincrowd_Db
 * @subpackage Profiler
 */
/**
 * Example Factory Use:
 * <code>
 * $db = Zend_Db::factory('pdo', array());
 * $db->setProfiler(new Pincrowd_Db_Profiler_Log());
 * </code>
 *
 */
class Pincrowd_Db_Profiler_Log extends Zend_Db_Profiler
{
    /**
     * Zend_Log instance
     * @var Zend_Log
     */
    protected $_log;
    /**
     * counter of the total elapsed time
     * @var double
     */
    protected $_totalElapsedTime;
    /**
     *
     * @param boolean $enabled
     */
    public function __construct ($enabled = false)
    {
        parent::__construct($enabled);
        $this->_log = new Zend_Log();
        /* @todo this should be configurable. */
        $writer = new Zend_Log_Writer_Stream(dirname(APPLICATION_PATH) . '/log/sql.log');
        $this->_log->addWriter($writer);
    }
    /**
     * Intercept the query end and log the profiling data.
     *
     * @param  integer $queryId
     * @throws Zend_Db_Profiler_Exception
     * @return void
     */
    public function queryEnd ($queryId)
    {
        $state = parent::queryEnd($queryId);
        if (! $this->getEnabled() || $state == self::IGNORED) {
            return;
        }
        // get profile of the current query
        $profile = $this->getQueryProfile($queryId);
        // update totalElapsedTime counter
        $this->_totalElapsedTime += $profile->getElapsedSecs();
        // create the message to be logged
        $message = PHP_EOL . 'Elapsed Secs: ' .
         round($profile->getElapsedSecs(), 5) . PHP_EOL;
        $message .= 'Query: ' . $profile->getQuery() . PHP_EOL;
        $message .= 'Params: ' . print_r($profile->getQueryParams(), true) . PHP_EOL;
        // log the message as INFO message
        $this->_log->info($message);
    }
}
