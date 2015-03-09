<?php
namespace Like;
use Groups\Model\GroupsTable;
use Groups\Model\UserGroupTable;
use Notification\Model\UserNotificationTable;
use User\Model\UserTable;
use Zend\Db\ResultSet\ResultSet;			#Uses in Table Group for Db
use Zend\Db\TableGateway\TableGateway;		#Uses in Table Group for Db
use Photo\Model\PhotoTable;
use Tag\Model\TagTable;
use Like\Model\LikeTable;
use Activity\Model\ActivityTable;
use Discussion\Model\DiscussionTable;
use Album\Model\AlbumTable;

#use Zend\Db\ResultSet\ResultSet;

use Zend\Mvc\ModuleRouteListener;			#it is use to listen module

/*************** Session *************/
use Zend\Session\SessionManager;
use Zend\Session\Container;

class Module
{    
	 public function onBootstrap($e) {
        $eventManager = $e->getApplication()->getEventManager();
        $moduleRouteListener = new ModuleRouteListener();
        $moduleRouteListener->attach($eventManager);
		$this->bootstrapSession($e);
    }
	
	public function bootstrapSession($e) {
        $session = $e->getApplication()
                     ->getServiceManager()
                     ->get('Zend\Session\SessionManager');
        $session->start();

        $container = new Container('initialized');
        if (!isset($container->init)) {
             $session->regenerateId(true);
             $container->init = 1;
        }
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

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }
	
	// Add this method:
    public function getServiceConfig()
    {
        return array(
            'factories' => array(
                'User\Model\UserTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserTable($dbAdapter);
                    return $table;
                },
				'Groups\Model\GroupsTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new GroupsTable($dbAdapter);
                    return $table;
                },
				'Groups\Model\UserGroupTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserGroupTable($dbAdapter);
                    return $table;
                },
				'Notification\Model\UserNotificationTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new UserNotificationTable($dbAdapter);
                    return $table;
                },
				'Photo\Model\PhotoTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new PhotoTable($dbAdapter);
					return $table;
                },
               'Like\Model\LikeTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new LikeTable($dbAdapter);
                    return $table;
                },
				'Activity\Model\ActivityTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new ActivityTable($dbAdapter);
					return $table;
                },
				'Discussion\Model\DiscussionTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new DiscussionTable($dbAdapter);
					return $table;
                },
				'Album\Model\AlbumTable' =>  function($sm) {
                    $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
                    $table = new AlbumTable($dbAdapter);
					return $table;
                },
               
				//session start
				'Zend\Session\SessionManager' => function ($sm) {
                    $config = $sm->get('config');
                    if (isset($config['session'])) {
                        $session = $config['session'];

                        $sessionConfig = null;
                        if (isset($session['config'])) {
                            $class = isset($session['config']['class'])  ? $session['config']['class'] : 'Zend\Session\Config\SessionConfig';
                            $options = isset($session['config']['options']) ? $session['config']['options'] : array();
                            $sessionConfig = new $class();
                            $sessionConfig->setOptions($options);
                        }

                        $sessionStorage = null;
                        if (isset($session['storage'])) {
                            $class = $session['storage'];
                            $sessionStorage = new $class();
                        }

                        $sessionSaveHandler = null;
                        if (isset($session['save_handler'])) {
                            // class should be fetched from service manager since it will require constructor arguments
                            $sessionSaveHandler = $sm->get($session['save_handler']);
                        }

                        $sessionManager = new SessionManager($sessionConfig, $sessionStorage, $sessionSaveHandler);
                        if (isset($session['validator'])) {
                            $chain = $sessionManager->getValidatorChain();
                            foreach ($session['validator'] as $validator) {
                                $validator = new $validator();
                                $chain->attach('session.validate', array($validator, 'isValid'));

                            }
                        }
                    } else {
                        $sessionManager = new SessionManager();
                    }
						Container::setDefaultManager($sessionManager);
						return $sessionManager;
                	},				
				//session ends
            ),
        );
    }
	
}