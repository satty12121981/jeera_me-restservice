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
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error = (empty($userinfo)) ? "Invalid Access Token." : $error;
            $this->checkError($error);
            $post = $request->getPost();
            $error = (isset($post['type']) && $post['type'] != null && $post['type'] != '' && $post['type'] != 'undefined' && is_numeric($post['type'])) ? '' : 'please input a valid type';
            $this->checkError($error);
            $type = $post['type'];
            $objnotification_list = $this->getUserNotificationTable()->getUserNotificationWithSenderInformation($userinfo->user_id,$type,$offset,$limit);
            if(!empty($objnotification_list)){
                foreach($objnotification_list as $list){
                    Switch($list->notification_type_title){
                        case "Friend Request":
                            $is_friend = $this->getUserFriendTable()->isFriend($userinfo->user_id,$list->user_notification_sender_id);
                            $isRequested = $this->getUserFriendTable()->isRequested($userinfo->user_id,$list->user_notification_sender_id);
                            $notification_list[] = array(
                                'user_notification_content'=>$list->user_notification_content,
                                'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                'user_notification_sender_id'=>$list->user_notification_sender_id,
                                'user_notification_reference_id'=>$list->user_notification_reference_id,
                                'user_notification_status'=>$list->user_notification_status,
                                'notification_type_title' =>$list->notification_type_title,
                                'sender_name' => $list->user_given_name,
                                'sender_profile_name' => $list->user_profile_name,
                                'sender_profile_photo' => $list->profile_photo,
                                'sender_user_fbid' => $list->user_fbid,
                                'user_notification_process' => $list->user_notification_process,
                                'is_friend' =>$is_friend,
                                'isRequested' => $isRequested,
                            );
                            break;
                        case "Friend Request Accept":
                            $notification_list[] = array(
                                'user_notification_content'=>$list->user_notification_content,
                                'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                'user_notification_sender_id'=>$list->user_notification_sender_id,
                                'user_notification_reference_id'=>$list->user_notification_reference_id,
                                'user_notification_status'=>$list->user_notification_status,
                                'notification_type_title' =>$list->notification_type_title,
                                'sender_name' => $list->user_given_name,
                                'sender_profile_name' => $list->user_profile_name,
                                'sender_profile_photo' => $list->profile_photo,
                                'sender_user_fbid' => $list->user_fbid,
                                'user_notification_process' => $list->user_notification_process,
                            );
                            break;
                        case "Group Invite":
                            $group  = $this->getGroupTable()->getPlanetinfo($list->user_notification_reference_id);
                            if(!empty($group)){
                                $notification_list[] = array(
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'notification_type_title' =>$list->notification_type_title,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $list->profile_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'user_notification_process' => $list->user_notification_process,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                );
                            }
                            break;
                        case "Group joining Request":
                            $group  = $this->getGroupTable()->getPlanetinfo($list->user_notification_reference_id);
                            if(!empty($group)){
                                $notification_list[] = array(
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'notification_type_title' =>$list->notification_type_title,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $list->profile_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'user_notification_process' => $list->user_notification_process,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                );
                            }
                            break;
                        case "Group Joining Request Accepted":
                            $group  = $this->getGroupTable()->getPlanetinfo($list->user_notification_reference_id);
                            if(!empty($group)){
                                $notification_list[] = array(
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'notification_type_title' =>$list->notification_type_title,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $list->profile_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'user_notification_process' => $list->user_notification_process,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                );
                            }
                            break;
                        case "Discussion":
                            $discussion = $this->getDiscussionTable()->getDiscussion($list->user_notification_reference_id);
                            if(!empty($discussion)){
                                $group  = $this->getGroupTable()->getPlanetinfo($discussion->group_discussion_group_id);
                                $notification_list[] = array(
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'notification_type_title' =>$list->notification_type_title,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $list->profile_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'user_notification_process' => $list->user_notification_process,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                );
                            }
                            break;
                        case "Event":
                            $activity = $this->getActivityTable()->getActivity($list->user_notification_reference_id);
                            if(!empty($activity)){
                                $group  = $this->getGroupTable()->getPlanetinfo($activity->group_activity_group_id);
                                $notification_list[] = array(
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'notification_type_title' =>$list->notification_type_title,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $list->profile_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'user_notification_process' => $list->user_notification_process,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                );
                            }
                            break;
                        case "Media":
                            $media = $this->getGroupMediaTable()->getMedia($list->user_notification_reference_id);
                            if(!empty($media)){
                                $group  = $this->getGroupTable()->getPlanetinfo($media->media_added_group_id);
                                $notification_list[] = array(
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'notification_type_title' =>$list->notification_type_title,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $list->profile_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'user_notification_process' => $list->user_notification_process,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                    'media_type'	=> $media->media_type,
                                    'media_content' => $media->media_content,
                                    'media_caption' => $media->media_caption,
                                );
                            }
                            break;
                        case "Group Admin Promoted":
                            $group  = $this->getGroupTable()->getPlanetinfo($list->user_notification_reference_id);
                            if(!empty($group)){
                                $notification_list[] = array(
                                    'user_notification_content'=>$list->user_notification_content,
                                    'user_notification_added_timestamp'=>$this->timeAgo($list->user_notification_added_timestamp),
                                    'user_notification_sender_id'=>$list->user_notification_sender_id,
                                    'user_notification_reference_id'=>$list->user_notification_reference_id,
                                    'user_notification_status'=>$list->user_notification_status,
                                    'notification_type_title' =>$list->notification_type_title,
                                    'sender_name' => $list->user_given_name,
                                    'sender_profile_name' => $list->user_profile_name,
                                    'sender_profile_photo' => $list->profile_photo,
                                    'sender_user_fbid' => $list->user_fbid,
                                    'user_notification_process' => $list->user_notification_process,
                                    'group_id'	=> $group->group_id,
                                    'group_title'	=> $group->group_title,
                                    'group_seo_title'	=> $group->group_seo_title,
                                );
                            }
                            break;
                    }
                }
            }
        }
        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
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
            $this->checkError($error);
            if ($userinfo->user_id) {
                $notification_list = $this->getUserNotificationTable()->getAllUnreadNotification($userinfo->user_id);
            }
        }
        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
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
    public function ApplePushNotifyAction(){

        $pushNotifications = new PushNotifications();
        $config = $this->getServiceLocator()->get('Config');
        $pushNotifications->ApplePushMessage($config);
        //$pushNotifications->applepushtest($config);
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