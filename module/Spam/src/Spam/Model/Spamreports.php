<?php 
namespace Spam\Model;
class Spamreports
{  
    public $report_id;
    public $content_id;
    public $content_type;
	public $reason_id;	
	public $report_comment;	
	public $reporter_id;	
	public $reported_date;	
	public function exchangeArray($data)
    {
        $this->report_id     = (isset($data['report_id'])) ? $data['report_id'] : null;
        $this->content_id = (isset($data['content_id'])) ? $data['content_id'] : null;
        $this->content_type  = (isset($data['content_type'])) ? $data['content_type'] : null;	  	
		$this->reason_id  = (isset($data['reason_id'])) ? $data['reason_id'] : null;			
		$this->report_comment  = (isset($data['report_comment'])) ? $data['report_comment'] : null;			
		$this->reporter_id  = (isset($data['reporter_id'])) ? $data['reporter_id'] : null;			
		 	
    }	 
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	
}