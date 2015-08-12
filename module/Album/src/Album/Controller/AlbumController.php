<?php
namespace Album\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;	
use Zend\View\Model\JsonModel; 
use Zend\Session\Container;   
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter; 
use Album\Model\GroupAlbum;  
use Album\Model\GroupEventAlbum;  
class AlbumController extends AbstractActionController
{
    protected $groupAlbumTable; 
	protected $groupTable;
	protected $groupEventAlbumTable;
	public function getAllGroupAlbumsAction(){
		$error ='';
		$msg = '';
		$albums = array();
		$request = $this->getRequest();
		$auth = new AuthenticationService();
		$this->layout('layout/layout_register');
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			if ($request->isPost()) {
				$post = $request->getPost();
				$group_id = $post['group_id']; 
				$group  = $this->getGroupTable()->getPlanetinfo($group_id);
				if(!empty($group)){
					$is_admin = 0;
					if($this->getUserGroupTable()->checkOwner($group_id,$identity->user_id)){
						$is_admin = 1;
					}
					$albums = $this->getGroupAlbumTable()->getAllActiveGroupAlbums($group_id,$identity->user_id,$is_admin);
				}else{$error ='We are failed to identify the given group';}		 	
			}else{$error ='Unable to process';}
		}else{$error = "Your session expired, please log in again to continue";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = (empty($error))?$msg:$error;
		$return_array['albums'] = $albums;
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function createAction(){
		$error ='';
		$msg = '';
		$albums = array();
		$request = $this->getRequest();
		$auth = new AuthenticationService();
		$this->layout('layout/layout_register');
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			if ($request->isPost()) {
				$post = $request->getPost();
				$group_id = $post['group_id'];
				$title = $post['title'];
				$description = $post['description'];
				$event_id = $post['event_id'];
				$group  = $this->getGroupTable()->getPlanetinfo($group_id);
				if(!empty($group)){
					if($title!=''){
						$objGroupAlbum = new GroupAlbum();
						$objGroupAlbum->group_id = $group_id;
						$objGroupAlbum->creator_id = $identity->user_id;
						$objGroupAlbum->album_title = $title;
						$objGroupAlbum->album_description = $description;
						$objGroupAlbum->created_ip = $_SERVER["SERVER_ADDR"];
						$objGroupAlbum->album_status = 'active';
						$newalbum_id = $this->getGroupAlbumTable()->saveAlbum($objGroupAlbum);
						if($event_id!=''){
							$objGroupEventAlbum = new GroupEventAlbum();
							$objGroupEventAlbum->event_id = $event_id;
							$objGroupEventAlbum->assignedby = $identity->user_id;
							$objGroupEventAlbum->album_id = $newalbum_id;
							$newalbum_id = $this->getGroupEventAlbumTable()->saveEventAlbum($objGroupEventAlbum);
						}
						$is_admin = 0;
						if($this->getUserGroupTable()->checkOwner($group_id,$identity->user_id)){
							$is_admin = 1;
						}
						$albums = $this->getGroupAlbumTable()->getAllActiveGroupAlbums($group_id,$identity->user_id,$is_admin);
					}else{$error ='Please add album title';}						
				}else{$error ='We are failed to identify the given group';}		 	
			}else{$error ='Unable to process';}
		}else{$error = "Your session expired, please log in again to continue";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = (empty($error))?$msg:$error;
		$return_array['albums'] = $albums;
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function deleteAlbumAction(){
		$error = '';		
		$auth = new AuthenticationService();
		$is_media_deleted = 0;
		$media_id = 0;
		$arr_deletedMedia = array();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();
			if ($request->isPost()){
				$post = $request->getPost(); 
				if(!empty($post)){				 
					$system_type = $post['system_type'];
					$content_id = $post['content_id'];					
					if($content_id!=''){
						$albumdata = $this->getGroupAlbumTable()->getAlbum($content_id);
						if(!empty($albumdata)){
							 
							$is_event_owner = 0;
							$event_album_details = $this->getGroupEventAlbumTable()->getAlbumEvents($content_id);
							if(!empty($event_album_details)){
								 
								if($event_album_details->group_activity_owner_user_id == $identity->user_id){
									$is_event_owner = 1;
								}
							}
							if($albumdata->creator_id == $identity->user_id || $this->getUserGroupTable()->checkOwner($albumdata->group_id,$identity->user_id) || $is_event_owner==1){
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
							}else{$error = "Sorry, You don't have the permission to do this operations";}									 
						}else{$error = "This album is not existing in the system";}
					}else{$error = "Forms are incomplete. Some values are missing";}	 			 
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";} 
		}else{$error = "Your session expired, please log in again to continue";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 		 		
		$return_array['media_id'] = $arr_deletedMedia; 		
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}
	public function editAlbumAction(){
		$error ='';
		$msg = '';
		$albums = array();
		$request = $this->getRequest();
		$auth = new AuthenticationService();
		$this->layout('layout/layout_register');
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			if ($request->isPost()) {
				$post = $request->getPost();
				$album_id = $post['album_id'];
				$title = $post['title'];
				$description = $post['description'];
				$event_id = $post['event_id'];
				$albumdata = $this->getGroupAlbumTable()->getAlbum($album_id);
				if(!empty($albumdata)){
					$is_admin = 0;
					if($this->getUserGroupTable()->checkOwner($albumdata->group_id,$identity->user_id)){
						$is_admin = 1;
					}					
					if($title!=''){
						if($is_admin==1 || $albumdata->creator_id == $identity->user_id){
							$is_allow_edit = 1;						 
							if($event_id!=''){
								$event_details = $this->getActivityTable()->getActivity($event_id);								 
								$rsvp_details = $this->getActivityRsvpTable()->getActivityRsvpOfUser($identity->user_id,$event_id);
								if((!empty($rsvp_details)&&$rsvp_details->group_activity_rsvp_id!='')||(!empty($event_details)&&$event_details->group_activity_owner_user_id==$identity->user_id)){
									$is_allow_edit = 1;			
								}else{
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
									$objGroupEventAlbum->assignedby = $identity->user_id;
									$objGroupEventAlbum->album_id = $album_id;
									$newalbum_id = $this->getGroupEventAlbumTable()->saveEventAlbum($objGroupEventAlbum);
								}
								if($event_id!=''&&!empty($eventAlbumData)&&$eventAlbumData->event_id!=$event_id){
									$objGroupEventAlbum = new GroupEventAlbum();
									$objGroupEventAlbum->event_album_id = $eventAlbumData->event_album_id;
									$objGroupEventAlbum->event_id = $event_id;
									$objGroupEventAlbum->assignedby = $identity->user_id;
									$objGroupEventAlbum->album_id = $album_id;
									$newalbum_id = $this->getGroupEventAlbumTable()->saveEventAlbum($objGroupEventAlbum);
								}
							}								
						}						 
					}else{$error ='Please add album title';}						
				}else{$error ='We are failed to identify the given group';}		 	
			}else{$error ='Unable to process';}
		}else{$error = "Your session expired, please log in again to continue";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = (empty($error))?$msg:$error;
	 
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function getGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;    
    }
	public function getGroupAlbumTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupAlbumTable = (!$this->groupAlbumTable)?$sm->get('Album\Model\GroupAlbumTable'):$this->groupAlbumTable;    
    }
	public function getGroupEventAlbumTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupEventAlbumTable = (!$this->groupEventAlbumTable)?$sm->get('Album\Model\GroupEventAlbumTable'):$this->groupEventAlbumTable;    
	}
	public function getUserGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->userGroupTable = (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;  
	}
	public function getGroupMediaContentTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupMediaContentTable = (!$this->groupMediaContentTable)?$sm->get('Groups\Model\GroupMediaContentTable'):$this->groupMediaContentTable;    
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
	public function getUserNotificationTable(){
        $sm = $this->getServiceLocator();  
		return $this->userNotificationTable= (!$this->userNotificationTable)?$sm->get('Notification\Model\UserNotificationTable'):$this->userNotificationTable;   
    }
	public function getActivityRsvpTable(){
		$sm = $this->getServiceLocator();
		return  $this->activityRsvpTable = (!$this->activityRsvpTable)?$sm->get('Activity\Model\ActivityRsvpTable'):$this->activityRsvpTable;
    }
	public function getActivityTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupActivityTable = (!$this->groupActivityTable)?$sm->get('Activity\Model\ActivityTable'):$this->groupActivityTable;    
    }
}