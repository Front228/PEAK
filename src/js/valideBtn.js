document.addEventListener('DOMContentLoaded', function() {
    const orderBtn = document.querySelector('.btn-buy');
    const formSection = document.querySelector('.block__form-order');
    const modal = document.getElementById('success-modal');
    const continueBtn = document.getElementById('continue-shopping');

    if (!orderBtn || !formSection || !modal || !continueBtn) return;

    // Сбор данных формы
    function getFormData() {
        return {
            surname: document.getElementById('surname').value.trim(),
            name: document.getElementById('name').value.trim(),
            phone: document.getElementById('phone').value.trim(),
            email: document.getElementById('email').value.trim(),
            comment: document.getElementById('order-coment').value.trim(),
            agreement1: document.getElementById('agreement1').checked,
            agreement2: document.getElementById('agreement2').checked
        };
    }

    // Подсветка ошибок
    function highlightErrors() {
        // Сброс стилей
        const fields = ['surname', 'name', 'phone', 'email'];
        fields.forEach(id => {
            const el = document.getElementById(id);
            el.style.borderColor = '#ddd';
            el.style.transition = 'border-color 0.3s ease';
        });

        ['agreement1', 'agreement2'].forEach(id => {
            const checkbox = document.getElementById(id);
            const label = checkbox.closest('label');
            label.style.color = '#333';
            label.style.transition = 'color 0.3s ease';
        });

        // Проверка и подсветка
        const data = getFormData();
        if (!data.surname) document.getElementById('surname').style.borderColor = '#ff6b35';
        if (!data.name) document.getElementById('name').style.borderColor = '#ff6b35';
        if (!data.phone) document.getElementById('phone').style.borderColor = '#ff6b35';
        if (!data.email) document.getElementById('email').style.borderColor = '#ff6b35';

        if (!data.agreement1) {
            document.getElementById('agreement1').closest('label').style.color = '#ff6b35';
        }
        if (!data.agreement2) {
            document.getElementById('agreement2').closest('label').style.color = '#ff6b35';
        }
    }

    // Отправка формы
    orderBtn.addEventListener('click', async function(e) {
        e.preventDefault();

        const data = getFormData();
        const isValid = data.surname && data.name && data.phone && data.email &&
                       data.agreement1 && data.agreement2;

        if (!isValid) {
            highlightErrors();
            // Плавная прокрутка к форме
            formSection.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start',
                inline: 'nearest'
            });
            return;
        }

        try {
            const response = await fetch('../php/handlers/submit_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(getFormData())
            });

            const result = await response.json();

            if (result.success) {
                modal.style.display = 'flex';
            } else {
                alert('Ошибка: ' + (result.errors?.join('\n') || 'Не удалось отправить заказ'));
                highlightErrors();
                formSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        } catch (err) {
            alert('Ошибка сети. Попробуйте позже.');
            console.error('Network error:', err);
        }
    });

    // Кнопка "Продолжить покупки"
    continueBtn.addEventListener('click', function() {
        modal.style.display = 'none';
        window.location.href = '/block/men.php';
    });
});