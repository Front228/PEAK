    document.addEventListener('DOMContentLoaded', function () {
    // Функция для получения длительности из data-aos-duration (или по умолчанию)
    function getDuration(element) {
        const duration = element.dataset.aosDuration;
        return duration ? parseInt(duration) : 600; // в миллисекундах
    }

    function getDelay(element) {
        const delay = element.dataset.aosDelay;
        return delay ? parseInt(delay) : 0;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
        if (entry.isIntersecting) {
            const el = entry.target;
            const delay = getDelay(el);
            const duration = getDuration(el);

            // Установим длительность анимации через inline-стиль
            el.style.transitionDuration = `${duration / 1000}s`;

            // Запустим анимацию с задержкой
            setTimeout(() => {
            el.classList.add('scroll-visible');
            }, delay);

            // Один раз (как AOS с once: true)
            observer.unobserve(el);
        }
        });
    }, {
        threshold: 0.1, // начинать, когда 10% элемента видно
        rootMargin: '0px 0px -50px 0px' // запускать чуть раньше
    });

    // Наблюдаем за всеми элементами с data-aos
    document.querySelectorAll('[data-aos]').forEach(el => {
        observer.observe(el);
    });
    });