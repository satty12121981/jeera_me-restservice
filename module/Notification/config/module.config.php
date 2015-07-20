<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Notification\Controller\Notification' => 'Notification\Controller\NotificationController',
			'Notification\Controller\UserNotification' => 'Notification\Controller\UserNotificationController',
            'Notification\Controller\PushNotification' => 'Notification\Controller\PushNotificationController',
        ),
    ),

    // The following section is router to route controller
    'router' => array(
        'routes' => array(
            'notifications' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/notifications',
                    'defaults' => array(
						'__NAMESPACE__' => 'Notification\Controller',
                        'controller' => 'notification',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                  'child_routes' => array(
                    'usernotificationspopup' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/usernotificationspopup',
                            'defaults' => array(
								'__NAMESPACE__' => 'Notification\Controller',
                                'controller' => 'UserNotification',
                                'action'     => 'usernotificationspopup',
                            ),
                        ),
                    ),
					'getnotifications' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/getnotifications',
                            'defaults' => array(
								'__NAMESPACE__' => 'Notification\Controller',
                                'controller' => 'notification',
                                'action'     => 'getnotifications',
                            ),
                        ),
                    ),
					'usernotificationslist' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/usernotificationslist',
                            'defaults' => array(
								'__NAMESPACE__' => 'Notification\Controller',
                                'controller' => 'usernotification',
                                'action'     => 'usernotificationslist',
                            ),
                        ),
                    ),
					'ajaxGetNotificationCount' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/ajaxGetNotificationCount',
                            'defaults' => array(
								'__NAMESPACE__' => 'Notification\Controller',
                                'controller' => 'notification',
                                'action'     => 'ajaxGetNotificationCount',
                            ),
                        ),
                    ),
					'ajaxLoadMore' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/ajaxLoadMore',
                            'defaults' => array(
								'__NAMESPACE__' => 'Notification\Controller',
                                'controller' => 'notification',
                                'action'     => 'ajaxLoadMore',
                            ),
                        ),
                    ),
					'ajaxGetUserNotificationList' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/ajaxGetUserNotificationList',
                            'defaults' => array(
								'__NAMESPACE__' => 'Notification\Controller',
                                'controller' => 'notification',
                                'action'     => 'ajaxGetUserNotificationList',
                            ),
                        ),
                    ),
                   'pushnotify' => array(
                      'type' => 'Literal',
                      'options' => array(
                          'route' => '/pushnotify',
                          'defaults' => array(
                              '__NAMESPACE__' => 'Notification\Controller',
                              'controller' => 'PushNotification',
                              'action'     => 'PushNotify',
                          ),
                      ),
                   ),
                  'pushnotifyregisteredusers' => array(
                      'type' => 'Literal',
                      'options' => array(
                          'route' => '/pushnotifyregisteredusers',
                          'defaults' => array(
                              '__NAMESPACE__' => 'Notification\Controller',
                              'controller' => 'PushNotification',
                              'action'     => 'PushNotifyRegisteredUsers',
                          ),
                      ),
                  ),
                ),
			),
		),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'notification' => __DIR__ . '/../view',
        ),
    ),
);

