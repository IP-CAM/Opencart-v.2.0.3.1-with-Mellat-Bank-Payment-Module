<?php

/* Bank (Mellat) WebService Pro v1.0 */

require_once(DIR_SYSTEM . 'library/nuSoap/nusoap.php');

class ControllerPaymentBankMellat extends Controller {
	private $WebService = array();
	private $errors = array();
	
	public function index() {
		/* Language */
		$this->language->load('payment/bank_mellat');
		$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		
		/* Template */
		$Template = $this->config->get('config_template') . '/template/payment/bank_mellat_checkout.tpl';
		$this->template = file_exists(DIR_TEMPLATE . $Template) ? $this->config->get('config_template') . '/template/payment/bank_mellat_checkout.tpl' : 'default/template/payment/bank_mellat_checkout.tpl';
		$this->render();
	}
	
	public function action() {
		/* Model */
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$order_info['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);
		$order_info['amount'] = $this->currency->convert($order_info['amount'], $order_info['currency_code'], "RLS");
		
		$json = array();
		$data = array(
			'orderId'     => $this->session->data['order_id'],
			'orderTotal' => $order_info['amount'],
			
			'address_confirm' => $this->url->link('payment/bank_mellat/callback', '', 'SSL'),
			'address_cancel'  => $this->url->link('checkout/checkout', '', 'SSL'),
			'address_success' => $this->url->link('checkout/success', '', 'SSL')
		);
		
		/* Connect */
		$this->load->library('Ws-Banks/bank_mellat');
		$this->WebService = new BankMellat($this->config->get('bank_mellat_terminal_id'), $this->config->get('bank_mellat_username'), $this->config->get('bank_mellat_password'));
		
		$this->data['refId'] = $this->WebService->PayAction($data['orderTotal'], $data['address_confirm'], "Order No ::" . $data['orderId']);
		$this->data['action'] = $this->WebService->data['Config']['Action'];
		
		if ($this->WebService->errors) {
			$json['error'] = implode(', ', $this->WebService->errors);
		} else {
			$json['action'] = $this->data['action'];
			$json['refId'] = $this->data['refId'];
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	public function callback() {
		/* Language */
		$this->language->load('payment/bank_mellat');
		$this->document->setTitle($this->language->get('text_heading'));
		
		$this->data['text_wait'] = $this->language->get('text_wait');
		$this->data['text_settle_yes'] = $this->language->get('text_settle_yes');
		$this->data['text_settle_no'] = $this->language->get('text_settle_no');
		$this->data['text_heading'] = $this->language->get('text_heading');
	
		$this->data['text_settled'] = $this->language->get('text_settled');
		$this->data['text_orderId'] = $this->language->get('text_orderId');
		$this->data['text_saleOrderId'] = $this->language->get('text_saleOrderId');
		$this->data['text_saleReferenceId'] = $this->language->get('text_saleReferenceId');
		
		$this->data['button_confirm'] = $this->language->get('button_confirm');
		$this->data['button_continue'] = $this->language->get('button_continue');
		
		$this->data['breadcrumbs'] = array();
      	$this->data['breadcrumbs'][] = array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home', '', 'SSL'), 'separator' => false);
      	$this->data['breadcrumbs'][] = array('text' => $this->language->get('text_heading'), 'href' => $this->url->link('payment/bank_mellat/callback', '', 'SSL'), 'separator' => $this->language->get('text_separator'));
		
		/* Model */
		$this->load->model('checkout/order');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		
		$order_info['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], false, false);
		$order_info['amount'] = $this->currency->convert($order_info['amount'], $order_info['currency_code'], "RLS");
		
		$json = array();
		$data = array(
			'orderId'     => $this->session->data['order_id'],
			'orderTotal' => $order_info['amount'],
			
			'address_confirm' => $this->url->link('payment/bank_mellat/callback', '', 'SSL'),
			'address_cancel'  => $this->url->link('checkout/checkout', '', 'SSL'),
			'address_success' => $this->url->link('checkout/success', '', 'SSL'),
		);
		
		/* Connect */
		$this->load->library('Ws-Banks/bank_mellat');
		$this->WebService = new BankMellat($this->config->get('bank_mellat_terminal_id'), $this->config->get('bank_mellat_username'), $this->config->get('bank_mellat_password'));

		$this->data['error_warning'] = "";
		$this->data['confirm'] = $this->WebService->PayConfirm($data['address_cancel']);
		
		if ($this->WebService->errors) {
			$this->data['error_warning'] = implode(', ', $this->WebService->errors);
			$this->data['continue'] = $data['address_cancel'];
		} else {
			$this->data['settled'] = $this->data['confirm']['settled'] ? $this->data['text_settle_yes'] : $this->data['text_settle_no'];
			$this->data['orderId'] = $this->data['confirm']['orderId'];
			$this->data['saleOrderId'] = $this->data['confirm']['saleOrderId'];
			$this->data['saleReferenceId'] = $this->data['confirm']['saleReferenceId'];
			
			$comment = $this->data['text_settled'] . $this->data['settled'] . "\n";
			$comment .= $this->data['text_orderId'] . $this->data['orderId'] . "\n";
			$comment .= $this->data['text_saleOrderId'] . $this->data['saleOrderId'] . "\n";
			$comment .= $this->data['text_saleReferenceId'] . $this->data['saleReferenceId'] . "\n";
			
			$this->model_checkout_order->confirm($order_info['order_id'], $this->config->get('bank_mellat_order_status_id'), $comment, true);
			$this->data['continue'] = $data['address_success'];
		}
	
		/* Template */
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);
		
		$Template = $this->config->get('config_template') . '/template/payment/bank_mellat_confirm.tpl';
		$this->template = file_exists(DIR_TEMPLATE . $Template) ? $this->config->get('config_template') . '/template/payment/bank_mellat_confirm.tpl' : 'default/template/payment/bank_mellat_confirm.tpl';
		$this->response->setOutput($this->render());
	}
}
?>