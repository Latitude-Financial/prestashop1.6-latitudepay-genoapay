<?php

class latitude_officialrefundModuleFrontController extends ModuleFrontController
{
    public function initContent() {
        $row = array();
        $json = '';
        parent::initContent();

        if (Tools::getValue('ajax')) {
            if (true) {
                $json = array(
                    'status' => 'success',
                    'message' => 'hahahaha'
                );
            } else {
                $json = array(
                    'status' => 'error',
                    'message' => $this->l('Error when getting product informations.')
                );
            }
        }
        header('Content-Type: application/json');
        echo Tools::jsonEncode($json);
    }
}