<?php
namespace Groups\Model;   
use Zend\Crypt\BlockCipher;	#for encryption
class UserGroupJoiningRequest
{  
    public $user_group_joining_request_id;
    public $user_group_joining_request_user_id;
    public $user_group_joining_request_group_id;
	public $user_group_joining_request_added_timestamp;
	public $user_group_joining_request_added_ip_address;
	public $user_group_joining_request_status;
	 

    public function exchangeArray($data)
    {
        $this->user_group_joining_request_id     = (isset($data['user_group_joining_request_id'])) ? $data['user_group_joining_request_id'] : null;
        $this->user_group_joining_request_user_id = (isset($data['user_group_joining_request_user_id'])) ? $data['user_group_joining_request_user_id'] : null;
        $this->user_group_joining_request_group_id  = (isset($data['user_group_joining_request_group_id'])) ? $data['user_group_joining_request_group_id'] : null;
		$this->user_group_joining_request_added_timestamp  = (isset($data['user_group_joining_request_added_timestamp'])) ? $data['user_group_joining_request_added_timestamp'] : null;
		$this->user_group_added_ip_address  = (isset($data['user_group_added_ip_address'])) ? $data['user_group_added_ip_address'] : null;
		$this->user_group_joining_request_added_ip_address  = (isset($data['user_group_joining_request_added_ip_address'])) ? $data['user_group_joining_request_added_ip_address'] : null;		
		$this->user_group_joining_request_status  = (isset($data['user_group_joining_request_status'])) ? $data['user_group_joining_request_status'] : null;		
    }
 
	
}