<?php 
namespace Spam\Model;
class Spamreasons
{  
    public $reason_id;
    public $reason;
    public $reason_description;
	public $content_type;	
	public function exchangeArray($data)
    {
        $this->reason_id     = (isset($data['reason_id'])) ? $data['reason_id'] : null;
        $this->reason = (isset($data['reason'])) ? $data['reason'] : null;
        $this->reason_description  = (isset($data['reason_description'])) ? $data['reason_description'] : null;	  	
		$this->content_type  = (isset($data['content_type'])) ? $data['content_type'] : null;			
    }	 
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	
}