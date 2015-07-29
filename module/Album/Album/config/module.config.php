<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Album\Controller\Album' => 'Album\Controller\AlbumController',
        ),
    ),
    // The following section is new and should be added to your file
       'router' => array(
        'routes' => array(
            'Album' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/album',
                    'defaults' => array(
						'__NAMESPACE__' => 'Album\Controller',
                        'controller' => 'Album',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'getAllGroupAlbums' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/getAllGroupAlbums',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'Album\Controller',
								'controller' => 'album',
								'action'     => 'getAllGroupAlbums',
							),
                        ),					 
                    ),
					'create' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/create',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'Album\Controller',
								'controller' => 'album',
								'action'     => 'create',
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

