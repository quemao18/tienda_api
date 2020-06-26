<?php
class Users_model extends CI_Model
{
	private $_db_web = null;
	private $_db_premium = null;
	
    public function __construct() {
        parent::__construct(); 
       // $this->load->database('web');
        $this->_db_web=$this->load->database('web', TRUE);
        $this->_db_premium=$this->load->database('premium', TRUE);
    }
        
    
    public function getUser($username, $password  = NULL)
    {
    	//$query = $this->_DB_WEB->get('usuario_web');
    	//return $query->result();
    	if(!empty($password))
    	$datos = $this->_db_web->query(
    			"select * from usuario_web " .
    			"where (usuario_web.nombre_usuario = '$username' or usuario_web.email = '".$username ."')".
    			//"and usuario_web.clave  = '" . Hash::getHash('md5', $password, HASH_KEY) ."'"
    			"and usuario_web.clave = '" .md5($password)."'"
    	);
    	else
    	$datos = $this->_db_web->query(
    			"select * from usuario_web " .
    			"where usuario_web.nombre_usuario = '$username' or usuario_web.email = '".$username ."'"
    			);
    	//if ($datos->num_rows()  > 0)
    		return $datos->row_array();
    	//return FALSE;
    
    }
    
    public function getClientPremium($user)
    {
    	//$query = $this->_DB_WEB->get('usuario_web');
    	//return $query->result();
    	
    			$datos = $this->_db_premium->query(
    					"select trim(nombre) nombre, trim(codigo) codigo, trim(sector) zona, trim(tipo) tipo, trim(direccion) direccion, telefonos from cliempre " .
    					" where codigo like '%".$user."%' or nombre like '%".$user."%' "
    					);
    			//if ($datos->num_rows()  > 0)
    			return $datos->result();
    			//return FALSE;
    
    }
    
    public function getClientTypePremium()
    {
    	//$query = $this->_DB_WEB->get('usuario_web');
    	//return $query->result();
    	 
    	$datos = $this->_db_premium->query(
    			"select trim(codigo) codigo, trim(nombre) nombre from tipocli "
    			);
    	//if ($datos->num_rows()  > 0)
    	return $datos->result();
    	//return FALSE;
    
    }
    
    public function getClientZonePremium()
    {
    	//$query = $this->_DB_WEB->get('usuario_web');
    	//return $query->result();
    
    	$datos = $this->_db_premium->query(
    			"select trim(codigo) codigo, trim(zona) nombre from sectores "
    			);
    	//if ($datos->num_rows()  > 0)
    	return $datos->result();
    	//return FALSE;
    
    }
       
	public function setToken($user, $token)
    {
    	$this->_db_web->query(
    			"update usuario_web set token = '$token' " .
    			"where id_usuario = '$user'  "
    	);
    }
	
    public function setLastLogin($user)
    {
    	$this->_db_web->query(
    			"update usuario_web set ultimo_login = now() " .
    			"where id_usuario = '$user'  "
    	);
    }
	
	
	 public function setPasswordTemp($user, $clave)
    {
    	
    
    	$id = $this->_db_web->query(
    			"update usuario_web set clave =  md5('$clave') " .
    			"where id_usuario = '$user' or email = '$user'  "
    	);
    	
    	$afftectedRows = $this->_db_web->affected_rows();
		
		return $afftectedRows;
    }
    
  	public function registerUser($name, $id, $password, $email, $direction, $phone)
    {
    	$random = hash('crc32', $email);
		//$random = rand('12345678', '99999999');
    	$data = array(
    			'id_usuario' 						=> $id,
    			'tipo_usuario_id_tipo_usuario' 		=> '2',
    			'nombre'           					=> $name,
    			'apellido'							=> '',
    			'nombre_usuario'    				=> $id,
    			//':password' 						=> Hash::getHash('sha1', $password, HASH_KEY),
    			'email' 							=> $email,
    			'direccion' 						=> $direction,
    			'telefono' 							=> $phone,
    			'clave' 							=> md5($password),
    			'fecha_modificacion'				=> '',
    			'fecha_creacion' 					=> date('Y-m-d H:i:s'),
    			'codigo' 							=> $random,
    			'estado'							=> '0',
    			'ultimo_login'						=> ''
    	);
    	
    	$this->_db_web->insert('usuario_web', $data);
    	return $random;
    	// return $this->_db_web->_error_message();
    }
    
    public function registerClientPremium($name, $id, $zone, $type, $direction, $phone)
    {
    	//$random = rand(1782598471, 9999999999);
    	$data = array(
    			'id_empresa'						=> '001000',
    			'agencia' 							=> '001',
    			'status' 							=> '1',
    			'formafis' 							=> '1',
    			'codigo' 							=> $id,
    			'nombre'           					=> $name,
    			'cedula'							=> $id,
    			'nrorif'  							=> $id,
    			'tipo' 								=> $type,
    			'direccion' 						=> $direction,
    			'telefonos'							=> $phone,
    			'fecha' 							=> date('Y-m-d'),
    			'precio' 							=> '1',
    			'credito' 							=> 'N',
    			'sector' 							=> $zone
    			
    	);
    	
    	$data2 = array(
    			//'codigo' 							=> $id,
    			'nombre'           					=> $name,
    			'cedula'							=> $id,
    			'nrorif'   							=> $id,
    			'tipo' 								=> $type,
    			'direccion' 						=> $direction,
    			'telefonos'							=> $phone,
    			//'fecha' 							=> date('Y-m-d'),
    			//'precio' 							=> '1',
    			//'credito' 							=> 'N',
    			'sector' 							=> $zone
    			 
    	);
    	
    	
    	if($this->_db_premium->query("select codigo from cliempre where codigo='$id'")->num_rows()>0)
    		return $this->_db_premium->where('codigo', $id)->update('cliempre', $data2);
    	else
    		return $this->_db_premium->insert('cliempre', $data);
    
    	// return $this->_db_web->_error_message();
	}
	

