// Подгружает условие задачи из API pynformatics и отрисовывает его на клиенте.
// Активируется для каждого элемента с классом .statement-api-content,
// id задачи берётся из data-атрибута problem-id.
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
                    console.error('Не удалось загрузить условие из API', error);
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
