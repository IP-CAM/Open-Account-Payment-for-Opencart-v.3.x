<?php
class ControllerEventOpen extends Controller {
	
	public function index(&$view, &$data, &$output) {// triggered after view customer form
		// build insert html
		//$this->log->write(print_r($data,true));
		// check current settings
		$this->load->model('customer/customer');
		$info = $this->model_customer_customer->getCustomer($data['customer_id']);
		// override with post values
		if($this->request->server['REQUEST_METHOD'] == 'POST') {
			$info['open_account'] = isset($this->request->post['open_account'])?$this->request->post['open_account']:0;
			$terms = array(
					$this->request->post['account_terms_early'],
					$this->request->post['account_terms_net'],
					$this->request->post['account_terms_limit'],
				);
		} else {
			if(isset($info['account_terms'])) $terms = explode(':', $info['account_terms']);
			if(!isset($terms)) $terms = array('',10,1000);
		}
		$insert = '<script type="text/javascript">//<!--'."\n";
		$insert .= '$(document).ready(function () {'."\n";
		$insert .= '	text_insert = '."'";
		$insert .= '<div class="form-group"><div class="col-sm-2"><label class="radio">';
		$this->language->load('extension/payment/open');
		$insert .= $this->language->get('text_open_account');
		$insert .= '</label></div><div class="col-sm-10">';
		$insert .= '<label class="radio"><input type="checkbox" name="open_account" value="1"';
		$insert .= (isset($info['open_account']) && $info['open_account'] == '1')?'checked="checked">':'>';
		$insert .= $data['text_yes'].'</label>';
		$insert .= '<label class="radio">';
		$insert .= $this->language->get('text_days').'</label>';
		$insert .= '<input type="text" name="account_terms_net" value="'.$terms[1].'">';
		$insert .= '<label class="radio">'.$this->language->get('text_limit').'</label>';
		$insert .= '<input type="text" name="account_terms_limit" value="'.$terms[2].'">';
		$insert .= '<label class="radio">'.$this->language->get('text_early').'</label>';
		$insert .= '<input type="text" name="account_terms_early" value="'.$terms[0].'">';
		$insert .= '</div></div>'."';\n";
		$insert .= '	$(\'#tab-customer\').children(\'fieldset\').children(\'legend\').first().after(text_insert);'."\n";
		$insert .= '});'."\n";
		$insert .= '//-->'."\n".'</script>'."\n";
		$split = strpos($output,'<label class="radio-inline">');// get insert point
			$output = str_replace('</body>',$insert.'</body>', $output);
		
	}
	
	public function save(&$route, &$data, &$output = null) {
		if((int)$output) {
			$id = $output;
			$temp = $data[0];
		} else {
			$temp = $data[1];
			$id = $data[0];
		}
		$sql = 'update '.DB_PREFIX.'customer set open_account = ';
		if(isset($temp['open_account']) && $temp['open_account']) {
			$sql .= ' 1';
		} else {
			$sql .= ' 0';
		}
		$terms = $this->db->escape($temp['account_terms_early'].':'.$temp['account_terms_net'].':'.$temp['account_terms_limit']);
		$sql .= ', account_terms = "'.$terms.'"';
		$sql .= ' where customer_id = '.(int)$id;
		$this->db->query($sql);
	}
}
