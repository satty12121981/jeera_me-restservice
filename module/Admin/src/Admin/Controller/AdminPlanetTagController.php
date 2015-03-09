<?php 
namespace Admin\Controller; 
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;	//Return model 
use Zend\Session\Container; // We need this when using sessions     
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Authentication\Storage\Session as SessionStorage;
use Zend\Db\Sql\Select; 
use Tag\Model\Tag;
use Tag\Model\GroupTag;
use Group\Model\Group;
use Admin\Form\AdminPlanetTagForm;
use Admin\Form\AdminPlanetTagFilter;   
use Admin\Form\AdminPlanetTagEditFilter;
 
class AdminPlanetTagController extends AbstractActionController
{   
	  
	protected $tagTable; 
	protected $groupTagTable; 
	protected $groupTable; 
    public function indexAction()
    {		 
		$error =array();
		$success =array();	
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage('admin'));
		$identity = null;
		$this->layout('layout/admin_page');	
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();	
			$this->layout()->identity = $identity;	
			$page = ($this->getEvent()->getRouteMatch()->getParam('page')>0)?$this->getEvent()->getRouteMatch()->getParam('page'):1;
			$offset = ($page)?($page-1)*20:0;
			$sort = ($this->getEvent()->getRouteMatch()->getParam('sort'))?$this->getEvent()->getRouteMatch()->getParam('sort'):'id';
			$order = ($this->getEvent()->getRouteMatch()->getParam('order')=='desc')?'DESC':'ASC';
			$field = '';
			switch($sort){
				case 'group':
					$field = 'group_title';
				break;
				case 'tags':
					$field = 'tags';
				break;
				default:
					$field = 'group_title';
			}
			$search = '';
			$request = $this->getRequest();	
			$search = ($request->isPost()&&$this->getRequest()->getPost('tag_search',''))?$this->getRequest()->getPost('tag_search',''):$this->getEvent()->getRouteMatch()->getParam('search');			 
			$total_tags = $this->getGroupTagTable()->getCountOfAllGroupTags($search); 
			$total_pages = ceil($total_tags/20);
			$allGroupTagData = array();	
			$allGroupTagData = $this->getGroupTagTable()->getAllGroupTags(20,$offset,$field,$order,$search);			 
			return array('allGroupTagData' => $allGroupTagData,'total_pages'=>$total_pages,'page'=> $page,'search'=>$search, 'field'=>$sort,'order'=>$order,  'error' => $error, 'success' => $success, 'flashMessages' => $this->flashMessenger()->getMessages());	 
		}else{
			return $this->redirect()->toRoute('jadmin/login', array('action' => 'login'));
		}
    }
	public function viewAction(){
		$error =array();
		$success =array();
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage('admin'));
		$identity = null;
		$this->layout('layout/admin_page');	
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();	
			$this->layout()->identity = $identity;
			$group_id = $this->getEvent()->getRouteMatch()->getParam('id');
			if($group_id!=''){
				$groupData = $this->getGroupTable()->getPlanetinfo($group_id);
				if(!empty($groupData)&&$groupData->group_id!=''){
					$grouptags = $this->getGroupTagTable()->fetchAllTagsOfPlanet($group_id);
					return array('grouptags' => $grouptags,'groupData'=>$groupData, 'error' => $error, 'success' => $success, 'flashMessages' => $this->flashMessenger()->getMessages());
				}else{
					$error[] = 'Given group is not existing in this system';
				}
			}else{
				return $this->redirect()->toRoute('jadmin/admin-planet-tags', array('action' => 'index'));
			}				
		}else{
			return $this->redirect()->toRoute('jadmin/login', array('action' => 'login'));
		}
	}
	public function deleteAction(){
		$error ='';
		$error_count =0;
		$return_array = array();
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage('admin'));
		if ($auth->hasIdentity()) {
			$request = $this->getRequest();
			if ($request->isPost()) {
				$post = $request->getPost();
				if(isset($post['tag_id'])&&$post['tag_id']!=''&&isset($post['group_id'])&&$post['group_id']!=''){ 				 
					if($this->getGroupTagTable()->removeTagFromGroup($post['group_id'],$post['tag_id'])){
						$error = 'Successfully removed';
						$error_count==0;
					}else{
						$error = 'Some error occured.Please try again';
						$error_count++;
					}
				}else{	
					$error = 'Invalid access';
					$error_count++;
				}
			}else{
				$error = 'Invalid access';
				$error_count++;
			}
		}else{
			$error = 'Your session expired';
			$error_count++;
		}
		 if($error_count==0){
			$return_array['msg'] = " ";
			$return_array['success'] = 1;
		 }
		 else{
			$return_array['msg'] = $error;
			$return_array['success'] = 0;
		 }
		 echo json_encode($return_array);die();
	}
	public function getTagListAction(){
		$error =array();
		$success =array();
		$viewModel = new ViewModel();
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage('admin'));
		$request = $this->getRequest();
		$identity = null;
		$limit = 50;
		$offset = 0;
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$post = $request->getPost();	
			if(!empty($post)){
				$group_id = $post['group_id'];
				$page = $post['page'];
				if($page){
					$offset = $page*$limit;
				}
				if($group_id!=''){
					$tags = $this->getGroupTagTable()->fetchAllTagsExceptGroup($group_id,$limit,$offset);
					$viewModel->setVariable('tags', $tags);	
				}else{
					$error[] = 'Invalid access method';
				}
			}else{
				$error[] = 'Invalid access method';
			}			 			
		}else{
			return $this->redirect()->toRoute('jadmin/login', array('action' => 'login'));
		}
		$viewModel->setVariable('error', $error);	
		$viewModel->setTerminal($request->isXmlHttpRequest());
		return $viewModel;	
	}	
	public function addGroupTagAction(){  
		$error ='';
		$error_count =0;
		$return_array = array();
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage('admin'));
		if ($auth->hasIdentity()) {
			$request = $this->getRequest();
			if ($request->isPost()) {
				$post = $request->getPost();
				if(isset($post['tag_id'])&&$post['tag_id']!=''&&isset($post['group_id'])&&$post['group_id']!=''){
					$groupData = $this->getGroupTable()->getPlanetinfo($post['group_id']);
					$tagData = $this->getTagTable()->getTag($post['tag_id']);
					if(!empty($groupData)&&!empty($tagData)){
						$tag_data['group_tag_group_id'] = $post['group_id'];
						$tag_data['group_tag_tag_id'] = $post['tag_id'];
						$objuser = new User();					
						$tag_data['group_tag_added_ip_address'] = $objuser->getUserIp();
						$groupTag = new GroupTag();
						$groupTag->exchangeArray($tag_data);		               	 
						if($this->getGroupTagTable()->saveGroupTag($groupTag)){
							$error = 'Successfully removed';
							$error_count==0;
						}else{
							$error = 'Some error occured.Please try again';
							$error_count++;
						}
					}else{
						$error = 'Invalid access';
						$error_count++;
					}
				}else{	
					$error = 'Invalid access';
					$error_count++;
				}
			}else{
				$error = 'Invalid access';
				$error_count++;
			}
		}else{
			$error = 'Your session expired';
			$error_count++;
		}
		 if($error_count==0){
			$return_array['msg'] = " ";
			$return_array['success'] = 1;
		 }
		 else{
			$return_array['msg'] = $error;
			$return_array['success'] = 0;
		 }
		 echo json_encode($return_array);die();
	}		 
	public function getUserTable()
    {
        $sm = $this->getServiceLocator();
		return $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;
    }		 
	public function getGroupTable()
    {
        $sm = $this->getServiceLocator();
		return $this->groupTable = (!$this->groupTable)? $sm->get('Groups\Model\GroupsTable'):$this->groupTable; 
    } 
	public function getTagTable()
    {
        $sm = $this->getServiceLocator();
		return $this->tagTable =(!$this->tagTable)?$sm->get('Tag\Model\TagTable'):$this->tagTable;
    } 
	public function getGroupTagTable()
    {
        $sm = $this->getServiceLocator();
		return $this->groupTagTable =(!$this->groupTagTable)?$sm->get('Tag\Model\GroupTagTable'):$this->groupTagTable;
    }
}