<?php
namespace Tag\Model;
use Zend\Db\Sql\Select, Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Crypt\BlockCipher;	#for encryption
use Zend\Db\Sql\Expression;
class TagCategoryTable extends AbstractTableGateway
{
    protected $table = 'y2m_tag_category'; 
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new TagCategory());
        $this->initialize();
    }
	public function getGroupCategories($limit,$offset,$group_id){
		$select = new Select;	
		$select->from('y2m_tag_category')
				   ->columns(array("tag_category_id","tag_category_title","tag_category_icon"))
				   ->join("y2m_tag","y2m_tag_category.tag_category_id = y2m_tag.category_id",array())
				   ->join("y2m_group_tag","y2m_tag.tag_id = y2m_group_tag.group_tag_tag_id",array())
				   ->where(array("y2m_group_tag.group_tag_group_id",$group_id))
				   ->where(array("tag_category_status",1));
		$select->group('y2m_tag_category.tag_category_id');
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();
	}
	public function getCountOfAllTagCategories($search=''){
        $select = new Select;
        $select->from('y2m_tag_category')        
               ->columns(array(new Expression('COUNT(y2m_tag_category.tag_category_id) as tag_category_count')));
        if($search!=''){
            $select->where->like('y2m_tag_category.tag_category_title',$search.'%');      
        }
        $statement = $this->adapter->createStatement();
        $select->prepareStatement($this->adapter, $statement);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());
        return  $resultSet->current()->tag_category_count;
    }
	public function getAllTagCategories($limit,$offset,$field="tag_category_id",$order='ASC',$search=''){ 
        $select = new Select;
        $usersubselect = new select;
        $groupsubselect = new select;   
        $tagsubselect = new select;   

        $tagsubselect->from('y2m_tag')
            ->columns(array(new Expression('COUNT(y2m_tag.category_id) as tag_count'),'category_id','tag_id'))
            ->group(array('category_id'))
            ;
        $usersubselect->from('y2m_user_tag')
            ->columns(array(new Expression('COUNT(y2m_user_tag.user_tag_id) as user_count'),'user_tag_tag_id'))
            ->group(array('user_tag_tag_id'))
            ;
        $groupsubselect->from('y2m_group_tag')
            ->columns(array(new Expression('COUNT(y2m_group_tag.group_tag_id) as group_count'),'group_tag_tag_id'))
            ->group(array('group_tag_tag_id'))
            ;

        $select->from('y2m_tag_category')
            ->columns(array('tag_category_id'=>'tag_category_id','tag_category_title'=>'tag_category_title'))
            ->join(array('temp2' => $tagsubselect), 'temp2.category_id = y2m_tag_category.tag_category_id',array('tag_count'),'left')
            ->join(array('temp' => $usersubselect), 'temp.user_tag_tag_id = temp2.tag_id',array('user_count'),'left')
            ->join(array('temp1' => $groupsubselect), 'temp1.group_tag_tag_id = temp2.tag_id',array('group_count'),'left');

        $select->limit($limit);
        $select->offset($offset);
        $select->order($field.' '.$order);
        if($search!=''){
            $select->where->like('y2m_tag_category.tag_category_title',$search.'%');      
        }
        $statement = $this->adapter->createStatement();
        //echo $select->getSqlString();
        $select->prepareStatement($this->adapter, $statement);
        $resultSet = new ResultSet();
        $resultSet->initialize($statement->execute());              
        return  $resultSet->buffer();
    }
	public function fetchAll(){
       $resultSet = $this->select();
       return $resultSet;
    }
	public function saveTagCategory(TagCategory $tag_category){
        $data = array(
            'tag_category_title' => $tag_category->tag_category_title,
            'tag_category_icon'  => $tag_category->tag_category_icon,   
            'tag_category_desc'  => $tag_category->tag_category_desc,
			'tag_category_status'  => $tag_category->tag_category_status		
        );
        $tag_category_id = (int)$tag_category->tag_category_id;
        if ($tag_category_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getTagCategory($tag_category_id)) {
                $this->update($data, array('tag_category_id' => $tag_category_id));
            } else {
                throw new \Exception('tag category id does not exist');
            }
        }
    }
	public function getTagCategory($tag_category_id){
        $tag_category_id  = (int) $tag_category_id;
        $rowset = $this->select(array('tag_category_id' => $tag_category_id));
        return $rowset->current();        
    }
	public function deleteTagCategory($tag_category_id){
        $this->delete(array('tag_category_id' => $tag_category_id));
    }
	public function getActiveCategories(){
		$select = new Select;
		$select->from('y2m_tag_category')
				->where(array("tag_category_status",1));
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();
	}
}