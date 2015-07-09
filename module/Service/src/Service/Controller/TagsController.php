<?php
namespace Service\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\View\Renderer\PhpRenderer;
use \Exception;

use Tag\Model\UserTag;
use Tag\Model\GroupTag;
use User\Model\User;
use Groups\Model\Groups;
class TagsController extends AbstractActionController
{
 	protected $userTable;
	protected $userProfileTable;
	protected $userTagTable;
	protected $tagTable;
	protected $groupTagTable;
    protected $groupsTable;
	protected $userGroupTable;	
	protected $tagCategoryTable;
	public function __construct(){
        $this->flagSuccess = "Success";
		$this->flagError = "Failure";
	}
	public function ListUserTagsAction(){
		$error = '';
		$user_tags = array();
		$userIntrests = array();
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$dataArr = array();	
			$postedValues = $this->getRequest()->getPost();
			$accToken = strip_tags(trim($postedValues['accesstoken']));
			
			if ((!isset($accToken)) || (trim($accToken) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			
			$userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
			if(empty($userinfo)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Invalid Access Token.";
				echo json_encode($dataArr);
				exit;
			}
			$user_id = $userinfo->user_id;
			$userInterests = $this->getUserTagTable()->getAllUserTagsForAPI($user_id);
			if(!empty($userInterests)){
				$userInterests = $this->formatTagsWithCategory($userInterests,"|");
				$dataArr[0]['flag'] = "Success";
				$dataArr[0]['usertags'] = $userInterests;
				echo json_encode($dataArr);
				exit;
			} else {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "No Tags(s) to the user.";
				echo json_encode($dataArr);
				exit;
			}	
		} else {
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request not authorised.";
			echo json_encode($dataArr);
			exit;
		}
	}
    public function AddUserTagsAction(){
    	$error = '';
		$user_tags = array();
		$userIntrests = array();
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$dataArr = array();	
			$postedValues = $this->getRequest()->getPost();
			$accToken = strip_tags(trim($postedValues['accesstoken']));
			$edit_user_tags = (isset($postedValues['tags']))?array_filter($postedValues['tags']):'';
			if ((!isset($accToken)) || (trim($accToken) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			if ((empty($edit_user_tags))) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please Input Tags.";
				echo json_encode($dataArr);
				exit;
			}
			
			$userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
			if(empty($userinfo)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Invalid Access Token.";
				echo json_encode($dataArr);
				exit;
			}
			$user_id = $userinfo->user_id;
			$objUser = new User();
			$flag =0;
			if(!empty($edit_user_tags[0])){
				$edit_user_tags = explode(",", $edit_user_tags[0]);
				foreach($edit_user_tags as $tags_in){
					$data_usertags = array();
					$tag_history = $this->getTagTable()->getTag($tags_in);
					if(!empty($tag_history)&&$tag_history->tag_id!=''){
						$tag_exist =  $this->getUserTagTable()->checkUserTag($user_id,$tags_in); 
						if(empty($tag_exist)){
							$data_usertags['user_tag_user_id'] = $user_id;
							$data_usertags['user_tag_tag_id'] = $tags_in;
							$data_usertags['user_tag_added_ip_address'] = $objUser->getUserIp();
							$objUsertag = new UserTag();
							$objUsertag->exchangeArray($data_usertags);
							$this->getUserTagTable()->saveUserTag($objUsertag);
							$flag=1;
						}
					}else{
						$dataArr[0]['flag'] = "Failure";
						$dataArr[0]['message'] = "Given tag ".$tags_in." is not exist in the system.";
						echo json_encode($dataArr);
						exit;
					}
				}
				if($flag){
					$userInterests = $this->getUserTagTable()->getAllUserTagsForAPI($userinfo->user_id);
					if(!empty($userInterests)){
						$userInterests = $this->formatTagsWithCategory($userInterests,"|");
						$dataArr[0]['flag'] = "Success";
						$dataArr[0]['usertags'] = $userInterests;
						echo json_encode($dataArr);
						exit;
					}
				}else{
					$dataArr[0]['flag'] = "Failure";
					$dataArr[0]['message'] = "Tag(s) already added to the user .";
					echo json_encode($dataArr);
					exit;
				}
			}				
		} else {
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request not authorised.";
			echo json_encode($dataArr);
			exit;
		}
    }
    public function DeleteUserTagsAction(){
    	$error = '';
		$user_tags = array();
		$userIntrests = array();
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$dataArr = array();	
			$postedValues = $this->getRequest()->getPost();
			$accToken = strip_tags(trim($postedValues['accesstoken']));
			$edit_user_tags = (isset($postedValues['tags']))?array_filter($postedValues['tags']):'';
			if ((!isset($accToken)) || (trim($accToken) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			if ((empty($edit_user_tags))) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please Input Tags.";
				echo json_encode($dataArr);
				exit;
			}
			$userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
			if(empty($userinfo)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Invalid Access Token.";
				echo json_encode($dataArr);
				exit;
			}
			$user_id = $userinfo->user_id;
			$tag_category_temp = array();
			$flag =0;
			if(isset($edit_user_tags[0]) && !empty($edit_user_tags[0])){
				$edit_user_tags = explode(",", $edit_user_tags[0]);
				if ($this->getUserTagTable()->deleteAllUserTagsForRestAPI($user_id,array_filter($edit_user_tags))){
					$userInterests = $this->getUserTagTable()->getAllUserTagsForAPI($userinfo->user_id);
					if(!empty($userInterests)){
						$userInterests = $this->formatTagsWithCategory($userInterests,"|");
						$dataArr[0]['flag'] = "Success";
						$dataArr[0]['usertags'] = $userInterests;	
						echo json_encode($dataArr);
						exit;
					} else {
						$dataArr[0]['flag'] = "Failure";
						$dataArr[0]['message'] = "No Tag(s) to the user.";
						echo json_encode($dataArr);
						exit;
					}	
				}else{
					$dataArr[0]['flag'] = "Failure";
					$dataArr[0]['message'] = "Tag(s) does not exists for the user .";
					echo json_encode($dataArr);
					exit;
				}
			}
		}else {
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request not authorised.";
			echo json_encode($dataArr);
			exit;
		}
    }
	public function ListAllTagsAction() {
    	$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$config = $this->getServiceLocator()->get('Config');
			$dataArr = array();	
			$postedValues = $this->getRequest()->getPost();
			$accToken = strip_tags(trim($postedValues['accesstoken']));
			$categoryId = (isset($postedValues['categoryID'])&&$postedValues['categoryID']!=null&&$postedValues['categoryID']!=''&&$postedValues['categoryID']!='undefined')?trim($postedValues['categoryID']):'';
            $offset = (isset($postedValues['nparam']))?strip_tags(trim($postedValues['nparam'])):'';
            $limit = (isset($postedValues['countparam']))?strip_tags(trim($postedValues['countparam'])):'';
			$search_string = (isset($postedValues['search'])&&$postedValues['search']!=null&&$postedValues['search']!=''&&$postedValues['search']!='undefined')?strip_tags(trim($postedValues['search'])):'';
			if ((!isset($accToken)) || (trim($accToken) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			if (!empty($categoryId) && !is_numeric($categoryId)) {
 				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid Category.";
				echo json_encode($dataArr);
				exit;		
			}
			$userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
			if(empty($userinfo)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Invalid Access Token.";
				echo json_encode($dataArr);
				exit;
			}
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
			$taglistdata = $this->getTagTable()->getAllTagsWithCategories((int) $limit,(int) $offset,$categoryId,'category_id','ASC',$search_string);
			
			if(!empty($taglistdata)){
				$loadtagslist = array();
				$loadtagslist = $this->formatTagsWithCategory($taglistdata,"|");
				$dataArr[0]['flag'] = "Success";
				$dataArr[0]['tags'] = $loadtagslist;
				echo json_encode($dataArr);
				exit;
			}else{
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "No Tags available.";
				echo json_encode($dataArr);
				exit;
			}
		}else{
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request not authorised.";
			echo json_encode($dataArr);
			exit;
		}
	}
	public function ListGroupTagsAction(){
        $error = '';
        $group_tags = array();
        $groupIntrests = array();
        $request = $this->getRequest();
        if($this->getRequest()->getMethod() == 'POST') {
            $dataArr = array();
            $postedValues = $this->getRequest()->getPost();
            $accToken = strip_tags(trim($postedValues['accesstoken']));
            $groupid = strip_tags(trim($postedValues['groupid']));
            if (empty($accToken)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
            if (empty($groupid)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
            if (isset($group_id) && !is_numeric($group_id)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a valid GroupId.";
                echo json_encode($dataArr);
                exit;
            }
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            if(empty($userinfo)){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "InValid Access Token.";
                echo json_encode($dataArr);
                exit;
            }
            $groupinfo = $this->getGroupTable()->getGroup($groupid);
            if(empty($groupinfo)){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "InValid Group ID.";
                echo json_encode($dataArr);
                exit;
            }
            $group_id = $groupinfo->group_id;
            $groupInterests = $this->getGroupTagTable()->getAllGroupTagsForAPI($groupid);
            if(!empty($groupInterests)){
                $groupInterests = $this->formatTagsWithCategory($groupInterests,"|");
                $dataArr[0]['flag'] = "Success";
                $dataArr[0]['grouptags'] = $groupInterests;
                echo json_encode($dataArr);
                exit;
            } else {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "No Tag(s) to the group.";
                echo json_encode($dataArr);
                exit;
            }

        } else {
            $dataArr[0]['flag'] = "Failure";
            $dataArr[0]['message'] = "Request not authorised.";
            echo json_encode($dataArr);
            exit;
        }
    }
    public function AddGroupTagsAction(){
        $error = '';
        $group_tags = array();
        $groupInterests = array();
        $request = $this->getRequest();
        if($this->getRequest()->getMethod() == 'POST') {
            $dataArr = array();
            $postedValues = $this->getRequest()->getPost();
            $accToken = strip_tags(trim($postedValues['accesstoken']));
            $group_id = strip_tags(trim($postedValues['groupid']));
            $edit_group_tags = (isset($postedValues['tags']))?array_filter($postedValues['tags']):'';
            if ((!isset($accToken)) || $accToken == '' ) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
            if (empty($edit_group_tags)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please Input Tags.";
                echo json_encode($dataArr);
                exit;
            }
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            if(empty($userinfo)){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "InValid Access Token.";
                echo json_encode($dataArr);
                exit;
            }
            if (isset($group_id) && !is_numeric($group_id)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a valid GroupId.";
                echo json_encode($dataArr);
                exit;
            }
            $groupinfo = $this->getGroupTable()->getGroup($group_id);
            if(empty($groupinfo->group_id)){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "InValid Group ID.";
                echo json_encode($dataArr);
                exit;
            }
            $objgroup = new Groups();
            $objUser = new User();
            $flag =0;

            if(!$this->getUserGroupTable()->checkOwner($groupinfo->group_id,$userinfo->user_id)){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Access token user is not the owner to add tags to the group";
                echo json_encode($dataArr);
                exit;
            }
            if(!empty($edit_group_tags[0])){
                $edit_group_tags = explode(",", $edit_group_tags[0]);
                foreach($edit_group_tags as $tags_in){
                    $data_grouptags = array();
                    $tag_history = $this->getTagTable()->getTag($tags_in);
                    if(!empty($tag_history)&&$tag_history->tag_id!=''){
                        $tag_exist =  $this->getGroupTagTable()->checkGroupTag($groupinfo->group_id,$tags_in);
                        if(empty($tag_exist)) {
                            $data_grouptags['group_tag_group_id'] = $groupinfo->group_id;
                            $data_grouptags['group_tag_tag_id'] = $tags_in;
                            $data_grouptags['group_tag_added_ip_address'] = $objUser->getUserIp();
                            $objgrouptag = new GroupTag();
                            $objgrouptag->exchangeArray($data_grouptags);
                            $this->getGroupTagTable()->saveGroupTag($objgrouptag);
                            $flag = 1;
                        }
                    }
                    else{
                        $dataArr[0]['flag'] = "Failure";
                        $dataArr[0]['message'] = "Given tag ".$tags_in." does not exist in the system.";
                        echo json_encode($dataArr);
                        exit;
                    }
                }
                if($flag){
                    $groupInterests = $this->getGroupTagTable()->getAllGroupTagsForAPI($groupinfo->group_id);
                    if(!empty($groupInterests)){
                        $groupInterests = $this->formatTagsWithCategory($groupInterests,"|");
                        $dataArr[0]['flag'] = "Success";
                        $dataArr[0]['grouptags'] = $groupInterests;
                        echo json_encode($dataArr);
                        exit;
                    }
                }else{
                    $dataArr[0]['flag'] = "Failure";
                    $dataArr[0]['message'] = "Tag(s) already added to the group";
                    echo json_encode($dataArr);
                    exit;
                }
            }
        } else {
            $dataArr[0]['flag'] = "Failure";
            $dataArr[0]['message'] = "Request not authorised.";
            echo json_encode($dataArr);
            exit;
        }
    }
    public function DeleteGroupTagsAction(){
        $error = '';
        $group_tags = array();
        $groupInterests = array();
        $request = $this->getRequest();
        if($this->getRequest()->getMethod() == 'POST') {
            $dataArr = array();
            $postedValues = $this->getRequest()->getPost();
            $accToken = strip_tags(trim($postedValues['accesstoken']));
            $groupid = strip_tags(trim($postedValues['groupid']));
            $edit_group_tags = (isset($postedValues['tags']))?array_filter($postedValues['tags']):'';
            if ((!isset($accToken)) || (trim($accToken) == '')) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
            if (empty($edit_group_tags)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please Input Tags.";
                echo json_encode($dataArr);
                exit;
            }
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            if(empty($userinfo)){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "InValid Access Token.";
                echo json_encode($dataArr);
                exit;
            }
            if (isset($group_id) && !is_numeric($group_id)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a valid GroupId.";
                echo json_encode($dataArr);
                exit;
            }
            $groupinfo = $this->getGroupTable()->getGroup($groupid);
            if(empty($groupinfo)){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "InValid Group ID.";
                echo json_encode($dataArr);
                exit;
            }
            if(!$this->getUserGroupTable()->checkOwner($groupinfo->group_id,$userinfo->user_id)){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Access token user is not the owner to add tags to the group";
                echo json_encode($dataArr);
                exit;
            }
            $tag_category_temp = array();
            $flag = 0;
            if(isset($edit_group_tags[0]) && !empty($edit_group_tags[0])){
                $edit_group_tags = explode(",", $edit_group_tags[0]);
                if ($this->getGroupTagTable()->deleteAllGroupTagsForRestAPI($groupid,array_filter($edit_group_tags))){
                    $groupInterests = $this->getGroupTagTable()->getAllGroupTagsForAPI($groupinfo->group_id);
                    if(!empty($groupInterests)){
                        $groupInterests = $this->formatTagsWithCategory($groupInterests,"|");
                        $dataArr[0]['flag'] = "Success";
                        $dataArr[0]['grouptags'] = $groupInterests;
                        echo json_encode($dataArr);
                        exit;
                    } else {
                        $dataArr[0]['flag'] = "Failure";
                        $dataArr[0]['message'] = "No Tag(s) to the group.";
                        echo json_encode($dataArr);
                        exit;
                    }
                }else{
                    $dataArr[0]['flag'] = "Failure";
                    $dataArr[0]['message'] = "Tag(s) does not exists for the group .";
                    echo json_encode($dataArr);
                    exit;
                }
            }
        }else {
            $dataArr[0]['flag'] = "Failure";
            $dataArr[0]['message'] = "Request not authorised.";
            echo json_encode($dataArr);
            exit;
        }
    }
	public function ListTagCategoriesAction(){
        if($this->getRequest()->getMethod() == 'POST') {
            $config = $this->getServiceLocator()->get('Config');
            $dataArr = array();
            $postedValues = $this->getRequest()->getPost();
            $accToken = (isset($postedValues['accesstoken']))?strip_tags(trim($postedValues['accesstoken'])):'';
            $search_string = (isset($postedValues['search'])&&$postedValues['search']!=null&&$postedValues['search']!=''&&$postedValues['search']!='undefined')?strip_tags(trim($postedValues['search'])):'';
            $offset = (isset($postedValues['nparam']))?strip_tags(trim($postedValues['nparam'])):'';
            $limit = (isset($postedValues['countparam']))?strip_tags(trim($postedValues['countparam'])):'';
            if (empty($accToken)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
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
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            if(empty($userinfo)){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Invalid Access Token.";
                echo json_encode($dataArr);
                exit;
            }
            $tagcategoriesdata = array();
			$offset = (int) $offset;
			$limit = (int) $limit;
			$offset =($offset>0)?$offset-1:0;
			$offset = $offset*$limit;
            $tagcategoriesdata = $this->getTagCategoryTable()->getAllTagCategoriesForRestAPI((int) $limit,(int) $offset,'tag_category_id','ASC',$search_string);
            if(!empty($tagcategoriesdata)){
                foreach($tagcategoriesdata as $index => $splitlist){
                    if (!empty($splitlist['tag_category_icon']))
                        $splitlist['tag_category_icon'] = $config['pathInfo']['absolute_img_path'].$config['image_folders']['tag_category'].$splitlist['tag_category_icon'];
                    else
                        $splitlist['tag_category_icon'] = $config['pathInfo']['absolute_img_path'].'/images/category-icon.png';
                    $loadtagcatslist[] = array(
                        'tag_category_id' =>$splitlist['tag_category_id'],
                        'tag_category_title' =>$splitlist['tag_category_title'],
                        'tag_category_icon' =>$splitlist['tag_category_icon'],
                        'tag_category_desc' =>$splitlist['tag_category_desc'],
                    );
                }
                $dataArr[0]['flag'] = "Success";
                $dataArr[0]['tag_categories'] = $loadtagcatslist;
                echo json_encode($dataArr);
                exit;
            }else{
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "No Tag categories available.";
                echo json_encode($dataArr);
                exit;
            }
        }else{
            $dataArr[0]['flag'] = "Failure";
            $dataArr[0]['message'] = "Request not authorised.";
            echo json_encode($dataArr);
            exit;
        }
    }
	public function ListTagsByCategoryAction() {
        $request = $this->getRequest();
        if($this->getRequest()->getMethod() == 'POST') {
            $config = $this->getServiceLocator()->get('Config');
            $dataArr = array();
            $postedValues = $this->getRequest()->getPost();
            $accToken = strip_tags(trim($postedValues['accesstoken']));
            $categoryId = (isset($postedValues['categoryID'])&&$postedValues['categoryID']!=null&&$postedValues['categoryID']!=''&&$postedValues['categoryID']!='undefined')?trim($postedValues['categoryID']):'';
            $offset = (isset($postedValues['nparam']))?strip_tags(trim($postedValues['nparam'])):'';
            $limit = (isset($postedValues['countparam']))?strip_tags(trim($postedValues['countparam'])):'';
            if ((!isset($accToken)) || (trim($accToken) == '')) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
            if (empty($categoryId) && !is_numeric($categoryId)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a Valid Category.";
                echo json_encode($dataArr);
                exit;
            }
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            if(empty($userinfo)){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Invalid Access Token.";
                echo json_encode($dataArr);
                exit;
            }
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
            $taglistdata = $this->getTagTable()->getAllTagsByCategory($categoryId,'category_id','ASC',(int) $limit,(int) $offset);

            if(!empty($taglistdata)){
                $dataArr[0]['flag'] = "Success";
                $dataArr[0]['tags'] = $taglistdata;
                echo json_encode($dataArr);
                exit;
            }else{
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "No Tags available.";
                echo json_encode($dataArr);
                exit;
            }
        }else{
            $dataArr[0]['flag'] = "Failure";
            $dataArr[0]['message'] = "Request not authorised.";
            echo json_encode($dataArr);
            exit;
        }
    }
	public function getUserTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;    
	}
	public function getUserProfileTable(){
		$sm = $this->getServiceLocator();
		return  $this->userProfileTable = (!$this->userProfileTable)?$sm->get('UserProfile\Model\UserProfileTable'):$this->groupTable;    
	}
	public function getTagCategoryTable(){
        $sm = $this->getServiceLocator();
        return  $this->tagCategoryTable = (!$this->tagCategoryTable)?$sm->get('Tag\Model\TagCategoryTable'):$this->tagCategoryTable;
    }
	public function getTagTable(){
		$sm = $this->getServiceLocator();
		return  $this->tagTable = (!$this->tagTable)?$sm->get('Tag\Model\TagTable'):$this->tagTable;    
	}
	public function getUserTagTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTagTable = (!$this->userTagTable)?$sm->get('Tag\Model\UserTagTable'):$this->userTagTable;    
	}
	public function getGroupTagTable(){
        $sm = $this->getServiceLocator();
        return  $this->groupTagTable = (!$this->groupTagTable)?$sm->get('Tag\Model\GroupTagTable'):$this->groupTagTable;
    }
    public function getGroupTable(){
        $sm = $this->getServiceLocator();
        return  $this->groupsTable = (!$this->groupsTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupsTable;
    }
	public function getUserGroupTable(){
        $sm = $this->getServiceLocator();
        return  $this->userGroupTable = (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;
    }
	public function formatTagsWithCategory($taglistdata,$char){
		$config = $this->getServiceLocator()->get('Config');
		$loadtagslist = array();
		if (!empty($taglistdata)){
			$objarr_tags = array();

			foreach($taglistdata as $index => $tagslist){
				$temptags = explode(",", $tagslist['tag_title']);
				$arr_tags[0] = array();
				foreach($temptags as $indexes => $splitlist){
					$arr_tags = array();
					$arr_tags = explode($char, $splitlist);
    				$objarr_tags[] = array('tag_id'=>$arr_tags[0],'tag_title'=>$arr_tags[1]);
    			}

				if (!empty($tagslist['tag_category_icon']))
				$tagslist['tag_category_icon'] = $config['pathInfo']['absolute_img_path'].$config['image_folders']['tag_category'].$tagslist['tag_category_icon'];
				else
				$tagslist['tag_category_icon'] = $config['pathInfo']['absolute_img_path'].'/images/category-icon.png';

    			$loadtagslist[] = array(
					'tag_category_id' =>$tagslist['category_id'],
					'tag_category_title' =>$tagslist['tag_category_title'],
					'tag_category_icon' =>$tagslist['tag_category_icon'],
					'tag_category_desc' =>$tagslist['tag_category_desc'],
					'tagslist' =>$objarr_tags,
					);

				unset($objarr_tags);
	        }
	        return $loadtagslist;
		}
		return;
	}
}
