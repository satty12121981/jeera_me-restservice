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

use Groups\Model\Groups;
use Groups\Model\UserGroup;
use Groups\Model\GroupJoiningQuestionnaire;
use Groups\Model\GroupQuestionnaireOptions;
use Groups\Model\UserGroupJoiningInvitation;
use Groups\Model\UserGroupJoiningRequest;
use Groups\Model\GroupQuestionnaireAnswers;
use Notification\Model\UserNotification; 
use \Exception;

use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
class GroupsController extends AbstractActionController
{

    public $form_error;
    public $flagSuccess;
    public $flagError;
    protected $userTable;
    protected $userProfileTable;
    protected $userFriendTable;
    protected $userGroupTable;
    protected $userTagTable;
    protected $groupTable;
    protected $activityTable;
    protected $discussionTable;
    protected $groupMediaTable;
    protected $likeTable;
    protected $commentTable;
    protected $activityRsvpTable;
    protected $groupTagTable;
    protected $groupJoiningQuestionnaire;
    protected $groupQuestionnaireOptions;
    protected $groupJoiningInvitationTable;
    protected $userGroupJoiningRequestTable;
    protected $groupQuestionnaireAnswersTable;
    protected $userNotificationTable;

    public function __construct()
    {
        $this->flagSuccess = "Success";
        $this->flagError = "Failure";
    }

