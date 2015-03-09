<?php
namespace Groups;
use Groups\Model\GroupsTable;
use Tag\Model\GroupTagTable;
use Groups\Model\UserGroupPermissionsTable;
use Groups\Model\GroupSettingsTable;
use Groups\Model\UserGroupJoiningRequestTable;
use Groups\Model\GroupJoiningQuestionnaireTable;
use Groups\Model\GroupQuestionnaireAnswersTable;
use Groups\Model\GroupQuestionnaireOptionsTable;
use Groups\Model\GroupPhotoTable;
use Groups\Model\UserGroupTable;
use Groups\Model\GroupMediaTable;
use Groups\Model\UserGroupJoiningInvitationTable;
class Module
{
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

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
	public function getServiceConfig()
    {
		return array(
            'factories' => array(
                  
				'Groups\Model\GroupsTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GroupsTable($dbAdapter);
                    return $table;
                }, 
				'Tag\Model\GroupTagTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GroupTagTable($dbAdapter);
					return $table;
                },
				'Groups\Model\UserGroupPermissionsTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserGroupPermissionsTable($dbAdapter);
					return $table;
                },
				'Groups\Model\GroupSettingsTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GroupSettingsTable($dbAdapter);
					return $table;
                },
				'Groups\Model\UserGroupJoiningRequestTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserGroupJoiningRequestTable($dbAdapter);
					return $table;
                }, 
				'Groups\Model\GroupJoiningQuestionnaireTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GroupJoiningQuestionnaireTable($dbAdapter);
					return $table;
                },
				'Groups\Model\GroupQuestionnaireAnswersTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GroupQuestionnaireAnswersTable($dbAdapter);
					return $table;
                },
				'Groups\Model\GroupQuestionnaireOptionsTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GroupQuestionnaireOptionsTable($dbAdapter);
					return $table;
                },
				'Groups\Model\GroupPhotoTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GroupPhotoTable($dbAdapter);
					return $table;
                },
				'Groups\Model\UserGroupTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserGroupTable($dbAdapter);
					return $table;
                },
				'Groups\Model\UserGroupTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserGroupTable($dbAdapter);
					return $table;
                },
				'Groups\Model\GroupMediaTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GroupMediaTable($dbAdapter);
					return $table;
                },
				 'Groups\Model\UserGroupJoiningInvitationTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserGroupJoiningInvitationTable($dbAdapter);
                    return $table;
                },
            ),
        );
	}
}