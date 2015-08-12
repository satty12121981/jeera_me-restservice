<?php
namespace Service\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Album\Model\GroupAlbum;
use Album\Model\GroupEventAlbum;
use \Exception;
class AlbumController extends AbstractActionController
{
    protected $userTable;
	protected $groupAlbumTable;
	protected $groupTable;
	protected $groupEventAlbumTable;
	protected $activityTable;
	protected $groupMediaTable;
	protected $groupMediaContentTable;
	protected $userGroupTable;
	protected $likeTable;
	protected $commentTable;
	protected $userNotificationTable;
	public function __construct(){
		$this->flagSuccess = "Success";
		$this->flagFailure = "Failure";
	}
	public function getGroupAlbumsAction(){
		$error ='';
		$msg = '';
		$albums = array();
		$error = '';
		$media_details = array();
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
			$error =($post['groupid']==null || $post['groupid']=='' || $post['groupid']=='undefined' || !is_numeric($post['groupid']))? "please input a valid group id":$error;
			$this->checkError($error);
			$group_id = $post['groupid'];
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
			$group  = $this->getGroupTable()->getPlanetinfo($group_id);
			if(!empty($group)){
				if ($group->group_type == "private"){
					$userPermissionOnGroup = $this->getUserGroupTable()->getGroupUserDetails($group_id,$userinfo->user_id);
					$error =(empty($userPermissionOnGroup))?"User has no permission or not a member of the group to fech albums of group.":$error;
					if (!empty($error)){
						$dataArr[0]['flag'] = $this->flagFailure;
						$dataArr[0]['message'] = $error;
						echo json_encode($dataArr);
						exit;
					}
				}
				$media_content = array();
				$unsorted_media = $this->getGroupMediaTable()->getAllUnsortedMedia($group_id);
				if(!empty($unsorted_media)) {
					foreach ($unsorted_media as $unsorted) {
						$unsortedmedia_ids = json_decode($unsorted['media_content']);
						$media_content = (is_array($unsortedmedia_ids)) ? array_merge($media_content, $unsortedmedia_ids):$media_content;
					}
					$album_icon = (isset($media_content[0]) && !empty($media_content[0])) ?$this->getGroupMediaContentTable()->getMediafile($media_content[0]):"";
					if (!empty($album_icon)) {
						if ($album_icon->media_type == 'image') {
							$album_icon_url = $config['pathInfo']['group_img_path_absolute_path'] . $group_id . '/media/medium/' . $album_icon->content;
						}
						if ($album_icon->media_type == 'youtube') {
							$video_id = $this->getYoutubeIdFromUrl($album_icon->content);
							$album_icon_url = 'http://img.youtube.com/vi/' . $video_id . '/0.jpg';
						}
					} else {
						$album_icon_url = $config['pathInfo']['base_url'] . "/public/images/album-thumb.png";
					}
					$album_details[] = array(
						'album_id' => 'unsorted',
						'album_title' => 'Post Images/Unsorted',
						'album_icon_url' => $album_icon_url,
						'album_image_count' => count($media_content),
						'album_type' => "Unsorted",
						"album_user_details" => "",
						'album_created_date' => "",
					);
				}
				$albums = $this->getGroupAlbumTable()->getAllActiveGroupAlbumsForAPI($group_id,$limit,$offset);
				if (count($albums)){
					$media_content = array();
					foreach($albums as $album){
						$media_details = $this->getGroupMediaTable()->getAllAlbumMedia($album->album_id,$offset,$limit);
						if(!empty($media_details)) {
							foreach ($media_details as $details) {
								$media_ids = json_decode($details['media_content']);
								$media_contents = (is_array($media_ids))?array_merge($media_content, $media_ids):$media_content;
							}
						}
						$getAlbumCreatedUser = $this->getUserTable()->getUser($album->creator_id);
						$creator_profile_pic =  $this->getUserTable()->getUserProfilePic($getAlbumCreatedUser->user_id);
						$profile_details_photo = $this->manipulateProfilePic($getAlbumCreatedUser->user_id,$creator_profile_pic->biopic, $getAlbumCreatedUser->user_fbid);
						$userdetails = array(
							"userid"=>$album->creator_id,
							"profilepicture"=>$profile_details_photo,
						);
						$albumurl = $config['pathInfo']['base_url']."/public/images/album-thumb.png";
						$albumImage_count = count($media_content);
						$album_icon = $this->getGroupMediaContentTable()->getAlbumIcon($album->album_id);
						if(!empty($album_icon)){
							if($album_icon->media_type=='image'){
								$album_icon_url=$config['pathInfo']['group_img_path_absolute_path'].$group_id.'/media/medium/'.$album_icon->content;
							}
							if($album_icon->media_type=='youtube'){
								$video_id = $this->getYoutubeIdFromUrl($album_icon->content);
								$album_icon_url='http://img.youtube.com/vi/'.$video_id.'/0.jpg';
							}
						}else{
							$album_icon_url=$config['pathInfo']['base_url']."/public/images/album-thumb.png";
						}

						$album_details[] = array(
							"album_id"=> $album->album_id,
							"album_title"=> $album->album_title,
							"album_user_details"=> $userdetails,
							'album_created_date'=> $album->created_date,
							'album_type' => ($album->event_album_id)?"Event Album":"group Album",
							"album_image_count"=> $albumImage_count,
							"album_icon_url"=> $album_icon_url,
						);
					}
				}

			}else{$error ='We are failed to identify the given group';}
		}
		$dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
		$dataArr[0]['message'] = $error;
		$dataArr[0]['albums'] = (!empty($album_details))?$album_details:"";
		echo json_encode($dataArr);
		exit;
	}
	public function getAlbumMediaAction(){
		$error = '';
		$arr_media_files = array();
		$allActiveMembers = array();
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
			$error =($post['groupid']==null || $post['groupid']=='' || $post['groupid']=='undefined' || !is_numeric($post['groupid']))? "please input a valid group id":$error;
			$this->checkError($error);
			$group_id = $post['groupid'];
			$error =($post['albumid']==null || $post['albumid']=='' || $post['albumid']=='undefined')? "please input a valid album id":$error;
			$this->checkError($error);
			$album_id = $post['albumid'];
			$arr_group_list = '';
			$offset = (isset($post['nparam']))?trim($post['nparam']):'';
			$error =($post['countparam']==null || $post['countparam']=='' || $post['countparam']=='undefined')? "please input a valid count param":$error;
			$this->checkError($error);
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
			$group  = $this->getGroupTable()->getPlanetinfo($group_id);
			if(!empty($group)) {
				if ($album_id) {
					if ($group->group_type == "private"){
						$userPermissionOnGroup = $this->getUserGroupTable()->getGroupUserDetails($group_id,$userinfo->user_id);
						$error =(empty($userPermissionOnGroup))?"User has no permission or not a member of the group to view media.":$error;
						if (!empty($error)){
							$dataArr[0]['flag'] = $this->flagFailure;
							$dataArr[0]['message'] = $error;
							echo json_encode($dataArr);
							exit;
						}
					}
					if ($album_id == 'unsorted') {
						$group_info = $this->getGroupTable()->getPlanetinfo($group_id);
						$unsorted_media = $this->getGroupMediaTable()->getAllUnsortedMedia($group_id);
						if (!empty($unsorted_media)) {
							$media_content = array();
							foreach ($unsorted_media as $unsorted) {
								$unsortedmedia_ids = json_decode($unsorted['media_content']);
								$media_content = (is_array($unsortedmedia_ids))?array_merge($media_content, $unsortedmedia_ids):$media_content;
							}
							if (!empty($media_content)) {
								$media_files = $this->getGroupMediaContentTable()->getMediaContents($media_content);
								$arr_media_files = array();
								foreach ($media_files as $files) {
									if ($files['media_type'] == 'youtube') {
										$video_id = $this->get_youtube_id_from_url($files['content']);
										$mediaurl =	'http://img.youtube.com/vi/'.$video_id.'/0.jpg';
										$arr_media_files[] = array(
											'id' => $files['media_content_id'],
											'media_files' => $mediaurl,
											'video_id' => $this->get_youtube_id_from_url($files['content']),
											'media_type' => $files['media_type'],
										);
									} else {
										$mediaurl = $config['pathInfo']['absolute_img_path'].$config['image_folders']['group'].$group_id.'/media/medium/'.$files['content'];
										$arr_media_files[] = array(
											'id' => $files['media_content_id'],
											'media_files' => $mediaurl,
											'media_type' => $files['media_type'],
										);
									}
								}
							}
						}
					} else {
						$album_details = $this->getGroupAlbumTable()->getAlbum($album_id);
						if (!empty($album_details)) {
							$media_content = [];
							$media_details = $this->getGroupMediaTable()->getAllAlbumMedia($album_id, $offset, $limit);
							$group_info = $this->getGroupTable()->getPlanetinfo($group_id);
							if (!empty($media_details)) {
								foreach ($media_details as $details) {
									$media_ids = json_decode($details['media_content']);
									$media_content = array_merge($media_content, $media_ids);
								}
								if (!empty($media_content)) {
									$media_files = $this->getGroupMediaContentTable()->getMediaContents($media_content);
									$arr_media_files = array();
									foreach ($media_files as $files) {
										if ($files['media_type'] == 'youtube') {
											$video_id = $this->get_youtube_id_from_url($files['content']);
											$mediaurl =	'http://img.youtube.com/vi/'.$video_id.'/0.jpg';
											$arr_media_files[] = array(
												'id' => $files['media_content_id'],
												'media_files' => $mediaurl,
												'video_id' => $this->get_youtube_id_from_url($files['content']),
												'media_type' => $files['media_type'],
											);
										} else {
											$mediaurl = $config['pathInfo']['absolute_img_path'].$config['image_folders']['group'].$group_id.'/media/medium/'.$files['content'];
											$arr_media_files[] = array(
												'id' => $files['media_content_id'],
												'media_files' => $mediaurl,
												'media_type' => $files['media_type'],
											);
										}
									}
								}
							}
						} else {
							$error = "Album not exist for the group";
						}
					}
				} else {
					$error = "Unable to process the request";
				}
			}else{$error ='We are failed to identify the given group';}
		}else{$error = "Unable to process the request";}

		$dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
		$dataArr[0]['message'] = $error;
		$dataArr[0]['albummedia'] = $arr_media_files;
		echo json_encode($dataArr);
		exit;
	}
	public function createAlbumAction(){
		$error ='';
		$msg = '';
		$albums = array();
		$request = $this->getRequest();
		if ($request->isPost()) {
			$post = $request->getPost();
			$accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
			$error = (empty($accToken)) ? "Request Not Authorised." : $error;
			$this->checkError($error);
			$userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
			$error = (empty($userinfo)) ? "Invalid Access Token." : $error;
			$this->checkError($error);
			$error =($post['groupid']==null || $post['groupid']=='' || $post['groupid']=='undefined' || !is_numeric($post['groupid']))? "please input a valid group id":$error;
			$this->checkError($error);
			$group_id = $post['groupid'];
			$title = $post['title'];
			$description = $post['description'];
			$event_id = $post['eventid'];
			if ($event_id && !is_numeric($event_id)){
				$error = "Please input a valid eventid";
				$this->checkError($error);
			}
			$group  = $this->getGroupTable()->getPlanetinfo($group_id);
			if(!empty($group)){
				if($title!=''){
					$is_admin = 0;
					$userPermissionOnGroup = $this->getUserGroupTable()->getGroupUserDetails($group_id,$userinfo->user_id);
					$error =(empty($userPermissionOnGroup))?"User has no permission or not a member of the group to create album.":$error;
					if (!empty($error)){
						$dataArr[0]['flag'] = $this->flagFailure;
						$dataArr[0]['message'] = $error;
						echo json_encode($dataArr);
						exit;
					}
					if($event_id!=''){
						if ($this->getUserGroupTable()->checkOwner($group_id, $userinfo->user_id)) {
							$is_admin = 1;
						}
						$eventDetails = $this->getActivityTable()->getEventDetailsForGroupOrActivityOwner($event_id, $userinfo->user_id, $group_id, $is_admin);
						if (empty($eventDetails)){
							$dataArr[0]['flag'] = $this->flagFailure;
							$dataArr[0]['message'] = "The logged in user is not a group owner (or) event creator to create event album (or) event does not exists for the group";
							echo json_encode($dataArr);
							exit;
						}
					}
					$objGroupAlbum = new GroupAlbum();
					$objGroupAlbum->group_id = $group_id;
					$objGroupAlbum->creator_id = $userinfo->user_id;
					$objGroupAlbum->album_title = $title;
					$objGroupAlbum->album_description = (!empty($description))?$description:"";
					$objGroupAlbum->created_ip = $_SERVER["SERVER_ADDR"];
					$objGroupAlbum->album_status = 'active';
					$newalbum_id = $this->getGroupAlbumTable()->saveAlbum($objGroupAlbum);
					$dataArr[0]['flag'] = $this->flagSuccess;
					$message = "Group album created successfully";
					if($event_id!='' && !empty($eventDetails)){
						$objGroupEventAlbum = new GroupEventAlbum();
						$objGroupEventAlbum->event_id = $event_id;
						$objGroupEventAlbum->assignedby = $userinfo->user_id;
						$objGroupEventAlbum->album_id = $newalbum_id;
						$newalbum_id = $this->getGroupEventAlbumTable()->saveEventAlbum($objGroupEventAlbum);
						$dataArr[0]['flag'] = $this->flagSuccess;
						$message = "Group event album created successfully";
					}
				}else{$dataArr[0]['flag'] = $this->flagFailure; $error ='Please add album title';}
			}else{$dataArr[0]['flag'] = $this->flagFailure; $error ='We are failed to identify the given group';}
		}else{$dataArr[0]['flag'] = $this->flagFailure; $error ='Unable to process';}

		$dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
		$dataArr[0]['message'] = (empty($error))?$message:$error;
		echo json_encode($dataArr);
		exit;
	}
	public function editAlbumAction(){
		$error ='';
		$msg = '';
		$albums = array();
		$request = $this->getRequest();
		if ($request->isPost()) {
			$post = $request->getPost();
			$accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
			$error = (empty($accToken)) ? "Request Not Authorised." : $error;
			$this->checkError($error);
			$userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
			$error = (empty($userinfo)) ? "Invalid Access Token." : $error;
			$this->checkError($error);
			$error =($post['albumid']==null || $post['albumid']=='' || $post['albumid']=='undefined' || !is_numeric($post['albumid']))? "please input a valid album id":$error;
			$this->checkError($error);
			$album_id = $post['albumid'];
			$title = $post['title'];
			$description = $post['description'];
			$event_id = $post['eventid'];
			if ($event_id && !is_numeric($event_id)){
				$error = "Please input a valid eventid";
				$this->checkError($error);
			}
			$albumdata = $this->getGroupAlbumTable()->getAlbum($album_id);
			if(!empty($albumdata)){
				$is_admin = 0;
				if($this->getUserGroupTable()->checkOwner($albumdata->group_id,$userinfo->user_id)){
					$is_admin = 1;
				}
				if($title!=''){
					if($is_admin==1 || $albumdata->creator_id == $userinfo->user_id){
						$is_allow_edit = 1;
						if($event_id!=''){
							$event_details = $this->getActivityTable()->getActivity($event_id);
							$rsvp_details = $this->getActivityRsvpTable()->getActivityRsvpOfUser($userinfo->user_id,$event_id);
							if((!empty($rsvp_details)&&$rsvp_details->group_activity_rsvp_id!='')||(!empty($event_details)&&$event_details->group_activity_owner_user_id==$userinfo->user_id)){
								$is_allow_edit = 1;
							}else{
								$dataArr[0]['flag'] = $this->flagFailure;
								$error ='You don\'t have the permission to add this event to album';
								$is_allow_edit =0;
							}
						}
						if($is_allow_edit==1){
							$album_data = array(
								'album_title' =>$title,
								'album_description' =>$description,
							);
							$this->getGroupAlbumTable()->updateAlbum($album_data,$album_id);
							$eventAlbumData = $this->getGroupEventAlbumTable()->getAlbumEvents($album_id);
							if($event_id==''&&!empty($eventAlbumData)){
								$this->getGroupEventAlbumTable()->deleteEventAlbum($album_id);
							}
							if($event_id!=''&&empty($eventAlbumData)){
								$objGroupEventAlbum = new GroupEventAlbum();
								$objGroupEventAlbum->event_id = $event_id;
								$objGroupEventAlbum->assignedby = $userinfo->user_id;
								$objGroupEventAlbum->album_id = $album_id;
								$newalbum_id = $this->getGroupEventAlbumTable()->saveEventAlbum($objGroupEventAlbum);
							}
							if($event_id!=''&&!empty($eventAlbumData)&&$eventAlbumData->event_id!=$event_id){
								$objGroupEventAlbum = new GroupEventAlbum();
								$objGroupEventAlbum->event_album_id = $eventAlbumData->event_album_id;
								$objGroupEventAlbum->event_id = $event_id;
								$objGroupEventAlbum->assignedby = $userinfo->user_id;
								$objGroupEventAlbum->album_id = $album_id;
								$newalbum_id = $this->getGroupEventAlbumTable()->saveEventAlbum($objGroupEventAlbum);
							}
							$dataArr[0]['flag'] = $this->flagSuccess; $error ='Album Edited Successfully';
						}else{
							$dataArr[0]['flag'] = $this->flagFailure; $error ='You don\'t have the permission to edit this event to album';
						}
					}else{
						$dataArr[0]['flag'] = $this->flagFailure; $error ='You don\'t have the permission to edit this event to album';
					}
				}else{ $dataArr[0]['flag'] = $this->flagFailure; $error ='Please add album title';}
			}else{ $dataArr[0]['flag'] = $this->flagFailure; $error ='We are failed to identify the given group';}
		}else{ $dataArr[0]['flag'] = $this->flagFailure; $error ='Unable to process';}

		$dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
		$dataArr[0]['message'] = $error;
		echo json_encode($dataArr);
		exit;
	}
	public function deleteAlbumAction(){
		$error = '';
		$request = $this->getRequest();
		$is_media_deleted = 0;
		$media_id = 0;
		$arr_deletedMedia = array();
		if ($request->isPost()) {
			$post = $request->getPost();
			$accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
			$error = (empty($accToken)) ? "Request Not Authorised." : $error;
			$this->checkError($error);
			$userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
			$error = (empty($userinfo)) ? "Invalid Access Token." : $error;
			$this->checkError($error);
			$error =($post['albumid']==null || $post['albumid']=='' || $post['albumid']=='undefined' || !is_numeric($post['albumid']))? "please input a valid album id":$error;
			$this->checkError($error);
			$content_id = $post['albumid'];
			if(!empty($post)){
				if($content_id!=''){
					$albumdata = $this->getGroupAlbumTable()->getAlbum($content_id);
					if(!empty($albumdata)){
						$is_event_owner = 0;
						$event_album_details = $this->getGroupEventAlbumTable()->getAlbumEvents($content_id);
						if(!empty($event_album_details)){
							if($event_album_details->group_activity_owner_user_id == $userinfo->user_id){
								$is_event_owner = 1;
							}
						}
						if($albumdata->creator_id == $userinfo->user_id || $this->getUserGroupTable()->checkOwner($albumdata->group_id,$userinfo->user_id) || $is_event_owner==1){
							$Mediadata =$this->getGroupMediaTable()->getAllAlbumFiles($content_id);
							if(!empty($Mediadata)){
								foreach($Mediadata as $mediafiles){
									$arr_media_contents = json_decode($mediafiles['media_content']);
									foreach($arr_media_contents as $items){
										$media_content = $this->getGroupMediaContentTable()->getMediafile($items);
										$MediaSystemTypeData = $this->getGroupTable()->fetchSystemType('Image');
										$this->getLikeTable()->deleteEventLike($MediaSystemTypeData->system_type_id,$items);
										$this->getLikeTable()->deleteEventCommentLike($MediaSystemTypeData->system_type_id,$items);
										$this->getCommentTable()->deleteEventComments($MediaSystemTypeData->system_type_id,$items);
										if($media_content->media_type=='image'){
											$config = $this->getServiceLocator()->get('Config');
											$group_image_path = $config['pathInfo']['group_img_path'];
											@unlink($group_image_path.$Mediadata->media_added_group_id."/media/".$media_content->content);
											@unlink($group_image_path.$Mediadata->media_added_group_id."/media/thumbnail/".$media_content->content);
											@unlink($group_image_path.$Mediadata->media_added_group_id."/media/medium/".$media_content->content);
										}
										$media_content_id = $media_content->media_content_id;
										$this->getGroupMediaContentTable()->deleteContent($media_content->media_content_id);
									}
									$SystemTypeData = $this->getGroupTable()->fetchSystemType('Media');
									$this->getLikeTable()->deleteEventCommentLike($SystemTypeData->system_type_id,$mediafiles['group_media_id']);
									$this->getLikeTable()->deleteEventLike($SystemTypeData->system_type_id,$mediafiles['group_media_id']);
									$this->getCommentTable()->deleteEventComments($SystemTypeData->system_type_id,$mediafiles['group_media_id']);
									$this->getGroupMediaTable()->deleteMedia($mediafiles['group_media_id']);
									$this->getUserNotificationTable()->deleteSystemNotifications(8,$mediafiles['group_media_id']);
									$arr_deletedMedia[] = $mediafiles['group_media_id'];
								}
							}
							$SystemTypeData = $this->getGroupTable()->fetchSystemType('Album');
							$this->getLikeTable()->deleteEventCommentLike($SystemTypeData->system_type_id,$content_id);
							$this->getLikeTable()->deleteEventLike($SystemTypeData->system_type_id,$content_id);
							$this->getCommentTable()->deleteEventComments($SystemTypeData->system_type_id,$content_id);
							$this->getGroupEventAlbumTable()->deleteEventAlbum($content_id);
							$this->getGroupAlbumTable()->deleteAlbum($content_id);
							$this->getUserNotificationTable()->deleteSystemNotifications(8,$content_id);
							$dataArr[0]['flag'] = $this->flagSuccess; $error = "Album deleted successfully";
						}else{$dataArr[0]['flag'] = $this->flagFailure; $error = "Sorry, You don't have the permission to do this operations";}
					}else{$dataArr[0]['flag'] = $this->flagFailure; $error = "This album is not existing in the system";}
				}else{$dataArr[0]['flag'] = $this->flagFailure; $error = "Forms are incomplete. Some values are missing";}
			}else{$dataArr[0]['flag'] = $this->flagFailure; $error = "Unable to process";}
		}else{$dataArr[0]['flag'] = $this->flagFailure; $error = "Unable to process";}

		$dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
		$dataArr[0]['message'] = $error;
		echo json_encode($dataArr);
		exit;
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
	public function getUserTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;
	}
	public function getUserGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->userGroupTable = (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;
	}
	public function getGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;
    }
	public function getActivityTable(){
		$sm = $this->getServiceLocator();
		return  $this->activityTable = (!$this->activityTable)?$sm->get('Activity\Model\ActivityTable'):$this->activityTable;
	}
	public function getGroupAlbumTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupAlbumTable = (!$this->groupAlbumTable)?$sm->get('Album\Model\GroupAlbumTable'):$this->groupAlbumTable;
    }
	public function getGroupEventAlbumTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupEventAlbumTable = (!$this->groupEventAlbumTable)?$sm->get('Album\Model\GroupEventAlbumTable'):$this->groupEventAlbumTable;
    }
	public function getGroupMediaTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupMediaTable = (!$this->groupMediaTable)?$sm->get('Groups\Model\GroupMediaTable'):$this->groupMediaTable;
	}
	public function getGroupMediaContentTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupMediaContentTable = (!$this->groupMediaContentTable)?$sm->get('Groups\Model\GroupMediaContentTable'):$this->groupMediaContentTable;
	}
	public function getLikeTable(){
		$sm = $this->getServiceLocator();
		return  $this->likeTable = (!$this->likeTable)?$sm->get('Like\Model\LikeTable'):$this->likeTable;
	}
	public function getCommentTable(){
		$sm = $this->getServiceLocator();
		return  $this->commentTable = (!$this->commentTable)?$sm->get('Comment\Model\CommentTable'):$this->commentTable;
	}
	public function getUserNotificationTable(){
		$sm = $this->getServiceLocator();
		return $this->userNotificationTable= (!$this->userNotificationTable)?$sm->get('Notification\Model\UserNotificationTable'):$this->userNotificationTable;
	}
}