<?php

namespace Comment\Model;

use Zend\Db\Sql\Select;

use Zend\Db\TableGateway\AbstractTableGateway;

use Zend\Db\TableGateway\TableGateway;

use Zend\Db\TableGateway\Feature\RowGatewayFeature;

use Zend\Db\Adapter\Adapter;

use Zend\Db\ResultSet\ResultSet;

use Zend\Db\Sql\Expression;

class CommentTable extends AbstractTableGateway

{ 

    protected $table = 'y2m_comment'; 	

    public function __construct(Adapter $adapter){

        $this->adapter = $adapter;

        $this->resultSetPrototype = new ResultSet();

        $this->resultSetPrototype->setArrayObjectPrototype(new Comment());

        $this->initialize();

    }

	//this function will fetch all comments

    public function fetchCommentsByReferenceWithLikes($CommentType,$LikeType,$ReferenceId,$LikeUserId){	

		$CommentType  = (int) $CommentType;

		$LikeType  = (int) $LikeType;

		$ReferenceId  = (int) $ReferenceId;

		$LikeUserId  = (int) $LikeUserId;		

		//sub query to take likes count for a planet discussion

		$subselect = new Select;

		$adapter = $this->getAdapter();

		$platform = $adapter->getPlatform();

		$quoteId = $platform->quoteIdentifier($LikeUserId);

		$subselect->from('y2m_comment')

			->columns(array(new Expression('COUNT(`y2m_like`.`like_id`) as `likes_count`'),'comment_id'=>'comment_id','comment_refer_id'=>'comment_refer_id'))

			->join('y2m_like', 'y2m_like.like_refer_id = y2m_comment.comment_id', array())

			->join(array('a'=>'y2m_system_type'), 'a.system_type_id = y2m_comment.comment_system_type_id', array())

			->join(array('b'=>'y2m_system_type'), 'b.system_type_id = y2m_like.like_system_type_id', array())

			->where(array('y2m_comment.comment_refer_id' => $ReferenceId,'y2m_like.like_system_type_id' => $LikeType))

			->group(array('y2m_comment.comment_id'))

			->order(array('y2m_comment.comment_added_timestamp ASC'));							

		//sub query to check user exist for the specific discussion of the planet liked

		$alias_subselect = new Select;

		$alias_subselect->from('y2m_comment')

			->columns(array('comment_id'=>'comment_id'))

			->join('y2m_like', 'y2m_like.like_refer_id = y2m_comment.comment_id', array())

			->join(array('a'=>'y2m_system_type'), 'a.system_type_id = y2m_comment.comment_system_type_id', array())

			->join(array('b'=>'y2m_system_type'), 'b.system_type_id = y2m_like.like_system_type_id', array())

			->join('y2m_user', 'y2m_user.user_id = y2m_like.like_by_user_id',array())

			->where(array('y2m_comment.comment_refer_id' => new Expression('`final`.`comment_refer_id`'),'y2m_user.user_id' => $LikeUserId ));		

		$expression = new Expression(

		  "IF ( EXISTS(" . @$alias_subselect->getSqlString($adapter->getPlatform()) . ") , `final`.`comment_refer_id`, 0)"

		);		

		//user query to select picture

		$alias_subselect2 = new Select;

		$alias_subselect2->from('y2m_comment')

			->columns(array('comment_id'=>'comment_id','user_id' => new Expression('`y2m_user`.`user_id`'),

						'user_given_name' => new Expression('`y2m_user`.`user_given_name`'),

						'photo_location'=> new Expression('`y2m_photo`.`photo_location`'),

						'photo_name'=> new Expression('`y2m_photo`.`photo_name`')))

			->join('y2m_user', 'y2m_user.user_id = y2m_comment.comment_by_user_id',array(),'left')

			->join('y2m_photo', 'y2m_photo.photo_id = y2m_user.user_profile_photo_id',array(),'left')

			->where(array('y2m_comment.comment_refer_id' => $ReferenceId));			

		//main query

		$mainSelect = new Select;

		$mainSelect->from('y2m_comment')

				->join(array('temp' => $subselect), 'temp.comment_id = y2m_comment.comment_id',array('likes_count',"user_check"=>$expression),'left')

				->join(array('temp1' => $alias_subselect2), 'temp1.comment_id = y2m_comment.comment_id',array('photo_location','photo_name','user_id','user_given_name'),'left')

				->join(array('final'=>'y2m_comment'), 'final.comment_id = y2m_comment.comment_id','comment_id','left')

				->columns(array('comment_refer_id'=>'comment_refer_id','comment_content'=>'comment_content'))

				->where(array('y2m_comment.comment_refer_id' => $ReferenceId))

				->where(array('y2m_comment.comment_system_type_id' => $CommentType));				   

		$statement = $this->adapter->createStatement();		

		$mainSelect->prepareStatement($this->adapter, $statement);

		//echo $mainSelect->getSqlString();

		$resultSet = new ResultSet();		

		$resultSet->initialize($statement->execute());

		return $resultSet;	    

    }	

