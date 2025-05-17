<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../config/db.php';

// Cancelamento
if (isset($_GET['cancelar']) && is_numeric($_GET['cancelar'])) {
    $id = intval($_GET['cancelar']);
    $pdo->prepare("DELETE FROM reservas_laboratorios WHERE id = ? AND id_usuario = ?")
        ->execute([$id, $_SESSION['usuario_id']]);
    header("Location: listar_reservas_laboratorio.php");
    exit;
}

// Ação de entrega de laboratório
if (isset($_POST['entregar'])) {
    // Busca os laboratórios reservados para hoje e ainda não concluídos
    $stmt = $pdo->prepare("
        SELECT DISTINCT id_laboratorio 
        FROM reservas_laboratorios 
        WHERE id_usuario = ? AND data = CURDATE() AND concluida = 0
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $laboratorios = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Insere log para cada laboratório reservado
    foreach ($laboratorios as $id_laboratorio) {
        $stmtLog = $pdo->prepare("
            INSERT INTO logs_entrega (id_usuario, tipo, id_item, data_hora) 
            VALUES (?, 'laboratorio', ?, NOW())
        ");
        $stmtLog->execute([$_SESSION['usuario_id'], $id_laboratorio]);
    }

    // Marca as reservas como concluídas
    $stmtUpdate = $pdo->prepare("
        UPDATE reservas_laboratorios 
        SET concluida = 1 
        WHERE id_usuario = ? AND data = CURDATE() AND concluida = 0
    ");
    $stmtUpdate->execute([$_SESSION['usuario_id']]);

    // Redireciona após a entrega
    header("Location: listar_reservas_laboratorio.php");
    exit;
}


// Buscar reservas do usuário logado
$stmt = $pdo->prepare("
    SELECT r.*, l.nome AS laboratorio, h.descricao AS horario
    FROM reservas_laboratorios r
    JOIN laboratorios l ON l.id = r.id_laboratorio
    JOIN horarios h ON h.id = r.id_horario
    WHERE r.id_usuario = ? 
    ORDER BY r.data DESC, h.id ASC
");
$stmt->execute([$_SESSION['usuario_id']]);
$reservas = $stmt->fetchAll();

// MODIFICADO: Verificar se há reservas não concluídas APENAS PARA O DIA ATUAL
$check = $pdo->prepare("SELECT COUNT(*) FROM reservas_laboratorios WHERE id_usuario = ? AND data = CURDATE() AND concluida = 0");
$check->execute([$_SESSION['usuario_id']]);
$mostrarBotaoEntregar = $check->fetchColumn() > 0;
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Reservas - Laboratórios</title>
    <link rel="stylesheet" href="../assets/minhas_reservas.css">
    <link rel="stylesheet" href="../assets/style.css">
    <link rel="stylesheet" href="../assets/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .btn-entregar {
            background-color: #27ae60;
            color: white;
            border: none;
            padding: 10px 20px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .btn-entregar:hover {
            background-color: #219150;
        }
    </style>
</head>

<body>
<?php include '../includes/menu.php'; ?>
<div class="container">
    <h2>Minhas Reservas de Laboratórios</h2>

    <?php if ($mostrarBotaoEntregar): ?>
        <form method="POST">
            <button type="submit" name="entregar" class="btn-entregar">✔️ Entregar</button>
        </form>
    <?php endif; ?>

    <?php if (count($reservas) === 0): ?>
        <p>Nenhuma reserva encontrada.</p>
    <?php else: ?>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Data</th>
                        <th>Horário</th>
                        <th>Laboratório</th>
                        <th>Observações</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $r): ?>
                        <tr>
                            <td data-label="Data"><?= date('d/m/Y', strtotime($r['data'])) ?></td>
                            <td data-label="Horário"><?= $r['horario'] ?></td>
                            <td data-label="Laboratório"><?= $r['laboratorio'] ?></td>
                            <td data-label="Observações"><?= nl2br(htmlspecialchars($r['observacoes'])) ?></td>
                            <td data-label="Ações">
                                <?php if ($r['concluida']): ?>
                                    <em>Concluída</em>
                                <?php elseif ($r['data'] >= date('Y-m-d')): ?>
                                    <a href="?cancelar=<?= $r['id'] ?>" class="cancelar-btn" onclick="return confirm('Deseja realmente cancelar esta reserva?')">
                                        <i class="fa-solid fa-xmark"></i> Cancelar
                                    </a>
                                <?php else: ?>
                                    <em>Expirada</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <br>
    <a href="../dashboard_laboratorios.php" class="back-link">⬅ Voltar para o painel</a>
</div>
</body>

</html>