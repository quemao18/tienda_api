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
//require APPPATH . '/libraries/Rif.php';


/**
 * Projects API controller
 *
 * Validation is missign
 */
class Users extends REST_Controller {
	
	public function __construct() {
		parent::__construct ();
		$this->load->model ( 'Users_model' );
		$this->load->library('email');

		//$this->methods['user_get']['limit'] = 500; // 500 requests per hour per user/key
		//$this->methods['user_post']['limit'] = 100; // 100 requests per hour per user/key
		//$this->methods['user_delete']['limit'] = 50; // 50 requests per hour per user/key			

	}
	
	public function nombre_rif_get(){
		
		$this->load->library('rif');
		
		$rif    = $this->get("rif");	
		$nombre_cne='';
		$nombre = '';
		
		if(substr($rif, 0, 1)=='V' || substr($rif, 0, 1)=='E')
		{
			$ced= 'http://www.cne.gob.ve/web/registro_civil/buscar_rep.php?nac=&ced='.substr($rif, 1);
			//$ced= 'http://www.cne.gob.ve/web/registro_civil/buscar_rep.php?nac=&ced='.substr($rif, 1);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $ced);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$result = curl_exec ($ch);
			$nombre_cne = getBetween($result, '<b>', '</b>');
		}/*else{
			$ced= 'http://contribuyente.seniat.gob.ve/getContribuyente/getrif?rif='.substr($rif, 0);
			//echo $ced;
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $ced);
			curl_setopt($ch, CURLOPT_TIMEOUT, 30);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			$result = curl_exec ($ch);
			$nombre_cne = getBetween($result, '(', ')');
			//echo $nombre_rif_;
		}		*/
		
		//$rif = new Rif($usuario);
		$this->rif->setRif($rif);
		 
		// Obtener los datos fiscales
		$datosFiscales = json_decode($this->rif->getInfo());
		//echo $datosFiscales->code_result;
		//
		//var_dump($datosFiscales);
		if($datosFiscales->code_result==1){
			//$nombre=utf8_decode(ucwords(strtolower($datosFiscales->seniat->nombre)));
			
				$nombre=utf8_decode((($datosFiscales->seniat->nombre)));
				$nombre = getBetween((($nombre)), '(', ')');
			
			$this->response(array(
					'status' => TRUE,
					'nombre' => $nombre
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
			//echo getBetween((($nombre)), '(', ')');
		}
		elseif($nombre_cne!=''){
			$nombre = $nombre_cne;
			$this->response(array(
					'status' => TRUE,
					'nombre' => $nombre
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		else {
			$this->response(array(
					'status' => FALSE,
					'nombre' => '',
					'message' => 'Rif o Cedula no encontrado...'
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		}
		//echo $usuario;
	
	}
	
		public function forget_get() {
		//$this->response( $this->project_model->get_all ());
		$username = $this->get('username');
		//$password = $this->get("password");
		//$clave = rand(123456, 999999);
		$clave = substr(md5(time()), 0, 6);
                 
		$temp = $this->Users_model->setPasswordTemp($username, $clave);
		$user = $this->Users_model->getUser($username);
		//$this->set_response(array('message' => 'user'.$username), REST_Controller::HTTP_NOT_FOUND);
		
		if (!empty($temp)){
			$this->response(array(
					'status' => TRUE,
					'message' => 'Password cambiado...',
					'password' => $clave, 
					'user' => $user
			), REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code			
		}else{				
		$this->response(array(
					'status' => FALSE,
					'message' => 'Usuario no encontrado'
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code	
		}	
	}
	
	public function user_get() {
		//$this->response( $this->project_model->get_all ());
		$username = $this->get('username');
		$password = $this->get("password");
		
                 
		$user = $this->Users_model->getUser($username, $password);
		//$this->set_response(array('message' => 'user'.$username), REST_Controller::HTTP_NOT_FOUND);
		
		if (empty($user)){
			$this->response(array(
					'status' => FALSE,
					'message' => 'Usuario o password incorrectos'
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code			
		}
		if ( $user['estado'] == 0 ){
				$this->response(array(
						'status' => FALSE,
						'message' => 'Cuenta no activa.'
				), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		}	
		
		$this->Users_model->setLastLogin($username);
		$this->response($user, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code					
		
	}
	
	
		public function user2_post() {
		//$this->response( $this->project_model->get_all ());
		$username = $this->post('username');
		$password = $this->post("password");
		
                 
		$user = $this->Users_model->getUser($username, $password);
		//$this->set_response(array('message' => 'user'.$username), REST_Controller::HTTP_NOT_FOUND);
		
		if (empty($user)){
			$this->response(array(
					'status' => FALSE,
					'message' => 'Usuario o password incorrectos'
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code			
		}
		if ( $user['estado'] == 0 ){
				$this->response(array(
						'status' => FALSE,
						'message' => 'Cuenta no activa.'
				), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		}	
		
		$this->Users_model->setLastLogin($username);
		$this->response($user, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code					
		
	}
	
	public function edit_get() {
		
		$user = $this->get ('user');
		$name = $this->get ('name');
		$email = $this->get ('email');
		$direction = $this->get ('direction');
		$phone = $this->get ('phone');
		$password = $this->get ('password');
		$checkEmail = $this->Users_model->checkEmail($email, $user);
		
		//if ( !empty($userEmail)) {
	
		if ( !$user  ) {
			 $this->response(array(
                    'status' => FALSE,
                    'message' => 'Usuario no encontrado.'
                ), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		}
		if ( $checkEmail ) {
			$this->response(array(
					'status' => FALSE,
					'message' => 'Email ya registrado.'
			), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		}
		
		$reg = $this->Users_model->editUser ( urldecode($name), urldecode($user), urldecode($password), urldecode($email), urldecode($direction), urldecode($phone) );
		
		$this->response (array(
					'status' => true,
					'message' => 'Guardado...'
				),	REST_Controller::HTTP_OK);
	}
	
	public function user_post() {
		
		$user = $this->post ('user');
		$name = $this->post ('name');
		$email = $this->post ('email');
		$direction = $this->post ('direction');
		$phone = $this->post ('phone');
		$password = $this->post ('password');
		//$random = rand(1782598471, 9999999999);
		//$random = crc32($user);
		$user_= $this->Users_model->getUser($user);		
		$userEmail = $this->Users_model->getUser($email);
		
		if ( !empty($user_)  || !empty($userEmail)) {
			 $this->set_response(array(
                    'status' => FALSE,
                    'message' => 'Nombre de usuario o Email ya existe.'
                ), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		}else{
			
			$reg = $this->Users_model->registerUser ( urldecode($name), urldecode($user), urldecode($password), urldecode($email), urldecode($direction), urldecode($phone) );			
			$this->response ( array (
					'status' => true,
					'message' => 'Registrado.'
			), REST_Controller::HTTP_OK );
			
		}
		
		
			//$this->set_response(array('name'=>$this->post('name')), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
		
	}
	
	public function client_premium_post() {
	
		$user = $this->post ('user');
		$name = $this->post ('name');
		$type = $this->post ('type');
		$direction = $this->post ('direction');
		$phone = $this->post ('phone');
		$zone = $this->post ('zone');
	
		//$user_= $this->Users_model->getUser($user);
		//$userEmail = $this->Users_model->getUser($email);
	

			$reg = $this->Users_model->registerClientPremium ( urldecode($name), urldecode($user), urldecode($zone), urldecode($type), urldecode($direction), urldecode($phone) );
			if($reg)
			$this->response ( array (
					'status' => true,
					'message' => 'Cliente Registrado...'
			), REST_Controller::HTTP_OK );
			else
			$this->set_response ( array (
					'status' => false,
					'message' => 'Error'
			), REST_Controller::HTTP_NOT_FOUND );
						
	
		//$this->set_response(array('name'=>$this->post('name')), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	}


	public function client_premium2_post() {
	
		$data = ( json_decode($this->post('data') ));
		// $user = $this->post ('user');
		// $name = $this->post ('name');
		// $type = $this->post ('type');
		// $direction = $this->post ('direction');
		// $phone = $this->post ('phone');
		// $zone = $this->post ('zone');
	
		//$user_= $this->Users_model->getUser($user);
		//$userEmail = $this->Users_model->getUser($email);
	

			$reg = $this->Users_model->registerClientPremium2 ( $data );
			if($reg)
			$this->response ( array (
					'status' => true,
					'message' => 'Cliente Registrado...'
			), REST_Controller::HTTP_OK );
			else
			$this->set_response ( array (
					'status' => false,
					'message' => 'Error'
			), REST_Controller::HTTP_NOT_FOUND );
						
	
		//$this->set_response(array('name'=>$this->post('name')), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	}
	
	public function client_premium_get() {
	
		$user = $this->get ('user');
		//$name = $this->post ('name');
		
		$results = $this->Users_model->getClientPremium($user);
		//$results = array('nombre'=>$this->get('user'));
		//$this->set_response($results, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
		$this->response ( array (
				'status' => true,
				'results' => $results
		), REST_Controller::HTTP_OK );
	
		//$this->set_response(array('name'=>$this->post('name')), REST_Controller::HTTP_NOT_FOUND); // NOT_FOUND (404) being the HTTP response code
	
	}
	
	public function client_type_premium_get() {
	
		//$user = $this->get ('user');
		//$name = $this->post ('name');
	
		$results = $this->Users_model->getClientTypePremium();
		//$results = array('nombre'=>$this->get('user'));
		//$this->set_response($results, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
	
		$this->set_response($results, REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
	
	}
	
	
	public function client_zone_premium_get() {
	
		//$user = $this->get ('user');
		//$name = $this->post ('name');
		//$zone = $this->get ('zone');
		$results = $this->Users_model->getClientZonePremium();
		//$results = array('nombre'=>$this->get('user'));
		//$this->set_response($results, REST_Controller::HTTP_OK); // OK (200) being the HTTP response code
	
		$this->set_response($results, REST_Controller::HTTP_OK); // NOT_FOUND (404) being the HTTP response code
		/*$this->response ( array (
				'status' => true,
				'results' => $results
		), REST_Controller::HTTP_OK ); */
		
	}
	
	public function no_save_post_no($id = NULL) {
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


	public function sendEmailPassword_post(){
		
		$this->email->set_newline("\r\n");
		 
		$id = $this->post ('user');
		$pass = $this->post ('password');
		$email = $this->post ('email');
		$name = $this->post ('name');
		
		//$this->load->library('email');
		$subject = 'Cambio de Password en '.$this->config->item('APP_NAME').'';
		$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
			    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			    <title>'.htmlspecialchars($subject, ENT_QUOTES, $this->email->charset).'</title>
			    <style type="text/css">
							body{
			    		 	font-family: Arial, Verdana, Helvetica, sans-serif;
            				font-size: 14px;
							}
			    </style>
				</head>
    			<body>
			    <a href="http://rodasalias.com/tienda"><img src="http://rodasalias.com/tienda/img/logo.png" ></a>	
     			<p>Hola <strong>' . ucwords(strtolower($name)) . '</strong>, ' .
	     			'<br><br>'.
	     			' Su password temporal es : '.$pass.'<br><br>' .
	     			'<a href=http://rodasalias.com/tienda/#login>Login</a>'.
	     			'<br><br>'.	     			
					  ' </strong><br><br>Graciar por su interes.<br><br>'.
					  '<a href=http://'.$this->config->item('APP_WEB').' >'.$this->config->item('APP_WEB').'</a></p>
    
				</body>
				</html>
						';
		 
		//echo $body;
		try {
	
		$result = $this->email
		->from($this->config->item('APP_EMAIL'))
		->reply_to($this->config->item('APP_EMAIL'))    // Optional, an account where a human being reads.
		->to($email)
		->subject($subject)
		->message($body)
		->send();
		
		if($result)
		$this->response ( array (
				'status' => true,
				'message' => 'Su password temporal fue enviado a su email...' 
		), REST_Controller::HTTP_OK );
		
		//var_dump($result);
		//echo '<br />';
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
		/*
			if(!$result) {
			//return false;
			$this->response ( array (
					'status' => false,
					'message' => 'Error.'
			), REST_Controller::HTTP_NOT_FOUND );
		}else{
			//return true;
			$this->response ( array (
					'status' => true,
					'message' => 'Email enviado con exito...'
			), REST_Controller::HTTP_OK );
		}
		*/
	}

	
	public function sendEmailRegister_post(){
		
		$this->email->set_newline("\r\n");
		 
		$id = $this->post ('user');
		$name = $this->post ('name');
		$email = $this->post ('email');
		$code = $this->post ('code');
		//$direction = $this->post ('direction');
		//$phone = $this->post ('phone');
		$pass = $this->post ('password');
		
		//$this->load->library('email');
		$subject = 'Registro en '.$this->config->item('APP_NAME').'';
		$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
			    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			    <title>'.htmlspecialchars($subject, ENT_QUOTES, $this->email->charset).'</title>
			    <style type="text/css">
							body{
			    		 	font-family: Arial, Verdana, Helvetica, sans-serif;
            				font-size: 14px;
							}
			    </style>
				</head>
    			<body>
			    <a href="http://rodasalias.com/tienda"><img src="http://rodasalias.com/tienda/img/logo.png" ></a>	
     			<p>Hola <strong>' . ucwords(strtolower($name)) . '</strong>, ' .
	     			'<br><br>Bienvenido a <strong>' .$this->config->item('APP_WEB'). '</strong>'.
	     			' para activar su cuenta haga clic sobre el siguiente enlace: <br><br>' .
	     			'<a href=http://rodasalias.com/tienda/#users/activate/' .$id. '>Activar</a>'.
	     			'<br><br>'.
	     			'Le recordamos que sus datos para ingresar son:<br>
				  <strong>CI/RIF: '.$id.'<br>Password: '.$pass.
					  ' </strong><br><br>Graciar por su interes.<br><br>'.
					  '<a href=http://'.$this->config->item('APP_WEB').' >'.$this->config->item('APP_WEB').'</a></p>
    
				</body>
				</html>
						';
		 
		//echo $body;
		try {
	
		$result = $this->email
		->from($this->config->item('APP_EMAIL'))
		->reply_to($this->config->item('APP_EMAIL'))    // Optional, an account where a human being reads.
		->to($email)
		->subject($subject)
		->message($body)
		->send();
		
		if($result)
		$this->response ( array (
				'status' => true,
				'message' => 'Revise su email para activar su cuenta...' 
		), REST_Controller::HTTP_OK );
		
		//var_dump($result);
		//echo '<br />';
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
		/*
			if(!$result) {
			//return false;
			$this->response ( array (
					'status' => false,
					'message' => 'Error.'
			), REST_Controller::HTTP_NOT_FOUND );
		}else{
			//return true;
			$this->response ( array (
					'status' => true,
					'message' => 'Email enviado con exito...'
			), REST_Controller::HTTP_OK );
		}
		*/
	}

	public function sendEmailTest_get(){

        require_once(APPPATH.'third_party/PHPMailer-master/PHPMailerAutoload.php');
        $mail = new PHPMailer();
        $mail->IsSMTP(); // we are going to use SMTP
        $mail->SMTPAuth   = true; // enabled SMTP authentication
        $mail->SMTPSecure = "ssl";  // prefix for secure protocol to connect to the server
        $mail->Host       = "smtp.gmail.com";      // setting GMail as our SMTP server
        $mail->Port       = 465;                   // SMTP port to connect to GMail
        $mail->Username   = "alejandro.toba@gmail.com";  // user email address
        $mail->Password   = "at04550428at";            // password in GMail
        $mail->SetFrom('alejandro.toba@gmail.com', 'Mail');  //Who is sending 
        $mail->isHTML(true);
        $mail->Subject    = "Mail Subject";
        $mail->Body      = '
            <html>
            <head>
                <title>Title</title>
            </head>
            <body>
            <h3>Heading</h3>
            <p>Message Body</p><br>
            <p>With Regards</p>
            <p>Your Name</p>
            </body>
            </html>
        ';
        $destino = 'alejandro.toba@gmail.com'; // Who is addressed the email to
        $mail->AddAddress($destino, "Receiver");
		//var_dump($mail->result());
        if(!$mail->Send()) {
            echo 'error: '.$mail->ErrorInfo;
        } else {
            echo  'enviado';
        }

	}
	
	public function sendEmailUpdate_get(){
	
		$this->email->set_newline("\r\n");
		
		$id = $this->get ('user');
		$name = $this->get ('name');
		$email = $this->get ('email');
		$pass = $this->get ('pass');
		
		//$this->load->library('email');
		$subject = 'Actualizacion de datos en '.$this->config->item('APP_NAME').'';
		$body = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
				<html xmlns="http://www.w3.org/1999/xhtml">
				<head>
			    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
			    <title>'.htmlspecialchars($subject, ENT_QUOTES, $this->email->charset).'</title>
			    <style type="text/css">
							body{
			    		 	font-family: Arial, Verdana, Helvetica, sans-serif;
            				font-size: 14px;
							}
				
			    </style>
				</head>
    			<body>
			    <a href="http://rodasalias.com/tienda"><img src="http://rodasalias.com/tienda/img/logo.png" ></a>	
            	<p>Hola <strong>' . ucwords(strtolower($name)) . '</strong>, ' .
	            	'<br><br> Ha actualizado sus datos en <strong>' .$this->config->item('APP_WEB'). '</strong>'.
	            	//' su cuenta haga clic sobre el siguiente enlace: <br><br>' .
		//'<a href="' . base_url() .'registro/activar/' .
		//$id. '/' . $codigo. '">' .
		//base_url() .'registro/activar/' .
		//$id . '/' . $codigo.'</a>'.
		'<br><br>'.
		//'Le recordamos que sus datos para ingresar son:<br>
		//		  <strong>CI/RIF: '.$id.'<br>Password: '.$pass.</strong><br><br>
		'Graciar por su interes.<br><br>'.
		'<a href=http://'.$this->config->item('APP_WEB').' >'.$this->config->item('APP_WEB').'</a></p>
	
    			</body>
				</html>
    				  		';
	
		try{
		$result = $this->email
		->from($this->config->item('APP_EMAIL'))
		->reply_to($this->config->item('APP_EMAIL'))    // Optional, an account where a human being reads.
		->to($email)
		->subject($subject)
		->message($body)
		->send();
	
		if($result)
		$this->response ( array (
				'status' => true,
				'message' => 'Email enviado con exito...' 
		), REST_Controller::HTTP_OK );
		
		//var_dump($result);
		//echo '<br />';
		echo $this->email->print_debugger();
		
		
		//print_r($result);
		$this->response ( array (
				'status' => false,
				'message' => 'Error enviando email...'.$result->ErrorInfo
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
	
	public function activate_get()
	{
		$user = $this->get ('user');
		$code = $this->Users_model->getUserCodeId ($user);
		//$cr32 =  hash('crc32', $user);
		if (! $code) {			
			$this->response ( array (
					'status' => false,
					'message' => 'Codigo no encontrado.'
			), REST_Controller::HTTP_NOT_FOUND );
		} else {
				$row = $this->Users_model->getUserCode($user, $code);
				if(empty($row)){
					$this->response ( array (
						'status' => false,
						'message' => 'Esta cuenta no existe.'
				), REST_Controller::HTTP_NOT_FOUND );			
				}
				if($row['estado'] == 1){
					$this->response ( array (
							'status' => false,
							'message' => 'Esta cuenta ya fue activada.'
					), REST_Controller::HTTP_NOT_FOUND );
				}
				$row = $this->Users_model->activateUser($user, $code);
				$this->response ( array (
						'status' => true,
						'message' => 'Cuenta activada con exito.'
				), REST_Controller::HTTP_OK );
	
			}
	}
	
}

/* End of file Products.php */
/* Location: ./application/controllers/api/Products.php */