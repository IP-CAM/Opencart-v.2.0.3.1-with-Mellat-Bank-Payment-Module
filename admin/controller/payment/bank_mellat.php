<?php
require_once(DIR_SYSTEM . 'library/nuSoap/nusoap.php');

class ControllerPaymentBankMellat extends Controller {
	private $error = array();
	
	public function index() {
		
		/* Language */
		$this->load->language('payment/bank_mellat');
		$this->document->setTitle($this->language->get('heading_title'));
		
		/* Model */
		$this->load->model('setting/setting');
		$this->load->model('localisation/order_status');
		
		/* (Update) */
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('bank_mellat', $this->request->post);
			
			$this->session->data['success'] = $this->language->get('text_success');			
			$this->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}
		
		$this->data['heading_title'] = $this->language->get('heading_title');
		
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_none'] = $this->language->get('text_none');
		$this->data['text_wait'] = $this->language->get('text_wait');
		
		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');
		$this->data['button_settle'] = $this->language->get('button_settle');
		$this->data['button_refund'] = $this->language->get('button_refund');
		
		$this->data['tab_general'] = $this->language->get('tab_general');
		$this->data['tab_settle'] = $this->language->get('tab_settle');
		$this->data['tab_refund'] = $this->language->get('tab_refund');
		
		$this->data['fields_configuration_1'] = $this->language->get('fields_configuration_1');
		$this->data['fields_configuration_2'] = $this->language->get('fields_configuration_2');
		$this->data['fields_configuration_3'] = $this->language->get('fields_configuration_3');
		$this->data['fields_configuration_4'] = $this->language->get('fields_configuration_4');
		
		$this->data['entry_provider'] = $this->language->get('entry_provider');
		$this->data['entry_terminal_id'] = $this->language->get('entry_terminal_id');
		$this->data['entry_username'] = $this->language->get('entry_username');
		$this->data['entry_password'] = $this->language->get('entry_password');
		$this->data['entry_order_status'] = $this->language->get('entry_order_status');		
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['entry_sale_amount'] = $this->language->get('entry_sale_amount');
		$this->data['entry_sale_order_id'] = $this->language->get('entry_sale_order_id');
		$this->data['entry_sale_reference_id'] = $this->language->get('entry_sale_reference_id');
		
		/* Warnings */
		$this->data['error_warning'] = isset($this->error['warning']) ? $this->error['warning'] : '';

		/* Breadcrumbs */
		$this->data['breadcrumbs'] = array();
   		$this->data['breadcrumbs'][] = array('text' => $this->language->get('text_home'), 'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'), 'separator' => false);
   		$this->data['breadcrumbs'][] = array('text' => $this->language->get('text_payment'), 'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'), 'separator' => ' :: ');
   		$this->data['breadcrumbs'][] = array('text' => $this->language->get('heading_title'), 'href' => $this->url->link('payment/bank_mellat', 'token=' . $this->session->data['token'], 'SSL'), 'separator' => ' :: ');
		
		/* Actions */
		$this->data['action'] = $this->url->link('payment/bank_mellat', 'token=' . $this->session->data['token'], 'SSL');
		$this->data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');	
		
		/* (Fields)->Payment */
		$this->data['bank_mellat_terminal_id'] = isset($this->request->post['bank_mellat_terminal_id']) ? $this->request->post['bank_mellat_terminal_id'] : $this->config->get('bank_mellat_terminal_id');
		$this->data['bank_mellat_username'] = isset($this->request->post['bank_mellat_username']) ? $this->request->post['bank_mellat_username'] : $this->config->get('bank_mellat_username');
		$this->data['bank_mellat_password'] = isset($this->request->post['bank_mellat_password']) ? $this->request->post['bank_mellat_password'] : $this->config->get('bank_mellat_password');
		
		/* (Fields)->Application */
		$this->data['bank_mellat_order_status_id'] = isset($this->request->post['bank_mellat_order_status_id']) ? $this->request->post['bank_mellat_order_status_id'] : $this->config->get('bank_mellat_order_status_id');
		$this->data['bank_mellat_sort_order'] = isset($this->request->post['bank_mellat_sort_order']) ? $this->request->post['bank_mellat_sort_order'] : $this->config->get('bank_mellat_sort_order');
		$this->data['bank_mellat_status'] = isset($this->request->post['bank_mellat_status']) ? $this->request->post['bank_mellat_status'] : $this->config->get('bank_mellat_status');
		
		/* DataRows */
		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		$this->data['token'] = $this->session->data['token'];
		
		/* Template */
		$this->template = 'payment/bank_mellat.tpl';
		$this->children = array('common/header', 'common/footer');
		
		$this->response->setOutput($this->render());
	}
	
	public function settle() {
		
		$json = array();
		if (!isset($this->request->get['sale_order_id'])) { $json['error'] = $this->language->get('error_sale_order_id'); }
		if (!isset($this->request->get['sale_reference_id'])) { $json['error'] = $this->language->get('error_sale_reference_id'); }
		
		if (!$json) {
			
			/* Connect */
			$this->load->library('Ws-Banks/bank_mellat');
			$this->WebService = new BankMellat($this->config->get('bank_mellat_terminal_id'), $this->config->get('bank_mellat_username'), $this->config->get('bank_mellat_password'));
			
			$this->data['settle'] = $this->WebService->Settle($this->request->get['sale_order_id'], $this->request->get['sale_reference_id']);
			//$this->data['action'] = $this->WebService->data['Config']['Action'];
			
			if ($this->WebService->errors) {
				$json['error'] = implode(', ', $this->WebService->errors);
			} else {
				if ($this->data['settle']) { 
					$json['success'] = $this->language->get('text_settle_success'); 
				} else {
					$json['error'] = $this->language->get('text_settle_error');
				}
			}
		}
		
		$this->response->setOutput(json_encode($json));
	}
	
	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/bank_mellat')) { $this->error['warning'] = $this->language->get('error_permission'); }
		return !$this->error ? true : false;
	}
}
?>