<?php 

namespace User\Model;
use Zend\Db\Sql\Select , \Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class UserGeneralSettingsTable extends AbstractTableGateway
{
    protected $table = 'y2m_user_general_settings';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new UserGeneralSettings());
        $this->initialize();
    }

    public function getUserGeneralsettings($user_id)
    { 		
		$select = new Select;
		$select->from('y2m_user_general_settings')  
		->where(array('user_id' =>  $user_id));
		//echo $select->getSqlString(); die();
   
	    $statement = $this->adapter->createStatement();
	    $select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString(); die();
	    $resultSet = new ResultSet();
	    $resultSet->initialize($statement->execute());  
        return $resultSet->current();
    }
	public function saveUserGeneralsettings($setting)
    {
		$data = array(
            'user_id' => $setting['user_id'],
            'event_alert'  => $setting['event_alert'],
			'survey_alert'  => $setting['survey_alert'],
			'new_feature'  => $setting['new_feature'],
			'friend_request'  => $setting['friend_request'],
			'message'  => $setting['message']
        );
		$id = (int)$setting['id']; 
        if ($id == 0) {
            $this->insert($data);
			//return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
			return "success";
        } else {       
                $this->update($data, array('id' => $id));   
				return "success";
        }
    }
	
	public function updateuserAboutme($data){
	 $userdata = array(
            'user_profile_about_me'  => $data['user_profile_about_me']	
        );
		$this->update($userdata, array('user_profile_user_id' => $data['user_profile_user_id']));
		return "success";
	}

}
