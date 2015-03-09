<?php
namespace Groups\Model;
use Zend\Crypt\BlockCipher;	#for encryption
class UserGroupJoiningInvitation
{
    public $user_group_joining_invitation_id;
    public $user_group_joining_invitation_sender_user_id;
    public $user_group_joining_invitation_receiver_id;
    public $user_group_joining_invitation_status;
	public $user_group_joining_invitation_ip_address;
	public $user_group_joining_invitation_group_id;

    public function exchangeArray($data)
    {
        $this->user_group_joining_invitation_id     = (isset($data['user_group_joining_invitation_id'])) ? $data['user_group_joining_invitation_id'] : null;
        $this->user_group_joining_invitation_sender_user_id     = (isset($data['user_group_joining_invitation_sender_user_id'])) ? $data['user_group_joining_invitation_sender_user_id'] : null;
        $this->user_group_joining_invitation_receiver_id = (isset($data['user_group_joining_invitation_receiver_id'])) ? $data['user_group_joining_invitation_receiver_id'] : null;
        $this->user_group_joining_invitation_status  = (isset($data['user_group_joining_invitation_status'])) ? $data['user_group_joining_invitation_status'] : null;
		$this->user_group_joining_invitation_ip_address  = (isset($data['user_group_joining_invitation_ip_address'])) ? $data['user_group_joining_invitation_ip_address'] : null;
		$this->user_group_joining_invitation_group_id  = (isset($data['user_group_joining_invitation_group_id'])) ? $data['user_group_joining_invitation_group_id'] : null;
    }


}