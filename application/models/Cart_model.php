<?php if (! defined ( 'BASEPATH' ))	exit ( 'No direct script access allowed' );

class Cart_model extends CI_Model {
	
	private $_db_premium = null;
	private $_db_web = null;
	private $_fecha = '';
	private $_fecha_hora = '';
	private $_hora = '';
	private $_host = '';
	private $_pid = '';
	private $_iva = '';
	private $_iva2= '';
	private $_id_campo = '';
	public $table = 'articulo';
	
	public function __construct() {
		parent::__construct();
		$this->_db_premium=$this->load->database('premium', TRUE);
		$this->_db_web=$this->load->database('web', TRUE);
		$this->_fecha_hora = date('Y-m-d H:i:s');
		$this->_fecha = date('Y-m-d');
		$this->_iva = '16'; //cambiar a 10
		$this->_iva2 = '10';
		$this->_hora=date('h:m A');
		$this->_host = gethostbyaddr($_SERVER['REMOTE_ADDR']);			
		$this->_pid = pid();
		$this->_id_campo = idCampo();
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

		public function exist2($data) {
	
		$items = ( $data );
		//$item = $data[0]->item;
		//print_r($items); exit;
		//$this->cargoEnc();
		
		//print_r($items);
		foreach ($items as  $item) {
		//print_r($item->item->codigo);
		$codigo = $item->item->codigo;
		$cantidad = $item->qty;
		$precio = $item->price;
		//$item = $data;
		//$username = $data['username'];
		//if($username==null) $username = 'V1';
		
		$existencia_real = $this->getExistenciaArticulo($codigo);
		$link = "update existenc set existencia=('$cantidad') where codigo='$codigo'";
		$this->_db_premium->query($link);
		$link = "update articulo set existencia=('$cantidad') where codigo='$codigo'";
		$this->_db_premium->query($link);
		
		
		if($existencia_real < $cantidad)
		{
			$proceso='1'; //entrada
			$cantidad=($cantidad-$existencia_real);
		}else{
			$proceso='2'; //salidas
			$cantidad= ($existencia_real-$cantidad);
		}
		//return $cantidad.$proceso;
		$this->cargoDetYKardex2($item, $cantidad, $proceso);
		
		
		}
		
		return true;
	
	}
	
	public function exist($data) {
		//$data= array("data"=>$data);
		//print_r($data); exit;
		$items = $data['items'];
		
		$this->cargoEnc();
		
		
		foreach ($items as  $item) {
		$codigo = $item['id'];
		$cantidad = $item['quantity'];
		$precio = $item['price'];
		//$item = $data;
		//$username = $data['username'];
		//if($username==null) $username = 'V1';
		
		$existencia_real = $this->getExistenciaArticulo($codigo);
		$link = "update existenc set existencia=('$cantidad') where codigo='$codigo'";
		$this->_db_premium->query($link);
		$link = "update articulo set existencia=('$cantidad') where codigo='$codigo'";
		$this->_db_premium->query($link);
		
		
		if($existencia_real < $cantidad)
		{
			$proceso='1'; //entrada
			$cantidad=($cantidad-$existencia_real);
		}else{
			$proceso='2'; //salidas
			$cantidad= ($existencia_real-$cantidad);
		}
		//return $cantidad.$proceso;
		$this->cargoDetYKardex($item, $cantidad, $proceso);
		
		}
		
		return true;
		 
	
	
	}
	
	public function price2($data, $dolarToday = '0') {
	
		//$items = $data['items'];
		//$item = $data;
		//$username = $data['username'];
		//if($username==null) $username = 'V1';
		 
		//foreach ($items as  $item) {
			
			$codigo= $data->item->codigo;
			$codigo_grupo= $data->item->codigo_grupo;
			$precio= $data->price;
			
			$util=round($this->getUtilGrupo($codigo_grupo));
			
			$iva_art = round($this->getIvaArticulo($codigo));
			
			$precio_neto=round($precio/($iva_art/100+1),2);
			//echo $precio_neto;
			$costo=round($precio_neto*(100-$util)/100,2);
			
			$pneto=round($costo*100/(100-$util),2);
			$precio1= $pneto;
			$iva_ind=round($pneto*$iva_art/100,2);
			//$util = $util - 5;
			$pfinal=round($pneto+$iva_ind,2);
			$preciofinal = $pfinal;
			
		
			for($i = 1; $i <= 8; $i++){
				//rutina pa acualizar precio
				//echo $util."%-".$pneto."-".$iva_ind."-".$pfinal."<br>";
				//echo $link."<br>";
			
				$link = "update articulo set util$i=$util, precio$i=$pneto, preciofin$i=$pfinal, campo8=round($preciofinal/$dolarToday,2), campo9=$dolarToday, costo=$costo, costo_ant=$costo, costo_prom=$costo, fechamodifi=NOW() where codigo='$codigo'";
				$this->_db_premium->query($link);
				//$res=1;
				$util = $util - 5;
			
				$pneto=$costo*100/(100-$util);
				$iva_ind=$pneto*$iva_art/100;
				$pfinal=$pneto+$iva_ind;
			
			}
			
		//}
		return $precio1;
		
	}

	public function price($data, $dolarToday = '0') {
	
		//$items = $data['items'];
		$item = $data;
		//$username = $data['username'];
		//if($username==null) $username = 'V1';
		
		//foreach ($items as  $item) {
			
			$codigo= $item['_id'];
			
			$util=round($this->getUtilGrupo($item['_data']['codigo_grupo']));
			
			$iva_art = round($this->getIvaArticulo($codigo));
			
			$precio_neto=round($item['_price']/($iva_art/100+1),2);
			//echo $precio_neto;
			$costo=round($precio_neto*(100-$util)/100,2);
			
			$pneto=round($costo*100/(100-$util),2);
			$precio1= $pneto;
			$iva_ind=round($pneto*$iva_art/100,2);
			//$util = $util - 5;
			$pfinal=round($pneto+$iva_ind,2);
			$preciofinal = $pfinal;
			
		
			for($i = 1; $i <= 8; $i++){
				//rutina pa acualizar precio
				//echo $util."%-".$pneto."-".$iva_ind."-".$pfinal."<br>";
				//echo $link."<br>";
			
				$link = "update articulo set util$i=$util, precio$i=$pneto, preciofin$i=$pfinal, costo=$costo, costo_ant=$costo, costo_prom=$costo, fechamodifi=NOW(), campo8=round($preciofinal/$dolarToday,2), campo9=$dolarToday where codigo='$codigo'";
				$this->_db_premium->query($link);
				//$res=1;
				$util = $util - 5;
			
				$pneto=$costo*100/(100-$util);
				$iva_ind=$pneto*$iva_art/100;
				$pfinal=$pneto+$iva_ind;
			
			}
			
		//}
		return $precio1;
		
	}
	
	public function save($data, $codClient, $name, $sector) {
		$items = $data['items'];
		//$username = $data['username'];
		if($codClient==null || $codClient == '') $username = 'V1';
		if($name == null || $name == '') $name = $this->_host;
		
		$username= $codClient;
		
		$totcosto=0;
		$totalfinal = 0;
		$documento= ''.date('dHis'); //ejm diahoraminutosegundos
		$almacen='01';
		$id_empresa='001000';
		$agencia='001';
		$tipodoc='ESP';
		$origen=1;
		$vendedor='01';
		$emisor='ATOBA';
		$usaserial='2';
		$tipoprecio='1';
		$agrupado=2;
		$usaexist=1;
		$timpueprc='16'; //ojo hay q cambiarlo segun las seniat
		$proveedor=$username;
		$compuesto=2;
		$fecha=$this->_fecha;
		$pid = $this->_pid;
		$rif=$username;
		$codcliente=$rif;
		$codhijo='';
		$estacion='000';
		
		//$nombrecli='CONSUMIDOR FINAL';
		$nombrecli = $name;//$nombrecli=$this->_host;
		$fechacrea=$fecha;
		$fechayhora=$this->_fecha_hora;
		
		$emision=$fecha;
		$recepcion=$emision;
		$vence=$recepcion;
		$sector= $sector;//$sector='02';
		$hora='';
		$formafis='1';
		$al_libro='1';
		$dbcr='2';
		$ampm='0';
		
		
		foreach ($items as  $item) {
			
			$codigo = $item['id'];
			$cantidad = $item['quantity'];
			$precio = $item['price'];
			
			$nombre=$item['name'];
			$grupo=$item['data']['codigo_grupo'];
			$subgrupo=$item['data']['subgrupo'];
			
			$costounit=$item['data']['costo'];
			$iva_articulo = $item['data']['iva'];
			
			$preciounit=getCosto($precio, $iva_articulo);
			//return $preciounit;
			//echo $preciounit;
			$preciofin=$preciounit;
			$preciooriginal=$preciofin;
			$montoneto=$preciooriginal*$cantidad;
			$montototal=$montoneto;
			$aux1=$cantidad;			
			$baseimpo1=$montototal;
			
			
			$sql =preg_replace("/\r\n+|\r+|\n+|\t+/i", "", "INSERT INTO opermv (
					id_empresa, 
					agencia, 
					tipodoc, 
					documento, 
					grupo, 
					subgrupo, 
					origen, 
					codigo,	
					codhijo, 
					pid, 
					nombre, 
					costounit, 
					preciounit, 
					preciofin, 
					preciooriginal, 
					cantidad, 
					montoneto, 
					montototal, 
					almacen, 
					proveedor, 
					fechadoc, 
					timpueprc, 
					vendedor, 
					emisor, 
					usaserial, 
					tipoprecio, 
					agrupado, 
					compuesto, 
					usaexist, 
					aux1, 
					estacion, 
					baseimpo1, 
					fechayhora) 
					VALUES (
					'$id_empresa',
					'$agencia',
					'$tipodoc', 
					'$documento', 
					'$grupo', 
					'$subgrupo', 
					'$origen', 
					'$codigo', 
					'$codhijo', 
					'$pid', 
					'$nombre', 
					'$costounit', 
					'$preciounit', 
					'$preciofin', 
					'$preciooriginal', 
					'$cantidad', 
					'$montoneto',
					'$montototal', 
					'$almacen', 
					'$proveedor', 
					'$fecha', 
					'$timpueprc', 
					'$vendedor', 
					'$emisor', 
					'$usaserial', 
					'$tipoprecio', 
					'$agrupado', 
					'$compuesto', 
					'$usaexist', 
					'$aux1',
					'$estacion',
					'$baseimpo1',
					'$fechayhora'
					)"
					);
			$res1= $this->_db_premium->query($sql);
			$totcosto += $costounit*$cantidad;
			$totalfinal += $montototal;
			//return $res1;			
		}
		
		$totbruto=$totalfinal;
		$totneto=$totbruto;
		$impuesto1=round($totbruto*($this->_iva)/100,2);
		$impuesto2=$impuesto1;
		$totimpuest=$impuesto1;
		
		$sql= preg_replace("/\r\n+|\r+|\n+|\t+/i", "", "INSERT INTO operti (
		id_empresa, 
		agencia, 
		tipodoc, 
		documento, 
		codcliente, 
		nombrecli, 
		rif, 
		tipoprecio, 
		emision, 
		recepcion, 
		vence, 
		fechacrea, 
		totcosto, 
		totbruto, 
		totneto, 
		totalfinal, 
		totimpuest, 
		impuesto1, 
		impuesto2, 
		vendedor, 
		uemisor, 
		estacion, 
		almacen, 
		sector, 
		formafis, 
		al_libro, 
		dbcr, 
		horadocum, 
		ampm, 
		baseimpo1, 
		fechayhora) VALUES (
		'$id_empresa', 
		'$agencia',
		'$tipodoc',
		'$documento', 
		'$codcliente',
		'$nombrecli',
		'$rif',
		'$tipoprecio',
		'$emision',
		'$recepcion',
		'$vence',
		'$fechacrea',
		'$totcosto',
		'$totbruto',
		'$totneto',
		'$totalfinal',
		'$totimpuest',
		'$impuesto1',
		'$impuesto2',
		'$vendedor',
		'$emisor',
		'$estacion',
		'$almacen',
		'$sector',
		'$formafis',
		'$al_libro',
		'$dbcr',
		'$hora',
		'$ampm',
		'$baseimpo1',
		'$fechayhora'
		)"
		);
			
