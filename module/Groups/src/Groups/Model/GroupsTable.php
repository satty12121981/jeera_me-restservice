<?php
namespace Groups\Model;
 
use Zend\Db\Sql\Select ;
use Zend\Db\TableGateway\AbstractTableGateway;
use Zend\Db\TableGateway\TableGateway;
use Zend\Db\TableGateway\Feature\RowGatewayFeature;
use Zend\Db\Adapter\Adapter;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
class GroupsTable extends AbstractTableGateway
{ 
    protected $table = 'y2m_group';  
    public function __construct(Adapter $adapter){
        $this->adapter = $adapter;
        $this->resultSetPrototype = new ResultSet();
        $this->resultSetPrototype->setArrayObjectPrototype(new Groups());
        $this->initialize();
    }
	public function generalGroupList($limit,$offset,$user=0){
		$sub_select = new Select;
		$sub_select2 = new Select;
		$sub_select->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as member_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array());
		$sub_select->group('y2m_group.group_id');
		$select_friend1 = new Select;
		$select_friend1->from('y2m_user_friend')
				   ->columns(array("user_friend_sender_user_id"))
				   ->where(array("user_friend_friend_user_id =".$user));
		$select_friend2 = new Select;
		$select_friend2->from('y2m_user_friend')
				   ->columns(array("user_friend_friend_user_id"))
				   ->where(array("user_friend_sender_user_id=".$user));
		$sub_select2->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as friend_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array())
				   ->where->in("user_group_user_id",$select_friend1)->or->in("user_group_user_id",$select_friend2);
		$sub_select2->group('y2m_group.group_id');
		$select = new Select;		
		$select->from('y2m_group')
			   ->columns(array('group_id'=>'group_id',
							 'group_title'=>'group_title',
							 'group_seo_title'=>'group_seo_title',
							 'group_description'=>'group_description',
							 'group_location'=>'group_location',
							 'group_city_id'=>'group_city_id',
							 'group_country_id'=>'group_country_id',
							 'group_location_lat'=>'group_location_lat',
							 'group_location_lng'=>'group_location_lng',
							 'group_web_address'=>'group_web_address',
							 'group_welcome_message_members'=>'group_welcome_message_members',
							 'group_web_address'=>'group_web_address',
							 'is_member'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_group WHERE y2m_user_group.user_group_user_id = '.$user.' AND y2m_user_group.user_group_group_id = y2m_group.group_id AND y2m_user_group.user_group_status=1),1,0)'),
							 'is_admin'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_group WHERE y2m_user_group.user_group_user_id = '.$user.' AND y2m_user_group.user_group_group_id = y2m_group.group_id AND y2m_user_group.user_group_is_owner = 1),1,0)')
				))
				->join(array('temp_member' => $sub_select), 'temp_member.group_id = y2m_group.group_id',array('member_count'),'left')
				->join(array('temp_friends' => $sub_select2), 'temp_friends.group_id = y2m_group.group_id',array('friend_count'),'left')
				->join("y2m_country","y2m_country.country_id = y2m_group.group_country_id",array("country_code_googlemap","country_title","country_code"),'left')
			    ->join("y2m_city","y2m_city.city_id = y2m_group.group_city_id",array("city"=>"name"),'left')
				->join("y2m_group_photo","y2m_group_photo.group_photo_group_id = y2m_group.group_id",array("group_photo_photo"=>"group_photo_photo"),'left')
				->where(array("y2m_group.group_status = 'active'"));
		$select->order(array('member_count DESC'));
		$select->limit($limit);
		$select->offset($offset);
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();	
	}
	public function getPlanetinfo($group_id){
		$select = new Select;
		$predicate = new  \Zend\Db\Sql\Where();
		$select->from('y2m_group')
		 ->where(array('y2m_group.group_id' => $group_id));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->current();
	}
	public function getGroupByName($group_title){         
        $rowset = $this->select(array('group_title' => $group_title));
        $row = $rowset->current();
		return $row;
    }
	public function saveGroupBasicDetails(Groups $objGroup, $intGroupId) {
         $data = array(
            'group_title'           => addslashes($objGroup->strGroupName),
            'group_seo_title'       => addslashes($objGroup->group_seo_title),
			'group_status'          => 'active',
			'group_description'     => addslashes($objGroup->strDesp),
			'group_city_id'         => $objGroup->intCityId,
			'group_country_id'      => $objGroup->intCountryId,
			'group_type'      => $objGroup->intGroupType
		);
        if($intGroupId != ''){
			$this->update($data, array('group_id' => $intGroupId));
			return $intGroupId;
        }else {
			$this->insert($data);
			return $this->adapter->getDriver()->getConnection()->getLastGeneratedValue();
        }
    }
	public function getGroupForSEO($group_seo_title){
      	$rowset = $this->select(array('group_seo_title' => $group_seo_title,'group_status'=>'active'));
		$row = $rowset->current();
        return $row;
    }
	public function getNewsFeeds($user_id,$type,$group_id,$activity,$limit,$offset){
		
		$result = new ResultSet();
		if($activity=='goingto'){
			$type = 'Event';
		}
		switch($type){
			case 'Text':
				$sql = 'SELECT group_discussion_id as event_id,group_discussion_added_timestamp as update_time,if(group_discussion_id,"New Status","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id  FROM  y2m_group_discussion INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_discussion.group_discussion_owner_user_id INNER JOIN y2m_group ON y2m_group_discussion.group_discussion_group_id = y2m_group.group_id  LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_discussion_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' AND user_group_status = "available" ) AND group_discussion_status = "available" AND y2m_user.user_status = "live" AND group_status = "active"';
				if($group_id!=''){
					$sql.=' AND group_id = '.$group_id;
				}
				if($activity == 'Interactions'){
					$sql.=' AND (group_discussion_id IN (SELECT comment_refer_id FROM y2m_comment WHERE comment_system_type_id = 2 AND comment_by_user_id = '.$user_id.') OR group_discussion_id IN(SELECT like_refer_id FROM y2m_like WHERE like_system_type_id = 2 AND like_by_user_id = '.$user_id.')) ' ;
				}
				if($activity == 'friends_post'){
					$sql.=' AND group_discussion_owner_user_id IN (SELECT  IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id) as friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = '.$user_id.' OR user_friend_friend_user_id = '.$user_id.' )  ' ;
				}
			break;
			case 'Media':
				$sql = 'SELECT group_media_id as event_id,media_added_date as update_time,if(group_media_id,"New Media","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_media INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_media.media_added_user_id INNER JOIN y2m_group ON y2m_group_media.media_added_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE media_added_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' AND user_group_status = "available" ) AND media_status = "active"  AND y2m_user.user_status = "live" AND group_status = "active"';
				if($group_id!=''){
					$sql.=' AND group_id = '.$group_id;
				}
				if($activity == 'Interactions'){
					$sql.=' AND (group_media_id IN (SELECT comment_refer_id FROM y2m_comment WHERE comment_system_type_id = 4 AND comment_by_user_id = '.$user_id.') OR group_media_id IN(SELECT like_refer_id FROM y2m_like WHERE like_system_type_id = 4 AND like_by_user_id = '.$user_id.')) ' ;
				}
				if($activity == 'friends_post'){
					$sql.=' AND media_added_user_id IN (SELECT  IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id) as friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = '.$user_id.' OR user_friend_friend_user_id = '.$user_id.' ) ' ;
				}
			break;
			case 'Event':
				$sql = 'SELECT group_activity_id as event_id,group_activity_added_timestamp as update_time,if(group_activity_id,"New Activity","") as type,	user_id,user_given_name,user_profile_name,user_fbid,profile_photo,group_title,group_seo_title,group_id FROM  y2m_group_activity INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id 	INNER JOIN y2m_group ON y2m_group_activity.group_activity_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_activity_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' AND user_group_status = "available") AND group_activity_status = "active" AND y2m_user.user_status = "live" AND group_status = "active"';
				if($group_id!=''){
					$sql.=' AND group_id = '.$group_id;
				}
				if($activity == 'Interactions'){
					$sql.=' AND (group_activity_id IN (SELECT comment_refer_id FROM y2m_comment WHERE comment_system_type_id = 1 AND comment_by_user_id = '.$user_id.') OR group_activity_id IN(SELECT like_refer_id FROM y2m_like WHERE like_system_type_id = 1 AND like_by_user_id = '.$user_id.')) ' ;
				}
				if($activity == 'friends_post'){
					$sql.=' AND group_activity_owner_user_id IN (SELECT  IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id) as friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = '.$user_id.' OR user_friend_friend_user_id = '.$user_id.' ) ' ;
				}
				if($activity == 'goingto'){
					$sql.=' AND group_activity_id IN (SELECT group_activity_rsvp_activity_id FROM y2m_group_activity_rsvp WHERE group_activity_rsvp_user_id = '.$user_id.') ' ;
				}
			break;
			default :
				$sql = 'SELECT group_activity_id as event_id,group_activity_added_timestamp as update_time,if(group_activity_id,"New Activity","") as type,	user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_activity INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id 	INNER JOIN y2m_group ON y2m_group_activity.group_activity_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_activity_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' AND user_group_status = "available") AND group_activity_status = "active" AND y2m_user.user_status = "live" AND group_status = "active"';
				if($group_id!=''){
					$sql.=' AND group_id = '.$group_id;
				}
				if($activity == 'Interactions'){
					$sql.=' AND (group_activity_id IN (SELECT comment_refer_id FROM y2m_comment WHERE comment_system_type_id = 1 AND comment_by_user_id = '.$user_id.') OR group_activity_id IN(SELECT like_refer_id FROM y2m_like WHERE like_system_type_id = 1 AND like_by_user_id = '.$user_id.')) ' ;
				}
				if($activity == 'friends_post'){
					$sql.=' AND group_activity_owner_user_id IN (SELECT  IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id) as friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = '.$user_id.' OR user_friend_friend_user_id = '.$user_id.' ) ' ;
				}
				$sql.=' UNION
				SELECT group_discussion_id as event_id,group_discussion_added_timestamp as update_time,if(group_discussion_id,"New Status","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id  FROM  y2m_group_discussion INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_discussion.group_discussion_owner_user_id INNER JOIN y2m_group ON y2m_group_discussion.group_discussion_group_id = y2m_group.group_id  LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_discussion_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' AND user_group_status = "available" ) AND group_discussion_status = "available" AND y2m_user.user_status = "live" AND group_status = "active"';
				if($group_id!=''){
					$sql.=' AND group_id = '.$group_id;
				}
				if($activity == 'Interactions'){
					$sql.=' AND (group_discussion_id IN (SELECT comment_refer_id FROM y2m_comment WHERE comment_system_type_id = 2 AND comment_by_user_id = '.$user_id.') OR group_discussion_id IN(SELECT like_refer_id FROM y2m_like WHERE like_system_type_id = 2 AND like_by_user_id = '.$user_id.')) ' ;
				}
				if($activity == 'friends_post'){
					$sql.=' AND group_discussion_owner_user_id IN (SELECT  IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id) as friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = '.$user_id.' OR user_friend_friend_user_id = '.$user_id.' ) ' ;
				}
				$sql.='
				 UNION
				SELECT group_media_id as event_id,media_added_date as update_time,if(group_media_id,"New Media","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_media INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_media.media_added_user_id INNER JOIN y2m_group ON y2m_group_media.media_added_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE media_added_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' AND user_group_status = "available" ) AND media_status = "active"  AND y2m_user.user_status = "live" AND group_status = "active"';
				if($group_id!=''){
					$sql.=' AND group_id = '.$group_id;
				}
				if($activity == 'Interactions'){
					$sql.=' AND (group_media_id IN (SELECT comment_refer_id FROM y2m_comment WHERE comment_system_type_id = 4 AND comment_by_user_id = '.$user_id.') OR group_media_id IN(SELECT like_refer_id FROM y2m_like WHERE like_system_type_id =4 AND like_by_user_id = '.$user_id.')) ' ;
				}
				if($activity == 'friends_post'){
					$sql.=' AND media_added_user_id IN (SELECT  IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id) as friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = '.$user_id.' OR user_friend_friend_user_id = '.$user_id.' ) ' ;
				}				
			
		}
		//echo $sql;die();
		$sql.=' ORDER BY update_time DESC LIMIT '.$offset.','.$limit; 		 
		$statement = $this->adapter-> query($sql); 		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->toArray();
	}
	public function getMyFeeds($user_id,$type,$limit,$offset){
		
		$result = new ResultSet();
		
		switch($type){
			case 'Text':
				$sql = 'SELECT group_discussion_id as event_id,group_discussion_added_timestamp as update_time,if(group_discussion_id,"New Status","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id  FROM  y2m_group_discussion INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_discussion.group_discussion_owner_user_id INNER JOIN y2m_group ON y2m_group_discussion.group_discussion_group_id = y2m_group.group_id  LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_discussion_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' AND user_group_status = "available" ) AND group_discussion_status = "available" AND y2m_user.user_status = "live" AND group_status = "active" AND y2m_group_discussion.group_discussion_owner_user_id='.$user_id;
				 
			break;
			case 'Media':
				$sql = 'SELECT group_media_id as event_id,media_added_date as update_time,if(group_media_id,"New Media","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_media INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_media.media_added_user_id INNER JOIN y2m_group ON y2m_group_media.media_added_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE media_added_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' AND user_group_status = "available" ) AND media_status = "active"  AND y2m_user.user_status = "live" AND group_status = "active" AND  y2m_group_media.media_added_user_id='.$user_id;
				 
			break;
			case 'Event':
				$sql = 'SELECT group_activity_id as event_id,group_activity_added_timestamp as update_time,if(group_activity_id,"New Activity","") as type,	user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_activity INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id 	INNER JOIN y2m_group ON y2m_group_activity.group_activity_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_activity_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' AND user_group_status = "available") AND group_activity_status = "active" AND y2m_user.user_status = "live" AND group_status = "active" AND y2m_group_activity.group_activity_owner_user_id ='.$user_id;
				 
			break;
			default :
				$sql = 'SELECT group_activity_id as event_id,group_activity_added_timestamp as update_time,if(group_activity_id,"New Activity","") as type,	user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_activity INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id 	INNER JOIN y2m_group ON y2m_group_activity.group_activity_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_activity_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' AND user_group_status = "available") AND group_activity_status = "active" AND y2m_user.user_status = "live" AND group_status = "active" AND y2m_group_activity.group_activity_owner_user_id ='.$user_id;
				 
				$sql.=' UNION
				SELECT group_discussion_id as event_id,group_discussion_added_timestamp as update_time,if(group_discussion_id,"New Status","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id  FROM  y2m_group_discussion INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_discussion.group_discussion_owner_user_id INNER JOIN y2m_group ON y2m_group_discussion.group_discussion_group_id = y2m_group.group_id  LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_discussion_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' AND user_group_status = "available" ) AND group_discussion_status = "available" AND y2m_user.user_status = "live" AND group_status = "active" AND y2m_group_discussion.group_discussion_owner_user_id='.$user_id; 
				$sql.='
				 UNION
				SELECT group_media_id as event_id,media_added_date as update_time,if(group_media_id,"New Media","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_media INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_media.media_added_user_id INNER JOIN y2m_group ON y2m_group_media.media_added_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE media_added_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_user_id = '.$user_id.' AND user_group_status = "available" ) AND media_status = "active"  AND y2m_user.user_status = "live" AND group_status = "active" AND  y2m_group_media.media_added_user_id='.$user_id;
			
		}
		//echo $sql;die();
		$sql.=' ORDER BY update_time DESC LIMIT '.$offset.','.$limit; 		 
		$statement = $this->adapter-> query($sql); 		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->toArray();
	}
	public function getMyActivity($user_id,$type,$limit,$offset){
		
		$result = new ResultSet();
		
		switch($type){
			case 'Comments':
				$sql = 'SELECT group_discussion_id as event_id,group_discussion_added_timestamp as update_time,if(group_discussion_id,"New Status","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id  FROM  y2m_group_discussion INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_discussion.group_discussion_owner_user_id INNER JOIN y2m_group ON y2m_group_discussion.group_discussion_group_id = y2m_group.group_id  LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_discussion_id IN (SELECT comment_refer_id FROM y2m_comment WHERE y2m_comment.comment_system_type_id =2 AND comment_by_user_id ='.$user_id.' )';
				$sql.='
				 UNION
				SELECT group_media_id as event_id,media_added_date as update_time,if(group_media_id,"New Media","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_media INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_media.media_added_user_id INNER JOIN y2m_group ON y2m_group_media.media_added_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id  WHERE group_media_id IN (SELECT comment_refer_id FROM y2m_comment WHERE y2m_comment.comment_system_type_id =4 AND comment_by_user_id ='.$user_id.' )';
				$sql.='
				 UNION
				 SELECT group_activity_id as event_id,group_activity_added_timestamp as update_time,if(group_activity_id,"New Activity","") as type,	user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_activity INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id 	INNER JOIN y2m_group ON y2m_group_activity.group_activity_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_activity_id IN (SELECT comment_refer_id FROM y2m_comment WHERE y2m_comment.comment_system_type_id =1 AND comment_by_user_id ='.$user_id.' )';
				 
			break;
			case 'Likes':
				$sql = 'SELECT group_discussion_id as event_id,group_discussion_added_timestamp as update_time,if(group_discussion_id,"New Status","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id  FROM  y2m_group_discussion INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_discussion.group_discussion_owner_user_id INNER JOIN y2m_group ON y2m_group_discussion.group_discussion_group_id = y2m_group.group_id  LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_discussion_id IN (SELECT like_refer_id FROM y2m_like WHERE y2m_like.like_system_type_id =2 AND like_by_user_id ='.$user_id.' )';
				$sql.='
				 UNION
				SELECT group_media_id as event_id,media_added_date as update_time,if(group_media_id,"New Media","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_media INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_media.media_added_user_id INNER JOIN y2m_group ON y2m_group_media.media_added_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id  WHERE group_media_id IN (SELECT like_refer_id FROM y2m_like WHERE y2m_like.like_system_type_id =4 AND like_by_user_id ='.$user_id.' )';
				$sql.='
				 UNION
				 SELECT group_activity_id as event_id,group_activity_added_timestamp as update_time,if(group_activity_id,"New Activity","") as type,	user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_activity INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id 	INNER JOIN y2m_group ON y2m_group_activity.group_activity_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_activity_id IN (SELECT like_refer_id FROM y2m_like WHERE y2m_like.like_system_type_id =1 AND like_by_user_id ='.$user_id.' )';
				 
			break;			 
			default :
				$sql = 'SELECT group_discussion_id as event_id,group_discussion_added_timestamp as update_time,if(group_discussion_id,"New Status","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id  FROM  y2m_group_discussion INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_discussion.group_discussion_owner_user_id INNER JOIN y2m_group ON y2m_group_discussion.group_discussion_group_id = y2m_group.group_id  LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_discussion_id IN (SELECT comment_refer_id FROM y2m_comment WHERE y2m_comment.comment_system_type_id =2 AND comment_by_user_id ='.$user_id.' ) OR group_discussion_id IN (SELECT like_refer_id FROM y2m_like WHERE y2m_like.like_system_type_id =2 AND like_by_user_id ='.$user_id.' )';
				$sql.='
				 UNION
				SELECT group_media_id as event_id,media_added_date as update_time,if(group_media_id,"New Media","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_media INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_media.media_added_user_id INNER JOIN y2m_group ON y2m_group_media.media_added_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id  WHERE group_media_id IN (SELECT comment_refer_id FROM y2m_comment WHERE y2m_comment.comment_system_type_id =4 AND comment_by_user_id ='.$user_id.' ) OR group_media_id IN (SELECT like_refer_id FROM y2m_like WHERE y2m_like.like_system_type_id =4 AND like_by_user_id ='.$user_id.' )';
				$sql.='
				 UNION
				 SELECT group_activity_id as event_id,group_activity_added_timestamp as update_time,if(group_activity_id,"New Activity","") as type,	user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_activity INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id 	INNER JOIN y2m_group ON y2m_group_activity.group_activity_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_activity_id IN (SELECT comment_refer_id FROM y2m_comment WHERE y2m_comment.comment_system_type_id =1 AND comment_by_user_id ='.$user_id.' ) OR group_activity_id IN (SELECT like_refer_id FROM y2m_like WHERE y2m_like.like_system_type_id =1 AND like_by_user_id ='.$user_id.' )';
			
		}
		//echo $sql;die();
		$sql.=' ORDER BY update_time DESC LIMIT '.$offset.','.$limit; 		 
		$statement = $this->adapter-> query($sql); 		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->toArray();
	}
	public function fetchSystemType($SystemTypeTitle){
		$SystemTypeTitle  = (string) $SystemTypeTitle;
		$table = new TableGateway('y2m_system_type', $this->adapter, new RowGatewayFeature('system_type_title'));
		$results = $table->select(array('system_type_title' => $SystemTypeTitle));
		$Row = $results->current();
		return $Row;
    }
	public function checkSeotitleExist($seotitle){
		$select = new Select;
		$select->from('y2m_group')
			   ->columns(array('group_id'))
			   ->where(array("group_seo_title"=>$seotitle));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);	
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		$row = $resultSet->current();
		if(!empty($row)&&$row->group_id!=''){
			return true;
		}else{
			return false;
		}
	}
	public function getGroupBySeoTitle($group_seo){
        $rowset = $this->select(array('group_seo_title' => $group_seo,'group_status'=>'active'));
        $row = $rowset->current();
		return $row;
    }
	public function getGroupDetails($group_id,$user_id){
		$sub_select = new Select;	
		$subselect2 = new Select;	
		$sub_select3 = new Select;	
		$sub_select->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as member_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array());
		$sub_select->group('y2m_group.group_id');
		$expression = new Expression(
            "IF (`user_friend_sender_user_id`= $user_id , `user_friend_friend_user_id`, `user_friend_sender_user_id`)"
        );
        $subselect2->from('y2m_user_friend')
            ->columns(array('friend_id'=>$expression))
            ->where->equalTo('user_friend_sender_user_id', $user_id)->OR->equalTo('user_friend_friend_user_id', $user_id)
           ;
		$sub_select3->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as friend_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array())
				   ->where->in("user_group_user_id",$subselect2);
		$sub_select3->group('y2m_group.group_id');
		$select = new Select;
		$select->from('y2m_group')
			   ->columns(array('group_id','group_title','group_seo_title','group_description','group_added_timestamp','group_type','is_admin'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_group WHERE  (y2m_user_group.user_group_group_id = y2m_group.group_id AND y2m_user_group.user_group_user_id = '.$user_id.' AND y2m_user_group.user_group_is_owner = 1)),1,0)'),
			   'is_member'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_group WHERE  (y2m_user_group.user_group_group_id = y2m_group.group_id AND y2m_user_group.user_group_user_id = '.$user_id.' AND y2m_user_group.user_group_is_owner = 0)),1,0)'),
			   'is_requested'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_group_joining_request WHERE  (y2m_user_group_joining_request.user_group_joining_request_group_id = y2m_group.group_id AND y2m_user_group_joining_request.user_group_joining_request_user_id = '.$user_id.' AND y2m_user_group_joining_request.user_group_joining_request_status = "active")),1,0)')))
			   ->join(array('temp_member' => $sub_select), 'temp_member.group_id = y2m_group.group_id',array('member_count'),'left')
			   ->join(array('temp_friends' => $sub_select3), 'temp_friends.group_id = y2m_group.group_id',array('friend_count'),'left')
			   ->join("y2m_country","y2m_country.country_id = y2m_group.group_country_id",array("country_code_googlemap","country_title","country_code"),'left')
			   ->join("y2m_city","y2m_city.city_id = y2m_group.group_city_id",array("city"=>"name"),'left')
			   ->join("y2m_group_photo","y2m_group_photo.group_photo_group_id = y2m_group.group_id",array("group_photo_photo"=>"group_photo_photo"),'left')
			   ->where(array("y2m_group.group_status = 'active'","y2m_group.group_id = ".$group_id));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);	
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		$row = $resultSet->current();
		return $row;
	}
	public function updateGroup($data,$group_id){
		 $this->update($data, array('group_id' => $group_id));
		return true;
	}
	public function searchGroup($search,$limit,$offset){
		$select = new Select;
		$sub_select = new Select;		
		$sub_select->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as member_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array());
		$sub_select->group('y2m_group.group_id');
		$select->from('y2m_group')
				 ->columns(array('group_id','group_title','group_seo_title','group_description','group_added_timestamp','group_type'))
				 ->join(array('temp_member' => $sub_select), 'temp_member.group_id = y2m_group.group_id',array('member_count'),'left')
				 ->join("y2m_country","y2m_country.country_id = y2m_group.group_country_id",array("country_code_googlemap","country_title","country_code"),'left')
			     ->join("y2m_city","y2m_city.city_id = y2m_group.group_city_id",array("city"=>"name"),'left')
			     ->join("y2m_group_photo","y2m_group_photo.group_photo_group_id = y2m_group.group_id",array("group_photo_photo"=>"group_photo_photo"),'left')
			     ->where(array("y2m_group.group_status = 'active'","y2m_group.group_title LIKE '%".$search."%'"));
		$select->limit($limit);
		$select->offset($offset);				 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);	
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->toArray();
		
	}
	public function getCountOfAllPlanet($group_id=null,$search=''){
		$predicate = new  \Zend\Db\Sql\Where();
		$select = new Select;
		$sub_select = new Select;
		$sub_select->from('y2m_group')
			   ->columns(array(new Expression('COUNT(y2m_group.group_id) as member_count'),"group_id"))
			   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array());
		$sub_select->group('y2m_group.group_id');
		$sub_select2 = new Select;
		$sub_select2->from('y2m_group')
			   ->columns(array(new Expression('COUNT(y2m_group.group_id) as activity_count'),"group_id"))
			   ->join(array('y2m_group_activity'=>'y2m_group_activity'),'y2m_group.group_id = y2m_group_activity.group_activity_group_id',array());
		$sub_select2->group('y2m_group.group_id');	
		$select->from(array('c' => 'y2m_group'))
			->columns(array(new Expression('COUNT(c.group_id) as group_count')))
			 ->join(array('p' => 'y2m_group'), 'c.group_id = p.group_parent_group_id',array())	
			 ->join(array('temp_member' => $sub_select), 'temp_member.group_id = c.group_id',array('member_count'),'left')
			 ->join(array('temp_activity' => $sub_select2), 'temp_activity.group_id = p.group_id',array('activity_count'),'left')
			 ->where($predicate->greaterThan('p.group_parent_group_id' , "0"))
			 ->order(array('c.group_id ASC'));
		//$select->columns(array('parent_title' => 'group_title'));
		if($group_id){	
			$select->where(array( 'p.group_parent_group_id' => $group_id));	
		}	
		if($search!=''){
			$select->where->like('c.group_title',$search.'%')->or->like('p.group_title',$search.'%');	
		}		
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return  $resultSet->current()->group_count;
		;	
	}
	public function getAllGroups($group_id=null,$limit,$offset,$field="group_id",$order='ASC',$search=''){ 	 		

			$predicate = new  \Zend\Db\Sql\Where();
			$sub_select = new Select;
			$sub_select->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as member_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array());
			$sub_select->group('y2m_group.group_id');			 
			$sub_select2 = new Select;
			$sub_select2->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as activity_count'),"group_id"))
				   ->join(array('y2m_group_activity'=>'y2m_group_activity'),'y2m_group.group_id = y2m_group_activity.group_activity_group_id',array());
			$sub_select2->group('y2m_group.group_id');		
			$select = new Select;
			$select->from(array('c' => 'y2m_group'))
				 //->join(array('p' => 'y2m_group'), 'c.group_id = p.group_parent_group_id', array('*'))	
				->join(array('temp_member' => $sub_select), 'temp_member.group_id = c.group_id',array('member_count'),'left')
			    ->join(array('temp_activity' => $sub_select2), 'temp_activity.group_id = c.group_id',array('activity_count'),'left')
				 //->where($predicate->greaterThan('p.group_parent_group_id' , "0"))
				 ;
			$select->columns(array('*'));
			if($group_id!=null){	
				$select->where(array( 'c.group_id' => $group_id));
			}
			if ($field == "group") {
				$field = 'y2m.group.group_id';
			}

			$select->limit($limit);
			$select->offset($offset);
			$select->order($field.' '.$order);
			if($search!=''){
				$select->where->like('c.group_title',$search.'%');
			}
			//echo $select->getSqlString(); exit;
			$statement = $this->adapter->createStatement();
			$select->prepareStatement($this->adapter, $statement);
			$resultSet = new ResultSet();
			$resultSet->initialize($statement->execute());
			return $resultSet;		 
    }
	public function getGroupIdFromSEO($group_seo_title){
      	$rowset = $this->select(array('group_seo_title' => $group_seo_title));
		$row = $rowset->current(); 
        return $row;
    }
	public function getGroup($group_id){
        $group_id  = (int) $group_id;
        $rowset = $this->select(array('group_id' => $group_id,'group_parent_group_id' => '0'));
        $row = $rowset->current();
        return $row;
    }
	 public function fetchAllGroups(){ 
		$select = new Select;
		$select->from('y2m_group')    			
			->where(array('y2m_group.group_parent_group_id' => "0"))
			->order(array('y2m_group.group_title ASC'));
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	  
		return $resultSet;	     
    }
	public function getPlanetDetailsForPalnetView($planet_id,$user_id){
		$sub_select = new Select;
		$sub_select->from('y2m_group')
				   ->columns(array(new Expression('COUNT(y2m_group.group_id) as member_count'),"group_id"))
				   ->join(array('y2m_user_group'=>'y2m_user_group'),'y2m_group.group_id = y2m_user_group.user_group_group_id',array());
		$sub_select->group('y2m_group.group_id');
		$select = new Select;
		$select->from('y2m_group')
			   ->columns(array("group_id"=>"group_id","group_status"=>"group_status","group_title"=>"group_title","group_seo_title"=>"group_seo_title","group_description"=>"group_description","group_location"=>"group_location","group_web_address"=>"group_web_address","group_added_timestamp"=>"group_added_timestamp","group_welcome_message_members"=>"group_welcome_message_members","group_location_lat"=>"group_location_lat","group_location_lng"=>"group_location_lng",'is_member'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_group WHERE y2m_user_group.user_group_user_id = '.$user_id.' AND y2m_user_group.user_group_group_id = y2m_group.group_id AND y2m_user_group.user_group_status=1),1,0)'),'is_admin'=>new Expression('IF(EXISTS(SELECT * FROM y2m_user_group WHERE y2m_user_group.user_group_user_id = '.$user_id.' AND y2m_user_group.user_group_group_id = y2m_group.group_id AND y2m_user_group.user_group_is_owner = 1),1,0)')))
			   ->join(array("galexy"=>"y2m_group"),"y2m_group.group_id = galexy.group_id",array("galexy_title"=>"group_title","galexy_seo_title"=>"group_seo_title"))			    
			   ->join(array('temp_member' => $sub_select), 'temp_member.group_id = y2m_group.group_id',array('member_count'),'left')
			   ->join("y2m_country","y2m_country.country_id = y2m_group.group_country_id",array("country_code_googlemap","country_title"),'left')
			   ->join("y2m_city","y2m_city.city_id = y2m_group.group_city_id",array("city"=>"name"),'left')
			   ->where(array("y2m_group.group_id"=>$planet_id));
		$statement = $this->adapter->createStatement();
		//echo $select->getSqlString();exit;
		$select->prepareStatement($this->adapter, $statement);		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());	
		return $resultSet->current();	
	}
	public function getPlanets($group_id=null){
		$group_id  = (int) $group_id;
		$predicate = new  \Zend\Db\Sql\Where();
		$select = new Select;
		$select->from('y2m_group')				 
			 ->where(array($predicate->greaterThan('y2m_group.group_parent_group_id' , "0"), 'y2m_group.group_parent_group_id' => $group_id))
			 ->order(array('y2m_group.group_id ASC'));		 
		$statement = $this->adapter->createStatement();
		$select->prepareStatement($this->adapter, $statement);
	//	echo $select->getSqlString();exit;
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->buffer();
	}
	public function getCountOfAllGroupsFilter($order='ASC',$group_type=null,$field_country=null,$field_city=null,$dateperiod,$datebetween){ 
		$results = new ResultSet();

		$groupFilter = null;
		$diffGroupSql = null;
		$flag_field_exist = null;
		$timedifffrom = null;
		$timediffto = null;

		switch( $dateperiod ) {
			case "week":
				$diffGroupSql = " YEARWEEK(`group_added_timestamp`) = YEARWEEK(CURRENT_DATE - INTERVAL 7 DAY) ";
			break;
			case "month":
				$diffGroupSql = " DATE_SUB(CURDATE(),INTERVAL 1 MONTH) <= `group_added_timestamp` ";
			break;
			case "period":
				$datebetween = explode("/", $datebetween);
				$timedifffrom = $datebetween[0];
				$timediffto = $datebetween[1];
				$diffGroupSql = " unix_timestamp( `group_added_timestamp` ) BETWEEN unix_timestamp( '".$timedifffrom."' ) AND unix_timestamp( '".$timediffto."' )";
			break;
			default:
				$diffGroupSql = '';
			break;
		}

		if ( $field_country && $field_city ) {
			$groupFilter = "WHERE `group_country_id` = ".$field_country." AND  `group_city_id` = ".$field_city;
			$flag_field_exist = true;
		}
		else if ( $field_country && !$field_city ) {
			$groupFilter = "WHERE `group_country_id` = ".$field_country;
			$flag_field_exist = true;
		}
		else if ( !$field_country && $field_city ) {
			$groupFilter = "WHERE `group_city_id` = ".$field_city;
			$flag_field_exist = true;
		}
		else {
			if ($diffGroupSql) $groupFilter = "WHERE" . $diffGroupSql;
		}


		if ($flag_field_exist == true) {
			if ($diffGroupSql) $groupFilter.= " AND" . $diffGroupSql;
		}

		if($group_type && $groupFilter) {
			$groupFilter.= " AND group_type = '" . $group_type."'" ;
		} else if($group_type && !$groupFilter) {
			$groupFilter = " WHERE group_type = '" . $group_type."'" ;
		}

		$sql = "SELECT `group_id`
				FROM `y2m_group`".$groupFilter."
				group by `group_id`
				ORDER BY `group_id` ".$order; 
		//echo $sql;
		//exit;
		$statement = $this->adapter->query($sql); 
		$results = $statement->execute();	
		return  $results->count();
	}
	public function getAllGroupsFilter($limit,$offset,$order='ASC',$group_type=null,$field_country=null,$field_city=null,$dateperiod,$datebetween){ 
		$results = new ResultSet();

		$groupFilter = null;
		$diffGroupSql = null;
		$flag_field_exist = null;
		$timedifffrom = null;
		$timediffto = null;

		switch( $dateperiod ) {
			case "week":
				$diffGroupSql = " YEARWEEK(`group_added_timestamp`) = YEARWEEK(CURRENT_DATE - INTERVAL 7 DAY) ";
			break;
			case "month":
				$diffGroupSql = " DATE_SUB(CURDATE(),INTERVAL 1 MONTH) <= `group_added_timestamp` ";
			break;
			case "period":
				$datebetween = explode("/", $datebetween);
				$timedifffrom = $datebetween[0];
				$timediffto = $datebetween[1];
				$diffGroupSql = " unix_timestamp( `group_added_timestamp` ) BETWEEN unix_timestamp( '".$timedifffrom."' ) AND unix_timestamp( '".$timediffto."' )";
			break;
			default:
				$diffGroupSql = '';
			break;
		}

		if ( $field_country && $field_city ) {
			$groupFilter = "WHERE `group_country_id` = ".$field_country." AND  `group_city_id` = ".$field_city;
			$flag_field_exist = true;
		}
		else if ( $field_country && !$field_city ) {
			$groupFilter = "WHERE `group_country_id` = ".$field_country;
			$flag_field_exist = true;
		}
		else if ( !$field_country && $field_city ) {
			$groupFilter = "WHERE `group_city_id` = ".$field_city;
			$flag_field_exist = true;
		}
		else {
			if ($diffGroupSql) $groupFilter = "WHERE" . $diffGroupSql;
		}


		if ($flag_field_exist == true) {
			if ($diffGroupSql) $groupFilter.= " AND" . $diffGroupSql;
		}

		if($group_type && $groupFilter) {
			$groupFilter.= " AND group_type = '" . $group_type."'" ;
		} else if($group_type && !$groupFilter) {
			$groupFilter = " WHERE group_type = '" . $group_type."'" ;
		}

		$sql = "SELECT *
				FROM `y2m_group` as g
				LEFT JOIN
				(
					SELECT d.group_discussion_group_id AS grp_id, count( d.group_discussion_id ) grp_dis_cnt
					FROM y2m_group_discussion d
					GROUP BY d.group_discussion_group_id
				) d ON d.grp_id = g.group_id
				LEFT JOIN (
					SELECT a.group_activity_group_id AS grp_id, count( a.group_activity_id ) grp_act_cnt
					FROM y2m_group_activity a
					GROUP BY a.group_activity_group_id
				) a ON d.grp_id = g.group_id
				LEFT JOIN (
					SELECT m.media_added_group_id AS grp_id, count( m.group_media_id ) grp_med_cnt
					FROM y2m_group_media m
					GROUP BY m.media_added_group_id
				) m ON m.grp_id = g.group_id ".$groupFilter." group by group_id
				ORDER BY grp_act_cnt ".$order. " , grp_dis_cnt ".$order. ", grp_med_cnt ".$order. " limit ".$limit." offset ".$offset; 
		
		//echo $sql;
		$statement = $this->adapter-> query($sql); 
		$results = $statement -> execute();	
		return $results;
	}
	public function getGroupNewsFeeds($user_id,$type,$group_id,$activity,$limit,$offset){
		
		$result = new ResultSet();
		if($activity=='goingto'){
			$type = 'Event';
		}
		switch($type){
			case 'Text':
				$sql = 'SELECT group_discussion_id as event_id,group_discussion_added_timestamp as update_time,if(group_discussion_id,"New Status","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id  FROM  y2m_group_discussion INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_discussion.group_discussion_owner_user_id INNER JOIN y2m_group ON y2m_group_discussion.group_discussion_group_id = y2m_group.group_id  LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_discussion_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_status = "available" ) AND group_discussion_status = "available" AND y2m_user.user_status = "live" AND group_status = "active" AND (group_type = "open" ||group_type = "public")';
				if($group_id!=''){
					$sql.=' AND group_id = '.$group_id;
				}
				if($activity == 'Interactions'){
					$sql.=' AND (group_discussion_id IN (SELECT comment_refer_id FROM y2m_comment WHERE comment_system_type_id = 2 AND comment_by_user_id = '.$user_id.') OR group_discussion_id IN(SELECT like_refer_id FROM y2m_like WHERE like_system_type_id = 2 AND like_by_user_id = '.$user_id.')) ' ;
				}
				if($activity == 'friends_post'){
					$sql.=' AND group_discussion_owner_user_id IN (SELECT  IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id) as friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = '.$user_id.' OR user_friend_friend_user_id = '.$user_id.' )  ' ;
				}
			break;
			case 'Media':
				$sql = 'SELECT group_media_id as event_id,media_added_date as update_time,if(group_media_id,"New Media","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_media INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_media.media_added_user_id INNER JOIN y2m_group ON y2m_group_media.media_added_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE media_added_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_status = "available" ) AND media_status = "active"  AND y2m_user.user_status = "live" AND group_status = "active" AND (group_type = "open" ||group_type = "public")';
				if($group_id!=''){
					$sql.=' AND group_id = '.$group_id;
				}
				if($activity == 'Interactions'){
					$sql.=' AND (group_media_id IN (SELECT comment_refer_id FROM y2m_comment WHERE comment_system_type_id = 4 AND comment_by_user_id = '.$user_id.') OR group_media_id IN(SELECT like_refer_id FROM y2m_like WHERE like_system_type_id = 4 AND like_by_user_id = '.$user_id.')) ' ;
				}
				if($activity == 'friends_post'){
					$sql.=' AND media_added_user_id IN (SELECT  IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id) as friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = '.$user_id.' OR user_friend_friend_user_id = '.$user_id.' ) ' ;
				}
			break;
			case 'Event':
				$sql = 'SELECT group_activity_id as event_id,group_activity_added_timestamp as update_time,if(group_activity_id,"New Activity","") as type,	user_id,user_given_name,user_profile_name,user_fbid,profile_photo,group_title,group_seo_title,group_id FROM  y2m_group_activity INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id 	INNER JOIN y2m_group ON y2m_group_activity.group_activity_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_activity_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_status = "available") AND group_activity_status = "active" AND y2m_user.user_status = "live" AND group_status = "active" AND (group_type = "open" ||group_type = "public")';
				if($group_id!=''){
					$sql.=' AND group_id = '.$group_id;
				}
				if($activity == 'Interactions'){
					$sql.=' AND (group_activity_id IN (SELECT comment_refer_id FROM y2m_comment WHERE comment_system_type_id = 1 AND comment_by_user_id = '.$user_id.') OR group_activity_id IN(SELECT like_refer_id FROM y2m_like WHERE like_system_type_id = 1 AND like_by_user_id = '.$user_id.')) ' ;
				}
				if($activity == 'friends_post'){
					$sql.=' AND group_activity_owner_user_id IN (SELECT  IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id) as friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = '.$user_id.' OR user_friend_friend_user_id = '.$user_id.' ) ' ;
				}
				if($activity == 'goingto'){
					$sql.=' AND group_activity_id IN (SELECT group_activity_rsvp_activity_id FROM y2m_group_activity_rsvp WHERE group_activity_rsvp_user_id = '.$user_id.') ' ;
				}
			break;
			default :
				$sql = 'SELECT group_activity_id as event_id,group_activity_added_timestamp as update_time,if(group_activity_id,"New Activity","") as type,	user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_activity INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_activity.group_activity_owner_user_id 	INNER JOIN y2m_group ON y2m_group_activity.group_activity_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_activity_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_status = "available") AND group_activity_status = "active" AND y2m_user.user_status = "live" AND group_status = "active" AND (group_type = "open" ||group_type = "public")';
				if($group_id!=''){
					$sql.=' AND group_id = '.$group_id;
				}
				if($activity == 'Interactions'){
					$sql.=' AND (group_activity_id IN (SELECT comment_refer_id FROM y2m_comment WHERE comment_system_type_id = 1 AND comment_by_user_id = '.$user_id.') OR group_activity_id IN(SELECT like_refer_id FROM y2m_like WHERE like_system_type_id = 1 AND like_by_user_id = '.$user_id.')) ' ;
				}
				if($activity == 'friends_post'){
					$sql.=' AND group_activity_owner_user_id IN (SELECT  IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id) as friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = '.$user_id.' OR user_friend_friend_user_id = '.$user_id.' ) ' ;
				}
				$sql.=' UNION
				SELECT group_discussion_id as event_id,group_discussion_added_timestamp as update_time,if(group_discussion_id,"New Status","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id  FROM  y2m_group_discussion INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_discussion.group_discussion_owner_user_id INNER JOIN y2m_group ON y2m_group_discussion.group_discussion_group_id = y2m_group.group_id  LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE group_discussion_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_status = "available" ) AND group_discussion_status = "available" AND y2m_user.user_status = "live" AND group_status = "active" AND (group_type = "open" ||group_type = "public")';
				if($group_id!=''){
					$sql.=' AND group_id = '.$group_id;
				}
				if($activity == 'Interactions'){
					$sql.=' AND (group_discussion_id IN (SELECT comment_refer_id FROM y2m_comment WHERE comment_system_type_id = 2 AND comment_by_user_id = '.$user_id.') OR group_discussion_id IN(SELECT like_refer_id FROM y2m_like WHERE like_system_type_id = 2 AND like_by_user_id = '.$user_id.')) ' ;
				}
				if($activity == 'friends_post'){
					$sql.=' AND group_discussion_owner_user_id IN (SELECT  IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id) as friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = '.$user_id.' OR user_friend_friend_user_id = '.$user_id.' ) ' ;
				}
				$sql.='
				UNION
				SELECT group_media_id as event_id,media_added_date as update_time,if(group_media_id,"New Media","") as type,user_id,user_given_name,user_profile_name,profile_photo,user_fbid,group_title,group_seo_title,group_id FROM  y2m_group_media INNER JOIN y2m_user ON y2m_user.user_id = y2m_group_media.media_added_user_id INNER JOIN y2m_group ON y2m_group_media.media_added_group_id = y2m_group.group_id LEFT JOIN y2m_user_profile_photo ON y2m_user.user_profile_photo_id = y2m_user_profile_photo.profile_photo_id WHERE media_added_group_id IN (SELECT user_group_group_id FROM y2m_user_group WHERE user_group_status = "available" ) AND media_status = "active"  AND y2m_user.user_status = "live" AND group_status = "active" AND (group_type = "open" ||group_type = "public")';
				if($group_id!=''){
					$sql.=' AND group_id = '.$group_id;
				}
				if($activity == 'Interactions'){
					$sql.=' AND (group_media_id IN (SELECT comment_refer_id FROM y2m_comment WHERE comment_system_type_id = 4 AND comment_by_user_id = '.$user_id.') OR group_media_id IN(SELECT like_refer_id FROM y2m_like WHERE like_system_type_id =4 AND like_by_user_id = '.$user_id.')) ' ;
				}
				if($activity == 'friends_post'){
					$sql.=' AND media_added_user_id IN (SELECT  IF(user_friend_sender_user_id='.$user_id.',user_friend_friend_user_id,user_friend_sender_user_id) as friend_user FROM y2m_user_friend WHERE user_friend_sender_user_id = '.$user_id.' OR user_friend_friend_user_id = '.$user_id.' ) ' ;
				}				
		}
	  	//echo $sql;die();
		$sql.=' ORDER BY update_time DESC LIMIT '.$offset.','.$limit; 		 
		$statement = $this->adapter-> query($sql); 		 
		$resultSet = new ResultSet();
		$resultSet->initialize($statement->execute());
		return $resultSet->toArray();
	}
}