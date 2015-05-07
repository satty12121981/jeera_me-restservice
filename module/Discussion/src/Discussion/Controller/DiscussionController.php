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
	protected $commentTable;
	protected $likeTable;

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
									$process = 'New Discussion';
									$this->UpdateNotifications($members->user_group_user_id,$msg,6,$subject,$from,$identity->user_id,$IdDiscussion,$process);

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
	public function statusviewAction(){
		$error = '';
		$auth = new AuthenticationService();
		$viewModel = new ViewModel();
		$config = $this->getServiceLocator()->get('Config');
		$viewModel->setVariable('image_folders',$config['image_folders']);
		if ($auth->hasIdentity()) {
			$this->layout('layout/layout_user');
			$identity = $auth->getIdentity();
			$profile_data = $this->getUserTable()->getProfileDetails($identity->user_id);
			$viewModel->setVariable( 'profile_data' , $profile_data);	
			$group_seo = $this->params('group_seo');
			$this->layout()->identity = $identity;       
			$groupinfo =  $this->getGroupsTable()->getGroupBySeoTitle($group_seo);		
			if(!empty($groupinfo)&&$groupinfo->group_id){
				$group_details = $this->getGroupsTable()->getGroupDetails($groupinfo->group_id,$identity->user_id);
				$viewModel->setVariable( 'group_details' , $group_details);
				$status_id =  $this->params('id');
				if(!empty($status_id)){
					$discussion_details = array();
					$discussion = $this->getDiscussionTable()->getDiscussionForView($status_id); 
					if(!empty($discussion)){
						if($discussion->group_discussion_group_id == $groupinfo->group_id){
							$SystemTypeData   = $this->getGroupsTable()->fetchSystemType("Discussion");
							$like_details     = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$status_id,$identity->user_id); 
							$comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$status_id,$identity->user_id); 							 
							$is_admin = 0;
							if($this->getUserGroupTable()->checkOwner($groupinfo->group_id,$discussion->user_id)){
								$is_admin = 1;
							}
							$str_liked_users  = '';
							$arr_likedUsers = array(); 
							if(!empty($like_details)&&isset($like_details['likes_counts'])){  
								$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$status_id,$identity->user_id,2,0);
								
								if($like_details['is_liked']==1){
									$arr_likedUsers[] = 'you';
								}
								if($like_details['likes_counts']>0&&!empty($liked_users)){
									foreach($liked_users as $likeuser){
										$arr_likedUsers[] = $likeuser['user_given_name'];
									}
								}
								 
							}						 
							$discussion_details = array(
								"group_discussion_id" => $discussion->group_discussion_id,
								"group_discussion_content" => $discussion->group_discussion_content,			 
								"user_given_name" => $discussion->user_given_name,
								"group_title" =>$discussion->group_title,
								"group_seo_title" =>$discussion->group_seo_title,
								"group_id" =>$discussion->group_id,	
								"user_id" => $discussion->user_id,
								"user_profile_name" => $discussion->user_profile_name,
								"profile_photo" => $discussion->profile_photo,	
								"user_fbid" =>$discussion->user_fbid,
								"like_count"	=>$like_details['likes_counts'],
								"is_liked"	=>$like_details['is_liked'],
								"comment_counts"	=>$comment_details['comment_counts'],
								"is_commented"	=>$comment_details['is_commented'],
								"arr_likedUsers"=>$arr_likedUsers,							 
								'is_admin'=>$is_admin,
								'time'=>$this->timeAgo($discussion->group_discussion_added_timestamp),
							);							
							$viewModel->setVariable( 'discussion_details' , $discussion_details);
							return $viewModel;
						}else{ return $this->redirect()->toRoute('home/404', array('action' => 'nopage')); }								
					}else{ return $this->redirect()->toRoute('home/404', array('action' => 'nopage')); }							  	
				}else{ return $this->redirect()->toRoute('home/404', array('action' => 'nopage')); }
			}else{ return $this->redirect()->toRoute('home/404', array('action' => 'nopage')); }
		}else{return $this->redirect()->toRoute('home', array('action' => 'nopage'));}
	}
	public function timeAgo($time_ago){ //echo $time_ago;die();
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
	public function UpdateNotifications($user_notification_user_id,$msg,$type,$subject,$from,$sender,$reference_id,$processs){

		$UserGroupNotificationData = array();						

		$UserGroupNotificationData['user_notification_user_id'] =$user_notification_user_id;		 

		$UserGroupNotificationData['user_notification_content']  = $msg;

		$UserGroupNotificationData['user_notification_added_timestamp'] = date('Y-m-d H:i:s');			

		$UserGroupNotificationData['user_notification_notification_type_id'] = $type;

		$UserGroupNotificationData['user_notification_status'] = 'unread';

		$UserGroupNotificationData['user_notification_sender_id'] = $sender;

		$UserGroupNotificationData['user_notification_reference_id'] = $reference_id;
		$UserGroupNotificationData['user_notification_process'] = $processs;
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
	public function getLikeTable(){         
		$sm = $this->getServiceLocator();
        return  $this->likeTable = (!$this->likeTable)?$sm->get('Like\Model\LikeTable'):$this->likeTable;       
    }
	public function getCommentTable(){
		$sm = $this->getServiceLocator();
		return  $this->commentTable = (!$this->commentTable)?$sm->get('Comment\Model\CommentTable'):$this->commentTable;   
	}
}

