<?php // index.php — MAILSYS Terminal UI ?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>MAILSYS v1.0 // TERMINAL</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Share+Tech+Mono&family=VT323:wght@400&display=swap" rel="stylesheet">
<style>
/* ── Reset & Base ─────────────────────────────────────────────── */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --green:      #00ff41;
  --green-dim:  #00cc33;
  --green-dark: #003b0e;
  --cyan:       #00e5ff;
  --red:        #ff2052;
  --yellow:     #ffe600;
  --bg:         #030a04;
  --bg2:        #040d05;
  --border:     #1a4d1e;
  --text-dim:   #2d7a38;
}

html, body {
  height: 100%;
  background: var(--bg);
  color: var(--green);
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  overflow: hidden;
  cursor: default;
}

/* ── Matrix Rain Canvas ───────────────────────────────────────── */
#matrix-canvas {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  opacity: 0.07;
  pointer-events: none;
  z-index: 0;
}

/* ── Scanline Overlay ─────────────────────────────────────────── */
.scanlines {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: repeating-linear-gradient(
    to bottom,
    transparent 0px,
    transparent 2px,
    rgba(0,0,0,0.12) 2px,
    rgba(0,0,0,0.12) 4px
  );
  pointer-events: none;
  z-index: 1;
}

/* ── CRT Vignette ─────────────────────────────────────────────── */
.vignette {
  position: fixed;
  top: 0; left: 0;
  width: 100%; height: 100%;
  background: radial-gradient(ellipse at center, transparent 60%, rgba(0,0,0,0.7) 100%);
  pointer-events: none;
  z-index: 2;
}

/* ── Layout ───────────────────────────────────────────────────── */
#app {
  position: relative;
  z-index: 10;
  display: flex;
  flex-direction: column;
  height: 100vh;
  padding: 10px;
  gap: 8px;
}

/* ── Header ASCII Banner ──────────────────────────────────────── */
#header {
  border: 1px solid var(--border);
  padding: 8px 14px 6px;
  background: rgba(0,20,5,0.85);
  flex-shrink: 0;
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 20px;
}

#banner {
  font-family: 'VT323', monospace;
  font-size: 28px;
  letter-spacing: 4px;
  color: var(--green);
  text-shadow: 0 0 20px var(--green), 0 0 40px var(--green-dim);
  animation: flicker 6s infinite;
  white-space: nowrap;
}

#sys-info {
  text-align: right;
  font-size: 11px;
  color: var(--text-dim);
  line-height: 1.6;
}
#sys-info span { color: var(--green-dim); }

@keyframes flicker {
  0%,94%,96%,100% { opacity: 1; }
  95% { opacity: 0.7; }
}

/* ── Main Content Area ────────────────────────────────────────── */
#main {
  display: flex;
  gap: 8px;
  flex: 1;
  min-height: 0;
}

/* ── Terminal Panel ───────────────────────────────────────────── */
#terminal-panel {
  flex: 1;
  display: flex;
  flex-direction: column;
  border: 1px solid var(--border);
  background: rgba(0,10,2,0.9);
  min-width: 0;
}

#terminal-titlebar {
  background: var(--green-dark);
  padding: 4px 12px;
  font-size: 11px;
  color: var(--green-dim);
  border-bottom: 1px solid var(--border);
  display: flex;
  justify-content: space-between;
  flex-shrink: 0;
}

#output {
  flex: 1;
  overflow-y: auto;
  padding: 12px 14px 6px;
  line-height: 1.65;
  font-size: 13px;
  scrollbar-width: thin;
  scrollbar-color: var(--border) transparent;
}

#output::-webkit-scrollbar { width: 4px; }
#output::-webkit-scrollbar-track { background: transparent; }
#output::-webkit-scrollbar-thumb { background: var(--border); }

/* Output line types */
.line { display: block; }
.line-cmd   { color: var(--cyan); }
.line-ok    { color: var(--green); }
.line-err   { color: var(--red); }
.line-warn  { color: var(--yellow); }
.line-info  { color: var(--text-dim); }
.line-sep   { color: var(--border); }
.line-header{ color: var(--green); font-weight: bold; text-shadow: 0 0 8px var(--green); }

/* ── Input Line ───────────────────────────────────────────────── */
#input-area {
  display: flex;
  align-items: center;
  padding: 8px 14px;
  border-top: 1px solid var(--border);
  gap: 6px;
  flex-shrink: 0;
  background: rgba(0,15,4,0.95);
}

