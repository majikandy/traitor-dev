// Traitor.dev Portal JS
// Plain vanilla JS — no build step, no frameworks

document.addEventListener('DOMContentLoaded', function () {
    // Auto-dismiss flash messages after 5 seconds (skip persistent ones)
    document.querySelectorAll('.flash:not(.flash-persist)').forEach(function (el) {
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

// Release preview toggle — lazy-loads iframe on first expand
function toggleReleasePreview(btn) {
    var row = btn.closest('div').parentElement;
    var panel = row.querySelector('.release-preview');
    var chevron = btn.querySelector('.release-chevron');
    var isOpen = !panel.classList.contains('hidden');

    if (isOpen) {
        panel.classList.add('hidden');
        chevron.style.transform = '';
        return;
    }

    // Inject iframe on first open
    if (!panel.querySelector('iframe')) {
        var url = btn.getAttribute('data-preview-url');
        panel.innerHTML =
            '<div class="relative w-full bg-gray-100 border-t border-gray-100" style="height:280px;overflow:hidden">' +
            '<iframe src="' + url + '" class="absolute top-0 left-0 border-0" ' +
            'style="width:1280px;height:800px;transform:scale(0.35);transform-origin:top left" ' +
            'loading="lazy" sandbox="allow-same-origin"></iframe>' +
            '</div>';
    }

    panel.classList.remove('hidden');
    chevron.style.transform = 'rotate(90deg)';
}

// Sidebar toggle for mobile
function toggleSidebar() {
    var sidebar = document.getElementById('sidebar');
    var overlay = document.getElementById('sidebar-overlay');

    sidebar.classList.toggle('-translate-x-full');
    overlay.classList.toggle('hidden');
}
