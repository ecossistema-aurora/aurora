document.addEventListener('DOMContentLoaded', () => {
    const userDropdownBtn = document.getElementById("dropdownMenuButton");
    const userDropdownMenu = document.getElementById("customDropdown");

    const notificationBtn = document.getElementById("notificationDropdown");
    const notificationMenu = document.querySelector(".dropdown-notification .dropdown-menu");

    if (userDropdownBtn && userDropdownMenu) {
        userDropdownBtn.addEventListener('click', (e) => {
            e.stopPropagation();

            if (notificationMenu && notificationMenu.classList.contains('show')) {
                notificationMenu.classList.remove('show');
            }

            userDropdownMenu.classList.toggle("show");

            setTimeout(() => {
                userDropdownBtn.blur();
            }, 100);
        });
    }

    if (notificationBtn && notificationMenu) {
        notificationBtn.addEventListener('click', (e) => {
            e.preventDefault();
            e.stopPropagation();

            if (userDropdownMenu && userDropdownMenu.classList.contains('show')) {
                userDropdownMenu.classList.remove('show');
            }

            notificationMenu.classList.toggle("show");
        });
    }

    document.addEventListener('click', (event) => {
        if (userDropdownMenu && userDropdownMenu.classList.contains('show')) {
            if (!userDropdownBtn.contains(event.target) && !userDropdownMenu.contains(event.target)) {
                userDropdownMenu.classList.remove('show');
            }
        }

        if (notificationMenu && notificationMenu.classList.contains('show')) {
            if (!notificationBtn.contains(event.target) && !notificationMenu.contains(event.target)) {
                notificationMenu.classList.remove('show');
            }
        }
    });

    const dropdownItems = document.querySelectorAll('.dropdown-item');
    dropdownItems.forEach(item => {
        item.addEventListener('click', () => {
            if (userDropdownMenu) userDropdownMenu.classList.remove('show');
            if (notificationMenu) notificationMenu.classList.remove('show');
        });
    });
});