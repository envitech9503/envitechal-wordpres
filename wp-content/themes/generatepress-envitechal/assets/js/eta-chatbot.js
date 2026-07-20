(function (window, document) {
    'use strict';

    if (window.etaChatbotInit) {
        return;
    }

    function delay(milliseconds) {
        return new Promise(function (resolve) {
            window.setTimeout(resolve, milliseconds);
        });
    }

    function request(url, options, timeout) {
        var controller = new AbortController();
        var timer = window.setTimeout(function () {
            controller.abort();
        }, timeout);
        options.signal = controller.signal;

        return window.fetch(url, options).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (data) {
                if (!response.ok) {
                    throw new Error(data.message || 'Assistant request failed.');
                }
                return data;
            });
        }).finally(function () {
            window.clearTimeout(timer);
        });
    }

    window.etaChatbotInit = function (root) {
        if (!root || root.dataset.etaInitialised === '1') {
            return;
        }
        root.dataset.etaInitialised = '1';

        var panel = root.querySelector('.eta-chatbot-panel');
        var launcher = root.querySelector('.eta-chatbot-launcher');
        var status = root.querySelector('.eta-chatbot-status');
        var fallback = root.querySelector('.eta-chatbot-fallback');
        var frame = root.querySelector('.eta-chatbot-frame-wrap');
        var messages = root.querySelector('.eta-chatbot-messages');
        var form = root.querySelector('.eta-chatbot-form');
        var input = root.querySelector('#eta-chatbot-question');
        var send = root.querySelector('.eta-chatbot-send');
        var history = [];

        function setState(state, message) {
            root.dataset.etaState = state;
            status.textContent = message;
            fallback.hidden = state !== 'unavailable';
            frame.hidden = state === 'unavailable';
            input.disabled = state !== 'ready';
            send.disabled = state !== 'ready';
            root.classList.toggle('has-live-chat', state === 'ready');
        }

        function addMessage(role, text, citations) {
            var item = document.createElement('div');
            item.className = 'eta-chatbot-message eta-chatbot-message-' + role;
            var body = document.createElement('p');
            body.textContent = text;
            item.appendChild(body);

            if (Array.isArray(citations) && citations.length) {
                var list = document.createElement('ul');
                list.className = 'eta-chatbot-citations';
                citations.forEach(function (url) {
                    if (typeof url !== 'string' || url.indexOf('https://envitechal.com/') !== 0 || text.indexOf(url) !== -1) {
                        return;
                    }
                    var entry = document.createElement('li');
                    var link = document.createElement('a');
                    link.href = url;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.textContent = url;
                    entry.appendChild(link);
                    list.appendChild(entry);
                });
                if (list.childNodes.length) {
                    item.appendChild(list);
                }
            }

            messages.appendChild(item);
            messages.scrollTop = messages.scrollHeight;
        }

        function checkHealth(attempt) {
            setState('connecting', attempt ? 'Still connecting — retrying safely…' : 'Connecting to the assistant…');
            return request(root.dataset.healthUrl, {
                method: 'GET',
                credentials: 'same-origin',
                headers: { 'X-WP-Nonce': root.dataset.restNonce }
            }, 14000).then(function () {
                setState('ready', 'Ready — answers include source links and avoid unsupported claims.');
                if (!messages.childNodes.length) {
                    addMessage('assistant', 'Hello. Ask about services, laboratory locations, credential scope, compliance references, or report verification.');
                }
                input.focus();
            }).catch(function () {
                if (attempt < 2) {
                    return delay(750 * Math.pow(2, attempt)).then(function () {
                        return checkHealth(attempt + 1);
                    });
                }
                setState('unavailable', 'Assistant unavailable — WhatsApp support is ready.');
            });
        }

        function sendQuestion(message, attempt) {
            return request(root.dataset.messageUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': root.dataset.restNonce
                },
                body: JSON.stringify({ message: message, history: history.slice(-8) })
            }, 30000).catch(function (error) {
                if (attempt < 2 && error.name !== 'AbortError') {
                    return delay(800 * Math.pow(2, attempt)).then(function () {
                        return sendQuestion(message, attempt + 1);
                    });
                }
                throw error;
            });
        }

        form.addEventListener('submit', function (event) {
            event.preventDefault();
            var question = input.value.trim();
            if (!question || root.dataset.etaState !== 'ready') {
                return;
            }

            addMessage('user', question);
            input.value = '';
            input.disabled = true;
            send.disabled = true;
            status.textContent = 'Preparing a source-checked answer…';

            sendQuestion(question, 0).then(function (data) {
                var answer = typeof data.answer === 'string' ? data.answer.trim() : '';
                if (!answer) {
                    throw new Error('The assistant returned no answer.');
                }
                history.push({ role: 'user', content: question });
                history.push({ role: 'assistant', content: answer });
                history = history.slice(-8);
                addMessage('assistant', answer, data.citations || []);
                status.textContent = 'Ready — verify compliance-critical details against the cited source.';
                input.disabled = false;
                send.disabled = false;
                input.focus();
            }).catch(function () {
                setState('unavailable', 'Assistant unavailable — WhatsApp support is ready.');
            });
        });

        panel.addEventListener('keydown', function (event) {
            if (event.key !== 'Tab') {
                return;
            }
            var focusable = Array.prototype.slice.call(panel.querySelectorAll('a[href], button:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])'));
            if (!focusable.length) {
                event.preventDefault();
                panel.focus();
                return;
            }
            var first = focusable[0];
            var last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });

        launcher.setAttribute('aria-expanded', 'true');
        checkHealth(0);
    };
}(window, document));
