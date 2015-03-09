<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'City\Controller\City' => 'City\Controller\CityController',
        ),
    ),
    // The following section is new and should be added to your file
       'router' => array(
        'routes' => array(
            'city' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/city',
                    'defaults' => array(
						'__NAMESPACE__' => 'City\Controller',
                        'controller' => 'city',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'cities' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/cities',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'City\Controller',
								'controller' => 'city',
								'action'     => 'cities',
							),
                        ),					 
                    ),
					'ajaxCitiesList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/ajaxCitiesList',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'City\Controller',
								'controller' => 'city',
								'action'     => 'ajaxCitiesList',
							),
                        ),					 
                    ),
                    'loadAllCitiesList' => array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/loadAllCitiesList',
                             
                            'defaults' => array(
                                '__NAMESPACE__' => 'City\Controller',
                                'controller' => 'city',
                                'action'     => 'loadAllCitiesList',
                            ),
                        ),                   
                    ),
					'citylist'=>array(
                        'type' => 'segment',
                        'options' => array(
                            'route' => '/citylist',
                             
							'defaults' => array(
								'__NAMESPACE__' => 'City\Controller',
								'controller' => 'city',
								'action'     => 'citylist',
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

