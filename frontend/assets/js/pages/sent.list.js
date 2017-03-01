var searchDialog = function () {
	var form = $('#email_sent_search');
	var runUserListener = function(){
		$("input[name=sender_user_name],input[name=receiver_user_name]", form).autocomplete({
			source: function( request, response ) {
				$.ajax({
					url: "/fa/userpanel/users",
					dataType: "json",
					data: {
						ajax:1,
						word: request.term
					},
					success: function( data ) {
						if(data.hasOwnProperty('status')){
							if(data.status){
								if(data.hasOwnProperty('items')){
									response( data.items );
								}
							}
						}

					}
				});
			},
			select: function( event, ui ) {
				var name = $(this).attr('name');
				name = name.substr(0, name.length - 5);
				$(this).val(ui.item.name+(ui.item.lastname ? ' '+ui.item.lastname : ''));
				$('input[name='+name+']', form).val(ui.item.id).trigger('change');
				return false;
			},
			focus: function( event, ui ) {
				var name = $(this).attr('name');
				name = name.substr(0, name.length - 5);
				$(this).val(ui.item.name+(ui.item.lastname ? ' '+ui.item.lastname : ''));
				$('input[name='+name+']', form).val(ui.item.id);
				return false;
			},
			create: function() {
		        $(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
					return $( "<li>" )
						.append( "<strong>" + item.name+(item.lastname ? ' '+item.lastname : '')+ "</strong><small class=\"ltr\">"+item.email+"</small><small class=\"ltr\">"+item.cellphone+"</small>" )
						.appendTo( ul );
				}
			},
			change:function(){
				if($(this).val() == ""){
					var name = $(this).attr('name');
					name = name.substr(0, name.length - 5);
					$('input[name='+name+']', form).val("");

				}
			},
			close:function(){
				if($(this).val() == ""){
					var name = $(this).attr('name');
					name = name.substr(0, name.length - 5);
					$('input[name='+name+']', form).val("");

				}
			}
		});
	};
	return {
		init: function() {
			runUserListener();

		}
	}
}();
$(function(){
	searchDialog.init();
});
