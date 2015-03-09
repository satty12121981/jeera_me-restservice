<?php
namespace Tag\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Expression as predicate;
class ActivityTagTable extends AbstractTableGateway
{
    protected $table = 'y2m_activity_tag'; 

    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new ActivityTag());

        $this->initialize();
    }
	public function saveActivityTags($tag_data){
		return $this->insert($tag_data);
	}
	public function getActivityTags($activity_id){	
		
		$select = new Select;
		$select->from('y2m_activity_tag')
    		->join('y2m_tag', 'y2m_tag.tag_id = y2m_activity_tag.group_tag_id', array('tag_title','tag_id'))			 
			->where(array('y2m_activity_tag.activity_id' => $activity_id));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());		 
		 	
		return  $resultSet->buffer();
	}
	public function RemoveActivityTags($tag_id,$activity_id){
		return $this->delete(array('activity_id' => $activity_id,'group_tag_id'=>$tag_id));
	}
	 public function getCountOfAllActivityTags($search=''){
		$select = new Select;
		$select->from('y2m_group_activity')
			->columns(array(new Expression('COUNT(distinct(y2m_group_activity.group_activity_id)) as tag_count')))
			->join('y2m_activity_tag', 'y2m_group_activity.group_activity_id = y2m_activity_tag.activity_id', array(),'left')
			->join('y2m_tag', 'y2m_tag.tag_id = y2m_activity_tag.group_tag_id', array('tags'=>new Expression('GROUP_CONCAT(y2m_tag.tag_title)')),'left') ;
		 
		if($search!=''){
			$select->where->like('y2m_group_activity.group_activity_title',$search.'%')->or->like('y2m_tag.tag_title',$search.'%');		
		}
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return  $resultSet->current()->tag_count;
	 }
public function getAllActivityTags($limit,$offset,$field='group_activity_title',$order = 'ASC',$search=''){		 
       	$select = new Select;
		$select->from('y2m_group_activity')
			->columns(array('group_activity_title','group_activity_id'))
    		->join('y2m_activity_tag', 'y2m_group_activity.group_activity_id = y2m_activity_tag.activity_id', array(),'left')
			->join('y2m_tag', 'y2m_tag.tag_id = y2m_activity_tag.group_tag_id', array('tags'=>new Expression('GROUP_CONCAT(y2m_tag.tag_title)')),'left')		 
			->group('y2m_group_activity.group_activity_id');
		 
		if($search!=''){
			$select->where->like('y2m_group_activity.group_activity_title',$search.'%')->or->like('y2m_tag.tag_title',$search.'%');		
		}
		$select->limit($limit);
		$select->offset($offset);
		$select->order($field.' '.$order);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet;	   
	}
	public function fetchAllTagsOfActivity($activity_id){
		$select = new Select;
		$select->from('y2m_activity_tag')
    		->join('y2m_tag', 'y2m_tag.tag_id = y2m_activity_tag.group_tag_id', array('tag_title','tag_id'))			 
			->where(array('y2m_activity_tag.activity_id' => $activity_id))
			->order(array('y2m_activity_tag.group_tag_id ASC'));		 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer();
	}
	public function fetchAllTagsExceptActivity($activity_id,$group_id,$limit,$offset){
		$insubselect = new Select;
		$insubselect->from('y2m_activity_tag')
			->columns(array(new Expression('distinct(y2m_activity_tag.group_tag_id) as tag_id')))
			->where(array('y2m_activity_tag.activity_id' => $activity_id));	
			
		$subselect = new Select;
		$subselect->from('y2m_group_tag')
			->columns(array(new Expression('distinct(y2m_group_tag.group_tag_tag_id) as tag_id')))
			->where->addPredicate(new predicate('y2m_group_tag.group_tag_tag_id NOT IN(?)',array($insubselect)));
			;
		$subselect->where(array('y2m_group_tag.group_tag_group_id' => $group_id));		 
		$select = new Select;
		$select->from('y2m_tag')
				->columns(array('tag_id','tag_title'))
				->where->addPredicate(new predicate('y2m_tag.tag_id IN(?)',array($subselect)));
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet;
	}
	public function RemoveAllActivityTags($activity_id){
		 $this->delete(array('activity_id' => $activity_id));
		 return true;
	}
	
 
}