	//this will fetch single Galaxy from table

    public function getComment($comment_id){

        $comment_id  = (int) $comment_id;

		$select = new Select;

        $select->from('y2m_comment')

			->where(array('y2m_comment.comment_id'=>$comment_id));

		$statement = $this->adapter->createStatement();

		$select->prepareStatement($this->adapter, $statement);

		//echo $mainSelect->getSqlString();

		$resultSet = new ResultSet();		

		$resultSet->initialize($statement->execute());	  

		return $resultSet->current();

    }	

	//this will save comment in a table

    public function saveComment(comment $comment){

		if (!empty($_SERVER['HTTP_CLIENT_IP'])) { $ip = $_SERVER['HTTP_CLIENT_IP'];} 

		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }

		else {   $ip = $_SERVER['REMOTE_ADDR'];}

       $data = array(

            'comment_system_type_id' => $comment->comment_system_type_id,

            'comment_by_user_id'  => $comment->comment_by_user_id,

			'comment_content'  => $comment->comment_content,

			'comment_status'  => $comment->comment_status,			 

			'comment_added_ip_address'  => $ip,

			'comment_refer_id'  => $comment->comment_refer_id,

			'comment_added_timestamp' =>date("Y-m-d H:i:s"),

        );

        $comment_id = (int)$comment->comment_id;

