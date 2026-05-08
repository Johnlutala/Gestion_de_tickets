document.addEventListener('DOMContentLoaded', () => {
    const autoDismissMessages = [
        'Ticket créé avec succès',
        'Le ticket a été déplacé dans la corbeille.',
    ];

    document.querySelectorAll('.alert.alert-success').forEach((alert) => {
        const message = alert.textContent ? alert.textContent.trim() : '';
        const shouldDismiss = autoDismissMessages.some((expectedMessage) => message.includes(expectedMessage));

        if (!shouldDismiss) {
            return;
        }

        window.setTimeout(() => {
            alert.classList.remove('show');
            window.setTimeout(() => {
                alert.remove();
            }, 500);
        }, 10000);
    });

    const chatWrapper = document.getElementById('chatWrapper');
    if (!chatWrapper) {
        return;
    }

    const chatMessages = document.getElementById('chatMessages');
    const chatSidebar = document.getElementById('chatSidebar');
    const chatMain = document.getElementById('chatMain');
    const searchInput = document.getElementById('searchConv');
    const replyForm = document.getElementById('replyForm');
    const replyText = document.getElementById('replyText');
    const replyAttachmentInput = document.getElementById('replyAttachment');
    const replyAttachmentName = document.getElementById('replyAttachmentName');
    const evaluationPanel = document.getElementById('evaluationPanel');
    const noteRange = document.getElementById('ticketNoteRange');
    const noteInput = document.getElementById('ticketNoteInput');
    const noteHidden = document.getElementById('ticketNoteHidden');
    const scoreLabel = document.getElementById('evaluationScoreValue');
    const evaluationForm = document.getElementById('ticketEvaluationForm');
    const evaluationSubmit = document.getElementById('ticketEvaluationSubmit');
    const hasSelected = chatWrapper.dataset.hasSelected === 'true';

    const isMobile = () => window.innerWidth <= 768;

    const showSidebar = () => {
        if (!chatSidebar || !chatMain) {
            return;
        }

        chatSidebar.classList.remove('mobile-hidden');
        chatMain.classList.add('mobile-hidden');
    };

    const showMain = () => {
        if (!chatSidebar || !chatMain) {
            return;
        }

        chatSidebar.classList.add('mobile-hidden');
        chatMain.classList.remove('mobile-hidden');
    };

    const autoResizeTextarea = () => {
        if (!replyText) {
            return;
        }

        replyText.style.height = 'auto';
        replyText.style.height = `${Math.min(replyText.scrollHeight, 120)}px`;
    };

    const syncEvaluationValue = (value) => {
        const parsedValue = parseInt(value || '10', 10);
        const normalized = Math.max(1, Math.min(20, Number.isNaN(parsedValue) ? 10 : parsedValue));

        if (noteRange) {
            noteRange.value = String(normalized);
        }
        if (noteInput) {
            noteInput.value = String(normalized);
        }
        if (noteHidden) {
            noteHidden.value = String(normalized);
        }
        if (scoreLabel) {
            scoreLabel.textContent = String(normalized);
        }
    };

    const openEvaluationPanel = () => {
        if (!evaluationPanel) {
            return;
        }

        evaluationPanel.classList.add('is-open');
        if (isMobile()) {
            evaluationPanel.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    };

    const closeEvaluationPanel = () => {
        if (evaluationPanel) {
            evaluationPanel.classList.remove('is-open');
        }
    };

    if (chatMessages) {
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }

    if (searchInput) {
        searchInput.addEventListener('input', (event) => {
            const query = event.target.value.toLowerCase();
            document.querySelectorAll('#chatList .chat-list-entry').forEach((entry) => {
                const item = entry.querySelector('.chat-list-item');
                if (!item) {
                    return;
                }

                const searchText = `${item.dataset.title || ''} ${item.dataset.marchand || ''}`.toLowerCase();
                entry.style.display = searchText.includes(query) ? '' : 'none';
            });
        });
    }

    document.querySelectorAll('.js-open-evaluation').forEach((button) => {
        button.addEventListener('click', openEvaluationPanel);
    });

    document.querySelectorAll('.js-close-evaluation').forEach((button) => {
        button.addEventListener('click', closeEvaluationPanel);
    });

    document.querySelectorAll('.js-submit-form-link').forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();

            if (link.classList.contains('disabled') || link.getAttribute('aria-disabled') === 'true') {
                return;
            }

            const formId = link.dataset.formId;
            const confirmMessage = link.dataset.confirmMessage;
            const targetForm = formId ? document.getElementById(formId) : null;
            if (!targetForm) {
                return;
            }

            if (confirmMessage && !window.confirm(confirmMessage)) {
                return;
            }

            targetForm.submit();
        });
    });

    document.querySelectorAll('form[data-confirm-message]').forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.dataset.confirmMessage;
            if (message && !window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    if (replyAttachmentInput && replyAttachmentName) {
        replyAttachmentInput.addEventListener('change', () => {
            replyAttachmentName.textContent = replyAttachmentInput.files.length > 0
                ? replyAttachmentInput.files[0].name
                : 'Aucun fichier';
        });
    }

    if (replyText) {
        autoResizeTextarea();
        replyText.addEventListener('input', autoResizeTextarea);
        replyText.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                if (replyForm) {
                    replyForm.requestSubmit();
                }
            }
        });
    }

    const scrollMessages = () => {
        if (!chatMessages) {
            return;
        }
        chatMessages.scrollTop = chatMessages.scrollHeight;
    };

    const appendMessage = (sender, content, isMe, time) => {
        if (!chatMessages || !content) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = `msg-bubble-wrap ${isMe ? 'me' : 'them'}`;

        if (!isMe) {
            const nameEl = document.createElement('div');
            nameEl.className = 'msg-sender-name';
            nameEl.textContent = sender;
            wrapper.appendChild(nameEl);
        }

        const bubble = document.createElement('div');
        bubble.className = 'msg-bubble';
        bubble.textContent = content;
        wrapper.appendChild(bubble);

        const timeEl = document.createElement('div');
        timeEl.className = 'msg-time';
        timeEl.textContent = time || new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        wrapper.appendChild(timeEl);

        chatMessages.appendChild(wrapper);
        scrollMessages();
    };

    if (replyForm) {
        replyForm.addEventListener('submit', async (event) => {
            const textValue = replyText ? replyText.value.trim() : '';
            const hasFile = replyAttachmentInput ? replyAttachmentInput.files.length > 0 : false;
            if (!textValue && !hasFile) {
                event.preventDefault();
                return;
            }

            if (hasFile || !replyForm.dataset.aiEndpoint) {
                return;
            }

            event.preventDefault();
            const sendButton = replyForm.querySelector('.btn-send');
            if (sendButton) {
                sendButton.disabled = true;
            }

            try {
                const response = await fetch(replyForm.dataset.aiEndpoint, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                    },
                    body: new FormData(replyForm),
                });

                const responseText = await response.text();
                let data = null;

                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('Réponse JSON invalide du serveur:', responseText, parseError);
                }

                if (!response.ok) {
                    const message = data?.error || responseText || `Erreur serveur ${response.status}`;
                    const detail = data?.details ? `\n${data.details}` : '';
                    window.alert(message + detail);
                    return;
                }

                if (!data || data.error) {
                    const detail = data?.details ? `\n${data.details}` : '';
                    window.alert((data?.error || 'Réponse invalide du serveur.') + detail);
                    return;
                }

                appendMessage(data.reply.sender, data.reply.content, true, data.reply.time);
                if (data.assistant) {
                    appendMessage(data.assistant.sender, data.assistant.content, false, data.assistant.time);
                }

                if (replyText) {
                    replyText.value = '';
                    autoResizeTextarea();
                }
            } catch (error) {
                console.error('Erreur AJAX IA:', error);
                window.alert(`Erreur de connexion au service IA : ${error?.message || 'vérifiez la console'}`);
            } finally {
                if (sendButton) {
                    sendButton.disabled = false;
                }
            }
        });
    }

    if (noteRange) {
        noteRange.addEventListener('input', (event) => syncEvaluationValue(event.target.value));
    }

    if (noteInput) {
        noteInput.addEventListener('input', (event) => syncEvaluationValue(event.target.value));
    }

    if (evaluationSubmit && evaluationForm) {
        evaluationSubmit.addEventListener('click', () => {
            syncEvaluationValue(noteInput ? noteInput.value : '10');
            evaluationForm.submit();
        });
    }

    syncEvaluationValue(noteInput ? noteInput.value : '10');

    const backButton = document.getElementById('btnBackMobile');
    if (backButton) {
        backButton.addEventListener('click', showSidebar);
    }

    document.querySelectorAll('#chatList .chat-list-item').forEach((item) => {
        item.addEventListener('click', () => {
            if (isMobile()) {
                showMain();
            }
        });
    });

    if (isMobile()) {
        if (hasSelected) {
            showMain();
        } else {
            showSidebar();
        }
    }

    window.addEventListener('resize', () => {
        if (!isMobile() && chatSidebar && chatMain) {
            chatSidebar.classList.remove('mobile-hidden');
            chatMain.classList.remove('mobile-hidden');
        }
    });
});
