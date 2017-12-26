<?php

class ControllerExtensionModuleOpencartml extends Controller {

    public function opcall() {


        if ($this->request->get['code']) {
            $this->log->write($this->request->get['code']);
            $test = $this->request->get['code'];

            echo '<h3>Código de autorização :: ' . $test;
            echo '<br>Autorização concedida, este codigo vai ser rmazenado em suas configurções' . '</h3>';
            $this->editacodigo('auth', ['module_opencartml_auth' => $test]);

            echo '<br><h2>Autorização gravada no banco de dados</h2> faça login novamente no administrador e termine de configurar o OpencartML' . '</h3>';
        } else {
            return;
        }
    }

    private function editacodigo($code, $data, $store_id = 0) {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "setting` WHERE store_id = '" . (int) $store_id . "' AND `key` = 'module_opencartml_auth'");

        foreach ($data as $key => $value) {
            
            //echo $value;
            //echo $this->db->escape($value);
            //echo $this->db->escape(json_encode($value, true));
            //exit;
            
            if (substr($key, 0, strlen($code)) == $code) {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int) $store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $value . "'");
                } else {
                    $this->db->query("INSERT INTO " . DB_PREFIX . "setting SET store_id = '" . (int) $store_id . "', `code` = '" . $this->db->escape($code) . "', `key` = '" . $this->db->escape($key) . "', `value` = '" . $value . "'");
                }
            }
        }
    }


