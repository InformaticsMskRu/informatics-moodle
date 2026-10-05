// Loads a problem statement, its sample tests, its time/memory limits and the
// "Служебное" ejudge links from the pynformatics API and renders them
// client-side. Fills elements with .statement-api-content (statement + samples),
// .statement-api-limits (limits), .statement-api-service (ejudge links) and
// .statement-api-languages (the submit form's language dropdown); the
// problem id is taken from the problem-id data attribute. Each problem is fetched
// once even when several containers reference it. Picking another problem in the
// table of contents swaps all of this in place (see switchTo()) instead of
// reloading the page.
(function() {
    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function masterLink(contestId, text) {
        var url = '/cgi-bin/new-master?contest_id=' + encodeURIComponent(contestId);
        return '<a href="' + url + '">' + escapeHtml(text) + '</a>';
    }

    // TeX is rendered by MathJax via Moodle's filter_mathjaxloader, which loads
    // MathJax lazily — only when the server-rendered page already contains math.
    // The statement comes from the API, so the server page has no
    // math and MathJax may never be loaded (window.MathJax is undefined). So we
    // don't call MathJax directly; instead we notify Moodle's filters about the
    // injected content: the mathjaxloader loads MathJax on demand and typesets
    // the new nodes. It only typesets elements with the .filter_mathjaxloader_equation
    // class, so renderStatement wraps the content in that span (as the server
    // filter does). Falls back to a direct MathJax call if the AMD loader is absent.
    function typesetMath(container) {
        if (typeof window.require === 'function') {
            window.require(['core/event'], function(event) {
                event.notifyFilterContentUpdated(container);
            });
            return;
        }
        var mathJax = window.MathJax;
        if (mathJax && mathJax.Hub && typeof mathJax.Hub.Queue === 'function') {
            mathJax.Hub.Queue(['Typeset', mathJax.Hub, container]);
        }
    }

    function renderStatement(container, data) {
        var html = '';
        if (typeof data.content === 'string') {
            html += data.content;
        }
        if (typeof data.sample_tests_html === 'string') {
            html += data.sample_tests_html;
        }
        // Wrap in the class the mathjax filter typesets (mirrors the server-side
        // filter output) so notifyFilterContentUpdated picks the TeX up.
        container.innerHTML = '<span class="filter_mathjaxloader_equation">' + html + '</span>';
        typesetMath(container);
    }

    // Shows or hides the sidebar block that wraps the limits container. The
    // block is emitted for admins even when limits are hidden (limits.php), so
    // the toggle can reveal it without a page reload.
    function setLimitsBlockHidden(container, hidden) {
        var block = container.closest ? container.closest('.block') : null;
        if (block) {
            block.classList.toggle('statements-limits-block-hidden', hidden);
        }
    }

    // Mirrors limit_block() in limits.php: timelimit floored to 2 decimals in
    // seconds, memorylimit in MiB; timelimit is shown only when positive.
    // Rendering is skipped (and the block hidden) when show_limits is off or the
    // problem has no limits.
    function renderLimits(container, data) {
        var show = container.getAttribute('data-show-limits') !== '0';
        if (!show) {
            container.innerHTML = '';
            setLimitsBlockHidden(container, true);
            return;
        }
        var html = '';
        if (typeof data.timelimit === 'number' && data.timelimit > 0) {
            var seconds = Math.floor(data.timelimit * 100) / 100;
            html += '<i class="icon fa fa-clock-o fa-fw" aria-hidden="true"></i>' + seconds + ' сек.<br/>';
        }
        if (typeof data.memorylimit === 'number' && data.memorylimit) {
            var mib = data.memorylimit / 1024 / 1024;
            html += '<i class="icon fa fa-table fa-fw" aria-hidden="true"></i>' + mib + ' MiB<br/>';
        }
        container.innerHTML = html;
        setLimitsBlockHidden(container, html === '');
    }

    // Re-fetches the problem and re-renders its limits without a page reload.
    // Called by js/module.js after the "Служебное" toggle flips show_limits.
    function refreshLimits(problemId, show) {
        var containers = document.querySelectorAll(
            '.statement-api-limits[data-problem-id="' + String(problemId).replace(/"/g, '') + '"]');
        if (!containers.length) {
            return;
        }
        for (var i = 0; i < containers.length; i++) {
            containers[i].setAttribute('data-show-limits', show ? '1' : '0');
        }
        fetch('/py/problem/' + encodeURIComponent(problemId) + '/json', {credentials: 'same-origin'})
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (!data) {
                    return;
                }
                for (var i = 0; i < containers.length; i++) {
                    renderLimits(containers[i], data);
                }
            })
            .catch(function(error) {
                if (window.console) {
                    console.error('Failed to refresh the limits from the API', error);
                }
            });
    }

    // Mirrors the "Служебное" block in toc.php: a new-master link for the
    // problem's own ejudge contest, plus one link per judges_settings reroute
    // target, each prefixed with the judge name resolved from judges.json.
    function renderService(container, data) {
        var lines = [];
        var settings = Array.isArray(data.judges_settings) ? data.judges_settings : [];
        // judges_settings reroutes the problem to explicit judges, so the
        // default ejudge_contest_id no longer points at the real judge — show
        // it only when there is no per-judge routing. (The API already omits
        // ejudge_contest_id in that case; this guard keeps the client correct
        // even if it is present.)
        if (data.ejudge_contest_id && settings.length === 0) {
            lines.push(masterLink(data.ejudge_contest_id,
                data.ejudge_contest_id + '/' + (data.short_id || '')));
        }
        settings.forEach(function(entry) {
            if (!entry || !entry.contest_id) {
                return;
            }
            var prefix = entry.judge_name || ('judge ' + entry.judge_id);
            var text = entry.contest_id + '/' + entry.problem_id;
            // The API returns a ready-to-use master url (entry.url); render it
            // as-is instead of rebuilding the link on the client.
            var link = entry.url
                ? '<a href="' + escapeHtml(entry.url) + '">' + escapeHtml(text) + '</a>'
                : masterLink(entry.contest_id, text);
            lines.push(escapeHtml(prefix) + ': ' + link);
        });
        container.innerHTML = lines.join('<br/>');
    }

    // Fills the language dropdown of the submit form (see submit_form.php) with
    // the languages the user can submit this problem in. The click handler in
    // js/module.js is delegated, so the added options work without rebinding.
    // The first option is selected: the form's built-in default may not be
    // offered for this problem.
    function renderLanguages(container, data) {
        if (!Array.isArray(data.languages)) {
            return;
        }
        container.innerHTML = data.languages.map(function(lang) {
            return '<a class="dropdown-item lang_choose_option" id="lang_choose_option" value="' +
                escapeHtml(lang.id) + '" href="#">' + escapeHtml(lang.name) + '</a>';
        }).join('');
        var first = container.querySelector('a.lang_choose_option');
        if (first) {
            first.click();
        }
    }

    function problemUrl(problemId, withLanguages) {
        var url = '/py/problem/' + encodeURIComponent(problemId) + '/json';
        // the statement's allowed_languages narrows the languages
        var statementNode = document.getElementById('statement_id');
        var statementId = statementNode ? parseInt(statementNode.textContent, 10) : NaN;
        if (withLanguages && statementId > 0) {
            url += '?statement_id=' + statementId;
        }
        return url;
    }

    function fetchProblem(problemId, withLanguages, signal) {
        var options = {credentials: 'same-origin'};
        if (signal) {
            options.signal = signal;
        }
        return fetch(problemUrl(problemId, withLanguages), options)
            .then(function(response) {
                return response.json().then(function(data) {
                    if (!response.ok || !data || data.error) {
                        throw new Error('Problem ' + problemId + ' was not loaded: ' + response.status);
                    }
                    return data;
                });
            });
    }

    function renderGroup(group, data) {
        group.statement.forEach(function(container) {
            renderStatement(container, data);
        });
        group.limits.forEach(function(container) {
            renderLimits(container, data);
        });
        group.service.forEach(function(container) {
            renderService(container, data);
        });
        group.languages.forEach(function(container) {
            renderLanguages(container, data);
        });
    }

    function render(problemId, group) {
        fetchProblem(problemId, group.languages.length > 0)
            .then(function(data) {
                renderGroup(group, data);
            })
            .catch(function(error) {
                if (window.console) {
                    console.error('Failed to load the problem from the API', error);
                }
            });
    }

    var GROUP_SELECTORS = {
        statement: '.statement-api-content',
        limits: '.statement-api-limits',
        service: '.statement-api-service',
        languages: '.statement-api-languages'
    };

    function findContainers(key) {
        return Array.prototype.slice.call(document.querySelectorAll(GROUP_SELECTORS[key]));
    }

    function init() {
        // Group containers by problem id so each problem is fetched only once.
        var groups = {};
        function collect(selector, key) {
            var nodes = document.querySelectorAll(selector);
            for (var i = 0; i < nodes.length; i++) {
                var problemId = nodes[i].getAttribute('data-problem-id');
                if (!problemId) {
                    continue;
                }
                if (!groups[problemId]) {
                    groups[problemId] = {statement: [], limits: [], service: [], languages: []};
                }
                groups[problemId][key].push(nodes[i]);
            }
        }
        Object.keys(GROUP_SELECTORS).forEach(function(key) {
            collect(GROUP_SELECTORS[key], key);
        });

        Object.keys(groups).forEach(function(problemId) {
            render(problemId, groups[problemId]);
        });
    }

    // ---- Switching the problem without a page reload ----------------------

    var pending = null;

    function currentProblemId() {
        var node = document.querySelector('.statement-api-content');
        return node ? node.getAttribute('data-problem-id') : null;
    }

    // The table of contents: highlight the item of the opened problem.
    function updateToc(problemId) {
        var links = document.querySelectorAll('.statements_toc a.statements-toc-link');
        for (var i = 0; i < links.length; i++) {
            var current = links[i].getAttribute('data-chapterid') === String(problemId);
            links[i].classList.toggle('statements-toc-current', current);
            if (current) {
                links[i].setAttribute('aria-current', 'page');
            } else {
                links[i].removeAttribute('aria-current');
            }
        }
    }

    // Everything outside the API containers that PHP renders per problem.
    function updateChrome(problemId, data) {
        var heading = document.getElementById('statement-problem-heading');
        if (heading) {
            heading.textContent = 'Задача №' + problemId + '. ' + data.name;
        }
        var submits = document.getElementById('statements-problem-submits-link');
        if (submits) {
            submits.setAttribute('href', 'view.php?chapterid=' + encodeURIComponent(problemId) + '&submit');
        }
        var toggle = document.getElementById('invert_limits');
        if (toggle) {
            toggle.setAttribute('data-problem-id', problemId);
            toggle.checked = !!data.show_limits;
        }
        // Admin tools (js/module.js) read the problem from this element on click.
        var problemData = document.getElementById('problem_data');
        if (problemData) {
            problemData.setAttribute('problem_id', problemId);
            problemData.setAttribute('sample_tests', data.sample_tests || '');
            problemData.setAttribute('limit_action', 'show_limits' in data ? (data.show_limits ? 'hide' : 'show') : 'null');
        }
        ['problem_tests', 'myAlert'].forEach(function(id) {
            var node = document.getElementById(id);
            if (node) {
                node.innerHTML = '';
            }
        });
    }

    function applyProblem(problemId, data) {
        var group = {};
        Object.keys(GROUP_SELECTORS).forEach(function(key) {
            group[key] = findContainers(key);
            group[key].forEach(function(container) {
                container.setAttribute('data-problem-id', problemId);
            });
        });
        // Admins get show_limits explicitly; for others the API sends the limits
        // only when they are shown.
        var showLimits = 'show_limits' in data ? !!data.show_limits : 'memorylimit' in data;
        group.limits.forEach(function(container) {
            container.setAttribute('data-show-limits', showLimits ? '1' : '0');
        });
        renderGroup(group, data);
        updateToc(problemId);
        updateChrome(problemId, data);
        // js/module.js retargets the submit form and reloads the submits table.
        document.dispatchEvent(new CustomEvent('statements:problemchange', {detail: {problemId: problemId, data: data}}));
    }

    // Opens the problem in place. url is the address of the problem's page: it
    // goes to the history and is the fallback for a regular navigation if the
    // API call fails. Returns false when the page can't switch in place.
    function switchTo(problemId, url, push) {
        var content = document.querySelector('.statement-api-content');
        if (!content || typeof fetch !== 'function') {
            return false;
        }
        if (String(problemId) === currentProblemId()) {
            return true;
        }
        if (pending) {
            pending.abort();
        }
        var controller = typeof AbortController === 'function' ? new AbortController() : null;
        pending = controller || {abort: function() {}};
        var request = pending;
        content.classList.add('statement-api-loading');

        fetchProblem(problemId, findContainers('languages').length > 0, controller && controller.signal)
            .then(function(data) {
                if (pending !== request) {
                    return;
                }
                pending = null;
                content.classList.remove('statement-api-loading');
                if (push) {
                    history.pushState({problemId: problemId}, '', url + '#1');
                }
                applyProblem(problemId, data);
                window.scrollTo(0, 0);
            })
            .catch(function(error) {
                if (error && error.name === 'AbortError') {
                    return;
                }
                if (window.console) {
                    console.error('Failed to switch the problem, reloading the page', error);
                }
                window.location.href = url;
            });
        return true;
    }

    function initSwitching() {
        if (!document.querySelector('.statement-api-content') || !window.history || !history.pushState) {
            return;
        }
        history.replaceState({problemId: currentProblemId()}, '');

        document.addEventListener('click', function(event) {
            var link = event.target.closest ? event.target.closest('.statements_toc a.statements-toc-link') : null;
            if (!link || event.defaultPrevented || event.button !== 0 ||
                    event.ctrlKey || event.metaKey || event.shiftKey || event.altKey) {
                return;
            }
            if (switchTo(link.getAttribute('data-chapterid'), link.href, true)) {
                event.preventDefault();
            }
        });

        window.addEventListener('popstate', function() {
            var match = /[?&]chapterid=(\d+)/.exec(window.location.search);
            if (match) {
                switchTo(match[1], window.location.href, false);
            }
        });
    }

    // Lets js/module.js refresh the limits block after the toggle flips the flag.
    window.StatementApi = window.StatementApi || {};
    window.StatementApi.refreshLimits = refreshLimits;
    window.StatementApi.switchTo = switchTo;

    function start() {
        init();
        initSwitching();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', start);
    } else {
        start();
    }
})();
