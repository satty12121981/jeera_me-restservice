var tags_page = 0;
jQuery(document).ready(function(){
	$(document).on("click",".remove_tag",function(event){ 
		 var element = $(this).attr('id');
		 var data_id = element.replace('remove_tag_','');
		 var tag = $("#group_taggs_"+data_id).val();
		 var group_id = $("#group_id").val();
		 $('<div></div>').appendTo('body')
		  .html('<div><h6>Do you want to remove '+tag+' from this group tag list?</h6></div>')
		  .dialog({
			  modal: true, title: 'message', zIndex: 10000, autoOpen: true,
			  width: '350', resizable: false,
			  buttons: {
				  Yes: function () {
					  var url = base_url+'/jadmin/planettags/delete';
					  $.ajax({
							type: "POST",
							url:url,
							data: {'tag_id':data_id,'group_id':group_id},
							dataType:"json",							 
							success:function(result) {
								 if(result.success){
									alert(tag+" Removed from the list");
									$("#group_taggs_"+data_id).parent().remove();						  
								 }else{
								 alert(result.msg);	
									
								 }
							}}); 																											
					  $(this).dialog("close");
				  },
				  No: function () {					  
					  $(this).dialog("close");
				  }
			  },
			  close: function (event, ui) {
				  $(this).remove();
			  }
		});
	});
	$(document).on("click",".tags_loadmore",function(event){ 
		 var group_id = $("#group_id").val();
		 var url = base_url+'/jadmin/planettags/getTagList';
		if($(this).attr("id")!='add-tag-to-group'){
			$(this).remove();
		}
		 $.ajax({
			type: "POST",
			url:url,
			data: {'group_id':group_id,'page':tags_page},			 					 
			success:function(result) {				  
				$("#select_tags").append(result);	
				tags_page++;			
			},
			error: function(msg)
			{
				alert('error');
			}
		}); 
	});
	$(document).on("click",".tag_list",function(event){ 
		 var group_id = $("#group_id").val();
		 var id = $(this).attr("id");
		 var data_id = id.replace('tag_list_','');
		 var tag = $(this).text();
		 var url = base_url+'/jadmin/planettags/addGroupTag';
		 $.ajax({
			type: "POST",
			url:url,
			data: {'group_id':group_id,'tag_id':data_id},	
			dataType:"json",				
			success:function(result) {				  
				 if(result.error){
					alert(result.msg);								  
				 }else{		
					$("#tag_list_"+data_id).remove();
					content = '<li><a id="group_taggs_'+data_id+'" href="javascript:void(0)">'+tag+'</a><a id="remove_tag_'+data_id+'" class="remove_tag" href="javascript:void(0)"><img title="Trash" src="/jeera_new/public/images/trash.png"></a></li>'
					$("#group_selected_tags").append(content);
				 }
			},
			error: function(msg)
			{
				alert('error');
			}
		});
	});
	 
});