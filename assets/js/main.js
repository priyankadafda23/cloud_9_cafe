document.addEventListener('DOMContentLoaded', function () {
    setCurrentYear();
    initGuestNavbarCollapse();
});

function setCurrentYear() {
    var yearNode = document.getElementById('currentYear');
    if (yearNode) {
        yearNode.textContent = String(new Date().getFullYear());
    }
}

function initGuestNavbarCollapse() {
    var navbarCollapse = document.getElementById('guestNavbar');
    if (!navbarCollapse) {
        return;
    }

    var navLinks = navbarCollapse.querySelectorAll('a');
    navLinks.forEach(function (link) {
        link.addEventListener('click', function () {
            var toggler = document.querySelector('.navbar-toggler');
            if (!toggler) {
                return;
            }

            var togglerVisible = window.getComputedStyle(toggler).display !== 'none';
            if (
                togglerVisible &&
                navbarCollapse.classList.contains('show') &&
                typeof bootstrap !== 'undefined'
            ) {
                var collapse = bootstrap.Collapse.getOrCreateInstance(navbarCollapse);
                collapse.hide();
            }
        });
    });
}
