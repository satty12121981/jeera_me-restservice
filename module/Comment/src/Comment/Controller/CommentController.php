<?php

####################Comment Controller #################################



namespace Comment\Controller;



use Zend\Mvc\Controller\AbstractActionController;

use Zend\View\Model\ViewModel;	//Return model 

use Zend\View\Model\JsonModel;

use Zend\Session\Container; // We need this when using sessions     

use Zend\Authentication\AuthenticationService;	//Needed for checking User session

use Zend\Authentication\Adapter\DbTable as AuthAdapter;	//Db apapter

use Zend\Crypt\BlockCipher;		# For encryption

/*use Zend\Authentication\Result as Result;

use Zend\Authentication\Storage;*/

 

#Group classs

 use Comment\Model\Comment;  

use \Exception;		#Exception class for handling exception

 

use Zend\View\Helper\HelperInterface;

use Zend\View\Renderer\RendererInterface;

 

use Zend\Mail;

use Zend\Mime\Message as MimeMessage;

use Zend\Mime\Part as MimePart;

use Notification\Model\UserNotification; 

class CommentController extends AbstractActionController

{     

	protected $userTable;

	protected $groupTable;

	protected $userGroupTable;

	protected $photoTable = ""; 

	protected $activityTable = ""; 

	protected $discussionTable = ""; 

	protected $albumTable = ""; 

	protected $commentTable ="";

	protected $likeTable;

	protected $albumDataTable;	

	protected $activityRsvpTable;

	protected $userNotificationTable;

	protected $albumTagTable;

	protected $groupMediaTable;

	 

	public function __construct(){

		return $this;

	}	

	#this function will load the css and javascript need for perticular action

	protected function getViewHelper($helperName){

    	return $this->getServiceLocator()->get('viewhelpermanager')->get($helperName);

	}    

