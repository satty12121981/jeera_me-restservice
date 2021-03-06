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
define('BASE_URL', 'http://'.@$_SERVER['SERVER_NAME']);
define('DOCUMENT_ROOT_PATH', $_SERVER['DOCUMENT_ROOT']);
define('WEBSITE_FULL_ADDRESS', BASE_URL);
define("GROUP_IMG_PATH", $_SERVER['DOCUMENT_ROOT']."/public/datagd/group/");
define("GROUP_IMG_PATH_ABOLUTE_PATH", "http://".$_SERVER['SERVER_NAME']."/public/datagd/group/");

define('USER_IMAGE_MINIMUM_WIDTH','380');
define('USER_IMAGE_MINIMUM_HEIGHT','214');
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
        'ROOTPATH'         => $_SERVER['DOCUMENT_ROOT']."jeera_me-restservice",
		'group_img_path' => $_SERVER['DOCUMENT_ROOT']."jeera_me-restservice/public/datagd/group/",
		'group_img_path_absolute_path' =>"http://".$_SERVER['SERVER_NAME']."/jeera_me-restservice/public/datagd/group/",
		'UploadPath'       => $_SERVER['DOCUMENT_ROOT']."jeera_me-restservice/public/datagd/",
		'AlbumUploadPath'  => $_SERVER['DOCUMENT_ROOT']."jeera_me-restservice/public/album/",
		'TagCategoryPath'  => "datagd/tag_category/",
		'base_url' =>'http://'.@$_SERVER['SERVER_NAME'].'/jeera_me-restservice',
		'fbredirect' =>"http://y2m.ae/development/jeerav1.2/user/fbredirect",
        'absolute_img_path' => "http://".$_SERVER['SERVER_NAME']."/jeera_me-restservice/public/",
		'AdminUserPath' => $_SERVER['DOCUMENT_ROOT']."jeera_me-restservice/public/adminuser/",
		'admin_user'        => $_SERVER['DOCUMENT_ROOT']."jeera_me-restservice/public/adminuser/",
		'admin_user_absolute_path' => "http://".$_SERVER['SERVER_NAME']."/jeera_me-restservice/public/adminuser/",
		'temppath'=> $_SERVER['DOCUMENT_ROOT']."jeera_me-restservice/public/datagd/temp/",
		'temp_absolute_path' => "http://".$_SERVER['SERVER_NAME']."/jeera_me-restservice/public/datagd/temp/",
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
