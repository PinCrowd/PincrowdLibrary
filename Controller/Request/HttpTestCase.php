<?php

class Pincrowd_Controller_Request_HttpTestCase extends Zend_Controller_Request_HttpTestCase
{
    /**
     * Valid request method types
     * @var array
     */
    protected $_validMethodTypes = array(
        'DELETE',
        'GET',
        'HEAD',
        'OPTIONS',
        'POST',
        'PUT',
        'TRACE',
        'PATCH'
    );
}