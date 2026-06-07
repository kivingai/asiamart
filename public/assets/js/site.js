(() => {
  const applyHeaderState = () => {
    const header = document.getElementById('siteHeader');
    if (!header) return;

    let ticking = false;

    const update = () => {
      header.classList.toggle('scrolled', window.scrollY > 20);
      ticking = false;
    };

    update();

    window.addEventListener(
      'scroll',
      () => {
        if (ticking) return;
        ticking = true;
        window.requestAnimationFrame(update);
      },
      { passive: true }
    );
  };

  const applyActiveNavState = () => {
    const normalizedPath = (
      window.location.pathname.split('/').pop() || 'index.html'
    ).toLowerCase();
    const isCatalogPage = /katalog|catalog/.test(normalizedPath);

    const syncGroup = (selector) => {
      const links = document.querySelectorAll(selector);
      links.forEach((link) => {
        const href = (link.getAttribute('href') || '').toLowerCase();
        const isActive = isCatalogPage
          ? /katalog|catalog/.test(href)
          : href === normalizedPath;
        link.classList.toggle('active', isActive);
      });
    };

    syncGroup('.main-nav .nav-link');
    syncGroup('.mobile-nav .mobile-item');
  };

  const init = () => {
    applyHeaderState();
    applyActiveNavState();
  };

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init, { once: true });
    return;
  }

  init();
})();
