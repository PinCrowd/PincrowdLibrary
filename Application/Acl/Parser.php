<?php
/**
 * Parses all Controller and generates the ACL lists examines the @aclRoleAllow 
 * annotation for declared roles.
 * 
 * @author zircote
 *
 */
class Pincrowd_Application_Acl_Parser
{
    /**
     * 
     * 
     * @var Zend_Controller_Front
     */
    protected $_frontController;
    
    public function __construct(Zend_Controller_Front $frontController)
    {
        $this->_frontController = $frontController;
    }
    /**
     * 
     * 
     * @return array
     */
    public function run()
    {
        $files = array();
        foreach ($this->_frontController->getControllerDirectory() as $module => $directory) {
            $dir = new DirectoryIterator($directory);
            foreach ($dir as $fileInfo) {
                if(!$fileInfo->isDot()){
                    if($module !=  'default'){
                        $prefix = ucfirst($module) . '_' ;
                    } else {
                        $prefix = null;
                    }
                    $class = str_replace('.php','',$prefix . $fileInfo->getFileName());
                    require_once ($directory . DIRECTORY_SEPARATOR . $fileInfo->getFileName());
                    $controller = strtolower(str_replace('Controller', '', str_replace('.php','',$fileInfo->getFileName())));
                    $files[$module][$controller] = $this->_reflect($class);
                }
            }
        }
        return $files;
    }
    /**
     * 
     * 
     * @param string $class (classname for introspection)
     */
    protected function _reflect($class)
    {
        $response = array();
        $reflect = new Zend_Reflection_Class($class);
        $methods = $reflect->getMethods(ReflectionMethod::IS_PUBLIC);
        /* @var $method Zend_Reflection_Method */
        foreach ($methods as $method) {
            if(substr($method->name,-6) == 'Action'){
                $roles = array();
                if($method->getDocComment()){
                    $docblock = new Zend_Reflection_Docblock($method->getDocComment());
                    /* @var $tag Zend_Reflection_Docblock_Tag */
                    foreach ($docblock->getTags('aclRoleAllow') as $tag) {
                        $roles['allow'][] = $tag->getDescription();
                    }
                    foreach ($docblock->getTags('aclRoleDeny') as $tag) {
                        $roles['deny'][] = $tag->getDescription();
                    }
                } else {
                    $roles['allow'][] = 'admin';
                }
                $response[substr($method->name,0,-6)] = $roles;
            }
        }
        return $response;
    }
    /**
     * <code>
     * $parser = new Pincrowd_Application_Acl_Parser($this->getFrontController()); 
     * $acl = new Zend_Acl();
     * $acl->addRole('anonymous')
     *     ->addRole('banned')
     *     ->addRole('admin','anonymous');
     * $roleList = $parser->getAcl($acl);
     * print_r($roleList);
     * </code>
     * 
     * @param Zend_Acl $acl
     */
    public function getAcl(Zend_Acl $acl)
    {
        
        $files = $this->run();
        $roleList = array();
        foreach ($files as $module => $controller) {
            foreach ($controller as $conName => $action) {
                foreach ($action as $act => $roles) {
                    $roleList["{$module}:{$conName}:{$act}"] = $roles;
                }
            };
        }
        foreach ($roleList as $resource => $roles) {
            $acl->addResource($resource);
            if(isset($roles['allow'])){
                $acl->allow($roles['allow'], $resource);
            }
            if(isset($roles['deny'])){
                $acl->deny($roles['deny'], $resource);
            }
        }
        return $acl;
    }
}
