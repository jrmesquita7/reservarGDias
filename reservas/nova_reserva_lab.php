<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header('Location: ../auth/login.php');
    exit;
}
require_once '../config/db.php';

$mensagem = '';
$data_minima = date('Y-m-d');
$data_maxima = date('Y-m-d', strtotime('+1 day'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_laboratorio = $_POST['id_laboratorio'];
    $data = $_POST['data'];
    $horarios = $_POST['id_horario'];
    $obs = $_POST['observacoes'];

    if ($data < $data_minima || $data > $data_maxima) {
        $mensagem = "A data da reserva deve ser hoje ou até amanhã.";
    } elseif (!is_array($horarios) || count($horarios) === 0) {
        $mensagem = "Selecione pelo menos um horário.";
    } else {
        $conflitos = [];
        foreach ($horarios as $id_horario) {
            $check = $pdo->prepare("SELECT r.*, u.nome as usuario FROM reservas_laboratorios r 
                                    JOIN usuarios u ON u.id = r.id_usuario
                                    WHERE r.id_laboratorio = ? AND r.id_horario = ? AND r.data = ?");
            $check->execute([$id_laboratorio, $id_horario, $data]);
            if ($existe = $check->fetch()) {
                $desc = $pdo->query("SELECT descricao FROM horarios WHERE id = " . intval($id_horario))->fetchColumn();
                $conflitos[] = [
                    'horario' => $desc,
                    'usuario' => $existe['usuario']
                ];
            }
        }

        if (count($conflitos) > 0) {
            $mensagem = "❌ Conflito nos horários:<br><ul>";
            foreach ($conflitos as $c) {
                $mensagem .= "<li><strong>{$c['horario']}</strong> já reservado por <strong>{$c['usuario']}</strong></li>";
            }
            $mensagem .= "</ul>";
        } else {
            foreach ($horarios as $id_horario) {
                $stmt = $pdo->prepare("INSERT INTO reservas_laboratorios (id_usuario, id_laboratorio, id_horario, data, observacoes) VALUES (?, ?, ?, ?, ?)");
                $stmt->execute([$_SESSION['usuario_id'], $id_laboratorio, $id_horario, $data, $obs]);
            }
            $mensagem = "✅ Reserva realizada com sucesso!";
        }
    }
}

$laboratorios = $pdo->query("SELECT * FROM laboratorios WHERE status = 'Disponível' ORDER BY nome")->fetchAll();
$horarios = $pdo->query("SELECT * FROM horarios ORDER BY id")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Reserva - Laboratórios</title>
    <link rel="stylesheet" href="../assets/reserva.css">
    <link rel="stylesheet" href="../assets/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>

<body>
<?php include '../includes/menu.php'; ?>
    <div class="container">
        <h2>Nova Reserva de Laboratório</h2>

        <?php if ($mensagem): ?>
            <div class="alert <?= strpos($mensagem, '❌') !== false ? 'error' : 'success' ?>">
                <?= $mensagem ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <label for="id_laboratorio">Laboratório:</label>
            <select name="id_laboratorio" id="id_laboratorio" required>
                <?php foreach ($laboratorios as $lab): ?>
                    <option value="<?= $lab['id'] ?>"><?= htmlspecialchars($lab['nome']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="data">Data:</label>
            <input type="date" name="data" id="data" required min="<?= $data_minima ?>" max="<?= $data_maxima ?>">

            <label for="horarios">Horários:</label>
            <div class="checkboxes-container" id="horarios">
                <?php foreach ($horarios as $h): ?>
                    <div class="checkbox-option">
                        <input type="checkbox" name="id_horario[]" value="<?= $h['id'] ?>" id="horario_<?= $h['id'] ?>">
                        <label for="horario_<?= $h['id'] ?>"><?= htmlspecialchars($h['descricao']) ?></label>
                    </div>
                <?php endforeach; ?>
            </div>

            <label for="observacoes">Observações:</label>
            <textarea name="observacoes" id="observacoes" rows="4"></textarea>

            <button type="submit">Reservar</button>
        </form>

        <a href="../dashboard_laboratorios.php" class="back-link">⬅ Voltar para o painel</a>
    </div>
</body>


</html>
