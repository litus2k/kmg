<?php
//exit("desactivado, pendiente arreglos y pruebas");

//para evitar error 403 Forbidden y poder realizar la extracción de datos de la web remota (web scrapping)
ini_set('user_agent', "Mozilla/5.0 Galeon/1.0.2 (X11; Linux i686; U;) Gecko/20011224"); //en principio vale cualquier cadena

//la extracción de datos puede tardar varios minutos y necesitamos evitar el error de max_execution_time
set_time_limit(0); //anulamos límite


//http://code.google.com/p/phpquery/wiki/Basics
include 'lib/phpQuery.php';


//patrones fuentes de datos
	
	//test
	
	define('PAGINA_SORTEOS'	, 'test/pagina.html');
	define('DATOS_SORTEO'	, 'test/sorteo-%d.html');
			
	//web loteriasyapuestas.es
	/*
	define('PAGINA_SORTEOS'	, 'http://www.loteriasyapuestas.es/index.php/mod.sorteos/mem.buscarListadoSorteos/filtro_cf.celebrado/juego.EMIL/pagina.%s');
	define('DATOS_SORTEO'	, 'http://www.loteriasyapuestas.es/index.php/mod.sorteos/mem.buscarDetalleSorteos/juego.EMIL/idsorteo.%d');
	*/
	
			
//selectores HTML/CSS
	
	//lista sorteos
	define('SORTEOS'	, 'div.cajasdeinfo');
	define('FECHA' 		, 'div.namefech');
	define('PREMIADOS'	, 'div.premespe');
	define('ID'			, 'div.inforesultdos a');

	//datos ampliados por sorteo
	define('DATOS_GENERALES'	, 'div.txtpremespedos');
	define('TABLA_PREMIOS'		, 'table.tabl_prem tbody tr td');	//para obtener tbody tr td 3 (premio) y 4 (acertantes)
		

		
//extraer datos

	function datos_sorteo($id_sorteo = 768202037)
	{
		// sorteo
		
		$file = sprintf(DATOS_SORTEO, $id_sorteo);
		phpQuery::newDocumentFileHTML($file);

		list($apuestas, $recaudacion, $bote, $premios) = pq_cantidades(pq(DATOS_GENERALES), 0, 1, 2, 3);
		//var_dump($apuestas, $recaudacion, $bote, $premios);
		
		list($premio, $acertantes) =  pq_cantidades(pq(TABLA_PREMIOS), 2, 3);
		//var_dump($premio, $acertantes);
		
		return array($premio, $acertantes, $apuestas, $recaudacion, $bote, $premios);
	}			
				
				
	function pq_cantidades()
	{
		if (func_num_args() < 2) 
			exit('error parametros! debe ser pq_cantidades($objeto_pq, indice , o lista indices)');
		
		$args = func_get_args(); 
		$o = array_shift($args);
		
		$l = array();
		while (!is_null($i = array_shift($args))) // !! usa is_null para evitar problemas con valores 0
			$l[] = isset($o->elements[$i]) ? cantidad_texto(pq($o->elements[$i])->text()) : '';
			
		return count($l) > 1 ? $l : $l[0];
	}	

	function cantidad_texto($cadena)
	{
		return preg_replace("/[^0-9,\.]/", "", $cadena);
	}
		
		
	//var_dump(datos_sorteo()); exit('fin datos sorteo');
	
	
	//lista sorteos

	function lista_sorteos_pagina($n = 1, $modo = 'ORIGEN')
	{
	
		echo "<h3>solicitando datos página $n ...</h3>";
	
		$file = sprintf(PAGINA_SORTEOS, $n);
		phpQuery::newDocumentFileHTML($file);


		$lista_sorteos = array();

		foreach (pq(SORTEOS) as $sorteo)
		{
			$s = pq($sorteo);
			
			list($dia, $fecha) = explode(',', pq(FECHA, $s)->text());

			$dia = strtoupper($dia[0]);
			$fecha = trim($fecha);
			
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
				
					default: var_dump($lista_numeros); exit("error lista_numeros [$cadena_numeros]");
				}
			}

			$id = preg_replace("/[^0-9]/", "", pq(ID, $s)->attr('href')); //echo " [$id] ";
			list($premio, $acertantes, $apuestas, $recaudacion, $bote, $premios) = datos_sorteo($id);
			
			echo "<p>sorteo $id $fecha</p>";
			//sleep(rand(1,3));  flush();

			$lista_sorteos[] = array($id, $dia, $fecha, $combinacion, $estrellas, $premio, $acertantes, $apuestas, $recaudacion, $bote, $premios);
		}
	
		switch ($modo)
		{
			case 'ORIGEN' : return $lista_sorteos; break;	//'break' for defensive programming!!
			case 'REVERSE': return array_reverse($lista_sorteos); break;
			
			default: exit("el modo '$modo' es desconocido");
		}
		
	}
	
	//var_dump(lista_sorteos()); exit('fin lista');


define('RUTA_ARCHIVO_DATOS'	, 'eme-historico_2004-20011.csv');
define('MODO_ESCRITURA'		, 'w');
define('MODO_LECTURA'		, 'r');
	
//guardar datos	
	
	print 'guardando datos... ';
	
	$fp = fopen(RUTA_ARCHIVO_DATOS, MODO_ESCRITURA);

	for ($p=40;$p>0;$p--)
	{
		$lista_sorteos = lista_sorteos_pagina($p, 'REVERSE');
		
		foreach ($lista_sorteos as $datos_sorteo)
			fputcsv($fp, $datos_sorteo);
	}
	
	fclose($fp);

	print 'datos guardados ';
	
	
//leer datos



/*
	+extraer datos 40 paginas
	
		--¿?!! revisar columnas premio, acertantes
	
		--!! errores algunos sorteos pag. 36	=> todos al empezar el 2005
				531902001 21/01/2005
				531202001 14/01/2005
				530502001 07/01/2005

	
	ordenar nºs por apariciones y ausencias
	comprobar grado de semejanza entre combinaciones (y si hay alguna repetida)
		agrupar semejantes y ordenar por grupos de combinaciones más comunes a menos
		
	calcular proporcion entre nº más repetidos y menos para cada combinación ganadora
	
	extraer patrones en base a tiempo (días y sorteos transcurridos), nº apuestas, cantidad premios, bote acumulado
	
	generar nuevas combinaciones en base a los resultados anteriores con el fin de acotar probabilidad
		realimentar sistema con la información pronosticada y los resultados futuros
			cargar sistema con la primera mitad de datos históricos
				reajustar sistema mediante comparación de pronósticos sobre la segunda mitad de resultados históricos
*/
	
	
//
//phpinfo();
?>