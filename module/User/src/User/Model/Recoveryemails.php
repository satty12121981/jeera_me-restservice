<?php 
namespace User\Model; 
use Zend\InputFilter\InputFilter;

class Recoveryemails 
{
    public $id;
    public $user_id;
    public $user_email;
	public $secret_code;
	public $senddate;
	public $status;	 
	protected $adapter;
    protected $inputFilter;

    /**
     * Used by ResultSet to pass each database row to the entity
     */ 
	
    public function exchangeArray($data)
    {
        $this->id     = (isset($data['id'])) ? $data['id'] : null;
        $this->user_id = (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->user_email  = (isset($data['user_email'])) ? $data['user_email'] : null;
		$this->secret_code  = (isset($data['secret_code'])) ? $data['secret_code'] : null;
		$this->senddate  = (isset($data['senddate'])) ? $data['senddate'] : null;
		$this->status  = (isset($data['status'])) ? $data['status'] : null;
    }

    // Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
	public function getForgotPasswordFilter(){
		$inputFilter = new InputFilter();
		$inputFilter->add(array(
            'name' => 'user_email',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
				 
            ),
			'validators' => array(
        					array('name' => 'EmailAddress'),
							array('name' => 'StringLength', 'options' => array('encoding' => 'UTF-8', 'min' => 1, 'max' => 200,),),
							//array('name' => 'Db\RecordExists', 'options' => array('table' => 'y2m_user','field' => 'user_email',  'adapter' => $dbAdapter),),
                
			),
        ));
		return $inputFilter;
	}
public function getResetPasswordFilter(){
		$inputFilter = new InputFilter();
		 $inputFilter->add(array(
            'name' => 'user_password',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
            ),
            'validators' => array(
                		array('name' => 'StringLength','options' => array('encoding' => 'UTF-8', 'min' => 3,'max' => 60,),
                ),
            ),
        ));
		
		$inputFilter->add(array(
            'name' => 'user_retype_password',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
            ),
            'validators' => array(
                		array('name' => 'StringLength','options' => array('encoding' => 'UTF-8', 'min' => 3,'max' => 60,),),
						array('name' => 'identical','options' => array('token' => 'user_password'),)	
						
           		 ),
        ));
		return $inputFilter;
		 
	}	
}
