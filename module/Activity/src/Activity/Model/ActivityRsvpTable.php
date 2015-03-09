<?php 
 
namespace Activity\Model;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class ActivityRsvpTable extends AbstractTableGateway
{
    protected $table = 'y2m_group_activity_rsvp'; 
	
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new ActivityRsvp());
        $this->initialize();
    }	
    public function fetchAll($where=Null, $order=Null, $limit=Null, $offset=Null)
    {
     	$resultSet = $this->select(function (Select $select) use ($where, $order, $limit, $offset) {		 
			$select->join('y2m_group_activity', 'y2m_group_activity.group_activity_id = y2m_group_activity_rsvp.group_activity_rsvp_activity_id', array('*'));
			$select->join('y2m_user', 'y2m_user.user_id = y2m_group_activity_rsvp.group_activity_rsvp_user_id', array('user_id','user_given_name', 'user_first_name', 'user_middle_name', 'user_last_name', 'user_email'));				
			$select->join('y2m_photo', 'y2m_photo.photo_id = y2m_user.user_profile_photo_id', array('photo_name'),$select::JOIN_LEFT);	
			$select->join('y2m_group', 'y2m_group.group_id = y2m_group_activity.group_activity_group_id', array('group_title', 'group_seo_title'));		
			$select->join(array('p' => 'y2m_group'), 'p.group_id = y2m_group.group_parent_group_id', array('parent_group_id' => 'group_id', 'parent_group_title' => 'group_title', 'parent_group_seo_title' => 'group_seo_title'));			
			if($where){	$select->where($where); }
			if($order){ $select->order($order); }
			if($limit){ $select->limit($limit); }
			//if($offset){ $select->offset($offset);  }		
				//echo $select->getSqlString();exit;
		});	
		return $resultSet;
    }	 
	public function getActivityRsvp($group_activity_rsvp_id)
    {
        $group_activity_rsvp_id  = (int) $group_activity_rsvp_id;
        $rowset = $this->select(array('group_activity_rsvp_id' => $group_activity_rsvp_id));
        $row = $rowset->current();
        return $row;
    }
	public function getActivityRsvpOfUser($group_activity_rsvp_user_id, $group_activity_rsvp_activity_id)
    {
        $group_activity_rsvp_user_id  = (int) $group_activity_rsvp_user_id;
		$group_activity_rsvp_activity_id  = (int) $group_activity_rsvp_activity_id;
        $rowset = $this->select(array('group_activity_rsvp_user_id' => $group_activity_rsvp_user_id, 'group_activity_rsvp_activity_id' => $group_activity_rsvp_activity_id));
        $row = $rowset->current();
        return $row;
    }  
    public function saveActivityRsvp(ActivityRsvp $activityRsvp)
    {    if (!empty($_SERVER['HTTP_CLIENT_IP'])) { $ip = $_SERVER['HTTP_CLIENT_IP'];} 
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }
		else {   $ip = $_SERVER['REMOTE_ADDR'];}    
	   $data = array(
            'group_activity_rsvp_user_id' => $activityRsvp->group_activity_rsvp_user_id,
            'group_activity_rsvp_activity_id'  => $activityRsvp->group_activity_rsvp_activity_id,			 
			'group_activity_rsvp_added_ip_address'  => $ip,
			'group_activity_rsvp_group_id'  => $activityRsvp->group_activity_rsvp_group_id		
        );
		 
        $group_activity_rsvp_id = (int)$activityRsvp->group_activity_rsvp_id;
        if ($group_activity_rsvp_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getActivityRsvp($group_activity_rsvp_id)) {
                $this->update($data, array('group_activity_rsvp_id' => $group_activity_rsvp_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    } 
    public function deleteActivityRsvp($group_activity_rsvp_id)
    {
        $this->delete(array('group_activity_rsvp_id' => $group_activity_rsvp_id));
    }
	public function removeActivityRsvp($activity_id,$user_id){
		return $this->delete(array('group_activity_rsvp_activity_id' => $activity_id,'group_activity_rsvp_user_id'=>$user_id));
	}
	public function getJoinMembers($activity_id,$limit,$offset){
		$mainSelect = new Select;
		$mainSelect->from('y2m_group_activity_rsvp')
				->join("y2m_user", 'y2m_user.user_id = y2m_group_activity_rsvp.group_activity_rsvp_user_id',array('user_given_name','user_first_name','user_last_name','user_id','user_profile_name','user_register_type','user_fbid'))
				->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')			 
				->join('y2m_user_profile', 'y2m_user_profile.user_profile_user_id = y2m_group_activity_rsvp.group_activity_rsvp_user_id', array('user_profile_country_id','user_profile_city_id'))
				->join("y2m_country","y2m_country.country_id = y2m_user_profile.user_profile_country_id",array("country_code_googlemap","country_title","country_code"),'left')
				->join("y2m_city","y2m_city.city_id = y2m_user_profile.user_profile_city_id",array("city"=>"name"),'left')
				->where(array('y2m_group_activity_rsvp.group_activity_rsvp_activity_id' => $activity_id));
		if($limit!='All'){
		$mainSelect->limit($limit);
		$mainSelect->offset($offset);
		}
		$mainSelect->order("group_activity_rsvp_added_timestamp DESC");		
		$statement = $this->adapter->createStatement();
		
		$mainSelect->prepareStatement($this->adapter, $statement);
		//echo $mainSelect->getSqlString();
		$resultSet = new ResultSet();
		
		$resultSet->initialize($statement->execute());	  

		return $resultSet->toArray();
	}
	public function getJoinMembersWithFriendshipStatus($activity_id,$user_id,$limit,$offset){
		$select = new Select;
		$select->from('y2m_group_activity_rsvp')
			->columns(array('is_friend'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_friend WHERE  (y2m_user_friend.user_friend_sender_user_id = y2m_group_activity_rsvp.group_activity_rsvp_user_id AND y2m_user_friend.user_friend_friend_user_id = '.$user_id.')OR(y2m_user_friend.user_friend_friend_user_id = y2m_group_activity_rsvp.group_activity_rsvp_user_id AND y2m_user_friend.user_friend_sender_user_id = '.$user_id.')),1,0)'),
			'is_requested'=>new Expression('IF(EXISTS(SELECT * FROM   y2m_user_friend_request WHERE  ( y2m_user_friend_request.user_friend_request_friend_user_id = y2m_group_activity_rsvp.group_activity_rsvp_user_id AND y2m_user_friend_request.user_friend_request_sender_user_id = '.$user_id.') ),1,0)'),
			'get_request'=>new Expression('IF(EXISTS(SELECT * FROM   y2m_user_friend_request WHERE  ( y2m_user_friend_request.user_friend_request_sender_user_id = y2m_group_activity_rsvp.group_activity_rsvp_user_id AND y2m_user_friend_request.user_friend_request_friend_user_id = '.$user_id.') ),1,0)')
			))
			->join('y2m_user', 'y2m_user.user_id = y2m_group_activity_rsvp.group_activity_rsvp_user_id', array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'))
			 ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			->where(array('y2m_group_activity_rsvp.group_activity_rsvp_activity_id' => $activity_id));
		$select->limit($limit);
		$select->offset($offset);	
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet; 
	}
	public function deleteAllActivityRsvp($activity_id)
    {
       $this->delete(array('group_activity_rsvp_activity_id' => $activity_id));
	    return true;
    }
	public function getAllJoinedMembers($activity_id,$group_id){
		$select = new Select;
		$select->from('y2m_group_activity_rsvp')
		->join('y2m_user', 'y2m_user.user_id = y2m_group_activity_rsvp.group_activity_rsvp_user_id', array('user_id','user_email'))
		->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
		->join('y2m_user_group_settings',new Expression('y2m_user_group_settings.user_id = y2m_user.user_id AND y2m_user_group_settings.group_id = '.$group_id),array('activity','member','discussion','media','group_announcement'),'left')
		->where(array('y2m_group_activity_rsvp.group_activity_rsvp_activity_id' => $activity_id));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet; 
	}
	public function GetActivityRsvpWithActivityDetails($rsvp_id){
		$select = new Select;
		$select->from('y2m_group_activity_rsvp')
		->join('y2m_user', 'y2m_user.user_id = y2m_group_activity_rsvp.group_activity_rsvp_user_id', array('user_id','user_email','user_given_name','user_profile_name','user_register_type','user_fbid'))
		->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
		->join('y2m_group_activity',new Expression('y2m_group_activity.group_activity_id = y2m_group_activity_rsvp.group_activity_rsvp_activity_id'),array('*'),'')
		->where(array('y2m_group_activity_rsvp.group_activity_rsvp_id' => $rsvp_id));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet->current();
	}
	public function getCountOfAllRSVPuser($activity_id){
		$select = new Select;
		$select->from('y2m_group_activity_rsvp')
			   ->columns(array(new Expression('COUNT(y2m_group_activity_rsvp.group_activity_rsvp_id) as rsvp_count')))
			   ->join('y2m_user', 'y2m_user.user_id = y2m_group_activity_rsvp.group_activity_rsvp_user_id', array())
			   ->join('y2m_group_activity',new Expression('y2m_group_activity.group_activity_id = y2m_group_activity_rsvp.group_activity_rsvp_activity_id'),array())
			   ->where(array('y2m_group_activity_rsvp.group_activity_rsvp_activity_id' => $activity_id));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet->current();
	}
}