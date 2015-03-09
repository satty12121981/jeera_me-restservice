<?php
namespace Groups\Model;   
use Zend\Crypt\BlockCipher;	#for encryption
class UserGroupPermissions
{  
    public $permission_id;
    public $group_id;
    public $role_id;
	public $function_id;
	 

    public function exchangeArray($data)
    {
        $this->permission_id     = (isset($data['permission_id'])) ? $data['permission_id'] : null;
        $this->group_id = (isset($data['group_id'])) ? $data['group_id'] : null;
        $this->role_id  = (isset($data['role_id'])) ? $data['role_id'] : null;
		$this->function_id  = (isset($data['function_id'])) ? $data['function_id'] : null;		 
    }
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
	
 
	
}