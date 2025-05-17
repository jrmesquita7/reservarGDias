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
    $id_item = $_POST['id_item'];
    $data = $_POST['data'];
    $horarios_selecionados = $_POST['id_horario'] ?? [];
    $obs = $_POST['observacoes'];

    if ($data < $data_minima || $data > $data_maxima) {
        $mensagem = "A data da reserva deve ser hoje ou até amanhã.";
    } elseif (!is_array($horarios_selecionados) || count($horarios_selecionados) === 0) {
        $mensagem = "Selecione pelo menos um horário.";
    } else {
        $conflitos = [];
        foreach ($horarios_selecionados as $id_horario) {
            $check = $pdo->prepare("SELECT r.*, u.nome as usuario FROM reservas r 
                                    JOIN usuarios u ON u.id = r.id_usuario
                                    WHERE r.id_item = ? AND r.id_horario = ? AND r.data = ?");
            $check->execute([$id_item, $id_horario, $data]);
            if ($existe = $check->fetch()) {
                $desc = $pdo->query("SELECT descricao FROM horarios WHERE id = " . intval($id_horario))->fetchColumn();
                $conflitos[] = [
                    'horario' => $desc,
                    'usuario' => $existe['usuario']
                ];
            }
        }

        if (count($conflitos) > 0) {
            $mensagem = "❌ Não foi possível concluir a reserva. Os seguintes horários já estão reservados:<br><ul>";
            foreach ($conflitos as $c) {
                $mensagem .= "<li><strong>{$c['horario']}</strong> por <strong>{$c['usuario']}</strong></li>";
            }
            $mensagem .= "</ul>";
        } else {
            $sucesso = 0;
            foreach ($horarios_selecionados as $id_horario) {
                $stmt = $pdo->prepare("INSERT INTO reservas (id_usuario, id_item, id_horario, data, observacoes) VALUES (?, ?, ?, ?, ?)");
                try {
                    $stmt->execute([$_SESSION['usuario_id'], $id_item, $id_horario, $data, $obs]);
                    $sucesso++;
                } catch (PDOException $e) {
                    $mensagem = "Erro ao reservar: " . $e->getMessage();
                    break;
                }
            }
            if ($sucesso > 0) {
                $mensagem = "✅ Reserva realizada com sucesso para $sucesso horário(s).";
            }
        }
    }
}

$itens = $pdo->query("SELECT * FROM itens WHERE status = 'Disponível' ORDER BY nome")->fetchAll();
$horarios = $pdo->query("SELECT * FROM horarios")->fetchAll();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nova Reserva</title>
    <link rel="stylesheet" href="../assets/reserva.css">
    <link rel="stylesheet" href="../assets/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<?php include '../includes/menu.php'; ?>
    <div class="container">
        <h2>Nova Reserva</h2>

        <?php if ($mensagem): ?>
            <div class="alert <?= strpos($mensagem, '❌') !== false ? 'error' : 'success' ?>"><?= $mensagem ?></div>
        <?php endif; ?>

        <form method="POST">
            <label for="id_item">Item:</label>
            <select name="id_item" id="id_item" required>
                <?php foreach ($itens as $item): ?>
                    <option value="<?= $item['id'] ?>"><?= htmlspecialchars($item['nome']) ?></option>
                <?php endforeach; ?>
            </select>

            <label for="data">Data:</label>
            <input type="date" name="data" id="data" required min="<?= $data_minima ?>" max="<?= $data_maxima ?>">

            <label>Horários:</label>
            <div class="checkboxes-container">
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
    </div>
</body>



</html>