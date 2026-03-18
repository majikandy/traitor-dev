// Traitor.dev - Blank Template JS

// Mobile nav toggle
document.addEventListener('DOMContentLoaded', function () {
    const toggle = document.querySelector('.nav-toggle');
    const navList = document.querySelector('.nav-list');

    if (toggle && navList) {
        toggle.addEventListener('click', function () {
            navList.classList.toggle('open');
        });
    }
});
