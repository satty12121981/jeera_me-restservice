<?php 
namespace User\Model;
use Zend\Db\Sql\Select , \Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class UserFriendRequestTable extends AbstractTableGateway
{
    protected $table = 'y2m_user_friend_request';
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new UserFriendRequest());
        $this->initialize();
    } 
    public function fetchAll() {   return $this->select();  }	
	public function sendFriendRequest($data) { 
        $this->insert($data);
		return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();        
    }
	public function getAllReuqestsCount($user_id){
		$select = new Select;
		$select->from('y2m_user_friend_request')
			   ->columns(array(new Expression('COUNT(y2m_user_friend_request.user_friend_request_id) as request_count')))			   
			   ->where(array('y2m_user_friend_request.user_friend_request_friend_user_id' =>$user_id,'y2m_user_friend_request.user_friend_request_status'=>'requested'));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->current()->request_count;	
	}
	public function getAllSentCount($user_id){
		$select = new Select;
		$select->from('y2m_user_friend_request')
			   ->columns(array(new Expression('COUNT(y2m_user_friend_request.user_friend_request_id) as request_count')))			   
			   ->where(array('y2m_user_friend_request.user_friend_request_sender_user_id' =>$user_id,'y2m_user_friend_request.user_friend_request_status'=>'requested'));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->current()->request_count;	
	}
	public function getAllReuqests($user_id){
		$select = new Select;
		$select->from('y2m_user_friend_request')			    
			   ->join('y2m_user',"y2m_user.user_id = y2m_user_friend_request.user_friend_request_sender_user_id",array('user_given_name','user_profile_name','user_register_type','user_fbid','user_id'))
			   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			   ->where(array('y2m_user_friend_request.user_friend_request_friend_user_id' =>$user_id,'y2m_user_friend_request.user_friend_request_status'=>'requested'));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->buffer();	
	}
	public function getAllFriendReuqests($user_id,$offset,$limit){
		$select = new Select;
		$group_count_select = new Select;
		$group_count_select->from('y2m_user_group')
					->columns(array('group_count'=>new Expression('COUNT(user_group_id)'),'user_group_user_id'=>'user_group_user_id'));
		$group_count_select->group('y2m_user_group.user_group_user_id');
		$group_created_select = new Select;
		$group_created_select->from('y2m_user_group')
					->columns(array('created_group_count'=>new Expression('COUNT(user_group_id)'),'user_group_user_id'=>'user_group_user_id'))
					->where(array('user_group_is_owner=1'));
		$group_created_select->group('y2m_user_group.user_group_user_id');
		$select->from('y2m_user_friend_request')			    
			   ->join('y2m_user',"y2m_user.user_id = y2m_user_friend_request.user_friend_request_sender_user_id",array('user_given_name','user_profile_name','user_register_type','user_fbid','user_id'))
			   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			   ->join("y2m_user_profile",'y2m_user.user_id = y2m_user_profile.user_profile_user_id', array('user_profile_city_id','user_profile_country_id'),'left')
			   ->join("y2m_country",'y2m_country.country_id = y2m_user_profile.user_profile_country_id', array('country_title','country_code'),'left')
			   ->join("y2m_city",'y2m_city.city_id = y2m_user_profile.user_profile_city_id', array('name'),'left')
			   ->join(array("group_count_temp"=>$group_count_select),'group_count_temp.user_group_user_id = y2m_user_friend_request.user_friend_request_sender_user_id', array('group_count'),'left')
			   //->join(array("group_created_temp"=>$group_created_select),'group_count_temp.user_group_user_id = y2m_user_friend_request.user_friend_request_sender_user_id', array('created_group_count'),'left')
			   ->where(array('y2m_user_friend_request.user_friend_request_friend_user_id' =>$user_id,'y2m_user_friend_request.user_friend_request_status'=>'requested'));
		$select->limit($limit);
	    $select->offset($offset);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->buffer();	
	}
	public function getAllFriendSentReuqests($user_id,$offset,$limit){
		$select = new Select;
		$group_count_select = new Select;
		$group_count_select->from('y2m_user_group')
					->columns(array('group_count'=>new Expression('COUNT(user_group_id)'),'user_group_user_id'=>'user_group_user_id'));
		$group_count_select->group('y2m_user_group.user_group_user_id');
		$group_created_select = new Select;
		$group_created_select->from('y2m_user_group')
					->columns(array('created_group_count'=>new Expression('COUNT(user_group_id)'),'user_group_user_id'=>'user_group_user_id'))
					->where(array('user_group_is_owner=1'));
		$group_created_select->group('y2m_user_group.user_group_user_id');
		$select->from('y2m_user_friend_request')			    
			   ->join('y2m_user',"y2m_user.user_id = y2m_user_friend_request.user_friend_request_friend_user_id",array('user_given_name','user_profile_name','user_register_type','user_fbid','user_id'))
			   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			   ->join("y2m_user_profile",'y2m_user.user_id = y2m_user_profile.user_profile_user_id', array('user_profile_city_id','user_profile_country_id'),'left')
			   ->join("y2m_country",'y2m_country.country_id = y2m_user_profile.user_profile_country_id', array('country_title','country_code'),'left')
			   ->join("y2m_city",'y2m_city.city_id = y2m_user_profile.user_profile_city_id', array('name'),'left')
			   ->join(array("group_count_temp"=>$group_count_select),'group_count_temp.user_group_user_id = y2m_user_friend_request.user_friend_request_friend_user_id', array('group_count'),'left')
			   //->join(array("group_created_temp"=>$group_created_select),'group_count_temp.user_group_user_id = y2m_user_friend_request.user_friend_request_friend_user_id', array('created_group_count'),'left')
			   ->where(array('y2m_user_friend_request.user_friend_request_sender_user_id' =>$user_id,'y2m_user_friend_request.user_friend_request_status'=>'requested'));
		$select->limit($limit);
	    $select->offset($offset);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->buffer();	
	}
	public function makeRequestTOProcessed($user_id,$request_id){
		$data['user_friend_request_status'] = 'friends';
		$this->update($data, array('user_friend_request_sender_user_id' => $request_id,'user_friend_request_friend_user_id'=>$user_id));		 
		return true;
	}
	public function DeclineFriendRequest($user_id,$request_id){
		$data['user_friend_request_status'] = 'declined';
		return $this->update($data, array('user_friend_request_sender_user_id' => $request_id,'user_friend_request_friend_user_id'=>$user_id));		 
	}
	public function RemoveFriendRequest($user_id,$request_id){
		$data['user_friend_request_status'] = 'deleted';
		return $this->update($data, array('user_friend_request_sender_user_id' => $request_id,'user_friend_request_friend_user_id'=>$user_id));
	}
	 // get friend request
     public function GetActiveFriendRequest($user_id,$request_id){
        $select = new Select;
        $select->from ('y2m_user_friend_request');
        $select->where("(user_friend_request_status = 'requested' AND user_friend_request_sender_user_id = ".$request_id." AND user_friend_request_friend_user_id = ".$user_id.")");
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);
        //echo $select->getSqlString($this->adapter->getPlatform());//exit;
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
       return $resultSet->toArray();
	}
    // function to process only active request (which has status = requested)
    public function makeActiveRequestTOProcessed($user_id,$request_id){
		$data['user_friend_request_status'] = 'friends';
        $this->update($data, array('user_friend_request_sender_user_id' => $request_id,'user_friend_request_friend_user_id'=>$user_id,'user_friend_request_status'=> 'requested'));
		return true;
	}
	public function getAllFriendSentReuqestsForAPI($user_id, $offset, $limit) {
		$group_count_select     = new Select;
		$group_count_select->from('y2m_user_group')
                           ->columns(array('joined_group_count'=>new Expression('COUNT(user_group_id)'),'user_group_user_id'=>'user_group_user_id'));
		$group_count_select->group('y2m_user_group.user_group_user_id');

		$group_created_select   = new Select;
		$group_created_select->from('y2m_user_group')
                             ->columns(array('created_group_count'=>new Expression('COUNT(user_group_id)'),'user_group_user_id'=>'user_group_user_id'))
                             ->where(array('user_group_is_owner=1'));
		$group_created_select->group('y2m_user_group.user_group_user_id');

        $select                 = new Select;
		$select->from('y2m_user_friend_request')
			   ->join('y2m_user',"y2m_user.user_id = y2m_user_friend_request.user_friend_request_friend_user_id",array('user_id','user_given_name','user_profile_name','user_email','user_status','user_fbid'))
               ->join("y2m_user_profile",'y2m_user.user_id = y2m_user_profile.user_profile_user_id', array('user_profile_about_me','user_profile_current_location','user_profile_phone'),'left')
			   ->join("y2m_country",'y2m_country.country_id = y2m_user_profile.user_profile_country_id', array('country_title','country_code','country_id'),'left')
			   ->join("y2m_city",'y2m_city.city_id = y2m_user_profile.user_profile_city_id', array('name','city_id'),'left')
			   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			   ->join(array("group_count_temp"=>$group_count_select),'group_count_temp.user_group_user_id = y2m_user_friend_request.user_friend_request_friend_user_id', array('joined_group_count'),'left')
			   ->join(array("group_created_temp"=>$group_created_select),'group_count_temp.user_group_user_id = y2m_user_friend_request.user_friend_request_friend_user_id', array('created_group_count'),'left')
			   ->where(array('y2m_user_friend_request.user_friend_request_sender_user_id' =>$user_id,'y2m_user_friend_request.user_friend_request_status'=>'requested'));
		$select->group('y2m_user_friend_request.user_friend_request_id');
		$select->limit((int) $limit);
	    $select->offset((int) $offset);
		$statement              = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString($this->adapter->getPlatform());exit;

		$resultSet              = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->toArray();
	}

    public function getAllFriendReuqestsForAPI($user_id, $offset, $limit) {
		$group_count_select     = new Select;
		$group_count_select->from('y2m_user_group')
                           ->columns(array('joined_group_count'=>new Expression('COUNT(user_group_id)'),'user_group_user_id'=>'user_group_user_id'));
		$group_count_select->group('y2m_user_group.user_group_user_id');

		$group_created_select   = new Select;
		$group_created_select->from('y2m_user_group')
                             ->columns(array('created_group_count'=>new Expression('COUNT(user_group_id)'),'user_group_user_id'=>'user_group_user_id'))
                             ->where(array('user_group_is_owner=1'));
		$group_created_select->group('y2m_user_group.user_group_user_id');

        $select                 = new Select;
		$select->from('y2m_user_friend_request')
			   ->join('y2m_user',"y2m_user.user_id = y2m_user_friend_request.user_friend_request_sender_user_id",array('user_id','user_given_name','user_profile_name','user_email','user_status','user_fbid'))
			   ->join("y2m_user_profile",'y2m_user.user_id = y2m_user_profile.user_profile_user_id', array('user_profile_about_me','user_profile_current_location','user_profile_phone'),'left')
			   ->join("y2m_country",'y2m_country.country_id = y2m_user_profile.user_profile_country_id', array('country_title','country_code','country_id'),'left')
			   ->join("y2m_city",'y2m_city.city_id = y2m_user_profile.user_profile_city_id', array('name','city_id'),'left')
			   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			   ->join(array("group_count_temp"=>$group_count_select),'group_count_temp.user_group_user_id = y2m_user_friend_request.user_friend_request_sender_user_id', array('joined_group_count'),'left')
			   ->join(array("group_created_temp"=>$group_created_select),'group_count_temp.user_group_user_id = y2m_user_friend_request.user_friend_request_sender_user_id', array('created_group_count'),'left')
			   ->where(array('y2m_user_friend_request.user_friend_request_friend_user_id' =>$user_id,'y2m_user_friend_request.user_friend_request_status'=>'requested'));
		$select->group('y2m_user_friend_request.user_friend_request_id');
		$select->limit((int) $limit);
	    $select->offset((int) $offset);
		$statement              = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString($this->adapter->getPlatform());exit;

		$resultSet              = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->toArray();
	}
}
