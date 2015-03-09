<?php
namespace Groups\Model;   
use Zend\Crypt\BlockCipher;	#for encryption
class UserGroup
{  
    public $user_group_id;
    public $user_group_user_id;
    public $user_group_group_id;
	public $user_group_added_timestamp;
	public $user_group_added_ip_address;
	public $user_group_status;
	public $user_group_is_owner;
	public $user_group_role;

    public function exchangeArray($data)
    {
        $this->user_group_id     = (isset($data['user_group_id'])) ? $data['user_group_id'] : null;
        $this->user_group_user_id = (isset($data['user_group_user_id'])) ? $data['user_group_user_id'] : null;
        $this->user_group_group_id  = (isset($data['user_group_group_id'])) ? $data['user_group_group_id'] : null;
		$this->user_group_added_timestamp  = (isset($data['user_group_added_timestamp'])) ? $data['user_group_added_timestamp'] : null;
		$this->user_group_added_ip_address  = (isset($data['user_group_added_ip_address'])) ? $data['user_group_added_ip_address'] : null;
		$this->user_group_status  = (isset($data['user_group_status'])) ? $data['user_group_status'] : null;		
		$this->user_group_is_owner  = (isset($data['user_group_is_owner'])) ? $data['user_group_is_owner'] : null;
		$this->user_group_role  = (isset($data['user_group_role'])) ? $data['user_group_role'] : null;				
    }
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
	
	public function selectFormatAllUserListGroupEncrypted($udata, array $excludeDetailArray){	 
		$blockCipher = BlockCipher::factory('mcrypt', array('algo' => 'aes'));
		$blockCipher->setKey('JHHU98789*&^&^%^$^^&g53$@8');  
		$selectObject =array(); 
		foreach($udata as $user){
			if(isset($excludeDetailArray['user_id']) && !empty($excludeDetailArray['user_id']) && trim($excludeDetailArray['user_id'])!=$user->user_id){	
				$selectObject[$blockCipher->encrypt($user->user_id)] = $user->user_first_name." ".$user->user_last_name;	
			}		
		} //foreach($data as $user)	
		return $selectObject;	//return blank array
	} 
	
}