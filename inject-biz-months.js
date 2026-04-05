// ============================================================
// WealthTrack — Biz Months Data Injector
// Paste this entire script into your browser console while on
// http://localhost:8080/WealthTrack.html
// ============================================================
(function() {
  const raw = localStorage.getItem('wt_v3');
  if (!raw) { alert('No WealthTrack data found in localStorage. Make sure you are on http://localhost:8080/WealthTrack.html'); return; }

  const s = JSON.parse(raw);

  // Ensure biz structure exists
  if (!s.biz) s.biz = {};
  if (!s.biz.contractor) s.biz.contractor = { name: 'Brother', costRateEUR: 66.52, sellRateEUR: 69.50, hoursPerDay: 8 };
  if (!s.biz.fixedCosts) s.biz.fixedCosts = [
    { id: 'fc1', name: 'Accounting',     category: 'admin',    amountEUR: 150, active: true },
    { id: 'fc2', name: 'Apps & Software', category: 'software', amountEUR:  50, active: true },
    { id: 'fc3', name: 'Hosting & Infra', category: 'infra',    amountEUR:  30, active: true },
    { id: 'fc4', name: 'Website',          category: 'infra',    amountEUR:  20, active: true }
  ];

  // Inject all historical months
  s.biz.months = {
    '2025-02': { contractor: { forecastDays: 20,  actualDays: 20,   cardExpenses: 350.86,  note: '' }, variableCosts: [] },
    '2025-03': { contractor: { forecastDays: 20,  actualDays: 20,   cardExpenses: 516.47,  note: '' }, variableCosts: [] },
    '2025-04': { contractor: { forecastDays: 22,  actualDays: 21,   cardExpenses: 742.88,  note: '' }, variableCosts: [] },
    '2025-05': { contractor: { forecastDays: 20,  actualDays: 19,   cardExpenses: 898.77,  note: '' }, variableCosts: [] },
    '2025-06': { contractor: { forecastDays: 20,  actualDays: 21,   cardExpenses: 763.19,  note: '' }, variableCosts: [] },
    '2025-07': { contractor: { forecastDays: 23,  actualDays: 22,   cardExpenses: 1154.95, note: '' }, variableCosts: [] },
    '2025-08': { contractor: { forecastDays: 20,  actualDays: 20,   cardExpenses: 1746.12, note: '' }, variableCosts: [] },
    '2025-09': { contractor: { forecastDays: 20,  actualDays: 15,   cardExpenses: 776.97,  note: '' }, variableCosts: [] },
    '2025-10': { contractor: { forecastDays: 23,  actualDays: 23,   cardExpenses: 2268.24, note: '' }, variableCosts: [] },
    '2025-11': { contractor: { forecastDays: 19,  actualDays: 17,   cardExpenses: 692.05,  note: '' }, variableCosts: [] },
    '2025-12': { contractor: { forecastDays: 18,  actualDays: 19.5, cardExpenses: 289.11,  note: '' }, variableCosts: [] },
    '2026-01': { contractor: { forecastDays: 21,  actualDays: 21,   cardExpenses: 851.13,  note: '' }, variableCosts: [] },
    '2026-02': { contractor: { forecastDays: 20,  actualDays: 20,   cardExpenses: 2560.18, note: '' }, variableCosts: [] },
    '2026-03': { contractor: { forecastDays: 22,  actualDays: 22,   cardExpenses: 1733.28, note: '' }, variableCosts: [] },
    '2026-04': { contractor: { forecastDays: 22,  actualDays: null, cardExpenses: 0,        note: '' }, variableCosts: [] },
  };

  localStorage.setItem('wt_v3', JSON.stringify(s));

  // Also save to server so it persists in the HTML file
  fetch('/save', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ data: s })
  }).then(r => r.json()).then(j => {
    console.log('Saved to server:', j);
    alert('✅ Biz months injected! Reloading...');
    location.reload();
  }).catch(() => {
    alert('✅ Biz months saved to localStorage!\n\n⚠️ Server save failed (is server.py running?). Data is in your browser but not persisted to the HTML file yet.');
    location.reload();
  });
})();
