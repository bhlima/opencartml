<?php


require_once '../admin/controller/extension/module/meli.php';

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
            $this->load->library('opencartml');                      
            $this->model_setting_setting->editSetting('module_opencartml', $this->request->post);
            $this->model_setting_setting->editSetting('module_opencartml', [
                'module_opencartml_status' => $this->request->post['module_opencartml_status'],
                'module_opencartml_client_id' => $this->request->post['module_opencartml_client_id'],
                'module_opencartml_client_secret' => $this->request->post['module_opencartml_client_secret'],
                'module_opencartml_debug' => $this->request->post['module_opencartml_debug'],
            ]);
            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $user_token, true));
            
       }
     
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

        /* Links */
        $data['action'] = $this->url->link('extension/module/opencartml', 'user_token=' . $user_token, true);
        $data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $user_token, true);
        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');
        $this->response->setOutput($this->load->view('extension/module/opencartml', $data));
    }

    public function validate() {

        /* Error Permission */
        if (!$this->user->hasPermission('modify', 'extension/module/opencartml')) {
            $this->error['warning'] = $this->language->get('warning');
        }

        /* Client_id */
        if (strlen($this->request->post['module_opencartml_client_id']) < 16) {
            $this->error['client_id'] = $this->language->get('error_client_id');
        } 
        

		

        /* Client_id */
        if (strlen($this->request->post['module_opencartml_client_secret']) < 64) {
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
