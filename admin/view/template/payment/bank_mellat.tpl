<?php echo $header; ?>
<div id="content">
	<div class="breadcrumb">
		<?php foreach ($breadcrumbs as $breadcrumb) { ?>
		<?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
		<?php } ?>
	</div>
	<?php if ($error_warning) { ?><div class="warning"><?php echo $error_warning; ?></div><?php } ?>
	<div class="box">
		<div class="heading">
			<h1><img src="view/image/shipping.png" alt="" /> <?php echo $heading_title; ?></h1>
			<div class="buttons"><a onclick="$('#form').submit();" class="button"><span><?php echo $button_save; ?></span></a><a onclick="location='<?php echo $cancel; ?>';" class="button"><span><?php echo $button_cancel; ?></span></a></div>
		</div>
		<div class="content">
			<div id="tabs" class="htabs">
				<a href="#tab-general"><?php echo $tab_general; ?></a>
				<a href="#tab-settle"><?php echo $tab_settle; ?></a>
				<!--<a href="#tab-refund"><?php //echo $tab_refund; ?></a>-->
			</div>
			<form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form">     
				
				<div id="tab-general" class="page">
					<table class="form">
						<tr>
							<td colspan="2">
								<br /><br />
								<font size="3" color="orange"><b><?php echo $fields_configuration_1; ?></b></font>
								<br /><br />
							</td>
						</tr>
						<tr>
							<td><?php echo $entry_terminal_id; ?></td>
							<td><input type="text" name="bank_mellat_terminal_id" value="<?php echo $bank_mellat_terminal_id; ?>" size="25" /></td>
						</tr>
						<tr>
							<td><?php echo $entry_username; ?></td>
							<td><input type="text" name="bank_mellat_username" value="<?php echo $bank_mellat_username; ?>" size="25" /></td>
						</tr>
						<tr>
							<td><?php echo $entry_password; ?></td>
							<td><input type="text" name="bank_mellat_password" value="<?php echo $bank_mellat_password; ?>" size="25" /></td>
						</tr>
						<tr>
							<td colspan="2">  
								<br /><br />
								<font size="3" color="orange"><b><?php echo $fields_configuration_2; ?></b></font>
								<br /><br />
							</td>
						</tr>
						<tr>
							<td><?php echo $entry_order_status; ?></td>
							<td>
								<select name="bank_mellat_order_status_id">
									<?php foreach ($order_statuses as $order_status) { ?>
										<option value="<?php echo $order_status['order_status_id']; ?>" <?php echo $order_status['order_status_id'] == $bank_mellat_order_status_id ? 'selected="selected"' : '' ?>><?php echo $order_status['name']; ?></option>
									<?php } ?>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo $entry_status; ?></td>
							<td>
								<select name="bank_mellat_status">
									<option value="1" <?php echo $bank_mellat_status ? 'selected="selected"' : ''; ?>><?php echo $text_enabled; ?></option>
									<option value="0" <?php echo !$bank_mellat_status  ? 'selected="selected"' : ''; ?>><?php echo $text_disabled; ?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td><?php echo $entry_sort_order; ?></td>
							<td><input type="text" name="bank_mellat_sort_order" value="<?php echo $bank_mellat_sort_order; ?>" size="1" /></td>
						</tr>
					</table>
				</div>
			</form>
			
			<div id="tab-settle" class="page">
				<table class="form">
					<tr>
						<td colspan="2">
							<br /><br />
							<font size="3" color="orange"><b><?php echo $fields_configuration_3; ?></b></font>
							<br /><br />
						</td>
					</tr>
					<tr>
						<td><?php echo $entry_sale_order_id; ?></td>
						<td><input type="text" name="bank_mellat_sale_order_id" size="25" /></td>
					</tr>
					<tr>
						<td><?php echo $entry_sale_reference_id; ?></td>
						<td><input type="text" name="bank_mellat_sale_reference_id" size="25" /></td>
					</tr>
				</table>
				<div class="buttons">
					<a id="button-settle" class="button"><span><?php echo $button_settle; ?></span></a>
				</div>
			</div>
			<!--<div id="tab-refund" class="page">
				<table class="form">
					<tr>
						<td colspan="2">
							<br /><br />
							<font size="3" color="orange"><b><?php //echo $fields_configuration_4; ?></b></font>
							<br /><br />
						</td>
					</tr>
					<tr>
						<td><?php //echo $entry_sale_amount; ?></td>
						<td><input type="text" name="bank_mellat_sale_amount" size="25" /></td>
					</tr>
					<tr>
						<td><?php //echo $entry_sale_order_id; ?></td>
						<td><input type="text" name="bank_mellat_sale_order_id" size="25" /></td>
					</tr>
					<tr>
						<td><?php //echo $entry_sale_reference_id; ?></td>
						<td><input type="text" name="bank_mellat_sale_reference_id" size="25" /></td>
					</tr>
				</table>
				<div class="buttons">
					<a onclick="$('#form').submit();" class="button"><span><?php //echo $button_refund; ?></span></a>
				</div>
			</div>-->
		</div>
	</div>
</div>
​
<script type="text/javascript">
<!--
	$('#tabs a').tabs(); 
	
	$('#button-settle').bind('click', function() {
		$.ajax({
			type: 'get',
			dataType: 'json',
			url: 'index.php?route=payment/bank_mellat/settle&token=<?php echo $token; ?>',
			data: 'sale_order_id=' + encodeURIComponent($('input[name=\'bank_mellat_sale_order_id\']').val()) + '&sale_reference_id=' + encodeURIComponent($('input[name=\'bank_mellat_sale_reference_id\']').val()),
			beforeSend: function() {
				$('#button-settle').attr('disabled', true);
				$('.box').before('<div class="attention"><?php echo $text_wait; ?></div>');
			},
					
			success: function(json) {
				if (json['error']) {
					$('.box').before('<div class="warning">' + json['error'] + '</div>');
					$('#button-settle').attr('disabled', false);
				}
				
				$('.attention').remove();
				if (json['success']) {
					$('.box').before('<div class="success">' + json['success'] + '</div>');
					$('#button-settle').attr('disabled', false);
				}
			}
		});
	});
//-->
</script>

<?php echo $footer; ?> 