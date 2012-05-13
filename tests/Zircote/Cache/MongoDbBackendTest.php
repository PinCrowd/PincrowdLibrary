<?php
/**
 * @category   Pincrowd
 * @package    Pincrowd_Cache
 * @subpackage UnitTests
 */

/**
 * Zend_Cache
 */
require_once 'Zend/Cache.php';
require_once 'Pincrowd/Cache/Backend/MongoDb.php';

/**
 * @category   Zend
 * @package    Zend_Cache
 * @subpackage UnitTests
 * @group      Zend_Cache
 */
class Pincrowd_Cache_MongodbBackendTest extends PHPUnit_Framework_TestCase {

    protected $_instance;
    protected $_className;
    protected $_root;

    public function __construct($name = null, array $data = array(), $dataName = '')
    {
        parent::__construct($name, $data, $dataName);
        $this->_className = 'Pincrowd_Cache_Backend_MongoDb';
        $this->_root = __DIR__;
        date_default_timezone_set('UTC');
    }

    public function setUp($notag = false)
    {
        $this->_instance = new Pincrowd_Cache_Backend_MongoDb(
            array('database_name' => 'zend_cache',
            'collection' => 'cache')
        );
        $this->_instance->setDirectives(array('logging' => true));
        if ($notag) {
            $this->_instance->save('bar : data to cache', 'bar');
            $this->_instance->save('bar2 : data to cache', 'bar2');
            $this->_instance->save('bar3 : data to cache', 'bar3');
        } else {
            $this->_instance->save('bar : data to cache', 'bar', array('tag3', 'tag4'));
            $this->_instance->save('bar2 : data to cache', 'bar2', array('tag3', 'tag1'));
            $this->_instance->save('bar3 : data to cache', 'bar3', array('tag2', 'tag3'));
        }
        $this->_capabilities = $this->_instance->getCapabilities();
        parent::setUp($notag);
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->_instance);
    }

    public function testConstructorCorrectCall()
    {
        $test = new Pincrowd_Cache_Backend_MongoDb(
            array('database_name' => 'zend_cache',
            'collection' => 'cache')
        );
    }

    public function testConstructorWithNoCollectionSpecified()
    {
        try {
            $test = new Pincrowd_Cache_Backend_MongoDb();
        } catch (Zend_Cache_Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown');
    }

    public function testCleanModeAll()
    {
        $this->_instance = new Pincrowd_Cache_Backend_MongoDb(
            array('database_name' => 'zend_cache',
            'collection' => 'cache')
        );
        parent::setUp();
        $this->assertTrue($this->_instance->clean('all'));
        $this->assertFalse($this->_instance->test('bar'));
        $this->assertFalse($this->_instance->test('bar2'));
    }

    public function testRemoveCorrectCallWithVacuum()
    {
        $this->_instance = new Pincrowd_Cache_Backend_MongoDb(
            array('database_name' => 'zend_cache',
            'collection' => 'cache')
        );
        parent::setUp();

        $this->assertTrue($this->_instance->remove('bar'));
        $this->assertFalse($this->_instance->test('bar'));
        $this->assertFalse($this->_instance->remove('barbar'));
        $this->assertFalse($this->_instance->test('barbar'));
    }

    /**
     * @group ZF-11640
     */
    public function testRemoveCorrectCallWithVacuumOnMemoryDb()
    {
        $this->_instance = new Pincrowd_Cache_Backend_MongoDb(
            array('database_name' => 'zend_cache',
            'collection' => 'cache')
        );
        parent::setUp();

        $this->assertGreaterThan(0, $this->_instance->test('bar2'));

        $this->assertTrue($this->_instance->remove('bar'));
        $this->assertFalse($this->_instance->test('bar'));

        $this->assertGreaterThan(0, $this->_instance->test('bar2'));
    }
    public function testExplicitMongoInstanceSetting()
    {
        $this->_instance = new Pincrowd_Cache_Backend_MongoDb(
            array('database_name' => 'zend_cache',
            'collection' => 'cache')
        );
        parent::setUp();
        $db = new MongoDb(new Mongo(), 'zend_cache');
        $this->_instance->setDatabase($db);
        $collection = $db->selectCollection('cache');
        $this->_instance->setCollection($collection);
        $this->assertInstanceOf('MongoDb', $this->_instance->getDatabase());
        $this->assertInstanceOf('MongoCollection', $this->_instance->getCollection());
        $this->assertGreaterThan(0, $this->_instance->test('bar2'));
        $this->assertTrue($this->_instance->remove('bar'));
        $this->assertFalse($this->_instance->test('bar'));

        $this->assertGreaterThan(0, $this->_instance->test('bar2'));
    }

    public function testConstructorBadOption()
    {
        try {
            $class = $this->_className;
            $test = new $class(array(1 => 'bar'));
        } catch (Zend_Cache_Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown');
    }

    public function testSetDirectivesCorrectCall()
    {
        $this->_instance->setDirectives(array('lifetime' => 3600, 'logging' => true));
    }

    public function testSetDirectivesBadArgument()
    {
        try {
            $this->_instance->setDirectives('foo');
        } catch (Zend_Cache_Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown');
    }

    public function testSetDirectivesBadDirective()
    {
        // A bad directive (not known by a specific backend) is possible
        // => so no exception here
        $this->_instance->setDirectives(array('foo' => true, 'lifetime' => 3600));
    }

    public function testSetDirectivesBadDirective2()
    {
        try {
            $this->_instance->setDirectives(array('foo' => true, 12 => 3600));
        } catch (Zend_Cache_Exception $e) {
            return;
        }
        $this->fail('Zend_Cache_Exception was expected but not thrown');
    }

    public function testSaveCorrectCall()
    {
        $res = $this->_instance->save('data to cache', 'foo', array('tag1', 'tag2'));
        $this->assertTrue($res);
    }

    public function testSaveWithNullLifeTime()
    {
        $this->_instance->setDirectives(array('lifetime' => null));
        $res = $this->_instance->save('data to cache', 'foo', array('tag1', 'tag2'));
        $this->assertTrue($res);
    }

    public function testSaveWithSpecificLifeTime()
    {
        $this->_instance->setDirectives(array('lifetime' => 3600));
        $res = $this->_instance->save('data to cache', 'foo', array('tag1', 'tag2'), 10);
        $this->assertTrue($res);
    }

    public function testRemoveCorrectCall()
    {
        $this->assertTrue($this->_instance->remove('bar'));
        $this->assertFalse($this->_instance->test('bar'));
        $this->assertFalse($this->_instance->remove('barbar'));
        $this->assertFalse($this->_instance->test('barbar'));
    }

    public function testTestWithAnExistingCacheId()
    {
        $res = $this->_instance->test('bar');
        if (!$res) {
            $this->fail('test() return false');
        }
        if (!($res > 999999)) {
            $this->fail('test() return an incorrect integer');
        }
        return;
    }

    public function testTestWithANonExistingCacheId()
    {
        $this->assertFalse($this->_instance->test('barbar'));
    }

    public function testTestWithAnExistingCacheIdAndANullLifeTime()
    {
        $this->_instance->setDirectives(array('lifetime' => null));
        $res = $this->_instance->test('bar');
        if (!$res) {
            $this->fail('test() return false');
        }
        if (!($res > 999999)) {
            $this->fail('test() return an incorrect integer');
        }
        return;
    }

    public function testGetWithANonExistingCacheId()
    {
        $this->assertFalse($this->_instance->load('barbar'));
    }

    public function testGetWithAnExistingCacheId()
    {
        $this->assertEquals('bar : data to cache', $this->_instance->load('bar'));
    }

    public function testGetWithAnExistingCacheIdAndUTFCharacters()
    {
        $data = '"""""' . "'" . '\n' . 'ééééé';
        $this->_instance->save($data, 'foo');
        $this->assertEquals($data, $this->_instance->load('foo'));
    }

    public function testGetWithAnExpiredCacheId()
    {
        $this->_instance->___expire('bar');
        $this->_instance->setDirectives(array('lifetime' => -1));
        $this->assertFalse($this->_instance->load('bar'));
        $this->assertEquals('bar : data to cache', $this->_instance->load('bar', true));
    }

    public function testCleanModeOld()
    {
        $this->_instance->___expire('bar2');
        $this->assertTrue($this->_instance->clean('old'));
        $this->assertTrue($this->_instance->test('bar') > 999999);
        $this->assertFalse($this->_instance->test('bar2'));
    }

    public function testCleanModeMatchingTags()
    {
        $this->assertTrue($this->_instance->clean('matchingTag', array('tag3')));
        $this->assertFalse($this->_instance->test('bar'));
        $this->assertFalse($this->_instance->test('bar2'));
    }

    public function testCleanModeMatchingTags2()
    {
        $this->assertTrue($this->_instance->clean('matchingTag', array('tag3', 'tag4')));
        $this->assertFalse($this->_instance->test('bar'));
        $this->assertTrue($this->_instance->test('bar2') > 999999);
    }

    public function testCleanModeNotMatchingTags()
    {
        $this->assertTrue($this->_instance->clean('notMatchingTag', array('tag3')));
        $this->assertTrue($this->_instance->test('bar') > 999999);
        $this->assertTrue($this->_instance->test('bar2') > 999999);
    }

    public function testCleanModeNotMatchingTags2()
    {
        $this->assertTrue($this->_instance->clean('notMatchingTag', array('tag4')));
        $this->assertTrue($this->_instance->test('bar') > 999999);
        $this->assertFalse($this->_instance->test('bar2'));
    }

    public function testCleanModeNotMatchingTags3()
    {
        $this->assertTrue($this->_instance->clean('notMatchingTag', array('tag4', 'tag1')));
        $this->assertTrue($this->_instance->test('bar') > 999999);
        $this->assertTrue($this->_instance->test('bar2') > 999999);
        $this->assertFalse($this->_instance->test('bar3'));
    }

    public function testGetFillingPercentage()
    {
        $res = $this->_instance->getFillingPercentage();
        $this->assertTrue(is_integer($res));
        $this->assertTrue($res >= 0);
        $this->assertTrue($res <= 100);
    }

    public function testGetFillingPercentageOnEmptyBackend()
    {
        $this->_instance->setDirectives(array('logging' => false)); // ???
        $this->_instance->clean(Zend_Cache::CLEANING_MODE_ALL);
        $res = $this->_instance->getFillingPercentage();
        $this->_instance->setDirectives(array('logging' => true)); // ???
        $this->assertTrue(is_integer($res));
        $this->assertTrue($res >= 0);
        $this->assertTrue($res <= 100);
    }

    public function testGetIds()
    {
        if (!($this->_capabilities['get_list'])) {
            # unsupported by this backend
            return;
        }
        $res = $this->_instance->getIds();
        $this->assertTrue(count($res) == 3);
        $this->assertTrue(in_array('bar', $res));
        $this->assertTrue(in_array('bar2', $res));
        $this->assertTrue(in_array('bar3', $res));
    }

    public function testGetTags()
    {
        if (!($this->_capabilities['tags'])) {
            # unsupported by this backend
            return;
        }
        $res = $this->_instance->getTags();
        $this->assertTrue(count($res) == 4);
        $this->assertTrue(in_array('tag1', $res));
        $this->assertTrue(in_array('tag2', $res));
        $this->assertTrue(in_array('tag3', $res));
        $this->assertTrue(in_array('tag4', $res));
    }

    public function testGetIdsMatchingTags()
    {
        if (!($this->_capabilities['tags'])) {
            # unsupported by this backend
            return;
        }
        $res = $this->_instance->getIdsMatchingTags(array('tag3'));
        $this->assertTrue(count($res) == 3);
        $this->assertTrue(in_array('bar', $res));
        $this->assertTrue(in_array('bar2', $res));
        $this->assertTrue(in_array('bar3', $res));
    }

    public function testGetIdsMatchingTags2()
    {
        if (!($this->_capabilities['tags'])) {
            # unsupported by this backend
            return;
        }
        $res = $this->_instance->getIdsMatchingTags(array('tag2'));
        $this->assertTrue(count($res) == 1);
        $this->assertTrue(in_array('bar3', $res));
    }

    public function testGetIdsMatchingTags3()
    {
        if (!($this->_capabilities['tags'])) {
            # unsupported by this backend
            return;
        }
        $res = $this->_instance->getIdsMatchingTags(array('tag9999'));
        $this->assertTrue(count($res) == 0);
    }


    public function testGetIdsMatchingTags4()
    {
        if (!($this->_capabilities['tags'])) {
            # unsupported by this backend
            return;
        }
        $res = $this->_instance->getIdsMatchingTags(array('tag3', 'tag4'));
        $this->assertTrue(count($res) == 1);
        $this->assertTrue(in_array('bar', $res));
    }

    public function testGetIdsNotMatchingTags()
    {
        if (!($this->_capabilities['tags'])) {
            # unsupported by this backend
            return;
        }
        $res = $this->_instance->getIdsNotMatchingTags(array('tag3'));
        $this->assertTrue(count($res) == 0);
    }

    public function testGetIdsNotMatchingTags2()
    {
        if (!($this->_capabilities['tags'])) {
            # unsupported by this backend
            return;
        }
        $res = $this->_instance->getIdsNotMatchingTags(array('tag1'));
        $this->assertTrue(count($res) == 2);
        $this->assertTrue(in_array('bar', $res));
        $this->assertTrue(in_array('bar3', $res));
    }

    public function testGetIdsNotMatchingTags3()
    {
        if (!($this->_capabilities['tags'])) {
            # unsupported by this backend
            return;
        }
        $res = $this->_instance->getIdsNotMatchingTags(array('tag1', 'tag4'));
        $this->assertTrue(count($res) == 1);
        $this->assertTrue(in_array('bar3', $res));
    }

    public function testGetMetadatas($notag = false)
    {
        $res = $this->_instance->getMetadatas('bar');
        $this->assertTrue(isset($res['tags']));
        $this->assertTrue(isset($res['mtime']));
        $this->assertTrue(isset($res['expire']));
        if ($notag) {
            $this->assertTrue(count($res['tags']) == 0);
        } else {
            $this->assertTrue(count($res['tags']) == 2);
            $this->assertTrue(in_array('tag3', $res['tags']));
            $this->assertTrue(in_array('tag4', $res['tags']));
        }
        $this->assertTrue($res['expire'] > time());
        $this->assertTrue($res['mtime'] <= time());
    }

    public function testTouch()
    {
        $res = $this->_instance->getMetadatas('bar');
        $bool = $this->_instance->touch('bar', 30);
        $this->assertTrue($bool);
        $res2 = $this->_instance->getMetadatas('bar');
        $this->assertTrue(($res2['expire'] - $res['expire']) == 30);
        $this->assertTrue(($res2['mtime'] >= $res['mtime']));
    }

    public function testGetCapabilities()
    {
        $res = $this->_instance->getCapabilities();
        $this->assertTrue(isset($res['tags']));
        $this->assertTrue(isset($res['automatic_cleaning']));
        $this->assertTrue(isset($res['expired_read']));
        $this->assertTrue(isset($res['priority']));
        $this->assertTrue(isset($res['infinite_lifetime']));
        $this->assertTrue(isset($res['get_list']));
    }

}



