<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Spam\Controller\Spam' => 'Spam\Controller\SpamController',
        ),
    ),
    // The following section is new and should be added to your file
       'router' => array(
        'routes' => array(
            'Spam' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/spam',
                    'defaults' => array(
						'__NAMESPACE__' => 'Spam\Controller',
                        'controller' => 'Spam',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'getreasons' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/getreasons',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'Spam\Controller',
								'controller' => 'Spam',
								'action'     => 'getreasons',
							),
                        ),					 
                    ),
					'sentreport'		=> array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/sentreport',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'Spam\Controller',
								'controller' => 'Spam',
								'action'     => 'sentreport',
							),
                        ),					 
                    ),			
                ),
            ),
        ),
    ),


    'view_manager' => array(
        'template_path_stack' => array(
            'city' => __DIR__ . '/../view',
        ),
    ),
);

