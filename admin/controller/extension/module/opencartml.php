<?php


require_once '../admin/controller/extension/module/opme.php';

class ControllerExtensionModuleOpencartml extends Controller {

    
    private $error = array();
    private $opme;
    public $siteId = 'MLB';
    public $redirectURI ;
    public $secretkey ;
    

  
    
    public function index() {

        /* Carrega idioma */
        $data = $this->load->language('extension/module/opencartml');
        $this->document->setTitle($this->language->get('heading_title'));
        $user_token = $this->session->data['user_token'];
        

        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->load->model('setting/setting');
                      
            $this->model_setting_setting->editSetting('module_opencartml', $this->request->post);
            $this->model_setting_setting->editSetting('module_opencartml', [
                'module_opencartml_status' => $this->request->post['module_opencartml_status'],
                'module_opencartml_client_id' => $this->request->post['module_opencartml_client_id'],
                'module_opencartml_client_secret' => $this->request->post['module_opencartml_client_secret'],
                'module_opencartml_debug' => $this->request->post['module_opencartml_debug'],
                'module_opencartml_ml_number' => $this->request->post['module_opencartml_ml_number'],                
                'module_opencartml_ml_cpf' => $this->request->post['module_opencartml_ml_cpf'],                   
                'module_opencartml_ml_data_nascimento' => $this->request->post['module_opencartml_ml_data_nascimento'],   
                
                
                'module_opencartml_category' => $this->request->post['module_opencartml_category'],                  
                'module_opencartml_subcategory' => $this->request->post['module_opencartml_subcategory'],                   
                'module_opencartml_currency' => $this->request->post['module_opencartml_currency'],                   
         
      ]);        
                        
                        
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $user_token, true));            
                                         
            }
            



        $obj_teste = new Opme($this->config->get('module_opencartml_client_id'), $this->config->get('module_opencartml_client_secret'));
        //$result = $obj_teste->hello();
        $uls = $obj_teste->getAuthUrl($this->url->link('extension/module/opencartml', 'user_token=' . $user_token, true));
        $params = array();
        $urw = '/sites/MLB';
        $result = $obj_teste->get($urw, $params);
 
        
        //echo $uls;
        print_r($result);
        exit();




        /**
             * Load Library custom meli
             */
           // $this->load>library('Meli');         
            
            //$obj_meli->helloword();
            
            //$result = $obj_meli->helloword();           
            //echo $result;
            //exit();
            
            //* Load Models */          
            $this->load->model('localisation/order_status');
            $this->load->model('customer/custom_field');
            $this->load->model('localisation/order_status');           
            $this->load->model('localisation/currency');           
            
                      
            
        /* Warning */
        if (isset($this->error['warning'])) {
            $data['warning'] = $this->error['warning'];
        } else {
            $data['warning'] = false;
        }
       
        
        
        /* Error Token */
        if (isset($this->error['token'])) {
            $data['error_token'] = $this->error['token'];
        } else {
            $data['error_token'] = false;
        }

    
        
        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('common/home', 'user_token=' . $user_token, true),
            'name' => $this->language->get('text_home')
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $user_token, true),
            'name' => $this->language->get('text_module')
        );

        $data['breadcrumbs'][] = array(
            'href' => $this->url->link('extension/module/opencartml', 'user_token=' . $user_token, true),
            'name' => $this->language->get('heading_title')
        );
        
        
        

        /* Status */
        if (isset($this->request->post['module_opencartml_status'])) {
            $data['module_opencartml_status'] = $this->request->post['module_opencartml_status'];
        } else {
            $data['module_opencartml_status'] = $this->config->get('module_opencartml_status');
        }

        /* Client_id */
        if (isset($this->request->post['module_opencartml_client_id'])) {
            $data['module_opencartml_client_id'] = $this->request->post['module_opencartml_client_id'];
        } else {
            $data['module_opencartml_client_id'] = $this->config->get('module_opencartml_client_id');
        }

        /* Client_secret */
        if (isset($this->request->post['module_opencartml_client_secret'])) {
            $data['module_opencartml_client_secret'] = $this->request->post['module_opencartml_client_secret'];
        } else {
            $data['module_opencartml_client_secret'] = $this->config->get('module_opencartml_client_secret');
        }

         /* Client_custom field numero */
        if (isset($this->request->post['module_opencartml_ml_number'])) {
            $data['module_opencartml_ml_number'] = $this->request->post['module_opencartml_ml_number'];
        } else {
            $data['module_opencartml_ml_number'] = $this->config->get('module_opencartml_ml_number');
        }       
        
          /* Client_custom field numero */
        if (isset($this->request->post['module_opencartml_ml_data_nascimento'])) {
            $data['module_opencartml_ml_data_nascimento'] = $this->request->post['module_opencartml_ml_data_nascimento'];
        } else {
            $data['module_opencartml_ml_data_nascimento'] = $this->config->get('module_opencartml_ml_data_nascimento');
        }  
        
           /* Client_custom field numero */
        if (isset($this->request->post['module_opencartml_ml_cpf'])) {
            $data['module_opencartml_ml_cpf'] = $this->request->post['module_opencartml_ml_cpf'];
        } else {
            $data['module_opencartml_ml_cpf'] = $this->config->get('module_opencartml_ml_cpf');
        }         
      
        /* Debug */
        if (isset($this->request->post['module_opencartml_debug'])) {
            $data['module_opencartml_debug'] = $this->request->post['module_opencartml_debug'];
        } else {
            $data['module_opencartml_debug'] = $this->config->get('module_opencartml_debug');
        }

        /* Debug */
        if (file_exists(DIR_LOGS . 'opencartml.log')) {
            if ((isset($this->request->post['module_opencartml_debug']) && $this->request->post['module_opencartml_debug'])) {
                $data['debug'] = file(DIR_LOGS . 'opencartml.log');
            } elseif ($this->config->get('module_opencartml_debug')) {
                $data['debug'] = file(DIR_LOGS . 'opencartml.log');
            } else {
                $data['debug'] = array();
            }
        } else {
            $data['debug'] = array();
        }
        
        
