<?php
	return array(
		'acl'=>array(
			'roles'=>array(
				'guest'=>null,
				'member'=>'guest',
				'admin'=>'member'
			),
			'resources'=>array(
				'allow'=>array(
					'Application\Controller\Index'=> array(
					 'index'=>'guest',
					 'search'=>'member'
				),
				'deny'=>array(
					
				),
			),
		),
	),
);
	
?>