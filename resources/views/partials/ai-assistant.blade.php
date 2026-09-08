{{-- Floating AI Assistant Widget --}}
<div id="aiAssistantContainer" class="ai-assistant-container">
    
    {{-- Launcher Button --}}
    <button type="button" id="aiAssistantLauncher" class="ai-launcher-btn" onclick="toggleAiModal()" aria-label="Open Intern Estate AI Assistant">
        <span class="ai-launcher-badge">InternEstate AI</span>
        <span class="ai-pulse-dot"></span>
    </button>

    {{-- Chat Modal Window --}}
    <div id="aiChatModal" class="ai-chat-modal" style="display: none;">
        
        {{-- Header --}}
        <div class="ai-chat-header">
            <div class="ai-header-info">
                <div>
                    <h4 class="ai-title">Intern Estate AI Assistant</h4>
                    <span class="ai-status">
                        <span class="status-dot"></span> Online • Real Estate Intelligence
                    </span>
                </div>
            </div>
            <div class="ai-header-actions">
                <button type="button" class="ai-icon-btn" onclick="clearAiChat()" title="Clear Conversation" style="width: auto; padding: 0 8px; font-size: 11px; font-weight: 700;">
                    Clear
                </button>
                <button type="button" class="ai-icon-btn" onclick="toggleAiModal()" title="Close Assistant" style="width: auto; padding: 0 8px; font-size: 11px; font-weight: 700;">
                    Close
                </button>
            </div>
        </div>

        {{-- Messages Body --}}
        <div id="aiMessagesList" class="ai-messages-list">
            
            {{-- Initial Bot Welcome Message --}}
            <div class="ai-message bot">
                <div class="ai-msg-bubble">
                    <p>Hello! I am your <strong>Intern Estate Smart AI Assistant</strong>.</p>
                    <p>Ask me anything about active investment projects, 1-3 year Credit Card EMI plans, or land joint-ventures!</p>
                </div>
            </div>

            {{-- Quick Suggestion Chips --}}
            <div id="aiDefaultSuggestions" class="ai-suggestions-container">
                <button type="button" class="ai-chip" onclick="sendAiPrompt('Top Recommended Projects')">
                    Top Projects
                </button>
                <button type="button" class="ai-chip" onclick="sendAiPrompt('How does EMI work?')">
                    EMI Payment Plans
                </button>
                <button type="button" class="ai-chip" onclick="sendAiPrompt('Submit Land for JV')">
                    Landowner JV
                </button>
                <button type="button" class="ai-chip" onclick="sendAiPrompt('NID & Tax Document rules')">
                    Legal Verification
                </button>
            </div>

        </div>

        {{-- Typing Indicator --}}
        <div id="aiTypingIndicator" class="ai-typing-indicator" style="display: none;">
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="dot"></span>
            <span class="typing-text">AI is searching live real estate data...</span>
        </div>

        {{-- Input Footer --}}
        <div class="ai-chat-footer">
            <form id="aiChatForm" onsubmit="handleAiSubmit(event)">
                <input 
                    type="text" 
                    id="aiInputMessage" 
                    class="ai-input-field" 
                    placeholder="Ask AI about projects, budget, EMI..." 
                    autocomplete="off"
                    required
                >
                <button type="submit" id="aiSendBtn" class="ai-send-btn">
                    <span>Send</span>
                </button>
            </form>
        </div>

    </div>

</div>

