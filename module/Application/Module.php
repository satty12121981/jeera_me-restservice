<?php
namespace Application;
use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
 
class Module
{
    public function onBootstrap(MvcEvent $e)
    {
        $sm = $e->getApplication()->getServiceManager();		 
    }

    public function getConfig()
    {
        return include __DIR__ . '/config/module.config.php';
    }

    public function getAutoloaderConfig()
    {
        return array(
            'Zend\Loader\StandardAutoloader' => array(
                'namespaces' => array(
                    __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
                ),
            ),
        );
    }
	
	// in Application/Module.php
	public function getControllerPluginConfig()
	{
		return array(
			'factories' => array(
				'GenericPlugin' => function($sm) {
					return new Controller\Plugin\GenericPlugin();
				}
			 )
		  ); 
	}
	
	public function getServiceConfig()
    {
        return array(
            'factories' => array(
				//session ends
            ),
        );
    }

	public function getViewHelperConfig()
    {
        return array(
            'factories' => array(
				//session ends
            ),
        );
    }	 
	
}
