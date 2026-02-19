
window.addEventListener('scroll', e => {
    document.body.style.cssText = `--scrollTop: ${this.scrollY}px`
})

// swiper

document.addEventListener('DOMContentLoaded', () => {
const swiper = new Swiper('.fullpage-swiper', {
    effect: 'fade',
    fadeEffect: { crossFade: true },
    pagination: {
    el: '.swiper-pagination',
    clickable: true,
    },
    autoplay: {
    delay: 5000,
    disableOnInteraction: false,
    },
    loop: true,
    speed: 800
});
});

document.addEventListener('DOMContentLoaded', () => {
  // === Плавный скролл к следующему блоку ===
  const scrollDown = document.getElementById('scrollDown');
  const nextSection = document.getElementById('nextSection');

  if (scrollDown && nextSection) {
    scrollDown.addEventListener('click', (e) => {
      e.preventDefault();
      nextSection.scrollIntoView({
        behavior: 'smooth',
        block: 'start'
      });
    });
  }

  // === Пауза/воспроизведение (оставляем ваш рабочий код) ===
  const video = document.getElementById('mainVideo');
  const playPauseBtn = document.querySelector('.video-play-pause-btn');
  const iconImg = playPauseBtn?.querySelector('.play-pause-icon');

  if (video && playPauseBtn && iconImg) {
    const pauseIcon = 'public/icon/pause-button.png';
    const playIcon = 'public/icon/play-button.png';

    function updateButtonState() {
      if (video.paused) {
        iconImg.src = playIcon;
        playPauseBtn.classList.add('playing');
      } else {
        iconImg.src = pauseIcon;
        playPauseBtn.classList.remove('playing');
      }
    }

    playPauseBtn.addEventListener('click', () => {
      if (video.paused) {
        video.play().catch(err => console.warn('Play failed:', err));
      } else {
        video.pause();
      }
      updateButtonState();
    });

    video.addEventListener('play', updateButtonState);
    video.addEventListener('pause', updateButtonState);
    if (!video.paused) updateButtonState();
  }

  // === Sticky-скролл (опционально, если используется) ===
  const videoContainer = document.getElementById('videoContainer');
  if (videoContainer) {
    function handleScroll() {
      const containerTop = videoContainer.getBoundingClientRect().top;
      if (containerTop <= 0) {
        videoContainer.style.position = 'sticky';
        videoContainer.style.top = '0';
        videoContainer.style.width = '100vw';
        videoContainer.style.height = '100vh';
        videoContainer.style.zIndex = '100';
      } else {
        videoContainer.style.position = '';
        videoContainer.style.width = '';
        videoContainer.style.height = '';
      }
    }
    window.addEventListener('scroll', handleScroll);
    handleScroll();
  }
});

