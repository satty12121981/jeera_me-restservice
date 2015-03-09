<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Tag\Controller\Tag' => 'Tag\Controller\TagController',
        ),
    ),

    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'tag' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/tag',
                    'defaults' => array(
						'__NAMESPACE__' => 'Tag\Controller',
                        'controller' => 'tag',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                     
					'listExceptSelected' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/listExceptSelected',
                            'defaults' => array(
                                'controller' => 'tag',
                                'action'     => 'listExceptSelected',
                            ),
                        ),
                    ),
					'searchTags' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/searchTags',
                            'defaults' => array(
                                'controller' => 'tag',
                                'action'     => 'searchTags',
                            ),
                        ),
                    ),
                    'getAllActiveCategories' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/getAllActiveCategories',
                            'defaults' => array(
                                'controller' => 'tag',
                                'action'     => 'getAllActiveCategories',
                            ),
                        ),
                    ),
                    'getAllTagsOfSelectedCategory' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/getAllTagsOfSelectedCategory',
                            'defaults' => array(
                                'controller' => 'tag',
                                'action'     => 'getAllTagsOfSelectedCategory',
                            ),
                        ),
                    ), 
					'getGroupCount'=> array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/getGroupCount',
                            'defaults' => array(
                                'controller' => 'tag',
                                'action'     => 'getGroupCount',
                            ),
                        ),
                    ), 
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'tag' => __DIR__ . '/../view',
        ),
    ),
);

