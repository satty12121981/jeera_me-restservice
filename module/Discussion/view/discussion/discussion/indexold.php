<br />
<?php $this->HeadScript()->appendFile($this->basePath().'/public/js/jquery-1.5.2.min.js','text/javascript'); ?>
 
  <?php $this->HeadScript()->appendFile($this->basePath().'/public/js/1625.js','text/javascript'); ?>
 <?php $this->HeadLink()->appendStylesheet($this->basePath().'/public/css/darkbox.css'); ?>
 <style type="text/css">
/* popup_box DIV-Styles*/
#popup_box { 
    display:none; /* Hide the DIV */
    position:fixed;  
    _position:absolute; /* hack for internet explorer 6 */  
    height:300px;  
    width:600px;  
    background:#FFFFFF;  
    left: 300px;
    top: 150px;
    z-index:100; /* Layering ( on-top of others), if you have lots of layers: I just maximized, you can change it yourself */
    margin-left: 15px;  
    
    /* additional features, can be omitted */
    border:2px solid #ff0000;      
    padding:15px;  
    font-size:15px;  
    -moz-box-shadow: 0 0 5px #ff0000;
    -webkit-box-shadow: 0 0 5px #ff0000;
    box-shadow: 0 0 5px #ff0000;
    
}
 
/* This is for the positioning of the Close Link */
#popupBoxClose {
    font-size:20px;  
    line-height:15px;  
    right:5px;  
    top:5px;  
    position:absolute;  
    color:#6fa5e2;  
    font-weight:500;      
}
</style>  
 <script>

 var group = '<?php echo $groupData->group_seo_title; ?>';
 var planet = '<?php echo $subGroupData->group_seo_title; ?>' ;
 var siteurl = '<?php echo $this->basePath(); ?>' ;
 var planet_id = '<?php echo $subGroupData->group_id; ?>' ;
 $("#discussion_loadmore").live("click",function(){
		$(this).hide();
		 var url			 = siteurl + "/discussion/loadmore/"+planet_id;
			 
			var callback = $.ajax({
			type: "POST",
			url: url,
			data:{
				'page': discussion_page,				 
			}, // serializes the form's elements.
			success: function(data)
				{				 
					$("#discussion_list_outer").append(data);
					//past_page = past_page+1;			 
				}
			});

			return false;
	});
	$(".mark_spam").live("click",function(){
		 var url			 = siteurl +"/group/"+group+"/"+planet+"/discussion/spamsproblems/"+this.id;
			 
			var callback = $.ajax({
			type: "POST",
			url: url,
			  // serializes the form's elements.
			success: function(data)
				{				 
					$("#popup_content").html(data);
					$('#popup_box').fadeIn("slow");
					$("#container").css({ // this is just for style
						"opacity": "0.3"  
					}); 		 
				} 
			 
			});

			return false;
	});
	$('#popupBoxClose').live("click", function() {            
          $('#popup_box').fadeOut("slow");
            $("#container").css({ // this is just for style        
                "opacity": "1"  
            }); 
        });
 </script>
 <script>
	var discussion_page = 1;
</script>
 
<div id="popup_box">    <!-- OUR PopupBox DIV-->
   <div id="popup_content"></div>
    <a id="popupBoxClose">Close</a>    
