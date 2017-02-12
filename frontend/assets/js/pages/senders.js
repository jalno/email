var addSender = function(){
	var form = $('.create_form');
	var $table_addresses = $('.table-addresses', form);
	var showSenderFields = function(){
		$('select[name=sender]', form).change(function(){
			var $val = $(this).val();
			$('.senderfields:not(.sender-'+$val+")",form).hide();
			$('.senderfields.sender-'+$val, form).show();
		}).trigger('change');
	};
	var addAddressListener = function(){
		var address_form = $('#address_add_form');
		var validator = function(){
			address_form.validate({
	            rules: {
	                address: {
	                    required: true
	                },
					name: {
	                    required: true
	                }
	            },
	            submitHandler: submit
	        });
		}
		var submit = function(){
			var address = $('input[name=address]', address_form).val();
			var name = $('input[name=name]', address_form).val();
			var status = parseInt($('select[name=status]', address_form).val());
			var primary = $('input[name=primary]', address_form).prop('checked');
			var addresses = $table_addresses.data('addresses');
			if(!addresses){
				addresses = new Array();
			}
			var found =false;
			for(var i =0;i!=addresses.length && !found;i++){
				if(addresses[i].address == address){
					found = true;
				}
			}
			if(!found){
				if(primary){
					for(var i =0;i!=addresses.length;i++){
						if(addresses[i].primary){
							addresses[i].primary = false;
						}
					}
				}
				addresses.push({
					address:address,
					name:name,
					status:status,
					primary:primary
				});
				$table_addresses.data('addresses', addresses);
				rebuildAddressesTable();

				$('#address-add').modal('hide');
			}else{
				$('input[name=address]', address_form).inputMsg({
					message: "این آدرس قبلا وارد شده"
				});
			}
		}
		validator();
		$('#address-add').on('hide.bs.modal', function(){
			address_form[0].reset();
		});
	}
	var rebuildAddressesTable = function(){
		var addresses = $table_addresses.data('addresses');
		if(!addresses){
			addresses = new Array();
		}
		var html = '';
		for(var i =0;i!=addresses.length;i++){
			var status,primary;
			switch(addresses[i].status){
				case(1):status = 'فعال';break;
				case(2):status = 'غیرفعال';break;
			}
			primary = addresses[i].primary ? '<label class="label label-success">بله</label>' : 'خیر';

			html += '<tr>';
			html += '<td>'+(i+1)+'</td>';
			html += '<td>'+addresses[i].address+'</td>';
			html += '<td>'+addresses[i].name+'</td>';
			html += '<td>'+status+'</td>';
			html += '<td>'+primary+'</td>';
			html += '<td class="center">';
				html += '<a href="#" class="btn btn-xs btn-danger btn-delete tooltips" data-address="'+addresses[i].address+'" title="حذف"><i class="fa fa-trash"></i></a>';
			html += '</td>';
			html += '</tr>';
		}
		var $tbody = $('tbody', $table_addresses);
		$tbody.html(html);
		$('.btn-delete', $tbody).on('click', btnAddressDeleteClick);
		var html = '';
		for(var i =0;i!=addresses.length;i++){
			for(var x in addresses[i]){
				if(x != 'primary' || addresses[i][x]){
					if(x == 'primary'){
						addresses[i][x] = 1;
					}
					html += '<input type="hidden" name="addresses['+i+']['+x+']" value="'+addresses[i][x]+'">';
				}
			}
		}
		$('.addressesfields', form).html(html);

	}
	var btnAddressDeleteClick = function(e){
		e.preventDefault();
		$('#address_delete_form input[name=address]').val($(this).data('address'));
		$('#address-delete').modal('show');
	}
	var formAddressDeleteSubmit = function(){
		$('#address_delete_form').on('submit', function(e){
			e.preventDefault();
			var address = $('input[name=address]', this).val();
			var addresses = $table_addresses.data('addresses');
			for(var i =0;i!=addresses.length;i++){
				if(addresses[i].address == address){
					addresses.splice(i, 1);
					break;
				}
			}
			$('#address-delete').modal('hide');
			rebuildAddressesTable();
		});
	}
	return {
		init: function() {
			showSenderFields();
			addAddressListener();
			formAddressDeleteSubmit();
			rebuildAddressesTable();
		}
	}
}();
$(function(){
	addSender.init();
});
