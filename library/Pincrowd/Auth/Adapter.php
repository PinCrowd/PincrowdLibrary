<?php
/**
 * 
 * 
 * @author zircote
 *
 */
class Pincrowd_Auth_Adapter implements Zend_Auth_Adapter_Interface
{
    /**
     * 
     * 
     * @var string
     */
    protected $_identity;
    /**
     * 
     * 
     * @var string
     */
    protected $_credential;
    /**
     * 
     * 
     * @var array
     */
    protected $_identityResult;
    /**
     * (non-PHPdoc)
     * @see Zend_Auth_Adapter_Interface::authenticate()
     */
    public function authenticate ()
    {
        $user = new Pincrowd_Model_User(array('_id' => $this->_identity));
        $playerMapper = new Pincrowd_Model_Mapper_Players();
        $playerMapper->getUser($user);
        $playerMapper = null;
        $crypt = new Pincrowd_Auth_Credential();
        if (($user->_id == $this->getIdentity()) &&
         $crypt->isValid($this->getCredential(), $user->password)) {
            $this->_identityResult = $user;
            $code = Zend_Auth_Result::SUCCESS;
        } else {
            $this->_identity = null;
            $code = Zend_Auth_Result::FAILURE;
        }
        return new Zend_Auth_Result($code, $this->_identity);
    }
    /**
     * 
     * 
     * @param string $credential
     * @return Pincrowd_Auth_Adapter
     */
    public function setCredential ($credential)
    {
        $this->_credential = $credential;
        return $this;
    }
    public function getCredential ()
    {
        return $this->_credential;
    }
    /**
     * @return array
     */
    public function getIdentityResult ()
    {
        return $this->_identityResult;
    }
    /**
     * 
     * 
     * @param string $identity
     * @return Pincrowd_Auth_Adapter
     */
    public function setIdentity ($identity)
    {
        $this->_identity = $identity;
        return $this;
    }
    /**
     * 
     * 
     * @return string
     */
    public function getIdentity ()
    {
        return $this->_identity;
    }
}
