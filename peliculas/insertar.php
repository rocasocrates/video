<!DOCTYPE html>
<html>
    <head>
        <title>Insertar un socio</title>
        <meta charset="utf-8" />
    </head>
    <body><?php
        require 'auxiliar.php';
        require 'insertar_auxiliar.php';
        require '../comunes/auxiliar.php';

        conectar();

        $cols = array('pelicula_id', 'titulo', 'precio_alq', 'codigo');

        $vals = array();
        for ($i = 0; $i < count($cols); $i++) {
            $vals[] = "";
        }

        $variables = array_combine($cols, $vals);
        extract($variables);

        $existe = TRUE;
        foreach ($cols as $col) {
            $existe = $existe && isset($_POST[$col]);
        }

        if ($existe) {

            foreach ($variables as $k => $v) {
                $variables[$k] = trim($_POST[$k]);
            }

            extract($variables);

            $error = array();

            try {
                comprobar_pelicula_id($pelicula_id, $error);
                comprobar_juntos($titulo, $precio_alq, $error);
                comprobar_titulo($titulo, $error);
                comprobar_precio_alq($precio_alq, $error);
                comprobar_codigo($codigo, $error);
                comprobar_errores($error);

                $res = pg_query("begin");
                bloquear_tabla_copias();
                comprobar_existe_copia($codigo, $error);
                if ($titulo != "" && $precio_alq != "") {
                    $valores = compact('titulo', 'precio_alq');
                    $res = insertar_pelicula($valores, $pelicula_id);
                    comprobar_operacion($res, $error);
                }
                $valores = compact('codigo', 'pelicula_id');
                $res = insertar_copia($valores);
                comprobar_operacion($res, $error);
                $res = pg_query("commit"); ?>
                <h3>Se ha insertado la película correctamente</h3><?php
                $exito = TRUE;
            } catch (Exception $e) {
                foreach ($error as $err): ?>
                    <h3>Error: <?= $err ?></h3><?php
                endforeach;
            }
        }

        if (!isset($exito)) {
            formulario_insertar($variables);
        } ?>
        <a href="index.php"><input type="button" value="Volver" /></a>
    </body>
</html>
