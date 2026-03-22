// Mobile nav toggle
document.querySelector('.nav-toggle').addEventListener('click', function () {
    document.querySelector('.nav').classList.toggle('open');
});

// Close nav on link click (mobile)
document.querySelectorAll('.nav a').forEach(function (link) {
    link.addEventListener('click', function () {
        document.querySelector('.nav').classList.remove('open');
    });
});
