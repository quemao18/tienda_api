<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| File and Directory Modes
|--------------------------------------------------------------------------
|
| These prefs are used when checking and setting modes when working
| with the file system.  The defaults are fine on servers with proper
| security, but you may wish (or even need) to change the values in
| certain environments (Apache running a separate process for each
| user, PHP under CGI with Apache suEXEC, etc.).  Octal values should
| always be used to set the mode correctly.
|
*/
/* variables usadas en el api */
$config['APP_NAME'] = 'RODASALIAS';
$config['APP_EMAIL'] = 'info@rodasalias.com';
$config['APP_EMAIL_PRESUPUESTO'] = 'atoba@rodasalias.com';



//$config['APP_EMAIL'] = 'alejandro.toba@gmail.com';
$config['APP_EMAIL_WEB_MASTER'] = 'alejandro.toba@gmail.com';
$config['APP_ALIAS_WEB_MASTER'] = 'atoba';

//smtp config
$config['APP_EMAIL_SMTP_PORT'] = '465';
$config['APP_EMAIL_POP'] = 'orinoco.tepuyserver.net';
$config['APP_EMAIL_SMTP_HOST'] = 'orinoco.tepuyserver.net';
//$config['APP_EMAIL_SMTP_HOST'] = 'ssl://smtp.googlemail.com';
//$config['APP_EMAIL_SMTP_USER'] = 'alejandro.toba@gmail.com';
//$config['APP_EMAIL_SMTP_PASS'] = 'at04550428at';
$config['APP_EMAIL_SMTP_USER'] = 'info@rodasalias.com';
$config['APP_EMAIL_SMTP_PASS'] = 'info@j317461070';

//end smtp config


$config['APP_SLOGAN'] = '';
$config['APP_WEB'] = 'www.rodasalias.com';
$config['APP_WEB_HTTP'] = 'http://www.rodasalias.com';
$config['APP_FACEBOOK'] = 'http://www.facebook.com/RodaSalias';
$config['APP_TWITTER'] = 'http://www.twitter.com/RodaSalias';
$config['APP_MRW'] = 'http://www.mrw.com.ve/';
$config['APP_TEALCA'] = 'http://www.tealca.com/';
$config['APP_ZOOM'] = 'http://www.grupozoom.com/';
$config['APP_DOMESA'] = 'http://www.domesa.com.ve/';

$config['APP_BANESCO'] = 'http://www.banesco.com/';
$config['APP_MERCANTIL'] = 'http://www.bancomercantil.com/';
$config['APP_VENEZUELA'] = 'http://www.bancodevenezuela.com/';
$config['APP_BFC'] = 'http://www.bfc.com.ve/';
$config['APP_BNC'] = 'http://www.bnc.com.ve/';
$config['APP_MP'] = 'http://www.mercadopago.com/';

$config['APP_TLF2'] = '+58-212-373.61.03';
$config['APP_TLF1'] = '+58-212-372.84.02';
$config['APP_DIRECCION'] = 'Carretera panamericana KM 16, sector La Guadalupe, Distribuidor la Rosaleda. Miranda, Venezuela.';
$config['APP_COORDENADAS'] = '10.368926, -66.987115';
$config['APP_SIMBOLO_MONEDA'] = 'Bs.';

$config['APP_META_DESCRIPTION'] = 'Cat&aacute;logo en l&iacute;nea, busca el producto que desees y si no lo consigues no dudes en contactarnos.';
$config['APP_META_KEYWORDS'] = 'Rolineras, estoperas, chumaceras, herramientas, cadenas, sellos';
$config['APP_META_AUTHOR'] = 'RodaSalias';
//$config['MENSAJE'] = 'Abierto de Lunes a Viernes. ';
$config['TIPO_MENSAJE'] = "alerta_msj"; //info_msj, alerta_msj, error_msj, exito_msj
//$config['APP_ICON_MAP'] = base_url('assets/images/logo.png');


/**
 * Variables para premium, de la base de datos.
 */
$config['CODIGO_ALMACEN'] = '01';
$config['AUMENTO'] = '0'; //aumento que se puede hacer temporal sobre todos los productos.

