<?php
class ModelExtensionPaymentOpen extends Model {
	public function getMethod($address, $total) {
		$this->load->language('extension/payment/open');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('payment_open_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if ($this->config->get('payment_open_total') > 0 && $this->config->get('payment_open_total') > $total) {
			$status = false;
		} elseif (!$this->config->get('payment_open_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}
		
		// next, check if enabled for this customer
		if(isset($this->session->data['customer_id'])) {
			$this->load->model('account/customer');
			$customer = $this->model_account_customer->getCustomer($this->session->data['customer_id']);
			if(!$customer['open_account']) $status = false;
		} else {// disabled for guest checkout
			$status = false;
		}
		$method_data = array();

		if ($status) {
			// parse terms text
			$terms = explode(':',$customer['account_terms']);
			$title = $this->language->get('text_title');
			$title .= ' ('.($terms[0]?($terms[0].' '):'').$this->language->get('text_net').' '.$terms[1];
			$title .= ', '.$this->currency->format($this->currency->convert($terms[2],$this->config->get('config_currency'),$this->session->data['currency']),$this->session->data['currency']);
			$title .= ' '.$this->language->get('text_limit').')';
			$method_data = array(
				'code'       => 'open',
				'title'      => $title,
				'terms'      => '',
				'sort_order' => $this->config->get('payment_open_sort_order')
			);
		}

		return $method_data;
	}
}
