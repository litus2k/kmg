<?php

/*  TODO: gestión destinos avanzada

			/   'carpeta/' 
			_   'prefijo_', '_sufijo'
			-   'prefijo-', '-sufijo'
				'nombre.ext'  => la ext define formato
				'nombre'
							
	TODO: acoplar algún plugin en cliente para definir recorte imagen	(jquery_upload_cropv1.2)		
*/

define('ERROR_NOT_A_UPLOADED_FILE'		, 'ERROR_NO_UPLOADED_FILE');
define('ERROR_MOVE_UPLOADED_FILE'		, 'ERROR_MOVE_UPLOADED_FILE');

define('ERROR_NO_IMAGE_TYPE_UPLOADED'	, 'ERROR_NO_IMAGE_TYPE_UPLOADED');
define('ERROR_NO_IMAGE_MIME_FILE'		, 'ERROR_NO_IMAGE_MIME_FILE');

define('ERROR_FORMATO_PROHIBIDO'		, 'ERROR_FORMATO_PROHIBIDO');
define('ERROR_FORMATO_IMAGEN_NO_VALIDO'	, 'ERROR_FORMATO_IMAGEN_NO_VALIDO');

define('ERROR_GUARDAR_IMAGEN_NO_VALIDO'	, 'ERROR_GUARDAR_IMAGEN_NO_VALIDO');
define('ERROR_GUARDAR_IMAGEN_NO_EXISTE'	, 'ERROR_GUARDAR_IMAGEN_NO_EXISTE');
define('ERROR_MODO_GUARDAR_IMAGEN'		, 'ERROR_MODO_GUARDAR_IMAGEN');


define('ERROR_NO_EXISTE_DATO_IMAGEN'	, 'ERROR_NO_EXISTE_DATO_IMAGEN');

//

define('FORMATOS_PROHIBIDOS'	, 'php,php3,php4,phtml,exe');
define('FORMATOS_IMAGENES'		, 'jpg,png,gif,jpeg');

//

$raiz = realpath('./') . '/';	//carpeta script o podria ser getcwd()
define('RUTA_RAIZ', $raiz);		//de momento se puede usar para obtener rutas relativas (subprimiendo la raíz de la ruta absoluta)

define('CARPETA_UPLOADS' 	, RUTA_RAIZ . 'ups/');

define('CARPETA_IMAGENES'	, CARPETA_UPLOADS . 'img/');
define('SUBCARPETA_MINIS'	, '_min/');

//

if (hay_archivo_subido('archivo')) 
	guardar_archivo_subido('archivo');

if (hay_archivo_subido('imagen')) 
{

	$opciones_imagen = array
	(
		//'destino'			=> CARPETA_IMAGENES,
		'carpeta'			=> CARPETA_IMAGENES, //!! la ruta tiene que ser relativa (a WWW?)!! (e internamente obtener la absoluta...)
		'dimensiones' 	=> '320x',
		'calidad' 			=> 70 //%
	);

	$opciones_imagen_mini = array
	(
		'carpeta'		=> CARPETA_IMAGENES . SUBCARPETA_MINIS,
		'dimensiones' => '32x32',
		'formato' 		=> 'png',
		'calidad' 		=> 40
	);

	$opciones_imagen['mini'] = $opciones_imagen_mini;
	
	$ruta_imagen = guardar_imagen_subida('imagen', $opciones_imagen);
}	

//

function hay_archivo_subido($nombre_upload)
{
	//return isset($_FILES[$nombre_upload]) && !empty(....['tmp_name']) && is_uploaded_file(.....['tmp_name']);
	return ($as = @$_FILES[$nombre_upload]) && ($tmp_name = @$as['tmp_name']) && is_uploaded_file($tmp_name);
}

//

