<?php 
namespace Groups\Model;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Where;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Expression as predicate;
class UserGroupTable extends AbstractTableGateway
{
    protected $table = 'y2m_user_group';  
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new UserGroup());
        $this->initialize();
    }
    public function fetchAll(){
        $resultSet = $this->select();
        return $resultSet;
    }	
	#this will check user is registered for which group
	public function getUserGroup($user_id, $group_id){      	 
		$user_id  = (int) $user_id;
		$group_id  = (int) $group_id; 
        $rowset = $this->select(array('user_group_user_id' => $user_id,'user_group_group_id' => $group_id ));
        $row = $rowset->current();		 
        return $row;
    }		
	#fetch the groups for which user is registered
	public function fetchAllActiveGroupsOfaUser($user_id){	   
	  	$select = new Select;
		$select->from('y2m_user_group')
    		->join('y2m_group', 'y2m_group.group_id = y2m_user_group.user_group_group_id', array('*'))
			->join('y2m_user', 'y2m_user.user_id = y2m_user_group.user_group_user_id', array('*'))
			->where(array('y2m_group.group_status' => 'active', 'y2m_user_group.user_group_user_id' => $user_id)); 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet->toArray();	  	 
    }	
	#fetch the user list for a group
	public function fetchAllUserListForGroup($group_id,$user_id,$offset=0,$limit='',$search_string=''){	   
		$select = new Select;
		$select->from('y2m_user_group')
			->columns(array('is_friend'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_friend WHERE  (y2m_user_friend.user_friend_sender_user_id = y2m_user_group.user_group_user_id AND y2m_user_friend.user_friend_friend_user_id = '.$user_id.')OR(y2m_user_friend.user_friend_friend_user_id = y2m_user_group.user_group_user_id AND y2m_user_friend.user_friend_sender_user_id = '.$user_id.')),1,0)'),
			'is_requested'=>new Expression('IF(EXISTS(SELECT * FROM   y2m_user_friend_request WHERE  ( y2m_user_friend_request.user_friend_request_friend_user_id = y2m_user_group.user_group_user_id AND y2m_user_friend_request.user_friend_request_sender_user_id = '.$user_id.' AND y2m_user_friend_request.user_friend_request_status = 0) ),1,0)'),
			'get_request'=>new Expression('IF(EXISTS(SELECT * FROM   y2m_user_friend_request WHERE  ( y2m_user_friend_request.user_friend_request_sender_user_id = y2m_user_group.user_group_user_id AND y2m_user_friend_request.user_friend_request_friend_user_id = '.$user_id.' AND y2m_user_friend_request.user_friend_request_status = 0) ),1,0)'),
			'user_group_status' =>'user_group_status',
			))
			->join('y2m_user', 'y2m_user.user_id = y2m_user_group.user_group_user_id', array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'))
			->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			 
			->where(array('y2m_user_group.user_group_group_id' => $group_id));
			if($search_string!=''){
				$select->where->like('y2m_user.user_given_name','%'.$search_string.'%');	
			}
		if($limit!=''){
		$select->limit($limit);
		$select->offset($offset);	
		}
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet; 
    }
	public function fetchAllUserListForGroupWithSettings($group_id,$user_id,$offset=0,$limit='',$search_string=''){	   
		$select = new Select;
		$select->from('y2m_user_group')
			->columns(array('is_friend'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_friend WHERE  (y2m_user_friend.user_friend_sender_user_id = y2m_user_group.user_group_user_id AND y2m_user_friend.user_friend_friend_user_id = '.$user_id.')OR(y2m_user_friend.user_friend_friend_user_id = y2m_user_group.user_group_user_id AND y2m_user_friend.user_friend_sender_user_id = '.$user_id.')),1,0)'),
			'is_requested'=>new Expression('IF(EXISTS(SELECT * FROM   y2m_user_friend_request WHERE  ( y2m_user_friend_request.user_friend_request_friend_user_id = y2m_user_group.user_group_user_id AND y2m_user_friend_request.user_friend_request_sender_user_id = '.$user_id.' AND y2m_user_friend_request.user_friend_request_status = 0) ),1,0)'),
			'get_request'=>new Expression('IF(EXISTS(SELECT * FROM   y2m_user_friend_request WHERE  ( y2m_user_friend_request.user_friend_request_sender_user_id = y2m_user_group.user_group_user_id AND y2m_user_friend_request.user_friend_request_friend_user_id = '.$user_id.' AND y2m_user_friend_request.user_friend_request_status = 0) ),1,0)'),
			'user_group_status' =>'user_group_status',
			))
			->join('y2m_user', 'y2m_user.user_id = y2m_user_group.user_group_user_id', array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'))
			->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			->join('y2m_user_group_settings',new Expression('y2m_user_group_settings.user_id = y2m_user.user_id AND y2m_user_group_settings.group_id = '.$group_id),array('activity','member','discussion','media','group_announcement'),'left')
			->where(array('y2m_user_group.user_group_group_id' => $group_id));
			if($search_string!=''){
				$select->where->like('y2m_user.user_given_name','%'.$search_string.'%');	
			}
		if($limit!=''){
		$select->limit($limit);
		$select->offset($offset);	
		}
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet; 
    }
	#This function will fetch all galaxy and planets for for a Group Id.Used in admin
	public function fetchAllUserForGeneralGroup($group_id){	   
	  	$select = new Select;
		$select->from('y2m_user_group')
    		->join('y2m_group', 'y2m_group.group_id = y2m_user_group.user_group_group_id', array('*'))
			->join('y2m_user', 'y2m_user.user_id = y2m_user_group.user_group_user_id', array('*'))
			->where(array('y2m_user_group.user_group_group_id' => "$group_id")); 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet;	 
    }	
	#fetch the planet for which user is registered
	public function fetchAllUserSubGroup($user_id, $group_id){	     
	  	$select = new Select;
	  	$predicate = new  \Zend\Db\Sql\Where();
		$select->from('y2m_user_group')
    		->join('y2m_group', 'y2m_group.group_id = y2m_user_group.user_group_group_id', array('*'))
			->join('y2m_user', 'y2m_user.user_id = y2m_user_group.user_group_user_id', array('*'))
			//->where(array($predicate->greaterThan('y2m_group.group_parent_group_id' , "0"), 'y2m_user_group.user_group_user_id' => "$user_id", 'y2m_user_group.user_group_group_id' => "$group_id"));		
			->where(array('y2m_user_group.user_group_user_id' => "$user_id", 'y2m_group.group_parent_group_id' => "$group_id"));			 	
		$statement = $this->adapter->createStatement();		 
		$select->prepareStatement($this->adapter, $statement);		
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet;	 
    }	
	#fetch the owner user information of a group
	public function findGroupOwner($group_id){	     
	  	$select = new Select;
	  	$predicate = new  \Zend\Db\Sql\Where();
		$select->from('y2m_user_group')
    		->join('y2m_group', 'y2m_group.group_id = y2m_user_group.user_group_group_id', array('*'))
			->join('y2m_user', 'y2m_user.user_id = y2m_user_group.user_group_user_id', array('*'))
			//->where(array($predicate->greaterThan('y2m_group.group_parent_group_id' , "0"), 'y2m_user_group.user_group_user_id' => "$user_id", 'y2m_user_group.user_group_group_id' => "$group_id"));		
			->where(array('y2m_user_group.user_group_is_owner' => "1", 'y2m_user_group.user_group_group_id' => "$group_id"));		
		$statement = $this->adapter->createStatement();		 
		$select->prepareStatement($this->adapter, $statement);			 	
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	   	$row = $resultSet->current();
    	//echo "<pre>";print_R($row);exit;    
		return $row;
    }	
	#fetch the planet for which user is registered
	public function fetchAllSubGroupUserNotRegisterInGroup($user_id){  	  	
		$allUserGroups =array();		
		$allUserGroups = $this->fetchAllUserGroup($user_id);		
		#load a Group randoly for that User has not register
		$userRegisterGroupsIds =array();	#it will hold the comma seperated value of users
		$groupString ="";		
		if(isset($allUserGroups) && count($allUserGroups)){ 
			foreach ($allUserGroups as $userGroupRow) {
			 	array_push($userRegisterGroupsIds, $userGroupRow->group_id);			 
			}	 
			$groupString = implode (", ", $userRegisterGroupsIds);
		}		
		if($groupString){
			$sql = "SELECT y2m_user.user_id, y2m_user.user_given_name,y2m_user.user_first_name,y2m_user.user_middle_name,y2m_user.user_last_name,		
		y2m_group.group_id, y2m_group.group_title 
				FROM y2m_user 
				CROSS JOIN y2m_group
				LEFT OUTER JOIN y2m_user_group ON y2m_user_group.user_group_user_id =  y2m_user.user_id  AND y2m_group.group_id = y2m_user_group.user_group_group_id  
				WHERE y2m_user.user_id = $user_id
				AND y2m_group.group_parent_group_id In ($groupString)
				AND y2m_user_group.user_group_id IS NULL ORDER BY rand() LIMIT 1 ";
		$statement = $this->adapter->query($sql);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	 
	  	return $resultSet;		
		}else{
			return array();
		}	 
    } 
    public function saveUserGroup(UserGroup $userGroup){
       $data = array(
            'user_group_user_id' => $userGroup->user_group_user_id,
            'user_group_group_id'  => $userGroup->user_group_group_id,
			'user_group_added_timestamp'  => $userGroup->user_group_added_timestamp,
			'user_group_added_ip_address'  => $userGroup->user_group_added_ip_address,
			'user_group_status'  => $userGroup->user_group_status,
			'user_group_is_owner'  => $userGroup->user_group_is_owner
		);
        $user_group_id = (int)$userGroup->user_group_id;
        if ($user_group_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getUserGroup($user_group_id)) {
                $this->update($data, array('user_group_id' => $user_group_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
    public function deleteUserGroup($user_group_id){
        $this->delete(array('user_group_id' => $user_group_id));
    }
	public function getUserRole($planet_id,$user_id){
		$select = new Select;
		$select->from('y2m_user_group')
			   ->join("y2m_group_roles","y2m_group_roles.group_roles_id = y2m_user_group.user_group_role")			    
			   ->where(array("y2m_user_group.user_group_group_id"=>$planet_id))
			   ->where(array("y2m_user_group.user_group_user_id"=>$user_id));
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();
	}
	public function getAllAdminUsers($group_id){
		$select = new Select;
		$select->from('y2m_user_group')
			   ->columns(array("user_group_user_id"))			   
			   ->where("(y2m_user_group.user_group_role!=0 OR user_group_is_owner = 1) AND y2m_user_group.user_group_group_id = ".$group_id);
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer();
	}	
	public function fetchAllUserListForTag($planet_id,$term){
 		$select = new Select;
		$select->from('y2m_user_group')
			   ->join("y2m_user","y2m_user.user_id = y2m_user_group.user_group_user_id",array("*"))
			   ->where(array("y2m_user_group.user_group_group_id"=>$planet_id));			   
		$select->where->like('y2m_user.user_given_name','%'.$term.'%');
		$statement = $this->adapter->createStatement(); 
	    $select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
	    $resultSet = new ResultSet();
	    $resultSet->initialize($statement->execute());  
	    return $resultSet->buffer();
    
	}
	public function countGroupMembers($planet_id){
		$select = new Select;
		$select->from('y2m_user_group')
			   ->columns(array(new Expression("count(user_group_id) as memberCount")))
			   ->where(array("y2m_user_group.user_group_group_id"=>$planet_id));			   
		 
		$statement = $this->adapter->createStatement(); 
	    $select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
	    $resultSet = new ResultSet();
	    $resultSet->initialize($statement->execute());  
	    return $resultSet->current();
	}
	public function QuitGroup($user_id,$planet_id){
		return $this->delete(array('user_group_user_id' => $user_id,'user_group_group_id'=>$planet_id));
	}
	public function suspendUser($user_id,$planet_id){
		 $data['user_group_status'] = 2;
		 return $this->update($data, array('user_group_group_id' => $planet_id,'user_group_user_id'=>$user_id));
	}
	public function RemoveSuspenssion($user_id,$planet_id){
		$data['user_group_status'] = 1;
		 return $this->update($data, array('user_group_group_id' => $planet_id,'user_group_user_id'=>$user_id));
	}
    public function fetchAllActiveUserListForGroup($group_id,$user_id,$offset=0,$limit='',$search_string='') {	   
		$select = new Select;
		$select->from('y2m_user_group')
			->columns(array('is_friend'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_friend WHERE  (y2m_user_friend.user_friend_sender_user_id = y2m_user_group.user_group_user_id AND y2m_user_friend.user_friend_friend_user_id = '.$user_id.')OR(y2m_user_friend.user_friend_friend_user_id = y2m_user_group.user_group_user_id AND y2m_user_friend.user_friend_sender_user_id = '.$user_id.')),1,0)'),
			'is_requested'=>new Expression('IF(EXISTS(SELECT * FROM   y2m_user_friend_request WHERE  ( y2m_user_friend_request.user_friend_request_friend_user_id = y2m_user_group.user_group_user_id AND y2m_user_friend_request.user_friend_request_sender_user_id = '.$user_id.'  AND y2m_user_friend_request.user_friend_request_status = 0 ) ),1,0)'),
			'get_request'=>new Expression('IF(EXISTS(SELECT * FROM   y2m_user_friend_request WHERE  ( y2m_user_friend_request.user_friend_request_sender_user_id = y2m_user_group.user_group_user_id AND y2m_user_friend_request.user_friend_request_friend_user_id = '.$user_id.'  AND y2m_user_friend_request.user_friend_request_status = 0) ),1,0)'),
			'user_group_status' =>'user_group_status',
			))
			->join('y2m_user', 'y2m_user.user_id = y2m_user_group.user_group_user_id', array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'))
			->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			->where(array('y2m_user_group.user_group_group_id' => $group_id))
			->where(array('y2m_user_group.user_group_status' => 1));
			if($search_string!=''){
				$select->where->like('y2m_user.user_given_name','%'.$search_string.'%');	
			}
		if($limit!=''){
		$select->limit($limit);
		$select->offset($offset);	
		}
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet; 
    }
	public function fetchAllUserPlanetsWithUserSettings($user_id,$offset,$limit) { //echo $user_id; die();
        $select = new Select;
        $select->from('y2m_user_group')
            ->join('y2m_group', 'y2m_group.group_id = y2m_user_group.user_group_group_id', array('*'))           
			->join('y2m_user_group_settings', 'y2m_user_group_settings.user_id = y2m_user_group.user_group_user_id AND y2m_user_group_settings.group_id = y2m_user_group.user_group_group_id', array('*'),"left")
            ->where(array('y2m_user_group.user_group_user_id' => $user_id))
            ->where->greaterThan('y2m_group.group_parent_group_id', 0);
		if($limit!=''){
			$select->limit($limit);
			$select->offset($offset);	
		}
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
		//echo $select->getSqlString(); die();
        return $resultSet->buffer();

    }
	public function fetchAllUserPlanets($user_id,$offset,$limit){
		$sub_select = new Select;
		$sub_select->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as member_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array());
		$sub_select->group('y2m_group.group_id');
		$select = new Select;		
        $select->from('y2m_user_group')
			->columns(array())
            ->join('y2m_group', 'y2m_group.group_id = y2m_user_group.user_group_group_id', array('*'))
			->join(array('temp_member' => $sub_select), 'temp_member.group_id = y2m_group.group_id',array('member_count'),'left')
			->join(array('y2m_album_data'=>'y2m_album_data'),'y2m_group.group_photo_id = y2m_album_data.data_id',array('data_content'=>'data_content'),'left')
			->join(array("group_parent"=>"y2m_group"),"group_parent.group_id = y2m_group.group_parent_group_id",array("parent_seo_title"=>'group_seo_title'),"left")
            ->where(array('y2m_user_group.user_group_user_id' => $user_id))
            ->where->greaterThan('y2m_group.group_parent_group_id', 0);
			
			$select->limit($limit);
			$select->offset($offset);
			
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return $resultSet->buffer();
	}
	public function AddMembersTOGroup($data){
		$this->insert($data);
		return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
	 }
	public function getPlanetMembersWithGroupSettings($planet_id){
		$select = new Select;
		$select->from('y2m_user_group')
			  ->join('y2m_user_group_settings',new Expression('y2m_user_group_settings.user_id = y2m_user_group.user_group_user_id AND y2m_user_group_settings.group_id = '.$planet_id),array('activity','member','discussion','media','group_announcement'),'left')
			   ->where(array("y2m_user_group.user_group_group_id"=>$planet_id));			   
		;	
		$statement = $this->adapter->createStatement();		
	    $select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
	    $resultSet = new ResultSet();
	    $resultSet->initialize($statement->execute());  
	    return $resultSet->buffer();
	}
	public function getAllAdminUsersWithGroupSettings($group_id){
		$select = new Select;
		$select->from('y2m_user_group')
			   ->columns(array("user_group_user_id"))
			   ->join('y2m_user_group_settings',new Expression('y2m_user_group_settings.user_id = y2m_user_group.user_group_user_id AND y2m_user_group_settings.group_id = '.$group_id),array('activity','member','discussion','media','group_announcement'),'left')
			   ->where(new Expression("(y2m_user_group.user_group_role!=0 OR user_group_is_owner = 1) AND y2m_user_group.user_group_group_id = ".$group_id));
		$select->group('user_group_user_id');
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer();
	}
	public function GetUserGroupWithGroupDetails($user_group_id){
		$select = new Select;
		$select->from('y2m_user_group')
			   ->columns(array("user_group_user_id"))
			   ->join('y2m_group', 'y2m_group.group_id = y2m_user_group.user_group_group_id', array('*'))
			   ->join(array("group_parent"=>"y2m_group"),"group_parent.group_id = y2m_group.group_parent_group_id",array("parent_seo_title"=>'group_seo_title'),"left")
			   ->join('y2m_user', 'y2m_user.user_id = y2m_user_group.user_group_user_id', array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'))
			   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			->where(array('y2m_user_group.user_group_id' => $user_group_id));
		$select->group('user_group_user_id');
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();
	}
	public function RemoveAllGroupMembersWithPermissions($group_id){
		$sql = "DELETE FROM  y2m_user_group_joining_invitation WHERE y2m_user_group_joining_invitation.user_group_joining_invitation_group_id =".$group_id;	
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		$sql = "DELETE FROM  y2m_user_group_joining_request WHERE y2m_user_group_joining_request.user_group_joining_request_group_id = ".$group_id;	 	 
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		$sql = "DELETE FROM y2m_user_group WHERE y2m_user_group.user_group_group_id = ".$group_id;	 
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();		 
		return true;
	}
	public function getAllPlanetMebers($planet_id,$limit,$offset,$field,$order){
		$select = new Select;
		$select->from('y2m_user_group')
				->columns(array("user_group_is_owner"))
			   ->join('y2m_user','y2m_user_group.user_group_user_id = y2m_user.user_id',array('user_id','user_given_name','user_first_name','user_last_name'))
			   ->join('y2m_group_roles','y2m_group_roles.group_roles_id = y2m_user_group.user_group_role',array('group_roles_name'),"left")
			   ->where(array("y2m_user_group.user_group_group_id"=>$planet_id));	
		 		$select->order($field.' '.$order);	   
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement(); 
	    $select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
	    $resultSet = new ResultSet();
	    $resultSet->initialize($statement->execute());  
	    return $resultSet->buffer();
	}
	public function updateUserRoles($user_id,$group_id,$roles){
		$data['user_group_role'] = $roles;
		$this->update($data, array('user_group_user_id' => $user_id,'user_group_group_id'=>$group_id));
		return true;
	}
	public function changeOwnership($user_id,$group_id,$newowner){
		$data['user_group_user_id'] = $newowner;
		$this->update($data, array('user_group_user_id' => $user_id,'user_group_group_id'=>$group_id));
		return true;
	}
	public function getGroupUserDetails($planet_id,$user_id){
		$select = new Select;
		$select->from('y2m_user_group')			  		    
			   ->where(array("y2m_user_group.user_group_group_id"=>$planet_id))
			   ->where(array("y2m_user_group.user_group_user_id"=>$user_id));
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();
	}
	public function getAllGroupMembers($group_id){
		$select = new Select;
		$select->from('y2m_user_group')			  		    
			   ->where(array("y2m_user_group.user_group_group_id"=>$group_id)) ;
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer();
	}
	public function getAllActiveMembersExceptMeAction($group_id,$user_id){
		$select = new Select;
		$select->from('y2m_user_group')
			   ->join('y2m_user','y2m_user_group.user_group_user_id = y2m_user.user_id',array('user_id','user_given_name','user_first_name','user_last_name','user_fbid'))
			   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			   ->where("y2m_user_group.user_group_group_id=".$group_id." AND y2m_user_group.user_group_status = 'available'")
			   ->where("y2m_user_group.user_group_user_id!= ".$user_id);
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();
	}
	public function getmatchGroupsByuserTags($user_id,$city,$country,$category,$limit,$offset){
		$select = new Select;
		$sub_select = new Select;
		$sub_select2 = new Select;
		$sub_select3 = new Select;
		$sub_select->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as member_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array());
		$sub_select->group('y2m_group.group_id');		
		$sub_select2->from('y2m_user_friend')
				  ->columns(array('friend_user'=>new Expression('IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id)')))
				  ->where->equalTo('user_friend_sender_user_id',$user_id)->OR->equalTo('user_friend_friend_user_id',$user_id)
				 ;
		$sub_select3->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as friend_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array())
				   ->where->in("user_group_user_id",$sub_select2);
		$sub_select3->group('y2m_group.group_id');		 
		$select->from('y2m_group')
			   ->join('y2m_group_tag',"y2m_group_tag.group_tag_group_id = y2m_group.group_id")
			   ->join('y2m_tag',"y2m_group_tag.group_tag_tag_id = y2m_tag.tag_id")
			   ->join('y2m_tag_category',"y2m_tag_category.tag_category_id = y2m_tag.category_id")			   
			   
			   ->join("y2m_country","y2m_country.country_id = y2m_group.group_country_id",array("country_code_googlemap","country_title","country_code"),'left')
			   ->join("y2m_city","y2m_city.city_id = y2m_group.group_city_id",array("city"=>"name"),'left')
			   ->join("y2m_group_photo","y2m_group_photo.group_photo_group_id = y2m_group.group_id",array("group_photo_photo"=>"group_photo_photo"),'left')
			   ->join(array('temp_member' => $sub_select), 'temp_member.group_id = y2m_group.group_id',array('member_count'),'left')
			   ->join(array('temp_friends' => $sub_select3), 'temp_friends.group_id = y2m_group.group_id',array('friend_count'),'left')
			   ->where('y2m_group.group_status = "active"')
			   ->where('y2m_group.group_id NOT IN (SELECT user_group_group_id FROM y2m_user_group WHERE y2m_user_group.user_group_user_id = '.$user_id.' )')
			   ->where(array("y2m_group_tag.group_tag_tag_id IN (SELECT user_tag_tag_id FROM y2m_user_tag WHERE user_tag_user_id = $user_id)"));
		if($country!=''){
			$select->where('y2m_country.country_title like "%'.$country.'%"');
		}
		if($city!=''){
			$select->where('y2m_city.name like "%'.$city.'%"');
		}
		if(!empty($category)){
			$select->where->in("y2m_tag_category.tag_category_id",$category);
		}
		$select->group("y2m_group.group_id");
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement();
		
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();
			   
	}
	public function getMatchGroupsByUserTagsForRestApi($user_id,$user_tag_status,$city,$country,$myfriends,$category,$limit=0,$offset=0){
		$select = new Select;
		$sub_select = new Select;
		$sub_select2 = new Select;
		$sub_select3 = new Select;
		$sub_select4 = new Select;
		$sub_select->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as member_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array());
		$sub_select->group('y2m_group.group_id');		
		$sub_select2->from('y2m_user_friend')
				  ->columns(array('friend_user'=>new Expression('IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id)')))
				  ->where->equalTo('user_friend_sender_user_id',$user_id)->OR->equalTo('user_friend_friend_user_id',$user_id);
		$sub_select3->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as friend_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),"y2m_group.group_id = y2m_user_group.user_group_group_id",array())
				   ->where->in("user_group_user_id",$sub_select2);
		$sub_select3->group('y2m_group.group_id');
		$sub_select4->from('y2m_group')
				   ->columns(array("group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),"y2m_group.group_id = y2m_user_group.user_group_group_id",array())
				   ->where->in("user_group_user_id",$sub_select2);
		$sub_select4->group('y2m_group.group_id');
		$select->from('y2m_group')
			   ->join('y2m_group_tag',"y2m_group_tag.group_tag_group_id = y2m_group.group_id")
			   ->join('y2m_tag',"y2m_group_tag.group_tag_tag_id = y2m_tag.tag_id")
			   ->join('y2m_tag_category',"y2m_tag_category.tag_category_id = y2m_tag.category_id")			   
			   ->join("y2m_country","y2m_country.country_id = y2m_group.group_country_id",array("country_code_googlemap","country_title","country_code"),'left')
			   ->join("y2m_city","y2m_city.city_id = y2m_group.group_city_id",array("city"=>"name"),'left')
			   ->join("y2m_group_photo","y2m_group_photo.group_photo_group_id = y2m_group.group_id",array("group_photo_photo"=>"group_photo_photo"),'left')
			   ->join(array('temp_member' => $sub_select), 'temp_member.group_id = y2m_group.group_id',array('member_count'),'left')
			   ->join(array('temp_friends' => $sub_select3), 'temp_friends.group_id = y2m_group.group_id',array('friend_count'),'left')
			   ->where('y2m_group.group_status = "active"')
			   ->where(array('y2m_group.group_id NOT IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' )'));

	        if ($user_tag_status != ''){
	            $select->where(array('y2m_group_tag.group_tag_tag_id IN
	                (SELECT y2m_user_tag.user_tag_tag_id FROM y2m_user_tag where y2m_user_tag.user_tag_user_id= '.$user_id.')'));
	        } else {
	            $select->where(array('y2m_group_tag.group_tag_tag_id IN
	                (SELECT y2m_tag.tag_id FROM y2m_tag)'));
	        }
		if($country!=''){
			$select->where(array('y2m_country.country_id'=>$country)) ;
		}
		if($city!=''){
			$select->where(array('y2m_city.city_id'=>$city));
		}
		if(isset($category[0]) && $category[0] !=''){
			$select->where->in("y2m_tag_category.tag_category_id",$category);
		}
		if (!empty($myfriends) && $myfriends == "yes" ){
			$select->where(array("y2m_group.group_id IN (SELECT y2m_group.group_id AS group_id FROM y2m_group INNER JOIN y2m_user_group AS y2m_user_group ON y2m_group.group_id = y2m_user_group.user_group_group_id WHERE user_group_user_id IN (SELECT IF(user_friend_sender_user_id=$user_id,user_friend_friend_user_id,user_friend_sender_user_id) AS friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = $user_id OR user_friend_friend_user_id = $user_id) GROUP BY y2m_group.group_id)"));
		}
		$select->group("y2m_group.group_id");
		$select->limit((int) $limit);
		$select->offset((int) $offset);
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
        	return $resultSet->toArray();
	}
	public function fetchAllUserGroupCount($user_id,$visitor_id, $strType,$profile_type){ 
        // creating condition for gropu navigations
         
	    $select = new Select;
        $select->from('y2m_group')
               ->columns(array(new Expression('COUNT(y2m_group.group_id) as group_count')))
               ->join('y2m_user_group', 'y2m_group.group_id = y2m_user_group.user_group_group_id')
			   ->where(array('user_group_user_id' => "$user_id"));
		if($strType == 'created'){
			$select->where(array("y2m_user_group.user_group_is_owner = 1"));
		}
		if($strType == 'joined'){
			$select->where(array("y2m_user_group.user_group_is_owner = 0"));
		}
		if($strType == 'pending'){
			$select->where(array("y2m_group.group_status = 'pending'"));
			$select->where(array("y2m_user_group.user_group_is_owner = 1"));
		}
		if($profile_type!='mine'){
			$select->where(array("y2m_group.group_status = 'active'"));
		}
		if($strType == 'mutual'){
			$select->where(array("y2m_group.group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = ".$visitor_id." GROUP BY(user_group_group_id))")); 
		}
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString($this->adapter->getPlatform());exit;
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->current();
    }
	public function fetchUserGroupList($user_id,$visitor_id,$strType,$profile,$limit,$offset){ 
		$select = new Select;
		$sub_select = new Select;
		$sub_select2 = new Select;
		$sub_select3 = new Select;
		$sub_select->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as member_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array());
		$sub_select->group('y2m_group.group_id');		
		$sub_select2->from('y2m_user_friend')
				  ->columns(array('friend_user'=>new Expression('IF(user_friend_sender_user_id='.$visitor_id.',user_friend_friend_user_id,user_friend_sender_user_id)')))
				  ->where->equalTo('user_friend_sender_user_id',$visitor_id)->OR->equalTo('user_friend_friend_user_id',$visitor_id)
				 ;
		$sub_select3->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as friend_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array())
				   ->where->in("user_group_user_id",$sub_select2);
		$sub_select3->group('y2m_group.group_id');
		$select->from('y2m_group')
			   ->columns(array("group_id","group_title","group_seo_title","group_status","group_type",'is_admin'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_group WHERE  (y2m_user_group.user_group_group_id = y2m_group.group_id AND y2m_user_group.user_group_user_id = '.$visitor_id.' AND y2m_user_group.user_group_is_owner = 1)),1,0)'),
			   'is_member'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_group WHERE  (y2m_user_group.user_group_group_id = y2m_group.group_id AND y2m_user_group.user_group_user_id = '.$visitor_id.' AND y2m_user_group.user_group_is_owner = 0)),1,0)'),
			   'is_requested'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_group_joining_request WHERE  (y2m_user_group_joining_request.user_group_joining_request_group_id = y2m_group.group_id AND y2m_user_group_joining_request.user_group_joining_request_user_id = '.$visitor_id.' AND y2m_user_group_joining_request.user_group_joining_request_status = "active")),1,0)')
			   ))
			   ->join('y2m_user_group', 'y2m_group.group_id = y2m_user_group.user_group_group_id')
			   ->join("y2m_country","y2m_country.country_id = y2m_group.group_country_id",array("country_code_googlemap","country_title","country_code"),'left')
			   ->join("y2m_city","y2m_city.city_id = y2m_group.group_city_id",array("city"=>"name"),'left')
			   ->join("y2m_group_photo","y2m_group_photo.group_photo_group_id = y2m_group.group_id",array("group_photo_photo"=>"group_photo_photo"),'left')
			   ->join(array('temp_member' => $sub_select), 'temp_member.group_id = y2m_group.group_id',array('member_count'),'left')
			   ->join(array('temp_friends' => $sub_select3), 'temp_friends.group_id = y2m_group.group_id',array('friend_count'),'left')			  
			   ->where(array("y2m_user_group.user_group_user_id = $user_id"));
		if($strType == 'created'){
			$select->where(array("y2m_user_group.user_group_is_owner = 1"));
		}
		if($strType == 'joined'){
			$select->where(array("y2m_user_group.user_group_is_owner = 0"));
		}
		if($strType == 'pending'){
			$select->where(array("y2m_group.group_status = 'pending'"));
			$select->where(array("y2m_user_group.user_group_is_owner = 1"));
		}
		if($strType == 'mutual'){
			$select->where(array("y2m_group.group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = ".$visitor_id." GROUP BY(user_group_group_id))")); 
		}
		if($profile!='mine'){
			$select->where(array("y2m_group.group_status = 'active'"));
		}
		if ($limit) {
			$select->limit($limit);
			$select->offset($offset);
        }
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString($this->adapter->getPlatform());exit;
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->toArray(); 
	}
	 public function deleteOneUserGroup($group_id, $user_id){
       return $this->delete(array('user_group_user_id' => $user_id, 'user_group_group_id' => $group_id));
    }
	public function checkOwner($group_id, $user_id){
		$select = new Select;
		$select->from('y2m_user_group')			  		    
			   ->where(array("y2m_user_group.user_group_group_id"=>$group_id))
			   ->where(array("y2m_user_group.user_group_user_id"=>$user_id))
			   ->where(array("y2m_user_group.user_group_is_owner"=>1));
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		$row =  $resultSet->current();
		if(!empty($row)){
		return true;
		}else{ return false;}
	}
	public function getFriendsCount($group_id, $user_id){  
		$select = new Select;
		$subselect = new Select;
		$expression = new Expression(
            "IF (`user_friend_sender_user_id`= $user_id , `user_friend_friend_user_id`, `user_friend_sender_user_id`)"
        );
        $subselect->from('y2m_user_friend')
            ->columns(array('friend_id'=>$expression))
            ->where->equalTo('user_friend_sender_user_id', $user_id)->OR->equalTo('user_friend_friend_user_id', $user_id)
           ;
		$select->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as friend_count')))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array())
				   ->where(array('y2m_group.group_id'=>$group_id))
				   ->where->in("user_group_user_id",$subselect);
		$select->group('y2m_group.group_id');
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		//echo $select->getSqlString();exit;
		$resultSet->initialize($statement->execute());	
		$row =  $resultSet->current();
		return $row;
	}
	public function getMembers($group_id,$user_id,$type,$offset,$limit){
		$group_count_select = new Select;
		$group_count_select->from('y2m_user_group')
					->columns(array('group_count'=>new Expression('COUNT(user_group_id)'),'user_group_user_id'=>'user_group_user_id'));
		$group_count_select->group('y2m_user_group.user_group_user_id');
		$group_created_select = new Select;
		$group_created_select->from('y2m_user_group')
					->columns(array('created_group_count'=>new Expression('COUNT(user_group_id)'),'user_group_user_id'=>'user_group_user_id'))
					->where(array('user_group_is_owner=1'));
		$group_created_select->group('y2m_user_group.user_group_user_id');
		$select = new Select;
		$select->from('y2m_user')
				->columns(array('user_id','user_given_name','user_profile_name','user_register_type','user_fbid','is_admin'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_group WHERE  (y2m_user_group.user_group_group_id = '.$group_id.' AND y2m_user_group.user_group_user_id = y2m_user.user_id AND y2m_user_group.user_group_is_owner = 1)),1,0)')))
				->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_user.user_id = y2m_user_group.user_group_user_id',array('user_group_is_owner','user_group_role'))
				->join('y2m_user_profile', 'y2m_user_profile.user_profile_user_id = y2m_user.user_id', array('*'))
				->join("y2m_country","y2m_country.country_id = y2m_user_profile.user_profile_country_id",array("country_code_googlemap","country_title","country_code"),'left')
				->join("y2m_city","y2m_city.city_id = y2m_user_profile.user_profile_city_id",array("city"=>"name"),'left')
				->join("y2m_user_profile_photo","y2m_user_profile_photo.profile_photo_id = y2m_user.user_profile_photo_id",array("profile_icon"=>"profile_photo"),'left')
				->join(array("group_count_temp"=>$group_count_select),'y2m_user.user_id = group_count_temp.user_group_user_id', array('group_count'),'left')
			  //  ->join(array("group_created_temp"=>$group_created_select),'y2m_user.user_id = group_count_temp.user_group_user_id', array('created_group_count'),'left')
				->where(array("y2m_user_group.user_group_group_id"=>$group_id));
		if($type == 'Friends'){
			$subselect = new Select;
			$expression = new Expression(
				"IF (`user_friend_sender_user_id`= $user_id , `user_friend_friend_user_id`, `user_friend_sender_user_id`)"
			);
			$subselect->from('y2m_user_friend')
				->columns(array('friend_id'=>$expression))
				->where->equalTo('user_friend_sender_user_id', $user_id)->OR->equalTo('user_friend_friend_user_id', $user_id)
				;
			$select->where->in("y2m_user_group.user_group_user_id",$subselect);
		}
		if($type == 'Owners'){
			$select->where('(user_group_role =1 OR user_group_is_owner=1)');
		}
		$select->order(array('user_group_is_owner DESC','user_group_role DESC'));
		$select->limit($limit);
	    $select->offset($offset);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		//echo $select->getSqlString();exit;
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();
	 
	}
	public function getCreatedGroupCount($user_id){
		$group_created_select = new Select;
		$group_created_select->from('y2m_user_group')
					->columns(array('created_group_count'=>new Expression('COUNT(user_group_id)'),'user_group_user_id'=>'user_group_user_id'))
					->where(array('user_group_is_owner=1'))
					->where(array('user_group_user_id='.$user_id));
		$group_created_select->group('y2m_user_group.user_group_user_id');
		 
		$statement = $this->adapter->createStatement();
		$group_created_select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		//echo $group_created_select->getSqlString();exit;
		$resultSet->initialize($statement->execute());	
		$row =  $resultSet->current();
		return $row;
	}
	public function getOwnersCount($group_id){
		$select = new Select;
		$select->from('y2m_user_group')
				->columns(array('group_owner_count'=>new Expression('COUNT(user_group_id)')))
				->where('(user_group_role =1 OR user_group_is_owner=1)')
				->where(array("y2m_user_group.user_group_group_id"=>$group_id));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		//echo $select->getSqlString();exit;
		$resultSet->initialize($statement->execute());	
		$row =  $resultSet->current();
		return $row;
	}
	public function getUserGroupCount($user_id){
		$group_select = new Select;
		$group_select->from('y2m_user_group')
					->columns(array('group_count'=>new Expression('COUNT(user_group_id)'),'user_group_user_id'=>'user_group_user_id'))
					->where(array("user_group_status='available'"))
					->where(array('user_group_user_id='.$user_id));
		$group_select->group('y2m_user_group.user_group_user_id');
		 
		$statement = $this->adapter->createStatement();
		$group_select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		//echo $group_select->getSqlString();exit;
		$resultSet->initialize($statement->execute());	
		$row =  $resultSet->current();
		return $row;
	}
        public function UpdateUserGroup(UserGroup $userGroup){
            $data = array(
                 'user_group_user_id'                => $userGroup->user_group_user_id,
                 'user_group_group_id'               => $userGroup->user_group_group_id,
                 'user_group_added_timestamp'        => $userGroup->user_group_added_timestamp,
                 'user_group_added_ip_address'       => $userGroup->user_group_added_ip_address,
                 'user_group_status'                 => $userGroup->user_group_status,
                 'user_group_is_owner'               => $userGroup->user_group_is_owner,
                 'user_group_role'                   => $userGroup->user_group_role //Added by Kiran Singh Date 16-03-2015 to add role for add group form
                 );
            $user_group_id = (int)$userGroup->user_group_id;
            if ($user_group_id == 0) {
                $this->insert($data);
                    return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
            } else {  //  print_r($data); echo "--"; die;      
                 $this->update($data, array('user_group_id' => $user_group_id));

            }
        }
