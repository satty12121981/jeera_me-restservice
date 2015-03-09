<?php 
namespace User\Model; 
use Zend\InputFilter\InputFilter;

class Emailme 
{
    public $emailme_id;
    public $emailme_content; 
	
    /**
     * Used by ResultSet to pass each database row to the entity
     */ 
	
    public function exchangeArray($data)
    {
        $this->emailme_id     = (isset($data['emailme_id'])) ? $data['emailme_id'] : null;
        $this->emailme_content = (isset($data['emailme_content'])) ? $data['emailme_content'] : null;

    }

    // Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
    	
}
