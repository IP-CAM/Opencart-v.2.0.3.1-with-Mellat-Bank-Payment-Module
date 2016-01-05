<div id="payment"></div>
<div class="buttons">
	<div class="right"><input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="button" /></div>
</div>

<script type="text/javascript"><!--
$('#button-confirm').bind('click', function() {
	$.ajax({
		type: 'post',
		dataType: 'json',
		url: 'index.php?route=payment/bank_mellat/action',
		data: $('#payment :input'),
		beforeSend: function() {
			$('#button-confirm').attr('disabled', true);
			$('#payment').before('<div class="attention"><img src="catalog/view/theme/default/image/loading.gif" alt="" />&nbsp;&nbsp;<?php echo $text_wait; ?></div>');
		},
				
		success: function(json) {
			if (json['error']) {
				$('#payment').before('<div class="warning">' + json['error'] + '</div>');
				$('#button-confirm').attr('disabled', false);
			}
			
			$('.attention').remove();
			if (json['action'] && json['refId']) {
					
				var data_action = json['action'] ? json['action'] : "";
				var data_refId = json['refId'] ? json['refId'] : "";
				
				var form = document.createElement("form");
				form.setAttribute("method", "POST");
				form.setAttribute("action", data_action);
				form.setAttribute("target", "_self");
				var hiddenField = document.createElement("input");              
				hiddenField.setAttribute("name", "RefId");
				hiddenField.setAttribute("value", data_refId);
				form.appendChild(hiddenField);

				document.body.appendChild(form);
				form.submit();
				document.body.removeChild(form);
			}
		}
	});
});
//--></script>