//$aumento='0'; 
$config['SELECT_ARTICULO'] = 
		"		trim(existenc.codigo) codigo, 
				trim(articulo.nombre) nombre, 
				trim(articulo.grupo) as codigo_grupo, 
				trim(articulo.subgrupo) subgrupo,
				(articulo.precio1*articulo.impuesto1/100)+articulo.precio1  precio1,
				(existenc.existencia), 
				articulo.impuesto1 iva,
				
				trim(existenc.ubicacion) ubicacion, 
				trim(articulo.referencia) referencia, 
				trim(articulo.marca) marca, 
				trim(articulo.detalles) detalles, 
				trim(articulo.modelo) medidas,
				trim(articulo.campo7) tipo, 
				(existenc.fechacrea),
				(articulo.fechamodifi),
				trim(existenc.documento) doc,
				articulo.costo,
				articulo.costo_ant,
				articulo.costo_prom,
				articulo.cntgrp,
				articulo.precio1grp,
				articulo.precio1grp,
				articulo.precio2grp,
				articulo.precio3grp,
				articulo.unidadgrp,
				articulo.util1,
				articulo.util2,
				articulo.util3,
        articulo.campo8 precio_dolar,
        articulo.campo9 dolar_today,
				trim(grupos.rutafoto) as foto_grupo,
				trim(grupos.nombre) as nombre_grupo,				
				trim(articulo.rutafoto) as foto_articulo,
				timestampdiff(month,articulo.fechamodifi,curdate() ) as meses_modificacion
						
		";


$config['SELECT_VENTAS_FACTURAS'] = " 
  DISTINCT(operti.TIPODOC),
  operti.DOCUMENTO,
  operti.CODCLIENTE,
  operti.NOMBRECLI,
  operti.EMISION,
  operti.VENCE,
  operti.TOTALFINAL, 
  operti.TOTPAGOS,
  operti.RETENCION,
  operti.ESTATUSDOC,
  operti.TOTNETO,
  operti.ESCREDITO,
  operti.RECARGOS,
  operti.TOTIMPUEST,
  operti.ULTIMOPAG,
  operti.ORDEN,
  operti.TOTCOSTO,
  operti.TOTCOMI,
  operti.TOTDESCUEN,
  operti.referencia,
  cliempre.tipo,
  tipocli.CODIGO AS codigotipo,
  tipocli.NOMBRE AS nombretipo,
  operti.AGENCIA,
  operti.ID_EMPRESA

		";
		
		
$config['SELECT_DEVOL_FACTURAS'] = " 
 devolti.TIPODOC,
  devolti.DOCUMENTO,
  devolti.CODCLIENTE,
  devolti.NOMBRECLI,
  devolti.EMISION,
  devolti.VENCE,
  devolti.TOTALFINAL,
  devolti.aplicadoa,
  devolti.TOTPAGOS,
  devolti.RETENCION,
  devolti.ESTATUSDOC,
  devolti.TOTNETO,
  devolti.RECARGOS,
  devolti.ESCREDITO,
  devolti.totdescuen,
  devolti.TOTIMPUEST,
  devolti.ULTIMOPAG,
  devolti.ORDEN,
  devolti.TOTCOSTO,
  devolti.TOTCOMI

		";

$config['SELECT_DEVOLUCION_FACTURAS']=" 
  devolti.TIPODOC,
  devolti.DOCUMENTO,
  devolti.CODCLIENTE,
  devolti.NOMBRECLI,
  devolti.EMISION,
  devolti.VENCE,
  devolti.TOTALFINAL,
  devolti.aplicadoa,
  devolti.TOTPAGOS,
  devolti.RETENCION,
  devolti.ESTATUSDOC,
  devolti.TOTNETO,
  devolti.RECARGOS,
  devolti.ESCREDITO,
  devolti.totdescuen,
  devolti.TOTIMPUEST,
  devolti.ULTIMOPAG,
  devolti.ORDEN,
  devolti.TOTCOSTO,
  devolti.TOTCOMI
FROM  
 cliempre
 LEFT JOIN tipocli ON (cliempre.TIPO=tipocli.CODIGO)
  AND (cliempre.ID_EMPRESA=tipocli.ID_EMPRESA)
  AND (cliempre.AGENCIA=tipocli.AGENCIA)
 LEFT JOIN devolti ON (devolti.CODCLIENTE=cliempre.CODIGO)
  AND (devolti.ID_EMPRESA=cliempre.ID_EMPRESA)
  AND (devolti.AGENCIA=cliempre.AGENCIA)

 WHERE devolti.TIPODOC='DEV' 
		";

