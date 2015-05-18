<?php
namespace Application\Controller\Plugin;
use \stdClass;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

use ZendService\Apple\Apns\Client\Message as Client;
use ZendService\Apple\Apns\Message;
use ZendService\Apple\Apns\Message\Alert;
use ZendService\Apple\Apns\Response\Message as Response;
use ZendService\Apple\Apns\Exception\RuntimeException;

class PushNotifications extends AbstractPlugin
{
	public function ApplePushMessage($config){

		$client = new Client();
        //echo $config['pathInfo']['ROOTPATH'].'/public/applepushnotifications/jeera.pem';
		$client->open(Client::SANDBOX_URI, $config['pathInfo']['ROOTPATH'].'/public/applepushnotifications/jeera.pem', '');
		
		$message = new Message();
		$message->setId('satty1212');
		$message->setToken('76931ba93ad92e3e1291fb01503f13171ee105c9c0a99a5c863627868c1086b3');
		$message->setBadge(5);
		$message->setSound('bingbong.aiff');

		// simple alert:
		$message->setAlert('Sathish likes to send text');
		// complex alert:
		/*
		$alert = new Alert();
		$alert->setBody('Bob wants to play poker');
		$alert->setActionLocKey('PLAY');
		$alert->setLocKey('GAME_PLAY_REQUEST_FORMAT');
		$alert->setLocArgs(array('Jenna', 'Frank'));
		$alert->setLaunchImage('Play.png');
		$message->setAlert($alert);
		*/
		
		try {
		    $response = $client->send($message);
		} catch (RuntimeException $e) {
		    echo $e->getMessage() . PHP_EOL;
		    exit(1);
		}
		$client->close();

		if ($response->getCode() != Response::RESULT_OK) {
		     switch ($response->getCode()) {
			 case Response::RESULT_PROCESSING_ERROR:
			     // you may want to retry
			     break;
			 case Response::RESULT_MISSING_TOKEN:
			     // you were missing a token
			     break;
			 case Response::RESULT_MISSING_TOPIC:
			     // you are missing a message id
			     break;
			 case Response::RESULT_MISSING_PAYLOAD:
			     // you need to send a payload
			     break;
			 case Response::RESULT_INVALID_TOKEN_SIZE:
			     // the token provided was not of the proper size
			     break;
			 case Response::RESULT_INVALID_TOPIC_SIZE:
			     // the topic was too long
			     break;
			 case Response::RESULT_INVALID_PAYLOAD_SIZE:
			     // the payload was too large
			     break;
			 case Response::RESULT_INVALID_TOKEN:
			     // the token was invalid; remove it from your system
			     break;
			 case Response::RESULT_UNKNOWN_ERROR:
			     // apple didn't tell us what happened
			     break;
		     }
		}

	}


}
