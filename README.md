
# Projeto Mensagens IPC com PHP e Daemon

## Descrição

Este projeto demonstra o uso de comunicação interprocessos (IPC) via filas de mensagens (message queue) em PHP para enviar dados de uma interface web para um script daemon em background, que grava mensagens em um banco de dados MySQL/MariaDB e retorna status para a interface.

O fluxo é:

1. A interface web (`index.html` e `form.php`) recebe uma mensagem do usuário.
2. A mensagem é enviada via IPC (função `msg_send`) para um daemon PHP que roda em loop infinito.
3. O daemon recebe a mensagem, insere no banco de dados e retorna status via IPC.
4. A interface recebe o status e exibe feedback ao usuário.

---

## Arquivos principais

- `index.html` — formulário simples para envio da mensagem.
- `form.php` — processa o POST do formulário, envia mensagem via IPC, aguarda resposta com timeout, exibe resultado.
- `daemon.php` — daemon PHP rodando em loop infinito, escutando fila IPC e salvando mensagens no banco.
- `README.md` — este arquivo.

---

## Requisitos

- PHP com suporte a IPC (extensão sysvmsg habilitada).
- MySQL ou MariaDB rodando localmente.
- Banco de dados criado com a tabela para armazenar as mensagens.
- WSL (Windows Subsystem for Linux) ou ambiente Linux/Unix para rodar o daemon e o servidor embutido PHP.

---

## Configuração do banco de dados

1. Crie o banco de dados `mensagens_db`:

```sql
CREATE DATABASE mensagens_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. Crie a tabela para mensagens:

```sql
CREATE TABLE mensagens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mensagem TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

3. Crie um usuário para o app (exemplo):

```sql
CREATE USER 'appuser'@'localhost' IDENTIFIED BY 'sua_senha_segura';
GRANT ALL PRIVILEGES ON mensagens_db.* TO 'appuser'@'localhost';
FLUSH PRIVILEGES;
```

---

## Como rodar o projeto

### 1. Iniciar o banco de dados

No WSL, rode:

```bash
sudo service mysql start
```

ou

```bash
sudo systemctl start mysql
```

Confirme que o banco está rodando.

---

### 2. Rodar o daemon PHP

Abra um terminal no WSL, navegue até a pasta do projeto (onde está o `daemon.php`):

```bash
cd /mnt/c/xampp/htdocs/Teste_dev
```

Rode o daemon em background para ele ficar escutando as mensagens:

```bash
nohup php daemon.php > daemon.log 2>&1 &
```

Você pode conferir o log em `daemon.log`.

---

### 3. Rodar o servidor PHP embutido para testes

Ainda na pasta do projeto, rode:

```bash
php -S localhost:8000
```

O servidor estará rodando em http://localhost:8000

---

### 4. Usar a aplicação

- Acesse no navegador: http://localhost:8000/
- Preencha o formulário e envie uma mensagem.
- O `form.php` enviará a mensagem para o daemon via IPC.
- O daemon salvará no banco e responderá o status.
- O resultado será exibido na tela.

---

## Observações importantes

- O daemon deve estar sempre rodando para processar as mensagens.
- O timeout no `form.php` evita que o navegador fique travado caso o daemon não responda.
- A chave IPC (`0x1234`) deve ser a mesma no daemon e no form.
- Para ambiente produtivo, configure senha e credenciais de forma segura (variáveis ambiente, .env, etc).
- PHP deve ter a extensão `sysvmsg` habilitada para funcionar IPC (`msg_send` e `msg_receive`).

---

## Como parar o daemon

Para encerrar o daemon em background, encontre o processo:

```bash
ps aux | grep php
```

E mate o processo correspondente:

```bash
kill PID
```

---

## Referências

- [msg_send - PHP Manual](https://www.php.net/manual/en/function.msg-send.php)
- [msg_receive - PHP Manual](https://www.php.net/manual/en/function.msg-receive.php)
- [Servidor embutido PHP](https://www.php.net/manual/en/features.commandline.webserver.php)

---

## Contato

Dúvidas ou sugestões, entre em contato.

---
