<?php
return array(
	 'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
			'User\Controller\UserProfile' => 'User\Controller\UserProfileController',
			 	
			),
    ),
    'router' => array(
        'routes' => array(
            'home' => array(
                'type' => 'Zend\Mvc\Router\Http\Literal',
				'priority' => 1000,
                'options' => array(
                    'route'    => '/',
                    'defaults' => array(
                        'controller' => 'Application\Controller\Index',
                        'action'     => 'index',
                    ),
                ),
				'may_terminate' => true,
				'child_routes' => array(
					'ajaxGroupGeneralList' => array(
						'type' => 'segment',
						'options' => array(
							'route' => 'ajaxGroupGeneralList',
							 
							'defaults' => array(								
								 'controller' => 'Application\Controller\Index',
								'action'     => 'ajaxGroupGeneralList',
							),
						),					 
					),
					'explore' => array(
						'type' => 'segment',
						'options' => array(
							'route' => 'explore',
							 
							'defaults' => array(								
								'controller' => 'User\Controller\UserProfile',
								'action'     => 'explore',
							),
						),					 
					),
					'feeds' => array(
						'type' => 'segment',
						'options' => array(
							'route' => 'feeds',
							 
							'defaults' => array(								
								 'controller' => 'User\Controller\UserProfile',
								'action'     => 'feeds',
							),
						),					 
					),
					'settings' => array(
						'type' => 'segment',
						'options' => array(
							'route' => 'settings',
							 
							'defaults' => array(								
								 'controller' => 'User\Controller\UserProfile',
								'action'     => 'settings',
							),
						),					 
					),
					'quicksearch' => array(
						'type' => 'segment',
						'options' => array(
							'route' => 'quicksearch',
							 
							'defaults' => array(								
								 'controller' => 'Application\Controller\Index',
								'action'     => 'quicksearch',
							),
						),					 
					),
					'search' => array(
						'type' => 'segment',
						'options' => array(
							'route' => 'search',
							 
							'defaults' => array(								
								 'controller' => 'Application\Controller\Index',
								'action'     => 'search',
							),
						),					 
					),
					'moresearchresults' => array(
						'type' => 'segment',
						'options' => array(
							'route' => 'moresearchresults',
							 
							'defaults' => array(								
								 'controller' => 'Application\Controller\Index',
								'action'     => 'moresearchresults',
							),
						),					 
					),
					'getNotificationCount'=> array(
						'type' => 'segment',
						'options' => array(
							'route' => 'getNotificationCount',
							 
							'defaults' => array(								
								 'controller' => 'Application\Controller\Index',
								'action'     => 'getNotificationCount',
							),
						),					 
					),
					'getNotificationlist'=> array(
						'type' => 'segment',
						'options' => array(
							'route' => 'getNotificationlist',
							 
							'defaults' => array(								
								 'controller' => 'Application\Controller\Index',
								'action'     => 'getNotificationlist',
							),
						),					 
					),
					'makenotificationreaded'=> array(
						'type' => 'segment',
						'options' => array(
							'route' => 'makenotificationreaded',
							 
							'defaults' => array(								
								 'controller' => 'Application\Controller\Index',
								'action'     => 'makenotificationreaded',
							),
						),					 
					),
					
					 
				),				 	
            ),
			
        ),
    ),
    'service_manager' => array(
        'abstract_factories' => array(
            //'Zend\Cache\Service\StorageCacheAbstractServiceFactory',
            //'Zend\Log\LoggerAbstractServiceFactory',
        ),
        'factories' => array(
            'translator' => 'Zend\I18n\Translator\TranslatorServiceFactory',
        ),
    ),
    'translator' => array(
        'locale' => 'en_US',
        'translation_file_patterns' => array(
            array(
                'type'     => 'gettext',
                'base_dir' => __DIR__ . '/../language',
                'pattern'  => '%s.mo',
            ),
        ),
    ),
    'controllers' => array(
        'invokables' => array(
            'Application\Controller\Index' => 'Application\Controller\IndexController',
			'Application\Controller\GenericPlugin' => 'Application\Controller\GenericPlugin',
        ),
    ),
    'view_manager' => array(
        'display_not_found_reason' => true,
        'display_exceptions'       => true,
        'doctype'                  => 'HTML5',
        'not_found_template'       => 'error/404',
        'exception_template'       => 'error/index',
        'template_map' => array(
            'layout/layout'           => __DIR__ . '/../view/layout/layout.phtml',
            'application/index/index' => __DIR__ . '/../view/application/index/index.phtml',
            'error/404'               => __DIR__ . '/../view/error/404.phtml',
            'error/index'             => __DIR__ . '/../view/error/index.phtml',
        ),
        'template_path_stack' => array(
            __DIR__ . '/../view',
        ),
		'strategies' => array(
            'ViewJsonStrategy',
        ),
    ),
);
