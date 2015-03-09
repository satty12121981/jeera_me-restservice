<?php 
####################UserGroup Controller #################################
//Created by Shail
#########################################################################
namespace Groups\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;	//Return model 
use Zend\Session\Container; // We need this when using sessions     
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;
use Zend\Crypt\BlockCipher;	#for encryption
#Group classs
use User\Model\User;  
use Groups\Model\Groups;  
use Groups\Model\UserGroup;
use Groups\Model\UserGroupRequest;
use Groups\Model\UserGroupAddSuggestion;
use Notification\Model\UserNotification;
use \Exception;
class UserGroupController extends AbstractActionController
{
    protected $groupTable;		#variable to hold the group model confirgration
	protected $userGroupTable;
	protected $userTable;
	protected $userProfileTable;
	protected $userGroupRequestTable;
	protected $userNotificationTable;
	protected $userGroupAddSuggestionTable;		
	
	#this function will load the css and javascript need for perticular action
	protected function getViewHelper($helperName)
	{
    	return $this->getServiceLocator()->get('viewhelpermanager')->get($helperName);
	} 
	   
 	#this function will load all the groups of user for which he is registered
    public function indexAction()
    {		
		#loading the configration
		$sm = $this->getServiceLocator(); 
		$basePath = $sm->get('Request')->getBasePath();	#get the base path
		
		$this->getViewHelper('HeadScript')->appendFile($basePath.'/js/jquery.min.js','text/javascript');
		$this->getViewHelper('HeadScript')->appendFile($basePath.'/js/1625.js','text/javascript');
		
		$userData =array();	///this will hold data from y2m_user table			
		try { 
			$request   = $this->getRequest();		
			$auth = new AuthenticationService();	
			$identity = null; 	 
			if ($auth->hasIdentity()) {
				// Identity exists; get it
           		$identity = $auth->getIdentity();			
				$userData = $this->getUserTable()->getUser($identity->user_id);					
				#fetch all groups of users
				$userGroups = $this->getUserGroupTable()->fetchAllUserGroup($userData->user_id);
			}	
		} catch (\Exception $e) {
        	//	echo "Caught exception: " . get_class($e) . "\n";
   			//echo "Message: " . $e->getMessage() . "\n";
			 
   		}
		 
		$viewModel = new ViewModel(array('users' => $userData,'userGroups' => $userGroups,));
		$viewModel->setTerminal($request->isXmlHttpRequest());
    	return $viewModel;		 
    }	
	
	#this function will load all the sub-groups of user for which he is registered
	public function usersubgroupAction(){
	
		#loading the configration
		$sm = $this->getServiceLocator(); 
		$basePath = $sm->get('Request')->getBasePath();	#get the base path	 
		
		$this->getViewHelper('HeadScript')->appendFile($basePath.'/js/jquery.min.js','text/javascript');
		$this->getViewHelper('HeadScript')->appendFile($basePath.'/js/1625.js','text/javascript');		
		
		try {	
			$request   = $this->getRequest();		
			$group_id= $this->params('group_id');	 
			$userData =array();	///this will hold data from y2m_user table
			$groupData =array();
			$userSubGroups =array();					
			$auth = new AuthenticationService();	
			$identity = null;        
			if ($auth->hasIdentity()) {
            	// Identity exists; get it
           		$identity = $auth->getIdentity();				
				#fetch the user palnestes
				$suggestSubGroup =array();
				 #check the identity againts the DB
				$userData = $this->getUserTable()->getUser($identity->user_id);				
				if(isset($userData->user_id) && !empty($userData->user_id) && isset($group_id) && !empty($group_id)){				
					#get Group Info
					$groupData = $this->getGroupTable()->getGroup($group_id);	
					if(isset($groupData->group_id) && !empty($groupData->group_id)){						
						#fetch all subgroups of users fffor that group Id
						$userSubGroups = $this->getUserGroupTable()->fetchAllUserSubGroup($userData->user_id, $groupData->group_id);								 	
					} //if(isset($userData->user_id) && !empty($userData->user_id)
				} //if(isset($userData->user_id) && !empty($userData->user_id) && isset($group_id) && !empty($group_id))
			} //if ($auth->hasIdentity()) 		 
		} catch (\Exception $e) {
        //	echo "Caught exception: " . get_class($e) . "\n";
   			//echo "Message: " . $e->getMessage() . "\n";			 
   		}	
		$viewModel = new ViewModel(array('userData' => $userData,'groupData' => $groupData,'userSubGroups' => $userSubGroups));
    	$viewModel->setTerminal($request->isXmlHttpRequest());
    	return $viewModel;		 		
	}
	