    public function exploregroupsAction()
    {
        $error = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            $config = $this->getServiceLocator()->get('Config');
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            if (empty($accToken)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
            $user_details = $this->getUserTable()->getUserByAccessToken($accToken);
            if (empty($user_details)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Invalid Access Token.";
                echo json_encode($dataArr);
                exit;
            }
            $user_id = $user_details->user_id;
            $city = (isset($post['city']) && $post['city'] != null && $post['city'] != '' && $post['city'] != 'undefined') ? strip_tags(trim($post['city'])) : '';
            $country = (isset($post['country']) && $post['country'] != null && $post['country'] != '' && $post['country'] != 'undefined') ? strip_tags(trim($post['country'])) : '';
            $category = (isset($post['categories']) && $post['categories'] != null && $post['categories'] != '' && $post['categories'] != 'undefined') ? $post['categories'] : '';
            $myfriends = (isset($post['myfriends']) && $post['myfriends'] != null && $post['myfriends'] != '' && $post['myfriends'] != 'undefined' && $post['myfriends'] == true) ? strip_tags(trim($post['myfriends'])) : '';
            $offset = (isset($post['nparam']) && $post['nparam'] != null && $post['nparam'] != '' && $post['nparam'] != 'undefined') ? trim($post['nparam']) : 0;
            $limit = (isset($post['countparam']) && $post['countparam'] != null && $post['countparam'] != '' && $post['countparam'] != 'undefined') ? trim($post['countparam']) : 30;
            if (!empty($country) && !is_numeric($country)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Enter a valid country id.";
                echo json_encode($dataArr);
                exit;
            }
            if (!empty($city) && !is_numeric($city)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Enter a valid city id.";
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
            $arr_group_list = '';
            $user_tag_available_status = '';
            $user_tag_status = $this->getUserTagTable()->checkTagExistForUser($user_id);
            $offset = (int)$offset;
            $limit = (int)$limit;
            $offset = ($offset > 0) ? $offset - 1 : 0;
            $offset = $offset * $limit;
            if ($user_tag_status[0]['tag_exists']) $user_tag_available_status = 1;
            $groups = $this->getUserGroupTable()->getMatchGroupsByUserTagsForRestApi($user_id, $user_tag_available_status, $city, $country, $myfriends, $category, (int)$limit, (int)$offset);
            if (!empty($groups)) {
                foreach ($groups as $list) {
                    if (!empty($list['group_photo_photo']))
                        $list['group_photo_photo'] = $config['pathInfo']['absolute_img_path'] . $config['image_folders']['group'] . $list['group_id'] . '/medium/' . $list['group_photo_photo'];
                    else
                        $list['group_photo_photo'] = $config['pathInfo']['absolute_img_path'] . '/images/group-img_def.jpg';
                    $tag_category = $this->getGroupTagTable()->getAllGroupTagCategiry($list['group_id']);
                    $tags = $this->getGroupTagTable()->getAllGroupTagsForAPI($list['group_id']);
                    if (!empty($tags)) {
                        $tags = $this->formatTagsWithCategory($tags, "|");
                    }
                    $groupUsers = $this->getUserGroupTable()->getMembers($list['group_id'], $user_id, "", 0, 5);
                    $arrMembers = array();
                    if (!empty($groupUsers)) {
                        foreach ($groupUsers as $f_list) {
                            $profile_photo = $this->manipulateProfilePic($f_list['user_id'], $f_list['profile_icon'], $f_list['user_fbid']);
                            $arrMembers[] = array(
                                'user_id' => $f_list['user_id'],
                                'user_given_name' => $f_list['user_given_name'],
                                'user_profile_name' => $f_list['user_profile_name'],
                                'country_title' => $f_list['country_title'],
                                'country_code' => $f_list['country_code'],
                                'city' => $f_list['city'],
                                'profile_photo' => $profile_photo,
                                'is_admin' => $f_list['is_admin'],
                                'user_group_is_owner' => $f_list['user_group_is_owner'],
                                'user_group_role' => $f_list['user_group_role']
                            );

                        }
                        $flag = 1;
                    }

                    $arr_group_list[] = array(
                        'group_id' => $list['group_id'],
                        'group_title' => $list['group_title'],
                        'group_seo_title' => $list['group_seo_title'],
                        'group_type' => (empty($list['group_type'])) ? "" : $list['group_type'],
                        'group_photo_photo' => $list['group_photo_photo'],
                        'country_title' => $list['country_title'],
                        'country_code' => $list['country_code'],
                        'member_count' => $list['member_count'],
                        'friend_count' => $list['friend_count'],
                        'city' => $list['city'],
                        'tag_categories_count' => count($tag_category),
                        'tags' => $tags,
                        'groupmembers' => $arrMembers,
                    );
                }
                $dataArr[0]['flag'] = "Success";
                if (!empty($user_tag_available_status))
                    $dataArr[0]['usertagsexist'] = 'Yes';
                else
                    $dataArr[0]['usertagsexist'] = 'No';
                $dataArr[0]['groups'] = $arr_group_list;
                echo json_encode($dataArr);
                exit;
            } else {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "No Groups available.";
                echo json_encode($dataArr);
                exit;
            }
        } else {
            $dataArr[0]['flag'] = "Failure";
            $dataArr[0]['message'] = "Request Not Authorised.";
            echo json_encode($dataArr);
            exit;
        }
        return;
    }
    public function inviteMembersToGroupListAction(){
        $error = '';
        $arrMembers = array();
        $groupMembers = array();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $offset = trim($post['nparam']);
            $limit = trim($post['countparam']);
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            if (empty($accToken)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            if (empty($userinfo)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Invalid Access Token.";
                echo json_encode($dataArr);
                exit;
            }
            if (!empty($post)) {
                $group_id = (isset($post['groupid']) && $post['groupid'] != null && $post['groupid'] != '' && $post['groupid'] != 'undefined' && is_numeric($post['groupid'])) ? strip_tags(trim($post['groupid'])) : '';
                if ((empty($group_id))) {
                    $dataArr[0]['flag'] = "Failure";
                    $dataArr[0]['message'] = "Please input a valid group id.";
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
                $offset = (int) $offset;
                $limit = (int) $limit;
                $offset =($offset>0)?$offset-1:0;
                $offset = $offset*$limit;

                $group = $this->getGroupTable()->getPlanetinfo($group_id);
                if (!empty($group)) {
                    $search_string = $post['searchstr'];
                    $arrMembers = $this->getUserGroupTable()->getFriendsNotMemberOfGroup($group_id, $userinfo->user_id, $search_string, $offset, $limit);
                } else {
                    $error = "Group not available";
                }
            }
        }
        if (count($arrMembers)){
            foreach ($arrMembers as $u_list) {
                $profile_photo = $this->manipulateProfilePic($u_list['user_id'], $u_list['profile_photo'], $u_list['user_fbid']);
                $groupMembers[] = array(
                    'user_id' => $u_list['user_id'],
                    'profile_photo' => $profile_photo,
                    'user_profile_name' => $u_list['user_profile_name'],
                    'user_register_type' => $u_list['user_profile_name'],
                    'user_fbid' => $u_list['user_fbid'],
                );
            }
        }
        $dataArr[0]['flag'] = (empty($error))?'Success':'Failure';
        $dataArr[0]['message'] = $error;
        $dataArr[0]['members'] = $groupMembers;
        echo json_encode($dataArr);
        exit;
    }
    public function inviteMembersToGroupAction()
    {
        $error = '';
        $arrMembers = array();
        $groupMembers = array();
        $request = $this->getRequest();
        if ($request->isPost()) {
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            if (empty($accToken)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
            $userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            if (empty($userinfo)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Invalid Access Token.";
                echo json_encode($dataArr);
                exit;
            }
            if (!empty($post)) {
                $group_id = (isset($post['groupid']) && $post['groupid'] != null && $post['groupid'] != '' && $post['groupid'] != 'undefined' && is_numeric($post['groupid'])) ? strip_tags(trim($post['groupid'])) : '';
                if ((empty($group_id))) {
                    $dataArr[0]['flag'] = "Failure";
                    $dataArr[0]['message'] = "Please input a valid group id.";
                    echo json_encode($dataArr);
                    exit;
                }
                $group = $this->getGroupTable()->getPlanetinfo($group_id);
                $friends = (isset($post['inviteusers']) && $post['inviteusers'] != null && $post['inviteusers'] != '' && $post['inviteusers'] != 'undefined') ? strip_tags(trim($post['inviteusers'])) : '';
                if ((empty($friends))) {
                    $dataArr[0]['flag'] = "Failure";
                    $dataArr[0]['message'] = "Please input invite users .";
                    echo json_encode($dataArr);
                    exit;
                }
                if ($friends != "all"){
                    $friends = explode(",", $friends);
                    $friends = array_filter($friends);
                }
                if (!empty($group)) {
                    if ($group->group_type == 'private') {
                        $owner = $this->getUserGroupTable()->checkOwner($group->group_id, $userinfo->user_id);
                        if ($owner) {
                            if ($friends == 'all') {
                                $all_members = $this->getUserGroupTable()->getFriendsNotMemberOfGroup($group_id, $userinfo->user_id, '');
                                foreach ($all_members as $items) {
                                    $arrMembers[] = $items['user_id'];
                                }
                            } else {
                                foreach ($friends as $index => $items) {
                                    $arrMembers[] = $items;
                                }
                            }
                            $UserGroupJoiningInvitation = new UserGroupJoiningInvitation();
                            if (count($arrMembers)) {
                                foreach ($arrMembers as $group_invt) {
                                    if (is_numeric($group_invt)){
                                        $userExist = $this->getUserTable()->getUser($group_invt);
                                        if (!empty($userExist)) {
                                            $invite = $this->getGroupJoiningInvitationTable()->checkInvited($group_invt, $group_id);
                                            if (empty($invite)) {
                                                $UserGroupJoiningInvitation->user_group_joining_invitation_sender_user_id = $userinfo->user_id;
                                                $UserGroupJoiningInvitation->user_group_joining_invitation_receiver_id = $group_invt;
                                                $UserGroupJoiningInvitation->user_group_joining_invitation_status = "active";
                                                $UserGroupJoiningInvitation->user_group_joining_invitation_ip_address = $_SERVER["SERVER_ADDR"];
                                                $UserGroupJoiningInvitation->user_group_joining_invitation_group_id = $group_id;
                                                $intUserGroupJoiningInvitation = $this->getGroupJoiningInvitationTable()->saveUserGroupJoiningInvite($UserGroupJoiningInvitation);
                                                if ($intUserGroupJoiningInvitation) {
                                                    $config = $this->getServiceLocator()->get('Config');
                                                    $base_url = $config['pathInfo']['base_url'];
                                                    $msg = $userinfo->user_given_name . " invited you to join the group " . $group->group_title;
                                                    $subject = 'Group joining invitation';
                                                    $from = 'admin@jeera.com';
                                                    $process = 'Invite';
                                                    $this->UpdateNotifications($group_invt, $msg, 3, $subject, $from, $userinfo->user_id, $group_id, $process);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $error = "You do not have the permission to perform this action";
                        }
                    } else {
                        if ($this->getUserGroupTable()->is_member($userinfo->user_id, $group_id)) {
                            if ($friends == 'all') {
                                $all_members = $this->getUserGroupTable()->getFriendsNotMemberOfGroup($group_id, $userinfo->user_id, '');
                                foreach ($all_members as $items) {
                                    $arrMembers[] = $items['user_id'];
                                }
                            } else {
                                foreach ($friends as $index => $items) {
                                    $arrMembers[] = $items;
                                }
                            }
                            $UserGroupJoiningInvitation = new UserGroupJoiningInvitation();
                            $config = $this->getServiceLocator()->get('Config');
                            $base_url = $config['pathInfo']['base_url'];
                            $msg = $userinfo->user_given_name . " invited you to join the group " . $group->group_title;
                            $subject = 'Group joining invitation';
                            $from = 'admin@jeera.com';
                            $process = 'Invite';
                            if (count($arrMembers)) {
                                foreach ($arrMembers as $group_invt) {
                                    $userExist = $this->getUserTable()->getUser($group_invt);
                                    if (!empty($userExist)) {
                                        $invite = $this->getGroupJoiningInvitationTable()->checkInvited($group_invt, $group_id);
                                        if (empty($invite)) {
                                            $UserGroupJoiningInvitation->user_group_joining_invitation_sender_user_id = $userinfo->user_id;
                                            $UserGroupJoiningInvitation->user_group_joining_invitation_receiver_id = $group_invt;
                                            $UserGroupJoiningInvitation->user_group_joining_invitation_status = "active";
                                            $UserGroupJoiningInvitation->user_group_joining_invitation_ip_address = $_SERVER["SERVER_ADDR"];
                                            $UserGroupJoiningInvitation->user_group_joining_invitation_group_id = $group_id;
                                            $intUserGroupJoiningInvitation = $this->getGroupJoiningInvitationTable()->saveUserGroupJoiningInvite($UserGroupJoiningInvitation);
                                            if ($intUserGroupJoiningInvitation) {
                                                $this->UpdateNotifications($group_invt, $msg, 3, $subject, $from, $userinfo->user_id, $group_id, $process);
                                            }
                                        }
                                    }
                                }
                            }
                        } else {
                            $error = "You do not have the permission to perform this action";
                        }
                    }
                } else {
                    $error = "Specified Group is not available";
                }
            } else {
                $error = "Unable to process the request";
            }
        } else {
            $error = "Request Not Authorized.";
        }

        $dataArr[0]['flag'] = (empty($error))?'Success':'Failure';
        $dataArr[0]['message'] = (empty($error))? 'Invite sent successfully':$error;
        echo json_encode($dataArr);
        exit;
    }
    public function groupslistAction()
    {
        $error = '';
        $request = $this->getRequest();
        if ($request->isPost()) {
            $config = $this->getServiceLocator()->get('Config');
            $post = $request->getPost();
            $accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            if (empty($accToken)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }
            $myinfo = $this->getUserTable()->getUserByAccessToken($accToken);
            if (empty($myinfo)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Invalid Access Token.";
                echo json_encode($dataArr);
                exit;
            }
            $user_id = $myinfo->user_id;

            $userIdentity = (empty($post['user_id'])) ? $user_id : $post['user_id'];
            if (isset($userIdentity) && !is_numeric($userIdentity)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a Valid Other User ID.";
                echo json_encode($dataArr);
                exit;
            }

            $error = (isset($post['type']) && $post['type'] != null && $post['type'] != '' && $post['type'] != 'undefined' && is_numeric($post['type'])) ? 'please input a valid type' :'' ;
            if ($error) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = $error;
                echo json_encode($dataArr);
                exit;
            }
            $strType = $post['type'];

            $offset = trim($post['nparam']);
            $limit = trim($post['countparam']);
            if (!empty($limit) && !is_numeric($limit)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a Valid Count Field.";
                echo json_encode($dataArr);
                exit;
            }
            if (!empty($offset) && !is_numeric($offset)) {
                $dataArr[0]['flag'] = "Failure";
                $dataArr[0]['message'] = "Please input a Valid N Field.";
                echo json_encode($dataArr);
                exit;
            }
            $offset = (int)$offset;
            $limit = (int)$limit;
            $offset = ($offset > 0) ? $offset - 1 : 0;
            $offset = $offset * $limit;

            $userinfo = $this->getUserTable()->getUser($userIdentity);
            $arr_group_list = array();
            if (!empty($userinfo) && $userinfo->user_id && !empty($myinfo) && $myinfo->user_id) {
                $profile_type = 'mine';
                if ($userinfo->user_id != $user_id) {
                    $profile_type = 'others';
                }
                $intTotalGroups = $this->getUserGroupTable()->fetchAllUserGroupCount($userinfo->user_id, $user_id, $strType, $profile_type);
                if ($intTotalGroups['group_count'] > 0) {
                    $arrGroups = $this->getUserGroupTable()->fetchUserGroupList($userinfo->user_id, $user_id, $strType, $profile_type, $limit, $offset);
                    $group_list = array();
                    if (!empty($arrGroups)) {
                        $loadtagslist = array();
                        $loadtagcatslist = array();
                        foreach ($arrGroups as $list) {
                            $tag_category = $this->getGroupTagTable()->getAllGroupTagCategiry($list['group_id']);
                            $tags = $this->getGroupTagTable()->getAllGroupTagsForAPI($list['group_id']);
                            if (!empty($tags)) {
                                $tags = $this->formatTagsWithCategory($tags,"|");
                            }
                            $request_count = 0;
                            if ($list['is_admin']) {
                                $request_count = $this->getUserGroupJoiningRequestTable()->countGroupMemberRequests($list['group_id'])->memberCount;
                            }
                            $is_requested = 0;
                            $requestedHystory = $this->getUserGroupJoiningRequestTable()->checkActiveRequestExist($list['group_id'], $user_id);
                            if (!empty($requestedHystory) && $requestedHystory->user_group_joining_request_id != '') {
                                $is_requested = 1;
                            }
                            $is_invited = 0;
                            $invitedHystory = $this->getGroupJoiningInvitationTable()->checkInvited($user_id, $list['group_id']);
                            if (!empty($invitedHystory) && $invitedHystory->user_group_joining_invitation_id != '') {
                                $is_invited = 1;
                            }
                            if (!empty($list['group_photo_photo']))
                                $list['group_photo_photo'] = $config['pathInfo']['absolute_img_path'] . $config['image_folders']['group'] . $list['group_id'] . '/medium/' . $list['group_photo_photo'];
                            else
                                $list['group_photo_photo'] = $config['pathInfo']['absolute_img_path'] . '/images/group-img_def.jpg';
                            $is_owner = false;
                            if ($list['user_group_is_owner']){
                                $is_owner = true;
                            }

                            $arr_group_list[] = array(
                                'group_id' => $list['group_id'],
                                'group_title' => $list['group_title'],
                                'group_seo_title' => $list['group_seo_title'],
                                'group_type' => $list['group_type'],
                                'group_status' => $list['group_status'],
                                'group_photo_photo' => $list['group_photo_photo'],
                                'country_title' => $list['country_title'],
                                'country_code' => $list['country_code'],
                                'member_count' => $list['member_count'],
                                'friend_count' => $list['friend_count'],
                                'city' => $list['city'],
                                'is_admin' => $list['is_admin'],
                                'is_member' => $list['is_member'],
                                'is_created_by_user' => $is_owner,
                                'request_count' => $request_count,
                                'is_requested' => $is_requested,
                                'is_invited' => $is_invited,
                                'tag_categories_count' => count($tag_category),
                                'tags' => $tags,
                            );
                        }
                    }
                } else {
                    $error = "No Record Found.";
                }
            }
        }
        $dataArr[0]['flag'] = (empty($error)) ? 'success' : 'failure';
        $dataArr[0]['message'] = $error;
        $dataArr[0]['groups'] = $arr_group_list;
        echo json_encode($dataArr);
        exit;
    }
	public function groupdetailsAction(){
		$request = $this->getRequest();
		if($this->getRequest()->getMethod() == 'POST') {
			$flag=0;
			$config = $this->getServiceLocator()->get('Config');
			$postedValues = $this->getRequest()->getPost();
			
			$offset = trim($postedValues['nparam']);
			$limit = trim($postedValues['countparam']);
			$type = trim($postedValues['type']);
			$activity = trim($postedValues['activity']);
			$group_id = trim($postedValues['groupid']);
			$accToken = strip_tags(trim($postedValues['accesstoken']));

			if ((!isset($accToken)) || (trim($accToken) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			if ((!isset($group_id)) || (trim($group_id) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			if (isset($group_id) && !is_numeric($group_id)) {
 				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid GroupId.";
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
			$user_details = $this->getUserTable()->getUserByAccessToken($accToken);
			if(empty($user_details)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Invalid Access Token.";
				echo json_encode($dataArr);
				exit;
			}
			$user_id = $user_details->user_id;
			$offset = (int) $offset;
			$limit = (int) $limit;
			$offset =($offset>0)?$offset-1:0;
			$offset = $offset*$limit;
			$newsfeedsList = $this->getGroupsTable()->getGroupNewsFeeds($user_id,$type,$group_id,$activity,(int) $limit,(int) $offset);
			$groupdetailslist  = $this->getGroupsTable()->getGroupDetails($group_id,$user_id);	
			$arr_group_list = array();
			$feeds = array();
			if (!empty($groupdetailslist)){
				if (!empty($groupdetailslist->group_photo_photo))
				$groupdetailslist->group_photo_photo = $config['pathInfo']['absolute_img_path'].$config['image_folders']['group'].$groupdetailslist->group_id.'/medium/'.$groupdetailslist->group_photo_photo;
				else
				$groupdetailslist->group_photo_photo = $config['pathInfo']['absolute_img_path'].'/images/group-img_def.jpg';
				$tag_category = $this->getGroupTagTable()->getAllGroupTagCategiry($groupdetailslist->group_id);
				$tags = $this->getGroupTagTable()->getAllGroupTagsForAPI($groupdetailslist->group_id);
                if(!empty($tags)) {
                    $tags = $this->formatTagsWithCategory($tags, "|");
                }
                    $arr_group_list[] = array(
					'group_id' =>$groupdetailslist->group_id,
					'group_title' =>$groupdetailslist->group_title,
					'group_seo_title' =>$groupdetailslist->group_seo_title,
					'group_type' =>(empty($groupdetailslist->group_type))?"":$groupdetailslist->group_type,
					'group_photo_photo' =>$groupdetailslist->group_photo_photo,										 
					'country_title' =>$groupdetailslist->country_title,
					'country_code' =>$groupdetailslist->country_code,
					'member_count' =>$groupdetailslist->member_count,
					'friend_count' =>$groupdetailslist->friend_count,
					'city' =>$groupdetailslist->city,	
					'tag_categories_count' =>count($tag_category),
					'tags' =>$tags,
					);
				$flag = 1;
			}

			$groupUsers = $this->getUserGroupTable()->fetchAllUserListForGroup($group_id,$user_id,0,5)->toArray();
			$tempmembers = array();
			if (!empty($groupUsers)) {
				
				foreach ($groupUsers as $list) {
					unset($list['user_register_type']);
					$list['profile_photo'] = $this->manipulateProfilePic($list['user_id'], $list['profile_photo'], $list['user_fbid']);
                    $is_friend = ($this->getUserFriendTable()->isFriend($list['user_id'],$user_id))?1:0;
                    $is_requested = ($this->getUserFriendTable()->isRequested($list['user_id'],$user_id))?1:0;
                    $is_pending = ($this->getUserFriendTable()->isPending($list['user_id'],$user_id))?1:0;
					$friend_status ="";
                    if ( $list['user_id'] != $user_id ){
                        $is_friend = ($this->getUserFriendTable()->isFriend($list['user_id'],$user_id))?1:0;
                        $is_requested = ($this->getUserFriendTable()->isRequested($list['user_id'],$user_id))?1:0;
                        $is_pending = ($this->getUserFriendTable()->isPending($list['user_id'],$user_id))?1:0;
                        if($is_friend){
                            $friend_status = 'IsFriend';
                        }
                        else if($is_requested){
                            $friend_status = 'RequestSent';
                        }
                        else if($is_pending){
                            $friend_status = 'RequestPending';
                        }
                        else{
                            $friend_status = 'NoFriends';
                        }
                    }
					$list['friend_status']= $friend_status;
					unset($list['is_friend']);
					unset($list['is_requested']);
					unset($list['get_request']);
					$tempmembers[] = $list;
				}
				$flag = 1;
			}

			if(!empty($newsfeedsList)){
				foreach($newsfeedsList as $list){
                    $friendl_status =  "";
					$profileDetails = $this->getUserTable()->getProfileDetails($list['user_id']);
					$userprofiledetails = array();
                    $profile_details_photo = $this->manipulateProfilePic($profileDetails->user_id, $profileDetails->profile_photo, $profileDetails->user_fbid);
                    if ( $list['user_id'] != $user_id ){
                        $is_lfriend = ($this->getUserFriendTable()->isFriend($list['user_id'],$user_id))?1:0;
                        $is_lrequested = ($this->getUserFriendTable()->isRequested($list['user_id'],$user_id))?1:0;
                        $is_lpending = ($this->getUserFriendTable()->isPending($list['user_id'],$user_id))?1:0;
                        if($is_lfriend){
                            $friendl_status = 'IsFriend';
                        }
                        else if($is_lrequested){
                            $friendl_status = 'RequestSent';
                        }
                        else if($is_lpending){
                            $friendl_status = 'RequestPending';
                        }
                        else{
                            $friendl_status = 'NoFriends';
                        }
                    }
                    //$dataArr[0]['friendship_status'] = $friend_status;
					$userprofiledetails[] = array('user_id'=>$profileDetails->user_id,
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
								'profile_photo'=>$profile_details_photo,
                                'friendship_status' => $friendl_status,
								);
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
										'postedby'=>$userprofiledetails,
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
											'postedby'=>$userprofiledetails,
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
											'postedby'=>$userprofiledetails,
							); 
						break;
					}
				}
			}

			if($flag){
				$dataArr[0]['flag'] = "Success";
				$dataArr[0]['groupdetails'] = $arr_group_list;
				$dataArr[0]['groupmembers'] = $tempmembers;
				$dataArr[0]['groupposts'] = $feeds;
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
	public function groupmembersAction(){
		$arrMembers = array();
    	if($this->getRequest()->getMethod() == 'POST') {
			$config = $this->getServiceLocator()->get('Config');
			$postedValues = $this->getRequest()->getPost();
			$offset = trim($postedValues['nparam']);
			$limit = trim($postedValues['countparam']);
			$type = trim($postedValues['type']);
			$group_id = trim($postedValues['groupid']);
			$accToken = strip_tags(trim($postedValues['accesstoken']));

			if ((!isset($accToken)) || (trim($accToken) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			if ((!isset($group_id)) || (trim($group_id) == '')) {
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Request Not Authorised.";
				echo json_encode($dataArr);
				exit;
			}
			if (isset($group_id) && !is_numeric($group_id)) {
 				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Please input a Valid GroupId.";
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
			$myinfo = $this->getUserTable()->getUserByAccessToken($accToken);
			if(empty($myinfo)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Invalid Access Token.";
				echo json_encode($dataArr);
				exit;
			}			 
			$group  = $this->getGroupsTable()->getPlanetinfo($group_id);
			if(empty($group)){
				$dataArr[0]['flag'] = "Failure";
				$dataArr[0]['message'] = "Given group not exist in this system.";
				echo json_encode($dataArr);
				exit;
			}		 
			$offset = (int) $offset;
			$limit = (int) $limit;
			$offset =($offset>0)?$offset-1:0;
			$offset = $offset*$limit;
			$members_list = $this->getUserGroupTable()->getMembers($group_id,$myinfo->user_id,$type,(int) $offset,(int) $limit);			
			if(!empty($members_list)){
				foreach($members_list as $list){
					$tag_category = $this->getUserTagTable()->getAllUserTagCategiry($list['user_id']);
                    $tags = $this->getUserTagTable()->getAllUserTagsForAPI($list['user_id']);
                    if(!empty($tags)){
                        $tags = $this->formatTagsWithCategory($tags,"|");
                    }
					$objcreated_group_count = $this->getUserGroupTable()->getCreatedGroupCount($list['user_id']);
					if(!empty($objcreated_group_count)){
					$created_group_count = $objcreated_group_count->created_group_count;
					}else{$created_group_count =0;}
					$is_friend = ($this->getUserFriendTable()->isFriend($list['user_id'],$myinfo->user_id))?1:0;
					$is_requested = ($this->getUserFriendTable()->isRequested($list['user_id'],$myinfo->user_id))?1:0;
					$isPending = ($this->getUserFriendTable()->isPending($list['user_id'],$myinfo->user_id))?1:0;
					$profile_photo = $this->manipulateProfilePic($list['user_id'], $list['profile_icon'], $list['user_fbid']);
					$friend_status ="";
					if($is_friend){
						$friend_status = 'IsFriend';
					}
					else if($is_requested){
						$friend_status = 'RequestSent';
					}
					else if($isPending){
						$friend_status = 'RequestPending';
					}
					else if ( $myinfo->user_id == $list['user_id']){
						$friend_status = '';
					}else{
						$friend_status = 'NoFriends';
					}

					$arrMembers[] = array(
									'user_id'=>$list['user_id'],
									'user_given_name'=>$list['user_given_name'],
									'user_profile_name'=>$list['user_profile_name'],
									'country_title'=>$list['country_title'],
									'country_code'=>$list['country_code'],
									'city'=>$list['city'],
									'profile_photo'=>$profile_photo,
									'tag_categories_count' =>count($tag_category),
									'tags' =>$tags,
									'joined_group_count'=>$list['group_count'],
									'created_group_count'=>$created_group_count,
									'is_admin'=>($type == 'pending')?0:$list['is_admin'],
									'user_group_is_owner'=>($type == 'pending')?0:$list['user_group_is_owner'],
									'user_group_role'=>($type == 'pending')?'':$list['user_group_role'],
									'friendship_status'=>$friend_status,
									);
				}
			}
			$dataArr[0]['flag'] =  'Success';
			$dataArr[0]['groupmembers'] = $arrMembers;		
			echo json_encode($dataArr);
			exit;
		}else{
			$dataArr[0]['flag'] = "Failure";
			$dataArr[0]['message'] = "Request Not Authorised.";
			echo json_encode($dataArr);
			exit;
		}
    }
    public function manipulateProfilePic($user_path_id, $profile_path_photo = null, $fb_path_id = null){
    	$config = $this->getServiceLocator()->get('Config');
		$return_photo = null;
		if (!empty($profile_path_photo))
			$return_photo = $config['pathInfo']['absolute_img_path'].$config['image_folders']['profile_path'].$user_path_id.'/'.$profile_path_photo;
		else if(isset($fb_path_id) && !empty($fb_path_id))
			$return_photo = 'http://graph.facebook.com/'.$fb_path_id.'/picture?type=normal';
		else
			$return_photo = $config['pathInfo']['absolute_img_path'].'/images/noimg.jpg';
		return $return_photo;

	}
	public function getGroupQuestionsAction() {
        $dataArr                                                    = array();               // declare array for response data
        $arrQuestions                                               = array();              // declare array for questions
        $request                                                    = $this->getRequest(); // create request object

        // if request is of type POST
        if ($request->isPost()) {
            // create post object
            $post                                                   = $request->getPost();
            // get access token
			$accToken                                               = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            // get group id
             $intGroupId                                            = (isset($post['groupid']) && $post['groupid'] != null && $post['groupid'] != '' && $post['groupid'] != 'undefined') ? strip_tags(trim($post['groupid'])) : '';

             // check access token
             if ($accToken == '') {
				$dataArr[0]['flag']                                 = $this->flagError;
				$dataArr[0]['message']                              = "Request Not Authorised.";
                $dataArr[0]['questions']                            = $arrQuestions;
				echo json_encode($dataArr);
				exit;
             }
             // check user id
             if ($intGroupId == '') {
                    $dataArr[0]['flag']                             = $this->flagError;
                    $dataArr[0]['message']                          = "Request Not Authorised.";
                    $dataArr[0]['questions']                        = $arrQuestions;
                    echo json_encode($dataArr);
                    exit;
             }

             // get user details on the basis of access token
			$user_details                                           = $this->getUserTable()->getUserByAccessToken($accToken);

            if(!empty($user_details)) {
                // get user id on the basis of access token i.e. the user which is logged in
                $loggedin_userId                                    = $user_details->user_id;

                // get group
                $arrGroup                                           = $this->getGroupTable()->getPlanetinfo($intGroupId);
                if(!empty($arrGroup)) {
                    // get questions
                     $arrQuestionnaire                              = $this->getGroupJoiningQuestionnaireTable()->getQuestionnaireArray($intGroupId);
                     if(!empty($arrQuestionnaire)){
                         $ctr                                       = 0;
                         foreach($arrQuestionnaire as $question){
                             $arrQuestions[$ctr]['questionnaire_id']    = $question['questionnaire_id'];
                             $arrQuestions[$ctr]['question']            = $question['question'];
                             $arrQuestions[$ctr]['answer_type']         = strtolower($question['answer_type']);

                             // check answer type for options
                             if($question['answer_type'] == 'checkbox' || $question['answer_type'] == 'radio'){
                                $arrOptionsDetails                 = $this->getGroupQuestionnaireOptionsTable()->getoptionOfOneQuestion($question['questionnaire_id']);
                                $arrOptions                        = array();
                                $optCtr                            = 0;
                                foreach($arrOptionsDetails as $option){
                                    $arrOptions[$optCtr]['option_id']  = $option['option_id'];
                                    $arrOptions[$optCtr]['option']     = $option['option'];
                                    $optCtr++;
                                }// foreach
                                $arrQuestions[$ctr]['options']          = $arrOptions;
                             }// if check answer type
                             $ctr++;
                         }// foreach
                            $dataArr[0]['flag']                     = $this->flagSuccess;
                            $dataArr[0]['message']                  = "";
                            $dataArr[0]['questions']                = $arrQuestions;
                     }else{
                        $dataArr[0]['flag']                         = $this->flagSuccess;
                        $dataArr[0]['message']                      = "No question exists for this group.";
                        $dataArr[0]['questions']                    = $arrQuestions;
                     }
                }else{
                    $dataArr[0]['flag']                             = $this->flagError;
                    $dataArr[0]['message']                          = "Group not exist in the system.";
                    $dataArr[0]['questions']                        = $arrQuestions;
                }
            }else{
                $dataArr[0]['flag']                                 = $this->flagError;
				$dataArr[0]['message']                              = "Invalid Access Token.";
                $dataArr[0]['questions']                            = $arrQuestions;
            }
        }else{
            $dataArr[0]['flag']                                     = $this->flagError;
			$dataArr[0]['message']                                  = "Request Not Authorised.";
            $dataArr[0]['questions']                                = $arrQuestions;
        }

        echo json_encode($dataArr);
        exit;

    }	  
    public function joinGroupAction() {
        $dataArr                                                    = array();               // declare array for response data
        $arrQuestions                                               = array();              // declare array for questions/answer
        $request                                                    = $this->getRequest(); // create request object

        // if request is of type POST
        if ($request->isPost()) {
             $post                                                  = $request->getPost();
            // get access token
			$accToken                                               = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
            // get group id
             $intGroupId                                            = (isset($post['groupid']) && $post['groupid'] != null && $post['groupid'] != '' && $post['groupid'] != 'undefined') ? strip_tags(trim($post['groupid'])) : '';
             // question ans answers0
             $arrQuestionAnswer                                     = (isset($post['QuestionAnswers']) && $post['QuestionAnswers'] != null && $post['QuestionAnswers'] != '' && $post['QuestionAnswers'] != 'undefined') ? strip_tags(trim($post['QuestionAnswers'])) : '';

            // check access token
            if ($accToken != '') {
                 // check group id
                 if ($intGroupId != '') {
                    // get user details on the basis of access token
                    $user_details                                   = $this->getUserTable()->getUserByAccessToken($accToken);
                    if(!empty($user_details)) {
                        // get user id on the basis of access token i.e. the user which is logged in
                        $loggedin_userId                            = $user_details->user_id;
                        // get group
                        $arrGroup                                   = $this->getGroupTable()->getPlanetinfo($intGroupId);
                        if(!empty($arrGroup)) {
                            $strGroupType                           = $arrGroup['group_type'];
                            // check user if already  the member or not
                            $usergroup                              = $this->getUserGroupTable()->getUserGroup($loggedin_userId,$intGroupId);
                            if(empty($usergroup)){
                                // check the question and options
                                $questionStatus                     = $this->fncValidateQuestionAnswer($intGroupId, $arrQuestionAnswer);
                                if($questionStatus == 0){
                                    // for open group
                                    if($arrGroup['group_type'] == 'open'){
                                        $user_data['user_group_user_id']    = $loggedin_userId;
                                        $user_data['user_group_group_id']   = $intGroupId;
                                        $user_data['user_group_status']     = "available";
                                        $this->getUserGroupTable()->AddMembersTOGroup($user_data);
                                        //save question and answer
                                        $this->fncSaveQuestionAnswer($intGroupId, $loggedin_userId, $arrQuestionAnswer);
                                        $config = $this->getServiceLocator()->get('Config');
                                        $base_url = $config['pathInfo']['base_url'];
                                        $msg = $user_details->user_given_name." Joined in the group ".$arrGroup['group_title'];
                                        $subject = 'Group joining Request';
                                        $from = 'admin@jeera.com';
                                        $process = 'Joined';
                                        $admin_users = $this->getUserGroupTable()->getAllAdminUsers($intGroupId);
                                        foreach($admin_users as $admins){
                                            if($user_details->user_id!=$admins->user_group_user_id){
                                                $this->UpdateNotifications($admins->user_group_user_id,$msg,4,$subject,$from,$user_details->user_id,$intGroupId,$process);
                                            }
                                        }
                                        $dataArr[0]['flag']                 = $this->flagSuccess;
                                        $dataArr[0]['message']              = "User added into group.";
                                        
                                    }else if($arrGroup['group_type'] == 'private'){// for private group
                                        // check invitation existence
                                        $invitedHystory = $this->getGroupJoiningInvitationTable()->checkInvited($loggedin_userId,$intGroupId);
                                        if($invitedHystory['user_group_joining_invitation_id'] != ''){
                                            $user_data['user_group_user_id']    = $loggedin_userId;
                                            $user_data['user_group_group_id']   = $intGroupId;
                                            $user_data['user_group_status']     = "available";
                                            $this->getUserGroupTable()->AddMembersTOGroup($user_data);

                                            //save question and answer
                                            $this->fncSaveQuestionAnswer($intGroupId, $loggedin_userId, $arrQuestionAnswer);
                                            $config = $this->getServiceLocator()->get('Config');
                                            $base_url = $config['pathInfo']['base_url'];
                                            $msg = $user_details->user_given_name." Joined in the group ".$arrGroup['group_title'];
                                            $subject = 'Group joining Request';
                                            $from = 'admin@jeera.com';
                                            $process = 'Joined';
                                            $admin_users = $this->getUserGroupTable()->getAllAdminUsers($intGroupId);
                                            foreach($admin_users as $admins){
                                                    if($user_details->user_id!=$admins->user_group_user_id){
                                                    $this->UpdateNotifications($admins->user_group_user_id,$msg,4,$subject,$from,$user_details->user_id,$intGroupId,$process);
                                                    }
                                            }
                                            $dataArr[0]['flag']                 = $this->flagSuccess;
                                            $dataArr[0]['message']              = "User added into group.";
                                        }else{
                                            $dataArr[0]['flag']                 = $this->flagError;
                                            $dataArr[0]['message']              = "Invitation does not exist in system.";
                                        }
                                    }else if($arrGroup['group_type'] == 'public'){
                                        // check request existence
                                         $invitedHystory = $this->getUserGroupJoiningRequestTable()->checkIfrequestExist($loggedin_userId,$intGroupId);
                                         if($invitedHystory['user_group_joining_request_id'] == ''){
                                                $user_data['user_group_joining_request_user_id']    = $loggedin_userId;
                                                $user_data['user_group_joining_request_group_id']   = $intGroupId;
                                                $user_data['user_group_joining_request_status']     = "active";
                                                $this->getUserGroupJoiningRequestTable()->AddRequestTOGroup($user_data);

                                                //save question and answer
                                                $this->fncSaveQuestionAnswer($intGroupId, $loggedin_userId, $arrQuestionAnswer);
                                                $config = $this->getServiceLocator()->get('Config');
                                                $base_url = $config['pathInfo']['base_url'];
                                                $msg = $user_details->user_given_name." requested to join in the group ".$arrGroup['group_title'];
                                                $subject = 'Group joining Request';
                                                $from = 'admin@jeera.com';
                                                $process = 'Requested';
                                                $admin_users = $this->getUserGroupTable()->getAllAdminUsers($intGroupId);
                                                //print_r($admin_users);die();
                                                foreach($admin_users as $admins){
                                                    if($user_details->user_id!=$admins->user_group_user_id){
                                                        $this->UpdateNotifications($admins->user_group_user_id,$msg,4,$subject,$from,$user_details->user_id,$intGroupId,$process);
                                                    }
                                                }
                                                $dataArr[0]['flag']     = $this->flagSuccess;
                                                $dataArr[0]['message']  = "Request has been sent.";
                                         }else{
                                             $dataArr[0]['flag']        = $this->flagError;
                                             $dataArr[0]['message']     = "Request has already been sent.";
                                         }
                                    }
                                }else{
                                    $dataArr[0]['flag']             = $this->flagError;
                                    $dataArr[0]['message']          = "Invalid question or options.";
                                }
                            }else{
                                $dataArr[0]['flag']                 = $this->flagError;
                                $dataArr[0]['message']              = "User is already the group member.";
                            }
                        }else{
                            $dataArr[0]['flag']                     = $this->flagError;
                            $dataArr[0]['message']                  = "Group not exist in the system.";
                        }
                    }else{
                        $dataArr[0]['flag']                         = $this->flagError;
                        $dataArr[0]['message']                      = "Invalid Access Token.";
                    }
                }else{
                    $dataArr[0]['flag']                             = $this->flagError;
                    $dataArr[0]['message']                          = "Request Not Authorised.";
                }
            }else{
                $dataArr[0]['flag']                                 = $this->flagError;
				$dataArr[0]['message']                              = "Request Not Authorised.";
            }
        }else{
            $dataArr[0]['flag']                                     = $this->flagError;
			$dataArr[0]['message']                                  = "Request Not Authorised.";
        }

        echo json_encode($dataArr);
        exit;
     }
    public function fncSaveQuestionAnswer($intGroupId, $intUserId, $jsonQuestionAnswer){
        if($jsonQuestionAnswer != ''){
             $arrQuestionList                 = json_decode($jsonQuestionAnswer, TRUE);

             if(!empty($arrQuestionList)){
                 foreach($arrQuestionList as $question){
                     $arrQuestionDetails            = $this->getGroupJoiningQuestionnaireTable()->getQuestionFromQuestionId($question['questionnaire_id']);
                     if(!empty($arrQuestionDetails)){
                         $data                          = array();
                        $arrGroupQuestion = $this->getGroupJoiningQuestionnaireTable()->getQuestionFromQuestionIdAndGroupId($question['questionnaire_id'], $intGroupId);
                        if(!empty($arrGroupQuestion)){
                            if($question['answer_type'] == 'radio'|| $question['answer_type'] == 'checkbox'){
                               $data['group_id']           = $intGroupId;
                               $data['question_id']        = $question['questionnaire_id'];
                               $data['selected_options']   = $question['selected_options'];
                               $data['added_user_id']      = $intUserId;
                           }else{
                               $data['group_id']           = $intGroupId;
                               $data['question_id']        = $question['questionnaire_id'];
                               $data['answer']             = $question['answer'];
                               $data['added_user_id']      = $intUserId;
                           }
                           // save question with answers
                           $this->getGroupQuestionnaireAnswersTable()->AddAnswer($data);
                        }
                     }

                 }// foreach
             }
        }
    }
    // fucntion to validate question and answer
    public function fncValidateQuestionAnswer($intGroupId, $jsonQuestionAnswer){
        $questionError  = 0;
        // check question/ answer
        if($jsonQuestionAnswer != ''){
             $arrQuestionList                = json_decode($jsonQuestionAnswer, TRUE);
             if(!empty($arrQuestionList)){
                  foreach($arrQuestionList as $question){
                     // check question existence
                     $arrQuestionDetails     = $this->getGroupJoiningQuestionnaireTable()->getQuestionFromQuestionId($question['questionnaire_id']);
                                                
                     if(!empty($arrQuestionDetails)){
                         // check group's question
                         $arrGroupQuestion   = $this->getGroupJoiningQuestionnaireTable()->getQuestionFromQuestionIdAndGroupId($question['questionnaire_id'], $intGroupId);
                         if(!empty($arrGroupQuestion)){
                            if($question['answer_type'] == 'radio'|| $question['answer_type'] == 'checkbox'){
                                if(trim($question['selected_options']) != ''){
                                    // check options
                                    $sptOption   = explode(',',$question['selected_options']);
                                    for($a=0; $a<count($sptOption); $a++ ){
                                       $arrOption   = $this->getGroupQuestionnaireOptionsTable()->getSelectedOptionDetails($sptOption[0]);
                                       if($arrOption[0]['question_id'] != $question['questionnaire_id']){
                                           $questionError   = 1;
                                       }
                                    }// for
                                }else{
                                  $questionError   = 1;
                                }
                             }
                         }else{
                             $questionError      = 1;
                         }
                     }else{
                             $questionError      = 1;
                     }
                  }// foreach
             }else{
                 $questionError      = 1;
             }
         }

         return $questionError;
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
    public function getGroupJoinRequestListAction() {
        $dataArr   = array(); 
        $arrRequests    = array();
        $config    = $this->getServiceLocator()->get('Config');     
	    $request       = $this->getRequest();

        if ($request->isPost()) {   
            $post       = $request->getPost();  
            $accToken   = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';                                                
            $group_id   = (isset($post['groupid']) && $post['groupid'] != null && $post['groupid'] != '' && $post['groupid'] != 'undefined') ? strip_tags(trim($post['groupid'])) : '';
            $nparam     = (isset($post['nparam']) && $post['nparam'] != null && $post['nparam'] != '' && $post['nparam'] != 'undefined') ? strip_tags(trim($post['nparam'])) : '';
            $countparam = (isset($post['countparam']) && $post['countparam'] != null && $post['countparam'] != '' && $post['countparam'] != 'undefined') ? strip_tags(trim($post['countparam'])) : '';
            //echo $accToken;die();
            if ($accToken == '') {
                $dataArr[0]['flag']         = $this->flagError;
                $dataArr[0]['message']      = "Request Not Authorised.";
                $dataArr[0]['groupmembers'] = $arrRequests;
                echo json_encode($dataArr);
                exit;
            }                                                
            if ($group_id == '') {
                $dataArr[0]['flag']         = $this->flagError;
                $dataArr[0]['message']      = "Request Not Authorised.";
                $dataArr[0]['groupmembers'] = $arrRequests;
                echo json_encode($dataArr);
                exit;
            }else if (!is_numeric($group_id)) { 
                $dataArr[0]['flag']         = $this->flagError;
                $dataArr[0]['message']      = "Group Id must be numeric.";
                $dataArr[0]['groupmembers'] = $arrRequests;
                echo json_encode($dataArr);
                exit;
            }
            if ($nparam != '' && !is_numeric($nparam)) {
                $dataArr[0]['flag']          = $this->flagError;
                $dataArr[0]['message']       = "Offest must be numeric.";
                $dataArr[0]['groupmembers']  = $arrRequests;
                echo json_encode($dataArr);
                exit;
            }
            if ($countparam != '' && !is_numeric($countparam)) {
                $dataArr[0]['flag']         = $this->flagError;
                $dataArr[0]['message']      = "Limit must be numeric.";
                $dataArr[0]['groupmembers'] = $arrRequests;
                echo json_encode($dataArr);
                exit;
            } 
            $user_details       = $this->getUserTable()->getUserByAccessToken($accToken);
            if(empty($user_details)) {
                $dataArr[0]['flag']          = $this->flagError;
                $dataArr[0]['message']       = "Invalid Access Token.";
                $dataArr[0]['groupmembers']  = $arrRequests;
                echo json_encode($dataArr);
                exit;
            }
            $group      = $this->getGroupsTable()->getPlanetinfo($group_id);
            if(empty($group)) {         // if group does not exist
                $dataArr[0]['flag']         = $this->flagError;
                $dataArr[0]['message']      = "Group does not exist in this system.";
                $dataArr[0]['groupmembers']  = $arrRequests;
                echo json_encode($dataArr);
                exit;
            } 
            $owner      = $this->getUserGroupTable()->checkOwner($group_id, $user_details->user_id);
            if($owner == false) {
                $dataArr[0]['flag']         = $this->flagError;
                $dataArr[0]['message']      = "Logged in user must be owner of the group.";
                $dataArr[0]['groupmembers'] = $arrRequests;
                echo json_encode($dataArr);
                exit;
            } 
			$offset = (int) $nparam;
			$limit = (int) $countparam;
			$offset =($offset>0)?$offset-1:0;
			$offset = $offset*$limit;
            $arrRequestslist    = $this->getUserGroupJoiningRequestTable()->getGroupJoinRequestList($group_id, 'active', $offset, $limit);                                                
            if(!empty($arrRequestslist)) {
                $ctr = 0;
                // loop through the array for creating response array
                foreach($arrRequestslist as $requests) {
                    $arrRequests[$ctr]['user_id']           = $requests['user_id'];
                    $arrRequests[$ctr]['user_given_name']   = $requests['user_given_name'];
                    $arrRequests[$ctr]['user_profile_name'] = $requests['user_profile_name'];
                    $arrRequests[$ctr]['country_title']     = $requests['country_title'];
                    $arrRequests[$ctr]['country_code']      = $requests['country_code'];
                    $arrRequests[$ctr]['city']              = $requests['name'];                                               
                    $arrRequests[$ctr]['profile_photo']     = $this->manipulateProfilePic($requests['user_id'], $requests['profile_photo'], $requests['user_fbid']);
                    $arrRequests[$ctr]['tag_count']         = $requests['tag_count'];
                    // get user tag category details
                    $categoryctr        = 0;
                    $arrUserTagCategory = array();
                    $arrUserTagCategory = $this->getUserTagTable()->getAllUserTagCategiry($requests['user_id']);
                    $tags = $this->getUserTagTable()->getAllUserTagsForAPI($requests['user_id']);
                    if(!empty($tags)){
                        $tags = $this->formatTagsWithCategory($tags,"|");
                    }

                    $arrRequests[$ctr]['tag_categories_count']          = count($arrUserTagCategory);
                    $arrRequests[$ctr]['tags']          = $tags;
                    $arrRequests[$ctr]['joined_group_count']    = $requests['joined_group_count'];
                    $arrRequests[$ctr]['created_group_count']   = $requests['created_group_count'];
                    $arrRequests[$ctr]['is_admin']              = $requests['is_admin'];

                    if($this->getUserFriendTable()->isFriend($user_details->user_id, $requests['user_id']) == true) {
                        $arrRequests[$ctr]['friendship_status'] = 'Friends';
                    } elseif($this->getUserFriendTable()->isRequested($user_details->user_id, $requests['user_id']) == true) {
                        $arrRequests[$ctr]['friendship_status'] = 'Requested';
                    }  elseif($this->getUserFriendTable()->isPending($user_details->user_id, $requests['user_id']) == true) {
                        $arrRequests[$ctr]['friendship_status'] = 'Pending';
                    }  else {
                        $arrRequests[$ctr]['friendship_status'] = 'Not a friend';
                    } 
                    $questionnairectr        = 0;
                    $arrUserQuestionnaire    = array();
                    $user_questionnaire      = $this->getGroupJoiningQuestionnaireTable()->getQuestionnaireArray($group_id);
                    // loop through user questionnaire array
                    foreach($user_questionnaire as $questionnaire) {
                        $arrUserQuestionnaire[$questionnairectr]['question_id']     = $questionnaire['questionnaire_id'];
                        $arrUserQuestionnaire[$questionnairectr]['answer_type']     = $questionnaire['answer_type'];                                                
                        $questionnaireoptionsctr            = 0;
                        $arrUserQuestionnaireOptions        = array();
                        $user_questionnaire_options         = $this->getGroupQuestionnaireOptionsTable()->getoptionOfOneQuestion($questionnaire['questionnaire_id']);

                        // loop through user questionnaire options array
                        foreach($user_questionnaire_options as $questionnaireoptions) {
                            $arrUserQuestionnaireOptions[$questionnaireoptionsctr]['option_id'] = $questionnaireoptions['option_id'];
                            $arrUserQuestionnaireOptions[$questionnaireoptionsctr]['option']    = $questionnaireoptions['option'];
                            $questionnaireoptionsctr++;
                        }

                        $arrUserQuestionnaire[$questionnairectr]['options'] = $arrUserQuestionnaireOptions;

                        // get answer of this question
                        $user_questionnaire_answer      = $this->getGroupQuestionnaireAnswersTable()->getAnswerOfOneQuestion($group_id, $questionnaire['questionnaire_id'], $requests['user_id']);
                       if($questionnaire['answer_type'] =='Textarea'){
                            $arrUserQuestionnaire[$questionnairectr]['answer']  = $user_questionnaire_answer['answer'];
                            $questionnairectr++;
                       }else{
                            $arrUserQuestionnaire[$questionnairectr]['answer']  = $user_questionnaire_answer['selected_options'];
                            $questionnairectr++;
                       }
                    }
                    $arrRequests[$ctr]['questionnaire']     = $arrUserQuestionnaire;
                    $ctr++;
                }
                $dataArr[0]['flag']         = $this->flagSuccess;
                $dataArr[0]['message']      = "";
                $dataArr[0]['groupmembers'] = $arrRequests;
            } else {  
                $dataArr[0]['flag']         = $this->flagSuccess;
                $dataArr[0]['message']      = "There are no group join requests availbale.";
                $dataArr[0]['groupmembers'] = $arrRequests;
            }
        } else {        // if request is not of type POST
            $dataArr[0]['flag']         = $this->flagError;
            $dataArr[0]['message']      = "Request Not Authorised.";
            $dataArr[0]['groupmembers'] = $arrRequests;
	}
        echo json_encode($dataArr);
        exit;
    }
    public function acceptRejectGroupJoinRequestAction() {
        $dataArr    = array();  
	    $request    = $this->getRequest();
        if ($request->isPost()) {  
            $post       = $request->getPost();                                              
            $accToken   = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';                                                
            $group_id   = (isset($post['groupid']) && $post['groupid'] != null && $post['groupid'] != '' && $post['groupid'] != 'undefined') ? strip_tags(trim($post['groupid'])) : '';
            $user_id    = (isset($post['user_id']) && $post['user_id'] != null && $post['user_id'] != '' && $post['user_id'] != 'undefined') ? strip_tags(trim($post['user_id'])) : '';
            $operation  = (isset($post['operation']) && $post['operation'] != null && $post['operation'] != '' && $post['operation'] != 'undefined') ? strip_tags(trim($post['operation'])) : '';                                               
            if ($accToken == '') {
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            }    
            if ($group_id == '') {
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            } else if (!is_numeric($group_id)) { 
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "Group Id must be numeric.";
                echo json_encode($dataArr);
                exit;
            }
            if ($user_id == '') {
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            } else if (!is_numeric($user_id)) {  
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "User Id must be numeric.";
                echo json_encode($dataArr);
                exit;
            }
            if ($operation == '') {
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "Request Not Authorised.";
                echo json_encode($dataArr);
                exit;
            } 
            $user_details   = $this->getUserTable()->getUserByAccessToken($accToken);
            if(empty($user_details)) {
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "Invalid Access Token.";
                echo json_encode($dataArr);
                exit;
            } 
            $group      = $this->getGroupsTable()->getPlanetinfo($group_id);
            if(empty($group)) {
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "Group does not exist in this system.";
                echo json_encode($dataArr);
                exit;
            } 
            $user   = $this->getUserTable()->getUser($user_id);
            if(empty($user)) {
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "User does not exist in this system.";
                echo json_encode($dataArr);
                exit;
            }
            // check whether logged in user is owner of the group or not
            $owner  = $this->getUserGroupTable()->checkOwner($group_id, $user_details->user_id);
            if($owner == false) { 
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "Logged in user must be owner of the group.";
                echo json_encode($dataArr);
                exit;
            }
            // check whether user is already a member of group or not
            $usergroup   = $this->getUserGroupTable()->getUserGroup($user_id, $group_id);
            if(!empty($usergroup)) {  
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "User is already a member of group.";
                echo json_encode($dataArr);
                exit;
            }
            // check whether user has made a request to join the group or not
            $invitedHistory      = $this->getUserGroupJoiningRequestTable()->checkIfrequestExist($user_id, $group_id);
            if($invitedHistory['user_group_joining_request_id'] == '') {    // if user has not made any request to join the group
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "There is no request to join the group.";
                echo json_encode($dataArr);
                exit;
            } else if($invitedHistory['user_group_joining_request_status'] != 'active') {   // if user's request has already been processed
                $dataArr[0]['flag']     = $this->flagError;
                $dataArr[0]['message']  = "Request has already been processed.";
                echo json_encode($dataArr);
                exit;
            }
            // if group owner accepts the request
            if($operation == 'accept') {
                // save user group
                $user_data['user_group_user_id']    = $user_id;
                $user_data['user_group_group_id']   = $group_id;
                $user_data['user_group_status']     = 'available';
                $user_data['user_group_is_owner']   = '0';
                $userGroup                          = new UserGroup();
                $userGroup->exchangeArray($user_data);
                $user_group_id                      = $this->getUserGroupTable()->saveUserGroup($userGroup);

                // if user joined the group successfully
                if($user_group_id > 0) {
                    // update request status
                    $this->getUserGroupJoiningRequestTable()->ChangeStatusTOProcessed($group_id, $user_id);
                    $config = $this->getServiceLocator()->get('Config');
                    $base_url = $config['pathInfo']['base_url'];
                    $msg = $user_details->user_given_name." Accept the group joining request to the group ".$group['group_title'];
                    $subject = 'Group joining Request';
                    $from = 'admin@jeera.com';
                    $process = 'Accepted';
                    $this->UpdateNotifications($user_id,$msg,5,$subject,$from,$user_details->user_id,$group_id,$process);
                    $dataArr[0]['flag']             = $this->flagSuccess;
                    $dataArr[0]['message']          = "Group Join Request Accepted.";
                }
            } else if($operation == 'reject') {     // if group owner rejects the request
                // update request status
                $this->getUserGroupJoiningRequestTable()->ChangeStatusTOProcessed($group_id, $user_id);

                $dataArr[0]['flag']                 = $this->flagSuccess;
                $dataArr[0]['message']              = "Group Join Request Rejected.";
            }else{
                $dataArr[0]['flag']                 = $this->flagSuccess;
                $dataArr[0]['message']              = "Unknown operations";
            }
        } else {        // if request is not of type POST
            $dataArr[0]['flag']                     = $this->flagError;
            $dataArr[0]['message']                  = "Request Not Authorised.";                        
        }
        echo json_encode($dataArr);
        exit;
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
	public function get_youtube_id_from_url($url){
		if (stristr($url,'youtu.be/'))
			{preg_match('/(https:|http:|)(\/\/www\.|\/\/|)(.*?)\/(.{11})/i', $url, $final_ID); return $final_ID[4]; }
		else 
			{@preg_match('/(https:|http:|):(\/\/www\.|\/\/|)(.*?)\/(embed\/|watch.*?v=|)([a-z_A-Z0-9\-]{11})/i', $url, $IDD); return $IDD[5]; }
	}
	public function getUserTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTable = (!$this->userTable)?$sm->get('User\Model\UserTable'):$this->userTable;    
	}
	public function getGroupsTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;    
	}
	public function getUserGroupTable(){
		$sm = $this->getServiceLocator();
		return  $this->userGroupTable = (!$this->userGroupTable)?$sm->get('Groups\Model\UserGroupTable'):$this->userGroupTable;    
	}
	public function getGroupTagTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupTagTable = (!$this->groupTagTable)?$sm->get('Tag\Model\GroupTagTable'):$this->groupTagTable;    
    }
	public function getRecoveremailsTable(){
		$sm = $this->getServiceLocator();
		return $this->RecoveryemailsTable =(!$this->RecoveryemailsTable)?$sm->get('User\Model\RecoveryemailsTable'):$this->RecoveryemailsTable;
	}
	public function getActivityTable(){
		$sm = $this->getServiceLocator();
		return  $this->activityTable = (!$this->activityTable)?$sm->get('Activity\Model\ActivityTable'):$this->activityTable;    
    }
	public function getDiscussionTable(){
		$sm = $this->getServiceLocator();
		return  $this->discussionTable = (!$this->discussionTable)?$sm->get('Discussion\Model\DiscussionTable'):$this->discussionTable;    
    }
	public function getGroupMediaTable(){
		$sm = $this->getServiceLocator();
		return  $this->groupMediaTable = (!$this->groupMediaTable)?$sm->get('Groups\Model\GroupMediaTable'):$this->groupMediaTable;    
    }
	public function getLikeTable(){
		$sm = $this->getServiceLocator();
		return  $this->likeTable = (!$this->likeTable)?$sm->get('Like\Model\LikeTable'):$this->likeTable; 
	}
	public function getCommentTable(){
		$sm = $this->getServiceLocator();
		return  $this->commentTable = (!$this->commentTable)?$sm->get('Comment\Model\CommentTable'):$this->commentTable;   
	}
	public function getActivityRsvpTable(){
		$sm = $this->getServiceLocator();
		return  $this->activityRsvpTable = (!$this->activityRsvpTable)?$sm->get('Activity\Model\ActivityRsvpTable'):$this->activityRsvpTable;
    }
	public function getUserTagTable(){
		$sm = $this->getServiceLocator();
		return  $this->userTagTable = (!$this->userTagTable)?$sm->get('Tag\Model\UserTagTable'):$this->userTagTable;    
	}
    public function getUserFriendTable(){
	$sm = $this->getServiceLocator();
	return  $this->userFriendTable = (!$this->userFriendTable)?$sm->get('User\Model\UserFriendTable'):$this->userFriendTable;    
    }	 
    public function getGroupTable(){
	$sm = $this->getServiceLocator();
	return  $this->groupTable = (!$this->groupTable)?$sm->get('Groups\Model\GroupsTable'):$this->groupTable;
    } 
    public function getGroupJoiningQuestionnaireTable(){
	$sm = $this->getServiceLocator();
	return  $this->groupJoiningQuestionnaire = (!$this->groupJoiningQuestionnaire)?$sm->get('Groups\Model\GroupJoiningQuestionnaireTable'):$this->groupJoiningQuestionnaire;
    } 
    public function getGroupQuestionnaireOptionsTable(){
	$sm = $this->getServiceLocator();
	return  $this->groupQuestionnaireOptions = (!$this->groupQuestionnaireOptions)?$sm->get('Groups\Model\GroupQuestionnaireOptionsTable'):$this->groupQuestionnaireOptions;
    } 
    public function getGroupJoiningInvitationTable(){
	$sm = $this->getServiceLocator();
	return  $this->groupJoiningInvitationTable = (!$this->groupJoiningInvitationTable)?$sm->get('Groups\Model\UserGroupJoiningInvitationTable'):$this->groupJoiningInvitationTable;
    } 	
    public function getUserGroupJoiningRequestTable(){
	$sm = $this->getServiceLocator();
        return  $this->userGroupJoiningRequestTable = (!$this->userGroupJoiningRequestTable)?$sm->get('Groups\Model\UserGroupJoiningRequestTable'):$this->userGroupJoiningRequestTable;
    } 
    public function getGroupQuestionnaireAnswersTable(){
	$sm = $this->getServiceLocator();
        return  $this->groupQuestionnaireAnswersTable = (!$this->groupQuestionnaireAnswersTable)?$sm->get('Groups\Model\GroupQuestionnaireAnswersTable'):$this->groupQuestionnaireAnswersTable;
    }
    public function getUserNotificationTable(){         
	$sm = $this->getServiceLocator();
	return  $this->userNotificationTable = (!$this->userNotificationTable)?$sm->get('Notification\Model\UserNotificationTable'):$this->userNotificationTable;    
    }	
}
