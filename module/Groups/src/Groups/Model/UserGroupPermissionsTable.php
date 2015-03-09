<?php 
namespace Groups\Model;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;

class UserGroupPermissionsTable extends AbstractTableGateway
{
    protected $table = 'y2m_user_group_permissions';  
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
	public function savePermissionsIfNotExist($group_id,$role_id,$function_id){
		$select = new Select;
		$select->from('y2m_user_group_permissions')
			   ->columns(array("permission_id"))
			   ->where(array("y2m_user_group_permissions.group_id"=>$group_id))
			   ->where(array("y2m_user_group_permissions.role_id"=>$role_id))
			   ->where(array("y2m_user_group_permissions.function_id"=>$function_id));
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		$row  = $resultSet->current(); 
		if($row && $row->permission_id){
			return $row->permission_id;
		}
		else{ 
			$data["group_id"] = $group_id;
			$data["role_id"] = $role_id;
			$data["function_id"] = $function_id;
			$this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
		} 
	}
	public function removeSelected($group_id,$role_id,$permissions){
		$sql = "DELETE FROM y2m_user_group_permissions  WHERE  y2m_user_group_permissions.group_id = ".$group_id ." AND y2m_user_group_permissions.role_id = ".$role_id." AND y2m_user_group_permissions.permission_id NOT IN (".implode(',',$permissions).")";
	 //echo $sql;die();
		$statement = $this->adapter-> query($sql);  		
		$resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return $resultSet;
	}
	public function removeAllPermissions($group_id,$role_id){
		$this->delete(array('group_id' => $group_id, 'role_id' => $role_id));
	}
    public function getUserPermissions($user_id,$planet_id){	
		$select = new Select;
		$select->from('y2m_user_group_permissions')
			   ->columns(array("function_id"))
			   ->join("y2m_group_roles","y2m_group_roles.group_roles_id = y2m_user_group_permissions.role_id",array())
			   ->join("y2m_user_group","y2m_user_group.user_group_role = y2m_group_roles.group_roles_id",array())
			   ->where(array("y2m_user_group.user_group_user_id"=>$user_id,"y2m_user_group.user_group_group_id"=>$planet_id));
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer(); 
	}
}