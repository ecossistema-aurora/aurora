
function toggleDropdown() {
        const dropdownMenu = document.getElementById("customDropdown");
        dropdownMenu?.classList.toggle("show");

        setTimeout(() => {
            document.getElementById("dropdownMenuButton")?.blur();
        }, 100);
    }
document.addEventListener('DOMContentLoaded', () => {
    const userBtn = document.getElementById("dropdownMenuButton");
    const userMenu = document.getElementById("customDropdown");
    const notifBtn = document.getElementById("notificationDropdown");
    const notifMenu = document.querySelector(".dropdown-notification .dropdown-menu");
    const offcanvasBody = document.querySelector('.offcanvas-body');

    const toggleMenu = (btn, menuToToggle, menuToClose) => {
        if (!btn || !menuToToggle) return;

        menuToClose?.classList.remove('show');

        const isNowOpen = menuToToggle.classList.toggle("show");

        if (isNowOpen && menuToToggle === userMenu && offcanvasBody) {
            setTimeout(() => {
                offcanvasBody.scrollTo({
                    top: offcanvasBody.scrollHeight,
                    behavior: 'smooth'
                });
            }, 50);
        }
    };

    userBtn?.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleMenu(userBtn, userMenu, notifMenu);
        setTimeout(() => userBtn.blur(), 100);
    });

    notifBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation();
        toggleMenu(notifBtn, notifMenu, userMenu);
    });

    document.addEventListener('click', (e) => {
        const clickedOutsideUser = !userBtn?.contains(e.target) && !userMenu?.contains(e.target);
        const clickedOutsideNotif = !notifBtn?.contains(e.target) && !notifMenu?.contains(e.target);

        if (clickedOutsideUser) userMenu?.classList.remove('show');
        if (clickedOutsideNotif) notifMenu?.classList.remove('show');
    

    const dropdownButton = document.getElementById("dropdownMenuButton");
    dropdownButton?.addEventListener('click', (e) => {
        e.stopPropagation();
        toggleDropdown();
    });

    document.addEventListener('click', (event) => {
        const dropdownMenu = document.getElementById("customDropdown");

        if (dropdownMenu?.classList.contains('show')) {
            if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.remove('show');
            }
        }
    });

    document.querySelectorAll('.dropdown-item').forEach(item => {
        item.addEventListener('click', () => {
            userMenu?.classList.remove('show');
            notifMenu?.classList.remove('show');
            const dropdownMenu = document.getElementById("customDropdown");
            dropdownMenu.classList.remove('show');

            setTimeout(() => {
                dropdownButton.blur();
            }, 100);
        });
    });
});
