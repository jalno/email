import "@jalno/translator";
import "jquery-validation";
import "bootstrap";
import "bootstrap-inputmsg";
interface Address{
	name:string;
	address:string;
	status:number;
	primary:boolean;
}
export class Senders{
	private static form = $('.senders_form');
	private static $table_addresses = $('.table-addresses', Senders.form);
	private static showSenderFields():void{
		$('select[name=sender]', Senders.form).change(function(){
			const $val = $(this).val();
			$(`.senderfields:not(.sender-${$val})`,Senders.form).hide();
			$('.senderfields.sender-'+$val, Senders.form).show();
		}).trigger('change');
	}
	private static addAddressListener():void{
		const form = $('#address_add_form');
		let submit = function(){
			const name = $('input[name=name]', form).val();
			const address = $('input[name=address]', form).val();
			const status = parseInt($('select[name=status]', form).val());
			const primary = $('input[name=primary]', form).prop('checked');
			let addresses: Address[] = Senders.$table_addresses.data('addresses');
			if(!addresses){
				addresses = [];
			}
			let found = false;
			for(let i =0;i!=addresses.length && !found;i++){
				if(addresses[i].address == address){
					found = true;
				}
			}
			if(!found){
				if(primary){
					for(let i =0;i!=addresses.length;i++){
						if(addresses[i].primary){
							addresses[i].primary = false;
						}
					}
				}
				let newAddress: Address = {
					name:name,
					address:address,
					status:status,
					primary:primary
				};
				addresses.push(newAddress);
				Senders.$table_addresses.data('addresses', addresses);
				Senders.rebuildAddresssTable();
				$('#address-add').modal('hide');
			}else{
				$('input[name=address]', form).inputMsg({
					message: t("email.data_duplicate.cellphone"),
				});
			}
		}
		form.validate({
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
		$('#address-add').on('hide.bs.modal', function(){
			(form[0] as HTMLFormElement).reset();
		});
	}
	private static rebuildAddresssTable():void{
		let addresses :Address[] = Senders.$table_addresses.data('addresses');
		if(!addresses){
			addresses = [];
		}
		let html = '';
		for(let i =0;i!=addresses.length;i++){
			let status,primary;
			switch(addresses[i].status){
				case(1):status = t("email.sender.status.active");break;
				case(2):status = t("email.sender.status.deactive");break;
			}
			primary = addresses[i].primary ? `<label class="label label-success">${t("yes")}</label>` : t("no");

			html += '<tr>';
			html += '<td>'+(i+1)+'</td>';
			html += '<td>'+addresses[i].address+'</td>';
			html += '<td>'+addresses[i].name+'</td>';
			html += '<td>'+status+'</td>';
			html += '<td>'+primary+'</td>';
			html += '<td class="center">';
				html += '<a href="#" class="btn btn-xs btn-danger btn-delete tooltips" data-address="'+addresses[i].address+`" title="${t("delete")}"><i class="fa fa-trash"></i></a>`;
			html += '</td>';
			html += '</tr>';
		}
		let $tbody = $('tbody', Senders.$table_addresses);
		$tbody.html(html);
		$('.btn-delete', $tbody).on('click', Senders.btnAddressDeleteClick);
		html = '';
		for(let i =0;i!=addresses.length;i++){
			for(let x in addresses[i]){
				if(x != 'primary' || addresses[i][x]){
					if(x == 'primary'){
						addresses[i][x] = true;
					}
					html += `<input type="hidden" name="addresses[${i}][${x}]" value="${addresses[i][x]}">`;
				}
			}
		}
		$('.addressesfields', Senders.form).html(html);
	}
	private static btnAddressDeleteClick(e:Event):void{
		e.preventDefault();
		$('#address_delete_form input[name=address]').val($(this).data('address'));
		$('#address-delete').modal('show');
	}
	private static formAddressDeleteSubmit():void{
		$('#address_delete_form').on('submit', function(e){
			e.preventDefault();
			let address = $('input[name=address]', this).val();
			let addresses : Address[]= Senders.$table_addresses.data('addresses');
			for(let i =0;i!=addresses.length;i++){
				if(addresses[i].address == address){
					addresses.splice(i, 1);
					break;
				}
			}
			$('#address-delete').modal('hide');
			Senders.rebuildAddresssTable();
		});
	}
	public static init():void{
		Senders.showSenderFields();
		Senders.addAddressListener();
		Senders.formAddressDeleteSubmit();
		Senders.rebuildAddresssTable();
	}
	public static initIfNeeded():void{
		if($('body').hasClass('email_senders') && Senders.form.length > 0){
			Senders.init();
		}
	}
}