<?php
####################Comment Controller #################################
#namespace for module like
namespace Service\Controller;
#library uses

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Authentication\AuthenticationService;	//Needed for checking User session
use Zend\Authentication\Adapter\DbTable as AuthAdapter;	//Db apapter

/*use Zend\Authentication\Result as Result;
use Zend\Authentication\Storage;*/

#Group classs
use Comment\Model\Comment;
use \Exception;		#Exception class for handling exception
use Zend\View\Helper\HelperInterface;
use Zend\View\Renderer\RendererInterface;
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Notification\Model\UserNotification;
class CommentController extends AbstractActionController
{
    protected $userTable;
    protected $groupTable;
    protected $userGroupTable;
    protected $photoTable = "";
    protected $activityTable = "";
    protected $discussionTable = "";
    protected $albumTable = "";
    protected $commentTable ="";
    protected $likeTable;
    protected $albumDataTable;
    protected $activityRsvpTable;
    protected $userNotificationTable;
    protected $albumTagTable;
    protected $groupMediaTable;
	public function __construct(){
        $this->flagSuccess = "Success";
        $this->flagFailure = "Failure";
	}
    public function getcommentsAction(){
        $error = '';
        $comment_count = 0;
        $comments = array();
        $request   = $this->getRequest();
        if ($request->isPost()){
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error = (empty($userinfo)) ? "Invalid Access Token." : $error;
            $this->checkError($error);
            $system_type = $post['type'];
            $SystemTypeData = $this->getGroupTable()->fetchSystemType($system_type);
            $error = (empty($SystemTypeData)) ? "Invalid Content Type to like" : $error;
            $this->checkError($error);
            $commentSystemType = $this->getGroupTable()->fetchSystemType('Comment');
            $error = (isset($post['content_id']) && $post['content_id'] != null && $post['content_id'] != '' && $post['content_id'] != 'undefined' && is_numeric($post['content_id'])) ? '' : 'please input a valid content id';
            $this->checkError($error);
            $refer_id = trim($post['content_id']);
            $offset = (isset($post['nparam']))?trim($post['nparam']):'';
            $limit = (isset($post['countparam']))?trim($post['countparam']):'';
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
            $comments_details = $this->getCommentTable()->getAllCommentsWithLike($SystemTypeData->system_type_id,$commentSystemType->system_type_id,$refer_id,$userinfo->user_id,(int) $limit, (int) $offset);
            if(!empty($comments_details) & count($comments_details) > 0){
                foreach($comments_details as $list){
                    $arrLikeMembers = array();
                    $like_details = $this->getLikeTable()->fetchLikesCountByReference($commentSystemType->system_type_id,$list->comment_id,$userinfo->user_id);
                    if(!empty($like_details)){
                        $liked_users = $this->getLikeTable()->likedUsersForRestAPI($SystemTypeData->system_type_id, $refer_id, $userinfo->user_id, (int) $limit, (int) $offset);
                        if($like_details['likes_counts']>0&&!empty($liked_users)){
                            foreach ($liked_users as $f_list) {
                                $profile_photo = $this->manipulateProfilePic($f_list['user_id'], $f_list['profile_photo'], $f_list['user_fbid']);
                                $arrLikeMembers[] = array(
                                    'user_id'=>$f_list['user_id'],
                                    'user_fbid'=>$f_list['user_fbid'],
                                    'user_given_name'=>$f_list['user_given_name'],
                                    'user_profile_name'=>$f_list['user_profile_name'],
                                    'profile_photo'=>$profile_photo,
                                );
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
                        'user_profile_name'=>$list->user_profile_name,
                        'liked_users'=>$arrLikeMembers,
                    );
                }
            } else $error = "No Comments for content";
        }

        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
        $dataArr[0]['message'] = $error;
        $dataArr[0]['comments'] = $comments;
        echo json_encode($dataArr);
        exit;
    }
    public function editcommentAction(){
        $error = '';
        $request   = $this->getRequest();
        if ($request->isPost()){
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error = (empty($userinfo)) ? "Invalid Access Token." : $error;
            $this->checkError($error);
            $error = (isset($post['comment_id']) && $post['comment_id'] != null && $post['comment_id'] != '' && $post['comment_id'] != 'undefined' && is_numeric($post['comment_id'])) ? '' : 'please input a valid comment id';
            $this->checkError($error);
            $comment_id = $post['comment_id'];
            $error = (isset($post['edited_comment']) && $post['edited_comment'] != null && $post['edited_comment'] != '' && $post['edited_comment'] != 'undefined' ) ? '' : 'please input a valid comment';
            $this->checkError($error);
            $comment = $post['edited_comment'];
            $comment_details = $this->getCommentTable()->getCommentWIthSystemType($comment_id);
            if(empty($comment_details)) {
                $error = "This comments no longer exist in the system";
                $this->checkError($error);
            }
            $allowedit = 0;
            if($comment_details->comment_by_user_id == $userinfo->user_id){
                $allowedit = 1;
            }
            switch($comment_details->system_type_title){
                case 'Activity':
                    $activity_deatils =  $this->getActivityTable()->getActivity($comment_details->comment_refer_id);
                    if($activity_deatils->group_activity_owner_user_id == $userinfo->user_id){
                        $allowedit = 1;
                    }
                    if($this->getUserGroupTable()->checkOwner($activity_deatils->group_activity_group_id,$userinfo->user_id)){
                        $allowedit = 1;
                    }
                    break;
                case 'Discussion':
                    $discussion_deatils =  $this->getDiscussionTable()->getDiscussion($comment_details->comment_refer_id);
                    if($discussion_deatils->group_discussion_owner_user_id == $userinfo->user_id){
                        $allowedit = 1;
                    }
                    if($this->getUserGroupTable()->checkOwner($discussion_deatils->group_discussion_group_id,$userinfo->user_id)){
                        $allowedit = 1;
                    }
                    break;
                case 'Media':
                    $media_deatils =  $this->getGroupMediaTable()->getMedia($comment_details->comment_refer_id);
                    if($media_deatils->media_added_user_id == $userinfo->user_id){
                        $allowedit = 1;
                    }
                    if($this->getUserGroupTable()->checkOwner($media_deatils->media_added_group_id,$userinfo->user_id)){
                        $allowedit = 1;
                    }
                    break;
            }
            if($allowedit == 1){
                $data['comment_content'] = $comment;
                $this->getCommentTable()->updateCommentTable($data,$comment_id);
                $dataArr[0]['flag'] = $this->flagSuccess;
                $dataArr[0]['message'] = "Comment edited successfully";
                echo json_encode($dataArr);
                exit;
            }else {
                $error = "User not allowed to edit";
            }
        }
        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
        $dataArr[0]['message'] = $error;
        echo json_encode($dataArr);
        exit;
    }
    public function deletecommentAction(){
        $error = '';
        $request   = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            $error = (empty($accToken)) ? "Request Not Authorised." : $error;
            $this->checkError($error);
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            $error = (empty($userinfo)) ? "Invalid Access Token." : $error;
            $this->checkError($error);
            $error = (isset($post['comment_id']) && $post['comment_id'] != null && $post['comment_id'] != '' && $post['comment_id'] != 'undefined' && is_numeric($post['comment_id'])) ? '' : 'please input a valid comment id';
            $this->checkError($error);
            $comment_id = $post['comment_id'];
            $comment_details = $this->getCommentTable()->getCommentWIthSystemType($comment_id);
            if(empty($comment_details)) {
                $error = "This comments no longer exist in the system";
                $this->checkError($error);
            }
            $allowdelete = 0;
            if ($comment_details->comment_by_user_id == $userinfo->user_id) {
                $allowdelete = 1;
            }
            switch ($comment_details->system_type_title) {
                case 'Activity':
                    $activity_deatils = $this->getActivityTable()->getActivity($comment_details->comment_refer_id);
                    if ($activity_deatils->group_activity_owner_user_id == $userinfo->user_id) {
                        $allowdelete = 1;
                    }
                    if ($this->getUserGroupTable()->checkOwner($activity_deatils->group_activity_group_id, $userinfo->user_id)) {
                        $allowdelete = 1;
                    }
                    break;
                case 'Discussion':
                    $discussion_deatils = $this->getDiscussionTable()->getDiscussion($comment_details->comment_refer_id);
                    if ($discussion_deatils->group_discussion_owner_user_id == $userinfo->user_id) {
                        $allowdelete = 1;
                    }
                    if ($this->getUserGroupTable()->checkOwner($discussion_deatils->group_discussion_group_id, $userinfo->user_id)) {
                        $allowdelete = 1;
                    }
                    break;
                case 'Media':
                    $media_deatils = $this->getGroupMediaTable()->getMedia($comment_details->comment_refer_id);
                    if ($media_deatils->media_added_user_id == $userinfo->user_id) {
                        $allowdelete = 1;
                    }
                    if ($this->getUserGroupTable()->checkOwner($media_deatils->media_added_group_id, $userinfo->user_id)) {
                        $allowdelete = 1;
                    }
                    break;
            }
            if ($allowdelete == 1) {
                $this->getCommentTable()->deleteComment($comment_id);
                $dataArr[0]['flag'] = $this->flagSuccess;
                $dataArr[0]['message'] = "Comment deleted successfully";
                echo json_encode($dataArr);
                exit;
            } else {
                $error = "User not allowed to delete";
            }
        }
        $dataArr[0]['flag'] = (empty($error))?$this->flagSuccess:$this->flagFailure;
        $dataArr[0]['message'] = $error;
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
	#This will like post and comments
    public function getActivityRsvpTable(){
        if (!$this->activityRsvpTable) {
            $sm = $this->getServiceLocator();
            $this->activityRsvpTable = $sm->get('Activity\Model\ActivityRsvpTable');
        }
        return $this->activityRsvpTable;
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
    public function getUserTable(){
        $sm = $this->getServiceLocator();
        return $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable; ;
    }
    public function getCommentTable(){
        $sm = $this->getServiceLocator();
        return  $this->commentTable = (!$this->commentTable)?$sm->get('Comment\Model\CommentTable'):$this->commentTable;
    }
    public function getGroupTable(){
        $sm = $this->getServiceLocator();
        return $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;
    }
    public function getDiscussionTable(){
        $sm = $this->getServiceLocator();
        return  $this->discussionTable = (!$this->discussionTable)?$sm->get('Discussion\Model\DiscussionTable'):$this->discussionTable;
    }
    public function getUserGroupTable(){
        $sm = $this->getServiceLocator();
        return $this->userGroupTable= (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;
    }
    public function getUserNotificationTable(){
        $sm = $this->getServiceLocator();
        return $this->userNotificationTable= (!$this->userNotificationTable)?$sm->get('Notification\Model\UserNotificationTable'):$this->userNotificationTable;
    }
    public function getGroupMediaTable(){
        $sm = $this->getServiceLocator();
        return  $this->groupMediaTable = (!$this->groupMediaTable)?$sm->get('Groups\Model\GroupMediaTable'):$this->groupMediaTable;
    }
    public function getActivityTable(){
        $sm = $this->getServiceLocator();
        return  $this->activityTable = (!$this->activityTable)?$sm->get('Activity\Model\ActivityTable'):$this->activityTable;
    }
    public function getLikeTable(){
        $sm = $this->getServiceLocator();
        return  $this->likeTable = (!$this->likeTable)?$sm->get('Like\Model\LikeTable'):$this->likeTable;
    }
}
