<?php 
namespace Groups\Controller;
use Zend\Authentication\AuthenticationService;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;	//Return model 
use Zend\View\Model\JsonModel;
use Groups\Model\Groups; 
use User\Model\User; 
use Groups\Model\UserGroup;
use Application\Controller\Plugin\UploadHandler;
use Application\Controller\Plugin\ResizeImage;
use Groups\Model\UserGroupJoiningRequest;
use Groups\Model\GroupPhoto;
use Tag\Model\GroupTag; 
use Groups\Model\GroupMedia;  
use Notification\Model\UserNotification; 
use Groups\Model\UserGroupJoiningInvitation; 
use Groups\Model\GroupJoiningQuestionnaire; 
use Groups\Model\GroupQuestionnaireOptions; 
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
class GroupsController extends AbstractActionController
{
	protected $userTable;
	protected $userFriendTable;
	protected $groupTable;
	protected $userGroupTable;
	protected $groupPhotoTable;
	protected $groupTagTable;
	protected $groupMediaTable;
	protected $userNotificationTable;
	protected $groupJoiningInvitationTable; 
    protected $groupJoiningQuestionnaire;  
    protected $groupQuestionnaireOptions; 
	protected $commentTable;
	protected $likeTable;
	protected $userGroupJoiningRequestTable;
	protected $groupQuestionnaireAnswersTable;
	protected $tagTable;
	protected $userTagTable;
	public function membergroupsAction(){
		$error = '';
		$auth = new AuthenticationService();
		$viewModel = new ViewModel();
		$config = $this->getServiceLocator()->get('Config');
		$viewModel->setVariable('image_folders',$config['image_folders']);
		if ($auth->hasIdentity()) {
			$this->layout('layout/layout_user');
			$identity = $auth->getIdentity();
			$profilename = $this->params('member_profile');
			$viewModel->setVariable( 'current_Profile', $profilename);
			$this->layout()->identity = $identity;
			$userinfo = $this->getUserTable()->getUserByProfilename($profilename);
			if(!empty($userinfo)&&$userinfo->user_id){
				$profile_type='mine';
				if($userinfo->user_id!= $identity->user_id){$profile_type='others';}
				$profileWidget = $this->forward()->dispatch('User\Controller\UserProfile', array(
											'action' => 'profile',
											'member_profile'     => $profilename,							 
										));				 
				
				$viewModel->addChild($profileWidget, 'profileWidget');
				$myprofile =($userinfo->user_id== $identity->user_id)?1:0;
				$viewModel->setVariable( 'myprofile' , $myprofile);	
				$myIntrests = $this->getUserTagTable()->getAllUserTags($identity->user_id);
				$viewModel->setVariable( 'myIntrests' , $myIntrests);
				$profile_data = $this->getUserTable()->getProfileDetails($identity->user_id);				 
				$viewModel->setVariable( 'profile_data' , $profile_data);
				$friends_count = $this->getUserFriendTable()->getFriendsCount($userinfo->user_id)->friends_count;				
				$viewModel->setVariable( 'friends_count' , $friends_count);	
				$intTotalGroups      = $this->getUserGroupTable()->fetchAllUserGroupCount( $userinfo->user_id, $identity->user_id,'',$profile_type);
				$viewModel->setVariable( 'group_count' , $intTotalGroups['group_count']);
				$intTotalCreatedGroups      = $this->getUserGroupTable()->fetchAllUserGroupCount( $userinfo->user_id,$identity->user_id, 'created',$profile_type);
				$viewModel->setVariable( 'created_group_count' , $intTotalCreatedGroups['group_count']);
				$intTotalJoinedGroups      = $this->getUserGroupTable()->fetchAllUserGroupCount( $userinfo->user_id,$identity->user_id, 'joined',$profile_type);
				$viewModel->setVariable( 'joined_group_count' , $intTotalJoinedGroups['group_count']);
				$intTotalPendingGroups      = $this->getUserGroupTable()->fetchAllUserGroupCount( $userinfo->user_id, $identity->user_id,'pending',$profile_type);
				$viewModel->setVariable( 'pending_group_count' , $intTotalPendingGroups['group_count']);
				$mutual_count = 0;
				if($userinfo->user_id!= $identity->user_id){
					$intTotalMutualGroups      = $this->getUserGroupTable()->fetchAllUserGroupCount( $userinfo->user_id,$identity->user_id,'mutual',$profile_type);
					$mutual_count =  $intTotalMutualGroups['group_count'];					
				}
				$viewModel->setVariable( 'mutual_count' , $mutual_count);
				return $viewModel; 
			}else{
				$error = "User not exist in the system";
				$result = new ViewModel(array('error'=>$error));		
				return $result;
			}
		}else{return $this->redirect()->toRoute('home', array('action' => 'index'));}
	}
	public function creategroupAction(){
		$error = '';
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$myinfo = $this->getUserTable()->getUser($identity->user_id);
			if(!empty($myinfo)&&$myinfo->user_id){
				$request   = $this->getRequest();
				if ($request->isPost()){					 
					$objGroup = new Groups();
					$objGroup->strGroupName = trim($this->getRequest()->getPost('strGroupName'));
					$objGroup->intCountryId = trim($this->getRequest()->getPost('intCountryId'));
					$objGroup->intCityId = trim($this->getRequest()->getPost('intCityId'));
					$objGroup->strDesp = trim($this->getRequest()->getPost('strDesp'));
					$intGroupType = trim($this->getRequest()->getPost('intGroupType')); 
					 switch ($intGroupType){
					  case 1:
					  $objGroup->intGroupType = 'open';
					  break;
					  case 2:
					  $objGroup->intGroupType = 'public';
					  break;
					  case 3:
					  $objGroup->intGroupType = 'private';
					  break;
					  default:
					   $objGroup->intGroupType = 'open';
					 }
					$arrgrouptags = explode(',',$this->getRequest()->getPost('addedtags'));
					$arrQuestions       = explode(',',$this->getRequest()->getPost('QuestionDetails'));
                    $arrAddedFriends    = explode(',',$this->getRequest()->getPost('addedFriends'));
					$que = 1;
                   // echo count($arrQuestions);
                   // print_r($arrQuestions);
                    if($arrQuestions[0] != ''){
                        foreach($arrQuestions as $question){
                              $arrQuestionDetail  = explode('##',$question);
                              if($arrQuestionDetail[1] == ''){
                                  $erro_count++;
                                  $error = "'Please enter question ".$que."";
                                  break;
                              }else{
                              if($arrQuestionDetail[0] != 'Textarea'){

                                    if($arrQuestionDetail[2] == '' && $arrQuestionDetail[3] == '' && $arrQuestionDetail[4] != ''){
                                        $erro_count++;
                                        $error = "Please enter atleast two options for question ".$que."";
                                        break;
                                    }
                                    else if($arrQuestionDetail[2] != '' && $arrQuestionDetail[3] == '' && $arrQuestionDetail[4] == ''){
                                        $erro_count++;
                                        $error = "Please enter atleast two options for question ".$que."";
                                        break;
                                    }
                                    else if($arrQuestionDetail[2] == '' && $arrQuestionDetail[3] != '' && $arrQuestionDetail[4] == ''){
                                        $erro_count++;
                                        $error = "Please enter atleast two options for question ".$que."";
                                        break;
                                    }
                                    else if($arrQuestionDetail[2] == '' && $arrQuestionDetail[3] == '' && $arrQuestionDetail[4] == ''){
                                        $erro_count++;
                                        $error = "Please enter atleast two options for question ".$que."";
                                        break;
                                    }
                                }// else
                              } // if
                              $que++;
                         } // foreach
                    }// if					
					$erro_count = 0;
					$erro_count =($objGroup->strGroupName == '')?$erro_count++:$erro_count;
					$erro_count =($objGroup->strDesp == '')?$erro_count++:$erro_count;
					$erro_count =(count($arrgrouptags)<=0)?$erro_count++:$erro_count;
					if(count($arrgrouptags)<=0){
						$erro_count++;
						$error = "Please select atleast one intrest";
					}
					if($objGroup->strDesp == ''){
						$erro_count++;
						$error = "Group description is required";
					}
					if($objGroup->strGroupName == ''){
						$erro_count++;
						$error = "Group Title is required";
					}					
					$group_info = $this->getGroupTable()->getGroupByName($objGroup->strGroupName);
					if(!empty($group_info)&& $group_info->group_id!=''){
						$erro_count++;
						$error = "Group name already exist";
					}
					if($erro_count == 0){
						$objGroup->group_seo_title = $this->creatSeotitle($objGroup->strGroupName);
						$intGroupId = $this->getGroupTable()->saveGroupBasicDetails($objGroup, '');
						
						if($intGroupId){
							$userGroup = new UserGroup();
							$userGroup->user_group_user_id              = $identity->user_id;
							$userGroup->user_group_group_id             = $intGroupId;
							$userGroup->user_group_added_timestamp      = '';
							$userGroup->user_group_added_ip_address     = $_SERVER["SERVER_ADDR"];
							$userGroup->user_group_status               = 'available';
							$userGroup->user_group_is_owner             = 1;
							$intU_GroupId                               = $this->getUserGroupTable()->saveUserGroup($userGroup);
							if(isset($_FILES)&&isset($_FILES['groupImage']['name'])&&$_FILES['groupImage']['name']!=''){ 
								$config = $this->getServiceLocator()->get('Config');
								$options['script_url']          = $config['pathInfo']['base_url'];
								$options['upload_dir']          = $config['pathInfo']['group_img_path'].$intGroupId."/";
								$options['upload_url']          = $config['pathInfo']['group_img_path_absolute_path'].$intGroupId."/";
								$options['param_name']          = 'groupImage';
								$options['min_width']           = 200;
								$options['min_height']          = 100;

								// object of file upload plug in which is used for simple upload as well as drag and drop upload functionality
								$upload_handler = new UploadHandler($options); 
								if(isset($upload_handler->image_objects['filename'])&&$upload_handler->image_objects['filename']!=''){
									$groupphoto  = new GroupPhoto();
									$groupphoto->group_photo_group_id  = $intGroupId;
									$groupphoto->group_photo_photo = $upload_handler->image_objects['filename'];
									$intGroupPhotoId  = $this->getGroupPhotoTable()->savePhoto($groupphoto);
								}
							}
							$grouptag   = new GroupTag();
							foreach($arrgrouptags as $group_tag){
								$grouptag->group_tag_group_id           = $intGroupId;
								$grouptag->group_tag_added_ip_address   = $_SERVER["SERVER_ADDR"];
								$grouptag->group_tag_tag_id             = $group_tag;
								$intGrouptagId                          = $this->getGroupTagTable()->saveGroupTag($grouptag);
							}
							$UserGroupJoiningInvitation                 = new UserGroupJoiningInvitation();
                            if($arrAddedFriends[0] != ''){
                                foreach($arrAddedFriends as $group_invt){
                                    $UserGroupJoiningInvitation->user_group_joining_invitation_sender_user_id           = $identity->user_id;
                                    $UserGroupJoiningInvitation->user_group_joining_invitation_receiver_id              = $group_invt;
                                    $UserGroupJoiningInvitation->user_group_joining_invitation_status                   = "active";
                                    $UserGroupJoiningInvitation->user_group_joining_invitation_ip_address               = $_SERVER["SERVER_ADDR"];
                                    $UserGroupJoiningInvitation->user_group_joining_invitation_group_id                 = $intGroupId;
                                    $intUserGroupJoiningInvitation   = $this->getGroupJoiningInvitationTable()->saveUserGroupJoiningInvite($UserGroupJoiningInvitation);
									if( $intUserGroupJoiningInvitation){
										$config = $this->getServiceLocator()->get('Config');
										$base_url = $config['pathInfo']['base_url'];
										$msg = $identity->user_given_name." invited you to join the group ".$objGroup->strGroupName;
										$subject = 'Group joining invitation';
										$from = 'admin@jeera.com';
										$process = 'Invite';
										$this->UpdateNotifications($group_invt,$msg,3,$subject,$from,$identity->user_id,$intGroupId,$process);
									}
                                }
                            }

                           // adding the question
                           if($arrQuestions[0] != ''){
                                foreach($arrQuestions as $question){
									$arrQuestionDetail = array();
                                    $arrQuestionDetail  = explode('##',$question);
									$answer_type = 'Textarea';
									$answer_type =($arrQuestionDetail[0] == 'CheckBox')?'checkbox':$answer_type;
									$answer_type =($arrQuestionDetail[0] == 'Radiobutton')?'radio':$answer_type;
                                    $addedQuestion      = array(
                                            'group_id'            => $intGroupId,
                                            'question'            => $arrQuestionDetail[1],
                                            'question_status'     => 'active',
                                            'added_ip'            => $_SERVER["SERVER_ADDR"],
                                            'added_user_id'       => $identity->user_id,
                                            'answer_type'         => $answer_type
                                       );
                                    // save question
                                    $intQuestionId                      = $this->getGroupJoiningQuestionnaireTable()->AddQuestion($addedQuestion);

                                    for($o=2; $o<=4; $o++){
                                        if($arrQuestionDetail[$o]!= ''){
                                            //getGroupQuestionnaireOptionsTable
                                            $addedOption    = array(
                                                'question_id'   => $intQuestionId,
                                                'option'        => $arrQuestionDetail[$o]
                                            );
                                            $intQOptionId                      = $this->getGroupQuestionnaireOptionsTable()->AddOptions($addedOption);
                                        }
                                    }// for
                                }// foreach
                           }//if
						}else{$error = "Some error occured. Please try again";}
					}
				}else{$error = "Unable to process";}
			}else{$error = "User not exist in the system";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;		 
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}
	public function creatSeotitle($planet_name){
		$string = trim($planet_name);		
		$string = preg_replace('/(\W\B)/', '',  $string);		
		$string = preg_replace('/[\W]+/',  '_', $string);		
		$string = str_replace('-', '_', $string);
		if(!$this->checkSeotitleExist($string)){
			return $string; 
		}
		$length = 5;
		$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
		$string = strtolower($string).'_'.$randomString;
		if(!$this->checkSeotitleExist($string)){
			return $string; 
		}		
		$string = strtolower($string).'_'.time();
		return $string; 
	}
	public function checkSeotitleExist($seo_title){		 
		if($this->getGroupTable()->checkSeotitleExist($seo_title)){
			return true;				
		}
		else{
			return false;
		}
	}
	public function ajaxAddMediaAction(){
		$error = '';
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$myinfo = $this->getUserTable()->getUser($identity->user_id);
			if(!empty($myinfo)&&$myinfo->user_id){
				$request   = $this->getRequest();
				if ($request->isPost()){
					$post = $request->getPost();
					$error =($post['media_type']=='')? "Media type required":$error;					 
					$error =($post['group_id']=='')? "Select one group":$error;	 
					$group  = $this->getGroupTable()->getPlanetinfo($post['group_id']);
					$error =(empty($group)||$group->group_id=='')?"Given group not exist in this system":$error;					 
					$media_type = $post['media_type'];
					switch($media_type){
						case 'image':
							if(isset($_FILES)&&isset($_FILES['mediaImage']['name'])&&$_FILES['mediaImage']['name']!=''){
								$config = $this->getServiceLocator()->get('Config');
								$options['script_url']          = $config['pathInfo']['base_url'];
								$options['upload_dir']          = $config['pathInfo']['group_img_path'].$group->group_id."/media/";
								$options['upload_url']          = $config['pathInfo']['group_img_path_absolute_path'].$group->group_id."/media/";
								$options['param_name']          = 'mediaImage';
								$options['min_width']           = 50;
								$options['min_height']          = 50;
								if(!is_dir($config['pathInfo']['group_img_path'].$group->group_id)){							
									mkdir($config['pathInfo']['group_img_path'].$group->group_id);
								}
								if(!is_dir($config['pathInfo']['group_img_path'].$group->group_id."/media/")){							
									mkdir($config['pathInfo']['group_img_path'].$group->group_id."/media/");
								} 
								$upload_handler = new UploadHandler($options); 
								if(isset($upload_handler->image_objects['filename'])&&$upload_handler->image_objects['filename']!=''){
									if($error==''){
										$objGroupMedia = new GroupMedia();
										$objGroupMedia->media_added_user_id = $identity->user_id;
										$objGroupMedia->media_added_group_id = $post['group_id'];
										$objGroupMedia->media_type = 'image';
										$objGroupMedia->media_content = $upload_handler->image_objects['filename'];
										$objGroupMedia->media_caption = ($post['imageCaption']!='undefined')?$post['imageCaption']:'';
										$objGroupMedia->media_status = 'active';
										$addeditem = $this->getGroupMediaTable()->saveGroupMedia($objGroupMedia);
										if($addeditem){
											$joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($group->group_id); 
											foreach($joinedMembers as $members){ 
												if($members->user_group_user_id!=$identity->user_id){
													$config = $this->getServiceLocator()->get('Config');
													$base_url = $config['pathInfo']['base_url'];
													$msg = $identity->user_given_name." added a new status under the group ".$group->group_title;
													$subject = 'New status added';
													$from = 'admin@jeera.com';
													$process = 'New Media';
													$this->UpdateNotifications($members->user_group_user_id,$msg,8,$subject,$from,$identity->user_id,$addeditem,$process);
												}
											}
										}else{$error = "Some error occcured. Please try again"; }
									}
								}else{ $error = "Some error occured in file uplading"; }
							}else{$error = "Select one image to upload";}
						break;
						case 'video':
							$error =($post['mediaVideo']=='')? "Add video to upload":$error;
							if($error==''){
								$objGroupMedia = new GroupMedia();
								$objGroupMedia->media_added_user_id = $identity->user_id;
								$objGroupMedia->media_added_group_id = $post['group_id'];
								$objGroupMedia->media_type = 'video';
								$objGroupMedia->media_content = $post['mediaVideo'];
								$objGroupMedia->media_caption = ($post['videoCaption']!='undefined')?$post['videoCaption']:'';
								$objGroupMedia->media_status = 'active';
								$addeditem = $this->getGroupMediaTable()->saveGroupMedia($objGroupMedia);
								if($addeditem){
									$joinedMembers =$this->getUserGroupTable()->getAllGroupMembers($group->group_id); 
									foreach($joinedMembers as $members){ 
										if($members->user_group_user_id!=$identity->user_id){
											$config = $this->getServiceLocator()->get('Config');
											$base_url = $config['pathInfo']['base_url'];
											$msg = $identity->user_given_name." added a new status under the group ".$group->group_title;
											$subject = 'New status added';
											$from = 'admin@jeera.com';
											$process = 'New Media';
											$this->UpdateNotifications($members->user_group_user_id,$msg,8,$subject,$from,$identity->user_id,$addeditem,$process);
										}
									}
								}else{$error = "Some error occcured. Please try again"; }
							}
						break;
					}				 
				}else{$error = "Unable to process";}
			}else{$error = "User not exist in the system";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;		 
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}	
	public function getAllActiveMembersExceptMeAction(){
		$error = '';
		$auth = new AuthenticationService();
		$allActiveMembers = array();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$myinfo = $this->getUserTable()->getUser($identity->user_id);
			if(!empty($myinfo)&&$myinfo->user_id){
				$request   = $this->getRequest();
				if ($request->isPost()){
					$post = $request->getPost();
					$error =($post['group_id']=='')? "Select one group":$error;	 
					$group  = $this->getGroupTable()->getPlanetinfo($post['group_id']);
					$error =(empty($group)||$group->group_id=='')?"Given group not exist in this system":$error;	
					$allActiveMembers = $this->getUserGroupTable()->getAllActiveMembersExceptMeAction($group->group_id,$identity->user_id);
				}else{$error = "Unable to process";}
			}else{$error = "User not exist in the system";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	
		$return_array['members'] = $allActiveMembers;		
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}
	public function getUserFriendsAction(){

        $error          = '';
		$auth           = new AuthenticationService();
		$identity       = $auth->getIdentity();

        $friendsList    = $this->getUserFriendTable()->fetchAllUserFriend($identity->user_id);
        //print_r($friendsList->toArray());
        $result = new JsonModel(array( 'friendsList' => $friendsList->toArray()));
		return $result;
    }
	public function getMediaAction(){
		$error = '';
		$auth = new AuthenticationService();
		$arr_group_media = array();
		$allActiveMembers = array();
		$comments = array();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$request   = $this->getRequest();
			if ($request->isPost()){ 
				$post = $request->getPost();
				$media_id = $post['media_id'];
				if($media_id){
					$group_media = $this->getGroupMediaTable()->getOneMedia($media_id);
					$is_admin = 0;
					if($this->getUserGroupTable()->checkOwner($group_media->group_id,$group_media->user_id)){
						$is_admin = 1;
					}
					$arr_group_media = array();
					if(!empty($group_media)&&$group_media->group_media_id!=''){
						$SystemTypeData = $this->getGroupTable()->fetchSystemType("Media");
						$like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$media_id,$identity->user_id);
						$like_count = $like_details->likes_counts;		
						$arr_likedUsers = array();						
						if(!empty($like_details)){  
							$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$media_id,$identity->user_id,2,0);
							$arr_likedUsers = array();
							if($like_details['is_liked']==1){
								$arr_likedUsers[] = 'you';
							}
							if($like_details['likes_counts']>0&&!empty($liked_users)){
								foreach($liked_users as $likeuser){
									$arr_likedUsers[] = $likeuser['user_given_name'];
								}
							}
							 
						}	
						$commet_users = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$media_id,$identity->user_id);
						$next_item = $this->getGroupMediaTable()->getNextMedia($group_media->group_id,$media_id);
						$prev_item = $this->getGroupMediaTable()->getPreviousMedia($group_media->group_id,$media_id);
						$arr_group_media = array(
										'media_type' => $group_media->media_type,
										'group_media_id' => $group_media->group_media_id,
										'media_content' => $group_media->media_content,
										'media_caption' => $group_media->media_caption,
										'added_time' =>$this->timeAgo($group_media->media_added_date),
										'group_id' => $group_media->group_id,
										'group_title' => $group_media->group_title,
										'group_seo_title' => $group_media->group_seo_title,
										'user_id' => $group_media->user_id,
										'user_given_name' => $group_media->user_given_name,
										'user_first_name' => $group_media->user_first_name,
										'user_last_name' => $group_media->user_last_name,
										'user_profile_name' => $group_media->user_profile_name,
										'user_fbid' => $group_media->user_fbid,
										'profile_photo' => $group_media->profile_photo,
										'likedUsers' => $arr_likedUsers,
										'likes_counts' =>$like_details['likes_counts'],
										'is_liked' =>$like_details['is_liked'],
										'comment_count' =>$commet_users['comment_counts'],
										'is_commented' =>$commet_users['is_commented'],
										'next_id' =>(isset($next_item->group_media_id))?$next_item->group_media_id:'',
										'prev_id' =>(isset($prev_item->group_media_id))?$prev_item->group_media_id:'',
										'is_admin'=>$is_admin,
										);
						$commentSystemTYpe =$this->getGroupTable()->fetchSystemType('Comment'); 
						$comments_details = $this->getCommentTable()->getAllCommentsWithLike($SystemTypeData->system_type_id,$commentSystemTYpe->system_type_id,$media_id,$identity->user_id,10,0);
						if(!empty($comments_details)){
							foreach($comments_details as $list){
								 
								$arr_likedUsers = array();
								$like_details = $this->getLikeTable()->fetchLikesCountByReference($commentSystemTYpe->system_type_id,$list->comment_id,$identity->user_id);
								if(!empty($like_details)){  
									$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($commentSystemTYpe->system_type_id,$list->comment_id,$identity->user_id,2,0);
									$arr_likedUsers = array();
									if($like_details['is_liked']==1){
										$arr_likedUsers[] = 'you';
									}
									if($like_details['likes_counts']>0&&!empty($liked_users)){
										foreach($liked_users as $likeuser){
											$arr_likedUsers[] = $likeuser['user_given_name'];
										}
									}
									 
								}
								$comments[] = array(
												'likes_count'=>$like_details['likes_counts'],
												'islike'=>$list->islike,
												'comment_content'=>$list->comment_content,
												'comment_id'=>$list->comment_id,
												'comment_time'=>$this->timeAgo($list->comment_added_timestamp),
												'user_given_name'=>$list->user_given_name,
												'user_id'=>$list->user_id,
												'user_register_type'=>$list->user_register_type,
												'user_fbid'=>$list->user_fbid,
												'profile_photo'=>$list->profile_photo,
												'likedUsers'=>$arr_likedUsers,
												'user_profile_name'=>$list->user_profile_name
											);
							}
						}
					}						
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	
		$return_array['group_media'] = $arr_group_media;
		$return_array['comments'] = $comments;		
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}
	public function timeAgo($time_ago){
		$time_ago = strtotime($time_ago);
		$cur_time   = time();
		$time_elapsed   = $cur_time - $time_ago;
		$seconds    = $time_elapsed ;
		$minutes    = round($time_elapsed / 60 );
		$hours      = round($time_elapsed / 3600);
		$days       = round($time_elapsed / 86400 );
		$weeks      = round($time_elapsed / 604800);
		$months     = round($time_elapsed / 2600640 );
		$years      = round($time_elapsed / 31207680 );
		// Seconds
		if($seconds <= 60){
			return "just now";
		}
		//Minutes
		else if($minutes <=60){
			if($minutes==1){
				return "one minute ago";
			}
			else{
				return "$minutes minutes ago";
			}
		}
		//Hours
		else if($hours <=24){
			if($hours==1){
				return "an hour ago";
			}else{
				return "$hours hrs ago";
			}
		}
		//Days
		else if($days <= 7){
			if($days==1){
				return "yesterday";
			}else{
				return "$days days ago";
			}
		}
		//Weeks
		else if($weeks <= 4.3){
			if($weeks==1){
				return "a week ago";
			}else{
				return "$weeks weeks ago";
			}
		}
		//Months
		else if($months <=12){
			if($months==1){
				return "a month ago";
			}else{
				return "$months months ago";
			}
		}
		//Years
		else{
			if($years==1){
				return "one year ago";
			}else{
				return "$years years ago";
			}
		}
	}
	public function matchedgroupsWithInterestsAction(){
		$error = '';
		$auth = new AuthenticationService();		  
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$request   = $this->getRequest();
			if ($request->isPost()){ 
				$post = $request->getPost();
				$city = (isset($post['city'])&&$post['city']!=null&&$post['city']!=''&&$post['city']!='undefined')?$post['city']:'';
				$country = (isset($post['country'])&&$post['country']!=null&&$post['country']!=''&&$post['country']!='undefined')?$post['country']:'';	
				$category = (isset($post['categories'])&&$post['categories']!=null&&$post['categories']!=''&&$post['categories']!='undefined')?$post['categories']:'';
				$page = (isset($post['page'])&&$post['page']!=null&&$post['page']!=''&&$post['page']!='undefined')?$post['page']:1;
				
				$arr_group_list = '';
				$limit =10;
				$page =($page>0)?$page-1:0;
				$offset = $page*$limit;
				$groups = $this->getUserGroupTable()->getmatchGroupsByuserTags($identity->user_id,$city,$country,$category,$limit,$offset);
				if(!empty($groups)){
					foreach($groups as $list){
						$tag_category = $this->getGroupTagTable()->getAllGroupTagCategiry($list['group_id']);
						$tags = $this->getGroupTagTable()->fetchAllGroupTags($list['group_id']);
						$is_requested = 0;
						$requestedHystory = $this->getUserGroupJoiningRequestTable()->checkActiveRequestExist($list['group_id'],$identity->user_id);
						if(!empty($requestedHystory)&&$requestedHystory->user_group_joining_request_id!=''){
							$is_requested = 1;
						}
						$is_invited = 0;
						$invitedHystory = $this->getGroupJoiningInvitationTable()->checkInvited($identity->user_id,$list['group_id']);
						if(!empty($invitedHystory)&&$invitedHystory->user_group_joining_invitation_id!=''){
							$is_invited = 1;
						}
						$arr_group_list[] = array(
										'group_id' =>$list['group_id'],
										'group_title' =>$list['group_title'],
										'group_seo_title' =>$list['group_seo_title'],
										'group_type' =>$list['group_type'],
										'group_photo_photo' =>$list['group_photo_photo'],										 
										'country_title' =>$list['country_title'],
										'country_code' =>$list['country_code'],
										'member_count' =>$list['member_count'],
										'friend_count' =>$list['friend_count'],
										'city' =>$list['city'],	
										'tag_category_count' =>count($tag_category),
										'tag_category' =>$tag_category,
										'tags' =>$tags,
										'is_requested'=>$is_requested,
										'is_invited'=>$is_invited,
										);
					}
				}
			}else{$error = "Unable to process";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	
		$return_array['groups'] = $arr_group_list;
		 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}
	 public function grouplistAction(){
        $error = '';
		$auth = new AuthenticationService();	
		$arr_group_list = '';
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$request   = $this->getRequest();
			if($request->isPost()){ 
                $profilename            = $this->getRequest()->getPost('profilename');
                $strType                = $this->getRequest()->getPost('type');
                $page                  = $this->getRequest()->getPost('page');
                $limit =10;
				$page =($page>0)?$page-1:0;
				$offset = $page*$limit;
				$userinfo = $this->getUserTable()->getUserByProfilename($profilename);
				$myinfo               = $this->getUserTable()->getUser($identity->user_id);
				if(!empty($userinfo)&&$userinfo->user_id&&!empty($myinfo)&&$myinfo->user_id){
					$profile_type='mine' ;
					if($userinfo->user_id!=$identity->user_id){
						$profile_type='others';
					}
					$intTotalGroups     = $this->getUserGroupTable()->fetchAllUserGroupCount($userinfo->user_id,$identity->user_id,$strType,$profile_type);
					if($intTotalGroups['group_count'] > 0){                            
						$arrGroups      = $this->getUserGroupTable()->fetchUserGroupList($userinfo->user_id,$identity->user_id,$strType,$profile_type,$limit,$offset );
						$group_list = array();
						if(!empty($arrGroups)){
							foreach($arrGroups as $list){
								$tag_category = $this->getGroupTagTable()->getAllGroupTagCategiry($list['group_id']);
								$tags = $this->getGroupTagTable()->fetchAllGroupTags($list['group_id']);
								$request_count =0;
								if($list['is_admin']){
									$request_count = $this->getUserGroupJoiningRequestTable()->countGroupMemberRequests($list['group_id'])->memberCount;
								}
								$is_requested = 0;
								$requestedHystory = $this->getUserGroupJoiningRequestTable()->checkActiveRequestExist($list['group_id'],$identity->user_id);
								if(!empty($requestedHystory)&&$requestedHystory->user_group_joining_request_id!=''){
									$is_requested = 1;
								}
								$is_invited = 0;
								$invitedHystory = $this->getGroupJoiningInvitationTable()->checkInvited($identity->user_id,$list['group_id']);
								if(!empty($invitedHystory)&&$invitedHystory->user_group_joining_invitation_id!=''){
									$is_invited = 1;
								}
								$arr_group_list[] = array(
									'group_id' =>$list['group_id'],
									'group_title' =>$list['group_title'],
									'group_seo_title' =>$list['group_seo_title'],
									'group_type' =>$list['group_type'],
									'group_status' =>$list['group_status'],
									'group_photo_photo' =>$list['group_photo_photo'],										 
									'country_title' =>$list['country_title'],
									'country_code' =>$list['country_code'],
									'member_count' =>$list['member_count'],
									'friend_count' =>$list['friend_count'],
									'city' =>$list['city'],	
									'is_admin' =>$list['is_admin'],	
									'is_member' =>$list['is_member'],	
									'tag_category_count' =>count($tag_category),
									'tag_category' =>$tag_category,
									'request_count' =>$request_count,
									'is_requested'=>$is_requested,
									'is_invited'=>$is_invited,
									'tags' =>$tags,
								);
							}
						}
					}else{
						$error      = "No Record Found.";							 
					}         
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	
		$return_array['groups'] = $arr_group_list;
		 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
    }
	public function leavegroupAction(){
		$error = '';
		$auth = new AuthenticationService();		 
		if ($auth->hasIdentity()) {
			$request   = $this->getRequest();
			$identity  = $auth->getIdentity();
            if ($request->isPost()){                   
                 $intGroupId = $this->getRequest()->getPost('groupId'); 
                 $userinfo   = $this->getUserTable()->getUser($identity->user_id);
				 if(!empty($userinfo)&&$userinfo->user_id){					 
					$arrUserGroup      = $this->getUserGroupTable()->getUserGroup($identity->user_id, $intGroupId);
					if(count($arrUserGroup) > 0){					    
					    if($this->getUserGroupTable()->deleteOneUserGroup($intGroupId, $identity->user_id)){
						   $this->getGroupQuestionnaireAnswersTable()->deleteUserAnswersOfGroup($intGroupId,$identity->user_id);
						}else{	$error = "Some error occured. Please try again"; }			   
				   }else{$error = "Group not exist in the system";}
				}else{$error = "User not exist in the system";}              
            }else{$error = "Unable to process";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;		 		 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}
	public function getQuestionnaireAction(){
        $error = '';
		$auth = new AuthenticationService();		 
		$questionnaire = array();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$request   = $this->getRequest();
			if($request->isPost()){                 
                $group_id                  = $this->getRequest()->getPost('group_id');
				$group_info = $this->getGroupTable()->getPlanetinfo($group_id);
				
                if(!empty($group_info)&&$group_info->group_id!=''){
					$questionnaire_list = $this->getGroupJoiningQuestionnaireTable()->getQuestionnaire($group_id);
					if(!empty($questionnaire_list)){
						
						foreach($questionnaire_list as $list){
							$options = array();
							if($list->answer_type == 'radio'||$list->answer_type == 'checkbox'){
								$options =$this->getGroupQuestionnaireOptionsTable()->getoptionOfOneQuestion($list->questionnaire_id);
							}
							$questionnaire[] = array(
									'questionnaire_id'=>$list->questionnaire_id,
									'question'=>$list->question,
									'answer_type'=>$list->answer_type,
									'options'=>$options,
								);
						}
					}
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	
		$return_array['questionnaire'] = $questionnaire;
		 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
    }
	public function saveUserQuestionnaireAction(){
		$error = '';
		$auth = new AuthenticationService();		 
		$questionnaire = array();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$request   = $this->getRequest();
			if($request->isPost()){
				$questionanswers = $this->getRequest()->getPost('questionanswers'); 
                $group_id  = $this->getRequest()->getPost('group_id');
				$group_info = $this->getGroupTable()->getPlanetinfo($group_id);				
                if(!empty($group_info)&&$group_info->group_id!=''){					 
					if(!empty($questionanswers)){						
						foreach($questionanswers as $list){
							$question = $this->getGroupJoiningQuestionnaireTable()->getQuestionFromQuestionId($list['question_id']);
							if(!empty($question)){ 
								if($question->answer_type == 'radio'||$question->answer_type == 'checkbox'){
									$data['group_id'] = $group_id;
									$data['question_id'] = $list['question_id'];
									$data['selected_options'] = $list['answer'];
									$data['added_user_id'] = $identity->user_id;
								}else{
									$data['group_id'] = $group_id;
									$data['question_id'] = $list['question_id'];
									$data['answer'] = $list['answer'];
									$data['added_user_id'] = $identity->user_id;
								}
								$this->getGroupQuestionnaireAnswersTable()->AddAnswer($data);
							}
						}
					}
					if($group_info->group_type == 'open'){
						$usergroup = $this->getUserGroupTable()->getUserGroup($identity->user_id,$group_id);
						if(empty($usergroup)){
							$user_data['user_group_user_id'] = $identity->user_id;
							$user_data['user_group_group_id'] = $group_id;
							$user_data['user_group_status'] = "available";							 
							$this->getUserGroupTable()->AddMembersTOGroup($user_data);
							$config = $this->getServiceLocator()->get('Config');
							$base_url = $config['pathInfo']['base_url'];
							$msg = $identity->user_given_name." Joined in the group ".$group_info->group_title;
							$subject = 'Group joining Request';
							$from = 'admin@jeera.com';
							$process = 'Joined';
							$admin_users = $this->getUserGroupTable()->getAllAdminUsers($group_id);
							foreach($admin_users as $admins){
								if($identity->user_id!=$admins->user_group_user_id){
								$this->UpdateNotifications($admins->user_group_user_id,$msg,4,$subject,$from,$identity->user_id,$group_id,$process);
								}
							}
						}
					}
					if($group_info->group_type == 'public'){ 
						$usergroup = $this->getUserGroupTable()->getUserGroup($identity->user_id,$group_id);
						if(empty($usergroup)){
							$user_data['user_group_joining_request_user_id'] = $identity->user_id;
							$user_data['user_group_joining_request_group_id'] = $group_id;
							$user_data['user_group_joining_request_status'] = "active"; 
							$this->getUserGroupJoiningRequestTable()->AddRequestTOGroup($user_data);
							$config = $this->getServiceLocator()->get('Config');
							$base_url = $config['pathInfo']['base_url'];
							$msg = $identity->user_given_name." requested to join in the group ".$group_info->group_title;
							$subject = 'Group joining Request';
							$from = 'admin@jeera.com';
							$process = 'Requested';
							$admin_users = $this->getUserGroupTable()->getAllAdminUsers($group_id);
							 
							foreach($admin_users as $admins){ 
								if($identity->user_id!=$admins->user_group_user_id){
								$this->UpdateNotifications($admins->user_group_user_id,$msg,4,$subject,$from,$identity->user_id,$group_id,$process);
								}
							} 
						}
					}
					if($group_info->group_type == 'private'){ 
						$invitedHystory = $this->getGroupJoiningInvitationTable()->checkInvited($identity->user_id,$group_id);	
						$usergroup = $this->getUserGroupTable()->getUserGroup($identity->user_id,$group_id);
						if(empty($usergroup)&&!empty($invitedHystory)&&$invitedHystory->user_group_joining_invitation_id!=''){ 
							$user_data['user_group_user_id'] = $identity->user_id;
							$user_data['user_group_group_id'] = $group_id;
							$user_data['user_group_status'] = "available";							 				 
							if($this->getUserGroupTable()->AddMembersTOGroup($user_data)){
								$this->getGroupJoiningInvitationTable()->ChangeStatusTOProcessed($invitedHystory->user_group_joining_invitation_id);
								$config = $this->getServiceLocator()->get('Config');
								$base_url = $config['pathInfo']['base_url'];
								$msg = $identity->user_given_name." Joined in the group ".$group_info->group_title;
								$subject = 'Group joining Request';
								$from = 'admin@jeera.com';
								$process = 'Joined';
								$admin_users = $this->getUserGroupTable()->getAllAdminUsers($group_id);
								foreach($admin_users as $admins){
									if($identity->user_id!=$admins->user_group_user_id){
									$this->UpdateNotifications($admins->user_group_user_id,$msg,4,$subject,$from,$identity->user_id,$group_id,$process);
									}
								}
							}							
						}
					}
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	
		$return_array['questionnaire'] = $questionnaire;
		 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}
	
	public function joinGroupAction(){
		$error = '';
		$auth = new AuthenticationService();		 
		$questionnaire = array();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$request   = $this->getRequest();
			if($request->isPost()){				 
                $group_id  = $this->getRequest()->getPost('group_id');
				$group_info = $this->getGroupTable()->getPlanetinfo($group_id);	 
                if(!empty($group_info)&&$group_info->group_id!=''){				 
					if($group_info->group_type == 'open'){
						$usergroup = $this->getUserGroupTable()->getUserGroup($identity->user_id,$group_id);
						if(empty($usergroup)){
							$user_data['user_group_user_id'] = $identity->user_id;
							$user_data['user_group_group_id'] = $group_id;
							$user_data['user_group_status'] = "available";							 
							$this->getUserGroupTable()->AddMembersTOGroup($user_data);
							$config = $this->getServiceLocator()->get('Config');
							$base_url = $config['pathInfo']['base_url'];
							$msg = $identity->user_given_name." Joined in the group ".$group_info->group_title;
							$subject = 'Group joining Request';
							$from = 'admin@jeera.com';
							$process = 'Joined';
							$admin_users = $this->getUserGroupTable()->getAllAdminUsers($group_id);
							foreach($admin_users as $admins){
								if($identity->user_id!=$admins->user_group_user_id){
								$this->UpdateNotifications($admins->user_group_user_id,$msg,4,$subject,$from,$identity->user_id,$group_id,$process);
								}
							}
						}
					}
					if($group_info->group_type == 'public'){ 
						$usergroup = $this->getUserGroupTable()->getUserGroup($identity->user_id,$group_id);
						if(empty($usergroup)){ 
							$user_data['user_group_joining_request_user_id'] = $identity->user_id;
							$user_data['user_group_joining_request_group_id'] = $group_id;
							$user_data['user_group_joining_request_status'] = "active";							 
							$this->getUserGroupJoiningRequestTable()->AddRequestTOGroup($user_data);
							$config = $this->getServiceLocator()->get('Config');
							$base_url = $config['pathInfo']['base_url'];
							$msg = $identity->user_given_name." requested to join in the group ".$group_info->group_title;
							$subject = 'Group joining Request';
							$from = 'admin@jeera.com';
							$process = 'Requested';
							$admin_users = $this->getUserGroupTable()->getAllAdminUsers($group_id);
							//print_r($admin_users);die();
							foreach($admin_users as $admins){
								if($identity->user_id!=$admins->user_group_user_id){
								$this->UpdateNotifications($admins->user_group_user_id,$msg,4,$subject,$from,$identity->user_id,$group_id,$process);
								}
							}
						}
					}
					if($group_info->group_type == 'private'){
						$invitedHystory = $this->getGroupJoiningInvitationTable()->checkInvited($identity->user_id,$group_id);	
						$usergroup = $this->getUserGroupTable()->getUserGroup($identity->user_id,$group_id);
						if(empty($usergroup)&&!empty($invitedHystory)&&$invitedHystory->user_group_joining_invitation_id!=''){ 
							$user_data['user_group_user_id'] = $identity->user_id;
							$user_data['user_group_group_id'] = $group_id;
							$user_data['user_group_status'] = "available";							 				 
							if($this->getUserGroupTable()->AddMembersTOGroup($user_data)){
								$this->getGroupJoiningInvitationTable()->ChangeStatusTOProcessed($invitedHystory->user_group_joining_invitation_id);
								$config = $this->getServiceLocator()->get('Config');
								$base_url = $config['pathInfo']['base_url'];
								$msg = $identity->user_given_name." Joined in the group ".$group_info->group_title;
								$subject = 'Group joining Request';
								$from = 'admin@jeera.com';
								$process = 'Joined';
								$admin_users = $this->getUserGroupTable()->getAllAdminUsers($group_id);
								foreach($admin_users as $admins){
									if($identity->user_id!=$admins->user_group_user_id){
									$this->UpdateNotifications($admins->user_group_user_id,$msg,4,$subject,$from,$identity->user_id,$group_id,$process);
									}
								}
							}
						}
					}
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	
		$return_array['questionnaire'] = $questionnaire;
		 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}
	public function getAllMediaAction(){
		$error = '';
		$auth = new AuthenticationService();
		$arr_group_media = array();
		$allActiveMembers = array();
		$comments = array();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$request   = $this->getRequest();
			if ($request->isPost()){ 
				$post = $request->getPost();
				$group_id = $post['group_id'];
				$page                  = $this->getRequest()->getPost('page');
                $limit =10;
				$page =($page>0)?$page-1:0;
				$offset = $page*$limit;				 
				if($group_id){
					$group_info = $this->getGroupTable()->getPlanetinfo($group_id);	
					if(!empty($group_info)&&$group_info->group_id!=''){	
						 $groupmedia = $this->getGroupMediaTable()->getAllMedia($group_id,$limit,$offset);
						 if(!empty($groupmedia)){
							 foreach($groupmedia as $media){
								$video_id ='';
								if($media['media_type']=='video'){
									$video_id = $this->getYoutubeIdFromUrl($media['media_content']);
								}
								$arr_group_media[] = array(
									'group_media_id'=>$media['group_media_id'],
									'media_type'=>$media['media_type'],
									'media_added_group_id'=>$media['media_added_group_id'],
									'media_content'=>$media['media_content'],
									'media_caption'=>$media['media_caption'],
									'media_added_date'=>$media['media_added_date'],
									'media_status'=>$media['media_status'],
									'video_id'=>$video_id,
								);
							 }
						 }						 
					}else{$error = "Group is not existing";}			
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	
		$return_array['group_media'] = $arr_group_media;
		$return_array['comments'] = $comments;		
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}
	public function getYoutubeIdFromUrl($url){
		preg_match("#([\/|\?|&]vi?[\/|=]|youtu\.be\/|embed\/)(\w+)#", $url, $matches);
		return(end($matches));
	}
	public function groupdetailsAction(){
		$error = '';
		$auth = new AuthenticationService();
		$viewModel = new ViewModel();
		$config = $this->getServiceLocator()->get('Config');
		$viewModel->setVariable('image_folders',$config['image_folders']);
		//$request   = $this->getRequest();
		$edit = $this->params()->fromQuery('edit');
		if ($auth->hasIdentity()) {
			$this->layout('layout/layout_user');
			$identity = $auth->getIdentity();
            $group_seo      = $this->params('group_seo');
			$profilepic = $this->getUserTable()->getUserProfilePic($identity->user_id);
			$pic = '';
			if(!empty($profilepic)&&$profilepic->biopic!='')
			$pic = $profilepic->biopic;
			$identity->profile_pic = $pic;			 
			$this->layout()->identity = $identity;
			$userinfo = $this->getUserTable()->getUser($identity->user_id);
            $viewModel->setVariable( 'current_Profile', $userinfo->user_profile_name);
			if(!empty($userinfo)&&$userinfo->user_id){
				$user_profileData = $this->getUserTable()->getProfileDetails($identity->user_id);
				$viewModel->setVariable( 'profilename', $userinfo->user_profile_name);
				$viewModel->setVariable( 'userinfo', $user_profileData);
				$arrGroup           = $this->getGroupTable()->getGroupBySeoTitle($group_seo);
                if(!empty($arrGroup)){  
					  $group_info = $this->getGroupTable()->getGroupDetails($arrGroup->group_id,$identity->user_id);
					  $arr_group_info = array();
					  if(!empty($group_info)){
						$tag_category = $this->getGroupTagTable()->getAllGroupTagCategiry($group_info->group_id);
						$tags = $this->getGroupTagTable()->fetchAllGroupTags($group_info->group_id);
						$viewModel->setVariable( 'enableEdit',0);
						$request_count = 0;
						if($group_info->is_admin){
							$request_count = $this->getUserGroupJoiningRequestTable()->countGroupMemberRequests($group_info->group_id)->memberCount;
							if(isset($edit)&&$edit==1){
								$viewModel->setVariable( 'enableEdit',1);
							}
						}
						$is_invited = 0;
								$invitedHystory = $this->getGroupJoiningInvitationTable()->checkInvited($identity->user_id,$group_info->group_id);
								if(!empty($invitedHystory)&&$invitedHystory->user_group_joining_invitation_id!=''){
									$is_invited = 1;
								}
						 $arr_group_info = array(
							'group_id'=>$group_info->group_id,
							'group_title'=>$group_info->group_title,
							'group_seo_title'=>$group_info->group_seo_title,
							'group_description'=>$group_info->group_description,
							'group_added_timestamp'=>date("F d, Y",strtotime($group_info->group_added_timestamp)),
							'group_type'=>$group_info->group_type,
							'is_admin'=>$group_info->is_admin,
							'is_member'=>$group_info->is_member,
							'is_requested'=>$group_info->is_requested,
							'is_invited'=>$is_invited,
							'member_count'=>$group_info->member_count,
							'friend_count'=>$group_info->friend_count,
							'country_title'=>$group_info->country_title,
							'country_code'=>$group_info->country_code,
							'group_photo_photo'=>$group_info->group_photo_photo,
							'city'=>$group_info->city,							 
							'request_count'=>$request_count,
						 );
					  }
					  $groupUsers = $this->getUserGroupTable()->fetchAllUserListForGroup($group_info->group_id,$identity->user_id,0,3)->toArray();
					  $viewModel->setVariable('tag_category',$tag_category);
					  $viewModel->setVariable('tags',$tags);
					  $viewModel->setVariable('group_info',$arr_group_info);
					  $viewModel->setVariable('groupUsers',$groupUsers);
					  $profile_data = $this->getUserTable()->getProfileDetails($identity->user_id);				 
					  $viewModel->setVariable( 'profile_data' , $profile_data);
					  $myIntrests = $this->getUserTagTable()->getAllUserTags($identity->user_id);
					  $viewModel->setVariable( 'myIntrests' , $myIntrests);
					  return $viewModel;
				}else{
					$error = "Group not exist";
					$result = new ViewModel(array('error'=>$error));
					return $result;
				}
			}else{
				$error = "User not exist in the system";
				$result = new ViewModel(array('error'=>$error));
				return $result;
			}
		}else{return $this->redirect()->toRoute('home', array('action' => 'index'));}
	}
	public function getAllGroupTagsAction(){
		$error = '';
		$auth = new AuthenticationService();
		$arr_group_tags = array();
		$tag_category = array();
		$comments = array();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$request   = $this->getRequest();
			if ($request->isPost()){ 
				$post = $request->getPost();
				$group_id = $post['group'];				 				 
				if($group_id){
					$tag_category = $this->getGroupTagTable()->getAllGroupTagCategiry($group_id);
					$arr_group_tags = $this->getGroupTagTable()->fetchAllGroupTags($group_id);
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	
		$return_array['group_tags'] = $arr_group_tags;	
		$return_array['tag_category'] = $tag_category;	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;
	}
	public function updateTagAction(){
		$error = '';
		$tag_category = array();
		$tags = array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();			
			if ($request->isPost()){
				$post = $request->getPost(); 
				$group_id = $post['group'];	
				$group_info = $this->getGroupTable()->getPlanetinfo($group_id);	
				if(!empty($group_info)&&$group_info->group_id!=''){	
					if(!empty($post['tags'])){
						foreach($post['tags'] as $tags){
							$data_grouptags = array();
							$tag_hystory = $this->getTagTable()->getTag($tags);
							$tag_exist =  $this->getGroupTagTable()->checkGroupTag($group_id,$tags); 
							if(!empty($tag_hystory)&&$tag_hystory->tag_id!=''&&empty($tag_exist)){
								$data_grouptags['group_tag_group_id'] =$group_id;
								$data_grouptags['group_tag_tag_id'] = $tags;
								 
								$objGroupTag = new GroupTag();
								$objGroupTag->exchangeArray($data_grouptags);
								$this->getGroupTagTable()->saveGroupTag($objGroupTag);
							}							
						}
						$this->getGroupTagTable()->deleteAllGroupTags($group_id,$post['tags']);
						$tag_category = $this->getGroupTagTable()->getAllGroupTagCategiry($group_info->group_id);
						$tags = $this->getGroupTagTable()->fetchAllGroupTags($group_info->group_id);
					}else{	
						$this->getGroupTagTable()->deleteGroupTag($group_id);
					}						
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}	
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;		
		$return_array['tags'] = $tags;		
		$return_array['tag_category'] = $tag_category;		 		
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function updateGroupAction(){
		$error = '';		
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();			
			if ($request->isPost()){
				$post = $request->getPost(); 
				$group_id = $post['group'];
				$group_title = $post['group_title'];
				$group_description = $post['group_description'];
				$group_type = $post['group_type'];
				$group_info = $this->getGroupTable()->getPlanetinfo($group_id);	
				if(!empty($group_info)&&$group_info->group_id!=''){	
					if($this->getUserGroupTable()->checkOwner($group_info->group_id,$identity->user_id)){
						$group_details = $this->getGroupTable()->getGroupByName($group_title);
						if(!empty($group_details)&& $group_details->group_id!=''&&$group_details->group_id!=$group_info->group_id){							 
							$error = "Group name already exist";
						}else{
							$group_data['group_title'] = $group_title;
							$group_data['group_description'] = $group_description;
							$group_data['group_type'] = $group_type;
							if($this->getGroupTable()->updateGroup($group_data,$group_info->group_id)){
								;					
							}else{
								$error = "Some error occured. Please try again";
							}
						}
					}else{$error = "You don't have the permissions to do this operation";}
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}	
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	 	 		
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function groupmembersAction(){
		$error = '';
		$auth = new AuthenticationService();
		$viewModel = new ViewModel();
		$config = $this->getServiceLocator()->get('Config');
		$viewModel->setVariable('image_folders',$config['image_folders']);
		if ($auth->hasIdentity()) {
			$this->layout('layout/layout_user');
			$identity = $auth->getIdentity();
			$profile_data = $this->getUserTable()->getProfileDetails($identity->user_id);
			$viewModel->setVariable( 'profile_data' , $profile_data);	
			$group_seo = $this->params('group_seo');
			$this->layout()->identity = $identity;       
			$groupinfo =  $this->getGroupTable()->getGroupBySeoTitle($group_seo);		
			if(!empty($groupinfo)&&$identity->user_id){			    
				$group_details = $this->getGroupTable()->getGroupDetails($groupinfo->group_id,$identity->user_id);
				$friendsCount      = $this->getUserGroupTable()->getFriendsCount($groupinfo->group_id,$identity->user_id);
                $arrownerCount		  = $this->getUserGroupTable()->getOwnersCount($groupinfo->group_id);
				$owner = $this->getUserGroupTable()->checkOwner($groupinfo->group_id,$identity->user_id);
				$pending_reguest_count = 0;
				if($owner){
					$viewModel->setVariable( 'is_owner' , 1);
					$pending_reguest_count = $this->getUserGroupJoiningRequestTable()->countGroupMemberRequests($groupinfo->group_id)->memberCount;
				}else{$viewModel->setVariable( 'is_owner' , 0);}
				if(!empty($arrownerCount)){
					$ownerCount = $arrownerCount->group_owner_count;
				}else{$ownerCount=0;}
				$friends_count = (isset($friendsCount->friend_count))?$friendsCount->friend_count:0;
                $viewModel->setVariable( 'friend_count' , $friends_count);
				$viewModel->setVariable( 'group_details' , $group_details);
				$viewModel->setVariable( 'ownerCount' , $ownerCount);
				$viewModel->setVariable( 'pending_reguest_count' , $pending_reguest_count);
				$questionnaire = $this->getGroupJoiningQuestionnaireTable()->getQuestionnaireArray($groupinfo->group_id);
				$viewModel->setVariable( 'questionnaire' , $questionnaire);
				return $viewModel;
			}else{
				$error = "User not exist in the system";
				$result = new ViewModel(array('error'=>$error));
				return $result;
			}
		}else{return $this->redirect()->toRoute('home', array('action' => 'index'));}
	}
	public function getMembersAction(){
		$error = '';
		$arrMembers = array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();			
			if ($request->isPost()){
				$post = $request->getPost(); 
				$group_id = $post['group_id'];	
				$page = $post['page'];
				$offset = ($page)?($page-1)*10:0;
				$type = $post['type'];
				$group_info = $this->getGroupTable()->getPlanetinfo($group_id);	
				if(!empty($group_info)&&$group_info->group_id!=''){
					if($type == 'pending'){
						$members_list = $this->getUserGroupJoiningRequestTable()->getRequestMembers($group_info->group_id,$offset,10);
					}else{
						$members_list = $this->getUserGroupTable()->getMembers($group_info->group_id,$identity->user_id,$type,$offset,10);
					}
					 if(!empty($members_list)){
						foreach($members_list as $list){
							$tag_category = $this->getUserTagTable()->getAllUserTagCategiry($list['user_id']);
							$objcreated_group_count = $this->getUserGroupTable()->getCreatedGroupCount($list['user_id']);
							if(!empty($objcreated_group_count)){
							$created_group_count = $objcreated_group_count->created_group_count;
							}else{$created_group_count =0;}
							$is_friend = ($this->getUserFriendTable()->isFriend($list['user_id'],$identity->user_id))?1:0;
							$is_requested = ($this->getUserFriendTable()->isRequested($list['user_id'],$identity->user_id))?1:0;
							$isPending = ($this->getUserFriendTable()->isPending($list['user_id'],$identity->user_id))?1:0;
							$arr_questionnaire = array();
							if($type == 'pending'){
								$questionnaire = $this->getGroupQuestionnaireAnswersTable()->getAllQuestionswithanswers($group_info->group_id,$list['user_id']);
								foreach($questionnaire as $questions){
									$options = $this->getGroupQuestionnaireOptionsTable()->getAnswerOptions(array($questions['selected_options']));
									$arr_questionnaire[] = array(	
															'question'=>$questions['question'],
															'answer_type'=>$questions['answer_type'],
															'answer'=>$questions['answer'],
															'options'=>$options,
															);
								}
							}
							$arrMembers[] = array(
											'user_id'=>$list['user_id'],
											'user_given_name'=>$list['user_given_name'],
											'user_profile_name'=>$list['user_profile_name'],
											'country_title'=>$list['country_title'],
											'country_code'=>$list['country_code'],
											'user_fbid'=>$list['user_fbid'],
											'user_register_type'=>$list['user_register_type'],
											'city'=>$list['city'],
											'profile_photo'=>$list['profile_icon'],
											'tag_count' =>count($tag_category),
											'tag_category' =>$tag_category,
											'group_count'=>$list['group_count'],
											'created_group_count'=>$created_group_count,
											'is_admin'=>($type == 'pending')?0:$list['is_admin'],
											'user_group_is_owner'=>($type == 'pending')?0:$list['user_group_is_owner'],
											'user_group_role'=>($type == 'pending')?'':$list['user_group_role'],
											'is_friend'=>$is_friend,
											'is_requested'=>$is_requested,
											'isPending'=>$isPending,
											'questionnaire' =>$arr_questionnaire,
											);
						}
					 }
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}	
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	 
		$return_array['members'] = $arrMembers;	 		
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function ignoreJoinRequestAction(){
		$error = '';
		$arrMembers = array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$request   = $this->getRequest();
			if ($request->isPost()){
				$post = $request->getPost();
				$group_id = $post['group_id'];
				$user_id = $post['user_id']; 
				$group_info = $this->getGroupTable()->getPlanetinfo($group_id);	
				if(!empty($group_info)&&$group_info->group_id!=''){	
					$owner = $this->getUserGroupTable()->checkOwner($group_info->group_id,$identity->user_id);
					if($owner){
						$this->getUserGroupJoiningRequestTable()->RemoveRequest($user_id,$group_id);
						$this->getGroupQuestionnaireAnswersTable()->deleteUserAnswersOfGroup($group_id,$user_id);
					}
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}	
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function acceptJoinRequestAction(){
		$error = '';
		$arrMembers = array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();
			$request   = $this->getRequest();
			if ($request->isPost()){
				$post = $request->getPost();
				$group_id = $post['group_id'];
				$user_id = $post['user_id']; 
				$group_info = $this->getGroupTable()->getPlanetinfo($group_id);	
				if(!empty($group_info)&&$group_info->group_id!=''){	
					$owner = $this->getUserGroupTable()->checkOwner($group_info->group_id,$identity->user_id);
					if($owner){						
						$usergroup = $this->getUserGroupTable()->getUserGroup($user_id,$group_id);
						if(empty($usergroup)){
							$user_data['user_group_user_id'] = $user_id;
							$user_data['user_group_group_id'] = $group_id;
							$user_data['user_group_status'] = "available";							 
							$this->getUserGroupTable()->AddMembersTOGroup($user_data);
							$this->getUserGroupJoiningRequestTable()->ChangeStatusTOProcessed($group_id,$user_id);
							$config = $this->getServiceLocator()->get('Config');
							$base_url = $config['pathInfo']['base_url'];
							$msg = $identity->user_given_name." Accept the group joining request to the group ".$group_info->group_title;
							$subject = 'Group joining Request';
							$from = 'admin@jeera.com';
							$process = 'Accepted';
							$this->UpdateNotifications($user_id,$msg,5,$subject,$from,$identity->user_id,$group_id,$process);					 
						}
					}
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}	
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function removeuserAction(){
		$error = '';
		$arrMembers = array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();			
			if ($request->isPost()){
				$post = $request->getPost(); 
				$group_id = $post['group_id'];	
				$user_id = $post['user_id'];	 
				$group_info = $this->getGroupTable()->getPlanetinfo($group_id);	
				if(!empty($group_info)&&$group_info->group_id!=''){	
					$owner = $this->getUserGroupTable()->checkOwner($group_info->group_id,$identity->user_id);
					if($owner){
						if($this->getUserGroupTable()->deleteOneUserGroup($group_info->group_id,$user_id)){
						 $this->getGroupQuestionnaireAnswersTable()->deleteUserAnswersOfGroup($group_info->group_id,$user_id);
						}else{$error = "Some error occured.Please try again";}
					}
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}	
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function promoteadminAction(){
		$error = '';
		$arrMembers = array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();			
			if ($request->isPost()){
				$post = $request->getPost(); 
				$group_id = $post['group_id'];	
				$user_id = $post['user_id'];	 
				$group_info = $this->getGroupTable()->getPlanetinfo($group_id);	
				if(!empty($group_info)&&$group_info->group_id!=''){	
					$owner = $this->getUserGroupTable()->checkOwner($group_info->group_id,$identity->user_id);
					if($owner){
						if($this->getUserGroupTable()->updateUserRoles($user_id,$group_info->group_id,1)){
							$config = $this->getServiceLocator()->get('Config');
							$base_url = $config['pathInfo']['base_url'];
							$msg = $identity->user_given_name."promoted you as an admin to the group ".$group_info->group_title;
							$subject = 'Group admin Promoted';
							$from = 'admin@jeera.com';
							$process = 'Promoted';
							$this->UpdateNotifications($user_id,$msg,9,$subject,$from,$identity->user_id,$group_id,$process);
								 
						}else{$error = "Some error occured.Please try again";}
					}
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}	
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function revokeadminAction(){
		$error = '';
		$arrMembers = array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();			
			if ($request->isPost()){
				$post = $request->getPost(); 
				$group_id = $post['group_id'];	
				$user_id = $post['user_id'];	 
				$group_info = $this->getGroupTable()->getPlanetinfo($group_id);	
				if(!empty($group_info)&&$group_info->group_id!=''){	
					$owner = $this->getUserGroupTable()->checkOwner($group_info->group_id,$identity->user_id);
					if($owner){
						if($this->getUserGroupTable()->updateUserRoles($user_id,$group_info->group_id,0)){
						;
						}else{$error = "Some error occured.Please try again";}
					}
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}	
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function updateQuestionnaireAction(){
		$error = '';
		$arrMembers = array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();			
			if ($request->isPost()){
				$post = $request->getPost(); 
				$group_id = $post['group_id'];					
				$group_info = $this->getGroupTable()->getPlanetinfo($group_id);	
				if(!empty($group_info)&&$group_info->group_id!=''){					
					$owner = $this->getUserGroupTable()->checkOwner($group_info->group_id,$identity->user_id);
					if($owner){
						 $type = $post['type'];	 
						 $question = $post['question'];	
						 $option1	 = $post['option1'];
						 $option2	 = $post['option2'];
						 $option3	 = $post['option3'];
						 $questionId = $post['questionId'];
						 $ndex = 0;
						 $arr_questionnaire = array();
						 foreach($type  as $questype){  
							switch($questype){
								case 'Textarea':   
									if($question[$ndex]==''||$question[$ndex]=='undefined'){
										$error = "Add questions";
									}else{
										$arr_questionnaire[] = array('answer_type'=>'Textarea',
																	'group_id'=>$group_id,
																	'question'=>$question[$ndex],
																	'question_status'=>'active',
																	'added_user_id'=>$identity->user_id,
																	'questionId'=>$questionId[$ndex],
																);
									}
								break;
								case 'checkbox':
									if($question[$ndex]==''||$question[$ndex]=='undefined'){
										$error = "Add questions";
									}else if($option1[$ndex]==''||$option1[$ndex]=='undefined'||$option2[$ndex]==''||$option2[$ndex]=='undefined'){
										$error = "Add options";
									}else{
										$arr_questionnaire[] = array('answer_type'=>'checkbox',
																	'group_id'=>$group_id,
																	'question'=>$question[$ndex],
																	'question_status'=>'active',
																	'added_user_id'=>$identity->user_id,
																	'questionId'=>$questionId[$ndex],
																	'option1'=>$option1[$ndex],
																	'option2'=>$option2[$ndex],
																	'option3'=>$option3[$ndex]
																);
									}
								break;
								case 'radio':
									if($question[$ndex]==''||$question[$ndex]=='undefined'||$option2[$ndex]==''||$option2[$ndex]=='undefined'){
										$error = "Add questions";
									}else if($option1[$ndex]==''||$option1[$ndex]=='undefined'){
										$error = "Add options";
									}else{
										$arr_questionnaire[] = array('answer_type'=>'radio',
																	'group_id'=>$group_id,
																	'question'=>$question[$ndex],
																	'question_status'=>'active',
																	'added_user_id'=>$identity->user_id,
																	'questionId'=>$questionId[$ndex],
																	'option1'=>$option1[$ndex],
																	'option2'=>$option2[$ndex],
																	'option3'=>$option3[$ndex]
																);
										}
								break;
							}
							$ndex++;
						 }
						 if($error == ""){
							foreach($arr_questionnaire as $list){ 
								if($list['questionId']!=''&&$list['questionId']!='undefined'&&$list['questionId']>0){
									$question = $this->getGroupJoiningQuestionnaireTable()->getQuestionFromQuestionId($list['questionId']);
									if(!empty($question)){
										if($question->answer_type == $list['answer_type']){
											$qdata['question'] = $list['question'];
											$this->getGroupJoiningQuestionnaireTable()->updateQuestion($qdata,$question->questionnaire_id);
											if($question->answer_type == 'radio' || $question->answer_type == 'checkbox'){
												$options = $this->getGroupQuestionnaireOptionsTable()->getoptionOfOneQuestion($question->questionnaire_id);
												$option_cnt = 1;
												foreach($options as $opt){
													$data['option'] = $list['option'.$option_cnt];
													$this->getGroupQuestionnaireOptionsTable()->UpdateOptions($list['option'.$option_cnt],$opt['option_id']);
													$option_cnt++;
												}
											}
										}else{
											if($question->answer_type == "Textarea"&&( $list['answer_type'] =='radio' || $list['answer_type'] =='checkbox')){
												$qdata[question] = $list['question'];
												$this->getGroupJoiningQuestionnaireTable()->updateQuestion($qdata,$question->questionnaire_id);
												if($list['option1']!=''&&$list['option1']!='undefined'){
												$addedOption    = array(
														'question_id'   => $question->questionnaire_id,
														'option'        => $list['option1']
													);
													$intQOptionId                      = $this->getGroupQuestionnaireOptionsTable()->AddOptions($addedOption);
												}
												if($list['option2']!=''&&$list['option2']!='undefined'){
												$addedOption    = array(
														'question_id'   => $question->questionnaire_id,
														'option'        => $list['option2']
													);
													$intQOptionId                      = $this->getGroupQuestionnaireOptionsTable()->AddOptions($addedOption);
												}
												if($list['option3']!=''&&$list['option3']!='undefined'){
												$addedOption    = array(
														'question_id'   => $question->questionnaire_id,
														'option'        => $list['option3']
													);
													$intQOptionId                      = $this->getGroupQuestionnaireOptionsTable()->AddOptions($addedOption);
												}
											}else if($list['answer_type'] == "Textarea"&&( $question->answer_type =='radio' ||  $question->answer_type =='checkbox')){
												$qdata[question] = $list['question'];
												$this->getGroupJoiningQuestionnaireTable()->updateQuestion($qdata,$question->questionnaire_id);
												 $this->getGroupQuestionnaireOptionsTable()->DeleteOptions($question->questionnaire_id);
											}else if(($list['answer_type'] == "radio" && $question->answer_type =='checkbox')||($list['answer_type'] == "checkbox" && $question->answer_type =='radio')){
												$options = $this->getGroupQuestionnaireOptionsTable()->getoptionOfOneQuestion($question->questionnaire_id);
												$option_cnt = 1;
												foreach($options as $opt){
													$data['option'] = $list['option'.$option_cnt];
													$this->getGroupQuestionnaireOptionsTable()->UpdateOptions($list['option'.$option_cnt],$opt['option_id']);
													$option_cnt++;
												}
											}
										}
										
									}else{
										$addedQuestion      = array(
                                            'group_id'            => $list['group_id'],
                                            'question'            => $list['question'],
                                            'question_status'     => 'active',
                                            'added_ip'            => $_SERVER["SERVER_ADDR"],
                                            'added_user_id'       => $identity->user_id,
                                            'answer_type'         => $list['answer_type'],
                                       );
										// save question
										$intQuestionId                      = $this->getGroupJoiningQuestionnaireTable()->AddQuestion($addedQuestion);
										if($list['answer_type']=='radio'||$list['answer_type']=='checkbox'){
											if($list['option1']!=''&&$list['option1']!='undefined'){
											$addedOption    = array(
													'question_id'   => $intQuestionId,
													'option'        => $list['option1']
												);
												$intQOptionId                      = $this->getGroupQuestionnaireOptionsTable()->AddOptions($addedOption);
											}
											if($list['option2']!=''&&$list['option2']!='undefined'){
											$addedOption    = array(
													'question_id'   => $intQuestionId,
													'option'        => $list['option2']
												);
												$intQOptionId                      = $this->getGroupQuestionnaireOptionsTable()->AddOptions($addedOption);
											}
											if($list['option3']!=''&&$list['option3']!='undefined'){
											$addedOption    = array(
													'question_id'   => $intQuestionId,
													'option'        => $list['option3']
												);
												$intQOptionId                      = $this->getGroupQuestionnaireOptionsTable()->AddOptions($addedOption);
											}
										}
									}
								}else{
									$addedQuestion      = array(
                                            'group_id'            => $list['group_id'],
                                            'question'            => $list['question'],
                                            'question_status'     => 'active',
                                            'added_ip'            => $_SERVER["SERVER_ADDR"],
                                            'added_user_id'       => $identity->user_id,
                                            'answer_type'         => $list['answer_type'],
                                       );
										// save question
										$intQuestionId                      = $this->getGroupJoiningQuestionnaireTable()->AddQuestion($addedQuestion);
										if($list['answer_type']=='radio'||$list['answer_type']=='checkbox'){
											if($list['option1']!=''&&$list['option1']!='undefined'){
											$addedOption    = array(
													'question_id'   => $intQuestionId,
													'option'        => $list['option1']
												);
												$intQOptionId                      = $this->getGroupQuestionnaireOptionsTable()->AddOptions($addedOption);
											}
											if($list['option2']!=''&&$list['option2']!='undefined'){
											$addedOption    = array(
													'question_id'   => $intQuestionId,
													'option'        => $list['option2']
												);
												$intQOptionId                      = $this->getGroupQuestionnaireOptionsTable()->AddOptions($addedOption);
											}
											if($list['option3']!=''&&$list['option3']!='undefined'){
											$addedOption    = array(
													'question_id'   => $intQuestionId,
													'option'        => $list['option3']
												);
												$intQOptionId                      = $this->getGroupQuestionnaireOptionsTable()->AddOptions($addedOption);
											}
										}
									}
								}
						 }
					}else{$error = "Unable to process";}
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";}	
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function updatProfilePicAction(){
		$error = '';
		$arrMembers = array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();
			if ($request->isPost()){
				$post = $request->getPost(); 
				if(!empty($post)){						 
					$img = $_POST['imageData'];  
					if($img!=''){
						$group_id = $post['group_id'];
						$group  = $this->getGroupTable()->getPlanetinfo($post['group_id']);
						if(!empty($group)){
							$img = str_replace('data:image/png;base64,', '', $img);
							$img = str_replace(' ', '+', $img);
							$data = base64_decode($img);
							$config = $this->getServiceLocator()->get('Config');
							$imagePath_dir = $config['pathInfo']['ROOTPATH'].'/public/datagd/group/'.$group->group_id.'/';
							$mediumimagePath_dir = $config['pathInfo']['ROOTPATH'].'/public/datagd/group/'.$group->group_id.'/medium';
							$filename = 'group_'.$group->group_id.''.time().'.png';	
							$imagePath = $config['pathInfo']['ROOTPATH'].'/public/datagd/group/'.$group->group_id.'/'.$filename;
							$mediumimagePath = $config['pathInfo']['ROOTPATH'].'/public/datagd/group/'.$group->group_id.'/medium/'.$filename;
							if(!is_dir($imagePath_dir)){							
								mkdir($imagePath_dir);
							} 
							if(!is_dir($mediumimagePath_dir)){							
								mkdir($mediumimagePath_dir);
							}							
							if(file_put_contents($imagePath, $data)){
								$resize = new ResizeImage($imagePath);
								$resize->resizeTo(380, 214, 'maxWidth');								
								$resize->saveImage($mediumimagePath);
								$group_photo =  $this->getGroupPhotoTable()->getGalexyPhoto($group->group_id);
								$groupphoto  = new GroupPhoto();
								$previous_image = '';
								if(!empty($group_photo)&&$group_photo->group_photo_id!=''){									
									$groupphoto->group_photo_id = $group_photo->group_photo_id;
									$previous_image = $groupphoto->group_photo_photo;
								}
								$groupphoto->group_photo_group_id  = $group->group_id;
								$groupphoto->group_photo_photo = $filename;
								$intGroupPhotoId  = $this->getGroupPhotoTable()->savePhoto($groupphoto);
								if($intGroupPhotoId){
									if($previous_image!=''){	
										@unlink($config['pathInfo']['ROOTPATH'].'/public/datagd/groups/'.$group->group_id.'/'.$groupphoto->group_photo_photo);
										@unlink($config['pathInfo']['ROOTPATH'].'/public/datagd/groups/'.$group->group_id.'/medium/'.$groupphoto->group_photo_photo);
									}
								}else{$error = "Some error occured.Please try again";}	
							}							 
						}else{$error = "Group not available";}												 
					}else{$error = "Image not available";}
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";} 
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function removeBannerAction(){
		$error = '';
		$arrMembers = array();
		$auth = new AuthenticationService();
		if ($auth->hasIdentity()) {
			$identity = $auth->getIdentity();			  
			$request   = $this->getRequest();
			if ($request->isPost()){
				$post = $request->getPost(); 
				if(!empty($post)){				 
					$group_id = $post['group'];
					$group  = $this->getGroupTable()->getPlanetinfo($group_id);
					if(!empty($group)){
						$owner = $this->getUserGroupTable()->checkOwner($group->group_id,$identity->user_id);
						if($owner){
							$config = $this->getServiceLocator()->get('Config');
							$imagePath_dir = $config['pathInfo']['ROOTPATH'].'/public/datagd/group/'.$group->group_id.'/';
							$mediumimagePath_dir = $config['pathInfo']['ROOTPATH'].'/public/datagd/group/'.$group->group_id.'/medium';
							$filename = 'group_'.$group->group_id.''.time().'.png';	
							$imagePath = $config['pathInfo']['ROOTPATH'].'/public/datagd/group/'.$group->group_id.'/'.$filename;
							$mediumimagePath = $config['pathInfo']['ROOTPATH'].'/public/datagd/group/'.$group->group_id.'/medium/'.$filename;
							$group_photo =  $this->getGroupPhotoTable()->getGalexyPhoto($group->group_id);
							$previous_image = '';
							if(!empty($group_photo)&&$group_photo->group_photo_id!=''){					 
								$previous_image = $groupphoto->group_photo_photo;
								$this->getGroupPhotoTable()->RemoveBanner($group_photo->group_photo_id);
								if($previous_image!=''){	
									@unlink($config['pathInfo']['ROOTPATH'].'/public/datagd/groups/'.$group->group_id.'/'.$groupphoto->group_photo_photo);
									@unlink($config['pathInfo']['ROOTPATH'].'/public/datagd/groups/'.$group->group_id.'/medium/'.$groupphoto->group_photo_photo);
									@unlink($config['pathInfo']['ROOTPATH'].'/public/datagd/groups/'.$group->group_id.'/thumbnail/'.$groupphoto->group_photo_photo);
								}
							}						 
						}else{$error = "You don\'t have the permission to do it";}						
					}else{$error = "Group not available";}				 
				}else{$error = "Unable to process";}
			}else{$error = "Unable to process";} 
		}else{$error = "Your session has to be expired";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error; 	
		$result = new JsonModel(array(
		'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function mediaviewAction(){
		$error = '';
		$auth = new AuthenticationService();
		$viewModel = new ViewModel();
		$config = $this->getServiceLocator()->get('Config');
		$viewModel->setVariable('image_folders',$config['image_folders']);
		if ($auth->hasIdentity()) {
			$this->layout('layout/layout_user');
			$identity = $auth->getIdentity();
			$profile_data = $this->getUserTable()->getProfileDetails($identity->user_id);
			$viewModel->setVariable( 'profile_data' , $profile_data);	
			$group_seo = $this->params('group_seo');
			$this->layout()->identity = $identity;       
			$groupinfo =  $this->getGroupTable()->getGroupBySeoTitle($group_seo);		
			if(!empty($groupinfo)&&$groupinfo->group_id){
				$group_details = $this->getGroupTable()->getGroupDetails($groupinfo->group_id,$identity->user_id);
				$viewModel->setVariable( 'group_details' , $group_details);
				$media_id =  $this->params('id');
				$arr_group_media = array();
				if(!empty($media_id)){
					$group_media = $this->getGroupMediaTable()->getOneMedia($media_id);
					$is_admin = 0;
					if($this->getUserGroupTable()->checkOwner($group_media->group_id,$group_media->user_id)){
						$is_admin = 1;
					}
					if(!empty($group_media)){
						if($group_media->media_added_group_id == $groupinfo->group_id){
							$SystemTypeData = $this->getGroupTable()->fetchSystemType("Media");
							$like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$media_id,$identity->user_id);
							$like_count = $like_details->likes_counts;		
							$arr_likedUsers = array();						
							if(!empty($like_details)){  
								$liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$media_id,$identity->user_id,2,0);
								$arr_likedUsers = array();
								if($like_details['is_liked']==1){
									$arr_likedUsers[] = 'you';
								}
								if($like_details['likes_counts']>0&&!empty($liked_users)){
									foreach($liked_users as $likeuser){
										$arr_likedUsers[] = $likeuser['user_given_name'];
									}
								}
								 
							}	
							$commet_users = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$media_id,$identity->user_id);
							$next_item = $this->getGroupMediaTable()->getNextMedia($group_media->group_id,$media_id);
							$prev_item = $this->getGroupMediaTable()->getPreviousMedia($group_media->group_id,$media_id);
							$arr_group_media = array(
											'media_type' => $group_media->media_type,
											'group_media_id' => $group_media->group_media_id,
											'media_content' => $group_media->media_content,
											'media_caption' => $group_media->media_caption,
											'added_time' =>$this->timeAgo($group_media->media_added_date),
											'group_id' => $group_media->group_id,
											'group_title' => $group_media->group_title,
											'group_seo_title' => $group_media->group_seo_title,
											'user_id' => $group_media->user_id,
											'user_given_name' => $group_media->user_given_name,
											'user_first_name' => $group_media->user_first_name,
											'user_last_name' => $group_media->user_last_name,
											'user_profile_name' => $group_media->user_profile_name,
											'user_fbid' => $group_media->user_fbid,
											'profile_photo' => $group_media->profile_photo,
											'arr_likedUsers' => $arr_likedUsers,
											'like_count' =>$like_details['likes_counts'],
											'is_liked' =>$like_details['is_liked'],
											'comment_count' =>$commet_users['comment_counts'],
											'is_commented' =>$commet_users['is_commented'],
											'next_id' =>(isset($next_item->group_media_id))?$next_item->group_media_id:'',
											'prev_id' =>(isset($prev_item->group_media_id))?$prev_item->group_media_id:'',
											'is_admin'=>$is_admin,
											'time'=>$this->timeAgo($group_media->media_added_date),
											);
								
							$viewModel->setVariable( 'arr_group_media' , $arr_group_media);
							return $viewModel;
						}else{ return $this->redirect()->toRoute('home/404', array('action' => 'nopage')); }								
					}else{ return $this->redirect()->toRoute('home/404', array('action' => 'nopage')); }							  	
				}else{ return $this->redirect()->toRoute('home/404', array('action' => 'nopage')); }
			}else{ return $this->redirect()->toRoute('home/404', array('action' => 'nopage')); }
		}else{return $this->redirect()->toRoute('home', array('action' => 'nopage'));}
	}
	public function UpdateNotifications($user_notification_user_id,$msg,$type,$subject,$from,$sender,$reference_id,$processs){
		$UserGroupNotificationData = array();						
		$UserGroupNotificationData['user_notification_user_id'] =$user_notification_user_id;		 
		$UserGroupNotificationData['user_notification_content']  = $msg;
		$UserGroupNotificationData['user_notification_added_timestamp'] = date('Y-m-d H:i:s');			
		$UserGroupNotificationData['user_notification_notification_type_id'] = $type;
		$UserGroupNotificationData['user_notification_status'] = 'unread';
		$UserGroupNotificationData['user_notification_sender_id'] = $sender;
		$UserGroupNotificationData['user_notification_reference_id'] = $reference_id;			
		$UserGroupNotificationData['user_notification_process'] = $processs;
		#lets Save the User Notification
		$UserGroupNotificationSaveObject = new UserNotification();
		$UserGroupNotificationSaveObject->exchangeArray($UserGroupNotificationData);	
		$insertedUserGroupNotificationId ="";	#this will hold the latest inserted id value
		$insertedUserGroupNotificationId = $this->getUserNotificationTable()->saveUserNotification($UserGroupNotificationSaveObject);
		$userData = $this->getUserTable()->getUser($user_notification_user_id); 
		//$this->sendNotificationMail($msg,$subject,$userData->user_email,$from);
	}
	public function sendNotificationMail($msg,$subject,$emailId,$from){
		$this->renderer = $this->getServiceLocator()->get('ViewRenderer');		
		$body = $this->renderer->render('user/email/emailInvitation.phtml', array('msg'=>$msg));
		$htmlPart = new MimePart($body);
		$htmlPart->type = "text/html";
		$textPart = new MimePart($body);
		$textPart->type = "text/plain";
		$body = new MimeMessage();
		$body->setParts(array($textPart, $htmlPart));
		$message = new Mail\Message();
		$message->setFrom($from);
		$message->addTo($emailId);
		//$message->addReplyTo($reply);							 
		$message->setSender("Jeera");
		$message->setSubject($subject);
		$message->setEncoding("UTF-8");
		$message->setBody($body);
		$message->getHeaders()->get('content-type')->setType('multipart/alternative');
		$transport = new Mail\Transport\Sendmail();
		$transport->send($message);
		return true;
	}	
	public function getGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;    
    }
	public function getUserGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->userGroupTable = (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;  
	}
	public function getGroupPhotoTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupPhotoTable = (!$this->groupPhotoTable)?$sm->get('Groups\Model\GroupPhotoTable'):$this->groupPhotoTable;  
	}
	public function getUserTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;    
	}
	public function getUserFriendTable(){
		$sm = $this->getServiceLocator();
		return  $this->userFriendTable = (!$this->userFriendTable)?$sm->get('User\Model\UserFriendTable'):$this->userFriendTable;    
	}	
	public function getGroupTagTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTagTable = (!$this->groupTagTable)?$sm->get('Tag\Model\GroupTagTable'):$this->groupTagTable;    
    }
	public function getGroupMediaTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupMediaTable = (!$this->groupMediaTable)?$sm->get('Groups\Model\GroupMediaTable'):$this->groupMediaTable;    
    }
	public function getUserNotificationTable(){         
		$sm = $this->getServiceLocator();
		return  $this->userNotificationTable = (!$this->userNotificationTable)?$sm->get('Notification\Model\UserNotificationTable'):$this->userNotificationTable;    
    }	 
    public function getGroupJoiningInvitationTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupJoiningInvitationTable = (!$this->groupJoiningInvitationTable)?$sm->get('Groups\Model\UserGroupJoiningInvitationTable'):$this->groupJoiningInvitationTable;
    }
    public function getGroupJoiningQuestionnaireTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupJoiningQuestionnaire = (!$this->groupJoiningQuestionnaire)?$sm->get('Groups\Model\GroupJoiningQuestionnaireTable'):$this->groupJoiningQuestionnaire;
    }
    public function getGroupQuestionnaireOptionsTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupQuestionnaireOptions = (!$this->groupQuestionnaireOptions)?$sm->get('Groups\Model\GroupQuestionnaireOptionsTable'):$this->groupQuestionnaireOptions;
    }
	public function getCommentTable(){
		$sm = $this->getServiceLocator();
		return  $this->commentTable = (!$this->commentTable)?$sm->get('Comment\Model\CommentTable'):$this->commentTable;   
	}
	public function getLikeTable(){         
		$sm = $this->getServiceLocator();
        return  $this->likeTable = (!$this->likeTable)?$sm->get('Like\Model\LikeTable'):$this->likeTable;       
    }
	public function getUserGroupJoiningRequestTable(){         
		$sm = $this->getServiceLocator();
        return  $this->userGroupJoiningRequestTable = (!$this->userGroupJoiningRequestTable)?$sm->get('Groups\Model\UserGroupJoiningRequestTable'):$this->userGroupJoiningRequestTable;       
    }
	public function getGroupQuestionnaireAnswersTable(){
		$sm = $this->getServiceLocator();
        return  $this->groupQuestionnaireAnswersTable = (!$this->groupQuestionnaireAnswersTable)?$sm->get('Groups\Model\GroupQuestionnaireAnswersTable'):$this->groupQuestionnaireAnswersTable; 
	}
	public function getTagTable(){
		$sm = $this->getServiceLocator();
		return  $this->tagTable = (!$this->tagTable)?$sm->get('Tag\Model\TagTable'):$this->tagTable;    
	}
	public function getUserTagTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTagTable = (!$this->userTagTable)?$sm->get('Tag\Model\UserTagTable'):$this->userTagTable;    
	}
	 
}