<?php
namespace Album\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Crypt\BlockCipher;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
class GroupAlbumTable extends AbstractTableGateway
{
    protected $table = 'y2m_group_album'; 
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new GroupAlbum());
        $this->initialize();
    }
	public function getAlbum($album_id){
        $album_id  = (int) $album_id;
        $rowset = $this->select(array('album_id' => $album_id));
        $row = $rowset->current();
        return $row;
    }
    public function getGroupAlbum($group_id,$album_id){
        $album_id  = (int) $album_id;
        $rowset = $this->select(array('group_id' => $group_id,'album_id' => $album_id, 'album_status' => 'active'));
        $row = $rowset->current();
        return $row;
    }
	public function saveAlbum(GroupAlbum $groupAlbum){
		$data = array(
            'group_id' => $groupAlbum->group_id,
            'creator_id'  => $groupAlbum->creator_id,
			'album_title'  => $groupAlbum->album_title,
			'album_description'  => $groupAlbum->album_description,			 
			'created_ip'  => $groupAlbum->created_ip,
			'album_status'  => $groupAlbum->album_status,	
        );
        $album_id = (int)$groupAlbum->album_id;
        if ($album_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getAlbum($album_id)) {
                $this->update($data, array('album_id' => $album_id));
				return $album_id;
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
	public function getAllActiveGroupAlbums($group_id,$user_id,$is_admin){
		$select = new Select;
		$select->from('y2m_group_album')
				->where(array("y2m_group_album.group_id"=>$group_id,"y2m_group_album.album_status"=>'active'));
			if(!$is_admin){
				$select->where(array("y2m_group_album.album_id NOT IN (SELECT album_id from y2m_group_event_album inner join y2m_group_activity ON y2m_group_event_album.event_id = y2m_group_activity.group_activity_id WHERE y2m_group_activity.group_activity_group_id = $group_id) OR y2m_group_album.album_id IN (SELECT album_id from y2m_group_event_album Inner join y2m_group_activity ON y2m_group_event_album.event_id = y2m_group_activity.group_activity_id Inner join y2m_group_activity_rsvp ON y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_activity.group_activity_id where y2m_group_activity.group_activity_group_id = $group_id AND y2m_group_activity_rsvp.group_activity_rsvp_user_id = $user_id )")  );
			} 				
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();			
	}
	public function getAllGroupAlbums($group_id,$limit,$offset){
		$select = new Select;
		$select->from('y2m_group_album')				
				->join('y2m_group_event_album','y2m_group_event_album.album_id = y2m_group_album.album_id',array('event_id'),'left')
				->where(array("y2m_group_album.group_id"=>$group_id,"y2m_group_album.album_status"=>'active'));
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();
	}
	public function getAllEventAlbums($group_id,$limit,$offset){
		$select = new Select;
		$select->from('y2m_group_album')				
				->join('y2m_group_event_album','y2m_group_event_album.album_id = y2m_group_album.album_id',array('event_id'))
				->join('y2m_group_activity','y2m_group_activity.group_activity_id = y2m_group_event_album.event_id',array('group_activity_title'))
				->where(array("y2m_group_album.group_id"=>$group_id,"y2m_group_album.album_status"=>'active'));
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();
	}
    public function getAllActiveGroupAlbumsForAPI($group_id,$limit,$offset){
        $select = new Select;
        $select->from('y2m_group_album')
            //->join("y2m_group_media","y2m_group_media.media_album_id = y2m_group_album.album_id",array("group_media_id","media_content"),'left')
            ->join("y2m_group_event_album","y2m_group_event_album.album_id = y2m_group_album.album_id",array("event_album_id","event_id"),'left')
            ->where(array("y2m_group_album.group_id"=>$group_id,"y2m_group_album.album_status"=>'active'));
        if($limit){
            $select->limit($limit);
            $select->offset($offset);
        }
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);
        //echo $select->getSqlString();
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return $resultSet;
    }

    public function getAllAlbumsRelatedToUser($user_id,$group_id,$is_admin){
        $select = new Select;
        $select->from('y2m_group_album')
            ->where(array('y2m_group_album.album_status' => "active"));
        if($is_admin){
            $select->where(array("group_id"=>$group_id));
        }else{
            $select->where(array('y2m_group_album.creator_id' => $user_id,"y2m_group_album.group_id"=>$group_id,'y2m_group_album.album_status' => "active"));
        }
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return $resultSet->toArray();
    }

    public function getAlbumDetailsForGroupOrAlbumOwner($album_id,$user_id,$group_id,$is_admin){
        $select = new Select;
        $select->from('y2m_group_album')
            ->where(array('y2m_group_album.album_status' => "active"));
        if($is_admin){
            $select->where(array("group_id"=>$group_id, "album_id" => $album_id));
        }else{
            $select->where(array('album_id'=>$album_id,'y2m_group_album.creator_id' => $user_id,"y2m_group_album.group_id"=>$group_id));
        }
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);
        // echo $select->getSqlString();
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return $resultSet->toArray();
    }

    public function getEventAlbumForGroupAlbum($album_id,$group_id){
        $select = new Select;
        $select->from('y2m_group_album')
            ->join('y2m_group_event_album', 'y2m_group_event_album.album_id = y2m_group_album.album_id', array('event_id'))
            ->where(array('y2m_group_album.album_id' => $album_id,"y2m_group_album.group_id" => $group_id, "y2m_group_album.album_status" => 'active'));
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);
       // echo $select->getSqlString();
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return $resultSet->toArray();
    }
    public function getEventAlbumRsvpCheckForUserGroupAlbum($album_id,$event_id,$user_id,$group_id,$is_admin){
        $select = new Select;
        if($is_admin) {
            $select->from('y2m_group_album')
                ->where(array("y2m_group_album.group_id" => $group_id, "y2m_group_album.album_id" => $album_id, "y2m_group_album.album_status" => 'active'));
        }else{
            $select->from('y2m_group_event_album')
                ->join('y2m_group_activity_rsvp','y2m_group_activity_rsvp.group_activity_rsvp_activity_id = y2m_group_event_album.event_id',array('group_activity_rsvp_id'))
                ->where(array('y2m_group_event_album.album_id' => $album_id,'y2m_group_activity_rsvp.group_activity_rsvp_activity_id' => $event_id,'y2m_group_activity_rsvp.group_activity_rsvp_user_id' => $user_id,"y2m_group_activity_rsvp.group_activity_rsvp_group_id" => $group_id));
        }
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);
        //echo $select->getSqlString();
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return $resultSet->toArray();
    }
    public function deleteAlbum($album_id){
        return $this->delete(array('album_id' => $album_id));
    }

}