</div>
<div id='loadingDiv'> Please Wait... </div>
<div id="discussion_list">
	<!--flash Messages.Stored in session-->
	<?php if(isset($flashMessages) && count($flashMessages)) : ?>
	<ul class="session">
		<?php foreach ($flashMessages as $msg) : ?>
		<li><?php echo $msg; ?></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>

	<!--error Message-->
	<?php if(isset($error) && count($error)) : ?>
	<ul class="error">
		<?php foreach ($error as $errormsg) : ?>
		<li><?php echo $errormsg; ?></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>

	<!--success message-->
	<?php if(isset($success) && count($success)) : ?>
	<ul class="success">
		<?php foreach ($success as $successmsg) : ?>
		<li><?php echo $successmsg; ?></li>
		<?php endforeach; ?>
	</ul>
	<?php endif; ?>
	
	<table width="100%" border="1">
		<tr>
			<td width="20%" align="center"><a href="<?php echo $this->url('groups/planethome', array('action' => 'subgroupdetail', 'group_id'=>$groupData->group_seo_title, 'planet_id'=>$subGroupData->group_seo_title)) ?>">Activities</a></td>
			<td width="20%" align="center"><a href="<?php echo $this->url('groups/group-discussion', array('action' => 'subgroupdetailwithdiscussion', 'group_id'=>$groupData->group_seo_title, 'sub_group_id'=>$subGroupData->group_seo_title)) ?>">Discussion</a></td>
			<td width="20%" align="center"><a href="#">Media</a></td>
			<td width="20%" align="center"><a href="<?php echo $this->url('groups/group-members', array('action' => 'index', 'group_id'=>$groupData->group_seo_title, 'sub_group_id'=>$subGroupData->group_seo_title)) ?>">Members</a></td>
		</tr>
	</table>
	<br />
	<!-- Add discussion form -->
	 <?php
	$form = $this->form;
	$form->setAttribute('action', $this->url('groups/group-discussion', array('action' => 'index','group_id'=>$groupData->group_seo_title, 'sub_group_id'=>$subGroupData->group_seo_title)));
	$form->prepare();
	?>
	<?php echo $this->form()->openTag($form); ?>
	<?php echo $this->formRow($form->get('group_discussion_group_id')); ?>
	<?php echo $this->formRow($form->get('group_discussion_content')); ?>
	<br class="clear"/>
	<?php echo $this->formSubmit($form->get('submit')); ?>
	<?php echo $this->form()->closeTag(); ?>
	<div id="discussion_list_outer">
	<table class="table" id="table_list">
		<?php 
		if(isset($discussions) && count($discussions)) {
			foreach($discussions as $discussion): 
			?>
			<tr>
				<td width="50%"><?php echo $this->escapeHtmlAttr($discussion->group_discussion_content);?> </td>
				<td width="10%">
					<div id="likes_<?php echo $discussion->group_discussion_id;?>">
						<?php 
						if ( $discussion->user_check && $discussion->likes_count > 1 ) {
							echo "<a href=\"javascript:void(0)\" class=\"discussion-unlikes\" id=\"$discussion->group_discussion_id\">UnLike</a>&nbsp;&nbsp;You&nbsp;+&nbsp;";
							echo ($discussion->likes_count-2);
						} else if ($discussion->user_check && $discussion->likes_count == 1 ) {
							echo "<a href=\"javascript:void(0)\" class=\"discussion-unlikes\" id=\"$discussion->group_discussion_id\">UnLike</a>&nbsp;&nbsp;You&nbsp;";
						} else{
							echo "<a href=\"javascript:void(0)\" class=\"discussion-likes\" id=\"$discussion->group_discussion_id\">Like</a>&nbsp;&nbsp;".$discussion->likes_count;
						}
						?>
					</div>
				</td>
				<td width="10%">
					<div id="inner_content">
						<div id="spam_result_<?php echo $discussion->group_discussion_id;?>">
							<?php 
							if( empty($discussion->spam_user_check) ) { echo "<a href=\"javascript:void(0)\" id=\"$discussion->group_discussion_id\" class=\"mark_spam\" >Report Spam</a>";  } 
							else {  echo "Marked Spam";  }
							?>
						</div>
					</div>
				</td>
				<td width="30%">
					<a href="javascript:void(0)" class="discussion-comments" id="<?php echo $discussion->group_discussion_id;?>">comments</a>
					<div id="result_<?php echo $discussion->group_discussion_id;?>" style="display:none"></div>
				</td>
			</tr>
			<?php 
			unset($form_fieldset_element); 
			endforeach;
			?>
			
	</table>
	<div class="clearfix" style="clear:both"></div>
			<div><a href="javascript:void(0)" id="discussion_loadmore" >Load More..</a></div>
			<?php
		}else{
			echo "<div>No more discussions found in the system</div>";
		}
		?>
	</div>
	
</div>
	