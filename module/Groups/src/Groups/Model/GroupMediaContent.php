<?php
namespace Groups\Model;
class GroupMediaContent
{  	
    public $media_content_id;    
    public $content;
    public $media_type;
    public function exchangeArray($data)
    {
		$this->media_content_id = (isset($data['media_content_id'])) ? $data['media_content_id'] : null;
        $this->media_type = (isset($data['media_type'])) ? $data['media_type'] : null;
        $this->content  = (isset($data['content'])) ? $data['content'] : null;			 
    } 
}