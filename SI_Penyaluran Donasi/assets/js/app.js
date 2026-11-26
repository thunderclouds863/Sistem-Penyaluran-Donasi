/* ==========================================================================
   Donasi Panti Asuhan — Shared UI Interactions
   ========================================================================== */

(function () {
  'use strict';

  // Sticky Header Shadow on Scroll
  const header = document.querySelector('.site-header');
  if (header) {
    const onScroll = () => {
      header.classList.toggle('is-scrolled', window.scrollY > 12);
    };
    window.addEventListener('scroll', onScroll, { passive: true });
    onScroll();
  }

  // Dropdown Menus
  document.querySelectorAll('[data-dropdown-toggle]').forEach((btn) => {
    btn.addEventListener('click', (e) => {
      e.stopPropagation();
      const wrap = btn.closest('.dropdown-wrap');
      const isOpen = wrap.classList.contains('is-open');
      document.querySelectorAll('.dropdown-wrap.is-open').forEach((el) => {
        el.classList.remove('is-open');
        const t = el.querySelector('[data-dropdown-toggle]');
        if (t) t.setAttribute('aria-expanded', 'false');
      });
      if (!isOpen) {
        wrap.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
  });

  document.addEventListener('click', () => {
    document.querySelectorAll('.dropdown-wrap.is-open').forEach((el) => {
      el.classList.remove('is-open');
      const t = el.querySelector('[data-dropdown-toggle]');
      if (t) t.setAttribute('aria-expanded', 'false');
    });
  });

  // Mobile Drawer
  const navToggle = document.querySelector('[data-nav-toggle]');
  const mobileDrawer = document.querySelector('[data-mobile-drawer]');
  if (navToggle && mobileDrawer) {
    navToggle.addEventListener('click', () => {
      const open = mobileDrawer.classList.toggle('is-open');
      navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      document.body.style.overflow = open ? 'hidden' : '';
    });

    mobileDrawer.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        mobileDrawer.classList.remove('is-open');
        navToggle.setAttribute('aria-expanded', 'false');
        document.body.style.overflow = '';
      });
    });
  }

  // Accessible Modals
  function openModal(id) {
    const modal = document.getElementById(id);
    if (!modal) return;
    modal.classList.add('is-open');
    modal.setAttribute('aria-hidden', 'false');
    document.body.style.overflow = 'hidden';
  }

  function closeModal(modal) {
    if (!modal) return;
    modal.classList.remove('is-open');
    modal.setAttribute('aria-hidden', 'true');
    document.body.style.overflow = '';
  }

  window.AppModal = { open: openModal, close: closeModal };

  document.querySelectorAll('[data-modal-open]').forEach((trigger) => {
    trigger.addEventListener('click', (e) => {
      e.preventDefault();
      openModal(trigger.getAttribute('data-modal-open'));
    });
  });

  document.querySelectorAll('[data-modal-close]').forEach((btn) => {
    btn.addEventListener('click', () => {
      closeModal(btn.closest('.app-modal-backdrop'));
    });
  });

  document.querySelectorAll('.app-modal-backdrop').forEach((backdrop) => {
    backdrop.addEventListener('click', (e) => {
      if (e.target === backdrop) closeModal(backdrop);
    });
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
      document.querySelectorAll('.app-modal-backdrop.is-open').forEach(closeModal);
    }
  });

  // Password Visibility Toggle
  document.querySelectorAll('[data-toggle-password]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-toggle-password');
      const input = document.getElementById(targetId);
      if (!input) return;
      const isPassword = input.type === 'password';
      input.type = isPassword ? 'text' : 'password';
      btn.setAttribute('aria-label', isPassword ? 'Sembunyikan password' : 'Tampilkan password');
      btn.innerHTML = isPassword
        ? '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>'
        : '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>';
    });
  });

  // Donation Center Live Filter
  const centerSearch = document.getElementById('centerSearchInput');
  if (centerSearch) {
    centerSearch.addEventListener('input', () => {
      const q = centerSearch.value.trim().toLowerCase();
      document.querySelectorAll('[data-center-card]').forEach((card) => {
        const text = card.getAttribute('data-search-text') || '';
        card.style.display = text.includes(q) ? '' : 'none';
      });
    });
  }

  // Quick Amount Chips on QRIS page
  const amountInput = document.getElementById('donation_amount');
  document.querySelectorAll('[data-amount-chip]').forEach((chip) => {
    chip.addEventListener('click', () => {
      if (!amountInput) return;
      amountInput.value = chip.getAttribute('data-amount-chip');
      document.querySelectorAll('[data-amount-chip]').forEach((c) => c.classList.remove('is-selected'));
      chip.classList.add('is-selected');
      amountInput.focus();
    });
  });
})();
