<?php
 
return array(     
    'modules' => array(
		'Admin',
		'Activity',		
        'User',		
		'Application',	 	
		'Country',	
		'City',		
		'Tag',
		'Groups',
		'Notification',
		'Discussion',
		'Like',
		'Comment',
		'Facebook',
		'Service',
		'Spam',
    ), 
    'module_listener_options' => array(        
        'module_paths' => array(
            './module',
            './vendor',
        ), 
        'config_glob_paths' => array(
            'config/autoload/{,*.}{global,local}.php',
        ),
    ),
);