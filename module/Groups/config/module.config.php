<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Groups\Controller\Groups' => 'Groups\Controller\GroupsController',
        ),
    ),
	'controller_plugins' => array(
        'invokables' => array(
             
     'ResizeImage' => 'Application\Controller\Plugin\ResizeImage;',
   
        ),
    ),
	 // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'groups' => array(
                'type'    => 'Literal',
				'priority' => 1000,
                'options' => array(
                    'route'    => '/groups',
					 
                   'defaults' => array(
						'__NAMESPACE__' => 'Groups\Controller',
                        'controller' => 'groups',
                        'action'     => 'index',
                    ),
                ),
				'may_terminate' => true,				
				'child_routes' => array(
					'groups' => array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/mygroup',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'mygroup',
							),
                        ),					 
                    ),
					'creategroup' => array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/creategroup',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'creategroup',
							),
                        ),					 
                    ),
					'getMedia' => array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/getMedia',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'getMedia',
							),
                        ),					 
                    ),
					'getAllMedia' => array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/getAllMedia',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'getAllMedia',
							),
                        ),					 
                    ),
					'ajaxAddMedia' => array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/ajaxAddMedia',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'ajaxAddMedia',
							),
                        ),					 
                    ),
					 'getAllActiveMembersExceptMe' => array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/getAllActiveMembersExceptMe',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'getAllActiveMembersExceptMe',
							),
                        ),					 
                    ),
					'getuserfriends' => array( 
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/getuserfriends',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'getuserfriends',
							),
                        ),
                    ),
					'matchedgroupsWithInterests' => array(  
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/matchedgroupsWithInterests',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'matchedgroupsWithInterests',
							),
                        ),
                    ),
					'grouplist' => array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/grouplist',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'grouplist',
							),
                        ),
                    ),
					'leavegroup' => array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/leavegroup',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'leavegroup',
							),
                        ),
                    ),
					'getQuestionnaire'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/getQuestionnaire',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'getQuestionnaire',
							),
                        ),
                    ),
					'saveUserQuestionnaire'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/saveUserQuestionnaire',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'saveUserQuestionnaire',
							),
                        ),
                    ),
					'joinGroup'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/joinGroup',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'joinGroup',
							),
                        ),
                    ),
					'getAllGroupTags'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/getAllGroupTags',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'getAllGroupTags',
							),
                        ),
                    ),
					'updateTag'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/updateTag',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'updateTag',
							),
                        ),
                    ),
					'updateGroup'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/updateGroup',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'updateGroup',
							),
                        ),
                    ),
					'getMembers'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/getMembers',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'getMembers',
							),
                        ),
                    ),
					'removeuser'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/removeuser',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'removeuser',
							),
                        ),
                    ),
					'promoteadmin'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/promoteadmin',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'promoteadmin',
							),
                        ),
                    ),
					'revokeadmin'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/revokeadmin',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'revokeadmin',
							),
                        ),
                    ),
					'updateQuestionnaire'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/updateQuestionnaire',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'updateQuestionnaire',
							),
                        ),
                    ),
					'updatProfilePic'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/updatProfilePic',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'updatProfilePic',
							),
                        ),
                    ),
					'ignoreJoinRequest'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/ignoreJoinRequest',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'ignoreJoinRequest',
							),
                        ),
                    ),
					'acceptJoinRequest'=> array(
                        'type' => 'segment',
						'priority' => 1000,
                        'options' => array(
                            'route' => '/acceptJoinRequest',

							'defaults' => array(
								'__NAMESPACE__' => 'Groups\Controller',
								'controller' => 'groups',
								'action'     => 'acceptJoinRequest',
							),
                        ),
                    ),
					'groupdetails' => array( 
                      'type' => 'segment',
					   
                      'options' => array(
                          'route' => '[/:group_seo]',
                           'constraints' => array(
                          'group_seo' => '[a-zA-Z0-9_-]*',
                      ),

                          'defaults' => array(
                              '__NAMESPACE__' => 'Groups\Controller',
                              'controller' => 'groups',
                              'action'     => 'groupdetails',
                          ),
                      ),
                  ),
				  'groupmembers' => array( 
                      'type' => 'segment',
					   
                      'options' => array(
                          'route' => '[/:group_seo]/members',
                           'constraints' => array(
                          'group_seo' => '[a-zA-Z0-9_-]*',
                      ),

                          'defaults' => array(
                              '__NAMESPACE__' => 'Groups\Controller',
                              'controller' => 'groups',
                              'action'     => 'groupmembers',
                          ),
                      ),
                  ),
				),				
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'groups' => __DIR__ . '/../view',
        ),
    ),
);