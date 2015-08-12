<?php
 
return array(     
    'modules' => array(
		'Admin',
		'Album',
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
		'ZfCommons',
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