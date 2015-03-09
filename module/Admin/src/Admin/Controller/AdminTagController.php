<?php
namespace Admin\Controller; 
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel; 
use Zend\Session\Container;     
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Db\Sql\Select;
use Zend\Authentication\Storage\Session as SessionStorage;
 
use Tag\Model\Tag;
use Admin\Form\AdminTagForm;
use Admin\Form\AdminTagFilter;   
use Admin\Form\AdminTagEditFilter;   
class AdminTagController extends AbstractActionController
{    
	protected $tagTable;	 
	protected $userTagTable;
	protected $groupTagTable; 
	protected $tagCategoryTable;
    public function indexAction()
    {	
		$error = array(); 
		$success = array();	 	
		$vm = new ViewModel(); 
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage('admin'));
		$identity = null; 
		$this->layout('layout/admin_page');		
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();	
			$this->layout()->identity = $identity;			 
			$allTagData = array();
			$page = ($this->getEvent()->getRouteMatch()->getParam('page')>0)?$this->getEvent()->getRouteMatch()->getParam('page'):1;
			$offset = ($page)?($page-1)*20:0;	
			$sort = ($this->getEvent()->getRouteMatch()->getParam('sort'))?$this->getEvent()->getRouteMatch()->getParam('sort'):'id';
			$order = ($this->getEvent()->getRouteMatch()->getParam('order')=='desc')?'DESC':'ASC';
			$category = ($this->getEvent()->getRouteMatch()->getParam('category'))?$this->getEvent()->getRouteMatch()->getParam('category'):'all';
			$field = '';
			switch($sort){
				case 'id':
					$field = 'tag_id';
				break;
				case 'title':
					$field = 'tag_title';
				break;
				case 'category':
					$field = 'tag_category_title';
				break;
				default:
					$field = 'tag_id';
			}		 
			$search = '';
			$request = $this->getRequest();			
			$search = ($request->isPost()&&$this->getRequest()->getPost('tag_search',''))?$this->getRequest()->getPost('tag_search',''):$this->getEvent()->getRouteMatch()->getParam('search');		
			$all_categories = $this->getTagCategoryTable()->fetchAll();
			$total_tags = $this->getTagTable()->getCountOfAllTags($category,$search); 
			$total_pages = ceil($total_tags/20);
			$allTagData = $this->getTagTable()->getAllTags(20,$offset,$category,$field,$order,$search);			 
			return array('allTagData' => $allTagData,'field'=>$sort,'order'=>$order,'search'=>$search,'category'=>$category,'all_categories'=>$all_categories,'total_pages'=>$total_pages,'page'=> $page, 'error' => $error, 'success' => $success, 'flashMessages' => $this->flashMessenger()->getMessages());	 	
        }else{			
			 return $this->redirect()->toRoute('jadmin/login', array('action' => 'login'));		
		}			 
    }	
	public function addAction()
    {        
	    $error = array(); 
		$success = array();		 
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage('admin'));
		$identity = null; 
		$this->layout('layout/admin_page');		
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();	
			$this->layout()->identity = $identity;
			$all_categories = $this->getTagCategoryTable()->fetchAll();
			$arr_tag_category = array();
			foreach($all_categories as $rows){
				$arr_tag_category[$rows->tag_category_id] = $rows->tag_category_title;
			}
			$form = new AdminTagForm($arr_tag_category);
			$form->get('submit')->setAttribute('value', 'Add');
			$request = $this->getRequest();
			$this->layout('layout/admin_page');	
			if ($request->isPost()) {
				$tag = new Tag();
				$sm = $this->getServiceLocator();
				$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
				$form->setInputFilter(new AdminTagFilter($dbAdapter));					 
				$form->setData($request->getPost());
				if ($form->isValid()) {
					$tagArrayDetails = $form->getData();
					$tagTitleSplit = explode(",",$tagArrayDetails['tag_title']);
					foreach ($tagTitleSplit as $key => $value) {
						if($value!=''){
							$checkTagExists = $this->getTagTable()->getTagByTitle($value);
							if (empty($checkTagExists) || !$checkTagExists->tag_id) {
								$tagArrayDetails['tag_title'] = $value;
								$tag->exchangeArray($tagArrayDetails);
								$this->getTagTable()->saveTag($tag);  
							}else{
								$this->flashMessenger()->addMessage($value.'already exist');
							}
						}
					}					              
					return $this->redirect()->toRoute('jadmin/admin-tags');
				} 
			}
			return array('form' => $form, 'error' => $error, 'success' => $success, 'flashMessages' => $this->flashMessenger()->getMessages());
		}else{			
			 return $this->redirect()->toRoute('jadmin/login', array('action' => 'login'));		
		}
    }
    public function editAction()
    {
        $error = array(); 
		$success = array();
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage('admin'));
		$identity = null; 
		$this->layout('layout/admin_page');		
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();	
			$this->layout()->identity = $identity;		
			$sm = $this->getServiceLocator();
			$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');		
			$id = (int)$this->params('id');
			if (!$id) {
				return $this->redirect()->toRoute('jadmin/admin-tags-add', array('action'=>'add'));
			}
			$tag = $this->getTagTable()->getTag($id); 
			if(!isset($tag->tag_id) || empty($tag->tag_id)){
				return $this->redirect()->toRoute('jadmin/admin-tags', array('action'=>'index'));
			}
			$all_categories = $this->getTagCategoryTable()->fetchAll();
			$arr_tag_category = array();
			foreach($all_categories as $rows){
				$arr_tag_category[$rows->tag_category_id] = $rows->tag_category_title;
			}
			$form = new AdminTagForm($arr_tag_category,$tag->category_id);
			$form->bind($tag);
			$form->get('submit')->setAttribute('value', 'Edit');        
			$request = $this->getRequest();
			if ($request->isPost()) {
				$form->setInputFilter(new AdminTagEditFilter($dbAdapter, $id));		
				$form->setData($request->getPost());
				if ($form->isValid()) {
					$this->getTagTable()->saveTag($tag);                
					return $this->redirect()->toRoute('jadmin/admin-tags');
				} 
			}
			return array(
				'id' => $id,
				'form' => $form,
				'error' => $error, 
				'success' => $success, 
				'flashMessages' => $this->flashMessenger()->getMessages()
			);
		}else{
			return $this->redirect()->toRoute('jadmin/login', array('action' => 'login'));	
		}
    }
    public function deleteAction()
    {
		$error = array();
		$success = array();		
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage('admin'));
		$identity = null; 
		$this->layout('layout/admin_page');		
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();	
			$this->layout()->identity = $identity;	
			$id = (int)$this->params('id');
			if (!$id) {
				return $this->redirect()->toRoute('jadmin/admin-tags');
			}
			$tag = $this->getTagTable()->getTag($id); 
			if(!isset($tag->tag_id) || empty($tag->tag_id)){
				return $this->redirect()->toRoute('jadmin/admin-tags', array('action'=>'index'));
			}
			$request = $this->getRequest();
			if ($request->isPost()) {
				$del = $request->getPost()->get('del', 'No');
				if ($del == 'Yes') {					 
					$this->getTagTable()->deleteTag($id);
					$this->getUserTagTable()->deleteUserTag($id);
					$this->getGroupTagTable()->deleteGroupTag($id);
				}            
				return $this->redirect()->toRoute('jadmin/admin-tags');
			}	 
			return array(
				'id' => $id,
				'tag' => $this->getTagTable()->getTag($id),
				'usersList' => $this->getUserTagTable()->fetchAllUsersOfTag($id),
				'groupList' => $this->getGroupTagTable()->fetchAllGroupsOfTag($id),
				'error' => $error, 
				'success' => $success, 
				'flashMessages' => $this->flashMessenger()->getMessages()
			);
		}else{
			return $this->redirect()->toRoute('jadmin/login', array('action' => 'login'));	
		}
    } 
	public function getTagTable()
    {        
		$sm = $this->getServiceLocator();
        return (!$this->tagTable)?$this->tagTable = $sm->get('Tag\Model\TagTable'): $this->tagTable;
    }  
	public function getUserTagTable()
    {        
		$sm = $this->getServiceLocator();
        return (!$this->userTagTable)?$this->userTagTable = $sm->get('Tag\Model\UserTagTable'): $this->userTagTable;
    }
	public function getGroupTagTable()
    { 
		$sm = $this->getServiceLocator();
        return (!$this->groupTagTable)?$this->groupTagTable = $sm->get('Tag\Model\GroupTagTable'): $this->groupTagTable;	
    }
	public function getTagCategoryTable()
    {
        $sm = $this->getServiceLocator();
        return (!$this->tagCategoryTable)?$this->tagCategoryTable = $sm->get('Tag\Model\TagCategoryTable'): $this->tagCategoryTable;		 
    }
}