<?php
/**
 * Created by PhpStorm.
 * User: bizca
 * Date: 8/7/2021
 * Time: 4:38 PM
 */

class SessionManager
{
    public function document_elements_type(){
        return $_SESSION['document_elements_type'] ?? "financial";
    }
}