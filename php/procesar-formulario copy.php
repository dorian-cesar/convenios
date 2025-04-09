<?php
require_once 'conexion.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php';

function guardarArchivo($archivo, $carpeta) {
    $nombreArchivo = uniqid() . '_' . basename($archivo['name']);
    $rutaDestino = 'uploads/' . $carpeta . '/' . $nombreArchivo;
    if (!file_exists('uploads/' . $carpeta)) {
        mkdir('uploads/' . $carpeta, 0777, true);
    }
    move_uploaded_file($archivo['tmp_name'], $rutaDestino);
    return $rutaDestino;
}

$nombres = $_POST['nombres'];
$apellidos = $_POST['apellidos'];
$fechaNacimiento = $_POST['fechaNacimiento'];
$rut = $_POST['rut'];
$sexo = $_POST['sexo'];
$telefono = $_POST['telefono'];
$email = $_POST['email'];
$region = $_POST['region_nombre'];
$comuna = $_POST['comuna_nombre'];
//$establecimiento = $_POST['establecimiento'];
$origen = $_POST['origen_nombre'];
$destino = $_POST['destino_nombre'];
$tipoConvenio = $_POST['tipo_convenio'];

$cedulaPath = guardarArchivo($_FILES['cedula'], 'cedulas');
$certificadoPath = guardarArchivo($_FILES['certificado'], 'certificados');

$stmt = $conexion->prepare("INSERT INTO convenios (
    nombres, apellidos, fecha_nacimiento, rut, sexo, telefono, email,
    region, comuna, origen, destino,
    cedula_path, certificado_path, tipo_convenio
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

$stmt->bind_param("ssssssssssssss",
    $nombres, $apellidos, $fechaNacimiento, $rut, $sexo, $telefono, $email,
    $region, $comuna,  $origen, $destino,
    $cedulaPath, $certificadoPath, $tipoConvenio
);

if ($stmt->execute()) {
    echo "Tu postulación fue enviada correctamente. Revisa tu correo para validar.";
} else {
    http_response_code(500);
    echo "Error al guardar la postulación: " . $stmt->error;
}

$stmt->close();
$conexion->close();

$mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP

        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Cambia esto por tu servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = ''; // Cambia esto por tu correo electrónico 
        $mail->Password ='' ;// Cambia esto por tu contraseña
        $mail->SMTPSecure = 'tls'; // O 'ssl' si es necesario
        $mail->Port = 587; // Puerto SMTP

            // Codificación UTF-8
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
    

        // Remitente y destinatario
        $mail->setFrom('tucorreo@tudominio.com', 'Convenios PullmanBus');
        $mail->addAddress($email, "$nombres $apellidos");

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Subject = "Confirmación de postulación a convenio - $tipoConvenio";
        $mail->Body    = "
            <h3>Hola $nombres,</h3>
            <p>Hemos recibido tu postulación al convenio <strong>$tipoConvenio</strong>.</p>
            <p>Validaremos tus documentos y te notificaremos el resultado vía e-mail en los próximos días.</p>
            <p>Gracias por postular. Si tienes dudas, contáctanos respondiendo este correo.</p>
            <br><p>Atentamente,<br>Equipo Turbus</p>
        ";

        $mail->send();
        echo "Tu postulación ha sido recibida y se ha enviado un correo de confirmación a $email.";
    } catch (Exception $e) {
        echo "Error al enviar el correo: {$mail->ErrorInfo}";
    }