//function guardar_imagen_subida($nombre_upload, $opciones = array(), $opciones_mini = null)
function guardar_imagen_subida($nombre_upload, $opciones = array())
{
	$as = $_FILES[$nombre_upload];

	comprobar_upload_imagen($as);
	
	// procesar imagen subida
	
	$ruta_origen = $as['tmp_name'];
	
	if (! empty($opciones['dimensiones']))
		$imagen = redimensionar_imagen($ruta_origen, $opciones['dimensiones']);

	if (! isset($imagen) )
		$imagen = leer_imagen($ruta_origen);
		
	// guardar imagen procesada

	$ruta_destino = ruta_destino($opciones, $as);

	//

	guardar_imagen($imagen, $ruta_destino, @$opciones['calidad']);

	//!! reutilizar estructura datos para reutilizar el proceso ... 
	// ( y para facilitar el guardar varios formatos, diferentes tamaños... )
	if (isset($opciones['mini'])) $destino_mini = guardar_imagen_subida($nombre_upload, $opciones['mini']);
			//!! o guardar_imagen($ruta, $opciones, ...)  ¿¿?? procesar_y_guardar_imagen($ruta..,....
	
	normalizar_permisos_archivo($ruta_destino);
	
	//return $ruta_destino;	//!! o array rutas..
	
	return str_replace(RUTA_RAIZ, '', $ruta_destino);
}

function ruta_destino($opciones, $as = null)
{
	$nombre 	= @$opciones['nombre'];		if (empty($nombre)) 	$nombre 	= $as['name'];
	$ext 		= @$opciones['formato']; 	if (empty($ext)) 		$ext 		= extension($nombre);	
	$carpeta 	= @$opciones['carpeta']; 	if (empty($carpeta)) 	$carpeta 	= CARPETA_IMAGENES;
	
	$nombre = basename(strtolower($nombre), extension($nombre)); //nos quedamos con "nombre."
	
	$ruta = $carpeta . $nombre . $ext;

	return $ruta;
}

function leer_imagen($ruta)
{
	return imageCreateFromString(file_get_contents($ruta));
}


function guardar_imagen($imagen, $ruta, $calidad = NULL)
{
	$funcion = funcion_guardar_imagen($ruta);
	
	if (is_null($calidad))
		$funcion($imagen, $ruta);
	else
		$funcion($imagen, $ruta, reajustar_calidad_compresion($funcion, $calidad));
}

function reajustar_calidad_compresion($funcion, $compresion)
{
	switch (strtolower($funcion))
	{
		case 'imagepng': $compresion = 9 - round(9 * $compresion / 100.0); break;
	}
	
	return $compresion;
}

// nueva_redimension =>  anchoXalto,  anchoX, Xalto, escala%, ....
// list($anchoN, $altoN) = nuevo_ancho_alto('50%', 320, 200) => (160,100)
//function nuevo_ancho_alto($nueva_redimension, $ancho_inicial, $alto_inicial)
function calcular_redimension($datos_redimension, $info) //$info =  datos_archivo_imagen($ruta); 
{
	list($wO, $hO) = $info;

	list($wN, $hN) = is_array($datos_redimension) ? $datos_redimension : explode('x', $datos_redimension);
	
	if (isset($wN) || isset($hN))
	{
		$relWHO = $wO / $hO;
		
		if (empty($hN))	$hN = round($wN / $relWHO);
		if (empty($wN))	$wN = round($hN * $relWHO);
	}
	else
	{
		$porciento = int($datos_redimension) / 100.0;
		
		$wN = round($wO * $porciento);
		$hN = round($hO * $porciento);
	}
	
	return array($wN, $hN, $wO, $hO);
}

//	redimensionar_imagen <= f(ruta_archivo, ancho, ...)
function redimensionar_imagen($ruta, $datos_redimension)
{
	$imgO = imageCreateFromString(file_get_contents($ruta));
	
	list($wN, $hN, $wO, $hO) = calcular_redimension($datos_redimension, datos_archivo_imagen($ruta));
	
	$imgN = ImageCreateTrueColor ($wN, $hN); //var_dump($wN, $hN);

		//$colorFondo = ImageColorAllocateAlpha($imgN, 255,255,255,0);
		//ImageFill($imgN , 0,0 , $colorFondo); //amb truecolor cal pintar el fons	
	
	ImageCopyResampled($imgN, $imgO, 0, 0, 0, 0, $wN, $hN, $wO, $hO);
	
	return $imgN;
}
	
