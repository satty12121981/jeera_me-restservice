<?php
####################Discussion Table Model #################################

namespace Discussion\Model;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class DiscussionTable extends AbstractTableGateway
{
    protected $table = 'y2m_group_discussion'; 	
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Discussion());
        $this->initialize();
    }	
	#this will fetch all discussion
    public function fetchAll(){
       $resultSet = $this->select();
       return $resultSet;
    }
	#this will fetch discussion details based primary key. Group discussion Id
    public function getDiscussion($group_discussion_id){
        $group_discussion_id  = (int) $group_discussion_id;
        $rowset = $this->select(array('group_discussion_id' => $group_discussion_id));
        $row = $rowset->current();
        return $row;
    }	
	#this will fetch the discussion of a Planet
	public function getGroupAllDiscussion($group_id){        
       	$group_id = (int) $group_id; 
		$resultSet = $this->select(function (Select $select) use ($group_id) {
			$select->where(array('group_discussion_group_id', $group_id));
			$select->order('group_discussion_added_timestamp DESC');
		});			 	
		return $resultSet;	 
    }	
	#this will fetch the discussion with likes of a Planet
	public function getGroupAllDiscussionWithLikes($GroupId,$SystemTypeId,$LikeUserId,$offset=0){        
       	$GroupId = (int) $GroupId; 
		$SystemTypeId  = (int) $SystemTypeId;
		$LikeUserId  = (int) $LikeUserId;
		
		//sub query to take likes count for a planet discussion
		$subselect = new Select;
		$adapter = $this->getAdapter();
		$platform = $adapter->getPlatform();
		$quoteId = $platform->quoteIdentifier($LikeUserId);
		$subselect->from('y2m_group_discussion')
			->columns(array(new Expression('COUNT(y2m_like.like_id) as likes_count'),'group_discussion_id'=>'group_discussion_id'))
			->join('y2m_like', 'y2m_like.like_refer_id = y2m_group_discussion.group_discussion_id',array())
			->join('y2m_system_type', 'y2m_system_type.system_type_id = y2m_like.like_system_type_id',array())
			->where(array('y2m_group_discussion.group_discussion_group_id' => $GroupId,'y2m_like.like_system_type_id' => $SystemTypeId))
			->group(array('y2m_group_discussion.group_discussion_id'))
			->order(array('y2m_group_discussion.group_discussion_added_timestamp ASC'));		
		$commentscount_subselect = new Select;		
		$commentscount_subselect->from('y2m_group_discussion')
			->columns(array(new Expression('COUNT(y2m_comment.comment_id) as comments_count'),'group_discussion_id'=>'group_discussion_id'))
			->join('y2m_comment', 'y2m_comment.comment_refer_id = y2m_group_discussion.group_discussion_id',array())
			->join('y2m_system_type', 'y2m_system_type.system_type_id = y2m_comment.comment_system_type_id',array())
			->where(array('y2m_group_discussion.group_discussion_group_id' => $GroupId,'y2m_comment.comment_system_type_id' => $SystemTypeId))
			->group(array('y2m_group_discussion.group_discussion_id'))
			->order(array('y2m_group_discussion.group_discussion_added_timestamp ASC'));							
		//sub query to check user exist for the specific discussion of the planet liked
		$alias_subselect = new Select;
		$alias_subselect->from('y2m_group_discussion')
			->columns(array('group_discussion_id'=>'group_discussion_id'))
			->join('y2m_like', 'y2m_like.like_refer_id = y2m_group_discussion.group_discussion_id',array())
			->join('y2m_system_type', 'y2m_system_type.system_type_id = y2m_like.like_system_type_id',array())
			->join('y2m_user', 'y2m_user.user_id = y2m_like.like_by_user_id',array())
			->where(array('y2m_group_discussion.group_discussion_id' => new Expression('`final`.`group_discussion_id`'),'y2m_user.user_id' => $LikeUserId));		
		$expression = new Expression(
		  "IF ( EXISTS(" . @$alias_subselect->getSqlString($adapter->getPlatform()) . ") , `final`.`group_discussion_id`, 0)"
		);		
		//sub query to check user spammed for the specific discussion of the planet
		$alias_subspamselect = new Select;
		$alias_subspamselect->from('y2m_group_discussion')
			->columns(array('group_discussion_id'=>'group_discussion_id'))
			->join('y2m_spam', 'y2m_spam.spam_refer_id = y2m_group_discussion.group_discussion_id',array())
			->join('y2m_system_type', 'y2m_system_type.system_type_id = y2m_spam.spam_system_type_id',array())
			->join('y2m_problem', 'y2m_problem.problem_id = y2m_spam.spam_problem_id',array())
			->join('y2m_user', 'y2m_user.user_id = y2m_spam.spam_report_user_id',array())
			->where(array('y2m_group_discussion.group_discussion_id' => new Expression('`final`.`group_discussion_id`'),'y2m_user.user_id' => $LikeUserId));			
		//user query to select picture
		$alias_subuserprofileselect = new Select;
		$alias_subuserprofileselect->from('y2m_group_discussion')
			->columns(array('group_discussion_id'=>'group_discussion_id','user_id' => new Expression('`y2m_user`.`user_id`'),'user_given_name' => new Expression('`y2m_user`.`user_given_name`'),'photo_location'=> new Expression('`y2m_photo`.`photo_location`'),'photo_name'=> new Expression('`y2m_photo`.`photo_name`')))
			->join('y2m_user', 'y2m_user.user_id = y2m_group_discussion.group_discussion_owner_user_id',array(),'left')
			->join('y2m_photo', 'y2m_photo.photo_id = y2m_user.user_profile_photo_id',array(),'left')
			->where(array('y2m_group_discussion.group_discussion_group_id' => $GroupId))
			->group(array('y2m_group_discussion.group_discussion_id'))
			->order(array('y2m_group_discussion.group_discussion_added_timestamp ASC'));		
		$expression1 = new Expression(
		  "IF ( EXISTS(" . @$alias_subspamselect->getSqlString($adapter->getPlatform()) . ") , `final`.`group_discussion_id`, 0)"
		);				
		//main query
		$mainSelect = new Select;
		$mainSelect->from('y2m_group_discussion')
			->join(array('temp' => $subselect), 'temp.group_discussion_id = y2m_group_discussion.group_discussion_id',array('likes_count',"user_check"=>$expression,"spam_user_check"=>$expression1),'left')
			->join(array('temp_comment' => $commentscount_subselect), 'temp_comment.group_discussion_id = y2m_group_discussion.group_discussion_id',array('comments_count'),'left')
			->join(array('temp1' => $alias_subuserprofileselect), 'temp1.group_discussion_id = y2m_group_discussion.group_discussion_id',array('photo_location','photo_name','user_id','user_given_name'),'left')
			->join(array('final'=>'y2m_group_discussion'), 'final.group_discussion_id = y2m_group_discussion.group_discussion_id','group_discussion_id','left')
			->columns(array('group_discussion_content'=>'group_discussion_content','group_discussion_owner_user_id'=>'group_discussion_owner_user_id'))
            ->where(array('final.group_discussion_group_id' => $GroupId));
			$mainSelect->limit(10);
			$mainSelect->offset($offset);	   
		$statement = $this->adapter->createStatement();		
		$mainSelect->prepareStatement($this->adapter, $statement);
	//echo $mainSelect->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());		
		return $resultSet;
    }
	#this function will save discussion in database
    public function saveDiscussion(discussion $discussion){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) { $ip = $_SERVER['HTTP_CLIENT_IP'];} 
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }
		else {   $ip = $_SERVER['REMOTE_ADDR'];}
	    $data = array(
            'group_discussion_content' => $discussion->group_discussion_content,
            'group_discussion_owner_user_id'  => $discussion->group_discussion_owner_user_id,
			'group_discussion_group_id'  => $discussion->group_discussion_group_id,
			'group_discussion_status'  => $discussion->group_discussion_status,
			'group_discussion_added_ip_address'  => $ip,			 	
			'group_discussion_modified_ip_address'  => $ip,
			'group_discussion_added_timestamp' => date("Y-m-d H:i:s"),
        );

        $group_discussion_id = (int) $discussion->group_discussion_id;
        if ($group_discussion_id == 0) {
			try {
				$this->insert($data);
			}
			catch (\PDOException $e) {
				print "First Message " . $e->getMessage() . "<br/>";
			}
			catch (\Exception $e) {
				print "Second Message: " . $e->getMessage() . "<br/>";
			} 
			
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getdiscussion($group_discussion_id)) {
                $this->update($data, array('group_discussion_id' => $group_discussion_id));
            } else {
                throw new \Exception('Group Discussion form id does not exist');
            }
        }
    }
	#this function will delete discussion in database
    public function deleteDiscussion($group_discussion_id){
        return $this->delete(array('group_discussion_id' => $group_discussion_id));
    }
	public function getAllDiscussionWithOwnerdetails($planet_id,$limit,$offset){
			$select = new Select;
			$select->from("y2m_group_discussion")
				   ->columns(array('group_discussion_content'=>'group_discussion_content','group_discussion_id'=>'group_discussion_id'))
				   ->join("y2m_user","y2m_user.user_id = y2m_group_discussion.group_discussion_owner_user_id",array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'))
				   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
				   ->where(array("group_discussion_group_id"=>$planet_id))
				   ->where(array("group_discussion_status"=>1));
				 $select->order(array('y2m_group_discussion.group_discussion_added_timestamp DESC'));	 
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer();
				   
	}
	public function updateDiscussionData($data,$group_discussion_id){
		return $this->update($data, array('group_discussion_id' => $group_discussion_id));
	}
	public function getDiscussionMembersWithGroupsettings($discusssion_id,$planet_id,$system_type){
		$select = new Select;
		$select->from('y2m_comment')
			  ->columns(array("comment_by_user_id"))
			  ->join('y2m_user_group_settings',new Expression('y2m_user_group_settings.user_id = y2m_comment.comment_by_user_id AND y2m_user_group_settings.group_id = '.$planet_id),array('activity','member','discussion','media','group_announcement'),'left')
			  ->where(array("y2m_comment.comment_system_type_id"=>$system_type))
			  ->where(array("y2m_comment.comment_refer_id"=>$discusssion_id));			   
		;	
		$select->group('comment_by_user_id');
		$statement = $this->adapter->createStatement();		
	    $select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
	    $resultSet = new ResultSet();
	    $resultSet->initialize($statement->execute());  
	    return $resultSet->buffer();
	}
	public function getOneDiscussionWithOwnerInfo($discussion_id){
		$select = new Select;
			$select->from("y2m_group_discussion")
				   ->columns(array('group_discussion_content'=>'group_discussion_content','group_discussion_id'=>'group_discussion_id','group_discussion_group_id'=>'group_discussion_group_id','group_discussion_owner_user_id'=>'group_discussion_owner_user_id'))
				   ->join("y2m_user","y2m_user.user_id = y2m_group_discussion.group_discussion_owner_user_id",array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'))
				   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
				   ->where(array("group_discussion_id"=>$discussion_id))
				   ;	 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();
	}
	public function getDiscussionForFeed($discussion_id){
		$group_discussion_id  = (int) $discussion_id;
        $rowset = $this->select(array('group_discussion_id' => $discussion_id));
        $row = $rowset->current();
        return $row;
	}	
}