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

  const dropdowns = document.querySelectorAll('[data-dropdown-toggle]');
  dropdowns.forEach(btn => {
    const parent = btn.closest('.nav-dropdown');
    if (!parent) return;

    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const isOpen = parent.classList.contains('open');
      document.querySelectorAll('.nav-dropdown.open').forEach(d => d.classList.remove('open'));
      if (!isOpen) {
        parent.classList.add('open');
      }
    });
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.nav-dropdown')) {
      document.querySelectorAll('.nav-dropdown.open').forEach(d => d.classList.remove('open'));
    }
  });

  // Nested submenu toggle for mobile
  document.querySelectorAll('.nav-submenu-toggle').forEach(el => {
    el.addEventListener('click', (e) => {
      if (window.innerWidth <= 960) {
        e.preventDefault();
        const parent = el.closest('.nav-submenu');
        if (!parent) return;
        const isOpen = parent.classList.contains('open');
        parent.closest('.nav-dropdown-menu')?.querySelectorAll('.nav-submenu.open').forEach(d => d.classList.remove('open'));
        if (!isOpen) {
          parent.classList.add('open');
        }
      }
    });
  });

  const searchToggle = document.querySelector('[data-search-toggle]');
  const searchOverlay = document.querySelector('[data-search-overlay]');
  const searchClose = document.querySelector('[data-search-close]');
  const searchInput = document.querySelector('.search-input');

  if (searchToggle && searchOverlay) {
    searchToggle.addEventListener('click', () => {
      searchOverlay.style.display = 'flex';
      if (searchInput) setTimeout(() => searchInput.focus(), 100);
    });
  }

  if (searchClose && searchOverlay) {
    searchClose.addEventListener('click', () => {
      searchOverlay.style.display = 'none';
    });
  }

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && searchOverlay && searchOverlay.style.display === 'flex') {
      searchOverlay.style.display = 'none';
    }
  });

  const mobileToggle = document.querySelector('[data-mobile-toggle]');
  const navMenu = document.querySelector('[data-nav-menu]');
  if (mobileToggle && navMenu) {
    mobileToggle.addEventListener('click', () => {
      navMenu.classList.toggle('mobile-open');
      mobileToggle.classList.toggle('active');
    });
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
