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
use Tag\Model\TagCategory;
use Admin\Form\AdminTagCategoryForm;
use Admin\Form\AdminTagCategoryFilter;   
use Admin\Form\AdminTagCategoryEditFilter;

class AdminTagCategoryController extends AbstractActionController
{    
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
			$field = '';
			switch($sort){
				case 'id':
					$field = 'tag_category_id';
				break;
				case 'title':
					$field = 'tag_category_title';
				break;
				default:
					$field = 'tag_category_id';
			}			 
			$search = '';
			$request = $this->getRequest();			
			$search = ($request->isPost()&&$this->getRequest()->getPost('tag_category_search',''))?$this->getRequest()->getPost('tag_category_search',''):$this->getEvent()->getRouteMatch()->getParam('search');
			$total_tags = $this->getTagCategoryTable()->getCountOfAllTagCategories($search);
			$total_pages = ceil($total_tags/20);
			$allTagCategoriesData = $this->getTagCategoryTable()->getAllTagCategories(20,$offset,$field,$order,$search);			 
			return array('allTagCategoriesData' => $allTagCategoriesData,'field'=>$sort,'order'=>$order,'search'=>$search,'total_pages'=>$total_pages,'page'=> $page, 'error' => $error, 'success' => $success, 'flashMessages' => $this->flashMessenger()->getMessages());	 	
        } else {			
			 return $this->redirect()->toRoute('jadmin/login', array('action' => 'login'));		
		}	
    }	
	public function addAction()
    {        
	    $error = array(); 
		$success = array();
		$tagCategorydetails = array();		 
		$auth = new AuthenticationService();
		$auth->setStorage(new SessionStorage('admin'));
		$identity = null; 
		$this->layout('layout/admin_page');		
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();	
			$this->layout()->identity = $identity;
            $sm = $this->getServiceLocator();
            $config = $this->getServiceLocator()->get('Config'); 
			$selectAllTagCategory = $this->getTagCategoryTable()->fetchAll();
			$form = new AdminTagCategoryForm($selectAllTagCategory);
			$form->get('submit')->setAttribute('value', 'Add');
			$request = $this->getRequest();
			$this->layout('layout/admin_page');	
			if ($request->isPost()) {
				$tag = new TagCategory();
				$sm = $this->getServiceLocator();
				$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
				$form->setInputFilter(new AdminTagCategoryFilter($dbAdapter));				
			    $post = array_merge_recursive(
				    $this->getRequest()->getPost()->toArray(),
				    $this->getRequest()->getFiles()->toArray()
				);							 
				$form->setData($post);
           		$fileName = $post['tag_category_icon']['name'];          	
				if ($form->isValid()) {
					 // Is image size equal to or smaller than 50*50?
					$size = new \Zend\Validator\File\ImageSize(array(
					    'maxWidth' => 50, 'maxHeight' => 50,
					));
	     			$adapter = new \Zend\File\Transfer\Adapter\Http();
					//validator can be more than one....Adding validtor for Size
					$adapter->setValidators(array($size), $fileName);
					//Adding Validator for Extension
					$adapter->addValidator('Extension', false, 'jpg,png,gif,ico');			 
					if (!$adapter->isValid()) {
						$dataError = $adapter->getMessages();
						if (count($dataError)) {
							foreach($dataError as $key=>$row) {
								$error[] = $row;
							} //set formElementErrors
						}						
						$form->setMessages(array('fileupload'=>$error ));						
					} else { 	
						 $adapter->setDestination($config['pathInfo']['TagCategoryPath']);			 
						 if ($adapter->receive($fileName)) {						 	
						 	$tagCategorydetails['tag_category_title'] = $post['tag_category_title'];
							$tagCategorydetails['tag_category_desc'] = $post['tag_category_desc'];
							$tagCategorydetails['tag_category_status'] = 1;
							$tagCategorydetails['tag_category_icon'] = $fileName;
							$tag->exchangeArray($tagCategorydetails);
							$this->getTagCategoryTable()->saveTagCategory($tag);                
							return $this->redirect()->toRoute('jadmin/admin-tags-category');
						 }
					}					
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
			$config = $this->getServiceLocator()->get('Config'); 
			$dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');		
			$id = (int)$this->params('id');
			if (!$id) {
				return $this->redirect()->toRoute('jadmin/admin-tag-categories-add', array('action'=>'add'));
			}
			$tag = $this->getTagCategoryTable()->getTagCategory($id); 
			if(!isset($tag->tag_category_id) || empty($tag->tag_category_id)){
				return $this->redirect()->toRoute('jadmin/admin-tags-category', array('action'=>'index'));
			} 
			$form = new AdminTagCategoryForm();
			$tempFileName = $tag->tag_category_icon; 
			$form->bind($tag);
			$form->get('submit')->setAttribute('value', 'Edit');        
			$request = $this->getRequest();
			if ($request->isPost()) {
				$form->setInputFilter(new AdminTagCategoryEditFilter($dbAdapter, $id));	
				$post = array_merge_recursive(
				    $this->getRequest()->getPost()->toArray(),
				    $this->getRequest()->getFiles()->toArray()
				);							 
				$form->setData($post);
           		$fileName = $post['tag_category_icon']['name'];					
				if ($form->isValid()) { 
					if($fileName!=''){
						// Is image size equal to or smaller than 50*50?
						$size = new \Zend\Validator\File\ImageSize(array(
							'maxWidth' => 50, 'maxHeight' => 50,
						));
						$adapter = new \Zend\File\Transfer\Adapter\Http();
						//validator can be more than one....Adding validtor for Size
						$adapter->setValidators(array($size), $fileName);
						//Adding Validator for Extension
						$adapter->addValidator('Extension', false, 'jpg,png,gif,ico');			 
						if (!$adapter->isValid()) {
							$dataError = $adapter->getMessages();
							if (count($dataError)) {
								foreach($dataError as $key=>$row) {
									$error[] = $row;
								} //set formElementErrors
							}						
							$form->setMessages(array('fileupload'=>$error ));						
						} else { 	
							$adapter->setDestination($config['pathInfo']['TagCategoryPath']);			 
							if ($adapter->receive($fileName)) {							
								$tag->tag_category_icon = $fileName;
								@unlink($config['pathInfo']['base_url'].'public/'.$config['image_folders']['tag_category'].$tempFileName);
							}						
						}
					}
					if (!$fileName){ $tag->tag_category_icon = $tempFileName;}
					if (count($error) ==0) { 
						$this->getTagCategoryTable()->saveTagCategory($tag);					 
						return $this->redirect()->toRoute('jadmin/admin-tags-category');
					}else{
						$tag->tag_category_icon = $tempFileName;
					}
				} 
			}
			return array(
				'id' => $id,
				'form' => $form,
				'image_path' => $config['pathInfo']['TagCategoryPath'],
				'icon_file' =>$tempFileName,
				'error' => $error, 
				'success' => $success, 
				'flashMessages' => $this->flashMessenger()->getMessages(),
				'icon_folder' =>$config['image_folders']['tag_category'],
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
			$config = $this->getServiceLocator()->get('Config');
			if (!$id) {
				return $this->redirect()->toRoute('jadmin/admin-tags-category');
			}
			$tag = $this->getTagCategoryTable()->getTagCategory($id);
			if(!isset($tag->tag_category_id) || empty($tag->tag_category_id)){
				return $this->redirect()->toRoute('jadmin/admin-tags-category', array('action'=>'index'));
			}
			$request = $this->getRequest();
			if ($request->isPost()) {
				$del = $request->getPost()->get('del', 'No');
				if ($del == 'Yes') {
					@unlink($config['pathInfo']['base_url'].'public/tagcategory/'.$tag->tag_category_icon);
                    $this->getTagCategoryTable()->deleteTagCategory($id);
				}            
				return $this->redirect()->toRoute('jadmin/admin-tags-category');
			}	 
			return array(
				'id' => $id,
				'tag' => $this->getTagCategoryTable()->getTagCategory($id),                
				'error' => $error, 
				'success' => $success, 
				'flashMessages' => $this->flashMessenger()->getMessages()
			);
		}else{
			return $this->redirect()->toRoute('jadmin/login', array('action' => 'login'));	
		}
    }    
    public function getTagCategoryTable()
    {
        $sm = $this->getServiceLocator();
        return (!$this->tagCategoryTable)?$this->tagCategoryTable = $sm->get('Tag\Model\TagCategoryTable'): $this->tagCategoryTable;		 
    }
}