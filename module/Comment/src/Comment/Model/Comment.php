<?php

namespace Comment\Model;

class Comment 

{  	

	const File_Delimiter          = '-';

	const File_Seperator          = '/';

	public $comment_id;

    public $comment_sytem_type_id;

    public $comment_by_user_id;

	public $comment_status;

	public $comment_content;

	public $comment_added_timestamp;

	public $comment_added_ip_address;

	public $comment_refer_id;



	

    public function exchangeArray($data)

    {

        $this->comment_id     = (isset($data['comment_id'])) ? $data['comment_id'] : null;

        $this->comment_system_type_id = (isset($data['comment_system_type_id'])) ? $data['comment_system_type_id'] : null;

        $this->comment_by_user_id	  = (isset($data['comment_by_user_id'])) ? $data['comment_by_user_id'] : null;

		$this->comment_status  = (isset($data['comment_status'])) ? $data['comment_status'] : null;

		$this->comment_content  = (isset($data['comment_content'])) ? $data['comment_content'] : null;

		$this->comment_added_timestamp  = (isset($data['comment_added_timestamp'])) ? $data['comment_added_timestamp'] : null;

		$this->comment_added_ip_address  = (isset($data['comment_added_ip_address'])) ? $data['comment_added_ip_address'] : null;

		$this->comment_refer_id  = (isset($data['comment_refer_id'])) ? $data['comment_refer_id'] : null;

    }

	

	#Add the following method: This will be Needed for Edit. Please do not change it.

    public function getArrayCopy()

    {

        return get_object_vars($this);

    }

	

	#This function will be used to pass to send select box input for All comments/Sub comments

	public function selectFormatAllcomment($data)

	{

		 

		$selectObject =array();

		foreach($data as $comment){			 

			$selectObject[$comment->comment_id] = $comment->comment_content;			

		}	

		 	

		return $selectObject;	//return blank array

	} 



}