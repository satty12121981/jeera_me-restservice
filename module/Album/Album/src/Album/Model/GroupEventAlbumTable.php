<?php
namespace Album\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Crypt\BlockCipher;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
class GroupEventAlbumTable extends AbstractTableGateway
{
    protected $table = 'y2m_group_event_album'; 
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new GroupEventAlbum());
        $this->initialize();
    }
	public function getEventAlbum($eventalbum_id){
        $eventalbum_id  = (int) $eventalbum_id;
        $rowset = $this->select(array('event_album_id' => $eventalbum_id));
        $row = $rowset->current();
        return $row;
    }
	public function saveEventAlbum(GroupEventAlbum $groupEventAlbum){
		$data = array(
            'event_id' => $groupEventAlbum->event_id,
            'album_id'  => $groupEventAlbum->album_id,
			'assignedby'  => $groupEventAlbum->assignedby,			 
        );
        $event_album_id = (int)$groupEventAlbum->event_album_id;
        if ($event_album_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getEventAlbum($event_album_id)) {
                $this->update($data, array('event_album_id' => $event_album_id));
				return $event_album_id;
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }	 
}