//function funcion_guardar_imagen($extension)
function funcion_guardar_imagen($ruta, $modo = 'extension')	
{
	if ($modo == 'extension')
		$funcion = funcion_guardar_imagen_por_extension(extension($ruta));

	//if ($modo == 'MIME')	//de momento queda descartado
	//	$funcion = funcion_guardar_imagen_por_mime(mime($ruta));
		
	if (! isset($funcion) )
		die(ERROR_MODO_GUARDAR_IMAGEN . " ¿$modo? ");
		
	if (function_exists($funcion))
		return $funcion;
	else
		die(ERROR_GUARDAR_IMAGEN_NO_EXISTE  . " ¿$funcion? ");	
}

function funcion_guardar_imagen_por_extension($tipo_extension)
{
	switch(strtolower($tipo_extension))
	{
		case 'jpg':
		case 'jpeg': $funcion = 'imagejpeg'; break;
		
		default: $funcion = 'image'.$tipo_extension;
	}
	
	return $funcion;
}

/*
	obtener funcion guardar según tipo mime
		!!! aunque para guardar en otro formato se obtendria según extension de la ruta destino
*/
function funcion_guardar_imagen_por_mime($tipo_mime)
{
	switch(strtolower($tipo_mime))
	{
		case 'image/gif':
	  		$funcion = 'imagegif';
			break;
			
      	case 'image/pjpeg':
		case 'image/jpeg':
		case 'image/jpg':
	  		$funcion = 'imagejpeg';
			break;
			
		case 'image/png':
		case 'image/x-png':
			$funcion = 'imagepng';
			break;
			
		default: die(ERROR_GUARDAR_IMAGEN_NO_VALIDO);
	}
	
	return $funcion;
}

	
function datos_archivo_imagen($ruta, $nombre_dato = NULL)
{
	if (is_null($nombre_dato))
		return getImageSize($ruta);
		
	$datos = getImageSize($ruta);
	
	if (isset($datos[$nombre_dato]))
		return $datos[$nombre_dato];
	else
		die(ERROR_NO_EXISTE_DATO_IMAGEN . " ¿$nombre_dato? ");
}
	
function guardar_archivo_subido($nombre_upload, $ruta_destino = '')
{
	$as = $_FILES[$nombre_upload];
	
	comprobar_upload($as);
	
	if (empty($ruta_destino))
		$ruta_destino = CARPETA_UPLOADS . $as['name'];
	
	if (! move_uploaded_file($as['tmp_name'], $ruta_destino))
		die(ERROR_MOVE_UPLOADED_FILE . ' ' . $as['tmp_name'] . ' ' . $ruta_destino);
		
	echo $as['name'] . " se ha guardado en $ruta_destino";
	
	normalizar_permisos_archivo($ruta_destino);
	
	//return $ruta_destino;
	return str_replace(RUTA_RAIZ, '', $ruta_destino);
}


function normalizar_permisos_archivo($ruta)
{
	chmod($ruta, 0666);
}


function extension($ruta_archivo)
{
	return end(explode('.', strtolower($ruta_archivo)));
}

function mime($ruta_archivo)
{
	return datos_archivo_imagen($ruta_archivo, 'mime');
}

function existe_extension($nombre_archivo, $lista_extensiones)
{
	return in_array(extension($nombre_archivo), $lista_extensiones);
}

function lista($cadena, $separador = ',')
{
	return explode($separador, $cadena);
}

function comprobar_upload($as)
{
	if ($as['error'])
		die(texto_error_upload($as['error']));

	if (! is_uploaded_file($as['tmp_name']))
		die(ERROR_NOT_A_UPLOADED_FILE . ' ' . $as['tmp_name']);	
		
	if (existe_extension($as['name'], lista(FORMATOS_PROHIBIDOS)))
		die(ERROR_FORMATO_PROHIBIDO);
		
	//if (file_size($as['tmp_name']) > MAX_PESO_UPLOAD)
	//	die(ERROR_MAX_PESO_UPLOAD);
}

