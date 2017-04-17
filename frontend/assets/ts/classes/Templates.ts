import * as $ from "jquery";
import "select2";
interface variable{
	key:string;
	description:string;
}
export class Templates{
	private static form = $('.tempates_form');
	private static runSelect2():void{
		$('select', Templates.form).attr('dir','rtl').select2({
			language:'fa',
			minimumResultsForSearch: Infinity
		});
		$('select[name=name]', Templates.form).select2({
			tags: true,
			multiple: false,
			language:'fa'
		});
		$('select[name=name]', Templates.form).change(Templates.setVariables);
	}
	private static setVariables():void{
		let val:string = $('select[name=name]', Templates.form).val();
		let $option = $('select[name=name] option:selected', Templates.form);
		let variables:variable[] = $option.data('variables');
		if(!variables){
			variables = [];
		}
		let $html = '';
		for(let i=0;i!=variables.length;i++){
			$html += `<tr><td>[${variables[i].key}]</td><td>${variables[i].description}</td></tr>`;
		}
		$('.table-variables tbody').html($html);
	}
	public static init() {
		Templates.runSelect2();
		Templates.setVariables();
	}
	public static initIfNeeded():void{
		if($('body').hasClass('email_templates') && Templates.form.length > 0){
			Templates.init();
		}
	}
}