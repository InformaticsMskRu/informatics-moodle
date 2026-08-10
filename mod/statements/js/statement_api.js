// Loads a problem statement, its sample tests and its time/memory limits from the
// pynformatics API and renders them client-side. Fills elements with
// .statement-api-content (statement + samples) and .statement-api-limits (limits);
// the problem id is taken from the problem-id data attribute. Each problem is
// fetched once even when several containers reference it.
(function() {
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
                    groups[problemId] = {statement: [], limits: []};
                }
                groups[problemId][key].push(nodes[i]);
            }
        }
        collect('.statement-api-content', 'statement');
        collect('.statement-api-limits', 'limits');

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
