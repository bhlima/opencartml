<?php

/**
 * Controlle principal OpencartML 
 * Versão 1.0 
 * Situação: Production
 * Autor / Flavio Lima  bhlims2 gmail com
 */
require_once '../admin/controller/extension/module/opme.php';

class ControllerExtensionModuleOpencartml extends Controller {

    private $error = array();
    private $opme;
    public $siteId = 'MLB';
    public $redirectURI;
    public $secretkey;
    public $access_token;
    public $refresh_token;
    public $expires_in;
    public $user_id;
    public $scope;
    public $token_type;

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
                'module_opencartml_feedback_status' => $this->request->post['module_opencartml_feedback_status'],
                'module_opencartml_adtype' => $this->request->post['module_opencartml_adtype'],
            ]);


            $this->session->data['success'] = $this->language->get('text_success');
            $this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $user_token, true));
        }

        $par1 = $this->config->get('module_opencartml_client_id');
        $par2 = $this->config->get('module_opencartml_client_secret');
        $redirectURI = 'https://nicbit.com.br/index.php?route=extension/module/opencartml/opcall';
        $ml_code = $this->config->get('module_opencartml_auth');
        $opme = new Opme($par1, $par2, $redirectURI);

        //Link para autorização
        $data['auth_link'] = $opme->getAuthUrl($redirectURI);
        
        //Link para configurar o aplicativo no mercadolivre
        $data['uri_retorno'] = HTTPS_SERVER . "index.php?route=extension/module/opencartml/opcall";
        
            
        //Categorias principais
        $categories = $opme->get('sites/MLB/categories');
        $data['categories'] = $categories['body'];

        //Moedas disponíveis
        $currencies = $opme->get('currencies');
        $data['currencies'] = $currencies['body'];

        //Autorização para dados da conta (privados)
        // $retorno = $opme->authorize($ml_code, $redirectURI);

        $this->load->model('localisation/order_status');
        $this->load->model('customer/custom_field');
        $this->load->model('localisation/order_status');
        $this->load->model('localisation/currency');
        $this->load->model('extension/module/opencartml');

        /**
         * Recupera dados do token armazenados em bd, pega o mais recente
         */
        $retorno = $this->model_extension_module_opencartml->GetToken();

        if ($retorno) {
            $access_token = $retorno['access_token'];
            $refresh_token = $retorno['refresh_token'];
            $expires_in = $retorno['expires_in'];
            $user_id = $retorno['user_id'];

            $opme->PutToken($access_token);
            $opme->PutRefresh($refresh_token);
        } else {
            $access_token = '';
            $refresh_token = '';
            $expires_in = '';
            $user_id = '';
        }
        
        
        if (isset($ml_code) || isset($access_token)) {
            if (isset($ml_code) && !isset($access_token)) {
                try {

                    $user = $opme->authorize($ml_code, $redirectURI);

                    // Atualiza as variaveis de ambiente
                    $access_token = $user['body']->access_token;
                    $refresh_token = time() + $user['body']->expires_in;
                    $expires_in = $user['body']->refresh_token;
                    $user_id = $user['body']->user_id;
                    $scope = $user['body']->scope;
                    $token_type = $user['body']->token_type;

                    //Insere os dados no banco de dados
                    $this->model_extension_module_opencartml->PutToken($access_token, $refresh_token, $expires_in, $user_id, $scope, $token_type);
                } catch (Exception $e) {
                    echo "Exception: ", $e->getMessage(), "\n";
                }
            } else {
                //Verifica se a sessão esta no tempo ou já expirou                
                if ($expires_in < time()) {
                    try {

                        $refresh = $opme->refreshAccessToken();

                        // Atualiza as variaveis de ambiente
                        $access_token = $refresh['body']->access_token;
                        $refresh_token = time() + $refresh['body']->expires_in;
                        $expires_in = $refresh['body']->refresh_token;
                        $user_id = $refresh['body']->user_id;
                        $scope = $refresh['body']->scope;
                        $token_type = $refresh['body']->token_type;

                        $this->model_extension_module_opencartml->PutToken($access_token, $refresh_token, $expires_in, $user_id, $scope, $token_type);

                        $opme->PutToken($access_token);
                        $opme->PutRefresh($refresh_token);
                        
                    } catch (Exception $e) {
                        echo "Exception: ", $e->getMessage(), "\n";
                    }
                }
            }
        }


        //Moedas disponíveis
        $listing_types = $opme->get('sites/MLB/listing_types');
        $data['listing_types'] = $listing_types['body'];

        //Dados da conta

        $account = $opme->get('users/me?access_token=' . $access_token);
        $data['account'] = $account['body'];

        
        
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



        /* FeedBack Status */
        if (isset($this->request->post['module_opencartml_feedback_status'])) {
            $data['module_opencartml_feedback_status'] = $this->request->post['module_opencartml_feedback_status'];
        } else {
            $data['module_opencartml_feedback_status'] = $this->config->get('module_opencartml_debug');
        }

        /* Ad Type */
        if (isset($this->request->post['module_opencartml_adtype'])) {
            $data['module_opencartml_adtype'] = $this->request->post['module_opencartml_adtype'];
        } else {
            $data['module_opencartml_adtype'] = $this->config->get('module_opencartml_adtype');
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
        $data['statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $data['add'] = $this->url->link('module/extension/opencartml/add', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['delete'] = $this->url->link('module/extension/opencartml/del', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['redirect_url'] = $this->url->link('module/extension/opencartml', 'user_token=' . $this->session->data['user_token'] . $url, true);
        $data['auth_code'] = $this->config->get('module_opencartml_auth');

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