	#this wil add the Galaxy to users using ajax
	public function addusergroupAction(){		
		$request   = $this->getRequest();		
		$group_id= (int) $this->params('group_id');
		$identity = null;       
		$auth = new AuthenticationService();			       
			if ($auth->hasIdentity()) {
            	// Identity exists; get it
           		$identity = $auth->getIdentity();				
				#check the identity against the DB
				$userData = $this->getUserTable()->getUser($identity->user_id);				
				if(isset($userData->user_id) && !empty($userData->user_id) && isset($group_id) && !empty($group_id)) {				
					#get Group Info
					$groupData = $this->getGroupTable()->getGroup($group_id);
					if(isset($groupData->group_id) && !empty($groupData->group_id) && isset($groupData->group_parent_group_id) && trim($groupData->group_parent_group_id)=="0"){
						#this will conform group exist and it is a Galaxy. Not Sub Group
						$groupAlreadyRegisterInfo =$this->getUserGroupTable()->getUserGroup($userData->user_id, $groupData->group_id);
			 			if(isset($groupAlreadyRegisterInfo->user_group_id) && !empty($groupAlreadyRegisterInfo->user_group_id)){
							//this means user is already register. Do nothing
							$arr = array('error' => "yes", 'status' => "You are already registered for this Galaxy");
							echo json_encode($arr);							 
						}else{
							 //this means user is not register. Proced adding.
							 $userGroupData = array();
							 $userGroupData['user_group_user_id'] = $userData->user_id;
							 $userGroupData['user_group_group_id'] = $groupData->group_id;
							 #create object of User class
							 
							 $userGroupData['user_group_added_ip_address'] = user::getUserIp();
							 $userGroupData['user_group_status'] = "1";
							 $userGroupData['user_group_is_owner'] = "0";
							 #lets Save the User
							$userGroup = new UserGroup();
							$userGroup->exchangeArray($userGroupData);
							$insertedUserGroupId ="";	#this will hold the latest inserted id value
							$insertedUserGroupId = $this->getUserGroupTable()->saveUserGroup($userGroup); 	
							if(isset($insertedUserGroupId) && !empty($insertedUserGroupId)){
								$arr = array('error' => "no", 'status' => "Galaxy added successfully");
								echo json_encode($arr);
							}else{
								$arr = array('error' => "yes", 'status' => "An error is occured while saving Galaxy.Please try again");
								echo json_encode($arr);
							}						 
						}
						
					} else {//if(isset($userData->user_id) && !empty($userData->user_id) && isset($group_id) && !empty($group_id))				
						$arr = array('error' => "yes", 'status' => "Invalid Access. Galaxy does not exist");
						echo json_encode($arr);
					}
				} else { //if(isset($userData->user_id) && !empty($userData->user_id) && isset($group_id) && !empty($group_id))
					$arr = array('error' => "yes", 'status' => "Invalid Access. You need to login");
					echo json_encode($arr);
				}				
			}else{
				$arr = array('error' => "yes", 'status' => "Invalid Access. You need to login");
				echo json_encode($arr);
			}
			
		 
		 exit;
		$viewModel = new ViewModel();
    	$viewModel->setTerminal($request->isXmlHttpRequest());
    	return $viewModel;
	}
	
