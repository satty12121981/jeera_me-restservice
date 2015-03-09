<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Activity\Controller\Activity' => 'Activity\Controller\ActivityController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'activity' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/activity',
                    'defaults' => array(
						'__NAMESPACE__' => 'Activity\Controller',
                        'controller' => 'activity',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'ajaxAddActivity' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/ajaxAddActivity',
                            'constraints' => array(
								 									 
							),
							'defaults' => array(
								'__NAMESPACE__' => 'Activity\Controller',
								'controller' => 'activity',
								'action'     => 'ajaxAddActivity',
							),
                        ),					 
                    ),
					'quitactivity' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/quitactivity',
                            'constraints' => array(
								 									 
							),
							'defaults' => array(
								'__NAMESPACE__' => 'Activity\Controller',
								'controller' => 'activity',
								'action'     => 'quitactivity',
							),
                        ),					 
                    ), 
				'joinactivity' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/joinactivity',
                            'constraints' => array(
								 									 
							),
							'defaults' => array(
								'__NAMESPACE__' => 'Activity\Controller',
								'controller' => 'activity',
								'action'     => 'joinactivity',
							),
                        ),					 
                    ), 			
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'activity' => __DIR__ . '/../view',
        ),
    ),
);
