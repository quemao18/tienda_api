<?php if (! defined ( 'BASEPATH' ))	exit ( 'No direct script access allowed' );

class Products_model extends CI_Model {
	
	private $_db_premium = null;
	public $table = 'articulo';
	
	public function __construct() {
		parent::__construct();
		$this->_db_premium=$this->load->database('premium', TRUE);
	
		
	}
	
	private function _contruct_select(){
		$this->_db_premium->select($this->config->item('SELECT_ARTICULO'));
		
		$this->_db_premium->join("existenc", "existenc.codigo = articulo.codigo");
		$this->_db_premium->join("grupos", "grupos.codigo = articulo.grupo");
		//$this->_db_premium->join("subgrupos", "articulo.subgrupo = subgrupos.subcodigo");
		$this->_db_premium->join("almacene", "existenc.almacen= almacene.codigo");
		$this->_db_premium->where("existenc.almacen", $this->config->item('CODIGO_ALMACEN'), "articulo.discont", '0');
		
		foreach ($this->config->item('GRUPOS_NO_VISIBLES') as $gp=>$val)
			$this->_db_premium->where("articulo.grupo !=", $val);
		//$this->_db_premium->select('*');
		
	}
	
	public function get_all($group, $topic) {
		//$this->_db_premium->select($this->config->item('SELECT_ARTICULO'));
		$this->_contruct_select();
		//$this->_db_premium->select('count(DISTINCT articulo.grupo) as filas');
		//$this->_contruct_select();
		//$this->_db_premium->like ('articulo.codigo', $topic);
		$this->_db_premium->where(
				"(
    				articulo.codigo 		LIKE '%".$topic."%' 	OR
    			  	articulo.nombre 		LIKE '%".$topic."%' 	OR
    			  	articulo.detalles 		LIKE '%".$topic."%'		OR
    				articulo.modelo			LIKE '%".$topic."%'		OR
    				articulo.referencia 	LIKE '%".$topic."%'
    			)"
				);
		if($group!='' && $group!='todos' )
    		$this->_db_premium->where('articulo.grupo', $group);	
		if($group=='')
			$this->_db_premium->group_by ('codigo_grupo');
		
		if(strpos($topic, 'SET') !== false)
			$this->_db_premium->order_by ('articulo.referencia ASC');
		else
			$this->_db_premium->order_by ('articulo.codigo DESC');
		
		return $this->_db_premium->get ( $this->table, 'subgrupos' )->result ();
	}
	
	
	public function get_product($code) {
		$this->_contruct_select();
		$this->_db_premium->where('articulo.codigo', $code);
		return $this->_db_premium->get ( $this->table )->row ();
	}
	
	public function get_groups() { //ojooo no se q hace
		//$this->__construct();
		$this->_db_premium->select ('trim(grupos.codigo) as codigo_grupo, trim(grupos.nombre) as nombre_grupo, trim(grupos.rutafoto) as foto_grupo');
		//$this->_db_premium->like ('articulo.codigo', $topic);
		$this->_db_premium->select('count(DISTINCT articulo.codigo) as filas');
		//if(!$grupo)
		$this->_db_premium->group_by ('articulo.grupo');
		return $this->_db_premium->get ( $this->table )->result ();
	}
	
	public function get_subgroup_name($subcode) {
		//$this->__construct();
		$this->_db_premium->select ('DISTINCT(subgrupos.nombre)');
		//$this->_db_premium->like ('articulo.codigo', $topic);
		$this->_db_premium->where('subcodigo', $subcode);
		$query = $this->_db_premium->get ( 'subgrupos' );
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$name= $row->nombre;
		}
		return $name;
		
		//return $this->_db_premium->get ( 'subgrupos' )->result ();
	}
	
	public function get($id) {
		return $this->db->where ( 'id', $id )->get ( $this->table )->row ();
	}
	public function add($data) {
		$this->db->insert ( $this->table, $data );
		return $this->db->insert_id ();
	}
	public function update($codigo, $data, $data2) {
		//print_r($this->db->last_query());
		//if(!empty($data2))
			$this->_db_premium->where ( 'codigo', $codigo)->update ( 'existenc', $data2 );
		
		return $this->_db_premium->where ( 'articulo.codigo', $codigo)->update ( $this->table, $data );
	}
	public function delete($id) {
		$this->db->where ( 'id', $id )->delete ( $this->table );
		return $this->db->affected_rows ();
	}
}

/* End of file Project_model.php */
/* Location: ./application/models/Project_model.php */