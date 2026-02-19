
document.addEventListener('DOMContentLoaded', function() {
    // Функция из main.js
    function updateHeaderCounters() {
        const cartEl = document.getElementById('cart-count');
        const favEl = document.getElementById('favorites-count');
        if (!cartEl || !favEl) return;

        const isLogged = document.querySelector('.logout_image') !== null;

        if (isLogged) {
            Promise.all([
                fetch('/src/php/handlers/get_cart_count.php').then(res => res.json()),
                fetch('/src/php/handlers/get_favorites_count.php').then(res => res.json())
            ]).then(([cartData, favData]) => {
                if (cartData.count > 0) {
                    cartEl.textContent = cartData.count;
                    cartEl.classList.add('show');
                } else {
                    cartEl.classList.remove('show');
                }
                if (favData.count > 0) {
                    favEl.textContent = favData.count;
                    favEl.classList.add('show');
                } else {
                    favEl.classList.remove('show');
                }
            });
        } else {
            const cart = JSON.parse(localStorage.getItem('cart') || '[]');
            const favs = JSON.parse(localStorage.getItem('favorites') || '[]');
            if (cart.length > 0) {
                cartEl.textContent = cart.length;
                cartEl.classList.add('show');
            } else {
                cartEl.classList.remove('show');
            }
            if (favs.length > 0) {
                favEl.textContent = favs.length;
                favEl.classList.add('show');
            } else {
                favEl.classList.remove('show');
            }
        }
    }

    updateHeaderCounters();
});
