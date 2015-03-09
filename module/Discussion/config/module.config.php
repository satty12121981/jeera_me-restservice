<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Discussion\Controller\Discussion' => 'Discussion\Controller\DiscussionController',
        ),
    ),

    // The following section routes the discussion controller
    'router' => array(
        'routes' => array(
            'discussion' => array(
					'type' => 'Literal',
					'priority' => 1000,
					'options' => array(
						'route'    => '/discussion',
						'defaults' => array(
							'__NAMESPACE__' => 'Discussion\Controller',
							'controller' => 'discussion',
							'action'     => 'index',
						),
					),
			'may_terminate' => true,
                'child_routes' => array(
					'addDiscussion' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/ajaxNewDiscussion',
                            'constraints' => array(
								 									 
							),
							'defaults' => array(
								'__NAMESPACE__' => 'Discussion\Controller',
								'controller' => 'discussion',
								'action'     => 'ajaxNewDiscussion',
							),
                        ),					 
                    ),	
					 
					 
					),
                ),
            ),
        ),
    
    'view_manager' => array(
        'template_path_stack' => array(
            'discussion' => __DIR__ . '/../view',
        ),
    ),
);

