<?php

	/**
	 * Sistema para cachear páginas de forma sencilla.
	 * 
	 * 
	 * @author: Carlos Mezquita (litus2k@gmail.com)
	 * @date: 2009-05-15
	 * 
	 * @param: cadena RUTA_CARPETA_CACHE
	 * @param: número TIEMPO_CACHE  en segundos
	 * 
	 **/
 

	define('NOMBRE_PAGINA_WEB'				, $_SERVER["SCRIPT_NAME"] . $_SERVER["QUERY_STRING"]);
	define('NOMBRE_ARCHIVO_CACHE'			, md5(NOMBRE_PAGINA_WEB));

	define('CACHE_CARPETA_PREDETERMINADA'	, dirname(__FILE__) . '/cache/');
	define('CACHE_TIEMPO_PREDETERMINADO'	, 5); //en segundos


	if (!defined('RUTA_CARPETA_CACHE'))
		define('RUTA_CARPETA_CACHE' 		, CACHE_CARPETA_PREDETERMINADA);
	
	if (!defined('TIEMPO_CACHE'))
		define('TIEMPO_CACHE'				, CACHE_TIEMPO_PREDETERMINADO);	


	define('MODO_ESCRITURA'					, 'w');
	define('MODO_LECTURA'					, 'r');
	
	define('ERROR_ABRIR_ARCHIVO_ESCRITURA'	, FALSE);
	define('ERROR_ABRIR_ARCHIVO_LECTURA'	, FALSE);
	define('GUARDAR_ARCHIVO_OK'				, TRUE);


	if (hay_pagina_cacheada())
	{
		mostrar_pagina_cacheada();
		exit();
	}
	else
		cachear_pagina();

	
	function hay_pagina_cacheada($ruta_archivo = NOMBRE_ARCHIVO_CACHE)
	{
		$ruta = RUTA_CARPETA_CACHE . $ruta_archivo;
		
		clearstatcache();
		
		if (!file_exists($ruta))
			return FALSE;
			
		if (!($tiempo_archivo = filemtime($ruta)))
			die("error al intentar obtener el tiempo del archivo [$ruta]");
			
		if (($tiempo_archivo + TIEMPO_CACHE) < time())
			return FALSE;
		
		return TRUE;
	}


	function mostrar_pagina_cacheada($ruta_archivo = NOMBRE_ARCHIVO_CACHE)
	{
		if ($contenido = leer_archivo_cache($ruta_archivo))
			echo $contenido;
		else
			echo "error leer archivo [$ruta_archivo]";
	}


	function cachear_pagina()
	{
		register_shutdown_function('finalizar_script');
		ob_start('procesar_salida');		
	}
	
	function finalizar_script()
	{		
		while (@ob_end_flush());
	}	

	function procesar_salida($contenido)
	{
		if (guardar_archivo_cache($contenido))
			return $contenido;
		else
			return "error guardar archivo " . NOMBRE_ARCHIVO_CACHE;
	}


	function guardar_archivo_cache($contenido, $ruta_archivo = NOMBRE_ARCHIVO_CACHE)
	{

		if($archivo = @fopen(RUTA_CARPETA_CACHE . $ruta_archivo, MODO_ESCRITURA)) 
		{
		   fwrite($archivo, $contenido);
		   fclose($archivo);
		}
		else 
			return ERROR_ABRIR_ARCHIVO_ESCRITURA;
			
		return GUARDAR_ARCHIVO_OK;			
		
	}

	function leer_archivo_cache($ruta_archivo = NOMBRE_ARCHIVO_CACHE)
	{
		$ruta = RUTA_CARPETA_CACHE . $ruta_archivo;
		 
		if ($archivo 	= @fopen($ruta, MODO_LECTURA))
		{
			$contenido 	= fread($archivo, filesize($ruta));
			fclose($archivo);
			return $contenido; 					
		}
		else
			return ERROR_ABRIR_ARCHIVO_LECTURA;
		
	}

?>