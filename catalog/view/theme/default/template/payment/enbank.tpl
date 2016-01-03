<h2><?php echo $text_instruction; ?></h2>
<div class="well well-sm">
  <p><b><?php echo $text_payable; ?></b></p>
  <p><?php echo $payable; ?></p>
  
  
  <form action="<?php echo $action; ?>" method="post" id="payment">
  <input type="hidden" name="Amount" value="<?php echo $Amount; ?>" />
  <input type="hidden" name="MID" value="<?php echo $MID; ?>" />
  <input type="hidden" name="RedirectURL" value="<?php echo $RedirectURL; ?>" />
  <input type="hidden" name="ResNum" value="<?php echo $ResNum; ?>" />
  <input type="hidden" name="cancel_return" value="<?php echo $cancel_return; ?>" />
  <input type="hidden" name="paymentaction" value="<?php echo $paymentaction; ?>" />

<div class="buttons">
    <div class="right"><a onclick="$('#payment').submit();" class="button"><span><?php echo $button_confirm; ?></span></a></div>
  </div>
</form>

  
</div>
<div class="buttons">
  <div class="pull-right">
    <input type="button" value="<?php echo $button_confirm; ?>" id="button-confirm" class="btn btn-primary" data-loading-text="<?php echo $text_loading; ?>" />
  </div>
</div>
<script type="text/javascript"><!--
$('#button-confirm').on('click', function() {
	$.ajax({
		type: 'get',
		url: 'index.php?route=payment/enbank/confirm',
		cache: false,
		beforeSend: function() {
			$('#button-confirm').button('loading');
		},
		complete: function() {
			$('#button-confirm').button('reset');
		},
		success: function() {
			location = '<?php echo $continue; ?>';
		}
	});
});
//--></script>
