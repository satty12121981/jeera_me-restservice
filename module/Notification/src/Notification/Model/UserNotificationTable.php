<?php

namespace Notification\Model;

use Zend\Db\Sql\Select;

use Zend\Db\TableGateway\AbstractTableGateway;

use Zend\Db\Adapter\Adapter;

use Zend\Db\ResultSet\ResultSet;

use Zend\Db\Sql\Expression;



class UserNotificationTable extends AbstractTableGateway

{

    protected $table = 'y2m_user_notification'; 

    public function __construct(Adapter $adapter){

        $this->adapter = $adapter;

        $this->resultSetPrototype = new ResultSet();

        $this->resultSetPrototype->setArrayObjectPrototype(new UserNotification());

        $this->initialize();

    }	

    public function fetchAll() {  return $this->select();    }

    public function getUserNotification($user_notification_id){

        $user_notification_id  = (int) $user_notification_id;

        $rowset = $this->select(array('user_notification_id' => $user_notification_id));

        $row = $rowset->current();

        return $row;

    }	

	public function getUserNotificationForUser($user_notification_user_id){

        $user_notification_user_id  = (int) $user_notification_user_id;	

		$resultSet = $this->select(function (Select $select) use ($user_notification_user_id) {

			$select->where(array('user_notification_user_id'=>$user_notification_user_id));

			$select->order('user_notification_added_timestamp DESC');

		});		

        return $resultSet;

    }	

	public function getUserNotificationCountForUserUnread($user_notification_user_id){

        $user_notification_user_id  = (int) $user_notification_user_id;

		$select = new Select;

		$select->columns(array(new Expression('COUNT(y2m_user_notification.user_notification_id) as notification_count')))

					->from('y2m_user_notification')

					->where(array('user_notification_user_id'=>$user_notification_user_id,'user_notification_status' => 'unread'))

					->order('user_notification_added_timestamp DESC');		

		$statement = $this->adapter->createStatement();

		$select->prepareStatement($this->adapter, $statement);

		$resultSet = new ResultSet();

		//echo $select->getSqlString();die();

		$resultSet->initialize($statement->execute());	

		$row = $resultSet->current();

		return $row->notification_count;

    } 

    public function saveUserNotification(UserNotification $notification){	   

		$data = array(

            'user_notification_user_id' => $notification->user_notification_user_id,

            'user_notification_content'  => $notification->user_notification_content,

			'user_notification_added_timestamp'  => $notification->user_notification_added_timestamp,

			'user_notification_status'  => $notification->user_notification_status,

			'user_notification_notification_type_id'  => $notification->user_notification_notification_type_id ,

			'user_notification_sender_id'  => $notification->user_notification_sender_id,

			'user_notification_reference_id'=> $notification->user_notification_reference_id,
			'user_notification_process'=> $notification->user_notification_process,
        );

        $user_notification_id = (int)$notification->user_notification_id;

        if ($user_notification_id == 0) {

            $this->insert($data);

			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();

        } else {

            if ($this->getUserNotification($user_notification_id)) {

                $this->update($data, array('user_notification_id' => $user_notification_id));

            } else {

                throw new \Exception('Form id does not exist');

            }

        }

    }	

	public function saveUserNotificationStatus(UserNotification $notification){	   

		$data = array(

			'user_notification_status'  => $notification->user_notification_status

        ); 

		if ($this->getUserNotificationForUser($notification->user_notification_user_id)) {

			$this->update($data, array('user_notification_user_id' => $notification->user_notification_user_id));

		} else {

			throw new \Exception('Form id does not exist');

		}       

    }

    public function deleteUserNotification($user_notification_id){  $this->delete(array('user_notification_id' => $user_notification_id)); }

	public function getAllNotification($user_id){

		$select = new Select;

		$select->from("y2m_user_notification")

			   ->columns(array("user_notification_content","user_notification_added_timestamp"))

			   ->join("y2m_notification_type","y2m_notification_type.notification_type_id = y2m_user_notification.user_notification_notification_type_id",array("notification_type_title","notification_type_id"))

			   ->where(array("user_notification_user_id"=>$user_id,"user_notification_status"=>'unread'));			   

		$statement = $this->adapter->createStatement();

		$select->prepareStatement($this->adapter, $statement);

		$resultSet = new ResultSet();

		$resultSet->initialize($statement->execute());	

		return $resultSet->buffer();

	}

	public function getAllUnreadNotification($user_id){

		$select = new Select;

		$select->from("y2m_user_notification")

			   ->columns(array("user_notification_content","user_notification_added_timestamp"))

			   ->join("y2m_notification_type","y2m_notification_type.notification_type_id = y2m_user_notification.user_notification_notification_type_id",array("notification_type_title","notification_type_id"))

			   ->where(array("user_notification_user_id"=>$user_id,"user_notification_status"=>'unread'));

			   

		$statement = $this->adapter->createStatement();

		$select->prepareStatement($this->adapter, $statement);

		$resultSet = new ResultSet();

		$resultSet->initialize($statement->execute());	

		return $resultSet->buffer();

	}

	public function  getAllUserNotificationWithAllStatus($user_id,$offset,$limit){

		$select = new Select;

		$select->from("y2m_user_notification")

			   ->columns(array("user_notification_content","user_notification_added_timestamp","user_notification_sender_id","user_notification_reference_id","user_notification_status"))

			   ->join("y2m_notification_type","y2m_notification_type.notification_type_id = y2m_user_notification.user_notification_notification_type_id",array("notification_type_title","notification_type_id"))

			   ->where(array("user_notification_user_id"=>$user_id))

			   ->order('user_notification_added_timestamp DESC');;

		$select->limit($limit);

		$select->offset($offset);	   

		$statement = $this->adapter->createStatement();

		$select->prepareStatement($this->adapter, $statement);

		$resultSet = new ResultSet();

		$resultSet->initialize($statement->execute());	

		return $resultSet->buffer();

	}