function texto_error_upload($codigo_error)
{
	$texto = array(
		0=>'There is no error, the file uploaded with success',
		1=>'The uploaded file exceeds the upload_max_filesize directive in php.ini',
		2=>'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
		3=>'The uploaded file was only partially uploaded',
		4=>'No file was uploaded',
		6=>'Missing a temporary folder'
	);

	return $texto[$codigo_error];
}



function comprobar_upload_imagen($as)
{
	comprobar_upload($as);

	if(! stristr( $as['type'], 'image/' ))		//if(! eregi('image/', $as['type']))
		die(ERROR_NO_IMAGE_TYPE_UPLOADED);
	
	$info = datos_archivo_imagen($as['tmp_name']);
	
	if(! stristr( $info['mime'], 'image/' ))	//if(! eregi('image/', $info['mime']))
		die(ERROR_NO_IMAGE_MIME_FILE);	
	
	if (! existe_extension($as['name'], lista(FORMATOS_IMAGENES)))
		die(ERROR_FORMATO_IMAGEN_NO_VALIDO);
	
}



/*
function bm_crear($dimensiones)
{
	list($ancho, $alto) = explode('x',$dimensiones);
	
	return ImageCreateTrueColor ($ancho, $alto);
}

function bm_alpha($bm)
{
	// Desactivar la mezcla alfa y establecer la bandera alfa
	imagealphablending($bm, false);
	imagesavealpha($bm, true);

	return $bm;	
}
*/




/*

function coord_redimensionar($dimension_inicial, $dimension_final, $modo = CENTRADO)
{
	list($anchoF, $altoF) 	= explode('x', $dimension_final);
	list($ancho0, $alto0) 	= explode('x', $dimension_inicial);

	$ancho = $anchoF = intval($anchoF);
	$alto = $altoF = intval($altoF);
	
	$x = $x0 = 0;
	$y = $y0 = 0;

	$ARWH = $ancho0 / $alto0;
	
	if (empty($ancho)) 	$anchoF = $ancho	= empty($alto) 	? $ancho0 	: round($ARWH * $alto);
	if (empty($alto)) 	$altoF 	= $alto 	= empty($ancho)	? $alto0 	: round($ancho / $ARWH);
	
	//ancho y alto para calcular sobrantes y asi mantener el AR de la imagen redimensionada			
	$W = round($ARWH * $alto); 
	$H = round($ancho / $ARWH);

	switch ($modo)
	{
		
		case RECORTADO:

			if ($H > $alto)
			{	
				$sobrante = ($alto0 / $H) * ($H - $alto);
				$alto0 -= round($sobrante);
				$y0 = round($sobrante / 2); // en centro	
			}
			
			if ($W > $ancho)
			{
				$sobrante = (($ancho0 / $W) * ($W - $ancho) / 2);
				$ancho0 -= round($sobrante);
				$x0 = round($sobrante / 2); // en centro
			}

			break;


		case CENTRADO:

			if ($W > $ancho)	$alto = $H;						
			if ($H > $alto)		$ancho = $W;
								
			if ($anchoF > $ancho)
				$x = round(($anchoF - $ancho) / 2);
									
			if ($altoF > $alto)
				$y = round(($altoF - $alto) / 2);
				
			break;


		case CENTRADO_X:

			if ($W > $ancho)	$alto = $H;						
			if ($H > $alto)		$ancho = $W;
								
			if ($anchoF > $ancho)
				$x = round(($anchoF - $ancho) / 2);

			break;


		case CENTRADO_Y:

			if ($W > $ancho)	$alto = $H;						
			if ($H > $alto)		$ancho = $W;
								
			if ($altoF > $alto)
				$y = round(($altoF - $alto) / 2);
				
			break;


		default:
			
			if ($W > $ancho)	$alto = $H;						
			if ($H > $alto)		$ancho = $W;	
	
	}	
	
	
	//return  coordenadas 
}


*/

	
?>
<body>
<form enctype=multipart/form-data  method=POST>
archivo <input name=archivo type=file >
imagen <input name=imagen type=file >
<input type=submit >
</form>
<? if (isset($ruta_imagen)) : ?>
<img src="<?=@$ruta_imagen?>" alt="ruta = <?=@$ruta_imagen?>" />
<? endif ?>
</body>