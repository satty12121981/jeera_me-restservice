<?php

namespace Notification\Controller;



use Zend\Mvc\Controller\AbstractActionController;

use Zend\View\Model\ViewModel;	//Return model 

use Zend\Session\Container; // We need this when using sessions

use Zend\Authentication\AuthenticationService;

use Zend\Authentication\Adapter\DbTable as AuthAdapter;

/*use Zend\Authentication\Result as Result;

use Zend\Authentication\Storage;*/

 

//Login Form

use Notification\Form\Notification;       // <-- Add this import

use Notification\Form\NotificationFilter;   



#Notification class

use Notification\Model\UserNotification; 

use Notification\Model\UserNotificationTable; 



class UserNotificationController extends AbstractActionController

{

    public function indexAction()

    {

		return this;

    }

	

	public function usernotificationsPopupAction()

    {

		$error = array();	#Error variable

		$success = array();	#success message variable



		

		$UserNotificationData = array();	//this will hold the all the discussions of Planet

		

		$UserNotificationCountData = array();	

		

		#db connectivity

		$sm = $this->getServiceLocator();

		

		$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');

		$request = $this->getRequest();

		try {					

		

			$auth = new AuthenticationService();	

			$identity = null;   



			if ($auth->hasIdentity()) {

		

				// Identity exists get it

				$identity = $auth->getIdentity();	

				

				$this->layout()->identity = $identity;	//assign Identity to layout 				

				#fetch the user Galaxy

				

				$this->userTable = $sm->get('User\Model\UserTable');

				

				 #check the identity against the DB

				$userData = $this->userTable->getUser($identity->user_id);			

		

				if(isset($userData->user_id) && !empty($userData->user_id)){	

					

					$this->UserNotificationTable = $sm->get('Notification\Model\UserNotificationTable');

						

					#fetch the Galaxy details

					$UserNotificationData = $this->UserNotificationTable->getUserNotificationForUser($userData->user_id);

					

					#fetch the Galaxy details

					$UserNotificationCountData = $this->UserNotificationTable->getUserNotificationCountForUserUnread($userData->user_id);

					

					if ($request->isPost()) {

						#add the discussion

						$post = $request->getPost();

						if ($post->get('unflagcount') == true) {						

							#get the discussion content

							$unflagcount = $post->get('unflagcount');

							$notificationSaveData = array();

						

							$notificationSaveData['user_notification_user_id'] = $userData->user_id;

							$notificationSaveData['user_notification_status'] = 0;

							

							$UserNotification = new UserNotification();

							$UserNotification->exchangeArray($notificationSaveData);

						

							$insertedUserNotificationId = "";	#this will hold the latest inserted id value

							$insertedUserNotificationId = $this->UserNotificationTable->saveUserNotificationStatus($UserNotification); 

						}

					

					}

					

					#fetch the Galaxy details

					$UserNotificationCountData = $this->UserNotificationTable->getUserNotificationCountForUserUnread($userData->user_id);



				} 

			} 		

		} catch (\Exception $e) {

			echo "Caught exception: " . get_class($e) . "\n";

			echo "Message: " . $e->getMessage() . "\n";			 

		}

		

		$viewModel = new ViewModel(array('userData' => $userData,'UserNotifications' =>$UserNotificationData,'UserNotificationsCount' =>$UserNotificationCountData,'error' => $error, 'success' => $success, 'flashMessages' => $this->flashMessenger()->getMessages()));

		$viewModel->setTerminal($request->isXmlHttpRequest());

		return $viewModel;	

    }

	public function usernotificationsListAction()

    {

		return this;

    }

	 

}