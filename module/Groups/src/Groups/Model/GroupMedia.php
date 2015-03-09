<?php
namespace Groups\Model;
class GroupMedia
{  	
    public $group_media_id;
    public $media_added_user_id;
    public $media_added_group_id;
	public $media_type;		
	public $media_content;
	public $media_caption;
	public $media_added_date;
	public $media_added_ip;
	public $media_status;
    public function exchangeArray($data)
    {
		$this->group_media_id = (isset($data['group_media_id'])) ? $data['group_media_id'] : null;
		$this->media_added_user_id     = (isset($data['media_added_user_id'])) ? $data['media_added_user_id'] : null;        
        $this->media_added_group_id  = (isset($data['media_added_group_id'])) ? $data['media_added_group_id'] : null;	
		$this->media_type = (isset($data['media_type'])) ? $data['media_type'] : null;
		$this->media_content     = (isset($data['media_content'])) ? $data['media_content'] : null;        
        $this->media_caption  = (isset($data['media_caption'])) ? $data['media_caption'] : null;	
		$this->media_added_date = (isset($data['media_added_date'])) ? $data['media_added_date'] : null;
		$this->media_added_ip     = (isset($data['media_added_ip'])) ? $data['media_added_ip'] : null;        
        $this->media_status  = (isset($data['media_status'])) ? $data['media_status'] : null;	
    } 
}