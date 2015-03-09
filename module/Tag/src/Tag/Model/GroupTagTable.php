<?php
namespace Tag\Model;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Select; 
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\Expression as predicate;
class GroupTagTable extends AbstractTableGateway
{
    protected $table = 'y2m_group_tag';
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new GroupTag());
        $this->initialize();
    } 
	public function fetchAllTagsOfGroup($group_id,$limit = '',$offset='',$tag_string='')
    {      	 
		$select = new Select;
		$select->from('y2m_group_tag')
    		->join('y2m_tag', 'y2m_tag.tag_id = y2m_group_tag.group_tag_tag_id', array('tag_title','tag_id'))
			->join('y2m_group', 'y2m_group.group_id = y2m_group_tag.group_tag_group_id', array())
			->where(array('y2m_group_tag.group_tag_group_id' => $group_id));
		if($tag_string!=''){
			$select->where->like('tag_title','%'.$tag_string.'%');
		}
		$statement = $this->adapter->createStatement();
		if($limit!=''){
			$select->limit($limit);
			$select->offset($offset);
		}		
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());		 
		return $resultSet->toArray();	
    }	
	public function fetchAllGroupsOfTag($tag_id)
    {
      	$tag_id  = (int) $tag_id;
	  	$resultSet = $this->select(array('group_tag_tag_id' => $tag_id));
        return $resultSet;
    }
	public function deleteGroupTag($group_tag_id)
    {
        $this->delete(array('group_tag_id' => $group_tag_id));
    }
	public function getCountOfAllGroupTags($search=''){
		$select = new Select;
		$select->from('y2m_group')
			->columns(array(new Expression('COUNT(distinct(y2m_group.group_id)) as tag_count')))
			->join('y2m_group_tag', 'y2m_group.group_id = y2m_group_tag.group_tag_group_id', array(),'left')
			->join('y2m_tag', 'y2m_tag.tag_id = y2m_group_tag.group_tag_tag_id', array('tags'=>new Expression('GROUP_CONCAT(y2m_tag.tag_title)')),'left') ;		
		if($search!=''){
			$select->where->like('y2m_group.group_title',$search.'%')->or->like('y2m_tag.tag_title',$search.'%');		
		}
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return  $resultSet->current()->tag_count;
	}
	public function getAllGroupTags($limit,$offset,$field='group_title',$order = 'ASC',$search=''){		 
       	$select = new Select;
		$select->from('y2m_group')
			->columns(array('group_title','group_id'))
    		->join('y2m_group_tag', 'y2m_group.group_id = y2m_group_tag.group_tag_group_id', array(),'left')
			->join('y2m_tag', 'y2m_tag.tag_id = y2m_group_tag.group_tag_tag_id', array('tags'=>new Expression('GROUP_CONCAT(y2m_tag.tag_title)')),'left')		 
			->group('y2m_group.group_id');		 
		if($search!=''){
			$select->where->like('y2m_group.group_title',$search.'%')->or->like('y2m_tag.tag_title',$search.'%');		
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
	public function fetchAllTagsOfPlanet($planet_id){
		$select = new Select;
		$select->from('y2m_group_tag')
    		->join('y2m_tag', 'y2m_tag.tag_id = y2m_group_tag.group_tag_tag_id', array('tag_title','tag_id'))			 
			->where(array('y2m_group_tag.group_tag_group_id' => $planet_id))
			->order(array('y2m_group_tag.group_tag_tag_id ASC'));		 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->buffer();
	}
	public function removeTagFromGroup($planet_id,$tag_id){ 
		return $this->delete(array('group_tag_group_id' => $planet_id,'group_tag_tag_id' =>$tag_id )); 
	}
	public function fetchAllTagsExceptGroup($group_id,$limit,$offset){
		$subselect = new Select;
		$subselect->from('y2m_group_tag')
			->columns(array(new Expression('distinct(y2m_group_tag.group_tag_tag_id) as tag_id')))    					 
			;
		$subselect->where(array('y2m_group_tag.group_tag_group_id' => $group_id));
		$select = new Select;
		$select->from('y2m_tag')
				->columns(array('tag_id','tag_title'))
				->where->addPredicate(new predicate('y2m_tag.tag_id NOT IN(?)',array($subselect)));
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();die();
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet;
	}
	public function saveGroupTag(GroupTag $grouptag){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])) { $ip = $_SERVER['HTTP_CLIENT_IP'];} 
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {  $ip = $_SERVER['HTTP_X_FORWARDED_FOR']; }
		else {   $ip = $_SERVER['REMOTE_ADDR'];}
       $data = array(
			'group_tag_group_id'            => $grouptag->group_tag_group_id,
            'group_tag_added_ip_address'    => $ip,
			'group_tag_tag_id'              => $grouptag->group_tag_tag_id
        );
       $this->insert($data);
		return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
    }
	public function getAllGroupTagCategiry($group_id){
		$select = new Select;
		$select->from('y2m_group_tag')
    		->join('y2m_tag', 'y2m_tag.tag_id = y2m_group_tag.group_tag_tag_id',  array())
			->join('y2m_tag_category', 'y2m_tag_category.tag_category_id = y2m_tag.category_id', array('tag_category_title', 'tag_category_icon','tag_category_id'))
			->where(array('y2m_group_tag.group_tag_group_id' => $group_id))
			->where(array('y2m_tag_category.tag_category_status' => 1))			 
			->order(array('y2m_tag.tag_title ASC'));	

		$select->group('y2m_tag.category_id');			
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		//echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray(); 
	}
	public function fetchAllGroupTags($group_id){
		$select = new Select;
		$select->from('y2m_group_tag')
    		->join('y2m_tag', 'y2m_tag.tag_id = y2m_group_tag.group_tag_tag_id', array('tag_title','tag_id'))			 
			->where(array('y2m_group_tag.group_tag_group_id' => $group_id))
			->order(array('y2m_group_tag.group_tag_tag_id ASC'));		 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();
	}
	public function checkGroupTag($group_id, $tag_id){
        $group_id  = (int) $group_id;
		$tag_id  = (int) $tag_id;
        $rowset = $this->select(array('group_tag_group_id' => $group_id, 'group_tag_tag_id' => $tag_id));
        return $rowset->current();        
    }
	public function deleteAllGroupTags($group_id,$tag_list=array()){
		if(!empty($tag_list)){
			$sql = "DELETE FROM y2m_group_tag WHERE group_tag_group_id = ".$group_id." AND group_tag_tag_id NOT IN (".implode(',',$tag_list).")"; 
			$statement = $this->adapter-> query($sql); 
			$statement -> execute();
		}else{ 	$this->delete(array('group_tag_group_id' => $group_id)); }		
	}
}