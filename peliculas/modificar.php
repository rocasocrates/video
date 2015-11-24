<!DOCTYPE html>
<html>
    <head>
        <title>Modificar un socio</title>
        <meta charset="utf-8" />
    </head>
    <body><?php
        require 'auxiliar.php';
        require 'modificar_auxiliar.php';
        require '../comunes/auxiliar.php';

        conectar();

        $cols = array('id', 'pelicula_id', 'titulo', 'precio_alq', 'codigo',
                      'dia', 'mes', 'anyo');

        $vals = array();
        for ($i = 0; $i < count($cols); $i++) {
            $vals[] = "";
        }

        $variables = array_combine($cols, $vals);
        extract($variables);

        if (isset($_GET['id'])) {
            $id = trim($_GET['id']);
            $res = pg_query_params("select *
                                      from v_copias
                                     where id = $1", array($id));
            $fila = pg_fetch_assoc($res, 0);
            $fila['precio_alq'] = $fila['precio_alq_format'];
            extract($fila);
            $variables = $fila;
        }

        $existe = TRUE;
        foreach ($cols as $col) {
            $existe = $existe && isset($_POST[$col]);
        }

        if ($existe) {

            foreach ($variables as $k => $v) {
                $variables[$k] = trim($_POST[$k]);
            }

            $variables['fecha_alta'] = $variables['anyo'] . '-' .
                                       $variables['mes'] . '-' .
                                       $variables['dia'];

            extract($variables);

            $error = array();

            try {
                comprobar_pelicula_id($pelicula_id, $error);
                comprobar_titulo_obligatorio($titulo, $error);
                comprobar_titulo($titulo, $error);
                comprobar_precio_alq_obligatorio($precio_alq, $error);
                comprobar_precio_alq($precio_alq, $error);
                comprobar_codigo($codigo, $error);
                comprobar_fecha_alta($dia, $mes, $anyo, $error);
                comprobar_errores($error);

                $res = pg_query("begin");
                bloquear_tabla_copias();
                comprobar_existe_copia($codigo, $id, $error);
                if ($titulo != "" && $precio_alq != "") {
                    $valores = compact('titulo', 'precio_alq');
                    $valores['id'] = $pelicula_id;
                    $res = modificar_pelicula($valores);
                    comprobar_operacion($res, $error);
                }
                $valores = compact('id', 'codigo', 'fecha_alta');
                $res = modificar_copia($valores);
                comprobar_operacion($res, $error);
                $res = pg_query("commit"); ?>
                <h3>Se ha modificado la película correctamente</h3><?php
                $exito = TRUE;
            } catch (Exception $e) {
                foreach ($error as $err): ?>
                    <h3>Error: <?= $err ?></h3><?php
                endforeach;
            }
        }

        if (!isset($exito)) {
            formulario_modificar($variables);
        } ?>
        <a href="index.php"><input type="button" value="Volver" /></a>
    </body>
</html>