.prompt {
  color: var(--cyan);
  white-space: nowrap;
  user-select: none;
  text-shadow: 0 0 8px var(--cyan);
}

#cmd-input {
  flex: 1;
  background: transparent;
  border: none;
  outline: none;
  color: var(--green);
  font-family: 'Share Tech Mono', monospace;
  font-size: 14px;
  caret-color: transparent; /* We'll use custom cursor */
}

#cursor-block {
  display: inline-block;
  width: 9px;
  height: 16px;
  background: var(--green);
  animation: blink 1s step-end infinite;
  vertical-align: middle;
  flex-shrink: 0;
}

@keyframes blink {
  0%, 100% { opacity: 1; }
  50%       { opacity: 0; }
}

/* ── Side Panel ───────────────────────────────────────────────── */
#side-panel {
  width: 280px;
  flex-shrink: 0;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.panel-box {
  border: 1px solid var(--border);
  background: rgba(0,10,2,0.9);
  overflow: hidden;
}

.panel-title {
  background: var(--green-dark);
  padding: 4px 10px;
  font-size: 11px;
  color: var(--green-dim);
  border-bottom: 1px solid var(--border);
  letter-spacing: 2px;
}

/* ── Command Dictionary ───────────────────────────────────────── */
#cmd-dict {
  flex: 1;
  overflow-y: auto;
  padding: 8px 10px;
  scrollbar-width: thin;
  scrollbar-color: var(--border) transparent;
}

.dict-entry { margin-bottom: 10px; }
.dict-cmd {
  color: var(--cyan);
  font-size: 12px;
  display: block;
}
.dict-desc {
  color: var(--text-dim);
  font-size: 11px;
  padding-left: 10px;
  line-height: 1.4;
  display: block;
}
.dict-sep { border: none; border-top: 1px solid var(--border); margin: 8px 0; }

/* ── Status Box ───────────────────────────────────────────────── */
#status-box { padding: 10px; font-size: 12px; }
.stat-row { display: flex; justify-content: space-between; margin-bottom: 4px; }
.stat-label { color: var(--text-dim); }
.stat-val   { color: var(--green); }
.stat-val.active { color: var(--green); text-shadow: 0 0 8px var(--green); animation: pulse 1s infinite; }
.stat-val.idle   { color: var(--text-dim); }
.stat-val.err    { color: var(--red); }

@keyframes pulse { 0%,100% { opacity:1; } 50% { opacity:0.5; } }

/* ── Countdown Bar ────────────────────────────────────────────── */
#countdown-section { padding: 8px 10px; border-top: 1px solid var(--border); }
#countdown-label { font-size: 11px; color: var(--text-dim); margin-bottom: 4px; }
#countdown-num {
  font-family: 'VT323', monospace;
  font-size: 38px;
  color: var(--green);
  text-shadow: 0 0 15px var(--green);
  text-align: center;
  line-height: 1;
}
#countdown-num.urgent { color: var(--red); text-shadow: 0 0 15px var(--red); }
#progress-bar-bg { height: 3px; background: var(--green-dark); margin-top: 4px; }
#progress-bar { height: 100%; background: var(--green); width: 0%; transition: width 1s linear; box-shadow: 0 0 6px var(--green); }

/* ── Recipient List (mini) ────────────────────────────────────── */
#recv-list-panel { flex-shrink: 0; }
#recv-list {
  padding: 6px 10px;
  max-height: 140px;
  overflow-y: auto;
  scrollbar-width: thin;
  scrollbar-color: var(--border) transparent;
}
.recv-item {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 11px;
  padding: 2px 0;
  cursor: pointer;
  color: var(--text-dim);
  transition: color 0.15s;
}
.recv-item:hover { color: var(--green); }
.recv-item.selected { color: var(--green); }
.recv-item.selected::before { content: '▶'; color: var(--cyan); font-size: 9px; }
.recv-item:not(.selected)::before { content: '▷'; font-size: 9px; }
.recv-email { flex: 1; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

/* ── Bottom Status Bar ────────────────────────────────────────── */
#statusbar {
  border: 1px solid var(--border);
  background: rgba(0,20,5,0.9);
  padding: 4px 14px;
  flex-shrink: 0;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 11px;
  color: var(--text-dim);
}

