/* Top Toast - Main JS */

// Mobile nav toggle
document.addEventListener('DOMContentLoaded', function () {
    var toggle = document.querySelector('.nav-toggle');
    var navList = document.querySelector('.nav-list');

    if (toggle && navList) {
        toggle.addEventListener('click', function () {
            navList.classList.toggle('open');
            toggle.setAttribute('aria-expanded', navList.classList.contains('open'));
        });
    }
});
