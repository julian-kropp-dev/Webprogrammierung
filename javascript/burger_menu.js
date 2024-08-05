/*
This code ensures that the burger menu is closed automatically when the window is resized to a larger width.
*/
window.addEventListener('resize', function () {
    if (window.innerWidth > 768) {
        document.getElementById('menu-toggle').checked = false;
    }
});
