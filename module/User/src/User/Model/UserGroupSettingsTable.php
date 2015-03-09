<?php 
namespace User\Model;
use Zend\Db\Sql\Select , \Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class UserGroupSettingsTable extends AbstractTableGateway
{
    protected $table = 'y2m_user_group_settings';
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new UserGroupSettings());
        $this->initialize();
    }
    public function getUserGroupsettings($user_id){ 		
		$select = new Select;
		$select->from('y2m_user_group_settings')  
		->where(array('user_id' =>  $user_id));		
	    $statement = $this->adapter->createStatement();
	    $select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString(); die();		
	    $resultSet = new ResultSet();
	    $resultSet->initialize($statement->execute());  
        return $resultSet->toArray();
    }
	public function saveUserGroupsettings($setting){
		$data = array(
            'user_id' => $setting['user_id'],
            'group_id'  => $setting['group_id'],
			'activity'  => $setting['activity'],
			'discussion'  => $setting['discussion'],
			'media'  => $setting['media'],
			'member'  => $setting['member'],
			'group_announcement' => $setting['group_announcement'],             
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
	public function getUserGroupSettingsOfSelectedGroup($user_id,$group_id){
		$select = new Select;
		$select->from('y2m_user_group_settings')  
		->where(array('user_id' =>  $user_id,'group_id'=>$group_id));		
	    $statement = $this->adapter->createStatement();
	    $select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString(); die();		
	    $resultSet = new ResultSet();
	    $resultSet->initialize($statement->execute());  
        return $resultSet->current();
	 }
	 public function RemoveSettings($user_id,$group_id){
		return $this->delete(array('user_id' => $user_id,'group_id'=>$group_id));
	}
}
