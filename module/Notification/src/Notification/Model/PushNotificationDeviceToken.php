<?php
namespace Notification\Model;

class PushNotificationDeviceToken {
    public $pushnotification_token_id;
    public $pushnotification_token_user_id;
    public $device_token;
	public $device_type;
    public $device_token_time;

    public function exchangeArray($data) {
        $this->pushnotification_token_id     = (isset($data['pushnotification_token_id'])) ? $data['pushnotification_token_id'] : null;
        $this->pushnotification_token_user_id = (isset($data['pushnotification_token_user_id'])) ? $data['pushnotification_token_user_id'] : null;
        $this->device_token  = (isset($data['device_token'])) ? $data['device_token'] : null;
		$this->device_type  = (isset($data['device_type'])) ? $data['device_type'] : null;
        $this->device_token_time  = (isset($data['device_token_time'])) ? $data['device_token_time'] : null;
    }

    public function getArrayCopy() {
        return get_object_vars($this);
    }
}