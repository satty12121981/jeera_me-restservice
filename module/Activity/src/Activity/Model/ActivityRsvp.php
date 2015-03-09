<?php 

namespace Activity\Model;
class ActivityRsvp
{  
    public $group_activity_rsvp_id;
    public $group_activity_rsvp_user_id;
    public $group_activity_rsvp_activity_id;
	public $group_activity_rsvp_added_timestamp;
	public $group_activity_rsvp_added_ip_address;
	public $group_activity_rsvp_group_id;
	
	#activity details
	public $group_activity_id;
    public $group_activity_content;
    public $group_activity_owner_user_id;
	public $group_activity_group_id;
	public $group_activity_status;
	public $group_activity_added_timestamp;
	public $group_activity_added_ip_address;
	public $group_activity_start_timestamp;
	public $group_activity_title;
	public $group_activity_location;
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
        $this->group_activity_rsvp_id     = (isset($data['group_activity_rsvp_id'])) ? $data['group_activity_rsvp_id'] : null;
        $this->group_activity_rsvp_user_id = (isset($data['group_activity_rsvp_user_id'])) ? $data['group_activity_rsvp_user_id'] : null;
        $this->group_activity_rsvp_activity_id  = (isset($data['group_activity_rsvp_activity_id'])) ? $data['group_activity_rsvp_activity_id'] : null;
		$this->group_activity_rsvp_added_timestamp  = (isset($data['group_activity_rsvp_added_timestamp'])) ? $data['group_activity_rsvp_added_timestamp'] : null;
		$this->group_activity_rsvp_added_ip_address  = (isset($data['group_activity_rsvp_added_ip_address'])) ? $data['group_activity_rsvp_added_ip_address'] : null;
		$this->group_activity_rsvp_group_id  = (isset($data['group_activity_rsvp_group_id'])) ? $data['group_activity_rsvp_group_id'] : null;		
		
		#activity
		$this->group_activity_id     = (isset($data['group_activity_id'])) ? $data['group_activity_id'] : null;
        $this->group_activity_content = (isset($data['group_activity_content'])) ? $data['group_activity_content'] : null;
        $this->group_activity_owner_user_id  = (isset($data['group_activity_owner_user_id'])) ? $data['group_activity_owner_user_id'] : null;
		$this->group_activity_group_id  = (isset($data['group_activity_group_id'])) ? $data['group_activity_group_id'] : null;
		$this->group_activity_status  = (isset($data['group_activity_status'])) ? $data['group_activity_status'] : null;
		$this->group_activity_added_timestamp  = (isset($data['group_activity_added_timestamp'])) ? $data['group_activity_added_timestamp'] : null;
		$this->group_activity_added_ip_address  = (isset($data['group_activity_added_ip_address'])) ? $data['group_activity_added_ip_address'] : null;	
		$this->group_activity_start_timestamp  = (isset($data['group_activity_start_timestamp'])) ? $data['group_activity_start_timestamp'] : null;	
		$this->group_activity_title  = (isset($data['group_activity_title'])) ? $data['group_activity_title'] : null;	
		$this->group_activity_location  = (isset($data['group_activity_location'])) ? $data['group_activity_location'] : null;	
		$this->group_activity_modifed_timestamp  = (isset($data['group_activity_modifed_timestamp'])) ? $data['group_activity_modifed_timestamp'] : null;	
		$this->group_activity_modified_ip_address  = (isset($data['group_activity_modified_ip_address'])) ? $data['group_activity_modified_ip_address'] : null;	
		$this->user_id  = (isset($data['user_id'])) ? $data['user_id'] : null;	
		$this->user_given_name  = (isset($data['user_given_name'])) ? $data['user_given_name'] : null;	
		$this->user_first_name  = (isset($data['user_first_name'])) ? $data['user_first_name'] : null;	
		$this->user_middle_name  = (isset($data['user_middle_name'])) ? $data['user_middle_name'] : null;	
		$this->user_last_name  = (isset($data['user_last_name'])) ? $data['user_last_name'] : null;	
		$this->group_title  = (isset($data['group_title'])) ? $data['group_title'] : null;	
		$this->group_seo_title  = (isset($data['group_seo_title'])) ? $data['group_seo_title'] : null;	
		$this->parent_group_id  = (isset($data['parent_group_id'])) ? $data['parent_group_id'] : null;	
		$this->parent_group_title  = (isset($data['parent_group_title'])) ? $data['parent_group_title'] : null;	
		$this->parent_group_seo_title  = (isset($data['parent_group_seo_title'])) ? $data['parent_group_seo_title'] : null;			 	
    }
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	
}