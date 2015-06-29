<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Service\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;
use Zend\View\Renderer\PhpRenderer;
use \Exception;
use Zend\Crypt\BlockCipher;
use Zend\Crypt\Password\Bcrypt;	
use User\Auth\BcryptDbAdapter as AuthAdapter;
use Zend\Session\Container;     
use Zend\Authentication\AuthenticationService;
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Authentication\Storage\Session;
use User\Model\User;
use User\Model\UserProfile;
use User\Model\UserFriend;
use Tag\Model\UserTag;
use User\Model\Recoveryemails;
use User\Form\Login;       
use User\Form\LoginFilter; 
use User\Form\ResetPassword;
use Facebook\Controller\Facebook;
use Facebook\Controller\FacebookApiException;
use Application\Controller\Plugin\ServiceUploadHandler;
use Application\Controller\Plugin\ResizeImage;

class IndexController extends AbstractActionController
{
    public $form_error ;
	protected $userTable;
	protected $userProfileTable;
	protected $userFriendTable;
	protected $userGroupTable;
	protected $userTagTable;
	protected $RecoveryemailsTable;
    protected $userProfilePhotoTable;
    protected $groupTable;
    protected $activityTable;
    protected $discussionTable;
    protected $likeTable;
    protected $commentTable;
    protected $activityRsvpTable;
    protected $groupMediaTable;
    protected $WEB_STAMPTIME;
    protected $tagCategoryTable;
    protected $groupTagTable;

	public function  __construct() {
        $this->facebook = new Facebook(array(
            'appId'  => '739393236113308',
            'secret' => '9da375419c2da6d66b7237673b285ff0'
        ));
        $this->flagSuccess = "Success";
		$this->flagError = "Failure";
    }

