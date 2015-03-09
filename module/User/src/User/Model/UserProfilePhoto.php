<?php
namespace User\Model;
use Zend\InputFilter\InputFilter;
 
class UserProfilePhoto
{
    public $profile_photo_id;
    public $profile_user_id;
    public $profile_photo;
	public $profile_photo_added_date;
	public $profile_photo_added_ip;
 

    protected $inputFilter;

    /**
     * Used by ResultSet to pass each database row to the entity
     */ 
    public function exchangeArray($data)
    {
        $this->profile_photo_id     = (isset($data['profile_photo_id'])) ? $data['profile_photo_id'] : null;
        $this->profile_user_id = (isset($data['profile_user_id'])) ? $data['profile_user_id'] : null;
        $this->profile_photo  = (isset($data['profile_photo'])) ? $data['profile_photo'] : null;
		$this->profile_photo_added_date  = (isset($data['profile_photo_added_date'])) ? $data['profile_photo_added_date'] : null;
		$this->profile_photo_added_ip  = (isset($data['profile_photo_added_ip'])) ? $data['profile_photo_added_ip'] : null;
		 
		
    }

    // Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	  
}