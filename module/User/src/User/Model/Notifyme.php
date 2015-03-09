<?php 
namespace User\Model; 
use Zend\InputFilter\InputFilter;

class Notifyme 
{
    public $notify_id;
	public $notify_content; 

    /**
     * Used by ResultSet to pass each database row to the entity
     */ 
	
    public function exchangeArray($data)
    {
        $this->notify_id     = (isset($data['notify_id'])) ? $data['notify_id'] : null;
        $this->notify_content = (isset($data['notify_content'])) ? $data['notify_content'] : null;
    
    }

    // Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    	
}
