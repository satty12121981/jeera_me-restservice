<?php
namespace Application\Controller\Plugin;
use \stdClass;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;

use ZendService\Apple\Apns\Client\Message as Client;
use ZendService\Apple\Apns\Message;
use ZendService\Apple\Apns\Message\Alert;
use ZendService\Apple\Apns\Response\Message as Response;
use ZendService\Apple\Apns\Exception\RuntimeException;

use ZendService\Apple\Apns\Client\Feedback as FeedClient;
use ZendService\Apple\Apns\Response\Feedback as FeedResponse;

use ZendService\Google\Gcm\Client as GcmClient;
use ZendService\Google\Gcm\Message as GcmMessage;
use ZendService\Google\Exception\RuntimeException as GcmException;

class PushNotifications extends AbstractPlugin
{
	public function ApplePushMessage($config){

		$client = new Client();
		$client->open(Client::SANDBOX_URI, $config['pathInfo']['ROOTPATH'].'/public/applepushnotifications/jeera-apns.pem', 'Jeera2015');
		
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

        $feedclient = new FeedClient();
        $feedclient->open(FeedClient::SANDBOX_URI, $config['pathInfo']['ROOTPATH'].'/public/applepushnotifications/jeera-apns.pem', 'Jeera2015');

        $feedresponses = $feedclient->feedback();
        $feedclient->close();

        foreach ($feedresponses as $response) {
            echo "Response time: ".$response->getTime() . 'Token : ' . $response->getToken();
        }
        exit;

	}

    public function GoogleCloudMessage(){

        $client = new GcmClient();
        $client->setApiKey('AIzaSyBduIyNVvpnc4xtUJdEs6EeqYQHei0h58k');

        $c = new \Zend\Http\Client(null, array(
            'adapter' => 'Zend\Http\Client\Adapter\Socket',
            'sslverifypeer' => false,
        ));
        $client->setHttpClient($c);

        $message = new GcmMessage();

        // up to 100 registration ids can be sent to at once
        $message->setRegistrationIds(array(
            'ej9mLk5SC_c:APA91bGGVSX2wuGn_JWzS0zVTlkiP9YV1oa9UmYeX49um-_BCEcKNJ4UycrzV44iM8zuikizpcuF94IDVBA74RIGYjg2s0pMB5INcwDHHzxKG1_Pm023bLud6F9c_Md84JuyjOklHJ0H',
        ));

        // optional fields
        $message->setData(array(
            'pull-request' => '1',

        ));
        $message->setCollapseKey('pull-request');
        $message->setRestrictedPackageName('com.zf.manual');
        $message->setDelayWhileIdle(false);
        $message->setTimeToLive(600);
        $message->setDryRun(false);
        $message->setData([
            'title' => 'Sample Push Notification',
            'message' => 'This is a test push notification using Google Cloud Messaging'
        ]);
        echo "<pre>";
        print_r($message);
        echo "</pre>";
        try {
            $response = $client->send($message);
            echo "<pre>";
            print_r($response->getResults());
            echo "</pre>";
        } catch (RuntimeException $e) {
            echo $e->getMessage() . PHP_EOL;
            exit(1);
        }
        echo 'Successful: ' . $response->getSuccessCount() . PHP_EOL;
        echo 'Failures: ' . $response->getFailureCount() . PHP_EOL;
        echo 'Canonicals: ' . $response->getCanonicalCount() . PHP_EOL;
        exit;
    }

}
