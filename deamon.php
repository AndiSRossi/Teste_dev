<?php
$key = 0x1234;
$queue = msg_get_queue($key);

try {
    $pdo = new PDO(
        "mysql:host=localhost;dbname=mensagens_db",
        "appuser",
        "sua_senha_segura",
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );

} catch (PDOException $e) {
    echo "Erro ao conectar ao banco de dados: " . $e->getMessage() . PHP_EOL;
    exit(1);
}

echo "Daemon iniciado. Aguardando mensagens..." . PHP_EOL;

while (true) {
    $msg_type = null;
    $msg = null;

    if (msg_receive($queue, 1, $msg_type, 1024, $msg, true, MSG_IPC_NOWAIT)) {
        $mensagem = trim($msg['mensagem']);

        try {
            $stmt = $pdo->prepare("INSERT INTO mensagens (mensagem) VALUES (:mensagem)");
            $stmt->execute([':mensagem' => $mensagem]);
            $status = 'ok';
            echo "Mensagem salva: {$mensagem}" . PHP_EOL;
        } catch (PDOException $e) {
            $status = 'fail';
            echo "Erro ao salvar mensagem: " . $e->getMessage() . PHP_EOL;
        }

        msg_send($queue, 2, ['status' => $status]);
    }

    usleep(500000);
}
