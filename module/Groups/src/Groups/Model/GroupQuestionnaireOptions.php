<?php
namespace Groups\Model;
class GroupQuestionnaireOptions
{  	
    public $option_id;
    public $question_id;
    public $option;	 
    public function exchangeArray($data)
    {
		$this->option_id = (isset($data['option_id'])) ? $data['option_id'] : null;
		$this->question_id     = (isset($data['question_id'])) ? $data['question_id'] : null;        
        $this->option  = (isset($data['option'])) ? $data['option'] : null;		  	  
    } 
}