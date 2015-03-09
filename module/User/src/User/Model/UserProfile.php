<?php 

namespace User\Model;

use Zend\InputFilter\InputFilter;
 

class UserProfile  
{
    public $user_profile_id;
    public $user_profile_dob;
    public $user_profile_about_me;
	public $user_profile_profession;
	public $user_profile_profession_at;
	public $user_profile_user_id;
	public $user_profile_city_id;
	public $user_profile_emailme_id;
	public $user_profile_notifyme_id;
	public $user_profile_country_id;
	public $user_address;
	public $user_profile_current_location;
	public $user_profile_phone;
	public $user_profile_status;
	public $user_profile_added_timestamp;
	public $user_profile_added_ip_address;
	public $user_profile_modified_timestamp;
	public $user_profile_modified_ip_address;
	 

    protected $inputFilter;

    /**
     * Used by ResultSet to pass each database row to the entity
     */ 
    public function exchangeArray($data)
    {
        $this->user_profile_id     = (isset($data['user_profile_id'])) ? $data['user_profile_id'] : null;
        $this->user_profile_dob = (isset($data['user_profile_dob'])) ? $data['user_profile_dob'] : null;
        $this->user_profile_about_me  = (isset($data['user_profile_about_me'])) ? $data['user_profile_about_me'] : null;
		$this->user_profile_profession  = (isset($data['user_profile_profession'])) ? $data['user_profile_profession'] : null;
		$this->user_profile_profession_at  = (isset($data['user_profile_profession_at'])) ? $data['user_profile_profession_at'] : null;
		$this->user_profile_user_id  = (isset($data['user_profile_user_id'])) ? $data['user_profile_user_id'] : null;
		$this->user_profile_city_id  = (isset($data['user_profile_city_id'])) ? $data['user_profile_city_id'] : null;		 
		$this->user_profile_country_id  = (isset($data['user_profile_country_id'])) ? $data['user_profile_country_id'] : null;
		$this->user_address  = (isset($data['user_address'])) ? $data['user_address'] : null;
		$this->user_profile_emailme_id  = (isset($data['user_profile_emailme_id'])) ? $data['user_profile_emailme_id'] : null;
		$this->user_profile_notifyme_id  = (isset($data['user_profile_notifyme_id'])) ? $data['user_profile_notifyme_id'] : null;
		$this->user_profile_current_location  = (isset($data['user_profile_current_location'])) ? $data['user_profile_current_location'] : null;
		$this->user_profile_phone  = (isset($data['user_profile_phone'])) ? $data['user_profile_phone'] : null;
		$this->user_profile_status  = (isset($data['user_profile_status'])) ? $data['user_profile_status'] : null;
		$this->user_profile_added_timestamp  = (isset($data['user_profile_added_timestamp'])) ? $data['user_profile_added_timestamp'] : null;
		$this->user_profile_added_ip_address  = (isset($data['user_profile_added_ip_address'])) ? $data['user_profile_added_ip_address'] : null;
		$this->user_profile_modified_timestamp  = (isset($data['user_profile_modified_timestamp'])) ? $data['user_profile_modified_timestamp'] : null;
		$this->user_profile_modified_ip_address  = (isset($data['user_profile_modified_ip_address'])) ? $data['user_profile_modified_ip_address'] : null;
    }

    // Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
	  
}
