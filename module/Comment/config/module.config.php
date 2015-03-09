<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Discussion\Controller\Discussion' => 'Discussion\Controller\DiscussionController',
			'Comment\Controller\Comment' => 'Comment\Controller\CommentController',
			'Groups\Controller\Groups' => 'Groups\Controller\GroupsController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'comment' => array(
					'type' => 'Segment',
					'priority' => 1000,
					'options' => array(
						'route'    => '/comment',
						'defaults' => array(
							'__NAMESPACE__' => 'Comment\Controller',
							'controller' => 'Comment',
							'action'     => 'index',
						),
					),
					'may_terminate' => true,
					'child_routes' => array(
						'comment' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/comments',
									'constraints' => array(
										 										
									),
								'defaults' => array(
									'__NAMESPACE__' => 'Comment\Controller',
									'controller' => 'Comment',
									'action'     => 'Comments',
								),
							),
						),
						'comments' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/comments',
									 
								'defaults' => array(
									'__NAMESPACE__' => 'Comment\Controller',
									'controller' => 'Comment',
									'action'     => 'comments',
								),
							),
						),
						'getComments'=> array(
							'type' => 'segment',
							'options' => array(
								'route' => '/getComments',
									 
								'defaults' => array(
									'__NAMESPACE__' => 'Comment\Controller',
									'controller' => 'Comment',
									'action'     => 'getComments',
								),
							),
						),
						'loadmore' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/loadmore',
									 
								'defaults' => array(
									'__NAMESPACE__' => 'Comment\Controller',
									'controller' => 'Comment',
									'action'     => 'loadmore',
								),
							),
						),
						'edit' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/edit',
									 
								'defaults' => array(
									'__NAMESPACE__' => 'Comment\Controller',
									'controller' => 'Comment',
									'action'     => 'edit',
								),
							),
						),
						'delete' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/delete',
									 
								'defaults' => array(
									'__NAMESPACE__' => 'Comment\Controller',
									'controller' => 'Comment',
									'action'     => 'delete',
								),
							),
						),
					),
                ),
            ),
        ),
    

    'view_manager' => array(
        'template_path_stack' => array(
            'comment' => __DIR__ . '/../view',
        ),
    ),
);

