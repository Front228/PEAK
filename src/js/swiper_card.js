document.addEventListener('DOMContentLoaded', () => {
  // Находим все карточки
  const productCards = document.querySelectorAll('.product-card');

  productCards.forEach((card) => {
    // Получаем изображения из data-атрибута
    const images = JSON.parse(card.dataset.images);

    // Элементы внутри карточки
    const mainImage = card.querySelector('.product-main-image');
    const hoverImage = card.querySelector('.product-hover-image');
    const paginationDots = card.querySelectorAll('.pagination-dot');

    let currentIndex = 0;

    // === Переключение по клику на пагинацию ===
    paginationDots.forEach((dot, index) => {
      dot.addEventListener('click', () => {
        currentIndex = index;
        updateImages();
        updatePagination();
      });
    });

    // === Обновление изображений ===
    function updateImages() {
      mainImage.src = images[currentIndex];
      // Для hoverImage — следующее изображение (циклически)
      hoverImage.src = images[(currentIndex + 1) % images.length];
    }

    // === Обновление пагинации ===
    function updatePagination() {
      paginationDots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentIndex);
      });
    }

    // === Наведение — показываем hoverImage ===
    const imageContainer = card.querySelector('.product-image-container');
    imageContainer.addEventListener('mouseenter', () => {
      hoverImage.style.opacity = '1';
      mainImage.style.opacity = '0.5';
    });

    imageContainer.addEventListener('mouseleave', () => {
      hoverImage.style.opacity = '0';
      mainImage.style.opacity = '1';
    });

    // === Кнопка "Купить" ===
    const buyBtn = card.querySelector('.product-buy-btn');
    if (buyBtn) {
      buyBtn.addEventListener('click', () => {
        alert('Товар добавлен в корзину!');
        // Здесь можно добавить логику добавления в корзину
      });
    }

    // Инициализация
    updateImages();
    updatePagination();
  });
});