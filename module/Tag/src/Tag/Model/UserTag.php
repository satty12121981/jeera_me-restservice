<?php
namespace Tag\Model;

class UserTag
{  
	public $user_tag_id;
    public $user_tag_user_id;
    public $user_tag_tag_id;
	public $user_tag_added_timestamp;
	public $user_tag_added_ip_address;
	 
	  
	
	/**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data)
    {
        $this->user_tag_id     = (isset($data['user_tag_id'])) ? $data['user_tag_id'] : null;
        $this->user_tag_user_id = (isset($data['user_tag_user_id'])) ? $data['user_tag_user_id'] : null;
        $this->user_tag_tag_id  = (isset($data['user_tag_tag_id'])) ? $data['user_tag_tag_id'] : null;
		$this->user_tag_added_timestamp  = (isset($data['user_tag_added_timestamp'])) ? $data['user_tag_added_timestamp'] : null;
		$this->user_tag_added_ip_address  = (isset($data['user_tag_added_ip_address'])) ? $data['user_tag_added_ip_address'] : null;
		 
    }
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	
}