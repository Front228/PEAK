document.addEventListener('DOMContentLoaded', function () {
    const allProducts = initialProducts;

    // === ПРОВЕРКА АВТОРИЗАЦИИ ===
    function isUserLoggedIn() {
        return document.querySelector('.logout_image') !== null;
    }

    // === ФУНКЦИЯ ОБНОВЛЕНИЯ СЧЁТЧИКОВ ===
    function updateHeaderCounters() {
    const isLogged = document.querySelector('.logout_image') !== null;

    // Получаем данные
    let cartCount = 0;
    let favCount = 0;

    if (isLogged) {
        // Для авторизованных — запрос к БД
        Promise.all([
            fetch('/src/php/handlers/get_cart_count.php').then(res => res.json()),
            fetch('/src/php/handlers/get_favorites_count.php').then(res => res.json())
        ]).then(([cartData, favData]) => {
            cartCount = cartData.count || 0;
            favCount = favData.count || 0;
            updateBadges(cartCount, favCount);
        }).catch(err => console.error('Ошибка счётчиков:', err));
    } else {
        // Для гостей — из localStorage
        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const favs = JSON.parse(localStorage.getItem('favorites') || '[]');
        cartCount = cart.length;
        favCount = favs.length;
        updateBadges(cartCount, favCount);
    }
}

// Обновление всех бейджей
function updateBadges(cartCount, favCount) {
    // Десктоп
    const desktopCart = document.getElementById('cart-count');
    const desktopFav = document.getElementById('favorites-count');

    // Мобилка
    const mobileCart = document.querySelector('.cart-count-mobile');
    const mobileFav = document.querySelector('.favorites-count-mobile');

    // Обновляем десктоп
    if (desktopCart) {
        if (cartCount > 0) {
            desktopCart.textContent = cartCount;
            desktopCart.classList.add('show');
        } else {
            desktopCart.classList.remove('show');
        }
    }

    if (desktopFav) {
        if (favCount > 0) {
            desktopFav.textContent = favCount;
            desktopFav.classList.add('show');
        } else {
            desktopFav.classList.remove('show');
        }
    }

    // Обновляем мобилку
    if (mobileCart) {
        if (cartCount > 0) {
            mobileCart.textContent = cartCount;
            mobileCart.classList.add('show');
        } else {
            mobileCart.classList.remove('show');
        }
    }

    if (mobileFav) {
        if (favCount > 0) {
            mobileFav.textContent = favCount;
            mobileFav.classList.add('show');
        } else {
            mobileFav.classList.remove('show');
        }
    }
}
// === ФУНКЦИЯ: ДОБАВИТЬ В КОРЗИНУ ===
function handleAddToCart(item, selectedSize, buyBtn) {
    // Если размер не передан — берём первый
    const sizeToUse = selectedSize || (item.size.split(',')[0] || 'N/A');

    // Сохраняем оригинальный текст кнопки
    const originalText = buyBtn ? buyBtn.textContent : 'Купить';

    if (isUserLoggedIn()) {
        fetch('/src/php/handlers/add_to_cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `product_id=${item.id}&section=${encodeURIComponent(item.section)}&quantity=1&size=${encodeURIComponent(sizeToUse)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                const el = document.getElementById('cart-count');
                const count = parseInt(el.textContent) || 0;
                el.textContent = count + 1;
                el.classList.add('show');

                // Визуальная обратная связь
                if (buyBtn) {
                    buyBtn.textContent = 'В корзине';
                    buyBtn.style.background = '#4caf50';
                    setTimeout(() => {
                        buyBtn.textContent = originalText;
                        buyBtn.style.background = '';
                    }, 2000);
                }
            } else {
                if (buyBtn) {
                    buyBtn.textContent = 'Ошибка';
                    setTimeout(() => {
                        buyBtn.textContent = originalText;
                    }, 2000);
                }
            }
        });
    } else {
        let cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const existing = cart.find(p => 
            p.id === item.id && 
            p.section === item.section && 
            p.selectedSize === sizeToUse
        );
        if (existing) {
            existing.quantity = (existing.quantity || 1) + 1;
        } else {
            cart.push({...item, quantity: 1, selectedSize: sizeToUse});
        }
        localStorage.setItem('cart', JSON.stringify(cart));
        updateHeaderCounters();

        // Визуальная обратная связь
        if (buyBtn) {
            buyBtn.textContent = 'В корзине';
            buyBtn.style.background = '#4caf50';
            setTimeout(() => {
                buyBtn.textContent = originalText;
                buyBtn.style.background = '';
            }, 2000);
        }
    }
}

