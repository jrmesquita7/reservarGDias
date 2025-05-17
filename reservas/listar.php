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
    $pdo->prepare("DELETE FROM reservas WHERE id = ? AND id_usuario = ?")
        ->execute([$id, $_SESSION['usuario_id']]);
    header("Location: listar.php");
    exit;
}

// Ação de entrega de equipamento
if (isset($_POST['entregar'])) {
    // Busca os itens reservados para hoje e ainda não concluídos
    $stmt = $pdo->prepare("
        SELECT DISTINCT id_item 
        FROM reservas 
        WHERE id_usuario = ? AND data = CURDATE() AND concluida = 0
    ");
    $stmt->execute([$_SESSION['usuario_id']]);
    $itens = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Insere log para cada item reservado
    foreach ($itens as $id_item) {
        $stmtLog = $pdo->prepare("
            INSERT INTO logs_entrega (id_usuario, tipo, id_item, data_hora) 
            VALUES (?, 'equipamento', ?, NOW())
        ");
        $stmtLog->execute([$_SESSION['usuario_id'], $id_item]);
    }

    // Marca as reservas como concluídas
    $stmtUpdate = $pdo->prepare("
        UPDATE reservas 
        SET concluida = 1 
        WHERE id_usuario = ? AND data = CURDATE() AND concluida = 0
    ");
    $stmtUpdate->execute([$_SESSION['usuario_id']]);

    // Redireciona após entrega
    header("Location: listar.php");
    exit;
}


// Buscar reservas do usuário logado
$stmt = $pdo->prepare("
    SELECT r.id, r.data, r.status, r.observacoes, r.concluida,
           i.nome AS nome_item,
           h.descricao AS horario
    FROM reservas r
    JOIN itens i ON r.id_item = i.id
    JOIN horarios h ON r.id_horario = h.id
    WHERE r.id_usuario = ?
    ORDER BY r.data DESC, h.id ASC
");
$stmt->execute([$_SESSION['usuario_id']]);
$reservas = $stmt->fetchAll();

// MODIFICADO: Verificar se há reservas não concluídas APENAS PARA O DIA ATUAL
$check = $pdo->prepare("SELECT COUNT(*) FROM reservas WHERE id_usuario = ? AND data = CURDATE() AND concluida = 0");
$check->execute([$_SESSION['usuario_id']]);
$mostrarBotaoEntregar = $check->fetchColumn() > 0;
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Reservas</title>
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
    <h2>Minhas Reservas de Equipamentos</h2>

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
                        <th>Item</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reservas as $r): ?>
                        <tr>
                            <td data-label="Data"><?= date('d/m/Y', strtotime($r['data'])) ?></td>
                            <td data-label="Horário"><?= $r['horario'] ?></td>
                            <td data-label="Item"><?= $r['nome_item'] ?></td>
                            <td data-label="Status"><?= $r['status'] ?></td>
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
    <a href="../dashboard.php" class="back-link">⬅ Voltar para o painel</a>
</div>
</body>
</html>