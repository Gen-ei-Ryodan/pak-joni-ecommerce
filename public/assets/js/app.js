(() => {
  const root = document.documentElement;

  const toggle = document.querySelector('[data-theme-toggle]');
  if (toggle) {
    toggle.addEventListener('click', () => {
      const next = root.dataset.theme === 'light' ? 'dark' : 'light';
      root.dataset.theme = next;
      localStorage.setItem('theme', next);
    });
  }

  const savedTheme = localStorage.getItem('theme');
  if (savedTheme) {
    root.dataset.theme = savedTheme;
  }

  const initCarousel = () => {
    const carousels = document.querySelectorAll('.carousel');
    carousels.forEach(carousel => {
      const items = carousel.querySelectorAll('.carousel-item');
      if (items.length === 0) return;

      let currentIndex = 0;
      const interval = carousel.dataset.interval ? parseInt(carousel.dataset.interval) : 5000;
      let timer;

      const showSlide = (index) => {
        items.forEach((item, i) => {
          item.classList.toggle('active', i === index);
        });
      };

      const nextSlide = () => {
        currentIndex = (currentIndex + 1) % items.length;
        showSlide(currentIndex);
      };

      const prevSlide = () => {
        currentIndex = (currentIndex - 1 + items.length) % items.length;
        showSlide(currentIndex);
      };

      const startTimer = () => {
        if (items.length > 1) {
          timer = setInterval(nextSlide, interval);
        }
      };

      const stopTimer = () => {
        if (timer) clearInterval(timer);
      };

      const prevBtn = carousel.querySelector('.carousel-control-prev');
      const nextBtn = carousel.querySelector('.carousel-control-next');

      if (prevBtn) {
        prevBtn.addEventListener('click', () => {
          stopTimer();
          prevSlide();
          startTimer();
        });
      }

      if (nextBtn) {
        nextBtn.addEventListener('click', () => {
          stopTimer();
          nextSlide();
          startTimer();
        });
      }

      showSlide(currentIndex);
      startTimer();
    });
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initCarousel);
  } else {
    initCarousel();
  }
})();
