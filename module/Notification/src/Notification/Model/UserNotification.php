<?php
namespace Notification\Model;

class UserNotification
{  
    public $user_notification_id;
    public $user_notification_user_id;
    public $user_notification_content;
	public $user_notification_added_timestamp;
	public $user_notification_status;
	public $user_notification_notification_type_id;
	public $user_notification_sender_id;
	public $user_notification_reference_id;

    public function exchangeArray($data)
    {
        $this->user_notification_id     = (isset($data['user_notification_id'])) ? $data['user_notification_id'] : null;
        $this->user_notification_user_id = (isset($data['user_notification_user_id'])) ? $data['user_notification_user_id'] : null;
        $this->user_notification_content  = (isset($data['user_notification_content'])) ? $data['user_notification_content'] : null;
		$this->user_notification_added_timestamp  = (isset($data['user_notification_added_timestamp'])) ? $data['user_notification_added_timestamp'] : null;
		$this->user_notification_status  = (isset($data['user_notification_status'])) ? $data['user_notification_status'] : null;
		$this->user_notification_notification_type_id  = (isset($data['user_notification_notification_type_id'])) ? $data['user_notification_notification_type_id'] : null;
		$this->user_notification_sender_id  = (isset($data['user_notification_sender_id'])) ? $data['user_notification_sender_id'] : null;
		$this->user_notification_reference_id  = (isset($data['user_notification_reference_id'])) ? $data['user_notification_reference_id'] : null;
    }
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
		
}