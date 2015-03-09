<?php
namespace User\Model;
use Zend\InputFilter\InputFilter;
 
class UserGroupSettings  
{
    public $id;
    public $user_id;
    public $group_id;
	public $activity;
	public $discussion;
	public $media;
	public $member;	 
	public $group_announcement;
    protected $inputFilter; 
    public function exchangeArray($data){
        $this->id     = (isset($data['id'])) ? $data['id'] : null;
        $this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->group_id  = (isset($data['group_id'])) ? $data['group_id'] : null;
		$this->activity  = (isset($data['activity'])) ? $data['activity'] : null;
		$this->discussion  = (isset($data['discussion'])) ? $data['discussion'] : null;
		$this->media  = (isset($data['media'])) ? $data['media'] : null;
		$this->member  = (isset($data['member'])) ? $data['member'] : null;		 
		$this->group_announcement  = (isset($data['group_announcement'])) ? $data['group_announcement'] : null;
		
    } 
    public function getArrayCopy(){
        return get_object_vars($this);
    }	  
}