<?php
namespace Groups\Model;
class GroupMediaContent
{  	
    public $media_content_id;    
    public $content;	 
    public function exchangeArray($data)
    {
		$this->media_content_id = (isset($data['media_content_id'])) ? $data['media_content_id'] : null;	
        $this->content  = (isset($data['content'])) ? $data['content'] : null;			 
    } 
}