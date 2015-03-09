<?php 
namespace User\Model; 
use Zend\InputFilter\InputFilter;

class Timezone 
{
    public $timezone_id;
    public $offset; 
	public $timezone; 

    /**
     * Used by ResultSet to pass each database row to the entity
     */ 
	
    public function exchangeArray($data)
    {
        $this->timezone_id     = (isset($data['timezone_id'])) ? $data['timezone_id'] : null;
        $this->offset = (isset($data['offset'])) ? $data['offset'] : null;
        $this->timezone  = (isset($data['timezone'])) ? $data['timezone'] : null;
    }

    // Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    	
}