{{-- Inline Client-Side AI Script with Smooth Typewriter Effect --}}
<script>
    function toggleAiModal() {
        const modal = document.getElementById('aiChatModal');
        const launcher = document.getElementById('aiAssistantLauncher');
        if (modal.style.display === 'none' || modal.style.display === '') {
            modal.style.display = 'flex';
            launcher.classList.add('active');
            document.getElementById('aiInputMessage').focus();
        } else {
            modal.style.display = 'none';
            launcher.classList.remove('active');
        }
    }

    function sendAiPrompt(promptText) {
        document.getElementById('aiInputMessage').value = promptText;
        handleAiSubmit(new Event('submit'));
    }

    function clearAiChat() {
        const msgList = document.getElementById('aiMessagesList');
        msgList.innerHTML = `
            <div class="ai-message bot">
                <div class="ai-msg-bubble">
                    <p>Conversation cleared. How can I assist you with real estate investments today?</p>
                </div>
            </div>
            <div id="aiDefaultSuggestions" class="ai-suggestions-container">
                <button type="button" class="ai-chip" onclick="sendAiPrompt('Top Recommended Projects')">Top Projects</button>
                <button type="button" class="ai-chip" onclick="sendAiPrompt('How does EMI work?')">EMI Payment Plans</button>
                <button type="button" class="ai-chip" onclick="sendAiPrompt('Submit Land for JV')">Landowner JV</button>
            </div>
        `;
    }

    async function handleAiSubmit(event) {
        event.preventDefault();
        const input = document.getElementById('aiInputMessage');
        const userMsg = input.value.trim();
        if (!userMsg) return;

        // Append User Message
        appendMessage(userMsg, 'user');
        input.value = '';

        // Show Typing Indicator
        const indicator = document.getElementById('aiTypingIndicator');
        indicator.style.display = 'flex';
        scrollToBottom();

        try {
            const response = await fetch("{{ route('api.ai.chat') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ message: userMsg })
            });

            const data = await response.json();
            indicator.style.display = 'none';

            if (data.status === 'success') {
                appendSmoothBotMessage(data.reply, data.action_link, data.suggestions);
            } else {
                appendMessage("Sorry, I encountered an issue processing your request. Please try again.", 'bot');
            }

        } catch (error) {
            console.error('AI Chat Error:', error);
            indicator.style.display = 'none';
            appendMessage("Unable to connect to AI server. Please check your network connection.", 'bot');
        }
    }

    function appendMessage(text, sender, actionLink = null, suggestions = []) {
        const msgList = document.getElementById('aiMessagesList');
        const msgDiv = document.createElement('div');
        msgDiv.className = `ai-message ${sender}`;

        let formattedText = text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');

        let htmlContent = `<div class="ai-msg-bubble"><p>${formattedText}</p>`;

        if (actionLink && actionLink.url) {
            htmlContent += `
                <div class="ai-action-box">
                    <a href="${actionLink.url}" class="ai-action-btn">${actionLink.text}</a>
                </div>
            `;
        }

        htmlContent += `</div>`;
        msgDiv.innerHTML = htmlContent;
        msgList.appendChild(msgDiv);

        if (suggestions && suggestions.length > 0) {
            const chipDiv = document.createElement('div');
            chipDiv.className = 'ai-suggestions-container';
            suggestions.forEach(s => {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'ai-chip';
                btn.innerText = s;
                btn.onclick = () => sendAiPrompt(s);
                chipDiv.appendChild(btn);
            });
            msgList.appendChild(chipDiv);
        }

        scrollToBottom();
    }

    // Typewriter Smooth Streaming Effect for Bot Response
    function appendSmoothBotMessage(text, actionLink = null, suggestions = []) {
        const msgList = document.getElementById('aiMessagesList');
        const msgDiv = document.createElement('div');
        msgDiv.className = 'ai-message bot';

        const bubble = document.createElement('div');
        bubble.className = 'ai-msg-bubble';
        const p = document.createElement('p');
        bubble.appendChild(p);
        msgDiv.appendChild(bubble);
        msgList.appendChild(msgDiv);

        let formattedText = text
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');

        let index = 0;
        const speed = 12;

        function typeWriter() {
            if (index < formattedText.length) {
                if (formattedText.charAt(index) === '<') {
                    const closingIndex = formattedText.indexOf('>', index);
                    if (closingIndex !== -1) {
                        index = closingIndex + 1;
                    } else {
                        index++;
                    }
                } else {
                    index++;
                }
                p.innerHTML = formattedText.substring(0, index);
                scrollToBottom();
                setTimeout(typeWriter, speed);
            } else {
                p.innerHTML = formattedText;
                if (actionLink && actionLink.url) {
                    const actionBox = document.createElement('div');
                    actionBox.className = 'ai-action-box';
                    actionBox.innerHTML = `<a href="${actionLink.url}" class="ai-action-btn">${actionLink.text}</a>`;
                    bubble.appendChild(actionBox);
                }

                if (suggestions && suggestions.length > 0) {
                    const chipDiv = document.createElement('div');
                    chipDiv.className = 'ai-suggestions-container';
                    suggestions.forEach(s => {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'ai-chip';
                        btn.innerText = s;
                        btn.onclick = () => sendAiPrompt(s);
                        chipDiv.appendChild(btn);
                    });
                    msgList.appendChild(chipDiv);
                }
                scrollToBottom();
            }
        }

        typeWriter();
    }

    function scrollToBottom() {
        const msgList = document.getElementById('aiMessagesList');
        msgList.scrollTop = msgList.scrollHeight;
    }
</script>
