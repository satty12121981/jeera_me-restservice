<?php
namespace User\Model;
use Zend\InputFilter\InputFilter;
 
class UserProfileSettings  
{
    public $id;
    public $user_id;
    public $firstname_field;
	public $lastname_field;
	public $middlename_filed;
	public $displayname_field;
	public $email_field;
	public $phone_field;
	public $location_field;
	public $dob_field;
	public $university_field;
	public $profession_field;
	public $gender_field;
	public $country_field;
	public $city_field;


    protected $inputFilter;

    /**
     * Used by ResultSet to pass each database row to the entity
     */ 
    public function exchangeArray($data)
    {
        $this->id     = (isset($data['id'])) ? $data['id'] : null;
        $this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->firstname_field  = (isset($data['firstname_field'])) ? $data['firstname_field'] : null;
		$this->lastname_field  = (isset($data['lastname_field'])) ? $data['lastname_field'] : null;
		$this->middlename_filed  = (isset($data['middlename_filed'])) ? $data['middlename_filed'] : null;
		$this->displayname_field  = (isset($data['displayname_field'])) ? $data['displayname_field'] : null;
		$this->email_field  = (isset($data['email_field'])) ? $data['email_field'] : null;
		$this->phone_field  = (isset($data['phone_field'])) ? $data['phone_field'] : null;
		$this->location_field  = (isset($data['location_field'])) ? $data['location_field'] : null;
		$this->dob_field  = (isset($data['dob_field'])) ? $data['dob_field'] : null;
		$this->university_field  = (isset($data['university_field'])) ? $data['university_field'] : null;
		$this->profession_field  = (isset($data['profession_field'])) ? $data['profession_field'] : null;
		$this->gender_field  = (isset($data['gender_field'])) ? $data['gender_field'] : null;
		$this->country_field  = (isset($data['country_field'])) ? $data['country_field'] : null;
		$this->city_field  = (isset($data['city_field'])) ? $data['city_field'] : null;
		
    }

    // Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	  
}