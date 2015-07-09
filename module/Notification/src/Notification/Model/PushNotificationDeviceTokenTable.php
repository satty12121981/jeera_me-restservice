<?php
namespace Notification\Model;
use Zend\Db\Sql\Select;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;

class PushNotificationDeviceTokenTable extends AbstractTableGateway {

    protected $table = 'y2m_pushnotification_token';

    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new PushNotificationDeviceToken());
        $this->initialize();
    }

    public function fetchAll() {  return $this->select();    }

    public function getPushNotificationToken($pushnotification_token_id){
		$pushnotification_token_id  = (int) $pushnotification_token_id;
        $rowset = $this->select(array('pushnotification_token_id' => $pushnotification_token_id));
        $row = $rowset->current();
        return $row;
    }

    public function getPushNotificationTokenByDeviceTokenForUser($pushnotification_token_user_id,$pushnotification_device_token){
        $pushnotification_token_user_id  = (int) $pushnotification_token_user_id;
        $resultSet = $this->select(function (Select $select) use ($pushnotification_token_user_id,$pushnotification_device_token) {
            $select->where(array('pushnotification_token_user_id'=>$pushnotification_token_user_id,'device_token'=>$pushnotification_device_token));
            $select->order('device_token_time DESC');
        });
        return $resultSet;
    }

	public function getPushNotificationTokenForUser($pushnotification_token_user_id){
		$pushnotification_token_user_id  = (int) $pushnotification_token_user_id;
		$resultSet = $this->select(function (Select $select) use ($pushnotification_token_user_id) {
			$select->where(array('pushnotification_token_user_id'=>$pushnotification_token_user_id));
			$select->order('device_token_time DESC');
		});
        return $resultSet;
    }

    public function savePushNotificationToken(PushNotificationToken $push_notification_token){
		$data = array(
            'pushnotification_token_user_id' => $push_notification_token->pushnotification_token_user_id,
            'device_token'  => $push_notification_token->device_token,
			'device_type'  => $push_notification_token->device_type,
			'device_token_time'  => $push_notification_token->device_token_time,
        );
		$pushnotification_token_id = (int)$push_notification_token->pushnotification_token_id;
        if ($pushnotification_token_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
			if ($this->getPushNotificationToken($pushnotification_token_id)) {
                $this->update($data, array('pushnotification_token_id' => $pushnotification_token_id));
            } else {
                throw new \Exception('push notification token id does not exist');
            }
        }
    }

    public function deletePushNotificationToken($pushnotification_token_id)
    {
        $this->delete(array('pushnotification_token_id' => $pushnotification_token_id));
    }
    public function deletePushNotificationTokenForUser($pushnotification_token_user_id){
        $this->delete(array('pushnotification_token_user_id' => $pushnotification_token_user_id));
    }

}