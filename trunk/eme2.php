<?php

define('RUTA_HISTORICO'		, 'eme-historico_2004-2011.csv');
define('RUTA_RESULTADOS'	, 'eme-resultados.csv');
define('MODO_ESCRITURA'		, 'w');
define('MODO_LECTURA'		, 'r');




//leer datos

	$lista = array();
	
	$apariciones_numero = array_fill(1,50,0);
	
	$sorteo = array();
	
	if (($f = fopen(RUTA_HISTORICO, MODO_LECTURA)) !== FALSE) 
	{
		while (($datos = fgetcsv($f, 150, ",")) !== FALSE) 
		{
			list($id, $dia, $fecha, $combinacion, $estrellas, $premio, $acertantes, $apuestas, $recaudacion, $bote, $premios) = $datos;
				
			if (isset($lista[$combinacion]))
				echo "a $fecha la combinacion $combinacion repetida el " . $lista[$combinacion];  
			else
				$lista[$combinacion] =  $fecha;
	
			$numeros = explode(' ', $combinacion);			
			foreach ($numeros as $n)	$apariciones_numero[$n] += 1;
	
			$sorteo[] = array('fecha' => $fecha, 'numeros' => $numeros, 'apariciones' => $apariciones_numero);
		}
		fclose($f);
	}
	
	//asort($apariciones_numero);	var_dump(count($lista), $apariciones_numero);
	
	$cs = 0;
?>

<style>
	table {width:100%}
	td {font-size:12px; text-align:center}
	td.premiado {background: lightgreen}
</style>

<table>
<tr>
	<th>fecha</th>
	<?foreach ($apariciones_numero as $n => $c) echo "<th>$n</th>" ?>
	<th>-rel-</th>
</tr>

	<?foreach ($sorteo as $s) : ?>
	<tr>
	<?
		$ceros = 0;
		
		$fecha = $s['fecha'];
		$lista_apariciones = $s['apariciones'];
		$numero_premiado = $s['numeros'];
		
		echo "<td>$fecha</td>";
		foreach ($lista_apariciones as $n => $c) 
		{
			if ($c == 0) $ceros += 1;
			
			$tipo = '';
			if (current($numero_premiado) == $n) 
			{
				$tipo = ' class="premiado" ';
				next($numero_premiado);
			}
			
			echo "<td $tipo>$c</td>";
		}
		echo "<td> $ceros </td>";
	?>
	</tr>
	<?
		if ((++$cs)%5 == 0)
		{
			echo "<th>fecha</th>";
			foreach ($apariciones_numero as $n => $c) echo "<th>$n</th>";
			echo "<th>-rel-</th>";
		}
		
		endforeach
	?>

</table>	
<?php	
	exit("FIN $cs");


	
//guardar datos	
	/*
	print 'guardando datos... ';
	
	$fp = fopen(RUTA_RESULTADOS, MODO_ESCRITURA);

	for ($p=40;$p>0;$p--)
	{
		$lista_resultados = lista_sorteos_pagina($p, 'REVERSE');
		
		foreach ($lista_sorteos as $datos_sorteo)
			fputcsv($fp, $datos_sorteo);
	}
	
	fclose($fp);

	print 'datos guardados ';
	*/
	



/*
	
	ordenar nºs por apariciones y ausencias
		ausencias o medir lo que tarda en volver a salir
		
	calcular proporcion entre nº más repetidos y menos para cada combinación ganadora
	
	comprobar grado de semejanza entre combinaciones (y si hay alguna repetida)
		agrupar semejantes y ordenar por grupos de combinaciones más comunes a menos
	
	extraer patrones en base a tiempo (días y sorteos transcurridos), nº apuestas, cantidad premios, bote acumulado
		!! generar lista de 400 sorteos x estadisticas de los 50 numeros...  unos 20MB
	
	generar nuevas combinaciones en base a los resultados anteriores con el fin de acotar probabilidad
		realimentar sistema con la información pronosticada y los resultados futuros
			cargar sistema con la primera mitad de datos históricos
				reajustar sistema mediante comparación de pronósticos sobre la segunda mitad de resultados históricos
*/
	
	
//
//phpinfo();
?>