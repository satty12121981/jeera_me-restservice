<?php
namespace Activity\Model;
class ActivityInvite
{  
    public $group_activity_invite_id;
    public $group_activity_invite_sender_user_id;
    public $group_activity_invite_receiver_user_id;
	public $group_activity_invite_status;
	public $group_activity_invite_added_date;
	public $group_activity_invite_added_ip_address;
	public $group_activity_invite_activity_id; 
	/**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data)
    {
        $this->group_activity_invite_id     = (isset($data['group_activity_invite_id'])) ? $data['group_activity_invite_id'] : null;
        $this->group_activity_invite_sender_user_id = (isset($data['group_activity_invite_sender_user_id'])) ? $data['group_activity_invite_sender_user_id'] : null;
        $this->group_activity_invite_receiver_user_id  = (isset($data['group_activity_invite_receiver_user_id'])) ? $data['group_activity_invite_receiver_user_id'] : null;
		$this->group_activity_invite_status  = (isset($data['group_activity_invite_status'])) ? $data['group_activity_invite_status'] : null;
		$this->group_activity_invite_added_date  = (isset($data['group_activity_invite_added_date'])) ? $data['group_activity_invite_added_date'] : null;
		$this->group_activity_invite_added_ip_address  = (isset($data['group_activity_invite_added_ip_address'])) ? $data['group_activity_invite_added_ip_address'] : null;
		$this->group_activity_invite_activity_id  = (isset($data['group_activity_invite_activity_id'])) ? $data['group_activity_invite_activity_id'] : null;	 			
    }	
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
}