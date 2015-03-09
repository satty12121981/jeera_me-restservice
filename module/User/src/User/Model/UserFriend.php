<?php 
namespace User\Model;
use Zend\InputFilter\InputFilter;
class UserFriend  
{
    public $user_friend_id;
    public $user_friend_sender_user_id;
    public $user_friend_friend_user_id;
	public $user_friend_added_ip_address;
	public $user_friend_status;
    protected $inputFilter;
    public function exchangeArray($data) {
        $this->user_friend_id     = (isset($data['user_friend_id'])) ? $data['user_friend_id'] : null;
        $this->user_friend_sender_user_id = (isset($data['user_friend_sender_user_id'])) ? $data['user_friend_sender_user_id'] : null;
        $this->user_friend_friend_user_id  = (isset($data['user_friend_friend_user_id'])) ? $data['user_friend_friend_user_id'] : null;
		$this->user_friend_added_ip_address 	  = (isset($data['user_friend_added_ip_address'])) ? $data['user_friend_added_ip_address'] : null;
		$this->user_friend_status  = (isset($data['user_friend_status'])) ? $data['user_friend_status'] : null;
    }
    public function getArrayCopy() {  return get_object_vars($this); }	  
}
