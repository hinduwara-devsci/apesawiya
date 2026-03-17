// ===== NAVBAR SCROLL EFFECT =====
const navbar = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  navbar.classList.toggle('scrolled', window.scrollY > 20);
});

// ===== HAMBURGER MENU =====
const hamburger = document.getElementById('hamburger');
const navLinks  = document.getElementById('nav-links');
hamburger.addEventListener('click', () => {
  hamburger.classList.toggle('open');
  navLinks.classList.toggle('open');
});
// Close menu on link click
navLinks.querySelectorAll('.nav-link').forEach(link => {
  link.addEventListener('click', () => {
    hamburger.classList.remove('open');
    navLinks.classList.remove('open');
  });
});

// ===== HERO SLIDER =====
const slides   = document.querySelectorAll('.slide');
const dotsWrap = document.getElementById('sliderDots');
let current = 0, timer;

function goTo(index) {
  slides[current].classList.remove('active');
  dotsWrap.children[current].classList.remove('active');
  current = (index + slides.length) % slides.length;
  slides[current].classList.add('active');
  dotsWrap.children[current].classList.add('active');
}

function next() { goTo(current + 1); }
function prev() { goTo(current - 1); }
function startAuto() { timer = setInterval(next, 5000); }
function resetAuto()  { clearInterval(timer); startAuto(); }

// Build dots
slides.forEach((_, i) => {
  const dot = document.createElement('button');
  dot.className = 'dot' + (i === 0 ? ' active' : '');
  dot.setAttribute('aria-label', 'Go to slide ' + (i + 1));
  dot.addEventListener('click', () => { goTo(i); resetAuto(); });
  dotsWrap.appendChild(dot);
});

document.getElementById('sliderNext').addEventListener('click', () => { next(); resetAuto(); });
document.getElementById('sliderPrev').addEventListener('click', () => { prev(); resetAuto(); });
startAuto();

// ===== MEMBER TABLE =====
let currentPage = 1;
let currentSearch = '';

async function fetchMembers(page = 1, search = '') {
  currentPage = page;
  currentSearch = search;
  const tbody = document.getElementById('member-tbody');
  tbody.innerHTML = '<tr><td colspan="4" class="table-empty">Loading…</td></tr>';

  try {
    const url = `fetch_members.php?page=${encodeURIComponent(page)}&search=${encodeURIComponent(search)}`;
    const res  = await fetch(url);
    if (!res.ok) throw new Error('Network error');
    const data = await res.json();
    renderTable(data.members);
    renderPagination(data.totalPages, page);
  } catch (err) {
    tbody.innerHTML = '<tr><td colspan="4" class="table-empty">Failed to load members.</td></tr>';
  }
}

function renderTable(members) {
  const tbody = document.getElementById('member-tbody');
  if (!members || members.length === 0) {
    tbody.innerHTML = '<tr><td colspan="4" class="table-empty">No members found.</td></tr>';
    return;
  }
  tbody.innerHTML = members.map((m, i) => `
    <tr class="clickable-row" onclick="window.location.href='member_profile.html?id=${m.id}'" title="Click to view payment profile">
      <td>${m.id}</td>
      <td><strong>${escHtml(m.name)}</strong></td>
      <td>${escHtml(m.indexnum || '—')}</td>
      <td>${escHtml(m.idnum   || '—')}</td>
    </tr>
  `).join('');
}

function renderPagination(totalPages, activePage) {
  const wrap = document.getElementById('pagination');
  wrap.innerHTML = '';
  for (let i = 1; i <= totalPages; i++) {
    const btn = document.createElement('button');
    btn.textContent = i;
    if (i === activePage) btn.classList.add('active');
    btn.addEventListener('click', () => fetchMembers(i, currentSearch));
    wrap.appendChild(btn);
  }
}

function searchMembers() {
  const search = document.getElementById('search-input').value.trim();
  fetchMembers(1, search);
}

// Allow pressing Enter in search box
document.getElementById('search-input').addEventListener('keydown', e => {
  if (e.key === 'Enter') searchMembers();
});

// Escape HTML to prevent XSS
function escHtml(str) {
  const d = document.createElement('div');
  d.textContent = str;
  return d.innerHTML;
}

// Initial load
fetchMembers();
