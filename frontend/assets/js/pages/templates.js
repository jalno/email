var templates = function(){
	var form = $('.create_form');
	var runSelect2 = function(){
		$('select', form).attr('dir','rtl').select2({
			language:'fa',
			minimumResultsForSearch: 'Infinity'
		});
		$('select[name=name]', form).attr('dir','rtl').select2({
			tags: true,
			multiple: false,
			language:'fa'
		});
		$('select[name=name]', form).change(setVariables);
	}
	var setVariables = function(){
		var val = $('select[name=name]', form).val();
		var $option = $('select[name=name] option:selected', form);
		var variables = $option.data('variables');
		if(!variables){
			variables = [];
		}
		var $html = '';
		for(var i=0;i!=variables.length;i++){
			$html += '<tr>';
				$html += '<td>['+variables[i].key+']</td>';
				$html += '<td>'+variables[i].description+'</td>';
			$html += '</tr>';
		}
		$('.table-variables tbody').html($html);
	}
	return {
		init: function() {
			runSelect2();
			setVariables();
		}
	}
}();
$(function(){
	templates.init();
});