        if ($comment_id == 0) {

            $this->insert($data);

			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();

        } else {

            if ($this->getComment($comment_id)) {

                $this->update($data, array('comment_id' => $comment_id));

            } else {

                throw new \Exception('Comment id does not exist');

            }

        }

    }	

	//it will delete any comment

    public function deleteComment($comment_id){

        return $this->delete(array('comment_id' => $comment_id));

    }

	public function getCommentsByReferenceWithLikes($CommentType,$LikeType,$ReferenceId,$LikeUserId,$limit,$offset){

	

		$CommentType  = (int) $CommentType;

		$LikeType  = (int) $LikeType;

		$ReferenceId  = (int) $ReferenceId;

		$LikeUserId  = (int) $LikeUserId;		

		//sub query to take likes count for a planet discussion

		$subselect = new Select;

		$adapter = $this->getAdapter();

		$platform = $adapter->getPlatform();

		$quoteId = $platform->quoteIdentifier($LikeUserId);

		$subselect->from('y2m_comment')

			->columns(array(new Expression('COUNT(`y2m_like`.`like_id`) as `likes_count`'),'comment_id'=>'comment_id','comment_refer_id'=>'comment_refer_id'))

			->join('y2m_like', 'y2m_like.like_refer_id = y2m_comment.comment_id', array())

			->join(array('a'=>'y2m_system_type'), 'a.system_type_id = y2m_comment.comment_system_type_id', array())

			->join(array('b'=>'y2m_system_type'), 'b.system_type_id = y2m_like.like_system_type_id', array())

			->where(array('y2m_comment.comment_refer_id' => $ReferenceId,'y2m_like.like_system_type_id' => $LikeType))

			->group(array('y2m_comment.comment_id'))

			->order(array('y2m_comment.comment_added_timestamp ASC'));							

		//sub query to check user exist for the specific discussion of the planet liked

		$alias_subselect = new Select;

		$alias_subselect->from('y2m_comment')

			->columns(array('comment_id'=>'comment_id'))

			->join('y2m_like', 'y2m_like.like_refer_id = y2m_comment.comment_id', array())

			->join(array('a'=>'y2m_system_type'), 'a.system_type_id = y2m_comment.comment_system_type_id', array())

			->join(array('b'=>'y2m_system_type'), 'b.system_type_id = y2m_like.like_system_type_id', array())

			->join('y2m_user', 'y2m_user.user_id = y2m_like.like_by_user_id',array())

			->where(array('y2m_comment.comment_refer_id' => new Expression('`final`.`comment_refer_id`'),'y2m_user.user_id' => $LikeUserId ));		

		$expression = new Expression(

		  "IF ( EXISTS(" . @$alias_subselect->getSqlString($adapter->getPlatform()) . ") , `final`.`comment_refer_id`, 0)"

		);		

		//user query to select picture

		$alias_subselect2 = new Select;

		$alias_subselect2->from('y2m_comment')

			->columns(array('comment_id'=>'comment_id','user_id' => new Expression('`y2m_user`.`user_id`'),

						'user_given_name' => new Expression('`y2m_user`.`user_given_name`'),

						'data_content'=> new Expression('`y2m_album_data`.`data_content`')

						))

			->join('y2m_user', 'y2m_user.user_id = y2m_comment.comment_by_user_id',array(),'left')

			->join('y2m_album_data', 'y2m_album_data.data_id = y2m_user.user_profile_photo_id',array(),'left')

			->where(array('y2m_comment.comment_refer_id' => $ReferenceId));			

		//main query

		$mainSelect = new Select;

		$mainSelect->from('y2m_comment')

				->join(array('temp' => $subselect), 'temp.comment_id = y2m_comment.comment_id',array('likes_count',"user_check"=>$expression),'left')

				->join(array('temp1' => $alias_subselect2), 'temp1.comment_id = y2m_comment.comment_id',array('data_content','user_id','user_given_name'),'left')

				->join(array('final'=>'y2m_comment'), 'final.comment_id = y2m_comment.comment_id','comment_id','left')

				->columns(array('comment_refer_id'=>'comment_refer_id','comment_content'=>'comment_content'))

				->where(array('y2m_comment.comment_refer_id' => $ReferenceId))

				->where(array('y2m_comment.comment_system_type_id' => $CommentType));

		$mainSelect->limit($limit);

		$mainSelect->offset($offset);	

 		$mainSelect->order(array('y2m_comment.comment_added_timestamp DESC'));			

		$statement = $this->adapter->createStatement();		

		$mainSelect->prepareStatement($this->adapter, $statement);

		//echo $mainSelect->getSqlString();

		$resultSet = new ResultSet();		

		$resultSet->initialize($statement->execute());	  

		return $resultSet;	    

    }

	public function getCommentCount($system_type,$event_id){

		$select = new Select;

		$select->from("y2m_comment")

			   ->columns(array( new Expression('count(y2m_comment.comment_id) as comment_count')))

			   ->where(array("y2m_comment.comment_system_type_id"=>$system_type))

			   ->where(array("y2m_comment.comment_refer_id"=>$event_id));	   

		$statement = $this->adapter->createStatement();

		

		$select->prepareStatement($this->adapter, $statement);

		//echo $select->getSqlString();

		$resultSet = new ResultSet();		

		$resultSet->initialize($statement->execute());	  

		return $resultSet->current();

	}

	public function getInsertedCommentWithUserDetails($comment_id){

		$select = new Select;

		$select->from("y2m_comment")

				->columns(array("comment_content","comment_id","comment_system_type_id","comment_refer_id"))

				->join("y2m_user","y2m_user.user_id = y2m_comment.comment_by_user_id",array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'))

				->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')

			   ->where(array("y2m_comment.comment_id"=>$comment_id));	   

		$statement = $this->adapter->createStatement();

		

		$select->prepareStatement($this->adapter, $statement);

		//echo $select->getSqlString();

		$resultSet = new ResultSet();		

		$resultSet->initialize($statement->execute());	  

		return $resultSet->current();

		

	}

	public function getAllCommentsWithLike($Systemtype,$commentType,$ReferenceId,$LikeUserId,$limit,$offset){

		$select = new Select;

		$select->from("y2m_comment")

				->columns(array("islike"=>new Expression('IF(EXISTS(SELECT * FROM y2m_like WHERE y2m_like.like_by_user_id = '.$LikeUserId.' AND y2m_like.like_refer_id = y2m_comment.comment_id AND  y2m_like.like_system_type_id  = '.$commentType.'),1,0)'),"comment_content"=>'comment_content',"comment_id"=>'comment_id',"comment_added_timestamp"=>"comment_added_timestamp"))

				->join("y2m_like","y2m_like.like_refer_id = y2m_comment.comment_id ",array(),'left')

				->join("y2m_user","y2m_user.user_id = y2m_comment.comment_by_user_id",array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'))

				->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')

				->where('y2m_comment.comment_refer_id ='. $ReferenceId)

				->where('y2m_comment.comment_system_type_id='. $Systemtype)	;			 

		$select->group('y2m_comment.comment_id');			   

		$select->order(array('y2m_comment.comment_added_timestamp DESC'));

		$select->limit($limit);

		$select->offset($offset);		

		$statement = $this->adapter->createStatement();

		

		$select->prepareStatement($this->adapter, $statement);

		//echo $select->getSqlString();die();

		$resultSet = new ResultSet();		

		$resultSet->initialize($statement->execute());	  

		return $resultSet->buffer();

		

	}

	public function deleteEventComments($system_type,$event_id){

		$this->delete(array('comment_system_type_id' => $system_type,'comment_refer_id'=>$event_id));

	}

	public function updateCommentTable($data,$id){

			return $this->update($data, array('comment_id' => $id));

	}

	public function fetchCommentCountByReference($CommentTypeId,$ReferenceId,$user_id){

		$CommentTypeId  = (int) $CommentTypeId;

		$ReferenceId  = (int) $ReferenceId;

		$select = new Select;

		$select->columns(array('comment_counts' => new \Zend\Db\Sql\Expression('COUNT(*)'),'is_commented'=>new Expression('IF(EXISTS(SELECT comment_id FROM y2m_comment WHERE y2m_comment.comment_by_user_id = '.$user_id.' AND y2m_comment.comment_system_type_id = '.$CommentTypeId.' AND y2m_comment.comment_refer_id = '.$ReferenceId.'),1,0)')))

			->from('y2m_comment')

			->where(array('y2m_comment.comment_system_type_id' => $CommentTypeId,'y2m_comment.comment_refer_id' => $ReferenceId))

			->order(array('y2m_comment.comment_system_type_id ASC'));

		

		$statement = $this->adapter->createStatement();

		//echo $select->getSqlString(); 

		$select->prepareStatement($this->adapter, $statement);

		$resultSet = new ResultSet();

		$resultSet->initialize($statement->execute());	

		$row = $resultSet->current();

		return $row;

    }

	public function getUserCommentCount($user_id){

		$sql = "SELECT count(*) as comment_count FROM (Select count(*)as cnt from y2m_comment WHERE `comment_by_user_id`=".$user_id." group by `comment_refer_id`,`comment_system_type_id`) as comments";

		 

		$statement = $this->adapter-> query($sql); 		 

		$resultSet = new ResultSet();

		$resultSet->initialize($statement->execute());

		return $resultSet->current();

	}

}