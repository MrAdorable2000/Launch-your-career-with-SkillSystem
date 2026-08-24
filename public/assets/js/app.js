/* ============================================================
   SkillSystem — Professional SaaS Front-End Engine v3.0
   ============================================================ */
(function () {
  'use strict';

  /* 1. THEME MANAGER */
  const Theme = {
    init() {
      const saved = this.get();
      document.documentElement.setAttribute('data-theme', saved);
      this.syncIcons();
      document.addEventListener('click', (e) => {
        if (e.target.closest('[data-theme-toggle]')) {
          e.preventDefault();
          this.toggle();
        }
      });
    },
    get() {
      try { return localStorage.getItem('ss_theme') || (document.cookie.match(/ss_theme=(\w+)/) || [])[1] || 'light'; }
      catch (e) { return 'light'; }
    },
    set(theme) {
      try { localStorage.setItem('ss_theme', theme); } catch (e) {}
      document.cookie = 'ss_theme=' + theme + ';path=/;max-age=31536000;SameSite=Lax';
    },
    toggle() {
      const cur = document.documentElement.getAttribute('data-theme') || 'light';
      const next = cur === 'dark' ? 'light' : 'dark';
      document.documentElement.setAttribute('data-theme', next);
      this.set(next);
      this.syncIcons();
      window.dispatchEvent(new CustomEvent('ss-theme-change', { detail: { theme: next } }));
    },
    syncIcons() {
      const cur = document.documentElement.getAttribute('data-theme') || 'light';
      document.querySelectorAll('[data-theme-toggle]').forEach((el) => {
        const sun = el.querySelector('.sun-icon');
        const moon = el.querySelector('.moon-icon');
        if (sun && moon) {
          sun.style.display = cur === 'dark' ? 'block' : 'none';
          moon.style.display = cur === 'dark' ? 'none' : 'block';
        }
      });
    }
  };

  /* 2. PAGE LOADER */
  const Loader = {
    init() {
      window.addEventListener('load', () => {
        const l = document.querySelector('.ss-page-loader');
        if (l) setTimeout(() => l.classList.add('hide'), 200);
      });
    }
  };

  /* 3. ANIMATED COUNTERS */
  const Counters = {
    init() {
      const els = document.querySelectorAll('[data-count]');
      if (!els.length) return;
      const obs = new IntersectionObserver((entries) => {
        entries.forEach((e) => { if (e.isIntersecting) { this.run(e.target); obs.unobserve(e.target); } });
      }, { threshold: 0.4 });
      els.forEach((el) => obs.observe(el));
    },
    run(el) {
      const target = parseFloat(el.dataset.count);
      const dec = parseInt(el.dataset.decimals || '0', 10);
      const prefix = el.dataset.prefix || '';
      const suffix = el.dataset.suffix || '';
      const dur = parseInt(el.dataset.duration || '1500', 10);
      const start = performance.now();
      const step = (now) => {
        const t = Math.min((now - start) / dur, 1);
        const eased = 1 - Math.pow(1 - t, 3);
        el.textContent = prefix + Number(target * eased).toLocaleString(undefined, { minimumFractionDigits: dec, maximumFractionDigits: dec }) + suffix;
        if (t < 1) requestAnimationFrame(step);
      };
      requestAnimationFrame(step);
    }
  };

  /* 4. SCROLL REVEAL */
  const Reveal = {
    init() {
      const els = document.querySelectorAll('.ss-reveal');
      if (!els.length) return;
      const obs = new IntersectionObserver((entries) => {
        entries.forEach((e) => { if (e.isIntersecting) { e.target.classList.add('in-view'); obs.unobserve(e.target); } });
      }, { threshold: 0.15 });
      els.forEach((el) => obs.observe(el));
    }
  };

  /* 5. AJAX NOTIFICATIONS */
  const Notifs = {
    init() {
      const bell = document.querySelector('[data-notif-endpoint]');
      if (!bell) return;
      this.endpoint = bell.dataset.notifEndpoint;
      this.markReadUrl = bell.dataset.notifMarkRead || '/api/notifications/read';
      this.refresh();
      setInterval(() => this.refresh(), 30000);
      document.addEventListener('click', (e) => {
        if (e.target.closest('[data-mark-all-read]')) { e.preventDefault(); this.markAll(); }
        const item = e.target.closest('.notif-item[data-notif-id]');
        if (item) this.markOne(item.dataset.notifId);
      });
    },
    csrf() { return document.querySelector('meta[name="csrf-token"]')?.content || ''; },
    async refresh() {
      try {
        const r = await fetch(this.endpoint, { headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' });
        const d = await r.json();
        this.updateBadge(d.count || 0);
      } catch (e) {}
    },
    updateBadge(n) {
      document.querySelectorAll('[data-notif-badge]').forEach((el) => {
        if (n > 0) { el.textContent = n > 99 ? '99+' : n; el.style.display = 'inline-flex'; }
        else { el.style.display = 'none'; }
      });
    },
    async markAll() {
      try {
        await fetch(this.markReadUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': this.csrf() }, body: JSON.stringify({ all: true }), credentials: 'same-origin' });
        document.querySelectorAll('.notif-item.unread').forEach((el) => el.classList.remove('unread'));
        this.updateBadge(0);
      } catch (e) {}
    },
    async markOne(id) {
      try {
        await fetch(this.markReadUrl, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-Token': this.csrf() }, body: JSON.stringify({ id: parseInt(id, 10) }), credentials: 'same-origin' });
        const item = document.querySelector('.notif-item[data-notif-id="' + id + '"]');
        if (item) item.classList.remove('unread');
        document.querySelectorAll('[data-notif-badge]').forEach((el) => {
          let n = parseInt(el.textContent, 10) || 0; n = Math.max(0, n - 1);
          if (n > 0) { el.textContent = n > 99 ? '99+' : n; } else { el.style.display = 'none'; }
        });
      } catch (e) {}
    }
  };

  /* 6. TOAST */
  const Toast = {
    container: null,
    init() {
      this.container = document.querySelector('.ss-toast-container');
      if (!this.container) {
        this.container = document.createElement('div');
        this.container.className = 'ss-toast-container';
        this.container.style.cssText = 'position:fixed;top:1rem;right:1rem;z-index:9999;display:flex;flex-direction:column;gap:0.5rem;pointer-events:none;';
        document.body.appendChild(this.container);
      }
    },
    show(msg, type = 'info', dur = 4000) {
      if (!this.container) this.init();
      const icons = { success: 'fa-check-circle', error: 'fa-times-circle', info: 'fa-info-circle', warning: 'fa-exclamation-triangle' };
      const colors = { success: 'var(--ss-success)', error: 'var(--ss-danger)', info: 'var(--ss-info)', warning: 'var(--ss-warning)' };
      const t = document.createElement('div');
      t.style.cssText = 'background:var(--ss-surface);border:1px solid var(--ss-border);border-radius:var(--ss-r);padding:0.85rem 1.1rem;box-shadow:var(--ss-shadow-xl);min-width:280px;max-width:360px;display:flex;align-items:center;gap:0.75rem;pointer-events:auto;animation:ss-slide-in-right 0.3s ease;';
      t.innerHTML = '<i class="fas ' + (icons[type] || icons.info) + '" style="font-size:1.1rem;color:' + (colors[type] || colors.info) + '"></i><div style="flex:1;font-size:0.875rem;color:var(--ss-text);">' + msg + '</div>';
      this.container.appendChild(t);
      setTimeout(() => { t.style.opacity = '0'; t.style.transform = 'translateX(20px)'; setTimeout(() => t.remove(), 300); }, dur);
    }
  };
  window.ssToast = Toast;

  /* 7. FORM VALIDATION */
  const Validate = {
    init() {
      document.addEventListener('submit', (e) => {
        const f = e.target.closest('[data-validate]');
        if (!f) return;
        if (!this.check(f)) e.preventDefault();
      });
      document.addEventListener('blur', (e) => {
        const inp = e.target;
        if (!inp.matches('[data-validate] input, [data-validate] select, [data-validate] textarea')) return;
        this.checkField(inp);
      }, true);
    },
    check(form) {
      let ok = true;
      form.querySelectorAll('input, select, textarea').forEach((el) => { if (!this.checkField(el)) ok = false; });
      return ok;
    },
    checkField(el) {
      const val = (el.value || '').trim();
      const type = el.dataset.validate || '';
      const req = el.hasAttribute('required');
      let err = '';
      if (req && !val) err = 'This field is required.';
      else if (val) {
        if (type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val)) err = 'Please enter a valid email.';
        else if (type === 'url' && !/^https?:\/\/.+/.test(val)) err = 'Please enter a valid URL.';
        else if (type === 'phone' && !/^[\d\s+()-]{7,20}$/.test(val)) err = 'Please enter a valid phone number.';
        else if (el.dataset.min && val.length < parseInt(el.dataset.min, 10)) err = 'Must be at least ' + el.dataset.min + ' characters.';
        else if (el.dataset.match) {
          const other = document.querySelector('[name="' + el.dataset.match + '"]');
          if (other && other.value !== val) err = 'Values do not match.';
        }
      }
      const grp = el.closest('.ss-form-group, .ss-float');
      let errEl = grp && grp.querySelector('.ss-form-error');
      if (err) {
        el.style.borderColor = 'var(--ss-danger)';
        if (grp) grp.classList.add('has-error');
        if (!errEl && grp) { errEl = document.createElement('div'); errEl.className = 'ss-form-error'; errEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + err; grp.appendChild(errEl); }
        else if (errEl) errEl.innerHTML = '<i class="fas fa-exclamation-circle"></i> ' + err;
        return false;
      } else {
        el.style.borderColor = '';
        if (grp) grp.classList.remove('has-error');
        if (errEl) errEl.remove();
        return true;
      }
    }
  };

  /* 8. PASSWORD STRENGTH */
  const PwStrength = {
    init() {
      document.querySelectorAll('[data-password-strength]').forEach((el) => {
        const meter = document.createElement('div');
        meter.className = 'ss-pw-strength';
        meter.innerHTML = '<div class="ss-pw-strength-bar"><div class="ss-pw-strength-fill"></div></div><div class="ss-pw-strength-text">Enter a password</div>';
        // Insert the meter AFTER the password wrapper (outside .ss-pw-wrap) so it
        // doesn't affect the vertical centering of the eye toggle button
        const pwWrap = el.closest('.ss-pw-wrap');
        if (pwWrap && pwWrap.parentNode) {
          pwWrap.parentNode.insertBefore(meter, pwWrap.nextSibling);
        } else {
          el.parentNode.insertBefore(meter, el.nextSibling);
        }
        el.addEventListener('input', () => {
          const score = this.score(el.value);
          const fill = meter.querySelector('.ss-pw-strength-fill');
          const text = meter.querySelector('.ss-pw-strength-text');
          const pct = (score / 5) * 100;
          fill.style.width = pct + '%';
          fill.className = 'ss-pw-strength-fill';
          if (score <= 1) { fill.classList.add('pw-weak'); text.textContent = 'Weak'; text.style.color = 'var(--ss-danger)'; }
          else if (score <= 2) { fill.classList.add('pw-fair'); text.textContent = 'Fair'; text.style.color = 'var(--ss-warning)'; }
          else if (score <= 3) { fill.classList.add('pw-good'); text.textContent = 'Good'; text.style.color = 'var(--ss-info)'; }
          else { fill.classList.add('pw-strong'); text.textContent = 'Strong'; text.style.color = 'var(--ss-success)'; }
        });
      });
    },
    score(pw) {
      let s = 0;
      if (pw.length >= 8) s++;
      if (pw.length >= 12) s++;
      if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) s++;
      if (/\d/.test(pw)) s++;
      if (/[^A-Za-z0-9]/.test(pw)) s++;
      return s;
    }
  };

  /* 9. FILE UPLOAD PREVIEW */
  const FilePreview = {
    init() {
      document.addEventListener('change', (e) => {
        const inp = e.target.closest('[data-file-preview]');
        if (!inp) return;
        const target = document.querySelector(inp.dataset.filePreview);
        if (!target) return;
        const file = inp.files[0];
        if (!file) return;
        if (file.type.startsWith('image/')) {
          const r = new FileReader();
          r.onload = (ev) => { target.innerHTML = '<img src="' + ev.target.result + '" style="max-width:100%;border-radius:12px;">'; target.style.display = 'block'; };
          r.readAsDataURL(file);
        } else {
          target.innerHTML = '<div class="ss-card" style="padding:1rem;display:flex;align-items:center;gap:0.75rem;"><i class="fas fa-file" style="color:var(--ss-primary)"></i><div><div style="font-weight:600;font-size:0.875rem;">' + file.name + '</div><div style="font-size:0.75rem;color:var(--ss-text-3);">' + (file.size / 1024).toFixed(1) + ' KB</div></div></div>';
          target.style.display = 'block';
        }
      });
      document.querySelectorAll('.ss-file-upload').forEach((zone) => {
        const inp = zone.querySelector('input[type=file]') || document.querySelector('#' + zone.dataset.input);
        ['dragenter', 'dragover'].forEach((evt) => zone.addEventListener(evt, (e) => { e.preventDefault(); zone.classList.add('dragover'); }));
        ['dragleave', 'drop'].forEach((evt) => zone.addEventListener(evt, (e) => { e.preventDefault(); zone.classList.remove('dragover'); }));
        zone.addEventListener('drop', (e) => { if (inp && e.dataTransfer.files.length) { inp.files = e.dataTransfer.files; inp.dispatchEvent(new Event('change')); } });
        zone.addEventListener('click', (e) => { if (e.target === zone && inp) inp.click(); });
      });
    }
  };

  /* 10. TABLE ENHANCEMENTS */
  const Tables = {
    init() {
      document.querySelectorAll('[data-table]').forEach((w) => this.setup(w));
    },
    setup(wrap) {
      const search = wrap.querySelector('[data-table-search]');
      if (search) search.addEventListener('input', () => this.filter(wrap, search.value));
      wrap.querySelectorAll('th[data-sort]').forEach((th) => th.addEventListener('click', () => this.sort(wrap, th)));
      wrap.querySelectorAll('[data-table-export]').forEach((btn) => btn.addEventListener('click', (e) => { e.preventDefault(); this.export(wrap, btn.dataset.tableExport); }));
    },
    filter(wrap, q) {
      q = q.toLowerCase();
      wrap.querySelectorAll('tbody tr').forEach((tr) => { tr.style.display = tr.textContent.toLowerCase().includes(q) ? '' : 'none'; });
    },
    sort(wrap, th) {
      const idx = Array.from(th.parentNode.children).indexOf(th);
      const dir = th.classList.contains('sorted') && th.dataset.dir === 'asc' ? 'desc' : 'asc';
      wrap.querySelectorAll('th').forEach((t) => { t.classList.remove('sorted'); t.dataset.dir = ''; });
      th.classList.add('sorted'); th.dataset.dir = dir;
      const tbody = wrap.querySelector('tbody');
      const rows = Array.from(tbody.querySelectorAll('tr'));
      rows.sort((a, b) => {
        const av = (a.children[idx] || {}).textContent || '';
        const bv = (b.children[idx] || {}).textContent || '';
        const an = parseFloat(av.replace(/[^0-9.-]/g, ''));
        const bn = parseFloat(bv.replace(/[^0-9.-]/g, ''));
        if (!isNaN(an) && !isNaN(bn)) return dir === 'asc' ? an - bn : bn - an;
        return dir === 'asc' ? av.localeCompare(bv) : bv.localeCompare(av);
      });
      rows.forEach((r) => tbody.appendChild(r));
    },
    export(wrap, type) {
      const table = wrap.querySelector('table');
      if (!table) return;
      if (type === 'csv') {
        const rows = [];
        table.querySelectorAll('tr').forEach((tr) => {
          const cells = Array.from(tr.querySelectorAll('th, td')).filter((c) => !c.querySelector('input[type=checkbox]'));
          rows.push(cells.map((c) => '"' + (c.textContent || '').trim().replace(/"/g, '""') + '"').join(','));
        });
        const blob = new Blob([rows.join('\n')], { type: 'text/csv' });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a'); a.href = url; a.download = 'export-' + Date.now() + '.csv'; a.click();
        URL.revokeObjectURL(url);
        Toast.show('CSV exported', 'success');
      } else if (type === 'print') {
        const win = window.open('', '_blank');
        win.document.write('<html><head><title>Export</title></head><body>' + table.outerHTML + '</body></html>');
        win.document.close(); win.print();
      }
    }
  };

  /* 11. SIDEBAR */
  const Sidebar = {
    init() {
      document.addEventListener('click', (e) => {
        if (e.target.closest('[data-sidebar-toggle]')) {
          e.preventDefault();
          document.getElementById('ssSidebar')?.classList.toggle('show');
        }
        if (e.target.closest('[data-sidebar-collapse]')) {
          e.preventDefault();
          const sb = document.getElementById('ssSidebar');
          if (sb) { sb.classList.toggle('collapsed'); document.body.classList.toggle('sidebar-collapsed'); try { localStorage.setItem('ss_sidebar_collapsed', sb.classList.contains('collapsed') ? '1' : '0'); } catch (e) {} }
        }
        if (window.innerWidth < 992) {
          const sb = document.getElementById('ssSidebar');
          if (sb && sb.classList.contains('show') && !e.target.closest('#ssSidebar') && !e.target.closest('[data-sidebar-toggle]')) sb.classList.remove('show');
        }
      });
      try { if (localStorage.getItem('ss_sidebar_collapsed') === '1') { document.getElementById('ssSidebar')?.classList.add('collapsed'); document.body.classList.add('sidebar-collapsed'); } } catch (e) {}
    }
  };

  /* 12. TABS & FAQ */
  const Tabs = {
    init() {
      document.addEventListener('click', (e) => {
        const t = e.target.closest('[data-tab]');
        if (!t) return;
        e.preventDefault();
        const group = t.closest('[data-tabs]');
        if (!group) return;
        group.querySelectorAll('[data-tab]').forEach((x) => x.classList.remove('active'));
        t.classList.add('active');
        const target = group.querySelector(t.dataset.tab) || document.querySelector(t.dataset.tab);
        if (target) { group.querySelectorAll('.ss-tab-pane').forEach((p) => p.classList.remove('active')); target.classList.add('active'); }
      });
      document.addEventListener('click', (e) => {
        const h = e.target.closest('.ss-faq-header');
        if (!h) return;
        h.closest('.ss-faq-item')?.classList.toggle('open');
      });
    }
  };

  /* 13. ALERTS AUTO-DISMISS */
  const Alerts = {
    init() {
      document.querySelectorAll('.ss-alert').forEach((el) => {
        setTimeout(() => { el.style.transition = 'opacity 0.3s, transform 0.3s'; el.style.opacity = '0'; el.style.transform = 'translateY(-10px)'; setTimeout(() => el.remove(), 300); }, 5000);
      });
    }
  };

  /* 14. CHART.JS DEFAULTS */
  const Charts = {
    init() {
      if (typeof Chart === 'undefined') return;
      Chart.defaults.font.family = "'Poppins', sans-serif";
      Chart.defaults.font.size = 12;
      this.apply();
      window.addEventListener('ss-theme-change', () => this.apply());
    },
    apply() {
      if (typeof Chart === 'undefined') return;
      const css = (n) => getComputedStyle(document.documentElement).getPropertyValue(n).trim();
      Chart.defaults.color = css('--ss-text-2') || '#475569';
      Chart.defaults.borderColor = css('--ss-border') || '#E2E8F0';
      Chart.defaults.plugins.legend.labels.color = css('--ss-text') || '#0F172A';
      Chart.defaults.plugins.tooltip.backgroundColor = css('--ss-text') || '#0F172A';
      Chart.defaults.plugins.tooltip.titleColor = '#fff';
      Chart.defaults.plugins.tooltip.bodyColor = '#fff';
      Chart.defaults.plugins.tooltip.padding = 10;
      Chart.defaults.plugins.tooltip.cornerRadius = 8;
    }
  };

  /* 15. PASSWORD SHOW/HIDE TOGGLE */
  // Global function callable from onclick="ssTogglePw(this)"
  // Finds the password input inside the same wrapper as the button and toggles its type
  window.ssTogglePw = function(btn) {
    // Prevent the form from submitting
    if (event && event.preventDefault) event.preventDefault();
    // Find the input — it's a sibling of the button in the same wrapper
    const wrapper = btn.parentElement;
    let input = wrapper.querySelector('input[type="password"]');
    if (!input) input = wrapper.querySelector('input[type="text"]');
    if (!input) {
      // Fallback: look for the closest input in the form-group
      const formGroup = btn.closest('.ss-form-group, .ss-float, .ss-input-icon');
      if (formGroup) {
        input = formGroup.querySelector('input[type="password"]') || formGroup.querySelector('input[type="text"]');
      }
    }
    if (!input) return;
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    const icon = btn.querySelector('i');
    if (icon) icon.className = isHidden ? 'fas fa-eye-slash' : 'fas fa-eye';
    btn.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
    // Keep focus on the input so the user can continue typing
    setTimeout(() => input.focus(), 0);
  };

  const PasswordToggle = {
    init() {
      // Mark all password inputs that already have an inline toggle button so we don't duplicate
      document.querySelectorAll('input[type="password"]').forEach((input) => {
        const wrapper = input.parentElement;
        if (wrapper.querySelector('.ss-pw-toggle')) {
          input.dataset.ssToggle = '1';
        }
      });
      // Auto-add toggles only to password inputs that DON'T already have one
      document.querySelectorAll('input[type="password"]:not([data-ss-toggle="1"])').forEach((input) => {
        if (input.dataset.ssToggle === '1') return;
        input.dataset.ssToggle = '1';
        const wrapper = input.parentElement;
        if (getComputedStyle(wrapper).position === 'static') wrapper.style.position = 'relative';
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'ss-pw-toggle';
        btn.setAttribute('aria-label', 'Show password');
        btn.innerHTML = '<i class="fas fa-eye"></i>';
        btn.addEventListener('click', function(e) { e.preventDefault(); window.ssTogglePw(btn); });
        wrapper.appendChild(btn);
      });
    }
  };

  /* 16. INIT ALL */
  function init() {
    Theme.init(); Loader.init(); Sidebar.init(); Tabs.init();
    Counters.init(); Reveal.init(); Notifs.init(); Toast.init();
    Validate.init(); PwStrength.init(); FilePreview.init();
    Tables.init(); Alerts.init(); Charts.init();
    PasswordToggle.init();
  }

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init);
  else init();

  window.SS = { Theme, Toast };

  // Loading bar on navigation
  document.addEventListener('click', (e) => {
    const a = e.target.closest('a');
    if (!a) return;
    const href = a.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:') || a.target === '_blank' || a.hasAttribute('data-bs-toggle')) return;
    if (a.hostname && a.hostname !== window.location.hostname) return;
  });
})();