$url = '';
        /* Links */
        $data['action'] = $this->url->link('extension/module/opencartml', 'user_token=' . $user_token, true);
        $data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $user_token, true);
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $data['link_custom_field'] = $this->url->link('customer/custom_field', 'user_token=' . $user_token, true);
        $data['custom_fields'] = $this->model_customer_custom_field->getCustomFields();
        $data['currencies'] = $this->model_localisation_currency->getCurrencies();
        $data['statuses'] = $this->model_localisation_order_status->getOrderStatuses();
	$data['add'] = $this->url->link('module/extension/opencartml/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('module/extension/opencartml/del', 'user_token=' . $this->session->data['user_token'] . $url, true);       
        $data['redirect_url'] = $this->url->link('module/extension/opencartml', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $this->response->setOutput($this->load->view('extension/module/opencartml', $data));

    }

    public function validate() {

        /* Error Permission */
        if (!$this->user->hasPermission('modify', 'extension/module/opencartml')) {
            $this->error['warning'] = $this->language->get('warning');
        }

        /* Client_id */
        if (strlen($this->request->post['module_opencartml_client_id']) < 10) {
            $this->error['client_id'] = $this->language->get('error_client_id');
        } 
        
        /* Client_id */
        if (strlen($this->request->post['module_opencartml_client_secret']) < 10) {
            $this->error['client_secret'] = $this->language->get('error_client_secret');

        }


        return !$this->error;
    }
    
    
   
    

    public function install() {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` (`type`, `code`) VALUES ('module', 'opencartml') ");
    }

    public function uninstall() {
		$this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `code` = 'opencartml';");
    }

}
