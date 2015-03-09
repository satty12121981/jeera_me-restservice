<?php
namespace User;
use User\Model\UserTable;
use User\Model\UserProfileTable;
use User\Model\RecoveryemailsTable;
use User\Model\UserFriendTable;
use User\Model\UserFriendRequestTable;
use User\Model\TimezoneTable;
use User\Model\EmailmeTable;
use User\Model\NotifymeTable;
use User\Model\UserProfilePhotoTable;

class Module
{
	public function init()
    { 
        
		//$this ->mvcPreDispatch();
    }
    public function getAutoloaderConfig()
    { 
        return array(
            'Zend\Loader\ClassMapAutoloader' => array(
                __DIR__ . '/autoload_classmap.php',
            ),
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
					
					 
                ),
            ),
        );
    }
    
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
			
                'User\Model\UserTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserTable($dbAdapter);
                    return $table;
                },
				  
				 
				'User\Model\UserProfileTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserProfileTable($dbAdapter);
                    return $table;
                },
				'User\Model\RecoveryemailsTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new RecoveryemailsTable($dbAdapter);
                    return $table;
                },	
				'User\Model\UserFriendTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserFriendTable($dbAdapter);
                    return $table;
                },					
				'User\Model\UserFriendRequestTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserFriendRequestTable($dbAdapter);
                    return $table;
                },
				'User\Model\TimezoneTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new TimezoneTable($dbAdapter);
                    return $table;
                },
				'User\Model\EmailmeTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new EmailmeTable($dbAdapter);
                    return $table;
                },'User\Model\NotifymeTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new NotifymeTable($dbAdapter);
                    return $table;
                },
				'User\Model\UserProfilePhotoTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserProfilePhotoTable($dbAdapter);
                    return $table;
                },
			),
        );
    }    

    public function getConfig()
    { 
        return include __DIR__ . '/config/module.config.php';
    }
	
	public function mvcPreDispatch() {   
          $auth = new Authentication();
        return $auth->preDispatch();
    }

}