// === ФУНКЦИЯ: ДОБАВИТЬ В ИЗБРАННОЕ ===
function handleAddToFavorites(item, btn) {
    // Проверяем текущее состояние по цвету иконки
    const isCurrentlyFavorite = btn.querySelector('svg').getAttribute('fill') === '#ff6b35';

    // Если уже в избранном — ничего не делаем
    if (isCurrentlyFavorite) {
        return;
    }

    if (isUserLoggedIn()) {
        fetch('/src/php/handlers/add_to_favorites.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `product_id=${item.id}&section=${encodeURIComponent(item.section)}`
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                markAsFavorite(btn, true);
                const el = document.getElementById('favorites-count');
                const count = parseInt(el.textContent) || 0;
                el.textContent = count + 1;
                el.classList.add('show');
            } else {
                // При ошибке — сбрасываем состояние
                markAsFavorite(btn, false);
                console.error(data.error);
            }
        });
    } else {
        let favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
        const isFavorite = favorites.some(p => p.id === item.id && p.section === item.section);

        if (isFavorite) {
            // Уже есть — удаляем
            favorites = favorites.filter(p => !(p.id === item.id && p.section === item.section));
            markAsFavorite(btn, false);
            
            // Обновляем счётчик
            const el = document.getElementById('favorites-count');
            const count = parseInt(el.textContent) || 0;
            if (count > 1) {
                el.textContent = count - 1;
            } else {
                el.classList.remove('show');
            }
        } else {
            // Нет — добавляем
            favorites.push(item);
            markAsFavorite(btn, true);
            
            // Обновляем счётчик
            const el = document.getElementById('favorites-count');
            const count = parseInt(el.textContent) || 0;
            el.textContent = count + 1;
            el.classList.add('show');
        }
        localStorage.setItem('favorites', JSON.stringify(favorites));
        updateHeaderCounters();
    }
}

