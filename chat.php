<?php
session_start();
require_once "class/chat.php";

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$chat = new Chat();
$username = $_SESSION['username'];


if (isset($_GET['action']) && $_GET['action'] === 'fetch') {
    $idthread = (int)$_GET['idthread'];
    $lastId = (int)$_GET['lastId'];

    $chats = $chat->getChats($idthread, $lastId);

    $result = [];
    foreach ($chats as $c) {
        $result[] = [
            "idchat" => $c['idchat'],
            "isi" => htmlspecialchars($c['isi']),
            "nama" => $c['nama'],
            "waktu" => date('d M Y H:i', strtotime($c['tanggal_pembuatan'])),
            "is_me" => $c['username_pembuat'] === $username
        ];
    }

    echo json_encode($result);
    exit;
}

if (isset($_POST['action']) && $_POST['action'] === 'send') {
    $idthread = (int)$_POST['idthread'];
    $isi = trim($_POST['isi']);

    if ($isi !== '') {
        $chat->insertChat($idthread, $username, $isi);
    }
    exit;
}


if (!isset($_GET['idthread'])) {
    die("Thread tidak valid");
}

$idthread = (int)$_GET['idthread'];

if (!$chat->canAccessThread($username, $idthread)) {
    die("Anda tidak memiliki akses ke thread ini.");
}


require_once "class/thread.php";
$threadObj = new Thread();
$threadData = $threadObj->getThreadById($idthread);
$idgrup = $threadData ? $threadData['idgrup'] : 0;
?>
<!DOCTYPE html>
<html>
<head>
<title>Chat Thread</title>
<?php include "inc/head.php"; ?>
<style>
:root {
    --chat-bg-me: #dcf8c6;
    --chat-bg-other: #ffffff;
    --chat-border: #ddd;
    --chat-text: #333;
    --chat-meta: #666;
}

@media (prefers-color-scheme: dark) {
    :root {
        --chat-bg-me: #005c4b;
        --chat-bg-other: #202c33;
        --chat-border: #2a3943;
        --chat-text: #e9edef;
        --chat-meta: #8696a0;
    }
}

body {
    margin: 0;
    padding: 0;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Arial, sans-serif;
    background: var(--panel);
    color: var(--text);
    height: 100vh;
    display: flex;
    flex-direction: column;
}

.chat-container {
    display: flex;
    flex-direction: column;
    height: 100vh;
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    border-left: 1px solid var(--chat-border);
    border-right: 1px solid var(--chat-border);
    background: var(--bg);
}

.chat-header {
    background: var(--panel);
    padding: 15px 20px;
    border-bottom: 1px solid var(--chat-border);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.chat-header h3 {
    margin: 0;
    font-size: 18px;
}

.chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 20px;
    background: var(--bg);
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.chat {
    display: flex;
    flex-direction: column;
    max-width: 70%;
    padding: 8px 12px;
    border-radius: 8px;
    word-wrap: break-word;
    animation: fadeIn 0.3s;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to { opacity: 1; transform: translateY(0); }
}

.chat.me {
    background: var(--chat-bg-me);
    align-self: flex-end;
    border-bottom-right-radius: 4px;
}

.chat.other {
    background: var(--chat-bg-other);
    align-self: flex-start;
    border-bottom-left-radius: 4px;
    border: 1px solid var(--chat-border);
}

.chat-name {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 4px;
    color: var(--chat-text);
}

.chat-text {
    font-size: 14px;
    line-height: 1.4;
    color: var(--chat-text);
    margin-bottom: 4px;
}

.chat-meta {
    font-size: 11px;
    color: var(--chat-meta);
    text-align: right;
}

.chat-input-container {
    background: var(--panel);
    padding: 15px 20px;
    border-top: 1px solid var(--chat-border);
    display: flex;
    gap: 10px;
    align-items: flex-end;
}

.chat-input-container textarea {
    flex: 1;
    padding: 10px 15px;
    border: 1px solid var(--chat-border);
    border-radius: 20px;
    resize: none;
    font-family: inherit;
    font-size: 14px;
    background: var(--bg);
    color: var(--text);
    min-height: 20px;
    max-height: 100px;
}

.chat-input-container button {
    padding: 10px 20px;
    background: var(--accent);
    color: white;
    border: none;
    border-radius: 20px;
    cursor: pointer;
    font-size: 14px;
    font-weight: 500;
    transition: opacity 0.2s;
}

.chat-input-container button:hover {
    opacity: 0.9;
}

.chat-input-container button:active {
    opacity: 0.8;
}

.back-link {
    color: var(--accent);
    text-decoration: none;
    font-size: 14px;
}

.back-link:hover {
    text-decoration: underline;
}

@media (max-width: 1100px) {
    .chat-container {
        max-width: 100%;
        border-left: none;
        border-right: none;
    }
}

@media (max-width: 768px) {
    .chat {
        max-width: 85%;
    }
    
    .chat-header {
        padding: 12px 15px;
    }
    
    .chat-header h3 {
        font-size: 16px;
    }
    
    .chat-messages {
        padding: 15px;
    }
    
    .chat-input-container {
        padding: 12px 15px;
    }
}

@media (max-width: 480px) {
    .chat {
        max-width: 90%;
        padding: 6px 10px;
    }
    
    .chat-name {
        font-size: 12px;
    }
    
    .chat-text {
        font-size: 13px;
    }
    
    .chat-meta {
        font-size: 10px;
    }
}
</style>
</head>
<body>

<div class="chat-container">
    <div class="chat-header">
        <div>
            <h3>Diskusi Thread</h3>
        </div>
        <a href="thread.php?idgrup=<?= $idgrup ?>" class="back-link">← Kembali</a>
    </div>

    <div id="chatMessages" class="chat-messages"></div>

    <div class="chat-input-container">
        <textarea id="message" rows="1" placeholder="Ketik pesan..."></textarea>
        <button onclick="sendChat()">Kirim</button>
    </div>
</div>

<script>
let lastId = 0;

function loadChat() {
    fetch(`chat.php?action=fetch&idthread=<?= $idthread ?>&lastId=${lastId}`)
        .then(res => res.json())
        .then(data => {
            data.forEach(chat => {
                appendChat(chat);
                lastId = chat.idchat;
            });
        });
}

function appendChat(chat) {
    let div = document.createElement("div");
    div.className = "chat " + (chat.is_me ? "me" : "other");
    div.innerHTML = `
        <div class="chat-name">${chat.nama}</div>
        <div class="chat-text">${chat.isi}</div>
        <div class="chat-meta">${chat.waktu}</div>
    `;
    document.getElementById("chatMessages").appendChild(div);
    scrollToBottom();
}

function scrollToBottom() {
    const messages = document.getElementById("chatMessages");
    messages.scrollTop = messages.scrollHeight;
}

function sendChat() {
    let msg = document.getElementById("message").value.trim();
    if (!msg) return;

    fetch("chat.php", {
        method: "POST",
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: `action=send&idthread=<?= $idthread ?>&isi=${encodeURIComponent(msg)}`
    }).then(() => {
        document.getElementById("message").value = "";
        loadChat();
    });
}

document.getElementById("message").addEventListener("input", function() {
    this.style.height = "auto";
    this.style.height = Math.min(this.scrollHeight, 100) + "px";
});
document.getElementById("message").addEventListener("keydown", function(e) {
    if (e.key === "Enter" && !e.shiftKey) {
        e.preventDefault();
        sendChat();
    }
});

setInterval(loadChat, 2000);
loadChat();
</script>

</body>
</html>
