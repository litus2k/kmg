<?php

//patrones fuentes de datos

	//define('PAGINA_SORTEOS', 'http://www.loteriasyapuestas.es/index.php/mod.sorteos/mem.buscarListadoSorteos/filtro_cf.celebrado/juego.EMIL/pagina.%s');
	define('PAGINA_SORTEOS', 'test/pagina.html');

	//define('DATOS_SORTEO'	, 'http://www.loteriasyapuestas.es/index.php/mod.sorteos/mem.buscarDetalleSorteos/juego.EMIL/idsorteo.%d');
	define('DATOS_SORTEO'	, 'test/sorteo-%d.html');

	
$file = sprintf(PAGINA_SORTEOS,1); //'test/pagina.html'; //'http://cat1.net';


//echo file_get_contents($file);


//http://code.google.com/p/phpquery/wiki/Basics
include 'lib/phpQuery.php';
phpQuery::newDocumentFileHTML($file);

//echo pq('h3')->html();


//extraer datos

	//selectores CSS
	define('SORTEOS'	, 'div.cajasdeinfo');
	define('FECHA' 		, 'div.namefech');
	define('PREMIADOS'	, 'div.premespe');
	define('ID'			, 'div.inforesultdos a');

	$lista_sorteos = array();

	foreach (pq(SORTEOS) as $sorteo)
	{
		$s = pq($sorteo);
		
		list($dia, $fecha) = explode(',', pq(FECHA, $s)->text());

		$dia = strtoupper($dia[0]);
		$fecha = trim($fecha);
		
		$id = preg_replace("/[^0-9]/", "", pq(ID, $s)->attr('href')); //echo " [$id] ";
		
		foreach(pq(PREMIADOS, $s) as $premiado)
		{
			$cadena_numeros = pq($premiado)->text();
			$cadena_numeros = htmlspecialchars_decode(utf8_decode($cadena_numeros));
			
			//http://www.phpcodester.com/2011/04/converting-special-characters-in-php-to-utf-8/
			$cadena_numeros = strtr($cadena_numeros, chr(hexdec('A0')), ' '); //para limpiar basura codificación windows...
			
			$cadena_numeros = strtr($cadena_numeros, ',', ' ');
			
			$lista_numeros = explode(' ', $cadena_numeros); //echo urlencode($cadena_numeros);
			
			switch(count($lista_numeros))
			{
				case 2: $estrellas 		= $cadena_numeros; break;
				case 5: $combinacion 	= $cadena_numeros; break;
			
				default: var_dump($lista_numeros); exit("error litsta_numeros [$cadena_numeros]");
			}
		}

		//xtra datos sorteo:	apuestas, recaudación, bote, premios, acertantes
			//http://www.loteriasyapuestas.es/index.php/mod.sorteos/mem.buscarDetalleSorteos/juego.EMIL/idsorteo.769602041
			//http://www.loteriasyapuestas.es/index.php/mod.sorteos/mem.buscarDetalleSorteos/juego.EMIL/idsorteo.766402032
			
		$lista_sorteos[] = array($id, $dia, $fecha, $combinacion, $estrellas);
	}

	//var_dump($lista_sorteos);


define('RUTA_ARCHIVO_DATOS'	, 'datos.csv');
define('MODO_ESCRITURA'		, 'w');
define('MODO_LECTURA'		, 'r');
	
//guardar datos	
	
	print 'guardando datos... ';
	
	$fp = fopen(RUTA_ARCHIVO_DATOS, MODO_ESCRITURA);
	
	foreach ($lista_sorteos as $datos_sorteo)
		fputcsv($fp, $datos_sorteo);
		
	fclose($fp);

	print 'datos guardados ';
	
	
//leer datos


	
//
//phpinfo();
?>