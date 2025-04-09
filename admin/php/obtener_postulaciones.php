<?php
require_once 'conexion.php';

$sql = "SELECT * FROM convenios ORDER BY fecha_postulacion DESC";
$resultado = $conexion->query($sql);

$postulaciones = [];

while ($fila = $resultado->fetch_assoc()) {
    $postulaciones[] = $fila;
}

header('Content-Type: application/json');
echo json_encode($postulaciones);
