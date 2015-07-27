<?php
namespace Groups\Model;
 
use Zend\Db\Sql\Select ;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class GroupMediaTable extends AbstractTableGateway
{ 
    protected $table = 'y2m_group_media';  
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new GroupMedia());
        $this->initialize();
    }
	public function saveGroupMedia(GroupMedia $objGroupMedia, $group_media_id='') {
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) { $ip = $_SERVER['HTTP_CLIENT_IP'];} 
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }
		else {   $ip = $_SERVER['REMOTE_ADDR'];}
         $data = array(
            'media_added_user_id'   => $objGroupMedia->media_added_user_id,
            'media_added_group_id'  => $objGroupMedia->media_added_group_id,
			'media_type'            => $objGroupMedia->media_type,
			'media_content'         => $objGroupMedia->media_content,
			'media_caption'         => $objGroupMedia->media_caption,
			'media_added_ip'        => $ip,
			'media_status'          => $objGroupMedia->media_status,
			'media_added_date'		=> date("Y-m-d H:i:s"),
		);
        if($group_media_id != ''){
			$this->update($data, array('group_media_id' => $group_media_id));
			return $group_media_id;
        }else {
			$this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        }
    }
	public function getMediaForFeed($media_id){
		$media_id  = (int) $media_id;
        $rowset = $this->select(array('group_media_id' => $media_id));
        $row = $rowset->current();
        return $row;
	}
	public function getMedia($media_id){
		$media_id  = (int) $media_id;
        $rowset = $this->select(array('group_media_id' => $media_id));
        $row = $rowset->current();
        return $row;
	}
	public function getOneMedia($media_id){
		$select = new Select;
		$select->from('y2m_group_media')
			   ->join("y2m_group","y2m_group.group_id = y2m_group_media.media_added_group_id",array("group_title","group_seo_title","group_id"))
			   ->join("y2m_user","y2m_user.user_id = y2m_group_media.media_added_user_id",array("user_id","user_given_name","user_first_name","user_last_name","user_profile_name","user_fbid"))
			   ->join('y2m_user_profile_photo','y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id',array('profile_photo'),'left')
			   ->where(array("group_media_id"=>$media_id,"media_status"=>"active"));
		$statement = $this->adapter->createStatement();
		
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();		
		$resultSet->initialize($statement->execute());	  
		return $resultSet->current();
	}
	public function getNextMedia($group_id,$media_id){
		$select = new Select;
		$select->from('y2m_group_media')
			->columns(array("group_media_id"))
			->where('group_media_id>'.$media_id)
			->where(array("media_added_group_id"=>$group_id))
			->order(array('group_media_id ASC'));
		$statement = $this->adapter->createStatement();		
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();		
		$resultSet->initialize($statement->execute());	  
		return $resultSet->current();
	}
	public function getPreviousMedia($group_id,$media_id){
		$select = new Select;
		$select->from('y2m_group_media')
			->columns(array("group_media_id"))
			->where('group_media_id<'.$media_id)
			->where(array("media_added_group_id"=>$group_id))
			->order(array('group_media_id DESC'));
		$statement = $this->adapter->createStatement();		
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();		
		$resultSet->initialize($statement->execute());	  
		return $resultSet->current();
	}
	public function getAllMedia($group_id,$limit,$offset){
		$select = new Select;
		$select->from('y2m_group_media')
			->where(array("media_added_group_id"=>$group_id));
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement();		
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();		
		$resultSet->initialize($statement->execute());	  
		return $resultSet->toArray();
	}
	public function updateMedia($data,$group_media_id){
		return $this->update($data, array('group_media_id' => $group_media_id));
	}
	public function deleteMedia($group_media_id){
        return $this->delete(array('group_media_id' => $group_media_id));
    }
}