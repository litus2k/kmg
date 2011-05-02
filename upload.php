<?php
define('ERROR_NOT_A_UPLOADED_FILE'		, 'ERROR_NO_UPLOADED_FILE');
define('ERROR_MOVE_UPLOADED_FILE'		, 'ERROR_MOVE_UPLOADED_FILE');

define('ERROR_NO_IMAGE_TYPE_UPLOADED'	, 'ERROR_NO_IMAGE_TYPE_UPLOADED');
define('ERROR_NO_IMAGE_MIME_FILE'		, 'ERROR_NO_IMAGE_MIME_FILE');

define('ERROR_FORMATO_PROHIBIDO'		, 'ERROR_FORMATO_PROHIBIDO');
define('ERROR_FORMATO_IMAGEN_NO_VALIDO'	, 'ERROR_FORMATO_IMAGEN_NO_VALIDO');

define('ERROR_GUARDAR_IMAGEN_NO_VALIDO'	, 'ERROR_GUARDAR_IMAGEN_NO_VALIDO');
define('ERROR_GUARDAR_IMAGEN_NO_EXISTE'	, 'ERROR_GUARDAR_IMAGEN_NO_EXISTE');


$raiz = realpath('./') . '/';	//carpeta script

define('CARPETA_UPLOADS' 	, $raiz . 'ups/');

define('CARPETA_IMAGENES'	, CARPETA_UPLOADS . 'img/');
define('SUBCARPETA_MINIS'	, '_min/');

define('FORMATOS_PROHIBIDOS'	, 'php,php3,php4,phtml,exe');
define('FORMATOS_IMAGENES'		, 'jpg,png,gif,jpeg');


//define('CALIDAD_COMPRESION', 3);

//

if (archivo_subido('archivo')) 
	guardar_archivo_subido('archivo');

if (archivo_subido('imagen')) 
{
	$opciones_imagen = array
	(
		'destino'			=> CARPETA_IMAGENES,
		'dimensiones' 		=> '320x',
		'redimensionado'	=> CENTRADO_X,
		'fondo'				=> 'FFFFFF',		
		'formato' 			=> 'png',
		'compresion' 		=> 7
	);

	$opciones_imagen_mini = array
	(
		'destino'		=> CARPETA_IMAGENES . SUBCARPETA_MINIS,
		'dimensiones' 	=> '32x32',
		'formato' 		=> 'png',
		'compresion' 	=> 4
	);
	
	guardar_imagen_subida('imagen', $opciones_imagen, $opciones_imagen_mini);
}	

//

function archivo_subido($nombre_upload)
{
	return isset($_FILES[$nombre_upload]) && !empty($_FILES[$nombre_upload]['tmp_name']) ;
}

function guardar_imagen_subida($nombre_upload, $opciones = array(), $opciones_mini = null)
{
	$as = $_FILES[$nombre_upload];

	comprobar_upload_imagen($as);

	//
	$ruta_destino = $opciones['destino'];
	//
	
	$ruta_origen = $as['tmp_name'];

	if (empty($ruta_destino))
		$ruta_destino = CARPETA_IMAGENES . $as['name'];

	//---  TODO: ampliar datos imagen (resolucion(ruta_origen), ...)
	
	if (! empty($opciones['dimensiones']))
		$imagen = redimensionar_imagen($imagen, $opciones['dimensiones']);

	if (! isset($imagen) )
		$imagen = imageCreateFromString(file_get_contents($ruta_origen));
		
	//
	
	$_guardar_imagen = funcion_guardar_imagen(extension($ruta_destino));
	
	$_guardar_imagen($imagen, $ruta_destino); //, CALIDAD_COMPRESION);

	//
	if (is_array($opciones_mini))	//para guardar en ./mini/
		$imagen_mini = bm_redimensionar($imagen, $opciones_mini);
	//
	
	return $ruta_destino;
}

/*
	redimensionar_imagen <= f(ruta_archivo, ancho, ...)

		$colorFondo = ImageColorAllocateAlpha($imgN, 255,255,255,0);
		ImageFill($imgN , 0,0 , $colorFondo); //amb truecolor cal pintar el fons	
*/
function redimensionar_imagen($ruta, $wN, $hN = NULL)
{
	$imgO = imageCreateFromString(file_get_contents($ruta));
	
	$info =  datos_archivo_imagen($ruta);
	$wO = $info['width'];	$hO = $info['height'];	$relWHO = $wO / hO;
	
	if (is_null($hN))	$hN = round($wN / $relWHO);
	
	$imgN = ImageCreateTrueColor ($wN, $hN); 

	ImageCopyResampled($imgN, $imgO, 0, 0, 0, 0, $wN, $hN, $wO, $hO);
	
	return $imgN;
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

	
function funcion_guardar_imagen($extension)
{
	switch(strtolower($extension))
	{
		case 'jpg':
		case 'jpeg': return 'imagejpeg'; break;
		
		default: return 'image'.$extension;
	}
}

/*
function funcion_guardar_imagen($tipo_mime)
{
	switch(strtolower($tipo_mime))
	{
		case 'image/gif':
	  		$funcion_guardar = 'imagegif';
			break;
			
      	case 'image/pjpeg':
		case 'image/jpeg':
		case 'image/jpg':
	  		$funcion_guardar = 'imagejpeg';
			break;
			
		case 'image/png':
		case 'image/x-png':
			$funcion_guardar = 'imagepng';
			break;
			
		default: die(ERROR_GUARDAR_IMAGEN_NO_VALIDO);
	}
	
	if (is_function_exists($funcion_guardar))
		return $funcion_guardar;
	else
		die(ERROR_GUARDAR_IMAGEN_NO_EXISTE  . " ¿$funcion_guardar? ");
}
*/

	
function datos_archivo_imagen($ruta)
{
	return getImageSize($ruta);
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
	
	return $ruta_destino;
}

function extension($ruta_archivo)
{
	return end(explode('.', strtolower($ruta_archivo)));
}

function existe_extension($nombre_archivo, $lista_extensiones)
{
	return in_array(extension($nombre_archivo), $lista_extensiones);
}

function comprobar_upload($as)
{
	if ($as['error'])
		die(texto_error_upload($as['error']));

	if (! is_uploaded_file($as['tmp_name']))
		die(ERROR_NOT_A_UPLOADED_FILE . ' ' . $as['tmp_name']);	
		
	if (existe_extension($as['name'], explode(',',FORMATOS_PROHIBIDOS)))
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
	
	if (! existe_extension($as['name'], explode(',',FORMATOS_IMAGENES)))
		die(ERROR_FORMATO_IMAGEN_NO_VALIDO);
	
}

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
<input name=archivo type=file >
<input name=imagen type=file >
<input type=submit >
</form>
</body>