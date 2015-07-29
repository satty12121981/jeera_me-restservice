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
	public function getAllActiveGroupAlbums($group_id){
		$select = new Select;
		$select->from('y2m_group_album')
				->where(array("y2m_group_album.group_id"=>$group_id,"y2m_group_album.album_status"=>'active'));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();			
	}
}