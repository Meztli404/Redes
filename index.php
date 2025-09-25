<?php
function calcularSubredes($ip, $numSubredes) {
    $resultados = [];

    // Convertir la IP a entero
    $ip_long = ip2long($ip);

    // Clase por defecto (A, B, C) para máscara base
    $octetos = explode(".", $ip);
    if ($octetos[0] <= 126) $bitsBase = 8;
    elseif ($octetos[0] <= 191) $bitsBase = 16;
    else $bitsBase = 24;

    $mascaraBase = $bitsBase;

    // Calcular bits necesarios para subredes
    $bitsSubred = ceil(log($numSubredes, 2));
    $nuevaMascara = $mascaraBase + $bitsSubred;

    $totalSubredes = pow(2, $bitsSubred);
    $hostsPorSubred = pow(2, (32 - $nuevaMascara)) - 2;
    $incremento = pow(2, (32 - $nuevaMascara));

    for ($i = 0; $i < $totalSubredes; $i++) {
        $network = ($ip_long & (-1 << (32 - $mascaraBase))) + ($i * $incremento);
        $broadcast = $network + $incremento - 1;
        $hostMin = $network + 1;
        $hostMax = $broadcast - 1;

        $resultados[] = [
            "subred" => $i + 1,
            "direccion_red" => long2ip($network),
            "rango_host" => long2ip($hostMin) . " - " . long2ip($hostMax),
            "broadcast" => long2ip($broadcast),
            "mascara" => long2ip(-1 << (32 - $nuevaMascara))
        ];
    }

    return $resultados;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $ip = $_POST["ip"];
    $subredes = intval($_POST["subredes"]);
    $resultado = calcularSubredes($ip, $subredes);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Calculadora de Subredes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f9;
            padding: 20px;
        }
        h2 {
            color: #333;
        }
        form {
            margin-bottom: 20px;
            padding: 15px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0px 2px 5px rgba(0,0,0,0.2);
        }
        input, button {
            padding: 8px;
            margin: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 8px;
            text-align: center;
        }
        th {
            background: #333;
            color: white;
        }
    </style>
    <script>
        function validarFormulario() {
            let ip = document.getElementById("ip").value;
            let subredes = document.getElementById("subredes").value;

            let ipRegex = /^(\d{1,3}\.){3}\d{1,3}$/;
            if (!ipRegex.test(ip)) {
                alert("Ingrese una dirección IP válida");
                return false;
            }
            if (isNaN(subredes) || subredes <= 0) {
                alert("Ingrese un número válido de subredes");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <h2>Calculadora de Subredes</h2>
    <form method="POST" onsubmit="return validarFormulario()">
        <label>Dirección IP:</label>
        <input type="text" name="ip" id="ip" placeholder="192.168.1.0" required>
        <label>Cantidad de Subredes:</label>
        <input type="number" name="subredes" id="subredes" required>
        <button type="submit">Calcular</button>
    </form>

    <?php if (!empty($resultado)): ?>
    <table>
        <tr>
            <th>Subred</th>
            <th>Dirección de Red</th>
            <th>Rango de Hosts</th>
            <th>Broadcast</th>
            <th>Máscara</th>
        </tr>
        <?php foreach ($resultado as $fila): ?>
        <tr>
            <td><?= $fila["subred"] ?></td>
            <td><?= $fila["direccion_red"] ?></td>
            <td><?= $fila["rango_host"] ?></td>
            <td><?= $fila["broadcast"] ?></td>
            <td><?= $fila["mascara"] ?></td>
        </tr>
        <?php endforeach; ?>
    </table>
    <?php endif; ?>
</body>
</html>


