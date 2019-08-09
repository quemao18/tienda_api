<?php

    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // Decide if the origin in $_SERVER['HTTP_ORIGIN'] is one
        // you want to allow, and if so:
        header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Max-Age: 86400');    // cache for 1 day
    }

    // Access-Control headers are received during OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
            // may also be using PUT, PATCH, HEAD etc
            header("Access-Control-Allow-Methods: GET, POST, OPTIONS");         

        if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
            header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

        exit(0);
    }

    //echo "You have CORS!";

defined ( 'BASEPATH' ) or exit ( 'No direct script access allowed' );

//require_once ('./application/libraries/REST_Controller.php');
require APPPATH . '/libraries/REST_Controller.php';


/**
 * Projects API controller
 *
 * Validation is missign
 */
class Inquire extends REST_Controller {
	
	public function __construct() {
		parent::__construct ();
		
		//$this->load->model ( 'Cart_model' );
	}
	
	
	public function index_get() {
		
		$data = $this->get('data');
		//print_r($data); exit;					
		if(true){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => TRUE,
					'message' => $data
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else		
		$this->response(array(
				'status' => FALSE,
				'message' => 'Error procesando venta.'				
		), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		
		
	}
		
	
}

/* End of file Cart.php */
/* Location: ./application/controllers/api/Cart.php */