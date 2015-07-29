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
}