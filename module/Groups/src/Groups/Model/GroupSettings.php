<?php
namespace Groups\Model;   
use Zend\Crypt\BlockCipher;	#for encryption
class GroupSettings
{  
    public $group_setting_id;
    public $group_setting_group_id;
    public $group_activity_settings;
	public $group_member_join_type;
	public $group_privacy_settings;
	public $group_setting_added_timestamp;
	public $group_setting_added_ip_address;
	public $group_setting_modified_timestamp;
	public $group_setting_modified_ip_address;
	public $group_joining_questionnaire;
	
    public function exchangeArray($data)
    {
        $this->group_setting_id     = (isset($data['group_setting_id'])) ? $data['group_setting_id'] : null;
        $this->group_setting_group_id = (isset($data['group_setting_group_id'])) ? $data['group_setting_group_id'] : null;
        $this->group_activity_settings  = (isset($data['group_activity_settings'])) ? $data['group_activity_settings'] : null;
		$this->group_member_join_type  = (isset($data['group_member_join_type'])) ? $data['group_member_join_type'] : null;		
		$this->group_privacy_settings     = (isset($data['group_privacy_settings'])) ? $data['group_privacy_settings'] : null;
        $this->group_setting_added_timestamp = (isset($data['group_setting_added_timestamp'])) ? $data['group_setting_added_timestamp'] : null;
        $this->group_setting_added_ip_address  = (isset($data['group_setting_added_ip_address'])) ? $data['group_setting_added_ip_address'] : null;
		$this->group_setting_modified_timestamp = (isset($data['group_setting_modified_timestamp'])) ? $data['group_setting_modified_timestamp'] : null;
        $this->group_setting_modified_ip_address  = (isset($data['group_setting_modified_ip_address'])) ? $data['group_setting_modified_ip_address'] : null;
		$this->group_joining_questionnaire =  (isset($data['group_joining_questionnaire'])) ? $data['group_joining_questionnaire'] : null;
    }
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
	
 
	
}