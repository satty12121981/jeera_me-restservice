<?php
namespace Like\Model;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class LikeTable extends AbstractTableGateway
{ 
    protected $table = 'y2m_like'; 	
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Like());
        $this->initialize();
    }
	// this function will fetch likes count by reference
    public function fetchLikesCountByReference($LikeTypeId,$ReferenceId,$user_id){
		$LikeTypeId  = (int) $LikeTypeId;
		$ReferenceId  = (int) $ReferenceId;
		$select = new Select;
		$select->columns(array('likes_counts' => new \Zend\Db\Sql\Expression('COUNT(*)'),'is_liked'=>new Expression('IF(EXISTS(SELECT like_id FROM y2m_like WHERE y2m_like.like_by_user_id = '.$user_id.' AND y2m_like.like_system_type_id = '.$LikeTypeId.' AND y2m_like.like_refer_id = '.$ReferenceId.'),1,0)')))
			->from('y2m_like')
			->where(array('y2m_like.like_system_type_id' => $LikeTypeId,'y2m_like.like_refer_id' => $ReferenceId))
			->order(array('y2m_like.like_system_type_id ASC'));
		
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString(); 
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		$row = $resultSet->current();
		return $row;
    }	
	// this function will check against user of the system like exists already
	public function LikeExistsCheck($LikeTypeId,$ReferenceId,$UserId){	
        $LikeTypeId  = (int) $LikeTypeId;
		$ReferenceId  = (int) $ReferenceId;
		$UserId  = (int) $UserId;
        $rowset = $this->select(array('like_system_type_id' => $LikeTypeId,'like_refer_id'=>$ReferenceId,'like_by_user_id'=>$UserId));
        $row = $rowset->current();
        return $row;
	}	
	// this function will fetch by ref
    public function fetchLikesUsersByReference($LikeTypeId,$ReferenceId){
		$LikeTypeId  = (int) $LikeTypeId;
		$ReferenceId  = (int) $ReferenceId;
		$select = new Select;
		$select->from('y2m_like')
			->join('y2m_system_type', 'y2m_like.like_system_type_id = y2m_system_type.system_type_id')
			->join('y2m_user', 'y2m_like.like_by_user_id = y2m_user.user_id')
			->join('y2m_photo', 'y2m_photo.photo_id = y2m_user.user_profile_photo_id')
			->where(array('y2m_like.like_system_type_id' => $LikeTypeId,'y2m_like.like_refer_id' => $ReferenceId))
			->order(array('y2m_like.like_system_type_id ASC'));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString(); 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet;
	}	
	// this will fetch single like from table
    public function getLike($like_id){
        $like_id  = (int) $like_id;
        $rowset = $this->select(array('like_id' => $like_id));
        $row = $rowset->current();
        return $row;
    }	
	// this will save Like in a table
    public function saveLike(Like $Like){
       $data = array(
            'like_system_type_id' => $Like->like_system_type_id,
            'like_by_user_id'  => $Like->like_by_user_id,
			'like_status'  => $Like->like_status,			 
			'like_added_ip_address'  => new \Zend\Db\Sql\Expression("INET_ATON('" . $Like->like_added_ip_address . "')"),
			'like_refer_id'  => $Like->like_refer_id,
			'like_added_timestamp' => date("Y-m-d H:i:s"),
        );
        $like_id = (int)$Like->like_id;
        if ($like_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getLike($like_id)) {
                $this->update($data, array('like_id' => $like_id));
            } else {
                throw new \Exception('Like id does not exist');
            }
        }
    }	
	// it will delete any Like
    public function deleteLike($like_id){
        $this->delete(array('like_id' => $like_id));
    }	
	// it will delete any Like
    public function deleteLikeByReference($like_system_type_id,$like_by_user_id,$like_refer_id){
        return $this->delete(array('like_system_type_id' => $like_system_type_id,'like_by_user_id' => $like_by_user_id,'like_refer_id' => $like_refer_id));
    }
	public function deleteEventCommentLike($system_type,$event_id){
		$sql  = 'DELETE  y2m_like.* FROM  y2m_like INNER JOIN y2m_comment ON y2m_comment.comment_id = y2m_like.like_refer_id   WHERE y2m_comment.comment_system_type_id = '.$system_type.' AND y2m_comment.comment_refer_id = '.$event_id;
		$statement = $this->adapter-> query($sql);
		return $statement->execute();		 		
	}
	public function deleteEventLike($system_type,$event_id){
		 $this->delete(array('like_system_type_id' => $system_type,'like_refer_id'=>$event_id));
	}
	public function getCommentSystemType($referance_id){
		$select = new Select;
		$select->from("y2m_system_type")
			->columns(array('*'))
			->where(array("system_type_id"=>$referance_id));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		// echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
		return $resultSet->current(); 
	}
	public function likedUsersWithoutLoggedOneWithFriendshipStatus($LikeTypeId,$ReferenceId,$UserId,$limit,$offset){
		$select = new Select;
		$select->from('y2m_like')
			->columns(array('is_friend'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_friend WHERE  (y2m_user_friend.user_friend_sender_user_id = y2m_like.like_by_user_id AND y2m_user_friend.user_friend_friend_user_id = '.$UserId.')OR(y2m_user_friend.user_friend_friend_user_id = y2m_like.like_by_user_id AND y2m_user_friend.user_friend_sender_user_id = '.$UserId.')),1,0)'),
			'is_requested'=>new Expression('IF(EXISTS(SELECT * FROM   y2m_user_friend_request WHERE  ( y2m_user_friend_request.user_friend_request_friend_user_id = y2m_like.like_by_user_id AND y2m_user_friend_request.user_friend_request_sender_user_id = '.$UserId.' AND y2m_user_friend_request.user_friend_request_status = 0) ),1,0)'),
			'get_request'=>new Expression('IF(EXISTS(SELECT * FROM   y2m_user_friend_request WHERE  ( y2m_user_friend_request.user_friend_request_sender_user_id = y2m_like.like_by_user_id AND y2m_user_friend_request.user_friend_request_friend_user_id = '.$UserId.' AND y2m_user_friend_request.user_friend_request_status = 0) ),1,0)'),			 
			))
			->join('y2m_user', 'y2m_user.user_id = y2m_like.like_by_user_id', array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'))
			->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			 
			->where('y2m_like.like_by_user_id!='.$UserId)
			->where(array('y2m_like.like_system_type_id' => $LikeTypeId,'y2m_like.like_refer_id' => $ReferenceId));
			 
		if($limit!=''){
		$select->limit($limit);
		$select->offset($offset);	
		}
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
	  	return $resultSet->toArray(); 
	}

    public function likedUsersForRestAPI($LikeTypeId,$ReferenceId,$UserId,$limit,$offset){
        $select = new Select;
        $select->from('y2m_like')
            ->columns(array('is_friend'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_friend WHERE  (y2m_user_friend.user_friend_sender_user_id = y2m_like.like_by_user_id AND y2m_user_friend.user_friend_friend_user_id = '.$UserId.')OR(y2m_user_friend.user_friend_friend_user_id = y2m_like.like_by_user_id AND y2m_user_friend.user_friend_sender_user_id = '.$UserId.')),1,0)'),
                'is_requested'=>new Expression('IF(EXISTS(SELECT * FROM   y2m_user_friend_request WHERE  ( y2m_user_friend_request.user_friend_request_friend_user_id = y2m_like.like_by_user_id AND y2m_user_friend_request.user_friend_request_sender_user_id = '.$UserId.' AND y2m_user_friend_request.user_friend_request_status = 0) ),1,0)'),
                'get_request'=>new Expression('IF(EXISTS(SELECT * FROM   y2m_user_friend_request WHERE  ( y2m_user_friend_request.user_friend_request_sender_user_id = y2m_like.like_by_user_id AND y2m_user_friend_request.user_friend_request_friend_user_id = '.$UserId.' AND y2m_user_friend_request.user_friend_request_status = 0) ),1,0)'),
            ))
            ->join('y2m_user', 'y2m_user.user_id = y2m_like.like_by_user_id', array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'))
            ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
            ->where(array('y2m_like.like_system_type_id' => $LikeTypeId,'y2m_like.like_refer_id' => $ReferenceId));
        if($limit!=''){
            $select->limit($limit);
            $select->offset($offset);
        }
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);
        //echo $select->getSqlString();exit;
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return $resultSet->toArray();
    }
	public function getUserLIkeCount($user_id){
		$sql = "SELECT count(*) as like_count FROM (Select count(*)as cnt from y2m_like WHERE `like_by_user_id`=".$user_id." group by `like_refer_id`,`like_system_type_id`) as likes";
		//echo $sql;die();
		$statement = $this->adapter-> query($sql); 		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->current();
	}
}