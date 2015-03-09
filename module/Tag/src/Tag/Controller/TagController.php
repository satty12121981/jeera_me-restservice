<?php
namespace Tag\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel; 
use Zend\Session\Container;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Tag\Model\Tag;  
use Tag\Model\TagTable;
class TagController extends AbstractActionController
{
    protected $tagTable;  
	protected $tagCategoryTable;
	protected $userTagTable;
	protected $userTable;
    public function getAllActiveCategoriesAction(){
		$tag_category = $this->getTagCategoryTable()->getActiveCategories();
		$result = new JsonModel(array(
		'tag_category' => $tag_category,      
		));		
		return $result;	
	}
	public function getAllTagsOfSelectedCategoryAction(){
		$tags = array();
		$request   = $this->getRequest();
		if ($request->isPost()){
			$post = $request->getPost();
			$category_id = $post['category_id'];
			$search = $post['search'];
			$tags = (!empty($category_id))?	$this->getTagTable()->getAllCategoryActiveTags($category_id,$search):$tags;			 
		}
		$result = new JsonModel(array(
		'tags' => $tags,      
		));		
		return $result;	
	}
	public function getTagTable(){
		$sm = $this->getServiceLocator();
        return $this->tagTable = (!$this->tagTable)?$sm->get('Tag\Model\TagTable'):$this->tagTable;      
    }
	public function getTagCategoryTable(){
		$sm = $this->getServiceLocator();
        return $this->tagCategoryTable = (!$this->tagCategoryTable)?$sm->get('Tag\Model\TagCategoryTable'):$this->tagCategoryTable;      
    }
	public function listExceptSelectedAction(){
		$auth = new AuthenticationService();	
		$identity = null;
		$error = array();
		$viewModel = new ViewModel();
		$request = $this->getRequest();
		if ($auth->hasIdentity()) {             
           	$identity = $auth->getIdentity();
			$user_id = $identity->user_id;
			$tags = $this->getTagTable()->listExceptSelected($user_id);
			$viewModel->setVariable('tags', $tags);
        }else{
			$error[] = "Your session expired";
		}
		$viewModel->setVariable('error', $error);
		$viewModel->setTerminal($request->isXmlHttpRequest());
		return $viewModel;
	}
	public function  searchTagsAction(){
		$auth = new AuthenticationService();	
		$identity = null;
		$error = array();
		$viewModel = new ViewModel();
		$request = $this->getRequest();
		$search_str = '';
		if ($auth->hasIdentity()) {             
           	$identity = $auth->getIdentity();
			$user_id = $identity->user_id;
			if ($request->isPost()) {
				$search_str = $request->getPost('search_str');
			}
			$tags = $this->getTagTable()->listExceptSelected($user_id,$search_str);
			$viewModel->setVariable('tags', $tags);
        }else{
			$error[] = "Your session expired";
		}
		$viewModel->setVariable('error', $error);
		$viewModel->setTerminal($request->isXmlHttpRequest());
		$viewModel->setTemplate('tag/tag/list-except-selected');
		return $viewModel;
	}
	 public function getGroupCountAction(){
		$error = '';
		$group_count = 0;
		$auth = new AuthenticationService();		 
		if ($auth->hasIdentity()) {
			$request   = $this->getRequest();
			$identity  = $auth->getIdentity();
            if ($request->isPost()){                 
                 $userinfo   = $this->getUserTable()->getUser($identity->user_id);
				 if(!empty($userinfo)&&$userinfo->user_id){					 
					 $group_count = $this->getUserTagTable()->getCountOfAllMatchedGroupsofUser($identity->user_id)->group_count;
				}else{$error = "User not exist in the system";}              
            }else{$error = "Unable to process";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	
		$return_array['group_count'] =$group_count;		
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	 }
	 public function getUserTagTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTagTable = (!$this->userTagTable)?$sm->get('Tag\Model\UserTagTable'):$this->userTagTable;    
	}
	public function getUserTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;    
	} 
}