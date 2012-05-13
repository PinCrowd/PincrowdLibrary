<?php
/**
 * @category Pincrowd
 * @package Pincrowd_Rest
 * @subpackage UnitTests
 */
/**
 * @category Pincrowd
 * @package Pincrowd_Rest
 * @subpackage UnitTests
 * @group Pincrowd_Rest_Cache_PageTest
 */
class Pincrowd_Rest_Cache_PageTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Pincrowd_Rest_Cache_Page
     */
    protected $object;
    protected $_testcontent = 'TEST_CONTENT';
    protected $_cacheId = '7659026c6e042ad725d30687147f36244c07569b';
    protected $_etag = '782e42e6725c23a2c78f27d0fcd2148f';

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $request = new Zend_Controller_Request_HttpTestCase();
        $request->setParams(
            array(
                'module' => 'v1',
                'controller' => 'leadresponder_route',
                'controllerResource' => 'leadresponder',
                'id' => 46
            )
        );
        $response = new Zend_Controller_Response_HttpTestCase();
        $response->setBody($this->_testcontent);
        $this->object = new Pincrowd_Rest_Cache_Page(array(
            'cache' => true,
            'specific_lifetime' => 3600,
            'tags' => array(),
            'priority' => null,
            'dnd' => false,
            'debug_header' => true
        ));
        $this->object->setRequest($request)->setResponse($response);
        $this->object->setBackend(
            new Pincrowd_Cache_Backend_MongoDb(
                array('database_name' => 'zend_cache','collection' => 'etag_cache')
            )
        );
        $this->object->__flush();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->object = null;
    }
    /**
     * @todo   Implement test__flush().
     */
    public function test__flush()
    {
        $this->assertEquals($this->_testcontent, $this->object->_flush());
    }
    public function testETagMatch()
    {
        $this->object->getRequest()->setHeader('if-none-match', $this->_etag);
        $this->object->start($this->_cacheId);
        $this->assertEquals(304, $this->object->getResponse()->getHttpResponseCode());
    }
    /**
     * @covers Pincrowd_Rest_Cache_Page::start
     * @todo   Implement testStart().
     */
    public function testStart()
    {
        $this->assertTrue(
            $this->object->start($this->_cacheId)
        );
    }
    /**
     * @todo   Implement testStart().
     */
    public function testStart2()
    {
        $this->assertTrue(
            $this->object->start()
        );
    }

    /**
     * @todo   Implement testCancel().
     */
    public function testCancel()
    {
        $this->object->cancel();
        $this->assertEquals($this->_testcontent, $this->object->_flush());
        foreach ($this->object->getResponse()->getHeaders() as $header) {
            $this->assertNotEquals('etag', strtolower($header['name']));
        }
    }

    /**
     * @todo   Implement test_flush().
     */
    public function test_flush()
    {
        $this->object->__flush();
    }

    /**
     * @todo   Implement testGetItemTags().
     */
    public function testGetItemTags()
    {
        $expected = 'test_tag';
        $this->object->setItemTags($expected);
        $this->object->setItemTags(array('5433','321'));
        $this->assertTrue(in_array($expected, $this->object->getItemTags()));
        $this->assertTrue(in_array('321', $this->object->getItemTags()));
        $this->assertTrue(in_array('5433', $this->object->getItemTags()));
        $this->assertFalse(in_array('678', $this->object->getItemTags()));
    }

    /**
     * @todo   Implement testTestETag().
     */
    public function testTestETag()
    {
        $this->assertTrue($this->object->testETag($this->_etag));
    }

    /**
     * @todo   Implement testGetRequest().
     */
    public function testGetRequest()
    {
        $this->assertInstanceOf(
            'Zend_Controller_Request_HttpTestCase', $this->object->getRequest()
        );
    }

    /**
     * @todo   Implement testGetResponse().
     */
    public function testGetResponse()
    {
        $this->assertInstanceOf(
            'Zend_Controller_Response_HttpTestCase', $this->object->getResponse()
        );
    }
}
