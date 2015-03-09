<?php
####################Activity Model #################################
//Created by Shail
#########################################################################
namespace Activity\Model;
class Activity
{  
    public $group_activity_id;
    public $group_activity_content;
    public $group_activity_owner_user_id;
	public $group_activity_group_id;
	public $group_activity_status;
	public $group_activity_type;
	public $group_activity_added_timestamp;
	public $group_activity_added_ip_address;
	public $group_activity_start_timestamp;
	public $group_activity_title;
	public $group_activity_location;
	public $group_activity_location_lat;
	public $group_activity_location_lng;
	public $group_activity_modifed_timestamp;
	public $group_activity_modified_ip_address;	 
	
	#activity ower property
	public $user_id;
	public $user_given_name;	
	public $user_first_name;
	public $user_middle_name;
	public $user_last_name;
	public $user_email; 
	
	#owner_photo
	public $photo_name;
	
	#planet details
	public $group_title;
	public $group_seo_title;
	
	#Galaxy details
	public $parent_group_title;
	public $parent_group_seo_title;
	public $parent_group_id;
	/**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data)
    {
        $this->group_activity_id     = (isset($data['group_activity_id'])) ? $data['group_activity_id'] : null;
        $this->group_activity_content = (isset($data['group_activity_content'])) ? $data['group_activity_content'] : null;
        $this->group_activity_owner_user_id  = (isset($data['group_activity_owner_user_id'])) ? $data['group_activity_owner_user_id'] : null;
		$this->group_activity_group_id  = (isset($data['group_activity_group_id'])) ? $data['group_activity_group_id'] : null;
		$this->group_activity_status  = (isset($data['group_activity_status'])) ? $data['group_activity_status'] : null;
		$this->group_activity_type  = (isset($data['group_activity_type'])) ? $data['group_activity_type'] : null;
		$this->group_activity_added_timestamp  = (isset($data['group_activity_added_timestamp'])) ? $data['group_activity_added_timestamp'] : null;
		$this->group_activity_added_ip_address  = (isset($data['group_activity_added_ip_address'])) ? $data['group_activity_added_ip_address'] : null;	
		$this->group_activity_start_timestamp  = (isset($data['group_activity_start_timestamp'])) ? $data['group_activity_start_timestamp'] : null;	
		$this->group_activity_title  = (isset($data['group_activity_title'])) ? $data['group_activity_title'] : null;	
		$this->group_activity_location  = (isset($data['group_activity_location'])) ? $data['group_activity_location'] : null;	
		$this->group_activity_location_lat  = (isset($data['group_activity_location_lat'])) ? $data['group_activity_location_lat'] : null;	
		$this->group_activity_location_lng  = (isset($data['group_activity_location_lng'])) ? $data['group_activity_location_lng'] : null;	
		$this->group_activity_modifed_timestamp  = (isset($data['group_activity_modifed_timestamp'])) ? $data['group_activity_modifed_timestamp'] : null;	
		$this->group_activity_modified_ip_address  = (isset($data['group_activity_modified_ip_address'])) ? $data['group_activity_modified_ip_address'] : null;	
		 		
    }	
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}