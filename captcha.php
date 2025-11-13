<?php
session_start();

// Tamaño de la imagen
$width = 120;
$height = 40;
$image = imagecreatetruecolor($width, $height);

// Colores
$bg = imagecolorallocate($image, 255, 255, 255); // fondo blanco
$txt_color = imagecolorallocate($image, 0, 0, 0); // texto negro
$line_color = imagecolorallocate($image, 64, 64, 64); // líneas grises

// Rellenar fondo
imagefilledrectangle($image, 0, 0, $width, $height, $bg);

// Generar código aleatorio de 5 caracteres
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$captcha_code = '';
for ($i = 0; $i < 5; $i++) {
    $captcha_code .= $chars[rand(0, strlen($chars)-1)];
}

// Guardar el captcha en sesión
//$captcha_code: código aleatorio que se genera
$_SESSION['captcha'] = $captcha_code;

// Dibujar líneas de ruido
//rand() % $height genera un número aleatorio
for ($i = 0; $i < 5; $i++) {
    imageline($image, 0, rand() % $height, $width, rand() % $height, $line_color);
}

// Dibujar el texto usando la fuente interna de GD
//$image: la imagen creada con imagecreatetruecolor
//5: tamaño de la fuente (1 a 5, 5 es más grande).
//10, 10: posición X y Y donde empieza a dibujar el texto.
//$captcha_code: el texto que queremos mostrar (el código aleatorio).
//$txt_color: el color del texto
imagestring($image, 5, 10, 10, $captcha_code, $txt_color);


// Enviar la imagen al navegador
header("Content-type: image/png");
imagepng($image);
imagedestroy($image);
?>
