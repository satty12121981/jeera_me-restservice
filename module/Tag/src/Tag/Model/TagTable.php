<?php
namespace Tag\Model;
use Zend\Db\Sql\Select , \Zend\Db\Sql\Where;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Crypt\BlockCipher;	
use Zend\Db\Sql\Expression;
class TagTable extends AbstractTableGateway
{
    protected $table = 'y2m_tag'; 
    public function __construct(Adapter $adapter)
    {
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Tag());
        $this->initialize();
    }
    public function getCountOfAllTags($category,$search=''){
		$select = new Select;
		$select->from('y2m_tag')		
			   ->columns(array(new Expression('COUNT(y2m_tag.tag_id) as tag_count')))
			   ->join("y2m_tag_category","y2m_tag.category_id = y2m_tag_category.tag_category_id",array("tag_category_title"));
		if($category!='all'){
			$select->where(array("y2m_tag_category.tag_category_id"=>$category));
		}
		if($search!=''){
			$select->where->like('y2m_tag.tag_title',$search.'%')->or->like('y2m_tag_category.tag_category_title',$search.'%');		
		}
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return  $resultSet->current()->tag_count;
	}
	public function getAllTags($limit,$offset,$category,$field="tag_id",$order='ASC',$search=''){ 
		$select = new Select;
		$usersubselect = new select;
		$groupsubselect = new select;		 	
		$usersubselect->from('y2m_user_tag')
			->columns(array(new Expression('COUNT(y2m_user_tag.user_tag_id) as user_count'),'user_tag_tag_id'))
			->group(array('user_tag_tag_id'))
			;
		$groupsubselect->from('y2m_group_tag')
			->columns(array(new Expression('COUNT(y2m_group_tag.group_tag_id) as group_count'),'group_tag_tag_id'))
			->group(array('group_tag_tag_id'))
			;		 
		$select->from('y2m_tag')
				->columns(array('tag_id'=>'tag_id','tag_title'=>'tag_title'))				
				->join("y2m_tag_category","y2m_tag.category_id = y2m_tag_category.tag_category_id",array("tag_category_title"))
				->join(array('temp' => $usersubselect), 'temp.user_tag_tag_id = y2m_tag.tag_id',array('user_count'),'left')
				->join(array('temp1' => $groupsubselect), 'temp1.group_tag_tag_id = y2m_tag.tag_id',array('group_count'),'left');				 
		$select->limit($limit);
		$select->offset($offset);
		$select->order($field.' '.$order);
		if($category!='all'){
			$select->where(array("y2m_tag_category.tag_category_id"=>$category));
		}
		if($search!=''){
			$select->where->like('y2m_tag.tag_title',$search.'%')->or->like('y2m_tag_category.tag_category_title',$search.'%');		
		}
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());			 	
		return  $resultSet->buffer();
	}
	public function getTagByTitle($tag_title)
    {
        $tag_title  = (string) $tag_title;
        $rowset = $this->select(array('tag_title' => $tag_title));
		return $rowset->current();         
    }
	public function saveTag(Tag $tag)
    {
       $data = array(
			'category_id' => $tag->category_id,
            'tag_title' => $tag->tag_title,             
			'tag_added_ip_address'  => $tag->tag_added_ip_address			
        );
        $tag_id = (int)$tag->tag_id;
        if ($tag_id == 0) {
            $this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        } else {
            if ($this->getTag($tag_id)) {
                $this->update($data, array('tag_id' => $tag_id));
            } else {
                throw new \Exception('Form id does not exist');
            }
        }
    }
	public function getTag($tag_id){
        $tag_id  = (int) $tag_id;
        $rowset = $this->select(array('tag_id' => $tag_id));
        return $rowset->current(); 
    }
	public function deleteTag($tag_id){
        $this->delete(array('tag_id' => $tag_id));
    }
	public function getAllCategoryActiveTags($category_id,$search){
		$select = new Select;
		$select->from('y2m_tag')
				->where(array("y2m_tag.category_id"=>$category_id));
		if($search!='')		
				$select->where->like('y2m_tag.tag_title',$search.'%');
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());			 	
		return   $resultSet->toArray();
	}
	public function getAllTagsFilter($limit,$offset,$order='ASC',$type=null,$field_country=null,$field_city=null,$dateperiod,$datebetween){ 
		$result = new ResultSet();
		$userTagFilter = null;
		$groupTagFilter = null;
		$diffUserTagSql = null;
		$diffGroupTagSql = null;
		$flag_field_exist = null;
		$timedifffrom = null;
		$timedifffrom = null;

		switch( $dateperiod ) {

			case "week":
				$diffUserTagSql = " YEARWEEK(`user_tag_added_timestamp`) = YEARWEEK(CURRENT_DATE - INTERVAL 7 DAY) ";
				$diffGroupTagSql = " YEARWEEK(`group_tag_added_timestamp`) = YEARWEEK(CURRENT_DATE - INTERVAL 7 DAY) ";
			break;
			case "month":
				$diffUserTagSql = " DATE_SUB(CURDATE(),INTERVAL 1 MONTH) <= `user_tag_added_timestamp` ";
				$diffGroupTagSql = " DATE_SUB(CURDATE(),INTERVAL 1 MONTH) <= `group_tag_added_timestamp` ";
			break;
			case "period":
				$datebetween = explode("/", $datebetween);
				$timedifffrom = $datebetween[0];
				$timediffto = $datebetween[1];
				$diffUserTagSql = " unix_timestamp( `user_tag_added_timestamp` ) BETWEEN unix_timestamp( '".$timedifffrom."' ) AND unix_timestamp( '".$timediffto."' )";
				$diffGroupTagSql = " unix_timestamp( `group_tag_added_timestamp` ) BETWEEN unix_timestamp( '".$timedifffrom."' ) AND unix_timestamp( '".$timediffto."' )";
			break;
			default:
				$diffUserTagSql = '';
				$diffGroupTagSql = '';
			break;
		}

		if ( $field_country && $field_city ) {
			$userTagFilter = "WHERE `user_profile_country_id` = ".$field_country." AND  `user_profile_city_id` = ".$field_city;
			$groupTagFilter = "WHERE `group_country_id` = ".$field_country." AND  `group_city_id` = ".$field_city;
			$flag_field_exist = true;
		}
		else if ( $field_country && !$field_city ) {
			$userTagFilter = "WHERE `user_profile_country_id` = ".$field_country;
			$groupTagFilter = "WHERE `group_country_id` = ".$field_country;
			$flag_field_exist = true;
		}
		else if ( !$field_country && $field_city ) {
			$userTagFilter = "WHERE `user_profile_city_id` = ".$field_city;
			$groupTagFilter = "WHERE `group_city_id` = ".$field_city;
			$flag_field_exist = true;
		}
		else {
			if ($diffUserTagSql) $userTagFilter = "WHERE" . $diffUserTagSql;
			if ($diffGroupTagSql) $groupTagFilter = "WHERE" . $diffGroupTagSql;
		}

		if ($flag_field_exist == true)
		{
			if ($diffUserTagSql) $userTagFilter.= " AND" . $diffUserTagSql;
			if ($diffGroupTagSql) $groupTagFilter.= " AND" . $diffGroupTagSql;
		}

		if( $type == "group" ) {
			$sql = "SELECT count( `group_tag_id` ) as u_t, tag_id, category_id, tag_category_title, tag_title, group_country_id, group_city_id, group_tag_added_timestamp
				FROM `y2m_tag`
				JOIN `y2m_group_tag` ON `tag_id` = `group_tag_tag_id`
				JOIN `y2m_group` ON `group_id` = `group_tag_group_id`
				JOIN `y2m_tag_category` ON `y2m_tag_category`.`tag_category_id` = `category_id`
				".$groupTagFilter."
				GROUP BY `tag_id`
				order by u_t ".$order. " limit ".$limit." offset ".$offset; 	

		} else {
			$sql = "SELECT count( `user_tag_id` ) as u_t, tag_id, category_id, tag_category_title, tag_title, user_profile_country_id as ctry_id, user_profile_city_id as city_id, user_tag_added_timestamp as tag_added_date
				FROM `y2m_tag`
				JOIN `y2m_user_tag` ON `tag_id` = `user_tag_tag_id`
				JOIN `y2m_user_profile` ON `user_profile_user_id` = `user_tag_user_id`
				JOIN `y2m_tag_category` ON `y2m_tag_category`.`tag_category_id` = `category_id`
				".$userTagFilter."
				GROUP BY `tag_id`
				ORDER BY u_t ".$order. " limit ".$limit." offset ".$offset; 
		}
		//echo $sql;
		$statement = $this->adapter-> query($sql); 
		$results = $statement -> execute();	
		return $results;
	}
	public function getAllTagsWithCategories($category_id,$search){
		$select = new Select;
 		$expression = new Expression(
            "GROUP_CONCAT(tag_id,'|',tag_title)"
        );
		$select->from('y2m_tag')

				->columns(array('tag_title'=>$expression,'category_id'))				
				->join("y2m_tag_category","y2m_tag.category_id = y2m_tag_category.tag_category_id",array("tag_category_title","tag_category_icon","tag_category_desc"));
	
		$field = 'category_id';
		$order = 'ASC';
		$select->order($field.' '.$order);
		if( $category_id ){
			$select->where(array("y2m_tag_category.tag_category_id"=>$category_id));
		}
		if( $search !='' ){
			$select->where->like('y2m_tag.tag_title',$search.'%')->or->like('y2m_tag_category.tag_category_title',$search.'%');		
		}
		$select->group('y2m_tag.category_id');
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());			 	
		return  $resultSet->buffer();

	}

}