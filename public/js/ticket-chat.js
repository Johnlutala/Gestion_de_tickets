document.addEventListener('DOMContentLoaded', () => {
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

    if (replyForm) {
        replyForm.addEventListener('submit', (event) => {
            const textValue = replyText ? replyText.value.trim() : '';
            const hasFile = replyAttachmentInput ? replyAttachmentInput.files.length > 0 : false;
            if (!textValue && !hasFile) {
                event.preventDefault();
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
