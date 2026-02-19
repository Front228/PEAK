document.addEventListener('DOMContentLoaded', function () {
    // Глобальные переменные
    const allProducts = initialProducts;

    // === ФУНКЦИИ ===
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

            const el = document.createElement('div');
            el.className = 'catalog-item';
            el.dataset.category = item.category;
            el.dataset.brand = item.brand.toLowerCase();
            el.dataset.size = item.size;
            el.dataset.color = item.color;
            el.dataset.price = item.price;

            let hoverImagesHtml = '';
            hoverImages.forEach((img, idx) => {
                hoverImagesHtml += `<img src="${img}" alt="${item.title} - ${idx + 2}" class="item-hover-img" data-index="${idx + 1}" style="display:none;">`;
            });

            el.innerHTML = `
                ${item.isNew ? '<div class="item-badge">Новинка</div>' : ''}
                <button class="item-favorite" aria-label="В избранное">
                    <svg><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"></path></svg>
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
                    <div class="item-price">${priceFormatted} ₽</div>
                    <button class="item-buy-btn">Купить</button>
                </div>
            `;

            grid.appendChild(el);
            initImageSlider(el, item.images);
        });
    }

    function initImageSlider(card, images) {
        const container = card.querySelector('.item-image-container');
        const mainImg = card.querySelector('.item-main-img');
        const hoverImgs = card.querySelectorAll('.item-hover-img');
        const dots = card.querySelectorAll('.pagination-dot');

        dots.forEach(dot => {
            dot.addEventListener('click', function () {
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

    // === СБРОС ФИЛЬТРОВ ===
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

    // === ПРИМЕНЕНИЕ ФИЛЬТРОВ ===
    document.getElementById('apply-filters')?.addEventListener('click', function () {
        const filters = {
            category: [],
            brand: document.getElementById('filter-brand')?.value.trim().toLowerCase() || '',
            size: [],
            color: document.querySelector('input[name="filter-color"]:checked')?.value || '',
            minPrice: parseInt(document.getElementById('filter-min')?.value) || 0,
            maxPrice: parseInt(document.getElementById('filter-max')?.value) || 37990
        };

        document.querySelectorAll('.filter-checkboxes input[type="checkbox"]:checked').forEach(cb => {
            const groupLabel = cb.closest('.filter-group').querySelector('.filter-group__label').textContent;
            if (groupLabel === 'Тип одежды') {
                filters.category.push(cb.value);
            } else if (groupLabel === 'Размер') {
                filters.size.push(cb.value);
            }
        });

        const filtered = allProducts.filter(item => {
            if (filters.category.length && !filters.category.includes(item.category)) return false;
            if (filters.brand && !item.brand.toLowerCase().includes(filters.brand)) return false;
            if (filters.size.length && !filters.size.includes(item.size)) return false;
            if (filters.color && item.color !== filters.color) return false;
            if (item.price < filters.minPrice || item.price > filters.maxPrice) return false;
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
        toggleBtn.addEventListener('click', () => {
            filterSidebar.style.transform = 'translateX(0)';
            document.body.style.overflow = 'hidden';
        });

        closeBtn.addEventListener('click', () => {
            filterSidebar.style.transform = 'translateX(-100%)';
            document.body.style.overflow = '';
        });

        document.addEventListener('click', (e) => {
            if (
                window.innerWidth <= 797 &&
                !filterSidebar.contains(e.target) &&
                e.target !== toggleBtn
            ) {
                filterSidebar.style.transform = 'translateX(-100%)';
                document.body.style.overflow = '';
            }
        });
    }

    // === ИНИЦИАЛИЗАЦИЯ ===
    renderItems(allProducts);
    updateProductCount(allProducts.length);
});