// Traitor.dev Portal JS
// Plain vanilla JS — no build step, no frameworks

document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss flash messages after 5 seconds
    document.querySelectorAll('.flash').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.3s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 300);
        }, 5000);
    });

    // Dropzone — show filename on select, highlight on drag
    document.querySelectorAll('.dropzone').forEach(function (zone) {
        var input = zone.querySelector('input[type="file"]');
        var text = zone.querySelector('.dropzone-text');
        var nameEl = zone.querySelector('.dropzone-filename');

        if (input) {
            input.addEventListener('change', function () {
                if (input.files.length) {
                    if (nameEl) nameEl.textContent = input.files[0].name;
                    if (text) text.textContent = 'File selected:';
                }
            });
        }

        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            zone.classList.add('dragover');
        });

        zone.addEventListener('dragleave', function () {
            zone.classList.remove('dragover');
        });

        zone.addEventListener('drop', function () {
            zone.classList.remove('dragover');
        });
    });
});
