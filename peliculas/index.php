<!DOCTYPE html>
<html>
    <head>
        <title>Videoclub</title>
        <meta charset="utf-8" />
    </head>
    <body><?php
        require 'auxiliar.php';
        require '../comunes/auxiliar.php';

        $columnas = array(
            'codigo' => array(
                'bonito'   => 'Código',
                'criterio' => 'codigo',
                'exacto'   => TRUE,
                'align'    => 'center'
            ),
            'titulo' => array(
                'bonito'   => 'Título',
                'criterio' => 'titulo'
            ),
            'precio_alq_format' => array(
                'bonito'   => 'Precio alquiler',
                'criterio' => 'precio_alq_format',
                'align'    => 'right',
                'post'     => 'normalizar_precio'
            ),
            'fecha_alta_format' => array(
                'bonito'   => 'Fecha alta',
                'criterio' => 'fecha_alta_format',
                'align'    => 'center',
                'post'     => 'normalizar_fecha'
            )
        );

        index($columnas, 'v_copias'); ?>
    </body>
</html>

