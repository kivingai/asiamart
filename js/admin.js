document.addEventListener('DOMContentLoaded', () => {
  const navItems = document.querySelectorAll('.nav-item');
  const sections = document.querySelectorAll('.admin-section');

 
  navItems.forEach((item) => {
    item.addEventListener('click', () => {
      
      navItems.forEach((nav) => nav.classList.remove('active'));
  
      item.classList.add('active');
      const targetId = item.getAttribute('data-target');
      
      sections.forEach((sec) => {
        if (sec.id === targetId) {
          sec.classList.add('active');
        } else {
          sec.classList.remove('active');
        }
      });
    });
  });
});