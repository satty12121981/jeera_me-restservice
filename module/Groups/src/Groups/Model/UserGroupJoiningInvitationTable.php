<?php
namespace Groups\Model;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
class UserGroupJoiningInvitationTable extends AbstractTableGateway
{
    protected $table = 'y2m_user_group_joining_invitation';
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new UserGroupJoiningInvitation());
        $this->initialize();
    }
    public function fetchAll()
    {
        $resultSet = $this->select();
        return $resultSet;
    }
     // function to save group invitation to friends
    public function saveUserGroupJoiningInvite(UserGroupJoiningInvitation $UserGroupJoiningInvitation)
    {
       $data = array(
            'user_group_joining_invitation_sender_user_id'  => $UserGroupJoiningInvitation->user_group_joining_invitation_sender_user_id,
            'user_group_joining_invitation_receiver_id'     => $UserGroupJoiningInvitation->user_group_joining_invitation_receiver_id,
			'user_group_joining_invitation_status'          => $UserGroupJoiningInvitation->user_group_joining_invitation_status,
            'user_group_joining_invitation_ip_address'      => $UserGroupJoiningInvitation->user_group_joining_invitation_ip_address,
            'user_group_joining_invitation_group_id'        => $UserGroupJoiningInvitation->user_group_joining_invitation_group_id
		);

        $user_group_joining_invitation_id = (int)$UserGroupJoiningInvitation->user_group_joining_invitation_id;
        if ($user_group_joining_invitation_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        }
		return true;
    }
	public function checkInvited($user_id,$group_id){
		$select = new Select;
		$select->from('y2m_user_group_joining_invitation')		 		 
			->where(array('y2m_user_group_joining_invitation.user_group_joining_invitation_group_id' => "$group_id"));
		$select->where(array("y2m_user_group_joining_invitation.user_group_joining_invitation_receiver_id"=>$user_id));
		$select->where(array("y2m_user_group_joining_invitation.user_group_joining_invitation_status"=>'active'));
			 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet->current();	
	}	 
}