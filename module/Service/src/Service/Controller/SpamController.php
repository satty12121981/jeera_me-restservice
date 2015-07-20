<?php
namespace Service\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\JsonModel;
use Zend\Authentication\AuthenticationService;
use Zend\Authentication\Adapter\DbTable as AuthAdapter;  
use Spam\Model\Spamreports;  
class SpamController extends AbstractActionController
{
	protected $spamreasonsTable;
	protected $spamreportTable;
	public function getreasonsAction(){
		$error = '';
		$auth = new AuthenticationService();
		$reasons = array(); 		 
		$request   = $this->getRequest();
		if ($request->isPost()){
			$post = $request->getPost();
			$accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
			$error = (empty($accToken)) ? "Request Not Authorised." : $error;
			$this->checkError($error);
			$userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
			$Type = (isset($post['type']) && $post['type'] != null && $post['type'] != '' && $post['type'] != 'undefined') ? strip_tags(trim($post['type'])) : '';
			$error = (empty($Type)) ? "Invalid Type." : $error;
			$this->checkError($error);
			$contentId = (isset($post['content_id']) && $post['content_id'] != null && $post['content_id'] != '' && $post['content_id'] != 'undefined') ? strip_tags(trim($post['content_id'])) : '';
			$error = (empty($contentId)) ? "Invalid Content id." : $error;
			$this->checkError($error);
			$request_details = $this->getSpamreportsTable()->checkAlreadyRequested($Type,$contentId,$userinfo->user_id);
			if(empty($request_details)){
				$reasons = $this->getSpamreasonsTable()->getReasonsByType($Type);
			}else{ $error = "Already reported";  }
		}else{$error = "Unable to process";}

		$dataArr[0]['flag'] = (empty($error))?'Success':'Failure';
		$dataArr[0]['message'] = $error;
		echo json_encode($dataArr);
		exit;
	}
	public function sentreportAction(){
		$error = '';
		$reasons = array();
		$dataArr= array();
		$request   = $this->getRequest();
		if ($request->isPost()){
			$post = $request->getPost();
			$accToken = (isset($post['accesstoken']) && $post['accesstoken'] != null && $post['accesstoken'] != '' && $post['accesstoken'] != 'undefined') ? strip_tags(trim($post['accesstoken'])) : '';
			$error = (empty($accToken)) ? "Request Not Authorised." : $error;
			$this->checkError($error);
			$userinfo = $this->getUserTable()->getUserByAccessToken($accToken);
			$Type = (isset($post['type']) && $post['type'] != null && $post['type'] != '' && $post['type'] != 'undefined') ? strip_tags(trim($post['type'])) : '';
			$error = (empty($Type)) ? "Invalid Type." : $error;
			$this->checkError($error);
			$type = $post['type'];
			$contentId = (isset($post['content_id']) && $post['content_id'] != null && $post['content_id'] != '' && $post['content_id'] != 'undefined') ? strip_tags(trim($post['content_id'])) : '';
			$error = (empty($contentId)) ? "Invalid Content id." : $error;
			$this->checkError($error);
			$contentId = $post['content_id'];
			$reasonId = (isset($post['reason_id']) && $post['reason_id'] != null && $post['reason_id'] != '' && $post['reason_id'] != 'undefined') ? strip_tags(trim($post['reason_id'])) : '';
			$error = (empty($reasonId)) ? "Invalid Reason id." : $error;
			$this->checkError($error);
			$otherReason = $post['otherreason'];
			$request_details = $this->getSpamreportsTable()->checkAlreadyRequested($type,$contentId,$userinfo->user_id);
			if(empty($request_details)){
				 $reasons = $this->getSpamreasonsTable()->getReasonDetails($reasonId);
				 if(!empty($reasons)){
					 if($contentId!=''&&$reasonId!=''&&$type!=''){
						if($reasons['reason']=='Other'&&$otherReason=='') {
							$error = "Please enter your reason";
						}else{
							$spamreport['content_id'] = $contentId;
							$spamreport['content_type'] = $Type;
							$spamreport['reason_id'] = $reasonId;
							$spamreport['report_comment'] = $otherReason;
							$spamreport['reporter_id'] = $userinfo->user_id;
							$Spamreports = new Spamreports();
							$Spamreports->exchangeArray($spamreport);
							$this->getSpamreportsTable()->saveSpamReports($Spamreports);
						}
					 }else{$error = "Fields are not complete";}
				 }else{$error = "Given reason is not valid";}
			}else{ $error = "Already reported";  }
		}else{$error = "Unable to process";}

		$dataArr[0]['flag'] = (empty($error))?'Success':'Failure';
		$dataArr[0]['message'] = $error;
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
	public function getSpamreasonsTable(){
		$sm = $this->getServiceLocator();
		return  $this->spamreasonsTable = (!$this->spamreasonsTable)?$sm->get('Spam\Model\SpamreasonsTable'):$this->spamreasonsTable;
    }
	public function getSpamreportsTable(){
		$sm = $this->getServiceLocator();
		return  $this->spamreportTable = (!$this->spamreportTable)?$sm->get('Spam\Model\SpamreportsTable'):$this->spamreportTable;
    }
}