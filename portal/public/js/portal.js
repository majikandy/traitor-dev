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
    document.querySelectorAll('[id="dropzone"]').forEach(function (zone) {
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
            zone.classList.add('border-blue-500', 'bg-blue-50');
        });

        zone.addEventListener('dragleave', function () {
            zone.classList.remove('border-blue-500', 'bg-blue-50');
        });

        zone.addEventListener('drop', function () {
            zone.classList.remove('border-blue-500', 'bg-blue-50');
        });
    });
});

// Sidebar toggle for mobile
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');

    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}
