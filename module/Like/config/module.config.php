<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Discussion\Controller\Discussion' => 'Discussion\Controller\DiscussionController',
			'Activity\Controller\Activity' => 'Activity\Controller\ActivityController',
			'Like\Controller\Like' => 'Like\Controller\LikeController',
			'Group\Controller\Group' => 'Group\Controller\GroupController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'like' => array(
					'type' => 'Literal',
					'priority' => 1000,
					'options' => array(
						'route'    => '/like',
						'defaults' => array(
							'__NAMESPACE__' => 'Like\Controller',
							'controller' => 'Like',
							'action'     => 'likes',
						),
					),
					'may_terminate' => true,
					'child_routes' => array(
						'like' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/likes',
									'constraints' => array(
									 										
									),
								'defaults' => array(
									'__NAMESPACE__' => 'Like\Controller',
									'controller' => 'Like',
									'action'     => 'Likes',
								),
							),
						),
						'unlike' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/unlikes',
									 
								'defaults' => array(
									'__NAMESPACE__' => 'Like\Controller',
									'controller' => 'Like',
									'action'     => 'UnLikes',
								),
							),
						),
						'commentlike' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/[:group_id]/[:sub_group_id]/[:system_type_id]/[:sub_system_type_id]/likes/[:group_refer_id]',
									'constraints' => array(
										'group_id' => '[a-zA-Z0-9_-]*',
										'sub_group_id' => '[a-zA-Z0-9_-]*',
										'system_type_id' => '[a-zA-Z0-9_-]*',
										'sub_system_type_id' => '[a-zA-Z0-9_-]*',
										'group_refer_id' => '[a-zA-Z0-9_-]*',											
									),
								'defaults' => array(
									'__NAMESPACE__' => 'Like\Controller',
									'controller' => 'Like',
									'action'     => 'CommentsLikes',
								),
							),
						),
						'commentunlike' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/[:group_id]/[:sub_group_id]/[:system_type_id]/[:sub_system_type_id]/unlikes/[:group_refer_id]',
									'constraints' => array(
										'group_id' => '[a-zA-Z0-9_-]*',
										'sub_group_id' => '[a-zA-Z0-9_-]*',
										'system_type_id' => '[a-zA-Z0-9_-]*',
										'sub_system_type_id' => '[a-zA-Z0-9_-]*',
										'group_refer_id' => '[a-zA-Z0-9_-]*',											
									),
								'defaults' => array(
									'__NAMESPACE__' => 'Like\Controller',
									'controller' => 'Like',
									'action'     => 'CommentsUnLikes',
								),
							),
						),
						'likeuserslist' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/userslist',
									'constraints' => array(
										 									
									),
								'defaults' => array(
									'__NAMESPACE__' => 'Like\Controller',
									'controller' => 'Like',
									'action'     => 'LikesUsersList',
								),
							),
						),
						'commentlikeuserslist' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/[:group_id]/[:sub_group_id]/[:system_type_id]/[:sub_system_type_id]/likes/userslist/[:group_refer_id]',
									'constraints' => array(
										'group_id' => '[a-zA-Z0-9_-]*',
										'sub_group_id' => '[a-zA-Z0-9_-]*',
										'system_type_id' => '[a-zA-Z0-9_-]*',
										'sub_system_type_id' => '[a-zA-Z0-9_-]*',
										'group_refer_id' => '[a-zA-Z0-9_-]*',											
									),
								'defaults' => array(
									'__NAMESPACE__' => 'Like\Controller',
									'controller' => 'Like',
									'action'     => 'CommentsLikesUsersList',
								),
							),
						),
					),

                ),
            ),
        ),

    'view_manager' => array(
        'template_path_stack' => array(
            'like' => __DIR__ . '/../view',
        ),
    ),
);

