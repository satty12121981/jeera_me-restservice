<?php
namespace Groups\Model;
class Groups
{  	 
	public $group_id;
    public $group_title;
    public $group_seo_title;
	public $group_status;
	public $group_description;
	public $group_added_timestamp;
	public $group_added_ip_address;
	public $group_parent_group_id;
	public $group_location;
	public $group_photo_id;
	public $group_modified_timestamp;
	public $group_modified_ip_address;	 
	public $group_city_id;
	public $group_country_id;
	public $group_location_lat;
	public $group_location_lng;
	public $group_web_address;
	public $group_welcome_message_members;
	 
    public function exchangeArray($data)
    {
        $this->group_id     = (isset($data['group_id'])) ? $data['group_id'] : null;
        $this->group_title = (isset($data['group_title'])) ? $data['group_title'] : null;
        $this->group_seo_title  = (isset($data['group_seo_title'])) ? $data['group_seo_title'] : null;
		$this->group_status  = (isset($data['group_status'])) ? $data['group_status'] : null;
		$this->group_description  = (isset($data['group_description'])) ? $data['group_description'] : null;
		$this->group_added_timestamp  = (isset($data['group_added_timestamp'])) ? $data['group_added_timestamp'] : null;
		$this->group_added_ip_address  = (isset($data['group_added_ip_address'])) ? $data['group_added_ip_address'] : null;
		$this->group_parent_group_id  = (isset($data['group_parent_group_id'])) ? $data['group_parent_group_id'] : null;
		$this->group_location  = (isset($data['group_location'])) ? $data['group_location'] : null;
		$this->group_photo_id  = (isset($data['group_photo_id'])) ? $data['group_photo_id'] : null;
		$this->group_modified_timestamp  = (isset($data['group_modified_timestamp'])) ? $data['group_modified_timestamp'] : null;
		$this->group_modified_ip_address  = (isset($data['group_modified_ip_address'])) ? $data['group_modified_ip_address'] : null;
		$this->group_location_lat  = (isset($data['group_location_lat'])) ? $data['group_location_lat'] : null;
		$this->group_location_lng  = (isset($data['group_location_lng'])) ? $data['group_location_lng'] : null;		 	 
		$this->group_city_id  = (isset($data['group_city_id'])) ? $data['group_city_id'] : null;	
		$this->group_country_id  = (isset($data['group_country_id'])) ? $data['group_country_id'] : null;
		$this->group_web_address  = (isset($data['group_web_address'])) ? $data['group_web_address'] : null;		
		$this->group_welcome_message_members  = (isset($data['group_welcome_message_members'])) ? $data['group_welcome_message_members'] : null;		 
    }	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy() {
        return get_object_vars($this);
    } 
}