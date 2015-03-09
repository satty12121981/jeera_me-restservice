<?php
namespace Tag\Model;

class GroupTag
{  
	public $group_tag_id;
    public $group_tag_group_id;
    public $group_tag_added_timestamp;
	public $group_tag_added_ip_address;
	public $group_tag_tag_id; 
	
	/**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data)
    {
        $this->group_tag_id     = (isset($data['group_tag_id'])) ? $data['group_tag_id'] : null;
        $this->group_tag_group_id = (isset($data['group_tag_group_id'])) ? $data['group_tag_group_id'] : null;
        $this->group_tag_added_timestamp  = (isset($data['group_tag_added_timestamp'])) ? $data['group_tag_added_timestamp'] : null;
		$this->group_tag_added_ip_address  = (isset($data['group_tag_added_ip_address'])) ? $data['group_tag_added_ip_address'] : null;
		$this->group_tag_tag_id  = (isset($data['group_tag_tag_id'])) ? $data['group_tag_tag_id'] : null;
		 
    }
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	  
	
}