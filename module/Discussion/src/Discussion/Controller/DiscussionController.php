<?php   
####################Discussion Controller #################################
namespace Discussion\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;	//Return model 
use Zend\View\Model\JsonModel;
use Zend\Session\Container; // We need this when using sessions     
use Zend\Authentication\AuthenticationService;	//Needed for checking User session
use Zend\Authentication\Adapter\DbTable as AuthAdapter;	//Db adapter
use Zend\Crypt\BlockCipher;		# For encryption 
use \Exception;	 
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Discussion\Model\Discussion;
use Notification\Model\UserNotification; 
class DiscussionController extends AbstractActionController
{
    protected $groupsTable;
	protected $discussionTable;
	protected $userTable;
	protected $userGroupTable;
	protected $userNotificationTable;
	public function ajaxNewDiscussionAction(){
		$error = '';
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			 
			$userinfo = $this->getUserTable()->getUser($identity->user_id);
			if(!empty($userinfo)&&$userinfo->user_id){				 
				$request   = $this->getRequest();
				if ($request->isPost()){
					$post = $request->getPost();
					if($post['statusText']=='') {
						$error = "Status is empty.. Please add something before you submit";
					}
					if($post['group_id']=='') {
						$error ="Select one group";
					}
					$group  = $this->getGroupsTable()->getPlanetinfo($post['group_id']);
					if(empty($group)||$group->group_id==''){
						$error = "Given group not exist in this system";
					}
					if($error == ''){
						$objDiscusion = new Discussion();
						$objDiscusion->group_discussion_content = $post['statusText'];
						$objDiscusion->group_discussion_owner_user_id = $identity->user_id;
						$objDiscusion->group_discussion_group_id = $group->group_id;
						$objDiscusion->group_discussion_status = 'available';
						$IdDiscussion = $this->getDiscussionTable()->saveDiscussion($objDiscusion);
						if($IdDiscussion){
							$joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($group->group_id); 
							foreach($joinedMembers as $members){ 
								if($members->user_group_user_id!=$identity->user_id){
									$config = $this->getServiceLocator()->get('Config');
									$base_url = $config['pathInfo']['base_url'];
									$msg = $identity->user_given_name." added a new status under the group ".$group->group_title;
									$subject = 'New status added';
									$from = 'admin@jeera.com';
									$this->UpdateNotifications($members->user_group_user_id,$msg,2,$subject,$from,$identity->user_id,$group->group_id);
								}
							}
						}else{$error = "Some error occured. Please try again";}
					}
				}else{$error = "Unable to process";}				 
			}else{$error = "User not exist in the system";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;		 
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	 
	}
	public function UpdateNotifications($user_notification_user_id,$msg,$type,$subject,$from,$sender,$reference_id){
		$UserGroupNotificationData = array();						
		$UserGroupNotificationData['user_notification_user_id'] =$user_notification_user_id;		 
		$UserGroupNotificationData['user_notification_content']  = $msg;
		$UserGroupNotificationData['user_notification_added_timestamp'] = date('Y-m-d H:i:s');			
		$UserGroupNotificationData['user_notification_notification_type_id'] = $type;
		$UserGroupNotificationData['user_notification_status'] = 'unread';
		$UserGroupNotificationData['user_notification_sender_id'] = $sender;
		$UserGroupNotificationData['user_notification_reference_id'] = $reference_id;
		#lets Save the User Notification
		$UserGroupNotificationSaveObject = new UserNotification();
		$UserGroupNotificationSaveObject->exchangeArray($UserGroupNotificationData);	
		$insertedUserGroupNotificationId ="";	#this will hold the latest inserted id value
		$insertedUserGroupNotificationId = $this->getUserNotificationTable()->saveUserNotification($UserGroupNotificationSaveObject);
		$userData = $this->getUserTable()->getUser($user_notification_user_id); 
		//$this->sendNotificationMail($msg,$subject,$userData->user_email,$from);
	}
	public function sendNotificationMail($msg,$subject,$emailId,$from){
		$this->renderer = $this->getServiceLocator()->get('ViewRenderer');		
		$body = $this->renderer->render('user/email/emailInvitation.phtml', array('msg'=>$msg));
		$htmlPart = new MimePart($body);
		$htmlPart->type = "text/html";
		$textPart = new MimePart($body);
		$textPart->type = "text/plain";
		$body = new MimeMessage();
		$body->setParts(array($textPart, $htmlPart));
		$message = new Mail\Message();
		$message->setFrom($from);
		$message->addTo($emailId);
		//$message->addReplyTo($reply);							 
		$message->setSender("Jeera");
		$message->setSubject($subject);
		$message->setEncoding("UTF-8");
		$message->setBody($body);
		$message->getHeaders()->get('content-type')->setType('multipart/alternative');
		$transport = new Mail\Transport\Sendmail();
		$transport->send($message);
		return true;
	}	
	public function getGroupsTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupsTable = (!$this->groupsTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupsTable;    
    }
	public function getDiscussionTable(){
		$sm = $this->getServiceLocator();
		return  $this->discussionTable = (!$this->discussionTable)?$sm->get('Discussion\Model\DiscussionTable'):$this->discussionTable;    
    }
	public function getUserGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->userGroupTable = (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;    
    }
	public function getUserTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;    
	}
	public function getUserNotificationTable(){         
		$sm = $this->getServiceLocator();
		return  $this->userNotificationTable = (!$this->userNotificationTable)?$sm->get('Notification\Model\UserNotificationTable'):$this->userNotificationTable;    
    }	
}
