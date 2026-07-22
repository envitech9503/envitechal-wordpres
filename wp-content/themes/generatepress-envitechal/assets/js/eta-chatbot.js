(function (window, document) {
    'use strict';

    if (window.etaChatbotInit) {
        return;
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
        if (root.dataset.etaPreflight !== 'ready') {
            root.hidden = true;
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

        function setBusy(isBusy) {
            root.dataset.etaBusy = isBusy ? 'true' : 'false';
            messages.setAttribute('aria-busy', isBusy ? 'true' : 'false');
        }

        function setState(state, message) {
            root.dataset.etaState = state;
            status.textContent = message;
            fallback.hidden = state !== 'unavailable';
            frame.hidden = state === 'unavailable';
            input.disabled = state !== 'ready';
            send.disabled = state !== 'ready';
            root.classList.toggle('has-live-chat', state === 'ready');
            setBusy(false);
        }

        function sourceLabel(url) {
            var labels = {
                '/services/': 'Service overview',
                '/contact-us-envi-tech-al/': 'Contact details',
                '/report-verification-portal/': 'Report verification',
                '/accreditations-certifications/': 'Accreditation details',
                '/gaseous-air-emission-testing-lab-near-me/': 'Stack-emission service',
                '/ambient-air-monitoring-services/': 'Ambient-air service',
                '/noise-monitoring-dosimetry/': 'Noise-monitoring service',
                '/services/equipment-calibration-services/': 'Calibration service',
                '/soil-hazardous-waste-testing/': 'Soil-testing service',
                '/drinking-water-testing-lab/': 'Drinking-water service',
                '/wastewater-testing-services/': 'Wastewater service',
                '/services/water-testing-lab-services/': 'Water-testing service',
                '/services/environmental-consultancy/': 'Consultancy service',
                '/sindh-environmental-quality-standards-seqs/': 'SEQS reference'
            };
            try {
                return labels[new URL(url).pathname] || 'Published source';
            } catch (error) {
                return 'Published source';
            }
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
                var seen = {};
                citations.forEach(function (url) {
                    if (typeof url !== 'string' || url.indexOf('https://envitechal.com/') !== 0 || text.indexOf(url) !== -1 || seen[url]) {
                        return;
                    }
                    seen[url] = true;
                    var entry = document.createElement('li');
                    var link = document.createElement('a');
                    link.href = url;
                    link.target = '_blank';
                    link.rel = 'noopener noreferrer';
                    link.textContent = 'Source: ' + sourceLabel(url);
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

        function sendQuestion(message) {
            return request(root.dataset.messageUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': root.dataset.restNonce
                },
                body: JSON.stringify({ message: message, history: history.slice(-8) })
            }, 20000);
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
            setBusy(true);
            status.textContent = 'Checking verified sources...';

            sendQuestion(question).then(function (data) {
                var answer = typeof data.answer === 'string' ? data.answer.trim() : '';
                if (!answer) {
                    throw new Error('The assistant returned no answer.');
                }
                history.push({ role: 'user', content: question });
                history.push({ role: 'assistant', content: answer });
                history = history.slice(-8);
                addMessage('assistant', answer, data.citations || []);
                setBusy(false);
                status.textContent = 'Source checked - open the cited page for details.';
                input.disabled = false;
                send.disabled = false;
                input.focus();
            }).catch(function () {
                setState('unavailable', 'Assistant unavailable - WhatsApp support is ready.');
            });
        });

        input.addEventListener('keydown', function (event) {
            if (event.key !== 'Enter' || event.shiftKey || event.isComposing) {
                return;
            }
            event.preventDefault();
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
            } else {
                send.click();
            }
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

        setState('ready', 'Source-checked answers with links to published pages.');
        if (!messages.childNodes.length) {
            addMessage('assistant', 'Hello. Ask a specific question about services, locations, accreditation, or report verification.');
        }
        launcher.setAttribute('aria-expanded', 'true');
        input.focus();
    };
}(window, document));
