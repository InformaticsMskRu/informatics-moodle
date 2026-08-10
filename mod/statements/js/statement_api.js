// Loads a problem statement from the pynformatics API and renders it client-side.
// Runs for every element with the .statement-api-content class;
// the problem id is taken from the problem-id data attribute.
(function() {
    function renderStatementFromApi(container) {
        var problemId = container.getAttribute('data-problem-id');
        if (!problemId) {
            return;
        }
        fetch('/py/problem/' + encodeURIComponent(problemId) + '/json', {credentials: 'same-origin'})
            .then(function(response) {
                return response.json();
            })
            .then(function(data) {
                if (data && typeof data.content !== 'undefined' && data.content !== null) {
                    container.innerHTML = data.content;
                }
            })
            .catch(function(error) {
                if (window.console) {
                    console.error('Failed to load the problem statement from the API', error);
                }
            });
    }

    function init() {
        var containers = document.querySelectorAll('.statement-api-content');
        for (var i = 0; i < containers.length; i++) {
            renderStatementFromApi(containers[i]);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