	#This will add the Planets to users using ajax
	public function addusersubgroupAction(){			
		#loading the configration
		$sm = $this->getServiceLocator(); 
		$basePath = $sm->get('Request')->getBasePath();	#get the base path	
		$request   = $this->getRequest();       		
		$cgroup_id=  (int)$this->params('group_id');		
		$identity = null;       
		$auth = new AuthenticationService();	
		$subGroupData =array(); 	//it will take the sub group info 
		$groupData = array();	    //it will take the group info			       
			if ($auth->hasIdentity()) {
            	// Identity exists; get it
           		$identity = $auth->getIdentity();				
				#check the identity againts the DB
				$userData = $this->getUserTable()->getUser($identity->user_id);						
				if(isset($userData->user_id) && !empty($userData->user_id) && isset($cgroup_id) && !empty($cgroup_id)) {				
					#get Group Info
					$subGroupData = $this->getGroupTable()->getSubGroup($cgroup_id);					 
					 
					if(isset($subGroupData->group_id) && !empty($subGroupData->group_id) && isset($subGroupData->group_parent_group_id) && trim($subGroupData->group_parent_group_id)!="0"){						 
						#Check if user has already add this planet or not
						$subGroupAlreadyRegisterInfo =$this->getUserGroupTable()->getUserGroup($userData->user_id, $subGroupData->group_id);	
								
							
						if(isset($subGroupAlreadyRegisterInfo->user_group_id) && !empty($subGroupAlreadyRegisterInfo->user_group_id)){						 
							//this means user is already register. Do nothing
							$arr = array('error' => "yes", 'status' => "You are already registered for this Planet");
							echo json_encode($arr);							 
						}else{						
							//this means user is not register. Proceed adding.
														
							############################################GALAXY CHECK CODE. AUTOADD GALAXY CODE COMES HERE#############
							//Check if user has already register Galaxy or Not. If yes, then do nothing.If not. Add that galaxy
							
							
							$groupAlreadyRegisterInfo =array();
							$groupAlreadyRegisterInfo =$this->getUserGroupTable()->getUserGroup($userData->user_id, $subGroupData->group_parent_group_id);					 
							
							if(isset($groupAlreadyRegisterInfo->user_group_id) && !empty($groupAlreadyRegisterInfo->user_group_id)){
								#This means user has already added this galaxy							
							}else{
								#Add this galaxy								 
								 $userGroupData = array();
								 $userGroupData['user_group_user_id'] = $userData->user_id;
								 $userGroupData['user_group_group_id'] = $subGroupData->group_parent_group_id;
								 #create object of User class								  
								 $userGroupData['user_group_added_ip_address'] = user::getUserIp();
								 $userGroupData['user_group_status'] = "1";
								 $userGroupData['user_group_is_owner'] = "0";
								 #lets Save the User
								$userGroup = new UserGroup();
								$userGroup->exchangeArray($userGroupData);
								$insertedUserGroupId ="";	#this will hold the latest inserted id value
								$insertedUserGroupId = $this->getUserGroupTable()->saveUserGroup($userGroup); 	
								if(isset($insertedUserGroupId) && !empty($insertedUserGroupId)){
									 #do Nothing
								}else{
									$arr = array('error' => "yes", 'status' => "An error is occured while saving Galaxy.Please try again");
									echo json_encode($arr);
								}							
							}						 						
							############################################GALAXY CHECK CODE. AUTOADD GALAXY CODE COMES HERE#############		
							
							
							
							#Lets save the Planet					
							$userGroupData = array();
							$userGroupData['user_group_user_id'] = $userData->user_id;
							$userGroupData['user_group_group_id'] = $subGroupData->group_id;
							#create object of User class
							$userGroupData['user_group_added_ip_address'] = user::getUserIp();
							$userGroupData['user_group_status'] = "1";
							$userGroupData['user_group_is_owner'] = "0";
							#lets Save the User
							$userGroup = new UserGroup();
							$userGroup->exchangeArray($userGroupData);
							$insertedUserGroupId ="";	#this will hold the latest inserted id value
							$insertedUserGroupId = $this->getUserGroupTable()->saveUserGroup($userGroup); 
							
							
							
							#send the Notification to User
							$UserGroupNotificationData = array();						
							$UserGroupNotificationData['user_notification_user_id'] = $userData->user_id;									
							$UserGroupNotificationData['user_notification_content'] = "You have successfully <a href='".$basePath."/profile'><b>".$subGroupData->group_title."</b></a>";						$UserGroupNotificationData['user_notification_added_timestamp'] = date('Y-m-d H:i:s');
							$userObject = new user(array());
							$UserGroupNotificationData['user_notification_notification_type_id'] = "1";
							$UserGroupNotificationData['user_notification_status'] = 0;			
							
															 
															
							#lets Save the User Notification
							$UserGroupNotificationSaveObject = new UserNotification();
							$UserGroupNotificationSaveObject->exchangeArray($UserGroupNotificationData);							
							$insertedUserGroupNotificationId ="";	#this will hold the latest inserted id value
							$insertedUserGroupNotificationId = $this->getUserNotificationTable()->saveUserNotification($UserGroupNotificationSaveObject);						
							
							/*# if user has already sent requst to this planet or not. 
								$checkUserRequestData = array();
								$checkUserRequestData = $this->getUserGroupRequestTable()->getUserGroupRequestParticular($userData->user_id, $subGroupData->group_id);
										 
								if(isset($checkUserRequestData->user_group_joining_request_id) && !empty($checkUserRequestData->user_group_joining_request_id)){
									#user has already send invitation. the Invitation needs to update and send a notification
									$userGroupRequestSaveData['user_group_joining_request_id'] = $checkUserRequestData->user_group_joining_request_id;												
								} 
										
									#save the request
									$userGroupRequestSaveData = array();						
									$userGroupRequestSaveData['user_group_joining_request_user_id'] = $userData->user_id;										
									$userGroupRequestSaveData['user_group_joining_request_group_id'] = $subGroupData->group_id;
									$userGroupRequestSaveData['user_group_joining_request_added_timestamp'] = date('Y-m-d H:i:s');
									$userObject = new user(array());
									$userGroupRequestSaveData['user_group_joining_request_added_ip_address'] = $userObject->getUserIp();
									$userGroupRequestSaveData['user_group_joining_request_status'] = 0;
										 
															
									#lets Save the Request
									$userGroupRequestSaveObject = new UserGroupRequest();
									$userGroupRequestSaveObject->exchangeArray($userGroupRequestSaveData);
									$insertedUserGroupRequestId ="";	#this will hold the latest inserted id value
									$insertedUserGroupRequestId = $this->getUserGroupRequestTable()->saveUserGroupRequest($userGroupRequestSaveObject); 	
									
										
									#Find the ower of Planet 
									$subGroupUserOwnerData = array();
									$subGroupUserOwnerData = $this->getUserGroupTable()->findGroupOwner($subGroupData->group_id);
									
									//echo "<li>".$subGroupUserOwnerData->user_group_user_id;exit;
									#send notification to owner
									if(isset($subGroupUserOwnerData->user_group_user_id) && !empty($subGroupUserOwnerData->user_group_user_id)){
										$UserGroupNotificationData = array();						
										$UserGroupNotificationData['user_notification_user_id'] = $subGroupUserOwnerData->user_group_user_id;										
										$UserGroupNotificationData['user_notification_content'] = "You send add requeest for Planet <a href='#'><b>".$subGroupData->group_title."</b></a>";								$UserGroupNotificationData['user_notification_added_timestamp'] = date('Y-m-d H:i:s');
										$userObject = new user(array());
										$UserGroupNotificationData['user_notification_notification_type_id'] = "1";
										$UserGroupNotificationData['user_notification_status'] = 0;											 
															
										#lets Save the User
										$UserGroupNotificationSaveObject = new UserNotification();
										$UserGroupNotificationSaveObject->exchangeArray($UserGroupNotificationData);
										$insertedUserGroupNotificationId ="";	#this will hold the latest inserted id value
										$insertedUserGroupNotificationId = $this->getUserNotificationTable()->saveUserNotification($UserGroupNotificationSaveObject);										
									} //if(isset($subGroupUserOwnerData->user_group_user_id) && !empty($subGroupUserOwnerData->user_group_user_id))		*/	
							
								
							if(isset($insertedUserGroupId) && !empty($insertedUserGroupId)){
								$arr = array('error' => "no", 'status' => "Planet added successfully to the user");
								echo json_encode($arr);
							}else{
								$arr = array('error' => "yes", 'status' => "An error is occured while saving Planet.Please try again");
								echo json_encode($arr);
							}						 
						}
						
					} else { //if(isset($userData->user_id) && !empty($userData->user_id) && isset($group_id) && !empty($group_id))				
						$arr = array('error' => "yes", 'status' => "Invalid Access. Planet does not exist");
						echo json_encode($arr);
					}
				} 			 			
			}else{
				$arr = array('error' => "yes", 'status' => "Invalid Access. You need to login");
				echo json_encode($arr);
			}
			
		 
		 exit;
		$viewModel = new ViewModel();
    	$viewModel->setTerminal($request->isXmlHttpRequest());
    	return $viewModel;	
		 
	
	}
	
