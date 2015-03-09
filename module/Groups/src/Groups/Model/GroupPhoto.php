<?php
namespace Groups\Model;
class GroupPhoto
{  	
    public $group_photo_id;
    public $group_photo_group_id;
    public $group_photo_photo;	 
    public function exchangeArray($data)
    {
		$this->group_photo_id = (isset($data['group_photo_id'])) ? $data['group_photo_id'] : null;
		$this->group_photo_group_id     = (isset($data['group_photo_group_id'])) ? $data['group_photo_group_id'] : null;        
        $this->group_photo_photo  = (isset($data['group_photo_photo'])) ? $data['group_photo_photo'] : null;		  	  
    } 
}