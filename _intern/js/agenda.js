// --- TASQUES: CACHE I CARREGAR PER RANG DE DATES ---
let agendaTasquesCache = {};
// map of task id -> task object for quick lookup when opening edit modal
let agendaTasquesById = {};

async function fetchTasques(dataInici, dataFi) {
  const key = dataInici + '_' + dataFi;
  if (agendaTasquesCache[key]) return agendaTasquesCache[key];
  const res = await fetch('api/tasques-get.php?data_inici=' + dataInici + '&data_fi=' + dataFi);
  const json = await res.json();
  if (json.tasques) agendaTasquesCache[key] = json.tasques;
  return json.tasques || [];
}

function pad(n) { return String(n).padStart(2, '0'); }

// Return local YYYY-MM-DD (avoid toISOString which converts to UTC and causes day shifts)
function formatDateISO(date) {
  const d = new Date(date);
  return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
}

// --- RENDER DAY AMB TASQUES ---
async function renderDay(date) {
  const tbody = document.querySelector('.agenda-day-table tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  const dataStr = formatDateISO(date);
  const tasques = await fetchTasques(dataStr, dataStr);
  const MOBILE_OVERLAY_THRESHOLD = 900;
  const useOverlay = window.innerWidth > MOBILE_OVERLAY_THRESHOLD;
  const tasquesByHour = {};
  for (const t of tasques) {
    const hour = t.hora_inici ? t.hora_inici.slice(0,2) : null;
    if (!tasquesByHour[hour]) tasquesByHour[hour] = [];
    tasquesByHour[hour].push(t);
  }
  for (let h = 6; h <= 23; h++) {
    const tr = document.createElement('tr');
    const tdHour = document.createElement('td');
    tdHour.className = 'agenda-day-hour';
    tdHour.textContent = (h < 10 ? '0' : '') + h + ':00';
    const tdSlot = document.createElement('td');
    tdSlot.className = 'agenda-day-slot';
    const tasquesHora = tasquesByHour[String(h).padStart(2,'0')] || [];
    for (const t of tasquesHora) {
      // render inline slot tasks only when overlays are disabled (small screens)
      if (!useOverlay) {
        const el = document.createElement('div');
        el.className = 'agenda-day-task';
        el.textContent = t.titol;
        if (t.es_important) el.style.background = '#ffd54f';
        if (t.es_urgent) el.style.border = '2px solid #e53935';
        // edit button for day task
        const btn = document.createElement('button');
        btn.className = 'agenda-task-edit-btn';
        btn.title = 'Editar tasca';
        btn.innerHTML = '&#9998;';
        // attach id for delegated handler and populate map
        if (t && t.id) {
          btn.dataset.tascaId = t.id;
          agendaTasquesById[t.id] = t;
        }
        btn.onclick = function(e) { e.stopPropagation(); openEditModal(t); };
        el.appendChild(btn);
        // try to position within the hour slot according to minutes
        if (t.hora_inici) {
          const [sh, sm] = t.hora_inici.split(':').map(x => parseInt(x,10));
          const slotRect = tdSlot.getBoundingClientRect();
          const minuteOffset = (sm / 60) * slotRect.height;
          el.style.position = 'absolute';
          el.style.left = '4px';
          el.style.right = '4px';
          el.style.top = minuteOffset + 'px';
        }
        tdSlot.appendChild(el);
      }
    }
    tr.appendChild(tdHour);
    tr.appendChild(tdSlot);
    tbody.appendChild(tr);
  }
  // Títol del dia
  const options = { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric' };
  document.getElementById('agenda-day-title').textContent =
    date.toLocaleDateString('ca-ES', options).replace(/^\w/, c => c.toUpperCase());
  // Actualitza el títol del mes també
  if (document.getElementById('agenda-month')) {
    document.getElementById('agenda-month').textContent = mesos[date.getMonth()] + ' ' + date.getFullYear();
  }
  // render overlay tasks for the day
  renderDayOverlay(tasques, date);
}

// --- RENDER MONTH AMB TASQUES ---
async function renderMonth(year, month) {
  const tbody = document.querySelector('.agenda-month-table tbody');
  if (!tbody) return;
  tbody.innerHTML = '';
  const firstDay = new Date(year, month, 1);
  const lastDay = new Date(year, month + 1, 0);
  let startDay = (firstDay.getDay() + 6) % 7; // Dilluns=0
  let day = 1;
  const dataInici = formatDateISO(firstDay);
  const dataFi = formatDateISO(lastDay);
  const tasques = await fetchTasques(dataInici, dataFi);
  const MOBILE_OVERLAY_THRESHOLD = 900;
  const useOverlay = window.innerWidth > MOBILE_OVERLAY_THRESHOLD;
  const tasquesByDate = {};
  for (const t of tasques) {
    if (!tasquesByDate[t.data_tasca]) tasquesByDate[t.data_tasca] = [];
    tasquesByDate[t.data_tasca].push(t);
  }
  for (let row = 0; row < 6; row++) {
    const tr = document.createElement('tr');
    for (let col = 0; col < 7; col++) {
      const td = document.createElement('td');
      if ((row === 0 && col < startDay) || day > lastDay.getDate()) {
        td.innerHTML = '';
      } else {
        const dateStr = formatDateISO(new Date(year, month, day));
        td.innerHTML = `<span class='agenda-month-day'>${day}</span>`;
        if (tasquesByDate[dateStr]) {
          for (const t of tasquesByDate[dateStr]) {
            // render inline month tasks only when overlays are disabled (small screens)
            if (!useOverlay) {
              const el = document.createElement('div');
              el.className = 'agenda-month-task';
              el.textContent = t.titol;
              if (t.es_important) el.style.background = '#ffd54f';
              if (t.es_urgent) el.style.border = '2px solid #e53935';
              // edit button for month task
              const btn = document.createElement('button');
              btn.className = 'agenda-task-edit-btn';
              btn.title = 'Editar tasca';
              btn.innerHTML = '&#9998;';
              // attach id for delegated handler and populate map
              if (t && t.id) {
                btn.dataset.tascaId = t.id;
                agendaTasquesById[t.id] = t;
              }
              btn.onclick = function(e) { e.stopPropagation(); openEditModal(t); };
              el.appendChild(btn);
              td.appendChild(el);
            }
          }
        }
        day++;
      }
      tr.appendChild(td);
    }
    tbody.appendChild(tr);
    if (day > lastDay.getDate()) break;
  }
  document.getElementById('agenda-month').textContent = mesos[month] + ' ' + year;
}

// --- HELPERS QUE FALTAVEN ---
const mesos = ['Gener','Febrer','Març','Abril','Maig','Juny','Juliol','Agost','Setembre','Octubre','Novembre','Desembre'];

function getMonday(d) {
  const date = new Date(d);
  const day = date.getDay();
  const diff = (day + 6) % 7; // convert Sun=0..Sat=6 to Mon=0..Sun=6
  date.setDate(date.getDate() - diff);
  date.setHours(0,0,0,0);
  return date;
}

function setWeek(monday) {
  // Ajusta capçaleres de dies i el rang
  const headers = document.querySelectorAll('.agenda-day-header');
  const mondayDate = new Date(monday);
  for (let d = 0; d < 7; d++) {
    const dayDate = new Date(mondayDate.getFullYear(), mondayDate.getMonth(), mondayDate.getDate() + d);
    const hdr = headers[d];
    if (hdr) {
      const options = { weekday: 'short', day: '2-digit', month: '2-digit' };
      hdr.textContent = dayDate.toLocaleDateString('ca-ES', options).replace(/^\w/, c => c.toUpperCase());
    }
  }
  // Set setmana/rang
  const start = new Date(mondayDate);
  const end = new Date(mondayDate.getFullYear(), mondayDate.getMonth(), mondayDate.getDate() + 6);
  const rangeText = `${start.getDate()}/${String(start.getMonth()+1).padStart(2,'0')} - ${end.getDate()}/${String(end.getMonth()+1).padStart(2,'0')}`;
  const weekNum = Math.ceil((((start - new Date(start.getFullYear(),0,1)) / 86400000) + new Date(start.getFullYear(),0,1).getDay()+1)/7);
  const weekLabel = `Setmana ${weekNum} (${rangeText})`;
  const elRange = document.getElementById('agenda-week-range');
  if (elRange) elRange.textContent = weekLabel;
}

// --- RENDER WEEK AMB TASQUES (restaurat) ---
async function renderWeek(monday) {
  setWeek(monday);
  const dataInici = formatDateISO(monday);
  const endDate = new Date(monday.getFullYear(), monday.getMonth(), monday.getDate() + 6);
  const dataFi = formatDateISO(endDate);
  const tasques = await fetchTasques(dataInici, dataFi);
  // Neteja slots
  document.querySelectorAll('.agenda-slot').forEach(td => td.innerHTML = '');
  const tasquesByDayHour = {};
  for (const t of tasques) {
    if (!tasquesByDayHour[t.data_tasca]) tasquesByDayHour[t.data_tasca] = {};
    const hour = t.hora_inici ? t.hora_inici.slice(0,2) : null;
    if (!tasquesByDayHour[t.data_tasca][hour]) tasquesByDayHour[t.data_tasca][hour] = [];
    tasquesByDayHour[t.data_tasca][hour].push(t);
  }
  const mondayDate = new Date(monday);
  // Try to render overlays first; if overlays are not possible, fall back to inline slot rendering
  const overlayOk = renderWeekOverlay(tasques, monday);
  if (!overlayOk) {
    for (let d = 0; d < 7; d++) {
      const date = new Date(mondayDate.getFullYear(), mondayDate.getMonth(), mondayDate.getDate() + d);
      const dateStr = formatDateISO(date);
      for (let h = 6; h <= 23; h++) {
        const slot = document.querySelector('.agenda-slot[data-day="' + d + '"][data-hour="' + h + '"]');
        if (!slot) continue;
        const tasquesHora = (tasquesByDayHour[dateStr] && tasquesByDayHour[dateStr][String(h).padStart(2,'0')]) || [];
        for (const t of tasquesHora) {
          const el = document.createElement('div');
          el.className = 'agenda-task';
          el.textContent = t.titol;
          if (t.es_important) el.style.background = '#ffd54f';
          if (t.es_urgent) el.style.border = '2px solid #e53935';
          // edit button for slot task
          const btn = document.createElement('button');
          btn.className = 'agenda-task-edit-btn';
          btn.title = 'Editar tasca';
          btn.innerHTML = '&#9998;';
          // attach id for delegated handler and populate map
          if (t && t.id) {
            btn.dataset.tascaId = t.id;
            agendaTasquesById[t.id] = t;
          }
          btn.onclick = function(e) { e.stopPropagation(); openEditModal(t); };
          el.appendChild(btn);
          slot.appendChild(el);
        }
      }
    }
  }
  // Actualitza títol mes
  document.getElementById('agenda-month').textContent = mesos[monday.getMonth()] + ' ' + monday.getFullYear();
}

// --- RENDER TASKS AS OVERLAY (WEEK) ---
function renderWeekOverlay(tasques, monday) {
  const weekView = document.querySelector('.agenda-calendar.week-view');
  if (!weekView) return;
  // Avoid creating overlays on small screens (handled by responsive CSS)
  const MOBILE_OVERLAY_THRESHOLD = 900;
  if (window.innerWidth <= MOBILE_OVERLAY_THRESHOLD) return false;
  let overlay = weekView.querySelector('.agenda-week-overlay');
  const table = weekView.querySelector('.agenda-table');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'agenda-week-overlay';
    weekView.appendChild(overlay);
  }
  overlay.innerHTML = '';
  // measure one slot to get hour height and day width
  const sampleSlot = weekView.querySelector('.agenda-slot[data-day="0"][data-hour="6"]');
  if (!sampleSlot) return false;
  const slotRect = sampleSlot.getBoundingClientRect();
  const tableRect = table ? table.getBoundingClientRect() : weekView.getBoundingClientRect();
  const weekViewRect = weekView.getBoundingClientRect();
  const hourHeight = slotRect.height;
  // compute left positions for each day using day 6:00 slot
  const dayLefts = [];
  for (let d = 0; d < 7; d++) {
    const s = weekView.querySelector('.agenda-slot[data-day="' + d + '"][data-hour="6"]');
    if (s) {
      const r = s.getBoundingClientRect();
      // left relative to weekView container
      dayLefts[d] = r.left - weekViewRect.left + 4; // small padding
    } else {
      dayLefts[d] = null;
    }
  }

  const dayWidth = slotRect.width - 8;

  const dayStartHour = 6;
  const dayEndHour = 23;

  for (const t of tasques) {
  if (t && t.id) agendaTasquesById[t.id] = t;
    // determine day index relative to monday
  // parse YYYY-MM-DD into a local Date at midnight to avoid timezone shifts
  const parts = (t.data_tasca || '').split('-').map(x => parseInt(x,10));
  const taskDate = (parts.length === 3) ? new Date(parts[0], parts[1]-1, parts[2]) : new Date(t.data_tasca);
  const mondayDate = new Date(monday.getFullYear(), monday.getMonth(), monday.getDate());
    const d = Math.floor((taskDate - mondayDate) / 86400000);
    if (d < 0 || d > 6) continue;
    const left = dayLefts[d];
    if (left == null) continue;
    // parse time and compute position using the actual slot element for better precision
    const start = t.hora_inici || null;
    const end = t.hora_fi || null;
    let topPx = 0;
    let heightPx = hourHeight * 0.6; // default small
    if (start) {
      const [sh, sm] = start.split(':').map(x => parseInt(x,10));
      // try to find the exact slot element for this day/hour
      const slotElem = weekView.querySelector('.agenda-slot[data-day="' + d + '"][data-hour="' + sh + '"]');
      if (slotElem) {
        const sRect = slotElem.getBoundingClientRect();
        const slotTopRel = sRect.top - weekViewRect.top;
        topPx = slotTopRel + (sm / 60) * sRect.height;
        if (end) {
          const [eh, em] = end.split(':').map(x => parseInt(x,10));
          // compute end position using end slot (if exists) or by adding duration
          const endSlot = weekView.querySelector('.agenda-slot[data-day="' + d + '"][data-hour="' + eh + '"]');
          if (endSlot) {
            const eRect = endSlot.getBoundingClientRect();
            const endTopRel = eRect.top - weekViewRect.top + (em / 60) * eRect.height;
            heightPx = endTopRel - topPx;
          } else {
            let durationMin = (eh * 60 + em) - (sh * 60 + sm);
            if (durationMin <= 0) durationMin = 30;
            heightPx = (durationMin / 60) * sRect.height;
          }
        } else {
          heightPx = sRect.height * 0.9;
        }
      } else {
        // fallback to previous calculation using hourHeight
        const taskDateLocal = new Date(taskDate.getFullYear(), taskDate.getMonth(), taskDate.getDate(), sh, sm);
        const dayStartDateLocal = new Date(mondayDate.getFullYear(), mondayDate.getMonth(), mondayDate.getDate() + d, dayStartHour, 0);
        const minutesFromStart = Math.max(0, Math.round((taskDateLocal - dayStartDateLocal) / 60000));
        topPx = (minutesFromStart / 60) * hourHeight;
        if (end) {
          const [eh, em] = end.split(':').map(x => parseInt(x,10));
          const taskEndLocal = new Date(taskDate.getFullYear(), taskDate.getMonth(), taskDate.getDate(), eh, em);
          let duration = Math.round((taskEndLocal - taskDateLocal) / 60000);
          if (duration <= 0) duration = 30;
          heightPx = (duration / 60) * hourHeight;
        } else {
          heightPx = hourHeight * 0.9;
        }
      }
    } else {
      // no time: stack at top area
      topPx = 2;
      heightPx = hourHeight * 0.6;
    }
    // clamp to visible area
    const dayTotalHeight = (dayEndHour - dayStartHour + 1) * hourHeight;
    if (topPx < 0) topPx = 0;
    if (topPx + heightPx > dayTotalHeight) heightPx = dayTotalHeight - topPx;

  const el = document.createElement('div');
  el.className = 'agenda-task overlay-task';
  // account for table top offset inside the weekView
  const tableTopOffset = tableRect.top - weekViewRect.top;
  el.style.left = left + 'px';
  el.style.top = (tableTopOffset + topPx) + 'px';
    el.style.width = (dayWidth) + 'px';
    el.style.height = Math.max(24, heightPx) + 'px';
    const titleSpan = document.createElement('span');
    titleSpan.textContent = t.titol + (start ? ' (' + start + (end ? ' - ' + end : '') + ')' : '');
    el.appendChild(titleSpan);
    // edit button
    const btn = document.createElement('button');
    btn.className = 'agenda-task-edit-btn';
    btn.title = 'Editar tasca';
    btn.innerHTML = '&#9998;';
    // attach id and handler
    if (t && t.id) { btn.dataset.tascaId = t.id; agendaTasquesById[t.id] = t; }
    btn.onclick = function(e) { e.stopPropagation(); openEditModal(t); };
    el.appendChild(btn);
    el.dataset.tascaId = t.id || '';
    overlay.appendChild(el);
  }
  return true;
}

// --- RENDER TASKS OVERLAY (DAY) ---
function renderDayOverlay(tasques, date) {
  const dayView = document.querySelector('.agenda-calendar.day-view');
  if (!dayView) return;
  // Avoid creating overlays on small screens (use inline slot rendering)
  const MOBILE_OVERLAY_THRESHOLD = 900;
  if (window.innerWidth <= MOBILE_OVERLAY_THRESHOLD) return;
  let overlay = dayView.querySelector('.agenda-day-overlay');
  const table = dayView.querySelector('.agenda-day-table');
  if (!overlay) {
    overlay = document.createElement('div');
    overlay.className = 'agenda-day-overlay';
    dayView.appendChild(overlay);
  }
  overlay.innerHTML = '';
  const firstSlot = dayView.querySelector('.agenda-day-table tbody tr');
  if (!firstSlot) return;
  const slotElem = dayView.querySelector('.agenda-day-table tbody tr td.agenda-day-slot');
  if (!slotElem) return;
  const slotHeight = slotElem.getBoundingClientRect().height;
  const startHour = 6;
  for (const t of tasques) {
    if (t && t.id) agendaTasquesById[t.id] = t;
    if (t.data_tasca !== formatDateISO(date)) continue;
    const start = t.hora_inici;
    const end = t.hora_fi;
    let topPx = 2;
    let heightPx = slotHeight * 0.7;
    if (start) {
      const [sh, sm] = start.split(':').map(x => parseInt(x,10));
      topPx = ((sh - startHour) * 60 + sm) / 60 * slotHeight;
      if (end) {
        const [eh, em] = end.split(':').map(x => parseInt(x,10));
        let duration = (eh * 60 + em) - (sh * 60 + sm);
        if (duration <= 0) duration = 30;
        heightPx = (duration / 60) * slotHeight;
      } else {
        heightPx = slotHeight * 0.9;
      }
    }
    const el = document.createElement('div');
    el.className = 'agenda-task overlay-task-day';
    el.style.top = topPx + 'px';
    el.style.height = Math.max(20, heightPx) + 'px';
    const titleSpan = document.createElement('span');
    titleSpan.textContent = t.titol + (start ? ' (' + start + (end ? ' - ' + end : '') + ')' : '');
    el.appendChild(titleSpan);
    const btn = document.createElement('button');
    btn.className = 'agenda-task-edit-btn';
    btn.title = 'Editar tasca';
    btn.innerHTML = '&#9998;';
  if (t && t.id) { btn.dataset.tascaId = t.id; agendaTasquesById[t.id] = t; }
  btn.onclick = function(e) { e.stopPropagation(); openEditModal(t); };
    el.appendChild(btn);
    overlay.appendChild(el);
  }
}

// Open modal and populate fields for editing
function openEditModal(tasca) {
  const modal = document.getElementById('modal-add-task');
  if (!modal) return;
  document.getElementById('tasca-id').value = tasca.id || '';
  document.getElementById('task-title').value = tasca.titol || '';
  document.getElementById('task-desc').value = tasca.descripcio || '';
  // ensure date is YYYY-MM-DD
  document.getElementById('task-date').value = tasca.data_tasca || '';
  document.getElementById('task-start').value = tasca.hora_inici ? tasca.hora_inici.slice(0,5) : '';
  document.getElementById('task-end').value = tasca.hora_fi ? tasca.hora_fi.slice(0,5) : '';
  document.getElementById('task-priority').value = tasca.prioritat || 'mitjana';
  document.getElementById('task-category').value = tasca.categoria || 'treball';
  document.querySelector('#form-add-task input[name="es_important"]').checked = !!tasca.es_important;
  document.querySelector('#form-add-task input[name="es_urgent"]').checked = !!tasca.es_urgent;
  // show modal
  modal.style.display = '';
}

// Delegated click handler as a fallback in case per-button handlers fail
document.addEventListener('click', function(e) {
  const btn = e.target.closest && e.target.closest('.agenda-task-edit-btn');
  if (!btn) return;
  e.stopPropagation();
  const id = btn.dataset.tascaId;
  if (id && agendaTasquesById[id]) {
    openEditModal(agendaTasquesById[id]);
  } else {
    // try to parse from DOM dataset on parent overlay element
    const parent = btn.closest && btn.closest('[data-tasca-id]');
    if (parent && parent.dataset && parent.dataset.tascaId) {
      const pid = parent.dataset.tascaId;
      if (agendaTasquesById[pid]) openEditModal(agendaTasquesById[pid]);
    }
  }
});

// Hook form submit: decide add vs update based on hidden id
const form = document.getElementById('form-add-task');
if (form) {
  form.addEventListener('submit', async function(e) {
    e.preventDefault();
    const id = document.getElementById('tasca-id').value;
    const payload = {
      id: id || null,
      titol: document.getElementById('task-title').value,
      descripcio: document.getElementById('task-desc').value,
      data_tasca: document.getElementById('task-date').value,
      hora_inici: document.getElementById('task-start').value || null,
      hora_fi: document.getElementById('task-end').value || null,
      prioritat: document.getElementById('task-priority').value,
      categoria: document.getElementById('task-category').value,
      es_important: document.querySelector('#form-add-task input[name="es_important"]').checked,
      es_urgent: document.querySelector('#form-add-task input[name="es_urgent"]').checked,
    };
    try {
      const url = id ? 'api/tasques-update.php' : 'api/tasques-add.php';
      const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
      const json = await res.json();
      if (json.error) {
        document.getElementById('add-task-msg').textContent = json.error;
        return;
      }
      // close modal
      document.getElementById('modal-add-task').style.display = 'none';
      // clear id
      document.getElementById('tasca-id').value = '';
      // refresh current views
      renderWeek(currentMonday);
      renderDay(currentDay);
      renderMonth(currentYear, currentMonth);
    } catch (err) {
      document.getElementById('add-task-msg').textContent = 'Error de xarxa: ' + err.message;
    }
  });
}


// --- INICIALITZACIÓ I NAVEGACIÓ (attach handlers al DOMContentLoaded per evitar errors)
let currentDay, currentMonday, currentYear, currentMonth;

window.addEventListener('DOMContentLoaded', function() {
  // Estat inicial
  currentDay = new Date();
  currentMonday = getMonday(new Date());
  currentYear = currentDay.getFullYear();
  currentMonth = currentDay.getMonth();

  // VISTA DIÀRIA
  const prevDayBtn = document.getElementById('prev-day');
  const nextDayBtn = document.getElementById('next-day');
  const todayDayBtn = document.getElementById('today-day-btn');
  if (prevDayBtn) prevDayBtn.onclick = function() { currentDay.setDate(currentDay.getDate() - 1); currentYear = currentDay.getFullYear(); currentMonth = currentDay.getMonth(); renderDay(currentDay); };
  if (nextDayBtn) nextDayBtn.onclick = function() { currentDay.setDate(currentDay.getDate() + 1); currentYear = currentDay.getFullYear(); currentMonth = currentDay.getMonth(); renderDay(currentDay); };
  if (todayDayBtn) todayDayBtn.onclick = function() { currentDay = new Date(); currentYear = currentDay.getFullYear(); currentMonth = currentDay.getMonth(); renderDay(currentDay); };

  // VISTA SETMANAL
  const prevWeekBtn = document.getElementById('prev-week');
  const nextWeekBtn = document.getElementById('next-week');
  const todayBtn = document.getElementById('today-btn');
  const weekViewBtn2 = document.getElementById('week-view-btn-2');
  if (prevWeekBtn) prevWeekBtn.onclick = function() { currentMonday.setDate(currentMonday.getDate() - 7); currentYear = currentMonday.getFullYear(); currentMonth = currentMonday.getMonth(); renderWeek(currentMonday); };
  if (nextWeekBtn) nextWeekBtn.onclick = function() { currentMonday.setDate(currentMonday.getDate() + 7); currentYear = currentMonday.getFullYear(); currentMonth = currentMonday.getMonth(); renderWeek(currentMonday); };
  if (todayBtn) todayBtn.onclick = function() { currentMonday = getMonday(new Date()); currentYear = currentMonday.getFullYear(); currentMonth = currentMonday.getMonth(); renderWeek(currentMonday); };
  if (weekViewBtn2) weekViewBtn2.onclick = function() { showWeekView(); };

  // VISTA MENSUAL / TOGGLE
  const monthViewBtn = document.getElementById('month-view-btn');
  const weekViewBtn = document.getElementById('week-view-btn');
  function ensureMonthNav() {
    let monthNav = document.querySelector('.agenda-month-nav');
    if (!monthNav) {
      monthNav = document.createElement('div');
      monthNav.className = 'agenda-month-nav';
      monthNav.innerHTML = `
        <button class="agenda-btn" id="prev-month">&lt;</button>
        <button class="agenda-btn" id="today-month-btn">Avui</button>
        <button class="agenda-btn" id="next-month">&gt;</button>
      `;
      // place month nav inside the main header to avoid duplicated navs before the calendar
      const header = document.querySelector('.agenda-header');
      const addBtn = document.getElementById('add-task-btn');
      if (header) {
        // insert before Add Task button for better layout
        if (addBtn) header.insertBefore(monthNav, addBtn);
        else header.appendChild(monthNav);
      }
      // start hidden; shown only in month view
      monthNav.style.display = 'none';
      const prevMonth = document.getElementById('prev-month');
      const nextMonth = document.getElementById('next-month');
      const todayMonth = document.getElementById('today-month-btn');
      if (prevMonth) prevMonth.onclick = function() { currentMonth--; if (currentMonth < 0) { currentMonth = 11; currentYear--; } renderMonth(currentYear, currentMonth); };
      if (nextMonth) nextMonth.onclick = function() { currentMonth++; if (currentMonth > 11) { currentMonth = 0; currentYear++; } renderMonth(currentYear, currentMonth); };
      if (todayMonth) todayMonth.onclick = function() { const now = new Date(); currentYear = now.getFullYear(); currentMonth = now.getMonth(); renderMonth(currentYear, currentMonth); };
    }
  }

  function showMonthView() {
    document.querySelectorAll('.agenda-calendar').forEach(el => el.style.display = 'none');
    const mv = document.querySelector('.agenda-calendar.month-view'); if (mv) mv.style.display = '';
    // hide week-specific header buttons and show month nav
    if (monthViewBtn) monthViewBtn.style.display = 'none';
    if (weekViewBtn) { weekViewBtn.style.display = ''; weekViewBtn.textContent = 'Vista setmanal'; }
    const prevWeek = document.getElementById('prev-week'); if (prevWeek) prevWeek.style.display = 'none';
    const nextWeek = document.getElementById('next-week'); if (nextWeek) nextWeek.style.display = 'none';
    const todayWeek = document.getElementById('today-btn'); if (todayWeek) todayWeek.style.display = 'none';
    ensureMonthNav();
    const monthNav = document.querySelector('.agenda-month-nav'); if (monthNav) monthNav.style.display = '';
    renderMonth(currentYear, currentMonth);
  }
  function showWeekView() {
    document.querySelectorAll('.agenda-calendar').forEach(el => el.style.display = 'none');
    const wv = document.querySelector('.agenda-calendar.week-view'); if (wv) wv.style.display = '';
    // show week nav and hide month-specific controls
    if (weekViewBtn) weekViewBtn.style.display = 'none';
    if (monthViewBtn) { monthViewBtn.style.display = ''; monthViewBtn.textContent = 'Vista mensual'; }
    const prevWeek = document.getElementById('prev-week'); if (prevWeek) prevWeek.style.display = '';
    const nextWeek = document.getElementById('next-week'); if (nextWeek) nextWeek.style.display = '';
    const todayWeek = document.getElementById('today-btn'); if (todayWeek) todayWeek.style.display = '';
    const monthNav = document.querySelector('.agenda-month-nav'); if (monthNav) monthNav.style.display = 'none';
    renderWeek(currentMonday);
  }
  if (monthViewBtn) monthViewBtn.onclick = showMonthView;
  if (weekViewBtn) weekViewBtn.onclick = showWeekView;

  // Inicialitza vista per defecte
  renderWeek(currentMonday);
  renderDay(currentDay);
  // ensure month nav exists but hidden initially
  ensureMonthNav();
  // track view state for mobile auto-switch
  let forcedMobileView = false;
  let prevView = 'week';
  let currentView = 'week';

  function showDayView() {
    document.querySelectorAll('.agenda-calendar').forEach(el => el.style.display = 'none');
    const dv = document.querySelector('.agenda-calendar.day-view'); if (dv) dv.style.display = '';
    // adjust the top toggle buttons visibility
    const monthViewBtn = document.getElementById('month-view-btn');
    const weekViewBtn = document.getElementById('week-view-btn');
    if (weekViewBtn) weekViewBtn.style.display = '';
    if (monthViewBtn) monthViewBtn.style.display = '';
    currentView = 'day';
    renderDay(currentDay);
  }
  // modal open/close
  const addTaskBtn = document.getElementById('add-task-btn');
  const modalClose = document.getElementById('close-add-task-modal');
  const modal = document.getElementById('modal-add-task');
  if (addTaskBtn) addTaskBtn.onclick = function() { 
    // clear form for new task
    document.getElementById('tasca-id').value = '';
    document.getElementById('form-add-task').reset();
    if (modal) modal.style.display = '';
  };
  if (modalClose) modalClose.onclick = function() { if (modal) modal.style.display = 'none'; };
  // Re-render overlays on resize (debounced) to avoid mispositioning and duplicates
  let resizeTimer = null;
  window.addEventListener('resize', function() {
    if (resizeTimer) clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
      // Clear cached overlay elements
      const weekOverlay = document.querySelector('.agenda-week-overlay'); if (weekOverlay) weekOverlay.innerHTML = '';
      const dayOverlay = document.querySelector('.agenda-day-overlay'); if (dayOverlay) dayOverlay.innerHTML = '';
      // Auto-switch to mobile-optimized single-day view on narrow widths
      const MOBILE_VIEW_THRESHOLD = 700;
      if (window.innerWidth <= MOBILE_VIEW_THRESHOLD && !forcedMobileView) {
        // remember previous view to restore later
        if (document.querySelector('.agenda-calendar.week-view') && document.querySelector('.agenda-calendar.week-view').style.display !== 'none') prevView = 'week';
        else if (document.querySelector('.agenda-calendar.month-view') && document.querySelector('.agenda-calendar.month-view').style.display !== 'none') prevView = 'month';
        else prevView = currentView || 'week';
        forcedMobileView = true;
        showDayView();
      } else if (window.innerWidth > MOBILE_VIEW_THRESHOLD && forcedMobileView) {
        // restore previous view
        forcedMobileView = false;
        if (prevView === 'week') showWeekView();
        else if (prevView === 'month') showMonthView();
        else showWeekView();
      } else {
        // Re-render visible views without switching
        const mv = document.querySelector('.agenda-calendar.month-view');
        const wv = document.querySelector('.agenda-calendar.week-view');
        const dv = document.querySelector('.agenda-calendar.day-view');
        if (mv && mv.style.display !== 'none') renderMonth(currentYear, currentMonth);
        if (wv && wv.style.display !== 'none') renderWeek(currentMonday);
        if (dv && dv.style.display !== 'none') renderDay(currentDay);
      }
    }, 150);
  });
});


