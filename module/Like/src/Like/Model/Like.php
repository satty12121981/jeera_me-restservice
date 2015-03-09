<?php
namespace Like\Model;
class Like 
{  	
	const File_Delimiter          = '-';
	const File_Seperator          = '/';
	public $like_id;
    public $like_sytem_type_id;
    public $like_by_user_id;
	public $like_status;
	public $like_added_timestamp;
	public $like_added_ip_address;
	public $like_refer_id;
	
    public function exchangeArray($data)
    {
        $this->like_id     = (isset($data['like_id'])) ? $data['like_id'] : null;
        $this->like_system_type_id = (isset($data['like_system_type_id'])) ? $data['like_system_type_id'] : null;
        $this->like_by_user_id	  = (isset($data['like_by_user_id'])) ? $data['like_by_user_id'] : null;
		$this->like_status  = (isset($data['like_status'])) ? $data['like_status'] : null;
		$this->like_added_timestamp  = (isset($data['like_added_timestamp'])) ? $data['like_added_timestamp'] : null;
		$this->like_added_ip_address  = (isset($data['like_added_ip_address'])) ? $data['like_added_ip_address'] : null;
		$this->like_refer_id  = (isset($data['like_refer_id'])) ? $data['like_refer_id'] : null;
    }
	
	#Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }

}