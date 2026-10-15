<?php

class Cl_Controller extends CI_Controller {

    public function __construct() {
        parent::__construct();
        /*group by issue skip*/
        $this->db->query("SET sql_mode=(SELECT REPLACE(@@sql_mode, 'ONLY_FULL_GROUP_BY', ''));");

        
    }
}
