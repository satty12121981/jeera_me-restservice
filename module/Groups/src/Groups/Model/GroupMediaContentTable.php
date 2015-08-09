<?php
namespace Groups\Model;
 
use Zend\Db\Sql\Select ;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class GroupMediaContentTable extends AbstractTableGateway
{ 
    protected $table = 'y2m_group_media_content';  
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new GroupMedia());
        $this->initialize();
    }
	public function saveGroupMediaContent(GroupMediaContent $objGroupMediaContent, $media_content_id='') {
		 
        $data = array(           
            'content'  => $objGroupMediaContent->content,
			'media_type' => $objGroupMediaContent->media_type,
		);
        if($media_content_id != ''){
			$this->update($data, array('media_content_id' => $media_content_id));
			return $media_content_id;
        }else {
			$this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        }
    }
	public function getMediaContents($media_content){
		if(!empty($media_content)){
			$select = new Select;
			$select->from('y2m_group_media_content')
					->where->in('media_content_id',$media_content);
			$statement = $this->adapter->createStatement();
			$select->prepareStatement($this->adapter, $statement);
			$resultSet = new ResultSet();		
			$resultSet->initialize($statement->execute());	  
			return $resultSet->toArray();
		}
		return false;
	}
	public function getMediafile($media_content){
		if(!empty($media_content)){
			$select = new Select;
			$select->from('y2m_group_media_content')
					->where(array('media_content_id'=>$media_content));
			$statement = $this->adapter->createStatement();		
			$select->prepareStatement($this->adapter, $statement);
			$resultSet = new ResultSet();		
			$resultSet->initialize($statement->execute());	  
			return $resultSet->current();
		}
		return false;
	}
	public function getAlbumIcon($album_id){
		$select = new Select;
		$icon_info = array();
		$select->from('y2m_group_media')			
			->where(array("media_album_id"=>$album_id));		 
		$statement = $this->adapter->createStatement();		
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();		
		$resultSet->initialize($statement->execute());	  
		$row =  $resultSet->current();
		if(!empty($row)&&$row->media_content!=''){
			$media_select = new Select;
			$media_select->from('y2m_group_media_content')			
				->where->in('media_content_id',json_decode($row->media_content));	 
			$statement = $this->adapter->createStatement();		
			$media_select->prepareStatement($this->adapter, $statement);
			$resultSet = new ResultSet();		
			$resultSet->initialize($statement->execute());	  
			$icon_info =  $resultSet->current();
			 
		}
		return $icon_info;
	}
	public function deleteContent($content_id){
		return $this->delete(array('media_content_id' => $content_id));
	}
}