	public function addusersubgroupsuggestionAction(){
		$request   = $this->getRequest();	
		$identity = null;       
		$auth = new AuthenticationService();	
		if ($auth->hasIdentity()) {
        	// Identity exists; get it
           	$identity = $auth->getIdentity();				
			#check the identity againts the DB
			$userData = $this->getUserTable()->getUser($identity->user_id);		
			if ($request->isPost()) {		
				$post = $request->getPost();			
				$planetName = trim($post->get('group_add_suggestion_name'));	
				$encryptedGroupId = $post->get('group_add_suggestion_parent_group_id');					
				
				#decrypt the Galaxy id
				$blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
				$blockCipher->setKey('JHHU98789*&^&^%^$^^&g53$@8'); 
				$group_add_suggestion_parent_group_id ="";
				$group_add_suggestion_parent_group_id =$blockCipher->decrypt($encryptedGroupId); 	
							
				if(trim($planetName) && !empty($planetName)){						
					#first check if same name already exist .If exist.Do not allow user
					$groupAddSuggestionExist =array();
					$groupAddSuggestionExist = $this->getUserGroupAddSuggestionTable()->getUserGroupAddSuggestionForName($planetName);					
					if(isset($groupAddSuggestionExist->group_add_suggestion_id) && !empty($groupAddSuggestionExist->group_add_suggestion_id)){	
						#Galaxy already exist	
						$arr = array('error' => "yes", 'status' => "The requested planet name is already taken");
						echo json_encode($arr);	exit;			
					} //if(isset($groupData->group_id) && !empty($groupData->group_id))
					
					#first check if suggest name is already exist in galaxy
					$groupData =array();
					$groupData = $this->getGroupTable()->getGroupForName($planetName);	
					if(isset($groupData->group_id) && !empty($groupData->group_id)){	
						#Galaxy already exist	
						$arr = array('error' => "yes", 'status' => "The requested name already exist for Galaxy");
						echo json_encode($arr);	exit;			
						
					} //if(isset($groupData->group_id) && !empty($groupData->group_id))
					
					#first check if suggest name is already exist in planet
					$subGroupData =array();
					$subGroupData = $this->getGroupTable()->getSubGroupForName($planetName);	
					if(isset($subGroupData->group_id) && !empty($subGroupData->group_id)){						
						#Planet already exist
						$arr = array('error' => "yes", 'status' => "The requested name already exist for Planet");
						echo json_encode($arr);	exit;	
					} //if(isset($subGroupData->group_id) && !empty($subGroupData->group_id))
					
					#lets save this planet
					$userGroupAddSuggestData = array();
					$userGroupAddSuggestData['group_add_suggestion_user_id'] = $userData->user_id;
					$userGroupAddSuggestData['group_add_suggestion_name'] = $planetName;
					$userGroupAddSuggestData['group_add_suggestion_status'] = "waiting";
					$userGroupAddSuggestData['group_add_suggestion_parent_group_id'] = $group_add_suggestion_parent_group_id;
					#create object of User class
					$user = new User();
					$userGroupAddSuggestData['group_add_suggestion_added_ip_address'] = $user->getUserIp();		
					
					#lets Save the User
					$userGroupAddSuggest = new UserGroupAddSuggestion();
					$userGroupAddSuggest->exchangeArray($userGroupAddSuggestData);
					$insertedUserGroupId ="";	#this will hold the latest inserted id value
					$insertedUserGroupAddSuggest = $this->getUserGroupAddSuggestionTable()->saveUserGroupAddSuggestion($userGroupAddSuggest); 	
					
					if(isset($insertedUserGroupAddSuggest) && !empty($insertedUserGroupAddSuggest)){
						#send Notification to user
						$UserGroupNotificationData = array();						
						$UserGroupNotificationData['user_notification_user_id'] = $userData->user_id;									
						$UserGroupNotificationData['user_notification_content'] = "You have Submit a new Planet ".$planetName." Request";
						$UserGroupNotificationData['user_notification_added_timestamp'] = date('Y-m-d H:i:s');
						$userObject = new user(array());
						$UserGroupNotificationData['user_notification_notification_type_id'] = "1";
						$UserGroupNotificationData['user_notification_status'] = 0;			
						#lets Save the User Notification
						$UserGroupNotificationSaveObject = new UserNotification();
						$UserGroupNotificationSaveObject->exchangeArray($UserGroupNotificationData);							
						$insertedUserGroupNotificationId ="";	#this will hold the latest inserted id value
						$insertedUserGroupNotificationId = $this->getUserNotificationTable()->saveUserNotification($UserGroupNotificationSaveObject);
											 
						$arr = array('error' => "no", 'status' => "Planet has been added for Review");
						echo json_encode($arr);
					}else{ //if(isset($insertedUserGroupAddSuggest) && !empty($insertedUserGroupAddSuggest))
						$arr = array('error' => "yes", 'status' => "An error is occured while saving.Please try again");
						echo json_encode($arr);
					}//else of if(isset($insertedUserGroupAddSuggest) && !empty($insertedUserGroupAddSuggest))						
				}else{ if(trim($planetName) && !empty($planetName))
					$arr = array('error' => "yes", 'status' => "Invalid Planet Name");
					echo json_encode($arr);	exit;
				} //else of if(trim($planetName) && !empty($planetName))		
			}else{ //if ($request->isPost())
				$arr = array('error' => "yes", 'status' => "Invalid Access");
				echo json_encode($arr);	exit;	
			} //else of if ($request->isPost())
		}else{ //if ($auth->hasIdentity())
			$arr = array('error' => "yes", 'status' => "Invalid Access");
			echo json_encode($arr);	exit;
		} //else of if ($auth->hasIdentity())
		exit;		
	}	
	
