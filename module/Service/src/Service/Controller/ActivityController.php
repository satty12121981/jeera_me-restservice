<?php
namespace Service\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Activity\Model\Activity;
use Activity\Model\ActivityInvite;
use Activity\Model\ActivityRsvp ;
use Notification\Model\UserNotification;
use \Exception;
class ActivityController extends AbstractActionController
{
	protected $userTable;
	protected $userProfileTable;
	protected $activityTable;
	protected $activityRsvpTable;
    protected $userNotificationTable;
    protected $groupActivityInviteTable;
    protected $userGroupTable;
	public function __construct(){
        $this->flagSuccess = "Success";
		$this->flagFailure = "Failure";
	}
    public function quitactivityAction(){
        $error = '';
        $message = '';
        $request   = $this->getRequest();
        if ($request->isPost()){
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken'])&&$post['accesstoken']!=null&&$post['accesstoken']!=''&&$post['accesstoken']!='undefined')?strip_tags(trim($post['accesstoken'])):'';
            $error =(empty($accToken))?"Request Not Authorised.":$error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error =(empty($userinfo))?"Invalid Access Token.":$error;
            $this->checkError($error);
            $activity_id =  $post['activity_id'];
            $activityDetails = $this->getActivityTable()->getActivity($activity_id);
            $error =(empty($activityDetails))?"Invalid Activity Id.":$error;
            $this->checkError($error);
            $userPermissionOnGroup = $this->getUserGroupTable()->getGroupUserDetails($activityDetails->group_activity_group_id,$userinfo->user_id);
            $error =(empty($userPermissionOnGroup))?"User has no permission on the event.":$error;
            $this->checkError($error);
            if(!empty($activityDetails)){
                if ($this->getActivityRsvpTable()->getActivityRsvpOfUser($userinfo->user_id, $activity_id)){
                    if($this->getActivityRsvpTable()->removeActivityRsvp($activity_id,$userinfo->user_id)){
                        $message = "Event quited Successfully";
                    }else{
                        $error = "Some error occurred. Please try again";
                    }
                }else{
                    $error = "No Rsvp exists to remove";
                }
            }
        }else{
            $error = "Request Not Authorised";
        }
        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
        $dataArr[0]['message'] = (empty($error))?$message:$error;
        echo json_encode($dataArr);
        exit;
    }
    public function checkError($error){
        if (!empty($error)){
            $dataArr[0]['flag'] = $this->flagFailure;
            $dataArr[0]['message'] = $error;
            echo json_encode($dataArr);
            exit;
        }
    }
    public function joinactivityAction(){
        $error = '';
        $message = '';
        $request   = $this->getRequest();
        if ($request->isPost()){
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken'])&&$post['accesstoken']!=null&&$post['accesstoken']!=''&&$post['accesstoken']!='undefined')?strip_tags(trim($post['accesstoken'])):'';
            $error =(empty($accToken))?"Request Not Authorised.":$error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error =(empty($userinfo))?"Invalid Access Token.":$error;
            $this->checkError($error);
            $activity_id =  $post['activity_id'];
            $activityDetails = $this->getActivityTable()->getActivity($activity_id);
            $error =(empty($activityDetails))?"Invalid Activity Id.":$error;
            $this->checkError($error);
            $userPermissionOnGroup = $this->getUserGroupTable()->getGroupUserDetails($activityDetails->group_activity_group_id,$userinfo->user_id);
            $error =(empty($userPermissionOnGroup))?"User has no permission on the event.":$error;
            $this->checkError($error);
            if($activity_id!=''){
                $activity = $this->getActivityTable()->getActivity($activity_id);
                $activity_rsvp = $this->getActivityRsvpTable()->getActivityRsvpOfUser($userinfo->user_id, $activity_id);
                if(!empty($activity)){
                    if (empty($activity_rsvp)){
                        $ActivityRsvp = new ActivityRsvp();
                        $ActivityRsvp->group_activity_rsvp_user_id = $userinfo->user_id;
                        $ActivityRsvp->group_activity_rsvp_activity_id = $activity_id;
                        $ActivityRsvp->group_activity_rsvp_group_id = $activity->group_activity_group_id;
                        if($this->getActivityRsvpTable()->saveActivityRsvp($ActivityRsvp)){
                            $config = $this->getServiceLocator()->get('Config');
                            $base_url = $config['pathInfo']['base_url'];
                            $msg = $userinfo->user_given_name." is joined in your activity ".$activity->group_activity_title ;
                            $subject = 'Event members';
                            $from = 'admin@jeera.com';
                            $process = 'Join Event';
                            $this->UpdateNotifications($activity->group_activity_owner_user_id,$msg,7,$subject,$from,$userinfo->user_id,$activity->group_activity_group_id,$process);
                            $message = "Event joined Successfully";
                        }else{$error="Some error occurred. Please try again";}
                    }else{$error = "Activity RSVP already exist for the user";}
                }else{$error = "Activity not exist";}
            }else{$error = "Activity Id required";}
        }else{$error = "Request Not Authorized";}
        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
        $dataArr[0]['message'] = (empty($error))?$message:$error;
        echo json_encode($dataArr);
        exit;
    }
    public function UpdateNotifications($user_notification_user_id,$msg,$type,$subject,$from,$sender,$reference_id,$process){
        $UserGroupNotificationData = array();
        $UserGroupNotificationData['user_notification_user_id'] = $user_notification_user_id;
        $UserGroupNotificationData['user_notification_content']  = $msg;
        $UserGroupNotificationData['user_notification_added_timestamp'] = date('Y-m-d H:i:s');
        $UserGroupNotificationData['user_notification_notification_type_id'] = $type;
        $UserGroupNotificationData['user_notification_status'] = 'unread';
        $UserGroupNotificationData['user_notification_sender_id'] = $sender;
        $UserGroupNotificationData['user_notification_reference_id'] = $reference_id;
		$UserGroupNotificationData['user_notification_process'] = $process;
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
    public function getUserNotificationTable(){
        $sm = $this->getServiceLocator();
        return  $this->userNotificationTable = (!$this->userNotificationTable)?$sm->get('Notification\Model\UserNotificationTable'):$this->userNotificationTable;
    }
    public function getUserTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;    
	}
	public function getGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;    
	}
	public function getUserGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->userGroupTable = (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;    
	}
	public function getActivityTable(){
		$sm = $this->getServiceLocator();
		return  $this->activityTable = (!$this->activityTable)?$sm->get('Activity\Model\ActivityTable'):$this->activityTable;    
    }
	public function getActivityRsvpTable(){
		$sm = $this->getServiceLocator();
		return  $this->activityRsvpTable = (!$this->activityRsvpTable)?$sm->get('Activity\Model\ActivityRsvpTable'):$this->activityRsvpTable;
    }
    public function getActivityInviteTable(){
        $sm = $this->getServiceLocator();
        return  $this->groupActivityInviteTable = (!$this->groupActivityInviteTable)?$sm->get('Activity\Model\ActivityInviteTable'):$this->groupActivityInviteTable;
    }
}
