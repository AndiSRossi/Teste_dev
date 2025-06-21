<?php
$statusMsg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensagem = trim($_POST['mensagem'] ?? '');

    if ($mensagem === '') {
        $statusMsg = '<div class="alert alert-warning">Por favor, insira uma mensagem válida.</div>';
    } else {
        $key = 0x1234;
        $queue = msg_get_queue($key);
        $data = ['mensagem' => $mensagem];

        if (!msg_send($queue, 1, $data)) {
            $statusMsg = '<div class="alert alert-danger">Erro ao enviar mensagem para o daemon.</div>';
        } else {
            $msg_type = null;
            $msg = null;

            $timeout = 5;
            $startTime = time();
            $received = false;

            while ((time() - $startTime) < $timeout) {
                if (msg_receive($queue, 2, $msg_type, 1024, $msg, true, MSG_IPC_NOWAIT)) {
                    $received = true;
                    break;
                }
                usleep(100000);
            }

            if ($received) {
                if (isset($msg['status']) && $msg['status'] === 'ok') {
                    $statusMsg = '<div class="alert alert-success">Mensagem salva com sucesso!</div>';
                } else {
                    $statusMsg = '<div class="alert alert-danger">Falha ao salvar no banco de dados.</div>';
                }
            } else {
                $statusMsg = '<div class="alert alert-danger">Timeout ao receber resposta do daemon.</div>';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8" />
    <title>Envio de Mensagem</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>

<body class="bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow rounded-4">
                    <div class="card-body">
                        <h3 class="card-title mb-4 text-center">Enviar Mensagem</h3>

                        <?= $statusMsg ?>

                        <form method="post" action="">
                            <div class="mb-3">
                                <label for="mensagem" class="form-label">Mensagem</label>
                                <input type="text" class="form-control" id="mensagem" name="mensagem" required autofocus
                                    value="<?= htmlspecialchars($_POST['mensagem'] ?? '') ?>" />
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Enviar</button>
                            </div>
                        </form>
                    </div>
                </div>
                <p class="mt-3 text-muted text-center small">
                    O daemon precisa estar rodando em segundo plano para funcionar corretamente.
                </p>
                <p class="text-center mt-2">
                    <a href="/">Voltar ao formulário inicial</a>
                </p>
            </div>
        </div>
    </div>
</body>

</html>