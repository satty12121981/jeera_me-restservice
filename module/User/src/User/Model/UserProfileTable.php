<?php 

namespace User\Model;
use Zend\Db\Sql\Select ;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class UserProfileTable extends AbstractTableGateway
{
    protected $table = 'y2m_user_profile';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new UserProfile());
        $this->initialize();
    } 
    public function getUserProfile($user_profile_id)
    {
        $user_profile_id  = (int) $user_profile_id;
        $rowset = $this->select(array('user_profile_id' => $user_profile_id));
        $row = $rowset->current();         
        return $row;
    }
    public function saveUserProfile(UserProfile $user)
    {
       $data = array(
            'user_profile_dob' => $user->user_profile_dob,
            'user_profile_about_me'  => $user->user_profile_about_me,
            'user_profile_profession'  => $user->user_profile_profession,
            'user_profile_profession_at'  => $user->user_profile_profession_at,
            'user_profile_user_id'  => $user->user_profile_user_id,
            'user_profile_city_id'  => $user->user_profile_city_id,         
            'user_profile_country_id'  => $user->user_profile_country_id,
            'user_address'  => $user->user_address,
            'user_profile_notifyme_id' => $user->user_profile_notifyme_id,
            'user_profile_emailme_id' => $user->user_profile_emailme_id,
            'user_profile_current_location'  => $user->user_profile_current_location,
            'user_profile_phone'  => $user->user_profile_phone,
            'user_profile_status'  => $user->user_profile_status,          
            'user_profile_added_ip_address'  => $user->user_profile_added_ip_address,
            'user_profile_modified_timestamp'  => date("Y-m-d H:i:s"),
            'user_profile_modified_ip_address'  => $user->user_profile_modified_ip_address  	
        );
		$user_profile_id = (int)$user->user_profile_id;
        if ($user_profile_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getUserProfile($user_profile_id)) {
                $this->update($data, array('user_profile_id' => $user_profile_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }		
    public function saveUserProfileApi(UserProfile $user)
    {       
        $data = array(  
                'user_profile_dob' => $user->user_profile_dob, 
                'user_profile_about_me'  => $user->user_profile_about_me,           
                'user_profile_profession'  => $user->user_profile_profession,           
                'user_profile_profession_at'  => $user->user_profile_profession_at,         
                'user_profile_user_id'  => $user->user_profile_user_id,         
                'user_profile_city_id'  => $user->user_profile_city_id,                     
                'user_profile_country_id'  => $user->user_profile_country_id,           
                'user_address'  => $user->user_address,
                'user_profile_notifyme_id' => $user->user_profile_notifyme_id,
                'user_profile_emailme_id' => $user->user_profile_emailme_id,     
                'user_profile_current_location'  => $user->user_profile_current_location,           
                'user_profile_phone'  => $user->user_profile_phone,
                'user_profile_status'  => $user->user_profile_status,
                'user_profile_added_ip_address'  => $user->user_profile_added_ip_address,           
                'user_profile_modified_timestamp'  => date("Y-m-d H:i:s"),          
                'user_profile_modified_ip_address'  => $user->user_profile_modified_ip_address  
                );      
        $user_profile_id = (int)$user->user_profile_id;        
        if ($user_profile_id == 0) {            
            try {               
                $this->insert($data);               
                return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();           
            } catch (\Exception $e) {               
                $dataArr[0]['flag'] = "Failure";                
                $dataArr[0]['message'] = "Database1 Error.";             
                echo json_encode($dataArr);             
                exit;           
            }        
        } else {                        
            if ($this->getUserProfile($user_profile_id)) {              
                try {                   
                    $this->update($data, array('user_profile_id' => $user_profile_id));             
                } 
                catch (\Exception $e) {
                                        
                    $dataArr[0]['flag'] = "Failure";                    
                    $dataArr[0]['message'] = "Database2 Error.";                 
                    echo json_encode($dataArr);                 
                    exit;               
                }           
            } 
            else {                
                throw new \Exception('Form id does not exist');            
            }        
        }    
    }  	
	public function updateUserProfile($data,$user_id){
		$this->update($data, array('user_profile_user_id' => $user_id));
	}
	public function checkCurrentPassword($password,$user_id){ 
		$select = new select();
		$select->from('y2m_user')
				->columns(array('user_id'))
				->where(array('user_password'=>$password))
				->where(array('user_id'=>$user_id));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		$row =  $resultSet->current();	
		if(!empty($row))return true;else return false;
	}	
}