#statusbar .sb-item { display: flex; gap: 6px; }
#statusbar .sb-val  { color: var(--green-dim); }
#net-indicator { display: inline-block; width: 7px; height: 7px; border-radius: 50%; background: var(--green); margin-right: 4px; box-shadow: 0 0 6px var(--green); }

/* ── Glitch on error ──────────────────────────────────────────── */
@keyframes glitch {
  0%   { transform: translateX(0); }
  20%  { transform: translateX(-3px); }
  40%  { transform: translateX(3px); }
  60%  { transform: translateX(-2px); }
  80%  { transform: translateX(2px); }
  100% { transform: translateX(0); }
}
.glitch { animation: glitch 0.3s ease; }
</style>
</head>
<body>

<canvas id="matrix-canvas"></canvas>
<div class="scanlines"></div>
<div class="vignette"></div>

<div id="app">

  <!-- ── Header ───────────────────────────────────────────────── -->
  <div id="header">
    <div id="banner">◈ MAILSYS v1.0 // AUTOMATED DISPATCH TERMINAL ◈</div>
    <div id="sys-info">
      <span id="clock">--:--:--</span><br>
      OS: <span>MAILSYS LINUX</span> | USER: <span>root</span><br>
      SESSION: <span id="session-id">--</span>
    </div>
  </div>

  <!-- ── Main ─────────────────────────────────────────────────── -->
  <div id="main">

    <!-- Terminal -->
    <div id="terminal-panel">
      <div id="terminal-titlebar">
        <span>TERMINAL // CMD_SHELL</span>
        <span id="line-count">LINES: 0</span>
      </div>
      <div id="output"></div>
      <div id="input-area">
        <span class="prompt">root@mailsys:~$&nbsp;</span>
        <input type="text" id="cmd-input" autocomplete="off" spellcheck="false">
        <div id="cursor-block"></div>
      </div>
    </div>

    <!-- Side Panel -->
    <div id="side-panel">

      <!-- Command Dictionary -->
      <div class="panel-box" style="flex:1;display:flex;flex-direction:column;min-height:0;">
        <div class="panel-title">// CMD REFERENCE</div>
        <div id="cmd-dict">
          <div class="dict-entry">
            <span class="dict-cmd">add mail &lt;email&gt;</span>
            <span class="dict-desc">新增收件者到資料庫</span>
          </div>
          <hr class="dict-sep">
          <div class="dict-entry">
            <span class="dict-cmd">remove mail &lt;email&gt;</span>
            <span class="dict-desc">從資料庫刪除收件者</span>
          </div>
          <hr class="dict-sep">
          <div class="dict-entry">
            <span class="dict-cmd">list</span>
            <span class="dict-desc">顯示所有收件者清單</span>
          </div>
          <hr class="dict-sep">
          <div class="dict-entry">
            <span class="dict-cmd">select all</span>
            <span class="dict-desc">全選所有收件者</span>
          </div>
          <hr class="dict-sep">
          <div class="dict-entry">
            <span class="dict-cmd">select &lt;email&gt;</span>
            <span class="dict-desc">選取單一收件者</span>
          </div>
          <hr class="dict-sep">
          <div class="dict-entry">
            <span class="dict-cmd">deselect all</span>
            <span class="dict-desc">取消所有選取</span>
          </div>
          <hr class="dict-sep">
          <div class="dict-entry">
            <span class="dict-cmd">deselect &lt;email&gt;</span>
            <span class="dict-desc">取消選取單一收件者</span>
          </div>
          <hr class="dict-sep">
          <div class="dict-entry">
            <span class="dict-cmd">set interval &lt;min&gt;</span>
            <span class="dict-desc">設定寄送間隔（分鐘）</span>
          </div>
          <hr class="dict-sep">
          <div class="dict-entry">
            <span class="dict-cmd">start</span>
            <span class="dict-desc">開始自動寄送</span>
          </div>
          <hr class="dict-sep">
          <div class="dict-entry">
            <span class="dict-cmd">stop</span>
            <span class="dict-desc">停止自動寄送</span>
          </div>
          <hr class="dict-sep">
          <div class="dict-entry">
            <span class="dict-cmd">send now</span>
            <span class="dict-desc">立即寄送一次</span>
          </div>
          <hr class="dict-sep">
          <div class="dict-entry">
            <span class="dict-cmd">status</span>
            <span class="dict-desc">顯示目前系統狀態</span>
          </div>
          <hr class="dict-sep">
          <div class="dict-entry">
            <span class="dict-cmd">clear</span>
            <span class="dict-desc">清除終端機畫面</span>
          </div>
        </div>
      </div>

      <!-- Recipient List -->
      <div class="panel-box" id="recv-list-panel">
        <div class="panel-title" style="display:flex;justify-content:space-between;">
          <span>// TARGETS</span>
          <span id="recv-count" style="color:var(--green-dim);">0 / 0</span>
        </div>
        <div id="recv-list"><span style="color:var(--text-dim);font-size:11px;padding:6px 10px;display:block;">[empty — use 'list' to load]</span></div>
      </div>

      <!-- Status & Countdown -->
      <div class="panel-box" id="recv-list-panel">
        <div class="panel-title">// DISPATCH STATUS</div>
        <div id="status-box">
          <div class="stat-row">
            <span class="stat-label">MODE</span>
            <span class="stat-val idle" id="stat-mode">IDLE</span>
          </div>
          <div class="stat-row">
            <span class="stat-label">INTERVAL</span>
            <span class="stat-val" id="stat-interval">-- min</span>
          </div>
          <div class="stat-row">
            <span class="stat-label">SELECTED</span>
            <span class="stat-val" id="stat-selected">0 targets</span>
          </div>
          <div class="stat-row">
            <span class="stat-label">TOTAL SENT</span>
            <span class="stat-val" id="stat-total">0</span>
          </div>
        </div>
        <div id="countdown-section">
          <div id="countdown-label">NEXT DISPATCH IN</div>
          <div id="countdown-num">--</div>
          <div id="progress-bar-bg"><div id="progress-bar"></div></div>
        </div>
      </div>

    </div>
  </div>

  <!-- ── Status Bar ────────────────────────────────────────────── -->
  <div id="statusbar">
    <div class="sb-item"><span id="net-indicator"></span> <span>NET: <span class="sb-val">CONNECTED</span></span></div>
    <div class="sb-item">DB: <span class="sb-val" id="sb-db">mailsys_db</span></div>
    <div class="sb-item">SENT: <span class="sb-val" id="sb-sent">0</span></div>
    <div class="sb-item">v1.0 // MAILSYS</div>
  </div>