	#access Galaxy/Planet Table Module
	 public function getGroupTable()
    {
        if (!$this->groupTable) {
            $sm = $this->getServiceLocator();
			$this->groupTable = $sm->get('Group\Model\GroupTable');
        }
        return $this->groupTable;
    } 
	
	#access User Galaxy/Planet Module
	 public function getUserGroupTable()
    {
        if (!$this->userGroupTable) {
            $sm = $this->getServiceLocator();
			$this->userGroupTable = $sm->get('Group\Model\UserGroupTable');
        }
        return $this->userGroupTable;
    } 
	
	#access Group Add Request Table Model
    public function getUserGroupRequestTable()
    {
        if (!$this->userGroupRequestTable) {
            $sm = $this->getServiceLocator();
            $this->userGroupRequestTable = $sm->get('Group\Model\UserGroupRequestTable');
        }
        return $this->userGroupRequestTable;
    } 
	
	#access User Module 
    public function getUserTable()
    {
        if (!$this->userTable) {
            $sm = $this->getServiceLocator();
            $this->userTable = $sm->get('User\Model\UserTable');
        }
        return $this->userTable;
    } 
	
	#access User Profile Module
    public function getUserProfileTable()
    {
        if (!$this->userProfileTable) {
            $sm = $this->getServiceLocator();
            $this->userProfileTable = $sm->get('User\Model\UserProfileTable');
        }
        return $this->userProfileTable;
    } 
	
	#access User Notification
    public function getUserNotificationTable()
    {
        if (!$this->userNotificationTable) {
            $sm = $this->getServiceLocator();
            $this->userNotificationTable = $sm->get('Notification\Model\UserNotificationTable');
        }
        return $this->userNotificationTable;
    }
	
	#access User Notification
    public function getUserGroupAddSuggestionTable()
    {
        if (!$this->userGroupAddSuggestionTable) {
            $sm = $this->getServiceLocator();
            $this->userGroupAddSuggestionTable = $sm->get('Group\Model\UserGroupAddSuggestionTable');
        }
        return $this->userGroupAddSuggestionTable;
    } 		 
	 
}