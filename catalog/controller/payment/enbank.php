<?php
class ControllerPaymentEnbank extends Controller {
	public function index() {
		$this->load->language('payment/enbank');

		$data['action'] = 'https://pna.shaparak.ir/CardServices/controller';
		$this->load->model('checkout/order');
		
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		//$this->load->library('encryption');
		
		//$encryption = new Encryption($this->config->get('config_encryption'));
		
	     $data['Amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
		if($this->currency->getCode()=='TOM') {
			$data['Amount']=$data['Amount'] * 10;
		}
		$data['MID']=$this->config->get('merchantID');
		$data['RedirectURL'] = $this->url->link('payment/enbank/callback&order_id=' . $encryption->encrypt($this->session->data['order_id']));
		//$data['RedirectURL'] = HTTPS_SERVER . 'index.php?route=payment/sb24/callback&order_id=' . $encryption->encrypt($this->session->data['order_id']);
		$data['ResNum'] = $this->session->data['order_id'];
		
		$data['return'] = $this->url->link('checkout/success', '', 'SSL');
		//$data['return'] = HTTPS_SERVER . 'index.php?route=checkout/success';
		
		$data['cancel_return'] = $this->url->link('checkout/payment', '', 'SSL');
		//$data['cancel_return'] = HTTPS_SERVER . 'index.php?route=checkout/payment';

		$data['back'] = $this->url->link('checkout/payment', '', 'SSL');
		//$data['back'] = HTTPS_SERVER . 'index.php?route=checkout/payment';
		
		
		
		$data['text_instruction'] = $this->language->get('text_instruction');
		$data['text_payable'] = $this->language->get('text_payable');
		$data['text_address'] = $this->language->get('text_address');
		$data['text_payment'] = $this->language->get('text_payment');
		$data['text_loading'] = $this->language->get('text_loading');

		$data['button_confirm'] = $this->language->get('button_confirm');

		$data['payable'] = $this->config->get('enbank_payable');
		$data['address'] = nl2br($this->config->get('config_address'));

		$data['continue'] = $this->url->link('checkout/success');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/enbank.tpl')) {
			return $this->load->view($this->config->get('config_template') . '/template/payment/enbank.tpl', $data);
		} else {
			return $this->load->view('default/template/payment/enbank.tpl', $data);
		}
	}

		
	public function getState($State) {

		
	switch($State){ 
         
        case 'Canceled By User' : 
            return("تراکنش توسط خريدار کنسل شده است."); 
            break; 
        case 'Invalid Amount' : 
            return("مبلغ سند برگشتی، از مبلغ تراکنش اصلی بيشتر است."); 
            break; 
        case 'Invalid Transaction' : 
            return("درخواست برگشت يک تراکنش رسيده است، در حالی که تراکنش اصلی پيدا نمی شود."); 
            break; 
        case 'Invalid Card Number' : 
            return("شماره کارت اشتباه است.");
			break; 
        case 'No Such Issuer' : 
            return("چنين صادر کننده کارتی وجود ندارد."); 
            break; 
        case 'Expired Card Pick Up' : 
            return("از تاريخ انقضای کارت گذشته است و کارت ديگر معتبر نيست."); 
            break; 
        case 'Allowable PIN Tries Exceeded Pick Up' : 
            return("رمز کارت (PIN) 3 مرتبه اشتباه وارد شده است در نتيجه کارت غير فعال خواهد شد."); 
            break; 
        case 'Incorrect PIN' : 
            return("خريدار رمز کارت (PIN) را اشتباه وارد کرده است.");
			break; 
        case 'Exceeds Withdrawal Amount Limit' : 
            return("مبلغ بيش از سقف برداشت می باشد.");
			break; 
        case 'Transaction Cannot Be Completed' : 
            return("تراکنش Authorize شده است ( شماره PIN و PAN درست هستند) ولی امکان سند خوردن وجود ندارد.");
			break; 
        case 'Response Received Too Late' : 
            return("تراکنش در شبکه بانکی Timeout خورده است.");
			break; 
        case 'Suspected Fraud Pick Up' : 
            return("خريدار يا فيلد CVV2 و يا فيلد ExpDate را اشتباه زده است. ( يا اصلا وارد نکرده است)");
			break; 
        case 'No Sufficient Funds' : 
            return("موجودی به اندازی کافی در حساب وجود ندارد.");
			break; 
        case 'Issuer Down Slm' : 
            return("سيستم کارت بانک صادر کننده در وضعيت عملياتی نيست.");
			break; 
        case 'TME Error' : 
            return("خطا ايجاد شده قابل شناسايى نيست. لطفا با مديريت سايت تماس بگيريد");
			break; 
    	}  
		
		return("پرداخت صورت نگرفت");
	}

	
	public function callback() {
	
		$this->load->library('encryption');

	if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {

			$data['base'] = HTTP_SERVER;

		} else {

			$data['base'] = HTTPS_SERVER;

		}

		$encryption = new Encryption($this->config->get('config_encryption'));

		$State = $this->request->post['State'];

		$RefNum = $this->request->post['RefNum'];

		$ResNum = $this->request->post['ResNum'];
		$MerchantID=$this->config->get('enbank_MID');
			$debugmod=false;

		

		$this->load->model('checkout/order');

		

		if(($State=='OK') or ($debugmod==true)) {

		

		$order_info = $this->model_checkout_order->getOrder($ResNum);

		

		if ($order_info) { //verify here

		

		$Amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);

			if($this->currency->getCode()=='TOM') {

		$Amount=$Amount * 10;

	}

		$order_id=$order_info['order_id'];

			

		if($debugmod==false) {
		$client = new nusoap_client("http://213.217.42.116/ref-payment/ws/ReferencePayment?WSDL", "wsdl" );
		
					$client = $client->getProxy( );
					if ( $errSp = $client->getError( ) )
					{
						$State=$errSp;
					}
			$result = $client->VerifyTransaction($RefNum, $MerchantID);

		} else { $result=$Amount; $RefNum='debug_test'; }

		

					if ( ($result > 0) and ($result==$Amount) ) {

					$this->model_checkout_order->confirm($order_id, $this->config->get('enbank_order_status_id'),'شماره رسيد ديجيتالي بانک اقتصاد نوین Refer Number: '.$RefNum,true);
						
						
								$this->response->setOutput('<html><head></head><body><table border="0" width="100%"><tr><td>&nbsp;</td><td style="border: 1px solid gray; font-family: tahoma; font-size: 14px; direction: rtl; text-align: right;">با تشکر پرداخت تکمیل شد.لطفا چند لحظه صبر کنید و یا  <a href="' . $this->url->link('checkout/success') . '"><b>اینجا کلیک نمایید</b></a></td><td>&nbsp;</td></tr><tr><td colspan="2"> شماره رسيد ديجيتالي بانک اقتصاد نوین Refer Number: '.$RefNum.'</td></tr></table></body></html>');
						
						
					} else {
						
        				$error = $this->getState($State).($debugmod==true? 'err1<br>state:'.$State.'<br>ref:'.$RefNum.'<br>res:'.$ResNum.'<br>mid:'.$MerchantID : '');
						$this->response->setOutput('<html><body><table border="0" width="100%"><tr><td>&nbsp;</td><td style="border: 1px solid gray; font-family: tahoma; font-size: 14px; direction: rtl; text-align: right;">'.$error.'<br /><br /><a href="' . $this->url->link('checkout/cart').  '"><b>بازگشت به فروشگاه</b></a></td><td>&nbsp;</td></tr></table></body></html>');
						
        			}
		
						
					}
		
		} else {
			
        				$error = $this->getState($State).($debugmod==true? 'err2<br>state:'.$State.'<br>ref:'.$RefNum.'<br>res:'.$ResNum.'<br>mid:'.$MerchantID : '');
						$this->response->setOutput('<html><body><table border="0" width="100%"><tr><td>&nbsp;</td><td style="border: 1px solid gray; font-family: tahoma; font-size: 14px; direction: rtl; text-align: right;">'.$error.'<br /><br /><a href="' . $this->url->link('checkout/cart').  '"><b>بازگشت به فروشگاه</b></a></td><td>&nbsp;</td></tr></table></body></html>');
						
		}
	}
	
	
	
	
	
	
	
	
	
	public function confirm() {
		if ($this->session->data['payment_method']['code'] == 'enbank') {
			$this->load->language('payment/enbank');

			$this->load->model('checkout/order');

			$comment  = $this->language->get('text_payable') . "\n";
			$comment .= $this->config->get('enbank_payable') . "\n\n";
			$comment .= $this->language->get('text_address') . "\n";
			$comment .= $this->config->get('config_address') . "\n\n";
			$comment .= $this->language->get('text_payment') . "\n";

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('enbank_order_status_id'), $comment, true);
		}
	}
}