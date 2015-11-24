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

        $cols = array('id', 'numero', 'dni', 'nombre', 'direccion',
                      'codpostal', 'poblacion_id', 'telefono');

        $vals = array();
        for ($i = 0; $i < count($cols); $i++) {
            $vals[] = "";
        }

        $variables = array_combine($cols, $vals);
        extract($variables);

        if (isset($_GET['id'])) {
            $id = trim($_GET['id']);
            $res = pg_query_params("select *
                                      from socios
                                     where id = $1", array($id));
            $fila = pg_fetch_assoc($res, 0);
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

            extract($variables);

            $error = array();

            try {
                comprobar_numero($numero, $error);
                comprobar_dni($dni, $error);
                comprobar_nombre($nombre, $error);
                comprobar_direccion($direccion, $error);
                comprobar_codpostal($codpostal, $error);
                comprobar_poblacion_id($poblacion_id, $error);
                comprobar_telefono($telefono, $error);
                comprobar_errores($error);

                $res = pg_query("begin");
                bloquear_tabla_socios();
                comprobar_existe_socio($numero, $id, $error);
                $res = modificar_socio(compact($cols));
                comprobar_operacion($res, $error);
                $res = pg_query("commit"); ?>
                <h3>Se ha modificado el socio correctamente</h3><?php
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
