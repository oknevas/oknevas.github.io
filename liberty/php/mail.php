<?php
// Вказуємо, що відповідь буде JSON
header('Content-Type: application/json');

// Підключення PHPMailer
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';
require 'phpmailer/Exception.php';

// === Налаштування === //
$title = "Заявка від сайту";
$file = $_FILES['file'] ?? null;

// Тіло листа
$c = true;
$body = "";
foreach ($_POST as $key => $value) {
    if ($value !== "" && !in_array($key, ["project_name","admin_email","form_subject"])) {
        $value = htmlspecialchars($value);
        $body .= "
        " . (($c = !$c) ? '<tr>' : '<tr style="background-color: #f8f8f8;">') . "
          <td style='padding:10px; border:1px solid #e9e9e9;'><b>$key</b></td>
          <td style='padding:10px; border:1px solid #e9e9e9;'>$value</td>
        </tr>
        ";
    }
}
$body = "<table style='width:100%; border-collapse:collapse;'>$body</table>";

// === Надсилання листа === //
$mail = new PHPMailer\PHPMailer\PHPMailer();

try {
    $mail->isSMTP();
    $mail->CharSet = "UTF-8";
    $mail->SMTPAuth = true;

    // Налаштування пошти
    $mail->Host       = 'smtp.gmail.com';
    $mail->Username   = 'example@gmail.com'; // логін
    $mail->Password   = ''; // пароль
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    $mail->setFrom('example@gmail.com', 'Заявка від сайту');

	// Одержувачі листа
    $mail->addAddress('ximanik307@colimarl.com');

    // Якщо є файли
    if ($file && !empty($file['name'][0])) {
        for ($ct = 0; $ct < count($file['tmp_name']); $ct++) {
            $uploadfile = tempnam(sys_get_temp_dir(), sha1($file['name'][$ct]));
            $filename = $file['name'][$ct];
            if (move_uploaded_file($file['tmp_name'][$ct], $uploadfile)) {
                $mail->addAttachment($uploadfile, $filename);
            }
        }
    }

    // Лист
    $mail->isHTML(true);
    $mail->Subject = $title;
    $mail->Body    = $body;

    if ($mail->send()) {
        echo json_encode(["success" => true, "message" => "Success!"]);
    } else {
        echo json_encode(["success" => false, "message" => "Error!"]);
    }

} catch (Exception $e) {
    echo json_encode(["success" => false, "message" => $mail->ErrorInfo]);
}
