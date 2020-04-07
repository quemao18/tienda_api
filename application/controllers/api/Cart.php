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
class Cart extends REST_Controller {
	
	public function __construct() {
		parent::__construct ();
		
		$this->load->model ( 'Cart_model' );
		$this->load->library('email');
                
	}
	
	
		public function sale2_post() {
		
		//$data = $this->post('data');
		$data = json_decode( $this->post('data') );	
		
		
		$id = $this->Cart_model->sale2 ( $data );
		
		//print_r($data); exit;					
		if($id){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => FALSE,
					'message' => 'Venta procesada'
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else		
		$this->response(array(
				'status' => FALSE,
				'message' => 'Error procesando venta.'				
		), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		
		
	}

	public function sale_post() {
		
		$data = $this->post('data');
		
		
		$id = $this->Cart_model->sale ( $data );
		
		//print_r($data); exit;					
		if($id){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => FALSE,
					'message' => 'Venta procesada'
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else		
		$this->response(array(
				'status' => FALSE,
				'message' => 'Error procesando venta.'				
		), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		
		
	}
	

		public function sale_iva_post() {
		
		$data = $this->post('data');
		
		
		$id = $this->Cart_model->sale_iva ( $data );
		
		//print_r($data); exit;					
		if($id){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => FALSE,
					'message' => 'Venta procesada con debito o credito'
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else		
		$this->response(array(
				'status' => FALSE,
				'message' => 'Error procesando venta.'				
		), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		
		
	}
	
	public function devol_get() {
	
		$id_venta = $this->get('id_venta');
		$codigo_articulo = $this->get('codigo_articulo');
		$precio_venta = $this->get('precio_venta');
		$cant_devol = $this->get('cant_devol');
		$key = $this->get('key');
		
		$id = $this->Cart_model->devol ( $id_venta, $codigo_articulo, $precio_venta, $cant_devol, $key );
	
		//print_r($data); exit;
		if($id){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => TRUE,
					'message' => 'Devolucion procesada'
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else
			$this->response(array(
					'status' => FALSE,
					'message' => 'Error procesando devolucion.'
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	
	}
	
	
	public function devol2_get() {
	

		$id_venta = $this->get('id_venta');
	
		$id = $this->Cart_model->devol2 ( $id_venta );
		//$id = true;
		//print_r($data); exit;
		if($id){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			
		
			$this->response(array(
					'status' => TRUE,
					'message' => 'Devolucion procesada'
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
			
		}
		else
			$this->response(array(
					'status' => FALSE,
					'message' => 'Error procesando devolucion.'
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	
	}
	
    public function inquire2_post() {
	
			$this->email->set_newline("\r\n");
		 
			// $data = $this->post ('data');
			$data = json_decode( $this->post('data'), true );	
			// echo $data; exit;
			$subject = 'Presupuesto Solicitado En '.$this->config->item('APP_NAME').'';
			 $body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
					<html xmlns="http://www.w3.org/1999/xhtml">
					<head>
					<link rel="stylesheet" type="text/css" href="http://rodasalias.com/tienda/app/libs/bootstrap/v336/css/bootstrap.min.css">
					<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
					<title>'.htmlspecialchars($subject, ENT_QUOTES, $this->email->charset).'</title>
					<style>
					#customers {
					  font-family: "Trebuchet MS", Arial, Helvetica, sans-serif;
					  border-collapse: collapse;
					  width: 80%;
					}
					
					#customers td, #customers th {
					  border: 1px solid #ddd;
					  padding: 8px;
					}
					
					#customers tr:nth-child(even){background-color: #f2f2f2;}
					
					/* #customers tr:hover {background-color: #ddd;} */
					
					#customers th {
					  padding-top: 12px;
					  padding-bottom: 12px;
					  text-align: left;
					  background-color: #448aff;
					  color: white;
					}
					</style>			    
				   </head>			    		
					<body>
					<a href="https://rodasalias.com/"><img src="https://rodasalias.com/tienda/img/logo.png" ></a>	
					 <strong>
					<br><br>Cliente:<br><br>
					  
					   CI/RIF: '.$data['username'].
					  '<br>Nombre: '.ucwords(strtolower($data['name'])).
					  '<br>Dirección: '.ucwords(strtolower($data['direction'])).
					  '<br>Email: '.(strtolower($data['email'])).
					  '</strong>
					<br><br>Presupuesto solicitado:<br><br>';

			 $body.= '
					<table class="table table-striped" id="customers">
					<thead>
							<tr>
							<th width="80%">Descripci&oacute;n</th>
							<th width="20%">Cantidad</th>
						</tr>
					</thead>
					<tbody>'; 
	$i = 1;
	 foreach ($data['items'] as $item): 	
	$body .= '	
		<tr>
			  <td align="center">'.$item['qty'].'</td>
		
				<td>
					<h4> '.trim(ucwords(strtolower($item['item']['nombre']))) .'
						<small> '.trim(ucwords(strtolower($item['item']['medidas']))).' </small>
					</h4>	
					<h4>
						Cod: '.trim(ucwords(strtolower($item['item']['codigo']))).'
						<small> Ref: '.trim(ucwords(strtolower($item['item']['referencia']))).' </small>'.trim(ucwords(strtolower($item['item']['marca']))).'
					</h4>	
					<h4>
						<small><i>'.trim(ucwords(strtolower($item['item']['detalles']))).'</i></small>
					</h4>	
				
				</td>
			</tr>
		 ';
	$i++;
	endforeach;
	$body.='
	
	 </tbody>
	</table>	
		';
	
	$body.=	
	'<br><br>Graciar por su interes. Pronto le responderemos.<br><br>'.
	'<a href=http://'.$this->config->item('APP_WEB').' >'.$this->config->item('APP_WEB').'</a></p>
	</body>
	</html>
			';
					
			//echo $body;
			try {
		
			$result = $this->email
			->from($this->config->item('APP_EMAIL'))
			->reply_to($data['email'])    // Optional, an account where a human being reads.
			->to(array($this->config->item('APP_EMAIL_PRESUPUESTO'), $data['email']))
			->subject($subject)
			->message($body)
			->send();
			
			if($result)
			$this->response ( array (
					'status' => true,
					'message' => 'Presupuesto enviado, muy pronto le responderemos.' 
			), REST_Controller::HTTP_OK );
			
			//var_dump($result);
			echo '<br />';
			echo $this->email->print_debugger();	
			//print_r($result);
			$this->response ( array (
					'status' => false,
					'message' => 'Error enviando email...'
			), REST_Controller::HTTP_NOT_FOUND );
				
			}catch (phpmailerException $e) {
			  echo $e->errorMessage(); //Pretty error messages from PHPMailer
			  $this->response ( array (
					  'status' => false,
					  'message' => 'Error: '.$e->errorMessage()
			  ), REST_Controller::HTTP_NOT_FOUND );
			} catch (Exception $e) {
			  echo $e->getMessage(); //Boring error messages from anything else!
				$this->response ( array (
						'status' => false,
						'message' => 'Error: '.$e->getMessage()
				), REST_Controller::HTTP_NOT_FOUND );
				
			}
		 
        }
        
        public function inquire_post() {
	
        $this->email->set_newline("\r\n");
		 
		$data = $this->post ('data');
		// $data = json_decode( $this->post('data'), true );	
		// echo $data; exit;
        $subject = 'Presupuesto Solicitado En '.$this->config->item('APP_NAME').'';
     	$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
                <link rel="stylesheet" type="text/css" href="http://rodasalias.com/tienda/app/libs/bootstrap/v336/css/bootstrap.min.css">
			    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			    <title>'.htmlspecialchars($subject, ENT_QUOTES, $this->email->charset).'</title>			    
			   </head>			    		
    			<body>
			    <a href="http://rodasalias.com/tienda"><img src="http://rodasalias.com/tienda/img/logo.png" ></a>	
     			<strong>
			    <br><br>Cliente:<br><br>
				  
			       CI/RIF: '.$data['username'].
				  '<br>Nombre: '.ucwords(strtolower($data['name'])).
				  '<br>Dirección: '.ucwords(strtolower($data['direction'])).
				  '<br>Email: '.(strtolower($data['email'])).
				  '</strong>
				<br><br>Presupuesto solicitado:<br><br>';
     	$body.= '
     			
<table class="table table-striped">
<thead>
        <tr>
         <th width="20%">Cantidad</th>
         <th width="80%">Descripci&oacute;n</th>
       </tr>
</thead>
 <tbody>'; 
$i = 1;
 foreach ($data['items'] as $item): 	
$body .= '	
	<tr>
          <td align="center">'.$item['quantity'].'</td>
	
            <td>
                <h4> '.trim(ucwords(strtolower($item['name']))) .'
                    <small> '.trim(ucwords(strtolower($item['data']['medidas']))).' </small>
                </h4>	
                <h4>
                    Cod: '.trim(ucwords(strtolower($item['data']['codigo']))).'
                    <small> Ref: '.trim(ucwords(strtolower($item['data']['referencia']))).' </small>'.trim(ucwords(strtolower($item['data']['marca']))).'
                </h4>	
                <h4>
                    <small><i>'.trim(ucwords(strtolower($item['data']['detalles']))).'</i></small>
                </h4>	
            
            </td>
        </tr>
	 ';
$i++;
endforeach;
$body.='

 </tbody>
</table>	
	';

$body.=	
'<br><br>Graciar por su interes. Pronto le responderemos.<br><br>'.
'<a href=http://'.$this->config->item('APP_WEB').' >'.$this->config->item('APP_WEB').'</a></p>
</body>
</html>
        ';
                
		//echo $body;
		try {
	
		$result = $this->email
		->from($this->config->item('APP_EMAIL'))
		->reply_to($data['email'])    // Optional, an account where a human being reads.
		->to(array($this->config->item('APP_EMAIL_PRESUPUESTO'), $data['email']))
		->subject($subject)
		->message($body)
		->send();
		
		if($result)
		$this->response ( array (
				'status' => true,
				'message' => 'Presupuesto enviado, muy pronto le responderemos.' 
		), REST_Controller::HTTP_OK );
		
		//var_dump($result);
		echo '<br />';
		echo $this->email->print_debugger();	
		//print_r($result);
		$this->response ( array (
				'status' => false,
				'message' => 'Error enviando email...'
		), REST_Controller::HTTP_NOT_FOUND );
			
		}catch (phpmailerException $e) {
		  echo $e->errorMessage(); //Pretty error messages from PHPMailer
		  $this->response ( array (
		  		'status' => false,
		  		'message' => 'Error: '.$e->errorMessage()
		  ), REST_Controller::HTTP_NOT_FOUND );
		} catch (Exception $e) {
		  echo $e->getMessage(); //Boring error messages from anything else!
			$this->response ( array (
					'status' => false,
					'message' => 'Error: '.$e->getMessage()
			), REST_Controller::HTTP_NOT_FOUND );
			
		}
	
	}
        
	public function save_post() {
	
		$data = $this->post('data');
		$codClient = $this->post('codClient');
		$name = $this->post('name');
		$sector = $this->post('zone');
	
	
		$id = $this->Cart_model->save ( $data, $codClient, $name, $sector );
		//$id='';
		//print_r($data); exit;
		if($id==''){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => TRUE,
					'message' => 'Guardado en espera...'//.$name//.$dataClient//.json_encode($id)
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else
			$this->response(array(
					'status' => FALSE,
					'message' => json_encode($id)
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	
	}

	public function save2_post() {
	
		$data = ( json_decode($this->post('data') ));
		$codClient = $this->post('codClient');
		$name = $this->post('name');
		$sector = $this->post('zone');
	
	
		$id = $this->Cart_model->save2 ( $data, $codClient, $name, $sector );
		//$id='';
		//print_r($data); exit;
		if($id==''){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => TRUE,
					'message' => 'Guardado en espera...'//.$name//.$dataClient//.json_encode($id)
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else
			$this->response(array(
					'status' => FALSE,
					'message' => json_encode($id)
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	
	}
	
	
public function exist2_post() {
	
		//$data = $this->post('data');
		$data = ( json_decode($this->post('data') ));
		//$item = $var[0]->item;
		//print_r($item->codigo);exit;

		//$data = ($this->post('data'));
		//$data = get_object_vars(json_decode($data));
		//$data = get_object_vars($data['items']);
		//print_r(($data->id));exit;
		
		//print_r(($this->post()));exit;
		$exist = $this->Cart_model->exist2 ( $data );
	
		//print_r($data); exit;
		if($exist){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => TRUE,
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


	public function exist_post() {
	
		$data = $this->post('data');

		$exist = $this->Cart_model->exist ( $data );
	
		//print_r($data); exit;
		if($exist){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => TRUE,
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

		public function price2_post() {
	
		$data = json_decode( $this->post('data') );	
		$dolarToday = $this->post('dolarToday');
		//$codigo = $this->post('codigo');
		//$codigo_grupo = $this->post('codigo_grupo');
		//$precio = $this->post('precio');
		//print_r ($data); exit;
		
		$precio1 = $this->Cart_model->price2 ( $data, $dolarToday );

		//$item = $this->post('item');
		//$dolarToday = $this->post('dolarToday');
		
		
		//$precio1 = $this->Cart_model->price ( $item, $dolarToday );
		//$precio1 = '11';
		//print_r($codigo); exit;
		if($precio1!=''){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => TRUE,
					'message' => 'Precio ajustado...'//.$codigo
					//'price' => ($data)
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else
			$this->set_response(array(
					'status' => FALSE,
					'message' => 'Error.'
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	
	}
	
	public function price_post() {
	
		//$data = $this->post('data');	
		
		$item = $this->post('item');
		$dolarToday = $this->post('dolarToday');
		
		
		$precio1 = $this->Cart_model->price ( $item, $dolarToday );
	
		//print_r($data); exit;
		if($precio1!=''){
			//$id = $this->Cart_model->saleDetails ( $id, $items );
			$this->set_response(array(
					'status' => TRUE,
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
					'status' => TRUE,
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