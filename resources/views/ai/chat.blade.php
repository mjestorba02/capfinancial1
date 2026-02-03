@extends('layouts.app')

@section('content')
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    .ai-chat-container {
        display: flex;
        flex-direction: column;
        height: calc(100vh - 120px);
        background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
    }

    .ai-chat-header {
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .ai-chat-header h1 {
        font-size: 1.8rem;
        font-weight: 700;
        display: flex;
        align-items: center;
        gap: 12px;
        margin: 0;
    }

    .ai-chat-header p {
        margin: 8px 0 0 0;
        opacity: 0.9;
        font-size: 0.95rem;
    }

    .ai-chat-main {
        display: flex;
        flex: 1;
        gap: 20px;
        padding: 20px;
        overflow: hidden;
    }

    /* Suggestions Panel */
    .ai-suggestions-panel {
        width: 300px;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 15px;
    }

    .ai-suggestions-panel h3 {
        color: #2d3748;
        font-size: 1rem;
        font-weight: 600;
        margin: 0 0 10px 0;
    }

    .suggestion-category {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        padding: 12px;
        cursor: pointer;
        transition: all 0.3s ease;
    }

    .suggestion-category:hover {
        background: #f7fafc;
        border-color: #667eea;
        box-shadow: 0 2px 8px rgba(102, 126, 234, 0.15);
    }

    .suggestion-category h4 {
        color: #667eea;
        font-size: 0.9rem;
        font-weight: 600;
        margin: 0 0 8px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .suggestion-category i {
        font-size: 1.1rem;
    }

    .suggestion-items {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .suggestion-item {
        background: #f7fafc;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 8px 10px;
        font-size: 0.85rem;
        color: #2d3748;
        cursor: pointer;
        transition: all 0.2s ease;
        white-space: normal;
        line-height: 1.4;
    }

    .suggestion-item:hover {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }

    /* Chat Area */
    .ai-chat-area {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        overflow: hidden;
    }

    .ai-messages {
        flex: 1;
        padding: 20px;
        overflow-y: auto;
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .ai-message {
        padding: 14px 16px;
        border-radius: 10px;
        word-wrap: break-word;
        overflow-wrap: break-word;
        animation: slideIn 0.3s ease;
        line-height: 1.5;
    }

    .ai-message strong {
        font-weight: 700;
        color: inherit;
    }

    .ai-message em {
        font-style: italic;
        color: inherit;
    }

    .ai-message code {
        background: rgba(0, 0, 0, 0.1);
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
        font-size: 0.9em;
    }

    .ai-message a {
        color: inherit;
        text-decoration: underline;
        cursor: pointer;
    }

    .ai-message br {
        display: block;
        content: '';
        margin: 4px 0;
    }

    .ai-message.user {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        align-self: flex-end;
        max-width: 85%;
        border-radius: 10px 0 10px 10px;
    }

    .ai-message.ai {
        background: #e8f0fe;
        color: #202124;
        border-left: 4px solid #667eea;
        max-width: 100%;
        border-radius: 0 10px 10px 10px;
    }

    .ai-message.system {
        background: #fff3cd;
        color: #856404;
        border-left: 4px solid #ffc107;
        border-radius: 8px;
        align-self: center;
    }

    .ai-message.error {
        background: #f8d7da;
        color: #721c24;
        border-left: 4px solid #dc3545;
        border-radius: 8px;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Input Area */
    .ai-input-area {
        border-top: 1px solid #e2e8f0;
        padding: 15px 20px;
        display: flex;
        gap: 10px;
    }

    .ai-input-wrapper {
        flex: 1;
        display: flex;
        gap: 8px;
    }

    .ai-input {
        flex: 1;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 12px 14px;
        font-size: 0.95rem;
        font-family: inherit;
        resize: none;
        max-height: 100px;
    }

    .ai-input:focus {
        outline: none;
        border-color: #667eea;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .ai-send-btn {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        padding: 12px 24px;
        cursor: pointer;
        font-weight: 600;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .ai-send-btn:hover:not(:disabled) {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .ai-send-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }

    .loading-indicator {
        display: inline-block;
        width: 8px;
        height: 8px;
        background: white;
        border-radius: 50%;
        animation: pulse 1.5s infinite;
        margin-right: 4px;
    }

    @keyframes pulse {
        0%, 100% { opacity: 1; }
        50% { opacity: 0.5; }
    }

    /* Responsive */
    @media (max-width: 768px) {
        .ai-chat-main {
            flex-direction: column;
            gap: 10px;
        }

        .ai-suggestions-panel {
            width: 100%;
            max-height: 200px;
        }

        .ai-message.user {
            max-width: 95%;
        }

        .ai-send-btn {
            padding: 10px 16px;
            font-size: 0.9rem;
        }
    }

    .empty-state {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #a0aec0;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 15px;
        color: #cbd5e0;
    }

    .empty-state p {
        font-size: 1rem;
        margin: 0;
    }
</style>

<div class="ai-chat-container">
    <div class="ai-chat-header">
        <h1>
            <i class="fas fa-robot"></i>
            Financial AI Assistant
        </h1>
        <p>Powered by Google Gemini - Get instant insights about your financial data</p>
    </div>

    <div class="ai-chat-main">
        <!-- Suggestions Panel -->
        <div class="ai-suggestions-panel">
            <div id="suggestionsContainer">
                <div style="text-align: center; color: #a0aec0;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem;"></i>
                    <p style="margin-top: 10px;">Loading suggestions...</p>
                </div>
            </div>
        </div>

        <!-- Chat Area -->
        <div class="ai-chat-area">
            <div class="ai-messages" id="messagesContainer">
                <div class="empty-state">
                    <i class="fas fa-comments"></i>
                    <p>Start a conversation with the AI Assistant</p>
                </div>
            </div>

            <div class="ai-input-area">
                <div class="ai-input-wrapper">
                    <textarea 
                        class="ai-input" 
                        id="promptInput" 
                        placeholder="Ask about budgets, collections, payables, or general financial insights..." 
                        rows="2"
                        maxlength="5000"
                    ></textarea>
                </div>
                <button class="ai-send-btn" id="sendBtn">
                    <i class="fas fa-paper-plane"></i>
                    Send
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    const messagesContainer = document.getElementById('messagesContainer');
    const promptInput = document.getElementById('promptInput');
    const sendBtn = document.getElementById('sendBtn');
    const suggestionsContainer = document.getElementById('suggestionsContainer');

    let isFirstMessage = true;

    // Load suggestions
    async function loadSuggestions() {
        try {
            const response = await fetch('{{ route("ai.suggestions") }}');
            const suggestions = await response.json();

            let html = '';
            for (const [key, category] of Object.entries(suggestions)) {
                html += `
                    <div class="suggestion-category">
                        <h4><i class="fas ${category.icon}"></i> ${category.title}</h4>
                        <div class="suggestion-items">
                            ${category.prompts.map(prompt => `
                                <div class="suggestion-item" onclick="useSuggestion('${prompt.replace(/'/g, "\\'")}')">
                                    ${prompt}
                                </div>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
            suggestionsContainer.innerHTML = html;
        } catch (error) {
            suggestionsContainer.innerHTML = '<p style="color: #e53e3e; padding: 10px;">Failed to load suggestions</p>';
        }
    }

    function useSuggestion(prompt) {
        promptInput.value = prompt;
        promptInput.focus();
    }

    function clearEmptyState() {
        if (isFirstMessage && messagesContainer.querySelector('.empty-state')) {
            messagesContainer.innerHTML = '';
            isFirstMessage = false;
        }
    }

    function parseMarkdown(text) {
        // Convert markdown to HTML
        let html = text;
        
        // Bold: **text** -> <strong>text</strong>
        html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
        
        // Italic: *text* -> <em>text</em>
        html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
        
        // Links: [text](url) -> <a href="url">text</a>
        html = html.replace(/\[(.+?)\]\((.+?)\)/g, '<a href="$2" target="_blank">$1</a>');
        
        // Line breaks: \n -> <br>
        html = html.replace(/\n/g, '<br>');
        
        // Code blocks: `code` -> <code>code</code>
        html = html.replace(/`(.+?)`/g, '<code>$1</code>');
        
        // Bullet points: * item -> • item (with styling)
        html = html.replace(/\n\* /g, '<br>• ');
        if (html.startsWith('* ')) {
            html = html.replace(/^\* /, '• ');
        }
        
        return html;
    }

    function addMessage(text, sender) {
        clearEmptyState();
        const messageDiv = document.createElement('div');
        messageDiv.className = `ai-message ${sender}`;
        
        if (sender === 'ai') {
            // Parse markdown for AI messages
            messageDiv.innerHTML = parseMarkdown(text);
        } else {
            // Plain text for user and error messages
            messageDiv.textContent = text;
        }
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    async function sendPrompt() {
        const prompt = promptInput.value.trim();
        if (!prompt) return;

        addMessage(prompt, 'user');
        promptInput.value = '';
        sendBtn.disabled = true;

        try {
            const response = await fetch('{{ route("ai.request") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ prompt })
            });

            const data = await response.json();

            if (data.success) {
                addMessage(data.response, 'ai');
            } else {
                addMessage(`Error: ${data.error}`, 'error');
            }
        } catch (error) {
            addMessage(`Error: ${error.message}`, 'error');
        } finally {
            sendBtn.disabled = false;
            promptInput.focus();
        }
    }

    sendBtn.addEventListener('click', sendPrompt);
    promptInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendPrompt();
        }
    });

    // Load suggestions on page load
    loadSuggestions();
</script>
@endsection
