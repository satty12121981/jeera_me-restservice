<?php 
namespace User\Model; 
use Zend\InputFilter\InputFilter;

class User 
{
    public $user_id;
    public $user_given_name;
    public $user_first_name;
	public $user_middle_name;
	public $user_last_name;
	public $user_profile_name;
	public $user_status;
	public $user_added_ip_address;
	public $user_email;
	public $user_password;
	public $user_gender;
	public $user_timeline_photo_id;	 
	public $user_profile_photo_id;
	public $user_mobile;
	public $user_verification_key;
	public $user_added_timestamp;
	public $user_modified_timestamp;
	public $user_modified_ip_address;
	public $user_register_type;
	public $user_fbid;
	public $user_accessToken;
	public $user_temp_accessToken;
	public $set_timestamp;
	public $user_timezone_id;
	protected $adapter;
    protected $inputFilter;

    /**
     * Used by ResultSet to pass each database row to the entity
     */ 
	
    public function exchangeArray($data)
    {
        $this->user_id     = (isset($data['user_id'])) ? $data['user_id'] : null;
        $this->user_given_name = (isset($data['user_given_name'])) ? $data['user_given_name'] : null;
        $this->user_first_name  = (isset($data['user_first_name'])) ? $data['user_first_name'] : null;
		$this->user_middle_name  = (isset($data['user_middle_name'])) ? $data['user_middle_name'] : null;
		$this->user_last_name  = (isset($data['user_last_name'])) ? $data['user_last_name'] : null;
		$this->user_profile_name  = (isset($data['user_profile_name'])) ? $data['user_profile_name'] : null;
		$this->user_status  = (isset($data['user_status'])) ? $data['user_status'] : null;
		$this->user_added_ip_address  = (isset($data['user_added_ip_address'])) ? $data['user_added_ip_address'] : null;
		$this->user_email  = (isset($data['user_email'])) ? $data['user_email'] : null;
		$this->user_password  = (isset($data['user_password'])) ? $data['user_password'] : null;
		$this->user_gender  = (isset($data['user_gender'])) ? $data['user_gender'] : null;
		$this->user_timeline_photo_id  = (isset($data['user_timeline_photo_id'])) ? $data['user_timeline_photo_id'] : null;		 
		$this->user_profile_photo_id  = (isset($data['user_profile_photo_id'])) ? $data['user_profile_photo_id'] : null;		 
		$this->user_mobile  = (isset($data['user_mobile'])) ? $data['user_mobile'] : null;
		$this->user_verification_key  = (isset($data['user_verification_key'])) ? $data['user_verification_key'] : null;
		$this->user_added_timestamp  = (isset($data['user_added_timestamp'])) ? $data['user_added_timestamp'] : null;
		$this->user_modified_timestamp  = (isset($data['user_modified_timestamp'])) ? $data['user_modified_timestamp'] : null;
		$this->user_modified_ip_address  = (isset($data['user_modified_ip_address'])) ? $data['user_modified_ip_address'] : null;
		$this->user_register_type  = (isset($data['user_register_type'])) ? $data['user_register_type'] : "site";
		$this->user_fbid  = (isset($data['user_fbid'])) ? $data['user_fbid'] : "";
		$this->user_accessToken  = (isset($data['user_accessToken'])) ? $data['user_accessToken'] : "";
		$this->user_temp_accessToken  = (isset($data['user_temp_accessToken'])) ? $data['user_temp_accessToken'] : "";
		$this->set_timestamp  = (isset($data['set_timestamp'])) ? $data['set_timestamp'] : "";
		$this->user_timezone_id  = (isset($data['user_timezone_id'])) ? $data['user_timezone_id'] : "";
    }

    // Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy()
    {
        return get_object_vars($this);
    }
        
	
	function getUserIp(){
	 		$ip ="";
			//Test if it is a shared client
			if (!empty($_SERVER['HTTP_CLIENT_IP'])){
			  $ip=$_SERVER['HTTP_CLIENT_IP'];
			//Is it a proxy address
			}elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){
			  $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
			}else{
			  $ip=$_SERVER['REMOTE_ADDR'];
			}
			//The value of $ip at this point would look something like: "192.0.34.166"
			$ip = ip2long($ip);			
			return $ip;
	 
	 }
	 	  
	 //Given any birthday in "yyyy/mm/dd" format, it should give you the proper age, even with respect to leap years/etc. Additionally, if a birth date is entered that hasn't happened yet (date is in the future), it will output age as "0"
	function calculateUserAge($date)
	{
		 
		$y = date('Y');
		$m = date('n');
		$d = date('j');
		list($yr,$mo,$day) = explode('-',$date);
		$now = ($y*10000+$m*100+$d);
		$past = ($yr*10000+$mo*100+$day);
		$diff = ($past-$now);
		if ($diff>0) { $age = 0 ; }
		else
		{
			$age = (($y-$yr)-1);
			if (($m>$mo) || (($m>=$mo) && ($d>=$day))) { $age++; }
		}
		return $age;
	}
	
	#This function will be used to pass to send select box input for All User
	public function selectFormatAllUser($data){
		$selectObject =array();
		foreach($data as $user){
			$selectObject[$user->user_id] = $user->user_first_name." ".$user->user_last_name;			
		}		
		return $selectObject;	//return blank array
	}
	
	public function setInputFilter(InputFilterInterface $inputFilter)
    {
        throw new \Exception("Not used");
    }

	public function getInputFilter($dbAdapter){
		 $inputFilter = new InputFilter();
		$inputFilter->add(array(
            'name' => 'user_given_name',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
            ),
            'validators' => array(					 
                array('name' => 'StringLength','options' => array('encoding' => 'UTF-8', 'min' => 1,'max' => 50,),),
            ),
        ));

$inputFilter->add(array(
            'name' => 'user_first_name',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
            ),
            'validators' => array(					 
                array('name' => 'StringLength','options' => array('encoding' => 'UTF-8', 'min' => 1,'max' => 50,),),
            ),
        ));
$inputFilter->add(array(
            'name' => 'user_middle_name',
            'required' => false,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
            ),
            'validators' => array(					 
                array('name' => 'StringLength','options' => array('encoding' => 'UTF-8', 'min' => 1,'max' => 50,),),
            ),
        ));		
$inputFilter->add(array(
            'name' => 'user_last_name',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
            ),
            'validators' => array(					 
                array('name' => 'StringLength','options' => array('encoding' => 'UTF-8', 'min' => 1,'max' => 50,),),
            ),
        ));		
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
							array('name' => 'Db\NoRecordExists', 'options' => array('table' => 'y2m_user','field' => 'user_email',  'adapter' => $dbAdapter),),
                
			),
        ));

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

	public function getInputFilterFrom2(){  
		$inputFilter = new InputFilter();
		$inputFilter->add(array(
            'name' => 'user_profile_country_id',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
				 
            ),
            'validators' => array(
                		array('name' => 'StringLength','options' => array('encoding' => 'UTF-8', 'min' => 1,'max' => 500,),
                ),
            ),
        ));	
		
		$inputFilter->add(array(
            'name' => 'user_profile_city',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
				array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
				 
            ),
			'validators' => array(  											 
							array('name' => 'StringLength', 'options' => array('encoding' => 'UTF-8', 'min' => 1, 'max' => 300,),							
                ),
			),
        ));
    
		
		$inputFilter->add(array(
            'name' => 'user_gender',
            'required' => true,
			'RegisterInArrayValidator'=>false,
             
        ));
		
		$inputFilter->add(array(
            'name' => 'user_profile_profession',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
            ),
            'validators' => array(
                		array('name' => 'StringLength','options' => array('encoding' => 'UTF-8', 'min' => 1,'max' => 200,),),
						 
						
           		 ),
        ));
		
		$inputFilter->add(array(
            'name' => 'user_profile_profession_at',
            'required' => true,
            'filters' => array(
                array('name' => 'StripTags'),
                array('name' => 'StringTrim'),
				array('name' => 'HtmlEntities'),
            ),
            'validators' => array(
                		array('name' => 'StringLength','options' => array('encoding' => 'UTF-8', 'min' => 1,'max' => 200,),),
						 
						
           		 ),
        ));
		return $inputFilter;
	}

	public function getResendverificationFilter(){
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
	
}
