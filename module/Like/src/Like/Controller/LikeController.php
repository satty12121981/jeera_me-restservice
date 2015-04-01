<?php
####################Like Controller #################################
#namespace for module like
namespace Like\Controller;
#library uses
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;	//Return model 
use Zend\View\Model\JsonModel;
use Zend\Session\Container;		// We need this when using sessions     
use Zend\Authentication\AuthenticationService;		//Needed for checking User session
use Zend\Authentication\Adapter\DbTable as AuthAdapter;		//Db adapter
use Zend\Crypt\BlockCipher;		# For encryption 
use \Exception;		 
use Like\Model\Like;  
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;

use Notification\Model\UserNotification; 
class LikeController extends AbstractActionController
{ 	
	protected $userTable;
	protected $groupTable;
	protected $userGroupTable;
	protected $photoTable = ""; 
	protected $activityTable = ""; 
	protected $discussionTable = "";
	protected $commentTable = ""; 
	protected $albumTable = ""; 
	protected $LikeTable = "";
	protected $likeTable;
	protected $remoteAddr;
	protected $albumDataTable;	
	protected $activityRsvpTable;	
	protected $userNotificationTable;
	protected $groupMediaTable;
	public function __construct(){
		return $this;
	}	
	#this function will load the css and javascript need for perticular action
	protected function getViewHelper($helperName){
    	return $this->getServiceLocator()->get('viewhelpermanager')->get($helperName);
	}    	
	public function indexAction(){
		return $this;
	}	
	#This will like post and cooments
	public function LikesAction() {		
		$error = '';
		$like_count = 0;
		$str_liked_users = '';
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$userinfo = $this->getUserTable()->getUser($identity->user_id);
			if(!empty($userinfo)&&$userinfo->user_id){ 
				$request   = $this->getRequest();
				if ($request->isPost()){
					$post = $request->getPost();
					$system_type = $post['system_type'];
					$refer_id = $post['content_id'];
					$SystemTypeData = $this->getGroupTable()->fetchSystemType($system_type);
					if(!empty($SystemTypeData)){
						switch($system_type){
							case 'Discussion':
								if($refer_id!=''){
									$discussion_data = $this->getDiscussionTable()->getDiscussion($refer_id);
									if(!empty($discussion_data)){
										if($this->addLIke($identity->user_id,$SystemTypeData->system_type_id,$refer_id)){
											$like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$identity->user_id);
											$like_count = $like_details->likes_counts;
											$joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($discussion_data->group_discussion_group_id); 
											$group  = $this->getGroupTable()->getPlanetinfo($discussion_data->group_discussion_group_id);										
											if(!empty($like_details)){  
												$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$refer_id,$identity->user_id,2,0);
												$arr_likedUsers = array();
												if($like_details['is_liked']==1){
													$arr_likedUsers[] = 'you';
												}
												if($like_details['likes_counts']>0&&!empty($liked_users)){
													foreach($liked_users as $likeuser){
														$arr_likedUsers[] = $likeuser['user_given_name'];
													}
												}
												if(!empty($arr_likedUsers)){
												$str_liked_users = implode(',',$arr_likedUsers);}
											}
											foreach($joinedMembers as $members){ 
												if($members->user_group_user_id!=$identity->user_id){
													$config = $this->getServiceLocator()->get('Config');
													$base_url = $config['pathInfo']['base_url'];
													$msg = $identity->user_given_name." Like one status in the group ".$group->group_title;
													$subject = 'Like status';
													$from = 'admin@jeera.com';
													$this->UpdateNotifications($members->user_group_user_id,$msg,2,$subject,$from,$identity->user_id,$refer_id);
												}
											}
										}
									}else{$error = "Content Not exist";}
								}else{$error = "Content Not exist";}
							break;
							case 'Media':
								if($refer_id!=''){
									$media_data = $this->getGroupMediaTable()->getMedia($refer_id);
									if(!empty($media_data)){
										if($this->addLIke($identity->user_id,$SystemTypeData->system_type_id,$refer_id)){
											$like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$identity->user_id);
											$like_count = $like_details->likes_counts;
											$joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($media_data->media_added_group_id); 
											$group  = $this->getGroupTable()->getPlanetinfo($media_data->media_added_group_id);
											if(!empty($like_details)){  
												$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$refer_id,$identity->user_id,2,0);
												$arr_likedUsers = array();
												if($like_details['is_liked']==1){
													$arr_likedUsers[] = 'you';
												}
												if($like_details['likes_counts']>0&&!empty($liked_users)){
													foreach($liked_users as $likeuser){
														$arr_likedUsers[] = $likeuser['user_given_name'];
													}
												}
												if(!empty($arr_likedUsers)){
												$str_liked_users = implode(',',$arr_likedUsers);}
											}
											foreach($joinedMembers as $members){ 
												if($members->user_group_user_id!=$identity->user_id){
													$config = $this->getServiceLocator()->get('Config');
													$base_url = $config['pathInfo']['base_url'];
													$msg = $identity->user_given_name." Like one media in the group ".$group->group_title;
													$subject = 'Like Media';
													$from = 'admin@jeera.com';
													$this->UpdateNotifications($members->user_group_user_id,$msg,2,$subject,$from,$identity->user_id,$refer_id);
												}
											}
										}
									}else{$error = "Content Not exist";}
								}else{$error = "Content Not exist";}
							break;
							case 'Activity':
								if($refer_id!=''){
									$activity_data = $this->getActivityTable()->getActivity($refer_id);
									if(!empty($activity_data)){
										if($this->addLIke($identity->user_id,$SystemTypeData->system_type_id,$refer_id)){
											$like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$identity->user_id);
											$like_count = $like_details->likes_counts;
											$joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($activity_data->group_activity_group_id); 
											$group  = $this->getGroupTable()->getPlanetinfo($activity_data->group_activity_group_id);
											if(!empty($like_details)){  
												$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$refer_id,$identity->user_id,2,0);
												$arr_likedUsers = array();
												if($like_details['is_liked']==1){
													$arr_likedUsers[] = 'you';
												}
												if($like_details['likes_counts']>0&&!empty($liked_users)){
													foreach($liked_users as $likeuser){
														$arr_likedUsers[] = $likeuser['user_given_name'];
													}
												}
												if(!empty($arr_likedUsers)){
												$str_liked_users = implode(',',$arr_likedUsers);}
											}
											foreach($joinedMembers as $members){ 
												if($members->user_group_user_id!=$identity->user_id){
													$config = $this->getServiceLocator()->get('Config');
													$base_url = $config['pathInfo']['base_url'];
													$msg = $identity->user_given_name." Like one activity in the group ".$group->group_title;
													$subject = 'Like Activity';
													$from = 'admin@jeera.com';
													$this->UpdateNotifications($members->user_group_user_id,$msg,2,$subject,$from,$identity->user_id,$refer_id);
												}
											}
										}
									}else{$error = "Content Not exist";}
								}else{$error = "Content Not exist";}
							break;
							case 'Comment':
								if($refer_id!=''){
									$comment_data = $this->getCommentTable()->getComment($refer_id);
									if(!empty($comment_data)){
										if($this->addLIke($identity->user_id,$SystemTypeData->system_type_id,$refer_id)){
											$like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$identity->user_id);
											$like_count = $like_details->likes_counts;											 									
											if(!empty($like_details)){  
												$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$refer_id,$identity->user_id,2,0);
												$arr_likedUsers = array();
												if($like_details['is_liked']==1){
													$arr_likedUsers[] = 'you';
												}
												if($like_details['likes_counts']>0&&!empty($liked_users)){
													foreach($liked_users as $likeuser){
														$arr_likedUsers[] = $likeuser['user_given_name'];
													}
												}
												if(!empty($arr_likedUsers)){
												$str_liked_users = implode(',',$arr_likedUsers);}
											}											 
											if($comment_data->comment_by_user_id!=$identity->user_id){
												$config = $this->getServiceLocator()->get('Config');
												$base_url = $config['pathInfo']['base_url'];
												$msg = $identity->user_given_name." Like your comment";
												$subject = 'Like status';
												$from = 'admin@jeera.com';
												$this->UpdateNotifications($comment_data->comment_by_user_id,$msg,2,$subject,$from,$identity->user_id,$refer_id);
											}											 
										}
									}else{$error = "Content Not exist";}
								}else{$error = "Content Not exist";}
							break;
						}
					}else{$error = "Unable to process";}
				}else{$error = "Unable to process";}
			}else{	$error = "User not exist in the system";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;
		$return_array['like_count'] = $like_count;	
		$return_array['liked_users'] = $str_liked_users	;	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}
	public function addLIke($user_id,$type,$content_id){
		$sm = $this->getServiceLocator();
		$this->remoteAddr = $sm->get('ControllerPluginManager')->get('GenericPlugin')->getRemoteAddress();
		$Like = new Like();
		$this->likeTable = $sm->get('Like\Model\LikeTable');
		$likeData = $this->likeTable->LikeExistsCheck($type,$content_id,$user_id);
		$LikesData = array();
		$LikesData['like_system_type_id'] = $type;
		$LikesData['like_by_user_id'] = $user_id;
		$LikesData['like_refer_id'] = $content_id;
		$LikesData['like_status'] = "active";		
		$LikesData['like_added_ip_address'] =  $this->remoteAddr;
		$Like->exchangeArray($LikesData);
		$insertedLikesId = $this->likeTable->saveLike($Like);
		if($insertedLikesId)
		return true;
		else
		return false;
	}	
	#This will load all Subgroups Of Group   
	public function UnLikesAction() { 
		$error = '';
		$like_count = 0;
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$userinfo = $this->getUserTable()->getUser($identity->user_id);
			if(!empty($userinfo)&&$userinfo->user_id){ 
				$request   = $this->getRequest();
				if ($request->isPost()){
					$post = $request->getPost();
					$system_type = $post['system_type'];
					$refer_id = $post['content_id'];
					$SystemTypeData = $this->getGroupTable()->fetchSystemType($system_type);
					if(!empty($SystemTypeData)){
						switch($system_type){
							case 'Discussion':
								if($refer_id!=''){
									$discussion_data = $this->getDiscussionTable()->getDiscussion($refer_id);
									if(!empty($discussion_data)){
										$likeData = $this->getLikeTable()->LikeExistsCheck($SystemTypeData->system_type_id,$refer_id,$identity->user_id);
										if ( !empty( $likeData->like_id ) ) {
											if( $this->getLikeTable()->deleteLikeByReference($SystemTypeData->system_type_id,$likeData->like_by_user_id,$refer_id)){											 
												$like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$identity->user_id);
												$like_count = $like_details->likes_counts;
											}
										}
									}else{$error = "Content Not exist";}
								}else{$error = "Content Not exist";}
							break;
							case 'Media':
								if($refer_id!=''){
									$media_data = $this->getGroupMediaTable()->getMedia($refer_id);
									if(!empty($media_data)){
										$likeData = $this->getLikeTable()->LikeExistsCheck($SystemTypeData->system_type_id,$refer_id,$identity->user_id);
										if ( !empty( $likeData->like_id ) ) {
											if( $this->getLikeTable()->deleteLikeByReference($SystemTypeData->system_type_id,$likeData->like_by_user_id,$refer_id)){											 
												$like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$identity->user_id);
												$like_count = $like_details->likes_counts;
											}
										}
									}else{$error = "Content Not exist";}
								}else{$error = "Content Not exist";}
							break;
							case 'Activity':
								if($refer_id!=''){
									$activity_data = $this->getActivityTable()->getActivity($refer_id);
									if(!empty($activity_data)){										 
										$likeData = $this->getLikeTable()->LikeExistsCheck($SystemTypeData->system_type_id,$refer_id,$identity->user_id);
										if ( !empty( $likeData->like_id ) ) {
											if( $this->getLikeTable()->deleteLikeByReference($SystemTypeData->system_type_id,$likeData->like_by_user_id,$refer_id)){											 
												$like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$identity->user_id);
												$like_count = $like_details->likes_counts;
											}
										}
									}else{$error = "Content Not exist";}
								}else{$error = "Content Not exist";}
							break;
							case 'Comment':
								if($refer_id!=''){
									$comment_data = $this->getCommentTable()->getComment($refer_id);
									if(!empty($comment_data)){										 
										$likeData = $this->getLikeTable()->LikeExistsCheck($SystemTypeData->system_type_id,$refer_id,$identity->user_id);
										if ( !empty( $likeData->like_id ) ) {
											if( $this->getLikeTable()->deleteLikeByReference($SystemTypeData->system_type_id,$likeData->like_by_user_id,$refer_id)){											 
												$like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$identity->user_id);
												$like_count = $like_details->likes_counts;
											}
										}
									}else{$error = "Content Not exist";}
								}else{$error = "Content Not exist";}
							break;
						}
					}else{$error = "Unable to process";}
				}else{$error = "Unable to process";}
			}else{	$error = "User not exist in the system";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;
		$return_array['like_count'] = $like_count;				
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}	
	#This will load all Subgroups Of Group   
	public function LikesUsersListAction() {	
		 $error = '';
		$like_count = 0;
		$liked_users = array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$userinfo = $this->getUserTable()->getUser($identity->user_id);
			if(!empty($userinfo)&&$userinfo->user_id){ 
				$request   = $this->getRequest();
				if ($request->isPost()){
					$post = $request->getPost();
					$system_type = $post['system_type'];
					$refer_id = $post['content_id'];
					$SystemTypeData = $this->getGroupTable()->fetchSystemType($system_type);
					if(!empty($SystemTypeData)){
						$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$refer_id,$identity->user_id,10,0);
						 
					}else{$error = "Unable to process";}
				}else{$error = "Unable to process";}
			}else{	$error = "User not exist in the system";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;
		$return_array['liked_users'] = $liked_users;				
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}	
	#This will load all Subgroups Of Group   
	public function CommentsLikesUsersListAction() {	
		$error = array();	#Error variable
		$success = array();	#success message variable
		$GroupId = "";
		$SubGroupId = "";	//This will hold the galaxy id
		$GroupReferId = ""; 
		$SystemTypeId = ""; 				
		$userData = array();	//this will hold data from y2m_user table
		$groupData = array();//this will hold the Galaxy data
		$SubGroupData = array();//this will hold the Planet data
		$LikesUsersListData = array();//this will hold the Planet data		
		$GroupId = $this->params('group_id'); 				
		$SubGroupId = $this->params('sub_group_id'); 
		$GroupReferId = $this->params('group_refer_id');
		$SystemTypeId = $this->params('system_type_id'); 	
		$SubSystemTypeId = $this->params('sub_system_type_id'); 	
		#db connectivity
		$sm = $this->getServiceLocator();
		$this->remoteAddr = $sm->get('ControllerPluginManager')->get('GenericPlugin')->getRemoteAddress();
		$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');		
		try {	
			$request   = $this->getRequest();
			$auth = new AuthenticationService();	
			$identity = null;   			
			if ($auth->hasIdentity()) {				
				// Identity exists; get it
				$identity = $auth->getIdentity();				
				#fetch the user Galaxy
				$this->userTable = $sm->get('User\Model\UserTable');				
				#check the identity against the DB
				$userData = $this->userTable->getUser($identity->user_id);		
				if(isset($userData->user_id) && !empty($userData->user_id) && isset($GroupId) && !empty($GroupId) && isset($SubGroupId) && !empty($SubGroupId)) {				
					$this->groupTable = $sm->get('Group\Model\GroupTable');					
					#get Group Info
					$SubGroupData = $this->groupTable->getSubGroupForSEO($SubGroupId);
					#fetch the Galaxy Info
					$groupData = $this->groupTable->getGroup($SubGroupData->group_parent_group_id);						
					$this->commentTable = $sm->get('Comment\Model\CommentTable');
					$SystemTypeData = $this->groupTable->fetchSystemType($SubSystemTypeId);					
					$this->discussionTable = $sm->get('Discussion\Model\DiscussionTable');					
					#fetch the Discussion planet details
					$GroupDiscussionCommentsData = $this->commentTable->getComment($GroupReferId);					
					#add discussion code
					if(isset($SubGroupData->group_id) && !empty($SubGroupData->group_id) && isset($SubGroupData->group_parent_group_id) && !empty($SubGroupData->group_parent_group_id) && isset($GroupDiscussionCommentsData->comment_id) && !empty($GroupDiscussionCommentsData->comment_id) ) {						
						$this->likeTable = $sm->get('Like\Model\LikeTable');	
						#fetch the Discussion Like of planet details
						$LikesUsersListData = $this->likeTable->fetchLikesUsersByReference($SystemTypeData->system_type_id,$GroupReferId);	
					}
				}			
			} //if ($auth->hasIdentity()) 			
		} catch (\Exception $e) {
			echo "Caught exception: " . get_class($e) . "\n";
			echo "Message: " . $e->getMessage() . "\n";			 
		}		
		
		$viewModel = new ViewModel(array('userData' => $userData,'groupData' => $groupData,'SubGroupData' => $SubGroupData, 'LikesUsersListData' => $LikesUsersListData, 'Group_Refer_Id' => $GroupReferId,'System_Type_Id' => $SystemTypeId, 'error' => $error, 'success' => $success, 'flashMessages' => $this->flashMessenger()->getMessages()));
		$viewModel->setTerminal($request->isXmlHttpRequest());
		return $viewModel;	
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
	public function getActivityRsvpTable(){
        if (!$this->activityRsvpTable) {
            $sm = $this->getServiceLocator();
            $this->activityRsvpTable = $sm->get('Activity\Model\ActivityRsvpTable');
        }
        return $this->activityRsvpTable;
    }
	public function getActivityTable(){
		$sm = $this->getServiceLocator();
		return  $this->activityTable = (!$this->activityTable)?$sm->get('Activity\Model\ActivityTable'):$this->activityTable;    
    }
	public function getGroupTable(){
        $sm = $this->getServiceLocator();
		return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;    
    }
	public function getUserGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->userGroupTable = (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;    
    }
	public function getDiscussionTable(){
		$sm = $this->getServiceLocator();
		return  $this->discussionTable = (!$this->discussionTable)?$sm->get('Discussion\Model\DiscussionTable'):$this->discussionTable;    
    }
	public function getGroupMediaTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupMediaTable = (!$this->groupMediaTable)?$sm->get('Groups\Model\GroupMediaTable'):$this->groupMediaTable;    
	}
	public function getUserNotificationTable(){
        if (!$this->userNotificationTable) {
            $sm = $this->getServiceLocator();
            $this->userNotificationTable = $sm->get('Notification\Model\UserNotificationTable');
        }
        return $this->userNotificationTable;
    }
	public function getLikeTable(){
		$sm = $this->getServiceLocator();
		return  $this->likeTable = (!$this->likeTable)?$sm->get('Like\Model\LikeTable'):$this->likeTable; 
	}
	public function getUserTable(){
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('User\Model\UserTable');
        }
        return $this->userTable;
    }
	public function getCommentTable(){
		$sm = $this->getServiceLocator();
		return  $this->commentTable = (!$this->commentTable)?$sm->get('Comment\Model\CommentTable'):$this->commentTable;   
	}
}
