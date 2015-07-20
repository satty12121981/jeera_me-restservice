<?php
namespace Spam\Controller;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;	
use Zend\View\Model\JsonModel; 
use Zend\Session\Container;   
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
		if ($auth->hasIdentity()) {			 
			$identity = $auth->getIdentity();			 
			if(!empty($identity)&&$identity->user_id){	
				$request   = $this->getRequest();
				if ($request->isPost()){
					$post = $request->getPost(); 
					$type = $post['type'];
					$content_id = $post['content_id'];
					$request_details = $this->getSpamreportsTable()->checkAlreadyRequested($type,$content_id,$identity->user_id);
					if(empty($request_details)){
						$reasons = $this->getSpamreasonsTable()->getReasonsByType($type);
					}else{ $error = "Already reported";  }					
				}else{$error = "Unable to process";}
			}else{	$error = "User not exist in the system"; }
		}else{$error = "Your session expired, please log in again to continue";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;	
		$return_array['reasons'] = $reasons;			 
		$result = new JsonModel(array(
			'return_array' => $return_array,      
		));		
		return $result;	
	}
	public function sentreportAction(){
		$error = '';
		$auth = new AuthenticationService();
		$reasons = array(); 		 
		if ($auth->hasIdentity()) {			 
			$identity = $auth->getIdentity();			 
			if(!empty($identity)&&$identity->user_id){	
				$request   = $this->getRequest();
				if ($request->isPost()){
					$post = $request->getPost(); 
					$type = $post['type'];
					$content_id = $post['content_id'];
					$reason_id = $post['reason_id'];
					$otherReason = $post['otherReason'];
					$request_details = $this->getSpamreportsTable()->checkAlreadyRequested($type,$content_id,$identity->user_id);
					if(empty($request_details)){
						 $reasons = $this->getSpamreasonsTable()->getReasonDetails($reason_id);
						 if(!empty($reasons)){
							 if($content_id!=''&&$reason_id!=''&&$type!=''){
								if($reasons['reason']=='Other'&&$otherReason=='') {
									$error = "Please enter your reason";
								}else{
									$spamreport['content_id'] = $content_id;
									$spamreport['content_type'] = $type;
									$spamreport['reason_id'] = $reason_id;
									$spamreport['report_comment'] = $otherReason;
									$spamreport['reporter_id'] = $identity->user_id;
									$Spamreports = new Spamreports();
									$Spamreports->exchangeArray($spamreport);
									$this->getSpamreportsTable()->saveSpamReports($Spamreports);
								}
							 }else{$error = "Fields are not complete";}
						 }else{$error = "Given reason is not valid";}
					}else{ $error = "Already reported";  }					
				}else{$error = "Unable to process";}
			}else{	$error = "User not exist in the system"; }
		}else{$error = "Your session expired, please log in again to continue";}
		$return_array= array();		 
		$return_array['process_status'] = (empty($error))?'success':'failed';
		$return_array['process_info'] = $error;			 	 
		$result = new JsonModel(array(
			'return_array' => $return_array,      
		));		
		return $result;	
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