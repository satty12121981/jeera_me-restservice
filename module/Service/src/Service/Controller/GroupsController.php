<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Service\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\View\Renderer\PhpRenderer;
use \Exception;

class GroupsController extends AbstractActionController
{
                                                
        public $form_error ;
        public $flagSuccess;
	public $flagError;
	protected $userTable;
	protected $userProfileTable;
	protected $userFriendTable;
	protected $userGroupTable;
	protected $userTagTable;
	protected $groupTable;
	protected $activityTable;
	protected $discussionTable;
	protected $groupMediaTable;
	protected $likeTable;
	protected $commentTable;
	protected $activityRsvpTable;
	protected $groupTagTable;
	 
    protected $groupJoiningQuestionnaire;
    protected $groupQuestionnaireOptions;
    protected $groupJoiningInvitationTable;
    protected $userGroupJoiningRequestTable;
    protected $groupQuestionnaireAnswersTable;
	
    public function __construct(){
        $this->flagSuccess = "Success";
        $this->flagError = "Failure";
    }
    public function exploregroupsAction(){
    	$error = '';
		$request   = $this->getRequest();
		if ($request->isPost()){ 
			$config = $this->getServiceLocator()->get('Config');
			$post = $request->getPost();
			$accToken = (isset($post['accesstoken'])&&$post['accesstoken']!=null&&$post['accesstoken']!=''&&$post['accesstoken']!='undefined')?strip_tags(trim($post['accesstoken'])):'';
			if (empty($accToken)) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			$user_details = $this->getUserTable()->getUserByAccessToken($accToken);
			if(empty($user_details)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Invalid Access Token.";
				echo json_encode($dataArr);
				exit;
			}
			$user_id = $user_details->user_id;
			$city = (isset($post['city'])&&$post['city']!=null&&$post['city']!=''&&$post['city']!='undefined')?strip_tags(trim($post['city'])):'';
			$country = (isset($post['country'])&&$post['country']!=null&&$post['country']!=''&&$post['country']!='undefined')?strip_tags(trim($post['country'])):'';	
			$category = (isset($post['categories'])&&$post['categories']!=null&&$post['categories']!=''&&$post['categories']!='undefined')?$post['categories']:'';
			$myfriends = (isset($post['myfriends'])&&$post['myfriends']!=null&&$post['myfriends']!=''&&$post['myfriends']!='undefined'&&$post['myfriends']==true)?strip_tags(trim($post['myfriends'])):'';
			$offset = (isset($post['nparam'])&&$post['nparam']!=null&&$post['nparam']!=''&&$post['nparam']!='undefined')?trim($post['nparam']):0;
			$limit = (isset($post['countparam'])&&$post['countparam']!=null&&$post['countparam']!=''&&$post['countparam']!='undefined')?trim($post['countparam']):30;
			if(!empty($country) && !is_numeric($country)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Enter a valid country id.";
				echo json_encode($dataArr);
				exit;
			} 
			if(!empty($city) && !is_numeric($city)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Enter a valid city id.";
				echo json_encode($dataArr);
				exit;
			}
			if (isset($limit) && !is_numeric($limit)) {
 				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid Count Field.";
				echo json_encode($dataArr);
				exit;		
			}
			if (isset($offset) && !is_numeric($offset)) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid N Field.";
				echo json_encode($dataArr);
				exit;
			}
			$arr_group_list = '';
            $user_tag_available_status = '';
            $user_tag_status = $this->getUserTagTable()->checkTagExistForUser($user_id);
            if ($user_tag_status[0]['tag_exists']) $user_tag_available_status = 1;
			$groups = $this->getUserGroupTable()->getMatchGroupsByUserTagsForRestApi($user_id,$user_tag_available_status,$city,$country,$myfriends,$category,(int) $limit,(int) $offset);
			if(!empty($groups)){
				foreach($groups as $list){
					if (!empty($list['group_photo_photo']))
					$list['group_photo_photo'] = $config['pathInfo']['absolute_img_path'].$config['image_folders']['group'].$list['group_id'].'/medium/'.$list['group_photo_photo'];
					else
					$list['group_photo_photo'] = $config['pathInfo']['absolute_img_path'].'/images/group-img_def.jpg';
					$tag_category = $this->getGroupTagTable()->getAllGroupTagCategiry($list['group_id']);
					$tags = $this->getGroupTagTable()->fetchAllGroupTags($list['group_id']);
                    $groupUsers = $this->getUserGroupTable()->getMembers($list['group_id'],$user_id,"",0,5);
                    $arrMembers = array();
                    if (!empty($groupUsers)) {
                        foreach ($groupUsers as $f_list) {
                            $profile_photo = $this->manipulateProfilePic($user_id, $f_list['profile_icon'], $f_list['user_fbid']);
                            $arrMembers[] = array(
                                'user_id'=>$f_list['user_id'],
                                'user_given_name'=>$f_list['user_given_name'],
                                'user_profile_name'=>$f_list['user_profile_name'],
                                'country_title'=>$f_list['country_title'],
                                'country_code'=>$f_list['country_code'],
                                'city'=>$f_list['city'],
                                'profile_photo'=>$profile_photo,
                                'is_admin'=>$f_list['is_admin'],
                                'user_group_is_owner'=>$f_list['user_group_is_owner'],
                                'user_group_role'=>$f_list['user_group_role']
                            );

                        }
                        $flag = 1;
                    }
					$temptags = array();
					foreach($tags as $tags_list){						 
						$temptags[] = array('tag_id'=>$tags_list['tag_id'],
											'tag_title'=>$tags_list['tag_title']);
					}		
					$tag_category_temp = array();
					foreach($tag_category as $tag_category_list){
						if (!empty($tag_category_list['tag_category_icon']))
						$tag_category_list['tag_category_icon'] = $config['pathInfo']['absolute_img_path'].$config['image_folders']['tag_category'].$tag_category_list['tag_category_icon'];
						else
						$tag_category_list['tag_category_icon'] = $config['pathInfo']['absolute_img_path'].'/images/category-icon.png';
						$tag_category_temp[] = array('tag_category_id'=>$tag_category_list['tag_category_id'],
													'tag_category_title'=>$tag_category_list['tag_category_title'],
													'tag_category_icon'=>$tag_category_list['tag_category_icon']
												);
					}
					$arr_group_list[] = array(
						'group_id' =>$list['group_id'],
						'group_title' =>$list['group_title'],
						'group_seo_title' =>$list['group_seo_title'],
						'group_type' =>(empty($list['group_type']))?"":$list['group_type'],
						'group_photo_photo' =>$list['group_photo_photo'],										 
						'country_title' =>$list['country_title'],
						'country_code' =>$list['country_code'],
						'member_count' =>$list['member_count'],
						'friend_count' =>$list['friend_count'],
						'city' =>$list['city'],	
						'tag_category_count' =>count($tag_category),
						'tag_category' =>$tag_category_temp,
						'tags' =>$temptags,
                        'groupmembers'=>$arrMembers,
						);
				}
				$dataArr[0]['flag'] = "Success";
                if (!empty($user_tag_available_status))
                    $dataArr[0]['usertagsexist'] = 'Yes';
                else
                    $dataArr[0]['usertagsexist'] = 'No';
				$dataArr[0]['groups'] = $arr_group_list;
				echo json_encode($dataArr);
				exit;
			}
			else{
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "No Groups available.";
				echo json_encode($dataArr);
				exit;
			}
		}else{
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request Not Authorised.";
			echo json_encode($dataArr);
			exit;
		}
		return;
    }
	public function groupdetailsAction(){
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$flag=0;
			$config = $this->getServiceLocator()->get('Config');
			$postedValues = $this->getRequest()->getPost();
			
			$offset = trim($postedValues['nparam']);
			$limit = trim($postedValues['countparam']);
			$type = trim($postedValues['type']);
			$activity = trim($postedValues['activity']);
			$group_id = trim($postedValues['groupid']);
			$accToken = strip_tags(trim($postedValues['accesstoken']));

			if ((!isset($accToken)) || (trim($accToken) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Autdhorised.";
				echo json_encode($dataArr);
				exit;
			}
			if ((!isset($group_id)) || (trim($group_id) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			if (isset($group_id) && !is_numeric($group_id)) {
 				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid GroupId.";
				echo json_encode($dataArr);
				exit;		
			}
			if (isset($limit) && !is_numeric($limit)) {
 				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid Count Field.";
				echo json_encode($dataArr);
				exit;		
			}
			if (isset($offset) && !is_numeric($offset)) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid N Field.";
				echo json_encode($dataArr);
				exit;
			}
			$user_details = $this->getUserTable()->getUserByAccessToken($accToken);
			if(empty($user_details)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Invalid Access Token.";
				echo json_encode($dataArr);
				exit;
			}
			$user_id = $user_details->user_id;
			$newsfeedsList = $this->getGroupsTable()->getGroupNewsFeeds($user_id,$type,$group_id,$activity,(int) $limit,(int) $offset);
			$groupdetailslist  = $this->getGroupsTable()->getGroupDetails($group_id,$user_id);	
			$arr_group_list = array();
			$feeds = array();
			if (!empty($groupdetailslist)){
				if (!empty($groupdetailslist->group_photo_photo))
				$groupdetailslist->group_photo_photo = $config['pathInfo']['absolute_img_path'].$config['image_folders']['group'].$groupdetailslist->group_id.'/medium/'.$groupdetailslist->group_photo_photo;
				else
				$groupdetailslist->group_photo_photo = $config['pathInfo']['absolute_img_path'].'/images/group-img_def.jpg';
				$tag_category = $this->getGroupTagTable()->getAllGroupTagCategiry($groupdetailslist->group_id);
				$tags = $this->getGroupTagTable()->fetchAllGroupTags($groupdetailslist->group_id);
				$temptags = array();
				$tag_category_temp = array();
				foreach($tags as $tags_list){
					$temptags[] = array('tag_id'=>$tags_list['tag_id'],
										'tag_title'=>$tags_list['tag_title']);					 
				}
				$tags = $temptags;
				foreach($tag_category as $tag_category_list){					 

					if (!empty($tag_category_list['tag_category_icon']))
					$tag_category_list['tag_category_icon'] = $config['pathInfo']['absolute_img_path'].$config['image_folders']['tag_category'].$tag_category_list['tag_category_icon'];
					else
					$tag_category_list['tag_category_icon'] = $config['pathInfo']['absolute_img_path'].'/images/category-icon.png';
					$tag_category_temp[] = array('tag_category_id'=>$tag_category_list['tag_category_id'],
												 'tag_category_title'=>$tag_category_list['tag_category_title'],
												 'tag_category_icon'=>$tag_category_list['tag_category_icon']
												);					 
				}
				$tag_category = $tag_category_temp;				
				$arr_group_list[] = array(
					'group_id' =>$groupdetailslist->group_id,
					'group_title' =>$groupdetailslist->group_title,
					'group_seo_title' =>$groupdetailslist->group_seo_title,
					'group_type' =>(empty($groupdetailslist->group_type))?"":$groupdetailslist->group_type,
					'group_photo_photo' =>$groupdetailslist->group_photo_photo,										 
					'country_title' =>$groupdetailslist->country_title,
					'country_code' =>$groupdetailslist->country_code,
					'member_count' =>$groupdetailslist->member_count,
					'friend_count' =>$groupdetailslist->friend_count,
					'city' =>$groupdetailslist->city,	
					'tag_category_count' =>count($tag_category),
					'tag_category' =>$tag_category,
					'tags' =>$tags,
					);
				$flag = 1;
			}

			$groupUsers = $this->getUserGroupTable()->fetchAllUserListForGroup($group_id,$user_id,0,5)->toArray();
			$tempmembers = array();
			if (!empty($groupUsers)) {
				
				foreach ($groupUsers as $list) {
					unset($list['user_register_type']);
					$list['profile_photo'] = $this->manipulateProfilePic($user_id, $list['profile_photo'], $list['user_fbid']);

					$friend_status ="";
					if($list['is_friend']){
						$friend_status = 'IsFriend';
					}
					else if($list['is_requested']){
						$friend_status = 'AccessUserRequested';
					}
					else if($list['get_request']){
						$friend_status = 'GroupUserRequested';
					}
					else if ( $user_id == $list['user_id']){
						$friend_status = '';
					}else{
						$friend_status = 'NoFriends';
					}
					$list['friend_status']= $friend_status;
					unset($list['is_friend']);
					unset($list['is_requested']);
					unset($list['get_request']);

					$tempmembers[] = $list;
				}
				$flag = 1;
			}

			if(!empty($newsfeedsList)){
				foreach($newsfeedsList as $list){
					$profile_photo = $this->manipulateProfilePic($user_id, $list['profile_photo'], $list['user_fbid']);
					$profileDetails = $this->getUserTable()->getProfileDetails($list['user_id']);
					$userprofiledetails = array();
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
								'profile_photo'=>$profile_photo,
								);
					switch($list['type']){
						case "New Activity":
						$activity_details = array();
						$activity = $this->getActivityTable()->getActivityForFeed($list['event_id'],$user_id);
						$SystemTypeData   = $this->getGroupsTable()->fetchSystemType("Activity");
						$like_details     = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$list['event_id'],$user_id); 
						$comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$list['event_id'],$user_id); 
						$str_liked_users  = '';
						
						if(!empty($like_details)&&isset($like_details['likes_counts'])){  
							$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$list['event_id'],$user_id,2,0);
						}
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
						$activity_details[] = array(
												"group_activity_id" => $activity->group_activity_id,
												"group_activity_title" => $activity->group_activity_title,
												"group_activity_location" => $activity->group_activity_location,
												"group_activity_location_lat" => $activity->group_activity_location_lat,
												"group_activity_location_lng" => $activity->group_activity_location_lng,
												"group_activity_content" => $activity->group_activity_content,
												"group_activity_start_timestamp" => date("M d,Y H:s a",strtotime($activity->group_activity_start_timestamp)),												 
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
							$SystemTypeData = $this->getGroupsTable()->fetchSystemType("Discussion");
							$like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$list['event_id'],$user_id);
							$comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$list['event_id'],$user_id); 
							$str_liked_users = '';
							if(!empty($like_details)&&isset($like_details['likes_counts'])){  
								$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$list['event_id'],$user_id,2,0);
							}
							$discussion_details[]= array(
												"group_discussion_id" => $discussion->group_discussion_id,
												"group_discussion_content" => $discussion->group_discussion_content,
												"group_title" =>$list['group_title'],
												"group_seo_title" =>$list['group_seo_title'],
												"group_id" =>$list['group_id'],												
												"like_count"	=>$like_details['likes_counts'],
												"is_liked"	=>$like_details['is_liked'],
												"comment_counts"	=>$comment_details['comment_counts'],
												"is_commented"	=>$comment_details['is_commented'],												 
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
							$SystemTypeData = $this->getGroupsTable()->fetchSystemType("Media");
							$like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$list['event_id'],$user_id);
							$comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$list['event_id'],$user_id); 
							$str_liked_users = '';
							if(!empty($like_details)&&isset($like_details['likes_counts'])){  
								$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$list['event_id'],$user_id,2,0);
							}
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
												"group_title" =>$list['group_title'],
												"group_seo_title" =>$list['group_seo_title'],	
												"group_id" =>$list['group_id'],													
												"like_count"	=>$like_details['likes_counts'],
												"is_liked"	=>$like_details['is_liked'],	
												"comment_counts"	=>$comment_details['comment_counts'],
												"is_commented"	=>$comment_details['is_commented'],												 										
												);
							$feeds[] = array('content' => $media_details,
											'type'=>$list['type'],
											'time'=>$this->timeAgo($list['update_time']),
											'postedby'=>$userprofiledetails,
							); 
						break;
					}
				}
			}

			if($flag){
				$dataArr[0]['flag'] = "Success";
				$dataArr[0]['groupdetails'] = $arr_group_list;
				$dataArr[0]['groupmembers'] = $tempmembers;
				$dataArr[0]['groupposts'] = $feeds;
				echo json_encode($dataArr);
				exit; 
			}
			else{
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "No details available.";
				echo json_encode($dataArr);
				exit;
			}
			
		}else{
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request Not Authorised.";
			echo json_encode($dataArr);
			exit;
		}
    }
	public function groupmembersAction(){
		$arrMembers = array();
    	if($this->getRequest()->getMethod() == 'POST') {
			$config = $this->getServiceLocator()->get('Config');
			$postedValues = $this->getRequest()->getPost();
			$offset = trim($postedValues['nparam']);
			$limit = trim($postedValues['countparam']);
			$type = trim($postedValues['type']);
			$group_id = trim($postedValues['groupid']);
			$accToken = strip_tags(trim($postedValues['accesstoken']));

			if ((!isset($accToken)) || (trim($accToken) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			if ((!isset($group_id)) || (trim($group_id) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			if (isset($group_id) && !is_numeric($group_id)) {
 				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid GroupId.";
				echo json_encode($dataArr);
				exit;		
			}
			if (isset($limit) && !is_numeric($limit)) {
 				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid Count Field.";
				echo json_encode($dataArr);
				exit;		
			}
			if (isset($offset) && !is_numeric($offset)) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid N Field.";
				echo json_encode($dataArr);
				exit;
			}
			$myinfo = $this->getUserTable()->getUserByAccessToken($accToken);
			if(empty($myinfo)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Invalid Access Token.";
				echo json_encode($dataArr);
				exit;
			}			 
			$group  = $this->getGroupsTable()->getPlanetinfo($group_id);
			if(empty($group)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Given group not exist in this system.";
				echo json_encode($dataArr);
				exit;
			}		 
			$members_list = $this->getUserGroupTable()->getMembers($group_id,$myinfo->user_id,$type,(int) $offset,(int) $limit);			
			if(!empty($members_list)){
				foreach($members_list as $list){
					$tag_category = $this->getUserTagTable()->getAllUserTagCategiry($list['user_id']);
					$objcreated_group_count = $this->getUserGroupTable()->getCreatedGroupCount($list['user_id']);
					if(!empty($objcreated_group_count)){
					$created_group_count = $objcreated_group_count->created_group_count;
					}else{$created_group_count =0;}
					$is_friend = ($this->getUserFriendTable()->isFriend($list['user_id'],$myinfo->user_id))?1:0;
					$is_requested = ($this->getUserFriendTable()->isRequested($list['user_id'],$myinfo->user_id))?1:0;
					$isPending = ($this->getUserFriendTable()->isPending($list['user_id'],$myinfo->user_id))?1:0;
					$profile_photo = $this->manipulateProfilePic($myinfo->user_id, $list['profile_icon'], $list['user_fbid']);
					$friend_status ="";
					if($is_friend){
						$friend_status = 'IsFriend';
					}
					else if($is_requested){
						$friend_status = 'AccessUserRequested';
					}
					else if($isPending){
						$friend_status = 'GroupUserRequested';
					}
					else if ( $myinfo->user_id == $list['user_id']){
						$friend_status = '';
					}else{
						$friend_status = 'NoFriends';
					}
					if (count($tag_category)){
						$tag_category_temp = array();
						foreach($tag_category as $tag_category_list){					 

							if (!empty($tag_category_list['tag_category_icon']))
							$tag_category_list['tag_category_icon'] = $config['pathInfo']['absolute_img_path'].$config['image_folders']['tag_category'].$tag_category_list['tag_category_icon'];
							else
							$tag_category_list['tag_category_icon'] = $config['pathInfo']['absolute_img_path'].'/images/category-icon.png';
							$tag_category_temp[] = array('tag_category_id'=>$tag_category_list['tag_category_id'],
														 'tag_category_title'=>$tag_category_list['tag_category_title'],
														 'tag_category_icon'=>$tag_category_list['tag_category_icon']
														);					 
						}
						$tag_category = $tag_category_temp;
					}
					
					$arrMembers[] = array(
									'user_id'=>$list['user_id'],
									'user_given_name'=>$list['user_given_name'],
									'user_profile_name'=>$list['user_profile_name'],
									'country_title'=>$list['country_title'],
									'country_code'=>$list['country_code'],
									'city'=>$list['city'],
									'profile_photo'=>$profile_photo,
									'tag_count' =>count($tag_category),
									'tag_category' =>$tag_category,
									'joined_group_count'=>$list['group_count'],
									'created_group_count'=>$created_group_count,
									'is_admin'=>($type == 'pending')?0:$list['is_admin'],
									'user_group_is_owner'=>($type == 'pending')?0:$list['user_group_is_owner'],
									'user_group_role'=>($type == 'pending')?'':$list['user_group_role'],
									'friendship_status'=>$friend_status,
									);
				}
			}
			$dataArr[0]['flag'] =  'Success';
			$dataArr[0]['groupmembers'] = $arrMembers;		
			echo json_encode($dataArr);
			exit;
		}else{
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request Not Authorised.";
			echo json_encode($dataArr);
			exit;
		}
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
	public function getGroupQuestionsAction() {
        $dataArr                                                    = array();               // declare array for response data
        $arrQuestions                                               = array();              // declare array for questions
        $request                                                    = $this->getRequest(); // create request object

        // if request is of type POST
        if ($request->isPost()) {
            // create post object
            $post                                                   = $request->getPost();
            // get access token
			$accToken                                               = (isset($post['accesstocken']) && $post['accesstocken'] != null && $post['accesstocken'] != '' && $post['accesstocken'] != 'undefined') ? strip_tags(trim($post['accesstocken'])) : '';
            // get group id
             $intGroupId                                            = (isset($post['GroupId']) && $post['GroupId'] != null && $post['GroupId'] != '' && $post['GroupId'] != 'undefined') ? strip_tags(trim($post['GroupId'])) : '';

             // check access token
             if ($accToken == '') {
				$dataArr[0]['flag']                                 = $this->flagError;
				$dataArr[0]['message']                              = "Request Not Authorised.";
                $dataArr[0]['questions']                            = $arrQuestions;
				echo json_encode($dataArr);
				exit;
             }
             // check user id
             if ($intGroupId == '') {
                    $dataArr[0]['flag']                             = $this->flagError;
                    $dataArr[0]['message']                          = "Request Not Authorised.";
                    $dataArr[0]['questions']                        = $arrQuestions;
                    echo json_encode($dataArr);
                    exit;
             }

             // get user details on the basis of access token
			$user_details                                           = $this->getUserTable()->getUserByAccessToken($accToken);

            if(!empty($user_details)) {
                // get user id on the basis of access token i.e. the user which is logged in
                $loggedin_userId                                    = $user_details->user_id;

                // get group
                $arrGroup                                           = $this->getGroupTable()->getPlanetinfo($intGroupId);
                if(!empty($arrGroup)) {
                    // get questions
                     $arrQuestionnaire                              = $this->getGroupJoiningQuestionnaireTable()->getQuestionnaireArray($intGroupId);
                     if(!empty($arrQuestionnaire)){
                         $ctr                                       = 0;
                         foreach($arrQuestionnaire as $question){
                             $arrQuestions[$ctr]['questionnaire_id']    = $question['questionnaire_id'];
                             $arrQuestions[$ctr]['question']            = $question['question'];
                             $arrQuestions[$ctr]['answer_type']         = strtolower($question['answer_type']);

                             // check answer type for options
                             if($question['answer_type'] == 'checkbox' || $question['answer_type'] == 'radio'){
                                $arrOptionsDetails                 = $this->getGroupQuestionnaireOptionsTable()->getoptionOfOneQuestion($question['questionnaire_id']);
                                $arrOptions                        = array();
                                $optCtr                            = 0;
                                foreach($arrOptionsDetails as $option){
                                    $arrOptions[$optCtr]['option_id']  = $option['option_id'];
                                    $arrOptions[$optCtr]['option']     = $option['option'];
                                    $optCtr++;
                                }// foreach
                                $arrQuestions[$ctr]['options']          = $arrOptions;
                             }// if check answer type
                             $ctr++;
                         }// foreach
                            $dataArr[0]['flag']                     = $this->flagSuccess;
                            $dataArr[0]['message']                  = "";
                            $dataArr[0]['questions']                = $arrQuestions;
                     }else{
                        $dataArr[0]['flag']                         = $this->flagSuccess;
                        $dataArr[0]['message']                      = "No question exists for this group.";
                        $dataArr[0]['questions']                    = $arrQuestions;
                     }
                }else{
                    $dataArr[0]['flag']                             = $this->flagError;
                    $dataArr[0]['message']                          = "Group not exist in the system.";
                    $dataArr[0]['questions']                        = $arrQuestions;
                }
            }else{
                $dataArr[0]['flag']                                 = $this->flagError;
				$dataArr[0]['message']                              = "Invalid Access Token.";
                $dataArr[0]['questions']                            = $arrQuestions;
            }
        }else{
            $dataArr[0]['flag']                                     = $this->flagError;
			$dataArr[0]['message']                                  = "Request Not Authorised.";
            $dataArr[0]['questions']                                = $arrQuestions;
        }

        echo json_encode($dataArr);
        exit;

    }	  
    public function joinGroupAction() {
        $dataArr                                                    = array();               // declare array for response data
        $arrQuestions                                               = array();              // declare array for questions/answer
        $request                                                    = $this->getRequest(); // create request object

        // if request is of type POST
        if ($request->isPost()) {
             $post                                                  = $request->getPost();
            // get access token
			$accToken                                               = (isset($post['accesstocken']) && $post['accesstocken'] != null && $post['accesstocken'] != '' && $post['accesstocken'] != 'undefined') ? strip_tags(trim($post['accesstocken'])) : '';
            // get group id
             $intGroupId                                            = (isset($post['GroupId']) && $post['GroupId'] != null && $post['GroupId'] != '' && $post['GroupId'] != 'undefined') ? strip_tags(trim($post['GroupId'])) : '';
             // question ans answers0
             $arrQuestionAnswer                                     = (isset($post['QuestionAnswers']) && $post['QuestionAnswers'] != null && $post['QuestionAnswers'] != '' && $post['QuestionAnswers'] != 'undefined') ? strip_tags(trim($post['QuestionAnswers'])) : '';

            // check access token
            if ($accToken != '') {
                 // check group id
                 if ($intGroupId != '') {
                    // get user details on the basis of access token
                    $user_details                                   = $this->getUserTable()->getUserByAccessToken($accToken);
                    if(!empty($user_details)) {
                        // get user id on the basis of access token i.e. the user which is logged in
                        $loggedin_userId                            = $user_details->user_id;
                        // get group
                        $arrGroup                                   = $this->getGroupTable()->getPlanetinfo($intGroupId);
                        if(!empty($arrGroup)) {
                            $strGroupType                           = $arrGroup['group_type'];
                            // check user if already  the member or not
                            $usergroup                              = $this->getUserGroupTable()->getUserGroup($loggedin_userId,$intGroupId);
                            if(empty($usergroup)){
                                // check the question and options
                                $questionStatus                     = $this->fncValidateQuestionAnswer($intGroupId, $arrQuestionAnswer);
                                if($questionStatus == 0){
                                    // for open group
                                    if($arrGroup['group_type'] == 'open'){
                                        $user_data['user_group_user_id']    = $loggedin_userId;
                                        $user_data['user_group_group_id']   = $intGroupId;
                                        $user_data['user_group_status']     = "available";
                                        $this->getUserGroupTable()->AddMembersTOGroup($user_data);
                                        //save question and answer
                                        $this->fncSaveQuestionAnswer($intGroupId, $loggedin_userId, $arrQuestionAnswer);

                                        $dataArr[0]['flag']                 = $this->flagSuccess;
                                        $dataArr[0]['message']              = "User added into group.";
                                    }else if($arrGroup['group_type'] == 'private'){// for private group
                                        // check invitation existence
                                        $invitedHystory = $this->getGroupJoiningInvitationTable()->checkInvited($loggedin_userId,$intGroupId);
                                        if($invitedHystory['user_group_joining_invitation_id'] != ''){
                                            $user_data['user_group_user_id']    = $loggedin_userId;
                                            $user_data['user_group_group_id']   = $intGroupId;
                                            $user_data['user_group_status']     = "available";
                                            $this->getUserGroupTable()->AddMembersTOGroup($user_data);

                                            //save question and answer
                                            $this->fncSaveQuestionAnswer($intGroupId, $loggedin_userId, $arrQuestionAnswer);

                                            $dataArr[0]['flag']                 = $this->flagSuccess;
                                            $dataArr[0]['message']              = "User added into group.";
                                        }else{
                                            $dataArr[0]['flag']                 = $this->flagError;
                                            $dataArr[0]['message']              = "Invitation does not exist in system.";
                                        }
                                    }else if($arrGroup['group_type'] == 'public'){
                                        // check request existence
                                         $invitedHystory = $this->getUserGroupJoiningRequestTable()->checkIfrequestExist($loggedin_userId,$intGroupId);
                                         if($invitedHystory['user_group_joining_request_id'] == ''){
                                                $user_data['user_group_joining_request_user_id']    = $loggedin_userId;
                                                $user_data['user_group_joining_request_group_id']   = $intGroupId;
                                                $user_data['user_group_joining_request_status']     = "active";
                                                $this->getUserGroupJoiningRequestTable()->AddRequestTOGroup($user_data);

                                                //save question and answer
                                                $this->fncSaveQuestionAnswer($intGroupId, $loggedin_userId, $arrQuestionAnswer);

                                                $dataArr[0]['flag']     = $this->flagSuccess;
                                                $dataArr[0]['message']  = "Request has been sent.";
                                         }else{
                                             $dataArr[0]['flag']        = $this->flagError;
                                             $dataArr[0]['message']     = "Request has already been sent.";
                                         }
                                    }
                                }else{
                                    $dataArr[0]['flag']             = $this->flagError;
                                    $dataArr[0]['message']          = "Invalid question or options.";
                                }
                            }else{
                                $dataArr[0]['flag']                 = $this->flagError;
                                $dataArr[0]['message']              = "User is already the group member.";
                            }
                        }else{
                            $dataArr[0]['flag']                     = $this->flagError;
                            $dataArr[0]['message']                  = "Group not exist in the system.";
                        }
                    }else{
                        $dataArr[0]['flag']                         = $this->flagError;
                        $dataArr[0]['message']                      = "Invalid Access Token.";
                    }
                }else{
                    $dataArr[0]['flag']                             = $this->flagError;
                    $dataArr[0]['message']                          = "Request Not Authorised.";
                }
            }else{
                $dataArr[0]['flag']                                 = $this->flagError;
				$dataArr[0]['message']                              = "Request Not Authorised.";
            }
        }else{
            $dataArr[0]['flag']                                     = $this->flagError;
			$dataArr[0]['message']                                  = "Request Not Authorised.";
        }

        echo json_encode($dataArr);
        exit;
     }
    public function fncSaveQuestionAnswer($intGroupId, $intUserId, $jsonQuestionAnswer){
        if($jsonQuestionAnswer != ''){
             $arrQuestionList                 = json_decode($jsonQuestionAnswer, TRUE);

             if(!empty($arrQuestionList)){
                 foreach($arrQuestionList as $question){
                     $arrQuestionDetails            = $this->getGroupJoiningQuestionnaireTable()->getQuestionFromQuestionId($question['questionnaire_id']);
                     if(!empty($arrQuestionDetails)){
                         $data                          = array();
                        $arrGroupQuestion = $this->getGroupJoiningQuestionnaireTable()->getQuestionFromQuestionIdAndGroupId($question['questionnaire_id'], $intGroupId);
                        if(!empty($arrGroupQuestion)){
                            if($question['answer_type'] == 'radio'|| $question['answer_type'] == 'checkbox'){
                               $data['group_id']           = $intGroupId;
                               $data['question_id']        = $question['questionnaire_id'];
                               $data['selected_options']   = $question['selected_options'];
                               $data['added_user_id']      = $intUserId;
                           }else{
                               $data['group_id']           = $intGroupId;
                               $data['question_id']        = $question['questionnaire_id'];
                               $data['answer']             = $question['answer'];
                               $data['added_user_id']      = $intUserId;
                           }
                           // save question with answers
                           $this->getGroupQuestionnaireAnswersTable()->AddAnswer($data);
                        }
                     }

                 }// foreach
             }
        }
    }
    // function to validate question and answer
    public function fncValidateQuestionAnswer($intGroupId, $jsonQuestionAnswer){
        $questionError  = 0;
        // check question/ answer
        if($jsonQuestionAnswer != ''){
             $arrQuestionList                = json_decode($jsonQuestionAnswer, TRUE);
             if(!empty($arrQuestionList)){
                  foreach($arrQuestionList as $question){
                     // check question existence
                     $arrQuestionDetails     = $this->getGroupJoiningQuestionnaireTable()->getQuestionFromQuestionId($question['questionnaire_id']);
                     if(!empty($arrQuestionDetails)){
                         // check group's question
                         $arrGroupQuestion   = $this->getGroupJoiningQuestionnaireTable()->getQuestionFromQuestionIdAndGroupId($question['questionnaire_id'], $intGroupId);
                         if(!empty($arrGroupQuestion)){
                            if($question['answer_type'] == 'radio'|| $question['answer_type'] == 'checkbox'){
                                if(trim($question['selected_options']) != ''){
                                    // check options
                                    $sptOption   = explode(',',$question['selected_options']);
                                    for($a=0; $a<count($sptOption); $a++ ){
                                       $arrOption   = $this->getGroupQuestionnaireOptionsTable()->getSelectedOptionDetails($sptOption[0]);
                                       if($arrOption[0]['question_id'] != $question['questionnaire_id']){
                                           $questionError   = 1;
                                       }
                                    }// for
                                }else{
                                  $questionError   = 1;
                                }
                             }
                         }else{
                             $questionError      = 1;
                         }
                     }else{
                             $questionError      = 1;
                     }
                  }// foreach
             }
         }

         return $questionError;
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
	public function  get_youtube_id_from_url($url){
		if (stristr($url,'youtu.be/'))
			{preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $final_ID); return $final_ID[4]; }
		else 
			{@preg_match('/(https:|http:|):(\/\/www\.|\/\/|)(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $IDD); return $IDD[5]; }
	}
	public function getUserTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;    
	}
	public function getGroupsTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;    
	}
	public function getUserGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->userGroupTable = (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;    
	}
	public function getGroupTagTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTagTable = (!$this->groupTagTable)?$sm->get('Tag\Model\GroupTagTable'):$this->groupTagTable;    
    }
	public function getRecoveremailsTable(){
		$sm = $this->getServiceLocator();
		return $this->RecoveryemailsTable =(!$this->RecoveryemailsTable)?$sm->get('User\Model\RecoveryemailsTable'):$this->RecoveryemailsTable;
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
	public function getUserTagTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTagTable = (!$this->userTagTable)?$sm->get('Tag\Model\UserTagTable'):$this->userTagTable;    
	}
	public function getUserFriendTable(){
		$sm = $this->getServiceLocator();
		return  $this->userFriendTable = (!$this->userFriendTable)?$sm->get('User\Model\UserFriendTable'):$this->userFriendTable;    
	}	 
    public function getGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;
    } 
    public function getGroupJoiningQuestionnaireTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupJoiningQuestionnaire = (!$this->groupJoiningQuestionnaire)?$sm->get('Groups\Model\GroupJoiningQuestionnaireTable'):$this->groupJoiningQuestionnaire;
    } 
    public function getGroupQuestionnaireOptionsTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupQuestionnaireOptions = (!$this->groupQuestionnaireOptions)?$sm->get('Groups\Model\GroupQuestionnaireOptionsTable'):$this->groupQuestionnaireOptions;
    } 
    public function getGroupJoiningInvitationTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupJoiningInvitationTable = (!$this->groupJoiningInvitationTable)?$sm->get('Groups\Model\UserGroupJoiningInvitationTable'):$this->groupJoiningInvitationTable;
    } 	
    public function getUserGroupJoiningRequestTable(){
		$sm = $this->getServiceLocator();
        return  $this->userGroupJoiningRequestTable = (!$this->userGroupJoiningRequestTable)?$sm->get('Groups\Model\UserGroupJoiningRequestTable'):$this->userGroupJoiningRequestTable;
    } 
    public function getGroupQuestionnaireAnswersTable(){
		$sm = $this->getServiceLocator();
        return  $this->groupQuestionnaireAnswersTable = (!$this->groupQuestionnaireAnswersTable)?$sm->get('Groups\Model\GroupQuestionnaireAnswersTable'):$this->groupQuestionnaireAnswersTable;
	}
}
