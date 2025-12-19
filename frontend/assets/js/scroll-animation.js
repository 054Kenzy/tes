window.onload = function() {
    checkVisible();
};

window.onscroll = function() {
    checkVisible();
};

function checkVisible() {
    var items = document.querySelectorAll('.fade-in');
    var i = 0;
    while (i < items.length) {
        var rect = items[i].getBoundingClientRect();
        
        // Cek apakah elemen di dalam viewport
        if (rect.top < window.innerHeight && rect.bottom > 0) {
        items[i].classList.add('visible');
        } else {
        // Kalau keluar viewport, hapus class visible
        items[i].classList.remove('visible');
        }
        
        i = i + 1;
    }
}
