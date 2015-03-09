<?php   
####################Activity Controller #################################

namespace Activity\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;	//Return model 
use Zend\Session\Container; // We need this when using sessions     
use Zend\Authentication\AuthenticationService;	//Needed for checking User sessi on
use Zend\Authentication\Adapter\DbTable as AuthAdapter;	//Db apaptor 
use Zend\View\Renderer\PhpRenderer;

use Zend\Crypt\BlockCipher;		# For encryption 
use Zend\View\Model\JsonModel; 
 
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
#activity form
use Activity\Model\Activity;   
use Notification\Model\UserNotification; 
use Activity\Model\ActivityInvite;  
use Activity\Model\ActivityRsvp ;
class ActivityController extends AbstractActionController
{
    protected $userTable;
	protected $groupTable;
	protected $groupActivityTable;
	protected $userGroupTable;
	protected $userFriendTable;
	protected $groupActivityInviteTable;
	protected $userNotificationTable;
	protected $activityRsvpTable;
    public function indexAction(){
		 
    }	
	public function ajaxAddActivityAction(){
		$error = '';
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$myinfo = $this->getUserTable()->getUser($identity->user_id);
			if(!empty($myinfo)&&$myinfo->user_id){
				$request   = $this->getRequest();
				if ($request->isPost()){
					$post = $request->getPost();
					$error =($post['group_id']=='')? "Select one group":$error;	 
					$group  = $this->getGroupTable()->getPlanetinfo($post['group_id']);
					$error =(empty($group)||$group->group_id=='')?"Given group not exist in this system":$error;
					$error =($post['event_title']==''||$post['event_title']=='undefined')? "Event title required":$error;
					$error =($post['event_date']==''||$post['event_date']=='undefined')? "Event date required":$error;
					$error =($post['event_location']==''||$post['event_location']=='undefined')? "Event location required":$error;
					$error =($post['event_description']==''||$post['event_description']=='undefined')? "Event description required":$error;
					$error = ($this->is_date($post['event_date']))?$error:"Enter a valid date";
					$stamp = strtotime($post['event_date'].' '.$post['event_time']);
					$error = ($stamp<=time())?"Past date events are not allowed":$error;
					if($error ==''){
						$objActivty = new Activity();
						$objActivty->group_activity_title = $post['event_title'];
						$objActivty->group_activity_content = $post['event_description'];
						$objActivty->group_activity_group_id = $post['group_id'];
						$objActivty->group_activity_owner_user_id = $identity->user_id;
						$objActivty->group_activity_status = 'active';
						$objActivty->group_activity_type = 'open';
						$objActivty->group_activity_start_timestamp = date("Y-m-d H:i:s",$stamp);
						$objActivty->group_activity_location = $post['event_location'];
						$objActivty->group_activity_location_lat = $post['event_location_lat'];
						$objActivty->group_activity_location_lng = $post['event_location_lng'];
						$newActivity_id = $this->getActivityTable()->createActivity($objActivty);
						if($newActivity_id){
							switch($post['selectedMemberType']){
								case "All members":
									$joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($group->group_id);
									foreach($joinedMembers as $members){ 
										if($members->user_group_user_id!=$identity->user_id){
											$config = $this->getServiceLocator()->get('Config');
											$base_url = $config['pathInfo']['base_url'];
											$msg = $identity->user_given_name." added a new event under the group ".$group->group_title;
											$subject = 'New event added';
											$from = 'admin@jeera.com';
											$this->UpdateNotifications($members->user_group_user_id,$msg,2,$subject,$from,$identity->user_id,$group->group_id);
										}
									}
								break;
								case "Friends":
									$friends = $this->getUserFriendTable()->userFriends($identity->user_id);
									foreach($friends as $members){ 										 
										$config = $this->getServiceLocator()->get('Config');
										$base_url = $config['pathInfo']['base_url'];
										$msg = $identity->user_given_name." added a new event under the group ".$group->group_title;
										$subject = 'New event added';
										$from = 'admin@jeera.com';
										$this->UpdateNotifications($members->friend_user,$msg,2,$subject,$from,$identity->user_id,$group->group_id);									 
									}
								break;
								case "Invite people":
									$invited_members = $post['invite_members'];
									if(!empty($invited_members)){
										foreach($invited_members as $members){
											$userinfo = $this->getUserTable()->getUser($members);
											if(!empty($userinfo)&&$userinfo->user_id){
												$objActivityInvite = new ActivityInvite();
												$objActivityInvite->group_activity_invite_sender_user_id = $identity->user_id;
												$objActivityInvite->group_activity_invite_receiver_user_id = $members;
												$objActivityInvite->group_activity_invite_status = 'invited';
												$objActivityInvite->group_activity_invite_activity_id = $newActivity_id;
												$this->getActivityInviteTable()->saveActivityInvite($objActivityInvite);
												$config = $this->getServiceLocator()->get('Config');
												$base_url = $config['pathInfo']['base_url'];
												$msg = $identity->user_given_name." added a new event under the group ".$group->group_title;
												$subject = 'New event added';
												$from = 'admin@jeera.com';
												$this->UpdateNotifications($members,$msg,2,$subject,$from,$identity->user_id,$group->group_id);
											}
										}
									}
								break;
							}
						}
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
	public function is_date( $str ){ 
		$stamp = strtotime( $str ); 
		if (!is_numeric($stamp)) 
			return FALSE; 
		$month = date( 'm', $stamp ); 
		$day   = date( 'd', $stamp ); 
		$year  = date( 'Y', $stamp ); 
		if (checkdate($month, $day, $year)) 
			return TRUE; 
		return FALSE; 
	}
	public function quitactivityAction(){
		$error = '';
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$myinfo = $this->getUserTable()->getUser($identity->user_id);
			if(!empty($myinfo)&&$myinfo->user_id){
				$request   = $this->getRequest();
				if ($request->isPost()){
					$post = $request->getPost();
					$activity_id =  $post['activity_id'];
					if($activity_id!=''){
						$acitivity = $this->getActivityTable()->getActivity($activity_id);
						if(!empty($acitivity)){
							if($this->getActivityRsvpTable()->removeActivityRsvp($activity_id,$identity->user_id)){
								;
							}else{$error = "Some error occured. Please try again";}
						}else{$error = "Activity not exist";}
					}else{$error = "Activity id required";}
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
	public function joinactivityAction(){
		$error = '';
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$myinfo = $this->getUserTable()->getUser($identity->user_id);
			if(!empty($myinfo)&&$myinfo->user_id){
				$request   = $this->getRequest();
				if ($request->isPost()){
					$post = $request->getPost();
					$activity_id =  $post['activity_id'];
					if($activity_id!=''){
						$acitivity = $this->getActivityTable()->getActivity($activity_id);
						if(!empty($acitivity)){
							$ActivityRsvp = new ActivityRsvp();
							$ActivityRsvp->group_activity_rsvp_user_id = $identity->user_id;
							$ActivityRsvp->group_activity_rsvp_activity_id = $activity_id;
							$ActivityRsvp->group_activity_rsvp_group_id = $acitivity->group_activity_group_id;
							if($this->getActivityRsvpTable()->saveActivityRsvp($ActivityRsvp)){
								$config = $this->getServiceLocator()->get('Config');
								$base_url = $config['pathInfo']['base_url'];
								$msg = $identity->user_given_name." is joined in your activity ".$acitivity->group_activity_title ;
								$subject = 'Event members';
								$from = 'admin@jeera.com';
								$this->UpdateNotifications($acitivity->group_activity_owner_user_id,$msg,1,$subject,$from,$identity->user_id,$acitivity->group_activity_group_id);
							}else{$error="Some error occured. Please try again";}
						}else{$error = "Activity not exist";}
					}else{$error = "Activity id required";}
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
	public function getUserTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;    
	}
	public function getGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;    
    }
	public function getActivityTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupActivityTable = (!$this->groupActivityTable)?$sm->get('Activity\Model\ActivityTable'):$this->groupActivityTable;    
    }
	public function getUserGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->userGroupTable = (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;    
    }
	public function getUserNotificationTable(){         
		$sm = $this->getServiceLocator();
		return  $this->userNotificationTable = (!$this->userNotificationTable)?$sm->get('Notification\Model\UserNotificationTable'):$this->userNotificationTable;    
    }
	public function getUserFriendTable(){
		$sm = $this->getServiceLocator();
		return  $this->userFriendTable = (!$this->userFriendTable)?$sm->get('User\Model\UserFriendTable'):$this->userFriendTable;    
	}
	public function getActivityInviteTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupActivityInviteTable = (!$this->groupActivityInviteTable)?$sm->get('Activity\Model\ActivityInviteTable'):$this->groupActivityInviteTable;    
    }
	public function getActivityRsvpTable(){
		$sm = $this->getServiceLocator();
		return  $this->activityRsvpTable = (!$this->activityRsvpTable)?$sm->get('Activity\Model\ActivityRsvpTable'):$this->activityRsvpTable;
    }
}