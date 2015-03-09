<?php 

namespace User\Model;

use Zend\InputFilter\InputFilter;

class UserSuspend
{
    public $user_suspend_id;
    public $user_suspend_user_id;
    public $user_suspend_start_date;
    public $user_suspend_end_date;
	public $user_suspend_added_ip_address;
    public $user_suspend_added_timestamp;
	public $user_suspend_reason;
	public $user_suspend_status;
	public $user_suspend_problem_id;
	public $user_suspend_other_reason;
	protected $inputFilter;

    public function exchangeArray($data)
    {
        $this->user_suspend_id     = (isset($data['user_suspend_id'])) ? $data['user_suspend_id'] : null;
        $this->user_suspend_user_id     = (isset($data['user_suspend_user_id'])) ? $data['user_suspend_user_id'] : null;
        $this->user_suspend_start_date = (isset($data['user_suspend_start_date'])) ? $data['user_suspend_start_date'] : null;
        $this->user_suspend_end_date  = (isset($data['user_suspend_end_date'])) ? $data['user_suspend_end_date'] : null;
		$this->user_suspend_added_ip_address     = (isset($data['user_suspend_added_ip_address'])) ? $data['user_suspend_added_ip_address'] : null;
        $this->user_suspend_added_timestamp = (isset($data['user_suspend_added_timestamp'])) ? $data['user_suspend_added_timestamp'] : null;
		$this->user_suspend_reason = (isset($data['user_suspend_reason'])) ? $data['user_suspend_reason'] : null;
		$this->user_suspend_status = (isset($data['user_suspend_status'])) ? $data['user_suspend_status'] : null;
		$this->user_suspend_problem_id = (isset($data['user_suspend_problem_id'])) ? $data['user_suspend_problem_id'] : null;
		$this->user_suspend_other_reason = (isset($data['user_suspend_other_reason'])) ? $data['user_suspend_other_reason'] : null;
    }
	    // Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}