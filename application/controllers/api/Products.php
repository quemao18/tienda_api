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
class Products extends REST_Controller {
	
	public function __construct() {
		parent::__construct ();
		
		$this->load->model ( 'Products_model' );
	}
	public function index_get() {
		
		$group = $this->get ('group');
		$topic = $this->get ('topic');
		//$this->response( $this->project_model->get_all ());
		//if(!empty($topic))
		$this->response( $this->Products_model->get_all ($group, $topic), REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
		//else
		//$this->response( $this->Products_model->get_groups (), REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
		
	}

	public function test_get(){
			$this->response(array(
					'status' => TRUE,
					'message' => 'ok'
			), REST_Controller::HTTP_OK); 
	}
	
	public function product_get() {
	
		$code = $this->get ('code');
		//$this->response( $this->project_model->get_all ());
		//if(!empty($topic))
		$this->response( $this->Products_model->get_product ($code), REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
		//else
		//$this->response( $this->Products_model->get_groups (), REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
	
	}
	
	
	public function update_product_get() {
	
		$product = $this->get('product');
		
		$code= $this->get('codigo');
		
		$data = [
				'referencia' => strtoupper($this->get('referencia')),
				'modelo' => strtoupper($this->get('medidas')),
				'nombre' => strtoupper($this->get('nombre')),
				'detalles' =>strtoupper($this->get('detalles')), 
				'marca' => strtoupper($this->get('marca'))				
				
		];
		
		//data2 es para la tabla existenc
		$data2 = [
				'ubicacion' => strtoupper($this->get('ubicacion'))
		];
		//$codigo = $this->get('codigo');
		//$referencia = $this->get('referencia');
		//$medidas = $this->get('medidas');
		//$detalles = $this->get('detalles');
		//$nombre = $this->get('nombre');
		
	
		$id = $this->Products_model->update ( $code, $data, $data2);
					
		//	$id=false;
		//print_r($data); exit;
		if($id){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => TRUE,
					'message' => 'Guardado...'
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else
			$this->response(array(
					'status' => FALSE,
					'message' => 'Error guardando.'
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	
	}
	
	public function groups_get() {
		//$this->response( $this->project_model->get_all ());
		//if(!empty($topic))
		$this->response( $this->Products_model->get_groups(), REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
		//else
		//$this->response( $this->Products_model->get_groups (), REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
	
	}
	
	public function subgroup_name_get() {
		//$group = $this->get ('group');
		$subgroup = $this->get ('subgroup');
		//$this->response( $this->project_model->get_all ());
		//if(!empty($topic))
		$this->response( $this->Products_model->get_subgroup_name($subgroup), REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
		//else
		//$this->response( $this->Products_model->get_groups (), REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
	
	}
	/*
	public function edit_get($id = NULL) {
		if (! $id) {
			 $this->response(array(
                    'status' => FALSE,
                    'message' => 'No users were found'
                ), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		}
		
		$this->response ( $this->project_model->get ( $id ) );
	}
	*/
	public function save_post($id = NULL) {
		if (! $id) {
			$new_id = $this->project_model->add ( $this->post () );
			$this->response ( array (
					'status' => true,
					'id' => $new_id,
					'message' => sprintf ( 'Project #%d has been created.', $new_id ) 
			), REST_Controller::HTTP_OK );
		} else {
			$this->project_model->update ( $id, $this->post () );
			$this->response ( array (
					'status' => true,
					'message' => sprintf ( 'Project #%d has been updated.', $id ) 
			), REST_Controller::HTTP_OK );
		}
	}
	public function remove_delete($id = NULL) {
		if ($this->project_model->delete ( $id )) {
			$this->response ( array (
					'status' => true,
					'message' => sprintf ( 'Project #%d has been deleted.', $id ) 
			), REST_Controller::HTTP_OK );
		} else {
			$this->response ( array (
					'status' => false,
					'error_message' => 'This project does not exist!' 
			),  REST_Controller::HTTP_NOT_FOUND );
		}
	}
}

/* End of file Products.php */
/* Location: ./application/controllers/api/Products.php */