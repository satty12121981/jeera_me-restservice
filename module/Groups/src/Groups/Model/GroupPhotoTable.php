<?php
namespace Groups\Model;
 
use Zend\Db\Sql\Select ;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class GroupPhotoTable extends AbstractTableGateway
{ 
    protected $table = 'y2m_group_photo';  
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Groups());
        $this->initialize();
    }
	public function AddOptions($option_data){
		return $this->insert($option_data);
	}
  public function getGroupPhoto($group_photo_id){
		$group_photo_id  = (int) $group_photo_id;
        $rowset = $this->select(array('group_photo_id' => $group_photo_id));
        $row = $rowset->current();
        return $row;
    }
	public function savePhoto(GroupPhoto $groupphoto){  
       $data = array(
            'group_photo_group_id' => $groupphoto->group_photo_group_id,
            'group_photo_photo'  => $groupphoto->group_photo_photo,			 		
        );
        $group_photo_id = (int)$groupphoto->group_photo_id;
        if ($group_photo_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getGroupPhoto($group_photo_id)) {
                $this->update($data, array('group_photo_id' => $group_photo_id));
				return $group_photo_id;
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
	public function getGalexyPhoto($group_id){
		$group_id  = (int) $group_id;
        $select = new Select;
		$select->from('y2m_group_photo')			 
			->where(array("group_photo_group_id"=>$group_id));		 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());		 
        $row = $resultSet->current();
        return $row;
	}
}