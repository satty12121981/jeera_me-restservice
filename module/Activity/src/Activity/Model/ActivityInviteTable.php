<?php
namespace Activity\Model;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
class ActivityInviteTable extends AbstractTableGateway
{
    protected $table = 'y2m_group_activity_invite'; 
	
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new ActivityInvite());
        $this->initialize();
    }
	
	#It will fetch all activities invites
    public function fetchAll(Select $select = null)
    {
    	if (null === $select){
      		$select = new Select();
		}
       	$select->from($this->table);
        $resultSet = $this->selectWith($select);
		$resultSet->buffer();
        return $resultSet;
    }

	#this will fetch activity details based primary key. Group ActivityInvite Id
   public function getActivityInvite($group_activity_invite_id)
    {
        $group_activity_invite_id  = (int) $group_activity_invite_id;
        $rowset = $this->select(array('group_activity_invite_id' => $group_activity_invite_id));
        $row = $rowset->current();
        return $row;
    }	 

	// this function will save activity in database
    public function saveActivityInvite(ActivityInvite $activity)
    {        
	   if (!empty($_SERVER['HTTP_CLIENT_IP'])) { $ip = $_SERVER['HTTP_CLIENT_IP'];} 
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }
		else {   $ip = $_SERVER['REMOTE_ADDR'];}
	   $data = array(
            'group_activity_invite_sender_user_id' => $activity->group_activity_invite_sender_user_id,
            'group_activity_invite_receiver_user_id'  => $activity->group_activity_invite_receiver_user_id,
			'group_activity_invite_status'  => $activity->group_activity_invite_status,			 
			'group_activity_invite_added_ip_address'  => $ip,
			'group_activity_invite_activity_id'  => $activity->group_activity_invite_activity_id 		
        );

        $group_activity_invite_id = (int)$activity->group_activity_invite_id;
        if ($group_activity_invite_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getActivityInvite($group_activity_invite_id)) {
                $this->update($data, array('group_activity_invite_id' => $group_activity_invite_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

	#this function will delete activity in database
    public function deleteActivityInvite($group_activity_invite_id)
    {
        $this->delete(array('group_activity_invite_id' => $group_activity_invite_id));
    } 
	public function checkInvited($activity_id,$user_id){
		$select = new Select;
		$select->from("y2m_group_activity_invite")
				->columns(array("group_activity_invite_id"))				 
				->where(array('y2m_group_activity_invite.group_activity_invite_activity_id' => $activity_id))
				->where(array('y2m_group_activity_invite.group_activity_invite_receiver_user_id' => $user_id))	;			 		
		$statement = $this->adapter->createStatement();
		
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();		
		$resultSet->initialize($statement->execute());	  
		$row =  $resultSet->current();
		if(!empty($row)&&$row->y2m_group_activity_invite){
			return true;
		}
		else{
			return false;
		}
	}
	public function deleteAllInviteActivity($activity_id){
		$this->delete(array('group_activity_invite_activity_id' => $activity_id));
	}
}