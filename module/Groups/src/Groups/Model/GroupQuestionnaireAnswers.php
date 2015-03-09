<?php
namespace Groups\Model;
class GroupQuestionnaireAnswers
{  	
    public $answer_id;
    public $group_id;
    public $question_id;
	public $answer;
	public $added_user_id;
	public $added_timestamp;
	public $added_ip;
	public $selected_options;
    public function exchangeArray($data)
    {
		$this->answer_id = (isset($data['answer_id'])) ? $data['answer_id'] : null;
		$this->group_id     = (isset($data['group_id'])) ? $data['group_id'] : null;        
        $this->question_id  = (isset($data['question_id'])) ? $data['question_id'] : null;
		$this->answer  = (isset($data['answer'])) ? $data['answer'] : null;
		$this->added_user_id  = (isset($data['added_user_id'])) ? $data['added_user_id'] : null;
		$this->added_ip  = (isset($data['added_ip'])) ? $data['added_ip'] : null;	 	  
		$this->selected_options  = (isset($data['selected_options'])) ? $data['selected_options'] : null;	 	  
    } 
}