
document.addEventListener('DOMContentLoaded', function() {
    // Чтение параметров из URL
    const urlParams = new URLSearchParams(window.location.search);
    const category = urlParams.get('category');
    const size = urlParams.get('size');
    const color = urlParams.get('color');
    const minPrice = urlParams.get('min');
    const maxPrice = urlParams.get('max');

    // Если есть параметры — заполняем и применяем фильтр
    if (category || size || color || minPrice || maxPrice) {
        // Устанавливаем чекбоксы/радио
        if (category) {
            const catCheckbox = document.querySelector(`.filter-checkboxes input[value="${category}"]`);
            if (catCheckbox) catCheckbox.checked = true;
        }
        if (size) {
            const sizeCheckbox = document.querySelector(`.filter-checkboxes input[value="${size}"]`);
            if (sizeCheckbox) sizeCheckbox.checked = true;
        }
        if (color) {
            const colorRadio = document.querySelector(`input[name="filter-color"][value="${color}"]`);
            if (colorRadio) colorRadio.checked = true;
        }
        if (minPrice) document.getElementById('filter-min').value = minPrice;
        if (maxPrice) document.getElementById('filter-max').value = maxPrice;

        // Автоматически применяем фильтр
        const applyBtn = document.getElementById('apply-filters');
        if (applyBtn) {
            applyBtn.click(); // ← Имитируем клик на кнопку "Применить"
        }
    }
});