</div><!-- /#app -->

<script>
// ════════════════════════════════════════════════════
// MATRIX RAIN
// ════════════════════════════════════════════════════
(function() {
  const canvas = document.getElementById('matrix-canvas');
  const ctx    = canvas.getContext('2d');
  let W, H, cols, drops;
  const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789@#$%&*<>[]{}|\\~'.split('');
  const fs = 13;

  function init() {
    W = canvas.width  = window.innerWidth;
    H = canvas.height = window.innerHeight;
    cols = Math.floor(W / fs);
    drops = Array(cols).fill(1);
  }

  function draw() {
    ctx.fillStyle = 'rgba(3,10,4,0.05)';
    ctx.fillRect(0, 0, W, H);
    ctx.fillStyle = '#00ff41';
    ctx.font = fs + 'px Share Tech Mono';
    drops.forEach((y, i) => {
      ctx.fillText(chars[Math.floor(Math.random() * chars.length)], i * fs, y * fs);
      if (y * fs > H && Math.random() > 0.975) drops[i] = 0;
      drops[i]++;
    });
  }

  init();
  setInterval(draw, 55);
  window.addEventListener('resize', init);
})();

// ════════════════════════════════════════════════════
// CLOCK & SESSION
// ════════════════════════════════════════════════════
document.getElementById('session-id').textContent =
  'SID-' + Math.random().toString(36).substr(2,8).toUpperCase();

setInterval(() => {
  document.getElementById('clock').textContent = new Date().toTimeString().substr(0,8);
}, 1000);

// ════════════════════════════════════════════════════
// TERMINAL ENGINE
// ════════════════════════════════════════════════════
const output   = document.getElementById('output');
const input    = document.getElementById('cmd-input');
let lineCount  = 0;

function print(text, cls = 'line-ok') {
  const lines = String(text).split('\n');
  lines.forEach(line => {
    const el = document.createElement('span');
    el.className = 'line ' + cls;
    el.textContent = line;
    output.appendChild(el);
    lineCount++;
  });
  output.scrollTop = output.scrollHeight;
  document.getElementById('line-count').textContent = 'LINES: ' + lineCount;
}