$config['SELECT_VENTAS_WEB']=" 
    sum( venta_web_detalle.precio_venta*venta_web_detalle.cantidad ) total, 
    sum( venta_web_detalle.precio_venta_dolar*venta_web_detalle.cantidad ) total_dolar, 
		venta_web.usuario_web_id_usuario, usuario_web.nombre,
		venta_web.fecha,
    venta_web.tipo,
		venta_web.id_venta 
		
		";

$config['SELECT_VENTA_DETALLE_WEB']="
		id_venta_web_detalle, venta_web_detalle.codigo_articulo, venta_web_detalle.cantidad, precio_venta, precio_venta_dolar, venta_web.fecha
		
		";

$config['SELECT_DEVOLUCION_DETALLE_WEB']="
		id_devolucion_detalle, devolucion_web_detalle.codigo_articulo, devolucion_web_detalle.cantidad, venta_web_detalle.precio_venta, venta_web_detalle.precio_venta_dolar

		";

$config['SELECT_DEVOLUCIONES_WEB']="
		devolucion_web.id_devolucion,
    sum( venta_web_detalle.precio_venta*devolucion_web_detalle.cantidad) total,
    sum( venta_web_detalle.precio_venta_dolar*devolucion_web_detalle.cantidad) total_dolar,
		devolucion_web_detalle.codigo_articulo,
		(devolucion_web_detalle.cantidad ) cantidad,
		devolucion_web.host,
		devolucion_web.fecha,
		devolucion_web.id_venta

		";

$config['SELECT_DEVOLUCION_WEB']="
 
sum((devolucion_web_detalle.cantidad)*venta_web_detalle.precio_venta) total, 
sum((devolucion_web_detalle.cantidad)*venta_web_detalle.precio_venta_dolar) total_dolar, 
sum(devolucion_web_detalle.cantidad) cantidad, venta_web_detalle.precio_venta, 
id_devolucion, id_venta, fecha, devolucion_web_detalle.codigo_articulo 
		
		";
		
$config['GRUPOS_NO_VISIBLES'] = array('OFI', 'LOC');


$config['GOOGLE_ANALYTICS']="
<script>
if (window.location.hostname !== 'localhost') {
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-30748624-1', 'auto');
  ga('send', 'pageview');
}
</script>";


/*
 *
 * 				REPLACE(LOWER(grupos.rutafoto),"."'z:\\\premium\\\administrativo8x\\\\fotos\\\'" .",'../img/fotos/') ruta_foto_web,
				REPLACE(LOWER(articulo.rutafoto),"."'z:\\\premium\\\administrativo8x\\\\fotos\\\'" .",'../img/fotos/') ruta_foto_web_art,
				CASE WHEN articulo.precio1=0 THEN 'LLAMAR' ELSE articulo.precio1*articulo.impuesto1/100+articulo.precio1 + articulo.precio1*$aumento END precio1,
				CASE WHEN articulo.precio2=0 THEN 'LLAMAR' ELSE articulo.precio2*articulo.impuesto1/100+articulo.precio2 + articulo.precio1*$aumento END precio2,
				CASE WHEN articulo.precio3=0 THEN 'LLAMAR' ELSE articulo.precio3*articulo.impuesto1/100+articulo.precio3 + articulo.precio1*$aumento END precio3,

 * 
define('APP_EMAIL', 'info@rodasalias.com');
define('APP_EMAIL_POP', 'mail.rodasalias.com');
define('APP_SMTP_SECURE', 'ssl');
define('APP_SMTP_AUTH', 'true');
define('APP_EMAIL_PORT', '465');
define('APP_EMAIL_PASS', 'in@123456');
define('APP_SLOGAN', 'mi primer framework php y mvc...');
define('APP_WEB', 'www.rodasalias.com');

define('SESSION_TIME', 10);
define('HASH_KEY', '4f6a6d832be79');
*/
/* End of file variables.php */
/* Location: ./application/config/variables.php */