<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: auth/login.php');
    exit;
}
require_once 'config/db.php';

$data_atual = isset($_GET['data']) ? $_GET['data'] : date('Y-m-d');
$data_anterior = date('Y-m-d', strtotime($data_atual . ' -1 day'));
$data_proxima  = date('Y-m-d', strtotime($data_atual . ' +1 day'));

$laboratorios = $pdo->query("SELECT * FROM laboratorios ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$horarios = $pdo->query("SELECT * FROM horarios ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT r.*, u.nome AS nome_usuario, h.descricao AS horario_desc, l.nome AS nome_laboratorio 
    FROM reservas_laboratorios r
    JOIN usuarios u ON u.id = r.id_usuario
    JOIN horarios h ON h.id = r.id_horario
    JOIN laboratorios l ON l.id = r.id_laboratorio
    WHERE r.data = ?
");
$stmt->execute([$data_atual]);
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

$reservas_map = [];
foreach ($reservas as $res) {
    $reservas_map[$res['id_horario']][$res['id_laboratorio']] = $res;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel de Reservas de Laboratórios - <?= date('d/m/Y', strtotime($data_atual)) ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            font-family: "Segoe UI", sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }

        .header {
            background-color: #2c3e50;
            color: #ecf0f1;
            padding: 15px 25px;
            font-size: 20px;
            font-weight: bold;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }

        .header a {
            color: #ecf0f1;
            margin-left: 15px;
            font-size: 14px;
            text-decoration: none;
        }

        .container {
            padding: 90px 20px 30px;
            max-width: 1200px;
            margin: 0 auto;
        }

        h2, h3 {
            text-align: center;
            color: #2c3e50;
        }

        .navegacao {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .navegacao a {
            color: #2980b9;
            text-decoration: none;
            font-weight: bold;
        }

        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            padding: 20px;
        }

        .card h4 {
            margin: 0 0 10px;
            font-size: 18px;
            color: #34495e;
        }

        .reserva-item {
            display: flex;
            justify-content: space-between;
            padding: 6px 0;
            border-bottom: 1px solid #eee;
        }

        .reserva-disponivel {
            color: #27ae60;
            font-weight: bold;
        }

        .reserva-ocupado {
            color: #c0392b;
            font-weight: bold;
        }

        @media (max-width: 600px) {
            .navegacao {
                flex-direction: column;
                gap: 10px;
            }
        }
    </style>
</head>
<body>

<?php include 'includes/menu.php'; ?>

<div class="container">
    <h2>Olá, <?= $_SESSION['usuario_nome'] ?>!</h2>

    <div class="navegacao">
        <a href="?data=<?= $data_anterior ?>"><i class="fa-solid fa-chevron-left"></i> Dia Anterior</a>
        <h3>Reservas para <?= date('d/m/Y', strtotime($data_atual)) ?></h3>
        <a href="?data=<?= $data_proxima ?>">Próximo Dia <i class="fa-solid fa-chevron-right"></i></a>
    </div>

    <div class="cards-grid">
        <?php foreach ($laboratorios as $lab): ?>
            <div class="card">
                <h4><?= htmlspecialchars($lab['nome']) ?></h4>
                <?php foreach ($horarios as $horario): ?>
                    <?php 
                        $res = $reservas_map[$horario['id']][$lab['id']] ?? null; 
                        $classe = $res ? 'reserva-ocupado' : 'reserva-disponivel';
                        $icone = $res ? '<i class="fa-solid fa-lock"></i>' : '<i class="fa-solid fa-check"></i>';
                        $texto = $res ? $res['nome_usuario'] : 'Disponível';
                    ?>
                    <div class="reserva-item">
                        <span><?= $horario['descricao'] ?></span>
                        <span class="<?= $classe ?>"><?= $icone ?> <?= htmlspecialchars($texto) ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>

</body>
</html>
