<?php

namespace Classes;

use PHPMailer\PHPMailer\PHPMailer;

class Correo {
    public $nombre;
    public $apellido;
    public $correo;
    public $token;

    public function __construct($nombre, $apellido, $correo, $token) {
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->correo = $correo;
        $this->token = $token;
    }

    public function enviarConfirmacion() {
        // Creación del nuevo objeto.
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];

        $mail->setFrom('munijocotenango@no_reply.com');
        $mail->addAddress($this->nombre, $this->apellido, $this->correo);
        $mail->Subject = 'Confirmación de cuenta';

        // Establecer el HTML.
        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = "<html>";
        $contenido .= "<p>Hola administrador, <strong>{$this->nombre} {$this->apellido}</strong> desea confirmar su cuenta en Proyecto Muni de Jocotenango.</p>";
        $contenido .= "<p>Para confirmar, haga clic aquí: <a href='" . $_ENV['HOST'] . "/confirmar?token=" . $this->token . "'>Confirmar cuenta</a>";       
        $contenido .= "<p>Si no desea la creación de esta cuenta; puede ignorar el mensaje.</p>";
        $contenido .= "</html>";
        $mail->Body = $contenido;

        //Enviar el correo.
        $mail->send();
    }

    public function enviarInstrucciones() {
        // Creación del nuevo objeto.
        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->Host = $_ENV['EMAIL_HOST'];
        $mail->SMTPAuth = true;
        $mail->Port = $_ENV['EMAIL_PORT'];
        $mail->Username = $_ENV['EMAIL_USER'];
        $mail->Password = $_ENV['EMAIL_PASS'];

        $mail->setFrom('munijocotenango@no_reply.com');
        $mail->addAddress($this->nombre, $this->apellido, $this->correo);
        $mail->Subject = 'Restablecer contraseña';

        // Establecer el HTML.
        $mail->isHTML(TRUE);
        $mail->CharSet = 'UTF-8';

        $contenido = "<html>";
        $contenido .= "<p>Hola administrador, <strong>{$this->nombre} {$this->apellido}</strong> desea restablecer su contraseña en Proyecto Muni de Jocotenango.</p>";
        $contenido .= "<p>Para restablecer, haga clic aquí: <a href='" . $_ENV['HOST'] . "/restablecer?token=" . $this->token . "'>Restablecer contraseña</a>";       
        $contenido .= "<p>Si este cambio no fue autorizado; puede ignorar el mensaje.</p>";
        $contenido .= "</html>";
        $mail->Body = $contenido;

        //Enviar el correo.
        $mail->send();
    }
}
