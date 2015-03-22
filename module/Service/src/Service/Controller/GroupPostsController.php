<?php
namespace Service\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Application\Controller\Plugin\UploadHandler;
use Application\Controller\Plugin\ResizeImage;
use Groups\Model\GroupPhoto;
use Groups\Model\GroupMedia;
use Discussion\Model\Discussion;
use Activity\Model\Activity;
use Activity\Model\ActivityInvite;
use Activity\Model\ActivityRsvp ;
use Notification\Model\UserNotification;
use \Exception;

class GroupPostsController extends AbstractActionController
{
	protected $userTable;
	protected $userProfileTable;
	protected $userFriendTable;
	protected $userGroupTable;
	protected $groupTable;
	protected $activityTable;
	protected $discussionTable;
	protected $groupMediaTable;
	protected $likeTable;
	protected $commentTable;
	protected $activityRsvpTable;
    protected $userNotificationTable;
    protected  $groupActivityInviteTable;

	public function __construct(){
        $this->flagSuccess = "Success";
		$this->flagFailure = "Failure";
	}
    public function PostMediaAction(){
    	$error = '';
		$request   = $this->getRequest();
		if ($request->isPost()){
            $config = $this->getServiceLocator()->get('Config');
            $post = $request->getPost();
            $file = $request->getFiles();
            $accToken = (isset($post['accesstoken'])&&$post['accesstoken']!=null&&$post['accesstoken']!=''&&$post['accesstoken']!='undefined')?strip_tags(trim($post['accesstoken'])):'';
            if (empty($accToken)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error =($post['mediatype']==null || $post['mediatype']=='' || $post['mediatype']=='undefined')? "Media type required":$error;
            $error =($post['groupid']==null || $post['groupid']=='' || $post['groupid']=='undefined' || !is_numeric($post['groupid']))? "please input a valid group id":$error;
            $group  = $this->getGroupTable()->getPlanetinfo($post['groupid']);
            $error =(empty($group)||$group->group_id=='')?"Given group not exist in this system":$error;
            $error =(empty($userinfo))?"Invalid Access Token.":$error;
            if (!empty($error)){
                $dataArr[0]['flag'] = $this->flagFailure;
                $dataArr[0]['message'] = $error;
                echo json_encode($dataArr);
                exit;
            }
            $error =(empty($this->getUserGroupTable()->getGroupUserDetails($group->group_id,$userinfo->user_id)))?"User has no permission or not a member of the group to post.":$error;
            if (!empty($error)){
                $dataArr[0]['flag'] = $this->flagFailure;
                $dataArr[0]['message'] = $error;
                echo json_encode($dataArr);
                exit;
            }
            $media_type = $post['mediatype'];
            switch($media_type){
                case 'status':
                    $error =($post['statustext']==null || $post['statustext']=='' || $post['statustext']=='undefined')? "Please post a status text to submit":$error;
                    if($error==''){
                        $objDiscusion = new Discussion();
                        $objDiscusion->group_discussion_content = $post['statustext'];
                        $objDiscusion->group_discussion_owner_user_id = $userinfo->user_id;
                        $objDiscusion->group_discussion_group_id = $group->group_id;
                        $objDiscusion->group_discussion_status = 'available';
                        $IdDiscussion = $this->getDiscussionTable()->saveDiscussion($objDiscusion);
                        if($IdDiscussion){
                            $joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($group->group_id);
                            $subject = 'New status added';
                            $msg = $userinfo->user_given_name . " added a new status under the group " . $group->group_title;
                            $this->grouppostNotifications($joinedMembers, $subject, $msg, $userinfo, $group);
                            $dataArr[0]['flag'] = $this->flagSuccess;
                            $error = "Status posted successfully";
                        }else{ $dataArr[0]['flag'] = $this->flagFailure; $error = "Some error occured. Please try again";}
                    }else{
                        $dataArr[0]['flag'] = $this->flagFailure;
                    }
                break;
                case 'event':
                    $error =($post['title']==''||$post['title']=='undefined')? "Event title required":$error;
                    $error =($post['date']==''||$post['date']=='undefined')? "Event date required":$error;
                    $error =($post['location']==''||$post['location']=='undefined')? "Event location required":$error;
                    $error =($post['description']==''||$post['description']=='undefined')? "Event description required":$error;
                    $error = ($this->is_date($post['date']))?$error:"Enter a valid date";
                    $stamp = strtotime($post['date'].' '.$post['time']);
                    $error = ($stamp<=time())?"Past date events are not allowed":$error;
                    if($error ==''){
                        $objActivty = new Activity();
                        $objActivty->group_activity_title = $post['title'];
                        $objActivty->group_activity_content = $post['description'];
                        $objActivty->group_activity_group_id = $post['groupid'];
                        $objActivty->group_activity_owner_user_id = $userinfo->user_id;
                        $objActivty->group_activity_status = 'active';
                        $objActivty->group_activity_type = 'open';
                        $objActivty->group_activity_start_timestamp = date("Y-m-d H:i:s",$stamp);
                        $objActivty->group_activity_location = $post['location'];
                        $objActivty->group_activity_location_lat = $post['location_lat'];
                        $objActivty->group_activity_location_lng = $post['location_lng'];
                        $newActivity_id = $this->getActivityTable()->createActivity($objActivty);
                        if($newActivity_id){
                            $msg = $userinfo->user_given_name." added a new event under the group ".$group->group_title;
                            $base_url = $config['pathInfo']['base_url'];
                            $subject = 'New event added';
                            $from = 'admin@jeera.com';
                            switch($post['membertype']){
                                case "allmembers":
                                    $joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($group->group_id);
                                    if (count($joinedMembers)){
                                        foreach($joinedMembers as $members){
                                            if($members->user_group_user_id!=$userinfo->user_id){
                                                $this->UpdateNotifications($members->user_group_user_id,$msg,2,$subject,$from,$userinfo->user_id,$group->group_id);
                                            }
                                        }
                                    }
                                    break;
                                case "friends":
                                    $friends = $this->getUserFriendTable()->userFriends($userinfo->user_id);
                                    $this->grouppostNotifications($friends, $subject, $msg, $userinfo, $group);
                                    break;
                                case "invitemembers":
                                    $invited_members = $post['invitemembers'];
                                    $invited_members = explode(",", $invited_members[0]);
                                    if(!empty($invited_members)){
                                        $config = $this->getServiceLocator()->get('Config');
                                        $base_url = $config['pathInfo']['base_url'];
                                        $from = 'admin@jeera.com';
                                        foreach($invited_members as $members){
                                            $usermemberinfo = $this->getUserTable()->getUser($members);
                                            $groupmemberdata = $this->getUserGroupTable()->getGroupUserDetails($group->group_id,$usermemberinfo->user_id);
                                            if(!empty($usermemberinfo) && !empty($groupmemberdata) && $usermemberinfo->user_id && $groupmemberdata->user_group_user_id){
                                                $objActivityInvite = new ActivityInvite();
                                                $objActivityInvite->group_activity_invite_sender_user_id = $userinfo->user_id;
                                                $objActivityInvite->group_activity_invite_receiver_user_id = $groupmemberdata->user_group_user_id;
                                                $objActivityInvite->group_activity_invite_status = 'invited';
                                                $objActivityInvite->group_activity_invite_activity_id = $newActivity_id;
                                                $this->getActivityInviteTable()->saveActivityInvite($objActivityInvite);
                                                $this->UpdateNotifications($members,$msg,2,$subject,$from,$userinfo->user_id,$group->group_id);
                                            }
                                        }
                                    }
                                    break;
                            }
                            $dataArr[0]['flag'] = $this->flagSuccess; $error = "Event added successfully";
                        }
                    } else $dataArr[0]['flag'] = $this->flagFailure;
                break;
                case 'image':
                    if(isset($file)&&isset($file['mediaimage']['name'])&&$file['mediaimage']['name']!=''){
                        $config = $this->getServiceLocator()->get('Config');
                        $options['script_url']          = $config['pathInfo']['base_url'];
                        $options['upload_dir']          = $config['pathInfo']['group_img_path'].$group->group_id."/media/";
                        $options['upload_url']          = $config['pathInfo']['group_img_path_absolute_path'].$group->group_id."/media/";
                        $options['param_name']          = 'mediaimage';
                        $options['min_width']           = 50;
                        $options['min_height']          = 50;
                        if(!is_dir($config['pathInfo']['group_img_path'].$group->group_id)){
                            mkdir($config['pathInfo']['group_img_path_absolute_path'].$group->group_id);
                        }
                        if(!is_dir($config['pathInfo']['group_img_path'].$group->group_id."/media/")){
                            mkdir($config['pathInfo']['group_img_path_absolute_path'].$group->group_id."/media/");
                        }
                        $upload_handler = new UploadHandler($options);
                        if(isset($upload_handler->image_objects['filename'])&&$upload_handler->image_objects['filename']!=''){
                            if($error==''){
                                $objGroupMedia = new GroupMedia();
                                $objGroupMedia->media_added_user_id = $userinfo->user_id;
                                $objGroupMedia->media_added_group_id = $post['groupid'];
                                $objGroupMedia->media_type = 'image';
                                $objGroupMedia->media_content = $upload_handler->image_objects['filename'];
                                $objGroupMedia->media_caption = ($post['imagecaption']!='undefined')?strip_tags(trim($post['imagecaption'])):'';
                                $objGroupMedia->media_status = 'active';
                                $addeditem = $this->getGroupMediaTable()->saveGroupMedia($objGroupMedia);
                                if($addeditem){
                                    $joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($group->group_id);
                                    $msg = $userinfo->user_given_name." added a new image under the group ".$group->group_title;
                                    $subject = 'New image added';
                                    $this->grouppostNotifications($joinedMembers, $subject, $msg, $userinfo, $group);
                                    $dataArr[0]['flag'] = $this->flagSuccess; $error = "media posted successfully";
                                }else{ $dataArr[0]['flag'] = $this->flagFailure; $error = "Some error occcured. Please try again"; }
                            }
                        }else{
                            $dataArr[0]['flag'] = $this->flagFailure;
                            $error = "Some error occured, during file upload. Please try again";
                        }
                    }else{
                        $dataArr[0]['flag'] = $this->flagFailure;
                        $error = "Select one image to upload and continue";
                    }
                    break;
                case 'video':
                    $error =($post['mediavideo']=='')? "Add video to upload":$error;
                    if($error==''){
                        $objGroupMedia = new GroupMedia();
                        $objGroupMedia->media_added_user_id = $userinfo->user_id;
                        $objGroupMedia->media_added_group_id = $post['groupid'];
                        $objGroupMedia->media_type = 'video';
                        $objGroupMedia->media_content = $post['mediavideo'];
                        $objGroupMedia->media_caption = ($post['videocaption']!='undefined')?strip_tags(trim($post['videocaption'])):'';
                        $objGroupMedia->media_status = 'active';
                        $addeditem = $this->getGroupMediaTable()->saveGroupMedia($objGroupMedia);
                        if($addeditem){
                            $joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($group->group_id);
                            $msg = $userinfo->user_given_name." added a new video under the group ".$group->group_title;
                            $subject = 'New video added';
                            $this->grouppostNotifications($joinedMembers, $subject, $msg, $userinfo, $group);
                            $dataArr[0]['flag'] = $this->flagSuccess; $error = "media posted successfully";
                        }else{ $dataArr[0]['flag'] = $this->flagFailure; $error = "Some error occcured. Please try again"; }
                    }else{
                        $dataArr[0]['flag'] = $this->flagFailure; $error = "Please input media video url and continue";
                    }
                    break;
            }
            $dataArr[0]['message'] = $error;
            echo json_encode($dataArr);
            exit;
        }else{
            $dataArr[0]['flag'] = $this->flagFailure;
            $dataArr[0]['message'] = "Request Not Authorised.";
            echo json_encode($dataArr);
            exit;
        }
        return;
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
    public function grouppostNotifications($joinedMembers, $subject, $msg, $userinfo, $group){
        if (count($joinedMembers)) {
            $config = $this->getServiceLocator()->get('Config');
            $base_url = $config['pathInfo']['base_url'];
            $from = 'admin@jeera.com';
            foreach ($joinedMembers as $members) {
                if ($members->user_group_user_id != $userinfo->user_id) {
                    $this->UpdateNotifications($members->user_group_user_id, $msg, 2, $subject, $from, $userinfo->user_id, $group->group_id);
                }
            }
        }
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
    public function getUserNotificationTable(){
        $sm = $this->getServiceLocator();
        return  $this->userNotificationTable = (!$this->userNotificationTable)?$sm->get('Notification\Model\UserNotificationTable'):$this->userNotificationTable;
    }
    public function getUserTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;    
	}
	public function getGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;    
	}
	public function getUserGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->userGroupTable = (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;    
	}
	public function getActivityTable(){
		$sm = $this->getServiceLocator();
		return  $this->activityTable = (!$this->activityTable)?$sm->get('Activity\Model\ActivityTable'):$this->activityTable;    
    }
	public function getDiscussionTable(){
		$sm = $this->getServiceLocator();
		return  $this->discussionTable = (!$this->discussionTable)?$sm->get('Discussion\Model\DiscussionTable'):$this->discussionTable;    
    }
	public function getGroupMediaTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupMediaTable = (!$this->groupMediaTable)?$sm->get('Groups\Model\GroupMediaTable'):$this->groupMediaTable;    
    }
	public function getLikeTable(){
		$sm = $this->getServiceLocator();
		return  $this->likeTable = (!$this->likeTable)?$sm->get('Like\Model\LikeTable'):$this->likeTable; 
	}
	public function getCommentTable(){
		$sm = $this->getServiceLocator();
		return  $this->commentTable = (!$this->commentTable)?$sm->get('Comment\Model\CommentTable'):$this->commentTable;   
	}
	public function getActivityRsvpTable(){
		$sm = $this->getServiceLocator();
		return  $this->activityRsvpTable = (!$this->activityRsvpTable)?$sm->get('Activity\Model\ActivityRsvpTable'):$this->activityRsvpTable;
    }
    public function getActivityInviteTable(){
        $sm = $this->getServiceLocator();
        return  $this->groupActivityInviteTable = (!$this->groupActivityInviteTable)?$sm->get('Activity\Model\ActivityInviteTable'):$this->groupActivityInviteTable;
    }
}
