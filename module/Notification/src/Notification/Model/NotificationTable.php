<?php



namespace Notification\Model;

use Zend\Db\Sql\Select;

use Zend\Db\TableGateway\AbstractTableGateway;

use Zend\Db\Adapter\Adapter;

use Zend\Db\ResultSet\ResultSet;



class NotificationTable extends AbstractTableGateway

{

    protected $table = 'y2m_notification_type'; 



    public function __construct(Adapter $adapter)

    {

        $this->adapter = $adapter;

        $this->resultSetPrototype = new ResultSet();

        $this->resultSetPrototype->setArrayObjectPrototype(new Notification());



        $this->initialize();

    }

    public function fetchAll()

    {

       $resultSet = $this->select();

        return $resultSet;

    }



   public function getNotification($notification_type_id)
   {

        $notification_type_id  = (int) $notification_type_id;

        $rowset = $this->select(array('notification_type_id' => $notification_type_id));

        $row = $rowset->current();

        return $row;

   }

    public function saveNotification(Notification $notification)
    {


	   $data = array(

            'notification_type_title' => $notification->notification_type_title,

            'notification_type_discription'  => $notification->notification_type_discription,

			'notification_type_added_date'  => $notification->notification_type_added_date,

			'notification_type_added_ip_address'  => $notification->notification_type_added_ip_address,

			'notification_type_modified_timestamp'  => $notification->notification_type_modified_timestamp,

			'notification_type_modified_ip_address'  => $notification->notification_type_modified_ip_address,

			'notification_type_status'  => $notification->notification_type_status 		

        );

        $notification_type_id = (int)$notification->notification_type_id;
        if ($notification_type_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getNotification($notification_type_id)) {
                $this->update($data, array('notification_type_id' => $notification_type_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }

    public function deleteNotification($notification_type_id)
    {
        $this->delete(array('notification_type_id' => $notification_type_id));
    }


}