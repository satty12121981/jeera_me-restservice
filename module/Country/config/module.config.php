<?php
return array(
    'controllers' => array(
        'invokables' => array(
            'Country\Controller\Country' => 'Country\Controller\CountryController',
        ),
    ),
    // The following section is new and should be added to your file
    'router' => array(
        'routes' => array(
            'country' => array(
                'type' => 'Literal',
                'priority' => 1000,
                'options' => array(
                    'route' => '/country',
                    'defaults' => array(
						'__NAMESPACE__' => 'Country\Controller',
                        'controller' => 'country',
                        'action'     => 'index',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(                    
                    'ajaxCountryList' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/ajaxCountryList',
                            'defaults' => array(
                                'controller' => 'country',
                                'action' => 'ajaxCountryList',
                            ),
                        ),                        
                    ),
					'countrylist' => array(
                        'type' => 'Literal',
                        'options' => array(
                            'route' => '/countrylist',
                            'defaults' => array(
                                'controller' => 'country',
                                'action' => 'countrylist',
                            ),
                        ),                        
                    ), 
                ),
            ),
        ),
    ),

    'view_manager' => array(
        'template_path_stack' => array(
            'country' => __DIR__ . '/../view',
        ),
    ),
);

