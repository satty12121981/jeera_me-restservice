<?php
namespace Tag\Model;

class ActivityTag
{  
	public $id;
    public $activity_id;
    public $group_id;
	public $group_tag_id;
	
	/**
     * Used by ResultSet to pass each database row to the entity
     */
    public function exchangeArray($data)
    {
        $this->id     = (isset($data['id'])) ? $data['id'] : null;
        $this->activity_id = (isset($data['activity_id'])) ? $data['activity_id'] : null;
        $this->group_id  = (isset($data['group_id'])) ? $data['group_id'] : null;
		$this->group_tag_id  = (isset($data['group_tag_id'])) ? $data['group_tag_id'] : null;
		
    }
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }	
}