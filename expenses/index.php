<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Expense Tracker</title>

<!-- PWA -->
<link rel="manifest" href="/expenses/manifest.json">
<meta name="theme-color" content="#3fb950">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="Expenses">
<link rel="apple-touch-icon" href="/expenses/icons/icon-192.png">

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<style>
:root{--bg:#0d1117;--surface:#161b22;--surface2:#21262d;--surface3:#30363d;--border:#30363d;--text:#e6edf3;--muted:#7d8590;--green:#3fb950;--red:#f85149;--blue:#58a6ff;--orange:#ffa657;--purple:#bc8cff;--gold:#d29922;--accent:#3fb950;--accent-bg:#238636}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:var(--bg);color:var(--text);min-height:100vh;font-size:14px;padding-bottom:env(safe-area-inset-bottom)}
a{color:var(--blue);text-decoration:none}

/* ── Login ── */
#login-screen{position:fixed;inset:0;background:var(--bg);display:flex;align-items:center;justify-content:center;z-index:1000}
.login-box{background:var(--surface);border:1px solid var(--border);border-radius:12px;padding:32px 28px;width:100%;max-width:360px}
.login-logo{font-size:22px;font-weight:700;text-align:center;margin-bottom:6px}.login-logo span{color:var(--accent)}
.login-sub{color:var(--muted);text-align:center;font-size:13px;margin-bottom:24px}
.field{margin-bottom:14px}.field label{display:block;font-size:12px;color:var(--muted);margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px}
.field input{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:9px 12px;color:var(--text);font-size:14px;outline:none}
.field input:focus{border-color:var(--accent)}
#login-btn{width:100%;padding:10px;background:var(--accent-bg);color:#fff;border:none;border-radius:6px;font-size:15px;font-weight:600;cursor:pointer;margin-top:4px}
#login-btn:hover{opacity:.85}
#login-err{color:var(--red);font-size:12px;text-align:center;margin-top:10px;min-height:16px}

/* ── Layout ── */
#app{display:none}
.topbar{background:var(--surface);border-bottom:1px solid var(--border);padding:0 20px;height:52px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:100}
.topbar-logo{font-size:16px;font-weight:700}.topbar-logo span{color:var(--accent)}
.topbar-user{display:flex;align-items:center;gap:12px;font-size:13px;color:var(--muted)}
.btn-signout{background:none;border:1px solid var(--border);color:var(--muted);border-radius:6px;padding:4px 10px;cursor:pointer;font-size:12px}
.btn-signout:hover{color:var(--text);border-color:var(--text)}
.nav{background:var(--surface);border-bottom:1px solid var(--border);display:flex;gap:4px;padding:0 16px}
.nav-btn{padding:10px 16px;background:none;border:none;color:var(--muted);cursor:pointer;font-size:13px;font-weight:500;border-bottom:2px solid transparent;transition:all .15s}
.nav-btn:hover{color:var(--text)}
.nav-btn.active{color:var(--accent);border-bottom-color:var(--accent)}
.main{max-width:1100px;margin:0 auto;padding:20px 16px}

/* ── Cards ── */
.cards{display:grid;grid-template-columns:repeat(auto-fill,minmax(160px,1fr));gap:12px;margin-bottom:20px}
.card{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:13px 15px}
.cl{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px}
.cv{font-size:20px;font-weight:700}
.cs{font-size:11px;color:var(--muted);margin-top:3px}
.pos{color:var(--green)}.neg{color:var(--red)}

/* ── Section header ── */
.sh{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;flex-wrap:wrap;gap:8px}
.st{font-size:16px;font-weight:700}
.month-nav{display:flex;align-items:center;gap:8px}
.month-nav button{background:var(--surface2);border:1px solid var(--border);color:var(--text);border-radius:6px;padding:5px 10px;cursor:pointer;font-size:13px}
.month-nav button:hover{background:var(--surface3)}
.month-label{font-size:14px;font-weight:600;min-width:120px;text-align:center}

/* ── Buttons ── */
.btn-p{background:var(--accent-bg);color:#fff;border:none;border-radius:6px;padding:8px 16px;cursor:pointer;font-size:13px;font-weight:600}
.btn-p:hover{opacity:.85}
.btn-s{background:var(--surface2);border:1px solid var(--border);color:var(--text);border-radius:6px;padding:6px 12px;cursor:pointer;font-size:12px}
.btn-s:hover{background:var(--surface3)}
.btn-d{background:none;border:1px solid rgba(248,81,73,.4);color:var(--red);border-radius:6px;padding:5px 10px;cursor:pointer;font-size:12px}
.btn-d:hover{background:rgba(248,81,73,.1)}
.btn-ai{background:rgba(88,166,255,.15);border:1px solid rgba(88,166,255,.4);color:var(--blue);border-radius:6px;padding:8px 14px;cursor:pointer;font-size:13px;width:100%;margin-top:8px;font-weight:500}
.btn-ai:hover{background:rgba(88,166,255,.25)}
.btn-ai:disabled{opacity:.5;cursor:not-allowed}

/* ── Table ── */
.tb{background:var(--surface);border:1px solid var(--border);border-radius:8px;overflow:hidden;margin-bottom:16px}
.tbh{padding:12px 16px;border-bottom:1px solid var(--border);display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap}
.tbt{font-size:14px;font-weight:600}
table{width:100%;border-collapse:collapse}
th{padding:8px 12px;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.5px;color:var(--muted);border-bottom:1px solid var(--border);white-space:nowrap}
td{padding:9px 12px;border-bottom:1px solid var(--border);font-size:13px;vertical-align:middle}
tr:last-child td{border-bottom:none}
tr:hover td{background:var(--surface2)}
.empty-row{text-align:center;color:var(--muted);padding:24px!important}

/* ── Badge ── */
.badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:11px;font-weight:500}
.b-travel{background:rgba(255,166,87,.15);color:var(--orange)}
.b-equipment{background:rgba(188,140,255,.15);color:var(--purple)}
.b-software{background:rgba(88,166,255,.15);color:var(--blue)}
.b-food{background:rgba(63,185,80,.15);color:var(--green)}
.b-accom-work{background:rgba(88,166,255,.15);color:#1a7fc1}
.b-accom-ent{background:rgba(255,100,150,.15);color:#c0396b}
.b-other{background:rgba(125,133,144,.15);color:var(--muted)}

/* ── Photo thumb ── */
.thumb{width:44px;height:44px;border-radius:6px;object-fit:cover;cursor:pointer;border:1px solid var(--border)}
.thumb-placeholder{width:44px;height:44px;border-radius:6px;background:var(--surface2);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:18px;color:var(--muted);cursor:pointer}

/* ── Modal ── */
.overlay{position:fixed;inset:0;background:rgba(0,0,0,.7);z-index:500;display:none;align-items:flex-start;justify-content:center;padding:20px;overflow-y:auto}
.overlay.open{display:flex}
.modal{background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:22px;width:100%;max-width:480px;margin:auto}
.modal h3{font-size:16px;font-weight:700;margin-bottom:16px}
.frow{display:flex;gap:10px}
.fg{flex:1;margin-bottom:12px}
.fg label{display:block;font-size:11px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:5px}
.fg input,.fg select,.fg textarea{width:100%;background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:8px 10px;color:var(--text);font-size:13px;outline:none}
.fg input:focus,.fg select:focus{border-color:var(--accent)}
.fg select option{background:var(--surface2)}
.ma{display:flex;gap:8px;justify-content:flex-end;margin-top:16px}

/* ── Photo upload area ── */
.photo-area{border:2px dashed var(--border);border-radius:8px;padding:18px;text-align:center;cursor:pointer;margin-bottom:4px;transition:border-color .2s}
.photo-area:hover{border-color:var(--accent)}
.photo-area img{max-width:100%;max-height:200px;border-radius:6px;object-fit:contain;cursor:zoom-in}
.photo-actions{display:flex;gap:8px;margin-top:6px;margin-bottom:4px}
.photo-hint{font-size:11px;color:var(--muted);text-align:center;margin-top:4px}

/* ── Lightbox ── */
#lightbox{position:fixed;inset:0;background:rgba(0,0,0,.95);z-index:9999;display:none;flex-direction:column;align-items:center;justify-content:center}
#lightbox-close{position:absolute;top:16px;right:16px;background:rgba(255,255,255,.15);border:none;color:#fff;font-size:20px;width:40px;height:40px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;z-index:10000}
#lightbox-hint{position:absolute;bottom:20px;color:rgba(255,255,255,.5);font-size:12px;pointer-events:none}
#lightbox-img-wrap{overflow:hidden;width:100vw;height:100vh;display:flex;align-items:center;justify-content:center}
#lightbox-img{max-width:95vw;max-height:95vh;border-radius:6px;object-fit:contain;transform-origin:center center;transition:transform .1s;user-select:none;touch-action:none}

/* ── Chart ── */
.chart-wrap{background:var(--surface);border:1px solid var(--border);border-radius:8px;padding:16px;margin-bottom:16px}

/* ── Filters ── */
.filters{display:flex;gap:8px;flex-wrap:wrap;margin-bottom:14px;align-items:center}
.filters select,.filters input{background:var(--surface2);border:1px solid var(--border);border-radius:6px;padding:6px 10px;color:var(--text);font-size:13px;outline:none}

/* ── User filter tabs (admin) ── */
.user-tabs{display:flex;gap:6px;flex-wrap:wrap;margin-bottom:14px}
.user-tab{padding:5px 14px;border-radius:20px;border:1px solid var(--border);background:var(--surface2);color:var(--muted);cursor:pointer;font-size:12px}
.user-tab.active{background:var(--accent-bg);border-color:var(--accent);color:#fff}

/* ── Responsive ── */
@media(max-width:680px){
  .frow{flex-direction:column;gap:0}
  .cards{grid-template-columns:1fr 1fr}
  table{font-size:12px}
  th,td{padding:7px 8px}
  .topbar{padding:0 12px}
  .main{padding:14px 10px}
}
</style>
</head>
<body>

<!-- LOGIN -->
<div id="login-screen">
  <div class="login-box">
    <div class="login-logo">🧾 <span>Expense</span>Tracker</div>
    <div class="login-sub">Sign in to continue</div>
    <div class="field"><label>Username</label><input id="l-user" type="text" placeholder="Enter username" autocomplete="username"></div>
    <div class="field"><label>Password</label><input id="l-pass" type="password" placeholder="Enter password" autocomplete="current-password" onkeydown="if(event.key==='Enter')doLogin()"></div>
    <button id="login-btn" onclick="doLogin()">Sign In</button>
    <div id="login-err"></div>
  </div>
</div>

<!-- MAIN APP -->
<div id="app">
  <div class="topbar">
    <div class="topbar-logo">🧾 <span>Expense</span>Tracker</div>
    <div class="topbar-user">
      <span id="topbar-name"></span>
      <span id="topbar-role" style="font-size:11px;background:var(--surface2);padding:2px 8px;border-radius:10px"></span>
      <button class="btn-signout" onclick="doLogout()">Sign out</button>
    </div>
  </div>
  <div class="nav">
    <button class="nav-btn active" data-page="dashboard" onclick="showPage('dashboard')">📊 Dashboard</button>
    <button class="nav-btn" data-page="expenses" onclick="showPage('expenses')">🧾 Expenses</button>
    <button class="nav-btn" data-page="report" onclick="showPage('report')">📈 Report</button>
    <button class="nav-btn" id="nav-settings" data-page="settings" onclick="showPage('settings')" style="display:none">⚙️ Settings</button>
  </div>
  <div class="main">

    <!-- DASHBOARD -->
    <div id="page-dashboard" class="page-content">
      <div class="sh">
        <span class="st">📊 Dashboard</span>
        <div class="month-nav">
          <button onclick="changeMonth(-1)">‹</button>
          <span class="month-label" id="dash-month-label"></span>
          <button onclick="changeMonth(1)">›</button>
        </div>
      </div>
      <div id="dash-cards" class="cards"></div>
      <div class="tb">
        <div class="tbh"><span class="tbt">Recent Expenses</span><button class="btn-p" onclick="showPage('expenses');openModal()">+ Add Expense</button></div>
        <div style="overflow-x:auto"><table><thead><tr><th>Photo</th><th>Date</th><th>Company</th><th>Category</th><th>Amount</th><th id="dash-user-th" style="display:none">User</th></tr></thead>
        <tbody id="dash-tbody"></tbody></table></div>
      </div>
    </div>

    <!-- EXPENSES -->
    <div id="page-expenses" class="page-content" style="display:none">
      <div class="sh">
        <span class="st">🧾 Expenses</span>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
          <div class="month-nav">
            <button onclick="changeMonth(-1)">‹</button>
            <span class="month-label" id="exp-month-label"></span>
            <button onclick="changeMonth(1)">›</button>
          </div>
          <button class="btn-p" onclick="openModal()">+ Add Expense</button>
        </div>
      </div>
      <div id="user-tabs" class="user-tabs" style="display:none"></div>
      <div class="tb">
        <div style="overflow-x:auto"><table><thead><tr><th>Photo</th><th>Date</th><th>Company</th><th>Category</th><th>Amount</th><th id="exp-user-th" style="display:none">User</th><th>Note</th><th></th></tr></thead>
        <tbody id="exp-tbody"></tbody></table></div>
      </div>
    </div>

    <!-- REPORT -->
    <div id="page-report" class="page-content" style="display:none">
      <div class="sh">
        <span class="st">📈 Monthly Report</span>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
          <div class="month-nav">
            <button onclick="changeMonth(-1)">‹</button>
            <span class="month-label" id="rep-month-label"></span>
            <button onclick="changeMonth(1)">›</button>
          </div>
          <button class="btn-s" onclick="exportCSV()">⬇ Export CSV</button>
          <button class="btn-s" onclick="downloadZip()">📦 Download ZIP</button>
          <button class="btn-s" onclick="window.print()">🖨 Print</button>
        </div>
      </div>
      <div id="rep-cards" class="cards"></div>
      <div class="chart-wrap" style="max-width:500px"><canvas id="rep-chart" height="220"></canvas></div>
      <div class="frow" style="gap:16px;flex-wrap:wrap">
        <div class="tb" style="flex:1;min-width:240px">
          <div class="tbh"><span class="tbt">By Category</span></div>
          <table><tbody id="rep-cat-tbody"></tbody></table>
        </div>
        <div class="tb" id="rep-user-box" style="flex:1;min-width:240px;display:none">
          <div class="tbh"><span class="tbt">By User</span></div>
          <table><tbody id="rep-user-tbody"></tbody></table>
        </div>
      </div>
      <div class="tb" style="margin-top:0">
        <div class="tbh"><span class="tbt">All Expenses</span></div>
        <div style="overflow-x:auto"><table><thead><tr><th>Date</th><th>Company</th><th>Category</th><th>Amount</th><th id="rep-user-th" style="display:none">User</th><th>Note</th></tr></thead>
        <tbody id="rep-tbody"></tbody></table></div>
      </div>
    </div>

    <!-- SETTINGS (admin only) -->
    <div id="page-settings" class="page-content" style="display:none">
      <div class="sh"><span class="st">⚙️ Settings</span></div>
      <div class="tb">
        <div class="tbh"><span class="tbt">Manage Users</span></div>
        <div style="overflow-x:auto"><table>
          <thead><tr><th>Name</th><th>Username</th><th>Role</th><th></th></tr></thead>
          <tbody id="settings-users-tbody"></tbody>
        </table></div>
      </div>
    </div>

  </div>
</div>

<!-- EDIT USER MODAL -->
<div class="overlay" id="edit-user-ov" onclick="if(event.target===this)closeEditUser()">
  <div class="modal">
    <h3>✏️ Edit User</h3>
    <input type="hidden" id="eu-id">
    <div class="fg"><label>Full Name</label><input id="eu-name" placeholder="Full name"></div>
    <div class="fg"><label>Username</label><input id="eu-username" placeholder="Username" autocomplete="off"></div>
    <div class="fg"><label>New Password <span style="color:var(--muted);font-size:11px">(leave blank to keep current)</span></label>
      <input type="password" id="eu-password" placeholder="New password" autocomplete="new-password"></div>
    <div style="display:flex;gap:8px;margin-top:16px">
      <button class="btn-p" style="flex:1" onclick="saveUser()">💾 Save</button>
      <button class="btn-s" style="flex:1" onclick="closeEditUser()">Cancel</button>
    </div>
    <div id="eu-msg" style="margin-top:10px;font-size:13px;text-align:center"></div>
  </div>
</div>

<!-- LIGHTBOX -->
<div id="lightbox">
  <button id="lightbox-close" onclick="closeLightbox()">✕</button>
  <div id="lightbox-img-wrap">
    <img id="lightbox-img" src="">
  </div>
  <div id="lightbox-hint">Pinch to zoom · Double-tap to reset</div>
</div>

<!-- ADD/EDIT MODAL -->
<div class="overlay" id="modal-ov">
  <div class="modal">
    <h3 id="modal-title">🧾 Add Expense</h3>
    <div class="photo-area" id="photo-area" onclick="document.getElementById('photo-input').click()">
      <div id="photo-empty"><div style="font-size:36px;margin-bottom:6px">📎</div><div style="font-weight:600">Tap to add photo or PDF</div><div style="color:var(--muted);font-size:12px;margin-top:4px">Choose from gallery, camera or files</div></div>
      <div id="photo-preview" style="display:none">
        <img id="photo-img" src="" onclick="event.stopPropagation();openLightboxFromModal()" title="Tap to zoom">
      </div>
    </div>
    <div id="photo-actions" class="photo-actions" style="display:none">
      <button class="btn-s" style="flex:1" onclick="document.getElementById('photo-input').click()">🔄 Change Photo</button>
      <button class="btn-s" style="flex:1" onclick="openLightboxFromModal()">🔍 View Full Size</button>
    </div>
    <div id="photo-hint-text" class="photo-hint" style="display:none">Tap photo or "View Full Size" to zoom in and read the invoice</div>
    <input type="file" id="photo-input" accept="image/*,application/pdf" style="display:none" onchange="handlePhoto(event)">
    <button class="btn-ai" id="btn-extract" style="display:none" onclick="extractFromPhoto()">🤖 Auto-extract from photo</button>
    <div class="frow" style="margin-top:12px">
      <div class="fg"><label>Date</label><input type="date" id="m-date"></div>
      <div class="fg"><label>Category</label>
        <select id="m-cat">
          <option value="transport_work">🚗 Transport Work</option>
          <option value="transport_entertainment">🎉 Transport Entertainment</option>
          <option value="accommodation_work">🏨 Accommodation Work</option>
          <option value="accommodation_entertainment">🛎 Accommodation Entertainment</option>
          <option value="equipment">🖥 Equipment & Supplies</option>
          <option value="software">💻 Software & Subscriptions</option>
          <option value="other">📋 Other</option>
        </select>
      </div>
    </div>
    <div class="fg"><label>Company / Issuer</label><input id="m-company" placeholder="e.g. Amazon, Ryanair"></div>
    <div class="frow">
      <div class="fg"><label>Amount</label><input type="number" step="0.01" min="0" id="m-amount" placeholder="0.00"></div>
      <div class="fg"><label>Currency</label>
        <select id="m-currency">
          <option value="EUR">EUR €</option>
          <option value="USD">USD $</option>
          <option value="GBP">GBP £</option>
          <option value="CHF">CHF</option>
        </select>
      </div>
    </div>
    <div class="fg"><label>Note (optional)</label><input id="m-note" placeholder="Any additional details"></div>
    <div class="ma">
      <button class="btn-s" onclick="closeModal()">Cancel</button>
      <button class="btn-p" onclick="saveExpense()">Save</button>
    </div>
  </div>
</div>

<script>
// ── State ──────────────────────────────────────────────────────
let currentUser = null;
let currentMonth = new Date().toISOString().slice(0,7);
let currentPage  = 'dashboard';
let editingId    = null;
let photoFile    = null;
let photoPreviewData = null;
let allUsers     = [];
let filterUserId = null;
let reportChart  = null;
let expenseCache = [];

// ── Helpers ────────────────────────────────────────────────────
const api = (action, opts={}) => fetch(`api.php?action=${action}`, {method:'GET', ...opts}).then(r=>r.json());
const post = (action, body) => fetch(`api.php?action=${action}`, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(body)}).then(r=>r.json());
const monthLabel = m => { const [y,mo]=m.split('-'); return new Date(+y,+mo-1,1).toLocaleString('en',{month:'long',year:'numeric'}); };
const fm = (v,cur='EUR') => new Intl.NumberFormat('en-US',{style:'currency',currency:cur,minimumFractionDigits:2}).format(v);
const catLabel = {'transport_work':'🚗 Transport Work','transport_entertainment':'🎉 Transport Entert.','accommodation_work':'🏨 Accommodation Work','accommodation_entertainment':'🛎 Accommodation Entert.','equipment':'🖥 Equipment','software':'💻 Software','other':'📋 Other'};
const catClass = {'transport_work':'b-travel','transport_entertainment':'b-food','accommodation_work':'b-accom-work','accommodation_entertainment':'b-accom-ent','equipment':'b-equipment','software':'b-software','other':'b-other'};

// ── Init ───────────────────────────────────────────────────────
(async () => {
  const res = await api('me');
  if (res.user) startApp(res.user);
})();

// ── Login / Logout ─────────────────────────────────────────────
async function doLogin() {
  const username = document.getElementById('l-user').value.trim();
  const password = document.getElementById('l-pass').value;
  const err = document.getElementById('login-err');
  if (!username || !password) { err.textContent = 'Please enter username and password'; return; }
  const btn = document.getElementById('login-btn');
  btn.disabled = true; btn.textContent = 'Signing in...';
  const res = await post('login', {username, password});
  btn.disabled = false; btn.textContent = 'Sign In';
  if (res.error) { err.textContent = res.error; document.getElementById('l-pass').value = ''; return; }
  startApp(res.user);
}

async function doLogout() {
  await post('logout', {});
  currentUser = null;
  document.getElementById('app').style.display = 'none';
  document.getElementById('login-screen').style.display = 'flex';
  document.getElementById('l-user').value = '';
  document.getElementById('l-pass').value = '';
  document.getElementById('login-err').textContent = '';
}

function startApp(user) {
  currentUser = user;
  document.getElementById('login-screen').style.display = 'none';
  document.getElementById('app').style.display = 'block';
  document.getElementById('topbar-name').textContent = user.name;
  document.getElementById('topbar-role').textContent = user.role;
  if (user.role === 'admin') {
    loadUsers();
    document.getElementById('nav-settings').style.display = '';
  }
  renderCurrentMonth();
  loadPage('dashboard');
}

// ── Navigation ─────────────────────────────────────────────────
function showPage(page) {
  currentPage = page;
  document.querySelectorAll('.page-content').forEach(p => p.style.display = 'none');
  document.getElementById('page-' + page).style.display = 'block';
  document.querySelectorAll('.nav-btn').forEach(b => b.classList.toggle('active', b.dataset.page === page));
  loadPage(page);
}

function loadPage(page) {
  if (page === 'dashboard') loadDashboard();
  else if (page === 'expenses') loadExpenses();
  else if (page === 'report') loadReport();
  else if (page === 'settings') loadSettings();
}

function renderCurrentMonth() {
  ['dash','exp','rep'].forEach(p => {
    const el = document.getElementById(p + '-month-label');
    if (el) el.textContent = monthLabel(currentMonth);
  });
}

function changeMonth(dir) {
  const [y,m] = currentMonth.split('-');
  const d = new Date(Date.UTC(+y, +m - 1 + dir, 1));
  currentMonth = d.toISOString().slice(0,7);
  renderCurrentMonth();
  loadPage(currentPage);
}

// ── Users (admin) ──────────────────────────────────────────────
async function loadUsers() {
  const res = await api('users');
  if (res.users) allUsers = res.users;
}

function loadSettings() {
  const tbody = document.getElementById('settings-users-tbody');
  if (!tbody) return;
  tbody.innerHTML = allUsers.map(u => `
    <tr>
      <td>${u.name}</td>
      <td><code style="background:var(--surface);padding:2px 6px;border-radius:4px;font-size:12px">${u.username}</code></td>
      <td><span class="badge ${u.role==='admin'?'b-software':'b-other'}">${u.role}</span></td>
      <td><button class="btn-s" onclick="openEditUser(${u.id})">✏️ Edit</button></td>
    </tr>`).join('') || '<tr><td colspan="4" class="empty-row">No users found.</td></tr>';
}

function openEditUser(id) {
  const u = allUsers.find(x => x.id == id);
  if (!u) return;
  document.getElementById('eu-id').value       = u.id;
  document.getElementById('eu-name').value     = u.name;
  document.getElementById('eu-username').value = u.username;
  document.getElementById('eu-password').value = '';
  document.getElementById('eu-msg').textContent = '';
  document.getElementById('edit-user-ov').style.display = 'flex';
}

function closeEditUser() {
  document.getElementById('edit-user-ov').style.display = 'none';
}

async function saveUser() {
  const id       = document.getElementById('eu-id').value;
  const name     = document.getElementById('eu-name').value.trim();
  const username = document.getElementById('eu-username').value.trim();
  const password = document.getElementById('eu-password').value;
  const msg      = document.getElementById('eu-msg');

  if (!name || !username) { msg.style.color='var(--red)'; msg.textContent = 'Name and username are required.'; return; }

  const body = {id, name, username};
  if (password) body.password = password;

  const res = await post('update_user', body);
  if (res.ok) {
    msg.style.color = 'var(--green)';
    msg.textContent = '✅ Saved!';
    // Update local cache
    const u = allUsers.find(x => x.id == id);
    if (u) { u.name = name; u.username = username; }
    loadSettings();
    renderUserTabs();
    setTimeout(closeEditUser, 800);
  } else {
    msg.style.color = 'var(--red)';
    msg.textContent = res.error || 'Error saving.';
  }
}

function renderUserTabs() {
  const box = document.getElementById('user-tabs');
  if (!currentUser || currentUser.role !== 'admin') { box.style.display='none'; return; }
  box.style.display = 'flex';
  box.innerHTML = `<div class="user-tab ${!filterUserId?'active':''}" onclick="setUserFilter(null)">All Users</div>`
    + allUsers.map(u => `<div class="user-tab ${filterUserId==u.id?'active':''}" onclick="setUserFilter(${u.id})">${u.name}</div>`).join('');
}

function setUserFilter(uid) {
  filterUserId = uid;
  renderUserTabs();
  loadExpenses();
}

// ── Dashboard ──────────────────────────────────────────────────
async function loadDashboard() {
  const params = new URLSearchParams({month: currentMonth});
  const [expRes, repRes] = await Promise.all([
    fetch(`api.php?action=expenses&${params}`).then(r=>r.json()),
    fetch(`api.php?action=report&${params}`).then(r=>r.json())
  ]);
  const expenses = expRes.expenses || [];
  const rep = repRes;
  const isAdmin = currentUser?.role === 'admin';

  // Cards
  const topCat = Object.entries(rep.by_category||{}).sort((a,b)=>b[1]-a[1])[0];
  let cardsHtml = `
    <div class="card"><div class="cl">Total Spent</div><div class="cv neg">${fm(rep.total||0)}</div><div class="cs">${rep.count||0} expenses</div></div>
    <div class="card"><div class="cl">Top Category</div><div class="cv">${topCat ? (catLabel[topCat[0]]||topCat[0]) : '—'}</div><div class="cs">${topCat ? fm(topCat[1]) : ''}</div></div>`;
  if (isAdmin) {
    Object.entries(rep.by_user||{}).forEach(([name,amt]) => {
      cardsHtml += `<div class="card"><div class="cl">${name}</div><div class="cv neg">${fm(amt)}</div><div class="cs">this month</div></div>`;
    });
  }
  document.getElementById('dash-cards').innerHTML = cardsHtml;

  // User column
  const userTh = document.getElementById('dash-user-th');
  if (userTh) userTh.style.display = isAdmin ? '' : 'none';

  // Recent 10
  const rows = expenses.slice(0,10).map(e => expRow(e, isAdmin, true)).join('') || `<tr><td colspan="6" class="empty-row">No expenses this month.</td></tr>`;
  document.getElementById('dash-tbody').innerHTML = rows;
}

// ── Expenses ───────────────────────────────────────────────────
async function loadExpenses() {
  const isAdmin = currentUser?.role === 'admin';
  renderUserTabs();
  const params = new URLSearchParams({month: currentMonth});
  if (filterUserId) params.set('user_id', filterUserId);
  const res = await fetch(`api.php?action=expenses&${params}`).then(r=>r.json());
  expenseCache = res.expenses || [];

  const userTh = document.getElementById('exp-user-th');
  if (userTh) userTh.style.display = (isAdmin && !filterUserId) ? '' : 'none';

  const rows = expenseCache.map(e => expRow(e, isAdmin && !filterUserId, false)).join('')
    || `<tr><td colspan="7" class="empty-row">No expenses this month. Click "+ Add Expense" to get started.</td></tr>`;
  document.getElementById('exp-tbody').innerHTML = rows;
}

function expRow(e, showUser, compact) {
  const isPDF = e.photo_url && e.photo_url.toLowerCase().endsWith('.pdf');
  const photo = e.photo_url
    ? (isPDF
        ? `<div class="thumb-placeholder" onclick="window.open('${e.photo_url}','_blank')" title="View PDF" style="cursor:pointer">📄</div>`
        : `<img class="thumb" src="${e.photo_url}" onclick="openLightbox('${e.photo_url}')" alt="receipt">`)
    : `<div class="thumb-placeholder" style="cursor:default">—</div>`;
  const userTd = showUser ? `<td>${e.user_name||''}</td>` : '';
  const actions = compact ? '' : `<td><button class="btn-s" style="margin-right:4px" onclick="openModal(${e.id})">✏️</button><button class="btn-d" onclick="deleteExpense(${e.id})">✕</button></td>`;
  const noteTd = `<td style="color:var(--muted);font-size:12px;max-width:160px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis">${e.note||''}</td>`;
  return `<tr>
    <td>${photo}</td>
    <td style="white-space:nowrap">${e.date}</td>
    <td>${e.company||'—'}</td>
    <td><span class="badge ${catClass[e.category]||'b-other'}">${catLabel[e.category]||e.category}</span></td>
    <td style="font-weight:600;color:var(--red);white-space:nowrap">${fm(e.amount, e.currency)}</td>
    ${userTd}
    ${noteTd}
    ${actions}
  </tr>`;
}

// ── Report ─────────────────────────────────────────────────────
async function loadReport() {
  const isAdmin = currentUser?.role === 'admin';
  const params = new URLSearchParams({month: currentMonth});
  const [repRes, expRes] = await Promise.all([
    fetch(`api.php?action=report&${params}`).then(r=>r.json()),
    fetch(`api.php?action=expenses&${params}`).then(r=>r.json())
  ]);
  const rep = repRes; const expenses = expRes.expenses||[];

  // Cards
  document.getElementById('rep-cards').innerHTML = `
    <div class="card"><div class="cl">Total</div><div class="cv neg">${fm(rep.total||0)}</div><div class="cs">${rep.count||0} expenses</div></div>
    ${Object.entries(rep.by_category||{}).map(([k,v])=>`<div class="card"><div class="cl">${catLabel[k]||k}</div><div class="cv neg">${fm(v)}</div></div>`).join('')}`;

  // Chart
  const cats = Object.keys(rep.by_category||{});
  const vals = Object.values(rep.by_category||{});
  if (reportChart) reportChart.destroy();
  if (cats.length) {
    reportChart = new Chart(document.getElementById('rep-chart'), {
      type:'bar',
      data:{labels:cats.map(c=>catLabel[c]||c), datasets:[{data:vals, backgroundColor:['#ffa657','#bc8cff','#58a6ff','#3fb950','#7d8590'], borderRadius:6}]},
      options:{plugins:{legend:{display:false}}, scales:{y:{ticks:{color:'#7d8590'}, grid:{color:'#30363d'}}, x:{ticks:{color:'#7d8590'}, grid:{display:false}}}, responsive:true}
    });
  } else {
    document.getElementById('rep-chart').getContext('2d').clearRect(0,0,500,220);
  }

  // By category table
  document.getElementById('rep-cat-tbody').innerHTML = Object.entries(rep.by_category||{}).map(([k,v])=>
    `<tr><td><span class="badge ${catClass[k]||'b-other'}">${catLabel[k]||k}</span></td><td style="text-align:right;font-weight:600;color:var(--red)">${fm(v)}</td></tr>`
  ).join('') || `<tr><td colspan="2" class="empty-row">No data</td></tr>`;

  // By user table (admin)
  const userBox = document.getElementById('rep-user-box');
  userBox.style.display = isAdmin ? '' : 'none';
  if (isAdmin) {
    document.getElementById('rep-user-tbody').innerHTML = Object.entries(rep.by_user||{}).map(([name,amt])=>
      `<tr><td>${name}</td><td style="text-align:right;font-weight:600;color:var(--red)">${fm(amt)}</td></tr>`
    ).join('') || `<tr><td colspan="2" class="empty-row">No data</td></tr>`;
  }
  const repUserTh = document.getElementById('rep-user-th');
  if (repUserTh) repUserTh.style.display = isAdmin ? '' : 'none';

  // Full list
  document.getElementById('rep-tbody').innerHTML = expenses.map(e=>`<tr>
    <td>${e.date}</td><td>${e.company||'—'}</td>
    <td><span class="badge ${catClass[e.category]||'b-other'}">${catLabel[e.category]||e.category}</span></td>
    <td style="font-weight:600;color:var(--red)">${fm(e.amount,e.currency)}</td>
    ${isAdmin?`<td>${e.user_name||''}</td>`:''}
    <td style="color:var(--muted)">${e.note||''}</td>
  </tr>`).join('') || `<tr><td colspan="6" class="empty-row">No expenses this month.</td></tr>`;
}

// ── CSV Export ─────────────────────────────────────────────────
function exportCSV() {
  const isAdmin = currentUser?.role === 'admin';
  const header = ['Date','Company','Category','Amount','Currency','Note', ...(isAdmin?['User']:[])];
  const rows = expenseCache.map(e => [e.date, e.company||'', catLabel[e.category]||e.category, e.amount, e.currency, e.note||'', ...(isAdmin?[e.user_name||'']:[])]);
  const csv = [header, ...rows].map(r => r.map(v=>`"${String(v).replace(/"/g,'""')}"`).join(',')).join('\n');
  const a = document.createElement('a');
  a.href = URL.createObjectURL(new Blob([csv], {type:'text/csv'}));
  a.download = `expenses_${currentMonth}.csv`;
  a.click();
}

// ── ZIP Download ───────────────────────────────────────────────
function downloadZip() {
  const params = new URLSearchParams({action: 'download_zip', month: currentMonth});
  if (filterUserId) params.set('user_id', filterUserId);
  window.location.href = 'api.php?' + params.toString();
}

// ── Modal ──────────────────────────────────────────────────────
function openModal(id) {
  editingId = id || null;
  photoFile = null; photoPreviewData = null;
  document.getElementById('photo-empty').style.display    = 'block';
  document.getElementById('photo-preview').style.display  = 'none';
  document.getElementById('photo-input').value = '';
  document.getElementById('btn-extract').style.display    = 'none';
  document.getElementById('photo-actions').style.display  = 'none';
  document.getElementById('photo-hint-text').style.display = 'none';
  document.getElementById('photo-area').onclick = () => document.getElementById('photo-input').click();
  document.getElementById('modal-title').textContent = id ? '🧾 Edit Expense' : '🧾 Add Expense';

  // Defaults
  const today = new Date().toISOString().slice(0,10);
  document.getElementById('m-date').value    = today;
  document.getElementById('m-company').value = '';
  document.getElementById('m-cat').value     = 'transport_work';
  document.getElementById('m-amount').value  = '';
  document.getElementById('m-currency').value = 'EUR';
  document.getElementById('m-note').value    = '';

  if (id) {
    const e = expenseCache.find(x => x.id == id);
    if (e) {
      document.getElementById('m-date').value     = e.date;
      document.getElementById('m-company').value  = e.company || '';
      document.getElementById('m-cat').value      = e.category;
      document.getElementById('m-amount').value   = e.amount;
      document.getElementById('m-currency').value = e.currency;
      document.getElementById('m-note').value     = e.note || '';
      if (e.photo_url) {
        document.getElementById('photo-img').src = e.photo_url;
        document.getElementById('photo-preview').style.display  = 'block';
        document.getElementById('photo-empty').style.display    = 'none';
        document.getElementById('btn-extract').style.display    = 'block';
        document.getElementById('photo-actions').style.display  = 'flex';
        document.getElementById('photo-hint-text').style.display = 'block';
        document.getElementById('photo-area').onclick = null;
        photoPreviewData = {url: e.photo_url, fromServer: true};
      }
    }
  }
  document.getElementById('modal-ov').classList.add('open');
}

function closeModal() {
  document.getElementById('modal-ov').classList.remove('open');
  editingId = null; photoFile = null; photoPreviewData = null;
}

// ── Photo / PDF handling ───────────────────────────────────────
async function handlePhoto(event) {
  const file = event.target.files[0];
  if (!file) return;
  photoFile = file;

  const isPDF = file.type === 'application/pdf';

  if (isPDF) {
    // Show PDF placeholder preview
    photoPreviewData = {isPDF: true, name: file.name};
    document.getElementById('photo-img').src = '';
    document.getElementById('photo-img').style.display = 'none';
    document.getElementById('photo-preview').innerHTML =
      `<div style="padding:20px;text-align:center">
        <div style="font-size:48px;margin-bottom:8px">📄</div>
        <div style="font-weight:600;color:var(--text)">${file.name}</div>
        <div style="color:var(--muted);font-size:12px;margin-top:4px">${(file.size/1024).toFixed(0)} KB · PDF</div>
      </div>`;
  } else {
    // Image — compress and preview
    const data = await compressImage(file);
    photoPreviewData = {dataUrl: data};
    document.getElementById('photo-preview').innerHTML =
      `<img id="photo-img" src="${data}" onclick="event.stopPropagation();openLightboxFromModal()" title="Tap to zoom" style="max-width:100%;max-height:200px;border-radius:6px;object-fit:contain;cursor:zoom-in">`;
  }

  document.getElementById('photo-preview').style.display  = 'block';
  document.getElementById('photo-empty').style.display    = 'none';
  document.getElementById('btn-extract').style.display    = isPDF ? 'none' : 'block';
  document.getElementById('photo-actions').style.display  = 'flex';
  document.getElementById('photo-hint-text').style.display = isPDF ? 'none' : 'block';
  document.getElementById('photo-area').onclick = null;
}

function compressImage(file) {
  return new Promise(resolve => {
    const reader = new FileReader();
    reader.onload = e => {
      const img = new Image();
      img.onload = () => {
        const MAX = 900; let w=img.width, h=img.height;
        if (w>MAX||h>MAX) { if(w>h){h=Math.round(h*MAX/w);w=MAX;}else{w=Math.round(w*MAX/h);h=MAX;} }
        const c = document.createElement('canvas'); c.width=w; c.height=h;
        c.getContext('2d').drawImage(img,0,0,w,h);
        resolve(c.toDataURL('image/jpeg', 0.75));
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  });
}

// ── AI Extraction ──────────────────────────────────────────────
async function extractFromPhoto() {
  const btn = document.getElementById('btn-extract');
  btn.disabled = true; btn.textContent = '⏳ Extracting...';
  try {
    let base64, mediaType = 'image/jpeg';
    if (photoPreviewData?.dataUrl) {
      const parts = photoPreviewData.dataUrl.split(',');
      base64 = parts[1];
      mediaType = parts[0].split(';')[0].split(':')[1] || 'image/jpeg';
    } else if (photoPreviewData?.url) {
      // Fetch existing photo from server
      const r = await fetch(photoPreviewData.url);
      const blob = await r.blob();
      mediaType = blob.type || 'image/jpeg';
      base64 = await new Promise(res => {
        const rd = new FileReader();
        rd.onload = e => res(e.target.result.split(',')[1]);
        rd.readAsDataURL(blob);
      });
    } else { throw new Error('No photo loaded'); }

    const res = await post('extract', {image_data: base64, media_type: mediaType});
    if (res.error) throw new Error(res.error);
    const ex = res.extracted || {};
    if (ex.date)     document.getElementById('m-date').value     = ex.date;
    if (ex.company)  document.getElementById('m-company').value  = ex.company;
    if (ex.amount)   document.getElementById('m-amount').value   = ex.amount;
    if (ex.currency) document.getElementById('m-currency').value = ex.currency;
    btn.textContent = '✅ Extracted!';
    setTimeout(() => { btn.disabled=false; btn.textContent='🤖 Auto-extract from photo'; }, 2000);
  } catch(e) {
    btn.disabled = false; btn.textContent = '🤖 Auto-extract from photo';
    alert('Extraction failed: ' + e.message);
  }
}

// ── Save expense ───────────────────────────────────────────────
async function saveExpense() {
  const date     = document.getElementById('m-date').value;
  const company  = document.getElementById('m-company').value.trim();
  const category = document.getElementById('m-cat').value;
  const amount   = parseFloat(document.getElementById('m-amount').value);
  const currency = document.getElementById('m-currency').value;
  const note     = document.getElementById('m-note').value.trim();
  if (!date || !amount) { alert('Please fill in date and amount.'); return; }

  let id = editingId;
  if (id) {
    await post('update', {id, date, company, category, amount, currency, note});
  } else {
    const res = await post('add', {date, company, category, amount, currency, note});
    if (res.error) { alert(res.error); return; }
    id = res.id;
  }

  // Upload photo if new file selected
  if (photoFile && id) {
    const fd = new FormData();
    fd.append('expense_id', id);
    fd.append('photo', photoFile);
    try {
      const upRes = await fetch('api.php?action=upload_photo', {
        method: 'POST',
        credentials: 'same-origin',
        body: fd
      });
      if (!upRes.ok) {
        const err = await upRes.json().catch(() => ({}));
        alert('⚠️ Expense saved, but photo upload failed: ' + (err.error || 'HTTP ' + upRes.status) + '\nYou can re-attach the photo by editing the expense.');
      } else {
        const upJson = await upRes.json().catch(() => ({}));
        if (upJson.error) {
          alert('⚠️ Expense saved, but photo upload failed: ' + upJson.error + '\nYou can re-attach the photo by editing the expense.');
        }
      }
    } catch (e) {
      alert('⚠️ Expense saved, but photo upload failed (network error). You can re-attach the photo by editing the expense.');
    }
  }

  closeModal();
  loadPage(currentPage);
}

// ── Delete expense ─────────────────────────────────────────────
async function deleteExpense(id) {
  if (!confirm('Delete this expense?')) return;
  await post('delete', {id});
  loadPage(currentPage);
}

// ── Lightbox ───────────────────────────────────────────────────
let _lbScale=1, _lbLastDist=0, _lbOriginX=0, _lbOriginY=0;

function openLightbox(url) {
  _lbScale=1;
  const img = document.getElementById('lightbox-img');
  img.style.transform = 'scale(1)';
  img.src = url;
  const lb = document.getElementById('lightbox');
  lb.style.display = 'flex';
}

function openLightboxFromModal() {
  if (photoPreviewData?.isPDF && photoFile) {
    const url = URL.createObjectURL(photoFile);
    window.open(url, '_blank');
    return;
  }
  const img = document.getElementById('photo-img');
  if (img && img.src) openLightbox(img.src);
}

function closeLightbox() {
  document.getElementById('lightbox').style.display = 'none';
  _lbScale = 1;
}

// Close on background tap (not on image)
document.getElementById('lightbox-img-wrap').addEventListener('click', function(e){
  if (e.target === this) closeLightbox();
});

// Double-tap to reset zoom
let _lbLastTap = 0;
document.getElementById('lightbox-img').addEventListener('click', function(){
  const now = Date.now();
  if (now - _lbLastTap < 300) { _lbScale=1; this.style.transform='scale(1)'; }
  _lbLastTap = now;
});

// Pinch-to-zoom
document.getElementById('lightbox-img').addEventListener('touchstart', function(e){
  if (e.touches.length === 2) {
    _lbLastDist = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
    e.preventDefault();
  }
}, {passive:false});

document.getElementById('lightbox-img').addEventListener('touchmove', function(e){
  if (e.touches.length === 2) {
    e.preventDefault();
    const dist = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
    _lbScale = Math.min(Math.max(_lbScale * (dist / _lbLastDist), 1), 5);
    _lbLastDist = dist;
    this.style.transform = `scale(${_lbScale})`;
  }
}, {passive:false});

function openLightboxFromTable(url) { openLightbox(url); }

// ── Service Worker (PWA) ───────────────────────────────────────
if ('serviceWorker' in navigator) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/expenses/sw.js')
      .catch(err => console.warn('SW registration failed:', err));
  });
}

// ── iOS "Add to Home Screen" hint (shown once) ─────────────────
window.addEventListener('load', () => {
  const isIos = /iphone|ipad|ipod/i.test(navigator.userAgent);
  const isStandalone = window.navigator.standalone === true;
  const shown = localStorage.getItem('pwa-hint-shown');
  if (isIos && !isStandalone && !shown) {
    const hint = document.createElement('div');
    hint.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:#161b22;border:1px solid #3fb950;border-radius:12px;padding:12px 18px;font-size:13px;color:#e6edf3;z-index:9999;max-width:300px;text-align:center;box-shadow:0 4px 20px rgba(0,0,0,.5)';
    hint.innerHTML = '📲 <strong>Install this app</strong><br>Tap <strong>Share</strong> → <strong>Add to Home Screen</strong> to use it like a native app.<br><br><button onclick="this.parentNode.remove();localStorage.setItem(\'pwa-hint-shown\',\'1\')" style="margin-top:6px;padding:6px 16px;background:#3fb950;color:#000;border:none;border-radius:8px;font-weight:600;cursor:pointer">Got it</button>';
    document.body.appendChild(hint);
    localStorage.setItem('pwa-hint-shown', '1');
  }
});
</script>
</body>
</html>
