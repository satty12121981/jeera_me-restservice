<?php

namespace Notification\Model;
use Zend\Db\Sql\Select, \Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;

class NotificationTypeTable extends AbstractTableGateway
{
    protected $table = 'y2m_notification_type'; 

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new NotificationType());

        $this->initialize();
    }
    public function fetchAll()
    {
       $resultSet = $this->select();
       return $resultSet;
    }

   public function get_notification_type($notification_type_id)
    {   
        $notification_type_id  = (int) $notification_type_id;
        $rowset = $this->select(array('notification_type_id' => $notification_type_id));
        $row = $rowset->current();
        
        return $row;
    } 

    public function savenotificationType(NotificationType $notification)
    {
	
	   $data = array(
            'notification_type_title' => $notification->notification_type_title,
            'notification_type_discription'  => $notification->notification_type_discription,
			'notification_type_status'  => $notification->notification_type_status 		
        );

            $notification_type_id = (int)$notification->notification_type_id; 
        if ($notification_type_id == 0) {
		  
            $this->insert($data);
			$lastId = $this->adapter->getDriver()->getLastGeneratedValue();
			$result = "";
			if($lastId){
			$result = "success";
			}
			else{
			$result = "fail";
			}
			return $result;

        } else {
            $result = "";		
            if ($this->get_notification_type($notification_type_id)) {
			    
                $this->update($data, array('notification_type_id' => $notification_type_id));
				$result = "success";
            } else {
			    $result = "fail";
                throw new \Exception('Form id does not exist');
            }
			return $result;
        }
    }
	public function Admin_enable_notificationtype($notification_type_id)
	{
	 $notification_type_id  = (int) $notification_type_id;
       $data = array('notification_type_status' => 1);
       $result = '';	   
	   if($this->update($data, array('notification_type_id' => $notification_type_id)))
	   {
	    $result = 'success';
	   }else{
		$result = 'fail';
	   }
	   return $result;
	}
	public function Admin_disable_notificationtype($notification_type_id)
	{
	$notification_type_id  = (int) $notification_type_id;
       $data = array('notification_type_status' => 0);
       $result = '';	   
	   if($this->update($data, array('notification_type_id' => $notification_type_id)))
	   {
	    $result = 'success';
	   }else{
		$result = 'fail';
	   }
	   return $result;
	}

    public function deleteNotificationType($notification_type_id)
    { 
	   $notification_type_id  = (int) $notification_type_id;
	   $result = '';	   
	   if($this->delete(array('notification_type_id' => $notification_type_id)))
	   {
	    $result = 'success';
	   }else{
		$result = 'fail';
	   }
	   return $result;
    }

}