	public function registerClientPremium2($data)
    {
		//print_r($data); exit;
		$id = $data->user;
		$phone = $data->phone;
		$type = $data->type;
		$direction = $data->direction;
		$zone = $data->zone;
		$name = $data->name;
    	//$random = rand(1782598471, 9999999999);
    	$data = array(
    			'id_empresa'						=> '001000',
    			'agencia' 							=> '001',
    			'status' 							=> '1',
    			'formafis' 							=> '1',
    			'codigo' 							=> $id,
    			'nombre'           					=> $name,
    			'cedula'							=> $id,
    			'nrorif'  							=> $id,
    			'tipo' 								=> $type,
    			'direccion' 						=> $direction,
    			'telefonos'							=> $phone,
    			'fecha' 							=> date('Y-m-d'),
    			'precio' 							=> '1',
    			'credito' 							=> 'N',
    			'sector' 							=> $zone
    			
    	);
    	
    	$data2 = array(
    			//'codigo' 							=> $id,
    			'nombre'           					=> $name,
    			'cedula'							=> $id,
    			'nrorif'   							=> $id,
    			'tipo' 								=> $type,
    			'direccion' 						=> $direction,
    			'telefonos'							=> $phone,
    			//'fecha' 							=> date('Y-m-d'),
    			//'precio' 							=> '1',
    			//'credito' 							=> 'N',
    			'sector' 							=> $zone
    			 
    	);
    	
    	
    	if($this->_db_premium->query("select codigo from cliempre where codigo='$id'")->num_rows()>0)
    		return $this->_db_premium->where('codigo', $id)->update('cliempre', $data2);
    	else
    		return $this->_db_premium->insert('cliempre', $data);
    
    	// return $this->_db_web->_error_message();
    }
    
	public function editUser($name, $user, $password = NULL, $email, $direction, $phone){
		if(!empty($password)){
		$data = array(
				//'id_usuario' 						=> $usuario,
				//'tipo_usuario_id_tipo_usuario' 		=> '2',
				'nombre'           					=> $name,
				'apellido'							=> '',
				//'nombre_usuario'    				=> $usuario,
				//':password' 						=> Hash::getHash('sha1', $password, HASH_KEY),
				'email' 							=> $email,
				'direccion' 						=> $direction,
				'telefono' 							=> $phone,
				'clave' 							=> md5($password),
				'fecha_modificacion'				=> date('Y-m-d H:i:s')
				//'fecha_creacion' 					=> date('Y-m-d H:i:s'),
				//'codigo' 							=> $random,
				//'estado'							=> '0',
				//'ultimo_login'						=> ''
		);
		}else{ 
		$data = array(
				//'id_usuario' 						=> $usuario,
				//'tipo_usuario_id_tipo_usuario' 		=> '2',
				'nombre'           					=> $name,
				'apellido'							=> '',
				//'nombre_usuario'    				=> $usuario,
				//':password' 						=> Hash::getHash('sha1', $password, HASH_KEY),
				'email' 							=> $email,
				'direccion' 						=> $direction,
				'telefono' 							=> $phone,
				//'clave' 							=> md5($password),
				'fecha_modificacion'				=> date('Y-m-d H:i:s')
				//'fecha_creacion' 					=> date('Y-m-d H:i:s'),
				//'codigo' 							=> $random,
				//'estado'							=> '0',
				//'ultimo_login'						=> ''
		);
		}
		
		$this->_db_web->where('id_usuario', $user);
		return $this->_db_web->update('usuario_web', $data);
		//print_r($this->_db_web->last_query());
	}
	

	
	public function mostraUsuarios(){
		$query = $this->_db_web->get('usuario');
		$query_result = $query->result();
		return $query_result;
	}
	
	//si existe el email para otro usuario devuelve true
	public function checkEmail($email, $user)
	{
			$id = $this->_db_web->query(
					"select * from usuario_web where email = '$email' and id_usuario!='$user'"
			);
		//print_r($this->_db_web->last_query());
		//return $id->row_array();
		if($id->num_rows()>0)
			return true;
		else
			return false;
		 
	}	
	
	public function activateUser($id, $code)
	{
		$this->_db_web->query(
				"update usuario_web set estado = 1 " .
				"where id_usuario = '$id' and codigo = '$code'"
		);
	}
	
	
	public function getUserCode($id, $code)
	{
		$usuario = $this->_db_web->query(
				"select * from usuario_web where id_usuario = '$id' and codigo = '$code' "
		);
			
		return $usuario->row_array();
	}
	
	
	public function getUserCodeId($id)
	{
		$codigo= '';
		$query = $this->_db_web->query(
				"select codigo from usuario_web where id_usuario = '$id' "
				);
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$codigo = $row->codigo;
		}
		return $codigo;
		
	}

	
}
/* End of file usuariomodel.php */
/* Location: ./application/models/usuariomodel.php */
