<?php
/**
 * Modelo de dados do acesso
 */
class ModelExtensionModuleOpencartml extends Model {

    public $result;

    /**
     * GetToken
     * @return type array() = Ultimo registro do banco de dados
     */
    public function GetToken() {
        
       //$lastid = $this->db->getLastId('oc_ml_token');
        $result = $this->db->query("SELECT MAX(ID) FROM " . DB_PREFIX ."ml_token");
        $lastid = $result->row;
        $id_query  = $lastid['MAX(ID)'];
        $sql = "SELECT * FROM `" . DB_PREFIX . "ml_token` WHERE id = '" . (int)$id_query . "'";     
	$query = $this->db->query($sql);
        return $query->row;
    }

    /**
     * PutToken
     * Grava o ultimo registro do token
     * @return type array()
     */
    public function PutToken($par1, $par2, $par3, $par4, $par5, $par6) {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "ml_token` (`access_token`, `refresh_token` , `expires_in` , `user_id`, `scope`, `token_type`) 
                VALUES ('" . $this->db->escape($par1) . "',"
                . "'" . $this->db->escape($par2) . "',"
                . "'" . $this->db->escape($par3) . "',"
                . "'" . $this->db->escape($par4) . "',"
                . "'" . $this->db->escape($par5) . "',"
                . "'" . $this->db->escape($par6) . "'");
        return;
    }

}
