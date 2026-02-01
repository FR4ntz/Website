<div class="card shadow-sm" style="height: 600px;">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <div><i class="bi bi-robot me-2"></i> SITA-BOT (AI Assistant)</div>
        <span class="badge bg-light text-primary small">Llama 3 Model</span>
    </div>
    
    <div class="card-body bg-light chat-box" id="chatContainer" style="overflow-y: auto;">
        <div class="msg-container">
            <div class="msg-ai">
                Halo! Saya SITA-BOT. 👋<br>
                Saya bisa membantu mencari ide judul, merapikan kalimat, atau menjelaskan metode penelitian. Ada yang bisa dibantu?
            </div>
        </div>
    </div>
    <div class="typing-indicator px-3 mb-2" id="loading">SITA-BOT sedang mengetik...</div>

    <div class="card-footer bg-white">
        <div class="input-group">
            <input type="text" id="userInput" class="form-control" placeholder="Tanya sesuatu tentang skripsi..." autocomplete="off">
            <button class="btn btn-primary" onclick="sendMessage()" id="btnSend"><i class="bi bi-send-fill"></i></button>
        </div>
    </div>
</div>