	public function  makeNotificationsReaded($user_id){

		  $data['user_notification_status'] = 'read';

		 return $this->update($data, array('user_notification_user_id' => $user_id,'user_notification_status'=>'unread'));

	}

	public function getNotificationUnreadCount($user_id,$type){

		$user_notification_user_id  = (int) $user_id;

		$select = new Select;

		$select->columns(array(new Expression('COUNT(y2m_user_notification.user_notification_id) as notification_count')))

					->from('y2m_user_notification')

					->join("y2m_notification_type","y2m_notification_type.notification_type_id = y2m_user_notification.user_notification_notification_type_id",array())

					->where(array('user_notification_user_id'=>$user_notification_user_id,'user_notification_status' => 'unread'))					

					->order('user_notification_added_timestamp DESC');	

		if($type=="Group"){

			$select->where(array("(y2m_notification_type.notification_type_title='Group' OR y2m_notification_type.notification_type_title='Discussion' OR y2m_notification_type.notification_type_title='Photo' OR y2m_notification_type.notification_type_title='Video' )"));

		}

		if($type=="Friends"){

			$select->where(array("(y2m_notification_type.notification_type_title='User')"));

		}

		if($type=="Event"){

			$select->where(array("(y2m_notification_type.notification_type_title='Activity')"));

		}

		if($type=="Interactions"){

			$select->where(array("(y2m_notification_type.notification_type_title='Comments' OR y2m_notification_type.notification_type_title='Like')"));

		}

		$statement = $this->adapter->createStatement();

		$select->prepareStatement($this->adapter, $statement);

		$resultSet = new ResultSet();

		//echo $select->getSqlString();die();

		$resultSet->initialize($statement->execute());	

		$row = $resultSet->current();

		return $row->notification_count;

	}

	public function  getAllUserNotificationWithType($user_id,$type,$offset,$limit){  

		$select = new Select;

		$select->from("y2m_user_notification")

			   ->columns(array("user_notification_content","user_notification_added_timestamp","user_notification_sender_id","user_notification_reference_id","user_notification_status"))

			   ->join("y2m_notification_type","y2m_notification_type.notification_type_id = y2m_user_notification.user_notification_notification_type_id",array("notification_type_title","notification_type_id"))

			   ->where(array("user_notification_user_id"=>$user_id))

			   ->order('user_notification_added_timestamp DESC');

		if($type=="Groups"){

			$select->where(array("(y2m_notification_type.notification_type_title='Group' OR y2m_notification_type.notification_type_title='Discussion' OR y2m_notification_type.notification_type_title='Photo' OR y2m_notification_type.notification_type_title='Video' )"));

		}

		if($type=="Friends"){

			$select->where(array("(y2m_notification_type.notification_type_title='User')"));

		}

		if($type=="Events"){

			$select->where(array("(y2m_notification_type.notification_type_title='Activity')"));

		}

		if($type=="Interactions"){

			$select->where(array("(y2m_notification_type.notification_type_title='Comments' OR y2m_notification_type.notification_type_title='Like')"));

		}

		$select->limit($limit);

		$select->offset($offset);	   

		$statement = $this->adapter->createStatement();

		$select->prepareStatement($this->adapter, $statement);

		//echo $select->getSqlString();die();

		$resultSet = new ResultSet();

		$resultSet->initialize($statement->execute());	

		return $resultSet->buffer();

	} 
	public function getUserNotificationWithSenderInformation($user_id,$type,$offset,$limit){
		$select = new Select;

		$select->from("y2m_user_notification")

			   ->columns(array("user_notification_content","user_notification_added_timestamp","user_notification_sender_id","user_notification_reference_id","user_notification_status","user_notification_process"))

			   ->join("y2m_notification_type","y2m_notification_type.notification_type_id = y2m_user_notification.user_notification_notification_type_id",array("notification_type_title","notification_type_id"))

			   ->join('y2m_user', 'y2m_user.user_id = y2m_user_notification.user_notification_sender_id', array('user_given_name','user_id','user_profile_name','user_register_type','user_fbid'))
				->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			   ->where(array("user_notification_user_id"=>$user_id))

			   ->order('user_notification_added_timestamp DESC');

		if($type=="Groups"){

			$select->where(array("(y2m_notification_type.notification_type_title='Group Invite' OR y2m_notification_type.notification_type_title='Group joining Request' OR y2m_notification_type.notification_type_title='Group Joining Request Accepted' OR y2m_notification_type.notification_type_title='Discussion' OR y2m_notification_type.notification_type_title='Event' OR y2m_notification_type.notification_type_title='Media' OR y2m_notification_type.notification_type_title='Group Admin Promoted' )"));

		}

		if($type=="Friends"){

			$select->where(array("(y2m_notification_type.notification_type_title='Friend Request'  OR y2m_notification_type.notification_type_title='Friend Request Accept')"));

		}

		if($type=="Events"){

			$select->where(array("(y2m_notification_type.notification_type_title='Event')"));

		}

		if($type=="Interactions"){

			$select->where(array("(y2m_user_notification.user_notification_process='like' OR y2m_user_notification.user_notification_process='comment')"));

		}

		$select->limit($limit);

		$select->offset($offset);	   

		$statement = $this->adapter->createStatement();

		$select->prepareStatement($this->adapter, $statement);

		//echo $select->getSqlString();die();

		$resultSet = new ResultSet();

		$resultSet->initialize($statement->execute());	

		return $resultSet->buffer();
	}
	

}