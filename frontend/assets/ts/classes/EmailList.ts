import "jquery-ui/ui/widgets/autocomplete.js";
import { Router, AjaxResponse } from "webuilder";
interface user{
	id:number;
	name:string;
	lastname:string;
	email:string;
	cellphone:string;
}
interface searchResponse extends AjaxResponse{
	items: user[];
}
export class EmailList{
	private static form = $('#emaillist_search');
	private static runUserListener = function(){
		function select(event, ui):boolean{
			let name = $(this).attr('name');
			name = name.substr(0, name.length - 5);
			$(this).val(ui.item.name+(ui.item.lastname ? ' '+ui.item.lastname : ''));
			$(`input[name="${name}"]`, EmailList.form).val(ui.item.id).trigger('change');
			return false;
		}
		function unselect(){
			if($(this).val() == ""){
				let name = $(this).attr('name');
				name = name.substr(0, name.length - 5);
				$('input[name='+name+']', EmailList.form).val("");
			}
		}
		$("input[name=sender_user_name], input[name=receiver_user_name]", EmailList.form).autocomplete({
			source: function( request, response ) {
				$.ajax({
					url: Router.url("userpanel/users"),
					dataType: "json",
					data: {
						ajax: 1,
						word: request.term
					},
					success: function( data: searchResponse) {
						if(data.status){
							response( data.items );
						}
					}
				});
			},
			select: select,
			focus: select,
			change:unselect,
			close:unselect,
			create: function() {
		        $(this).data('ui-autocomplete')._renderItem = function( ul, item ) {
					return $( "<li>" )
						.append( "<strong>" + item.name+(item.lastname ? ' '+item.lastname : '')+ "</strong><small class=\"ltr\">"+item.email+"</small><small class=\"ltr\">"+item.cellphone+"</small>" )
						.appendTo( ul );
				}
			}
		});
	}
	private static runEmailReader():void{
		const $messages = $('body.emaillist .panel .messages');
		const $content = $('.messages-content', $messages);
		$('li.messages-item', $messages).on('click', function(){
			$('li.messages-item.active', $messages).removeClass('active');
			$(this).addClass('active');
			$('.message-time', $content).html($('.messages-item-time .text', $(this)).data('time'));
			$('.message-from', $content).html($(this).data('from'));
			$('.message-to', $content).html($(this).data('to'));
			$('.message-subject', $content).html($('.messages-item-subject', $(this)).html());
			$('.message-content iframe', $content).attr("src", Router.url(`userpanel/email/${$(this).data('type')}/view/${$(this).data('email')}`));
			if($('.message-actions .send', $content).length){
				$('.message-actions .send', $content).attr("href", Router.url(`userpanel/email/send?to=${$(this).data('to')}`));
			}
			if($('.message-actions .forward', $content).length){	
				$('.message-actions .forward', $content).attr("href", Router.url(`userpanel/email/send?forward=${$(this).data('email')}&type=${$(this).data('type')}`));
			}
			$('.message-actions .open-email', $content).attr("href", Router.url(`userpanel/email/${$(this).data('type')}/view/${$(this).data('email')}`));
			if($('.message-actions .load-email', $content).length){
				$('.message-actions .load-email', $content).data("href", Router.url(`userpanel/email/${$(this).data('type')}/view/${$(this).data('email')}?externalFiles=1`)).css({color: '#999999'});
			}
		});
		$('li.messages-item', $messages).first().trigger('click');
		if($('.message-actions .load-email', $content).length){
			$('.message-actions .load-email', $content).on('click',function(e){
				e.preventDefault();
				const $iframe = $('.message-content iframe', $content);
				const $opener = $('.message-actions .open-email', $content);
				if(!$(this).data('show')){
					$(this).data('show', true);
					$(this).css({
						color: '#007AFF'
					});
					$iframe.data('src', $iframe.attr("src"));
					$iframe.attr("src", $(this).data('href'));
					$opener.data('href', $opener.attr("href"));
					$opener.attr("href", $(this).data('href'));
				}else{
					$(this).data('show', false);
					$(this).css({
						color: '#999999'
					});
					$iframe.attr("src", $iframe.data('src'));
					$opener.attr("href", $opener.data('href'));
				}
			});
		}
	}
	public static init():void{
		EmailList.runUserListener();
		EmailList.runEmailReader();
	}
	public static initIfNeeded():void{
		if($('body').hasClass('emaillist')){
			EmailList.init();
		}
	}
}