	public function CommentsAction(){		

		$error = '';

		$comment_count = 0;	

		$comment_id		=0;

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

					$comment = $post['txt_comment'];
					$hashedUser  = $post['hashedUser'];
					
					if($comment!=''&&$comment!='null'){

						$SystemTypeData = $this->getGroupTable()->fetchSystemType($system_type);

						if(!empty($SystemTypeData)){ 

							switch($system_type){

								case 'Discussion':

									if($refer_id!=''){

										$discussion_data = $this->getDiscussionTable()->getDiscussion($refer_id);

										if(!empty($discussion_data)){

											$comment_id = $this->addComment($identity->user_id,$SystemTypeData->system_type_id,$refer_id,$comment);

											if($comment_id){

												$comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$refer_id,$identity->user_id);

												$comment_count = $comment_details['comment_counts'];

												$joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($discussion_data->group_discussion_group_id); 

												$group  = $this->getGroupTable()->getPlanetinfo($discussion_data->group_discussion_group_id);
												foreach($hashedUser as $users){
													if($users!=$identity->user_id){
													$config = $this->getServiceLocator()->get('Config');

													$base_url = $config['pathInfo']['base_url'];

													$msg = $identity->user_given_name." mentioned you in a comment on status in the group ".$group->group_title;

													$subject = 'Comment status';

													$from = 'admin@jeera.com';
													$process = 'comment_hashed';
													$this->UpdateNotifications($users,$msg,6,$subject,$from,$identity->user_id,$refer_id,$process);
													}
												}									 

												foreach($joinedMembers as $members){ 

													if($members->user_group_user_id!=$identity->user_id&&!in_array($members->user_group_user_id,$hashedUser)){

														$config = $this->getServiceLocator()->get('Config');

														$base_url = $config['pathInfo']['base_url'];														 
														$msg = $identity->user_given_name." Commented one status in the group ".$group->group_title;
														
														$subject = 'Comment status';

														$from = 'admin@jeera.com';
														$process = 'comment';
														$this->UpdateNotifications($members->user_group_user_id,$msg,6,$subject,$from,$identity->user_id,$refer_id,$process);

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

											$comment_id = $this->addComment($identity->user_id,$SystemTypeData->system_type_id,$refer_id,$comment);

											if($comment_id){	

												$comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$refer_id,$identity->user_id);

												$comment_count = $comment_details['comment_counts'];

												$joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($media_data->media_added_group_id); 

												$group  = $this->getGroupTable()->getPlanetinfo($media_data->media_added_group_id);
												foreach($hashedUser as $users){
													if($users!=$identity->user_id){
													$config = $this->getServiceLocator()->get('Config');

													$base_url = $config['pathInfo']['base_url'];

													$msg = $identity->user_given_name." mentioned you in a comment on status in the group ".$group->group_title;

													$subject = 'Comment status';

													$from = 'admin@jeera.com';
													$process = 'comment_hashed';
													$this->UpdateNotifications($users,$msg,6,$subject,$from,$identity->user_id,$refer_id,$process);
													}
												}	
												foreach($joinedMembers as $members){ 

													if($members->user_group_user_id!=$identity->user_id&&!in_array($members->user_group_user_id,$hashedUser)){

														$config = $this->getServiceLocator()->get('Config');

														$base_url = $config['pathInfo']['base_url'];

														$msg = $identity->user_given_name." Commented one media in the group ".$group->group_title;

														$subject = 'Comment Media';

														$from = 'admin@jeera.com';
														$process = 'comment';
														$this->UpdateNotifications($users,$msg,8,$subject,$from,$identity->user_id,$refer_id,$process);

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

											$comment_id = $this->addComment($identity->user_id,$SystemTypeData->system_type_id,$refer_id,$comment);

											if($comment_id){

												$comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$refer_id,$identity->user_id);

												$comment_count = $comment_details['comment_counts'];

												$joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($activity_data->group_activity_group_id); 

												$group  = $this->getGroupTable()->getPlanetinfo($activity_data->group_activity_group_id);												 
												foreach($hashedUser as $users){
													if($users!=$identity->user_id){
													$config = $this->getServiceLocator()->get('Config');

													$base_url = $config['pathInfo']['base_url'];

													$msg = $identity->user_given_name." mentioned you in a comment on status in the group ".$group->group_title;

													$subject = 'Comment status';

													$from = 'admin@jeera.com';
													$process = 'comment_hashed';
													$this->UpdateNotifications($users,$msg,6,$subject,$from,$identity->user_id,$refer_id,$process);
													}
												}	
												foreach($joinedMembers as $members){ 

													if($members->user_group_user_id!=$identity->user_id&&!in_array($members->user_group_user_id,$hashedUser)){

														$config = $this->getServiceLocator()->get('Config');

														$base_url = $config['pathInfo']['base_url'];

														$msg = $identity->user_given_name." commented one activity in the group ".$group->group_title;

														$subject = 'commented Activity';

														$from = 'admin@jeera.com';
														$process = 'comment';
														$this->UpdateNotifications($members->user_group_user_id,$msg,7,$subject,$from,$identity->user_id,$refer_id,$process);

													}

												}

											}

										}else{$error = "Content Not exist";}

									}else{$error = "Content Not exist";}

								break;

							}						

						}else{$error = "Unable to process";}

					}else{$error = "Type your comments";}

				}else{$error = "Unable to process";}

			}else{	$error = "User not exist in the system";}

		}else{$error = "Your session has to be expired";}

		$return_array= array();		 

		$return_array['process_status'] = (empty($error))?'success':'failed';

		$return_array['process_info'] = $error;	

		$return_array['comment_id'] = $comment_id;	

		$return_array['comment_count'] = $comment_count;			

		$result = new JsonModel(array(

		'return_array' => $return_array,      

		));		

		return $result;

	}

