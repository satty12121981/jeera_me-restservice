<?php
####################Like Controller #################################
#namespace for module like
namespace Service\Controller;
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
        $this->flagSuccess = "Success";
        $this->flagFailure = "Failure";
	}	
	#This will like post and comments
	public function LikeAction() {
		$error = '';
		$like_count = 0;
		$str_liked_users = '';
        $arrLikeMembers  = array();
        $request   = $this->getRequest();
        if ($request->isPost()){
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken'])&&$post['accesstoken']!=null&&$post['accesstoken']!=''&&$post['accesstoken']!='undefined')?strip_tags(trim($post['accesstoken'])):'';
            $error =(empty($accToken))?"Request Not Authorised.":$error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error =(empty($userinfo))?"Invalid Access Token.":$error;
            $this->checkError($error);
            $system_type = $post['type'];
            $SystemTypeData = $this->getGroupTable()->fetchSystemType($system_type);
            $error =(empty($SystemTypeData))?"Invalid Content Type to like":$error;
            $this->checkError($error);
            $error = (isset($post['content_id'])&&$post['content_id']!=null&&$post['content_id']!=''&&$post['content_id']!='undefined' && is_numeric($post['content_id']))?'':'please input a valid content id';
            $this->checkError($error);
            $refer_id = trim($post['content_id']);
            switch($system_type){
                case 'Discussion':
                    if($refer_id!=''){
                        $discussion_data = $this->getDiscussionTable()->getDiscussion($refer_id);
                        if(!empty($discussion_data)){
                            if($this->addLike($userinfo->user_id,$SystemTypeData->system_type_id,$refer_id)){
                                $like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id);
                                $like_count = $like_details->likes_counts;
                                $joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($discussion_data->group_discussion_group_id);
                                $group  = $this->getGroupTable()->getPlanetinfo($discussion_data->group_discussion_group_id);
                                if(!empty($like_details)){
                                    $liked_users = $this->getLikeTable()->likedUsersForRestAPI($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id,"","");
                                }
                                $msg = $userinfo->user_given_name." Like one status in the group ".$group->group_title;
                                $subject = 'Like status';
                                $type = 6;
                                $this->likedPostUsersNotifications($joinedMembers, $userinfo, $group, $refer_id, $msg, $subject,$type);
                            }
                        }else{$error = "Content Not exist";}
                    }else{$error = "Content Not exist";}
                break;
                case 'Media':
                    if($refer_id!=''){
                        $media_data = $this->getGroupMediaTable()->getMedia($refer_id);
                        if(!empty($media_data)){
                            if($this->addLike($userinfo->user_id,$SystemTypeData->system_type_id,$refer_id)){
                                $like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id);
                                $like_count = $like_details->likes_counts;
                                $joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($media_data->media_added_group_id);
                                $group  = $this->getGroupTable()->getPlanetinfo($media_data->media_added_group_id);
                                if(!empty($like_details)){
                                    $liked_users = $this->getLikeTable()->likedUsersForRestAPI($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id,"","");
                                }
                                $msg = $userinfo->user_given_name." Like one media in the group ".$group->group_title;
                                $subject = 'Like Media';
                                $type = 8;
                                $this->likedPostUsersNotifications($joinedMembers, $userinfo, $group, $refer_id, $msg, $subject,$type);
                            }
                        }else{$error = "Content Not exist";}
                    }else{$error = "Content Not exist";}
                break;
                case 'Activity':
                    if($refer_id!=''){
                        $activity_data = $this->getActivityTable()->getActivity($refer_id);
                        if(!empty($activity_data)){
                            if($this->addLike($userinfo->user_id,$SystemTypeData->system_type_id,$refer_id)){
                                $like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id);
                                $like_count = $like_details->likes_counts;
                                $joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($activity_data->group_activity_group_id);
                                $group  = $this->getGroupTable()->getPlanetinfo($activity_data->group_activity_group_id);
                                if(!empty($like_details)){
                                    $liked_users = $this->getLikeTable()->likedUsersForRestAPI($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id,"","");
                                }
                                $subject = 'Like Activity';
                                $from = 'admin@jeera.com';
                                $type = 7;
                                $this->likedPostUsersNotifications($joinedMembers, $userinfo, $group, $refer_id, $msg, $subject,$type);
                            }
                        }else{$error = "Content Not exist";}
                    }else{$error = "Content Not exist";}
                break;
                case 'Comment':
                    if($refer_id!=''){
                        $comment_data = $this->getCommentTable()->getComment($refer_id);
                        if(!empty($comment_data)){
                            if($this->addLike($userinfo->user_id,$SystemTypeData->system_type_id,$refer_id)){
                                $like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id);
                                $like_count = $like_details->likes_counts;
                                if(!empty($like_details)){
                                    $liked_users = $this->getLikeTable()->likedUsersForRestAPI($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id,"","");
                                }
                                if($comment_data->comment_by_user_id!=$userinfo->user_id){
                                    $config = $this->getServiceLocator()->get('Config');
                                    $base_url = $config['pathInfo']['base_url'];
                                    $msg = $userinfo->user_given_name." Like your comment";
                                    $subject = 'Like status';
                                    $from = 'admin@jeera.com';
                                    $process = 'comment like';
                                    if($comment_data->comment_system_type_id == 1){
                                            $this->UpdateNotifications($comment_data->comment_by_user_id,$msg,7,$subject,$from,$userinfo->user_id,$comment_data->comment_refer_id,$process);
                                    }
                                    else if($comment_data->comment_system_type_id == 2){
                                            $this->UpdateNotifications($comment_data->comment_by_user_id,$msg,6,$subject,$from,$userinfo->user_id,$comment_data->comment_refer_id,$process);
                                    }
                                    else if($comment_data->comment_system_type_id == 4){
                                            $this->UpdateNotifications($comment_data->comment_by_user_id,$msg,8,$subject,$from,$userinfo->user_id,$comment_data->comment_refer_id,$process);
                                    }
                                }
                            }
                        }else{$error = "Content Not exist";}
                    }else{$error = "Content Not exist";}
                break;
            }
        }
        if (!empty($liked_users)) {
            foreach ($liked_users as $f_list) {
                $profile_photo = $this->manipulateProfilePic($f_list['user_id'], $f_list['profile_photo'], $f_list['user_fbid']);
                $arrLikeMembers[] = array(
                    'user_id'=>$f_list['user_id'],
                    'user_fbid'=>$f_list['user_fbid'],
                    'user_given_name'=>$f_list['user_given_name'],
                    'user_profile_name'=>$f_list['user_profile_name'],
                    'profile_photo'=>$profile_photo,
                );

            }
        }
        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
        $dataArr[0]['message'] = $error;
        $dataArr[0]['like_count'] = $like_count;
        $dataArr[0]['liked_users'] = $arrLikeMembers;
        echo json_encode($dataArr);
        exit;
	}
    public function likedPostUsersNotifications($joinedMembers, $userinfo, $group, $refer_id, $msg, $subject,$type){
        if (count($joinedMembers)) {
            $config = $this->getServiceLocator()->get('Config');
            $base_url = $config['pathInfo']['base_url'];
            $from = 'admin@jeera.com';
            $process="like";
            foreach($joinedMembers as $members){
                if($members->user_group_user_id!=$userinfo->user_id){
                    $this->UpdateNotifications($members->user_group_user_id,$msg,$type,$subject,$from,$userinfo->user_id,$refer_id,$process);
                }
            }
        }
    }
    public function checkError($error){
        if (!empty($error)){
            $dataArr[0]['flag'] = $this->flagFailure;
            $dataArr[0]['message'] = $error;
            echo json_encode($dataArr);
            exit;
        }
    }
	public function addLike($user_id,$type,$content_id){
		$sm = $this->getServiceLocator();
		$this->remoteAddr = $sm->get('ControllerPluginManager')->get('GenericPlugin')->getRemoteAddress();
		$Like = new Like();
		$this->likeTable = $sm->get('Like\Model\LikeTable');
		$likeData = $this->likeTable->LikeExistsCheck($type,$content_id,$user_id);
        if ( empty( $likeData->like_id ) ) {
            $LikesData = array();
            $LikesData['like_system_type_id'] = $type;
            $LikesData['like_by_user_id'] = $user_id;
            $LikesData['like_refer_id'] = $content_id;
            $LikesData['like_status'] = "active";
            $LikesData['like_added_ip_address'] = $this->remoteAddr;
            $Like->exchangeArray($LikesData);
            $insertedLikesId = $this->likeTable->saveLike($Like);
            return $insertedLikesId;
        }else{
            $error = "User already Liked the content";
            $this->checkError($error);
        }
	}
 	public function UnLikeAction() {
		$error = '';
		$like_count = 0;
		$auth = new AuthenticationService();
        $request   = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error = (empty($userinfo)) ? "Invalid Access Token." : $error;
            $this->checkError($error);
            $system_type = $post['type'];
            $SystemTypeData = $this->getGroupTable()->fetchSystemType($system_type);
            $error = (empty($SystemTypeData)) ? "Invalid Content Type to like" : $error;
            $this->checkError($error);
            $error = (isset($post['content_id']) && $post['content_id'] != null && $post['content_id'] != '' && $post['content_id'] != 'undefined' && is_numeric($post['content_id'])) ? '' : 'please input a valid content id';
            $this->checkError($error);
            $refer_id = trim($post['content_id']);
            switch($system_type){
                case 'Discussion':
                    if($refer_id!=''){
                        $discussion_data = $this->getDiscussionTable()->getDiscussion($refer_id);
                        if(!empty($discussion_data)){
                            $likeData = $this->getLikeTable()->LikeExistsCheck($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id);
                            if ( !empty( $likeData->like_id ) ) {
                                if( $this->getLikeTable()->deleteLikeByReference($SystemTypeData->system_type_id,$likeData->like_by_user_id,$refer_id)){
                                    $like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id);
                                    $like_count = $like_details->likes_counts;
                                    $error = "Content UnLiked By User";
                                }
                            }else {$error = "Content Not Liked By User to unlike";}
                        }else{$error = "Content Not exist";}
                    }else{$error = "Content Not exist";}
                break;
                case 'Media':
                    if($refer_id!=''){
                        $media_data = $this->getGroupMediaTable()->getMedia($refer_id);
                        if(!empty($media_data)){
                            $likeData = $this->getLikeTable()->LikeExistsCheck($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id);
                            if ( !empty( $likeData->like_id ) ) {
                                if( $this->getLikeTable()->deleteLikeByReference($SystemTypeData->system_type_id,$likeData->like_by_user_id,$refer_id)){
                                    $like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id);
                                    $like_count = $like_details->likes_counts;
                                    $error = "Content UnLiked By User";
                                }
                            }else {$error = "Content Not Liked By User to unlike";}
                        }else{$error = "Content Not exist";}
                    }else{$error = "Content Not exist";}
                break;
                case 'Activity':
                    if($refer_id!=''){
                        $activity_data = $this->getActivityTable()->getActivity($refer_id);
                        if(!empty($activity_data)){
                            $likeData = $this->getLikeTable()->LikeExistsCheck($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id);
                            if ( !empty( $likeData->like_id ) ) {
                                if( $this->getLikeTable()->deleteLikeByReference($SystemTypeData->system_type_id,$likeData->like_by_user_id,$refer_id)){
                                    $like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id);
                                    $like_count = $like_details->likes_counts;
                                    $error = "Content UnLiked By User";
                                }
                            }else {$error = "Content Not Liked By User to unlike";}
                        }else{$error = "Content Not exist";}
                    }else{$error = "Content Not exist";}
                break;
                case 'Comment':
                    if($refer_id!=''){
                        $comment_data = $this->getCommentTable()->getComment($refer_id);
                        if(!empty($comment_data)){
                            $likeData = $this->getLikeTable()->LikeExistsCheck($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id);
                            if ( !empty( $likeData->like_id ) ) {
                                if( $this->getLikeTable()->deleteLikeByReference($SystemTypeData->system_type_id,$likeData->like_by_user_id,$refer_id)){
                                    $like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$refer_id,$userinfo->user_id);
                                    $like_count = $like_details->likes_counts;
                                    $error = "Content UnLiked By User";
                                }
                            }else {$error = "Content Not Liked By User to unlike";}
                        }else{$error = "Content Not exist";}
                    }else{$error = "Content Not exist";}
                break;
            }
        }
        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
        $dataArr[0]['message'] = $error;
        $dataArr[0]['like_count'] = $like_count;
        echo json_encode($dataArr);
        exit;
	}
	public function LikesUsersListAction() {
		$error = '';
		$liked_users = array();
        $request   = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error = (empty($userinfo)) ? "Invalid Access Token." : $error;
            $this->checkError($error);
            $system_type = $post['type'];
            $SystemTypeData = $this->getGroupTable()->fetchSystemType($system_type);
            $error = (empty($SystemTypeData)) ? "Invalid Content Type to like" : $error;
            $this->checkError($error);
            $error = (isset($post['content_id']) && $post['content_id'] != null && $post['content_id'] != '' && $post['content_id'] != 'undefined' && is_numeric($post['content_id'])) ? '' : 'please input a valid content id';
            $this->checkError($error);
            $refer_id = trim($post['content_id']);
            $offset = (isset($post['nparam']))?trim($post['nparam']):'';
            $limit = (isset($post['countparam']))?trim($post['countparam']):'';
            $liked_users = $this->getLikeTable()->likedUsersForRestAPI($SystemTypeData->system_type_id, $refer_id, $userinfo->user_id, (int) $limit, (int) $offset);
        }
        if (!empty($liked_users)) {
            foreach ($liked_users as $f_list) {
                $profile_photo = $this->manipulateProfilePic($f_list['user_id'], $f_list['profile_photo'], $f_list['user_fbid']);
                $arrLikeMembers[] = array(
                    'user_id'=>$f_list['user_id'],
                    'user_fbid'=>$f_list['user_fbid'],
                    'user_given_name'=>$f_list['user_given_name'],
                    'user_profile_name'=>$f_list['user_profile_name'],
                    'profile_photo'=>$profile_photo,
                );

            }
        }
        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
        $dataArr[0]['message'] = $error;
        $dataArr[0]['liked_users'] = $arrLikeMembers;
        echo json_encode($dataArr);
        exit;
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
    public function manipulateProfilePic($user_id, $profile_photo = null, $fb_id = null){
        $config = $this->getServiceLocator()->get('Config');
        $return_photo = null;
        if (!empty($profile_photo))
            $return_photo = $config['pathInfo']['absolute_img_path'].$config['image_folders']['profile_path'].$user_id.'/'.$profile_photo;
        else if(isset($fb_id) && !empty($fb_id))
            $return_photo = 'http://graph.facebook.com/'.$fb_id.'/picture?type=normal';
        else
            $return_photo = $config['pathInfo']['absolute_img_path'].'/images/noimg.jpg';
        return $return_photo;

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
