<?php
$file = 'test/pagina.html'; //'http://cat1.net';


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
	define('ID'			, 'div.inforesultdos a:href');

	$lista_sorteos = array();

	foreach (pq(SORTEOS) as $sorteo)
	{
		$s = pq($sorteo);
		
		$fecha = pq(FECHA, $s)->text();	//echo $fecha;
		
		//http://snippetdb.com/php/extract-numbers-from-string
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
				case 2: $estrellas 				= $cadena_numeros; break;
				case 5: $combinacion_ganadora 	= $cadena_numeros; break;
			
				default: var_dump($lista_numeros); exit("error litsta_numeros [$cadena_numeros]");
			}
		}

		$lista_sorteos[] = array($id, $fecha, $combinacion_ganadora, $estrellas);
	}

	//var_dump($lista_sorteos);


//guardar datos	
	
	
	
//
//phpinfo();
?>
