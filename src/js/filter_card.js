document.addEventListener('DOMContentLoaded', function () {
    // Данные товаров (пример)
    const catalogItems = [
        {
            id: 1,
            title: 'Куртка Arcteryx GAMMA HOODY MENS, Nightscape',
            brand: 'Arcteryx',
            price: 57100,
            images: [
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-065.webp',
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-165.webp'
            ],
            isNew: true,
            category: 'верхняя одежда',
            size: 'XL',
            color: 'синий',
            discount: 0
        },
        {
            id: 2,
            title: 'Куртка Arcteryx GAMMA HOODY MENS, Nightscape',
            brand: 'Arcteryx',
            price: 57100,
            images: [
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-065.webp',
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-165.webp'
            ],
            isNew: true,
            category: 'верхняя одежда',
            size: 'XL',
            color: 'синий',
            discount: 0
        },
        {
            id: 3,
            title: 'Куртка Arcteryx GAMMA HOODY MENS, Nightscape',
            brand: 'Arcteryx',
            price: 57100,
            images: [
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-065.webp',
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-165.webp'
            ],
            isNew: true,
            category: 'верхняя одежда',
            size: 'XL',
            color: 'синий',
            discount: 0
        },
        {
            id: 4,
            title: 'Куртка Arcteryx GAMMA HOODY MENS, Nightscape',
            brand: 'Arcteryx',
            price: 57100,
            images: [
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-065.webp',
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-165.webp'
            ],
            isNew: true,
            category: 'верхняя одежда',
            size: 'XL',
            color: 'синий',
            discount: 0
        },
        {
            id: 5,
            title: 'Куртка Arcteryx GAMMA HOODY MENS, Nightscape',
            brand: 'Arcteryx',
            price: 57100,
            images: [
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-065.webp',
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-165.webp'
            ],
            isNew: true,
            category: 'верхняя одежда',
            size: 'XL',
            color: 'синий',
            discount: 0
        },
        {
            id: 6,
            title: 'Куртка Arcteryx GAMMA HOODY MENS, Nightscape',
            brand: 'Arcteryx',
            price: 57100,
            images: [
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-065.webp',
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-165.webp'
            ],
            isNew: true,
            category: 'верхняя одежда',
            size: 'XL',
            color: 'синий',
            discount: 0
        },
        {
            id: 7,
            title: 'Куртка Arcteryx GAMMA HOODY MENS, Nightscape',
            brand: 'Arcteryx',
            price: 57100,
            images: [
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-065.webp',
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-165.webp'
            ],
            isNew: true,
            category: 'верхняя одежда',
            size: 'XL',
            color: 'синий',
            discount: 0
        },
        {
            id: 8,
            title: 'Куртка Arcteryx GAMMA HOODY MENS, Nightscape',
            brand: 'Arcteryx',
            price: 57100,
            images: [
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-065.webp',
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-165.webp'
            ],
            isNew: true,
            category: 'верхняя одежда',
            size: 'XL',
            color: 'синий',
            discount: 0
        },
        {
            id: 9,
            title: 'Куртка Arcteryx GAMMA HOODY MENS, Nightscape',
            brand: 'Arcteryx',
            price: 57100,
            images: [
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-065.webp',
                '/public/image/product/kurtka-arcteryx-gamma-hoody-mens-nightscape-165.webp'
            ],
            isNew: true,
            category: 'верхняя одежда',
            size: 'XL',
            color: 'синий',
            discount: 0
        },
        {
            id: 10,
            title: 'Термоштаны Under Armour ColdGear',
            brand: 'Under Armour',
            price: 8500,
            images: [
                '/public/image/product/underarmour-thermo-1.jpg',
                '/public/image/product/underarmour-thermo-2.jpg'
            ],
            isNew: false,
            category: 'термо бельё',
            size: 'XL-2XL',
            color: 'черный',
            discount: 15
        }
        // Добавьте остальные товары
    ];

    function renderItems(items) {
        const grid = document.getElementById('catalog-grid');
        if (!grid) return;

        grid.innerHTML = '';

        items.forEach(item => {
            const el = document.createElement('div');
            el.className = 'catalog-item';
            el.dataset.category = item.category;
            el.dataset.brand = item.brand.toLowerCase();
            el.dataset.size = item.size;
            el.dataset.color = item.color;
            el.dataset.price = item.price;

            const priceFormatted = item.price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ');

            el.innerHTML = `
                ${item.isNew ? '<div class="item-badge">Новинка</div>' : ''}
                <button class="item-favorite" aria-label="В избранное">
                    <svg><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"></path></svg>
                </button>
                <div class="item-image-container">
                    <img src="${item.images[0]}" alt="${item.title}" class="item-main-img">
                    ${item.images[1] ? `<img src="${item.images[1]}" alt="Вид сзади" class="item-hover-img">` : ''}
                </div>
                <div class="item-pagination">
                    <span class="pagination-dot active" data-index="0"></span>
                    ${item.images[1] ? '<span class="pagination-dot" data-index="1"></span>' : ''}
                </div>
                <div class="item-info">
                    <div class="item-brand">${item.brand}</div>
                    <h3 class="item-title">${item.title}</h3>
                    <div class="item-price">${priceFormatted} ₽</div>
                    <button class="item-buy-btn">Купить</button>
                </div>
            `;

            grid.appendChild(el);
        });
    }

    // === ЛОГИКА ФИЛЬТРОВ ===
    const filterSidebar = document.getElementById('catalog-filters');
    const toggleBtn = document.getElementById('mobile-filter-toggle');
    const closeBtn = document.getElementById('mobile-filter-close');

    // Показать фильтры
    function showFilters() {
        filterSidebar.style.transform = 'translateX(0)';
        document.body.style.overflow = 'hidden'; // запрет скролла
    }

    // Скрыть фильтры
    function hideFilters() {
        filterSidebar.style.transform = 'translateX(-100%)';
        document.body.style.overflow = ''; // разрешить скролл
    }

    // Обработчики
    if (toggleBtn && filterSidebar) {
        toggleBtn.addEventListener('click', showFilters);

        closeBtn.addEventListener('click', hideFilters);

        // Закрытие по клику вне фильтров (только на мобильных)
        document.addEventListener('click', (e) => {
            if (
                window.innerWidth <= 797 &&
                !filterSidebar.contains(e.target) &&
                e.target !== toggleBtn
            ) {
                hideFilters();
            }
        });
    }

    // Применение фильтров
    document.getElementById('apply-filters')?.addEventListener('click', function () {
        const filters = {
            category: [],
            brand: document.getElementById('filter-brand')?.value.trim().toLowerCase() || '',
            size: [],
            color: document.querySelector('input[name="filter-color"]:checked')?.value || '',
            minPrice: parseInt(document.getElementById('filter-min')?.value) || 0,
            maxPrice: parseInt(document.getElementById('filter-max')?.value) || 37990,
            discount: document.getElementById('filter-discount')?.value || ''
        };

        document.querySelectorAll('.filter-checkboxes input[type="checkbox"]:checked').forEach(cb => {
            const groupLabel = cb.closest('.filter-group').querySelector('.filter-group__label').textContent;
            if (groupLabel === 'Тип одежды') {
                filters.category.push(cb.value);
            } else if (groupLabel === 'Размер') {
                filters.size.push(cb.value);
            }
        });

        const filtered = catalogItems.filter(item => {
            if (filters.category.length && !filters.category.includes(item.category)) return false;
            if (filters.brand && !item.brand.toLowerCase().includes(filters.brand)) return false;
            if (filters.size.length && !filters.size.includes(item.size)) return false;
            if (filters.color && item.color !== filters.color) return false;
            if (item.price < filters.minPrice || item.price > filters.maxPrice) return false;
            return true;
        });

        renderItems(filtered);
        // alert(`Показано: ${filtered.length} из ${catalogItems.length} товаров`);
    });

    // Инициализация
    renderItems(catalogItems);
});

