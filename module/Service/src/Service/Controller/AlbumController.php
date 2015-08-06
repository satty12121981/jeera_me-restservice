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
				$albums = $this->getGroupAlbumTable()->getAllActiveGroupAlbumsForAPI($group_id,$limit,$offset);
				if (count($albums)){
					foreach($albums as $album){
						$media_contents = $this->getGroupMediaContentTable()->getMediaContents(json_decode($album->media_content));
						$media_files = [];
						$getAlbumCreatedUser = $this->getUserTable()->getUser($album->creator_id);
						$creator_profile_pic =  $this->getUserTable()->getUserProfilePic($getAlbumCreatedUser->user_id);
						$profile_details_photo = $this->manipulateProfilePic($getAlbumCreatedUser->user_id,$creator_profile_pic->biopic, $getAlbumCreatedUser->user_fbid);
						$userdetails = array(
							"userid"=>$album->creator_id,
							"profilepicture"=>$profile_details_photo,
						);
						$albumurl = $config['pathInfo']['base_url']."/public/images/album-thumb.png";
						if (isset($media_contents) && !empty($media_contents)){
							if($media_contents[0]['media_type'] == 'youtube'){
								$video_id = $this->get_youtube_id_from_url($media_contents[0]['content']);
								$albumurl =	'http://img.youtube.com/vi/'.$video_id.'/0.jpg';
							}else{
								$albumurl = $config['pathInfo']['absolute_img_path'].$config['image_folders']['group'].$group_id.'/media/medium/'.$media_contents[0]['content'];
							}
						}

						$media_details[] = array(
							"album_id"=> $album->album_id,
							"album_title"=> $album->album_title,
							"usercreated"=> $userdetails,
							'datecreated'=> $album->created_date,
							'albumtype' => ($album->event_album_id)?"Event Album":"group Album",
							"mediacount"=> (!empty($media_contents)) ? count($media_contents) : 0,
							"albumpictureurl"=> $albumurl,
						);
					}

				}
			}else{$error ='We are failed to identify the given group';}
		}
		$dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
		$dataArr[0]['message'] = $error;
		$dataArr[0]['albums'] = $media_details;
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
								$media_content = array_merge($media_content, $unsortedmedia_ids);
							}
							$logged_user_ismember = 0;
							if ($this->getUserGroupTable()->is_member($userinfo->user_id, $group_id)) {
								$logged_user_ismember = 1;
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
											'files' => $mediaurl,
											'video_id' => $this->get_youtube_id_from_url($files['content']),
											'media_type' => $files['media_type'],
										);
									} else {
										$mediaurl = $config['pathInfo']['absolute_img_path'].$config['image_folders']['group'].$group_id.'/media/medium/'.$files['content'];
										$arr_media_files[] = array(
											'id' => $files['media_content_id'],
											'files' => $mediaurl,
											'media_type' => $files['media_type'],
										);
									}
								}

								$arr_group_media = array(
									'album_id' => 'unsorted',
									'album_title' => 'Post Images/Unsorted',
									'media_files' => $arr_media_files,
									'logged_user_ismember' => $logged_user_ismember,
									'group_title' => $group_info->group_title,
									'group_seo_title' => $group_info->group_seo_title,
									'group_id' => $group_info->group_id,
								);

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
												'files' => $mediaurl,
												'video_id' => $this->get_youtube_id_from_url($files['content']),
												'media_type' => $files['media_type'],
											);
										} else {
											$mediaurl = $config['pathInfo']['absolute_img_path'].$config['image_folders']['group'].$group_id.'/media/medium/'.$files['content'];
											$arr_media_files[] = array(
												'id' => $files['media_content_id'],
												'files' => $mediaurl,
												'media_type' => $files['media_type'],
											);
										}
									}
								}
							}
						} else {
							$error = "Album not exist";
						}
					}
				} else {
					$error = "Unable to process";
				}
			}else{$error ='We are failed to identify the given group';}
		}else{$error = "Unable to process";}

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
							$dataArr[0]['message'] = "The logged in user is not a group owner or event creator to create event album or event does not exists for the group";
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
}