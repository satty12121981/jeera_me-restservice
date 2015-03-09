<?php 

namespace User\Model;
use Zend\Db\Sql\Select , \Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class UserProfileSettingsTable extends AbstractTableGateway
{
    protected $table = 'y2m_user_profile_settings';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new UserProfileSettings());
        $this->initialize();
    }

    public function getUserProfilesettings($user_id)
    { 		
		$select = new Select;
		$select->from('y2m_user_profile_settings')  
		->where(array('user_id' =>  $user_id));
		
	    $statement = $this->adapter->createStatement();
	    $select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString(); die();
		
	    $resultSet = new ResultSet();
	    $resultSet->initialize($statement->execute());  
        return $resultSet->current();
    }
	public function saveUserProfilesettings($setting)
    { 	// print_r($setting); die();
	 
		$data = array(
            'user_id' => $setting['user_id'],
			$setting['field']  => $setting['option'],
        );
		//print_r($data); die();
		$id = (int)$setting['id']; 
        if ($id == 0) {
           return $this->insert($data);
			//return "success";
        } else {       
               return $this->update($data, array('id' => $id));   
				 
        }
    }
	

	


}
