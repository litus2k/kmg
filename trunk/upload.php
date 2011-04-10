<?php
define('ERROR_NOT_A_UPLOADED_FILE'		, 'ERROR_NO_UPLOADED_FILE');
define('ERROR_MOVE_UPLOADED_FILE'		, 'ERROR_MOVE_UPLOADED_FILE');

define('ERROR_NO_IMAGE_TYPE_UPLOADED'	, 'ERROR_NO_IMAGE_TYPE_UPLOADED');
define('ERROR_NO_IMAGE_MIME_FILE'		, 'ERROR_NO_IMAGE_MIME_FILE');

define('ERROR_FORMATO_PROHIBIDO'		, 'ERROR_FORMATO_PROHIBIDO');
define('ERROR_NO_FORMATO_IMAGEN_VALIDO'	, 'ERROR_NO_FORMATO_IMAGEN_VALIDO');


$raiz = realpath('./') . '/';	//carpeta script

define('CARPETA_UPLOADS' 	, $raiz . 'ups/');

define('CARPETA_IMAGENES'	, CARPETA_UPLOADS . 'img/');
define('SUBCARPETA_MINIS'	, '_min/');

define('FORMATOS_PROHIBIDOS'	, array('php', 'php3', 'php4', 'phtml','exe'));
define('FORMATOS_IMAGENES'		, array('jpg', 'png', 'gif', 'jpeg'));


if (isset($_FILES['archivo'])) 
	guardar_archivo_subido($_FILES['archivo']);

	
if (isset($_FILES['imagen'])) 
	guardar_imagen_subida($_FILES['imagen']);
	

//

function guardar_imagen_subida($as, $ruta_destino = '', $opciones = array)
{
	comprobar_upload($as);
	
	if(! eregi('image/', $as['type']))
		die(ERROR_NO_IMAGE_TYPE_UPLOADED);
	
	$info = getImageSize($as['tmp_name']);
	if(! eregi('image/', $info['mime']))
		die(ERROR_NO_IMAGE_MIME_FILE);	
	
	if (! existe_extension($as['name'], FORMATOS_IMAGENES))
		die(ERROR_NO_FORMATO_IMAGEN_VALIDO);
	
	$ruta_origen = $as['tmp_name'];

	if (empty($ruta_destino))
		$ruta_destino = CARPETA_IMAGENES . $as['name'];
	
	//
	
	$imagen = imageCreateFromString(file_get_contents($ruta_origen));
	
	$ruta_imagen_final = $ruta_destino;
	
	return $ruta_imagen_final;
}
	
function guardar_archivo_subido($as, $ruta_destino = '')
{
	comprobar_upload($as);
	
	if (empty($ruta_destino))
		$ruta_destino = CARPETA_UPLOADS . $as['name'];
	
	if (! move_uploaded_file($as['tmp_name'], $ruta_destino))
		die(ERROR_MOVE_UPLOADED_FILE . ' ' . $as['tmp_name'] . ' ' . $ruta_destino);
		
	echo $as['name'] . " se ha guardado en $ruta_destino";
	
	return $ruta_destino;
}


function existe_extension($nombre_archivo, $lista_extensiones)
{
	return in_array(end(explode('.', strtolower($nombre_archivo))), $lista_extensiones);
}

function comprobar_upload($as)
{
	if ($as['error'])
		die(texto_error_upload($as['error']));

	if (! is_uploaded_file($as['tmp_name']))
		die(ERROR_NOT_A_UPLOADED_FILE . ' ' . $as['tmp_name']);	
		
	if (existe_extension($as['name'], FORMATOS_PROHIBIDOS)
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
<input type=submit >
</form>
</body>