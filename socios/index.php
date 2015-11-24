<?php session_start() ?>
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
            'numero' => array(
                'bonito'   => 'Número',
                'criterio' => 'numero',
                'exacto'   => TRUE
            ),
            'dni' => array(
                'bonito'   => 'DNI',
                'criterio' => 'dni',
                'exacto'   => TRUE
            ),
            'nombre' => array(
                'bonito'   => 'Nombre',
                'criterio' => 'nombre'
            ),
            'direccion' => array(
                'bonito'   => 'Dirección',
                'criterio' => 'direccion'
            ),
            'poblacion_nombre' => array(
                'bonito'   => 'Población',
                'criterio' => 'poblacion_nombre'
            ),
            'provincia_nombre' => array(
                'bonito'   => 'Provincia',
                'criterio' => 'provincia_nombre'
            ),
            'codpostal' => array(
                'bonito'   => 'Código postal',
                'criterio' => 'codpostal',
                'exacto'   => TRUE
            ),
            'telefono' => array(
                'bonito'   => 'Teléfono',
                'criterio' => 'telefono',
                'exacto'   => TRUE
            )
        );

        index($columnas, 'v_socios'); ?>
    </body>
</html>

