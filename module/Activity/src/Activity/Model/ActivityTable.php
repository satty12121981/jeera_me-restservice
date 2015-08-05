<?php
####################Activity Table Model #################################

#########################################################################
namespace Activity\Model;
use Zend\Db\Sql\Select , \Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class ActivityTable extends AbstractTableGateway
{
    protected $table = 'y2m_group_activity'; 	
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Activity());
        $this->initialize();
    }	
	public function fetchAll($where=Null, $order=Null, $limit=Null, $offset=Null){ 	  
		$resultSet = $this->select(function (Select $select) use ($where, $order, $limit, $offset) {		 
			$select->join('y2m_user', 'y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id', array('user_id','user_given_name', 'user_first_name', 'user_middle_name', 'user_last_name', 'user_email'));				
			$select->join('y2m_photo', 'y2m_photo.photo_id = y2m_user.user_profile_photo_id', array('photo_name'),$select::JOIN_LEFT);	
			$select->join('y2m_group', 'y2m_group.group_id = y2m_group_activity.group_activity_group_id', array('group_title', 'group_seo_title'));		
			$select->join(array('p' => 'y2m_group'), 'p.group_id = y2m_group.group_parent_group_id', array('parent_group_id' => 'group_id', 'parent_group_title' => 'group_title', 'parent_group_seo_title' => 'group_seo_title'));
			//$select->columns(array('parent_title' => 'group_title'));	
			if($where){	$select->where($where); }
			if($order){ $select->order($order); }
			if($limit){ $select->limit($limit); }
			if($offset){ $select->offset($offset);  }		
			//echo $select->getSqlString();exit;
		});		 
		return $resultSet;
    }
	public function fetchAll_upcoming($where=Null, $order=Null, $limit=Null, $offset=Null){ 	  
		$current_date = date('Y-m-d H:i:s');	
		$resultSet = $this->select(function (Select $select) use ($where, $order, $limit, $offset,$current_date) {		 
			$select->join('y2m_user', 'y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id', array('user_id','user_given_name', 'user_first_name', 'user_middle_name', 'user_last_name', 'user_email'));				
			$select->join('y2m_photo', 'y2m_photo.photo_id = y2m_user.user_profile_photo_id', array('photo_name'),$select::JOIN_LEFT);	
			$select->join('y2m_group', 'y2m_group.group_id = y2m_group_activity.group_activity_group_id', array('group_title', 'group_seo_title'));		
			$select->join(array('p' => 'y2m_group'), 'p.group_id = y2m_group.group_parent_group_id', array('parent_group_id' => 'group_id', 'parent_group_title' => 'group_title', 'parent_group_seo_title' => 'group_seo_title'));
			//$select->join('y2m_group_activity_invite', 'y2m_group_activity_invite.group_activity_invite_activity_id = y2m_group_activity.group_activity_id', array('group_activity_invite_sender_user_id', 'group_activity_invite_receiver_user_id'));	

			//$select->columns(array('parent_title' => 'group_title'));	
			$select->where->greaterThan('y2m_group_activity.group_activity_start_timestamp' , $current_date);
			if($where){	$select->where($where); }
			if($order){ $select->order($order); }
			if($limit){ $select->limit($limit); }
			if($offset){ $select->offset($offset); }
			//echo $select->getSqlString();exit;
		});		 
		return $resultSet;
    }
	public function fetchAll_past($where=Null, $order=Null, $limit=Null, $offset=Null){ 
		$current_date = date('Y-m-d H:i:s');	
		$resultSet = $this->select(function (Select $select) use ($where, $order, $limit, $offset,$current_date) {		 
			$select->join('y2m_user', 'y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id', array('user_id','user_given_name', 'user_first_name', 'user_middle_name', 'user_last_name', 'user_email'));				
			$select->join('y2m_photo', 'y2m_photo.photo_id = y2m_user.user_profile_photo_id', array('photo_name'),$select::JOIN_LEFT);	
			$select->join('y2m_group', 'y2m_group.group_id = y2m_group_activity.group_activity_group_id', array('group_title', 'group_seo_title'));		
			$select->join(array('p' => 'y2m_group'), 'p.group_id = y2m_group.group_parent_group_id', array('parent_group_id' => 'group_id', 'parent_group_title' => 'group_title', 'parent_group_seo_title' => 'group_seo_title'));
			//$select->columns(array('parent_title' => 'group_title'));	
			$select->where->lessThan('y2m_group_activity.group_activity_start_timestamp' , $current_date);
			if($where){	$select->where($where); }
			if($order){ $select->order($order); }
			if($limit){ $select->limit($limit); }
			if($offset){ $select->offset($offset);  }		
			//echo $select->getSqlString();exit;
		});		 
		return $resultSet;
    }	
	//=====================Admin Actions==========================	
	public function Admin_get_activity($activity_id){ 	
	    $activity_id  = (int) $activity_id;
	    $resultSet = $this->select(function (Select $select) use ($activity_id) {
            $select
                ->join('y2m_user','y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id', array('user_id','user_given_name'))
                ->join('y2m_group','y2m_group.group_id = y2m_group_activity.group_activity_group_id', array('group_id','group_title','group_seo_title'))
				->join(array('p' => 'y2m_group'), 'p.group_id = y2m_group.group_parent_group_id', array('parent_group_id' => 'group_id', 'parent_group_title' => 'group_title', 'parent_group_seo_title' => 'group_seo_title'))
                ->where('group_activity_id = '.$activity_id);
        });
        $result = $resultSet->current();	
	    return $result;
	}	
	public function Admin_block_activity($activity_id){
	   $activity_id  = (int) $activity_id;
       $data = array('group_activity_status' => 2);
       $result = '';	   
	   if($this->update($data, array('group_activity_id' => $activity_id)))
	   {
	    $result = 'success';
	   }else{
		$result = 'fail';
	   }
	   return $result;	   
	}	
	public function Admin_unblock_activity($activity_id){ 
       $data = array('group_activity_status' => 1);
       $result = '';	   
	   if($this->update($data, array('group_activity_id' => $activity_id)))
	   {
	    $result = 'success';
	   }else{
		$result = 'fail';
	   }
	   return $result;
	}	
	//=====================Admin Actions========================
	#this will fetch activity details based primary key. Group Activity Id
    public function getActivity($group_activity_id){
        $group_activity_id  = (int) $group_activity_id;
        $rowset = $this->select(array('group_activity_id' => $group_activity_id));
        $row = $rowset->current();
        return $row;
    }	
	//this will fetch the activities of a Galaxy
	public function getGroupAllActivity($group_id){        
       	$group_id = (int) $group_id; 
		$resultSet = $this->select(function (Select $select) use ($group_id) {
			$select->where(array('group_activity_group_id', $group_id));
			$select->order('group_activity_added_timestamp DESC');
		});			 	
		return $resultSet;	 
    }
	
	//this function will be used for calender for fetching activities of User register
	public function getUserJoinedActivityForCalender($year, $month, $user_id){
		$year = (int) $year;
		$month = (int) $month;
		$user_id = (int) $user_id;		
		$startDate ='';
		$endDate ='';	#it will be 30 days after the start date		
		#create start date
		$date = new \DateTime($year.'-'.$month.'-01');
		$startDate =$date->format('Y-m-d H:i:s');		
		#create end date
		$date = new \DateTime($year.'-'.$month.'-01');
		$date->add(new \DateInterval('P1M'));#it will make make next mont date
		$endDate =$date->format('Y-m-d  H:i:s');		
		$year = (int) $year; 
		$month = (int) $month; 
		$user_id = (int) $user_id; 
		$predicate = new  \Zend\Db\Sql\Where();
		$resultSet = $this->select(function (Select $select) use ($year, $month, $user_id, $predicate, $startDate, $endDate ) {
			$select->join('y2m_group_activity_rsvp', 'y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id', array('group_activity_rsvp_user_id'));
			$select->join('y2m_group', 'y2m_group.group_id = y2m_group_activity_rsvp.group_activity_rsvp_group_id', array('group_id'));
			$select->join('y2m_user', 'y2m_user.user_id = y2m_group_activity_rsvp.group_activity_rsvp_user_id', array('user_id'));
			$select->where(array($predicate->between('group_parent_group_id' , "$startDate", "$endDate"), 'y2m_group_activity_rsvp.group_activity_rsvp_user_id', $user_id));
			$select->order('group_activity_added_timestamp DESC');
		});		 
		$eventDispayInCalender = array();
		$i=0;
			foreach($resultSet as $row) {
				// echo "<li>".$timestamp = mktime(0,0,0,$cMonth,1,$cYear);
				$tmpArray =array();	//This variable will hold the variables of Date format comming from db for Events
				$tmpArray = explode(" ", $row['group_activity_start_timestamp']);	#seperate date and time
				#we dont need date. Now we will seperate date, time and year
				$tmpArrayDate =array();	
				$tmpArrayDate =explode("-", $tmpArray[0]);		
				$eventDispayInCalender[$i]['date'] = $tmpArrayDate[2];
				$eventDispayInCalender[$i]['month'] = $tmpArrayDate[1];
				$eventDispayInCalender[$i]['year'] = $tmpArrayDate[0];
				$eventDispayInCalender[$i]['even_data'] = $row;					
				$i++;
				//print_r($row);
			}	
			return $eventDispayInCalender;
    }
	// this function will save activity in database
    public function saveActivity(Activity $activity){
		
	   $data = array(
            'group_activity_content' => $activity->group_activity_content,
            'group_activity_owner_user_id'  => $activity->group_activity_owner_user_id,
			'group_activity_group_id'  => $activity->group_activity_group_id,
			'group_activity_status'  => $activity->group_activity_status,
			'group_activity_type'  => $activity->group_activity_type,
			'group_activity_added_timestamp'  => $activity->group_activity_added_timestamp,
			'group_activity_added_ip_address'  => $activity->group_activity_added_ip_address,	
			'group_activity_start_timestamp'  => $activity->group_activity_start_timestamp,		
			'group_activity_title'  => $activity->group_activity_title,		
			'group_activity_location'  => $activity->group_activity_location,		
			'group_activity_modifed_timestamp'  => $activity->group_activity_modifed_timestamp,		
			'group_activity_modified_ip_address'  => $activity->group_activity_modified_ip_address			
        );
        $group_activity_id = (int)$activity->group_activity_id;
        if ($group_activity_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getActivity($group_activity_id)) {
                $this->update($data, array('group_activity_id' => $group_activity_id));
				return $group_activity_id;
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
	#this function will delete activity in database
    public function deleteActivity($group_activity_id){   
		$result = "";
        if($this->delete(array('group_activity_id' => $group_activity_id)))
		{
		$result = 'success';
	    }else{
		$result = 'fail';
	    }
	    return $result;
    }
	public function getAllActivityWithLikes($GroupId,$SystemTypeId,$LikeUserId,$offset=0){ 
		$GroupId = (int) $GroupId; 
		$SystemTypeId  = (int) $SystemTypeId;
		$LikeUserId  = (int) $LikeUserId;
		$subselect = new Select;
		$adapter = $this->getAdapter();
		$platform = $adapter->getPlatform();
		$quoteId = $platform->quoteIdentifier($LikeUserId);
		$subselect->from('y2m_group_activity')
			->columns(array(new Expression('COUNT(y2m_like.like_id) as likes_count'),'group_activity_id'=>'group_activity_id'))
			->join('y2m_like', 'y2m_like.like_refer_id = y2m_group_activity.group_activity_id',array())
			->join('y2m_system_type', 'y2m_system_type.system_type_id = y2m_like.like_system_type_id',array())
			->where(array('y2m_group_activity.group_activity_group_id' => $GroupId,'y2m_like.like_system_type_id' => $SystemTypeId))
			->group(array('y2m_group_activity.group_activity_id'))
			->order(array('y2m_group_activity.group_activity_added_timestamp ASC'));
		//echo $subselect->getSqlString();die();
		$commentscount_subselect = new Select;
		$commentscount_subselect->from('y2m_group_activity')
			->columns(array(new Expression('COUNT(y2m_comment.comment_id) as comments_count'),'group_activity_id'=>'group_activity_id'))
			->join('y2m_comment', 'y2m_comment.comment_refer_id = y2m_group_activity.group_activity_id',array())
			->join('y2m_system_type', 'y2m_system_type.system_type_id = y2m_comment.comment_system_type_id',array())
			->where(array('y2m_group_activity.group_activity_group_id' => $GroupId,'y2m_comment.comment_system_type_id' => $SystemTypeId))
			->group(array('y2m_group_activity.group_activity_id'))
			->order(array('y2m_group_activity.group_activity_added_timestamp ASC'));
							
		//sub query to check user exist for the specific discussion of the planet liked
		$alias_subselect = new Select;
		$alias_subselect->from('y2m_group_activity')
			->columns(array('group_activity_id'=>'group_activity_id'))
			->join('y2m_like', 'y2m_like.like_refer_id = y2m_group_activity.group_activity_id',array())
			->join('y2m_system_type', 'y2m_system_type.system_type_id = y2m_like.like_system_type_id',array())
			->join('y2m_user', 'y2m_user.user_id = y2m_like.like_by_user_id',array())
			->where(array('y2m_group_activity.group_activity_id' => new Expression('`final`.`group_activity_id`'),'y2m_user.user_id' => $LikeUserId));
		$expression = new Expression(
		  "IF ( EXISTS(" . @$alias_subselect->getSqlString($adapter->getPlatform()) . ") , `final`.`group_activity_id`, 0)"
		);
		
		//sub query to check user spammed for the specific discussion of the planet
		$alias_subspamselect = new Select;
		$alias_subspamselect->from('y2m_group_activity')
			->columns(array('group_activity_id'=>'group_activity_id'))
			->join('y2m_spam', 'y2m_spam.spam_refer_id = y2m_group_activity.group_activity_id',array())
			->join('y2m_system_type', 'y2m_system_type.system_type_id = y2m_spam.spam_system_type_id',array())
			->join('y2m_problem', 'y2m_problem.problem_id = y2m_spam.spam_problem_id',array())
			->join('y2m_user', 'y2m_user.user_id = y2m_spam.spam_report_user_id',array())
			->where(array('y2m_group_activity.group_activity_id' => new Expression('`final`.`group_activity_id`'),'y2m_user.user_id' => $LikeUserId));
		//user query to select picture
		$alias_subuserprofileselect = new Select;
		$alias_subuserprofileselect->from('y2m_group_activity')
			->columns(array('group_activity_id'=>'group_activity_id','user_id' => new Expression('`y2m_user`.`user_id`'),'user_given_name' => new Expression('`y2m_user`.`user_given_name`'),'photo_location'=> new Expression('`y2m_photo`.`photo_location`'),'photo_name'=> new Expression('`y2m_photo`.`photo_name`')))
			->join('y2m_user', 'y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id',array(),'left')
			->join('y2m_photo', 'y2m_photo.photo_id = y2m_user.user_profile_photo_id',array(),'left')
			->where(array('y2m_group_activity.group_activity_group_id' => $GroupId))
			->group(array('y2m_group_activity.group_activity_id'))
			->order(array('y2m_group_activity.group_activity_added_timestamp ASC'));
		
		$expression1 = new Expression(
		  "IF ( EXISTS(" . @$alias_subspamselect->getSqlString($adapter->getPlatform()) . ") , `final`.`group_activity_id`, 0)"
		);
		//main query
		$current_date = date('Y-m-d H:i:s');
		$mainSelect = new Select;
		$mainSelect->from('y2m_group_activity')
			->join(array('temp' => $subselect), 'temp.group_activity_id = y2m_group_activity.group_activity_id',array('likes_count',"user_check"=>$expression,"spam_user_check"=>$expression1),'left')
			->join(array('temp_comment' => $commentscount_subselect), 'temp_comment.group_activity_id = y2m_group_activity.group_activity_id',array('comments_count'),'left')
			->join(array('temp1' => $alias_subuserprofileselect), 'temp1.group_activity_id = y2m_group_activity.group_activity_id',array('photo_location','photo_name','user_id','user_given_name'),'left')
			->join(array('final'=>'y2m_group_activity'), 'final.group_activity_id = y2m_group_activity.group_activity_id','group_activity_id','left')
			->columns(array('group_activity_content'=>'group_activity_content','group_activity_owner_user_id'=>'group_activity_owner_user_id','group_activity_title'=>'group_activity_title','group_activity_location'=>'group_activity_location','group_activity_start_timestamp'=>'group_activity_start_timestamp'))
            ->where(array('final.group_activity_group_id' => $GroupId))
			->where->lessThan('y2m_group_activity.group_activity_start_timestamp' , $current_date);	   
		$statement = $this->adapter->createStatement();
		$mainSelect->order(array('y2m_group_activity.group_activity_start_timestamp DESC'));
		$mainSelect->limit(10);
		$mainSelect->offset($offset);
		$mainSelect->prepareStatement($this->adapter, $statement);
	//echo $mainSelect->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
		
		return $resultSet;	
	}
	public function fetchAll_upcomingWithLikes($GroupId,$SystemTypeId,$LikeUserId,$offset=0){
		$GroupId = (int) $GroupId; 
		$SystemTypeId  = (int) $SystemTypeId;
		$LikeUserId  = (int) $LikeUserId;
		$subselect = new Select;
		$adapter = $this->getAdapter();
		$platform = $adapter->getPlatform();
		$quoteId = $platform->quoteIdentifier($LikeUserId);
		$subselect->from('y2m_group_activity')
			->columns(array(new Expression('COUNT(y2m_like.like_id) as likes_count'),'group_activity_id'=>'group_activity_id'))
			->join('y2m_like', 'y2m_like.like_refer_id = y2m_group_activity.group_activity_id',array())
			->join('y2m_system_type', 'y2m_system_type.system_type_id = y2m_like.like_system_type_id',array())
			->where(array('y2m_group_activity.group_activity_group_id' => $GroupId,'y2m_like.like_system_type_id' => $SystemTypeId))
			->group(array('y2m_group_activity.group_activity_id'))
			->order(array('y2m_group_activity.group_activity_added_timestamp ASC'));
		//echo $subselect->getSqlString();die();
		$commentscount_subselect = new Select;
		$commentscount_subselect->from('y2m_group_activity')
			->columns(array(new Expression('COUNT(y2m_comment.comment_id) as comments_count'),'group_activity_id'=>'group_activity_id'))
			->join('y2m_comment', 'y2m_comment.comment_refer_id = y2m_group_activity.group_activity_id',array())
			->join('y2m_system_type', 'y2m_system_type.system_type_id = y2m_comment.comment_system_type_id',array())
			->where(array('y2m_group_activity.group_activity_group_id' => $GroupId,'y2m_comment.comment_system_type_id' => $SystemTypeId))
			->group(array('y2m_group_activity.group_activity_id'))
			->order(array('y2m_group_activity.group_activity_added_timestamp ASC'));
							
		//sub query to check user exist for the specific discussion of the planet liked
		$alias_subselect = new Select;
		$alias_subselect->from('y2m_group_activity')
			->columns(array('group_activity_id'=>'group_activity_id'))
			->join('y2m_like', 'y2m_like.like_refer_id = y2m_group_activity.group_activity_id',array())
			->join('y2m_system_type', 'y2m_system_type.system_type_id = y2m_like.like_system_type_id',array())
			->join('y2m_user', 'y2m_user.user_id = y2m_like.like_by_user_id',array())
			->where(array('y2m_group_activity.group_activity_id' => new Expression('`final`.`group_activity_id`'),'y2m_user.user_id' => $LikeUserId));
		$expression = new Expression(
		  "IF ( EXISTS(" . @$alias_subselect->getSqlString($adapter->getPlatform()) . ") , `final`.`group_activity_id`, 0)"
		);
		
		//sub query to check user spammed for the specific discussion of the planet
		$alias_subspamselect = new Select;
		$alias_subspamselect->from('y2m_group_activity')
			->columns(array('group_activity_id'=>'group_activity_id'))
			->join('y2m_spam', 'y2m_spam.spam_refer_id = y2m_group_activity.group_activity_id',array())
			->join('y2m_system_type', 'y2m_system_type.system_type_id = y2m_spam.spam_system_type_id',array())
			->join('y2m_problem', 'y2m_problem.problem_id = y2m_spam.spam_problem_id',array())
			->join('y2m_user', 'y2m_user.user_id = y2m_spam.spam_report_user_id',array())
			->where(array('y2m_group_activity.group_activity_id' => new Expression('`final`.`group_activity_id`'),'y2m_user.user_id' => $LikeUserId));
		//user query to select picture
		$alias_subuserprofileselect = new Select;
		$alias_subuserprofileselect->from('y2m_group_activity')
			->columns(array('group_activity_id'=>'group_activity_id','user_id' => new Expression('`y2m_user`.`user_id`'),'user_given_name' => new Expression('`y2m_user`.`user_given_name`'),'photo_location'=> new Expression('`y2m_photo`.`photo_location`'),'photo_name'=> new Expression('`y2m_photo`.`photo_name`')))
			->join('y2m_user', 'y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id',array(),'left')
			->join('y2m_photo', 'y2m_photo.photo_id = y2m_user.user_profile_photo_id',array(),'left')
			->where(array('y2m_group_activity.group_activity_group_id' => $GroupId))
			->group(array('y2m_group_activity.group_activity_id'))
			->order(array('y2m_group_activity.group_activity_added_timestamp ASC'));
		
		$expression1 = new Expression(
		  "IF ( EXISTS(" . @$alias_subspamselect->getSqlString($adapter->getPlatform()) . ") , `final`.`group_activity_id`, 0)"
		);
		//main query
		$current_date = date('Y-m-d H:i:s');
		$mainSelect = new Select;
		$mainSelect->from('y2m_group_activity')
			->join(array('temp' => $subselect), 'temp.group_activity_id = y2m_group_activity.group_activity_id',array('likes_count',"user_check"=>$expression,"spam_user_check"=>$expression1),'left')
			->join(array('temp_comment' => $commentscount_subselect), 'temp_comment.group_activity_id = y2m_group_activity.group_activity_id',array('comments_count'),'left')
			->join(array('temp1' => $alias_subuserprofileselect), 'temp1.group_activity_id = y2m_group_activity.group_activity_id',array('photo_location','photo_name','user_id','user_given_name'),'left')
			->join(array('final'=>'y2m_group_activity'), 'final.group_activity_id = y2m_group_activity.group_activity_id','group_activity_id','left')
			->columns(array('group_activity_content'=>'group_activity_content','group_activity_owner_user_id'=>'group_activity_owner_user_id','group_activity_title'=>'group_activity_title','group_activity_location'=>'group_activity_location','group_activity_start_timestamp'=>'group_activity_start_timestamp'))
            ->where(array('final.group_activity_group_id' => $GroupId))			
			->where->greaterThan('y2m_group_activity.group_activity_start_timestamp' , $current_date)			 
			;
			$mainSelect->order(array('y2m_group_activity.group_activity_start_timestamp DESC'));
			$mainSelect->limit(10);
			$mainSelect->offset($offset);
			 
		$statement = $this->adapter->createStatement();
		
		$mainSelect->prepareStatement($this->adapter, $statement);
	//echo $mainSelect->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
		
		return $resultSet;	
	}
	public function getAllUpcomingActivityWithUserCount($user_id,$offset,$limit){
		$select = new Select;
		$select->from('y2m_group_activity')
			   ->columns(array(new Expression('COUNT(y2m_group_activity_rsvp.group_activity_rsvp_id) as member_count'),'group_activity_title'=>'group_activity_title','group_activity_location'=>'group_activity_location','group_activity_content'=>'group_activity_content','group_activity_start_timestamp'=>'group_activity_start_timestamp','group_activity_id'=>'group_activity_id','is_member'=>new Expression('IF(EXISTS(SELECT * FROM y2m_group_activity_rsvp WHERE y2m_group_activity_rsvp.group_activity_rsvp_user_id = '.$user_id.' AND y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id),1,0)')))
			   ->join(array('y2m_group_activity_rsvp'=>'y2m_group_activity_rsvp'),'y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id',array(),'left')
			   ->join(array('y2m_user'=>'y2m_user'),'y2m_group_activity.group_activity_owner_user_id = y2m_user.user_id',array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'),'left')
			   ->join("y2m_group","y2m_group.group_id = y2m_group_activity.group_activity_group_id",array('group_seo_title'))
			   ->join(array("parent"=>"y2m_group"),"parent.group_id = y2m_group.group_parent_group_id",array('parent_seo_title'=>'group_seo_title'))
			   ->join(array('y2m_user_profile_photo'=>'y2m_user_profile_photo'),'y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'=>'profile_photo'),'left');
		$select->where('y2m_group_activity.group_activity_start_timestamp>now()');
		$select->group('y2m_group_activity.group_activity_id');
		$select->order(array('member_count DESC'));
		$select->order(array('y2m_group_activity.group_activity_start_timestamp DESC'));
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet;	
	}
	public function getEventCalendarJason($user_id){
		$select = new Select;
		$select->from('y2m_group_activity')
			   ->columns(array("group_activity_title"=>"group_activity_title","group_activity_start_timestamp"=>"group_activity_start_timestamp",'group_activity_id'=>'group_activity_id'))
			   ->join("y2m_group_activity_rsvp","y2m_group_activity.group_activity_id = y2m_group_activity_rsvp.group_activity_rsvp_activity_id",array())
			   ->join("y2m_group","y2m_group.group_id = y2m_group_activity.group_activity_group_id",array('group_seo_title'))
			   ->join(array("parent"=>"y2m_group"),"parent.group_id = y2m_group.group_parent_group_id",array('parent_seo_title'=>'group_seo_title'))
			   ->where(array("y2m_group_activity_rsvp.group_activity_rsvp_user_id"=>$user_id));
		$select->order(array('y2m_group_activity.group_activity_start_timestamp DESC'));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		$row_array = array();
		$return_arr = array();
		foreach($resultSet as $row) { 
			$year = date('Y',strtotime($row->group_activity_start_timestamp));
			$month = date('m',strtotime($row->group_activity_start_timestamp)); 
			$date = date('d',strtotime($row->group_activity_start_timestamp)); 
			
			$row_array['classEvent'] = $row->group_activity_title;
			$row_array['title'] = $row->group_activity_title;
			$row_array['description'] = $row->group_activity_title;
			$row_array['startDate'] = "$year-$month-$date";
			$row_array['url'] = $row->group_activity_id; 
			$row_array['group_seo_title'] = $row->group_seo_title; 
			$row_array['parent_seo_title'] = $row->parent_seo_title; 
			array_push($return_arr,$row_array);
		}
		
		return $return_arr;	   
	}
	public function getAllUpcomingActivityWithCountofUsersLikeComment($user_id,$planet_id,$offset,$limit){
		$select = new Select;
		$select->from('y2m_group_activity')
			   ->columns(array(new Expression('COUNT(y2m_group_activity_rsvp.group_activity_rsvp_id) as member_count'),'group_activity_title'=>'group_activity_title','group_activity_location'=>'group_activity_location','group_activity_content'=>'group_activity_content','group_activity_start_timestamp'=>'group_activity_start_timestamp','group_activity_id'=>'group_activity_id','group_activity_owner_user_id'=>'group_activity_owner_user_id','is_member'=>new Expression('IF(EXISTS(SELECT * FROM y2m_group_activity_rsvp WHERE y2m_group_activity_rsvp.group_activity_rsvp_user_id = '.$user_id.' AND y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id),1,0)')))
			   ->join(array('y2m_group_activity_rsvp'=>'y2m_group_activity_rsvp'),'y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id',array(),'left')			    
			   ->join(array('y2m_user'=>'y2m_user'),'y2m_group_activity.group_activity_owner_user_id = y2m_user.user_id',array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'),'left')
			   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left');
		$select->where('y2m_group_activity.group_activity_start_timestamp>=now()');
		$select->where(array('y2m_group_activity.group_activity_group_id'=>$planet_id));
		$select->where(array('y2m_group_activity.group_activity_status'=>1));
		$select->group('y2m_group_activity.group_activity_id');		
		$select->order(array('y2m_group_activity.group_activity_start_timestamp DESC'));
		$select->order(array('member_count DESC'));
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer();	
	}
	public function getAllPastActivityWithCountofUsersLikeComment($user_id,$planet_id,$offset,$limit){
		$select = new Select;
		$select->from('y2m_group_activity')
			   ->columns(array(new Expression('COUNT(y2m_group_activity_rsvp.group_activity_rsvp_id) as member_count'),'group_activity_title'=>'group_activity_title','group_activity_location'=>'group_activity_location','group_activity_content'=>'group_activity_content','group_activity_start_timestamp'=>'group_activity_start_timestamp','group_activity_id'=>'group_activity_id','group_activity_owner_user_id'=>'group_activity_owner_user_id','is_member'=>new Expression('IF(EXISTS(SELECT * FROM y2m_group_activity_rsvp WHERE y2m_group_activity_rsvp.group_activity_rsvp_user_id = '.$user_id.' AND y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id),1,0)')))
			   ->join(array('y2m_group_activity_rsvp'=>'y2m_group_activity_rsvp'),'y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id',array(),'left')			    
			   ->join(array('y2m_user'=>'y2m_user'),'y2m_group_activity.group_activity_owner_user_id = y2m_user.user_id',array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'),'left')
			   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left');
		$select->where('y2m_group_activity.group_activity_start_timestamp<now()');
		$select->where(array('y2m_group_activity.group_activity_group_id'=>$planet_id));
		$select->group('y2m_group_activity.group_activity_id');		
		$select->order(array('y2m_group_activity.group_activity_start_timestamp DESC'));
		$select->order(array('member_count DESC'));
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer();	
	}
	public function getAllTodayActivityWithCountofUsersLikeComment($user_id,$planet_id,$date_selected=''){
		$select = new Select;
		$select->from('y2m_group_activity')
			   ->columns(array(new Expression('COUNT(y2m_group_activity_rsvp.group_activity_rsvp_id) as member_count'),'group_activity_title'=>'group_activity_title','group_activity_location'=>'group_activity_location','group_activity_content'=>'group_activity_content','group_activity_start_timestamp'=>'group_activity_start_timestamp','group_activity_id'=>'group_activity_id','is_member'=>new Expression('IF(EXISTS(SELECT * FROM y2m_group_activity_rsvp WHERE y2m_group_activity_rsvp.group_activity_rsvp_user_id = '.$user_id.' AND y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id),1,0)')))
			   ->join(array('y2m_group_activity_rsvp'=>'y2m_group_activity_rsvp'),'y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id',array(),'left')			    
			   ->join(array('y2m_user'=>'y2m_user'),'y2m_group_activity.group_activity_owner_user_id = y2m_user.user_id',array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'),'left')
			   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left');
		if($date_selected == '')
		$select->where('DATE(y2m_group_activity.group_activity_start_timestamp)=CURDATE()');
		else
		$select->where('DATE(y2m_group_activity.group_activity_start_timestamp)="'.$date_selected.'"');
		$select->where(array('y2m_group_activity.group_activity_group_id'=>$planet_id));
		$select->group('y2m_group_activity.group_activity_id');		
		$select->order(array('y2m_group_activity.group_activity_start_timestamp DESC'));
		$select->order(array('member_count DESC'));	 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer();	
	}	
	public function getOneActivityWithMembercount($user_id,$planet_id,$activity_id){
		$select = new Select;
		$select->from('y2m_group_activity')
			   ->columns(array(new Expression('COUNT(y2m_group_activity_rsvp.group_activity_rsvp_id) as member_count'),'group_activity_title'=>'group_activity_title','group_activity_location'=>'group_activity_location','group_activity_owner_user_id'=>'group_activity_owner_user_id','group_activity_status'=>'group_activity_status','group_activity_content'=>'group_activity_content','group_activity_start_timestamp'=>'group_activity_start_timestamp','group_activity_id'=>'group_activity_id','group_activity_type'=>'group_activity_type','is_member'=>new Expression('IF(EXISTS(SELECT * FROM y2m_group_activity_rsvp WHERE y2m_group_activity_rsvp.group_activity_rsvp_user_id = '.$user_id.' AND y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id),1,0)')))
			   ->join(array('y2m_group_activity_rsvp'=>'y2m_group_activity_rsvp'),'y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id',array(),'left')			    
			   ->join(array('y2m_user'=>'y2m_user'),'y2m_group_activity.group_activity_owner_user_id = y2m_user.user_id',array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'),'left')
			    ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left');	 
		$select->where(array('y2m_group_activity.group_activity_group_id'=>$planet_id));
		$select->where(array('y2m_group_activity.group_activity_id'=>$activity_id)); 	
		$select->group('y2m_group_activity.group_activity_id');		
		  
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();	
	}
	public function getOneActivityWithMembercountWithoutPlanetid($user_id,$activity_id){
		$select = new Select;
		$select->from('y2m_group_activity')
			   ->columns(array(new Expression('COUNT(y2m_group_activity_rsvp.group_activity_rsvp_id) as member_count'),'group_activity_title'=>'group_activity_title','group_activity_location'=>'group_activity_location','group_activity_owner_user_id'=>'group_activity_owner_user_id','group_activity_status'=>'group_activity_status','group_activity_content'=>'group_activity_content','group_activity_start_timestamp'=>'group_activity_start_timestamp','group_activity_id'=>'group_activity_id','group_activity_type'=>'group_activity_type','group_activity_group_id'=>'group_activity_group_id','is_member'=>new Expression('IF(EXISTS(SELECT * FROM y2m_group_activity_rsvp WHERE y2m_group_activity_rsvp.group_activity_rsvp_user_id = '.$user_id.' AND y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id),1,0)')))
			   ->join(array('y2m_group_activity_rsvp'=>'y2m_group_activity_rsvp'),'y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id',array(),'left')			    
			   ->join(array('y2m_user'=>'y2m_user'),'y2m_group_activity.group_activity_owner_user_id = y2m_user.user_id',array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'),'left')
			    ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left');	 		 
		$select->where(array('y2m_group_activity.group_activity_id'=>$activity_id)); 	
		$select->group('y2m_group_activity.group_activity_id');		
		  
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();	
	}
	public function getActivityRequests($group_id){
		$select = new Select;
		$select->from('y2m_group_activity')
			   ->columns(array("group_activity_id"=>"group_activity_id","group_activity_content"=>"group_activity_content","group_activity_title"=>"group_activity_title","group_activity_location"=>"group_activity_location","group_activity_start_timestamp"=>"group_activity_start_timestamp"))			    
			   ->join(array('y2m_user'=>'y2m_user'),'y2m_group_activity.group_activity_owner_user_id = y2m_user.user_id',array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'),'left')
			  ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left');
		$select->where('y2m_group_activity.group_activity_start_timestamp>now()');
		$select->where(array('y2m_group_activity.group_activity_group_id'=>$group_id,"group_activity_status"=>0));
		$select->group('y2m_group_activity.group_activity_id');		 
		$select->order(array('y2m_group_activity.group_activity_start_timestamp DESC'));		 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer();	
	}
	public function checkRequestExist($planet_id,$activity_id){
		$select = new Select;
		$select->from('y2m_group_activity');			   
		$select->where(array('y2m_group_activity.group_activity_id'=>$activity_id));
		$select->where(array('y2m_group_activity.group_activity_group_id'=>$planet_id,"group_activity_status"=>0));
	 	 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();	
	}
	public function makeActivityActive($activity_id){
		$data['group_activity_status'] = 1;
		return $this->update($data, array('group_activity_id' => $activity_id));
	}
	public function makeActivityIgnore($activity_id){
		$data['group_activity_status'] = 2;
		return $this->update($data, array('group_activity_id' => $activity_id));
	}
	public function getCountOfAllActivities($planet_id){
		$select = new Select;
		$select->from('y2m_group_activity')
				->columns(array(new Expression('COUNT(y2m_group_activity.group_activity_id) as activity_count')));
		$select->where(array('y2m_group_activity.group_activity_group_id'=>$planet_id));
	 	 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute()); 
		return $resultSet->current()->activity_count;
	}
	public function RemoveAllGroupActivitiesAndRelatedInfos($group_id){
		$sql = "DELETE FROM  y2m_like WHERE like_system_type_id = 3 AND like_refer_id IN (SELECT  comment_id FROM  y2m_comment INNER JOIN  y2m_group_activity ON y2m_group_activity.group_activity_id = y2m_comment.comment_refer_id AND y2m_comment.comment_system_type_id = 1 INNER JOIN  y2m_group ON y2m_group.group_id = y2m_group_activity.group_activity_group_id  WHERE y2m_group.group_id = ".$group_id.")";	
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		$sql = "DELETE FROM y2m_like WHERE like_system_type_id = 1 AND like_refer_id IN (SELECT  group_activity_id FROM  y2m_group_activity  INNER JOIN  y2m_group ON y2m_group.group_id = y2m_group_activity.group_activity_group_id  WHERE y2m_group.group_id = ".$group_id.")";	 	 
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		$sql = "DELETE FROM y2m_comment WHERE comment_system_type_id = 1 AND comment_refer_id IN (SELECT  group_activity_id FROM  y2m_group_activity  INNER JOIN  y2m_group ON y2m_group.group_id = y2m_group_activity.group_activity_group_id  WHERE y2m_group.group_id = ".$group_id.")";	 
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		$sql = "DELETE FROM y2m_group_activity_invite WHERE  group_activity_invite_activity_id IN (SELECT  group_activity_id FROM  y2m_group_activity  INNER JOIN  y2m_group ON y2m_group.group_id = y2m_group_activity.group_activity_group_id  WHERE y2m_group.group_id = ".$group_id.")";	 
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		$sql = "DELETE FROM  y2m_group_activity_rsvp WHERE  group_activity_rsvp_activity_id IN (SELECT  group_activity_id FROM  y2m_group_activity  INNER JOIN  y2m_group ON y2m_group.group_id = y2m_group_activity.group_activity_group_id  WHERE y2m_group.group_id = ".$group_id.")";	 
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		$sql = "DELETE FROM  y2m_activity_tag WHERE  activity_id IN (SELECT  group_activity_id FROM  y2m_group_activity  INNER JOIN  y2m_group ON y2m_group.group_id = y2m_group_activity.group_activity_group_id  WHERE y2m_group.group_id = ".$group_id.")";	 
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		$sql = "DELETE  FROM y2m_group_activity WHERE y2m_group_activity.group_activity_group_id = ".$group_id;	 
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		return true;
	}
	public function getAllPlanetActivities($planet_id,$limit,$offset,$field="group_activity_id",$order='ASC',$search=''){
		$select = new Select;
		$select->from('y2m_group_activity')
				->columns(array("group_activity_id","group_activity_content","group_activity_title","group_activity_location","group_activity_status","group_activity_start_timestamp"))
				->join("y2m_group","y2m_group.group_id = y2m_group_activity.group_activity_group_id",array("group_title","group_seo_title","group_id"))
				->join("y2m_user","y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id",array("user_given_name","user_first_name","user_last_name","user_id"));
		$select->where(array('y2m_group_activity.group_activity_group_id'=>$planet_id));
	 	$select->limit($limit);
		$select->offset($offset);
		$select->order($field.' '.$order);
		if($search!=''){
				$select->where->like('y2m_group_activity.group_activity_title',$search.'%')->or->like('y2m_group_activity.group_activity_content',$search.'%')->or->like('y2m_group_activity.group_activity_location',$search.'%')->or->like('y2m_user.user_first_name',$search.'%');				 	
			}	
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute()); 
		return $resultSet->buffer();
	}
	public function getCountOfPlanetActivities($planet_id,$search=''){
		$select = new Select;
		$select->from('y2m_group_activity')
				->columns(array(new Expression('COUNT(y2m_group_activity.group_activity_id) as activity_count')))
				->join("y2m_group","y2m_group.group_id = y2m_group_activity.group_activity_group_id",array("group_title","group_seo_title","group_id"))
				->join("y2m_user","y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id",array("user_given_name","user_first_name","user_last_name","user_id"));
		$select->where(array('y2m_group_activity.group_activity_group_id'=>$planet_id));
	 	 if($search!=''){
				$select->where->like('y2m_group_activity.group_activity_title',$search.'%')->or->like('y2m_group_activity.group_activity_content',$search.'%')->or->like('y2m_group_activity.group_activity_location',$search.'%')->or->like('y2m_user.user_first_name',$search.'%');				 	
			}	
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute()); 
		return $resultSet->current()->activity_count;
	}
	public function updateActivity($activity_id,$data){		 
		return $this->update($data, array('group_activity_id' => $activity_id));
	}
	public function removeActivityLikesaAndComments($activity_id){
		$sql = "DELETE FROM  y2m_like WHERE like_system_type_id = 3 AND like_refer_id IN (SELECT  comment_id FROM  y2m_comment WHERE y2m_comment.comment_system_type_id = 1 AND y2m_comment.comment_refer_id = ".$activity_id.")";	
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		$sql = "DELETE FROM y2m_comment WHERE comment_system_type_id = 1 AND comment_refer_id =".$activity_id;	
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();
		$sql = "DELETE FROM y2m_like WHERE like_system_type_id = 1 AND like_refer_id =".$activity_id;	 
		$statement = $this->adapter-> query($sql); 
		$statement -> execute();	 
		return true;
	}
	public function createActivity(Activity $activity){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) { $ip = $_SERVER['HTTP_CLIENT_IP'];} 
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }
		else {   $ip = $_SERVER['REMOTE_ADDR'];}
		$data = array(
            'group_activity_content' => $activity->group_activity_content,
            'group_activity_owner_user_id'  => $activity->group_activity_owner_user_id,
			'group_activity_group_id'  => $activity->group_activity_group_id,
			'group_activity_status'  => $activity->group_activity_status,
			'group_activity_type'  => $activity->group_activity_type,
			'group_activity_added_ip_address'  => $ip,	
			'group_activity_start_timestamp'  => $activity->group_activity_start_timestamp,		
			'group_activity_title'  => $activity->group_activity_title,		
			'group_activity_location'  => $activity->group_activity_location,
			'group_activity_location_lat'  => $activity->group_activity_location_lat,
			'group_activity_location_lng'  => $activity->group_activity_location_lng,			
			'group_activity_modified_ip_address'  =>$ip,
			'group_activity_added_timestamp'=>date("Y-m-d H:i:s"),
        );       
        $this->insert($data);
		return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();     
	}
	public function getActivityForFeed($activity_id,$user_id){
		$select = new Select;
		$sub_select = new Select;
		$sub_select2 = new Select;
		$sub_select->from('y2m_group_activity')
				   ->columns(array(new Expression('COUNT(y2m_group_activity.group_activity_id) as rsvp_count'),"group_activity_id"))
				   ->join(array('y2m_group_activity_rsvp'=>'y2m_group_activity_rsvp'),'y2m_group_activity.group_activity_id = y2m_group_activity_rsvp.group_activity_rsvp_activity_id',array());
		$sub_select->group('y2m_group_activity.group_activity_id');
		$select_friend1 = new Select;
		$select_friend1->from('y2m_user_friend')
				   ->columns(array("user_friend_sender_user_id"))
				   ->where(array("user_friend_friend_user_id =".$user_id));
		$select_friend2 = new Select;
		$select_friend2->from('y2m_user_friend')
				   ->columns(array("user_friend_friend_user_id"))
				   ->where(array("user_friend_sender_user_id=".$user_id));
		$sub_select2->from('y2m_group_activity')
				   ->columns(array(new Expression('COUNT(y2m_group_activity.group_activity_id) as friend_count'),"group_activity_id"))
				   ->join(array('y2m_group_activity_rsvp'=>'y2m_group_activity_rsvp'),'y2m_group_activity.group_activity_id = y2m_group_activity_rsvp.group_activity_rsvp_activity_id',array())
				   ->where->in("group_activity_rsvp_user_id",$select_friend1)->or->in("group_activity_rsvp_user_id",$select_friend2);
		$sub_select2->group('y2m_group_activity.group_activity_id');		
		$select->from('y2m_group_activity')
				->columns(array("group_activity_id","group_activity_content","group_activity_title","group_activity_start_timestamp","group_activity_location","group_activity_location_lat","group_activity_location_lng",'is_going'=>new Expression('IF(EXISTS(SELECT * FROM y2m_group_activity_rsvp WHERE y2m_group_activity_rsvp.group_activity_rsvp_user_id = '.$user_id.' AND y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id),1,0)'),))
				->join(array('temp_member' => $sub_select), 'temp_member.group_activity_id = y2m_group_activity.group_activity_id',array('rsvp_count'),'left')
				->join(array('temp_friends' => $sub_select2), 'temp_friends.group_activity_id = y2m_group_activity.group_activity_id',array('friend_count'),'left')
				->where(array("y2m_group_activity.group_activity_id"=>$activity_id)); 			 	
		 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute()); 
		return $resultSet->current();
	}
	public function getActivityForMap($user_id){
		$select = new Select;
		$select->from('y2m_group_activity')
				->columns(array("group_activity_id","group_activity_content","group_activity_title","group_activity_start_timestamp","group_activity_location","group_activity_location_lat","group_activity_location_lng",'is_going'=>new Expression('IF(EXISTS(SELECT * FROM y2m_group_activity_rsvp WHERE y2m_group_activity_rsvp.group_activity_rsvp_user_id = '.$user_id.' AND y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id),1,0)'),))
				->join("y2m_group","y2m_group.group_id = y2m_group_activity.group_activity_group_id",array("group_title","group_seo_title","group_id"))
				->where(array("y2m_group.group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE y2m_user_group.user_group_user_id = $user_id)"))
				->where(array("group_activity_start_timestamp>=now()"))
				 ; 			 	
		 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute()); 
		return $resultSet->toArray();
	}
	public function getActivityByEventGroupID($group_id,$activity_id){
		$select = new Select;
		$select->from('y2m_group_activity')
				->join('y2m_group', 'y2m_group.group_id = y2m_group_activity.group_activity_group_id', array('group_id','group_title','group_seo_title'))
				->where(array('y2m_group_activity.group_activity_id'=>$activity_id))
				->where(array('y2m_group_activity.group_activity_group_id'=>$group_id,"group_activity_status"=>0));

		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->current();
	}
	public function getActivityDetailById($activity_id){
		$activity_id  = (int) $activity_id;

		$select = new Select;
		$select->from('y2m_group_activity')
			->join('y2m_user', 'y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id', array('user_id','user_given_name'))
			->join('y2m_group', 'y2m_group.group_id = y2m_group_activity.group_activity_group_id', array('group_id','group_title','group_seo_title'))
			->where(array('y2m_group_activity.group_activity_id' => "$activity_id"));

		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);

		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		$row = $resultSet->current();
		return $row;
	}
	public function getAllEventsRelatedToUser($user_id,$group_id,$is_admin){
		$select = new Select;
		$select->from('y2m_group_activity')
			->where(array('y2m_group_activity.group_activity_status' => "active"));
		if($is_admin){
			$select->where(array("group_activity_group_id"=>$group_id));
		}else{
			$select->where(array('y2m_group_activity.group_activity_owner_user_id' => $user_id,"group_activity_group_id"=>$group_id));
		}
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->toArray();
	}

	public function getEventDetailsForGroupOrActivityOwner($activity_id,$user_id,$group_id,$is_admin){
		$select = new Select;
		$select->from('y2m_group_activity')
			->where(array('y2m_group_activity.group_activity_status' => "active"));
		if($is_admin){
			$select->where(array("group_activity_group_id"=>$group_id,'y2m_group_activity.group_activity_id' => $activity_id));
		}else{
			$select->where(array('y2m_group_activity.group_activity_id' => $activity_id,'y2m_group_activity.group_activity_owner_user_id' => $user_id,"group_activity_group_id"=>$group_id));
		}
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->toArray();
	}
}
