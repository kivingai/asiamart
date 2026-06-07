document.addEventListener('DOMContentLoaded', () => {
  const navItems = document.querySelectorAll('.nav-item');
  const sections = document.querySelectorAll('.admin-section');

  const showSection = (targetId) => {
    navItems.forEach((item) => {
      item.classList.toggle('active', item.getAttribute('data-target') === targetId);
    });

    sections.forEach((section) => {
      section.classList.toggle('active', section.id === targetId);
    });
  };

  navItems.forEach((item) => {
    item.addEventListener('click', () => {
      const targetId = item.getAttribute('data-target');
      if (!targetId) return;
      showSection(targetId);
    });
  });
});
