<?php
namespace Notification\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;	//Return model 
use Zend\View\Model\JsonModel;
use Zend\Session\Container; // We need this when using sessions
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
/*use Zend\Authentication\Result as Result;
use Zend\Authentication\Storage;*/ 
//Login Form
#Notification class
use Notification\Model\Notification;  
use Notification\Model\NotificationTable; 
class NotificationController extends AbstractActionController
{
    protected $NotificationTable;		
  	protected $userNotificationTable;
	protected $userTable;
	protected $activityTable;
	protected $groupTable;
    public function indexAction(){
		$error = '';
		$auth = new AuthenticationService();
		$viewModel = new ViewModel();
		$config = $this->getServiceLocator()->get('Config');
		$viewModel->setVariable('image_folders',$config['image_folders']);
		if ($auth->hasIdentity()) {
			$this->layout('layout/layout_user');
			$identity = $auth->getIdentity();			 
			$this->layout()->identity = $identity;	
			$profileWidget = $this->forward()->dispatch('User\Controller\UserProfile', array(
										'action' => 'profile',
										'member_profile'     => $identity->user_profile_name,							 
									));				
			$viewModel->addChild($profileWidget, 'profileWidget');
			$group_unread_count = $this->getUserNotificationTable()->getNotificationUnreadCount($identity->user_id,'Group');
			$viewModel->setVariable('group_unread_count',$group_unread_count);
			$friends_unread_count = $this->getUserNotificationTable()->getNotificationUnreadCount($identity->user_id,'Friends');
			$viewModel->setVariable('friends_unread_count',$friends_unread_count);
			$events_unread_count = $this->getUserNotificationTable()->getNotificationUnreadCount($identity->user_id,'Event');
			$viewModel->setVariable('events_unread_count',$events_unread_count);
			$Interactions_unread_count = $this->getUserNotificationTable()->getNotificationUnreadCount($identity->user_id,'Interactions');
			$viewModel->setVariable('Interactions_unread_count',$Interactions_unread_count);
			return $viewModel;
		}else{return $this->redirect()->toRoute('home', array('action' => 'index'));}
    }
	public function getnotificationsAction(){ 
		$error = '';
		$notification_list =array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();			
			if ($request->isPost()){
				$post = $request->getPost(); 
				$type = $post['type'];
				$page = (isset($post['page'])&&$post['page']!=null&&$post['page']!=''&&$post['page']!='undefined')?$post['page']:1;
				$limit =10;
				$page =($page>0)?$page-1:0;
				$offset = $page*$limit;
				$objnotification_list = $this->getUserNotificationTable()->getAllUserNotificationWithType($identity->user_id,$type,$offset,$limit);
				foreach($objnotification_list as $list){
					$sender_info = $this->getUserTable()->getProfileDetails($list->user_notification_sender_id);
					$reference_info = array();
					if($list->notification_type_title=='Group'||$list->notification_type_title=='Activity'||$list->notification_type_title=='Discussion'||$list->notification_type_title=='Photo'||$list->notification_type_title=='Video'){
						$reference_info =$this->getGroupTable()->getGroupDetails($list->user_notification_reference_id,$identity->user_id);
					}
					$notification_list[] = array(
								'user_notification_content'=>$list->user_notification_content,
								'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
								'user_notification_sender_id'=>$list->user_notification_sender_id,
								'user_notification_reference_id'=>$list->user_notification_reference_id,
								'user_notification_status'=>$list->user_notification_status,
								'notification_type_title' =>$list->notification_type_title,
								'sender_name' => $sender_info->user_given_name,
								'sender_profile_name' => $sender_info->user_profile_name,
								'sender_profile_photo' => $sender_info->profile_photo,
								'reference_info' =>$reference_info,
								'sender_user_fbid' =>$sender_info->user_fbid,
								
									);
				}
			}else{$error = "Unable to process";}	
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 
		$return_array['notification_list'] = $notification_list; 		
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}
	public function ajaxGetNotificationCountAction(){		 
		$sm = $this->getServiceLocator();
		$auth = new AuthenticationService();
		$error = array();
		$identity = null;
		$viewModel = new ViewModel();	
		$request   = $this->getRequest();
		$notification_count = 0;	
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();		 
			if($identity->user_id){
				$notification_count = $this->getUserNotificationTable()->getUserNotificationCountForUserUnread($identity->user_id); 
			}			
		}else{
			$error[] = "Your session has to be expired";
		}		 
		echo $notification_count;die();	 
	}	
	
	public function ajaxGetUserNotificationListAction(){
		$sm = $this->getServiceLocator();
		$auth = new AuthenticationService();
		$error = array();
		$identity = null;
		$viewModel = new ViewModel();	
		$request   = $this->getRequest();
		$notification_list = array();	
		$viewModel = new ViewModel();	
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();		 
			if($identity->user_id){
				$notification_list = $this->getUserNotificationTable()->getAllUnreadNotification($identity->user_id);
				$this->getUserNotificationTable()->makeNotificationsReaded($identity->user_id);
			}			
		}else{
			$error[] = "Your session has to be expired";
		}		 
		$viewModel->setVariable('error', $error);	
		$viewModel->setVariable('notification_list', $notification_list);
		$viewModel->setTerminal($request->isXmlHttpRequest());
		return $viewModel;
	}	
	
	public function getActivityTable(){
		 if (!$this->activityTable) {
            $sm = $this->getServiceLocator();
			$this->activityTable = $sm->get('Activity\Model\ActivityTable');
        }
        return $this->activityTable;
	}
	public function ajaxLoadMoreAction(){
		$sm = $this->getServiceLocator();
		$auth = new AuthenticationService();
		$error = array();
		$identity = null;
		$viewModel = new ViewModel();	
		$request   = $this->getRequest();
		$notification_list = array();	
		$viewModel = new ViewModel();
		$page = 0;			
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$user_id = $identity->user_id;
			$post = $request->getPost();		
			if ($request->isPost()){
				$page =$post->get('page');
				if(!$page)
				$page = 0;				 
			}
			$offset = $page*25;
			if($identity->user_id){
				$notification_list = $this->getUserNotificationTable()->getAllUserNotificationWithAllStatus($identity->user_id,$offset,20);
			}else{
				$error[] = "invalid request";
			}				
		}else{
			$error[] = "Your session has to be expired";
		}		 
		$viewModel->setVariable('error', $error);	
		$viewModel->setVariable('notification_list', $notification_list);	 
		$viewModel->setTerminal($request->isXmlHttpRequest());
		return $viewModel;
		
	}
	public function timeAgo($time_ago){
		$time_ago = strtotime($time_ago);
		$cur_time   = time();
		$time_elapsed   = $cur_time - $time_ago;
		$seconds    = $time_elapsed ;
		$minutes    = round($time_elapsed / 60 );
		$hours      = round($time_elapsed / 3600);
		$days       = round($time_elapsed / 86400 );
		$weeks      = round($time_elapsed / 604800);
		$months     = round($time_elapsed / 2600640 );
		$years      = round($time_elapsed / 31207680 );
		// Seconds
		if($seconds <= 60){
			return "just now";
		}
		//Minutes
		else if($minutes <=60){
			if($minutes==1){
				return "one minute ago";
			}
			else{
				return "$minutes minutes ago";
			}
		}
		//Hours
		else if($hours <=24){
			if($hours==1){
				return "an hour ago";
			}else{
				return "$hours hrs ago";
			}
		}
		//Days
		else if($days <= 7){
			if($days==1){
				return "yesterday";
			}else{
				return "$days days ago";
			}
		}
		//Weeks
		else if($weeks <= 4.3){
			if($weeks==1){
				return "a week ago";
			}else{
				return "$weeks weeks ago";
			}
		}
		//Months
		else if($months <=12){
			if($months==1){
				return "a month ago";
			}else{
				return "$months months ago";
			}
		}
		//Years
		else{
			if($years==1){
				return "one year ago";
			}else{
				return "$years years ago";
			}
		}
	}
	public function getUserNotificationTable(){
		$sm = $this->getServiceLocator();
		return  $this->userNotificationTable = (!$this->userNotificationTable)?$sm->get('Notification\Model\UserNotificationTable'):$this->userNotificationTable;		 
	}
	public function getGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;    
    }
	public function getUserTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;    
	}
}