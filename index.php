<!DOCTYPE html>
<html lang="es">
<head>
 <meta charset="UTF-8">
 <meta name="viewport" content="width=device-width, initial-scale=1.0">
 <title>CLOUDSWITCH</title>
 <link rel="stylesheet" type="text/css" href="dkshfowihe4owight.css">
</head>
<body>
<center>
 <h3>ALMACENADOR DE ARCHIVOS CLOUDSWITCH</h3>
 <form method="POST" enctype="multipart/form-data">
<input type="file" name="files[]" multiple required class="file-input" id="file-input">
<button type="button" class="custom-button" id="select-files">Seleccionar archivos</button>
<p>
<input class="subbutton" type="submit" value="SUBIR">
</p>
 </form>

 <table>
<thead>
<tr>
 <th>Nombre del archivo</th>
 <th>URL acortada</th>
 <th>Opciones</th>
</tr>
</thead>
<tbody>
<?php
session_start();

if (!isset($_SESSION['user_id'])) {
$_SESSION['user_id'] = uniqid('', true);
}

$baseFolder = 'uploads'; 
$maxFolders = 10;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
if (isset($_FILES['files'])) {
$archivos = $_FILES['files'];
$totalArchivos = count($archivos['name']);
if ($totalArchivos > 5) {
echo "Solo se permite subir un máximo de 5 archivos.";
exit();
}

$maxFileSize = 90 * 1024 * 1024; // 90 MB
if (!file_exists($baseFolder)) {
mkdir($baseFolder, 0755, true);
}

$folders = glob("$baseFolder/folder_*");
if (count($folders) >= $maxFolders) {
$oldestFolder = array_shift($folders);
array_map('unlink', glob("$oldestFolder/*.*")); 
rmdir($oldestFolder); 
}

do {
$newFolder = "$baseFolder/folder_" . rand(10000, 99999);
} while (file_exists($newFolder));
mkdir($newFolder, 0755, true);

$archivosNuevos = [];
foreach ($archivos['name'] as $key => $nombreArchivo) {
$rutaCompletaFinal = $newFolder . '/' . basename($nombreArchivo);

if ($archivos['size'][$key] > $maxFileSize) {
echo "El archivo $nombreArchivo excede el límite de tamaño de 90 MB.<br>";
continue; 
}

$contenido = file_get_contents($archivos['tmp_name'][$key]);
if (preg_match('/https:\/\/static\.xx\.|tiktok|gmail|facebook|instagram/i', $contenido)) {
echo "Error: El archivo $nombreArchivo contiene contenido no permitido.<br>";
continue;
}

if (move_uploaded_file($archivos['tmp_name'][$key], $rutaCompletaFinal)) {
$archivosNuevos[] = $rutaCompletaFinal;
} else {
echo "ERROR al subir el archivo $nombreArchivo.<br>";
}
}

if ($archivosNuevos) {
foreach ($archivosNuevos as $archivo) {
$url = htmlspecialchars($archivo);
$nfile = basename($url);
$url = "http://cloudswitch.unaux.com/$url"; 

$apiUrl = 'https://acut0.onrender.com/acortar';
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['url' => $url]));
$response = curl_exec($ch);
curl_close($ch);

$responseData = json_decode($response, true);
if (isset($responseData['acortada'])) {
$acortada = $responseData['acortada'];
echo "<tr>
<td>$nfile</td>
<td>$acortada</td>
<td>
<button class='opc' onclick='irAlSitio(\"$acortada\")'>Ir al sitio web</button><br>
<button class='opc' onclick='copiarAlPortapapeles(\"$acortada\")'>Copiar en portapapeles</button>
</td>
</tr>";
} else {
$error = $responseData['error'] ?? 'Error desconocido';
echo "<tr><td>$nfile</td><td>Error para $url: $error</td><td></td></tr>";
}
}
}
}
}
?>
</tbody>
 </table>
</center>

<script>
document.getElementById('select-files').onclick = function() {
document.getElementById('file-input').click();
};

function irAlSitio(url) {
window.open(url, '_blank');
}

function copiarAlPortapapeles(url) {
const textArea = document.createElement("textarea");
textArea.value = url;
document.body.appendChild(textArea);
textArea.select(); 
document.execCommand("copy"); 
document.body.removeChild(textArea); 
alert('URL COPIADA EXITOSAMENTE'); 
}
</script>

</body>
</html>
