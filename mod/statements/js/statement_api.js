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

    function renderStatement(container, data) {
        var html = '';
        if (typeof data.content === 'string') {
            html += data.content;
        }
        if (typeof data.sample_tests_html === 'string') {
            html += data.sample_tests_html;
        }
        container.innerHTML = html;
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
        if (data.ejudge_contest_id) {
            lines.push(masterLink(data.ejudge_contest_id,
                data.ejudge_contest_id + '/' + (data.short_id || '')));
        }
        if (Array.isArray(data.judges_settings)) {
            data.judges_settings.forEach(function(entry) {
                if (!entry || !entry.contest_id) {
                    return;
                }
                var prefix = entry.judge_name || ('judge ' + entry.judge_id);
                lines.push(escapeHtml(prefix) + ': ' +
                    masterLink(entry.contest_id, entry.contest_id + '/' + entry.problem_id));
            });
        }
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
