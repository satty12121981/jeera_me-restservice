<?php
/**
 * Global Configuration Override
 *
 * You can use this file for overriding configuration values from modules, etc.
 * You would place values in here that are agnostic to the environment and not
 * sensitive to security.
 *
 * @NOTE: In practice, this file will typically be INCLUDED in your source
 * control, so do not include passwords or other sensitive information in this
 * file.
 */

return array(
    'db' => array(
        'driver'         => 'Pdo',
        'dsn'            => 'mysql:dbname=y2m_jeera_admin;host=localhost',
		'username'       => 'root',
        'password'       => '',
        'driver_options' => array(
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\''
        ),
    ),
	'pathInfo' => array(
        'ROOTPATH'         => "/var/www/html/jeera_me",
		'group_img_path' =>"/var/www/html/jeera_me/public/datagd/group/",
		'group_img_path_absolute_path' =>"http://y2m.ae/development/jeera_me/public/datagd/group/",
		'UploadPath'       => "/var/www/html/jeera_me/public/datagd/",
		'AlbumUploadPath'       => "/var/www/html/jeera_me/public/album/",
		'TagCategoryPath'  => "datagd/tag_category/",
		'base_url' =>'http://'.@$_SERVER['SERVER_NAME'].'/development/jeera_me',
		'fbredirect' =>"http://y2m.ae/development/jeera_me/user/fbredirect",
        'absolute_img_path' => "C:/wamp/www/jeera_me-restservice/public/",
    ),
	'image_folders' => array(
        'group'         => "datagd/group/",	
		'tag_category'         => "datagd/tag_category/",
		'profile_path'         => "datagd/profile/", 
    ),
    'service_manager' => array(
        'factories' => array(
            'Zend\Db\Adapter\Adapter'
                    => 'Zend\Db\Adapter\AdapterServiceFactory',
        ),
    ),
	'session' => array(
        'config' => array(
            'class' => 'Zend\Session\Config\SessionConfig',
            'options' => array(
                'name' => 'myapp',
            ),
        ),
        'storage' => 'Zend\Session\Storage\SessionArrayStorage',
        'validators' => array(
            array(
                'Zend\Session\Validator\RemoteAddr',
                'Zend\Session\Validator\HttpUserAgent',
            ),
        ),
    ),
);
