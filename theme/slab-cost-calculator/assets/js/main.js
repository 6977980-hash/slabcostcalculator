/**
 * SLAB COST CALCULATOR - CORE THEME SCRIPTS
 * Mobile menu, accordion FAQs, smooth interactions, and accessibility focus management.
 */

(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', () => {
    initMobileNav();
    initFAQAccordions();
  });

  // Mobile Navigation Drawer Toggle
  function initMobileNav() {
    const toggleBtn = document.querySelector('.mobile-menu-toggle');
    const navLinks = document.querySelector('.primary-nav .nav-links');

    if (!toggleBtn || !navLinks) return;

    toggleBtn.addEventListener('click', () => {
      const isExpanded = toggleBtn.getAttribute('aria-expanded') === 'true';
      toggleBtn.setAttribute('aria-expanded', !isExpanded);
      
      if (navLinks.style.display === 'flex') {
        navLinks.style.display = 'none';
      } else {
        navLinks.style.display = 'flex';
        navLinks.style.flexDirection = 'column';
        navLinks.style.position = 'absolute';
        navLinks.style.top = '72px';
        navLinks.style.left = '0';
        navLinks.style.right = '0';
        navLinks.style.backgroundColor = '#ffffff';
        navLinks.style.padding = '1rem';
        navLinks.style.borderBottom = '1px solid #e2e8f0';
        navLinks.style.boxShadow = '0 10px 15px -3px rgba(0, 0, 0, 0.1)';
      }
    });
  }

  // FAQ Accordions
  function initFAQAccordions() {
    const faqItems = document.querySelectorAll('.faq-item');
    if (!faqItems.length) return;

    faqItems.forEach(item => {
      const btn = item.querySelector('.faq-question');
      if (!btn) return;

      btn.addEventListener('click', () => {
        const isOpen = item.classList.contains('active');
        
        // Optional: close other open items in the same section
        // faqItems.forEach(i => i.classList.remove('active'));

        if (!isOpen) {
          item.classList.add('active');
          btn.setAttribute('aria-expanded', 'true');
        } else {
          item.classList.remove('active');
          btn.setAttribute('aria-expanded', 'false');
        }
      });
    });
  }

})();