	public function getCommentsAction(){

		$error = '';

		$comment_count = 0;	

		$comments = array();

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

					$page = 0;

					$page = $post['page'];

					$offset = $page>0?($page-1)*10:0;

					$SystemTypeData = $this->getGroupTable()->fetchSystemType($system_type);

					$commentSystemTYpe =$this->getGroupTable()->fetchSystemType('Comment'); 

						if(!empty($SystemTypeData)){

							$comments_details = $this->getCommentTable()->getAllCommentsWithLike($SystemTypeData->system_type_id,$commentSystemTYpe->system_type_id,$refer_id,$identity->user_id,10,$offset);

							if(!empty($comments_details)){

								foreach($comments_details as $list){

									$str_liked_users = '';

									$arr_likedUsers = array();

									$like_details = $this->getLikeTable()->fetchLikesCountByReference($commentSystemTYpe->system_type_id,$list->comment_id,$identity->user_id);

									if(!empty($like_details)){  

										$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($commentSystemTYpe->system_type_id,$list->comment_id,$identity->user_id,2,0);									 

										if($like_details['is_liked']==1){

											$arr_likedUsers[] = 'you';

										}

										if($like_details['likes_counts']>0&&!empty($liked_users)){

											foreach($liked_users as $likeuser){

												$arr_likedUsers[] = $likeuser['user_given_name'];

											}

										}

										 

									}

									$comments[] = array(

													'likes_count'=>$like_details['likes_counts'],

													'islike'=>$list->islike,

													'comment_content'=>$list->comment_content,

													'comment_id'=>$list->comment_id,

													'comment_time'=>$this->timeAgo($list->comment_added_timestamp),

													'user_given_name'=>$list->user_given_name,

													'user_id'=>$list->user_id,

													'user_register_type'=>$list->user_register_type,

													'user_fbid'=>$list->user_fbid,

													'profile_photo'=>$list->profile_photo,

													'liked_users'=>$arr_likedUsers,

													'user_profile_name'=>$list->user_profile_name

												);

								}

							}

						}else{$error = "Unable to process";}					

				}else{$error = "Unable to process";}

			}else{	$error = "User not exist in the system";}

		}else{$error = "Your session has to be expired";}

		$return_array= array();		 

		$return_array['process_status'] = (empty($error))?'success':'failed';

		$return_array['process_info'] = $error;

		$return_array['comments'] = $comments;			

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

	public function addComment($user_id,$system_type_id,$referer_id,$comment){

		$commentsData = array();

		$commentsData['comment_system_type_id'] = $system_type_id;

		$commentsData['comment_by_user_id'] = $user_id;

		$commentsData['comment_refer_id'] = $referer_id;

		$commentsData['comment_content'] = $comment;

		$commentsData['comment_status'] = "active";	

		$Objcomment = new Comment();		

		$Objcomment->exchangeArray($commentsData);							

		$insertedcommentsId = "";

		$insertedcommentsId = $this->getCommentTable()->saveComment($Objcomment); 

		return $insertedcommentsId;

	}

	public function loadmoreAction(){

		$auth = new AuthenticationService(); 

		$error = array();

		$identity = array();

		$comment_data = array();

		$planet_member = 0;

		$system = '';

		$is_admin = 0;

		$sm = $this->getServiceLocator();	

		if ($auth->hasIdentity()) {

			$identity = $auth->getIdentity();

			$request = $this->getRequest();

			if ($request->isPost()) {

				$post = $request->getPost();

				$system =  $post['type'];

				$this->groupTable = $sm->get('Groups\Model\GroupsTable');	

				if($system == 'Activity'||$system == 'Discussion'||$system == 'Media'){

					$planet = $post['planet'];

					$planetdetails =  $this->groupTable->getGroupIdFromSEO($planet);

					$planet_id = $planetdetails->group_id; 

					if($this->groupTable->is_member($planet_id,$identity->user_id)){ 

						$planet_member = 1;

					}else{	

						$planet_member = 0;

					}

					$admin_status = $this->getGroupTable()->getAdminStatus($planet_id,$identity->user_id);

					if($admin_status->is_admin){

						$is_admin = 1;

					}

					$user_role = $this->getUserGroupTable()->getUserRole($planet_id,$identity->user_id);

					if(!empty($user_role)){

						$is_admin = 1;

					}					

					if($system == 'Activity'){

						$referer_id =  $post['content_id'];

						$activity_data = $this->getActivityTable()->getActivity($referer_id);

						if($activity_data->group_activity_owner_user_id == $identity->user_id){

								$is_admin = 1;

						}

					}

					if($system == 'Discussion'){

						$referer_id =  $post['content_id'];

						$this->discussionTable = $sm->get('Discussion\Model\DiscussionTable');

						$discussion_data = $this->discussionTable->getDiscussion($referer_id);						 

						if($discussion_data->group_discussion_owner_user_id == $identity->user_id){

								$is_admin = 1;

						}

					}

					if($system == 'Media'){

						$referer_id =  $post['content_id'];

						$this->albumdataTable = $sm->get('Album\Model\AlbumDataTable');

						$GroupalbumData = $this->albumdataTable->getGroupAlbumData($referer_id);			 

						if($GroupalbumData->added_user_id == $identity->user_id){

								$is_admin = 1;

						}

					}

					 

				}

				if($system){					

					$SystemTypeData = $this->groupTable->fetchSystemType($system);

					$referer_id =  $post['content_id'];

					if($referer_id){	

						$page = $request->getPost('page');

						if($page>0){	

							$page = ($page-1)*10+2;

						}else{

							$page = $page+2;

						}

						$this->commentTable = $sm->get('Comment\Model\CommentTable');

						$comment_data = $this->commentTable->getAllCommentsWithLike($SystemTypeData->system_type_id,$referer_id,$identity->user_id,10,$page);

					}else{

						$error[] = "Unautherised access.";

					}

				}else{

					$error[] = "Unautherised access.";

				}

			}else{	

				$error[] = "Unautherised access.";

			}

		}

		else{

			$error[] = "Your session already expired. Please try again after login..";

		}

		$viewModel = new ViewModel(array('error' => $error,'comment_data' => $comment_data,'planet_member'=>$planet_member,'system'=>$system,'user_id'=>$identity->user_id,'is_admin'=>$is_admin));

		$viewModel->setTerminal($request->isXmlHttpRequest());

		return $viewModel;

	 }

	public function editAction(){

		$error = '';

		$error_cnt = 0;

		$success = array();

		$GroupReferId = ""; 

		$SystemTypeId = ""; 				

		$auth = new AuthenticationService(); 

		$userData = array();

		$ModuleCommentsData = array();

		$request   = $this->getRequest();		

		$post = $request->getPost();		

		$GroupReferId = $post['content_id']; 			

		$SystemTypeId = $post['type']; 	 

		$sm = $this->getServiceLocator();	

		$auth = new AuthenticationService();	

		$insertedcommentsId = 0;

		$identity = null;   

		if ($auth->hasIdentity()) {			

			$identity = $auth->getIdentity();			

			$this->userTable = $sm->get('User\Model\UserTable');			

			$userData = $this->userTable->getUser($identity->user_id);			

			if (isset($post['action'])&&$post['action']=='save') {

				$comment_id = $post['content_id'];

				$this->commentTable = $sm->get('Comment\Model\CommentTable');

				$comment_data = $this->commentTable->getInsertedCommentWithUserDetails($comment_id); 

				if($comment_data->user_id == $identity->user_id){

					if($post['comment_content']!=''){

						$data['comment_content'] = $post['comment_content'];

						if($this->commentTable->updateCommentTable($data,$comment_id)){

							

						}else{

							$error = "Some error occured.Please try agaian";

							$error_cnt++; 

						}

					}else{	

						$error = "Comment content required.";

						$error_cnt++; 

					}

				}else{

					$error = "You don't have the permissions to do this.";

					$error_cnt++; 

				}

			}	 	

		}else{

			$error = "Your sesssion has been expired. Please log in and try again.";

			$error_cnt++; 

		}

		if($error_cnt == 0){

			$return_array = array('error' =>0,'msg'=>$error);

		}else{

			$return_array = array('error' =>1,'msg'=>$error);

		}

		echo json_encode($return_array);die();		

	 }

	public function deleteAction(){

		$error = '';

		$error_cnt = 0;

		$success = array();

		$GroupReferId = ""; 

		$SystemTypeId = ""; 				

		$auth = new AuthenticationService(); 

		$userData = array();	 

		$ModuleCommentsData = array();

		$request   = $this->getRequest();		

		$post = $request->getPost();		  

		$GroupReferId = $post['content_id']; 			

		//$SystemTypeId = $post['type']; 	 

		$sm = $this->getServiceLocator();	

		$auth = new AuthenticationService();	

		$insertedcommentsId = 0;

		$identity = null;   

		if ($auth->hasIdentity()) {			 

			$identity = $auth->getIdentity();		 

			$this->groupTable = $sm->get('Groups\Model\GroupsTable'); 			 

			$this->userTable = $sm->get('User\Model\UserTable');

			$delete_permission = 0; 			 

			$comment_id = $post['content_id'];

			$this->commentTable = $sm->get('Comment\Model\CommentTable');

			$comment_data = $this->commentTable->getInsertedCommentWithUserDetails($comment_id);

			$SystemInfo = $this->groupTable->getSystemInfo($comment_data->comment_system_type_id);

			if($comment_data->user_id == $identity->user_id){

				$delete_permission = 1;

			}else{

				switch ($SystemInfo->system_type_title) {

					case 'Discussion':

						$this->discussionTable = $sm->get('Discussion\Model\DiscussionTable');

						$DiscussionData = $this->discussionTable->getDiscussion($comment_data->comment_refer_id);//print_r($GroupModuleData);die();

						if($DiscussionData->group_discussion_owner_user_id == $identity->user_id){

							$delete_permission = 1;

						}

						$admin_status = $this->getGroupTable()->getAdminStatus($DiscussionData->group_discussion_group_id,$identity->user_id);

						if($admin_status->is_admin){

							$delete_permission = 1;

						}

						$user_role = $this->getUserGroupTable()->getUserRole($DiscussionData->group_discussion_group_id,$identity->user_id);

						if(!empty($user_role)){

							$delete_permission = 1;

						}

						break;

					case 'Activity':

						$this->activityTable = $sm->get('Activity\Model\ActivityTable');

						$ActivityData = $this->activityTable->getActivity($comment_data->comment_refer_id);

						if($ActivityData->group_activity_owner_user_id!=''){

							if($ActivityData->group_activity_owner_user_id == $identity->user_id){

								$delete_permission = 1;

							}

							$admin_status = $this->getGroupTable()->getAdminStatus($ActivityData->group_activity_group_id,$identity->user_id);

							if($admin_status->is_admin){

								$delete_permission = 1;

							}

							$user_role = $this->getUserGroupTable()->getUserRole($ActivityData->group_activity_group_id,$identity->user_id);

							if(!empty($user_role)){

								$delete_permission = 1;

							}

						}

					case 'album':

						$this->albumTable = $sm->get('Album\Model\AlbumTable');

						$GroupModuleData = $this->albumTable->getGroupAlbum($comment_data->comment_refer_id);

						$moduleId = $GroupModuleData->group_album_group_id;

						break;

					case 'Media': 

						$this->albumdataTable = $sm->get('Album\Model\AlbumDataTable');

						$GroupModuleData = $this->albumdataTable->getGroupAlbumData($GroupReferId);

						$moduleId = $GroupModuleData->album_group_id; 

					 break;

					 case 'Userfiles': 

						$this->albumdataTable = $sm->get('Album\Model\AlbumDataTable');

						$GroupModuleData = $this->albumdataTable->getGroupAlbumData($GroupReferId);

						$moduleId = $GroupModuleData->album_user_id;

						$profile_name = $post['profile']; 	 

						if($profile_name!=''){

							$userinfo = $this->userTable->getUserByProfilename($profilename);

							if($userinfo->user_id==$moduleId){

							$delete_permission = 1;

							}

						}

					 break;

				}

			}

			if($delete_permission){

				$SystemTypeData = $this->getGroupTable()->fetchSystemType('Comment');

				$this->getLikeTable()->deleteEventLike($SystemTypeData->system_type_id,$comment_id);

				if($this->commentTable->deleteComment($comment_id)){

					$error = "Successfully removed comments.";

					$error_cnt=0;

				}else{

					$error = "Some error occured. Please try again.";

					$error_cnt++;

				}					

				

			}else{

				$error = "You don't have the permissions to do this.";

				$error_cnt++; 

			}			 	 	

		}else{

			$error = "Your sesssion has been expired. Please log in and try again.";

			$error_cnt++; 

		}

		if($error_cnt == 0){

			$return_array = array('error' =>0,'msg'=>$error);

		}else{

			$return_array = array('error' =>1,'msg'=>$error);

		}

		echo json_encode($return_array);die();		

	}

	 

	

	

	public function getActivityRsvpTable(){

        if (!$this->activityRsvpTable) {

            $sm = $this->getServiceLocator();

            $this->activityRsvpTable = $sm->get('Activity\Model\ActivityRsvpTable');

        }

        return $this->activityRsvpTable;

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

	

	public function getUserTable(){       

        $sm = $this->getServiceLocator(); 

        return $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable; ;

    }

	public function getCommentTable(){

		$sm = $this->getServiceLocator();

		return  $this->commentTable = (!$this->commentTable)?$sm->get('Comment\Model\CommentTable'):$this->commentTable;   

	}

	public function getGroupTable(){       

		$sm = $this->getServiceLocator();			 

        return $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;

    }

	public function getDiscussionTable(){

		$sm = $this->getServiceLocator();

		return  $this->discussionTable = (!$this->discussionTable)?$sm->get('Discussion\Model\DiscussionTable'):$this->discussionTable;    

    }

	public function getUserGroupTable(){        

        $sm = $this->getServiceLocator();		 

        return $this->userGroupTable= (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;   

    } 

	public function getUserNotificationTable(){

        $sm = $this->getServiceLocator();         

        return $this->userNotificationTable= (!$this->userNotificationTable)?$sm->get('Notification\Model\UserNotificationTable'):$this->userNotificationTable;   

    }

	public function getGroupMediaTable(){

		$sm = $this->getServiceLocator();

		return  $this->groupMediaTable = (!$this->groupMediaTable)?$sm->get('Groups\Model\GroupMediaTable'):$this->groupMediaTable;    

	}

	public function getActivityTable(){       

       $sm = $this->getServiceLocator();       

       return  $this->activityTable = (!$this->activityTable)?$sm->get('Activity\Model\ActivityTable'):$this->activityTable;    

    }

	public function getLikeTable(){         

		$sm = $this->getServiceLocator();

        return  $this->likeTable = (!$this->likeTable)?$sm->get('Like\Model\LikeTable'):$this->likeTable;       

    }

}