public function getFriendsNotMemberOfGroup($group_id,$user_id,$search_string,$offset=0,$limit=0){
		$group_select = new Select;
		$subselect = new Select;
		$expression = new Expression(
            "IF (`user_friend_sender_user_id`= $user_id , `user_friend_friend_user_id`, `user_friend_sender_user_id`)"
        );
        $subselect->from('y2m_user_friend')
            ->columns(array('friend_id'=>$expression))
            ->where->equalTo('user_friend_sender_user_id', $user_id)->OR->equalTo('user_friend_friend_user_id', $user_id)
           ;
		$group_select->from('y2m_user')
					->columns(array('user_id','user_given_name','user_profile_name','user_register_type','user_fbid'))
					->join(array('temp_member' => $subselect), 'temp_member.friend_id = y2m_user.user_id',array())
					->join("y2m_user_profile_photo","y2m_user_profile_photo.profile_photo_id = y2m_user.user_profile_photo_id",array("profile_photo"=>"profile_photo"),'left')
					->where(array("y2m_user.user_id NOT IN (SELECT 	user_group_user_id FROM y2m_user_group WHERE user_group_group_id =  $group_id)"));
		if($search_string!=''){
				$group_select->where->like('y2m_user.user_given_name','%'.$search_string.'%');	
			}
		if($limit>0){
			$group_select->limit($limit);
			$group_select->offset($offset);
		}
		 
		$statement = $this->adapter->createStatement();
		$group_select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		//echo $group_select->getSqlString();exit;
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();
	
	}
	public function is_member($user_id,$group_id){
		$select = new Select;
		$select->from('y2m_user_group')			    	 	    
			   ->where(array("y2m_user_group.user_group_group_id"=>$group_id))
			   ->where(array("y2m_user_group.user_group_user_id"=>$user_id));
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		$row =  $resultSet->current();
		if(!empty($row)&&isset($row->user_group_id)&&$row->user_group_id!=''){
			return true;
		}else{return false;}
	}
}