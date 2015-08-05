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
					$albums = $this->getGroupAlbumTable()->getAllActiveGroupAlbums($group_id);					
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
						$albums = $this->getGroupAlbumTable()->getAllActiveGroupAlbums($group_id);
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
}