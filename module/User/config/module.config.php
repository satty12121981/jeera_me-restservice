<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'User\Controller\User' => 'User\Controller\UserController',
			'User\Controller\UserProfile' => 'User\Controller\UserProfileController',
			'Groups\Controller\Groups' => 'Groups\Controller\GroupsController',			
			),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(			
            'user' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/user',
                    'defaults' => array(
						'__NAMESPACE__' => 'User\Controller',
                        'controller' => 'user',
                        'action'     => 'index',
                    ),
                ),
				'may_terminate' => true,				
				'child_routes' => array(
					'register' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/register',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
								'controller' => 'user',
								'action'     => 'register',
							),
                        ),					 
                    ),
					 'fblogin' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/fblogin',
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'user',
                                'action'     => 'fblogin',
                            ),
                        ),
                    ),
					'fbredirect' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/fbredirect',
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'user',
                                'action'     => 'fbredirect',
                            ),
                        ),
                    ),
					'ajaxLogin' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/ajaxLogin',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
								'controller' => 'user',
								'action'     => 'ajaxLogin',
							),
                        ),					 
                    ),
					'resendverification' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/resendverification',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
								'controller' => 'user',
								'action'     => 'resendverification',
							),
                        ),					 
                    ),					
					'varifyemail' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/varifyemail[/:key][/:id]',
                            'defaults' => array(
                                'controller' => 'user',
                                'action'     => 'varifyemail',
                            ),
                        ),
                    ),
					'forgotPassword' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/forgotPassword',
                            'defaults' => array(
                                'controller' => 'user',
                                'action'     => 'forgotPassword',
                            ),
                        ),
                    ),
					'resetpassword' => array(
                        'type' => 'Segment',
                        'options' => array(
                            'route' => '/resetpassword[/:key][/:id]',
                            'defaults' => array(
                                'controller' => 'user',
                                'action'     => 'resetpassword',
                            ),
                        ),
                    ),
					 'logout' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/logout',
                            'defaults' => array(
                                'controller' => 'user',
                                'action'     => 'logout',
                            ),
                        ),
                    ),
					'saveSettings' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/savesettings',
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'saveSettings',
                            ),
                        ),
                    ),
					'updateBio' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/updateBio',
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'updateBio',
                            ),
                        ),
                    ),
					'updatProfilePic' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/updatProfilePic',
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'updatProfilePic',
                            ),
                        ),
                    ),
					'getFeeds' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/getFeeds',
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'getFeeds',
                            ),
                        ),
                    ),
					'getMyFeeds' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/getMyFeeds',
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'getMyFeeds',
                            ),
                        ),
                    ),
					 
					'getMyActivities' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/getMyActivities',
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'getMyActivities',
                            ),
                        ),
                    ),
					
				),
            ),
			'memberprofile' => array(
                'type' => 'segment',
				 'priority' => 1000,
				'may_terminate' => true,
                'options' => array(
                   'route' => '[/:member_profile]',
				   'constraints' => array(
						'member_profile' => '[a-zA-Z0-9_-]*',												 
					),
                    'defaults' => array(
						'__NAMESPACE__' => 'User\Controller',
                        'controller' => 'userprofile',
                        'action'     => 'memberprofile',
                    ),
                ),
                'child_routes' => array(
					
					'friends' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/friends',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'friends',
						  ),
						 ),
						),
						'activities' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/activities',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'activities',
						  ),
						 ),
						),
						'explore' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/explore',
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'explore',
						  ),
						 ),
						),
						'moreFriends' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/moreFriends',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'moreFriends',
						  ),
						 ),
						),
						'receivedRequests' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/receivedRequests',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'receivedRequests',
						  ),
						 ),
						),
						'sentRequests' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/sentRequests',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'sentRequests',
						  ),
						 ),
						),
						'mutualFriends' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/mutualFriends',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'mutualFriends',
						  ),
						 ),
						),
						'groups' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/groups',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'Groups\Controller',
                                'controller' => 'groups',
                                'action'     => 'membergroups',
						  ),
						 ),
						),
						 
						
                    'memberconnect' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/connect/[:member_profile]',
							'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',												 
							),
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'memberconnect',
                            ),
                        ),
                    ),
					'planets' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/planets',
							'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',												 
							),
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'planets',
                            ),
                        ),
                    ),
					'photos' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/photos',
							'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',												 
							),
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'photos',
                            ),
                        ),
                    ),
					 
					'feeds-loadmore' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/morefeeds',
							'constraints' => array(							 										 
							),
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'morefeeds',
                            ),
                        ),
                    ),
					'photos_view' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/photos/[:album_id]',
							'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	
							'album_id' => '[a-zA-Z0-9_-]*',								
							),
                            'defaults' => array(
								'__NAMESPACE__' => 'Album\Controller',
                                'controller' => 'album',
                                'action'     => 'userAlbumView',
                            ),
                        ),
                    ),
					
					'user_photos' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/photos/user_photos',
							'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	
							 				
							),
                            'defaults' => array(
								'__NAMESPACE__' => 'Album\Controller',
                                'controller' => 'album',
                                'action'     => 'userPhotos',
                            ),
                        ),
                    ),
					
					'file_view' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/photos/[:album_id]/[:num]',
							'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	
							'num' => '[0-9]+',								
							),
                            'defaults' => array(
								'__NAMESPACE__' => 'Album\Controller',
                                'controller' => 'album',
                                'action'     => 'userfile',
                            ),
                        ),
                    ),
					'memberapprove' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/approve/[:member_profile]',
							'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',												 
							),
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'memberapprove',
                            ),
                        ),
                    ),
					'profile' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/profile/[:member_profile]',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'profile',
						  ),
						 ),
						),
						 
						'updateProfile' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/updateProfile/[:member_profile]',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'updateProfile',
						  ),
						 ),
						),
						'updateTags' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/updateTags/[:member_profile]',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'updateTags',
						  ),
						 ),
						),
						'myintrests' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/myintrests/[:member_profile]',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'myintrests',
						  ),
						 ),
						),
						'sentFriendRequest' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/sentFriendRequest/[:member_profile]',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'sentFriendRequest',
						  ),
						 ),
						),
						'acceptFriendRequest' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/acceptFriendRequest/[:member_profile]',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'acceptFriendRequest',
						  ),
						 ),
						),
						'rejectFriendRequest' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/rejectFriendRequest/[:member_profile]',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'rejectFriendRequest',
						  ),
						 ),
						),
						'unFriendRequest' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/unFriendRequest/[:member_profile]',          
						   'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',	         
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'unFriendRequest',
						  ),
						 ),
						),
						
					'saveProfilePic' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/saveProfilePic',
							'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',												 
							),
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'saveProfilePic',
                            ),
                        ),
                    ),
						
						'updatebio' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/updatebio',
									 
								'defaults' => array(
									'controller' => 'UserProfile',
									'action'     => 'updatebio',
								),
							),
						),
						'ajaxLoadMoreFriends' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/ajaxLoadMoreFriends/[:member_profile]',
								'constraints' => array(
								'member_profile' => '[a-zA-Z0-9_-]*',	         
							   ),	 
								'defaults' => array(
									'controller' => 'UserProfile',
									'action'     => 'ajaxLoadMoreFriends',
								),
							),
						),
						'ajaxLoadMorePlanets' => array(
							'type' => 'segment',
							'options' => array(
								'route' => '/ajaxLoadMorePlanets/[:member_profile]',
								'constraints' => array(
								'member_profile' => '[a-zA-Z0-9_-]*',	         
							   ),	 
								'defaults' => array(
									'controller' => 'UserProfile',
									'action'     => 'ajaxLoadMorePlanets',
								),
							),
						),
					'settingsGroupLoadmore' => array(
						 'type' => 'segment',
						 'options' => array(
						  'route' => '/settingsGroupLoadmore',          
						   'constraints' => array(
							  
						   ),
						  'defaults' => array(
						  '__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'settingsGroupLoadmore',
						  ),
						 ),
						),	
					'memberreject' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/reject/[:member_profile]',
							'constraints' => array(
							'member_profile' => '[a-zA-Z0-9_-]*',												 
							),
                            'defaults' => array(
								'__NAMESPACE__' => 'User\Controller',
                                'controller' => 'userprofile',
                                'action'     => 'memberreject',
                            ),
                        ),
                    ),
				),
			),			
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'user' => __DIR__ . '/../view',
        ),
    ),
);

