<?php 
namespace Groups\Model;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class GroupSettingsTable extends AbstractTableGateway
{
    protected $table = 'y2m_group_setting';  
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new UserGroupPermissions());
        $this->initialize();
    }

    public function fetchAll()
    {
        $resultSet = $this->select();
        return $resultSet;
    }
    public function loadGroupSettings($planet_id){
		$select = new Select;
		$select->from("y2m_group_setting")
			   ->columns(array("group_setting_group_id","group_activity_settings","group_welcome_email","group_member_join_type","group_privacy_settings","group_discussion_settings","group_joining_questionnaire"))
			    
			   ->where(array("group_setting_group_id"=>$planet_id));
			    
		 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);		 
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();	
	}
    public function saveSettings($planet_id,$settings,$field){
		$select = new Select;
		$select->from("y2m_group_setting")
			   ->columns(array("group_setting_group_id","group_activity_settings","group_member_join_type","group_discussion_settings","group_privacy_settings","group_discussion_settings"))
			    
			   ->where(array("group_setting_group_id"=>$planet_id));
			    
		 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);		 
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		$row = $resultSet->current();	
		if(!empty($row)&&$row->group_setting_group_id==$planet_id){
			$sql = "UPDATE y2m_group_setting  SET ".$field." = '".$settings."' WHERE  y2m_group_setting.group_setting_group_id = ".$planet_id ;		 	    
			$statement = $this->adapter-> query($sql);  		
			$resultSet = new ResultSet();
			$resultSet->initialize($statement->execute());
			 return true;
			
		}else{
			$data[$field] = $settings;
			$data['group_setting_group_id'] = $planet_id;
			$this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
		}
	}
	
}