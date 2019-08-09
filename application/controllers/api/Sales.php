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
class Sales extends REST_Controller {
	
	public function __construct() {
		parent::__construct ();
		
		$this->load->model ( 'Sales_model' );
	}
	
	
	public function sales_get() {
		
		$date_ini = $this->get('date_ini');
		$date_end = $this->get('date_end');
		$code = $this->get('code');
		$date_ini = strtotime($date_ini);	
		$date_ini = date('Y-m-d',$date_ini);
		$date_end = strtotime($date_end);	
		$date_end = date('Y-m-d',$date_end);
		//print_r($data); exit;					
		$this->response( $this->Sales_model->get_sales ($date_ini, $date_end, $code), REST_Controller::HTTP_OK);
		
		
	}
	
	public function sale_detail_get() {
	
		
		$id_venta = $this->get('id_venta');
	
		//print_r($data); exit;
		$this->response( $this->Sales_model->getSaleDetail ($id_venta), REST_Controller::HTTP_OK);
	
	
	}
	
	public function devol_detail_get() {
	
	
		$id_devolucion = $this->get('id_devolucion');
	
		//print_r($data); exit;
		$this->response( $this->Sales_model->getDevolDetail ($id_devolucion), REST_Controller::HTTP_OK);
	
	
	}
	
	public function devol_detail_by_id_venta_get() {
	
	
		$id_venta = $this->get('id_venta');
	
		//print_r($data); exit;
		$this->response( $this->Sales_model->getDevolDetailIdVenta ($id_venta), REST_Controller::HTTP_OK);
	
	
	}
	
	public function save_post() {
	
		$data = $this->post('data');
	
	
		$id = $this->Cart_model->save ( $data );
	
		//print_r($data); exit;
		if($id==''){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => FALSE,
					'message' => 'Guardado en espera...'
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else
			$this->set_response(array(
					'status' => FALSE,
					'message' => json_encode($id)
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	
	}
	
	
	public function exist_post() {
	
		//$data = $this->post('data');
	
		$data = $this->post('data');
	
	
		$exist = $this->Cart_model->exist ( $data );
	
		//print_r($data); exit;
		if($exist){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => FALSE,
					'message' => 'Existencia ajustada...'
					//'price' => ($data)
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else
			$this->set_response(array(
					'status' => FALSE,
					'message' => 'Error'//json_encode($exist)
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	
	}
	
	public function price_post() {
	
		//$data = $this->post('data');	
		
		$item = $this->post('item');
		
		
		$precio1 = $this->Cart_model->price ( $item );
	
		//print_r($data); exit;
		if($precio1!=''){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => FALSE,
					'message' => 'Precio ajustado...'
					//'price' => ($data)
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else
			$this->set_response(array(
					'status' => FALSE,
					'message' => 'Error.'
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	
	}
	
	public function price_get() {
	
		$data = $this->get('codigo');
		$precio1 = $this->Cart_model->getPrecioArticulo( $codigo );
	
		//print_r($data); exit;
		if($precio1!=''){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => FALSE,
					'message' => 'Actualizado',
					'price' => json_encode($precio1)
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else
			$this->response(array(
					'status' => FALSE,
					'message' => 'Error.'
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	
	}
	
}

/* End of file Cart.php */
/* Location: ./application/controllers/api/Cart.php */