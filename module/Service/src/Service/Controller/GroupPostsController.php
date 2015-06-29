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
    protected $groupActivityInviteTable;
    protected $groupPhotoTable;

	public function __construct(){
        $this->flagSuccess = "Success";
		$this->flagFailure = "Failure";
	}

    public function MyFeedsAction(){
        $error = '';
        $feeds = array();
        $request   = $this->getRequest();
        $config = $this->getServiceLocator()->get('Config');
        if ($request->isPost()){
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error = (empty($userinfo)) ? "Invalid Access Token." : $error;
            $this->checkError($error);
            $type	= $post['type'];
            $activity  = $post['activity'];
            $group_id = "";
            if (isset($post['groupid'])){
                $group  = $this->getGroupTable()->getPlanetinfo($post['groupid']);
                $error =(empty($group)||$group->group_id=='')? "Given group not exist in the system":$error;
                $this->checkError($error);
                $group_id = $post['groupid'];
                $userPermissionOnGroup = $this->getUserGroupTable()->getGroupUserDetails($group->group_id,$userinfo->user_id);
                $error =(empty($userPermissionOnGroup))?"User has no permission on the group to view feeds.":$error;
                $this->checkError($error);
            }
            $offset = (isset($post['nparam']))?trim($post['nparam']):'';
            $limit = (isset($post['countparam']))?trim($post['countparam']):'';
            if (!empty($limit) && !is_numeric($limit)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a Valid Count Param Field.";
                echo json_encode($dataArr);
                exit;
            }
            if (!empty($offset) && !is_numeric($offset)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a Valid N Param Field.";
                echo json_encode($dataArr);
                exit;
            }
			$offset = (int) $offset;
			$limit = (int) $limit;
			$offset =($offset>0)?$offset-1:0;
			$offset = $offset*$limit;
            $feeds_list = $this->getGroupTable()->getNewsFeedsAPI($userinfo->user_id,$type,$group_id,$activity,(int) $limit, (int) $offset);
            foreach($feeds_list as $list){
                $is_admin = 0;
                $friendl_status = "";
                if($this->getUserGroupTable()->checkOwner($list['group_id'],$list['user_id'])){
                    $is_admin = 1;
                }
                if ( $list['user_id'] != $userinfo->user_id ){
                    $is_lfriend = ($this->getUserFriendTable()->isFriend($list['user_id'],$userinfo->user_id))?1:0;
                    $is_lrequested = ($this->getUserFriendTable()->isRequested($list['user_id'],$userinfo->user_id))?1:0;
                    $is_lpending = ($this->getUserFriendTable()->isPending($list['user_id'],$userinfo->user_id))?1:0;
                    if($is_lfriend){
                        $friendl_status = 'Friends';
                    }
                    else if($is_lrequested){
                        $friendl_status = 'RequestSent';
                    }
                    else if($is_lpending){
                        $friendl_status = 'RequestPending';
                    }
                    else{
                        $friendl_status = 'NoFriends';
                    }
                }
                $profileDetails = $this->getUserTable()->getProfileDetails($list['user_id']);
                $userprofiledetails = array();
                $profile_details_photo = $this->manipulateProfilePic($profileDetails->user_id, $profileDetails->profile_photo, $profileDetails->user_fbid);
                $userprofiledetails[] = array('user_id'=>$profileDetails->user_id,
                    'user_given_name'=>$profileDetails->user_given_name,
                    'user_profile_name'=>$profileDetails->user_profile_name,
                    'user_email'=>$profileDetails->user_email,
                    'user_status'=>$profileDetails->user_status,
                    'user_fbid'=>$profileDetails->user_fbid,
                    'user_profile_about_me'=>$profileDetails->user_profile_about_me,
                    'user_profile_current_location'=>$profileDetails->user_profile_current_location,
                    'user_profile_phone'=>$profileDetails->user_profile_phone,
                    'country_title'=>$profileDetails->country_title,
                    'country_code'=>$profileDetails->country_code,
                    'country_id'=>$profileDetails->country_id,
                    'city_name'=>$profileDetails->city_name,
                    'city_id'=>$profileDetails->city_id,
                    'profile_photo'=>$profile_details_photo,
                    'friendship_status' => $friendl_status,
                );
                if ($list['group_photo_photo'])
                    $group_cover_photo = $config['pathInfo']['ROOTPATH'].'/public/datagd/groups/'.$list['group_id'].'/'.$list['group_photo_photo'];
                else
                    $group_cover_photo = $config['pathInfo']['absolute_img_path'].'/images/noimg.jpg';
                switch($list['type']){
                    case "New Activity":
                        $activity_details = array();
                        $activity = $this->getActivityTable()->getActivityForFeed($list['event_id'],$userinfo->user_id);
                        $SystemTypeData   = $this->groupTable->fetchSystemType("Activity");
                        $like_details     = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$list['event_id'],$userinfo->user_id);
                        $comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$list['event_id'],$userinfo->user_id);
                        $str_liked_users  = '';
                        $arrLikedUsers = array();
                        $arrLikedUsers = $this->formatLikedUsers($like_details,$SystemTypeData->system_type_id,$list['event_id'],$userinfo->user_id);
                        $rsvp_count = $this->getActivityRsvpTable()->getCountOfAllRSVPuser($activity->group_activity_id)->rsvp_count;
                        $attending_users = array();
                        $tempattendusers =  array();
                        if($rsvp_count>0){
                            $attending_users = $this->getActivityRsvpTable()->getJoinMembers($activity->group_activity_id,3,0);
                        }
                        if (count($attending_users)){
                            foreach ($attending_users as $attendlist) {
                                unset($attendlist['group_activity_rsvp_id']);
                                unset($attendlist['group_activity_rsvp_user_id']);
                                unset($attendlist['group_activity_rsvp_activity_id']);
                                unset($attendlist['group_activity_rsvp_added_timestamp']);
                                unset($attendlist['group_activity_rsvp_added_ip_address']);
                                unset($attendlist['group_activity_rsvp_group_id']);
                                $attendlist['profile_photo'] = $this->manipulateProfilePic($attendlist['user_id'], $attendlist['profile_photo'], $attendlist['user_fbid']);
                                $tempattendusers[]=$attendlist;
                            }
                            $attending_users = $tempattendusers;
                        }

                        $allow_join = (strtotime($activity->group_activity_start_timestamp)>strtotime("now"))?1:0;
                        $activity_details[] = array(
                            "group_activity_id" => $activity->group_activity_id,
                            "group_activity_title" => $activity->group_activity_title,
                            "group_activity_location" => $activity->group_activity_location,
                            "group_activity_location_lat" => $activity->group_activity_location_lat,
                            "group_activity_location_lng" => $activity->group_activity_location_lng,
                            "group_activity_content" => $activity->group_activity_content,
                            "group_activity_start_timestamp" => date("M d,Y H:s a",strtotime($activity->group_activity_start_timestamp)),
                            "group_image_link" =>$group_cover_photo,
                            "group_title" =>$list['group_title'],
                            "group_seo_title" =>$list['group_seo_title'],
                            "group_id" =>$list['group_id'],
                            "like_count"	=>$like_details['likes_counts'],
                            "is_liked"	=>$like_details['is_liked'],
                            "comment_counts"	=>$comment_details['comment_counts'],
                            "is_commented"	=>$comment_details['is_commented'],
                            "rsvp_count" =>($activity->rsvp_count)?$activity->rsvp_count:0,
                            "rsvp_friend_count" =>($activity->friend_count)?$activity->friend_count:0,
                            "is_going"=>$activity->is_going,
                            "attending_users" =>$attending_users,
                            "allow_join" =>$allow_join,
                            'is_admin'=>$is_admin,
                        );
                        $feeds[] = array('content' => $activity_details,
                            'type'=>$list['type'],
                            'time'=>$this->timeAgo($list['update_time']),
                            'postedby'=>$userprofiledetails,
                        );
                        break;
                    case "New Status":
                        $discussion_details = array();
                        $discussion = $this->getDiscussionTable()->getDiscussionForFeed($list['event_id']);
                        $SystemTypeData = $this->groupTable->fetchSystemType("Discussion");
                        $like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$list['event_id'],$userinfo->user_id);
                        $comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$list['event_id'],$userinfo->user_id);
                        $str_liked_users = '';
                        $arrLikedUsers = array();
                        $arrLikedUsers = $this->formatLikedUsers($like_details,$SystemTypeData->system_type_id,$list['event_id'],$userinfo->user_id);
                        $discussion_details[] = array(
                            "group_discussion_id" => $discussion->group_discussion_id,
                            "group_discussion_content" => $discussion->group_discussion_content,
                            "group_image_link" =>$group_cover_photo,
                            "group_title" =>$list['group_title'],
                            "group_seo_title" =>$list['group_seo_title'],
                            "group_id" =>$list['group_id'],
                            "like_count"	=>$like_details['likes_counts'],
                            "is_liked"	=>$like_details['is_liked'],
                            "comment_counts"	=>$comment_details['comment_counts'],
                            "is_commented"	=>$comment_details['is_commented'],
                            'is_admin'=>$is_admin,
                        );
                        $feeds[] = array('content' => $discussion_details,
                            'type'=>$list['type'],
                            'time'=>$this->timeAgo($list['update_time']),
                            'postedby'=>$userprofiledetails,
                        );
                        break;
                    case "New Media":
                        $media_details = array();
                        $media = $this->getGroupMediaTable()->getMediaForFeed($list['event_id']);
                        $video_id  = '';
                        if($media->media_type == 'video')
                            $video_id  = $this->get_youtube_id_from_url($media->media_content);
                        $SystemTypeData = $this->groupTable->fetchSystemType("Media");
                        $like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$list['event_id'],$userinfo->user_id);
                        $comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$list['event_id'],$userinfo->user_id);
                        $str_liked_users = '';
                        $arrLikedUsers = array();
                        $arrLikedUsers = $this->formatLikedUsers($like_details,$SystemTypeData->system_type_id,$list['event_id'],$userinfo->user_id);
                        if (!empty($media->media_content)){
                            if($media->media_type == 'video'){
                                $media->media_content =	'http://img.youtube.com/vi/'.$video_id.'/0.jpg';
                            }else{
                                $media->media_content = $config['pathInfo']['absolute_img_path'].$config['image_folders']['group'].$list['group_id'].'/media/medium/'.$media->media_content;
                            }
                        }

                        $media_details[] = array(
                            "group_media_id" => $media->group_media_id,
                            "media_type" => $media->media_type,
                            "media_content" => $media->media_content,
                            "media_caption" => $media->media_caption,
                            "video_id" => $video_id,
                            "group_image_link" =>$group_cover_photo,
                            "group_title" =>$list['group_title'],
                            "group_seo_title" =>$list['group_seo_title'],
                            "group_id" =>$list['group_id'],
                            "like_count"	=>$like_details['likes_counts'],
                            "is_liked"	=>$like_details['is_liked'],
                            "comment_counts"	=>$comment_details['comment_counts'],
                            "is_commented"	=>$comment_details['is_commented'],
                            'is_admin'=>$is_admin,
                        );
                        $feeds[] = array(
                            'content' => $media_details,
                            'type'=>$list['type'],
                            'time'=>$this->timeAgo($list['update_time']),
                            'postedby'=>$userprofiledetails,
                        );
                        break;
                }
            }
        }
        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
        $dataArr[0]['message'] = $error;
        $dataArr[0]['feeds'] = $feeds;
        echo json_encode($dataArr);
        exit;

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
            $error =($post['mediatype']!="status" && $post['mediatype']!="image" && $post['mediatype']!="video" && $post['mediatype']!="event")? "please post with valid media type":$error;
            $error =($post['groupid']==null || $post['groupid']=='' || $post['groupid']=='undefined' || !is_numeric($post['groupid']))? "please input a valid group id":$error;
            $group  = $this->getGroupTable()->getPlanetinfo($post['groupid']);
            $error =(empty($group)||$group->group_id=='')?"Given group not exist in the system":$error;
            $error =(empty($userinfo))?"Invalid Access Token.":$error;
            if (!empty($error)){
                $dataArr[0]['flag'] = $this->flagFailure;
                $dataArr[0]['message'] = $error;
                echo json_encode($dataArr);
                exit;
            }
            $userPermissionOnGroup = $this->getUserGroupTable()->getGroupUserDetails($group->group_id,$userinfo->user_id);
            $error =(empty($userPermissionOnGroup))?"User has no permission or not a member of the group to post.":$error;
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
                            $msg = $userinfo->user_given_name." added a new status under the group ".$group->group_title;
							$subject = 'New status added';
							$process = 'New Discussion';
                            $this->grouppostNotifications($joinedMembers, $subject, $msg, $userinfo,$IdDiscussion,6,$process);
                            $dataArr[0]['flag'] = $this->flagSuccess;
                            $error = "Status posted successfully";
                        }else{ $dataArr[0]['flag'] = $this->flagFailure; $error = "Some error occurred. Please try again";}
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
                        $objActivty->group_activity_location_lat = (isset($post['location_lat']))?$post['location_lat']:"";
                        $objActivty->group_activity_location_lng = (isset($post['location_lng']))?$post['location_lng']:"";
                        $newActivity_id = $this->getActivityTable()->createActivity($objActivty);
                        if($newActivity_id){
                            $msg = $userinfo->user_given_name." added a new event under the group ".$group->group_title;
                            $base_url = $config['pathInfo']['base_url'];
                            $subject = 'New event added';
                            $from = 'admin@jeera.com';
							$process = 'New Event';
                            if(!isset($post['membertype']) || empty($post['membertype'])) $post['membertype'] = "allmembers";
                            switch($post['membertype']){
                                case "allmembers":
                                    $joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($group->group_id);
                                    if (count($joinedMembers)){
                                        foreach($joinedMembers as $members){
                                            if($members->user_group_user_id!=$userinfo->user_id){
                                                $this->UpdateNotifications($members->user_group_user_id,$msg,7,$subject,$from,$userinfo->user_id,$newActivity_id,$process);
                                            }
                                        }
                                    }
                                    break;
                                case "friends":
                                    $friends = $this->getUserFriendTable()->userFriends($userinfo->user_id);
                                    $this->grouppostNotifications($friends, $subject, $msg, $userinfo, $newActivity_id,7,$process);
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
                                                $this->UpdateNotifications($members,$msg,7,$subject,$from,$userinfo->user_id,$newActivity_id,$process);
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
									$process = 'New Media';
                                    $this->grouppostNotifications($joinedMembers, $subject, $msg, $userinfo,$addeditem,8,$process);
                                    $dataArr[0]['flag'] = $this->flagSuccess; $error = "media posted successfully";
                                }else{ $dataArr[0]['flag'] = $this->flagFailure; $error = "Some error occcured. Please try again"; }
                            }
                        }else{
                            $dataArr[0]['flag'] = $this->flagFailure;
                            $error = "Some error occured, during file upload. Please try again";
                        }
                    }else{
                        $dataArr[0]['flag'] = $this->flagFailure;
                        $error = "Select a image to upload and continue";
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
							$process = 'New Media';
                            $this->grouppostNotifications($joinedMembers, $subject, $msg, $userinfo, $addeditem,8,$process);
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
    public function manipulateProfilePic($user_path_id, $profile_path_photo = null, $fb_path_id = null){
        $config = $this->getServiceLocator()->get('Config');
        $return_photo = null;
        if (!empty($profile_path_photo))
            $return_photo = $config['pathInfo']['absolute_img_path'].$config['image_folders']['profile_path'].$user_path_id.'/'.$profile_path_photo;
        else if(isset($fb_path_id) && !empty($fb_path_id))
            $return_photo = 'http://graph.facebook.com/'.$fb_path_id.'/picture?type=normal';
        else
            $return_photo = $config['pathInfo']['absolute_img_path'].'/images/noimg.jpg';
        return $return_photo;

    }
    public function formatLikedUsers($like_details,$system_type_id,$refer_id,$user_id){
        $arrLikedUsers = array();
        if(!empty($like_details)&&isset($like_details['likes_counts'])){
            $liked_users = $this->getLikeTable()->likedUsersForRestAPI($system_type_id,$refer_id,$user_id,"","");
            if($like_details['likes_counts']>0&&!empty($liked_users)){
                foreach($liked_users as $likeuser){
                    $arrLikedUsers[] = $likeuser['user_given_name'];
                }
            }
        }
        return $arrLikedUsers;
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
    public function get_youtube_id_from_url($url){
        if (stristr($url,'youtu.be/'))
        {preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $final_ID); return isset($final_ID[4])?$final_ID[4]:''; }
        else
        {@preg_match('/(https:|http:|):(\/\/www\.|\/\/|)(.*?)\/(embed\/|watch.*?v=|channel\/)([a-z_A-Z0-9\-]{11})/i', $url, $IDD); return isset($IDD[5])?$IDD[5]:''; }
    }
    public function checkError($error){
        if (!empty($error)){
            $dataArr[0]['flag'] = $this->flagFailure;
            $dataArr[0]['message'] = $error;
            echo json_encode($dataArr);
            exit;
        }
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
    public function grouppostNotifications($joinedMembers, $subject, $msg, $userinfo, $reference_id,$type,$process){
        if (count($joinedMembers)) {
            $config = $this->getServiceLocator()->get('Config');
            $base_url = $config['pathInfo']['base_url'];
            $from = 'admin@jeera.com';
            foreach ($joinedMembers as $members) {
                if ($members->user_group_user_id != $userinfo->user_id) {
                    $this->UpdateNotifications($members->user_group_user_id, $msg, $type, $subject, $from, $userinfo->user_id, $reference_id,$process);
                }
            }
        }
    }
    public function UpdateNotifications($user_notification_user_id,$msg,$type,$subject,$from,$sender,$reference_id,$process){
        $UserGroupNotificationData = array();
        $UserGroupNotificationData['user_notification_user_id'] =$user_notification_user_id;
        $UserGroupNotificationData['user_notification_content']  = $msg;
        $UserGroupNotificationData['user_notification_added_timestamp'] = date('Y-m-d H:i:s');
        $UserGroupNotificationData['user_notification_notification_type_id'] = $type;
        $UserGroupNotificationData['user_notification_status'] = 'unread';
        $UserGroupNotificationData['user_notification_sender_id'] = $sender;
        $UserGroupNotificationData['user_notification_reference_id'] = $reference_id;
		$UserGroupNotificationData['user_notification_process'] = $process;
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
    public function getUserFriendTable(){
        $sm = $this->getServiceLocator();
        return  $this->userFriendTable = (!$this->userFriendTable)?$sm->get('User\Model\UserFriendTable'):$this->userFriendTable;
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
    public function getGroupPhotoTable(){
        $sm = $this->getServiceLocator();
        return  $this->groupPhotoTable = (!$this->groupPhotoTable)?$sm->get('Groups\Model\GroupPhotoTable'):$this->groupPhotoTable;
    }
}