		$res2= $this->_db_premium->query($sql);
		//$res1=false;
		if($res1 && $res2)
			return '';
		else 
			return 'Error guardando...';
	}

	//save2
	public function save2($data, $codClient, $name, $sector) {
		$items = $data;
		//print_r($items); return;
		//$username = $data['username'];
		if($codClient==null || $codClient == '') $username = 'V1';
		if($name == null || $name == '') $name = $this->_host;
		
		$username= $codClient;
		
		$totcosto=0;
		$totalfinal = 0;
		$documento= ''.date('dHis'); //ejm diahoraminutosegundos
		$almacen='01';
		$id_empresa='001000';
		$agencia='001';
		$tipodoc='ESP';
		$origen=1;
		$vendedor='01';
		$emisor='ATOBA';
		$usaserial='2';
		$tipoprecio='1';
		$agrupado=2;
		$usaexist=1;
		$timpueprc='16'; //ojo hay q cambiarlo segun las seniat
		$proveedor=$username;
		$compuesto=2;
		$fecha=$this->_fecha;
		$pid = $this->_pid;
		$rif=$username;
		$codcliente=$rif;
		$codhijo='';
		$estacion='000';
		
		//$nombrecli='CONSUMIDOR FINAL';
		$nombrecli = $name;//$nombrecli=$this->_host;
		$fechacrea=$fecha;
		$fechayhora=$this->_fecha_hora;
		
		$emision=$fecha;
		$recepcion=$emision;
		$vence=$recepcion;
		$sector= $sector;//$sector='02';
		$hora='';
		$formafis='1';
		$al_libro='1';
		$dbcr='2';
		$ampm='0';
		
		
		foreach ($items as  $item) {

			$codigo = $item->item->codigo;
			$cantidad = $item->qty;
			$precio = $item->price;
			
			$nombre=$item->item->nombre;
			$grupo=$item->item->codigo_grupo;
			$subgrupo=$item->item->subgrupo;
			
			$costounit=$item->item->costo;
			$iva_articulo = $item->item->iva;
			
			//$preciounit=getCosto($precio, $iva_articulo);
			$preciounit=getCosto($precio, $this->_iva);
			//return $preciounit;
			//echo $preciounit;
			$preciofin=$preciounit;
			$preciooriginal=$preciofin;
			$montoneto=$preciooriginal*$cantidad;
			$montototal=$montoneto;
			$aux1=$cantidad;			
			$baseimpo1=$montototal;
			
			
			$sql =preg_replace("/\r\n+|\r+|\n+|\t+/i", "", "INSERT INTO opermv (
					id_empresa, 
					agencia, 
					tipodoc, 
					documento, 
					grupo, 
					subgrupo, 
					origen, 
					codigo,	
					codhijo, 
					pid, 
					nombre, 
					costounit, 
					preciounit, 
					preciofin, 
					preciooriginal, 
					cantidad, 
					montoneto, 
					montototal, 
					almacen, 
					proveedor, 
					fechadoc, 
					timpueprc, 
					vendedor, 
					emisor, 
					usaserial, 
					tipoprecio, 
					agrupado, 
					compuesto, 
					usaexist, 
					aux1, 
					estacion, 
					baseimpo1, 
					fechayhora) 
					VALUES (
					'$id_empresa',
					'$agencia',
					'$tipodoc', 
					'$documento', 
					'$grupo', 
					'$subgrupo', 
					'$origen', 
					'$codigo', 
					'$codhijo', 
					'$pid', 
					'$nombre', 
					'$costounit', 
					'$preciounit', 
					'$preciofin', 
					'$preciooriginal', 
					'$cantidad', 
					'$montoneto',
					'$montototal', 
					'$almacen', 
					'$proveedor', 
					'$fecha', 
					'$timpueprc', 
					'$vendedor', 
					'$emisor', 
					'$usaserial', 
					'$tipoprecio', 
					'$agrupado', 
					'$compuesto', 
					'$usaexist', 
					'$aux1',
					'$estacion',
					'$baseimpo1',
					'$fechayhora'
					)"
					);
			$res1= $this->_db_premium->query($sql);
			$totcosto += $costounit*$cantidad;
			$totalfinal += $montototal;
			//return $res1;			
		}
		
		$totbruto=$totalfinal;
		$totneto=$totbruto;
		$impuesto1=round($totbruto*($this->_iva)/100,2);
		$impuesto2=$impuesto1;
		$totimpuest=$impuesto1;
		
		$sql= preg_replace("/\r\n+|\r+|\n+|\t+/i", "", "INSERT INTO operti (
		id_empresa, 
		agencia, 
		tipodoc, 
		documento, 
		codcliente, 
		nombrecli, 
		rif, 
		tipoprecio, 
		emision, 
		recepcion, 
		vence, 
		fechacrea, 
		totcosto, 
		totbruto, 
		totneto, 
		totalfinal, 
		totimpuest, 
		impuesto1, 
		impuesto2, 
		vendedor, 
		uemisor, 
		estacion, 
		almacen, 
		sector, 
		formafis, 
		al_libro, 
		dbcr, 
		horadocum, 
		ampm, 
		baseimpo1, 
		fechayhora) VALUES (
		'$id_empresa', 
		'$agencia',
		'$tipodoc',
		'$documento', 
		'$codcliente',
		'$nombrecli',
		'$rif',
		'$tipoprecio',
		'$emision',
		'$recepcion',
		'$vence',
		'$fechacrea',
		'$totcosto',
		'$totbruto',
		'$totneto',
		'$totalfinal',
		'$totimpuest',
		'$impuesto1',
		'$impuesto2',
		'$vendedor',
		'$emisor',
		'$estacion',
		'$almacen',
		'$sector',
		'$formafis',
		'$al_libro',
		'$dbcr',
		'$hora',
		'$ampm',
		'$baseimpo1',
		'$fechayhora'
		)"
		);
			
		$res2= $this->_db_premium->query($sql);
		//$res1=false;
		if($res1 && $res2)
			return '';
		else 
			return 'Error guardando...';
	}
	
	public function sale($data) {
		
		$items = $data['items'];
		$username = $data['username'];
		if($username==null) $username = 'V1';
		
		$data2= array(
					'usuario_web_id_usuario'=> $username,
					'fecha' => $this->_fecha_hora,
					'host' => $this->_host
					
		);
		$this->_db_web->insert ( 'venta_web', $data2 );
		$this->cargoEnc();		
		$this->saleDetails ( $this->_db_web->insert_id (), $items );
		return true;
	}

	public function sale_iva($data) {
		
		$items = $data['items'];
		$username = $data['username'];
		$ivaRate = $data['ivaRate'];
		if($username==null) $username = 'V1';
		
		$data2= array(
					'usuario_web_id_usuario'=> $username,
					'fecha' => $this->_fecha_hora,
					'host' => $this->_host,
					'tipo' => 'debito/credito'
					
		);
		$this->_db_web->insert ( 'venta_web', $data2 );
		$this->cargoEnc();		
		$this->saleDetailsIva ( $this->_db_web->insert_id (), $items, $ivaRate );
		return true;
	}

	public function sale2($data) {
		
		//print_r($data); exit;
		//$items = $data['items'];
		$username = $data[0]->username;
		if($username==null) $username = 'V1';
		
		$data2= array(
					'usuario_web_id_usuario'=> $username,
					'fecha' => $this->_fecha_hora,
					'host' => $this->_host
					
		);
		$this->_db_web->insert ( 'venta_web', $data2 );
		$this->cargoEnc();		
		$this->saleDetails2 ( $this->_db_web->insert_id (), $data );
		return true;
	}

	public function devol($id_venta, $codigo_articulo, $precio_venta, $cant_devol, $key) {
	
		/*$item = $data;
		$id_venta = $data['item']['id_venta'];
		$username = $data['username'];
		if($username==null) $username = 'V1';
	*/
		$data2= array(
				'id_venta'=> $id_venta,
				'fecha' => $this->_fecha_hora,
				'host' => $this->_host
					
		);
		
		$item= array(
				'quantity'=> $cant_devol,
				'id' => $codigo_articulo,
				'price' => $precio_venta, 
				'name' => $this->getNombreArticulo($codigo_articulo), 
				'data' => array(
						'codigo_grupo'=> $this->getGrupoArticulo($codigo_articulo),
						'subgrupo' => $this->getSubGrupoArticulo($codigo_articulo)
				)
					
		);
		//if($key == 0)
		//$this->_db_web->insert ( 'devolucion_web', $data2 );
		//$this->cargoEnc();
		$this->devolDetails ( $this->getIdDevolucionWeb(), $item );
		return true;
	}
	
	
	public function devol2($id_venta) {
	
		/*$item = $data;
			$id_venta = $data['item']['id_venta'];
			$username = $data['username'];
			if($username==null) $username = 'V1';
			*/
		
		$data2= array(
				'id_venta'=> $id_venta,
				'fecha' => $this->_fecha_hora,
				'host' => $this->_host
					
		);
		$this->_db_web->insert ( 'devolucion_web', $data2 );
		$this->cargoEnc();
		
		//$list = json_decode($list);
	return true;
	}
	
	public function cargoEnc() {
		//insert en BDD premiun tabla cargoenc
		$fecha_max = $this->getFechaCargoEnc();
		
		if($fecha_max != $this->_fecha){
			
		$this->_db_premium->query ("
				INSERT INTO
				cargoenc (
				id_empresa,
				agencia,
				documento,
				realizador,
				emisor,
				motivo,
				fecha,
				estacion,
				tipoentradasalida
				)
				VALUES (
				'001000',
				'001',
				(SELECT LPAD((SELECT (adminmatriz.agencias.cexisten +1) from adminmatriz.agencias where adminmatriz.agencias.id_empresa='001000'), 8, '0')),
				'$this->_host',
				'ATOBA',
				'AJUSTES WEB',
				'$this->_fecha',
				'000',
				'06' 
				)
				");
		
		$this->_db_premium->query ("update admin001000.agencias 
						set admin001000.agencias.cexisten= (SELECT max(admin001000.cargoenc.documento) FROM admin001000.cargoenc) 
						WHERE admin001000.agencias.id_empresa='001000'"				
				);
		$this->_db_premium->query("update adminmatriz.agencias
						set adminmatriz.agencias.cexisten= (SELECT max(admin001000.cargoenc.documento) FROM admin001000.cargoenc)
						WHERE adminmatriz.agencias.id_empresa='001000'");
						
		//return "fal";
		}
		//return "tr";
		
	}

	public function saleDetails2($id_venta_web, $items) {

		foreach ($items as  $item) {
			
			$codigo = $item->item->codigo;
			$cantidad = $item->qty;
			$precio = $item->price;
			
			$this->_db_web->query("
					INSERT INTO venta_web_detalle (
					id_venta_web_detalle, 
					codigo_articulo, 
					cantidad, 
					precio_venta) 
					VALUES (
					'$id_venta_web', 
					'$codigo', 
					'$cantidad', 
					'$precio' 
				    )							
			");
		$this->cargoDetYKardex2($item);	
					
		$link = "update existenc set existencia=(existencia-'$cantidad') where codigo='$codigo'";
		$this->_db_premium->query($link);
		$link = "update articulo set existencia=(existencia-'$cantidad') where codigo='$codigo'";
		$this->_db_premium->query($link);
		
		}

	}
	
	public function saleDetails($id_venta_web, $items) {

		foreach ($items as  $item) {
			
			$codigo = $item['id'];
			$cantidad = $item['quantity'];
			$precio = $item['price'];
			
			$this->_db_web->query("
					INSERT INTO venta_web_detalle (
					id_venta_web_detalle, 
					codigo_articulo, 
					cantidad, 
					precio_venta) 
					VALUES (
					'$id_venta_web', 
					'$codigo', 
					'$cantidad', 
					'$precio' 
				    )							
			");
		$this->cargoDetYKardex($item);	
			
		$link = "update existenc set existencia=(existencia-'$cantidad') where codigo='$codigo'";
		$this->_db_premium->query($link);
		$link = "update articulo set existencia=(existencia-'$cantidad') where codigo='$codigo'";
		$this->_db_premium->query($link);

		}
	}

	public function saleDetailsIva($id_venta_web, $items, $ivaRate) {

		foreach ($items as  $item) {
			
			$codigo = $item['id'];
			$cantidad = $item['quantity'];
			$precio = $item['price'] / (1 + $this->_iva/100);
			$precio = $precio + ($precio * $ivaRate/100);
			
			$this->_db_web->query("
					INSERT INTO venta_web_detalle (
					id_venta_web_detalle, 
					codigo_articulo, 
					cantidad, 
					precio_venta) 
					VALUES (
					'$id_venta_web', 
					'$codigo', 
					'$cantidad', 
					'$precio' 
				    )							
			");
		$this->cargoDetYKardex($item);	
						
		$link = "update existenc set existencia=(existencia-'$cantidad') where codigo='$codigo'";
		$this->_db_premium->query($link);
		$link = "update articulo set existencia=(existencia-'$cantidad') where codigo='$codigo'";
		$this->_db_premium->query($link);

		}
	}
	
	public function devolDetails($id_devolucion_web, $item) {
	
		//foreach ($items as  $item) {
				
			$codigo = $item['id'];
			$cantidad = $item['quantity'];
				
			$this->_db_web->query("
					INSERT INTO devolucion_web_detalle (
					id_devolucion_detalle,
					codigo_articulo,
					cantidad
					)
					VALUES (
					'$id_devolucion_web',
					'$codigo',
					'$cantidad'
					
					)
					");
			$this->cargoDetYKardex($item, null, 1);
		//}
	
		$link = "update existenc set existencia=(existencia+'$cantidad') where codigo='$codigo'";
		$this->_db_premium->query($link);
		$link = "update articulo set existencia=(existencia+'$cantidad') where codigo='$codigo'";
		$this->_db_premium->query($link);
	}
	

	public function cargoDetYKardex2($item, $cantidad=null, $proceso='2') {
					
		//foreach ($items as  $item) {
				
			$codigo = $item->item->codigo;
			if($cantidad==null) $cantidad = $item->qty;
			$precio = $item->price;
				
			$documento=$this->getDocCargoEnc();
			$nombre=$item->item->nombre;
			$grupo=$item->item->codigo_grupo;
			$subgrupo=$item->item->subgrupo;
			$fecha=$this->_fecha;
			$hora=$this->_hora;
			$pid=$this->_pid;
			$id_campo=$this->_id_campo;
			 
			$sql_cargodet = "
			INSERT INTO cargodet (
			id_empresa,
			agencia,
			documento,
			codigo,
			idcampo,
			nombre,
			grupo,
			subgrupo,
			proceso,
			cantidad,
			fecha,
			emisor,
			estacion,
			usaserial,
			_seriales,
			precio,
			_motivoanul,
			costo,
			costopromfecha,
			factor,
			seriales,
			motivoanul
			)
			VALUES (
			'001000',
			'001',
			'$documento',
			'$codigo',
			'$id_campo',
			'$nombre',
			'$grupo',
			'$subgrupo',
			'$proceso',
			'$cantidad',
			'$fecha',
			'ATOBA',
			'000',
			'2',
			' ',
			'$precio',
			' ',
			'$precio',
			'$precio',
			'0',
			' ',
			' '
			)
			";							
			//return $id_campo;
			
			$sql_kardex="
			INSERT INTO kardex
			(
			id_empresa,
			agencia,
			documento,
			codigo,
			hora,
			grupo,
			origen,
			concepto,
			cantidad,
			estacion,
			almacen,
			sumaresta,
			emisor,
			fecha,
			aux1,
			aux2,
			aux3,
			idvalidacion,
			pid,
			costo,
			costoprom
			)
			VALUES (
			'001000',
			'001',
			'$documento',
			'$codigo',
			'$hora',
			'$grupo',
			'C&D',
			'Cargo y descargo de existencia documento # $documento',
			'$cantidad',
			'000',
			'01',
			$proceso,
			'ATOBA',
			'$fecha',
			'0',
			'0',
			'0',
			' ',
			'$pid',
			'$precio',
			'$precio'
			)
			";
				
			
			 $this->_db_premium->query($sql_cargodet);
			 $this->_db_premium->query($sql_kardex);
				
		//}
	
	}
	
	public function cargoDetYKardex($item, $cantidad=null, $proceso='2') {
					
		//foreach ($items as  $item) {
				
			$codigo = $item['id'];
			if($cantidad==null) $cantidad = $item['quantity'];
			$precio = $item['price'];
				
			$documento=$this->getDocCargoEnc();
			$nombre=$item['name'];
			$grupo=$item['data']['codigo_grupo'];
			$subgrupo=$item['data']['subgrupo'];;
			$fecha=$this->_fecha;
			$hora=$this->_hora;
			$pid=$this->_pid;
			$id_campo=$this->_id_campo;
			 
			$sql_cargodet = "
			INSERT INTO cargodet (
			id_empresa,
			agencia,
			documento,
			codigo,
			idcampo,
			nombre,
			grupo,
			subgrupo,
			proceso,
			cantidad,
			fecha,
			emisor,
			estacion,
			usaserial,
			_seriales,
			precio,
			_motivoanul,
			costo,
			costopromfecha,
			factor,
			seriales,
			motivoanul
			)
			VALUES (
			'001000',
			'001',
			'$documento',
			'$codigo',
			'$id_campo',
			'$nombre',
			'$grupo',
			'$subgrupo',
			'$proceso',
			'$cantidad',
			'$fecha',
			'ATOBA',
			'000',
			'2',
			' ',
			'$precio',
			' ',
			'$precio',
			'$precio',
			'0',
			' ',
			' '
			)
			";							
			//return $id_campo;
			
			$sql_kardex="
			INSERT INTO kardex
			(
			id_empresa,
			agencia,
			documento,
			codigo,
			hora,
			grupo,
			origen,
			concepto,
			cantidad,
			estacion,
			almacen,
			sumaresta,
			emisor,
			fecha,
			aux1,
			aux2,
			aux3,
			idvalidacion,
			pid,
			costo,
			costoprom
			)
			VALUES (
			'001000',
			'001',
			'$documento',
			'$codigo',
			'$hora',
			'$grupo',
			'C&D',
			'Cargo y descargo de existencia documento # $documento',
			'$cantidad',
			'000',
			'01',
			$proceso,
			'ATOBA',
			'$fecha',
			'0',
			'0',
			'0',
			' ',
			'$pid',
			'$precio',
			'$precio'
			)
			";
				
			
			 $this->_db_premium->query($sql_cargodet);
			 $this->_db_premium->query($sql_kardex);
				
		//}
	
	}
	
	function getIvaArticulo($codigo){
		$sql="SELECT impuesto1 from articulo WHERE codigo ='".$codigo."';";
		$query = $this->_db_premium->query($sql);
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$detalle = $row->impuesto1;
		}
		
		return $detalle;

	}
	
	function getUtilGrupo($grupo){
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
	
	public function getFechaCargoEnc(){
		$query = $this->_db_premium->query("SELECT max(fecha) as fecha from cargoenc");
	
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$fecha_max = $row->fecha;
		}
		return $fecha_max;
	}
	
	public function getIdDevolucionWeb(){
		$query = $this->_db_web->query("SELECT max(id_devolucion) as id from devolucion_web");
	
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$id = $row->id;
		}
		return $id;
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
	
	public function getNombreArticulo($codigo){
		$query = $this->_db_premium->query("SELECT nombre from articulo where codigo='$codigo' ");
	
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$nombre = $row->nombre;
		}
		return $nombre;
	}
	
	public function getGrupoArticulo($codigo){
		$query = $this->_db_premium->query("SELECT grupo from articulo where codigo='$codigo' ");
	
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$nombre = $row->grupo;
		}
		return $nombre;
	}
	public function getSubGrupoArticulo($codigo){
		$query = $this->_db_premium->query("SELECT subgrupo from articulo where codigo='$codigo' ");
	
		if ($query->num_rows() > 0)
		{
			$row = $query->row();
			$nombre = $row->subgrupo;
		}
		return $nombre;
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
	
	
	public function get($id) {
		return $this->db->where ( 'id', $id )->get ( $this->table )->row ();
	}
	public function add($data) {
		$this->db->insert ( $this->table, $data );
		return $this->db->insert_id ();
	}
	public function update($id, $data) {
		//print_r($this->db->last_query());
		return $this->db->where ( 'id', $id )->update ( $this->table, $data );
	}
	public function delete($id) {
		$this->db->where ( 'id', $id )->delete ( $this->table );
		return $this->db->affected_rows ();
	}
}

/* End of file Project_model.php */
/* Location: ./application/models/Project_model.php */