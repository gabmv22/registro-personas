<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$conexion = new mysqli("localhost", "root", "", "mi_backend");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$nombre = "";
$edad = "";
$estado = "Pendiente";
$editar = false;

/* ELIMINAR */
if (isset($_GET["eliminar"])) {
    $id = $_GET["eliminar"];
    $conexion->query("DELETE FROM usuarios WHERE id = $id");
}

/* EDITAR */
if (isset($_GET["editar"]) && $_SERVER["REQUEST_METHOD"] != "POST") {
    $editar = true;
    $id_editar = $_GET["editar"];

    $resultado_editar = $conexion->query("SELECT * FROM usuarios WHERE id = $id_editar");
    $usuario = $resultado_editar->fetch_assoc();

    $nombre = $usuario["nombre"];
    $edad = $usuario["edad"];
}

/* INSERT O UPDATE */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST["nombre"];
    $edad = $_POST["edad"];

    if (!empty($nombre) && !empty($edad)) {

        /* Verificar si el nombre ya existe (solo para INSERT) */
        if (!isset($_POST["id"])) {

            $stmt_check = $conexion->prepare("SELECT id FROM usuarios WHERE nombre=?");
            $stmt_check->bind_param("s", $nombre);
            $stmt_check->execute();
            $stmt_check->store_result();

            if ($stmt_check->num_rows > 0) {
                echo "Ese nombre ya está registrado.";
            } else {
                $stmt = $conexion->prepare("INSERT INTO usuarios (nombre, edad) VALUES (?, ?)");
                $stmt->bind_param("si", $nombre, $edad);
                $stmt->execute();
            }

        } else {
            /* UPDATE */
            $id = $_POST["id"];
            $stmt = $conexion->prepare("UPDATE usuarios SET nombre=?, edad=? WHERE id=?");
            $stmt->bind_param("sii", $nombre, $edad, $id);
            $stmt->execute();
        }
    }
}

$resultado = $conexion->query("SELECT * FROM usuarios");
?>

<!DOCTYPE html>
<html>
<head>
    <title> pagina web 1</title>
    <style>
body {
    font-family: Arial, sans-serif;
    background-color: #f4f6f9;
    padding: 40px;
}

h2 {
    color: #333;
}

form {
    background: white;
    padding: 20px;
    border-radius: 10px;
    width: 300px;
    box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

input {
    width: 100%;
    padding: 8px;
    margin-bottom: 10px;
    border-radius: 5px;
    border: 1px solid #ccc;
}

button {
    width: 100%;
    padding: 10px;
    background-color: #4CAF50;
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}

button:hover {
    background-color: #45a049;
}

table {
    border-collapse: collapse;
    width: 100%;
    background: white;
    box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
}

th, td {
    padding: 12px;
    text-align: center;
}

th {
    background-color: #4CAF50;
    color: white;
}

tr:nth-child(even) {
    background-color: #f2f2f2;
}

a {
    text-decoration: none;
    margin: 0 5px;
    color: #4CAF50;
    font-weight: bold;
}

a:hover {
    color: red;
}
</style>
</head>
<body>

<h2>Escribe tu nombre</h2>

<form method="POST">

<?php if ($editar) { ?>
    <input type="hidden" name="id" value="<?php echo $id_editar; ?>">
<?php } ?>

<input type="text" name="nombre" value="<?php echo $nombre; ?>" placeholder="Tu nombre aquí">
<input type="number" name="edad" value="<?php echo $edad; ?>" placeholder="Tu edad">

<button type="submit">Enviar</button>
</form>

<h2>Usuarios registrados</h2>

<table border="1">
    <tr>
        <th>ID</th>
        <th>Nombre</th>
        <th>Edad</th>
        <th>Acciones</th>
    </tr>

<?php while($fila = $resultado->fetch_assoc()) { ?>
    <tr>
        <td><?php echo $fila["id"]; ?></td>
        <td><?php echo $fila["nombre"]; ?></td>
        <td><?php echo $fila["edad"]; ?></td>
        <td>
            <a href="?editar=<?php echo $fila["id"]; ?>">Editar</a>
            <a href="?eliminar=<?php echo $fila["id"]; ?>">Eliminar</a>
        </td>
    </tr>
<?php } ?>

</table>

</body>
</html>