	public function registerAction(){
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$postedValues = $this->getRequest()->getPost();
			$str = $this->getRequest()->getContent();

			if ((!isset($postedValues['name'])) || (trim($postedValues['name']) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Name is required.";
				echo json_encode($dataArr);
				exit;
			}

			if ((!isset($postedValues['email'])) || (trim($postedValues['email']) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Email ID is required.";
				echo json_encode($dataArr);
				exit;
			}

			if(!filter_var(trim($postedValues['email']), FILTER_VALIDATE_EMAIL)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Invalid Email ID.";
				echo json_encode($dataArr);
				exit;
			}

			if ((!isset($postedValues['password'])) || (trim($postedValues['password']) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Password is required.";
				echo json_encode($dataArr);
				exit;
			}
			if ((!isset($postedValues['country_id'])) || (trim($postedValues['country_id']) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Country is required.";
				echo json_encode($dataArr);
				exit;
			}else{  
				if(!is_numeric($postedValues['country_id'])){
					$dataArr[0]['flag'] = "Failure";
					$dataArr[0]['message'] = "Enter a valid country id.";
					echo json_encode($dataArr);
					exit;
				}
			}
			if ((!isset($postedValues['city_id'])) || (trim($postedValues['city_id']) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "City is required.";
				echo json_encode($dataArr);
				exit;
			}else{
				if(!is_numeric($postedValues['city_id'])){
					$dataArr[0]['flag'] = "Failure";
					$dataArr[0]['message'] = "Enter a valid city id.";
					echo json_encode($dataArr);
					exit;
				}
			}
			$password = strip_tags($postedValues['password']);
			$password = trim($password);
			$email = strip_tags($postedValues['email']);
			$email = trim($email);
			$name = strip_tags($postedValues['name']);
			$name = trim($name);
			$user_details = $this->getUserTable()->getUserFromEmail(trim($email));
			if ($user_details) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Email ID already registered with us.";
				echo json_encode($dataArr);
				exit;
			}
			$bcrypt = new Bcrypt();
			$data['user_password'] = $bcrypt->create($password);
			$user_verification_key = md5('enckey'.rand().time());
			$data['user_verification_key'] = $user_verification_key;
			$data['user_profile_name'] = $this->make_url_friendly($name);
			$data['user_email'] = $email;
			$data['user_given_name'] = $name;
			$data['user_status'] = "not activated";
			$user = new User();
			$user->exchangeArray($data);
			$insertedUserId = $this->getUserTable()->saveUser($user);
			$user_id = $insertedUserId;
			$uniqueToken = $user_id."#".uniqid();
			$encodedUniqToken = base64_encode($uniqueToken);
			$data['user_accessToken'] = $encodedUniqToken;
			$user_details = $this->getUserTable()->getUserFromEmail($email);
			$this->getUserTable()->updateUser($data,$user_details->user_id);
			if($insertedUserId){
				$profile_data['user_profile_user_id'] = $insertedUserId;
				$profile_data['user_profile_country_id'] = strip_tags($postedValues['country_id']);
				$profile_data['user_profile_city_id'] = strip_tags($postedValues['city_id']);
				$profile_data['user_profile_status'] = "available";
				$userProfile = new UserProfile();
				$userProfile->exchangeArray($profile_data);
				$insertedUserProfileId = $this->getUserProfileTable()->saveUserProfileApi($userProfile);					 
				$this->sendVerificationEmail($user_verification_key,$insertedUserId,$data['user_email']);
				$dataArr = $this->getAllUserRelatedDetails($user_details->user_id,$data['user_accessToken']);
				echo json_encode($dataArr);
				exit;
			} else{
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Some error occurred. Please try again.";
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

    public function fbregisterAction(){
		$request = $this->getRequest();
		$UserData = array();
		if($this->getRequest()->getMethod() == 'POST') {
			$postedValues = $this->getRequest()->getPost();
			$str = $this->getRequest()->getContent();
			$user_details = array();
			$fbid =(isset($postedValues['fbid']))?trim($postedValues['fbid']):'';
			$accesstoken = (isset($postedValues['accesstoken']))?trim($postedValues['accesstoken']):'';
			if(empty($fbid)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Fb id required";
				echo json_encode($dataArr);
				exit;
			}
			if(empty($accesstoken)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Access token required";
				echo json_encode($dataArr);
				exit;
			}
			if (!empty($fbid)&&!empty($accesstoken)) {
				$user_profile = array();
				try{
					$this->facebook->setAccessToken($accesstoken);
					$user_profile = $this->facebook->api('/me');
				} catch(Exception $e){
					 $dataArr[0]['flag'] = "Failure";
					$dataArr[0]['message'] = "Invalid Access Token";
					echo json_encode($dataArr);
					exit;
				}
				if(!empty($user_profile)){
					if($fbid == $user_profile['id']){
						$UserData = $this->getUserTable()->getUserByFbid(strip_tags(trim($user_profile['id'])));
						if(!empty($UserData)&&$UserData->user_id){
							$user_details = $UserData;
						}elseif(!empty($user_profile['email']) && filter_var($user_profile['email'], FILTER_VALIDATE_EMAIL)){
							$user_details = $this->getUserTable()->getUserFromEmail(strip_tags(trim($user_profile['email'])));
						}else{
							$user_details = $UserData;
						}
						if(!empty($user_details)&&!empty($user_details->user_id)){
							$insertedUserId = $user_details->user_id;
							if($user_details->user_status == 'not activated'){
								$data = array('user_status'=>"live");
								$this->getUserTable()->updateUser($data,$user_details->user_id);	
							}
							if($user_details->user_fbid == ''){
								$data = array('user_fbid'=>$user_profile['id']);
								$this->getUserTable()->updateUser($data,$user_details->user_id);	
							}
						}else{
							$user_data['user_given_name'] =  (isset($user_profile['name'])&&!empty($user_profile['name']))?$user_profile['name']:'unknown';					 
							$user_data['user_profile_name'] = $this->make_url_friendly($user_profile['name']);
							$user_data['user_status'] = "live";
							$user_data['user_email'] = (isset($user_profile['email'])&& filter_var($user_profile['email'], FILTER_VALIDATE_EMAIL))?$user_profile['email']:NULL;					 
							$user_data['user_register_type'] = 'facebook';
							$user_data['user_fbid'] =$user_profile['fbid']; 
							$user = new User();
							$user->exchangeArray($user_data);
							$insertedUserId = $this->getUserTable()->saveUser($user);
							if($insertedUserId){
								$user_profile_data['user_profile_user_id'] = $insertedUserId;
								$user_profile_data['user_profile_emailme_id'] = '';
								$user_profile_data['user_profile_notifyme_id'] = '';
								$userProfile = new UserProfile();
								$userProfile->exchangeArray($user_profile_data);
								$insertedUserProfileId = $this->getUserProfileTable()->saveUserProfile($userProfile);
							}
						}
						if($insertedUserId) {
							$uniqueToken = $postedValues['fbid']."#".uniqid();
							$encodedUniqToken = base64_encode($uniqueToken);
							$access_data['user_accessToken'] = $encodedUniqToken;		 
							$this->getUserTable()->updateUser($access_data,$insertedUserId);					
							$dataArr = $this->getAllUserRelatedDetails($insertedUserId,$access_data['user_accessToken']);
							echo json_encode($dataArr);
							exit;
						} else {
							$dataArr[0]['flag'] = "Failure";
							$dataArr[0]['message'] = "Some Error Occurred. Please Try Again.";
							echo json_encode($dataArr);
							exit;
						}
					}else{
						$dataArr[0]['flag'] = "Failure";
						$dataArr[0]['message'] = "Fb id mismatch";
						echo json_encode($dataArr);
						exit;
					}
				}else{
					$dataArr[0]['flag'] = "Failure";
					$dataArr[0]['message'] = "Invalid access tocken";
					echo json_encode($dataArr);
					exit;
				}			
			}else {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "No Input Parameters.";
				echo json_encode($dataArr);
				exit;
			}		
		} else {
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request Not Authorised.";
			echo json_encode($dataArr);
			exit;
		}
    }
		
	public function loginAction(){
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$postedValues = $this->getRequest()->getPost();
			$password = strip_tags($postedValues['password']);
			$password = trim($password);
			$email = strip_tags($postedValues['email']);
			$email = trim($email);

			if ((!isset($email)) || ($email == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Email ID is required.";
				echo json_encode($dataArr);
				exit;
			}
			if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Invalid Email ID.";
				echo json_encode($dataArr);
				exit;
			}
			if ((!isset($password)) || ($password == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Password is required.";
				echo json_encode($dataArr);
				exit;
			}

			$dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
	
			$authAdapter = new AuthAdapter($dbAdapter);
	
			$authAdapter
				->setTableName('y2m_user')
				->setIdentityColumn('user_email')
				->setCredentialColumn('user_password');					
	
			$authAdapter
				->setIdentity(addslashes($email))
				->setCredential($password);
	
			$result = $authAdapter->authenticate();

			if (!$result->isValid()) {
				$user_details = $this->getUserTable()->getUserFromEmail($email);
				if (empty($user_details)) {
					$dataArr[0]['flag'] = "Failure";
					$dataArr[0]['message'] = "Email ID does not exists.";
					echo json_encode($dataArr);
					exit;
				} else {
					$dataArr[0]['flag'] = "Failure";
					$dataArr[0]['message'] = "Email ID or Password is incorrect.";
					echo json_encode($dataArr);
					exit;
				}
			} else {
				$user_details = $this->getUserTable()->getUserFromEmail($email);
				$set_secretcode = $this->updateAccessToken($email,$user_details->user_id);
				$dataArr = $this->getAllUserRelatedDetails($user_details->user_id,$set_secretcode);
				echo json_encode($dataArr);
				exit;
			}
		} else {
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request Not Authorised.";
			echo json_encode($dataArr);
			exit;
		}
	}

	public function updateAccessToken($email,$user_id){
		$set_secretcode = $this->rec_create_secretcode($email);
		$data['user_accessToken'] = $set_secretcode;
		$data['user_temp_accessToken'] = $set_secretcode;
		$this->getUserTable()->updateUser($data,$user_id);
		$set_timestamp = $this->rec_create_timestamp();
		$data_array = compact('set_timestamp');
		$this->getUserTable()->updateUser($data_array,$user_id);
		return $set_secretcode;
	}

	public function userfulldetailsAction(){
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {

			$config = $this->getServiceLocator()->get('Config');
			$postedValues = $this->getRequest()->getPost();

			$userID = trim($postedValues['userID']);
			$accToken = strip_tags(trim($postedValues['accesstoken']));
            $friend_status ="";
			if ((!isset($accToken)) || (trim($accToken) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            if ( empty($userinfo) ){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Invalid Access Token.";
                echo json_encode($dataArr);
                exit;
            }
			if ((!isset($userID)) || (trim($userID) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			if (isset($userID) && !is_numeric($userID)) {
 				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid userID.";
				echo json_encode($dataArr);
			}

            $user_details = $this->getUserTable()->getUser($userID);
            if(empty($user_details)){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "User not exist in the system.";
                echo json_encode($dataArr);
                exit;
            }

			$dataArr = $this->getAllUserRelatedDetails($user_details->user_id,$user_details->user_accessToken);

            $is_friend = ($this->getUserFriendTable()->isFriend($user_details->user_id,$userinfo->user_id))?1:0;
            $is_requested = ($this->getUserFriendTable()->isRequested($user_details->user_id,$userinfo->user_id))?1:0;
            $is_pending = ($this->getUserFriendTable()->isPending($user_details->user_id,$userinfo->user_id))?1:0;
            if($is_friend){
                $friend_status = 'IsFriend';
            }
            else if($is_requested){
                $friend_status = 'RequestSent';
            }
            else if($is_pending){
                $friend_status = 'RequestPending';
            }
            else if ( $userinfo->user_id == $user_details->user_id ){
                $friend_status = '';
            }else{
                $friend_status = 'NoFriends';
            }
            $dataArr[0]['friendship_status'] = $friend_status;
			echo json_encode($dataArr);
			exit;
		}else{
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request Not Authorised.";
			echo json_encode($dataArr);
			exit;
		}
	}

    public function guestaccessAction(){
        $request = $this->getRequest();
        if($request->getMethod() == 'POST') {
            $postedValues = $this->getRequest()->getPost();
            $offset = trim($postedValues['nparam']);
            $limit = trim($postedValues['countparam']);

            if (isset($limit) && !is_numeric($limit)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a Valid Count Field.";
                echo json_encode($dataArr);
                exit;
            }
            if (isset($offset) && !is_numeric($offset)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a Valid N Field.";
                echo json_encode($dataArr);
                exit;
            }

            $offset = (int) $offset;
            $limit = (int) $limit;
            $offset =($offset>0)?$offset-1:0;
            $offset = $offset*$limit;
            $groups = $this->getGroupsTable()->generalGroupList($limit, $offset);
            $group_general_list = array();
            $config = $this->getServiceLocator()->get('Config');

            if (count($groups)){
                foreach ($groups as $list) {
                    if (!empty($list['group_photo_photo']))
                        $list['group_photo_photo'] = $config['pathInfo']['absolute_img_path'] . $config['image_folders']['group'] . $list['group_id'] . '/medium/' . $list['group_photo_photo'];
                    else
                        $list['group_photo_photo'] = $config['pathInfo']['absolute_img_path'] . '/images/group-img_def.jpg';
                    $tag_category = $this->getGroupTagTable()->getAllGroupTagCategiry($list['group_id']);
                    $tags = $this->getGroupTagTable()->getAllGroupTagsForAPI($list['group_id']);
                    if (!empty($tags)) {
                        $tags = $this->formatTagsWithCategory($tags,"|");
                    }
                    $group_general_list[] = array(
                        'group_id' => $list['group_id'],
                        'group_title' => $list['group_title'],
                        'group_seo_title' => $list['group_seo_title'],
                        'group_description' => 'group_description',
                        'group_location' => $list['group_location'],
                        'group_city_id' => $list['group_city_id'],
                        'group_country_id' => $list['group_country_id'],
                        'group_photo_photo' => $list['group_photo_photo'],
                        'group_location_lat' => $list['group_location_lat'],
                        'group_location_lng' => $list['group_location_lng'],
                        'group_web_address' => $list['group_web_address'],
                        'country_title' => $list['country_title'],
                        'country_code' => $list['country_code'],
                        'city' => $list['city'],
                        'member_count' => $list['member_count'],
                        'friend_count' => $list['friend_count'],
                        'is_admin' => $list['is_admin'],
                        'is_member' => $list['is_member'],
                        'tag_categories_count' => count($tag_category),
                        'tags' => $tags,
                    );
                }
            }

            $dataArr[0]['flag'] = "Success";
            $dataArr[0]['groups'] = $group_general_list;
            echo json_encode($dataArr);
            exit;
        }
        else{
            $dataArr[0]['flag'] = "Failure";
            $dataArr[0]['message'] = "No details available.";
            echo json_encode($dataArr);
            exit;
        }
    }

	public function userpostsAction(){
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$config = $this->getServiceLocator()->get('Config');
			$postedValues = $this->getRequest()->getPost();

			$type = trim($postedValues['type']);
			$offset = trim($postedValues['nparam']);
			$limit = trim($postedValues['countparam']);

            $userID = trim($postedValues['userID']);
            $accToken = strip_tags(trim($postedValues['accesstoken']));

            if ((!isset($accToken)) || (trim($accToken) == '')) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            if ( empty($userinfo) ){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Invalid Access Token.";
                echo json_encode($dataArr);
                exit;
            }
            if ((!isset($userID)) || (trim($userID) == '')) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
            if (isset($userID) && !is_numeric($userID)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a Valid userID.";
                echo json_encode($dataArr);
            }

            $postedValues = $this->getRequest()->getPost();
            $user_details = $this->getUserTable()->getUser($userID);
            if(empty($user_details)){
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "User not exist in the system.";
                echo json_encode($dataArr);
                exit;
            }
			if (isset($limit) && !is_numeric($limit)) {
 				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid Count Field.";
				echo json_encode($dataArr);
				exit;		
			}
			if (isset($offset) && !is_numeric($offset)) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid N Field.";
				echo json_encode($dataArr);
				exit;
			}
			$user_id = $user_details->user_id;
            $offset = (int) $offset;
            $limit = (int) $limit;
            $offset =($offset>0)?$offset-1:0;
            $offset = $offset*$limit;
			$newsfeedsList = $this->getGroupsTable()->getMyFeeds($user_id,$userinfo->user_id,$type,(int) $limit,(int) $offset);
            $user_profile_pic = $this->getUserTable()->getUserProfilePic($user_details->user_id);
            $profileDetails = $this->getUserTable()->getProfileDetails($user_details->user_id);
            $user_details_photo = $this->manipulateProfilePic($user_details->user_id, $user_profile_pic->biopic, $user_details->user_fbid);
            $userprofiledetails[] = array('user_id'=>$user_details->user_id,
                'user_given_name'=>$user_details->user_given_name,
                'user_profile_name'=>$user_details->user_profile_name,
                'user_email'=>$user_details->user_email,
                'user_status'=>$user_details->user_status,
                'user_fbid'=>$user_details->user_fbid,
                'user_profile_about_me'=>$profileDetails->user_profile_about_me,
                'user_profile_current_location'=>$profileDetails->user_profile_current_location,
                'user_profile_phone'=>$profileDetails->user_profile_phone,
                'country_title'=>$profileDetails->country_title,
                'country_code'=>$profileDetails->country_code,
                'country_id'=>$profileDetails->country_id,
                'city_name'=>$profileDetails->city_name,
                'city_id'=>$profileDetails->city_id,
                'profile_photo'=>$user_details_photo,
            );
            if(!empty($newsfeedsList)){
                foreach($newsfeedsList as $list){

                    switch($list['type']){
                        case "New Activity":
                            $activity_details = array();
                            $activity = $this->getActivityTable()->getActivityForFeed($list['event_id'],$user_id);
                            $SystemTypeData   = $this->getGroupsTable()->fetchSystemType("Activity");
                            $like_details     = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$list['event_id'],$user_id);
                            $comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$list['event_id'],$user_id);
                            $str_liked_users  = '';

                            if(!empty($like_details)&&isset($like_details['likes_counts'])){
                                $liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$list['event_id'],$user_id,2,0);
                            }
                            $rsvp_count = $this->getActivityRsvpTable()->getCountOfAllRSVPuser($activity->group_activity_id)->rsvp_count;
                            $attending_users = array();
                            $tempattendusers =  array();
                            if($rsvp_count>0){
                                $attending_users = $this->getActivityRsvpTable()->getJoinMembers($activity->group_activity_id,3,0);
                            }
                            if (count($attending_users)){
                                foreach ($attending_users as $attendlist) {
                                    unset($attendlist['group_activity_rsvp_id']);
                                    unset($attendlist['group_activity_rsvp_user_id']);
                                    unset($attendlist['group_activity_rsvp_activity_id']);
                                    unset($attendlist['group_activity_rsvp_added_timestamp']);
                                    unset($attendlist['group_activity_rsvp_added_ip_address']);
                                    unset($attendlist['group_activity_rsvp_group_id']);

                                    $attendlist['profile_photo'] = $this->manipulateProfilePic($attendlist['user_id'], $attendlist['profile_photo'], $attendlist['user_fbid']);

                                    $tempattendusers[]=$attendlist;
                                }
                                $attending_users = $tempattendusers;
                            }
                            $activity_details[] = array(
                                "group_activity_id" => $activity->group_activity_id,
                                "group_activity_title" => $activity->group_activity_title,
                                "group_activity_location" => $activity->group_activity_location,
                                "group_activity_location_lat" => $activity->group_activity_location_lat,
                                "group_activity_location_lng" => $activity->group_activity_location_lng,
                                "group_activity_content" => $activity->group_activity_content,
                                "group_activity_start_timestamp" => date("M d,Y H:s a",strtotime($activity->group_activity_start_timestamp)),
                                "group_title" =>$list['group_title'],
                                "group_seo_title" =>$list['group_seo_title'],
                                "group_id" =>$list['group_id'],
                                "like_count"	=>$like_details['likes_counts'],
                                "is_liked"	=>$like_details['is_liked'],
                                "comment_counts"	=>$comment_details['comment_counts'],
                                "is_commented"	=>$comment_details['is_commented'],
                                "rsvp_count" =>($activity->rsvp_count)?$activity->rsvp_count:0,
                                "rsvp_friend_count" =>($activity->friend_count)?$activity->friend_count:0,
                                "is_going"=>$activity->is_going,
                                "attending_users" =>$attending_users,
                            );
                            $feeds[] = array('content' => $activity_details,
                                'type'=>$list['type'],
                                'time'=>$this->timeAgo($list['update_time']),
                            );
                            break;
                        case "New Status":
                            $discussion_details = array();
                            $discussion = $this->getDiscussionTable()->getDiscussionForFeed($list['event_id']);
                            $SystemTypeData = $this->getGroupsTable()->fetchSystemType("Discussion");
                            $like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$list['event_id'],$user_id);
                            $comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$list['event_id'],$user_id);
                            $str_liked_users = '';
                            if(!empty($like_details)&&isset($like_details['likes_counts'])){
                                $liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$list['event_id'],$user_id,2,0);
                            }
                            $discussion_details[]= array(
                                "group_discussion_id" => $discussion->group_discussion_id,
                                "group_discussion_content" => $discussion->group_discussion_content,
                                "group_title" =>$list['group_title'],
                                "group_seo_title" =>$list['group_seo_title'],
                                "group_id" =>$list['group_id'],
                                "like_count"	=>$like_details['likes_counts'],
                                "is_liked"	=>$like_details['is_liked'],
                                "comment_counts"	=>$comment_details['comment_counts'],
                                "is_commented"	=>$comment_details['is_commented'],
                            );
                            $feeds[] = array('content' => $discussion_details,
                                'type'=>$list['type'],
                                'time'=>$this->timeAgo($list['update_time']),
                            );
                            break;
                        case "New Media":
                            $media_details = array();
                            $media = $this->getGroupMediaTable()->getMediaForFeed($list['event_id']);
                            $video_id  = '';
                            if($media->media_type == 'video')
                                $video_id  = @$this->get_youtube_id_from_url($media->media_content);
                            $SystemTypeData = $this->getGroupsTable()->fetchSystemType("Media");
                            $like_details  = $this->getLikeTable()->fetchLikesCountByReference($SystemTypeData->system_type_id,$list['event_id'],$user_id);
                            $comment_details  = $this->getCommentTable()->fetchCommentCountByReference($SystemTypeData->system_type_id,$list['event_id'],$user_id);
                            $str_liked_users = '';
                            if(!empty($like_details)&&isset($like_details['likes_counts'])){
                                $liked_users = $this->getLikeTable()->likedUsersWithoutLoggedOneWithFriendshipStatus($SystemTypeData->system_type_id,$list['event_id'],$user_id,2,0);
                            }
                            if (!empty($media->media_content)){
                                if($media->media_type == 'video'){
                                    $media->media_content =	'http://img.youtube.com/vi/'.$video_id.'/0.jpg';
                                }else{
                                    $media->media_content = $config['pathInfo']['absolute_img_path'].$config['image_folders']['group'].$list['group_id'].'/media/medium/'.$media->media_content;
                                }
                            }
                            $media_details[] = array(
                                "group_media_id" => $media->group_media_id,
                                "media_type" => $media->media_type,
                                "media_content" => $media->media_content,
                                "media_caption" => $media->media_caption,
                                "video_id" => $video_id,
                                "group_title" =>$list['group_title'],
                                "group_seo_title" =>$list['group_seo_title'],
                                "group_id" =>$list['group_id'],
                                "like_count"	=>$like_details['likes_counts'],
                                "is_liked"	=>$like_details['is_liked'],
                                "comment_counts"	=>$comment_details['comment_counts'],
                                "is_commented"	=>$comment_details['is_commented'],
                            );
                            $feeds[] = array('content' => $media_details,
                                'type'=>$list['type'],
                                'time'=>$this->timeAgo($list['update_time']),
                            );
                            break;
                    }
                }
                $dataArr[0]['flag'] = "Success";
                $dataArr[0]['userposts'] = $feeds;
                $dataArr[0]['postedby'] = $userprofiledetails;
                echo json_encode($dataArr);
                exit;
            }
            else{
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "No details available.";
                echo json_encode($dataArr);
                exit;
            }
		}else{
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request Not Authorised.";
			echo json_encode($dataArr);
			exit;
		}
	}
		
	public function loginaccessAction(){
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$postedValues = $this->getRequest()->getPost();
			if ((!isset($postedValues['email'])) || ($postedValues['email'] == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "User Name is required.";
				echo json_encode($dataArr);
				exit;
			}
			if ((!isset($postedValues['password'])) || ($postedValues['password'] == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Password is required.";
				echo json_encode($dataArr);
				exit;
			}
			if ((!isset($postedValues['accesstoken'])) || ($postedValues['accesstoken'] == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Access Token is required.";
				echo json_encode($dataArr);
				exit;
			}
			$user_details = $this->getUserTable()->getUserFromEmail($postedValues['email']);
			if (!$user_details) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Oops! Something went wrong.";
				echo json_encode($dataArr);
				exit;
			} else {
				
				$userId = $user_details->user_id;
				$secretcode = $postedValues['accesstoken'];
				$username = $postedValues['email'];
				$set_secretcode = $this->rec_create_secretcode($username);
				if ($secretcode != $set_secretcode) {
					$dataArr[0]['flag'] = "Failure";
					$dataArr[0]['message'] = "Your token has been expired.";
					echo json_encode($dataArr);
					exit;                 
				} else {
					$dbAdapter = $this->getServiceLocator()->get('Zend\Db\Adapter\Adapter');
					$authAdapter = new AuthAdapter($dbAdapter);
					$authAdapter
						->setTableName('y2m_user')
						->setIdentityColumn('user_email')
						->setCredentialColumn('user_password');
					$authAdapter
						->setIdentity(addslashes($postedValues['email']))
						->setCredential($postedValues['password']);
					$result = $authAdapter->authenticate();
					if ($result->isValid()) {
						$auth = new AuthenticationService();
						$storage = $auth->getStorage();
						$storage->write($authAdapter->getResultRowObject(null,'user_password'));
						$dataArr[0]['flag'] = "Success";
						$dataArr[0]['message'] = "Login Successful.";
						echo json_encode($dataArr);
						exit; 
					}
				}
			}
		} else {
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request Not Authorised.";
			echo json_encode($dataArr);
			exit;
		}
	}

    public function get_youtube_id_from_url($url){
        if (stristr($url,'youtu.be/'))
        {preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $final_ID); return $final_ID[4]; }
        else
        {@preg_match('/(https:|http:|):(\/\/www\.|\/\/|)(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $IDD); return $IDD[5]; }
    }

    public function timeAgo($time_ago){ //echo $time_ago;die();
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

	public function rec_create_secretcode($email){
        $user_details = $this->getUserTable()->getUserFromEmail($email);
		
        if ($user_details->set_timestamp != '') {
            $current_timestamp = $this->rec_create_timestamp(); //get current time stamp
 
            $diff = ( strtotime($current_timestamp) - strtotime($user_details->set_timestamp) ); //check time difference
            $minutes = round(((($diff % 604800) % 86400) % 3600) / 60, 2); //minute difference
			
            if ($minutes <= 2) {
                $set_timestamp = $user_details->set_timestamp;				
                $user_temp_accessToken = $user_details->user_temp_accessToken;
				return $user_temp_accessToken;
			} else {
                $set_timestamp = $current_timestamp;  //timestamp is expired
			}       
        }
        else {
            $set_timestamp = $this->rec_create_timestamp();
        }
        $set_secretcode = md5($user_details->user_id . $set_timestamp); 
        return $set_secretcode; //return secret code                           
    }
	
    public function rec_create_timestamp(){
        $currentTime = date('Y-m-d H:i');
        $currentDate = strtotime($currentTime);
        $futureDate = $currentDate + $this->WEB_STAMPTIME;
        $set_timestamp = date("Y-m-d H:i", $futureDate);
        return $set_timestamp;
    }
		
	public function logoutAction(){
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$postedValues = $this->getRequest()->getPost();
			if ((!isset($postedValues['accesstoken'])) || ($postedValues['accesstoken'] == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Accesstoken is required.";
				echo json_encode($dataArr);
				exit;
			}
			$user_details = $this->getUserTable()->getUserByAccessToken($postedValues['accesstoken']);
			if (empty($user_details)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Invalid Access Token.";
				echo json_encode($dataArr);
				exit;
			}else{
				$data['user_accessToken'] = '';				 
				$this->getUserTable()->updateUser($data,$user_details->user_id);
				$auth = new AuthenticationService();
				$auth->clearIdentity();
				unset($_SESSION);
				$dataArr[0]['flag'] = "Success";
				$dataArr[0]['message'] = "you have been logged out successfully.";
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
	
	public function forgotPasswordAction(){
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$postedValues = $this->getRequest()->getPost();
			if ((!isset($postedValues['email'])) || ($postedValues['email'] == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Email is required.";
				echo json_encode($dataArr);
				exit;
			} else {
				$user_data =  $this->getUserTable()->getUserFromEmail($postedValues['email']);
				if(empty($user_data)){
					$dataArr[0]['flag'] = "Failure";
					$dataArr[0]['message'] = "This email is not existing in this system.";
					echo json_encode($dataArr);
					exit;
				} else {
					$data['user_id'] = $user_data->user_id;
					$secret_code = time().rand();
					$data['secret_code'] = $secret_code;
					$data['user_email'] = $user_data->user_email;
					$data['status'] = 0;
					$recoveremails = new Recoveryemails();
					$recoveremails->exchangeArray($data);
					$recoveremails->exchangeArray($data);
					$this->getRecoveremailsTable()->ResetAllActiveRequests($user_data->user_id);
					$insertedRecoveryId = $this->getRecoveremailsTable()->saveRecovery($recoveremails);
					if($insertedRecoveryId){
						$this->sendPasswordResetMail($secret_code,$insertedRecoveryId,$user_data->user_email);
						$dataArr[0]['flag'] = "Success";
						$dataArr[0]['message'] = "Password reset option send to your email. Please check your email and follow the steps.";
						echo json_encode($dataArr);
						exit;
					}
				}
			}
		} else {
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request not authorised.";
			echo json_encode($dataArr);
			exit;
		}
	}

    public function editProfileAction(){
        $error = '';
        $request   = $this->getRequest();
        if ($request->isPost()){
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $myinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $userinfo = array();
            if(!empty($myinfo)){
                $data_profile = array();
                $data = array();
                if($post['username']!=''&&$post['countryID']!=''&&$post['cityID']!=''){
                    $data['user_given_name'] = trim(strip_tags($post['username']));
                    $data_profile['user_profile_user_id'] = $myinfo->user_id;
                    if (!empty($post['cityID']) && !is_numeric($post['cityID'])) {
                        $dataArr[0]['flag'] = "Failure";
                        $dataArr[0]['message'] = "Please input a Valid City ID.";
                        echo json_encode($dataArr);
                        exit;
                    }
                    if (!empty($post['countryID']) && !is_numeric($post['countryID'])) {
                        $dataArr[0]['flag'] = "Failure";
                        $dataArr[0]['message'] = "Please input a Valid Country ID.";
                        echo json_encode($dataArr);
                        exit;
                    }
                    $data_profile['user_profile_city_id'] = $post['cityID'];
                    $data_profile['user_profile_country_id'] = $post['countryID'];

                    $data_profile['user_profile_about_me'] = trim(strip_tags($post['aboutme']));
                    if($this->getUserTable()->updateUser($data,$myinfo->user_id)){
                        $this->getUserProfileTable()->updateUserProfile($data_profile,$myinfo->user_id);
                        $userinfo = $this->getAllUserRelatedDetails($myinfo->user_id,$myinfo->user_accessToken);
                    }else{$error = "Some error occured. Please try again";}
                }else{$error = "Name, country and city fields are mandatory";}
            }else{$error = "User not exist in the system";}
        }else{$error = "Request Not Authorized";}

        $dataArr[0]['flag'] = (empty($error))?'success':'failure';
        $dataArr[0]['message'] = (empty($error))?"Profile edited successfully":$error;
        $dataArr[0]['userdetails'] = $userinfo;
        echo json_encode($dataArr);
        exit;
    }

    public function editProfilePicAction(){
        $error = '';
        $request   = $this->getRequest();
        if ($request->isPost()){
            $post = $request->getPost();
            $file = $request->getFiles();
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            if(!empty($userinfo)&&$userinfo->user_id){
                if(!empty($post)){
                    if(isset($file)&&isset($file['profilepic']['name'])&&$file['profilepic']['name']!='') {
                        if (@is_array(getimagesize($file['profilepic']['tmp_name']))) {
                            $config = $this->getServiceLocator()->get('Config');
                            $upload_path = $config['pathInfo']['ROOTPATH'].'/public/';
                            $filename = 'profile_' . $userinfo->user_id . '' . time() . '.png';
                            $options['script_url'] = $config['pathInfo']['base_url'];
                            $options['upload_dir'] = $upload_path . $config['image_folders']['profile_path'] . $userinfo->user_id . '/';
                            $options['upload_url'] = $config['pathInfo']['absolute_img_path'] . $config['image_folders']['profile_path'] . $userinfo->user_id . '/' . $filename;
                            $options['param_name'] = 'profilepic';
                            $options['min_width'] = 50;
                            $options['min_height'] = 50;
                            if (!is_dir($upload_path . $config['image_folders']['profile_path'] . $userinfo->user_id)) {
                                mkdir($upload_path . $config['image_folders']['profile_path'] . $userinfo->user_id);
                            }

                            $upload_handler = new ServiceUploadHandler($options);
                            if (isset($upload_handler->image_objects['filename']) && $upload_handler->image_objects['filename'] != '') {
                                if ($error == '') {
                                    rename($options['upload_dir'] . $upload_handler->image_objects['filename'], $options['upload_dir'].$filename);
                                    $photodata['profile_user_id'] = $userinfo->user_id;
                                    $photodata['profile_photo'] = $filename;
                                    $photodata['user_profile_photo_status'] = 'available';
                                    $insert_id = $this->getUserProfilePhotoTable()->addUserProfilePic($photodata);
                                    $user_data = array();
                                    if ($insert_id) {
                                        $user_data['user_profile_photo_id'] = $insert_id;
                                        $this->getUserTable()->updateUser($user_data, $userinfo->user_id);
                                    } else {
                                        $error = "Some error occured. Please try again";
                                    }
                                    $userdetails = $this->getAllUserRelatedDetails($userinfo->user_id,$userinfo->user_accessToken);
                                }
                            } else {
                                $dataArr[0]['flag'] = "Failure";
                                $error = "Some error occured, during file upload. Please try again";
                            }
                        } else {
                            $dataArr[0]['flag'] = "Failure";
                            $error = "Please upload a valid image";
                        }
                    }
                    else {
                        $dataArr[0]['flag'] = "Failure";
                        $error = "Select a image to upload and continue";
                    }
                    //----
                }else{$error = "Unable to process";}
            }else{$error = "User not exist in the system";}
        }else{$error = "Request Not Authorized";}
        $dataArr[0]['flag'] = (empty($error))?'success':'failure';
        $dataArr[0]['message'] = (empty($error))?"Profile picture updated successfully":$error;
        if (empty($error)) $dataArr[0]['userdetails'] = $userdetails;
        echo json_encode($dataArr);
        exit;
    }

    public function checkError($error){
        if (!empty($error)){
            $dataArr[0]['flag'] = "Failure";
            $dataArr[0]['message'] = $error;
            echo json_encode($dataArr);
            exit;
        }
    }

	public function sendPasswordResetMail($user_verification_key,$insertedRecoveryid,$emailId){

		$this->renderer = $this->getServiceLocator()->get('ViewRenderer');	 
		$user_recoverId = md5(md5('recoverid~'.$insertedRecoveryid));
		$body = $this->renderer->render('user/email/emailResetPassword.phtml', array('user_verification_key'=>$user_verification_key,'user_recoverId'=>$user_recoverId));
		$htmlPart = new MimePart($body);
		$htmlPart->type = "text/html";

		$textPart = new MimePart($body);
		$textPart->type = "text/plain";

		$body = new MimeMessage();
		$body->setParts(array($textPart, $htmlPart));

		$message = new Mail\Message();
		$message->setFrom('admin@jeera.com');
		$message->addTo($emailId);

		//$message->addReplyTo($reply);							 

		$message->setSender("Jeera");
		$message->setSubject("Reset password request");
		$message->setEncoding("UTF-8");
		$message->setBody($body);
		$message->getHeaders()->get('content-type')->setType('multipart/alternative');

		$transport = new Mail\Transport\Sendmail();
		$transport->send($message);

		return true;
	}
	
	public function sendVerificationEmail($user_verification_key,$insertedUserId,$emailId){
		$this->renderer = $this->getServiceLocator()->get('ViewRenderer');	 
		$user_insertedUserId = md5(md5('userId~'.$insertedUserId));
		$body = $this->renderer->render('user/email/emailVarification.phtml', array('user_verification_key'=>$user_verification_key,'user_insertedUserId'=>$user_insertedUserId));
		$htmlPart = new MimePart($body);
		$htmlPart->type = "text/html";

		$textPart = new MimePart($body);
		$textPart->type = "text/plain";

		$body = new MimeMessage();
		$body->setParts(array($textPart, $htmlPart));

		$message = new Mail\Message();
		$message->setFrom('admin@jeera.com');
		$message->addTo($emailId);

		//$message->addReplyTo($reply);							 

		$message->setSender("Jeera");
		$message->setSubject("Registration confirmation");
		$message->setEncoding("UTF-8");
		$message->setBody($body);
		$message->getHeaders()->get('content-type')->setType('multipart/alternative');

		$transport = new Mail\Transport\Sendmail();
		$transport->send($message);
		return true;
	}

	public function getAllUserRelatedDetails($user_id, $set_secretcode){ 
		$config = $this->getServiceLocator()->get('Config');
		$swapusertags = array();
		$profileDetails = array();
		$usertags = array();
		$groupCountDetails = array();
		$friends = array();
		$moveuserfriends = array();
		$dataArr = array();
		$user_details = array();

		$profileDetails = $this->getUserTable()->getProfileDetails($user_id);
        $tags = $this->getUserTagTable()->getAllUserTagsForAPI($user_id);
        if(!empty($tags)){
            $tags = $this->formatTagsWithCategory($tags,"|");
        }
		$groupCountDetails = $this->getUserGroupTable()->getUserGroupCount($user_id);
		unset($profileDetails->user_register_type);
		unset($profileDetails->user_profile_city_id);
		unset($profileDetails->user_profile_country_id);
		unset($profileDetails->user_profile_profession);
		unset($profileDetails->user_profile_profession_at);
		unset($profileDetails->user_address);
		unset($groupCountDetails->user_group_user_id);

		$friends = $this->getUserFriendTable()->fetchAllUserFriend($user_id,2);

		if (isset($friends)&& !empty($friends)){
			foreach ($friends as $key => $friend){
				$friend_profile_pic = $this->getUserTable()->getUserProfilePic($friend->friend_id);
				$swapuserfriends = array(
					'friend_user_id' => $friend->friend_id,
					'friend_profile_name' => $friend->user_profile_name,
					'friend_given_name' => $friend->user_given_name,
					'friend_fbid' => $friend->user_fbid,
					);
				if (isset($friend_profile_pic) && !empty($friend_profile_pic->biopic)) 
					$swapuserfriends['friend_pictureurl'] = $config['pathInfo']['absolute_img_path'].$config['image_folders']['profile_path'].$friend->friend_id.'/'.$friend_profile_pic->biopic;
				else if(isset($friend->user_fbid) && !empty($friend->user_fbid))
					$swapuserfriends['friend_pictureurl'] = 'http://graph.facebook.com/'.$friend->user_fbid.'/picture?type=normal';
				else  
					$swapuserfriends['friend_pictureurl'] = $config['pathInfo']['absolute_img_path'].'/images/noimg.jpg';
				$moveuserfriends[] = $swapuserfriends;
			}
		}

		$profile_photo = '';
		if (!empty($profileDetails->profile_photo))
			$profile_photo = $config['pathInfo']['absolute_img_path'].$config['image_folders']['profile_path'].$user_id.'/'.$profileDetails->profile_photo;
		else if(isset($profileDetails->user_fbid) && !empty($profileDetails->user_fbid))
			$profile_photo = 'http://graph.facebook.com/'.$profileDetails->user_fbid.'/picture?type=normal';
		else
			$profile_photo = $config['pathInfo']['absolute_img_path'].'/images/noimg.jpg';

		$userprofileDetails[0] = array('user_id'=>$profileDetails->user_id,
									'user_given_name'=>$profileDetails->user_given_name,									 
									'user_profile_name'=>$profileDetails->user_profile_name,
									'user_email'=>$profileDetails->user_email,
									'user_status'=>$profileDetails->user_status,
									'user_fbid'=>$profileDetails->user_fbid,
									'user_profile_about_me'=>$profileDetails->user_profile_about_me,
									'user_profile_current_location'=>$profileDetails->user_profile_current_location,
									'user_profile_phone'=>$profileDetails->user_profile_phone,
									'country_title'=>$profileDetails->country_title,
									'country_code'=>$profileDetails->country_code,
									'country_id'=>$profileDetails->country_id,
									'city_name'=>$profileDetails->city_name,
									'city_id'=>$profileDetails->city_id,
									'profile_photo'=>$profile_photo,
									);
		$dataArr[0]['flag'] = "Success";
		$dataArr[0]['accesstoken'] = $set_secretcode;
		$dataArr[0]['userfriends'] = $moveuserfriends;
		$dataArr[0]['usertags'] = $tags;
		$dataArr[0]['userprofiledetails'] = $userprofileDetails;
		
		if (!empty($groupCountDetails->group_count))
			$dataArr[0]['usergroupscount'] = $groupCountDetails->group_count;
		else 
			$dataArr[0]['usergroupscount'] = 0;

		if ($profileDetails->user_status == "live") 
			$dataArr[0]['confirmedemail'] = "yes";
		else
			$dataArr[0]['confirmedemail'] = "no";

		return $dataArr;
	}

    public function manipulateProfilePic($user_id, $profile_photo = null, $fb_id = null){
        $config = $this->getServiceLocator()->get('Config');
        $return_photo = null;
        if (!empty($profile_photo))
            $return_photo = $config['pathInfo']['absolute_img_path'].$config['image_folders']['profile_path'].$user_id.'/'.$profile_photo;
        else if(isset($fb_id) && !empty($fb_id))
            $return_photo = 'http://graph.facebook.com/'.$fb_id.'/picture?type=normal';
        else
            $return_photo = $config['pathInfo']['absolute_img_path'].'/images/noimg.jpg';
        return $return_photo;

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

	public function make_url_friendly($string){
		$string = trim($string);
		$string = preg_replace('/(\W\B)/', '',  $string); 
		$string = preg_replace('/[\W]+/',  '_', $string); 
		$string = str_replace('-', '_', $string);

		if( !empty($string) && !$this->checkProfileNameExist($string)){
			return $string; 
		}

		$length = 5;
		$randomString = substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, $length);
		$string = strtolower($string).'_'.$randomString;

		if(!$this->checkProfileNameExist($string)){ //recheck again generated name exist
			return $string; 
		} 

		$string = strtolower($string).'_'.time(); 
		return $string;		
	}

	public function checkProfileNameExist($string){
		if($this->getUserTable()->checkProfileNameExist($string)){
			return true;
		} else {
			return false;
		}
	}

	public function checkUserActive($email){
		$user_data= $this->getUserTable()->getUserFromEmail($email);
		if($user_data->user_status =='live'){return true;}else{return false;}
	}

	public function getUserTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;    
	}

    public function getGroupsTable(){
        $sm = $this->getServiceLocator();
        return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;
    }

    public function getActivityTable(){
        $sm = $this->getServiceLocator();
        return  $this->activityTable = (!$this->activityTable)?$sm->get('Activity\Model\ActivityTable'):$this->activityTable;
    }

    public function getTagCategoryTable(){
        $sm = $this->getServiceLocator();
        return  $this->tagCategoryTable = (!$this->tagCategoryTable)?$sm->get('Tag\Model\TagCategoryTable'):$this->tagCategoryTable;
    }

    public function getGroupTagTable(){
        $sm = $this->getServiceLocator();
        return  $this->groupTagTable = (!$this->groupTagTable)?$sm->get('Tag\Model\GroupTagTable'):$this->groupTagTable;
    }

    public function getDiscussionTable(){
        $sm = $this->getServiceLocator();
        return  $this->discussionTable = (!$this->discussionTable)?$sm->get('Discussion\Model\DiscussionTable'):$this->discussionTable;
    }

    public function getLikeTable(){
        $sm = $this->getServiceLocator();
        return  $this->likeTable = (!$this->likeTable)?$sm->get('Like\Model\LikeTable'):$this->likeTable;
    }

    public function getGroupMediaTable(){
        $sm = $this->getServiceLocator();
        return  $this->groupMediaTable = (!$this->groupMediaTable)?$sm->get('Groups\Model\GroupMediaTable'):$this->groupMediaTable;
    }

    public function getCommentTable(){
        $sm = $this->getServiceLocator();
        return  $this->commentTable = (!$this->commentTable)?$sm->get('Comment\Model\CommentTable'):$this->commentTable;
    }

    public function getActivityRsvpTable(){
        $sm = $this->getServiceLocator();
        return  $this->activityRsvpTable = (!$this->activityRsvpTable)?$sm->get('Activity\Model\ActivityRsvpTable'):$this->activityRsvpTable;
    }

	public function getUserProfileTable(){
		$sm = $this->getServiceLocator();
		return  $this->userProfileTable = (!$this->userProfileTable)?$sm->get('User\Model\UserProfileTable'):$this->userProfileTable;    
	}

    public function getUserProfilePhotoTable(){
        $sm = $this->getServiceLocator();
        return  $this->userProfilePhotoTable = (!$this->userProfilePhotoTable)?$sm->get('User\Model\UserProfilePhotoTable'):$this->userProfilePhotoTable;
    }

	public function getUserFriendTable(){
		$sm = $this->getServiceLocator();
		return  $this->userFriendTable = (!$this->userFriendTable)?$sm->get('User\Model\UserFriendTable'):$this->userFriendTable;    
	}

	public function getUserGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->userGroupTable = (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;    
	}

	public function getUserTagTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTagTable = (!$this->userTagTable)?$sm->get('Tag\Model\UserTagTable'):$this->userTagTable;    
	}

	public function getRecoveremailsTable(){
		$sm = $this->getServiceLocator();
		return $this->RecoveryemailsTable =(!$this->RecoveryemailsTable)?$sm->get('User\Model\RecoveryemailsTable'):$this->RecoveryemailsTable;
	}
	
}
