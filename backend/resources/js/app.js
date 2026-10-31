import './bootstrap';
import 'flowbite';

document.addEventListener('DOMContentLoaded', () => {
  const splash = document.getElementById('splash-screen');
  if (splash) {
    setTimeout(() => splash.remove(), 700);
  }

  const menuBtn = document.getElementById('mobile-menu-btn');
  const mobileNav = document.getElementById('mobile-nav');
  if (menuBtn && mobileNav) {
    menuBtn.addEventListener('click', () => {
      mobileNav.classList.toggle('hidden');
      menuBtn.dataset.open = mobileNav.classList.contains('hidden') ? '0' : '1';
    });
  }

  const langToggle = document.querySelectorAll('[data-lang-toggle]');
  langToggle.forEach((btn) => {
    btn.addEventListener('click', () => {
      const lang = btn.dataset.langToggle;
      window.location.href = `/lang/${lang}`;
    });
  });
});
