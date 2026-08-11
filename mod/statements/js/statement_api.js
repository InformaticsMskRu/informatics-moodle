// Loads a problem statement, its sample tests, its time/memory limits and the
// "Служебное" ejudge links from the pynformatics API and renders them
// client-side. Fills elements with .statement-api-content (statement + samples),
// .statement-api-limits (limits) and .statement-api-service (ejudge links); the
// problem id is taken from the problem-id data attribute. Each problem is fetched
// once even when several containers reference it.
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
    // For user 469 the statement comes from the API, so the server page has no
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

    // Mirrors limit_block() in limits.php: timelimit floored to 2 decimals in
    // seconds, memorylimit in MiB; timelimit is shown only when positive.
    function renderLimits(container, data) {
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

    function render(problemId, group) {
        fetch('/py/problem/' + encodeURIComponent(problemId) + '/json', {credentials: 'same-origin'})
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (!data) {
                    return;
                }
                group.statement.forEach(function(container) {
                    renderStatement(container, data);
                });
                group.limits.forEach(function(container) {
                    renderLimits(container, data);
                });
                group.service.forEach(function(container) {
                    renderService(container, data);
                });
            })
            .catch(function(error) {
                if (window.console) {
                    console.error('Failed to load the problem from the API', error);
                }
            });
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
                    groups[problemId] = {statement: [], limits: [], service: []};
                }
                groups[problemId][key].push(nodes[i]);
            }
        }
        collect('.statement-api-content', 'statement');
        collect('.statement-api-limits', 'limits');
        collect('.statement-api-service', 'service');

        Object.keys(groups).forEach(function(problemId) {
            render(problemId, groups[problemId]);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
