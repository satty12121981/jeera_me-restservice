<?php 

namespace User\Model;

use Zend\InputFilter\InputFilter;

class UserBlock
{
    public $user_block_id;
    public $user_block_user_id;
    public $user_block_problem_id;
    public $user_block_reason;
	public $user_block_other_reason;
    public $user_block_added_ip_address;
	public $user_block_added_timestamp;
	public $user_block_status;
    public function exchangeArray($data)
    {
        $this->user_block_id     = (isset($data['user_block_id'])) ? $data['user_block_id'] : null;
        $this->user_block_user_id     = (isset($data['user_block_user_id'])) ? $data['user_block_user_id'] : null;
        $this->user_block_problem_id = (isset($data['user_block_problem_id'])) ? $data['user_block_problem_id'] : null;
        $this->user_block_reason  = (isset($data['user_block_reason'])) ? $data['user_block_reason'] : null;
		$this->user_block_other_reason     = (isset($data['user_block_other_reason'])) ? $data['user_block_other_reason'] : null;
        $this->user_block_added_ip_address = (isset($data['user_block_added_ip_address'])) ? $data['user_block_added_ip_address'] : null;
		$this->user_block_added_timestamp = (isset($data['user_block_added_timestamp'])) ? $data['user_block_added_timestamp'] : null;
		$this->user_block_status = (isset($data['user_block_status'])) ? $data['user_block_status'] : null;		 
    }
	    // Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}