<?php
namespace Application\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\View\Model\ViewModel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
class IndexController extends AbstractActionController
{	
	protected $groupTable;
	protected $tagCategoryTable;
	protected $groupTagTable;
	protected $userTable;
	protected $userNotificationTable;
	public function indexAction()
    {
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			return $this->redirect()->toRoute('memberprofile', array('member_profile' => $identity->user_profile_name));
		}else{
			$config = $this->getServiceLocator()->get('Config');
			$groups = $this->getGroupTable()->generalGroupList(6,0);
			$group_general_list = array();
			foreach($groups as $list){
				$group_general_list[] = array('group_id'=> $list['group_id'],
											'group_values'=>$list,
											'tagCategory'=>$this->getTagCategoryTable()->getGroupCategories(5,0,$list['group_id']),
											'tags' =>$this->getGroupTagTable()->fetchAllTagsOfGroup($list['group_id'])
										);	 
			}	 
			$result = new ViewModel(array(
			'groups' => $group_general_list,'image_folders'=> $config['image_folders'],
				'success'=>true,
			));		
			return $result; 
		}
    }
	public function ajaxGroupGeneralListAction(){
		$group_general_list = array();
		$request   = $this->getRequest();
		if ($request->isPost()){
			$post = $request->getPost();
			$offset = ($post->get('page'))?$post->get('page')*6:0;
			$groups = $this->getGroupTable()->generalGroupList(6,$offset);		
			foreach($groups as $list){
				$group_general_list[] = array('group_id'=> $list['group_id'],
										'group_values'=>$list,
										'tagCategory'=>$this->getTagCategoryTable()->getGroupCategories(5,0,$list['group_id']),
										'tags' =>$this->getGroupTagTable()->fetchAllTagsOfGroup($list['group_id'])
									);	
			}
		}
		$result = new JsonModel(array(
	    'groups' => $group_general_list,      
        ));		
        return $result; 
	}
	public function quicksearchAction(){
		$error = '';
		$arrMembers = array();
		$auth = new AuthenticationService();
		$groupinfo = array();
		$user_info = array();
		$request   = $this->getRequest();			
		if ($request->isPost()){
			$post = $request->getPost(); 
			$searchdata = $post['searchdata'];	
			$groupinfo = $this->getGroupTable()->searchGroup($searchdata,2,0) ;
			$user_info = $this->getUserTable()->searchUser($searchdata,2,0);  
			  
		}else{$error = "Unable to process";}		 
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 
		$return_array['groupinfo'] = $groupinfo;
		$return_array['user_info'] = $user_info; 			 			
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function searchAction(){
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
			$searchdata = ($this->getRequest()->getQuery('str'))?$this->getRequest()->getQuery('str'):'';
			$groupinfo = $this->getGroupTable()->searchGroup($searchdata,5,0) ;
			$user_info = $this->getUserTable()->searchUser($searchdata,5,0);  
			$viewModel->setVariable('groupinfo',$groupinfo);
			$viewModel->setVariable('user_info',$user_info);
			$viewModel->setVariable('searchdata',$searchdata);
			return $viewModel; 
		}else{return $this->redirect()->toRoute('home', array('action' => 'index'));}
	}
	public function moresearchresultsAction(){
		$error = '';
		$arrMembers = array();
		$auth = new AuthenticationService();
		$groupinfo = array();
		$user_info = array();
		$request   = $this->getRequest();			
		if ($request->isPost()){
			$post = $request->getPost(); 
			$searchdata = $post['searchdata'];
			$type = $post['type'];
			$page = (isset($post['page'])&&$post['page']!=null&&$post['page']!=''&&$post['page']!='undefined')?$post['page']:1;
			$limit =5;
			$page =($page>0)?$page-1:0;
			$offset = $page*$limit;
			if($type == 'All'){
				$groupinfo = $this->getGroupTable()->searchGroup($searchdata,5,$offset) ;
				$user_info = $this->getUserTable()->searchUser($searchdata,5,$offset);  
			}
			if($type == 'Groups'){
				$groupinfo = $this->getGroupTable()->searchGroup($searchdata,5,$offset) ;				 
			}
			if($type == 'Members'){				 
				$user_info = $this->getUserTable()->searchUser($searchdata,5,$offset);  
			}
			  
		}else{$error = "Unable to process";}		 
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 
		$return_array['groupinfo'] = $groupinfo;
		$return_array['user_info'] = $user_info; 			 			
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function getNotificationCountAction(){
		$error = '';
		$notification_count =0;
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();			
			if ($request->isPost()){
				$notification_count = $this->getUserNotificationTable()->getUserNotificationCountForUserUnread($identity->user_id);
			}else{$error = "Unable to process";}	
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 
		$return_array['notification_count'] = $notification_count; 		
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function getNotificationlistAction(){
		$error = '';
		$notification_list =array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();			
			if ($request->isPost()){
				$objnotification_list = $this->getUserNotificationTable()->getAllUserNotificationWithAllStatus($identity->user_id,0,5);
				if(!empty($objnotification_list)){
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
								'reference_info' =>$reference_info
									);
				}
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
	public function makenotificationreadedAction(){
		$error = '';		 
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();			
			if ($request->isPost()){
				$this->getUserNotificationTable()->makeNotificationsReaded($identity->user_id);
			}else{$error = "Unable to process";}	
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 		 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
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
	public function getGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;    
    }
	public function getTagCategoryTable(){
		$sm = $this->getServiceLocator();
		return  $this->tagCategoryTable = (!$this->tagCategoryTable)?$sm->get('Tag\Model\TagCategoryTable'):$this->tagCategoryTable;    
    }
	public function getGroupTagTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTagTable = (!$this->groupTagTable)?$sm->get('Tag\Model\GroupTagTable'):$this->groupTagTable;    
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
