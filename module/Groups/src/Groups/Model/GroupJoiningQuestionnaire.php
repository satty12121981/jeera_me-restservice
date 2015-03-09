<?php
namespace Groups\Model;
class GroupJoiningQuestionnaire
{  	
    public $questionnaire_id;
    public $group_id;
    public $question;
	public $question_status;
	public $added_timestamp;
	public $added_ip;
	public $modified_timestamp;
	public $modified_ip;
	public $added_user_id;
	public $modified_user_id;
	public $answer_type;
	

    public function exchangeArray($data)
    {
		$this->questionnaire_id = (isset($data['questionnaire_id'])) ? $data['questionnaire_id'] : null;
		$this->group_id     = (isset($data['group_id'])) ? $data['group_id'] : null;        
        $this->question  = (isset($data['question'])) ? $data['question'] : null;
		$this->question_status  = (isset($data['question_status'])) ? $data['question_status'] : null;
		$this->added_timestamp  = (isset($data['added_timestamp'])) ? $data['added_timestamp'] : null;
		$this->added_ip  = (isset($data['added_ip'])) ? $data['added_ip'] : null;
		$this->modified_timestamp  = (isset($data['modified_timestamp'])) ? $data['modified_timestamp'] : null;
		$this->modified_ip  = (isset($data['modified_ip'])) ? $data['modified_ip'] : null;
		$this->added_user_id  = (isset($data['added_user_id'])) ? $data['added_user_id'] : null;
		$this->modified_user_id  = (isset($data['modified_user_id'])) ? $data['modified_user_id'] : null;	
		$this->answer_type  = (isset($data['answer_type'])) ? $data['answer_type'] : null;			
    }
	
	// Add the following method: This will be Needed for Edit. Please do not change it.
    public function getArrayCopy() {
        return get_object_vars($this);
    }
	
	#This function will be used to pass to send select box input for All Groups/Sub Groups
	public function selectFormatAllGroup($data) {
		 
		$selectObject = array();
		
		foreach($data as $group){			 
			$selectObject[$group->group_id] = $group->group_title;			
		}	
		 	
		return $selectObject;	//return blank array
	} 
	
	#This function will be used only in Admin Planet Tags. 
	public function selectFormatAllGroupForPlanetTags($data) {
		 
		$selectObject = array();
		foreach($data as $group){			 
			$selectObject[$group->group_id] = $group->parent_title." -- ".$group->group_title;			
		}			 	
		return $selectObject;	//return blank array
	} 
	
	public function generateGroupImageName() {
	  $id = uniqid();
      $id = base_convert($id, 16, 2);
      $id = str_pad($id, strlen($id) + (8 - (strlen($id) % 8)), '0', STR_PAD_LEFT);

      $chunks = str_split($id, 8);
      //$mask = (int) base_convert(IDGenerator::BIT_MASK, 2, 10);

      $id = array();
      foreach ($chunks as $key => $chunk) {
         //$chunk = str_pad(base_convert(base_convert($chunk, 2, 10) ^ $mask, 10, 2), 8, '0', STR_PAD_LEFT);
         if ($key & 1) {  // odd
            array_unshift($id, $chunk);
         } else {         // even
            array_push($id, $chunk);
         }
      }

      return base_convert(implode($id), 2, 36);
    }
	
    public function uploadGroupImage($type, $file, $rootPath, $adapter, $name) {
	
		//This function will return the name of file upload. False in ase of error uploading 
		//@type is "Galaxy or Planet"
		//@file is a array file
		//@rootPath will be absolute path to upload directory for example C:/wamp/www/1625/public/datagd/
		//@adapter is the service adapter
		//@name will be any name that you want to add with file
		
		$filename_prefix = $type.self::File_Delimiter;
		
		$adapter->setDestination($rootPath.self::Group_Thumb_Path.$type);
		
		#remove the while space and space froms string
		$name = str_replace(' ', '', $name);
		$name = preg_replace('/\s+/', '', $name);
		
		#create Unique File Name
		$filename = $filename_prefix.$name.self::File_Delimiter.self::generateGroupImageName().".".end(explode(".", $file['name']));
		$adapter->addFilter('File\Rename',array('target' => $adapter->getDestination().self::File_Seperator.$filename,'overwrite' => true));
		
		if($adapter->receive($file['name'])) {
			return $filename;
		} 
		else { 
			return false;
		} 			 
		return false;	
	}
}