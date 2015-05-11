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
use User\Model\User;
use User\Model\UserFriend;
use User\Model\UserFriendRequest;
use Tag\Model\UserTag;
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;   
use Notification\Model\UserNotification; 
class FriendsController extends AbstractActionController
{
    public $form_error;
	protected $userFriendTable;
	protected $userTable;
    protected $userTagTable;
    public $flagSuccess;
	public $flagError;
    protected $userFriendRequestTable;
	protected $userNotificationTable;
	public function __construct(){
        $this->flagSuccess = "Success";
		$this->flagError = "Failure";
	}
	/**
	 * This function is used for adding user friend.
	 **/
    public function addAction(){
        $dataArr    = array();
		$request    = $this->getRequest();
		if ($request->isPost()){
			$post = $request->getPost();
			$accToken = (isset($post['accesstoken'])&&$post['accesstoken']!=null&&$post['accesstoken']!=''&&$post['accesstoken']!='undefined')?strip_tags(trim($post['accesstoken'])):'';
			if (empty($accToken)) {
				$dataArr[0]['flag'] = $this->flagError;
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			$logged_user_details = $this->getUserTable()->getUserByAccessToken($accToken);
			if(empty($logged_user_details)){
				$dataArr[0]['flag'] = $this->flagError;
				$dataArr[0]['message'] = "Invalid Access Token.";
				echo json_encode($dataArr);
				exit;
			}

            // get data
			$userId    = $logged_user_details->user_id;
			$friend_id = (isset($post['user_id'])&&$post['user_id']!=null&&$post['user_id']!=''&&$post['user_id']!='undefined')?strip_tags(trim($post['user_id'])):'';
			$operation = (isset($post['operation'])&&$post['operation']!=null&&$post['operation']!=''&&$post['operation']!='undefined')?strip_tags(trim($post['operation'])):'';

			if ((!isset($friend_id)) || ($friend_id == '')) {
				$dataArr[0]['flag'] = $this->flagError;
				$dataArr[0]['message'] = "User id is required.";
				echo json_encode($dataArr);
				exit;
			}
			if ((!isset($operation)) || ($operation == '')) {
				$dataArr = array();
				$dataArr[0]['flag'] = $this->flagError;
				$dataArr[0]['message'] = "Operation is required.";
				echo json_encode($dataArr);
				exit;
			}
			if($userId == $friend_id){
				$dataArr[0]['flag'] = $this->flagError;
				$dataArr[0]['message'] = "friend id must be different.";
				echo json_encode($dataArr);
				exit;
			}
            $user_details  = $this->getUserTable()->getUser($friend_id);
            if(!empty($user_details)){
				switch($operation){
					case 'send':
						$isFriend = $this->getUserFriendTable()->isFriend($userId,$friend_id);
						if($isFriend == 0){                        
							$arrFriendInfo = $this->getUserFriendRequestTable()->GetActiveFriendRequest($friend_id, $userId);
							if(empty($arrFriendInfo)){
								$arrFriendData= array(
                                'user_friend_request_sender_user_id'    => $userId,
                                'user_friend_request_friend_user_id'    => $friend_id,
                                'user_friend_request_status'            => 'requested',
                                'user_friend_request_added_timestamp'   => date('Y-m-d h:m:s')
								);
								// save the friend request
								$intFriendReqId                 = $this->getUserFriendRequestTable()->sendFriendRequest($arrFriendData);
								if($intFriendReqId != ''){
									$config = $this->getServiceLocator()->get('Config');
									$base_url = $config['pathInfo']['base_url'];								 
									$msg = '<a href="'.$base_url.$logged_user_details->user_profile_name.'">'.$logged_user_details->user_given_name." Sent you a friend request</a>";
									$subject = 'Friend request';
									$from = 'admin@jeera.com';
									$process = 'requested';
									$this->UpdateNotifications($user_details->user_id,$msg,1,$subject,$from,$logged_user_details->user_id,$user_details->user_id,$process);
									$dataArr[0]['flag']         = $this->flagSuccess;
									$dataArr[0]['message']      = "Friend request has been sent successfully.";
								}else{
									$dataArr[0]['flag']         = $this->flagError;
									$dataArr[0]['message']      = "Friend request has not been sent successfully.";
								}
							}else{
								$dataArr[0]['flag']             = $this->flagError;
								$dataArr[0]['message']          = "Friend request has already been sent.";
							}
						}else{
							 $dataArr[0]['flag']                = $this->flagError;
							 $dataArr[0]['message']             = "User is already friend.";
						}
					break;
					case 'accept':
						$isFriend = $this->getUserFriendTable()->isFriend($userId,$friend_id);
						if($isFriend == 0){      
							$arrFriendInfo  = $this->getUserFriendRequestTable()->GetActiveFriendRequest($friend_id, $userId);
							if(!empty($arrFriendInfo)){                           
								$intFriendStatus = $this->getUserFriendRequestTable()->makeActiveRequestTOProcessed($friend_id, $userId);
								if($intFriendStatus){
									$objUserFriend                                  = new UserFriend();                                
									$objUserFriend->user_friend_sender_user_id      = $userId;
									$objUserFriend->user_friend_friend_user_id      = $friend_id;
									$objUserFriend->user_friend_added_ip_address    = $_SERVER["SERVER_ADDR"];
									$objUserFriend->user_friend_status              = 'available';

									$insertedUserId                                 = $this->getUserFriendTable()->saveUserFriend($objUserFriend);

									if($insertedUserId != ''){
										$config = $this->getServiceLocator()->get('Config');
										$base_url = $config['pathInfo']['base_url'];								 
										$msg = '<a href="'.$base_url.$logged_user_details->user_profile_name.'">'.$logged_user_details->user_given_name." accept your friend request</a>";
										$subject = 'Friend request';
										$from = 'admin@jeera.com';
										$process = 'accepted';
										$this->UpdateNotifications($user_details->user_id,$msg,2,$subject,$from,$logged_user_details->user_id,$user_details->user_id,$process);
									  $dataArr[0]['flag']                           = $this->flagSuccess;
									  $dataArr[0]['message']                        = "User friend added successfully.";
									}else{
									  $dataArr[0]['flag']                           = $this->flagError;
									  $dataArr[0]['message']                        = "Some error occured.";
									}
							   }else{
									$dataArr[0]['flag']                             = $this->flagError;
									$dataArr[0]['message']                          = "Some error occured.";
							   }
							}else{
								 $dataArr[0]['flag']                                = $this->flagError;
								 $dataArr[0]['message']                             = "No active request exist from this user.";
							}
						}else{
							 $dataArr[0]['flag']                = $this->flagError;
							 $dataArr[0]['message']             = "User is already friend.";
						}
					break;
					case 'reject':
						$isFriend = $this->getUserFriendTable()->isFriend($userId,$friend_id);
						if($isFriend == 0){
							$arrFriendInfo = $this->getUserFriendRequestTable()->GetActiveFriendRequest($friend_id, $userId);
							if(!empty($arrFriendInfo)){                         
								$intFriendStatus  = $this->getUserFriendRequestTable()->DeclineFriendRequest($friend_id, $userId);
								if($intFriendStatus){
									$dataArr[0]['flag']                                 = $this->flagSuccess;
									$dataArr[0]['message']                              = "Friend request has been rejected.";
								}else{
									$dataArr[0]['flag']                                 = $this->flagError;
									$dataArr[0]['message']                              = "Some error occured.";
								}
							}else{
								$dataArr[0]['flag']                                    = $this->flagError;
								$dataArr[0]['message']                                 = "No active request exist from this user.";
							}
						}else{
							 $dataArr[0]['flag']                = $this->flagError;
							 $dataArr[0]['message']             = "User is already friend.";
						}
					break;
					case 'remove':
						$isFriend  = $this->getUserFriendTable()->isFriend($userId,$friend_id);
						if($isFriend == 1){                       
							$friendStatus                                          = $this->getUserFriendTable()->RemoveFrined($userId,$friend_id);
							if($friendStatus){                             
								$intFriendStatus  = $this->getUserFriendRequestTable()->DeclineFriendRequest($friend_id, $userId);
								if($intFriendStatus){
									$dataArr[0]['flag']                             = $this->flagSuccess;
									$dataArr[0]['message']                          = "Friend request has been removed.";
								}else{
									$dataArr[0]['flag']                             = $this->flagError;
									$dataArr[0]['message']                          = "Some error occured.";
								}
							}else{
								$dataArr[0]['flag']                               = $this->flagError;
								$dataArr[0]['message']                            = "Some error occured.";
							}
						}else{
							$dataArr[0]['flag']                                    = $this->flagError;
							$dataArr[0]['message']                                 = "User is not friend.";
						}
					break;
					default:
					$dataArr[0]['flag']     = $this->flagError;
					$dataArr[0]['message']  = "Invalid operation.";
				}
                 
            }else{
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "User does not exists in system.";
            }
		}else{
			$dataArr[0]['flag'] = $this->flagError;
			$dataArr[0]['message'] = "Request Not Authorised.";
		}
		echo json_encode($dataArr);
		exit;
    }
	/**
    * This function is used for getting user friend list.
    **/
    public function getUserFriendListAction() {
        $dataArr  = array();      
        $arrFriends = array();
        $config = $this->getServiceLocator()->get('Config');   
		$request = $this->getRequest();  

        if ($request->isPost()) {     
			$post = $request->getPost();   
			$accToken   = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : ''; 
            $user_id   = (isset($post['user_id']) && $post['user_id'] != null && $post['user_id'] != '' && $post['user_id'] != 'undefined') ? strip_tags(trim($post['user_id'])) : '';             
			$type    = (isset($post['type']) && $post['type'] != null && $post['type'] != '' && $post['type'] != 'undefined') ? strip_tags(trim($post['type'])) : ''; 
			$nparam  = (isset($post['nparam']) && $post['nparam'] != null && $post['nparam'] != '' && $post['nparam'] != 'undefined') ? strip_tags(trim($post['nparam'])) : ''; 
			$countparam   = (isset($post['countparam']) && $post['countparam'] != null && $post['countparam'] != '' && $post['countparam'] != 'undefined') ? strip_tags(trim($post['countparam'])) : '';
			
            // if access token is empty
			if ($accToken == '') {
				$dataArr[0]['flag']                                 = $this->flagError;
				$dataArr[0]['message']                              = "Request Not Authorised.";
                $dataArr[0]['friends']                              = $arrFriends;
				echo json_encode($dataArr);
				exit;
			}

            // if type is empty
			if ($type == '') {
				$dataArr[0]['flag']                                 = $this->flagError;
				$dataArr[0]['message']                              = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}

            // if nparam is empty
			if ($nparam == '') {
				$dataArr[0]['flag']                                 = $this->flagError;
				$dataArr[0]['message']                              = "Request Not Authorised.";
                $dataArr[0]['friends']                              = $arrFriends;
				echo json_encode($dataArr);
				exit;
			}

            // if countparam is empty
			if ($countparam == '') {
				$dataArr[0]['flag']                                 = $this->flagError;
				$dataArr[0]['message']                              = "Request Not Authorised.";
                $dataArr[0]['friends']                              = $arrFriends;
				echo json_encode($dataArr);
				exit;
			}

            // if request is for getting list of friends or mutual friends i.e. type = Friends or type = Mutual
            if($type == 'friends' || $type == 'mutual') {
                // if user_id is empty
                if ($user_id == '') {
                    $dataArr[0]['flag']                             = $this->flagError;
                    $dataArr[0]['message']                          = "Request Not Authorised.";
                    $dataArr[0]['friends']                          = $arrFriends;
                    echo json_encode($dataArr);
                    exit;
                }else if (!is_numeric($user_id)) {
                    $dataArr[0]['flag']                             = $this->flagError;
                    $dataArr[0]['message']                          = "User id must be numeric.";
                    $dataArr[0]['friends']                          = $arrFriends;
                    echo json_encode($dataArr);
                    exit;
                }
            }

            // get user details on the basis of access token
			$user_details                                           = $this->getUserTable()->getUserByAccessToken($accToken);

            // if user details does not exist
			if(empty($user_details)) {
				$dataArr[0]['flag']                                 = $this->flagError;
				$dataArr[0]['message']                              = "Invalid Access Token.";
                $dataArr[0]['friends']                              = $arrFriends;
				echo json_encode($dataArr);
				exit;
			}

            // get user id on the basis of access token i.e. the user which is logged in
			$loggedin_userId                                        = $user_details->user_id;
			
			$nparam = (int) $nparam;
			$countparam = (int) $countparam;
			$nparam =($nparam>0)?$nparam-1:0;
			$nparam = $nparam*$countparam;

            // if request is for getting list of friends i.e. type = Friends
            if($type == 'friends') {
                $arrFriendslist                                     = $this->getUserFriendTable()->getAllFriendsForAPI($user_id, $loggedin_userId, $nparam, $countparam);
            } else if($type == 'requested' && $loggedin_userId == $user_id) {   // if request is for getting list of all friend requests i.e. type = Requested
                $arrFriendslist                                     = $this->getUserFriendRequestTable()->getAllFriendSentReuqestsForAPI($loggedin_userId, $nparam, $countparam);
            } else if($type == 'pending' && $loggedin_userId == $user_id) {   // if request is for getting list of all pending friend requests i.e. type = Pending
                $arrFriendslist                                     = $this->getUserFriendRequestTable()->getAllFriendReuqestsForAPI($loggedin_userId, $nparam, $countparam);
            } else if($type == 'mutual' && $loggedin_userId != $user_id) {   // if request is for getting list of mutual friends i.e. type = Mutual
                $arrFriendslist                                     = $this->getUserFriendTable()->getAllMutualFriendsForAPI($user_id, $loggedin_userId, $nparam, $countparam);
            }

           //print_r($arrFriendslist); die;
            // if data is coming from database
            if(!empty($arrFriendslist)) {
                $ctr                                                        = 0;
                //$dataArr[0]['flag']                                 = $this->flagSuccess;
                // loop through the array for creating response array
                foreach($arrFriendslist as $friend) {

                    $arrFriends[$ctr]['user_id']                       = $friend['user_id'];
                    $arrFriends[$ctr]['user_given_name']               = $friend['user_given_name'];
                    $arrFriends[$ctr]['user_profile_name']             = $friend['user_profile_name'];
                    $arrFriends[$ctr]['user_email']                    = $friend['user_email'];
                    $arrFriends[$ctr]['user_status']                   = $friend['user_status'];
                    $arrFriends[$ctr]['user_fbid']                     = $friend['user_fbid'];
                    $arrFriends[$ctr]['user_profile_about_me']         = $friend['user_profile_about_me'];
                    $arrFriends[$ctr]['user_profile_current_location'] = $friend['user_profile_current_location'];
                    $arrFriends[$ctr]['user_profile_phone']            = $friend['user_profile_phone'];
                    $arrFriends[$ctr]['country_title']                 = $friend['country_title'];
                    $arrFriends[$ctr]['country_code']                  = $friend['country_code'];
                    $arrFriends[$ctr]['country_id']                    = $friend['country_id'];
                    $arrFriends[$ctr]['city_name']                     = $friend['name'];
                    $arrFriends[$ctr]['city_id']                       = $friend['city_id'];

                    // get user profile picture
                    $arrFriends[$ctr]['profile_photo']                 = $this->manipulateProfilePic($friend['user_id'], $friend['profile_photo'], $friend['user_fbid']);





                    // get user tag category details
                    $arrUserTagCategory                             = array();
                    $user_tags                                      = $this->getUserTagTable()->getAllUserTagCategiry($friend['user_id']);

                    // loop through user tag details array
                    foreach($user_tags as $tag) {
                        $arrUserTagCategory['tag_category_id']      = $tag['tag_category_id'];
                        $arrUserTagCategory['tag_category_title']   = $tag['tag_category_title'];

                        // if tag category icon is not blank
                        if (!empty($tag['tag_category_icon'])) {
                            $arrUserTagCategory['tag_category_icon']= $config['pathInfo']['absolute_img_path'].$config['image_folders']['tag_category'].$tag['tag_category_icon'];
                        } else {    // if tag category icon is blank then show default icon
                            $arrUserTagCategory['tag_category_icon']= $config['pathInfo']['absolute_img_path'].'/images/category-icon.png';
                        }
                    }

                    $arrFriends[$ctr]['tag_category']                  = $arrUserTagCategory;
                    $arrFriends[$ctr]['joined_group_count']            = $friend['joined_group_count'];
                    $arrFriends[$ctr]['created_group_count']           = $friend['created_group_count'];

                    if($type == 'requested') {
                        $arrFriends[$ctr]['friendship_status']         = 'Requested';
                    }
                    else if($type == 'pending') {
                        $arrFriends[$ctr]['friendship_status']         = 'Pending';
                    } else {
                        if($friend['is_friend'] == '1') {
                            $arrFriends[$ctr]['friendship_status']     = 'Friends';
                        } elseif($friend['is_requested'] == '1') {
                            $arrFriends[$ctr]['friendship_status']     = 'Requested';
                        }  elseif($friend['get_request'] == '1') {
                            $arrFriends[$ctr]['friendship_status']     = 'Pending';
                        }  else {
                            $arrFriends[$ctr]['friendship_status']     = 'Not a friend';
                        }
                    }

                    $ctr++;
                }// foreach
                    $dataArr[0]['flag']                                 = $this->flagSuccess;
                    $dataArr[0]['message']                              = "";
                    $dataArr[0]['friends']                              = $arrFriends;
            } else {    // if there is no data coming from database
                $dataArr[0]['flag']                                     = $this->flagSuccess;
                $dataArr[0]['message']                                  = "There are no friends availbale.";
                $dataArr[0]['friends']                                  = $arrFriends;
            }
        } else {        // if request is not of type POST
			$dataArr[0]['flag']                                         = $this->flagError;
			$dataArr[0]['message']                                      = "Request Not Authorised.";
            $dataArr[0]['friends']                                      = $arrFriends;
		}

		echo json_encode($dataArr);
		exit;
    }

    // this function is used to get the profile picture of user
    public function manipulateProfilePic($user_id, $profile_photo = null, $fb_id = null) {
    	$config             = $this->getServiceLocator()->get('Config');
		$return_photo       = null;

		if (!empty($profile_photo)) {
			$return_photo   = $config['pathInfo']['absolute_img_path'].$config['image_folders']['profile_path'].$user_id.'/'.$profile_photo;
        } else if(isset($fb_id) && !empty($fb_id)) {
			$return_photo   = 'http://graph.facebook.com/'.$fb_id.'/picture?type=normal';
        } else {
			$return_photo   = $config['pathInfo']['absolute_img_path'].'/images/noimg.jpg';
        }

		return $return_photo;
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
	/**
    * This is User Friend Table Reference Object.
    **/
	public function getUserFriendTable() {
		$sm = $this->getServiceLocator();
		return $this->userFriendTable = (!$this->userFriendTable)?$sm->get('User\Model\UserFriendTable'):$this->userFriendTable;
	}

	/**
    * This is User Table Reference Object.
    **/
	public function getUserTable() {
		$sm = $this->getServiceLocator();
		return $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;
	}

    // function to use the user friend request table
    public function getUserFriendRequestTable() {
		$sm = $this->getServiceLocator();
		return $this->userFriendRequestTable = (!$this->userFriendRequestTable)?$sm->get('User\Model\UserFriendRequestTable'):$this->userFriendRequestTable;
	}

    // function to use the user tag table
	public function getUserTagTable() {
		$sm = $this->getServiceLocator();
		return $this->userTagTable = (!$this->userTagTable)?$sm->get('Tag\Model\UserTagTable'):$this->userTagTable;
	}
	public function getUserNotificationTable(){         
		$sm = $this->getServiceLocator();
		return  $this->userNotificationTable = (!$this->userNotificationTable)?$sm->get('Notification\Model\UserNotificationTable'):$this->userNotificationTable;    
    }
}
