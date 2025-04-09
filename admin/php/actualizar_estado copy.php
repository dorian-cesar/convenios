<?php
require 'conexion.php';



use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $estado = $_POST['estado']; // "Aprobado" o "Rechazado"

    // 1. Actualizar en la base de datos

  

    $stmt = $conexion->prepare("UPDATE convenios SET estado = ? WHERE id = ?");
    $stmt->bind_param("si", $estado, $id);
    $stmt->execute();

        // 2. Obtener email del postulante
    $stmt2 = $conexion->prepare("SELECT email, nombres FROM convenios WHERE id = ?");
    $stmt2->bind_param("i", $id);
    $stmt2->execute();
    $resultado = $stmt2->get_result();
    $postulante = $resultado->fetch_assoc();

    if ($postulante) {
        $correo = $postulante['email'];
        $nombre = $postulante['nombres'];

        // 3. Enviar email con PHPMailer
        $mail = new PHPMailer(true);

        try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Cambia esto por tu servidor SMTP
        $mail->SMTPAuth = true;
        $mail->Username = ''; // Cambia esto por tu correo electrónico 
        $mail->Password ='' ;//
        $mail->SMTPSecure = 'tls'; // O 'ssl' si es necesario
        $mail->Port = 587; // Puerto SMTP

            // Codificación UTF-8
        $mail->CharSet = 'UTF-8';
        $mail->Encoding = 'base64';
            $mail->setFrom('tu-correo@dominio.com', 'Convenios PullmanBus');
            $mail->addAddress($correo, $nombre);

            $mail->isHTML(true);
            $mail->Subject = 'Resultado de tu postulación';

            if ($estado === 'Aprobada') {
                $mail->Body = "
                    <p>Hola <strong>$nombre</strong>,</p>
                    <p>¡Tu postulación al convenio ha sido <strong>Aprobada</strong>! En las próximas 72 horas activaremos tus datos y podrás viajar con descuento.</p>
                    <p>Gracias por postular a PullmanBus.</p>";
            } else {
                $mail->Body = "
                    <p>Hola <strong>$nombre</strong>,</p>
                    <p>Lamentamos informarte que tu postulación al convenio fue <strong>Rechazada</strong>.</p>
                    <p>Te invitamos a revisar los requisitos y volver a intentarlo corrigiendo la información o documentos.</p>";
            }

            $mail->send();
            echo "Estado actualizado y correo enviado.";
        } catch (Exception $e) {
            echo "Error al enviar correo: {$mail->ErrorInfo}";
        }
    } else {
        echo "Postulante no encontrado.";
    }
}
?>
