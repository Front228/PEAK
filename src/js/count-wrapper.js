function updateHeaderCounters(){
    const cartEl = document.getElementById('cart-count');
    const favEl = document.getElementById('favorites-count');
    if (!cartEl || !favEl ) return;
    
    const isLogged = document.querySelector('.logout_image') !==null;

    if(isLogged){
        return
    }

    const cart = JSON.parse(localStorage.getItem('cart') || '[]');
    const favs = JSON.parse(localStorage.getItem('favorites') || '[]');

    if(cart.length > 0){
        cartEl.textContent = cart.length;
        cartEl.classList.add('show');
    } else{
        cartEl.classList.remove('show');
    }
    if(favs.length > 0){
        favEl.textContent = favs.length;
        favEl.classList.add('show');
    } else{
        favEl.classList.remove('show');
    }
}

document.addEventListener('DOMContentLoaded', updateHeaderCounters);