function printSep() { print('─'.repeat(52), 'line-sep'); }

// ════════════════════════════════════════════════════
// STATE
// ════════════════════════════════════════════════════
let allEmails     = [];     // [{id, email, label}]
let selectedIds   = new Set();
let intervalMin   = null;
let dispatchTimer = null;
let countdownSec  = 0;
let countdownTick = null;
let totalSent     = 0;
let history       = [];
let historyIdx    = -1;

// ════════════════════════════════════════════════════
// API HELPERS
// ════════════════════════════════════════════════════
async function api(endpoint, method = 'GET', body = null) {
  const opts = { method, headers: { 'Content-Type': 'application/json' } };
  if (body) opts.body = JSON.stringify(body);
  const res  = await fetch(endpoint, opts);
  return res.json();
}

// ════════════════════════════════════════════════════
// UI HELPERS
// ════════════════════════════════════════════════════
function updateStatusPanel() {
  const mode = document.getElementById('stat-mode');
  if (dispatchTimer) {
    mode.textContent = '● DISPATCHING';
    mode.className = 'stat-val active';
  } else {
    mode.textContent = 'IDLE';
    mode.className = 'stat-val idle';
  }
  document.getElementById('stat-interval').textContent =
    intervalMin !== null ? intervalMin + ' min' : '-- min';
  document.getElementById('stat-selected').textContent =
    selectedIds.size + ' targets';
  document.getElementById('stat-total').textContent = totalSent;
  document.getElementById('sb-sent').textContent = totalSent;
}

function renderRecvList() {
  const list = document.getElementById('recv-list');
  if (allEmails.length === 0) {
    list.innerHTML = '<span style="color:var(--text-dim);font-size:11px;padding:6px 10px;display:block;">[no targets in database]</span>';
    document.getElementById('recv-count').textContent = '0 / 0';
    return;
  }
  list.innerHTML = '';
  allEmails.forEach(r => {
    const item = document.createElement('div');
    item.className = 'recv-item' + (selectedIds.has(r.id) ? ' selected' : '');
    item.innerHTML = `<span class="recv-email">${escHtml(r.email)}</span>`;
    item.addEventListener('click', () => {
      if (selectedIds.has(r.id)) { selectedIds.delete(r.id); }
      else { selectedIds.add(r.id); }
      renderRecvList();
      updateStatusPanel();
      print(`» ${selectedIds.has(r.id) ? 'SELECTED' : 'DESELECTED'}: ${r.email}`, 'line-info');
    });
    list.appendChild(item);
  });
  document.getElementById('recv-count').textContent =
    selectedIds.size + ' / ' + allEmails.length;
}

