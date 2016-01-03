<?php
class ControllerPaymentEnbank extends Controller {
	public function index() {
		$this->load->language('payment/enbank');

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