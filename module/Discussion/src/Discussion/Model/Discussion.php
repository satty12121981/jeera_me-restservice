<?php
####################Disccussion Model ###################################

namespace Discussion\Model;
class Discussion
{  
    public $group_discussion_id;
    public $group_discussion_content;
    public $group_discussion_owner_user_id;
	public $group_discussion_group_id;
	public $group_discussion_status;
	public $group_discussion_added_timestamp;
	public $group_discussion_added_ip_address;
	public $group_discussion_modifed_timestamp;
	public $group_discussion_modified_ip_address;	 
	
	/**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data)
    {
        $this->group_discussion_id     = (isset($data['group_discussion_id'])) ? $data['group_discussion_id'] : null;
        $this->group_discussion_content = (isset($data['group_discussion_content'])) ? $data['group_discussion_content'] : null;
        $this->group_discussion_owner_user_id  = (isset($data['group_discussion_owner_user_id'])) ? $data['group_discussion_owner_user_id'] : null;
		$this->group_discussion_group_id  = (isset($data['group_discussion_group_id'])) ? $data['group_discussion_group_id'] : null;
		$this->group_discussion_status  = (isset($data['group_discussion_status'])) ? $data['group_discussion_status'] : null;
		$this->group_discussion_added_timestamp  = (isset($data['group_discussion_added_timestamp'])) ? $data['group_discussion_added_timestamp'] : null;
		$this->group_discussion_added_ip_address  = (isset($data['group_discussion_added_ip_address'])) ? $data['group_discussion_added_ip_address'] : null;	
		$this->group_discussion_modified_timestamp  = (isset($data['group_discussion_modified_timestamp'])) ? $data['group_discussion_modified_timestamp'] : null;	
		$this->group_discussion_modified_ip_address  = (isset($data['group_discussion_modified_ip_address'])) ? $data['group_discussion_modified_ip_address'] : null;			
    }	
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}