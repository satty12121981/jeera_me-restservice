<?php

namespace Notification\Model;



class NotificationType

{  

    public $notification_type_id;

    public $notification_type_title;

    public $notification_type_discription;

	public $notification_type_added_date;

	public $notification_type_status;	  

	



    public function exchangeArray($data)

    {

        $this->notification_type_id     = (isset($data['notification_type_id'])) ? $data['notification_type_id'] : null;

        $this->notification_type_title = (isset($data['notification_type_title'])) ? $data['notification_type_title'] : null;

        $this->notification_type_discription  = (isset($data['notification_type_discription'])) ? $data['notification_type_discription'] : null;

		$this->notification_type_added_date  = (isset($data['notification_type_added_date'])) ? $data['notification_type_added_date'] : null;

		$this->notification_type_status  = (isset($data['notification_type_status'])) ? $data['notification_type_status'] : null;

		 		

    }

	

	// Add the following method: This will be Needed for Edit. Please do not change it.

    public function getArrayCopy()

    {

        return get_object_vars($this);

    }

	

	

	

		

}