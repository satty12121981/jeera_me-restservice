<?php
namespace User\Model;
use Zend\InputFilter\InputFilter;
 
class UserGeneralSettings  
{
    public $id;
    public $user_id;
    public $event_alert;
	public $survey_alert;
	public $new_feature;
	public $friend_request;
	public $message;

    protected $inputFilter;

    /**
     * Used by ResultSet to pass each database row to the entity
     */ 
    public function exchangeArray($data)
    {
        $this->id     = (isset($data['id'])) ? $data['id'] : null;
        $this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->event_alert  = (isset($data['event_alert'])) ? $data['event_alert'] : null;
		$this->survey_alert  = (isset($data['survey_alert'])) ? $data['survey_alert'] : null;
		$this->new_feature  = (isset($data['new_feature'])) ? $data['new_feature'] : null;
		$this->friend_request  = (isset($data['friend_request'])) ? $data['friend_request'] : null;
		$this->message  = (isset($data['message'])) ? $data['message'] : null;
		
    }

    // Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	  
}