<?php
namespace Service\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Notification\Model\Notification;
use Notification\Model\NotificationTable;
use Application\Controller\Plugin\PushNotifications;
use \Exception;

class NotificationController extends AbstractActionController
{
    protected $NotificationTable;
  	protected $userNotificationTable;
	protected $userTable;
	protected $activityTable;
	protected $userFriendTable;		
	protected $groupTable;
	protected $discussionTable;
	protected $groupMediaTable;

	public function NotificationsAction(){
		$error = '';
		$notification_list =array();
		$request   = $this->getRequest();
		if ($request->isPost()){
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error = (empty($userinfo)) ? "Invalid Access Token." : $error;
            $this->checkError($error);
            $strType = $post['type'];
            $strProcess = $post['process'];
            $offset = trim($post['nparam']);
            $limit = trim($post['countparam']);
            if (!empty($limit) && !is_numeric($limit)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a Valid Count Field.";
                echo json_encode($dataArr);
                exit;
            }
            if (!empty($offset) && !is_numeric($offset)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a Valid N Field.";
                echo json_encode($dataArr);
                exit;
            }
            $offset = (int)$offset;
            $limit = (int)$limit;
            $offset = ($offset > 0) ? $offset - 1 : 0;
            $offset = $offset * $limit;
            $objnotification_list = $this->getUserNotificationTable()->getUserNotificationWithTypeForAPI($userinfo->user_id,$strType,$strProcess,(int) $offset,(int) $limit);
            if(!empty($objnotification_list)){
                foreach($objnotification_list as $list){
                    $sender_photo = $this->manipulateProfilePic($list->user_id, $list->profile_photo, $list->user_fbid);
                    Switch($list->notification_type_title){
                        case "Friend Request":
                            $is_friend = $this->getUserFriendTable()->isFriend($userinfo->user_id,$list->user_notification_sender_id);
                            $isRequested = $this->getUserFriendTable()->isRequested($userinfo->user_id,$list->user_notification_sender_id);
                            $notification_list[] = array(
                                'user_notification_id'=>$list->user_notification_id,
                                'user_notification_content'=>$list->user_notification_content,
                                'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                'user_notification_sender_id'=>$list->user_notification_sender_id,
                                'user_notification_reference_id'=>$list->user_notification_reference_id,
                                'user_notification_status'=>$list->user_notification_status,
                                'sender_name' => $list->user_given_name,
                                'sender_profile_name' => $list->user_profile_name,
                                'sender_profile_photo' => $sender_photo,
                                'sender_user_fbid' => $list->user_fbid,
                                'is_friend' =>$is_friend,
                                'is_requested' => $isRequested,
                                'type' => "User",
                                'process' => "FriendRequest",
                            );
                            break;
                        case "Friend Request Accept":
                            $notification_list[] = array(
                                'user_notification_id'=>$list->user_notification_id,
                                'user_notification_content'=>$list->user_notification_content,
                                'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                'user_notification_sender_id'=>$list->user_notification_sender_id,
                                'user_notification_reference_id'=>$list->user_notification_reference_id,
                                'user_notification_status'=>$list->user_notification_status,
                                'sender_name' => $list->user_given_name,
                                'sender_profile_name' => $list->user_profile_name,
                                'sender_profile_photo' => $sender_photo,
                                'sender_user_fbid' => $list->user_fbid,
                                'type' => "User",
                                'process' => "FriendRequestAccepted",
                            );
                            break;
                        case "Friend Request Reject":
                            $notification_list[] = array(
                                'user_notification_id'=>$list->user_notification_id,
                                'user_notification_content'=>$list->user_notification_content,
                                'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                'user_notification_sender_id'=>$list->user_notification_sender_id,
                                'user_notification_reference_id'=>$list->user_notification_reference_id,
                                'user_notification_status'=>$list->user_notification_status,
                                'sender_name' => $list->user_given_name,
                                'sender_profile_name' => $list->user_profile_name,
                                'sender_profile_photo' => $sender_photo,
                                'sender_user_fbid' => $list->user_fbid,
                                'type' => "User",
                                'process' => "FriendRequestRejected",
                            );
                            break;
                        case "Group Invite":
                            $group  = $this->getGroupTable()->getPlanetinfo($list->user_notification_reference_id);
                            if(!empty($group)){
                                $notification_list[] = array(
                                    'user_notification_id'=>$list->user_notification_id,
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $sender_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                    'type' => "Group",
                                    'process' => "GroupInvite",
                                );
                            }
                            break;
                        case "Group joining Request":
                            $group  = $this->getGroupTable()->getPlanetinfo($list->user_notification_reference_id);
                            if(!empty($group)){
                                $notification_list[] = array(
                                    'user_notification_id'=>$list->user_notification_id,
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $sender_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                    'type' => "Group",
                                    'process' => "GroupJoiningRequest",
                                );
                            }
                            break;
                        case "Group Joining Request Accepted":
                            $group  = $this->getGroupTable()->getPlanetinfo($list->user_notification_reference_id);
                            if(!empty($group)){
                                $notification_list[] = array(
                                    'user_notification_id'=>$list->user_notification_id,
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $sender_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                    'type' => "Group",
                                    'process' => "GroupJoiningRequestAccepted",
                                );
                            }
                            break;
                        case "Group Joining Request Rejected":
                            $group  = $this->getGroupTable()->getPlanetinfo($list->user_notification_reference_id);
                            if(!empty($group)){
                                $notification_list[] = array(
                                    'user_notification_id'=>$list->user_notification_id,
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $sender_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                    'type' => "Group",
                                    'process' => "GroupJoiningRequestRejected",
                                );
                            }
                            break;
                        case "Discussion":
                            $discussion = $this->getDiscussionTable()->getDiscussion($list->user_notification_reference_id);
                            if(!empty($discussion)){
                                $group  = $this->getGroupTable()->getPlanetinfo($discussion->group_discussion_group_id);
                                if ($list->user_notification_process == "New Discussion"){
                                    $notification_process = "NewStatus";
                                }
                                else if ($list->user_notification_process == "comment"){
                                    $notification_process = "CommentMade";
                                }
                                else if ($list->user_notification_process == "comment_hashed"){
                                    $notification_process = "MentionedInComment";
                                }
                                else if ($list->user_notification_process == "like"){
                                    $notification_process = "Liked";
                                }
                                else if ($list->user_notification_process == "comment like"){
                                    $notification_process = "CommentLiked";
                                }
                                $notification_list[] = array(
                                    'user_notification_id'=>$list->user_notification_id,
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $sender_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                    'type' => "Status",
                                    'process' => $notification_process,
                                );
                            }
                            break;
                        case "Event":
                            $activity = $this->getActivityTable()->getActivity($list->user_notification_reference_id);
                            if(!empty($activity)){
                                $group  = $this->getGroupTable()->getPlanetinfo($activity->group_activity_group_id);
                                if ($list->user_notification_process == "New Event"){
                                    $notification_process = "NewEvent";
                                }
                                else if ($list->user_notification_process == "comment"){
                                    $notification_process = "CommentMade";
                                }
                                else if ($list->user_notification_process == "comment_hashed"){
                                    $notification_process = "MentionedInComment";
                                }
                                else if ($list->user_notification_process == "like"){
                                    $notification_process = "Liked";
                                }
                                else if ($list->user_notification_process == "comment like"){
                                    $notification_process = "CommentLiked";
                                }
                                else if ($list->user_notification_process == "Join Event"){
                                    $notification_process = "JoinedEvent";
                                }

                                $notification_list[] = array(
                                    'user_notification_id'=>$list->user_notification_id,
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $sender_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                    'type' => "Event",
                                    'process' => $notification_process,
                                );
                            }
                            break;
                        case "Media":
                            $media = $this->getGroupMediaTable()->getMedia($list->user_notification_reference_id);
                            if(!empty($media)){
                                $group  = $this->getGroupTable()->getPlanetinfo($media->media_added_group_id);
                                if ($list->user_notification_process == "New Media"){
                                    $notification_process = "NewMedia";
                                }
                                else if ($list->user_notification_process == "comment"){
                                    $notification_process = "CommentMade";
                                }
                                else if ($list->user_notification_process == "comment_hashed"){
                                    $notification_process = "MentionedInComment";
                                }
                                else if ($list->user_notification_process == "like"){
                                    $notification_process = "Liked";
                                }
                                else if ($list->user_notification_process == "comment like"){
                                    $notification_process = "CommentLiked";
                                }
                                $notification_list[] = array(
                                    'user_notification_id'=>$list->user_notification_id,
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $sender_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                    'media_type'	=> $media->media_type,
                                    'media_content' => $media->media_content,
                                    'media_caption' => $media->media_caption,
                                    'type' => "Media",
                                    'process' => $notification_process,
                                );
                            }
                            break;
                        case "Group Admin Promoted":
                            $group  = $this->getGroupTable()->getPlanetinfo($list->user_notification_reference_id);
                            if(!empty($group)){
                                $notification_list[] = array(
                                    'user_notification_id'=>$list->user_notification_id,
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'user_notification_type_title' =>$list->notification_type_title,
                                    'user_notification_process' => $list->user_notification_process,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $sender_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                    'type' => "Group",
                                    'process' => "GroupAdminPromoted",
                                );
                            }
                            break;
                    }
                }
            }
        }
        $dataArr[0]['flag'] = (empty($error))?"Success":"Failure";
        $dataArr[0]['message'] = $error;
        $dataArr[0]['notification_list'] = $notification_list;
        echo json_encode($dataArr);
        exit;
	}
	public function NotificationsCountAction(){

		$sm = $this->getServiceLocator();
		$error = array();
		$request   = $this->getRequest();
		$notification_count = 0;
        if ($request->isPost()) {
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error = (empty($userinfo)) ? "Invalid Access Token." : $error;
            $this->checkError($error);
            if ($userinfo->user_id) {
                $notification_count = $this->getUserNotificationTable()->getUserNotificationCountForUserUnread($userinfo->user_id);
            }
        }
        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
        $dataArr[0]['message'] = $error;
        $dataArr[0]['notification_count'] = $notification_count;
        echo json_encode($dataArr);
        exit;
	}
    public function UserNotificationListAction(){

        $sm = $this->getServiceLocator();
        $error = '';
        $request   = $this->getRequest();
        $notification_count = 0;
        if ($request->isPost()) {
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error = (empty($userinfo)) ? "Invalid Access Token." : $error;
            if ($error) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = $error;
                echo json_encode($dataArr);
                exit;
            }
            if ($userinfo->user_id) {
                $notification_list = $this->getUserNotificationTable()->getAllUnreadNotification($userinfo->user_id);
            }
        }
        $dataArr[0]['flag'] = (empty($error))?"Success":"Failure";
        $dataArr[0]['message'] = $error;
        $dataArr[0]['notification_count'] = $notification_count;
        echo json_encode($dataArr);
        exit;
    }
	public function UpdateNotificationStatusAction(){
        $sm = $this->getServiceLocator();
        $error = '';
        $request   = $this->getRequest();
        $notification_count = 0;
        if ($request->isPost()) {
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error = (empty($userinfo)) ? "Invalid Access Token." : $error;
            $this->checkError($error);
            if ($userinfo->user_id) {
                $this->getUserNotificationTable()->makeNotificationsReaded($userinfo->user_id);
                $error = "Notification Read";
            }
        }
        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
        $dataArr[0]['message'] = $error;
        echo json_encode($dataArr);
        exit;
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
    public function ApplePushNotifyAction(){
        $pushNotifications = new PushNotifications();
        $config = $this->getServiceLocator()->get('Config');
        $pushNotifications->ApplePushMessage($config);
    }
    public function GooglePushNotifyAction(){
        $GoogleCloudMsgs = new PushNotifications();
        $GoogleCloudMsgs->GoogleCloudMessage();
    }
    public function checkError($error){
        if (!empty($error)){
            $dataArr[0]['flag'] = "Failure";
            $dataArr[0]['message'] = $error;
            echo json_encode($dataArr);
            exit;
        }
    }
	public function getActivityTable(){
		 if (!$this->activityTable) {
            $sm = $this->getServiceLocator();
			$this->activityTable = $sm->get('Activity\Model\ActivityTable');
        }
        return $this->activityTable;
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
	public function getGroupMediaTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupMediaTable = (!$this->groupMediaTable)?$sm->get('Groups\Model\GroupMediaTable'):$this->groupMediaTable;    
    }
	public function getDiscussionTable(){
		$sm = $this->getServiceLocator();
		return  $this->discussionTable = (!$this->discussionTable)?$sm->get('Discussion\Model\DiscussionTable'):$this->discussionTable;    
    }
	public function getUserFriendTable(){
		$sm = $this->getServiceLocator();
		return  $this->userFriendTable = (!$this->userFriendTable)?$sm->get('User\Model\UserFriendTable'):$this->userFriendTable;    
	}
}