function escHtml(t) {
  return String(t).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ════════════════════════════════════════════════════
// COUNTDOWN LOGIC
// ════════════════════════════════════════════════════
function startCountdown(seconds) {
  stopCountdown();
  countdownSec = seconds;
  const total = seconds;
  const num   = document.getElementById('countdown-num');
  const bar   = document.getElementById('progress-bar');

  function tick() {
    num.textContent = countdownSec + 's';
    num.className   = countdownSec <= 10 ? 'urgent' : '';
    const pct = ((total - countdownSec) / total) * 100;
    bar.style.width = pct + '%';
    if (countdownSec <= 0) { stopCountdown(); return; }
    countdownSec--;
  }
  tick();
  countdownTick = setInterval(tick, 1000);
}

function stopCountdown() {
  if (countdownTick) { clearInterval(countdownTick); countdownTick = null; }
  document.getElementById('countdown-num').textContent = '--';
  document.getElementById('countdown-num').className   = '';
  document.getElementById('progress-bar').style.width  = '0%';
}

// ════════════════════════════════════════════════════
// SEND ACTION
// ════════════════════════════════════════════════════
async function dispatchEmails() {
  if (selectedIds.size === 0) {
    print('ERROR: no targets selected — use "select all" or "select <email>"', 'line-err');
    return;
  }
  print('> initiating dispatch sequence...', 'line-info');
  try {
    const data = await api('send_emails.php', 'POST', { ids: [...selectedIds] });
    if (data.success) {
      print('[' + (data.ts || '--') + '] ' + data.message, 'line-ok');
      print('  SUBJECT: ' + (data.subject || '?'), 'line-info');
      totalSent += selectedIds.size;
      updateStatusPanel();
      if (dispatchTimer && intervalMin !== null) {
        startCountdown(intervalMin);
      }
    } else {
      print('DISPATCH FAILED: ' + data.message, 'line-err');
      document.getElementById('terminal-panel').classList.add('glitch');
      setTimeout(() => document.getElementById('terminal-panel').classList.remove('glitch'), 300);
    }
  } catch(e) {
    print('NETWORK ERROR: ' + e.message, 'line-err');
  }
}

// ════════════════════════════════════════════════════
// COMMAND PARSER
// ════════════════════════════════════════════════════
async function runCommand(raw) {
  const cmd = raw.trim();
  if (!cmd) return;

  print('root@mailsys:~$ ' + cmd, 'line-cmd');
  printSep();

  // ── add mail <email> ──
  if (/^add mail\s+\S+/i.test(cmd)) {
    const email = cmd.split(/\s+/)[2];
    print('> registering target: ' + email, 'line-info');
    try {
      const d = await api('add_email.php', 'POST', { email });
      print(d.message, d.success ? 'line-ok' : 'line-err');
      if (d.success) {
        allEmails.push({ id: d.id, email });
        renderRecvList();
      }
    } catch(e) { print('NET ERROR: ' + e.message, 'line-err'); }

  // ── remove mail <email> ──
  } else if (/^remove mail\s+\S+/i.test(cmd)) {
    const email = cmd.split(/\s+/)[2];
    print('> removing target: ' + email, 'line-info');
    try {
      const d = await api('remove_email.php', 'POST', { email });
      print(d.message, d.success ? 'line-ok' : 'line-err');
      if (d.success) {
        const idx = allEmails.findIndex(r => r.email === email);
        if (idx !== -1) {
          selectedIds.delete(allEmails[idx].id);
          allEmails.splice(idx, 1);
          renderRecvList();
          updateStatusPanel();
        }
      }
    } catch(e) { print('NET ERROR: ' + e.message, 'line-err'); }

  // ── list ──
  } else if (/^list$/i.test(cmd)) {
    print('> fetching target list from database...', 'line-info');
    try {
      const d = await api('list_emails.php');
      if (d.success) {
        allEmails = d.data;
        if (allEmails.length === 0) {
          print('DATABASE IS EMPTY — use "add mail <email>" to add targets', 'line-warn');
        } else {
          print('ID    EMAIL                              REGISTERED', 'line-header');
          print('──    ─────────────────────────────────  ──────────', 'line-sep');
          allEmails.forEach(r => {
            const sel  = selectedIds.has(r.id) ? '▶' : ' ';
            const idStr = String(r.id).padEnd(5);
            const em    = r.email.padEnd(35);
            const ts    = r.created_at ? r.created_at.substr(0,10) : '--';
            print(sel + ' ' + idStr + em + ts, selectedIds.has(r.id) ? 'line-ok' : 'line-info');
          });
          print('TOTAL: ' + allEmails.length + ' targets', 'line-info');
        }
        renderRecvList();
      } else {
        print('ERROR: ' + d.message, 'line-err');
      }
    } catch(e) { print('NET ERROR: ' + e.message, 'line-err'); }

  // ── select all ──
  } else if (/^select all$/i.test(cmd)) {
    if (allEmails.length === 0) {
      print('NO TARGETS IN DATABASE — run "list" first', 'line-warn');
    } else {
      allEmails.forEach(r => selectedIds.add(r.id));
      print('ALL ' + allEmails.length + ' TARGETS SELECTED', 'line-ok');
      renderRecvList();
      updateStatusPanel();
    }

  // ── select <email> ──
  } else if (/^select\s+\S+/i.test(cmd)) {
    const email = cmd.split(/\s+/)[1];
    const r = allEmails.find(x => x.email.toLowerCase() === email.toLowerCase());
    if (r) {
      selectedIds.add(r.id);
      print('SELECTED: ' + r.email, 'line-ok');
      renderRecvList();
      updateStatusPanel();
    } else {
      print('NOT FOUND: ' + email + ' — run "list" to reload', 'line-err');
    }

  // ── deselect all ──
  } else if (/^deselect all$/i.test(cmd)) {
    selectedIds.clear();
    print('ALL TARGETS DESELECTED', 'line-warn');
    renderRecvList();
    updateStatusPanel();

  // ── deselect <email> ──
  } else if (/^deselect\s+\S+/i.test(cmd)) {
    const email = cmd.split(/\s+/)[1];
    const r = allEmails.find(x => x.email.toLowerCase() === email.toLowerCase());
    if (r) {
      selectedIds.delete(r.id);
      print('DESELECTED: ' + r.email, 'line-warn');
      renderRecvList();
      updateStatusPanel();
    } else {
      print('NOT FOUND: ' + email, 'line-err');
    }

  // ── set interval <n> ──
  } else if (/^set interval\s+\d+/i.test(cmd)) {
    const n = parseInt(cmd.split(/\s+/)[2]);
    if (n < 0) {
      print('INTERVAL MUST BE > 0 SECOND', 'line-err');
    } else {
      intervalMin = n;
      print('INTERVAL SET: ' + n + ' second(s)', 'line-ok');
      if (dispatchTimer) {
        clearInterval(dispatchTimer);
        dispatchTimer = setInterval(dispatchEmails, intervalMin * 1000);
        startCountdown(intervalMin);
        print('DISPATCH INTERVAL UPDATED — restarting timer', 'line-warn');
      }
      updateStatusPanel();
    }

  // ── start ──
  } else if (/^start$/i.test(cmd)) {
    if (dispatchTimer) {
      print('ALREADY RUNNING — use "stop" first', 'line-warn');
    } else if (intervalMin === null) {
      print('ERROR: interval not set — use "set interval <minutes>"', 'line-err');
    } else if (selectedIds.size === 0) {
      print('ERROR: no targets selected — use "select all" or "select <email>"', 'line-err');
    } else {
      print('DISPATCH SEQUENCE INITIATED', 'line-ok');
      print('> interval: ' + intervalMin + ' minute(s)', 'line-info');
      print('> targets:  ' + selectedIds.size, 'line-info');
      print('> sending first batch now...', 'line-info');
      await dispatchEmails();
      dispatchTimer = setInterval(dispatchEmails, intervalMin * 1000);
      startCountdown(intervalMin);
      updateStatusPanel();
    }

  // ── stop ──
  } else if (/^stop$/i.test(cmd)) {
    if (!dispatchTimer) {
      print('SYSTEM IS NOT RUNNING', 'line-warn');
    } else {
      clearInterval(dispatchTimer);
      dispatchTimer = null;
      stopCountdown();
      print('DISPATCH SEQUENCE TERMINATED', 'line-warn');
      updateStatusPanel();
    }

  // ── send now ──
  } else if (/^send now$/i.test(cmd)) {
    await dispatchEmails();

  // ── status ──
  } else if (/^status$/i.test(cmd)) {
    print('══ SYSTEM STATUS ══════════════════════════════════', 'line-header');
    print('  MODE:        ' + (dispatchTimer ? '● ACTIVE' : '○ IDLE'), dispatchTimer ? 'line-ok' : 'line-info');
    print('  INTERVAL:    ' + (intervalMin !== null ? intervalMin + 'sec' : 'NOT SET'), 'line-info');
    print('  SELECTED:    ' + selectedIds.size + ' / ' + allEmails.length + ' targets', 'line-info');
    print('  TOTAL SENT:  ' + totalSent + ' emails dispatched', 'line-info');
    if (dispatchTimer) {
      print('  NEXT SEND:   ' + countdownSec + 's', 'line-info');
    }

  // ── clear ──
  } else if (/^clear$/i.test(cmd)) {
    output.innerHTML = '';
    lineCount = 0;
    document.getElementById('line-count').textContent = 'LINES: 0';
    boot(); return;

  // ── help ──
  } else if (/^help$/i.test(cmd)) {
    print('══ AVAILABLE COMMANDS ══════════════════════════════', 'line-header');
    [
      ['add mail <email>',    '新增收件者'],
      ['remove mail <email>', '移除收件者'],
      ['list',                '顯示所有收件者'],
      ['select all',          '全選收件者'],
      ['select <email>',      '選取單一收件者'],
      ['deselect all',        '取消全選'],
      ['deselect <email>',    '取消單一選取'],
      ['set interval <min>',  '設定寄送間隔(分鐘)'],
      ['start',               '開始自動寄送'],
      ['stop',                '停止自動寄送'],
      ['send now',            '立即寄送一次'],
      ['status',              '顯示系統狀態'],
      ['clear',               '清除終端機'],
    ].forEach(([c, d]) => {
      print('  ' + c.padEnd(28) + '— ' + d, 'line-info');
    });

  // ── unknown ──
  } else {
    print('UNKNOWN COMMAND: "' + cmd + '" — type "help" for available commands', 'line-err');
    document.getElementById('terminal-panel').classList.add('glitch');
    setTimeout(() => document.getElementById('terminal-panel').classList.remove('glitch'), 300);
  }

  printSep();
}

// ════════════════════════════════════════════════════
// INPUT HANDLING
// ════════════════════════════════════════════════════
input.addEventListener('keydown', async (e) => {
  if (e.key === 'Enter') {
    const val = input.value;
    history.unshift(val);
    historyIdx = -1;
    input.value = '';
    await runCommand(val);
  } else if (e.key === 'ArrowUp') {
    e.preventDefault();
    if (historyIdx < history.length - 1) {
      historyIdx++;
      input.value = history[historyIdx];
    }
  } else if (e.key === 'ArrowDown') {
    e.preventDefault();
    if (historyIdx > 0) { historyIdx--; input.value = history[historyIdx]; }
    else { historyIdx = -1; input.value = ''; }
  } else if (e.key === 'Tab') {
    e.preventDefault();
    // basic tab-complete for common prefixes
    const v = input.value;
    const candidates = ['add mail ', 'remove mail ', 'select all', 'select ', 'deselect all', 'deselect ', 'set interval ', 'start', 'stop', 'send now', 'list', 'status', 'clear', 'help'];
    const match = candidates.find(c => c.startsWith(v) && c !== v);
    if (match) input.value = match;
  }
});

// Focus input on click anywhere
document.addEventListener('click', () => input.focus());

// ════════════════════════════════════════════════════
// BOOT SEQUENCE
// ════════════════════════════════════════════════════
function boot() {
  const lines = [
    { t: '  ███╗   ███╗ █████╗ ██╗██╗     ███████╗██╗   ██╗███████╗', c: 'line-header' },
    { t: '  ████╗ ████║██╔══██╗██║██║     ██╔════╝╚██╗ ██╔╝██╔════╝', c: 'line-header' },
    { t: '  ██╔████╔██║███████║██║██║     ███████╗ ╚████╔╝ ███████╗ ', c: 'line-header' },
    { t: '  ██║╚██╔╝██║██╔══██║██║██║     ╚════██║  ╚██╔╝  ╚════██║ ', c: 'line-header' },
    { t: '  ██║ ╚═╝ ██║██║  ██║██║███████╗███████║   ██║   ███████║ ', c: 'line-header' },
    { t: '  ╚═╝     ╚═╝╚═╝  ╚═╝╚═╝╚══════╝╚══════╝   ╚═╝   ╚══════╝ ', c: 'line-header' },
    { t: '', c: 'line-info' },
    { t: '  AUTOMATED EMAIL DISPATCH TERMINAL  //  v1.0  //  ROOT ACCESS GRANTED', c: 'line-warn' },
    { t: '  ─────────────────────────────────────────────────────────────', c: 'line-sep' },
  ];
  const boot2 = [
    { t: '> INITIALIZING SYSTEM...          [OK]', c: 'line-info' },
    { t: '> LOADING DATABASE DRIVER...      [OK]', c: 'line-info' },
    { t: '> ESTABLISHING SMTP GATEWAY...    [OK]', c: 'line-info' },
    { t: '> MOUNTING RECIPIENT STORE...     [OK]', c: 'line-info' },
    { t: '> ALL SYSTEMS NOMINAL', c: 'line-ok' },
    { t: '', c: 'line-info' },
    { t: '  Type "help" for available commands. Type "list" to load recipients.', c: 'line-info' },
    { t: '────────────────────────────────────────────────────────────────', c: 'line-sep' },
  ];

  let i = 0;
  function nextLine(arr, cb) {
    if (i >= arr.length) { i = 0; if (cb) cb(); return; }
    print(arr[i].t, arr[i].c);
    i++;
    setTimeout(() => nextLine(arr, cb), 30);
  }

  nextLine(lines, () => {
    setTimeout(() => nextLine(boot2, () => {
      updateStatusPanel();
      input.focus();
    }), 200);
  });
}

boot();
</script>
</body>
</html>
