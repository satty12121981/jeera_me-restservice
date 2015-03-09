<?php 

namespace User\Model;
use Zend\Db\Sql\Select , \Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class UserProfilePhotoTable extends AbstractTableGateway
{
    protected $table = 'y2m_user_profile_photo';

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new UserProfilePhoto());
        $this->initialize();
    }
	public function addUserProfilePic($data){
		$this->insert($data);
		return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
	}
	public function checkUserProfilePicExist($user_id){
		$select = new Select;
		$select->from('y2m_user_profile_photo')  
		->where(array('profile_user_id' =>  $user_id));
		
	    $statement = $this->adapter->createStatement();
	    $select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString(); die();
		
	    $resultSet = new ResultSet();
	    $resultSet->initialize($statement->execute());  
        return $resultSet->current();
	}
 

}
