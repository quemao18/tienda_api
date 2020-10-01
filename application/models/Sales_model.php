<?php if (! defined ( 'BASEPATH' ))	exit ( 'No direct script access allowed' );

class Sales_model extends CI_Model {
	
	private $_db_premium = null;
	private $_db_web = null;
	private $_fecha = '';
	private $_fecha_hora = '';
	private $_hora = '';
	private $_host = '';
	private $_pid = '';
	private $_iva = '';
	private $_id_campo = '';
	
	public function __construct() {
		parent::__construct();
		$this->_db_premium=$this->load->database('premium', TRUE);
		$this->_db_web=$this->load->database('web', TRUE);
		$this->_fecha_hora = date('Y-m-d H:i:s');
		$this->_fecha = date('Y-m-d');
		$this->_iva = '16';
		$this->_hora=date('h:m A');
		$this->_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);			
		$this->_pid = pid();
		$this->_id_campo = idCampo();
		
		
	}
	
	private function _contruct_select(){
	
		
	}
	
	public function get_sales($date_ini, $date_end, $code) {
		
		$sales_total = array(
				'venta_facturas' => $this->getVentasFactura($date_ini, $date_end),
				'devol_facturas' => $this->getDevolFactura($date_ini, $date_end),
				'venta_web' => $this->getVentasWeb($date_ini, $date_end, $code),
				'devolucion_web' => $this->getDevolucionesWeb($date_ini, $date_end)
				
				
		);
		return $sales_total;
		
	}
	
	
	public function getDevolFactura($date_ini, $date_end){

		$total_devol =0;
		$total_devol2 = 0;
		
		$this->_db_premium->select($this->config->item('SELECT_DEVOL_FACTURAS'));
		
		//$this->_db_premium->join('cliempre', 'operti.CODCLIENTE=cliempre.CODIGO', 'left outer');
		//$this->_db_premium->join('operti', 'operti.CODCLIENTE=cliempre.CODIGO', 'left outer');
		/**
		 * LEFT JOIN devolti ON (devolti.CODCLIENTE=cliempre.CODIGO)
			  AND (devolti.ID_EMPRESA=cliempre.ID_EMPRESA)
			  AND (devolti.AGENCIA=cliempre.AGENCIA)
		 */
		$this->_db_premium->join('devolti', 'devolti.codcliente =cliempre.CODIGO and devolti.ID_EMPRESA=cliempre.ID_EMPRESA AND devolti.AGENCIA=cliempre.AGENCIA', 'left outer');
		$this->_db_premium->join('tipocli', 'cliempre.TIPO=tipocli.CODIGO AND (cliempre.ID_EMPRESA=tipocli.ID_EMPRESA) AND (cliempre.AGENCIA=tipocli.AGENCIA)', 'left outer');
		//$this->_db_premium->join('agencias', 'operti.AGENCIA=agencias.AGENCIA', 'left outer');
		
		$this->_db_premium->where_in('devolti.TIPODOC', 'DEV');
		
		$this->_db_premium->where('emision >=', $date_ini);
		$this->_db_premium->where('emision <=', $date_end);
		$this->_db_premium->group_by ('devolti.documento');
		$query = $this->_db_premium->get('cliempre');
		
		/**
		 * $sql=
		get_qry_devol_facturas()." 
		AND emision between '$fecha' and adddate('$fecha_venta_fin',0)
		GROUP by devolti.documento;" ;
		$result=mysql_query($sql);
		while($r=@mysql_fetch_array($result))
			{	
			if($r['ESCREDITO']!=1){
			$total_devol=$r['TOTALFINAL'];
			$total_devol2+=$total_devol;
				}
			}
		 * 
		 */
		
		foreach($query->result_array() as $row){
			//echo $row['someContent'];
			//if($row['ESCREDITO']==1){
				$total_devol=$row['TOTALFINAL'];
				$total_devol2+=$total_devol;
			//}
		}
		
		
		return $total_devol2;
		

	}
	
	public function getVentasFactura($date_ini, $date_end){

		$total_facturas =0;
		$total_facturas2 = 0;
		$total_facturas_credito = 0;
		$total_facturas_credito2 = 0;
		$sales= array();
		$this->_db_premium->select($this->config->item('SELECT_VENTAS_FACTURAS'));
		
		$this->_db_premium->join('cliempre', 'operti.CODCLIENTE=cliempre.CODIGO', 'left outer');
		$this->_db_premium->join('tipocli', 'cliempre.TIPO=tipocli.CODIGO', 'left outer');
		$this->_db_premium->join('agencias', 'operti.AGENCIA=agencias.AGENCIA', 'left outer');
		$this->_db_premium->where_in('operti.TIPODOC', 'FAC');
		
		$this->_db_premium->where('fechayhora >=', $date_ini); //cambie emision por fechayhora
		$this->_db_premium->where('fechayhora <=', $date_end);
		$this->_db_premium->group_by ('operti.documento');
		$query = $this->_db_premium->get('operti');
		
		foreach($query->result_array() as $row){
			//echo $row['someContent'];
			$total_facturas=$row['TOTALFINAL'];
			$total_facturas2+=$total_facturas;
			if($row['ESCREDITO']==1){
				$total_facturas_credito=$row['TOTALFINAL']-$row['TOTPAGOS'];
				$total_facturas_credito2+=$row['TOTALFINAL']-$row['TOTPAGOS'];
			}
		}
		
		$sales=array(
				'total_facturas' => $total_facturas2 - $total_facturas_credito2 - $this->getDevolFactura($date_ini, $date_end) ,
				'total_facturas_credito' => $total_facturas_credito2
				
		);
		return $sales;
		

	}
	
	public function getSaleDetail($id_venta){
	
		$sales= array();
		$venta = array();
		$totaltotal = 0;
		$totaltotal_devoluciones = 0;
		//$devolucion = $this->getDevolucionWeb($id_venta);
	/*	if($devolucion->cantidad!=''){
			$this->_db_web->select($this->config->item('SELECT_VENTA_DETALLE_WEB'));
			$this->_db_web->select('devolucion_web_detalle.cantidad as cantidad_devuelta');
			$this->_db_web->join('devolucion_web_detalle', 'devolucion_web_detalle.codigo_articulo=venta_web_detalle.codigo_articulo');
			$this->_db_web->join('devolucion_web', 'devolucion_web.id_venta=venta_web_detalle.id_venta_web_detalle');
				
			//$this->_db_web->where('devolucion_web.id_venta', $id_venta);
			$this->_db_web->where('venta_web_detalle.id_venta_web_detalle', $id_venta);
			$this->_db_web->group_by ('venta_web_detalle.codigo_articulo');
			$query = $this->_db_web->get('venta_web_detalle');			
			$venta = $query->result();
		}else{
		*/
		$this->_db_web->select($this->config->item('SELECT_VENTA_DETALLE_WEB'));
		$this->_db_web->select('articulo.nombre as nombre, articulo.referencia as referencia, articulo.detalles as detalles, articulo.modelo as medidas');
		$this->_db_web->join('venta_web', 'venta_web.id_venta=venta_web_detalle.id_venta_web_detalle');
		$this->_db_web->join($this->_db_premium->database.'.articulo', $this->_db_premium->database.'.articulo.codigo=venta_web_detalle.codigo_articulo');
		
		//$this->_db_web->join('devolucion_web', 'devolucion_web.id_devolucion=venta_web.id_venta', 'outer left');
		//$this->_db_web->join('usuario_web', 'venta_web.usuario_web_id_usuario=usuario_web.id_usuario');	
		//$this->_db_web->where('venta_web.fecha >=', $date_ini);
		$this->_db_web->where('venta_web_detalle.id_venta_web_detalle', $id_venta);
		$this->_db_web->group_by ('codigo_articulo');
		//$this->_db_web->order_by ('venta_web.fecha DESC');
		$query = $this->_db_web->get('venta_web_detalle');
		//$venta = $query->result();
		//}
		$dev=0;
		//$venta = array();
		foreach($query->result_array() as $v => $ventad){
			
			$devolucion=$this->getDevolucionWeb($id_venta, $ventad['codigo_articulo']);
			
			if($devolucion->cantidad >0)
				$dev = $devolucion->cantidad;
			else
				$dev=0;
			
			if(isset($ventad['referencia'])) $referencia= $ventad['referencia']; else $referencia= '';			
			if(isset($ventad['nombre'])) $nombre= $ventad['nombre']; else $nombre = '';
			if(isset($ventad['medidas'])) $medidas = $ventad['medidas']; else $medidas = '';
			if(isset($ventad['detalles'])) $detalles = $ventad['detalles']; else $detalles = '';
			
			$venta4[] = array(
					'cantidad' => $ventad['cantidad'],
					'codigo_articulo' => $ventad['codigo_articulo'],
					'id_venta_web_detalle' => $ventad['id_venta_web_detalle'],
					'precio_venta' => $ventad['precio_venta'],
					'precio_venta_dolar' => $ventad['precio_venta_dolar'],
					'referencia' =>$referencia,
					'nombre' => $nombre,
					'detalles' => $detalles,
					'medidas' => $medidas,
					'fecha' => $ventad['fecha'],
					'cantidad_devuelta' => $dev
			);
			
			
		
		}
		
		
		$sales=array(
					'venta' => $venta4
					//'devolucion' => $devolucion,
					//'cantidad_devuelta' => $devolucion->cantidad
	
		);
		return $sales;
	
	
	}
	
	public function getDevolDetail($id_devolucion){
	
		$sales= array();
		$venta = array();
		$totaltotal = 0;
		$totaltotal_devoluciones = 0;
		$this->_db_web->select($this->config->item('SELECT_DEVOLUCION_DETALLE_WEB'));
		$this->_db_web->select('trim(articulo.codigo) as codigo, trim(articulo.nombre) as nombre, trim(articulo.referencia) as referencia, trim(articulo.detalles) as detalles, trim(articulo.modelo) as medidas');
		 
		$this->_db_web->join('devolucion_web', 'devolucion_web.id_devolucion = devolucion_web_detalle.id_devolucion_detalle');
		$this->_db_web->join('venta_web_detalle', 'venta_web_detalle.id_venta_web_detalle = devolucion_web.id_venta and devolucion_web_detalle.codigo_articulo = venta_web_detalle.codigo_articulo');
		$this->_db_web->join($this->_db_premium->database.'.articulo', $this->_db_premium->database.'.articulo.codigo = devolucion_web_detalle.codigo_articulo');
		
	
		//$this->_db_web->where($this->_db_premium->database.'.articulo.codigo = devolucion_web_detalle.codigo_articulo');
		$this->_db_web->where('devolucion_web_detalle.id_devolucion_detalle', $id_devolucion);
		//$this->_db_web->where('devolucion_web_detalle.codigo_articulo = venta_web_detalle.codigo_articulo');
		//$this->_db_web->group_by ('venta_web.fecha');
		//$this->_db_web->order_by ('venta_web.fecha DESC');
	
		$query = $this->_db_web->get('devolucion_web_detalle');
		$devolucion = $query->result_array();
		//$devolucion = $query->row();
	
	
		
		return $devolucion;
	
	
	}
	
	public function getDevolDetailIdVenta($id_venta){
	
		$sales= array();
		$venta = array();
		$totaltotal = 0;
		$totaltotal_devoluciones = 0;
		$this->_db_web->select($this->config->item('SELECT_DEVOLUCION_DETALLE_WEB'));
	
		$this->_db_web->join('devolucion_web', 'devolucion_web.id_devolucion=devolucion_web_detalle.id_devolucion_detalle');
		$this->_db_web->join('venta_web_detalle', 'venta_web_detalle.id_venta_web_detalle =devolucion_web.id_venta');
	
		//$this->_db_web->where('venta_web.fecha >=', $date_ini);
		$this->_db_web->where('devolucion_web.id_venta', $id_venta);
		//$this->_db_web->group_by ('venta_web.fecha');
		//$this->_db_web->order_by ('venta_web.fecha DESC');
	
		$query = $this->_db_web->get('devolucion_web_detalle');
	
		$devolucion = $query->row();
	
	
		$sales=array(
				'devolucion' => $devolucion
	
		);
		return $devolucion;
	
	
	}
	
	
	public function getVentasWeb($date_ini, $date_end, $code){
		
		$sales= array();
		$venta = array();
		$totaltotal = 0;
		$totaltotal_devoluciones = 0;
		$this->_db_web->select($this->config->item('SELECT_VENTAS_WEB'));
		$this->_db_web->select($this->_db_premium->database.'.articulo.nombre', $this->_db_premium->database.'.articulo.referencia', $this->_db_premium->database.'.articulo.detalles', $this->_db_premium->database.'.articulo.modelo as medidas');
		$this->_db_web->join('venta_web', 'venta_web.id_venta=venta_web_detalle.id_venta_web_detalle');
		$this->_db_web->join('usuario_web', 'venta_web.usuario_web_id_usuario=usuario_web.id_usuario');
		$this->_db_web->join($this->_db_premium->database.'.articulo', $this->_db_premium->database.'.articulo.codigo=venta_web_detalle.codigo_articulo');
		
		if($code!='') {
		//$this->_db_web->like('venta_web_detalle.codigo_articulo', $code);
		//$this->_db_premium->select('nombre');
		$this->_db_web->like($this->_db_premium->database.'.articulo.nombre', $code);
		$this->_db_web->or_like($this->_db_premium->database.'.articulo.codigo', $code);
		$this->_db_web->or_like($this->_db_premium->database.'.articulo.detalles', $code);
		$this->_db_web->or_like($this->_db_premium->database.'.articulo.referencia', $code);
		$this->_db_web->or_like($this->_db_premium->database.'.articulo.modelo', $code);
		}
		
		if($date_ini!='' && $date_end!=''){
		$this->_db_web->where('venta_web.fecha >=', $date_ini);
		$this->_db_web->where('venta_web.fecha <=', $date_end);
		}
		$this->_db_web->group_by ('venta_web.id_venta');
		$this->_db_web->order_by ('venta_web.id_venta DESC');
		
		
		
		$query = $this->_db_web->get('venta_web_detalle');
	
		$venta = $query->result();
		
		foreach($query->result_array() as $row){
			//echo $row['someContent'];
			
			$totaltotal+=$row['total'];
			//$totaltotal_devoluciones += $this->getDevolucionWeb($row['id_venta']);
		}
	
		$sales=array(
				'total_ventas_web' => $totaltotal,
				//'total_devolucion_web' => $totaltotal_devoluciones,
				
				'venta' => $venta 
	
		);
		return $sales;
	
	
	}
	
	
	public function getDevolucionesWeb($date_ini, $date_end){
	
		$sales= array();
		$venta = array();
		$totaltotal = 0;
		$totaltotal_devoluciones = 0;
		$this->_db_web->select($this->config->item('SELECT_DEVOLUCIONES_WEB'));
	
		$this->_db_web->join('devolucion_web', 'devolucion_web.id_devolucion=devolucion_web_detalle.id_devolucion_detalle');
		$this->_db_web->join('venta_web', 'venta_web.id_venta=devolucion_web.id_venta');
		$this->_db_web->join('venta_web_detalle', 'venta_web.id_venta=venta_web_detalle.id_venta_web_detalle and devolucion_web_detalle.codigo_articulo = venta_web_detalle.codigo_articulo');
		//$this->_db_web->join('devolucion_web_detalle', 'devolucion_web_detalle.codigo_articulo = venta_web_detalle.codigo_articulo');
		
		
		$this->_db_web->where('devolucion_web.fecha >=', $date_ini);
		$this->_db_web->where('devolucion_web.fecha <=', $date_end);
		//$this->_db_web->where('devolucion_web_detalle.codigo_articulo = venta_web_detalle.codigo_articulo');
		
		$this->_db_web->group_by ('devolucion_web.id_devolucion');
		$this->_db_web->order_by ('devolucion_web.fecha DESC');
	
		$query = $this->_db_web->get('devolucion_web_detalle', 'venta_web_detalle');
	
		$devolucion = $query->result();
	
		foreach($query->result_array() as $row){
			//echo $row['someContent'];
				
			$totaltotal+=$row['total'];
			//$totaltotal_devoluciones += $this->getDevolucionWeb($row['id_devolucion']);
		}
	
		$sales=array(
				'total_devolucion_web' => $totaltotal,
				//'total_devolucion_web' => $totaltotal_devoluciones,
	
				'devolucion' => $devolucion
	
		);
		return $sales;
	
	
	}
	
	public function getDevolucionWeb($sale_id, $codigo_articulo){
	
		/*
		 * from devolucion_web 
INNER JOIN devolucion_web_detalle on (devolucion_web.id_devolucion = devolucion_web_detalle.id_devolucion_detalle)
INNER JOIN venta_web_detalle ON (devolucion_web.id_venta=venta_web_detalle.id_venta_web_detalle) AND (devolucion_web_detalle.codigo_articulo=venta_web_detalle.codigo_articulo) 
 (devolucion_web_detalle.codigo_articulo=venta_web_detalle.codigo_articulo)'
		 */
		//$sales= array();
		$totaltotal = 0;
		$this->_db_web->select($this->config->item('SELECT_DEVOLUCION_WEB'));
	
		$this->_db_web->join('devolucion_web_detalle', 'devolucion_web.id_devolucion = devolucion_web_detalle.id_devolucion_detalle');
		$this->_db_web->join('venta_web_detalle', 'devolucion_web.id_venta=venta_web_detalle.id_venta_web_detalle AND venta_web_detalle.codigo_articulo=devolucion_web_detalle.codigo_articulo');
		//$this->_db_web->join('devolucion_web_detalle', 'devolucion_web_detalle.codigo_articulo=venta_web_detalle.codigo_articulo');
		
		$this->_db_web->where('devolucion_web.id_venta =', $sale_id);
		$this->_db_web->where('devolucion_web_detalle.codigo_articulo =', $codigo_articulo);
		
		
		$query = $this->_db_web->get('devolucion_web');
			
		$row = $query->row();
		//$totaltotal=$row->total;
		
		return $row;
	
	
	}
	
	public function getUtilGrupo($grupo){
		$sql="SELECT * from grupos where codigo= '$grupo'";
		$query = $this->_db_premium->query($sql);
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$detalle = $row->prcutil;
		}
		if($detalle>0)
			return $detalle;
			else
				return 40;				
	}
	
	
	public function getDocCargoEnc(){
		$query = $this->_db_premium->query("SELECT cexisten from agencias WHERE id_empresa='001000'");
	
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$doc = $row->cexisten;
		}
		return $doc;
	}
	
	
	public function getPrecioArticulo($codigo){
		$query = $this->_db_premium->query("SELECT precio1 from articulo where codigo='$codigo' ");
	
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$codigo = $row->fecha;
		}
		return $codigo;
	}
	
	public function getExistenciaArticulo($codigo){
		$query = $this->_db_premium->query(" SELECT existencia from articulo where codigo= '$codigo' ");
	
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$existencia = $row->existencia;
		}
		return $existencia;
	}
	
}

/* End of file Project_model.php */
/* Location: ./application/models/Project_model.php */