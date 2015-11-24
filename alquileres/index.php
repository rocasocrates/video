<?php session_start() ?>
<!DOCTYPE html>
<html>
    <head>
        <title>Videoclub</title>
        <meta charset="utf-8" />
    </head>
    <body><?php
        require '../comunes/auxiliar.php';
        require 'auxiliar.php';

        conectar();

        comprobar_usuario_conectado();

        $numero = $_SESSION['socio_numero'];
        $codigo = isset($_GET['codigo']) ? trim($_GET['codigo']) : "";

        if (isset($_POST['socio_id'], $_POST['copia_id'])) {
            try {
                $socio_id = trim($_POST['socio_id']);
                $copia_id = trim($_POST['copia_id']);
                alquilar($socio_id, $copia_id);
                $res = pg_query_params("select precio_alq
                                          from v_copias
                                         where id = $1", array($copia_id));
                $fila = pg_fetch_assoc($res, 0);
                $precio_alq = (double) $fila['precio_alq'];
                $cookie = isset($_COOKIE['subtotal']) ? trim($_COOKIE['subtotal']) : 0.0;
                $cookie += $precio_alq;
                setcookie('subtotal', "$cookie");
            } catch (Exception $e) { ?>
                <h3>Error: <?= $e->getMessage() ?></h3><?php
            }
        } elseif (isset($_POST['alquiler_id'])) {
            $alquiler_id = trim($_POST['alquiler_id']);
            devolver($alquiler_id);
        } elseif ($numero != "" && $codigo == "") {
            setcookie('subtotal', '0.0');
            $cookie = 0.0;
        }

        if (isset($cookie)) {
            $subtotal = $cookie;
        } elseif (isset($_COOKIE['subtotal'])) {
            $subtotal = trim($_COOKIE['subtotal']);
        } else {
            $subtotal = 0.0;
        }

        try {
            $fila = obtener_socio($numero); ?>
            <h4>Subtotal: <?= $subtotal ?></h4><?php
            $socio_id = $fila['id'];
            pendientes_de_socio($socio_id, $numero);
            pedir_codigo_copia($codigo);
            no_vacio($codigo);
            $fila = obtener_copia($codigo);
            mostrar_copia($fila);
            $copia_id = $fila['id'];
            copia_disponible($copia_id);
            boton_alquilar($socio_id, $copia_id);
        } catch (Exception $e) {
            $msg = $e->getMessage();
            if ($msg != ""): ?>
                <h3>Error: <?= $msg ?></h3><?php
            endif;
        } ?>
    </body>
</html>