// === ВИЗУАЛЬНАЯ ОБРАТНАЯ СВЯЗЬ ===
function markAsFavorite(btn, isFavorite) {
    if (isFavorite) {
        btn.innerHTML = '<svg fill="#ff6b35"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"></path></svg>';
    } else {
        btn.innerHTML = '<svg fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"></path></svg>';
    }
}
    // === ПЕРЕНОС ДАННЫХ ПРИ ВХОДЕ ===
    function migrateGuestDataIfNeeded() {
        if (!isUserLoggedIn()) return;

        const migrated = document.getElementById('migrated-flag');
        if (migrated) return;

        const cart = JSON.parse(localStorage.getItem('cart') || '[]');
        const favs = JSON.parse(localStorage.getItem('favorites') || '[]');

        if (cart.length > 0 || favs.length > 0) {
            const size = item.selectedSize || (item.size ? item.size.split(',')[0] : 'N/A');
            cart.forEach(item => {
                fetch('/src/php/handlers/add_to_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `product_id=${item.id}&section=${encodeURIComponent(item.section)}&quantity=${item.quantity || 1}$size=${encodeURIComponent(size)}`
                });
            });

            favs.forEach(item => {
                fetch('/src/php/handlers/add_to_favorites.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `product_id=${item.id}&section=${encodeURIComponent(item.section)}`
                });
            });

            localStorage.removeItem('cart');
            localStorage.removeItem('favorites');

            const flag = document.createElement('div');
            flag.id = 'migrated-flag';
            flag.style.display = 'none';
            document.body.appendChild(flag);
        }
    }

    // === ОСТАЛЬНОЙ КОД (рендер, фильтры и т.д.) ===
    function updateProductCount(count) {
        const el = document.getElementById('count-number');
        if (el) {
            el.textContent = count;
        }
    }

    function renderItems(items) {
    const grid = document.getElementById('catalog-grid');
    if (!grid) return;

    grid.innerHTML = '';

    items.forEach(item => {
        const priceFormatted = item.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

        const mainImage = item.images[0];
        const hoverImages = item.images.slice(1);
        const paginationDots = item.images.map((_, i) => `
            <span class="pagination-dot ${i === 0 ? 'active' : ''}" data-index="${i}"></span>
        `).join('');

        const card = document.createElement('div');
        card.className = 'catalog-item';
        card.dataset.category = item.category;
        card.dataset.brand = item.brand.toLowerCase();
        card.dataset.size = item.size;
        card.dataset.color = item.color;
        card.dataset.price = item.price;

        // Сохраняем ссылку на страницу товара
        const productUrl = `/block/product.php?id=${item.id}&section=${item.section}`;

        let hoverImagesHtml = '';
        hoverImages.forEach((img, idx) => {
            hoverImagesHtml += `<img src="${img}" alt="${item.title} - ${idx + 2}" class="item-hover-img" data-index="${idx + 1}" style="display:none;">`;
        });
        
        const shouldShowSize = item.size && item.size !== 'N/A' && item.size.trim() !== '';
        const sizeSelectorHtml = shouldShowSize 
            ? `
                <div class="size-selector">
                    ${item.size.split(',').map(size => `
                        <button class="size-btn" data-size="${size.trim()}">${size.trim()}</button>
                    `).join('')}
                </div>`
            : '<div class="size-placeholder"></div>';

        card.innerHTML = `
            ${item.isNew ? '<div class="item-badge">Новинка</div>' : ''}
            <button class="item-favorite" aria-label="В избранное">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"></path>
                </svg>
            </button>
            <div class="item-image-container">
                <img src="${mainImage}" alt="${item.title}" class="item-main-img" data-index="0">
                ${hoverImagesHtml}
            </div>
            <div class="item-pagination">
                ${paginationDots}
            </div>
            <div class="item-info">
                <div class="item-brand">${item.brand}</div>
                <h3 class="item-title">${item.title}</h3>
                ${sizeSelectorHtml}
                <div class="item-price">${priceFormatted} ₽</div>
                
                <button class="item-buy-btn">Купить</button>
            </div>
        `;

        grid.appendChild(card);
        initImageSlider(card, item.images);

        // === ВЫБОР РАЗМЕРА ===
        const sizeButtons = card.querySelectorAll('.size-btn');
        sizeButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.stopPropagation();
                // Снимаем выделение со всех
                sizeButtons.forEach(b => b.classList.remove('selected'));
                // Выделяем текущий
                this.classList.add('selected');
            });
        });

        // Автоматически выделяем первый размер
        if (sizeButtons.length > 0) {
            sizeButtons[0].classList.add('selected');
        }

        // === КЛИКАБЕЛЬНЫЕ ЗОНЫ: изображение, бренд, название ===
        const clickableElements = [
            card.querySelector('.item-main-img'),
            card.querySelector('.item-hover-img'),
            card.querySelector('.item-brand'),
            card.querySelector('.item-title')
        ];

        clickableElements.forEach(el => {
            if (el) {
                el.style.cursor = 'pointer';
                el.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = productUrl;
                });
            }
        });

        // === КНОПКА "Купить" ===
        const buyBtn = card.querySelector('.item-buy-btn');
        buyBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            
            // Находим выбранный размер
            const sizeBtn = card.querySelector('.size-btn.selected');
            const selectedSize = sizeBtn ? sizeBtn.dataset.size : null;
            
            handleAddToCart(item, selectedSize, buyBtn);
        });

        // === КНОПКА "Избранное" ===
        const favoriteBtn = card.querySelector('.item-favorite');
        if (!isUserLoggedIn()) {
            const favorites = JSON.parse(localStorage.getItem('favorites') || '[]');
            const isFav = favorites.some(p => p.id === item.id && p.section === item.section);
            markAsFavorite(favoriteBtn, isFav);
        }

        favoriteBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            handleAddToFavorites(item, favoriteBtn);
        });
    });
}

    // === ИНИЦИАЛИЗАЦИЯ СЛАЙДЕРА ===
    function initImageSlider(card, images) {
        const container = card.querySelector('.item-image-container');
        const mainImg = card.querySelector('.item-main-img');
        const hoverImgs = card.querySelectorAll('.item-hover-img');
        const dots = card.querySelectorAll('.pagination-dot');

        // Внутри initImageSlider()
        dots.forEach(dot => {
            dot.addEventListener('click', function (e) {
                e.stopPropagation(); // ← ОСТАНАВЛИВАЕМ ВСПЛЫТИЕ
                const targetIndex = parseInt(this.dataset.index);
                mainImg.src = images[targetIndex];
                mainImg.dataset.index = targetIndex;
                hoverImgs.forEach(img => img.style.display = 'none');
                dots.forEach(d => d.classList.remove('active'));
                this.classList.add('active');
            });
        });

        container.addEventListener('mouseenter', function () {
            const currentIndex = parseInt(mainImg.dataset.index);
            let nextIndex = currentIndex + 1;
            if (nextIndex >= images.length) nextIndex = 0;

            const targetHover = Array.from(hoverImgs).find(img => 
                parseInt(img.dataset.index) === nextIndex
            );

            if (targetHover) {
                hoverImgs.forEach(img => img.style.display = 'none');
                targetHover.style.display = 'block';
            }
        });

        container.addEventListener('mouseleave', function () {
            hoverImgs.forEach(img => img.style.display = 'none');
        });
    }

    // === ФИЛЬТРЫ ===
    function resetFilters() {
        document.querySelectorAll('.filter-checkboxes input[type="checkbox"]').forEach(cb => cb.checked = false);
        document.querySelectorAll('input[name="filter-color"]').forEach(radio => radio.checked = false);
        document.getElementById('filter-brand').value = '';
        document.getElementById('filter-discount').value = '';
        document.getElementById('filter-min').value = '0';
        document.getElementById('filter-max').value = '99999';

        renderItems(allProducts);
        updateProductCount(allProducts.length);
    }

    document.getElementById('apply-filters')?.addEventListener('click', function () {
    const filters = {
        category: [],
        brand: document.getElementById('filter-brand')?.value.trim().toLowerCase() || '',
        size: [],
        color: document.querySelector('input[name="filter-color"]:checked')?.value || '',
        minPrice: parseInt(document.getElementById('filter-min')?.value) || 0,
        maxPrice: parseInt(document.getElementById('filter-max')?.value) || 99999
    };

    // Собираем чекбоксы из "Тип одежды"
    document.querySelectorAll('.filter-accordion').forEach(accordion => {
        const headerText = accordion.querySelector('.filter-accordion-header')?.textContent.trim();
        
        if (headerText?.includes('Тип одежды')) {
            accordion.querySelectorAll('.filter-checkboxes input[type="checkbox"]:checked').forEach(cb => {
                filters.category.push(cb.value);
            });
        }
        
        if (headerText?.includes('Размер')) {
            accordion.querySelectorAll('.filter-checkboxes input[type="checkbox"]:checked').forEach(cb => {
                filters.size.push(cb.value);
            });
        }
    });

    const filtered = allProducts.filter(item => {
        // Фильтр по категории
        if (filters.category.length && !filters.category.includes(item.category)) return false;
        
        // Фильтр по бренду
        if (filters.brand && !item.brand.toLowerCase().includes(filters.brand)) return false;
        
        // Фильтр по цвету
        if (filters.color && item.color !== filters.color) return false;
        
        // Фильтр по цене
        if (item.price < filters.minPrice || item.price > filters.maxPrice) return false;
        
        // Фильтр по размеру
        if (filters.size.length > 0) {
            const itemSizes = (item.size && typeof item.size === 'string') 
                ? item.size.split(',').map(s => s.trim())
                : [];
            const hasMatchingSize = filters.size.some(filterSize => 
                itemSizes.includes(filterSize)
            );
            if (!hasMatchingSize) return false;
        }

        return true;
    });

    renderItems(filtered);
    updateProductCount(filtered.length);
});

    // === МОБИЛЬНЫЕ ФИЛЬТРЫ ===
    const filterSidebar = document.getElementById('catalog-filters');
    const toggleBtn = document.getElementById('mobile-filter-toggle');
    const closeBtn = document.getElementById('mobile-filter-close');

    if (toggleBtn && filterSidebar) {
        toggleBtn.addEventListener('click', function () {
            filterSidebar.classList.add('active');
            document.body.style.overflow = 'hidden';
        });

        closeBtn.addEventListener('click', function () {
            filterSidebar.classList.remove('active');
            document.body.style.overflow = '';
        });

        document.addEventListener('click', (e) => {
            if (
                window.innerWidth <= 768 &&
                !filterSidebar.contains(e.target) &&
                e.target !== toggleBtn
            ) {
                filterSidebar.classList.remove('active');
                document.body.style.overflow = '';
            }
        });
    }

    // === КНОПКА СБРОСА ===
    const resetBtn = document.createElement('button');
    resetBtn.type = 'button';
    resetBtn.className = 'filter-reset-btn';
    resetBtn.textContent = 'Сбросить фильтры';
    resetBtn.addEventListener('click', resetFilters);

    const filtersContainer = document.querySelector('.catalog-filters');
    if (filtersContainer) {
        filtersContainer.appendChild(resetBtn);
    }

    // === ЗАПУСК ===
    renderItems(allProducts);
    updateProductCount(allProducts.length);
    updateHeaderCounters();
    migrateGuestDataIfNeeded();
});