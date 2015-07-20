<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

return array(
    'router' => array(
        'routes' => array(            
            'register' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/service[/][:action][/:name][/page/:page]',
					'constraints' => array(
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'name' => '[a-zA-Z][a-zA-Z0-9_-]*',
							'page' => '[0-9]+'
                            ),
                    'defaults' => array(
                        'controller' => 'Service\Controller\Index',
                        'action'     => 'register',
                    ),
                ),
            ),  
			'login' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/service[/][:action][/:name][/page/:page]',
					'constraints' => array(
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'name' => '[a-zA-Z][a-zA-Z0-9_-]*',
							'page' => '[0-9]+'
                            ),
                    'defaults' => array(
                        'controller' => 'Service\Controller\Index',
                        'action'     => 'login',
                    ),
                ),
            ),  
			'loginaccess' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/service[/][:action][/:name][/page/:page]',
					'constraints' => array(
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'name' => '[a-zA-Z][a-zA-Z0-9_-]*',
							'page' => '[0-9]+'
                            ),
                    'defaults' => array(
                        'controller' => 'Service\Controller\Index',
                        'action'     => 'loginaccess',
                    ),
                ),
            ),  
			'logout' => array(
                'type' => 'Segment',
                'options' => array(
                    'route'    => '/service[/][:action][/:name][/page/:page]',
					'constraints' => array(
                            'action' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'name' => '[a-zA-Z][a-zA-Z0-9_-]*',
							'page' => '[0-9]+'
                            ),
                    'defaults' => array(
                        'controller' => 'Service\Controller\Index',
                        'action'     => 'logout',
                    ),
                ),
            ),           
            'service' => array(
                'type'    => 'segment',
                    'options' => array(
                        'route'    => '/service[/][:controller[/:action]][/:first_param][/:second_param][/:third_param][/:fourth_param][/:fifth_param][/:sixth_param][/:seventh_param][/:eight_param][/:nineth_param][/:tenth_param]',
                         'constraints' => array(
                            'controller' => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'action'     => '[a-zA-Z][a-zA-Z0-9_-]*',
                            'first_param' => '[a-zA-Z0-9_-]*',
                            'second_param' => '[a-zA-Z0-9_-]*',
                            'third_param' => '[a-zA-Z0-9_-]*',
                            'fourth_param' => '[a-zA-Z0-9_-]*',
                            'fifth_param' => '[a-zA-Z0-9_-]*',
                            'sixth_param' => '[a-zA-Z0-9_-]*',
                            'seventh_param' => '[a-zA-Z0-9_-]*',
                            'eight_param' => '[a-zA-Z0-9_-]*',
                            'nineth_param' => '[a-zA-Z0-9_-]*',
                            'tenth_param' => '[a-zA-Z0-9_-]*',
                        ),
                        'defaults' => array(
                            '__NAMESPACE__' => 'Service\Controller',
                            'controller' => 'Index',
                            'action'     => 'index',
                        ),
                 ),
             ),
            
        ),
    ),
    
    'controllers' => array(
        'invokables' => array(
		'Service\Controller\Index' => 'Service\Controller\IndexController',
		'Service\Controller\Groups' => 'Service\Controller\GroupsController', 
		'Service\Controller\Tags' => 'Service\Controller\TagsController',
		'Service\Controller\Friends' => 'Service\Controller\FriendsController',
		'Service\Controller\GroupPosts' => 'Service\Controller\GroupPostsController',
		'Service\Controller\Activity' => 'Service\Controller\ActivityController',
		'Service\Controller\Like' => 'Service\Controller\LikeController',
		'Service\Controller\Comment' => 'Service\Controller\CommentController',
		'Service\Controller\Notification' => 'Service\Controller\NotificationController',
		'Service\Controller\Spam' => 'Service\Controller\SpamController',
        ),
    ),

);

