<?php  
namespace User\Model;
use Zend\Db\Sql\Select ;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class UserTable extends AbstractTableGateway
{
    protected $table = 'y2m_user';
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new User());
        $this->initialize();
    }
    public function getUserFromEmail($email){       
        $rowset = $this->select(array('user_email' => $email));
        return $rowset->current();
    }
	public function saveUser(User $user){
       $data = array(
            'user_given_name' => $user->user_given_name,
            'user_first_name'  => $user->user_first_name,
			'user_middle_name'  => $user->user_middle_name,
			'user_last_name'  => $user->user_last_name,
			'user_profile_name' =>$user->user_profile_name,
			'user_status'  => $user->user_status,
			'user_added_ip_address'  => $user->user_added_ip_address,
			'user_email'  => $user->user_email,
			'user_password'  => $user->user_password,
			'user_gender'  => $user->user_gender,
			'user_timeline_photo_id'  => $user->user_timeline_photo_id,			 
			'user_profile_photo_id'  => $user->user_profile_photo_id,			 
			'user_mobile'  => $user->user_mobile,
			'user_verification_key'  => $user->user_verification_key,
			
			'user_modified_timestamp'  => date("Y-m-d H:i:s"),
			'user_modified_ip_address'  => $user->user_modified_ip_address,	
			'user_register_type'  => $user->user_register_type,
			'user_fbid'			=> $user->user_fbid,
			'user_accessToken'	=> $user->user_accessToken,
        );
		 $user_id = (int)$user->user_id;
        if ($user_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
						 		 
        } else {
            if ($this->getUser($user_id)) { $this->update($data, array('user_id' => $user_id)); } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
	public function getUser($user_id){
        $user_id  = (int) $user_id;
        $rowset = $this->select(array('user_id' => $user_id));
        return $rowset->current();        
    }
	public function checkProfileNameExist($string){
		$select = new Select;
		$select->from('y2m_user')
			   ->columns(array('user_id'))
			   ->where(array('user_profile_name'=>$string));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		$row =  $resultSet->current();	
		if(!empty($row)&&$row->user_id!=''){
			return true;
		}
		else{
			return false;
		}
	}
	public function getUserByProfilename($profile_name){
        $profile_name  = (string) $profile_name;
        $rowset = $this->select(array('user_profile_name' => $profile_name));
        return $rowset->current();        
    }
	public function getProfileDetails($user_id){
		$select = new select();
		$select->from('y2m_user')
			   ->columns(array("user_id"=>"user_id","user_given_name"=>"user_given_name","user_first_name"=>"user_first_name","user_middle_name"=>"user_middle_name","user_last_name"=>"user_last_name","user_profile_name"=>"user_profile_name","user_email"=>"user_email","user_gender"=>"user_gender","user_mobile"=>"user_mobile","user_status"=>"user_status","user_register_type"=>"user_register_type","user_fbid"=>"user_fbid","user_timezone_id"=>"user_timezone_id"))
			   ->join("y2m_user_profile","y2m_user_profile.user_profile_user_id = y2m_user.user_id",array("user_profile_dob","user_profile_about_me","user_profile_profession","user_profile_profession_at","user_profile_city_id","user_profile_country_id","user_address","user_profile_current_location","user_profile_phone","user_profile_emailme_id","user_profile_notifyme_id"),"left")
			   ->join("y2m_country","y2m_country.country_id = y2m_user_profile.user_profile_country_id",array("country_title","country_code","country_id"),"left")
			   ->join("y2m_city","y2m_city.city_id = y2m_user_profile.user_profile_city_id",array("city_name"=>"name","city_id"=>"city_id"),"left")
			   ->join(array("profile_photo"=>"y2m_user_profile_photo"),"profile_photo.profile_photo_id = y2m_user.user_profile_photo_id",array("profile_photo"=>"profile_photo"),"left")			    
			   ->where(array("y2m_user.user_id"=>$user_id));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		$row =  $resultSet->current();	
		return $row;
	}
	public function updateUser($data,$user_id){
		if ($this->getUser($user_id)) {	$this->update($data, array('user_id' => $user_id));return true;} else {	throw new \Exception('Form id does not exist');}		
	}
	public function checkUserVarification($code,$user){ 
		$rowset = $this->select(array('user_verification_key'=>$code));
        $row = $rowset->current();
		if($row){
			if($row->user_status==0&&md5(md5('userId~'.$row->user_id))==$user)return $row->user_id;	else return false;
		}
		else{return false;}
	}
	public function getUserProfilePic($user_id){
		$select = new Select;
		$select->from('y2m_user_profile_photo')
			   ->columns(array('biopic'=>'profile_photo'))
			   ->join('y2m_user','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array())
			   ->where(array('y2m_user.user_id = '.$user_id));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();
		//exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->current();
	}

	public function checkEmailExists($email,$user_id){       
        $select = new Select;		 
		$select->from('y2m_user')
			   ->columns(array('user_id'))
			   ->where(array('user_email'=>$email))
			   ->where('user_id !='.$user_id);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);	
		//echo $select->getSqlString();exit;		
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		$row =  $resultSet->current();	
		if(empty($row)){return 1;}else{return 0;}
    }
	public function searchUser($search,$limit,$offset){
		$select = new select();
		$select->from('y2m_user')
			   ->columns(array("user_id"=>"user_id","user_given_name"=>"user_given_name","user_first_name"=>"user_first_name","user_middle_name"=>"user_middle_name","user_last_name"=>"user_last_name","user_profile_name"=>"user_profile_name","user_email"=>"user_email","user_gender"=>"user_gender","user_mobile"=>"user_mobile","user_register_type"=>"user_register_type","user_fbid"=>"user_fbid","user_timezone_id"=>"user_timezone_id"))
			   ->join("y2m_user_profile","y2m_user_profile.user_profile_user_id = y2m_user.user_id",array("user_profile_dob","user_profile_about_me","user_profile_profession","user_profile_profession_at","user_profile_city_id","user_profile_country_id","user_address","user_profile_current_location","user_profile_phone","user_profile_emailme_id","user_profile_notifyme_id"),"left")
			   ->join("y2m_country","y2m_country.country_id = y2m_user_profile.user_profile_country_id",array("country_title","country_code","country_id"),"left")
			   ->join("y2m_city","y2m_city.city_id = y2m_user_profile.user_profile_city_id",array("city_name"=>"name","city_id"=>"city_id"),"left")
			   ->join(array("profile_photo"=>"y2m_user_profile_photo"),"profile_photo.profile_photo_id = y2m_user.user_profile_photo_id",array("profile_photo"=>"profile_photo"),"left")			    
			   ->where(array("y2m_user.user_status"=>'live'))
			   ->where(array("y2m_user.user_given_name LIKE '%".$search."%' OR y2m_user.user_email LIKE '%".$search."%'"));
			   $select->limit($limit);
		$select->offset($offset);	
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return  $resultSet->toArray();	
		 
	}
	public function getUserByEmail($email){
		$select = new Select;
		$select->from("y2m_user")
			->columns(array('*'))
			->where(array("user_email"=>$email));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		// echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
		return $resultSet->current(); 
	}
	public function getUserByFbid($fbid){
		$select = new Select;
		$select->from("y2m_user")
			->columns(array('*'))
			->where(array("user_fbid"=>$fbid));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		// echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
		return $resultSet->current(); 
	}
	public function getUserByAccessToken($acctoken){
		$select = new Select;
		$select->from("y2m_user")
			->columns(array('*'))
			->where(array("user_accessToken"=>$acctoken));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
		return $resultSet->current(); 
	}
}
