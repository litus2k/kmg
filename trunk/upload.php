<?php
define('ERROR_NOT_A_UPLOADED_FILE'		, 'ERROR_NO_UPLOADED_FILE');
define('ERROR_MOVE_UPLOADED_FILE'		, 'ERROR_MOVE_UPLOADED_FILE');

define('ERROR_NO_IMAGE_TYPE_UPLOADED'	, 'ERROR_NO_IMAGE_TYPE_UPLOADED');
define('ERROR_NO_IMAGE_MIME_FILE'		, 'ERROR_NO_IMAGE_MIME_FILE');

define('ERROR_FORMATO_PROHIBIDO'		, 'ERROR_FORMATO_PROHIBIDO');
define('ERROR_FORMATO_IMAGEN_NO_VALIDO'	, 'ERROR_FORMATO_IMAGEN_NO_VALIDO');


$raiz = realpath('./') . '/';	//carpeta script

define('CARPETA_UPLOADS' 	, $raiz . 'ups/');

define('CARPETA_IMAGENES'	, CARPETA_UPLOADS . 'img/');
define('SUBCARPETA_MINIS'	, '_min/');

define('FORMATOS_PROHIBIDOS'	, 'php,php3,php4,phtml,exe');
define('FORMATOS_IMAGENES'		, 'jpg,png,gif,jpeg');


define('CALIDAD_COMPRESION', 3);

if (archivo_subido('archivo')) 
	guardar_archivo_subido('archivo');

if (archivo_subido('imagen')) 
	guardar_imagen_subida('imagen');
	

//

function archivo_subido($nombre_upload)
{
	return isset($_FILES[$nombre_upload]) && !empty($_FILES[$nombre_upload]['tmp_name']) ;
}

function guardar_imagen_subida($nombre_upload, $ruta_destino = '', $opciones = array())
{
	$as = $_FILES[$nombre_upload];
	
	comprobar_upload($as);
	
	//if(! eregi('image/', $as['type']))
	
	if(! stristr( $as['type'], 'image/' ))
		die(ERROR_NO_IMAGE_TYPE_UPLOADED);
	
	$info = datos_archivo_imagen($as['tmp_name']);
	
	//if(! eregi('image/', $info['mime']))
	if(! stristr( $info['mime'], 'image/' ))
		die(ERROR_NO_IMAGE_MIME_FILE);	
	
	if (! existe_extension($as['name'], explode(',',FORMATOS_IMAGENES)))
		die(ERROR_FORMATO_IMAGEN_NO_VALIDO);
	
	$ruta_origen = $as['tmp_name'];

	if (empty($ruta_destino))
		$ruta_destino = CARPETA_IMAGENES . $as['name'];
	
	//
	
	$imagen = imageCreateFromString(file_get_contents($ruta_origen));

	$_guardar_imagen = funcion_guardar_imagen(extension($ruta_destino));
	
	$_guardar_imagen($imagen, $ruta_destino); //, CALIDAD_COMPRESION);
	
	//
	
	return $ruta_destino;
}
	
function funcion_guardar_imagen($extension)
{
	switch(strtolower($extension))
	{
		case 'jpg':
		case 'jpeg': return 'imagejpeg'; break;
		
		default: return 'image'.$extension;
	}
}
	
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

	
?>
<body>
<form enctype=multipart/form-data  method=POST>
<input name=archivo type=file >
<input name=imagen type=file >
<input type=submit >
</form>
</body>