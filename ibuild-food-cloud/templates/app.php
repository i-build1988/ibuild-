<?php
if (!defined('ABSPATH')) {
    http_response_code(403);
    exit;
}
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>I-BUILD قطاع التغذية - التشغيل والمخازن والمشتريات</title>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700;900&amp;display=swap" rel="stylesheet">
<script src="%%IBF_QRCODE_URL%%"></script>
<script src="%%IBF_HTML5_QRCODE_URL%%"></script>
<script src="%%IBF_XLSX_URL%%"></script>
<style>
:root {
  --bg:#f4f7f5; --card:#ffffff; --card2:#eef4f1; --border:#dbe6e0;
  --green:#16835b; --green-dark:#0f6747; --blue:#0b8fa3; --accent:#16835b; --accent2:#0b8fa3;
  --red:#c84a4a; --text:#17251f; --muted:#66776f;
  --shadow:0 14px 40px rgba(20,55,42,.08); --shadow-soft:0 5px 18px rgba(20,55,42,.06);
}
*{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'Cairo',sans-serif;background:linear-gradient(180deg,#f8faf9 0%,var(--bg) 100%);color:var(--text);min-height:100vh;}

/* OVERLAYS */
.overlay{position:fixed;inset:0;background:rgba(12,30,23,.72);backdrop-filter:blur(8px);z-index:999;display:flex;align-items:center;justify-content:center;padding:1rem;}
#pwd-overlay:target{display:flex!important;}
.modal-box{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:2rem;max-width:480px;width:100%;box-shadow:var(--shadow);}
.modal-box h2{font-size:1.3rem;font-weight:900;color:var(--green);margin-bottom:.5rem;}
.modal-box p{color:var(--muted);font-size:.85rem;margin-bottom:1.5rem;line-height:1.7;}
.field{margin-bottom:1rem;}
.field label{display:block;font-size:.85rem;color:var(--muted);margin-bottom:.4rem;font-weight:700;}
.field input,.field select{width:100%;padding:.7rem 1rem;background:var(--card2);border:1.5px solid var(--border);border-radius:10px;color:var(--text);font-family:'Cairo',sans-serif;font-size:.9rem;}
.field input:focus,.field select:focus{outline:none;border-color:var(--green);}
.btn-primary{width:100%;padding:.9rem;border-radius:11px;border:none;background:var(--green);color:#fff;font-family:'Cairo',sans-serif;font-size:1rem;font-weight:900;cursor:pointer;margin-top:.5rem;box-shadow:0 7px 18px rgba(22,131,91,.18);}
.btn-primary:hover{opacity:.9;}
.config-note{margin-top:1rem;padding:.8rem;background:rgba(22,131,91,.08);border-radius:8px;border:1px solid rgba(22,131,91,.25);font-size:.8rem;color:var(--green);line-height:1.6;}

/* NAV */
nav{background:rgba(255,255,255,.94);backdrop-filter:blur(12px);border-bottom:1px solid var(--border);padding:0 1.5rem;display:flex;align-items:center;justify-content:space-between;height:68px;position:sticky;top:0;z-index:100;box-shadow:var(--shadow-soft);}
.nav-brand{display:flex;align-items:center;gap:.8rem;}
.nav-logo-text{font-size:1.3rem;font-weight:900;background:linear-gradient(135deg,var(--green),var(--blue));-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.nav-subtitle{font-size:.7rem;color:var(--muted);display:block;margin-top:-4px;}
.nav-btns{display:flex;gap:.4rem;}
.nav-btn{padding:.35rem 1rem;border-radius:8px;border:none;cursor:pointer;font-family:'Cairo',sans-serif;font-size:.9rem;font-weight:700;transition:all .2s;}
.nav-btn.active{background:var(--green);color:#fff;box-shadow:0 5px 14px rgba(22,131,91,.16);}
.nav-btn:not(.active){background:var(--card2);color:var(--muted);}
@media(max-width:720px){.nav-btn{padding:.35rem .65rem;font-size:.78rem}}

/* PAGES */
.page{display:none;padding:1.5rem;max-width:920px;margin:0 auto;}
.page.active{display:block;}
body.manager-mode .page{display:none!important;}
body.manager-mode #page-manager{display:block!important;}
body.manager-mode #pwd-overlay{display:none!important;}

/* ===== شريط الانتقال السريع (للمدير) ===== */
.jump-bar{display:none;position:fixed;top:10px;left:10px;z-index:900;gap:.35rem;
  background:rgba(255,255,255,.96);border:1px solid var(--border);border-radius:14px;padding:.4rem;
  box-shadow:var(--shadow);backdrop-filter:blur(6px);}
body.manager-mode .jump-bar,body.jump-visible .jump-bar{display:flex;}
.jump-btn{display:flex;flex-direction:column;align-items:center;gap:2px;min-width:52px;
  background:transparent;border:0;border-radius:10px;padding:.4rem .3rem;cursor:pointer;
  color:var(--muted);font-family:'Cairo',sans-serif;transition:background .15s,transform .15s;}
.jump-btn span{font-size:1.15rem;line-height:1;}
.jump-btn em{font-style:normal;font-size:.6rem;font-weight:800;opacity:.85;}
.jump-btn:hover{background:var(--card2);transform:translateY(-2px);}
.jump-btn.active{background:#16835b;color:#fff;}
@media(max-width:600px){
  .jump-bar{top:auto;bottom:8px;left:8px;right:8px;justify-content:space-between;padding:.3rem;}
  .jump-btn{min-width:0;flex:1;}
  .jump-btn em{font-size:.55rem;}
}

/* ===== ثيم داشبورد المدير الفاتح ===== */
body.manager-mode{background:linear-gradient(180deg,#ffffff 0%,var(--bg) 100%);color:var(--text);}
body.manager-mode nav{background:rgba(255,255,255,.96);border-color:var(--border);}
body.manager-mode .page-title,body.manager-mode .section-card-title{color:var(--green);}
body.manager-mode .section-card{background:#ffffff;border-color:var(--border);box-shadow:var(--shadow-soft);}
body.manager-mode .stat-card{background:#ffffff;border-color:var(--border);box-shadow:var(--shadow-soft);}
body.manager-mode .stat-card:hover{transform:translateY(-3px);box-shadow:var(--shadow);}
body.manager-mode .stat-num{color:var(--green);font-size:1.9rem;letter-spacing:-.01em;}
body.manager-mode .stat-lbl{color:var(--muted);}
body.manager-mode .inv-table{color:var(--text);}
body.manager-mode .inv-table thead th{background:var(--card2);color:var(--green-dark);border-color:var(--border);}
body.manager-mode .inv-table td{border-color:var(--border);}
body.manager-mode .inv-table tbody tr:hover{background:#f7faf8;}
body.manager-mode .master-form-card,body.manager-mode .master-row{background:#ffffff;border-color:var(--border);color:var(--text);}
body.manager-mode .master-row span{color:var(--muted);}
body.manager-mode input,body.manager-mode select,body.manager-mode textarea{background:#ffffff;border-color:var(--border);color:var(--text);}
body.manager-mode input::placeholder{color:#819087;}
body.manager-mode .data-integrity-note{background:#f3f8f5;border-color:var(--border);color:var(--muted);}
body.manager-mode .action-btn{background:#ffffff;border-color:var(--border);color:var(--muted);}
body.manager-mode .action-btn:hover{background:var(--card2);color:var(--green);}
body.manager-mode .action-btn.danger{background:#fff7f7;border-color:#e2a4a4;color:var(--red);}
body.manager-mode .action-btn.danger:hover{background:var(--red);color:#fff;}
body.manager-mode .empty{color:var(--muted);}
body.manager-mode h3{color:var(--green-dark);}
/* مؤشر الاتجاه */
.trend{display:block;font-size:.76rem;font-weight:800;margin-top:.25rem;}
.trend.up{color:#4ade80;}
.trend.down{color:#f87171;}
.trend.flat{color:#94a3b8;}
/* الرسوم البيانية */
.chart-wrap{width:100%;overflow-x:auto;}
.chart-wrap svg{display:block;min-width:320px;width:100%;height:auto;}
.chart-legend{display:flex;gap:1rem;flex-wrap:wrap;font-size:.78rem;margin-top:.6rem;color:var(--muted);}
.chart-legend i{display:inline-block;width:11px;height:11px;border-radius:3px;margin-inline-end:.35rem;vertical-align:middle;}
.page-title{font-size:1.4rem;font-weight:900;margin-bottom:1.2rem;}
.page-title span{color:var(--green);}

/* LOGIN PAGE */
#page-login{max-width:420px;}
.login-logo{text-align:center;margin-bottom:2rem;}
.login-logo .big-logo{font-size:2.5rem;font-weight:900;background:linear-gradient(135deg,var(--green),var(--blue));-webkit-background-clip:text;-webkit-text-fill-color:transparent;}
.login-logo p{color:var(--muted);font-size:.85rem;margin-top:.3rem;}
.login-card{background:var(--card);border:1px solid var(--border);border-radius:20px;padding:2rem;box-shadow:var(--shadow);}
.login-card h3{font-size:1.1rem;font-weight:900;color:var(--green);margin-bottom:1.2rem;text-align:center;}
.login-error{background:rgba(200,74,74,.1);border:1px solid var(--red);border-radius:8px;padding:.7rem 1rem;font-size:.85rem;color:var(--red);margin-bottom:1rem;display:none;}

/* MEAL TABS */
.meal-tabs{display:flex;gap:.5rem;margin-bottom:1.2rem;flex-wrap:wrap;}
.meal-tab{flex:1;min-width:80px;padding:.55rem;border-radius:10px;border:2px solid var(--border);background:var(--card);color:var(--muted);cursor:pointer;font-family:'Cairo',sans-serif;font-size:.9rem;font-weight:700;transition:all .2s;text-align:center;}
.meal-tab.active{border-color:var(--green);color:var(--green);background:rgba(22,131,91,.08);}
.meal-content{display:none;}
.meal-content.active{display:block;}

/* INFO BOX */
.info-box{background:var(--card);border:1.5px solid var(--green);border-radius:14px;padding:1rem 1.2rem;margin-bottom:1.2rem;}
.info-box-title{font-size:.78rem;color:var(--green);font-weight:700;letter-spacing:1px;margin-bottom:.8rem;text-transform:uppercase;}
.info-grid{display:grid;grid-template-columns:1fr 1fr;gap:.8rem;}
@media(max-width:500px){.info-grid{grid-template-columns:1fr;}}
.info-field label{display:block;font-size:.78rem;color:var(--muted);font-weight:700;margin-bottom:.3rem;}
.info-input{width:100%;padding:.55rem .8rem;background:var(--card2);border:1.5px solid var(--border);border-radius:8px;color:var(--text);font-family:'Cairo',sans-serif;font-size:1rem;font-weight:700;text-align:center;}
.info-input:focus{outline:none;border-color:var(--green);}

/* ITEMS */
.section-label{font-size:.78rem;color:var(--muted);font-weight:700;letter-spacing:2px;text-transform:uppercase;margin-bottom:.8rem;border-right:3px solid var(--green);padding-right:.6rem;}
.items-grid{display:grid;grid-template-columns:1fr 1fr;gap:.7rem;margin-bottom:1.2rem;}
@media(max-width:560px){.items-grid{grid-template-columns:1fr;}}
.item-card{background:var(--card);border:1.5px solid var(--border);border-radius:12px;padding:.9rem 1rem;display:flex;align-items:center;justify-content:space-between;gap:.7rem;transition:border-color .2s;}
.item-card.custom-item{border-color:rgba(11,143,163,.3);}
.item-info{flex:1;min-width:0;}
.item-name{font-size:.95rem;font-weight:700;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.item-unit{font-size:.75rem;color:var(--muted);margin-top:.1rem;}
.item-note{font-size:.72rem;color:var(--blue);margin-top:.15rem;}
.qty-wrap{display:flex;align-items:center;gap:.3rem;flex-shrink:0;}
.qty-btn{width:28px;height:28px;border-radius:7px;border:none;background:var(--card2);color:var(--text);font-size:1rem;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:background .15s;}
.qty-btn:hover{background:var(--green);color:#fff;}
.qty-input{width:62px;height:32px;border-radius:7px;border:1.5px solid var(--border);background:var(--card2);color:var(--text);text-align:center;font-family:'Cairo',sans-serif;font-size:.95rem;font-weight:700;}
.qty-input:focus{outline:none;border-color:var(--green);}

/* ADD ITEM BOX */
.add-item-box{background:var(--card);border:1.5px dashed var(--blue);border-radius:12px;padding:1rem;margin-bottom:1.2rem;}
.add-item-box .add-title{font-size:.82rem;color:var(--blue);font-weight:700;margin-bottom:.7rem;}
.add-item-row{display:flex;gap:.5rem;flex-wrap:wrap;}
.add-item-row input{flex:1;min-width:120px;padding:.5rem .8rem;background:var(--card2);border:1.5px solid var(--border);border-radius:8px;color:var(--text);font-family:'Cairo',sans-serif;font-size:.9rem;}
.add-item-row input:focus{outline:none;border-color:var(--blue);}
.add-item-btn{padding:.5rem 1rem;border-radius:9px;border:none;background:var(--green);color:#fff;font-family:'Cairo',sans-serif;font-size:.85rem;font-weight:700;cursor:pointer;white-space:nowrap;}
.add-item-btn:hover{opacity:.85;}

.submit-btn{width:100%;padding:.95rem;border-radius:12px;border:none;background:var(--green);color:#fff;font-family:'Cairo',sans-serif;font-size:1.05rem;font-weight:900;cursor:pointer;transition:transform .15s,box-shadow .15s;box-shadow:0 7px 18px rgba(22,131,91,.18);}
.submit-btn:hover{transform:translateY(-2px);}
.submit-btn:disabled{opacity:.5;cursor:not-allowed;transform:none;}

/* SUPERVISOR HEADER */
.sup-header{background:var(--card);border:1.5px solid var(--border);border-radius:14px;padding:1rem 1.2rem;margin-bottom:1.2rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;}
.sup-info{display:flex;flex-direction:column;}
.sup-name{font-size:1rem;font-weight:900;color:var(--green);}
.sup-site{font-size:.82rem;color:var(--muted);}
.logout-btn{padding:.4rem .9rem;border-radius:8px;border:1.5px solid var(--border);background:none;color:var(--muted);cursor:pointer;font-family:'Cairo',sans-serif;font-size:.82rem;font-weight:700;transition:all .2s;}
.logout-btn:hover{border-color:var(--red);color:var(--red);}

/* DASHBOARD */
.dash-header{display:flex;align-items:center;justify-content:space-between;margin-bottom:1.2rem;flex-wrap:wrap;gap:.8rem;}
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:.8rem;margin-bottom:1.5rem;}
@media(max-width:600px){.stats-row{grid-template-columns:1fr 1fr;}}
.stat-card{background:#ffffff;border:1px solid var(--border);border-radius:16px;padding:1.1rem;text-align:center;box-shadow:var(--shadow-soft);transition:transform .2s,box-shadow .2s;}
.stat-card:hover{transform:translateY(-2px);box-shadow:var(--shadow);}
.stat-num{font-size:1.8rem;font-weight:900;color:var(--green);}
.stat-lbl{font-size:.78rem;color:var(--muted);margin-top:.2rem;}

.section-card{background:#ffffff;border:1px solid var(--border);border-radius:18px;padding:1.3rem;margin-bottom:1.2rem;box-shadow:var(--shadow-soft);}
.section-card-title{font-size:.95rem;font-weight:900;color:var(--green);margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;}

.log-table{width:100%;border-collapse:collapse;}
.log-table th,.log-table td{padding:.6rem .8rem;text-align:right;border-bottom:1px solid var(--border);font-size:.85rem;}
.log-table th{color:var(--muted);font-weight:700;font-size:.75rem;letter-spacing:1px;}
.log-table tr:last-child td{border-bottom:none;}
.log-table tr:hover td{background:rgba(22,131,91,.06);}

.badge{display:inline-block;padding:.18rem .65rem;border-radius:20px;font-size:.72rem;font-weight:700;}
.badge-فطار{background:rgba(22,131,91,.15);color:var(--green);}
.badge-غداء{background:rgba(11,143,163,.15);color:var(--blue);}
.badge-عشاء{background:rgba(22,131,91,.1);color:var(--green);}
.badge-site{background:rgba(11,143,163,.12);color:var(--blue);font-size:.75rem;}

.qr-section{background:var(--card);border:1.5px solid var(--border);border-radius:14px;padding:1.3rem;margin-bottom:1.2rem;display:flex;gap:1.5rem;align-items:center;flex-wrap:wrap;}
.qr-info h3{font-size:1rem;font-weight:900;margin-bottom:.4rem;}
.qr-info p{color:var(--muted);font-size:.82rem;line-height:1.6;}
#qrcode{background:#fff;padding:6px;border-radius:8px;display:inline-block;}

.date-input{background:var(--card2);border:1.5px solid var(--border);color:var(--text);padding:.4rem .8rem;border-radius:8px;font-family:'Cairo',sans-serif;font-size:.85rem;}
.site-filter{background:var(--card2);border:1.5px solid var(--border);color:var(--text);padding:.4rem .8rem;border-radius:8px;font-family:'Cairo',sans-serif;font-size:.85rem;}
.action-btn{padding:.45rem .95rem;border-radius:9px;border:1px solid var(--border);background:var(--card);color:var(--muted);cursor:pointer;font-family:'Cairo',sans-serif;font-size:.82rem;font-weight:700;transition:all .2s;}
.action-btn:hover{border-color:var(--green);color:var(--green);}
.action-btn.danger{border-color:var(--red);color:var(--red);}
.action-btn.danger:hover{background:var(--red);color:#fff;}
.action-btn.excel{border-color:var(--green);color:var(--green);}
.action-btn.excel:hover{background:var(--green);color:#fff;}

.empty{text-align:center;padding:2.5rem;color:var(--muted);}
.empty .icon{font-size:2.5rem;margin-bottom:.8rem;}
.spinner{display:inline-block;width:18px;height:18px;border:2px solid rgba(255,255,255,.2);border-top-color:#fff;border-radius:50%;animation:spin .7s linear infinite;}
@keyframes spin{to{transform:rotate(360deg);}}

.toast{position:fixed;bottom:1.5rem;left:50%;transform:translateX(-50%) translateY(120px);padding:.75rem 1.8rem;border-radius:12px;font-weight:700;font-size:.95rem;transition:transform .3s;z-index:9999;white-space:nowrap;}
.toast.show{transform:translateX(-50%) translateY(0);}
.toast.success{background:var(--green);color:#fff;}
.toast.error{background:var(--red);color:#fff;}
.toast.info{background:var(--blue);color:#fff;}

.meal-group{margin-bottom:1.8rem;}
.meal-group-header{display:flex;align-items:center;gap:.8rem;margin-bottom:.8rem;flex-wrap:wrap;}
.tag{background:var(--card2);border:1px solid var(--border);border-radius:6px;padding:.2rem .6rem;font-size:.75rem;color:var(--muted);}
.tag strong{color:var(--text);}

/* CHEF */
.chef-log-card{background:var(--card);border:1.5px solid var(--border);border-radius:14px;padding:1.2rem;margin-bottom:1rem;transition:border-color .2s;}
.chef-log-card.approved{border-color:var(--green);}
.chef-log-card.rejected{border-color:var(--red);}
.chef-log-header{display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;margin-bottom:.8rem;}
.chef-log-title{font-size:.95rem;font-weight:900;}
.chef-log-meta{font-size:.78rem;color:var(--muted);}
.chef-items-list{display:flex;flex-wrap:wrap;gap:.4rem;margin-bottom:.8rem;}
.chef-item-tag{background:var(--card2);border:1px solid var(--border);border-radius:20px;padding:.25rem .75rem;font-size:.8rem;}
.chef-item-tag strong{color:var(--green);}
.chef-actions{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.6rem;}
.chef-approve-btn{flex:1;padding:.6rem;border-radius:8px;border:none;background:var(--green);color:#fff;font-family:'Cairo',sans-serif;font-size:.9rem;font-weight:700;cursor:pointer;transition:opacity .2s;}
.chef-approve-btn:hover{opacity:.85;}
.chef-reject-btn{flex:1;padding:.6rem;border-radius:8px;border:1.5px solid var(--red);background:none;color:var(--red);font-family:'Cairo',sans-serif;font-size:.9rem;font-weight:700;cursor:pointer;transition:all .2s;}
.chef-reject-btn:hover{background:var(--red);color:#fff;}
.chef-notes-input{width:100%;padding:.6rem .9rem;background:var(--card2);border:1.5px solid var(--border);border-radius:8px;color:var(--text);font-family:'Cairo',sans-serif;font-size:.85rem;resize:none;}
.chef-notes-input:focus{outline:none;border-color:var(--blue);}
.status-badge{display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .8rem;border-radius:20px;font-size:.8rem;font-weight:700;}
.status-badge.approved{background:rgba(22,131,91,.15);color:var(--green);}
.status-badge.rejected{background:rgba(200,74,74,.15);color:var(--red);}
.status-badge.pending{background:rgba(11,143,163,.15);color:var(--blue);}
.chef-header-box{background:var(--card);border:1.5px solid var(--border);border-radius:14px;padding:1rem 1.2rem;margin-bottom:1.2rem;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.5rem;}

/* INVENTORY */
.inv-table{width:100%;border-collapse:collapse;}
.inv-table th,.inv-table td{padding:.6rem .7rem;text-align:right;border-bottom:1px solid var(--border);font-size:.85rem;}
.inv-table th{color:var(--muted);font-weight:700;font-size:.75rem;letter-spacing:1px;background:var(--card2);}
.inv-table tr:hover td{background:rgba(22,131,91,.02);}
.inv-input{width:100%;padding:.35rem .5rem;background:var(--card2);border:1.5px solid var(--border);border-radius:6px;color:var(--text);font-family:'Cairo',sans-serif;font-size:.85rem;text-align:center;}
.inv-input:focus{outline:none;border-color:var(--green);}
.inv-closing{font-weight:900;color:var(--green);}
.inv-closing.negative{color:var(--red);}
.inv-cost{color:var(--blue);font-weight:700;}
.inv-total-row td{font-weight:900;background:rgba(22,131,91,.05);border-top:2px solid var(--green);}
.inv-save-btn{padding:.5rem 1.2rem;border-radius:9px;border:none;background:var(--green);color:#fff;font-family:'Cairo',sans-serif;font-size:.9rem;font-weight:700;cursor:pointer;}
.inv-save-btn:hover{opacity:.85;}
.inv-add-row{display:flex;gap:.5rem;flex-wrap:wrap;padding:.8rem;background:rgba(11,143,163,.05);border-radius:10px;border:1.5px dashed var(--blue);margin-bottom:1rem;}
.inv-add-row input{flex:1;min-width:100px;padding:.5rem .7rem;background:var(--card2);border:1.5px solid var(--border);border-radius:8px;color:var(--text);font-family:'Cairo',sans-serif;font-size:.85rem;}
.inv-add-row input:focus{outline:none;border-color:var(--blue);}
.inv-search-row{display:flex;align-items:center;gap:.5rem;margin-bottom:.8rem;}
.inv-search-row input{width:100%;padding:.55rem .8rem;background:var(--card2);border:1.5px solid var(--border);border-radius:8px;color:var(--text);font-family:'Cairo',sans-serif;font-size:.9rem;}
.inv-search-row input:focus{outline:none;border-color:var(--blue);}
.inv-search-count{font-size:.8rem;color:var(--muted);white-space:nowrap;}
.ration-selector{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.7rem;margin-bottom:1rem;}
.ration-option{display:flex;align-items:center;gap:.7rem;padding:.8rem 1rem;border:1.5px solid var(--border);border-radius:11px;background:var(--card);cursor:pointer;transition:.2s;}
.ration-option:has(input:checked){border-color:var(--green);background:rgba(22,131,91,.06);box-shadow:inset -3px 0 var(--green);}
.ration-option input{width:18px;height:18px;accent-color:var(--green);}
.ration-option strong,.ration-option span{display:block;}
.ration-option strong{font-size:.9rem;}.ration-option span{font-size:.75rem;color:var(--muted);margin-top:.15rem;}
.ration-section-title{grid-column:1/-1;display:flex;align-items:center;justify-content:space-between;gap:.5rem;color:var(--green);font-size:.85rem;font-weight:900;margin-top:.15rem;}
.ration-choice-group{grid-column:1/-1;padding:.8rem;border:1.5px dashed var(--green);border-radius:14px;background:rgba(22,131,91,.035);}
.ration-choice-title{display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin-bottom:.65rem;color:var(--green);font-size:.88rem;font-weight:900;}
.ration-choice-title span{font-size:.72rem;color:var(--muted);font-weight:700;}
.ration-choice-options{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:.6rem;}
.ration-option.alternative{background:var(--card2);}
.backup-bar{display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:1rem;padding:.8rem;border:1.5px dashed var(--blue);border-radius:10px;background:rgba(11,143,163,.05);}
.backup-bar input{display:none;}
.master-forms{display:grid;grid-template-columns:1fr 1fr;gap:1rem;margin-bottom:1.2rem;}
.master-form-card{padding:1rem;border:1.5px solid var(--border);border-radius:12px;background:var(--card2);}
.master-form-card h3,.master-tables h3{font-size:.95rem;margin-bottom:.8rem;color:var(--green);}
.master-form-grid{display:grid;grid-template-columns:1fr 1fr;gap:.55rem;margin-bottom:.7rem;}
.master-form-grid input,.master-form-grid select{min-width:0;padding:.55rem .7rem;border:1.5px solid var(--border);border-radius:8px;background:var(--card);color:var(--text);font-family:'Cairo',sans-serif;}
.master-tables{display:grid;grid-template-columns:1fr 1fr;gap:1rem;}
.master-list{display:grid;gap:.5rem;max-height:270px;overflow:auto;}
.master-row{display:flex;align-items:center;justify-content:space-between;gap:.6rem;padding:.7rem .8rem;border:1px solid var(--border);border-radius:9px;background:var(--card);}
.master-row strong,.master-row span{display:block}.master-row strong{font-size:.85rem}.master-row span{font-size:.72rem;color:var(--muted);margin-top:.15rem}.master-row code{font-size:.7rem;color:var(--blue)}
.ration-modal-box{max-width:980px;max-height:94vh;overflow:auto;}
.ration-component-row{display:grid;grid-template-columns:1.4fr 90px 85px 80px 125px 35px;gap:.45rem;margin-bottom:.5rem;padding:.55rem;border:1px solid var(--border);border-radius:9px;background:var(--card2);}
.ration-component-row input,.ration-component-row select{min-width:0;padding:.5rem;border:1.5px solid var(--border);border-radius:7px;background:var(--card);font-family:'Cairo',sans-serif;}
.ration-component-row button{border:none;border-radius:7px;color:var(--red);background:rgba(200,74,74,.1);cursor:pointer;}
.item-card,.sup-header,.chef-header-box,.qr-section,.info-box{border-width:1px;border-radius:16px;box-shadow:0 3px 14px rgba(20,55,42,.035);}
.item-card:hover{border-color:rgba(22,131,91,.35);transform:translateY(-1px);}
.field input,.field select,.info-input,.date-input,.site-filter,.inv-input,.qty-input{transition:border-color .2s,box-shadow .2s,background .2s;}
.field input:focus,.field select:focus,.info-input:focus,.date-input:focus,.site-filter:focus,.inv-input:focus,.qty-input:focus{box-shadow:0 0 0 3px rgba(22,131,91,.1);}
.workflow-strip{display:flex;gap:.45rem;flex-wrap:wrap;margin:.65rem 0;}
.workflow-step{display:inline-flex;align-items:center;gap:.3rem;padding:.3rem .7rem;border:1px solid var(--border);border-radius:20px;background:var(--card2);font-size:.72rem;color:var(--muted);font-weight:700;}
.workflow-step.done{border-color:rgba(22,131,91,.35);background:rgba(22,131,91,.08);color:var(--green);}
.workflow-step.rejected{border-color:rgba(200,74,74,.35);background:rgba(200,74,74,.08);color:var(--red);}
.ops-card{border:1px solid var(--border);border-radius:14px;background:var(--card);padding:1rem;margin-bottom:.75rem;box-shadow:0 3px 14px rgba(20,55,42,.035);}
.ops-card-head{display:flex;align-items:flex-start;justify-content:space-between;gap:.6rem;flex-wrap:wrap;margin-bottom:.5rem;}
.ops-card-title{font-weight:900;color:var(--text);}.ops-card-meta{font-size:.75rem;color:var(--muted);margin-top:.15rem;}
.ops-actions{display:flex;gap:.45rem;flex-wrap:wrap;margin-top:.7rem;}
.compact-form{display:grid;grid-template-columns:repeat(4,minmax(120px,1fr));gap:.55rem;margin-bottom:.75rem;}
.compact-form input,.compact-form select,.compact-form textarea{width:100%;padding:.58rem .7rem;border:1px solid var(--border);border-radius:9px;background:var(--card2);color:var(--text);font-family:'Cairo',sans-serif;}
.compact-form textarea{grid-column:1/-1;min-height:70px;resize:vertical;}
.data-integrity-note{padding:.7rem .9rem;border-right:3px solid var(--green);background:rgba(22,131,91,.06);border-radius:8px;font-size:.78rem;color:var(--muted);margin:.7rem 0;}
.evidence-preview,.evidence-gallery{display:grid;grid-template-columns:repeat(auto-fill,minmax(120px,1fr));gap:.65rem;margin:.8rem 0;}
.evidence-preview img,.evidence-gallery img{width:100%;height:110px;object-fit:cover;border-radius:10px;border:1px solid var(--border);background:var(--card2);}
.employee-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:.7rem;}
.employee-card{border:1px solid var(--border);border-radius:12px;background:var(--card);padding:.85rem;display:block;min-width:0;}
.employee-card>.action-btn{width:100%;}
.employee-card small{display:block;color:var(--muted);margin-top:.18rem;}
.scanner-box{max-width:480px;margin:.8rem auto;border:1px dashed var(--blue);border-radius:12px;padding:.5rem;background:#fff;}
.attendance-present{border-color:var(--green);background:rgba(22,131,91,.05);}
.inline-recipe-panel{border:1px solid rgba(22,131,91,.35);background:linear-gradient(180deg,#fff,rgba(22,131,91,.035));border-radius:16px;padding:1rem;margin-bottom:1.2rem;}
.inline-recipe-head{display:flex;align-items:flex-start;justify-content:space-between;gap:.7rem;flex-wrap:wrap;margin-bottom:.8rem;}
.inline-recipe-head strong{color:var(--green);font-size:1rem}.inline-recipe-head span{font-size:.78rem;color:var(--muted);}
.inline-fixed-list{display:flex;gap:.4rem;flex-wrap:wrap;margin-bottom:.75rem}.inline-fixed-list .workflow-step{color:var(--green);border-color:rgba(22,131,91,.28);}
.inline-choice-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:.55rem;}
.inline-choice-option{border:1px solid var(--border);border-radius:10px;background:var(--card);padding:.65rem;display:grid;gap:.4rem;}
.inline-choice-option strong{font-size:.82rem}.inline-choice-option small{font-size:.68rem;color:var(--muted);display:block;}
.inline-choice-option input{width:100%;padding:.5rem;border:1.5px solid var(--border);border-radius:8px;background:var(--card2);font-family:'Cairo',sans-serif;text-align:center;font-weight:900;color:var(--green);}
.inline-group-status{font-size:.75rem;font-weight:800;margin:.55rem 0}.inline-group-status.ok{color:var(--green)}.inline-group-status.warn{color:var(--red)}
.auto-total{background:rgba(22,131,91,.09)!important;color:var(--green)!important;border-color:rgba(22,131,91,.35)!important;}
.equipment-kpis{display:grid;grid-template-columns:repeat(4,1fr);gap:.7rem;margin-bottom:1rem;}
.equipment-kpi{background:var(--card);border:1px solid var(--border);border-radius:12px;padding:.85rem;text-align:center;box-shadow:var(--shadow-soft);}
.equipment-kpi strong{display:block;font-size:1.35rem;color:var(--green);}.equipment-kpi span{font-size:.73rem;color:var(--muted);}
.equipment-status{display:inline-flex;align-items:center;gap:.3rem;padding:.25rem .55rem;border-radius:999px;font-size:.72rem;font-weight:800;white-space:nowrap;}
.equipment-status.operational{background:rgba(22,131,91,.1);color:var(--green);}
.equipment-status.needs_maintenance,.equipment-status.under_maintenance{background:rgba(224,143,35,.12);color:#a55e00;}
.equipment-status.out_of_service{background:rgba(200,74,74,.1);color:var(--red);}
.equipment-toolbar{display:flex;gap:.5rem;align-items:center;flex-wrap:wrap;margin-bottom:1rem;}
.equipment-toolbar input[type=search]{flex:1;min-width:220px;padding:.62rem .8rem;border:1.5px solid var(--border);border-radius:9px;background:var(--card);font-family:'Cairo',sans-serif;}
.maintenance-due{color:var(--red);font-weight:800}.maintenance-ok{color:var(--green);}
@media(max-width:560px){.ration-selector,.ration-choice-options{grid-template-columns:1fr;}.meal-tab{min-width:125px;}}
@media(max-width:760px){.master-forms,.master-tables{grid-template-columns:1fr}.master-form-grid{grid-template-columns:1fr}}
@media(max-width:760px){.ration-component-row{grid-template-columns:1fr 1fr}.ration-component-row button{min-height:36px}.compact-form{grid-template-columns:1fr 1fr}}
@media(max-width:700px){.equipment-kpis{grid-template-columns:1fr 1fr}.equipment-toolbar>*{width:100%}}
@media(max-width:500px){.compact-form{grid-template-columns:1fr}}
</style>
</head>
<body>

<!-- CONFIG OVERLAY -->
<div class="overlay" id="config-overlay" style="display:none;">
  <div class="modal-box">
    <h2>⚙️ إعدادات I-BUILD</h2>
    <p>إعداد المستخدمين والمواقع والصلاحيات يتم من لوحة WordPress الآمنة.</p>
    <button class="btn-primary" onclick="saveConfig()">فتح إدارة المستخدمين والصلاحيات</button>
    <button class="action-btn" style="width:100%;margin-top:.6rem" onclick="closeConfig()">إلغاء</button>
    <div class="config-note">🔒 لا توجد كلمات مرور داخل ملف الواجهة. تسجيل الدخول والتحقق من الصلاحيات يتمان على الخادم.</div>
  </div>
</div>

<!-- RATION STANDARD MODAL -->
<div class="overlay" id="ration-overlay" style="display:none;">
  <div class="modal-box ration-modal-box">
    <h2>🧮 إضافة مقنن متعدد المكونات</h2>
    <p>عرّف صنف المنيو ومكوناته، والكمية القياسية للفرد، ونسبة الفاقد، ومخزن الصرف.</p>
    <div class="info-grid">
      <div class="field"><label>كود الصنف</label><input id="ration-code" placeholder="DISH-001"></div>
      <div class="field"><label>اسم الصنف</label><input id="ration-dish-name" placeholder="أرز بالشعرية"></div>
      <div class="field"><label>فترة الوجبة</label><select id="ration-meal"><option value="breakfast">الفطار</option><option value="lunch">الغداء</option><option value="dinner">العشاء</option></select></div>
      <div class="field"><label>مجموعة البدائل (اختياري)</label><input id="ration-choice-group" placeholder="مثال: بروتين الغداء"></div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;gap:.5rem;margin:.5rem 0 .8rem"><strong>المكونات والمقنن للفرد</strong><button class="action-btn" onclick="addRationComponentRow()">➕ مكون</button></div>
    <div id="ration-components"></div>
    <div style="display:flex;gap:.6rem;margin-top:1rem"><button class="btn-primary" onclick="saveRationStandard()">💾 حفظ المقنن</button><button class="action-btn" onclick="closeRationModal()">إلغاء</button></div>
  </div>
</div>

<!-- NAV -->
<nav>
  <div class="nav-brand">
    <div>
      <div class="nav-logo-text">iBUILD</div>
      <span class="nav-subtitle">قطاع التغذية والتشغيل · قاعدة بيانات مركزية</span>
    </div>
  </div>
  <div class="jump-bar" id="jump-bar">
    <button class="jump-btn" onclick="managerJump('dashboard')" title="داشبورد المدير"><span>📊</span><em>الداشبورد</em></button>
    <button class="jump-btn" onclick="managerJump('supervisor')" title="شاشة الإشراف"><span>👤</span><em>الإشراف</em></button>
    <button class="jump-btn" onclick="managerJump('chef')" title="شاشة الشيف"><span>👨‍🍳</span><em>الشيف</em></button>
    <button class="jump-btn" onclick="managerJump('admin')" title="الإدارة العليا"><span>🏢</span><em>الإدارة العليا</em></button>
    <button class="jump-btn" onclick="managerJump('hr')" title="متابعة HR"><span>👔</span><em>HR</em></button>
    <button class="jump-btn" onclick="managerJump('quality')" title="الجودة"><span>📸</span><em>الجودة</em></button>
  </div>
  <div class="nav-btns" id="nav-btns">
    <button class="nav-btn active" onclick="showLoginPage(this)" id="btn-supervisor">الأشراف</button>
    <button class="nav-btn" onclick="requestChefAccess(this)" id="btn-chef">الشيف 👨‍🍳</button>
    <button class="nav-btn" onclick="showManagerLogin(this);return false;" id="btn-manager">المدير</button>
    <button class="nav-btn" id="btn-reviewer" style="display:none">المراجع 🔎</button>
  </div>
</nav>

<!-- ===== CHEF PAGE ===== -->
<div class="page" id="page-chef">
  <div class="chef-header-box">
    <div>
      <div style="font-size:1rem;font-weight:900;color:var(--green)" id="chef-name-display">👨‍🍳 الشيف</div>
      <div style="font-size:.82rem;color:var(--muted)" id="chef-site-display">📍 الموقع</div>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;">
      <input type="date" class="date-input" id="chef-filter-date" onchange="renderChefPage()">
      <button class="logout-btn" onclick="doChefLogout()">خروج</button>
    </div>
  </div>

  <div class="stats-row" style="grid-template-columns:repeat(3,1fr)">
    <div class="stat-card"><div class="stat-num" id="chef-stat-pending" style="color:var(--blue)">0</div><div class="stat-lbl">في الانتظار</div></div>
    <div class="stat-card"><div class="stat-num" id="chef-stat-approved" style="color:var(--green)">0</div><div class="stat-lbl">تمت الموافقة</div></div>
    <div class="stat-card"><div class="stat-num" id="chef-stat-rejected" style="color:var(--red)">0</div><div class="stat-lbl">مرفوض</div></div>
  </div>

  <div id="chef-logs-container">
    <div class="empty"><div class="icon">📭</div><div>لا يوجد تسجيلات</div></div>
  </div>

  <div class="section-card" style="margin-top:1.2rem">
    <div class="section-card-title"><span>📝 طلبات الشراء لاعتماد الشيف</span><button class="action-btn" onclick="renderChefPurchaseRequests()">تحديث</button></div>
    <div id="chef-purchase-requests"><div class="empty"><div class="icon">📝</div><div>لا توجد طلبات شراء</div></div></div>
  </div>
</div>

<!-- ===== REVIEWER PAGE ===== -->
<div class="page" id="page-reviewer">
  <div class="chef-header-box">
    <div>
      <div style="font-size:1rem;font-weight:900;color:var(--green)" id="rev-name-display">🔎 المراجع</div>
      <div style="font-size:.82rem;color:var(--muted)" id="rev-scope-display">📍 المدارس المسندة</div>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;flex-wrap:wrap">
      <select class="date-input" id="rev-site" onchange="renderReviewer()"></select>
      <input type="date" class="date-input" id="rev-from" onchange="renderReviewer()">
      <input type="date" class="date-input" id="rev-to" onchange="renderReviewer()">
      <button class="action-btn" onclick="renderReviewer()">تحديث</button>
      <button class="logout-btn" onclick="doLogout()">خروج</button>
    </div>
  </div>

  <div class="meal-tabs" style="margin-bottom:1rem">
    <button class="meal-tab active" id="rev-tab-cost" onclick="switchReviewerTab('cost',this)">💰 التكلفة</button>
    <button class="meal-tab" id="rev-tab-audit" onclick="switchReviewerTab('audit',this)">🧾 سجل المراجعة</button>
    <button class="meal-tab" id="rev-tab-perms" onclick="switchReviewerTab('perms',this)">🔐 الصلاحيات</button>
  </div>

  <!-- التكلفة -->
  <div class="meal-content active" id="rev-panel-cost">
    <div class="stats-row" style="grid-template-columns:repeat(4,1fr)">
      <div class="stat-card"><div class="stat-num" id="rev-cost-total">0.00</div><div class="stat-lbl">إجمالي التكلفة (ج)</div></div>
      <div class="stat-card"><div class="stat-num" id="rev-cost-meals">0</div><div class="stat-lbl">وجبات منصرفة</div></div>
      <div class="stat-card"><div class="stat-num" id="rev-cost-per-meal">0.00</div><div class="stat-lbl">تكلفة الوجبة (ج)</div></div>
      <div class="stat-card"><div class="stat-num" id="rev-cost-waste">0.00</div><div class="stat-lbl">تكلفة الهدر (ج)</div></div>
    </div>
    <div class="section-card">
      <div class="section-card-title">
        <span>💰 تكلفة المدارس</span>
        <button class="action-btn" onclick="exportReviewerCostExcel()">⬇️ تصدير Excel</button>
      </div>
      <div class="data-integrity-note">شاشة عرض فقط. التكلفة = قيمة المنصرف من المخزن + تكلفة الهدر، خلال الفترة المختارة ولمدارسك المسندة فقط.</div>
      <div id="rev-cost-table"><div class="empty"><div class="icon">💰</div><div>اختر فترة لعرض التكلفة</div></div></div>
    </div>
  </div>

  <!-- سجل المراجعة -->
  <div class="meal-content" id="rev-panel-audit">
    <div class="section-card">
      <div class="section-card-title">
        <span>🧾 سجل المراجعة — من فعل ماذا ومتى</span>
        <span style="display:flex;gap:.4rem;flex-wrap:wrap">
          <select class="date-input" id="rev-audit-action" onchange="renderReviewerAudit()">
            <option value="">كل العمليات</option>
            <option value="insert">إضافة</option>
            <option value="update">تعديل</option>
            <option value="save">حفظ</option>
            <option value="delete">حذف</option>
            <option value="chef_decision">قرار الشيف</option>
            <option value="warehouse_issue">اعتماد صرف المخزن</option>
            <option value="permissions">تغيير صلاحيات</option>
            <option value="create_user">إنشاء مستخدم</option>
            <option value="disable_user">تعطيل مستخدم</option>
            <option value="delete_user">حذف مستخدم</option>
          </select>
          <button class="action-btn" onclick="renderReviewerAudit()">تحديث</button>
          <button class="action-btn" onclick="exportReviewerAuditExcel()">⬇️ تصدير Excel</button>
        </span>
      </div>
      <div class="data-integrity-note">كل إنشاء وتعديل وحذف وتغيير صلاحية مسجَّل باسم منفّذه ووقته. السجل للقراءة فقط ولا يمكن تعديله من أي حساب.</div>
      <div class="stats-row" style="grid-template-columns:repeat(3,1fr);padding:0;margin-bottom:1rem">
        <div class="stat-card"><div class="stat-num" id="rev-audit-count">0</div><div class="stat-lbl">حركة معروضة</div></div>
        <div class="stat-card"><div class="stat-num" id="rev-audit-total">0</div><div class="stat-lbl">إجمالي الحركات</div></div>
        <div class="stat-card"><div class="stat-num" id="rev-audit-actors">0</div><div class="stat-lbl">منفّذون</div></div>
      </div>
      <div id="rev-audit-table"><div class="empty"><div class="icon">🧾</div><div>لا توجد حركات في هذه الفترة</div></div></div>
    </div>
  </div>

  <!-- الصلاحيات -->
  <div class="meal-content" id="rev-panel-perms">
    <div class="section-card">
      <div class="section-card-title">
        <span>🔐 صلاحيات المستخدمين</span>
        <button class="action-btn" onclick="renderReviewerPermissions()">تحديث</button>
      </div>
      <div class="data-integrity-note">تعديل أي خانة يُحفظ على الخادم فور الضغط على «حفظ»، ويُسجَّل في سجل المراجعة باسمك ووقته.</div>
      <div id="rev-perms-table"><div class="empty"><div class="icon">🔐</div><div>جاري تحميل المستخدمين...</div></div></div>
    </div>
  </div>
</div>

<!-- CHEF PASSWORD MODAL -->
<div class="overlay" id="chef-pwd-overlay" style="display:none;">
  <div class="modal-box" style="max-width:360px;text-align:center;">
    <div style="font-size:2.5rem;margin-bottom:.8rem;">👨‍🍳</div>
    <h2 style="color:var(--blue)">دخول الشيف</h2>
    <p>أدخل كلمة سر الشيف للدخول</p>
    <input type="password" id="chef-pwd-input" placeholder="كلمة السر"
      style="width:100%;padding:.7rem 1rem;background:var(--card2);border:1.5px solid var(--border);border-radius:10px;color:var(--text);font-family:'Cairo',sans-serif;font-size:1rem;text-align:center;margin-bottom:.8rem;"
      onkeydown="if(event.key==='Enter') checkChefPassword()">
    <div id="chef-pwd-error" style="color:var(--red);font-size:.85rem;margin-bottom:.8rem;display:none;">❌ كلمة السر غلط</div>
    <div style="display:flex;gap:.5rem;">
      <button onclick="checkChefPassword()" style="flex:1;padding:.8rem;border-radius:10px;border:none;background:var(--green);color:#fff;font-family:'Cairo',sans-serif;font-size:1rem;font-weight:900;cursor:pointer;">دخول</button>
      <button onclick="document.getElementById('chef-pwd-overlay').style.display='none'" style="padding:.8rem 1.2rem;border-radius:10px;border:1.5px solid var(--border);background:none;color:var(--muted);font-family:'Cairo',sans-serif;cursor:pointer;">إلغاء</button>
    </div>
  </div>
</div>

<!-- ===== LOGIN PAGE ===== -->
<div class="page active" id="page-login">
  <div class="login-logo">
    <div class="big-logo">iBUILD</div>
    <p>قطاع التغذية - صرف الوجبات والمخازن والمشتريات</p>
  </div>
  <div class="login-card">
    <h3>🔐 تسجيل الدخول</h3>
    <div id="login-error" class="login-error">❌ كلمة السر غلط</div>
    <div class="field">
      <label>كلمة السر</label>
      <input type="password" id="login-pwd" placeholder="أدخل كلمة السر" onkeydown="if(event.key==='Enter') doLogin()">
    </div>
    <button class="btn-primary" onclick="doLogin()">دخول</button>
    <div class="config-note" style="color:var(--muted);border-color:var(--border)">يتم تسجيل الدخول من صفحة النظام الآمنة بحساب مستقل لكل موظف.</div>
  </div>
</div>

<!-- ===== MANAGER LOGIN PAGE ===== -->
<div class="page" id="page-manager-login" style="max-width:420px;">
  <div class="login-logo">
    <div class="big-logo">iBUILD</div>
    <p>دخول المدير</p>
  </div>
  <div class="login-card">
    <h3>🔐 كلمة سر المدير</h3>
    <div id="manager-page-error" class="login-error">❌ كلمة السر غلط</div>
    <div class="field">
      <label>كلمة السر</label>
      <input type="password" id="manager-page-pwd" placeholder="أدخل كلمة سر المدير" onkeydown="if(event.key==='Enter'){event.preventDefault(); checkManagerPagePassword();}">
    </div>
    <button class="btn-primary" onclick="checkManagerPagePassword();return false;">دخول المدير</button>
  </div>
</div>

<!-- ===== SUPERVISOR PAGE ===== -->
<div class="page" id="page-supervisor">
  <div class="sup-header">
    <div class="sup-info">
      <div class="sup-name" id="sup-name-display">👤 اسم المشرف</div>
      <div class="sup-site" id="sup-site-display">📍 الموقع</div>
    </div>
    <div style="display:flex;gap:.5rem;align-items:center;">
      <div style="font-size:.85rem;color:var(--muted)" id="today-label"></div>
      <button class="logout-btn" onclick="doLogout()">خروج</button>
    </div>
  </div>

  <div class="meal-tabs">
    <button class="meal-tab active" onclick="switchMeal('breakfast',this)">🌅 الفطار</button>
    <button class="meal-tab" onclick="switchMeal('lunch',this)">☀️ الغداء</button>
    <button class="meal-tab" onclick="switchMeal('dinner',this)">🌙 العشاء</button>
    <button class="meal-tab" onclick="switchMeal('recipe',this)" style="border-color:rgba(22,131,91,.35);color:var(--green)">🧮 المنيو والريسيبي</button>
    <button class="meal-tab" onclick="switchMeal('inventory',this)" style="border-color:rgba(11,143,163,.4);color:var(--blue)">📦 المخزن</button>
    <button class="meal-tab" onclick="switchMeal('purchase-request',this)">📝 طلب شراء</button>
    <button class="meal-tab" onclick="switchMeal('invoice',this)" style="border-color:rgba(22,131,91,.4);color:var(--green)">🧾 فاتورة مشتريات</button>
    <button class="meal-tab" onclick="switchMeal('cashbox',this)" style="border-color:rgba(22,131,91,.35);color:var(--green)">💰 خزنة المشتريات</button>
    <button class="meal-tab" onclick="switchMeal('returns',this)">↩️ مرتجعات وهدر</button>
    <button class="meal-tab" onclick="switchMeal('cleaning',this)" style="border-color:rgba(11,143,163,.4);color:var(--blue)">🧹 مخزن النظافة</button>
    <button class="meal-tab" onclick="switchMeal('operation',this)" style="border-color:rgba(196,138,26,.45);color:#a9741a">⚙️ مخزن التشغيل</button>
    <button class="meal-tab" id="tab-quality" onclick="switchMeal('quality',this)" style="border-color:rgba(22,131,91,.4);color:var(--green)">📸 الجودة</button>
    <button class="meal-tab" id="tab-hr" onclick="switchMeal('hr',this)" style="border-color:rgba(11,143,163,.4);color:var(--blue)">👥 HR والعاملون</button>
    <button class="meal-tab" id="tab-equipment" onclick="switchMeal('equipment',this)" style="border-color:rgba(224,143,35,.5);color:#a55e00">🛠️ المعدات والصيانة</button>
    <button class="meal-tab" id="tab-hr-overview" onclick="switchMeal('hr-overview',this)" style="border-color:rgba(120,72,190,.45);color:#6b3fa0">👔 متابعة HR</button>
  </div>

  <!-- QUALITY TAB -->
  <div class="meal-content" id="meal-quality">
    <div class="info-box">
      <div class="info-box-title">📸 توثيق الجودة بالصور</div>
      <div class="info-grid">
        <div class="info-field"><label>التاريخ</label><input class="info-input" type="date" id="quality-date" onchange="loadQualityChecks()"></div>
        <div class="info-field"><label>نوع الفحص</label><select class="info-input" id="quality-category"><option value="food_preparation">تحضير الطعام</option><option value="refrigerators">الثلاجات والتبريد</option><option value="warehouses">المخازن</option><option value="hygiene">النظافة الشخصية والموقع</option></select></div>
        <div class="info-field"><label>المكان / المنطقة</label><input class="info-input" id="quality-area" placeholder="مثال: مطبخ التحضير"></div>
        <div class="info-field"><label>درجة الحرارة (اختياري)</label><input class="info-input" type="number" step="0.1" id="quality-temperature" placeholder="°C"></div>
      </div>
      <div class="field" style="margin-top:.8rem"><label>الملاحظات والإجراء التصحيحي</label><input id="quality-notes" placeholder="الحالة والملاحظات والإجراء المطلوب"></div>
      <div class="inv-add-row">
        <input type="file" id="quality-photos" accept="image/jpeg,image/png,image/webp" capture="environment" multiple onchange="previewQualityPhotos(this)">
        <div style="font-size:.78rem;color:var(--muted);align-self:center">صور تحضير الطعام أو الثلاجات أو المخازن — حتى 6 صور، و8MB للصورة.</div>
      </div>
      <div id="quality-photo-preview" class="evidence-preview"></div>
      <button class="submit-btn" id="quality-save-btn" onclick="saveQualityCheck()">💾 حفظ فحص الجودة والصور</button>
    </div>
    <div class="section-label">سجل الجودة بالموقع</div>
    <div id="quality-checks-list"><div class="empty"><div class="icon">📸</div><div>لا توجد فحوصات مسجلة</div></div></div>
  </div>

  <!-- HR TAB -->
  <div class="meal-content" id="meal-hr">
    <div class="info-box" style="border-color:var(--blue)">
      <div class="info-box-title" style="color:var(--blue)">👥 العاملون بالموقع</div>
      <div class="compact-form">
        <input id="hr-employee-code" placeholder="كود العامل">
        <input id="hr-employee-name" placeholder="اسم العامل">
        <input id="hr-employee-job" placeholder="الوظيفة">
        <input id="hr-employee-phone" type="tel" placeholder="رقم الهاتف (اختياري)">
      </div>
      <button class="add-item-btn" onclick="addHrEmployee()">إضافة العامل وتوليد QR</button>
    </div>
    <div class="stats-row">
      <div class="stat-card"><div class="stat-num" id="hr-total-employees">0</div><div class="stat-lbl">العاملون النشطون</div></div>
      <div class="stat-card attendance-present"><div class="stat-num" id="hr-present-count">0</div><div class="stat-lbl">الموجودون الآن</div></div>
      <div class="stat-card"><div class="stat-num" id="hr-checkins-count">0</div><div class="stat-lbl">حضور اليوم</div></div>
      <div class="stat-card"><div class="stat-num" id="hr-checkouts-count">0</div><div class="stat-lbl">انصراف اليوم</div></div>
    </div>
    <div class="section-card">
      <div class="section-card-title"><span>📷 تسجيل الحضور والانصراف بالـ QR</span></div>
      <div class="compact-form">
        <select id="hr-attendance-action"><option value="check_in">تسجيل حضور</option><option value="check_out">تسجيل انصراف</option></select>
        <input id="hr-manual-token" placeholder="امسح QR أو أدخل الكود يدويًا">
        <button class="add-item-btn" onclick="processManualAttendance()">تسجيل بالكود</button>
        <button class="action-btn" onclick="startAttendanceScanner()">فتح الكاميرا</button>
      </div>
      <div id="hr-qr-reader" class="scanner-box" style="display:none"></div>
      <button class="action-btn danger" id="hr-stop-scanner" style="display:none;margin:auto" onclick="stopAttendanceScanner()">إغلاق الكاميرا</button>
      <div class="data-integrity-note">آخر حركة لكل عامل تحدد حالته الحالية، ويُرحّل عدد الموجودين تلقائيًا إلى «أفراد طاقم التشغيل» في الفطار والغداء والعشاء.</div>
    </div>
    <div class="section-label">قائمة العاملين وQR</div>
    <div id="hr-employees-list" class="employee-grid"></div>
    <div class="section-label" style="margin-top:1.2rem">حركات اليوم</div>
    <div id="hr-attendance-list"></div>
  </div>

  <!-- EQUIPMENT & MAINTENANCE TAB -->
  <div class="meal-content" id="meal-equipment">
    <div class="equipment-kpis">
      <div class="equipment-kpi"><strong id="equipment-total-count">0</strong><span>إجمالي المعدات</span></div>
      <div class="equipment-kpi"><strong id="equipment-maintenance-count" style="color:#a55e00">0</strong><span>تحتاج صيانة</span></div>
      <div class="equipment-kpi"><strong id="equipment-overdue-count" style="color:var(--red)">0</strong><span>صيانة متأخرة</span></div>
      <div class="equipment-kpi"><strong id="equipment-cost-total">0</strong><span>إجمالي مصروف الصيانة</span></div>
    </div>

    <div class="info-box" style="border-color:#d98b23">
      <div class="info-box-title" style="color:#a55e00">🛠️ إضافة معدة إلى الكشف</div>
      <input type="hidden" id="equipment-edit-id">
      <div class="compact-form">
        <input id="equipment-code" placeholder="كود المعدة *">
        <input id="equipment-name" placeholder="اسم المعدة *">
        <input id="equipment-category" placeholder="النوع — ثلاجة، فرن، خلاط...">
        <input id="equipment-serial" placeholder="الرقم المسلسل">
        <input id="equipment-location" placeholder="مكان المعدة *">
        <input id="equipment-purchase-date" type="date" title="تاريخ الشراء">
        <select id="equipment-status">
          <option value="operational">تعمل بحالة جيدة</option>
          <option value="needs_maintenance">تحتاج صيانة</option>
          <option value="under_maintenance">تحت الصيانة</option>
          <option value="out_of_service">خارج الخدمة</option>
        </select>
        <input id="equipment-next-date" type="date" title="موعد الصيانة القادمة">
        <textarea id="equipment-notes" placeholder="ملاحظات حالة المعدة أو العطل"></textarea>
      </div>
      <div style="display:flex;gap:.55rem;flex-wrap:wrap">
        <button class="add-item-btn" id="equipment-save-btn" onclick="saveEquipment()">💾 حفظ المعدة</button>
        <button class="action-btn" id="equipment-cancel-edit" style="display:none" onclick="resetEquipmentForm()">إلغاء التعديل</button>
      </div>
    </div>

    <div class="equipment-toolbar">
      <input type="search" id="equipment-search" placeholder="🔎 بحث بالكود أو اسم المعدة أو المكان" oninput="renderEquipmentPanel()">
      <input type="file" id="equipment-import-file" accept=".xlsx,.xls,.csv" style="display:none" onchange="importEquipmentFile(this)">
      <button class="action-btn" onclick="document.getElementById('equipment-import-file').click()">📤 رفع كشف Excel</button>
      <button class="action-btn excel" onclick="exportEquipmentExcel()">📥 تنزيل كشف Excel</button>
    </div>
    <div class="data-integrity-note">أعمدة Excel المقبولة: كود المعدة، اسم المعدة، النوع، الرقم المسلسل، المكان، الحالة، تاريخ الشراء، آخر صيانة، الصيانة القادمة، تكلفة الصيانة، الملاحظات.</div>

    <div style="overflow-x:auto;margin-bottom:1.2rem">
      <table class="inv-table">
        <thead><tr><th>الكود</th><th>المعدة والمكان</th><th>الحالة</th><th>آخر صيانة</th><th>الصيانة القادمة</th><th>إجمالي المصروف</th><th>إجراءات</th></tr></thead>
        <tbody id="equipment-tbody"><tr><td colspan="7"><div class="empty"><div class="icon">🛠️</div><div>لا توجد معدات مسجلة</div></div></td></tr></tbody>
      </table>
    </div>

    <div class="info-box" id="maintenance-form-box" style="border-color:var(--blue)">
      <div class="info-box-title" style="color:var(--blue)">🔧 تسجيل عملية صيانة</div>
      <div class="compact-form">
        <select id="maintenance-equipment"><option value="">اختر المعدة *</option></select>
        <input id="maintenance-date" type="date" title="تاريخ تنفيذ الصيانة">
        <select id="maintenance-type"><option value="preventive">صيانة وقائية</option><option value="corrective">صيانة إصلاحية</option><option value="emergency">صيانة طارئة</option></select>
        <input id="maintenance-technician" placeholder="الفني أو شركة الصيانة">
        <input id="maintenance-cost" type="number" min="0" step="0.01" placeholder="المبلغ المصروف بالجنيه">
        <select id="maintenance-result"><option value="operational">تم الإصلاح وتعمل</option><option value="under_maintenance">ما زالت تحت الصيانة</option><option value="out_of_service">خارج الخدمة</option></select>
        <input id="maintenance-next-date" type="date" title="تاريخ الصيانة القادمة">
        <input id="maintenance-invoice" placeholder="رقم الفاتورة / أمر الشغل">
        <textarea id="maintenance-work" placeholder="العطل والأعمال التي تمت وقطع الغيار المستخدمة *"></textarea>
      </div>
      <button class="submit-btn" style="background:var(--blue)" onclick="saveMaintenanceLog()">✅ حفظ عملية الصيانة والتكلفة</button>
    </div>

    <div class="section-label">سجل عمليات الصيانة</div>
    <div id="maintenance-history"><div class="empty"><div class="icon">🔧</div><div>لا توجد عمليات صيانة مسجلة</div></div></div>
  </div>

  <!-- MENU & RECIPE TAB -->
  <div class="meal-content" id="meal-recipe">
    <div class="info-box" style="border-color:var(--green);">
      <div class="info-box-title" style="color:var(--green);">🧮 إضافة منيو وتوليد الريسيبي طبقًا للمقننات</div>
      <div class="info-grid">
        <div class="info-field"><label>اسم المنيو</label><input class="info-input" type="text" id="recipe-menu-name" value="منيو التشغيل اليومي"></div>
        <div class="info-field"><label>تاريخ التشغيل</label><input class="info-input" type="date" id="recipe-date"></div>
        <div class="info-field"><label>فترة الوجبة</label><select class="info-input" id="recipe-meal" onchange="renderRationSelector()"><option value="breakfast">الفطار</option><option value="lunch">الغداء</option><option value="dinner">العشاء</option></select></div>
        <div class="info-field"><label>عدد الوجبات</label><input class="info-input" type="number" min="1" id="recipe-servings" value="100"></div>
      </div>
    </div>
    <div style="display:flex;align-items:center;justify-content:space-between;gap:.7rem;flex-wrap:wrap;margin-bottom:.8rem"><div class="section-label" style="border-color:var(--green);color:var(--green);margin:0">اختر أصناف المنيو</div><button class="action-btn" onclick="openRationModal()">➕ إضافة مقنن متعدد المكونات</button></div>
    <div id="ration-selector" class="ration-selector"></div>
    <div style="display:flex;gap:.6rem;flex-wrap:wrap;margin-bottom:1rem;">
      <button class="submit-btn" style="flex:1;background:var(--green);" onclick="generateRecipe()">🧮 توليد الريسيبي</button>
      <button class="action-btn" onclick="prepareRecipeForIssue()">📤 تجهيز طلب الصرف</button>
      <button class="action-btn" onclick="saveGeneratedMenu()">💾 حفظ المنيو</button>
      <button class="action-btn excel" onclick="exportRecipeExcel()">📥 Excel</button>
    </div>
    <div class="info-box" id="recipe-summary" style="display:none;border-color:var(--green);"></div>
    <div style="overflow-x:auto;">
      <table class="inv-table">
        <thead><tr><th>صنف المنيو</th><th>المكون</th><th>مقنن الفرد</th><th>الفاقد</th><th>إجمالي المطلوب</th><th>المخزن</th></tr></thead>
        <tbody id="recipe-tbody"><tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem;">اختر الأصناف ثم اضغط توليد الريسيبي</td></tr></tbody>
      </table>
    </div>
  </div>

  <!-- INVENTORY TAB -->
  <div class="meal-content" id="meal-inventory">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.8rem;margin-bottom:1rem;">
      <div class="section-label" style="margin:0">📦 إدارة المخزن اليومي</div>
      <div style="display:flex;gap:.5rem;align-items:center;">
        <select id="inv-department" onchange="renderInventoryTable()" title="قسم المخزن">
          <option value="">كل الأقسام</option>
          <option value="food">التغذية</option>
          <option value="cleaning">النظافة</option>
          <option value="operation">التشغيل</option>
        </select>
        <input type="date" class="date-input" id="inv-date">
        <button class="inv-save-btn" onclick="saveInventory()">💾 حفظ المخزن</button>
        <button class="action-btn excel" onclick="exportInventoryExcel()">📥 Excel</button>
      </div>
    </div>

    <div class="inv-add-row">
      <input type="text" id="inv-new-name" placeholder="➕ اسم الصنف الجديد">
      <input type="text" id="inv-new-unit" placeholder="الوحدة (جم، كيلو...)">
      <button class="add-item-btn" onclick="addInventoryItem()">إضافة صنف</button>
    </div>
    <div class="data-integrity-note">🔒 حماية البيانات: يمكن إضافة أصناف جديدة، لكن لا يُسمح بمسح صنف سبق استخدامه في صرف أو فاتورة أو مرتجع؛ يُوقف فقط مع الاحتفاظ بسجله.</div>

    <div class="inv-add-row" style="background:rgba(22,131,91,.05);border-color:var(--green);">
      <input type="file" id="inventory-file" accept=".xlsx,.xls,.csv" onchange="importInventoryFile(this)">
      <button class="add-item-btn" onclick="document.getElementById('inventory-file').click()">📤 رفع رصيد أول المدة</button>
      <div style="font-size:.8rem;color:var(--muted);align-self:center;">الأعمدة المقبولة: الصنف، الكمية/رصيد أول المدة، الوحدة، السعر</div>
    </div>

    <div class="inv-search-row">
      <input type="search" id="inv-search" placeholder="🔎 بحث في المخزن بالاسم أو الوحدة" oninput="renderInventoryTable()">
      <span class="inv-search-count" id="inv-search-count"></span>
    </div>

    <div style="overflow-x:auto;">
      <table class="inv-table" id="inv-table">
        <thead>
          <tr>
            <th>الصنف</th>
            <th>الوحدة</th>
            <th>رصيد أول المدة</th>
            <th>➕ إضافات</th>
            <th>➖ المنصرف</th>
            <th>🔄 تحويلات</th>
            <th>رصيد آخر المدة</th>
          </tr>
        </thead>
        <tbody id="inv-tbody"></tbody>
        <tfoot>
          <tr class="inv-total-row">
            <td colspan="5" style="text-align:left;">📦 إجمالي الأصناف</td>
            <td class="inv-cost" id="inv-grand-total">-</td>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>

  <!-- PURCHASE REQUEST TAB -->
  <div class="meal-content" id="meal-purchase-request">
    <div class="info-box">
      <div class="info-box-title">📝 نموذج طلب شراء واعتمادات التشغيل</div>
      <div class="workflow-strip"><span class="workflow-step done">1 المشرف</span><span class="workflow-step">2 الشيف</span><span class="workflow-step">3 أمين المخزن</span><span class="workflow-step">4 المشتريات</span><span class="workflow-step">5 المدير</span></div>
      <div class="compact-form">
        <input type="date" id="pr-date">
        <input type="date" id="pr-needed-date" title="تاريخ الاحتياج">
        <select id="pr-department" onchange="changePurchaseDepartment()"><option value="food">قطاع التغذية</option><option value="cleaning">قطاع النظافة</option><option value="operation">أصناف التشغيل</option></select>
        <select id="pr-warehouse"><option value="المخزن الرئيسي">المخزن الرئيسي</option><option value="المخزن المبرد">المخزن المبرد</option><option value="المخزن المجمد">المخزن المجمد</option><option value="مخزن النظافة">مخزن النظافة</option></select>
        <select id="pr-supplier"><option value="">اختر المورد المسجل</option></select>
        <select id="pr-priority"><option value="عادي">أولوية عادية</option><option value="عاجل">عاجل</option><option value="طارئ">طارئ</option></select>
        <textarea id="pr-reason" placeholder="سبب الشراء وملاحظات التشغيل"></textarea>
      </div>
    </div>
    <div class="section-label">أصناف المخزن ورصيدها الحالي</div>
    <div class="inv-add-row">
      <select id="pr-inventory-item" onchange="selectPurchaseInventoryItem()"><option value="">اختر الصنف من المخزن</option></select>
      <input id="pr-current-stock" readonly placeholder="الرصيد الحالي">
      <input id="pr-item-unit" readonly placeholder="الوحدة">
      <input id="pr-item-qty" type="number" min="0.001" step="0.001" placeholder="الكمية المطلوب شراؤها">
      <input id="pr-item-est-price" type="number" min="0" step="0.01" placeholder="السعر التقديري">
      <input id="pr-item-note" placeholder="مواصفات / ملاحظة">
      <button class="add-item-btn" onclick="addPurchaseRequestItem()">إضافة</button>
    </div>
    <div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>#</th><th>القسم</th><th>الصنف</th><th>رصيد المخزن</th><th>المطلوب شراء</th><th>الوحدة</th><th>سعر تقديري</th><th>الإجمالي</th><th>المواصفات</th><th></th></tr></thead><tbody id="pr-items-tbody"><tr><td colspan="10" style="text-align:center;color:var(--muted);padding:1.5rem">اختر القسم ثم أضف الأصناف من المخزن</td></tr></tbody><tfoot><tr class="inv-total-row"><td colspan="7" style="text-align:left">إجمالي الطلب التقديري</td><td id="pr-estimated-total" colspan="3">0 جنيه</td></tr></tfoot></table></div>
    <button class="submit-btn" style="margin-top:1rem" onclick="submitPurchaseRequest()">✅ إرسال طلب الشراء للاعتماد</button>
    <div class="section-label" style="margin-top:1.4rem">طلبات الموقع السابقة</div>
    <div id="purchase-request-list"><div class="empty"><div class="icon">📝</div><div>لا توجد طلبات بعد</div></div></div>
  </div>

  <!-- INVOICE TAB -->
  <div class="meal-content" id="meal-invoice">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:.8rem;margin-bottom:1rem;">
      <div class="section-label" style="margin:0">🧾 فاتورة المشتريات</div>
      <div style="display:flex;gap:.5rem;align-items:center;">
        <input type="date" class="date-input" id="invoice-date">
        <button class="inv-save-btn" onclick="saveInvoice()">💾 حفظ الفاتورة</button>
        <button class="action-btn excel" onclick="exportInvoiceExcel()">📥 Excel</button>
      </div>
    </div>

    <div class="info-box">
      <div class="info-box-title">🏢 دليل الموردين — نقدي أو آجل</div>
      <div class="compact-form">
        <input type="hidden" id="supplier-master-id">
        <input id="supplier-master-name" placeholder="اسم المورد">
        <select id="supplier-master-type"><option value="cash">نقدي</option><option value="credit">آجل</option></select>
        <input id="supplier-master-phone" type="tel" placeholder="رقم التليفون">
        <input id="supplier-master-account" placeholder="رقم الحساب / بيانات الدفع">
        <input id="supplier-master-tax" placeholder="الرقم الضريبي (اختياري)">
        <input id="supplier-master-notes" placeholder="ملاحظات المورد وشروط التوريد">
      </div>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap"><button class="add-item-btn" onclick="saveSupplierMaster()">💾 حفظ المورد</button><select class="site-filter" id="invoice-supplier-master" onchange="applySupplierToInvoice()"><option value="">اختر موردًا مسجلًا</option></select></div>
      <div id="supplier-master-list" style="margin-top:.8rem"></div>
    </div>

    <div class="inv-add-row" style="background:rgba(22,131,91,.05);border-color:rgba(22,131,91,.35);">
      <input type="text" id="invoice-number" placeholder="رقم الفاتورة / المستند">
      <input type="text" id="invoice-supplier-name" placeholder="اسم المورد">
      <input type="tel" id="invoice-supplier-phone" placeholder="رقم تليفون المورد">
      <input type="text" id="invoice-supplier-account" placeholder="حساب المورد / رقم الحساب">
      <select id="invoice-payment-type" onchange="handlePaymentTypeChange()" style="flex:1;min-width:120px;padding:.5rem .7rem;background:var(--card2);border:1.5px solid var(--border);border-radius:8px;color:var(--text);font-family:'Cairo',sans-serif;font-size:.85rem;">
        <option value="cash">كاش</option>
        <option value="credit">آجل</option>
      </select>
      <input type="number" id="invoice-paid-amount" placeholder="المدفوع" min="0" step="0.01" onchange="renderInvoiceTable()">
    </div>

    <div class="inv-add-row">
      <input type="text" id="inv-item-name" placeholder="اسم الصنف">
      <input type="text" id="inv-item-unit" placeholder="الوحدة">
      <input type="number" id="inv-item-qty" placeholder="الكمية" min="0" step="0.01">
      <input type="number" id="inv-item-price" placeholder="السعر" min="0" step="0.01">
      <button class="add-item-btn" onclick="addInvoiceItem()">➕ إضافة صنف غير موجود</button>
    </div>

    <div style="overflow-x:auto;margin-top:.5rem;">
      <table class="inv-table">
        <thead>
          <tr>
            <th>#</th>
            <th>الوجبة</th>
            <th>الصنف</th>
            <th>الوحدة</th>
            <th>الكمية</th>
            <th>السعر</th>
            <th>الإجمالي</th>
            <th>حذف</th>
          </tr>
        </thead>
        <tbody id="invoice-tbody"></tbody>
        <tfoot>
          <tr class="inv-total-row">
            <td colspan="6" style="text-align:left;font-size:1rem;">💰 إجمالي الفاتورة</td>
            <td class="inv-cost" id="invoice-total" colspan="2">0 جنيه</td>
          </tr>
          <tr class="inv-total-row">
            <td colspan="6" style="text-align:left;font-size:1rem;">📌 المتبقي على المورد</td>
            <td class="inv-cost" id="invoice-remaining" colspan="2">0 جنيه</td>
          </tr>
        </tfoot>
      </table>
    </div>
    <div style="margin-top:1rem;padding:.8rem 1rem;background:rgba(22,131,91,.08);border-radius:10px;border:1px solid rgba(22,131,91,.2);font-size:.85rem;color:var(--muted);">
      💡 عند حفظ الفاتورة، الأصناف بتتضاف تلقائياً لرصيد المخزن كـ "إضافات"
    </div>
  </div>

  <!-- PURCHASE CASHBOX -->
  <div class="meal-content" id="meal-cashbox">
    <div class="stats-row">
      <div class="stat-card"><div class="stat-num" id="cashbox-opening">0</div><div class="stat-lbl">رصيد افتتاحي</div></div>
      <div class="stat-card"><div class="stat-num" id="cashbox-in" style="color:var(--green)">0</div><div class="stat-lbl">إجمالي الداخل والتحصيل</div></div>
      <div class="stat-card"><div class="stat-num" id="cashbox-out" style="color:var(--red)">0</div><div class="stat-lbl">إجمالي المدين / المصروف</div></div>
      <div class="stat-card"><div class="stat-num" id="cashbox-balance" style="color:var(--green)">0</div><div class="stat-lbl">الرصيد الحالي</div></div>
    </div>
    <div class="info-box" style="border-color:var(--green);">
      <div class="info-box-title" style="color:var(--green);">💰 إعداد خزنة الموقع</div>
      <div class="info-grid"><div class="info-field"><label>الرصيد الافتتاحي بالجنيه</label><input class="info-input" type="number" min="0" step="0.01" id="cashbox-opening-input" value="0"></div><div style="display:flex;align-items:end"><button class="inv-save-btn" style="width:100%;height:43px" onclick="saveCashboxOpening()">💾 حفظ الرصيد</button></div></div>
    </div>
    <div class="section-label" style="border-color:var(--green);color:var(--green);">تسجيل حركة خزنة</div>
    <div class="data-integrity-note">حركات الصرف وسداد الموردين تُرسل للإدارة أولًا، ولا تُخصم من الرصيد إلا بعد الاعتماد.</div>
    <div class="inv-add-row" style="border-color:var(--green);background:rgba(22,131,91,.05)">
      <select id="cashbox-type" class="site-filter" onchange="handleCashboxTypeChange()"><option value="deposit">دائن — إيداع</option><option value="receivable">دائن — تحصيل</option><option value="expense">مدين — صرف</option><option value="supplier_payment">مدين — سداد مورد</option></select>
      <input type="number" id="cashbox-amount" min="0.01" step="0.01" placeholder="المبلغ">
      <input type="text" id="cashbox-category" placeholder="التصنيف / البيان">
      <input type="text" id="cashbox-reference" readonly placeholder="رقم المستند يتولد تلقائيًا">
      <input type="text" id="cashbox-supplier" list="cashbox-supplier-options" placeholder="ابحث واختر المورد (اختياري)">
      <datalist id="cashbox-supplier-options"></datalist>
      <input type="text" id="cashbox-notes" placeholder="ملاحظات الحركة">
      <button class="add-item-btn" style="background:var(--green)" onclick="saveCashboxTransaction()">تسجيل / إرسال للاعتماد</button>
    </div>
    <div class="section-label" style="border-color:var(--green);color:var(--green);margin-top:1.2rem">البحث وكشف آخر اليوم</div>
    <div class="compact-form">
      <input type="date" id="cashbox-filter-from" title="من تاريخ" onchange="renderCashboxTransactions()">
      <input type="date" id="cashbox-filter-to" title="إلى تاريخ" onchange="renderCashboxTransactions()">
      <select id="cashbox-filter-type" onchange="renderCashboxTransactions()"><option value="">كل أنواع الحركة</option><option value="deposit">إيداع</option><option value="receivable">تحصيل</option><option value="expense">صرف</option><option value="supplier_payment">سداد مورد</option></select>
      <input type="text" id="cashbox-filter-supplier" list="cashbox-supplier-options" placeholder="بحث باسم المورد" oninput="renderCashboxTransactions()">
      <button class="action-btn" onclick="resetCashboxFilters()">مسح البحث</button>
      <button class="action-btn excel" onclick="exportCashboxExcel()">📥 Excel آخر اليوم</button>
    </div>
    <div id="cashbox-filter-summary" class="data-integrity-note" style="margin:.8rem 0"></div>
    <div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>التاريخ</th><th>النوع</th><th>رقم المستند</th><th>حالة الاعتماد</th><th>التصنيف</th><th>المورد</th><th>الملاحظات</th><th>المبلغ</th><th>اعتمدها</th></tr></thead><tbody id="cashbox-tbody"><tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--muted)">لا توجد حركات</td></tr></tbody></table></div>
  </div>

  <!-- RETURNS & WASTE -->
  <div class="meal-content" id="meal-returns">
    <div class="info-box">
      <div class="info-box-title">↩️ مرتجعات آخر اليوم والهدر</div>
      <div style="font-size:.82rem;color:var(--muted)">المرتجع يذهب إلى مخزن مرتجعات منفصل، والهدر يُسجل مع السبب والتكلفة للمراجعة.</div>
    </div>
    <div class="compact-form">
      <input type="date" id="rw-date">
      <select id="rw-type"><option value="return">مرتجع صالح</option><option value="waste">هدر / تالف</option></select>
      <select id="rw-meal"><option value="فطار">فطار</option><option value="غداء">غداء</option><option value="عشاء">عشاء</option><option value="مشتريات">مشتريات</option><option value="نظافة">نظافة</option></select>
      <select id="rw-item-name" onchange="selectReturnItem()"><option value="">اختر الصنف من المخزن</option></select>
      <input id="rw-unit" placeholder="الوحدة" readonly title="تُملأ تلقائيًا من المخزن">
      <input id="rw-qty" type="number" min="0.001" step="0.001" placeholder="الكمية">
      <input id="rw-reason" placeholder="سبب المرتجع أو الهدر">
      <select id="rw-condition"><option value="صالح لإعادة الصرف">صالح لإعادة الصرف</option><option value="يحتاج فحص جودة">يحتاج فحص جودة</option><option value="غير صالح">غير صالح</option></select>
    </div>
    <button class="submit-btn" onclick="saveReturnWaste()">💾 تسجيل الحركة</button>

    <div class="section-label" style="margin-top:1.4rem">🔎 بحث وفلترة</div>
    <div class="compact-form">
      <input id="rw-search" placeholder="بحث بالصنف" oninput="renderReturnWaste()">
      <input type="date" id="rw-from" onchange="renderReturnWaste()" title="من تاريخ">
      <input type="date" id="rw-to" onchange="renderReturnWaste()" title="إلى تاريخ">
      <select id="rw-filter-type" onchange="renderReturnWaste()">
        <option value="">كل الحركات</option>
        <option value="return">مرتجع</option>
        <option value="waste">هدر</option>
        <option value="transfer">تحويل من المرتجعات</option>
      </select>
      <button class="action-btn" onclick="clearReturnFilters()">مسح الفلتر</button>
      <button class="action-btn excel" onclick="exportReturnsExcel()">📥 Excel</button>
    </div>
    <div id="rw-filter-summary" class="data-integrity-note" style="margin:.8rem 0"></div>

    <div class="master-tables" style="margin-top:1.2rem">
      <div><h3>مخزن المرتجعات — حركة اليوم</h3><div id="return-stock-list"></div></div>
      <div><h3>سجل المرتجعات والهدر</h3><div id="return-waste-list"></div></div>
    </div>
  </div>

  <!-- CLEANING -->
  <div class="meal-content" id="meal-cleaning">
    <div class="info-box" style="border-color:var(--blue);">
      <div class="info-box-title" style="color:var(--blue);">🧹 صرف مخزن النظافة</div>
      <div style="font-size:.85rem;color:var(--muted);line-height:1.7;">حدد الكميات المطلوبة من أصناف النظافة ثم اضغط تسجيل — تروح للشيف للاعتماد.</div>
    </div>
    <div class="section-label" style="color:var(--blue);border-color:var(--blue);">أصناف النظافة المتاحة</div>
    <div class="items-grid" id="grid-cleaning"></div>
    <div class="add-item-box" style="border-color:var(--blue);">
      <div class="add-title" style="color:var(--blue);">➕ إضافة صنف نظافة</div>
      <div class="add-item-row">
        <input type="text" id="new-item-name-cleaning" placeholder="اسم الصنف">
        <input type="text" id="new-item-unit-cleaning" placeholder="الوحدة (قطعة، كيلو...)">
        <button class="add-item-btn" onclick="addCustomItem('cleaning')">إضافة</button>
      </div>
    </div>
    <button class="submit-btn" id="btn-cleaning" onclick="submitCleaning()" style="background:var(--green);">✅ تسجيل طلب صرف النظافة</button>
  </div>

  <!-- HR OVERVIEW -->
  <div class="meal-content" id="meal-hr-overview">
    <div class="info-box" style="border-color:rgba(120,72,190,.35);">
      <div class="info-box-title" style="color:#6b3fa0;">👔 متابعة HR — الحضور والجودة والصيانة</div>
      <div style="font-size:.85rem;color:var(--muted);line-height:1.7;">شاشة عرض فقط تجمع حضور وانصراف العاملين، وفحوصات الجودة، وحالة المعدات والصيانة في مكان واحد. التسجيل والتعديل يتم من تبويبات كل قسم.</div>
    </div>
    <div class="compact-form">
      <input type="date" id="hro-date" onchange="renderHrOverview()">
      <button class="action-btn" onclick="renderHrOverview()">🔄 تحديث</button>
      <button class="action-btn excel" onclick="exportHrOverviewExcel()">📥 Excel</button>
    </div>
    <div id="hro-reset-note" class="data-integrity-note" style="display:none;margin:.6rem 0">🛡️ كل رسيت ينزّل نسخة احتياطية JSON بالسجلات المحذوفة قبل تنفيذ المسح.</div>
    <div id="hro-reset-row" class="compact-form" style="display:none">
      <button class="action-btn danger" onclick="resetSection('attendance')">🗑️ رسيت الحضور</button>
      <button class="action-btn danger" onclick="resetSection('quality_checks')">🗑️ رسيت الجودة</button>
      <button class="action-btn danger" onclick="resetSection('maintenance_logs')">🗑️ رسيت الصيانة</button>
      <button class="action-btn danger" onclick="resetSection('employees')">🗑️ رسيت العاملين</button>
      <button class="action-btn danger" onclick="resetSection('equipment')">🗑️ رسيت المعدات</button>
    </div>

    <div class="section-label" style="color:#6b3fa0;border-color:#6b3fa0;">⏱️ الحضور والانصراف</div>
    <div class="stats-row" style="grid-template-columns:repeat(4,1fr);padding:0;margin-bottom:.8rem">
      <div class="stat-card"><div class="stat-num" id="hro-total">0</div><div class="stat-lbl">إجمالي العاملين</div></div>
      <div class="stat-card"><div class="stat-num" id="hro-present" style="color:var(--green)">0</div><div class="stat-lbl">موجودون الآن</div></div>
      <div class="stat-card"><div class="stat-num" id="hro-left">0</div><div class="stat-lbl">سجّلوا انصراف</div></div>
      <div class="stat-card"><div class="stat-num" id="hro-absent" style="color:var(--red)">0</div><div class="stat-lbl">لم يسجلوا حضور</div></div>
    </div>
    <div id="hro-attendance"></div>

    <div class="section-label" style="color:var(--green);border-color:var(--green);margin-top:1.2rem">📸 فحوصات الجودة</div>
    <div id="hro-quality"></div>

    <div class="section-label" style="color:#a55e00;border-color:#a55e00;margin-top:1.2rem">🛠️ المعدات والصيانة</div>
    <div class="stats-row" style="grid-template-columns:repeat(3,1fr);padding:0;margin-bottom:.8rem">
      <div class="stat-card"><div class="stat-num" id="hro-equipment-total">0</div><div class="stat-lbl">إجمالي المعدات</div></div>
      <div class="stat-card"><div class="stat-num" id="hro-equipment-need" style="color:var(--red)">0</div><div class="stat-lbl">تحتاج صيانة</div></div>
      <div class="stat-card"><div class="stat-num" id="hro-maintenance-cost">0</div><div class="stat-lbl">مصروف الصيانة (ج)</div></div>
    </div>
    <div id="hro-equipment"></div>
  </div>

  <!-- OPERATION SUPPLIES -->
  <div class="meal-content" id="meal-operation">
    <div class="info-box" style="border-color:rgba(196,138,26,.35);">
      <div class="info-box-title" style="color:#a9741a;">⚙️ صرف أصناف التشغيل</div>
      <div style="font-size:.85rem;color:var(--muted);line-height:1.7;">الأصناف التي يُشتغل بها ولا تظهر في المنيو — الفحم والزيت والسمن والبهارات والغاز ومستلزمات التعبئة. حدد الكميات ثم اضغط تسجيل، ويظهر رصيدها في تبويب المخزن تحت قسم «التشغيل».</div>
    </div>
    <div class="section-label" style="color:#a9741a;border-color:#a9741a;">أصناف التشغيل المتاحة</div>
    <div class="items-grid" id="grid-operation"></div>
    <div class="add-item-box" style="border-color:rgba(196,138,26,.5);">
      <div class="add-title" style="color:#a9741a;">➕ إضافة صنف تشغيل</div>
      <div class="add-item-row">
        <input type="text" id="new-item-name-operation" placeholder="اسم الصنف">
        <input type="text" id="new-item-unit-operation" placeholder="الوحدة (كيلو، لتر...)">
        <button class="add-item-btn" onclick="addCustomItem('operation')">إضافة</button>
      </div>
    </div>
    <button class="submit-btn" id="btn-operation" onclick="submitOperation()" style="background:#a9741a;">✅ تسجيل صرف التشغيل</button>
  </div>

  <!-- BREAKFAST -->
  <div class="meal-content active" id="meal-breakfast">
    <div class="info-box">
      <div class="info-box-title">📊 بيانات الوجبة</div>
      <div class="info-grid">
        <div class="info-field"><label>👨‍🎓 عدد الطلاب</label><input class="info-input" type="number" min="0" placeholder="0" id="students-breakfast" oninput="updateMealHeadcount('breakfast')"></div>
        <div class="info-field"><label>👔 عدد الإداريين</label><input class="info-input" type="number" min="0" placeholder="0" id="admins-breakfast" oninput="updateMealHeadcount('breakfast')"></div>
        <div class="info-field"><label>🍽️ عدد الوجبات الكلي (تلقائي)</label><input class="info-input auto-total" type="number" min="0" placeholder="0" id="meals-count-breakfast" readonly></div>
        <div class="info-field"><label>👥 أفراد طاقم التشغيل</label><input class="info-input" type="number" min="0" placeholder="0" id="staff-count-breakfast"></div>
      </div>
    </div>
    <div class="inline-recipe-panel">
      <div class="inline-recipe-head"><div><strong>🧮 ريسيبي الفطار</strong><span>يُحسب فورًا من إجمالي الطلاب والإداريين</span></div><span>الأوزان بالكيلو · العبوات والقطع بالعدد</span></div>
      <div id="inline-recipe-controls-breakfast"></div>
      <div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>الصنف</th><th>وجبات الصنف</th><th>مقنن الفرد</th><th>الإجمالي</th><th>الوحدة</th></tr></thead><tbody id="inline-recipe-tbody-breakfast"></tbody></table></div>
    </div>
    <div class="section-label">الكميات المصروفة - الفطار</div>
    <div class="items-grid" id="grid-breakfast"></div>
    <div class="add-item-box">
      <div class="add-title">➕ إضافة صنف جديد</div>
      <div class="add-item-row">
        <input type="text" id="new-item-name-breakfast" placeholder="اسم الصنف">
        <input type="text" id="new-item-unit-breakfast" placeholder="الوحدة (جم، قطعة...)">
        <button class="add-item-btn" onclick="addCustomItem('breakfast')">إضافة</button>
      </div>
    </div>
    <button class="submit-btn" id="btn-breakfast" onclick="submitMeal('breakfast','فطار')">✅ تسجيل صرف الفطار</button>
  </div>

  <!-- LUNCH -->
  <div class="meal-content" id="meal-lunch">
    <div class="info-box">
      <div class="info-box-title">📊 بيانات الوجبة</div>
      <div class="info-grid">
        <div class="info-field"><label>👨‍🎓 عدد الطلاب</label><input class="info-input" type="number" min="0" placeholder="0" id="students-lunch" oninput="updateMealHeadcount('lunch')"></div>
        <div class="info-field"><label>👔 عدد الإداريين</label><input class="info-input" type="number" min="0" placeholder="0" id="admins-lunch" oninput="updateMealHeadcount('lunch')"></div>
        <div class="info-field"><label>🍽️ عدد الوجبات الكلي (تلقائي)</label><input class="info-input auto-total" type="number" min="0" placeholder="0" id="meals-count-lunch" readonly></div>
        <div class="info-field"><label>👥 أفراد طاقم التشغيل</label><input class="info-input" type="number" min="0" placeholder="0" id="staff-count-lunch"></div>
      </div>
    </div>
    <div class="inline-recipe-panel">
      <div class="inline-recipe-head"><div><strong>🧮 ريسيبي الغداء</strong><span>وزّع البروتين والنشويات والعصير/الحلو/الفاكهة حسب المنيو</span></div><span>ربع فرخة لكل وجبة · البانيه 150 جم</span></div>
      <div id="inline-recipe-controls-lunch"></div>
      <div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>الصنف</th><th>وجبات الصنف</th><th>مقنن الفرد</th><th>الإجمالي</th><th>الوحدة</th></tr></thead><tbody id="inline-recipe-tbody-lunch"></tbody></table></div>
    </div>
    <div class="section-label">الكميات المصروفة - الغداء</div>
    <div class="items-grid" id="grid-lunch"></div>
    <div class="add-item-box">
      <div class="add-title">➕ إضافة صنف جديد</div>
      <div class="add-item-row">
        <input type="text" id="new-item-name-lunch" placeholder="اسم الصنف">
        <input type="text" id="new-item-unit-lunch" placeholder="الوحدة (جم، قطعة...)">
        <button class="add-item-btn" onclick="addCustomItem('lunch')">إضافة</button>
      </div>
    </div>
    <button class="submit-btn" id="btn-lunch" onclick="submitMeal('lunch','غداء')">✅ تسجيل صرف الغداء</button>
  </div>

  <!-- DINNER -->
  <div class="meal-content" id="meal-dinner">
    <div class="info-box">
      <div class="info-box-title">📊 بيانات الوجبة</div>
      <div class="info-grid">
        <div class="info-field"><label>👨‍🎓 عدد الطلاب</label><input class="info-input" type="number" min="0" placeholder="0" id="students-dinner" oninput="updateMealHeadcount('dinner')"></div>
        <div class="info-field"><label>👔 عدد الإداريين</label><input class="info-input" type="number" min="0" placeholder="0" id="admins-dinner" oninput="updateMealHeadcount('dinner')"></div>
        <div class="info-field"><label>🍽️ عدد الوجبات الكلي (تلقائي)</label><input class="info-input auto-total" type="number" min="0" placeholder="0" id="meals-count-dinner" readonly></div>
        <div class="info-field"><label>👥 أفراد طاقم التشغيل</label><input class="info-input" type="number" min="0" placeholder="0" id="staff-count-dinner"></div>
      </div>
    </div>
    <div class="inline-recipe-panel">
      <div class="inline-recipe-head"><div><strong>🧮 ريسيبي العشاء</strong><span>يمكن توزيع عدد الوجبات على أكثر من بديل</span></div><span>الأوزان بالكيلو · العبوات والقطع بالعدد</span></div>
      <div id="inline-recipe-controls-dinner"></div>
      <div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>الصنف</th><th>وجبات الصنف</th><th>مقنن الفرد</th><th>الإجمالي</th><th>الوحدة</th></tr></thead><tbody id="inline-recipe-tbody-dinner"></tbody></table></div>
    </div>
    <div class="section-label">الكميات المصروفة - العشاء</div>
    <div class="items-grid" id="grid-dinner"></div>
    <div class="add-item-box">
      <div class="add-title">➕ إضافة صنف جديد</div>
      <div class="add-item-row">
        <input type="text" id="new-item-name-dinner" placeholder="اسم الصنف">
        <input type="text" id="new-item-unit-dinner" placeholder="الوحدة (جم، قطعة...)">
        <button class="add-item-btn" onclick="addCustomItem('dinner')">إضافة</button>
      </div>
    </div>
    <button class="submit-btn" id="btn-dinner" onclick="submitMeal('dinner','عشاء')">✅ تسجيل صرف العشاء</button>
  </div>
</div>

<!-- ===== MANAGER ===== -->
<div class="page" id="page-manager">
  <div class="dash-header">
    <div class="page-title" style="margin:0">📊 داشبورد المدير</div>
    <div style="display:flex;gap:.5rem;flex-wrap:wrap;align-items:center">
      <input type="date" class="date-input" id="filter-date" onchange="renderDashboard()">
      <select class="site-filter" id="filter-site" onchange="renderDashboard()">
        <option value="">كل المواقع</option>
        <option value="الأقصر">الأقصر</option>
        <option value="قنا">قنا</option>
        <option value="أسيوط">أسيوط</option>
        <option value="المنيا">المنيا</option>
      </select>
      <button class="action-btn excel" onclick="exportExcel()">📥 Excel</button>
      <button class="action-btn" onclick="document.getElementById('supplier-accounts-section').scrollIntoView({behavior:'smooth'})">📒 حسابات الموردين</button>
      <button class="action-btn" onclick="document.getElementById('manager-equipment-section').scrollIntoView({behavior:'smooth'})">🛠️ المعدات</button>
      <button class="action-btn" onclick="openUserManagement()">👥 المستخدمون</button>
      <button class="logout-btn" onclick="doLogout()">خروج</button>
      <button class="action-btn danger" onclick="clearToday()">🗑️ مسح</button>
    </div>
  </div>

  <div style="background:rgba(22,131,91,.1);border:1.5px solid var(--green);border-radius:10px;padding:.75rem 1rem;margin-bottom:1rem;color:var(--green);font-weight:900;text-align:center;">
    ✅ نسخة الصلاحيات مفعلة - المدير يتحكم في صلاحيات المشرفين
  </div>

  <div class="section-card" id="user-management-section">
    <div class="section-card-title">
      <span>👥 المستخدمون والصلاحيات</span>
      <button class="action-btn" onclick="renderUsersPanel()">تحديث القائمة</button>
    </div>
    <div class="data-integrity-note">ينشئ المدير حسابًا مستقلًا لكل موظف. لا تظهر كلمة المرور بعد الحفظ ولا تُخزن داخل واجهة النظام.</div>
    <div class="master-form-card" style="margin-top:1rem">
      <h3>➕ إضافة مستخدم جديد</h3>
      <div class="master-form-grid">
        <input id="manager-user-username" autocomplete="off" placeholder="اسم المستخدم بالإنجليزية">
        <input id="manager-user-name" placeholder="اسم الموظف">
        <input id="manager-user-email" type="email" placeholder="البريد الإلكتروني (اختياري)">
        <input id="manager-user-password" type="password" minlength="10" autocomplete="new-password" placeholder="كلمة مرور قوية — 10 أحرف على الأقل">
        <select id="manager-user-role" onchange="applyUserRoleDefaults()">
          <option value="ibf_supervisor">مشرف موقع التغذية</option>
          <option value="ibf_chef">شيف قطاع التغذية</option>
          <option value="ibf_storekeeper">أمين مخزن التغذية</option>
          <option value="ibf_purchasing">مسؤول مشتريات التغذية</option>
          <option value="ibf_cashier">مسؤول خزنة التغذية</option>
          <option value="ibf_quality">مسؤول جودة التغذية</option>
          <option value="ibf_hr">مسؤول الموارد البشرية</option>
          <option value="ibf_maintenance">مسؤول المعدات والصيانة</option>
          <option value="ibf_reviewer">مراجع المدارس — تكلفة وسجل مراجعة وصلاحيات</option>
          <option value="ibf_viewer">مشاهدة تقارير التغذية</option>
          <option value="ibf_executive">موظف الإدارة العليا — تحويل المخزون</option>
          <option value="ibf_manager">مدير نظام التغذية</option>
        </select>
        <input id="manager-user-site" list="manager-user-sites" placeholder="الموقع — مثال: القاهرة">
        <datalist id="manager-user-sites"></datalist>
      </div>
      <div id="manager-user-sites-box" style="display:none;margin:.6rem 0">
        <div class="section-label">المدارس التي يراجعها هذا الحساب</div>
        <div class="data-integrity-note">اختر مدرسة أو أكثر. المراجع لن يرى أي بيانات خارج ما تختاره هنا.</div>
        <div id="manager-user-sites-list" style="display:flex;gap:.8rem;flex-wrap:wrap;margin-top:.6rem;font-size:.85rem;color:var(--muted)"></div>
      </div>
      <div style="display:flex;gap:1rem;flex-wrap:wrap;margin:.9rem 0;color:var(--muted);font-size:.85rem">
        <label><input type="checkbox" id="manager-user-meals" checked> تسجيل الوجبات</label>
        <label><input type="checkbox" id="manager-user-inventory"> تعديل المخزن</label>
        <label><input type="checkbox" id="manager-user-invoices"> الفواتير وطلبات الشراء</label>
        <label><input type="checkbox" id="manager-user-quality"> الجودة والصور</label>
        <label><input type="checkbox" id="manager-user-hr"> HR والحضور</label>
        <label><input type="checkbox" id="manager-user-equipment"> المعدات والصيانة</label>
      </div>
      <button class="add-item-btn" id="manager-user-create-btn" onclick="createManagerUser()">إنشاء المستخدم</button>
    </div>
    <div class="section-label" style="margin-top:1.2rem">المستخدمون الحاليون</div>
    <div id="manager-users-container"><div class="empty"><div class="icon">👥</div><div>جاري تحميل المستخدمين...</div></div></div>
  </div>

  <div class="backup-bar">
    <strong style="align-self:center;margin-left:auto">🛡️ تصدير نسخة من بيانات الخادم</strong>
    <button class="action-btn" onclick="exportBackup()">⬇️ تنزيل نسخة احتياطية</button>
    <input type="file" id="backup-file" accept=".json" onchange="importBackup(this)">
    <button class="action-btn" onclick="document.getElementById('backup-file').click()">⬆️ استعادة نسخة</button>
  </div>

  <div class="section-card">
    <div class="section-card-title"><span>🏢 الهيكل التشغيلي وتعريف المخازن</span><span style="display:flex;gap:.4rem"><button class="action-btn" onclick="renderMasterData()">تحديث</button><button class="action-btn danger" onclick="resetSection('suppliers')">🗑️ رسيت</button></span></div>
    <div class="stats-row" style="grid-template-columns:repeat(3,1fr);padding:0;margin-bottom:1rem">
      <div class="stat-card"><div class="stat-num" id="master-sites-count">0</div><div class="stat-lbl">مواقع تشغيلية</div></div>
      <div class="stat-card"><div class="stat-num" id="master-warehouses-count">0</div><div class="stat-lbl">مخازن معرفة</div></div>
      <div class="stat-card"><div class="stat-num" id="master-cold-count">0</div><div class="stat-lbl">مبرد ومجمد</div></div>
    </div>
    <div class="master-forms">
      <div class="master-form-card">
        <h3>➕ إضافة موقع</h3>
        <div class="master-form-grid"><input id="master-site-code" placeholder="كود الموقع"><input id="master-site-name" placeholder="اسم الموقع"><input id="master-site-city" placeholder="المحافظة / المدينة"><input id="master-site-client" placeholder="العميل / العقد"></div>
        <div style="display:flex;gap:1rem;flex-wrap:wrap;margin:.5rem 0;font-size:.85rem">
          <label style="display:flex;align-items:center;gap:.4rem"><input type="checkbox" id="master-site-main" onchange="handleSiteTypeChoice('main')"> مخزن الشركة الرئيسي</label>
          <label style="display:flex;align-items:center;gap:.4rem"><input type="checkbox" id="master-site-supply" onchange="handleSiteTypeChoice('supply')"> موقع توريدات فقط</label>
        </div>
        <button class="add-item-btn" onclick="addMasterSite()">حفظ الموقع</button>
      </div>
      <div class="master-form-card">
        <h3>➕ تعريف مخزن</h3>
        <div class="master-form-grid"><input id="master-wh-code" placeholder="كود المخزن"><input id="master-wh-name" placeholder="اسم المخزن"><select id="master-wh-site"></select><select id="master-wh-type"><option>جاف</option><option>مبرد</option><option>مجمد</option><option>كيماويات</option><option>تعبئة وتغليف</option></select><input id="master-wh-manager" placeholder="أمين المخزن"><input id="master-wh-min" type="number" step="0.1" placeholder="أقل حرارة"><input id="master-wh-max" type="number" step="0.1" placeholder="أعلى حرارة"></div>
        <button class="add-item-btn" onclick="addMasterWarehouse()">حفظ المخزن</button>
      </div>
    </div>
    <div class="master-tables">
      <div><h3>المواقع</h3><div id="master-sites-table"></div></div>
      <div><h3>المخازن</h3><div id="master-warehouses-table"></div></div>
    </div>
  </div>

  <!-- ===== PROFITABILITY ===== -->
  <div class="section-card" id="profit-section">
    <div class="section-card-title"><span>💵 الربحية — البيع والتكلفة لكل مدرسة</span><button class="action-btn" onclick="renderProfitability()">تحديث</button></div>

    <div class="master-form-card" id="manager-inventory-upload-card" style="margin-bottom:1rem">
      <h3>💲 أسعار البيع للعميل</h3>
      <div class="master-form-grid">
        <select id="price-site" onchange="loadSitePrices()"></select>
        <input id="price-breakfast" type="number" min="0" step="0.01" placeholder="سعر الفطار">
        <input id="price-lunch" type="number" min="0" step="0.01" placeholder="سعر الغداء">
        <input id="price-dinner" type="number" min="0" step="0.01" placeholder="سعر العشاء">
      </div>
      <button class="add-item-btn" onclick="saveSitePrices()">حفظ أسعار المدرسة</button>
      <div id="price-list" style="margin-top:.8rem"></div>
    </div>

    <div class="stats-row" style="grid-template-columns:repeat(4,1fr);padding:0;margin-bottom:1rem">
      <div class="stat-card"><div class="stat-num" id="pf-revenue">0</div><div class="stat-lbl">إيراد اليوم (ج)</div></div>
      <div class="stat-card"><div class="stat-num" id="pf-cost">0</div><div class="stat-lbl">تكلفة اليوم (ج)</div></div>
      <div class="stat-card"><div class="stat-num" id="pf-profit">0</div><div class="stat-lbl">الربح (ج)</div></div>
      <div class="stat-card"><div class="stat-num" id="pf-margin">0%</div><div class="stat-lbl">هامش الربح</div></div>
    </div>
    <div class="stats-row" style="grid-template-columns:repeat(3,1fr);padding:0;margin-bottom:1rem">
      <div class="stat-card"><div class="stat-num" id="ga-supply-revenue">0</div><div class="stat-lbl">مبيعات التوريدات (ج)</div></div>
      <div class="stat-card"><div class="stat-num" id="ga-supply-cost">0</div><div class="stat-lbl">تكلفة التوريدات (ج)</div></div>
      <div class="stat-card"><div class="stat-num" id="ga-supply-profit">0</div><div class="stat-lbl">ربح التوريدات (ج)</div></div>
    </div>
    <div class="stats-row" style="grid-template-columns:repeat(4,1fr);padding:0;margin-bottom:1rem">
      <div class="stat-card"><div class="stat-num" id="pf-sold">0</div><div class="stat-lbl">وجبات مباعة (مسعّرة)</div></div>
      <div class="stat-card"><div class="stat-num" id="pf-unpriced">0</div><div class="stat-lbl">وجبات بلا سعر</div></div>
      <div class="stat-card"><div class="stat-num" id="pf-stock-value">0</div><div class="stat-lbl">قيمة مخازن المدارس (ج)</div></div>
      <div class="stat-card"><div class="stat-num" id="pf-stock-items">0</div><div class="stat-lbl">إجمالي أصناف المدارس</div></div>
    </div>
    <div id="pf-alert" class="data-integrity-note" style="margin-bottom:1rem"></div>

    <div class="stats-row" style="grid-template-columns:repeat(2,1fr);padding:0;margin-bottom:1rem">
      <div class="stat-card" style="border-color:var(--green)"><div class="stat-num" id="pf-best" style="font-size:1rem">—</div><div class="stat-lbl">🏆 أفضل مدرسة ربحية</div></div>
      <div class="stat-card" style="border-color:var(--red)"><div class="stat-num" id="pf-worst" style="font-size:1rem">—</div><div class="stat-lbl">⚠️ أقل مدرسة ربحية</div></div>
    </div>

    <div class="master-tables" style="margin-bottom:1rem">
      <div><h3>📊 ربحية المدارس</h3><div class="chart-wrap" id="pf-chart-schools"></div></div>
      <div><h3>📈 الإيراد مقابل التكلفة</h3><div class="chart-wrap" id="pf-chart-mix"></div></div>
    </div>

    <div style="display:flex;gap:.5rem;flex-wrap:wrap;margin-bottom:.8rem">
      <button class="action-btn excel" onclick="exportProfitExcel()">📥 Excel</button>
    </div>
    <div id="pf-table"></div>
  </div>

  <!-- ===== GENERAL ADMINISTRATION ===== -->
  <div class="section-card" id="general-admin-section">
    <div class="section-card-title"><span>🏢 الإدارة العامة — أرصدة المخازن والتحويلات</span><span style="display:flex;gap:.4rem"><button class="action-btn" onclick="renderGeneralAdmin()">تحديث</button><button class="action-btn danger" onclick="resetSection('stock_transfers')">🗑️ رسيت</button></span></div>
    <div class="stats-row" style="grid-template-columns:repeat(4,1fr);padding:0;margin-bottom:1rem">
      <div class="stat-card"><div class="stat-num" id="ga-stores-count">0</div><div class="stat-lbl">مخازن نشطة</div></div>
      <div class="stat-card"><div class="stat-num" id="ga-items-count">0</div><div class="stat-lbl">إجمالي الأصناف</div></div>
      <div class="stat-card"><div class="stat-num" id="ga-total-value">0</div><div class="stat-lbl">قيمة المخزون (ج)</div></div>
      <div class="stat-card"><div class="stat-num" id="ga-transfers-count">0</div><div class="stat-lbl">تحويلات مسجلة</div></div>
    </div>

    <div class="master-form-card" style="margin-bottom:1rem">
      <h3>📤 رفع جرد Excel إلى مخزن موقع</h3>
      <div class="master-form-grid">
        <select id="ga-inventory-upload-site"><option value="">اختر الموقع</option></select>
        <input type="date" id="ga-inventory-upload-date">
        <input type="file" id="ga-inventory-upload-file" accept=".xlsx,.xls,.csv" onchange="importManagerSiteInventory(this)">
      </div>
      <div class="data-integrity-note" style="margin:.6rem 0">اختر الموقع والتاريخ ثم ارفع الملف. الأصناف تُحفظ في مخزن الموقع المختار، وكل مشرف يرى مخزون موقعه فقط.</div>
      <button class="add-item-btn" onclick="document.getElementById('ga-inventory-upload-file').click()">اختيار ورفع ملف Excel</button>
    </div>

    <div class="master-form-card" style="margin-bottom:1rem">
      <h3>🔄 تحويل مخزون بين المخازن والمواقع</h3>
      <div class="master-form-grid">
        <select id="ga-from-site" onchange="renderTransferItemOptions()"></select>
        <select id="ga-from-dept" onchange="renderTransferItemOptions()"><option value="food">التغذية</option><option value="cleaning">النظافة</option><option value="operation">التشغيل</option></select>
        <select id="ga-item" onchange="selectTransferItem()"><option value="">اختر الصنف</option></select>
        <input id="ga-available" placeholder="الرصيد المتاح" readonly>
        <input id="ga-qty" type="number" min="0.001" step="0.001" placeholder="الكمية المحوَّلة" oninput="updateTransferProfitPreview()">
        <select id="ga-to-site" onchange="handleTransferDestination()"></select>
        <select id="ga-to-dept"><option value="food">التغذية</option><option value="cleaning">النظافة</option><option value="operation">التشغيل</option></select>
        <input id="ga-purchase-price" type="number" min="0" step="0.01" placeholder="سعر الشراء / التكلفة" oninput="applyTransferMarkup()">
        <input id="ga-profit-percent" type="number" min="0" step="0.01" placeholder="نسبة الربح %" oninput="applyTransferMarkup()">
        <input id="ga-sale-price" type="number" min="0" step="0.01" placeholder="سعر البيع تلقائي" readonly>
        <input id="ga-notes" placeholder="ملاحظات التحويل">
      </div>
      <div id="ga-profit-preview" class="data-integrity-note" style="margin:.6rem 0">اختر موقع توريدات لحساب الربح.</div>
      <div class="data-integrity-note" style="margin:.6rem 0">⚠️ التحويل فوري: يُخصم من المصدر ويُضاف للوجهة في نفس اللحظة بدون تأكيد استلام. راجع الكمية قبل الحفظ.</div>
      <button class="add-item-btn" onclick="saveStockTransfer()">تنفيذ التحويل</button>
    </div>

    <div class="compact-form">
      <select id="ga-filter-site" onchange="renderGeneralAdmin()"><option value="">كل المواقع</option></select>
      <select id="ga-filter-dept" onchange="renderGeneralAdmin()"><option value="">كل الأقسام</option><option value="food">التغذية</option><option value="cleaning">النظافة</option><option value="operation">التشغيل</option></select>
      <button class="action-btn excel" onclick="exportGeneralAdminExcel()">📥 Excel</button>
    </div>

    <div class="master-tables" style="margin-top:1rem">
      <div><h3>أرصدة كل المخازن</h3><div id="ga-stores-table"></div></div>
      <div><h3>سجل التحويلات</h3><div id="ga-transfers-list"></div></div>
    </div>
  </div>

  <div class="qr-section">
    <div><div id="qrcode"></div></div>
    <div class="qr-info">
      <h3>🔗 رابط الأشراف</h3>
      <p>شارك الرابط أو امسح الـ QR مع الأشراف. كل مشرف يدخل بباسورده الخاص.</p>
      <p style="color:var(--green);font-size:.8rem;margin-top:.4rem" id="conn-status">⏳ جاري الاتصال...</p>
    </div>
  </div>

  <div class="stats-row">
    <div class="stat-card"><div class="stat-num" id="stat-students">0</div><div class="stat-lbl">إجمالي الطلاب</div></div>
    <div class="stat-card"><div class="stat-num" id="stat-admins">0</div><div class="stat-lbl">إجمالي الإداريين</div></div>
    <div class="stat-card"><div class="stat-num" id="stat-meals">0</div><div class="stat-lbl">وجبات مسجلة</div></div>
    <div class="stat-card"><div class="stat-num" id="stat-sites">0</div><div class="stat-lbl">مواقع مسجلة</div></div>
  </div>

  <div class="section-card">
    <div class="section-card-title"><span>💰 التكلفة اليومية بمتوسط السعر</span><span style="display:flex;gap:.4rem"><button class="action-btn" onclick="renderDailyCost()">تحديث</button><button class="action-btn danger" onclick="resetSection('inventory')">🗑️ رسيت</button></span></div>
    <div id="daily-cost-container"><div class="empty"><div class="icon">💰</div><div>جاري حساب التكلفة اليومية...</div></div></div>
  </div>

  <div class="section-card">
    <div class="section-card-title"><span>📦 اعتماد الصرف من المخزن</span><span style="display:flex;gap:.4rem"><button class="action-btn" onclick="renderWarehouseApprovals()">تحديث</button><button class="action-btn danger" onclick="resetSection('meal_logs')">🗑️ رسيت</button></span></div>
    <div class="data-integrity-note">لا يُخصم أي صرف من رصيد المخزن إلا بعد موافقة الشيف ثم اعتماد أمين المخزن.</div>
    <div id="warehouse-approval-container"><div class="empty"><div class="icon">📦</div><div>لا توجد طلبات بانتظار المخزن</div></div></div>
  </div>

  <div class="section-card">
    <div class="section-card-title"><span>📝 طلبات الشراء والتوقيعات</span><span style="display:flex;gap:.4rem"><button class="action-btn" onclick="renderManagerPurchaseRequests()">تحديث</button><button class="action-btn danger" onclick="resetSection('purchase_requests')">🗑️ رسيت</button></span></div>
    <div id="manager-purchase-requests"><div class="empty"><div class="icon">📝</div><div>لا توجد طلبات شراء</div></div></div>
  </div>

  <div class="section-card" id="manager-cashbox-section">
    <div class="section-card-title"><span>💰 اعتماد مصروفات الخزنة</span><span style="display:flex;gap:.4rem"><button class="action-btn" onclick="renderManagerCashboxApprovals()">تحديث</button><button class="action-btn danger" onclick="resetSection('cashbox_transactions')">🗑️ رسيت</button></span></div>
    <div class="data-integrity-note">لا يُخصم الصرف ولا يُرحّل إلى حساب المورد إلا بعد الاعتماد النهائي من الإدارة.</div>
    <div id="manager-cashbox-approvals"><div class="empty"><div class="icon">💰</div><div>لا توجد مصروفات معلقة</div></div></div>
  </div>

  <div class="section-card">
    <div class="section-card-title"><span>↩️ متابعة المرتجعات والهدر</span><span style="display:flex;gap:.4rem"><button class="action-btn" onclick="renderManagerReturnsWaste()">تحديث</button><button class="action-btn danger" onclick="resetSection('returns_waste')">🗑️ رسيت</button></span></div>
    <div id="manager-return-waste"><div class="empty"><div class="icon">↩️</div><div>لا توجد حركات</div></div></div>
  </div>

  <div class="section-card" id="manager-equipment-section">
    <div class="section-card-title">
      <span>🛠️ متابعة المعدات والصيانة</span>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap">
        <select class="site-filter" id="manager-equipment-site" onchange="renderManagerEquipment()"><option value="">كل المواقع</option></select>
        <button class="action-btn" onclick="renderManagerEquipment()">تحديث</button>
        <button class="action-btn excel" onclick="exportManagerEquipmentExcel()">📥 Excel</button>
      </div>
    </div>
    <div id="manager-equipment-container"><div class="empty"><div class="icon">🛠️</div><div>جاري تحميل كشف المعدات...</div></div></div>
  </div>

  <div class="section-card" id="log-section">
    <div class="section-card-title">سجل الصرف التفصيلي</div>
    <div id="log-container"><div class="empty"><div class="icon">📭</div><div>جاري التحميل...</div></div></div>
  </div>

  <div class="section-card">
    <div class="section-card-title">
      <span>📦 ملخص المخزن</span>
      <select class="site-filter" id="inv-site-filter" onchange="renderManagerInventory()">
        <option value="">-- اختر موقع --</option>
        <option value="الأقصر">الأقصر</option>
        <option value="قنا">قنا</option>
        <option value="أسيوط">أسيوط</option>
        <option value="المنيا">المنيا</option>
      </select>
    </div>
    <div id="manager-inv-container"><div class="empty"><div class="icon">📦</div><div>اختر موقع لعرض المخزن</div></div></div>
  </div>

  <div class="section-card" id="supplier-accounts-section">
    <div class="section-card-title">
      <span>📒 كشف حسابات الموردين</span>
      <div style="display:flex;gap:.5rem;flex-wrap:wrap"><input type="month" class="date-input" id="supplier-month-filter" onchange="renderSupplierAccounts()"><input id="manager-supplier-search" list="manager-supplier-options" placeholder="بحث باسم المورد" oninput="renderSupplierAccounts()"><datalist id="manager-supplier-options"></datalist><button class="action-btn" onclick="renderSupplierAccounts()">تحديث</button></div>
    </div>
    <div id="supplier-accounts-container"><div class="empty"><div class="icon">📒</div><div>جاري تحميل الحسابات...</div></div></div>
  </div>

  <div class="section-card">
    <div class="section-card-title">
      <span>🔐 صلاحيات المشرفين</span>
      <button class="action-btn" onclick="renderPermissionsPanel()">تحديث</button>
    </div>
    <div id="permissions-container"><div class="empty"><div class="icon">🔐</div><div>جاري تحميل الصلاحيات...</div></div></div>
  </div>
</div>

<!-- PASSWORD MODAL -->
<div class="overlay" id="pwd-overlay" style="display:none;">
  <div class="modal-box" style="max-width:360px;text-align:center;">
    <div style="font-size:2.5rem;margin-bottom:.8rem;">🔐</div>
    <h2>داشبورد المدير</h2>
    <p>أدخل كلمة سر المدير للدخول</p>
    <input type="password" id="pwd-input" placeholder="كلمة السر"
      style="width:100%;padding:.7rem 1rem;background:var(--card2);border:1.5px solid var(--border);border-radius:10px;color:var(--text);font-family:'Cairo',sans-serif;font-size:1rem;text-align:center;margin-bottom:.8rem;"
      onkeydown="if(event.key==='Enter'){event.preventDefault(); checkPassword();}">
    <div id="pwd-error" style="color:var(--red);font-size:.85rem;margin-bottom:.8rem;display:none;">❌ كلمة السر غلط</div>
    <div style="display:flex;gap:.5rem;">
      <button id="manager-login-btn" type="button" onclick="checkPassword();return false;" style="flex:1;padding:.8rem;border-radius:10px;border:none;background:var(--green);color:#fff;font-family:'Cairo',sans-serif;font-size:1rem;font-weight:900;cursor:pointer;">دخول</button>
      <a href="#" onclick="closePwdModal()" style="padding:.8rem 1.2rem;border-radius:10px;border:1.5px solid var(--border);background:none;color:var(--muted);font-family:'Cairo',sans-serif;cursor:pointer;text-decoration:none;">إلغاء</a>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script>
// ===== SUPERVISORS =====
const IBF_BOOT = window.IBF_BOOT || {};
const SUPERVISORS = IBF_BOOT.supervisors || {};
const DEFAULT_MASTER_SITES = [
  {id:'SITE-LX',code:'SITE-LX',name:'الأقصر',city:'الأقصر',client:'I-BUILD قطاع التغذية'},
  {id:'SITE-QN',code:'SITE-QN',name:'قنا',city:'قنا',client:'I-BUILD قطاع التغذية'},
  {id:'SITE-AS',code:'SITE-AS',name:'أسيوط',city:'أسيوط',client:'I-BUILD قطاع التغذية'},
  {id:'SITE-MI',code:'SITE-MI',name:'المنيا',city:'المنيا',client:'I-BUILD قطاع التغذية'}
];
const CHEFS = IBF_BOOT.chefs || {};
const MANAGER_PASSWORDS = [];
let currentUser = null;
let currentChef = null;
let chefBtn = null;
let managerBtn = null;
let lastLogs = [];
let currentPermissions = {
  can_submit_meals:false,
  can_edit_inventory:false,
  can_add_invoice:false,
  can_manage_cashbox:false,
  can_manage_quality:false,
  can_manage_hr:false,
  can_manage_equipment:false
};
let qualityChecks = [];
let hrEmployees = [];
let hrAttendance = [];
let attendanceScanner = null;
let qualityPreviewUrls = [];
let equipmentRecords = [];
let maintenanceRecords = [];
let managerEquipmentRecords = [];
let managerMaintenanceRecords = [];

// ===== MEAL DATA =====
const MEALS = {
  breakfast: [
    {name:'فول مطهو',unit:'كيلو',note:'150 جم للفرد'},
    {name:'بيض',unit:'قطعة',note:'عدد (2)'},
    {name:'مربى عبوة',unit:'قطعة',note:'بالتبادل'},
    {name:'حلاوة بار',unit:'قطعة',note:'بالتبادل'},
    {name:'لبن عبوة',unit:'قطعة',note:'بالتبادل'},
    {name:'عصير عبوة',unit:'قطعة',note:'بالتبادل'},
    {name:'جبنة بيضاء عبوة',unit:'قطعة',note:'بالتبادل'},
    {name:'جبنة نستو',unit:'قطعة',note:'عدد (2) - بالتبادل'},
    {name:'عيش',unit:'قطعة',note:'عدد (2) سياحي/شامي'},
    {name:'شاي',unit:'قطعة',note:'فتلة أو باكيت للفرد'},
    {name:'سلطة خضراء',unit:'كيلو',note:'100 جم للفرد - 3 أصناف'},
    {name:'زيت+ملح+ليمون',unit:'تتبيلة',note:''},
  ],
  lunch: [
    {name:'فراخ بالعظم',unit:'فرخة',note:'ربع فرخة للوجبة — كل 4 وجبات = فرخة'},
    {name:'بانيه',unit:'كيلو',note:'150 جم للفرد - بالتبادل'},
    {name:'لحم',unit:'كيلو',note:'150 جم للفرد - بالتبادل'},
    {name:'سمك',unit:'كيلو',note:'200 جم للفرد - بالتبادل'},
    {name:'أرز',unit:'كيلو',note:'135 جم للفرد'},
    {name:'مكرونة',unit:'كيلو',note:'120 جم للفرد - بالتبادل'},
    {name:'خضار (سلطة+طهي)',unit:'كيلو',note:'430 جم للفرد'},
    {name:'فاكهة الموسم',unit:'قطعة',note:'اختيار وتوزيع'},
    {name:'حلو مشغول',unit:'قطعة',note:'اختيار وتوزيع'},
    {name:'عصير عبوة',unit:'قطعة',note:'اختيار وتوزيع'},
    {name:'عيش',unit:'قطعة',note:'عدد (2) سياحي/شامي'},
  ],
  dinner: [
    {name:'فول مطهو',unit:'كيلو',note:'150 جم للفرد'},
    {name:'بليلة باللبن',unit:'كيلو',note:'150 جم للفرد - بالتبادل'},
    {name:'بيض',unit:'قطعة',note:'عدد (1)'},
    {name:'مربى عبوة',unit:'قطعة',note:'بالتبادل'},
    {name:'حلاوة بار',unit:'قطعة',note:'بالتبادل'},
    {name:'لبن عبوة',unit:'قطعة',note:'بالتبادل'},
    {name:'عصير عبوة',unit:'قطعة',note:'بالتبادل'},
    {name:'جبنة بيضاء عبوة',unit:'قطعة',note:'بالتبادل'},
    {name:'جبنة نستو',unit:'قطعة',note:'عدد (2) - بالتبادل'},
    {name:'عيش بلدي',unit:'قطعة',note:'عدد (2)'},
    {name:'زبادي',unit:'قطعة',note:'عدد (1)'},
    {name:'سلطة خضراء',unit:'كيلو',note:'100 جم للفرد - 3 أصناف'},
    {name:'زيتون أسباني',unit:'كيلو',note:'50 جم للفرد'},
    {name:'زيت+ملح+ليمون',unit:'تتبيلة',note:''},
  ]
};

// ===== RATION RULES / MENU RECIPE =====
const RATION_RULES = {
  breakfast: [
    {name:'فول مطهو',perPerson:150,unit:'جم'},{name:'بيض',perPerson:2,unit:'قطعة'},
    {name:'مربى عبوة',perPerson:1,unit:'قطعة',choiceGroup:'breakfast-complement',choiceGroupLabel:'بدائل الإفطار — وزّع عدد الوجبات'},
    {name:'حلاوة بار',perPerson:1,unit:'قطعة',choiceGroup:'breakfast-complement',choiceGroupLabel:'بدائل الإفطار — وزّع عدد الوجبات'},
    {name:'لبن عبوة',perPerson:1,unit:'قطعة',choiceGroup:'breakfast-milk',choiceGroupLabel:'لبن — اختياري',choiceOptional:true},
    {name:'عصير عبوة',perPerson:1,unit:'قطعة',choiceGroup:'breakfast-juice',choiceGroupLabel:'عصير — اختياري',choiceOptional:true},
    {name:'جبنة بيضاء عبوة',perPerson:1,unit:'قطعة',choiceGroup:'breakfast-cheese',choiceGroupLabel:'بدائل الجبن — وزّع عدد الوجبات'},
    {name:'جبنة نستو',perPerson:2,unit:'قطعة',choiceGroup:'breakfast-cheese',choiceGroupLabel:'بدائل الجبن — وزّع عدد الوجبات'},
    {name:'عيش',perPerson:2,unit:'قطعة'},{name:'شاي',perPerson:1,unit:'قطعة'},
    {name:'سلطة خضراء',perPerson:100,unit:'جم'}
  ],
  lunch: [
    {name:'فراخ بالعظم',perPerson:0.25,unit:'فرخة',roundUp:true,choiceGroup:'lunch-protein',choiceGroupLabel:'بروتين الغداء — وزّع عدد الوجبات'},
    {name:'بانيه',perPerson:150,unit:'جم',choiceGroup:'lunch-protein',choiceGroupLabel:'بروتين الغداء — وزّع عدد الوجبات'},
    {name:'لحم',perPerson:150,unit:'جم',choiceGroup:'lunch-protein',choiceGroupLabel:'بروتين الغداء'},
    {name:'سمك',perPerson:200,unit:'جم',choiceGroup:'lunch-protein',choiceGroupLabel:'بروتين الغداء'},
    {name:'أرز',perPerson:135,unit:'جم',choiceGroup:'lunch-starch',choiceGroupLabel:'نشويات الغداء'},
    {name:'مكرونة',perPerson:120,unit:'جم',choiceGroup:'lunch-starch',choiceGroupLabel:'نشويات الغداء'},
    {name:'خضار (سلطة+طهي)',perPerson:430,unit:'جم'},
    {name:'فاكهة الموسم',perPerson:1,unit:'قطعة',choiceGroup:'lunch-dessert',choiceGroupLabel:'العصير والحلو والفاكهة — وزّع عدد الوجبات'},
    {name:'حلو مشغول',perPerson:1,unit:'قطعة',choiceGroup:'lunch-dessert',choiceGroupLabel:'العصير والحلو والفاكهة — وزّع عدد الوجبات'},
    {name:'عصير عبوة',perPerson:1,unit:'قطعة',choiceGroup:'lunch-dessert',choiceGroupLabel:'العصير والحلو والفاكهة — وزّع عدد الوجبات'},
    {name:'عيش',perPerson:2,unit:'قطعة'}
  ],
  dinner: [
    {name:'فول مطهو',perPerson:150,unit:'جم',choiceGroup:'dinner-hot',choiceGroupLabel:'الصنف الساخن — وزّع عدد الوجبات'},
    {name:'بليلة باللبن',perPerson:150,unit:'جم',choiceGroup:'dinner-hot',choiceGroupLabel:'الصنف الساخن — وزّع عدد الوجبات'},
    {name:'بيض',perPerson:1,unit:'قطعة'},
    {name:'مربى عبوة',perPerson:1,unit:'قطعة',choiceGroup:'dinner-complement',choiceGroupLabel:'بدائل العشاء — وزّع عدد الوجبات'},
    {name:'حلاوة بار',perPerson:1,unit:'قطعة',choiceGroup:'dinner-complement',choiceGroupLabel:'بدائل العشاء — وزّع عدد الوجبات'},
    {name:'لبن عبوة',perPerson:1,unit:'قطعة',choiceGroup:'dinner-milk',choiceGroupLabel:'لبن — اختياري',choiceOptional:true},
    {name:'عصير عبوة',perPerson:1,unit:'قطعة',choiceGroup:'dinner-juice',choiceGroupLabel:'عصير — اختياري',choiceOptional:true},
    {name:'جبنة بيضاء عبوة',perPerson:1,unit:'قطعة',choiceGroup:'dinner-cheese',choiceGroupLabel:'بدائل الجبن — وزّع عدد الوجبات'},
    {name:'جبنة نستو',perPerson:2,unit:'قطعة',choiceGroup:'dinner-cheese',choiceGroupLabel:'بدائل الجبن — وزّع عدد الوجبات'},
    {name:'عيش بلدي',perPerson:2,unit:'قطعة'},
    {name:'زبادي',perPerson:1,unit:'قطعة'},{name:'سلطة خضراء',perPerson:100,unit:'جم'},
    {name:'زيتون أسباني',perPerson:50,unit:'جم'}
  ]
};
let generatedRecipe=[];
let generatedMenuDishes=[];
const inlineChoiceAllocations={breakfast:{},lunch:{},dinner:{}};
const inlineMealTotals={breakfast:0,lunch:0,dinner:0};
function defaultWarehouseType(name){
  if(/فراخ|بانيه|لحم|سمك/.test(name))return 'مجمد';
  if(/لبن|جبن|زبادي|بيض|سلطة|خضار|فاكهة/.test(name))return 'مبرد';
  return 'جاف';
}
function getRationStandards(meal){
  const defaults=(RATION_RULES[meal]||[]).map((item,index)=>({id:`DEFAULT-${meal}-${index}`,code:`IB-${meal.toUpperCase()}-${String(index+1).padStart(2,'0')}`,dishName:item.name,meal,choiceGroup:item.choiceGroup||'',choiceGroupLabel:item.choiceGroupLabel||'',choiceOptional:!!item.choiceOptional,components:[{name:item.name,perPerson:item.perPerson,unit:item.unit,roundUp:!!item.roundUp,lossPercent:0,warehouseType:defaultWarehouseType(item.name)}],isDefault:true}));
  const db=loadLocalDb(),custom=(db.ration_standards||[]).filter(item=>item.meal===meal&&item.active!==false);
  return [...defaults,...custom];
}
function inlineRecipeGroups(meal){
  const groups=new Map();
  getRationStandards(meal).filter(standard=>standard.choiceGroup).forEach(standard=>{
    if(!groups.has(standard.choiceGroup)){
      groups.set(standard.choiceGroup,{key:standard.choiceGroup,label:standard.choiceGroupLabel||'مجموعة بدائل',optional:!!standard.choiceOptional,items:[]});
    }
    groups.get(standard.choiceGroup).items.push(standard);
  });
  return [...groups.values()];
}
function inlineRateLabel(component){
  if(component.roundUp&&component.unit==='فرخة'&&Number(component.perPerson)===0.25) return '¼ فرخة';
  return `${Number(component.perPerson).toLocaleString('ar-EG',{maximumFractionDigits:3})} ${component.unit}`;
}
function inlineStandardRateLabel(standard){
  return (standard.components||[]).map(inlineRateLabel).join(' + ');
}
function ensureInlineAllocations(meal,total,previousTotal){
  inlineChoiceAllocations[meal]=inlineChoiceAllocations[meal]||{};
  inlineRecipeGroups(meal).forEach(group=>{
    const values=inlineChoiceAllocations[meal][group.key]||{};
    group.items.forEach(item=>{
      if(!Number.isFinite(Number(values[item.id]))) values[item.id]=0;
    });
    const first=group.items[0];
    const restTotal=group.items.slice(1).reduce((sum,item)=>sum+(Number(values[item.id])||0),0);
    const defaultOnly=first&&(Number(values[first.id])||0)===previousTotal&&restTotal===0;
    const allZero=group.items.every(item=>(Number(values[item.id])||0)===0);
    if(first&&!group.optional&&(allZero||defaultOnly)) values[first.id]=total;
    inlineChoiceAllocations[meal][group.key]=values;
  });
}
function mealHeadcount(meal){
  const students=Math.max(0,parseInt(document.getElementById('students-'+meal)?.value)||0);
  const admins=Math.max(0,parseInt(document.getElementById('admins-'+meal)?.value)||0);
  return students+admins;
}
function updateMealHeadcount(meal){
  const total=mealHeadcount(meal);
  const previousTotal=inlineMealTotals[meal]||0;
  const totalInput=document.getElementById('meals-count-'+meal);
  if(totalInput) totalInput.value=total;
  ensureInlineAllocations(meal,total,previousTotal);
  inlineMealTotals[meal]=total;
  renderInlineRecipeControls(meal);
  refreshInlineRecipeResults(meal);
}
function initInlineRecipes(){
  ['breakfast','lunch','dinner'].forEach(meal=>{
    const total=mealHeadcount(meal);
    inlineMealTotals[meal]=total;
    const totalInput=document.getElementById('meals-count-'+meal);
    if(totalInput) totalInput.value=total;
    ensureInlineAllocations(meal,total,0);
    renderInlineRecipeControls(meal);
    refreshInlineRecipeResults(meal);
  });
}
function renderInlineRecipeControls(meal){
  const container=document.getElementById('inline-recipe-controls-'+meal);
  if(!container)return;
  const standards=getRationStandards(meal);
  const fixed=standards.filter(standard=>!standard.choiceGroup);
  const groups=inlineRecipeGroups(meal);
  const total=inlineMealTotals[meal]||0;
  const fixedHtml=fixed.length?`<div class="inline-fixed-list">${fixed.map(standard=>`<span class="workflow-step done">✓ ${safeText(standard.dishName)} · ${safeText(inlineStandardRateLabel(standard))}</span>`).join('')}</div>`:'';
  const groupsHtml=groups.map((group,index)=>{
    const values=inlineChoiceAllocations[meal]?.[group.key]||{};
    return `<div class="ration-choice-group" style="margin-bottom:.8rem">
      <div class="ration-choice-title"><strong>🔁 ${safeText(group.label)}</strong><span>${group.optional?'اختياري من صفر إلى ': 'إجمالي المجموعة يجب أن يساوي '}${total.toLocaleString('ar-EG')} وجبة</span></div>
      <div class="inline-choice-grid">${group.items.map(item=>`
        <label class="inline-choice-option">
          <span><strong>${safeText(item.dishName)}</strong><small>${safeText(inlineStandardRateLabel(item))} لكل وجبة</small></span>
          <input type="number" min="0" max="${total}" step="1" value="${Number(values[item.id])||0}" aria-label="عدد وجبات ${safeText(item.dishName)}" oninput="setInlineAllocation('${meal}',${index},'${safeText(item.id)}',this.value)">
        </label>`).join('')}</div>
      <div class="inline-group-status" data-inline-status="${index}"></div>
    </div>`;
  }).join('');
  container.innerHTML=fixedHtml+groupsHtml;
}
function setInlineAllocation(meal,groupIndex,itemId,value){
  const group=inlineRecipeGroups(meal)[groupIndex];
  if(!group)return;
  const amount=Math.max(0,Math.floor(Number(value)||0));
  inlineChoiceAllocations[meal][group.key][itemId]=amount;
  refreshInlineRecipeResults(meal);
}
function normalizeInlineComponent(component,servedMeals){
  const raw=(Number(component.perPerson)||0)*servedMeals;
  if(component.unit==='جم') return {quantity:raw/1000,unit:'كيلو'};
  if(component.unit==='مل') return {quantity:raw/1000,unit:'لتر'};
  if(component.roundUp) return {quantity:Math.ceil(raw),unit:component.unit};
  return {quantity:raw,unit:component.unit};
}
function calculateInlineRecipe(meal){
  const total=inlineMealTotals[meal]||0;
  const groups=inlineRecipeGroups(meal);
  const groupStatus=groups.map(group=>{
    const values=inlineChoiceAllocations[meal]?.[group.key]||{};
    const sum=group.items.reduce((amount,item)=>amount+(Number(values[item.id])||0),0);
    return {group,sum,difference:total-sum,valid:group.optional?sum<=total:sum===total};
  });
  const aggregate={};
  getRationStandards(meal).forEach(standard=>{
    const servedMeals=standard.choiceGroup
      ? Number(inlineChoiceAllocations[meal]?.[standard.choiceGroup]?.[standard.id])||0
      : total;
    if(servedMeals<=0)return;
    (standard.components||[]).forEach(component=>{
      const normalized=normalizeInlineComponent(component,servedMeals);
      const key=`${component.name}|${normalized.unit}`;
      if(!aggregate[key]){
        aggregate[key]={
          name:component.name,
          quantity:0,
          unit:normalized.unit,
          servedMeals:0,
          rates:[],
          sources:[]
        };
      }
      aggregate[key].quantity+=normalized.quantity;
      aggregate[key].servedMeals+=servedMeals;
      const rate=inlineRateLabel(component);
      if(!aggregate[key].rates.includes(rate))aggregate[key].rates.push(rate);
      if(!aggregate[key].sources.includes(standard.dishName))aggregate[key].sources.push(standard.dishName);
    });
  });
  const rows=Object.values(aggregate).map(row=>({...row,quantity:Number(row.quantity.toFixed(3))}));
  return {total,rows,groupStatus,valid:total>0&&groupStatus.every(status=>status.valid)};
}
function ensureRecipeIssueInput(meal,row){
  const grid=document.getElementById('grid-'+meal);
  let input=[...grid.querySelectorAll('input.qty-input')].find(field=>field.dataset.item===row.name);
  if(input)return input;
  const card=document.createElement('div');
  card.className='item-card custom-item';
  card.innerHTML=`<div class="item-info"><div class="item-name">${safeText(row.name)}</div><div class="item-unit">${safeText(row.unit)}</div><div class="item-note">من الريسيبي التلقائي</div></div><div class="qty-wrap"><button class="qty-btn" onclick="dec(this)">−</button><input class="qty-input" type="number" value="0" min="0" step="0.001" data-item="${safeText(row.name)}" data-unit="${safeText(row.unit)}"><button class="qty-btn" onclick="inc(this)">+</button></div>`;
  grid.appendChild(card);
  return card.querySelector('input');
}
function syncInlineRecipeToIssueGrid(meal,rows){
  const recipeNames=new Set(getRationStandards(meal).flatMap(standard=>(standard.components||[]).map(component=>component.name)));
  document.querySelectorAll(`#grid-${meal} input.qty-input`).forEach(input=>{
    if(recipeNames.has(input.dataset.item))input.value=0;
  });
  rows.forEach(row=>{
    const input=ensureRecipeIssueInput(meal,row);
    input.value=row.unit==='قطعة'||row.unit==='فرخة'?Math.ceil(row.quantity):Number(row.quantity.toFixed(3));
    input.dataset.unit=row.unit;
    const unitLabel=input.closest('.item-card')?.querySelector('.item-unit');
    if(unitLabel)unitLabel.textContent=row.unit;
  });
}
function refreshInlineRecipeResults(meal){
  const calculation=calculateInlineRecipe(meal);
  const controls=document.getElementById('inline-recipe-controls-'+meal);
  controls?.querySelectorAll('.inline-group-status').forEach((element,index)=>{
    const status=calculation.groupStatus[index];
    if(!status)return;
    element.className='inline-group-status '+(status.valid?'ok':'warn');
    element.textContent=status.valid
      ? status.group.optional?`✅ كمية اختيارية: ${status.sum.toLocaleString('ar-EG')} وجبة`:`✅ تم توزيع ${status.sum.toLocaleString('ar-EG')} وجبة بالكامل`
      : status.difference>0
        ? `⚠️ متبقي توزيع ${status.difference.toLocaleString('ar-EG')} وجبة`
        : `⚠️ التوزيع زائد بمقدار ${Math.abs(status.difference).toLocaleString('ar-EG')} وجبة`;
  });
  const tbody=document.getElementById('inline-recipe-tbody-'+meal);
  if(tbody){
    tbody.innerHTML=calculation.rows.length?calculation.rows.map(row=>`<tr>
      <td><strong>${safeText(row.name)}</strong>${row.sources.length>1?`<div style="font-size:.68rem;color:var(--muted)">${row.sources.map(safeText).join(' / ')}</div>`:''}</td>
      <td>${row.servedMeals.toLocaleString('ar-EG')}</td>
      <td>${row.rates.map(safeText).join(' + ')}</td>
      <td class="inv-cost">${row.quantity.toLocaleString('ar-EG',{maximumFractionDigits:3})}</td>
      <td><span class="tag">${safeText(row.unit)}</span></td>
    </tr>`).join(''):'<tr><td colspan="5" style="text-align:center;color:var(--muted);padding:1.5rem">أدخل عدد الطلاب والإداريين ليظهر الريسيبي</td></tr>';
  }
  syncInlineRecipeToIssueGrid(meal,calculation.rows);
  return calculation;
}
function validateInlineRecipe(meal){
  const calculation=refreshInlineRecipeResults(meal);
  if(calculation.total<1){
    showToast('أدخل عدد الطلاب والإداريين أولًا','error');
    return null;
  }
  const invalid=calculation.groupStatus.find(status=>!status.valid);
  if(invalid){
    showToast(`أكمل توزيع «${invalid.group.label}» حتى يساوي ${calculation.total} وجبة`,'error');
    return null;
  }
  return calculation;
}
function openRationModal(){
  document.getElementById('ration-code').value='';document.getElementById('ration-dish-name').value='';document.getElementById('ration-choice-group').value='';
  document.getElementById('ration-meal').value=document.getElementById('recipe-meal')?.value||'lunch';
  document.getElementById('ration-components').innerHTML='';addRationComponentRow();addRationComponentRow();
  document.getElementById('ration-overlay').style.display='flex';
}
function closeRationModal(){document.getElementById('ration-overlay').style.display='none';}
function addRationComponentRow(){
  const row=document.createElement('div');row.className='ration-component-row';
  row.innerHTML='<input class="ration-comp-name" placeholder="اسم المكون"><input class="ration-comp-qty" type="number" min="0.001" step="0.001" placeholder="للفرد"><select class="ration-comp-unit"><option>جم</option><option>مل</option><option>قطعة</option><option>حصة</option></select><input class="ration-comp-loss" type="number" min="0" max="100" step="0.1" value="0" placeholder="فاقد %"><select class="ration-comp-wh"><option>جاف</option><option>مبرد</option><option>مجمد</option><option>تعبئة وتغليف</option></select><button onclick="this.parentElement.remove()">✕</button>';
  document.getElementById('ration-components').appendChild(row);
}
function saveRationStandard(){
  const code=document.getElementById('ration-code').value.trim().toUpperCase(),dishName=document.getElementById('ration-dish-name').value.trim(),meal=document.getElementById('ration-meal').value,choiceGroupLabel=document.getElementById('ration-choice-group').value.trim();
  const components=[...document.querySelectorAll('.ration-component-row')].map(row=>({name:row.querySelector('.ration-comp-name').value.trim(),perPerson:parseFloat(row.querySelector('.ration-comp-qty').value)||0,unit:row.querySelector('.ration-comp-unit').value,lossPercent:parseFloat(row.querySelector('.ration-comp-loss').value)||0,warehouseType:row.querySelector('.ration-comp-wh').value})).filter(item=>item.name&&item.perPerson>0);
  if(!code||!dishName||!components.length){showToast('أدخل كود واسم الصنف ومكونًا واحدًا على الأقل','error');return;}
  if(components.some(item=>item.lossPercent<0||item.lossPercent>100)){showToast('نسبة الفاقد يجب أن تكون بين 0 و100','error');return;}
  const db=loadLocalDb();db.ration_standards=db.ration_standards||[];
  if(db.ration_standards.some(item=>item.code.toLowerCase()===code.toLowerCase())){showToast('كود المقنن مستخدم من قبل','error');return;}
  const choiceGroup=choiceGroupLabel?`CUSTOM-${meal}-${choiceGroupLabel.toLocaleLowerCase('ar-EG').replace(/\s+/g,'-')}`:'';
  db.ration_standards.push({id:'RATION-'+Date.now(),code,dishName,meal,choiceGroup,choiceGroupLabel,components,version:1,active:true,createdAt:new Date().toISOString()});saveLocalDb(db);
  document.getElementById('recipe-meal').value=meal;closeRationModal();renderRationSelector();showToast('✅ تم حفظ المقنن وإضافته للمنيو','success');
}

// ===== CLEANING ITEMS =====
const CLEANING_ITEMS = [
  {name:'صابون فل',unit:'زجاجه'},{name:'مقشة ناعمة عادية',unit:'قطعه'},
  {name:'معطر ارض جركن 60 لتر',unit:'لتر'},{name:'صابون سائل عجينه',unit:'كيس'},
  {name:'اسفنج مواعين',unit:'قطعه'},{name:'كروكس',unit:'قطعه'},
  {name:'مساحة',unit:'قطعه'},{name:'مقشة سوبر',unit:'قطعه'},
  {name:'شرشوبه',unit:'قطعه'},{name:'فرشة بلاط',unit:'قطعه'},
  {name:'معطر خام',unit:'زجاجه'},{name:'شاور يد',unit:'كيلو'},
  {name:'يد خشب',unit:'قطعه'},{name:'جردل بالعصارة',unit:'قطعه'},
  {name:'كمامات طبية',unit:'علبه'},{name:'بيروسول',unit:'زجاجه'},
  {name:'مساحة كبيرة',unit:'قطعه'},{name:'اكياس زبالة 40*70',unit:'كيلو'},
  {name:'اكياس زبالة 50*90',unit:'كيلو'},{name:'مزيل تكلس',unit:'قطعه'},
  {name:'فلاش',unit:'زجاجه'},{name:'شاور يد اوبال',unit:'زجاجه'},
  {name:'ديتول سائل',unit:'كيلو'},{name:'ديسبنسر بلاستيك',unit:'قطعه'},
  {name:'كلور ( كلوريل )',unit:'زجاجه'},{name:'جركن صابون 60',unit:'كيلو'},
  {name:'شاور يد 10 لتر',unit:'جركن 10 ك'},{name:'كلور شعبي',unit:'زجاجه'},
  {name:'سلك الومنيوم',unit:'كيلو'},{name:'فوط كبيره',unit:'قطعه'},
  {name:'فوط ملونه',unit:'قطعه'},{name:'جاروف',unit:'قطعه'},
  {name:'مناديل تواليت',unit:'قطعه'},{name:'صابون وجه',unit:'قطعه'},
  {name:'مناديل باكت 550',unit:'قطعه'},{name:'ديسبنسر حديد',unit:'قطعه'},
  {name:'جوانتي بلاستك',unit:'كيس'},{name:'داوني اصلي 1 لتر',unit:'زجاجه'},
  {name:'منعم اقمشه 3 لتر',unit:'جركن 5 لتر'},{name:'ديتول 5 لتر',unit:'جركن 5 لتر'},
  {name:'صابون 5 لتر',unit:'جركن 5 لتر'},{name:'ليفة مواعين',unit:'قطعه'},
  {name:'صابون سائل فيبا 730 مل',unit:'زجاجه'},{name:'مسحوق ليدر 70 جم',unit:'كيس'},
  {name:'معطر ارض',unit:'زجاجه'},{name:'فوط ارض',unit:'قطعه'},
  {name:'سلك مواعين',unit:'قطعه'},{name:'فوطه تلميع',unit:'قطعه'},
  {name:'صابون 10 لتر',unit:'جركن 10 ك'},{name:'مقشه خشنه',unit:'قطعه'},
  {name:'مزيل دهون',unit:'زجاجه'},{name:'صابون بيريل 1 لتر',unit:'زجاجه'},
  {name:'شاور يد 5 لتر',unit:'جركن 5 لتر'},{name:'شاور يد فيرا 1 لتر',unit:'جركن'},
  {name:'جركن صابون فيرا 20 لتر',unit:'جركن'},{name:'اريال سايب',unit:'كيلو'},
  {name:'صابون 20 لتر',unit:'جركن 20 لتر'},{name:'معطر بخاخ',unit:'قطعه'},
  {name:'اسفنج مواعين (لفة)',unit:'لفة'},{name:'كمامه قماش',unit:'قطعه'},
];

// ===== OPERATION ITEMS (مستلزمات التشغيل — غير المنيو وغير النظافة) =====
const OPERATION_ITEMS = [
  {name:'فحم',unit:'كيلو'},{name:'زيت طهي',unit:'لتر'},
  {name:'زيت قلي',unit:'لتر'},{name:'سمن',unit:'كيلو'},
  {name:'سمن بلدي',unit:'كيلو'},{name:'زبدة',unit:'كيلو'},
  {name:'بهارات مشكلة',unit:'كيلو'},{name:'ملح طعام',unit:'كيلو'},
  {name:'فلفل أسود',unit:'كيلو'},{name:'كمون',unit:'كيلو'},
  {name:'كزبرة',unit:'كيلو'},{name:'شطة',unit:'كيلو'},
  {name:'كركم',unit:'كيلو'},{name:'ورق لورا',unit:'كيلو'},
  {name:'قرفة',unit:'كيلو'},{name:'مكعبات مرقة',unit:'علبه'},
  {name:'خل',unit:'لتر'},{name:'صلصة طماطم',unit:'كيلو'},
  {name:'خميرة',unit:'كيلو'},{name:'بيكنج بودر',unit:'كيلو'},
  {name:'دقيق',unit:'كيلو'},{name:'نشا',unit:'كيلو'},
  {name:'سكر',unit:'كيلو'},{name:'بقسماط',unit:'كيلو'},
  {name:'أنبوبة غاز',unit:'أنبوبة'},{name:'غاز تعبئة',unit:'كيلو'},
  {name:'ولاعة بوتاجاز',unit:'قطعه'},{name:'فويل ألومنيوم',unit:'لفة'},
  {name:'ورق زبدة',unit:'لفة'},{name:'أكياس حفظ طعام',unit:'كيس'},
  {name:'أطباق فوم',unit:'قطعه'},{name:'شرائط تغليف',unit:'لفة'},
];

// ===== INVENTORY BASE ITEMS (from قنااان.xlsx) =====
const INVENTORY_BASE_ITEMS = [
  {name:'باتيه',unit:'قطعه'},
  {name:'جبنه 80جم',unit:'قطعه'},
  {name:'جبنه عبور لاند 1/2',unit:'قطعه'},
  {name:'ملح',unit:'قطعه'},
  {name:'جبنه نستو بريزيدون',unit:'علبه'},
  {name:'جبنه نستو',unit:'قطعه'},
  {name:'زبادي',unit:'قطعه'},
  {name:'لبن 200ملي',unit:'قطعه'},
  {name:'لبن 1 لتر',unit:'لتر'},
  {name:'عصير',unit:'قطعه'},
  {name:'لبن 1/2 لتر',unit:'لتر'},
  {name:'حلاوة بار 21 جرام',unit:'قطعه'},
  {name:'حمص',unit:'كيلو'},
  {name:'دقيق',unit:'كيلو'},
  {name:'بيض',unit:'قطعه'},
  {name:'فراخ',unit:'فرخه'},
  {name:'لحمة قطع',unit:'كيلو'},
  {name:'بانيه',unit:'كيلو'},
  {name:'بابريكا',unit:'كيلو'},
  {name:'عيش سياحي',unit:'قطعه'},
  {name:'عيش كيزر',unit:'قطعه'},
  {name:'بطاطس',unit:'كيلو'},
  {name:'خيار',unit:'كيلو'},
  {name:'بصل',unit:'كيلو'},
  {name:'جزر',unit:'كيلو'},
  {name:'طماطم',unit:'كيلو'},
  {name:'كرنب',unit:'قطعه'},
  {name:'كابوتشا',unit:'قطعه'},
  {name:'ليمون',unit:'كيلو'},
  {name:'ثوم',unit:'كيلو'},
  {name:'خضرة',unit:'خضره'},
  {name:'خضار مشكل',unit:'كيس 350 جرام'},
  {name:'كوسه',unit:'كيلو'},
  {name:'تفاح',unit:'كيلو'},
  {name:'برتقال',unit:'كيلو'},
  {name:'فلفل الوان',unit:'كيلو'},
  {name:'شاى العروسه',unit:'فتله'},
  {name:'سكر',unit:'كيس'},
  {name:'ارز',unit:'كيلو'},
  {name:'شعرية',unit:'كيس 350 جرام'},
  {name:'مكرونه مرمرية',unit:'كيس 350 جرام'},
  {name:'مكرونه قلم',unit:'كيس 350 جرام'},
  {name:'فاصوليا',unit:'كيلو'},
  {name:'لسان عصفور',unit:'كيس 350 جرام'},
  {name:'فلفل اسود',unit:'كيلو'},
  {name:'كمون',unit:'كيلو'},
  {name:'زيت',unit:'زجاجه'},
  {name:'حلاوه بار 40 جرام',unit:'قطعه'},
  {name:'ورق لورا',unit:'كيلو'},
  {name:'صلصة',unit:'برطمان'},
  {name:'خل',unit:'زجاجه'},
  {name:'جنزبيل',unit:'كيلو'},
  {name:'بسله بالجزر',unit:'كيس 350 جرام'},
  {name:'سي فود',unit:'كيس 350 جرام'},
  {name:'مربي',unit:'قطعه'},
  {name:'مخلل كبير',unit:'كيلو'},
  {name:'طحينه',unit:'كيلو'},
  {name:'كاتشب ظرف',unit:'ظرف'},
  {name:'فول تدميس',unit:'كيلو'},
  {name:'مكرونه اسباجتي',unit:'كيس 350 جرام'},
  {name:'عدس اصفر',unit:'كيلو'},
  {name:'عدس بجبه',unit:'كيلو'},
  {name:'بطاطس بوم فريت',unit:'كيلو'},
  {name:'لحمه مفرومه',unit:'كيلو'},
  {name:'بقسماط',unit:'كيلو'},
  {name:'تونه مفتتة',unit:'علبه'},
  {name:'كوب كرتون - ( شاى )',unit:'قطعه'},
  {name:'رول الومنيوم',unit:'قطعه'},
  {name:'شوكه بلاستيك',unit:'قطعه'},
  {name:'طبق 1/8',unit:'قطعه'},
  {name:'طبق فويل 250',unit:'قطعه'},
  {name:'طبق فويل 420',unit:'قطعه'},
  {name:'طبق فويل خفيف 600',unit:'قطعه'},
  {name:'معالق كبيرة',unit:'قطعه'},
  {name:'معلقة زبادى',unit:'قطعه'},
  {name:'استريتش',unit:'قطعه'},
  {name:'علب للتوابل',unit:'قطعه'},
  {name:'سكينه',unit:'قطعه'},
  {name:'صابون فل',unit:'زجاجه'},
  {name:'مقشة ناعمة عادية',unit:'قطعه'},
  {name:'معطر ارض جركن 60 لتر',unit:'لتر'},
  {name:'صابون سائل عجينه',unit:'كيس'},
  {name:'اسفنج مواعين',unit:'قطعه'},
  {name:'كروكس',unit:'قطعه'},
  {name:'مساحة',unit:'قطعه'},
  {name:'مقشة سوبر',unit:'قطعه'},
  {name:'شرشوبه',unit:'قطعه'},
  {name:'فرشة بلاط',unit:'قطعه'},
  {name:'1معطر خام',unit:'زجاجه'},
  {name:'شاور يد',unit:'كيلو'},
  {name:'يد خشب',unit:'قطعه'},
  {name:'جردل بالعصارة',unit:'قطعه'},
  {name:'كمامات طبية',unit:'علبه'},
  {name:'بيروسول',unit:'زجاجه'},
  {name:'باسكت بغطاء صغير',unit:'قطعه'},
  {name:'مساحة كبيرة',unit:'قطعه'},
  {name:'اكياس زبالة 40*70',unit:'كيلو'},
  {name:'اكياس زبالة 50*90',unit:'كيلو'},
  {name:'مزيل تكلس',unit:'قطعه'},
  {name:'فلاش',unit:'زجاجه'},
  {name:'شاور يد اوبال',unit:'زجاجه'},
  {name:'ديتول سائل',unit:'كيلو'},
  {name:'ديسبنسر بلاستيك',unit:'قطعه'},
  {name:'كلور زجاجه ( كلوريل )',unit:'زجاجه'},
  {name:'جركن صابون 60',unit:'كيلو'},
  {name:'شاور يد 10 لتر',unit:'جركن 10 ك'},
  {name:'كلور شعبي',unit:'زجاجه'},
  {name:'سلك الومنيوم',unit:'كيلو'},
  {name:'فوط كبيره',unit:'قطعه'},
  {name:'فوط ملونه',unit:'قطعه'},
  {name:'جاروف',unit:'قطعه'},
  {name:'مناديل تواليت',unit:'قطعه'},
  {name:'صابون وجه',unit:'قطعه'},
  {name:'مناديل باكت 550',unit:'قطعه'},
  {name:'اكياس شحن',unit:'كيلو'},
  {name:'ديسبنسر حديد',unit:'قطعه'},
  {name:'سمنة 11 ك',unit:'جركن 11 ك'},
  {name:'مخلل',unit:'برطمان'},
  {name:'مرقه',unit:'علبه'},
  {name:'زيت (جركن)',unit:'جركن 16 ك'},
  {name:'فحم',unit:'شوال 10 ك'},
  {name:'فول مدشوش',unit:'كيلو'},
  {name:'قمح',unit:'كيلو'},
  {name:'لوبيا',unit:'كيلو'},
  {name:'فطير',unit:'قطعه'},
  {name:'توابل مشكله',unit:'كيلو'},
  {name:'جوانتي بلاستك',unit:'كيس'},
  {name:'مياه معدنيه كبيرة',unit:'زجاجه'},
  {name:'موز',unit:'كيلو'},
  {name:'باذنجان',unit:'كيلو'},
  {name:'فلفل رومي',unit:'كيلو'},
  {name:'مكرونه مرمريه',unit:'كيلو'},
  {name:'كوب ارز بلبن',unit:'علبه'},
  {name:'بسبوسه جافه',unit:'كيلو'},
  {name:'طبق فوم 1/4',unit:'قطعه'},
  {name:'باميه',unit:'كيلو'},
  {name:'نشا',unit:'كيلو'},
  {name:'مشروم',unit:'علبه'},
  {name:'عسل اسود',unit:'كيلو'},
  {name:'اكياس وجبات',unit:'كيلو'},
  {name:'طبق فوم 1/2',unit:'قطعه'},
  {name:'رول فويل كبير',unit:'قطعه'},
  {name:'كوب مياه',unit:'قطعه'},
  {name:'باذنجان ابيض',unit:'كيلو'},
  {name:'جبنه رومي',unit:'كيلو'},
  {name:'فانيليا',unit:'علبه'},
  {name:'علب وجبات',unit:'علبه'},
  {name:'شطه',unit:'كيلو'},
  {name:'حبهان',unit:'كيلو'},
  {name:'جوزه الطيب',unit:'كيلو'},
  {name:'خس',unit:'قطعه'},
  {name:'كزبره',unit:'كيلو'},
  {name:'كفته صيامي',unit:'كيس 350 جرام'},
  {name:'يوسفي',unit:'كيلو'},
  {name:'كعك',unit:'كيلو'},
  {name:'نسكافيه جولد',unit:'علبه'},
  {name:'منظف معدات',unit:'علبه'},
  {name:'جوز هند',unit:'كيلو'},
  {name:'زبيب',unit:'كيلو'},
  {name:'اكياس نايلون 20*35',unit:'كيلو'},
  {name:'اكياس تغليف',unit:'كيلو'},
  {name:'كركم',unit:'كيلو'},
  {name:'قرنفل',unit:'كيلو'},
  {name:'لانشون',unit:'كيلو'},
  {name:'داوني اصلي 1 لتر',unit:'زجاجه'},
  {name:'منعم اقمشه 3 لتر',unit:'جركن 5 لتر'},
  {name:'زجاجه ديتول 5',unit:'جركن 5 لتر'},
  {name:'صابون 5 لتر',unit:'جركن 5 لتر'},
  {name:'سمك فيليه',unit:'كيلو'},
  {name:'بطاطا',unit:'كيلو'},
  {name:'كبريت',unit:'علبه'},
  {name:'بسله',unit:'كيلو'},
  {name:'كوب رايل',unit:'قطعه'},
  {name:'ثوم بودر',unit:'كيلو'},
  {name:'بصل بودر',unit:'كيلو'},
  {name:'فيتا 1/8',unit:'قطعه'},
  {name:'توك شيف',unit:'قطعه'},
  {name:'مسطرده',unit:'علبه'},
  {name:'جوافه',unit:'كيلو'},
  {name:'ليفة مواعين',unit:'قطعه'},
  {name:'اكياس صرفيات',unit:'كيلو'},
  {name:'صابون سائل فيبا 730 مل',unit:'زجاجه'},
  {name:'طبق فوم 1ك',unit:'قطعه'},
  {name:'فلفل حامي',unit:'كيلو'},
  {name:'صلصة 3 ك',unit:'عبوة 3 ك'},
  {name:'كرفس',unit:'خضره'},
  {name:'مسحوق ليدر 70حم',unit:'كيس'},
  {name:'اكياس تخزين',unit:'كيلو'},
  {name:'حلويات مشكله',unit:'كيلو'},
  {name:'عيش كبير',unit:'قطعه'},
  {name:'معطر ارض',unit:'زجاجه'},
  {name:'فوط ارض',unit:'قطعه'},
  {name:'سمك بوري',unit:'كيلو'},
  {name:'مكسرات',unit:'كيلو'},
  {name:'عسل نحل',unit:'برطمان'},
  {name:'قرفه',unit:'كيلو'},
  {name:'كاري',unit:'كيلو'},
  {name:'شعريه',unit:'كيلو'},
  {name:'بهارات لحمه',unit:'كيلو'},
  {name:'مرقه دجاج',unit:'كيلو'},
  {name:'ملح ليمون',unit:'كيلو'},
  {name:'فلفل ابيض',unit:'كيلو'},
  {name:'هير هيد',unit:'قطعه'},
  {name:'اكياس حليب 25*40',unit:'كيلو'},
  {name:'صدور بالعظم',unit:'كيلو'},
  {name:'سلك مواعين',unit:'قطعه'},
  {name:'مقشره بطاطس',unit:'قطعه'},
  {name:'حلويات شرقي',unit:'قطعه'},
  {name:'كانز جيب',unit:'علبه'},
  {name:'قرفه خشب',unit:'كيلو'},
  {name:'روز ماري',unit:'كيلو'},
  {name:'زعتر',unit:'كيلو'},
  {name:'سوداني',unit:'كيلو'},
  {name:'بيكنج بودر',unit:'كيس'},
  {name:'خميره فوريه',unit:'كيس'},
  {name:'فوطه تلميع',unit:'قطعه'},
  {name:'بسله مجمده',unit:'كيس'},
  {name:'بطاس',unit:'كيلو'},
  {name:'سكر بودر',unit:'كيلو'},
  {name:'جلاش',unit:'لفة'},
  {name:'ارز بسمتي',unit:'كيلو'},
  {name:'صابون 10 لتر',unit:'جركن 10 ك'},
  {name:'زبده',unit:'كيلو'},
  {name:'كبده',unit:'كيلو'},
  {name:'كانز بريل',unit:'قطعه'},
  {name:'حمام',unit:'واحدة'},
  {name:'جركن كاتشب',unit:'كيلو'},
  {name:'مايونيز',unit:'كيلو'},
  {name:'دهن لحمه',unit:'كيلو'},
  {name:'لحمه مستورده سن',unit:'كيلو'},
  {name:'ملوخيه',unit:'كيس 350 جرام'},
  {name:'مقشه خشنه',unit:'قطعه'},
  {name:'بانيه صيامي',unit:'كيس 350 جرام'},
  {name:'صلصه 800 جرام',unit:'برطمان'},
  {name:'عصير بودر',unit:'كيلو'},
  {name:'خله شيش',unit:'علبه'},
  {name:'تمر هندي',unit:'كيلو'},
  {name:'عصير سوبيا',unit:'كيلو'},
  {name:'بلح',unit:'كيلو'},
  {name:'صنيه كنافه',unit:'علبه'},
  {name:'كنافه',unit:'كيلو'},
  {name:'كاستر',unit:'علبه'},
  {name:'ملوخيه ناشفه',unit:'كيلو'},
  {name:'عرق سوس',unit:'كيلو'},
  {name:'كربوناتو',unit:'كيلو'},
  {name:'سمبوسه',unit:'علبه'},
  {name:'شربات',unit:'زجاجه'},
  {name:'موتزريلا',unit:'كيلو'},
  {name:'فول صويا',unit:'كيلو'},
  {name:'بنجر',unit:'كيلو'},
  {name:'بلح الشام',unit:'كيلو'},
  {name:'طبق مميز بالغطا',unit:'قطعه'},
  {name:'شمام',unit:'كيلو'},
  {name:'محشي ورق عنب',unit:'كيلو'},
  {name:'سمنه',unit:'كيلو'},
  {name:'طبق تقسيم 3عين',unit:'قطعه'},
  {name:'بطيخ',unit:'واحدة'},
  {name:'فروتي',unit:'كيس'},
  {name:'خوخ',unit:'كيلو'},
  {name:'مشمش',unit:'كيلو'},
  {name:'عنب',unit:'كيلو'},
  {name:'شهد',unit:'كيلو'},
  {name:'شاي 40 جرام',unit:'علبه'},
  {name:'اكياس وجبات باتيه30*38',unit:'كيلو'},
  {name:'بنطلون',unit:'قطعه'},
  {name:'كمامه قماش',unit:'قطعه'},
  {name:'مريله شيف',unit:'قطعه'},
  {name:'مياه معدنيه ص',unit:'زجاجه'},
  {name:'فول جاهز',unit:'علبه'},
  {name:'ينسون',unit:'فتله'},
  {name:'كركديه',unit:'فتله'},
  {name:'نعناع',unit:'فتله'},
  {name:'كاكاو',unit:'علبه'},
  {name:'لبن1.5',unit:'زجاجه'},
  {name:'منين بالعجوه',unit:'كيلو'},
  {name:'فينو',unit:'قطعه'},
  {name:'عجينه طعميه',unit:'كيلو'},
  {name:'لون طعام',unit:'قطعه'},
  {name:'زيتون مخلي',unit:'كيلو'},
  {name:'عيش فرنساوي',unit:'قطعه'},
  {name:'هوت صوص',unit:'علبه'},
  {name:'باسكت بغطاء وسط',unit:'قطعه'},
  {name:'جاتوة',unit:'قطعه'},
  {name:'شوكولاته',unit:'علبه'},
  {name:'مزيل دهون',unit:'زجاجه'},
  {name:'بردقوش',unit:'كيلو'},
  {name:'فانيلبا',unit:'كيس'},
  {name:'كمثري',unit:'كيلو'},
  {name:'تين',unit:'كيلو'},
  {name:'خلطه توابل للكفته',unit:'كيلو'},
  {name:'جيلي',unit:'علبه'},
  {name:'مكرونه بنا',unit:'كيلو'},
  {name:'ورق زبده',unit:'كيلو'},
  {name:'باسكت كبير',unit:'قطعه'},
  {name:'لبن 200 مكس',unit:'علبه'},
  {name:'صوص الباربكيو',unit:'جركن'},
  {name:'حلاوه',unit:'4 كجم'},
  {name:'زيت سائل',unit:'كيلو'},
  {name:'طاجن',unit:'قطعه'},
  {name:'وراك فراخ',unit:'كيلو'},
  {name:'زيت 700 مللى',unit:'700 مللى'},
  {name:'تيشرت ميلتون',unit:'قطعه'},
  {name:'عبد المعبود ساده 100جرام',unit:'كيس'},
  {name:'مربى700 جرام',unit:'قطعه'},
  {name:'حلاوة 1 كيلو',unit:'كيلو'},
  {name:'معلقة استانلس',unit:'قطعه'},
  {name:'تونه قطع',unit:'علبه'},
  {name:'فلتر مياه 5 مراحل',unit:'قطعه'},
  {name:'سمسم',unit:'كيلو'},
  {name:'غطاء علبة شوربة',unit:'قطعه'},
  {name:'صنيه بسبوسه',unit:'علبه'},
  {name:'شوكولاتة خام',unit:'علبه'},
  {name:'مضرب بيض',unit:'قطعه'},
  {name:'كوب قهوه كرتون',unit:'قطعه'},
  {name:'كاكاو خام',unit:'كيلو'},
  {name:'لبن بودرة بالكيلو',unit:'كيلو'},
  {name:'كوب ربل 180 مللى',unit:'قطعه'},
  {name:'غطاء شفاف',unit:'قطعه'},
  {name:'بصل اخضر',unit:'لفة'},
  {name:'بن',unit:'كيلو'},
  {name:'فراوله',unit:'كيلو'},
  {name:'فارماسيه',unit:'كيلو'},
  {name:'كريم شانتيه',unit:'كيلو'},
  {name:'فلامنك',unit:'كيلو'},
  {name:'قرنبيط',unit:'كيلو'},
  {name:'كيوي',unit:'كيلو'},
  {name:'منين ملبن',unit:'كيلو'},
  {name:'عسل جلوكوز',unit:'كيلو'},
  {name:'صابون بيريل 1 لتر',unit:'زجاجه'},
  {name:'خلطة توابل كفتة',unit:'كيس'},
  {name:'كاتشب 200 مل',unit:'زجاجه'},
  {name:'شيش',unit:'كيلو'},
  {name:'كريمة لبانى',unit:'علبه'},
  {name:'برجر',unit:'كيلو'},
  {name:'ذره فشار',unit:'كيلو'},
  {name:'علب فارغه للفشار',unit:'علبه'},
  {name:'جبنه شيدر',unit:'كيلو'},
  {name:'عصير جهينه 235ملل',unit:'علبه'},
  {name:'خله اسنان',unit:'علبه'},
  {name:'شاور يد 5 لتر',unit:'جركن 5 لتر'},
  {name:'جيلاتين',unit:'كيلو'},
  {name:'ولاعة بوتاجاز',unit:'قطعه'},
  {name:'مصفاة',unit:'قطعه'},
  {name:'مكرونه قلم 1/2ك',unit:'كيس'},
  {name:'سميط',unit:'كيلو'},
  {name:'كركاديه',unit:'كيلو'},
  {name:'جرجير',unit:'خضره'},
  {name:'جركن مسطردة',unit:'كيلو'},
  {name:'زبادي المراعي 105جرام',unit:'قطعه'},
  {name:'هوهوز',unit:'علبه'},
  {name:'شفاطه(شاليموه)',unit:'قطعه'},
  {name:'كوب عصير',unit:'قطعه'},
  {name:'كمبود',unit:'علبه'},
  {name:'كريز',unit:'كيلو'},
  {name:'عيش سوري',unit:'قطعه'},
  {name:'ايس كريم',unit:'قطعه'},
  {name:'كانز ستار(كبير)',unit:'علبه'},
  {name:'سمك بلطي',unit:'كيلو'},
  {name:'طعميه',unit:'قطعه'},
  {name:'شيبسي عائلي 5ج',unit:'كيس'},
  {name:'خل هاينز',unit:'زجاجه'},
  {name:'نسكافيه3*1',unit:'كيس'},
  {name:'مولتو 10',unit:'كيس'},
  {name:'شوكلاته كبيره',unit:'قطعه'},
  {name:'مانجو',unit:'كيلو'},
  {name:'بن 250جرام',unit:'كيس'},
  {name:'عصير مانجو',unit:'لتر'},
  {name:'بسكوت لوكر',unit:'قطعه'},
  {name:'شاور يد فيرا 1 لتر',unit:'جركن'},
  {name:'بانيه 900جرام',unit:'علبه'},
  {name:'جركن صابون فيرا 20 لتر',unit:'جركن'},
  {name:'بيتزا',unit:'قطعه'},
  {name:'زيت جركن',unit:'جركن 20 لتر'},
  {name:'خلطه توابل حواوشي',unit:'كيس'},
  {name:'جبنه دومتي 1ك',unit:'علبه'},
  {name:'طبق كبير',unit:'قطعه'},
  {name:'سلطنيه  وسط',unit:'قطعه'},
  {name:'باتون ساليه',unit:'كيلو'},
  {name:'بيتي فور',unit:'كيلو'},
  {name:'مقوار',unit:'قطعه'},
  {name:'مش بلدي',unit:'كيلو'},
  {name:'بوله شوربه',unit:'قطعه'},
  {name:'سويس رول',unit:'قطعه'},
  {name:'بنطلون كحلى',unit:'قطعه'},
  {name:'بدلة شيف الوان',unit:'بدله'},
  {name:'جمبري',unit:'كيلو'},
  {name:'اوراك فراخ',unit:'قطعه'},
  {name:'تورته',unit:'قطعه'},
  {name:'اريال سايب',unit:'كيلو'},
  {name:'سبانخ',unit:'كيس'},
  {name:'صابون 20 لتر',unit:'جركن 20 لتر'},
  {name:'عياشه الوطنيه',unit:'قطعه'},
  {name:'سمك مكريل',unit:'كيلو'},
  {name:'قلايه 2 عين',unit:'قطعه'},
  {name:'عصير دومتي',unit:'علبه'},
  {name:'جمبري 20*30',unit:'كيلو'},
  {name:'معطر بخاخ *6',unit:'قطعه'},
  {name:'زيت 3 لتر',unit:'زجاجه'},
  {name:'عيش ضفاير',unit:'قطعه'},
  {name:'مربي مشمش',unit:'برطمان'},
  {name:'حلويات غربي',unit:'كيلو'},
  {name:'ليمون جنزبيل',unit:'علبه'},
  {name:'طبق حلويات ك',unit:'قطعه'},
  {name:'ورق عنب',unit:'كيس'},
  {name:'بهارات السمك',unit:'كيلو'},
  {name:'رقاق',unit:'لفة'},
  {name:'جبنه ميكس',unit:'كيلو'},
  {name:'كوب زجاج',unit:'قطعه'},
  {name:'معلقه ص استالس',unit:'قطعه'},
  {name:'كاكاا',unit:'كيلو'},
  {name:'بهارات كبسه',unit:'كيلو'},
  {name:'باتيه صيامي',unit:'قطعه'},
  {name:'بهارات فراخ',unit:'كيلو'},
  {name:'ماء  ورد',unit:'زجاجه'},
  {name:'شوكه طعام معدن',unit:'قطعه'},
  {name:'معلقه طعام معدن',unit:'قطعه'},
  {name:'بابريكا مدخنه',unit:'كيلو'},
  {name:'سبع بهارات',unit:'كيلو'},
  {name:'قهوه 35جرام ساده',unit:'كيس'},
];

// ===== SUPABASE =====
let supabase_client = null;
let usingLocalDb = false;
let serverDbCache = {
  meal_logs:[],inventory:[],invoices:[],suppliers:[],purchase_requests:[],returns_waste:[],
  sites:[],warehouses:[],ration_standards:[],quality_checks:[],employees:[],attendance:[],
  equipment:[],maintenance_logs:[]
};
let snapshotSaveTimer = null;
const dirtySnapshotTables = new Set();
const SNAPSHOT_TABLES = ['suppliers','purchase_requests','returns_waste','sites','warehouses','ration_standards'];

async function apiFetch(path, options={}){
  const response=await fetch((IBF_BOOT.restUrl||'')+path,{
    credentials:'same-origin',
    ...options,
    headers:{'Content-Type':'application/json','X-WP-Nonce':IBF_BOOT.nonce||'',...(options.headers||{})}
  });
  const json=await response.json().catch(()=>({message:'استجابة غير صالحة من الخادم'}));
  if(!response.ok) throw new Error(json.message||'تعذر الاتصال بقاعدة البيانات');
  return json;
}

async function loadServerSnapshot(){
  if(!IBF_BOOT.restUrl) return;
  try{
    const result=await apiFetch('snapshot',{method:'GET'});
    serverDbCache={...serverDbCache,...(result.data||{})};
  }catch(error){
    console.error(error);
    showToast('تعذر تحميل البيانات المركزية. أعد فتح الصفحة.','error');
  }
}

async function saveServerSnapshot(){
  if(!IBF_BOOT.restUrl) return;
  try{
    const data={};dirtySnapshotTables.forEach(table=>data[table]=serverDbCache[table]||[]);
    if(!Object.keys(data).length) return;
    await apiFetch('snapshot',{method:'POST',body:JSON.stringify({data})});
    dirtySnapshotTables.clear();
  }catch(error){
    console.error(error);
    showToast('تعذر حفظ آخر تعديل على الخادم','error');
  }
}

function loadLocalDb(){
  if(IBF_BOOT.restUrl) return JSON.parse(JSON.stringify(serverDbCache));
  return JSON.parse(JSON.stringify(serverDbCache));
}
function saveLocalDb(db){
  if(IBF_BOOT.restUrl){
    SNAPSHOT_TABLES.forEach(table=>{
      if(JSON.stringify(serverDbCache[table]||[])!==JSON.stringify(db[table]||[])) dirtySnapshotTables.add(table);
    });
    serverDbCache=db;
    clearTimeout(snapshotSaveTimer);
    snapshotSaveTimer=setTimeout(saveServerSnapshot,250);
    return;
  }
  serverDbCache=db;
}
function notifyLocalChange(){setTimeout(()=>{renderDashboard(); if(currentChef) renderChefPage();}, 0);}
function makeLocalQuery(table){
  const state = {action:null, payload:null, filters:[], orderBy:null, single:false};
  const api = {
    select(){state.action = state.action || 'select'; return api;},
    insert(payload){state.action='insert'; state.payload=payload; return Promise.resolve(runLocalQuery(table,state));},
    update(payload){state.action='update'; state.payload=payload; return api;},
    delete(){state.action='delete'; return api;},
    upsert(payload, options){state.action='upsert'; state.payload=payload; state.options=options||{}; return Promise.resolve(runLocalQuery(table,state));},
    eq(field,value){state.filters.push({field,value}); return api;},
    order(field, opts){state.orderBy={field, ascending:!(opts && opts.ascending===false)}; return api;},
    single(){state.single=true; return Promise.resolve(runLocalQuery(table,state));},
    then(resolve,reject){return Promise.resolve(runLocalQuery(table,state)).then(resolve,reject);}
  };
  return api;
}
function runLocalQuery(table,state){
  const db = loadLocalDb();
  db[table] = db[table] || [];
  const matches = row => state.filters.every(f => String(row[f.field]) === String(f.value));
  if(state.action === 'insert'){
    const rows = Array.isArray(state.payload) ? state.payload : [state.payload];
    const inserted = rows.map(row => ({id: Date.now()+Math.floor(Math.random()*100000), created_at:new Date().toISOString(), ...row}));
    db[table].push(...inserted); saveLocalDb(db); notifyLocalChange();
    return {data: inserted, error:null};
  }
  if(state.action === 'update'){
    const updated = [];
    db[table] = db[table].map(row => {
      if(!matches(row)) return row;
      const next = {...row, ...state.payload};
      updated.push(next);
      return next;
    });
    saveLocalDb(db); notifyLocalChange();
    return {data: updated, error:null};
  }
  if(state.action === 'delete'){
    const before = db[table].length;
    db[table] = db[table].filter(row => !matches(row));
    saveLocalDb(db); notifyLocalChange();
    return {data: {count: before - db[table].length}, error:null};
  }
  if(state.action === 'upsert'){
    const rows = Array.isArray(state.payload) ? state.payload : [state.payload];
    const keys = (state.options.onConflict || 'id').split(',').map(x=>x.trim()).filter(Boolean);
    rows.forEach(row => {
      const idx = db[table].findIndex(existing => keys.every(k => existing[k] === row[k]));
      if(idx >= 0) db[table][idx] = {...db[table][idx], ...row};
      else db[table].push({id: Date.now()+Math.floor(Math.random()*100000), created_at:new Date().toISOString(), ...row});
    });
    saveLocalDb(db); notifyLocalChange();
    return {data: rows, error:null};
  }
  let rows = db[table].filter(matches);
  if(state.orderBy){
    rows = rows.sort((a,b)=>{
      const av=a[state.orderBy.field]||'', bv=b[state.orderBy.field]||'';
      return state.orderBy.ascending ? String(av).localeCompare(String(bv)) : String(bv).localeCompare(String(av));
    });
  }
  if(state.single) return {data: rows[0] || null, error:null};
  return {data: rows, error:null};
}
function createLocalClient(){
  return {
    from: makeLocalQuery,
    channel(){return {on(){return this;}, subscribe(){return this;}};}
  };
}

function makeRemoteQuery(table){
  const state={action:null,payload:null,filters:[],orderBy:null,single:false,options:{}};
  const api={
    select(){state.action=state.action||'select';return api;},
    insert(payload){state.action='insert';state.payload=payload;return runRemoteQuery(table,state);},
    update(payload){state.action='update';state.payload=payload;return api;},
    delete(){state.action='delete';return api;},
    upsert(payload,options){state.action='upsert';state.payload=payload;state.options=options||{};return runRemoteQuery(table,state);},
    eq(field,value){state.filters.push({field,value});return api;},
    order(field,opts){state.orderBy={field,ascending:!(opts&&opts.ascending===false)};return api;},
    single(){state.single=true;return runRemoteQuery(table,state);},
    then(resolve,reject){return runRemoteQuery(table,state).then(resolve,reject);}
  };
  return api;
}

async function runRemoteQuery(table,state){
  try{
    return await apiFetch('query',{method:'POST',body:JSON.stringify({
      table,action:state.action||'select',payload:state.payload,filters:state.filters,
      orderBy:state.orderBy,single:state.single,options:state.options
    })});
  }catch(error){
    return {data:state.single?null:[],error:{message:error.message}};
  }
}

function createWordPressClient(){
  return {
    from:makeRemoteQuery,
    channel(){return {on(){return this;},subscribe(){return this;}};}
  };
}
function safeText(value){return String(value??'').replace(/[&<>"']/g,char=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[char]));}
function ensureMasterData(){
  const db=loadLocalDb();
  if(!Array.isArray(db.sites)||!db.sites.length) db.sites=DEFAULT_MASTER_SITES.map(site=>({...site}));
  if(!Array.isArray(db.warehouses)) db.warehouses=[];
  if(!Array.isArray(db.ration_standards)) db.ration_standards=[];
  if(!Array.isArray(db.suppliers)) db.suppliers=[];
  if(!Array.isArray(db.purchase_requests)) db.purchase_requests=[];
  if(!Array.isArray(db.returns_waste)) db.returns_waste=[];
  saveLocalDb(db);
}
function syncSiteSelect(select,sites,allLabel){
  if(!select)return;const current=select.value;
  select.innerHTML=(allLabel?`<option value="">${allLabel}</option>`:'')+sites.map(site=>`<option value="${safeText(site.name)}">${safeText(site.name)} — ${safeText(site.city)}</option>`).join('');
  if([...select.options].some(option=>option.value===current))select.value=current;
}
function siteKindLabel(site){return site?.isMain?'🏢 مخزن رئيسي':site?.isSupplyOnly?'🚚 توريدات فقط':'🏫 موقع تشغيلي';}
function handleSiteTypeChoice(changed){
  const main=document.getElementById('master-site-main'),supply=document.getElementById('master-site-supply');
  if(changed==='main'&&main?.checked&&supply)supply.checked=false;
  if(changed==='supply'&&supply?.checked&&main)main.checked=false;
}
function renderMasterData(){
  ensureMasterData();const db=loadLocalDb(),sites=db.sites||[],warehouses=db.warehouses||[];
  const siteCount=document.getElementById('master-sites-count'),whCount=document.getElementById('master-warehouses-count'),coldCount=document.getElementById('master-cold-count');
  if(siteCount)siteCount.textContent=sites.length;if(whCount)whCount.textContent=warehouses.length;if(coldCount)coldCount.textContent=warehouses.filter(item=>['مبرد','مجمد'].includes(item.type)).length;
  syncSiteSelect(document.getElementById('master-wh-site'),sites);
  syncSiteSelect(document.getElementById('filter-site'),sites,'كل المواقع');
  syncSiteSelect(document.getElementById('inv-site-filter'),sites,'-- اختر موقع --');
  syncSiteSelect(document.getElementById('manager-equipment-site'),sites,'كل المواقع');
  const prWarehouse=document.getElementById('pr-warehouse');
  if(prWarehouse&&warehouses.length){const current=prWarehouse.value,siteWarehouses=warehouses.filter(item=>!currentUser?.site||item.site===currentUser.site),visibleWarehouses=siteWarehouses.length?siteWarehouses:warehouses;prWarehouse.innerHTML=visibleWarehouses.map(item=>`<option value="${safeText(item.name)}">${safeText(item.name)} — ${safeText(item.site)}</option>`).join('');if([...prWarehouse.options].some(option=>option.value===current))prWarehouse.value=current;}
  const siteBox=document.getElementById('master-sites-table');
  if(siteBox)siteBox.innerHTML=`<div class="master-list">${sites.map(site=>`<div class="master-row"><div><strong>${safeText(site.name)}</strong><span>${safeText(site.city)} · ${safeText(site.client||'بدون عميل')} · ${siteKindLabel(site)}</span></div><code>${safeText(site.code)}</code></div>`).join('')}</div>`;
  const whBox=document.getElementById('master-warehouses-table');
  if(whBox)whBox.innerHTML=warehouses.length?`<div class="master-list">${warehouses.map(item=>`<div class="master-row"><div><strong>${safeText(item.name)}</strong><span>${safeText(item.site)} · ${safeText(item.type)} · ${safeText(item.manager)}</span></div><code>${safeText(item.code)}</code></div>`).join('')}</div>`:'<div class="empty" style="padding:1.5rem"><div>لم يتم تعريف مخازن بعد</div></div>';
}
function addMasterSite(){
  const code=document.getElementById('master-site-code').value.trim().toUpperCase(),name=document.getElementById('master-site-name').value.trim(),city=document.getElementById('master-site-city').value.trim(),client=document.getElementById('master-site-client').value.trim();
  if(!code||!name||!city){showToast('أدخل كود واسم ومدينة الموقع','error');return;}
  const db=loadLocalDb();db.sites=db.sites||[];
  if(db.sites.some(site=>site.code.toLowerCase()===code.toLowerCase()||site.name.toLowerCase()===name.toLowerCase())){showToast('الموقع أو الكود مسجل من قبل','error');return;}
  const isMain=!!document.getElementById('master-site-main')?.checked;
  const isSupplyOnly=!!document.getElementById('master-site-supply')?.checked;
  if(isMain&&isSupplyOnly){showToast('لا يمكن أن يكون الموقع مخزنًا رئيسيًا وموقع توريدات معًا','error');return;}
  if(isMain)db.sites.forEach(site=>{site.isMain=false;});
  db.sites.push({id:'SITE-'+Date.now(),code,name,city,client,isMain,isSupplyOnly});saveLocalDb(db);
  const mainBox=document.getElementById('master-site-main');if(mainBox)mainBox.checked=false;
  const supplyBox=document.getElementById('master-site-supply');if(supplyBox)supplyBox.checked=false;
  ['master-site-code','master-site-name','master-site-city','master-site-client'].forEach(id=>document.getElementById(id).value='');renderMasterData();showToast('✅ تمت إضافة الموقع','success');
}
function addMasterWarehouse(){
  const code=document.getElementById('master-wh-code').value.trim().toUpperCase(),name=document.getElementById('master-wh-name').value.trim(),site=document.getElementById('master-wh-site').value,type=document.getElementById('master-wh-type').value,manager=document.getElementById('master-wh-manager').value.trim(),minTemp=document.getElementById('master-wh-min').value,maxTemp=document.getElementById('master-wh-max').value;
  if(!code||!name||!site||!manager){showToast('أكمل كود واسم وموقع ومسؤول المخزن','error');return;}
  if(minTemp!==''&&maxTemp!==''&&Number(minTemp)>Number(maxTemp)){showToast('أقل درجة حرارة يجب ألا تزيد عن أعلى درجة','error');return;}
  const db=loadLocalDb();db.warehouses=db.warehouses||[];
  if(db.warehouses.some(item=>item.code.toLowerCase()===code.toLowerCase())){showToast('كود المخزن مستخدم من قبل','error');return;}
  db.warehouses.push({id:'WH-'+Date.now(),code,name,site,type,manager,minTemp:minTemp===''?null:Number(minTemp),maxTemp:maxTemp===''?null:Number(maxTemp),active:true});saveLocalDb(db);
  ['master-wh-code','master-wh-name','master-wh-manager','master-wh-min','master-wh-max'].forEach(id=>document.getElementById(id).value='');renderMasterData();showToast('✅ تم تعريف المخزن','success');
}
function getConfig(){
  return {url:'',key:''};
}
// إدارة المستخدمين تفتح داخل التطبيق.
// الخروج إلى wp-admin كان يرمي المدير على profile.php لأن دوره لا يملك edit_posts،
// وووردبريس يوجّه من لا يملكها إلى صفحة الملف الشخصي.
function saveConfig(){
  const overlay=document.getElementById('config-overlay');
  if(overlay)overlay.style.display='none';
  openUserManagement();
}
function openSettings(){
  if(!IBF_BOOT.user?.permissions?.can_manage_users){
    showToast('إدارة المستخدمين متاحة للمدير فقط','error');return;
  }
  openUserManagement();
}
function closeConfig(){document.getElementById('config-overlay').style.display='none';}
function resetConfig(){openSettings();}
function syncManagerUserSites(){
  const list=document.getElementById('manager-user-sites');
  const sites=(loadLocalDb().sites||[]).map(site=>site.name).filter(Boolean);
  if(list)list.innerHTML=sites.map(site=>`<option value="${safeText(site)}"></option>`).join('');
  const picker=document.getElementById('manager-user-sites-list');
  if(picker){
    const checked=new Set(selectedReviewerSites());
    picker.innerHTML=sites.length
      ?sites.map(site=>`<label><input type="checkbox" class="reviewer-site-option" value="${safeText(site)}" ${checked.has(site)?'checked':''}> ${safeText(site)}</label>`).join('')
      :'<span>عرّف المواقع أولًا من «الهيكل التشغيلي وتعريف المخازن».</span>';
  }
}
function selectedReviewerSites(){
  return [...document.querySelectorAll('.reviewer-site-option:checked')].map(box=>box.value);
}
function applyUserRoleDefaults(){
  const role=document.getElementById('manager-user-role')?.value||'ibf_supervisor';
  const siteInput=document.getElementById('manager-user-site');
  document.getElementById('manager-user-meals').checked=role==='ibf_supervisor';
  document.getElementById('manager-user-inventory').checked=role==='ibf_storekeeper';
  document.getElementById('manager-user-invoices').checked=role==='ibf_purchasing';
  document.getElementById('manager-user-quality').checked=['ibf_supervisor','ibf_quality'].includes(role);
  document.getElementById('manager-user-hr').checked=['ibf_supervisor','ibf_hr'].includes(role);
  document.getElementById('manager-user-equipment').checked=['ibf_supervisor','ibf_maintenance'].includes(role);
  const sitesBox=document.getElementById('manager-user-sites-box');
  if(sitesBox){
    sitesBox.style.display=role==='ibf_reviewer'?'':'none';
    if(role==='ibf_reviewer')syncManagerUserSites();
  }
  if(siteInput){
    const globalRole=['ibf_manager','ibf_executive'].includes(role);
    siteInput.placeholder=globalRole?'الموقع اختياري لهذا الدور':'الموقع مطلوب — مثال: القاهرة';
    siteInput.required=!globalRole;
  }
}
function openUserManagement(){
  const section=document.getElementById('user-management-section');
  if(!IBF_BOOT.user?.permissions?.can_manage_users){
    showToast('إدارة المستخدمين متاحة للمدير فقط','error');
    return;
  }
  syncManagerUserSites();
  applyUserRoleDefaults();
  renderUsersPanel();
  if(section)section.scrollIntoView({behavior:'smooth',block:'start'});
}
async function renderUsersPanel(){
  const container=document.getElementById('manager-users-container');
  if(!container||!IBF_BOOT.restUrl)return;
  container.innerHTML='<div class="empty"><div class="icon">👥</div><div>جاري تحميل المستخدمين...</div></div>';
  try{
    const result=await apiFetch('users',{method:'GET'});
    const users=result.data||[];
    container.innerHTML=users.length?`<div style="overflow-x:auto"><table class="inv-table">
      <thead><tr><th>الموظف</th><th>الدور</th><th>الموقع</th><th>البريد</th><th>الصلاحيات الإضافية</th><th>إجراءات</th></tr></thead>
      <tbody>${users.map(user=>{
        const perms=user.permissions||{};
        const permissionLabels=[
          perms.can_submit_meals?'وجبات':'',
          perms.can_edit_inventory?'مخزن':'',
          perms.can_add_invoice?'مشتريات':'',
          perms.can_manage_quality?'جودة':'',
          perms.can_manage_hr?'HR وحضور':'',
          perms.can_manage_equipment?'معدات وصيانة':'',
          perms.can_view_costs?'تكلفة':'',
          perms.can_view_audit_log?'سجل مراجعة':'',
          perms.can_manage_users?'صلاحيات':''
        ].filter(Boolean).join(' · ')||'حسب الدور';
        return `<tr>
          <td><strong>${safeText(user.name)}</strong><div style="font-size:.72rem;color:var(--muted)">${safeText(user.username)}</div></td>
          <td>${safeText(user.roleLabel)}</td>
          <td>${safeText((user.sites&&user.sites.length>1?user.sites.join(' · '):user.site)||'كل المواقع')}</td>
          <td>${safeText(user.email||'—')}</td>
          <td>${safeText(permissionLabels)}</td>
          <td style="white-space:nowrap">${user.id===IBF_BOOT.user?.id
            ? '<span style="font-size:.72rem;color:var(--muted)">حسابك</span>'
            : `<button class="action-btn" onclick="removeManagerUser(${user.id},${safeText(JSON.stringify(user.name))},'disable')" title="سحب الصلاحيات مع الاحتفاظ بالسجل">⛔ تعطيل</button>
               <button class="action-btn danger" onclick="removeManagerUser(${user.id},${safeText(JSON.stringify(user.name))},'delete')" title="حذف الحساب نهائيًا">🗑️ مسح</button>`}</td>
        </tr>`;
      }).join('')}</tbody>
    </table></div>`:'<div class="empty"><div class="icon">👥</div><div>لا توجد حسابات تشغيلية بعد</div></div>';
  }catch(error){
    container.innerHTML=`<div class="empty"><div class="icon">⚠️</div><div>${safeText(error.message)}</div></div>`;
  }
}
// تعطيل أو مسح مستخدم — الخادم يفرض صلاحية إدارة المستخدمين
async function removeManagerUser(userId,userName,mode){
  if(!IBF_BOOT.user?.permissions?.can_manage_users){showToast('إدارة المستخدمين متاحة للمدير فقط','error');return;}
  if(userId===IBF_BOOT.user?.id){showToast('لا يمكنك حذف حسابك أنت','error');return;}

  if(mode==='disable'){
    if(!confirm(`تعطيل حساب "${userName}"؟\n\nسيفقد الوصول للنظام فورًا، ويبقى سجله وحركاته كما هي.\nيمكنك إعادة تفعيله لاحقًا بإسناد دور جديد له.`))return;
  }else{
    if(!confirm(`⚠️ مسح نهائي لحساب "${userName}"\n\nالحساب سيُحذف من ووردبريس ولا يمكن التراجع.\nسجلات العمل السابقة تبقى محفوظة لكن منسوبة للمدير.\n\nلو كنت تريد إيقاف وصوله فقط استخدم «تعطيل» بدلًا من المسح.\n\nمتأكد؟`))return;
  }

  try{
    const result=await apiFetch('users',{method:'DELETE',body:JSON.stringify({id:userId,mode})});
    const record=result.data||{};
    if(mode==='delete'){
      // نسخة من بيانات الحساب المحذوف
      try{
        const payload={app:'I-BUILD Food Operations',version:2,kind:'deleted_user_backup',
          deletedAt:new Date().toISOString(),deletedBy:IBF_BOOT.user?.name||'',data:{user:record}};
        const blob=new Blob([JSON.stringify(payload,null,2)],{type:'application/json;charset=utf-8'});
        const url=URL.createObjectURL(blob),link=document.createElement('a');
        link.href=url;link.download=`I-BUILD_حساب_محذوف_${record.username||userId}.json`;
        document.body.appendChild(link);link.click();link.remove();
        setTimeout(()=>URL.revokeObjectURL(url),1000);
      }catch(e){console.error(e);}
    }
    delete CHEFS[record.username];
    delete SUPERVISORS[record.username];
    await renderUsersPanel();
    renderPermissionsPanel();
    showToast('✅ '+(result.message||'تم التنفيذ'),'success');
  }catch(error){
    showToast('❌ '+error.message,'error');
  }
}
async function createManagerUser(){
  const username=document.getElementById('manager-user-username').value.trim();
  const displayName=document.getElementById('manager-user-name').value.trim();
  const email=document.getElementById('manager-user-email').value.trim();
  const password=document.getElementById('manager-user-password').value;
  const role=document.getElementById('manager-user-role').value;
  const site=document.getElementById('manager-user-site').value.trim();
  const button=document.getElementById('manager-user-create-btn');
  if(!username||!displayName){showToast('أدخل اسم المستخدم واسم الموظف','error');return;}
  if(password.length<10){showToast('كلمة المرور يجب ألا تقل عن 10 أحرف','error');return;}
  const reviewerSiteList=role==='ibf_reviewer'?selectedReviewerSites():[];
  if(role==='ibf_reviewer'&&!reviewerSiteList.length){showToast('اختر المدارس التي يراجعها هذا الحساب','error');return;}
  if(!['ibf_manager','ibf_executive','ibf_reviewer'].includes(role)&&!site){showToast('حدد موقع الموظف','error');return;}
  const payload={
    username,
    display_name:displayName,
    email,
    password,
    role,
    site:site||reviewerSiteList[0]||'',
    sites:reviewerSiteList,
    can_submit_meals:document.getElementById('manager-user-meals').checked,
    can_edit_inventory:document.getElementById('manager-user-inventory').checked,
    can_add_invoice:document.getElementById('manager-user-invoices').checked,
    can_manage_quality:document.getElementById('manager-user-quality').checked,
    can_manage_hr:document.getElementById('manager-user-hr').checked,
    can_manage_equipment:document.getElementById('manager-user-equipment').checked
  };
  button.disabled=true;
  button.textContent='جاري إنشاء الحساب...';
  try{
    const result=await apiFetch('users',{method:'POST',body:JSON.stringify(payload)});
    const user=result.data||{};
    if(role==='ibf_chef')CHEFS[user.username]={name:user.name,site:user.site};
    if(['ibf_supervisor','ibf_storekeeper','ibf_purchasing','ibf_quality','ibf_hr','ibf_maintenance'].includes(role)){
      SUPERVISORS[user.username]={name:user.name,site:user.site};
    }
    ['manager-user-username','manager-user-name','manager-user-email','manager-user-password'].forEach(id=>document.getElementById(id).value='');
    document.getElementById('manager-user-site').value='';
    document.querySelectorAll('.reviewer-site-option:checked').forEach(box=>{box.checked=false;});
    applyUserRoleDefaults();
    await renderUsersPanel();
    renderPermissionsPanel();
    showToast('✅ تم إنشاء حساب '+user.name,'success');
  }catch(error){
    showToast('❌ '+error.message,'error');
  }finally{
    button.disabled=false;
    button.textContent='إنشاء المستخدم';
  }
}
function initSupabase(url,key){
  if(IBF_BOOT.restUrl){
    supabase_client=createWordPressClient();
    usingLocalDb=false;
    if(document.getElementById('conn-status')) document.getElementById('conn-status').textContent='✅ متصل بقاعدة بيانات الشركة';
    return;
  }
  usingLocalDb = false;
  if(!url||!key||!window.supabase){
    supabase_client = createLocalClient();
    usingLocalDb = true;
    if(document.getElementById('conn-status'))
      document.getElementById('conn-status').textContent='✅ وضع محلي آمن: البيانات محفوظة على هذا الجهاز';
    return;
  }
  try{
    supabase_client=window.supabase.createClient(url,key);
  }catch(e){
    supabase_client = createLocalClient();
    usingLocalDb = true;
    if(document.getElementById('conn-status'))
      document.getElementById('conn-status').textContent='⚠️ وضع محلي: تعذر الاتصال بـ Supabase';
    return;
  }
  if(document.getElementById('conn-status'))
    document.getElementById('conn-status').textContent='✅ متصل بقاعدة البيانات';
  supabase_client.channel('meal_logs').on('postgres_changes',{event:'*',schema:'public',table:'meal_logs'},()=>renderDashboard()).subscribe();
}

// ===== BUILD GRIDS =====
function buildGrids(){
  ['breakfast','lunch','dinner'].forEach(meal=>{
    const grid=document.getElementById('grid-'+meal);
    grid.innerHTML=MEALS[meal].map(item=>`
      <div class="item-card">
        <div class="item-info">
          <div class="item-name">${item.name}</div>
          <div class="item-unit">${item.unit}</div>
          ${item.note?`<div class="item-note">${item.note}</div>`:''}
        </div>
        <div class="qty-wrap">
          <button class="qty-btn" onclick="dec(this)">−</button>
          <input class="qty-input" type="number" value="0" min="0" data-item="${item.name}" data-unit="${item.unit}">
          <button class="qty-btn" onclick="inc(this)">+</button>
        </div>
      </div>`).join('');
  });
  // Cleaning grid
  const cleanGrid=document.getElementById('grid-cleaning');
  if(cleanGrid){
    cleanGrid.innerHTML=CLEANING_ITEMS.map(item=>`
      <div class="item-card" style="border-color:rgba(11,143,163,.25);">
        <div class="item-info">
          <div class="item-name">${item.name}</div>
          <div class="item-unit" style="color:var(--blue);">${item.unit}</div>
        </div>
        <div class="qty-wrap">
          <button class="qty-btn" onclick="dec(this)" style="background:rgba(11,143,163,.12);color:var(--blue);">−</button>
          <input class="qty-input" type="number" value="0" min="0" data-item="${item.name}" data-unit="${item.unit}">
          <button class="qty-btn" onclick="inc(this)" style="background:rgba(11,143,163,.12);color:var(--blue);">+</button>
        </div>
      </div>`).join('');
  }
  // Operation supplies grid
  const opGrid=document.getElementById('grid-operation');
  if(opGrid){
    opGrid.innerHTML=OPERATION_ITEMS.map(item=>`
      <div class="item-card" style="border-color:rgba(196,138,26,.3);">
        <div class="item-info">
          <div class="item-name">${item.name}</div>
          <div class="item-unit" style="color:#a9741a;">${item.unit}</div>
        </div>
        <div class="qty-wrap">
          <button class="qty-btn" onclick="dec(this)" style="background:rgba(196,138,26,.12);color:#a9741a;">−</button>
          <input class="qty-input" type="number" value="0" min="0" data-item="${item.name}" data-unit="${item.unit}">
          <button class="qty-btn" onclick="inc(this)" style="background:rgba(196,138,26,.12);color:#a9741a;">+</button>
        </div>
      </div>`).join('');
  }
}

// ===== ADD CUSTOM ITEM =====
function normalizeDigits(value){
  return String(value??'').replace(/[٠-٩]/g,d=>String(d.charCodeAt(0)-0x0660)).replace(/[۰-۹]/g,d=>String(d.charCodeAt(0)-0x06F0)).trim();
}
function addCustomItem(meal){
  const nameEl=document.getElementById('new-item-name-'+meal);
  const unitEl=document.getElementById('new-item-unit-'+meal);
  const name=nameEl.value.trim();
  const unit=unitEl.value.trim()||'قطعة';
  if(!name){showToast('أدخل اسم الصنف','error');return;}
  const grid=document.getElementById('grid-'+meal);
  const div=document.createElement('div');
  div.className='item-card custom-item';
  div.innerHTML=`
    <div class="item-info">
      <div class="item-name">${safeText(name)}</div>
      <div class="item-unit">${safeText(unit)}</div>
      <div class="item-note">✨ صنف مضاف</div>
    </div>
    <div class="qty-wrap">
      <button class="qty-btn" onclick="dec(this)">−</button>
      <input class="qty-input" type="number" value="0" min="0" data-item="${safeText(name)}" data-unit="${safeText(unit)}">
      <button class="qty-btn" onclick="inc(this)">+</button>
    </div>`;
  grid.appendChild(div);
  nameEl.value=''; unitEl.value='';
  showToast(`✅ تم إضافة "${name}"`,'success');
}

// ===== INIT =====
window.addEventListener('DOMContentLoaded',async()=>{
  setupManagerAccess();
  buildGrids();
  const now=new Date();
  if(document.getElementById('today-label'))
    document.getElementById('today-label').textContent=now.toLocaleDateString('ar-EG',{weekday:'long',year:'numeric',month:'long',day:'numeric'});
  if(document.getElementById('filter-date'))
    document.getElementById('filter-date').value=toDateStr(now);
  if(document.getElementById('inv-date'))
    document.getElementById('inv-date').value=toDateStr(now);
  if(document.getElementById('invoice-date'))
    document.getElementById('invoice-date').value=toDateStr(now);
  if(document.getElementById('chef-filter-date'))
    document.getElementById('chef-filter-date').value=toDateStr(now);
  if(document.getElementById('pr-date')) document.getElementById('pr-date').value=toDateStr(now);
  if(document.getElementById('pr-needed-date')) document.getElementById('pr-needed-date').value=toDateStr(new Date(now.getTime()+86400000));
  if(document.getElementById('rw-date')) document.getElementById('rw-date').value=toDateStr(now);
  if(document.getElementById('quality-date')) document.getElementById('quality-date').value=toDateStr(now);
  if(document.getElementById('maintenance-date')) document.getElementById('maintenance-date').value=toDateStr(now);
  if(document.getElementById('supplier-month-filter')) document.getElementById('supplier-month-filter').value=toDateStr(now).slice(0,7);
  const cfg=getConfig();
  document.getElementById('config-overlay').style.display='none';
  initSupabase(cfg.url, cfg.key);
  await loadServerSnapshot();
  ensureMasterData();
  initInlineRecipes();
  renderMasterData();
  renderSuppliersMaster();
  renderReturnWaste();
  applyAuthenticatedSession();
  window.setInterval(async()=>{
    if(document.hidden) return;
    await loadServerSnapshot();
    if(IBF_BOOT.user?.role==='manager'){
      renderDashboard();renderMasterData();renderManagerPurchaseRequests();renderManagerCashboxApprovals();renderManagerReturnsWaste();renderManagerEquipment();
    }else if(IBF_BOOT.user?.role==='chef') renderChefPage();
    else if(IBF_BOOT.user?.role==='reviewer') renderReviewer();
  },20000);
  const url=window.location.href;
  if(document.getElementById('qrcode') && window.QRCode)
    new QRCode(document.getElementById('qrcode'),{text:url,width:110,height:110,colorDark:'#1a2e1a',colorLight:'#ffffff'});
  else if(document.getElementById('qrcode'))
    document.getElementById('qrcode').innerHTML='<div style="font-size:.8rem;color:var(--muted);padding:.8rem;text-align:center">QR غير متاح حالياً</div>';
});

function applyAuthenticatedSession(){
  const user=IBF_BOOT.user;
  if(!user) return;
  document.querySelectorAll('.nav-btn').forEach(button=>button.style.display='none');
  if(user.role==='manager'){
    const button=document.getElementById('btn-manager');if(button)button.style.display='inline-flex';
    openManagerDashboard();
    return;
  }
  if(user.role==='reviewer'){
    const button=document.getElementById('btn-reviewer');
    if(button){button.style.display='inline-flex';button.classList.add('active');}
    document.querySelectorAll('.page').forEach(page=>page.classList.remove('active'));
    document.getElementById('page-reviewer').classList.add('active');
    setupReviewerScreen();
    renderReviewerCost();
    return;
  }
  if(user.role==='chef'){
    currentChef={name:user.name,site:user.site,code:user.username};
    const button=document.getElementById('btn-chef');if(button){button.style.display='inline-flex';button.classList.add('active');}
    document.getElementById('chef-name-display').textContent='👨‍🍳 '+user.name;
    document.getElementById('chef-site-display').textContent='📍 موقع '+user.site;
    document.getElementById('chef-filter-date').value=todayStr();
    document.querySelectorAll('.page').forEach(page=>page.classList.remove('active'));
    document.getElementById('page-chef').classList.add('active');
    renderChefPage();
    return;
  }
  currentUser={name:user.name,site:user.site,code:user.username};
  currentPermissions={...permissionDefaults()};
  const button=document.getElementById('btn-supervisor');if(button){button.style.display='inline-flex';button.classList.add('active');}
  document.getElementById('sup-name-display').textContent='👤 '+user.name;
  document.getElementById('sup-site-display').textContent='📍 موقع '+user.site;
  document.querySelectorAll('.page').forEach(page=>page.classList.remove('active'));
  document.getElementById('page-supervisor').classList.add('active');
  applySupervisorPermissions();
  if(hasPermission('can_manage_quality')) loadQualityChecks();
  if(hasPermission('can_manage_hr')) loadHrData();
}

// ===== شاشة المراجع =====
// عرض فقط للتكلفة وسجل المراجعة، مع تحرير الصلاحيات. كل القراءة محدودة
// بمدارس الحساب على الخادم، فلا يفيد العبث بالفلتر في المتصفح.
let reviewerCostRows=[];
let reviewerAuditRows=[];

function revSetText(id,value){
  const element=document.getElementById(id);
  if(element)element.textContent=value;
}

function reviewerSites(){
  const list=IBF_BOOT.user?.sites;
  if(Array.isArray(list)&&list.length) return list;
  const single=IBF_BOOT.user?.site;
  return single?[single]:[];
}

function setupReviewerScreen(){
  const user=IBF_BOOT.user;
  if(!user) return;
  const sites=reviewerSites();
  revSetText('rev-name-display','🔎 '+user.name);
  revSetText('rev-scope-display',sites.length?'📍 '+sites.join(' · '):'📍 لم تُسنَد لك مدارس بعد');
  const select=document.getElementById('rev-site');
  if(select) select.innerHTML='<option value="">كل مدارسي</option>'+sites.map(site=>`<option value="${safeText(site)}">${safeText(site)}</option>`).join('');
  const today=todayStr();
  const from=document.getElementById('rev-from'), to=document.getElementById('rev-to');
  if(from&&!from.value) from.value=today;
  if(to&&!to.value) to.value=today;
}

function switchReviewerTab(key,button){
  document.querySelectorAll('#page-reviewer .meal-tab').forEach(tab=>tab.classList.remove('active'));
  document.querySelectorAll('#page-reviewer .meal-content').forEach(panel=>panel.classList.remove('active'));
  if(button) button.classList.add('active');
  document.getElementById('rev-panel-'+key)?.classList.add('active');
  if(key==='audit') renderReviewerAudit();
  else if(key==='perms') renderReviewerPermissions();
  else renderReviewerCost();
}

function reviewerRange(){
  const from=document.getElementById('rev-from')?.value||todayStr();
  const to=document.getElementById('rev-to')?.value||from;
  const site=document.getElementById('rev-site')?.value||'';
  return from<=to?{from,to,site}:{from:to,to:from,site};
}

async function renderReviewer(){
  const active=document.querySelector('#page-reviewer .meal-tab.active')?.id||'rev-tab-cost';
  if(active==='rev-tab-audit') return renderReviewerAudit();
  if(active==='rev-tab-perms') return renderReviewerPermissions();
  return renderReviewerCost();
}

async function renderReviewerCost(){
  const box=document.getElementById('rev-cost-table');
  if(!box) return;
  const {from,to,site}=reviewerRange();
  const allowed=reviewerSites();
  box.innerHTML='<div class="empty"><div class="icon">⏳</div><div>جاري حساب التكلفة...</div></div>';

  let inventory=[];
  try{
    const {data}=await supabase_client.from('inventory').select('*');
    inventory=(data||[]).filter(row=>{
      const date=String(row.stock_date||'');
      return date>=from&&date<=to&&(!site||row.site===site);
    });
  }catch(error){
    box.innerHTML=`<div class="empty"><div class="icon">⚠️</div><div>${safeText(error.message)}</div></div>`;
    return;
  }
  let logs=[];
  try{
    // سجل الوجبات لا يأتي مع الـ snapshot، فيُقرأ باستعلام مستقل
    const {data}=await supabase_client.from('meal_logs').select('*');
    logs=(data||[]).filter(log=>{
      const date=String(log.log_date||'');
      return date>=from&&date<=to&&(!site||log.site===site);
    });
  }catch(error){
    logs=[];
  }
  const waste=(loadLocalDb().returns_waste||[]).filter(item=>{
    const date=String(item.date||'');
    return date>=from&&date<=to&&item.type==='waste'&&(!site||item.site===site);
  });

  const bySite=new Map();
  const ensure=name=>{
    if(!bySite.has(name)) bySite.set(name,{site:name,meals:0,students:0,admins:0,dispensedCost:0,wasteCost:0,stockValue:0,items:0});
    return bySite.get(name);
  };
  allowed.filter(name=>!site||name===site).forEach(ensure);
  logs.forEach(log=>{
    const row=ensure(log.site||'');
    row.meals+=Number(log.meals_total)||0;
    row.students+=Number(log.students_count)||0;
    row.admins+=Number(log.admins_count)||0;
  });
  inventory.forEach(item=>{
    const row=ensure(item.site||'');
    row.dispensedCost+=(parseFloat(item.dispensed)||0)*(parseFloat(item.price)||0);
    row.stockValue+=(parseFloat(item.closing_stock)||0)*(parseFloat(item.price)||0);
    row.items++;
  });
  waste.forEach(item=>{
    ensure(item.site||'').wasteCost+=parseFloat(item.totalCost)||0;
  });

  reviewerCostRows=[...bySite.values()].map(row=>{
    const cost=row.dispensedCost+row.wasteCost;
    return {...row,cost,costPerMeal:row.meals>0?cost/row.meals:0};
  }).sort((a,b)=>b.cost-a.cost);

  const totals=reviewerCostRows.reduce((acc,row)=>({
    cost:acc.cost+row.cost,meals:acc.meals+row.meals,waste:acc.waste+row.wasteCost
  }),{cost:0,meals:0,waste:0});
  revSetText('rev-cost-total',totals.cost.toFixed(2));
  revSetText('rev-cost-meals',totals.meals);
  revSetText('rev-cost-per-meal',(totals.meals>0?totals.cost/totals.meals:0).toFixed(2));
  revSetText('rev-cost-waste',totals.waste.toFixed(2));

  box.innerHTML=reviewerCostRows.length?`<div style="overflow-x:auto"><table class="inv-table">
    <thead><tr><th>المدرسة</th><th>طلاب</th><th>إداريين</th><th>وجبات</th><th>تكلفة المنصرف</th><th>تكلفة الهدر</th><th>إجمالي التكلفة</th><th>تكلفة الوجبة</th><th>أصناف</th><th>قيمة المخزن</th></tr></thead>
    <tbody>${reviewerCostRows.map(row=>`<tr>
      <td><strong>${safeText(row.site)}</strong></td>
      <td>${row.students}</td><td>${row.admins}</td><td>${row.meals}</td>
      <td>${row.dispensedCost.toFixed(2)} ج</td>
      <td style="color:${row.wasteCost>0?'var(--red)':'inherit'}">${row.wasteCost.toFixed(2)} ج</td>
      <td><strong>${row.cost.toFixed(2)} ج</strong></td>
      <td>${row.costPerMeal.toFixed(2)} ج</td>
      <td>${row.items}</td>
      <td>${row.stockValue.toFixed(2)} ج</td></tr>`).join('')}
      <tr style="background:rgba(22,131,91,.08)"><td colspan="6"><strong>الإجمالي</strong></td><td><strong>${totals.cost.toFixed(2)} ج</strong></td><td colspan="3"></td></tr>
    </tbody></table></div>`:'<div class="empty"><div class="icon">💰</div><div>لا توجد حركات تكلفة في هذه الفترة</div></div>';
}

async function renderReviewerAudit(){
  const box=document.getElementById('rev-audit-table');
  if(!box) return;
  const {from,to,site}=reviewerRange();
  const action=document.getElementById('rev-audit-action')?.value||'';
  box.innerHTML='<div class="empty"><div class="icon">⏳</div><div>جاري تحميل سجل المراجعة...</div></div>';
  try{
    const params=new URLSearchParams({from,to,limit:'500'});
    if(site) params.set('site',site);
    if(action) params.set('action',action);
    const result=await apiFetch('audit-log?'+params.toString(),{method:'GET'});
    reviewerAuditRows=result.data||[];
    revSetText('rev-audit-count',reviewerAuditRows.length);
    revSetText('rev-audit-total',result.total||0);
    revSetText('rev-audit-actors',new Set(reviewerAuditRows.map(row=>row.actorName)).size);
    box.innerHTML=reviewerAuditRows.length?`<div style="overflow-x:auto"><table class="inv-table">
      <thead><tr><th>التاريخ والوقت</th><th>المنفّذ</th><th>العملية</th><th>القسم</th><th>المرجع</th><th>الموقع</th></tr></thead>
      <tbody>${reviewerAuditRows.map(row=>`<tr>
        <td style="white-space:nowrap">${safeText(row.createdAt)}</td>
        <td><strong>${safeText(row.actorName)}</strong></td>
        <td>${safeText(row.actionLabel)}</td>
        <td>${safeText(row.recordLabel)}</td>
        <td style="font-size:.75rem;color:var(--muted)">${safeText(row.externalId||'—')}</td>
        <td>${safeText(row.site||'عام')}</td></tr>`).join('')}</tbody>
    </table></div>${(result.total||0)>reviewerAuditRows.length?`<div class="data-integrity-note" style="margin-top:.8rem">معروض أحدث ${reviewerAuditRows.length} حركة من إجمالي ${result.total}. ضيّق الفترة لعرض الباقي.</div>`:''}`
      :'<div class="empty"><div class="icon">🧾</div><div>لا توجد حركات في هذه الفترة</div></div>';
  }catch(error){
    reviewerAuditRows=[];
    box.innerHTML=`<div class="empty"><div class="icon">⚠️</div><div>${safeText(error.message)}</div></div>`;
  }
}

async function renderReviewerPermissions(){
  const box=document.getElementById('rev-perms-table');
  if(!box) return;
  if(!IBF_BOOT.user?.permissions?.can_manage_users){
    box.innerHTML='<div class="empty"><div class="icon">🔐</div><div>المدير لم يفتح لك صلاحية إدارة المستخدمين</div></div>';
    return;
  }
  box.innerHTML='<div class="empty"><div class="icon">⏳</div><div>جاري تحميل المستخدمين...</div></div>';
  try{
    const result=await apiFetch('users',{method:'GET'});
    const users=(result.data||[]).filter(user=>user.id!==IBF_BOOT.user?.id);
    box.innerHTML=users.length?`<div style="overflow-x:auto"><table class="inv-table">
      <thead><tr><th>الموظف</th><th>الدور</th><th>الموقع</th><th>وجبات</th><th>مخزن</th><th>مشتريات</th><th>جودة</th><th>HR</th><th>معدات</th><th>حفظ</th></tr></thead>
      <tbody>${users.map(user=>{
        const p=user.permissions||{};
        const cell=(key,checked)=>`<td><input type="checkbox" id="rev-perm-${user.id}-${key}" ${checked?'checked':''}></td>`;
        return `<tr>
          <td><strong>${safeText(user.name)}</strong><div style="font-size:.72rem;color:var(--muted)">${safeText(user.username)}</div></td>
          <td>${safeText(user.roleLabel)}</td>
          <td>${safeText((user.sites&&user.sites.length?user.sites.join(' · '):user.site)||'كل المواقع')}</td>
          ${cell('meals',p.can_submit_meals)}
          ${cell('inventory',p.can_edit_inventory)}
          ${cell('invoice',p.can_add_invoice)}
          ${cell('quality',p.can_manage_quality)}
          ${cell('hr',p.can_manage_hr)}
          ${cell('equipment',p.can_manage_equipment)}
          <td><button class="action-btn" onclick="saveReviewerPermissions(${user.id},${safeText(JSON.stringify(user.username))})">حفظ</button></td>
        </tr>`;
      }).join('')}</tbody>
    </table></div>`:'<div class="empty"><div class="icon">👥</div><div>لا توجد حسابات لعرضها</div></div>';
  }catch(error){
    box.innerHTML=`<div class="empty"><div class="icon">⚠️</div><div>${safeText(error.message)}</div></div>`;
  }
}

async function saveReviewerPermissions(userId,username){
  const read=key=>!!document.getElementById(`rev-perm-${userId}-${key}`)?.checked;
  try{
    const {error}=await supabase_client.from('app_permissions').upsert({
      supervisor_code:username,
      can_submit_meals:read('meals'),
      can_edit_inventory:read('inventory'),
      can_add_invoice:read('invoice'),
      can_manage_quality:read('quality'),
      can_manage_hr:read('hr'),
      can_manage_equipment:read('equipment')
    },{onConflict:'supervisor_code'});
    if(error) throw new Error(error.message);
    showToast('✅ تم حفظ صلاحيات '+username,'success');
    renderReviewerPermissions();
  }catch(error){
    showToast('❌ تعذر حفظ الصلاحيات: '+error.message,'error');
  }
}

function exportReviewerCostExcel(){
  if(!ensureExcelReady()) return;
  if(!reviewerCostRows.length){showToast('لا توجد بيانات تكلفة للتصدير','error');return;}
  const {from,to}=reviewerRange();
  const rows=[['I-BUILD — تقرير تكلفة المدارس (مراجعة)'],[`الفترة: من ${from} إلى ${to}`],[],
    ['المدرسة','طلاب','إداريين','وجبات','تكلفة المنصرف','تكلفة الهدر','إجمالي التكلفة','تكلفة الوجبة','أصناف','قيمة المخزن']];
  reviewerCostRows.forEach(row=>rows.push([row.site,row.students,row.admins,row.meals,
    Number(row.dispensedCost.toFixed(2)),Number(row.wasteCost.toFixed(2)),Number(row.cost.toFixed(2)),
    Number(row.costPerMeal.toFixed(2)),row.items,Number(row.stockValue.toFixed(2))]));
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,XLSX.utils.aoa_to_sheet(rows),'التكلفة');
  XLSX.writeFile(wb,`ibuild-review-cost-${from}_${to}.xlsx`);
}

function exportReviewerAuditExcel(){
  if(!ensureExcelReady()) return;
  if(!reviewerAuditRows.length){showToast('لا توجد حركات للتصدير','error');return;}
  const {from,to}=reviewerRange();
  const rows=[['I-BUILD — سجل المراجعة'],[`الفترة: من ${from} إلى ${to}`],[],
    ['التاريخ والوقت','المنفّذ','العملية','القسم','المرجع','الموقع']];
  reviewerAuditRows.forEach(row=>rows.push([row.createdAt,row.actorName,row.actionLabel,row.recordLabel,row.externalId,row.site||'عام']));
  const wb=XLSX.utils.book_new();
  XLSX.utils.book_append_sheet(wb,XLSX.utils.aoa_to_sheet(rows),'سجل المراجعة');
  XLSX.writeFile(wb,`ibuild-audit-${from}_${to}.xlsx`);
}

function setupManagerAccess(){
  const managerButton = document.getElementById('btn-manager');
  const loginButton = document.getElementById('manager-login-btn');
  const input = document.getElementById('pwd-input');
  if(managerButton) managerButton.addEventListener('click', ()=>{
    showManagerLogin(managerButton);
  });
  if(loginButton) loginButton.addEventListener('click', (event)=>{
    event.preventDefault();
    event.stopPropagation();
    checkPassword();
  });
  if(input) input.addEventListener('keydown', (event)=>{
    if(event.key==='Enter'){
      event.preventDefault();
      checkPassword();
    }
  });
}

// ===== HELPERS =====
function toDateStr(d){
  const year=d.getFullYear();
  const month=String(d.getMonth()+1).padStart(2,'0');
  const day=String(d.getDate()).padStart(2,'0');
  return `${year}-${month}-${day}`;
}
function todayStr(){return toDateStr(new Date());}
function inc(btn){const i=btn.previousElementSibling;i.value=+i.value+1;}
function dec(btn){const i=btn.nextElementSibling;if(+i.value>0)i.value=+i.value-1;}
function showToast(msg,type='success'){const t=document.getElementById('toast');t.textContent=msg;t.className=`toast ${type} show`;setTimeout(()=>t.classList.remove('show'),2800);}

function permissionDefaults(){
  if(IBF_BOOT.user?.permissions) return {...IBF_BOOT.user.permissions};
  return usingLocalDb
    ? {can_submit_meals:true,can_edit_inventory:true,can_add_invoice:true,can_manage_cashbox:true,can_manage_quality:true,can_manage_hr:true,can_manage_equipment:true}
    : {can_submit_meals:false,can_edit_inventory:false,can_add_invoice:false,can_manage_cashbox:false,can_manage_quality:false,can_manage_hr:false,can_manage_equipment:false};
}
async function fetchPermissionFor(code){
  if(code && code===IBF_BOOT.user?.username) return permissionDefaults();
  if(!supabase_client || !code) return permissionDefaults();
  const {data} = await supabase_client.from('app_permissions').select('*').eq('supervisor_code', code).single();
  if(!data) return permissionDefaults();
  return {
    can_submit_meals: !!data.can_submit_meals,
    can_edit_inventory: !!data.can_edit_inventory,
    can_add_invoice: !!data.can_add_invoice,
    can_manage_cashbox: !!data.can_manage_cashbox,
    can_manage_quality: !!data.can_manage_quality,
    can_manage_hr: !!data.can_manage_hr,
    can_manage_equipment: !!data.can_manage_equipment
  };
}
async function loadSupervisorPermissions(){
  currentPermissions = await fetchPermissionFor(currentUser?.code);
  applySupervisorPermissions();
}
function hasPermission(key){return !!currentPermissions[key];}
function requirePermission(key, message){
  if(hasPermission(key)) return true;
  showToast(message || '⚠️ الصلاحية غير مفعلة من المدير','error');
  return false;
}
function setDisabledByPermission(selector, disabled){
  document.querySelectorAll(selector).forEach(el=>{
    el.disabled = disabled;
    el.style.opacity = disabled ? '.45' : '';
    el.style.cursor = disabled ? 'not-allowed' : '';
  });
}
function applySupervisorPermissions(){
  setDisabledByPermission('#meal-breakfast .submit-btn, #meal-lunch .submit-btn, #meal-dinner .submit-btn, #meal-cleaning .submit-btn, #meal-operation .submit-btn', !hasPermission('can_submit_meals'));
  setDisabledByPermission('#meal-inventory input, #meal-inventory button', !hasPermission('can_edit_inventory'));
  setDisabledByPermission('#meal-invoice input, #meal-invoice button', !hasPermission('can_add_invoice'));
  setDisabledByPermission('#meal-purchase-request input, #meal-purchase-request select, #meal-purchase-request textarea, #meal-purchase-request button', !hasPermission('can_add_invoice'));
  setDisabledByPermission('#meal-returns input, #meal-returns select, #meal-returns button', !hasPermission('can_edit_inventory'));
  setDisabledByPermission('#meal-cashbox input, #meal-cashbox select, #meal-cashbox button', !hasPermission('can_manage_cashbox'));
  setDisabledByPermission('#meal-quality input, #meal-quality select, #meal-quality button', !hasPermission('can_manage_quality'));
  setDisabledByPermission('#meal-hr input, #meal-hr select, #meal-hr button', !hasPermission('can_manage_hr'));
  setDisabledByPermission('#meal-equipment input, #meal-equipment select, #meal-equipment textarea, #meal-equipment button', !hasPermission('can_manage_equipment'));
  applyTabVisibility();
}

// كل تبويب وشرط ظهوره. الأدوار المساعدة (HR، الجودة، الصيانة، الخزنة) كانت ترى
// شريط المشرف كاملًا وتفتح على الفطار؛ الآن ترى تبويباتها فقط وتفتح عليها.
function tabPermissionMap(){
  const roleKey=IBF_BOOT.user?.roleKey||'';
  return {
    'breakfast':        hasPermission('can_submit_meals'),
    'lunch':            hasPermission('can_submit_meals'),
    'dinner':           hasPermission('can_submit_meals'),
    'recipe':           hasPermission('can_submit_meals')||hasPermission('can_manage_recipes'),
    'inventory':        hasPermission('can_edit_inventory'),
    'purchase-request': hasPermission('can_add_invoice')||roleKey==='ibf_storekeeper',
    'invoice':          hasPermission('can_add_invoice'),
    'cashbox':          hasPermission('can_manage_cashbox'),
    'returns':          hasPermission('can_edit_inventory'),
    'cleaning':         hasPermission('can_submit_meals'),
    'operation':        hasPermission('can_submit_meals'),
    'quality':          hasPermission('can_manage_quality'),
    'hr':               hasPermission('can_manage_hr'),
    'equipment':        hasPermission('can_manage_equipment'),
    'hr-overview':      hasPermission('can_manage_hr')
  };
}
function applyTabVisibility(){
  const allowed=tabPermissionMap();
  const tabs=[...document.querySelectorAll('#page-supervisor .meal-tab')];
  let firstAllowed=null;
  tabs.forEach(tab=>{
    const match=/switchMeal\('([^']+)'/.exec(tab.getAttribute('onclick')||'');
    if(!match)return;
    const key=match[1];
    const ok=allowed[key]!==false;
    tab.style.display=ok?'':'none';
    if(ok&&!firstAllowed)firstAllowed=tab;
  });
  // إن كان التبويب المفتوح ممنوعًا، افتح أول تبويب مسموح
  const activeTab=tabs.find(tab=>tab.classList.contains('active')&&tab.style.display!=='none');
  if(!activeTab&&firstAllowed)firstAllowed.click();
  if(firstAllowed)document.getElementById('no-tabs-note')?.remove();
  if(!firstAllowed){
    const box=document.getElementById('page-supervisor');
    if(box&&!document.getElementById('no-tabs-note')){
      const note=document.createElement('div');
      note.id='no-tabs-note';
      note.className='data-integrity-note';
      note.textContent='لم يفتح لك المدير أي صلاحية بعد. تواصل معه لتحديد مهامك.';
      box.prepend(note);
    }
  }
}

// ===== LOGIN =====
function showLoginPage(btn){
  document.body.classList.remove('manager-mode');
  document.querySelectorAll('.page').forEach(x=>x.classList.remove('active'));
  document.querySelectorAll('.page').forEach(x=>x.style.display='');
  document.querySelectorAll('.nav-btn').forEach(x=>x.classList.remove('active'));
  document.getElementById('page-login').classList.add('active');
  btn.classList.add('active');
}
function showManagerLogin(btn){
  document.body.classList.remove('manager-mode');
  document.querySelectorAll('.page').forEach(x=>{x.classList.remove('active'); x.style.display='';});
  document.querySelectorAll('.nav-btn').forEach(x=>x.classList.remove('active'));
  document.getElementById('page-manager-login').classList.add('active');
  if(btn) btn.classList.add('active');
  const input=document.getElementById('manager-page-pwd');
  const error=document.getElementById('manager-page-error');
  if(error) error.style.display='none';
  if(input){input.value=''; setTimeout(()=>input.focus(),100);}
}
function checkManagerPagePassword(){
  const input=document.getElementById('manager-page-pwd');
  const error=document.getElementById('manager-page-error');
  const val=(input?.value||'').trim();
  if(MANAGER_PASSWORDS.includes(val)){
    if(error) error.style.display='none';
    openManagerDashboard();
  }else{
    if(error) error.style.display='block';
    if(input){input.value=''; input.focus();}
  }
}
function doLogin(){
  const pwd=document.getElementById('login-pwd').value.trim();
  document.getElementById('login-error').style.display='none';

  // Check manager password
  if(MANAGER_PASSWORDS.includes(pwd)){
    document.getElementById('login-pwd').value='';
    openManagerDashboard();
    return;
  }

  // Check chef password
  const chef=CHEFS[pwd];
  if(chef){
    document.getElementById('login-pwd').value='';
    currentChef=chef;
    document.body.classList.remove('manager-mode');
    document.getElementById('chef-name-display').textContent='👨‍🍳 '+chef.name;
    document.getElementById('chef-site-display').textContent='📍 موقع '+chef.site;
    document.getElementById('chef-filter-date').value=todayStr();
    document.querySelectorAll('.page').forEach(x=>x.classList.remove('active'));
    document.querySelectorAll('.nav-btn').forEach(x=>x.classList.remove('active'));
    document.getElementById('page-chef').classList.add('active');
    document.getElementById('btn-chef').classList.add('active');
    renderChefPage();
    return;
  }

  // Check supervisor password
  const user=SUPERVISORS[pwd];
  if(!user){
    document.getElementById('login-error').style.display='block';
    document.getElementById('login-pwd').value='';
    return;
  }
  document.getElementById('login-pwd').value='';
    currentUser=user;
    currentUser.code=pwd;
    document.body.classList.remove('manager-mode');
  document.getElementById('sup-name-display').textContent='👤 '+user.name;
  document.getElementById('sup-site-display').textContent='📍 موقع '+user.site;
  document.querySelectorAll('.page').forEach(x=>x.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(x=>x.classList.remove('active'));
  document.getElementById('page-supervisor').classList.add('active');
  document.getElementById('btn-supervisor').classList.add('active');
  loadSupervisorPermissions();
}
function doLogout(){
  if(IBF_BOOT.logoutUrl){window.location.href=IBF_BOOT.logoutUrl;return;}
  document.body.classList.remove('manager-mode');
  currentUser=null;
  document.querySelectorAll('.page').forEach(x=>x.classList.remove('active'));
  document.querySelectorAll('.page').forEach(x=>x.style.display='');
  document.querySelectorAll('.nav-btn').forEach(x=>x.classList.remove('active'));
  document.getElementById('page-login').classList.add('active');
  document.getElementById('btn-supervisor').classList.add('active');
}

// ===== MANAGER PASSWORD =====
function requestManagerAccess(btn){
  managerBtn=btn || document.getElementById('btn-manager');
  const overlay=document.getElementById('pwd-overlay');
  const input=document.getElementById('pwd-input');
  const error=document.getElementById('pwd-error');
  if(error) error.style.display='none';
  if(input) input.value='';
  if(overlay) overlay.style.display='flex';
  setTimeout(()=>input && input.focus(),100);
}
function checkPassword(){
  const input=document.getElementById('pwd-input');
  const val=(input?.value||'').trim();
  if(MANAGER_PASSWORDS.includes(val)){
    history.replaceState(null,'',location.pathname);
    openManagerDashboard();
  }else{
    document.getElementById('pwd-error').style.display='block';
    if(input){input.value=''; input.focus();}
  }
}
function closePwdModal(){
  history.replaceState(null,'',location.pathname);
  const overlay=document.getElementById('pwd-overlay');
  if(overlay) overlay.style.display='none';
}
function openManagerDashboard(){
  document.body.classList.add('manager-mode');
  const managerPage = document.getElementById('page-manager');
  const managerButton = document.getElementById('btn-manager');
  document.querySelectorAll('.page').forEach(x=>{x.classList.remove('active'); x.style.setProperty('display','none','important');});
  document.querySelectorAll('.nav-btn').forEach(x=>x.classList.remove('active'));
  if(managerPage){managerPage.classList.add('active'); managerPage.style.setProperty('display','block','important');}
  if(managerButton) managerButton.classList.add('active');
  const overlay=document.getElementById('pwd-overlay');
  if(overlay) overlay.style.setProperty('display','none','important');
  const fd = document.getElementById('filter-date');
  if(fd) fd.value = todayStr();
  Promise.resolve(renderDashboard()).catch(err=>{
    console.error(err);
    showToast('⚠️ الداشبورد فتح، لكن تحميل البيانات محتاج مراجعة الاتصال','error');
  });
  renderPermissionsPanel();
  renderMasterData();
  syncManagerUserSites();
  renderUsersPanel();
  renderManagerEquipment();
  Promise.resolve(renderGeneralAdmin()).catch(err=>console.error(err));
}

// ===== شريط الانتقال السريع للمدير =====
// المدير لا يُضبط له currentUser في applyAuthenticatedSession، وشاشات المشرف تعتمد عليه.
// لذلك نبنيه هنا من IBF_BOOT مع أخذ فلتر الموقع في الداشبورد كموقع فعّال.
function managerEffectiveSite(){
  return document.getElementById('filter-site')?.value||IBF_BOOT.user?.site||'';
}
function ensureManagerIdentity(){
  const user=IBF_BOOT.user;if(!user)return false;
  const site=managerEffectiveSite();
  currentUser={name:user.name,site,code:user.username};
  currentPermissions={...permissionDefaults()};
  return true;
}
function markJump(target){
  document.querySelectorAll('.jump-btn').forEach(button=>{
    button.classList.toggle('active',(button.getAttribute('onclick')||'').includes(`'${target}'`));
  });
}
function showPageOnly(pageId){
  document.body.classList.remove('manager-mode');
  document.body.classList.add('jump-visible');
  document.querySelectorAll('.page').forEach(page=>{
    page.classList.remove('active');
    page.style.setProperty('display','none','important');
  });
  const page=document.getElementById(pageId);
  if(page){page.classList.add('active');page.style.setProperty('display','block','important');}
  window.scrollTo({top:0,behavior:'instant'});
}
function managerJump(target){
  if(!IBF_BOOT.user?.permissions?.can_view_all_sites&&IBF_BOOT.user?.role!=='manager'){
    showToast('التنقل بين الشاشات متاح للمدير فقط','error');return;
  }
  markJump(target);
  if(target==='dashboard'){
    document.body.classList.remove('jump-visible');
    openManagerDashboard();
    return;
  }
  if(target==='admin'){
    document.body.classList.remove('jump-visible');
    openManagerDashboard();
    setTimeout(()=>document.getElementById('general-admin-section')?.scrollIntoView({behavior:'smooth',block:'start'}),120);
    return;
  }
  if(!ensureManagerIdentity())return;
  if(target==='chef'){
    const user=IBF_BOOT.user;
    currentChef={name:user.name,site:managerEffectiveSite(),code:user.username};
    showPageOnly('page-chef');
    const nameBox=document.getElementById('chef-name-display');
    if(nameBox)nameBox.textContent='👨‍🍳 '+user.name;
    const siteBox=document.getElementById('chef-site-display');
    if(siteBox)siteBox.textContent='📍 '+(managerEffectiveSite()||'كل المواقع');
    const dateBox=document.getElementById('chef-filter-date');
    if(dateBox&&!dateBox.value)dateBox.value=todayStr();
    renderChefPage();
    return;
  }
  // شاشات المشرف: الإشراف و HR والجودة
  showPageOnly('page-supervisor');
  const nameBox=document.getElementById('sup-name-display');
  if(nameBox)nameBox.textContent='👤 '+(IBF_BOOT.user?.name||'');
  const siteBox=document.getElementById('sup-site-display');
  if(siteBox)siteBox.textContent='📍 '+(managerEffectiveSite()||'كل المواقع');
  applySupervisorPermissions();
  const tabId={supervisor:'',hr:'tab-hr-overview',quality:'tab-quality'}[target];
  if(tabId){
    const tab=document.getElementById(tabId);
    if(tab)tab.click();
  }
  if(!managerEffectiveSite()){
    showToast('اختر موقعًا من فلتر الداشبورد لعرض بيانات هذه الشاشة','error');
  }
}

// ===== HR OVERVIEW (عرض فقط) =====
let hroQuality=[],hroEquipment=[],hroMaintenance=[],hroEmployees=[],hroAttendance=[];
async function renderHrOverview(){
  if(!supabase_client||!currentUser?.site)return;
  const date=document.getElementById('hro-date')?.value||todayStr();
  // أزرار الرسيت للمدير فقط — الخادم يرفض الحذف من غيره على أي حال
  const isManager=IBF_BOOT.user?.permissions?.can_manage_users||IBF_BOOT.user?.role==='manager';
  const resetRow=document.getElementById('hro-reset-row');
  if(resetRow)resetRow.style.display=isManager?'':'none';
  const resetNote=document.getElementById('hro-reset-note');
  if(resetNote)resetNote.style.display=isManager?'':'none';
  const [employees,attendance,quality,equipment,maintenance]=await Promise.all([
    supabase_client.from('employees').select('*').eq('site',currentUser.site).order('name',{ascending:true}),
    supabase_client.from('attendance').select('*').eq('site',currentUser.site).eq('work_date',date).order('timestamp',{ascending:false}),
    supabase_client.from('quality_checks').select('*').eq('site',currentUser.site).eq('check_date',date).order('created_at',{ascending:false}),
    supabase_client.from('equipment').select('*').eq('site',currentUser.site).order('equipment_name',{ascending:true}),
    supabase_client.from('maintenance_logs').select('*').eq('site',currentUser.site).order('maintenance_date',{ascending:false})
  ]);
  hroEmployees=(employees.data||[]).filter(employee=>employee.active!==false);
  hroAttendance=attendance.data||[];
  hroQuality=quality.data||[];
  hroEquipment=equipment.data||[];
  hroMaintenance=maintenance.data||[];

  // ---- الحضور: آخر حركة زمنيًا لكل عامل تحدد حالته ----
  // نرتب هنا صراحةً بدل الاعتماد على ترتيب الخادم
  const byTime=[...hroAttendance].sort((a,b)=>String(a.timestamp||'').localeCompare(String(b.timestamp||'')));
  const latest={};
  byTime.forEach(record=>{latest[record.employee_id]=record;});
  const rows=hroEmployees.map(employee=>{
    const own=byTime.filter(record=>record.employee_id===employee.id);
    const checkIn=own.find(record=>record.attendance_type==='check_in');
    const checkOut=own.filter(record=>record.attendance_type==='check_out').slice(-1)[0];
    const last=latest[employee.id];
    const state=!last?{key:'absent',label:'لم يسجل',color:'var(--red)'}
      :last.attendance_type==='check_in'?{key:'in',label:'موجود',color:'var(--green)'}
      :{key:'out',label:'انصرف',color:'var(--muted)'};
    return {employee,state,checkIn:checkIn?.time_label||'—',checkOut:checkOut?.time_label||'—'};
  });
  const set=(id,value)=>{const box=document.getElementById(id);if(box)box.textContent=value;};
  set('hro-total',hroEmployees.length);
  set('hro-present',rows.filter(row=>row.state.key==='in').length);
  set('hro-left',rows.filter(row=>row.state.key==='out').length);
  set('hro-absent',rows.filter(row=>row.state.key==='absent').length);

  const attendanceBox=document.getElementById('hro-attendance');
  if(attendanceBox)attendanceBox.innerHTML=rows.length?`<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>العامل</th><th>الكود</th><th>الوظيفة</th><th>الحضور</th><th>الانصراف</th><th>الحالة</th></tr></thead><tbody>${rows.map(row=>`<tr><td><strong>${safeText(row.employee.name)}</strong></td><td>${safeText(row.employee.employee_code||'')}</td><td>${safeText(row.employee.job_title||'—')}</td><td>${safeText(row.checkIn)}</td><td>${safeText(row.checkOut)}</td><td style="color:${row.state.color}"><strong>${row.state.label}</strong></td></tr>`).join('')}</tbody></table></div>`:'<div class="empty"><div>لا يوجد عاملون مسجلون بالموقع</div></div>';

  // ---- الجودة ----
  const labels={food_preparation:'تحضير الطعام',refrigerators:'الثلاجات والتبريد',warehouses:'المخازن',hygiene:'النظافة الشخصية والموقع'};
  const qualityBox=document.getElementById('hro-quality');
  if(qualityBox)qualityBox.innerHTML=hroQuality.length?`<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>النوع</th><th>المكان</th><th>الحرارة</th><th>الصور</th><th>الملاحظات</th><th>المسؤول</th></tr></thead><tbody>${hroQuality.map(check=>`<tr><td><strong>${safeText(labels[check.category]||check.category||'')}</strong></td><td>${safeText(check.area||'')}</td><td>${check.temperature===null||check.temperature===undefined?'—':safeText(String(check.temperature))+'°'}</td><td>${(check.photos||[]).length} صورة</td><td>${safeText(check.notes||'—')}</td><td>${safeText(check.supervisor_name||'')}</td></tr>`).join('')}</tbody></table></div>`:'<div class="empty"><div>لا توجد فحوصات جودة في هذا التاريخ</div></div>';

  // ---- المعدات والصيانة ----
  const needing=hroEquipment.filter(item=>['needs_maintenance','under_maintenance'].includes(item.status));
  const cost=hroMaintenance.reduce((sum,log)=>sum+(parseFloat(log.cost)||0),0);
  set('hro-equipment-total',hroEquipment.length);
  set('hro-equipment-need',needing.length);
  set('hro-maintenance-cost',cost.toFixed(2));
  const equipmentBox=document.getElementById('hro-equipment');
  if(equipmentBox)equipmentBox.innerHTML=hroEquipment.length?`<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>المعدة</th><th>الكود</th><th>المكان</th><th>الحالة</th><th>آخر صيانة</th><th>الصيانة القادمة</th><th>إجمالي التكلفة</th></tr></thead><tbody>${hroEquipment.map(item=>{
    const overdue=item.next_maintenance_date&&String(item.next_maintenance_date)<date;
    return `<tr><td><strong>${safeText(item.equipment_name||'')}</strong></td><td>${safeText(item.equipment_code||'')}</td><td>${safeText(item.location||'—')}</td><td>${equipmentStatusBadge(item.status)}</td><td>${safeText(item.last_maintenance_date||'—')}</td><td style="color:${overdue?'var(--red)':'inherit'}">${safeText(item.next_maintenance_date||'—')}${overdue?' ⚠️':''}</td><td>${Number(item.maintenance_total_cost||0).toFixed(2)} ج</td></tr>`;
  }).join('')}</tbody></table></div>`:'<div class="empty"><div>لا توجد معدات مسجلة بالموقع</div></div>';
}
function exportHrOverviewExcel(){
  if(!ensureExcelReady())return;
  const date=document.getElementById('hro-date')?.value||todayStr();
  if(!hroEmployees.length&&!hroQuality.length&&!hroEquipment.length){showToast('لا توجد بيانات للتصدير','error');return;}
  const wb=XLSX.utils.book_new();
  const byTime=[...hroAttendance].sort((a,b)=>String(a.timestamp||'').localeCompare(String(b.timestamp||'')));
  const latest={};byTime.forEach(record=>{latest[record.employee_id]=record;});
  const att=[['I-BUILD — متابعة HR'],['الموقع: '+(currentUser?.site||''),'التاريخ: '+date],[],['العامل','الكود','الوظيفة','الحضور','الانصراف','الحالة']];
  hroEmployees.forEach(employee=>{
    const own=byTime.filter(r=>r.employee_id===employee.id);
    const checkIn=own.find(r=>r.attendance_type==='check_in');
    const checkOut=own.filter(r=>r.attendance_type==='check_out').slice(-1)[0];
    const record=latest[employee.id];
    att.push([employee.name,employee.employee_code||'',employee.job_title||'',checkIn?.time_label||'—',checkOut?.time_label||'—',
      !record?'لم يسجل':record.attendance_type==='check_in'?'موجود':'انصرف']);
  });
  const ws1=XLSX.utils.aoa_to_sheet(att);ws1['!cols']=[{wch:22},{wch:12},{wch:18},{wch:14},{wch:14},{wch:12}];
  XLSX.utils.book_append_sheet(wb,ws1,'الحضور');
  const labels={food_preparation:'تحضير الطعام',refrigerators:'الثلاجات والتبريد',warehouses:'المخازن',hygiene:'النظافة الشخصية والموقع'};
  const qc=[['فحوصات الجودة'],['التاريخ: '+date],[],['النوع','المكان','الحرارة','عدد الصور','الملاحظات','المسؤول']];
  hroQuality.forEach(c=>qc.push([labels[c.category]||c.category||'',c.area||'',c.temperature??'',(c.photos||[]).length,c.notes||'',c.supervisor_name||'']));
  const ws2=XLSX.utils.aoa_to_sheet(qc);ws2['!cols']=[{wch:20},{wch:18},{wch:10},{wch:10},{wch:30},{wch:18}];
  XLSX.utils.book_append_sheet(wb,ws2,'الجودة');
  const eq=[['المعدات والصيانة'],[],['المعدة','الكود','المكان','الحالة','آخر صيانة','الصيانة القادمة','إجمالي التكلفة']];
  hroEquipment.forEach(item=>eq.push([item.equipment_name||'',item.equipment_code||'',item.location||'',equipmentStatusLabel(item.status),
    item.last_maintenance_date||'',item.next_maintenance_date||'',Number(item.maintenance_total_cost||0).toFixed(2)]));
  const ws3=XLSX.utils.aoa_to_sheet(eq);ws3['!cols']=[{wch:22},{wch:12},{wch:16},{wch:16},{wch:14},{wch:16},{wch:14}];
  XLSX.utils.book_append_sheet(wb,ws3,'المعدات');
  XLSX.writeFile(wb,`iBUILD_متابعة_HR_${currentUser?.site||''}_${date}.xlsx`);
  showToast('✅ تم تنزيل تقرير متابعة HR','success');
}

// ===== CHARTS (SVG داخلي — بدون مكتبات خارجية) =====
function trendHtml(current,previous){
  if(!previous)return'<span class="trend flat">— لا مقارنة</span>';
  const change=((current-previous)/Math.abs(previous))*100;
  if(!Number.isFinite(change))return'<span class="trend flat">— لا مقارنة</span>';
  const rounded=Math.abs(change).toFixed(1);
  if(Math.abs(change)<0.05)return'<span class="trend flat">— ثابت عن أمس</span>';
  return change>0
    ?`<span class="trend up">▲ ${rounded}% عن أمس</span>`
    :`<span class="trend down">▼ ${rounded}% عن أمس</span>`;
}
function barChartSvg(rows,options={}){
  const width=560,rowHeight=34,pad=8;
  const height=Math.max(rows.length*rowHeight+pad*2,60);
  if(!rows.length)return'<div class="empty"><div>لا توجد بيانات للرسم</div></div>';
  const max=Math.max(...rows.map(row=>Math.abs(row.value)),1);
  const labelWidth=Math.min(150,Math.max(...rows.map(row=>String(row.label).length))*9+16);
  const barArea=width-labelWidth-90;
  const bars=rows.map((row,index)=>{
    const y=pad+index*rowHeight;
    const barWidth=Math.max((Math.abs(row.value)/max)*barArea,2);
    const x=width-labelWidth-barWidth;
    const color=row.color||(row.value>=0?'#34d399':'#f87171');
    return `<text x="${width-6}" y="${y+21}" text-anchor="end" fill="#cfe6da" font-size="13" font-weight="700">${safeText(row.label)}</text>
      <rect x="${x}" y="${y+6}" width="${barWidth}" height="20" rx="5" fill="${color}" opacity=".92"/>
      <text x="${x-8}" y="${y+21}" text-anchor="end" fill="#8fa89c" font-size="12">${safeText(row.display??String(row.value))}</text>`;
  }).join('');
  return `<svg viewBox="0 0 ${width} ${height}" style="direction:ltr" role="img" aria-label="${safeText(options.title||'رسم بياني')}">${bars}</svg>`;
}
function stackedBarSvg(rows){
  const width=560,rowHeight=44,pad=10;
  if(!rows.length)return'<div class="empty"><div>لا توجد بيانات للرسم</div></div>';
  const height=rows.length*rowHeight+pad*2;
  const max=Math.max(...rows.map(row=>row.revenue),1);
  const labelWidth=Math.min(140,Math.max(...rows.map(row=>String(row.label).length))*9+16);
  const barArea=width-labelWidth-20;
  const bars=rows.map((row,index)=>{
    const y=pad+index*rowHeight;
    const revenueWidth=Math.max((row.revenue/max)*barArea,2);
    const costWidth=Math.max((row.cost/max)*barArea,0);
    return `<text x="${width-6}" y="${y+16}" text-anchor="end" fill="#cfe6da" font-size="13" font-weight="700">${safeText(row.label)}</text>
      <rect x="${width-labelWidth-revenueWidth}" y="${y+20}" width="${revenueWidth}" height="9" rx="4" fill="#34d399"/>
      <rect x="${width-labelWidth-costWidth}" y="${y+31}" width="${costWidth}" height="9" rx="4" fill="#fb923c"/>`;
  }).join('');
  return `<svg viewBox="0 0 ${width} ${height}" style="direction:ltr" role="img" aria-label="الإيراد مقابل التكلفة">${bars}</svg>
    <div class="chart-legend"><span><i style="background:#34d399"></i>الإيراد</span><span><i style="background:#fb923c"></i>التكلفة</span></div>`;
}

// ===== PROFITABILITY =====
const MEAL_PRICE_KEYS={'فطار':'breakfast','غداء':'lunch','عشاء':'dinner'};
function sitePrices(siteName){
  const site=(loadLocalDb().sites||[]).find(item=>item.name===siteName);
  const prices=site?.prices||{};
  return {breakfast:Number(prices.breakfast)||0,lunch:Number(prices.lunch)||0,dinner:Number(prices.dinner)||0};
}
function mealPriceFor(siteName,mealLabel){
  const key=MEAL_PRICE_KEYS[mealLabel];
  return key?sitePrices(siteName)[key]:0;
}
function syncPriceSiteSelect(){
  const select=document.getElementById('price-site');if(!select)return;
  const current=select.value,sites=allSites();
  select.innerHTML=sites.map(site=>`<option value="${safeText(site.name)}">${safeText(site.name)}${site.isMain?' 🏢':''}</option>`).join('');
  if([...select.options].some(option=>option.value===current))select.value=current;
  loadSitePrices();
}
function loadSitePrices(){
  const name=document.getElementById('price-site')?.value||'';
  const prices=sitePrices(name);
  ['breakfast','lunch','dinner'].forEach(key=>{
    const field=document.getElementById('price-'+key);
    if(field)field.value=prices[key]||'';
  });
}
function saveSitePrices(){
  if(!canTransferStock()&&!IBF_BOOT.user?.permissions?.can_manage_users){showToast('⚠️ ضبط الأسعار للمدير فقط','error');return;}
  const name=document.getElementById('price-site')?.value||'';
  if(!name){showToast('اختر المدرسة أولًا','error');return;}
  const read=key=>{const value=parseFloat(document.getElementById('price-'+key)?.value);return Number.isFinite(value)&&value>=0?value:0;};
  const db=loadLocalDb();db.sites=db.sites||[];
  const index=db.sites.findIndex(site=>site.name===name);
  if(index<0){showToast('المدرسة غير معرفة في البيانات الأساسية','error');return;}
  db.sites[index]={...db.sites[index],prices:{breakfast:read('breakfast'),lunch:read('lunch'),dinner:read('dinner')}};
  saveLocalDb(db);renderProfitability();
  showToast(`✅ تم حفظ أسعار ${name}`,'success');
}
function renderPriceList(){
  const box=document.getElementById('price-list');if(!box)return;
  const sites=allSites().filter(site=>!site.isMain);
  box.innerHTML=sites.length?`<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>المدرسة</th><th>الفطار</th><th>الغداء</th><th>العشاء</th></tr></thead><tbody>${sites.map(site=>{
    const prices=sitePrices(site.name);
    const cell=value=>value>0?`${value.toFixed(2)} ج`:'<span style="color:var(--red)">غير مسعّر</span>';
    return `<tr><td><strong>${safeText(site.name)}</strong></td><td>${cell(prices.breakfast)}</td><td>${cell(prices.lunch)}</td><td>${cell(prices.dinner)}</td></tr>`;
  }).join('')}</tbody></table></div>`:'<div class="empty"><div>لم تُعرَّف مدارس بعد</div></div>';
}
let profitRows=[];
// إجماليات اليوم السابق لمؤشرات الاتجاه
async function previousDayTotals(date,siteFilter){
  const empty={revenue:0,cost:0};
  if(!supabase_client)return empty;
  const previousDate=toDateStr(new Date(new Date(date).getTime()-86400000));
  try{
    let logsQuery=supabase_client.from('meal_logs').select('*').eq('log_date',previousDate);
    if(siteFilter)logsQuery=logsQuery.eq('site',siteFilter);
    const {data:logs}=await logsQuery;
    const {data:inventory}=await supabase_client.from('inventory').select('*').eq('stock_date',previousDate);
    const revenue=(logs||[]).reduce((sum,log)=>sum+((Number(log.meals_total)||0)*mealPriceFor(log.site,log.meal)),0);
    const dispensed=(inventory||[]).filter(row=>!siteFilter||row.site===siteFilter)
      .reduce((sum,row)=>sum+(parseFloat(row.dispensed)||0)*(parseFloat(row.price)||0),0);
    const waste=(loadLocalDb().returns_waste||[])
      .filter(item=>item.date===previousDate&&item.type==='waste'&&(!siteFilter||item.site===siteFilter))
      .reduce((sum,item)=>sum+(parseFloat(item.totalCost)||0),0);
    return {revenue,cost:dispensed+waste};
  }catch(error){console.error(error);return empty;}
}
async function renderProfitability(){
  const table=document.getElementById('pf-table');if(!table)return;
  syncPriceSiteSelect();renderPriceList();
  const date=document.getElementById('filter-date')?.value||todayStr();
  const siteFilter=document.getElementById('filter-site')?.value||'';
  const mainSites=new Set(allSites().filter(site=>site.isMain).map(site=>site.name));

  // الإيراد من سجل الوجبات، والتكلفة من المنصرف والهدر
  const logs=(lastLogs||[]).filter(log=>!siteFilter||log.site===siteFilter);
  let inventoryRows=[];
  if(supabase_client){
    const {data}=await supabase_client.from('inventory').select('*').eq('stock_date',date);
    inventoryRows=(data||[]).filter(row=>!siteFilter||row.site===siteFilter);
  }
  const waste=(loadLocalDb().returns_waste||[]).filter(item=>item.date===date&&(!siteFilter||item.site===siteFilter));
  await loadStoreBalances();

  const bySite=new Map();
  const ensure=site=>{
    if(!bySite.has(site))bySite.set(site,{site,students:0,admins:0,meals:0,soldMeals:0,unpricedMeals:0,revenue:0,dispensedCost:0,wasteCost:0,returnsQty:0,stockValue:0,stockItems:0});
    return bySite.get(site);
  };
  logs.forEach(log=>{
    const row=ensure(log.site||'');
    row.students+=Number(log.students_count)||0;
    row.admins+=Number(log.admins_count)||0;
    const meals=Number(log.meals_total)||0;
    row.meals+=meals;
    const price=mealPriceFor(log.site,log.meal);
    if(price>0){row.revenue+=meals*price;row.soldMeals+=meals;}
    else if(meals>0)row.unpricedMeals+=meals;
  });
  inventoryRows.forEach(row=>{
    const target=ensure(row.site||'');
    target.dispensedCost+=(parseFloat(row.dispensed)||0)*(parseFloat(row.price)||0);
  });
  waste.forEach(item=>{
    const target=ensure(item.site||'');
    if(item.type==='waste')target.wasteCost+=parseFloat(item.totalCost)||0;
    if(item.type==='return')target.returnsQty+=parseFloat(item.qty)||0;
  });
  storeBalances.filter(row=>!siteFilter||row.site===siteFilter).forEach(row=>{
    const target=ensure(row.site||'');
    target.stockValue+=row.closing*row.price;target.stockItems++;
  });

  profitRows=[...bySite.values()].map(row=>{
    const cost=row.dispensedCost+row.wasteCost;
    const profit=row.revenue-cost;
    return {...row,cost,profit,margin:row.revenue>0?(profit/row.revenue)*100:0,
            costPerMeal:row.meals>0?cost/row.meals:0,revenuePerMeal:row.meals>0?row.revenue/row.meals:0,
            isMain:mainSites.has(row.site)};
  }).sort((a,b)=>b.profit-a.profit);

  const schools=profitRows.filter(row=>!row.isMain);
  const totals=profitRows.reduce((acc,row)=>({
    revenue:acc.revenue+row.revenue,cost:acc.cost+row.cost,meals:acc.meals+row.meals,
    sold:acc.sold+row.soldMeals,unpriced:acc.unpriced+row.unpricedMeals,returns:acc.returns+row.returnsQty
  }),{revenue:0,cost:0,meals:0,sold:0,unpriced:0,returns:0});
  const schoolStock=schools.reduce((acc,row)=>({value:acc.value+row.stockValue,items:acc.items+row.stockItems}),{value:0,items:0});
  const profit=totals.revenue-totals.cost;
  const set=(id,value)=>{const box=document.getElementById(id);if(box)box.textContent=value;};
  // مقارنة بأمس لمؤشرات الاتجاه
  const previous=await previousDayTotals(date,siteFilter);
  const withTrend=(id,value,prev)=>{
    const box=document.getElementById(id);if(!box)return;
    box.textContent=typeof value==='number'?value.toFixed(2):value;
    const holder=box.parentElement?.querySelector('.trend');
    if(holder)holder.remove();
    box.insertAdjacentHTML('afterend',trendHtml(typeof value==='number'?value:parseFloat(value)||0,prev));
  };
  withTrend('pf-revenue',totals.revenue,previous.revenue);
  withTrend('pf-cost',totals.cost,previous.cost);
  withTrend('pf-profit',profit,previous.revenue-previous.cost);
  set('pf-margin',(totals.revenue>0?(profit/totals.revenue*100):0).toFixed(1)+'%');
  set('pf-sold',totals.sold);
  set('pf-unpriced',totals.unpriced);
  set('pf-stock-value',schoolStock.value.toFixed(2));
  set('pf-stock-items',schoolStock.items);

  const ranked=schools.filter(row=>row.revenue>0);
  set('pf-best',ranked.length?`${ranked[0].site} — ${ranked[0].margin.toFixed(1)}% (${ranked[0].profit.toFixed(2)} ج)`:'—');
  set('pf-worst',ranked.length>1?`${ranked[ranked.length-1].site} — ${ranked[ranked.length-1].margin.toFixed(1)}% (${ranked[ranked.length-1].profit.toFixed(2)} ج)`:'—');

  // الرسوم البيانية
  const schoolsChart=document.getElementById('pf-chart-schools');
  if(schoolsChart)schoolsChart.innerHTML=barChartSvg(
    schools.filter(row=>row.revenue>0||row.cost>0)
      .map(row=>({label:row.site,value:row.profit,display:`${row.profit.toFixed(0)} ج · ${row.margin.toFixed(0)}%`})),
    {title:'ربحية المدارس'});
  const mixChart=document.getElementById('pf-chart-mix');
  if(mixChart)mixChart.innerHTML=stackedBarSvg(
    schools.filter(row=>row.revenue>0||row.cost>0)
      .map(row=>({label:row.site,revenue:row.revenue,cost:row.cost})));

  const alert=document.getElementById('pf-alert');
  if(alert)alert.textContent=totals.unpriced>0
    ?`⚠️ ${totals.unpriced} وجبة مسجلة بلا سعر بيع — لن تدخل في الإيراد حتى تضبط سعر مدرستها أعلاه.`
    :(totals.sold>0?`✅ كل الوجبات المسجلة اليوم مسعّرة ودخلت في الإيراد.`:'لا توجد وجبات مسجلة في هذا التاريخ.');

  table.innerHTML=profitRows.length?`<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>المدرسة</th><th>طلاب</th><th>إداريين</th><th>وجبات</th><th>مباعة</th><th>سعر البيع/وجبة</th><th>سعر التكلفة/وجبة</th><th>الإيراد</th><th>التكلفة</th><th>الربح</th><th>الهامش</th><th>مرتجعات</th><th>قيمة المخزن</th></tr></thead><tbody>${profitRows.map(row=>`<tr>
      <td><strong>${safeText(row.site)}</strong>${row.isMain?' 🏢':''}</td>
      <td>${row.students}</td><td>${row.admins}</td><td>${row.meals}</td>
      <td>${row.soldMeals}${row.unpricedMeals?` <span style="color:var(--red);font-size:.72rem">(${row.unpricedMeals} بلا سعر)</span>`:''}</td>
      <td>${row.revenuePerMeal.toFixed(2)} ج</td>
      <td>${row.costPerMeal.toFixed(2)} ج</td>
      <td>${row.revenue.toFixed(2)} ج</td>
      <td>${row.cost.toFixed(2)} ج</td>
      <td style="color:${row.profit>=0?'var(--green)':'var(--red)'}"><strong>${row.profit.toFixed(2)} ج</strong></td>
      <td style="color:${row.margin>=0?'var(--green)':'var(--red)'}">${row.margin.toFixed(1)}%</td>
      <td>${row.returnsQty.toFixed(2)}</td>
      <td>${row.stockValue.toFixed(2)} ج</td></tr>`).join('')}
      <tr style="background:rgba(22,131,91,.08)"><td colspan="7"><strong>الإجمالي</strong></td><td><strong>${totals.revenue.toFixed(2)} ج</strong></td><td><strong>${totals.cost.toFixed(2)} ج</strong></td><td><strong>${profit.toFixed(2)} ج</strong></td><td colspan="3"></td></tr>
    </tbody></table></div>`:'<div class="empty"><div>لا توجد بيانات في هذا التاريخ</div></div>';
}
function exportProfitExcel(){
  if(!ensureExcelReady())return;
  if(!profitRows.length){showToast('لا توجد بيانات ربحية','error');return;}
  const date=document.getElementById('filter-date')?.value||todayStr();
  const wb=XLSX.utils.book_new();
  const data=[['I-BUILD — تقرير الربحية'],['التاريخ: '+date],[],
    ['المدرسة','طلاب','إداريين','وجبات','مباعة','بلا سعر','سعر البيع/وجبة','سعر التكلفة/وجبة','الإيراد','التكلفة','الربح','الهامش %','مرتجعات','قيمة المخزن']];
  profitRows.forEach(row=>data.push([row.site,row.students,row.admins,row.meals,row.soldMeals,row.unpricedMeals,
    row.revenuePerMeal.toFixed(2),row.costPerMeal.toFixed(2),row.revenue.toFixed(2),row.cost.toFixed(2),
    row.profit.toFixed(2),row.margin.toFixed(1),row.returnsQty.toFixed(2),row.stockValue.toFixed(2)]));
  const ws=XLSX.utils.aoa_to_sheet(data);
  ws['!cols']=[{wch:18},{wch:8},{wch:9},{wch:9},{wch:9},{wch:10},{wch:14},{wch:16},{wch:12},{wch:12},{wch:12},{wch:10},{wch:10},{wch:14}];
  XLSX.utils.book_append_sheet(wb,ws,'الربحية');
  XLSX.writeFile(wb,`iBUILD_ربحية_${date}.xlsx`);
  showToast('✅ تم تنزيل تقرير الربحية','success');
}

// ===== GENERAL ADMINISTRATION =====
// الرصيد = افتتاحي + وارد + محوَّل إلينا − منصرف − محوَّل منّا
function computeClosing(row){
  return (parseFloat(row.opening_stock)||0)+(parseFloat(row.additions)||0)+(parseFloat(row.transfer_in)||0)
        -(parseFloat(row.dispensed)||0)-(parseFloat(row.transfer_out)||0);
}
// currentPermissions لا تُملأ لدور المدير (تُضبط في مسار المشرف فقط)، فنقرأ من IBF_BOOT مباشرة
function canTransferStock(){
  const permissions=IBF_BOOT.user?.permissions||{};
  return !!(permissions.can_transfer_stock||permissions.can_view_all_sites||IBF_BOOT.user?.role==='manager');
}
let storeBalances=[];
async function loadStoreBalances(){
  if(!supabase_client)return[];
  const {data,error}=await supabase_client.from('inventory').select('*').order('stock_date',{ascending:false});
  if(error){showToast('تعذر تحميل أرصدة المخازن','error');return[];}
  const latest=new Map();
  (data||[]).forEach(row=>{
    const key=(row.site||'')+'|'+normalizeSearchText(row.item_name||'');
    if(!latest.has(key))latest.set(key,row);
  });
  storeBalances=[...latest.values()].map(row=>({
    site:row.site||'',item_name:row.item_name||'',unit:row.unit||'',
    department:row.department||inventoryDepartment(row.item_name),
    price:Number(row.price)||0,markupPercent:Number(row.markup_percent??row.markupPercent)||0,
    salePrice:Number(row.sale_price??row.salePrice)||0,closing:computeClosing(row),stock_date:row.stock_date||''
  }));
  return storeBalances;
}
function allSites(){
  const db=loadLocalDb();
  return (db.sites||[]).slice().sort((a,b)=>(b.isMain?1:0)-(a.isMain?1:0));
}
function syncTransferSiteSelects(){
  const sites=allSites();
  const fill=(id,allLabel,sourceOnly=false)=>{
    const select=document.getElementById(id);if(!select)return;
    const current=select.value;
    const availableSites=sourceOnly?sites.filter(site=>!site.isSupplyOnly):sites;
    select.innerHTML=(allLabel?`<option value="">${allLabel}</option>`:'')+availableSites.map(site=>`<option value="${safeText(site.name)}">${safeText(site.name)}${site.isMain?' 🏢 (الرئيسي)':site.isSupplyOnly?' 🚚 (توريدات)':''}</option>`).join('');
    if([...select.options].some(option=>option.value===current))select.value=current;
  };
  fill('ga-from-site','',true);fill('ga-to-site');fill('ga-filter-site','كل المواقع');
  handleTransferDestination();
}
function renderTransferItemOptions(){
  const site=document.getElementById('ga-from-site')?.value||'';
  const department=document.getElementById('ga-from-dept')?.value||'food';
  const select=document.getElementById('ga-item');if(!select)return;
  const rows=storeBalances.filter(row=>row.site===site&&row.department===department&&row.closing>0)
    .sort((a,b)=>String(a.item_name).localeCompare(String(b.item_name),'ar'));
  select.innerHTML='<option value="">اختر الصنف</option>'+rows.map(row=>`<option value="${safeText(row.item_name)}" data-unit="${safeText(row.unit)}" data-available="${row.closing}" data-price="${row.price}" data-markup="${row.markupPercent||0}" data-sale-price="${row.salePrice||0}">${safeText(row.item_name)} — الرصيد ${row.closing} ${safeText(row.unit)}</option>`).join('');
  selectTransferItem();
}
function selectTransferItem(){
  const option=document.getElementById('ga-item')?.selectedOptions?.[0];
  const field=document.getElementById('ga-available');
  if(field)field.value=option?.value?`${option.dataset.available} ${option.dataset.unit}`:'';
  const purchase=document.getElementById('ga-purchase-price');
  if(purchase&&option?.value)purchase.value=Number(option.dataset.price||0)||'';
  const markup=document.getElementById('ga-profit-percent');
  if(markup&&option?.value)markup.value=Number(option.dataset.markup||0)||'';
  applyTransferMarkup();
}
function selectedSupplyDestination(){
  const name=document.getElementById('ga-to-site')?.value||'';
  return allSites().find(site=>site.name===name&&site.isSupplyOnly)||null;
}
function handleTransferDestination(){
  const supply=selectedSupplyDestination();
  const from=document.getElementById('ga-from-site');
  if(supply&&from){
    const main=allSites().find(site=>site.isMain);
    if(main&&[...from.options].some(option=>option.value===main.name))from.value=main.name;
    from.disabled=true;
  }else if(from){from.disabled=false;}
  ['ga-purchase-price','ga-profit-percent','ga-sale-price'].forEach(id=>{const field=document.getElementById(id);if(field)field.style.display=supply?'':'none';});
  renderTransferItemOptions();
  updateTransferProfitPreview();
}
function applyTransferMarkup(){
  const purchase=Math.max(0,Number(document.getElementById('ga-purchase-price')?.value)||0);
  const markup=Math.max(0,Number(document.getElementById('ga-profit-percent')?.value)||0);
  const sale=document.getElementById('ga-sale-price');
  if(sale)sale.value=purchase>0?(purchase*(1+markup/100)).toFixed(2):'';
  updateTransferProfitPreview();
}
function updateTransferProfitPreview(){
  const box=document.getElementById('ga-profit-preview');if(!box)return;
  const supply=selectedSupplyDestination();
  if(!supply){box.textContent='تحويل مخزني داخلي — لا تُحسب مبيعات.';return;}
  const qty=Math.max(0,Number(document.getElementById('ga-qty')?.value)||0);
  const purchase=Math.max(0,Number(document.getElementById('ga-purchase-price')?.value)||0);
  const markup=Math.max(0,Number(document.getElementById('ga-profit-percent')?.value)||0);
  const sale=Math.max(0,Number(document.getElementById('ga-sale-price')?.value)||0);
  const cost=qty*purchase,revenue=qty*sale,profit=revenue-cost,margin=revenue>0?(profit/revenue)*100:0;
  box.innerHTML=`🚚 توريد إلى <strong>${safeText(supply.name)}</strong> · التكلفة: <strong>${cost.toFixed(2)} ج</strong> · البيع: <strong>${revenue.toFixed(2)} ج</strong> · الربح: <strong style="color:${profit>=0?'var(--green)':'var(--red)'}">${profit.toFixed(2)} ج</strong> · زيادة على التكلفة: <strong>${markup.toFixed(1)}%</strong> · هامش البيع: <strong>${margin.toFixed(1)}%</strong>`;
}
async function upsertInventoryDelta(site,itemName,unit,department,price,field,qty,date){
  const {data}=await supabase_client.from('inventory').select('*').eq('site',site).eq('item_name',itemName).eq('stock_date',date).single();
  const base=data||{site,item_name:itemName,unit,department,price,opening_stock:0,additions:0,dispensed:0,transfer_in:0,transfer_out:0,stock_date:date};
  const row={...base,site,item_name:itemName,unit:base.unit||unit,department:base.department||department,price:Number(base.price)||price||0,stock_date:date};
  row[field]=(parseFloat(row[field])||0)+qty;
  row.closing_stock=computeClosing(row);
  row.last_updated=new Date().toISOString();
  if(data&&data.id)return supabase_client.from('inventory').update(row).eq('id',data.id);
  return supabase_client.from('inventory').insert(row);
}
async function saveStockTransfer(){
  if(!supabase_client){showToast('الاتصال بقاعدة البيانات مش مكتمل','error');return;}
  if(!canTransferStock()){showToast('⚠️ ليس لديك صلاحية تحويل المخزون','error');return;}
  await saveServerSnapshot();
  const fromSite=document.getElementById('ga-from-site').value,toSite=document.getElementById('ga-to-site').value;
  const fromDept=document.getElementById('ga-from-dept').value,toDept=document.getElementById('ga-to-dept').value;
  const option=document.getElementById('ga-item')?.selectedOptions?.[0];
  const name=option?.value||'',unit=option?.dataset.unit||'قطعة',price=Number(option?.dataset.price)||0;
  const available=Number(option?.dataset.available)||0;
  const qty=parseFloat(document.getElementById('ga-qty').value)||0;
  const notes=document.getElementById('ga-notes').value.trim();
  const fromRecord=allSites().find(site=>site.name===fromSite),toRecord=allSites().find(site=>site.name===toSite);
  const supplyOnly=!!toRecord?.isSupplyOnly;
  const purchasePrice=supplyOnly?(parseFloat(document.getElementById('ga-purchase-price').value)||0):price;
  const markupPercent=supplyOnly?(parseFloat(document.getElementById('ga-profit-percent').value)||0):0;
  const salePrice=supplyOnly?(parseFloat(document.getElementById('ga-sale-price').value)||0):0;
  if(!fromSite||!toSite){showToast('حدد الموقع المرسل والمستقبل','error');return;}
  if(fromSite===toSite&&fromDept===toDept){showToast('لا يمكن التحويل إلى نفس المخزن','error');return;}
  if(!name){showToast('اختر الصنف المراد تحويله','error');return;}
  if(qty<=0){showToast('أدخل الكمية المحوَّلة','error');return;}
  if(qty>available){showToast(`الكمية أكبر من الرصيد المتاح (${available} ${unit})`,'error');return;}
  if(fromRecord?.isSupplyOnly){showToast('موقع التوريدات يستقبل البضاعة فقط ولا يرسلها','error');return;}
  if(supplyOnly&&!fromRecord?.isMain){showToast('التوريد يجب أن يخرج من المخزن الرئيسي','error');return;}
  if(supplyOnly&&(purchasePrice<=0||salePrice<=0)){showToast('أدخل سعر الشراء وسعر البيع','error');return;}
  const date=todayStr();
  const out=await upsertInventoryDelta(fromSite,name,unit,fromDept,price,'transfer_out',qty,date);
  if(out?.error){showToast('❌ تعذر الخصم من المصدر: '+out.error.message,'error');return;}
  if(supplyOnly){
    const pricing=await supabase_client.from('inventory').update({markup_percent:markupPercent,sale_price:salePrice,last_updated:new Date().toISOString()}).eq('site',fromSite).eq('item_name',name).eq('stock_date',date);
    if(pricing?.error){await upsertInventoryDelta(fromSite,name,unit,fromDept,price,'transfer_out',-qty,date);showToast('❌ تعذر حفظ نسبة الربح، وتم التراجع عن الخصم','error');return;}
  }
  const into=await upsertInventoryDelta(toSite,name,unit,toDept,purchasePrice,'transfer_in',qty,date);
  if(into?.error){
    await upsertInventoryDelta(fromSite,name,unit,fromDept,price,'transfer_out',-qty,date);
    showToast('❌ تعذر الإضافة للوجهة، وتم التراجع عن الخصم: '+into.error.message,'error');return;
  }
  const user=IBF_BOOT.user;
  const transferResult=await supabase_client.from('stock_transfers').insert({
    id:'TRF-'+Date.now(),date,fromSite,fromDept,toSite,toDept,
    supplyOnly,items:[{name,unit,qty,price:purchasePrice,purchasePrice,markupPercent,salePrice,cost:qty*purchasePrice,revenue:qty*salePrice,profit:qty*(salePrice-purchasePrice)}],notes,
    createdBy:user?.name||user?.username||'الإدارة',createdAt:new Date().toISOString()
  });
  if(transferResult?.error){
    await upsertInventoryDelta(fromSite,name,unit,fromDept,price,'transfer_out',-qty,date);
    await upsertInventoryDelta(toSite,name,unit,toDept,purchasePrice,'transfer_in',-qty,date);
    showToast('❌ تعذر تسجيل التحويل، وتم التراجع عن حركة المخزون: '+transferResult.error.message,'error');return;
  }
  document.getElementById('ga-qty').value='';document.getElementById('ga-notes').value='';
  await renderGeneralAdmin();
  showToast(`✅ تم تحويل ${qty} ${unit} من "${name}" — ${fromSite} ← ${toSite}`,'success');
}
async function loadStockTransfers(){
  if(!supabase_client)return[];
  const {data}=await supabase_client.from('stock_transfers').select('*').order('createdAt',{ascending:false});
  return data||[];
}
async function printSupplyTransfer(id){
  if(!supabase_client)return;
  const {data:transfer,error}=await supabase_client.from('stock_transfers').select('*').eq('id',id).single();
  if(error||!transfer||!transfer.supplyOnly){showToast('تعذر تحميل إذن التوريد','error');return;}
  const item=(transfer.items||[])[0]||{},popup=window.open('','_blank','width=760,height=680');
  if(!popup){showToast('اسمح بالنوافذ المنبثقة للطباعة','error');return;}
  popup.document.write(`<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>فاتورة توريد ${safeText(transfer.id)}</title><style>body{font-family:Arial;padding:32px;color:#17251f}h1{color:#16835b}table{width:100%;border-collapse:collapse;margin:24px 0}th,td{border:1px solid #ccd8d2;padding:10px;text-align:right}.total{border:2px solid #16835b;border-radius:10px;padding:15px;font-size:20px}.sign{margin-top:60px;display:grid;grid-template-columns:1fr 1fr;gap:50px}.sign div{border-top:1px solid #333;padding-top:8px;text-align:center}@media print{body{padding:10px}}</style></head><body><h1>I-BUILD — فاتورة توريد</h1><p>رقم الفاتورة: ${safeText(transfer.id)} · التاريخ: ${safeText(transfer.date)}</p><p>العميل / موقع التوريدات: <strong>${safeText(transfer.toSite)}</strong></p><table><thead><tr><th>الصنف</th><th>الكمية</th><th>سعر البيع</th><th>الإجمالي</th></tr></thead><tbody><tr><td>${safeText(item.name)}</td><td>${Number(item.qty||0)} ${safeText(item.unit||'')}</td><td>${Number(item.salePrice||0).toFixed(2)} ج</td><td><strong>${Number(item.revenue||0).toFixed(2)} ج</strong></td></tr></tbody></table><div class="total">إجمالي الفاتورة: <strong>${Number(item.revenue||0).toFixed(2)} جنيه</strong></div><p>ملاحظات: ${safeText(transfer.notes||'—')}</p><div class="sign"><div>مسؤول التوريد</div><div>المستلم</div></div><script>window.onload=()=>window.print()<\/script></body></html>`);popup.document.close();
}
async function renderGeneralAdmin(){
  const box=document.getElementById('ga-stores-table');if(!box)return;
  syncManagerInventoryUploadSites();
  syncTransferSiteSelects();
  await loadStoreBalances();
  renderTransferItemOptions();
  const transfers=await loadStockTransfers();
  const siteFilter=document.getElementById('ga-filter-site')?.value||'';
  const deptFilter=document.getElementById('ga-filter-dept')?.value||'';
  const rows=storeBalances.filter(row=>(!siteFilter||row.site===siteFilter)&&(!deptFilter||row.department===deptFilter));

  const stores=new Map();
  rows.forEach(row=>{
    const key=row.site+'|'+row.department;
    if(!stores.has(key))stores.set(key,{site:row.site,department:row.department,items:0,qty:0,value:0});
    const store=stores.get(key);store.items++;store.qty+=row.closing;store.value+=row.closing*row.price;
  });
  const storeRows=[...stores.values()].sort((a,b)=>b.value-a.value);
  const totalValue=storeRows.reduce((sum,store)=>sum+store.value,0);
  const mainSiteNames=new Set(allSites().filter(site=>site.isMain).map(site=>site.name));

  document.getElementById('ga-stores-count').textContent=storeRows.length;
  document.getElementById('ga-items-count').textContent=rows.length;
  document.getElementById('ga-total-value').textContent=totalValue.toLocaleString('ar-EG',{maximumFractionDigits:0});
  document.getElementById('ga-transfers-count').textContent=transfers.length;
  const supplyTotals=transfers.filter(transfer=>transfer.supplyOnly).reduce((totals,transfer)=>{
    const item=(transfer.items||[])[0]||{};
    totals.cost+=Number(item.cost)||0;totals.revenue+=Number(item.revenue)||0;totals.profit+=Number(item.profit)||0;
    return totals;
  },{cost:0,revenue:0,profit:0});
  document.getElementById('ga-supply-revenue').textContent=supplyTotals.revenue.toFixed(2);
  document.getElementById('ga-supply-cost').textContent=supplyTotals.cost.toFixed(2);
  const supplyProfitBox=document.getElementById('ga-supply-profit');
  supplyProfitBox.textContent=supplyTotals.profit.toFixed(2);supplyProfitBox.style.color=supplyTotals.profit>=0?'var(--green)':'var(--red)';

  box.innerHTML=storeRows.length?`<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>الموقع</th><th>القسم</th><th>عدد الأصناف</th><th>إجمالي الكمية</th><th>القيمة</th></tr></thead><tbody>${storeRows.map(store=>`<tr><td><strong>${safeText(store.site)}</strong>${mainSiteNames.has(store.site)?' 🏢':''}</td><td>${departmentLabel(store.department)}</td><td>${store.items}</td><td>${store.qty.toFixed(2)}</td><td><strong>${store.value.toFixed(2)} ج</strong></td></tr>`).join('')}<tr style="background:rgba(22,131,91,.08)"><td colspan="4"><strong>الإجمالي</strong></td><td><strong>${totalValue.toFixed(2)} ج</strong></td></tr></tbody></table></div>`:'<div class="empty"><div>لا توجد أرصدة مسجلة</div></div>';

  const list=document.getElementById('ga-transfers-list');
  if(list)list.innerHTML=transfers.length?`<div class="master-list">${transfers.slice(0,40).map(transfer=>{
    const item=(transfer.items||[])[0]||{};
    const supplyDetails=transfer.supplyOnly?` · شراء ${Number(item.purchasePrice||0).toFixed(2)} · بيع ${Number(item.salePrice||0).toFixed(2)} · ربح ${Number(item.profit||0).toFixed(2)} ج`:'';
    return `<div class="master-row"><div><strong>${transfer.supplyOnly?'🚚':'🔄'} ${safeText(item.name||'')}</strong><span>${safeText(transfer.date)} · ${safeText(transfer.fromSite)} (${departmentLabel(transfer.fromDept)}) ← ${safeText(transfer.toSite)} (${departmentLabel(transfer.toDept)}) · ${safeText(transfer.createdBy)}${supplyDetails}</span></div><div><code>${item.qty||0} ${safeText(item.unit||'')}</code>${transfer.supplyOnly?` <button class="action-btn" onclick='printSupplyTransfer(${safeText(JSON.stringify(transfer.id))})'>🖨️ إذن صرف</button>`:''}</div></div>`;
  }).join('')}</div>`:'<div class="empty"><div>لا توجد تحويلات مسجلة</div></div>';
}

function syncManagerInventoryUploadSites(){
  const select=document.getElementById('ga-inventory-upload-site'),date=document.getElementById('ga-inventory-upload-date');
  if(!select)return;
  const card=document.getElementById('manager-inventory-upload-card');
  if(card)card.style.display=IBF_BOOT.user?.permissions?.can_edit_inventory?'block':'none';
  const current=select.value;
  select.innerHTML='<option value="">اختر الموقع</option>'+allSites().map(site=>`<option value="${safeText(site.name)}">${safeText(site.name)} — ${site.isMain?'مخزن رئيسي':site.isSupplyOnly?'توريدات':'موقع تشغيلي'}</option>`).join('');
  if([...select.options].some(option=>option.value===current))select.value=current;
  if(date&&!date.value)date.value=todayStr();
}
async function exportGeneralAdminExcel(){
  if(!ensureExcelReady())return;
  await loadStoreBalances();
  const transfers=await loadStockTransfers();
  const siteFilter=document.getElementById('ga-filter-site')?.value||'';
  const deptFilter=document.getElementById('ga-filter-dept')?.value||'';
  const rows=storeBalances.filter(row=>(!siteFilter||row.site===siteFilter)&&(!deptFilter||row.department===deptFilter));
  if(!rows.length){showToast('لا توجد أرصدة مطابقة للفلتر','error');return;}
  const wb=XLSX.utils.book_new();
  const stock=[['I-BUILD — أرصدة كل المخازن'],['الموقع: '+(siteFilter||'الكل'),'القسم: '+(deptFilter?departmentLabel(deptFilter):'الكل')],[],['الموقع','القسم','الصنف','الوحدة','الرصيد','سعر الوحدة','القيمة','آخر تاريخ']];
  let total=0;
  rows.forEach(row=>{const value=row.closing*row.price;total+=value;stock.push([row.site,departmentLabel(row.department),row.item_name,row.unit,row.closing.toFixed(2),row.price.toFixed(2),value.toFixed(2),row.stock_date]);});
  stock.push([]);stock.push(['','','','','','إجمالي القيمة',total.toFixed(2)+' جنيه']);
  const ws=XLSX.utils.aoa_to_sheet(stock);
  ws['!cols']=[{wch:16},{wch:12},{wch:24},{wch:10},{wch:12},{wch:12},{wch:14},{wch:14}];
  XLSX.utils.book_append_sheet(wb,ws,'أرصدة المخازن');
  const log=[['سجل التحويلات والتوريدات'],[],['التاريخ','من موقع','من قسم','إلى موقع','إلى قسم','النوع','الصنف','الوحدة','الكمية','سعر الشراء','سعر البيع','التكلفة','المبيعات','الربح','ملاحظات','نفّذها']];
  transfers.forEach(transfer=>{const item=(transfer.items||[])[0]||{};log.push([transfer.date,transfer.fromSite,departmentLabel(transfer.fromDept),transfer.toSite,departmentLabel(transfer.toDept),transfer.supplyOnly?'توريد بسعر بيع':'تحويل داخلي',item.name||'',item.unit||'',item.qty||0,item.purchasePrice||item.price||0,item.salePrice||0,item.cost||0,item.revenue||0,item.profit||0,transfer.notes||'',transfer.createdBy||'']);});
  const ws2=XLSX.utils.aoa_to_sheet(log);
  ws2['!cols']=[{wch:12},{wch:16},{wch:12},{wch:16},{wch:12},{wch:16},{wch:22},{wch:10},{wch:10},{wch:12},{wch:12},{wch:14},{wch:14},{wch:14},{wch:24},{wch:18}];
  XLSX.utils.book_append_sheet(wb,ws2,'التحويلات');
  XLSX.writeFile(wb,`iBUILD_أرصدة_المخازن_${todayStr()}.xlsx`);
  showToast('✅ تم تنزيل كشف أرصدة المخازن','success');
}

// ===== MENU & RECIPE =====
function renderRationSelector(){
  const meal=document.getElementById('recipe-meal')?.value||'breakfast';
  const container=document.getElementById('ration-selector');
  if(!container) return;
  const standards=getRationStandards(meal);
  const fixed=standards.filter(standard=>!standard.choiceGroup),groups=new Map();
  standards.filter(standard=>standard.choiceGroup).forEach(standard=>{
    if(!groups.has(standard.choiceGroup))groups.set(standard.choiceGroup,{label:standard.choiceGroupLabel||'مجموعة بدائل',optional:!!standard.choiceOptional,items:[]});
    groups.get(standard.choiceGroup).items.push(standard);
  });
  const fixedHtml=fixed.length?`<div class="ration-section-title"><span>✅ الأصناف الأساسية</span><small>يمكن إلغاء أي صنف غير مطلوب</small></div>${fixed.map(standard=>`
    <label class="ration-option">
      <input type="checkbox" data-ration-id="${safeText(standard.id)}" checked>
      <div><strong>${safeText(standard.dishName)}</strong><span>${standard.components.length} مكونات · ${safeText(standard.code)}${standard.isDefault?' · أساسي':' · مضاف'}</span></div>
    </label>`).join('')}`:'';
  const groupsHtml=[...groups.entries()].map(([groupKey,group],groupIndex)=>`
    <div class="ration-choice-group">
      <div class="ration-choice-title"><strong>🔁 ${safeText(group.label)}</strong><span>${group.optional?'اختياري ويمكن تركه بدون اختيار':'اختر بديلًا واحدًا فقط'}</span></div>
      <div class="ration-choice-options">${group.items.map((standard,index)=>`
        <label class="ration-option alternative">
          <input type="${group.optional?'checkbox':'radio'}" ${group.optional?'':`name="ration-choice-${meal}-${groupIndex}"`} data-choice-group="${safeText(groupKey)}" data-ration-id="${safeText(standard.id)}" ${!group.optional&&index===0?'checked':''}>
          <div><strong>${safeText(standard.dishName)}</strong><span>${standard.components.length} مكونات · ${safeText(standard.code)} · بديل</span></div>
        </label>`).join('')}</div>
    </div>`).join('');
  container.innerHTML=fixedHtml+groupsHtml;
  generatedRecipe=[];
  generatedMenuDishes=[];
  const tbody=document.getElementById('recipe-tbody');
  if(tbody) tbody.innerHTML='<tr><td colspan="6" style="text-align:center;color:var(--muted);padding:2rem;">اختر الأصناف ثم اضغط توليد الريسيبي</td></tr>';
}
function normalizeRecipeTotal(total,unit){
  if(unit==='جم') return {quantity:total/1000,unit:'كيلو'};
  if(unit==='مل') return {quantity:total/1000,unit:'لتر'};
  return {quantity:total,unit};
}
function generateRecipe(){
  const meal=document.getElementById('recipe-meal').value;
  const servings=parseInt(document.getElementById('recipe-servings').value)||0;
  if(servings<1){showToast('أدخل عدد وجبات صحيح','error');return;}
  const selectedIds=[...document.querySelectorAll('#ration-selector input:checked')].map(input=>input.dataset.rationId);
  if(!selectedIds.length){showToast('اختر صنفًا واحدًا على الأقل','error');return;}
  const standards=getRationStandards(meal).filter(standard=>selectedIds.includes(String(standard.id)));
  generatedMenuDishes=standards.map(standard=>standard.dishName);
  const aggregate={};
  standards.forEach(standard=>standard.components.forEach(component=>{
    const key=`${component.name}|${component.unit}|${component.warehouseType}`;
    const base=component.perPerson*servings,loss=base*(component.lossPercent||0)/100;
    if(!aggregate[key])aggregate[key]={name:component.name,unit:component.unit,roundUp:!!component.roundUp,warehouseType:component.warehouseType,perPerson:0,baseQuantity:0,lossQuantityRaw:0,totalRaw:0,dishNames:[]};
    aggregate[key].perPerson+=component.perPerson;aggregate[key].baseQuantity+=base;aggregate[key].lossQuantityRaw+=loss;aggregate[key].totalRaw+=base+loss;
    if(!aggregate[key].dishNames.includes(standard.dishName))aggregate[key].dishNames.push(standard.dishName);
  }));
  generatedRecipe=Object.values(aggregate).map(item=>{
    const total=normalizeRecipeTotal(item.roundUp?Math.ceil(item.totalRaw):item.totalRaw,item.unit),loss=normalizeRecipeTotal(item.lossQuantityRaw,item.unit);
    return {...item,servings,totalQuantity:total.quantity,totalUnit:total.unit,lossQuantity:loss.quantity,lossUnit:loss.unit};
  });
  document.getElementById('recipe-tbody').innerHTML=generatedRecipe.map(item=>`<tr>
    <td><strong>${item.dishNames.map(safeText).join(' / ')}</strong></td><td>${safeText(item.name)}</td><td>${item.perPerson.toLocaleString('ar-EG',{maximumFractionDigits:3})} ${safeText(item.unit)}</td>
    <td>${item.lossQuantity.toLocaleString('ar-EG',{maximumFractionDigits:3})} ${safeText(item.lossUnit)}</td><td class="inv-cost">${item.totalQuantity.toLocaleString('ar-EG',{maximumFractionDigits:3})} ${safeText(item.totalUnit)}</td><td><span class="tag">${safeText(item.warehouseType)}</span></td>
  </tr>`).join('');
  const mealName={breakfast:'الفطار',lunch:'الغداء',dinner:'العشاء'}[meal];
  const summary=document.getElementById('recipe-summary');
  summary.style.display='block';
  summary.innerHTML=`<div class="info-box-title" style="color:var(--green)">✅ تم توليد الريسيبي</div><strong>${safeText(document.getElementById('recipe-menu-name').value||'منيو التشغيل')}</strong><div style="color:var(--muted);font-size:.85rem;margin-top:.4rem">${mealName} · ${servings.toLocaleString('ar-EG')} وجبة · ${generatedMenuDishes.length} أصناف منيو · ${generatedRecipe.length} مكونات مجمعة · شامل الفاقد</div>`;
  showToast('✅ تم حساب الريسيبي وكميات الصرف','success');
}
function prepareRecipeForIssue(){
  if(!generatedRecipe.length){generateRecipe();if(!generatedRecipe.length)return;}
  const meal=document.getElementById('recipe-meal').value,grid=document.getElementById('grid-'+meal);
  generatedRecipe.forEach(item=>{
    let input=[...grid.querySelectorAll('input.qty-input')].find(field=>field.dataset.item===item.name);
    if(!input){
      const card=document.createElement('div');card.className='item-card custom-item';
      card.innerHTML=`<div class="item-info"><div class="item-name">${safeText(item.name)}</div><div class="item-unit">${safeText(item.totalUnit)}</div><div class="item-note">من الريسيبي المولد</div></div><div class="qty-wrap"><button class="qty-btn" onclick="dec(this)">−</button><input class="qty-input" type="number" value="0" min="0" step="0.001" data-item="${safeText(item.name)}" data-unit="${safeText(item.totalUnit)}"><button class="qty-btn" onclick="inc(this)">+</button></div>`;
      grid.appendChild(card);input=card.querySelector('input');
    }
    input.value=Number(item.totalQuantity.toFixed(3));input.dataset.unit=item.totalUnit;
  });
  document.getElementById('meals-count-'+meal).value=document.getElementById('recipe-servings').value;
  const tab=[...document.querySelectorAll('.meal-tab')].find(button=>button.getAttribute('onclick')?.includes(`'${meal}'`));
  if(tab)switchMeal(meal,tab);showToast('✅ تم تجهيز كميات الصرف من الريسيبي','success');
}
async function saveGeneratedMenu(){
  if(!currentUser){showToast('سجل دخول المشرف أولاً','error');return;}
  if(!generatedRecipe.length) generateRecipe();
  if(!generatedRecipe.length) return;
  const meal=document.getElementById('recipe-meal').value;
  const date=document.getElementById('recipe-date').value||todayStr();
  const name=document.getElementById('recipe-menu-name').value.trim()||'منيو التشغيل';
  const servings=parseInt(document.getElementById('recipe-servings').value)||0;
  const {error}=await supabase_client.from('menus').insert({name,meal_key:meal,service_date:date,servings,site:currentUser.site,supervisor_name:currentUser.name,dishes:generatedMenuDishes,recipe:generatedRecipe,status:'تم توليد الريسيبي'});
  if(error){showToast('تعذر حفظ المنيو: '+error.message,'error');return;}
  showToast('✅ تم حفظ المنيو والريسيبي','success');
}
function exportRecipeExcel(){
  if(!generatedRecipe.length){showToast('ولّد الريسيبي أولاً','error');return;}
  if(!ensureExcelReady()) return;
  const meal=document.getElementById('recipe-meal').value;
  const servings=parseInt(document.getElementById('recipe-servings').value)||0;
  const data=[['I-BUILD قطاع التغذية - الريسيبي'],['المنيو',document.getElementById('recipe-menu-name').value],['التاريخ',document.getElementById('recipe-date').value],['الوجبة',meal],['عدد الوجبات',servings],['أصناف المنيو',generatedMenuDishes.join(' / ')],[],['صنف المنيو','المكون','مقنن الفرد','وحدة المقنن','فاقد التشغيل','إجمالي المطلوب','الوحدة','المخزن']];
  generatedRecipe.forEach(item=>data.push([item.dishNames.join(' / '),item.name,item.perPerson,item.unit,item.lossQuantity+' '+item.lossUnit,item.totalQuantity,item.totalUnit,item.warehouseType]));
  const wb=XLSX.utils.book_new(),ws=XLSX.utils.aoa_to_sheet(data);
  ws['!cols']=[{wch:28},{wch:24},{wch:14},{wch:14},{wch:18},{wch:18},{wch:12},{wch:14}];
  XLSX.utils.book_append_sheet(wb,ws,'الريسيبي');
  XLSX.writeFile(wb,`I-BUILD_ريسيبي_${document.getElementById('recipe-date').value||todayStr()}.xlsx`);
}

// ===== QUALITY EVIDENCE =====
function previewQualityPhotos(input){
  qualityPreviewUrls.forEach(url=>URL.revokeObjectURL(url));
  qualityPreviewUrls=[];
  const preview=document.getElementById('quality-photo-preview');
  const files=[...(input?.files||[])];
  if(files.length>6){
    input.value='';
    preview.innerHTML='';
    showToast('الحد الأقصى 6 صور في الفحص الواحد','error');
    return;
  }
  const oversized=files.find(file=>file.size>8*1024*1024);
  if(oversized){
    input.value='';
    preview.innerHTML='';
    showToast('حجم كل صورة يجب ألا يزيد عن 8MB','error');
    return;
  }
  qualityPreviewUrls=files.map(file=>URL.createObjectURL(file));
  preview.innerHTML=qualityPreviewUrls.map((url,index)=>`<div><img src="${url}" alt="معاينة صورة الجودة ${index+1}"><div style="font-size:.7rem;color:var(--muted);text-align:center">${safeText(files[index].name)}</div></div>`).join('');
}

async function uploadEvidenceFile(file){
  const form=new FormData();
  form.append('file',file);
  const response=await fetch((IBF_BOOT.restUrl||'')+'evidence',{
    method:'POST',
    credentials:'same-origin',
    headers:{'X-WP-Nonce':IBF_BOOT.nonce||''},
    body:form
  });
  const json=await response.json().catch(()=>({message:'استجابة غير صالحة من الخادم'}));
  if(!response.ok) throw new Error(json.message||'تعذر رفع الصورة');
  return json.data;
}

async function saveQualityCheck(){
  if(!currentUser){showToast('سجل دخول المشرف أولاً','error');return;}
  if(!requirePermission('can_manage_quality','⚠️ ليست لديك صلاحية الجودة والصور')) return;
  const date=document.getElementById('quality-date').value||todayStr();
  const category=document.getElementById('quality-category').value;
  const area=document.getElementById('quality-area').value.trim();
  const temperature=document.getElementById('quality-temperature').value;
  const notes=document.getElementById('quality-notes').value.trim();
  const input=document.getElementById('quality-photos');
  const files=[...(input.files||[])];
  if(!area){showToast('أدخل مكان التصوير أو منطقة الفحص','error');return;}
  if(!files.length){showToast('أضف صورة واحدة على الأقل لإثبات الفحص','error');return;}
  const button=document.getElementById('quality-save-btn');
  button.disabled=true;
  button.textContent='جاري رفع الصور وحفظ الفحص...';
  try{
    const photos=[];
    for(const file of files){
      photos.push(await uploadEvidenceFile(file));
    }
    const record={
      id:'QC-'+Date.now()+'-'+Math.random().toString(36).slice(2,7),
      site:currentUser.site,
      check_date:date,
      category,
      area,
      temperature:temperature===''?null:Number(temperature),
      notes,
      photos,
      supervisor_name:currentUser.name,
      created_at:new Date().toISOString()
    };
    const {error}=await supabase_client.from('quality_checks').insert(record);
    if(error) throw new Error(error.message||'تعذر حفظ فحص الجودة');
    input.value='';
    document.getElementById('quality-area').value='';
    document.getElementById('quality-temperature').value='';
    document.getElementById('quality-notes').value='';
    previewQualityPhotos(input);
    await loadQualityChecks();
    showToast('✅ تم حفظ فحص الجودة والصور بالموقع','success');
  }catch(error){
    showToast('❌ '+error.message,'error');
  }finally{
    button.disabled=false;
    button.textContent='💾 حفظ فحص الجودة والصور';
  }
}

async function loadQualityChecks(){
  const container=document.getElementById('quality-checks-list');
  if(!container||!supabase_client||!currentUser)return;
  const date=document.getElementById('quality-date')?.value||todayStr();
  container.innerHTML='<div class="empty"><div class="icon">📸</div><div>جاري تحميل سجل الجودة...</div></div>';
  const {data,error}=await supabase_client.from('quality_checks').select('*')
    .eq('site',currentUser.site).eq('check_date',date).order('created_at',{ascending:false});
  if(error){
    container.innerHTML=`<div class="empty"><div class="icon">⚠️</div><div>${safeText(error.message)}</div></div>`;
    return;
  }
  qualityChecks=data||[];
  renderQualityChecks();
}

function renderQualityChecks(){
  const container=document.getElementById('quality-checks-list');
  if(!container)return;
  const labels={
    food_preparation:'تحضير الطعام',
    refrigerators:'الثلاجات والتبريد',
    warehouses:'المخازن',
    hygiene:'النظافة الشخصية والموقع'
  };
  if(!qualityChecks.length){
    container.innerHTML='<div class="empty"><div class="icon">📸</div><div>لا توجد فحوصات مسجلة في هذا التاريخ</div></div>';
    return;
  }
  container.innerHTML=qualityChecks.map(check=>{
    const photos=Array.isArray(check.photos)?check.photos:[];
    const temperature=check.temperature===null||check.temperature===''?'':` · 🌡️ ${safeText(check.temperature)}°C`;
    const time=check.created_at?new Date(check.created_at).toLocaleString('ar-EG'):'';
    return `<div class="section-card" style="margin-bottom:.8rem">
      <div class="section-card-title"><span>✅ ${safeText(labels[check.category]||check.category)} — ${safeText(check.area)}</span><span class="tag">${safeText(check.check_date)}</span></div>
      <div style="font-size:.8rem;color:var(--muted);margin-bottom:.6rem">📍 ${safeText(check.site)}${temperature} · بواسطة ${safeText(check.supervisor_name)} · ${safeText(time)}</div>
      ${check.notes?`<div class="data-integrity-note">${safeText(check.notes)}</div>`:''}
      <div class="evidence-gallery">${photos.map(photo=>`<a href="${safeText(photo.url)}" target="_blank" rel="noopener"><img src="${safeText(photo.url)}" alt="${safeText(photo.name||'صورة جودة')}"></a>`).join('')}</div>
    </div>`;
  }).join('');
}

// ===== HR, EMPLOYEE QR & ATTENDANCE =====
function uniqueId(prefix){
  const token=window.crypto?.randomUUID?window.crypto.randomUUID():Date.now()+'-'+Math.random().toString(36).slice(2);
  return prefix+'-'+token;
}

async function addHrEmployee(){
  if(!currentUser){showToast('سجل دخول المشرف أولاً','error');return;}
  if(!requirePermission('can_manage_hr','⚠️ ليست لديك صلاحية HR والحضور')) return;
  const code=document.getElementById('hr-employee-code').value.trim().toUpperCase();
  const name=document.getElementById('hr-employee-name').value.trim();
  const jobTitle=document.getElementById('hr-employee-job').value.trim();
  const phone=document.getElementById('hr-employee-phone').value.trim();
  if(!code||!name||!jobTitle){showToast('أدخل كود العامل والاسم والوظيفة','error');return;}
  if(hrEmployees.some(employee=>String(employee.employee_code).toUpperCase()===code)){
    showToast('كود العامل مستخدم من قبل في هذا الموقع','error');return;
  }
  const id=uniqueId('EMP');
  const employee={
    id,
    site:currentUser.site,
    employee_code:code,
    name,
    job_title:jobTitle,
    phone,
    qr_token:'IBUILD-FOOD|'+id+'|'+uniqueId('QR'),
    active:true,
    created_at:new Date().toISOString()
  };
  const {error}=await supabase_client.from('employees').insert(employee);
  if(error){showToast('❌ '+error.message,'error');return;}
  ['hr-employee-code','hr-employee-name','hr-employee-job','hr-employee-phone'].forEach(id=>document.getElementById(id).value='');
  await loadHrData();
  showToast('✅ تم إضافة العامل وتوليد QR خاص به','success');
}

async function loadHrData(){
  if(!supabase_client||!currentUser)return;
  const employeesResult=await supabase_client.from('employees').select('*')
    .eq('site',currentUser.site).order('name',{ascending:true});
  const attendanceResult=await supabase_client.from('attendance').select('*')
    .eq('site',currentUser.site).eq('work_date',todayStr()).order('timestamp',{ascending:false});
  if(employeesResult.error||attendanceResult.error){
    const message=employeesResult.error?.message||attendanceResult.error?.message||'تعذر تحميل بيانات العاملين';
    showToast('❌ '+message,'error');
    return;
  }
  hrEmployees=(employeesResult.data||[]).filter(employee=>employee.active!==false);
  hrAttendance=attendanceResult.data||[];
  renderHrPanel();
  syncStaffCountToMeals();
}

function latestAttendanceByEmployee(){
  const latest={};
  hrAttendance.forEach(record=>{
    if(!latest[record.employee_id]) latest[record.employee_id]=record;
  });
  return latest;
}

function presentEmployees(){
  const latest=latestAttendanceByEmployee();
  return hrEmployees.filter(employee=>latest[employee.id]?.attendance_type==='check_in');
}

function syncStaffCountToMeals(){
  const count=presentEmployees().length;
  ['breakfast','lunch','dinner'].forEach(meal=>{
    const input=document.getElementById('staff-count-'+meal);
    if(input) input.value=count;
  });
}

function renderHrPanel(){
  const latest=latestAttendanceByEmployee();
  const present=presentEmployees();
  const total=document.getElementById('hr-total-employees');
  const presentCount=document.getElementById('hr-present-count');
  const checkins=document.getElementById('hr-checkins-count');
  const checkouts=document.getElementById('hr-checkouts-count');
  if(total) total.textContent=hrEmployees.length;
  if(presentCount) presentCount.textContent=present.length;
  if(checkins) checkins.textContent=hrAttendance.filter(record=>record.attendance_type==='check_in').length;
  if(checkouts) checkouts.textContent=hrAttendance.filter(record=>record.attendance_type==='check_out').length;
  const list=document.getElementById('hr-employees-list');
  if(list){
    list.innerHTML=hrEmployees.length?hrEmployees.map((employee,index)=>{
      const isPresent=latest[employee.id]?.attendance_type==='check_in';
      return `<div class="employee-card">
        <div style="display:flex;justify-content:space-between;gap:.5rem">
          <div><strong>${safeText(employee.name)}</strong><div style="font-size:.76rem;color:var(--muted)">${safeText(employee.job_title)} · ${safeText(employee.employee_code)}</div></div>
          <span class="tag" style="${isPresent?'color:var(--green)':'color:var(--muted)'}">${isPresent?'موجود الآن':'غير موجود'}</span>
        </div>
        <div id="employee-qr-${index}" style="width:112px;min-height:112px;background:#fff;padding:6px;border-radius:8px;margin:.7rem auto"></div>
        <button class="action-btn" onclick="printEmployeeQr('${safeText(employee.id)}')">🖨️ طباعة بطاقة QR</button>
      </div>`;
    }).join(''):'<div class="empty"><div class="icon">👥</div><div>أضف العاملين بالموقع لتوليد بطاقات QR</div></div>';
    if(window.QRCode){
      hrEmployees.forEach((employee,index)=>{
        const mount=document.getElementById('employee-qr-'+index);
        if(mount)new QRCode(mount,{text:employee.qr_token,width:100,height:100,colorDark:'#142c3e',colorLight:'#ffffff'});
      });
    }
  }
  const attendanceList=document.getElementById('hr-attendance-list');
  if(attendanceList){
    attendanceList.innerHTML=hrAttendance.length?`<div style="overflow-x:auto"><table class="inv-table">
      <thead><tr><th>الوقت</th><th>العامل</th><th>الكود</th><th>الحركة</th><th>سجلها</th></tr></thead>
      <tbody>${hrAttendance.map(record=>`<tr>
        <td>${safeText(record.time_label||new Date(record.timestamp).toLocaleTimeString('ar-EG'))}</td>
        <td><strong>${safeText(record.employee_name)}</strong></td>
        <td>${safeText(record.employee_code)}</td>
        <td><span class="tag" style="color:${record.attendance_type==='check_in'?'var(--green)':'var(--red)'}">${record.attendance_type==='check_in'?'حضور':'انصراف'}</span></td>
        <td>${safeText(record.recorded_by)}</td>
      </tr>`).join('')}</tbody>
    </table></div>`:'<div class="empty"><div class="icon">🕒</div><div>لا توجد حركات حضور أو انصراف اليوم</div></div>';
  }
}

function printEmployeeQr(employeeId){
  const employee=hrEmployees.find(item=>String(item.id)===String(employeeId));
  if(!employee||!window.QRCode){showToast('تعذر تجهيز بطاقة QR','error');return;}
  const popup=window.open('','_blank','width=520,height=680');
  if(!popup){showToast('اسمح بفتح النافذة لطباعة البطاقة','error');return;}
  popup.document.write(`<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>بطاقة ${safeText(employee.name)}</title><style>body{font-family:Arial,sans-serif;text-align:center;padding:30px;color:#142c3e}.card{border:2px solid #16835b;border-radius:18px;padding:24px;max-width:340px;margin:auto}h2{margin:.3rem}p{color:#566;margin:.35rem}#print-qr{width:230px;min-height:230px;margin:20px auto;background:#fff}</style></head><body><div class="card"><h2>I-BUILD Food</h2><div id="print-qr"></div><h2>${safeText(employee.name)}</h2><p>${safeText(employee.job_title)}</p><p>كود العامل: <strong>${safeText(employee.employee_code)}</strong></p><p>الموقع: ${safeText(employee.site)}</p></div></body></html>`);
  popup.document.close();
  const mount=popup.document.getElementById('print-qr');
  new QRCode(mount,{text:employee.qr_token,width:220,height:220,colorDark:'#142c3e',colorLight:'#ffffff'});
  setTimeout(()=>{popup.focus();popup.print();},400);
}

function processManualAttendance(){
  const input=document.getElementById('hr-manual-token');
  processAttendanceToken(input.value.trim());
}

async function processAttendanceToken(token){
  if(!requirePermission('can_manage_hr','⚠️ ليست لديك صلاحية تسجيل الحضور')) return;
  if(!token){showToast('امسح QR أو أدخل كود العامل','error');return;}
  const employee=hrEmployees.find(item=>item.qr_token===token||String(item.employee_code).toUpperCase()===token.toUpperCase());
  if(!employee){showToast('QR غير صالح أو العامل غير مسجل في هذا الموقع','error');return;}
  const action=document.getElementById('hr-attendance-action').value;
  const latest=latestAttendanceByEmployee()[employee.id];
  if(action==='check_in'&&latest?.attendance_type==='check_in'){
    showToast(employee.name+' مسجل حضور بالفعل ولم يسجل انصراف','error');return;
  }
  if(action==='check_out'&&latest?.attendance_type!=='check_in'){
    showToast('لا يمكن تسجيل الانصراف قبل تسجيل الحضور','error');return;
  }
  const now=new Date();
  const record={
    id:uniqueId('ATT'),
    employee_id:employee.id,
    employee_code:employee.employee_code,
    employee_name:employee.name,
    site:currentUser.site,
    work_date:todayStr(),
    attendance_type:action,
    timestamp:now.toISOString(),
    time_label:now.toLocaleTimeString('ar-EG'),
    recorded_by:currentUser.name
  };
  const {error}=await supabase_client.from('attendance').insert(record);
  if(error){showToast('❌ '+error.message,'error');return;}
  document.getElementById('hr-manual-token').value='';
  await loadHrData();
  showToast(`✅ تم تسجيل ${action==='check_in'?'حضور':'انصراف'} ${employee.name}`,'success');
}

function startAttendanceScanner(){
  if(!requirePermission('can_manage_hr','⚠️ ليست لديك صلاحية تسجيل الحضور')) return;
  if(!window.Html5QrcodeScanner){showToast('ماسح QR غير متاح. استخدم إدخال الكود يدويًا','error');return;}
  if(attendanceScanner)return;
  const reader=document.getElementById('hr-qr-reader');
  reader.style.display='block';
  document.getElementById('hr-stop-scanner').style.display='block';
  attendanceScanner=new Html5QrcodeScanner('hr-qr-reader',{
    fps:10,
    qrbox:{width:220,height:220},
    rememberLastUsedCamera:true
  },false);
  attendanceScanner.render(async decodedText=>{
    await stopAttendanceScanner();
    await processAttendanceToken(decodedText);
  },()=>{});
}

async function stopAttendanceScanner(){
  const scanner=attendanceScanner;
  attendanceScanner=null;
  if(scanner){
    try{await scanner.clear();}catch(error){console.warn(error);}
  }
  const reader=document.getElementById('hr-qr-reader');
  if(reader){reader.innerHTML='';reader.style.display='none';}
  const stop=document.getElementById('hr-stop-scanner');
  if(stop)stop.style.display='none';
}

// ===== EQUIPMENT & MAINTENANCE =====
const EQUIPMENT_STATUS_LABELS={
  operational:'تعمل بحالة جيدة',
  needs_maintenance:'تحتاج صيانة',
  under_maintenance:'تحت الصيانة',
  out_of_service:'خارج الخدمة'
};
const MAINTENANCE_TYPE_LABELS={preventive:'وقائية',corrective:'إصلاحية',emergency:'طارئة'};
function equipmentStatusLabel(status){return EQUIPMENT_STATUS_LABELS[status]||EQUIPMENT_STATUS_LABELS.operational;}
function equipmentStatusBadge(status){
  const icon={operational:'✅',needs_maintenance:'⚠️',under_maintenance:'🔧',out_of_service:'⛔'}[status]||'✅';
  return `<span class="equipment-status ${safeText(status||'operational')}">${icon} ${safeText(equipmentStatusLabel(status))}</span>`;
}
function equipmentDateLabel(value){
  if(!value)return '—';
  const date=new Date(String(value).slice(0,10)+'T00:00:00');
  return Number.isNaN(date.getTime())?safeText(value):date.toLocaleDateString('ar-EG');
}
function isMaintenanceOverdue(item){
  if(!item.next_maintenance_date||item.status==='out_of_service')return false;
  return String(item.next_maintenance_date).slice(0,10)<todayStr();
}
function equipmentCostTotal(equipmentId,records=maintenanceRecords){
  const historyTotal=records.filter(record=>String(record.equipment_id)===String(equipmentId)).reduce((sum,record)=>sum+(Number(record.cost)||0),0);
  const item=[...equipmentRecords,...managerEquipmentRecords].find(record=>String(record.id)===String(equipmentId));
  return Math.max(historyTotal,Number(item?.maintenance_total_cost)||0);
}
async function loadEquipmentData(){
  if(!supabase_client||!currentUser?.site)return;
  const [equipmentResult,maintenanceResult]=await Promise.all([
    supabase_client.from('equipment').select('*').eq('site',currentUser.site).order('equipment_name',{ascending:true}),
    supabase_client.from('maintenance_logs').select('*').eq('site',currentUser.site).order('maintenance_date',{ascending:false})
  ]);
  if(equipmentResult.error||maintenanceResult.error){
    showToast('تعذر تحميل كشف المعدات والصيانة','error');return;
  }
  equipmentRecords=equipmentResult.data||[];
  maintenanceRecords=maintenanceResult.data||[];
  renderEquipmentPanel();
}
function renderEquipmentPanel(){
  const search=(document.getElementById('equipment-search')?.value||'').trim().toLowerCase();
  const filtered=equipmentRecords.filter(item=>[
    item.equipment_code,item.equipment_name,item.category,item.location,item.serial_number
  ].some(value=>String(value||'').toLowerCase().includes(search)));
  const maintenanceCount=equipmentRecords.filter(item=>['needs_maintenance','under_maintenance'].includes(item.status)).length;
  const overdueCount=equipmentRecords.filter(isMaintenanceOverdue).length;
  const totalCost=equipmentRecords.reduce((sum,item)=>sum+equipmentCostTotal(item.id),0);
  const setText=(id,value)=>{const element=document.getElementById(id);if(element)element.textContent=value;};
  setText('equipment-total-count',equipmentRecords.length.toLocaleString('ar-EG'));
  setText('equipment-maintenance-count',maintenanceCount.toLocaleString('ar-EG'));
  setText('equipment-overdue-count',overdueCount.toLocaleString('ar-EG'));
  setText('equipment-cost-total',totalCost.toLocaleString('ar-EG',{maximumFractionDigits:2})+' ج');

  const equipmentSelect=document.getElementById('maintenance-equipment');
  if(equipmentSelect){
    const current=equipmentSelect.value;
    equipmentSelect.innerHTML='<option value="">اختر المعدة *</option>'+equipmentRecords.map(item=>`<option value="${safeText(item.id)}">${safeText(item.equipment_code)} — ${safeText(item.equipment_name)}</option>`).join('');
    if(equipmentRecords.some(item=>String(item.id)===String(current)))equipmentSelect.value=current;
  }
  const tbody=document.getElementById('equipment-tbody');
  if(tbody){
    tbody.innerHTML=filtered.length?filtered.map(item=>{
      const overdue=isMaintenanceOverdue(item);
      const total=equipmentCostTotal(item.id);
      return `<tr>
        <td><strong>${safeText(item.equipment_code)}</strong><div style="font-size:.7rem;color:var(--muted)">${safeText(item.serial_number||'بدون مسلسل')}</div></td>
        <td><strong>${safeText(item.equipment_name)}</strong><div style="font-size:.72rem;color:var(--muted)">${safeText(item.category||'غير مصنف')} · ${safeText(item.location||'—')}</div></td>
        <td>${equipmentStatusBadge(item.status)}</td>
        <td>${equipmentDateLabel(item.last_maintenance_date)}${item.last_maintenance_cost?`<div style="font-size:.7rem;color:var(--muted)">${Number(item.last_maintenance_cost).toLocaleString('ar-EG')} ج</div>`:''}</td>
        <td class="${overdue?'maintenance-due':'maintenance-ok'}">${equipmentDateLabel(item.next_maintenance_date)}${overdue?'<div style="font-size:.68rem">متأخرة</div>':''}</td>
        <td><strong>${total.toLocaleString('ar-EG',{maximumFractionDigits:2})} ج</strong></td>
        <td><div style="display:flex;gap:.35rem;flex-wrap:wrap">
          <button class="action-btn" onclick="selectEquipmentForMaintenance('${safeText(item.id)}')">🔧 صيانة</button>
          <button class="action-btn" onclick="editEquipment('${safeText(item.id)}')">✏️ تعديل</button>
          ${item.status==='operational'?`<button class="action-btn danger" onclick="markEquipmentNeedsMaintenance('${safeText(item.id)}')">⚠️ يحتاج صيانة</button>`:''}
        </div></td>
      </tr>`;
    }).join(''):'<tr><td colspan="7"><div class="empty"><div class="icon">🛠️</div><div>لا توجد معدات مطابقة</div></div></td></tr>';
  }
  renderMaintenanceHistory();
}
function resetEquipmentForm(){
  ['equipment-edit-id','equipment-code','equipment-name','equipment-category','equipment-serial','equipment-location','equipment-purchase-date','equipment-next-date','equipment-notes'].forEach(id=>{
    const element=document.getElementById(id);if(element)element.value='';
  });
  document.getElementById('equipment-status').value='operational';
  document.getElementById('equipment-save-btn').textContent='💾 حفظ المعدة';
  document.getElementById('equipment-cancel-edit').style.display='none';
}
function editEquipment(id){
  const item=equipmentRecords.find(record=>String(record.id)===String(id));if(!item)return;
  document.getElementById('equipment-edit-id').value=item.id;
  document.getElementById('equipment-code').value=item.equipment_code||'';
  document.getElementById('equipment-name').value=item.equipment_name||'';
  document.getElementById('equipment-category').value=item.category||'';
  document.getElementById('equipment-serial').value=item.serial_number||'';
  document.getElementById('equipment-location').value=item.location||'';
  document.getElementById('equipment-purchase-date').value=String(item.purchase_date||'').slice(0,10);
  document.getElementById('equipment-status').value=item.status||'operational';
  document.getElementById('equipment-next-date').value=String(item.next_maintenance_date||'').slice(0,10);
  document.getElementById('equipment-notes').value=item.notes||'';
  document.getElementById('equipment-save-btn').textContent='💾 تحديث المعدة';
  document.getElementById('equipment-cancel-edit').style.display='inline-flex';
  document.getElementById('meal-equipment').scrollIntoView({behavior:'smooth',block:'start'});
}
async function saveEquipment(){
  if(!requirePermission('can_manage_equipment','⚠️ ليست لديك صلاحية المعدات والصيانة'))return;
  const id=document.getElementById('equipment-edit-id').value;
  const code=document.getElementById('equipment-code').value.trim().toUpperCase();
  const name=document.getElementById('equipment-name').value.trim();
  const location=document.getElementById('equipment-location').value.trim();
  if(!code||!name||!location){showToast('أدخل كود المعدة واسمها ومكانها','error');return;}
  if(equipmentRecords.some(item=>String(item.id)!==String(id)&&String(item.equipment_code).toUpperCase()===code)){
    showToast('كود المعدة مستخدم من قبل في هذا الموقع','error');return;
  }
  const existing=equipmentRecords.find(item=>String(item.id)===String(id))||{};
  const status=document.getElementById('equipment-status').value;
  const record={
    ...existing,
    id:id||uniqueId('EQ'),
    site:currentUser.site,
    equipment_code:code,
    equipment_name:name,
    category:document.getElementById('equipment-category').value.trim(),
    serial_number:document.getElementById('equipment-serial').value.trim(),
    location,
    purchase_date:document.getElementById('equipment-purchase-date').value||null,
    status,
    next_maintenance_date:document.getElementById('equipment-next-date').value||null,
    notes:document.getElementById('equipment-notes').value.trim(),
    maintenance_requested_date:status==='needs_maintenance'?(existing.maintenance_requested_date||todayStr()):null,
    updated_by:currentUser.name,
    updated_at:new Date().toISOString()
  };
  const result=id
    ? await supabase_client.from('equipment').update(record).eq('id',id)
    : await supabase_client.from('equipment').insert({...record,created_by:currentUser.name,created_at:new Date().toISOString()});
  if(result.error){showToast('❌ '+result.error.message,'error');return;}
  resetEquipmentForm();
  await loadEquipmentData();
  showToast(id?'✅ تم تحديث بيانات المعدة':'✅ تمت إضافة المعدة إلى الكشف','success');
}
async function markEquipmentNeedsMaintenance(id){
  if(!requirePermission('can_manage_equipment'))return;
  const item=equipmentRecords.find(record=>String(record.id)===String(id));if(!item)return;
  const {error}=await supabase_client.from('equipment').update({
    status:'needs_maintenance',
    maintenance_requested_date:todayStr(),
    updated_by:currentUser.name,
    updated_at:new Date().toISOString()
  }).eq('id',id);
  if(error){showToast('تعذر تحديث حالة المعدة','error');return;}
  await loadEquipmentData();
  showToast('⚠️ تم تسجيل أن المعدة تحتاج صيانة','success');
}
function selectEquipmentForMaintenance(id){
  const select=document.getElementById('maintenance-equipment');
  if(select)select.value=id;
  document.getElementById('maintenance-date').value=todayStr();
  document.getElementById('maintenance-form-box').scrollIntoView({behavior:'smooth',block:'start'});
}
async function saveMaintenanceLog(){
  if(!requirePermission('can_manage_equipment','⚠️ ليست لديك صلاحية تسجيل الصيانة'))return;
  const equipmentId=document.getElementById('maintenance-equipment').value;
  const item=equipmentRecords.find(record=>String(record.id)===String(equipmentId));
  const date=document.getElementById('maintenance-date').value;
  const work=document.getElementById('maintenance-work').value.trim();
  const cost=Math.max(0,Number(document.getElementById('maintenance-cost').value)||0);
  if(!item||!date||!work){showToast('اختر المعدة وأدخل تاريخ الصيانة والأعمال التي تمت','error');return;}
  const resultStatus=document.getElementById('maintenance-result').value;
  const nextDate=document.getElementById('maintenance-next-date').value||null;
  const record={
    id:uniqueId('MNT'),
    equipment_id:item.id,
    equipment_code:item.equipment_code,
    equipment_name:item.equipment_name,
    site:currentUser.site,
    maintenance_date:date,
    log_date:date,
    maintenance_type:document.getElementById('maintenance-type').value,
    technician:document.getElementById('maintenance-technician').value.trim(),
    cost,
    result_status:resultStatus,
    next_maintenance_date:nextDate,
    invoice_number:document.getElementById('maintenance-invoice').value.trim(),
    work_done:work,
    recorded_by:currentUser.name,
    created_at:new Date().toISOString()
  };
  const {error}=await supabase_client.from('maintenance_logs').insert(record);
  if(error){showToast('تعذر حفظ عملية الصيانة: '+error.message,'error');return;}
  const previousTotal=equipmentCostTotal(item.id);
  const updateResult=await supabase_client.from('equipment').update({
    status:resultStatus,
    last_maintenance_date:date,
    last_maintenance_cost:cost,
    maintenance_total_cost:previousTotal+cost,
    next_maintenance_date:nextDate,
    last_maintenance_work:work,
    maintenance_requested_date:resultStatus==='operational'?null:(item.maintenance_requested_date||todayStr()),
    updated_by:currentUser.name,
    updated_at:new Date().toISOString()
  }).eq('id',item.id);
  if(updateResult.error){showToast('تم حفظ الصيانة وتعذر تحديث حالة المعدة','error');return;}
  ['maintenance-technician','maintenance-cost','maintenance-next-date','maintenance-invoice','maintenance-work'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('maintenance-equipment').value='';
  document.getElementById('maintenance-result').value='operational';
  document.getElementById('maintenance-date').value=todayStr();
  await loadEquipmentData();
  showToast(`✅ تم تسجيل الصيانة بتكلفة ${cost.toLocaleString('ar-EG')} جنيه`,'success');
}
function renderMaintenanceHistory(){
  const container=document.getElementById('maintenance-history');if(!container)return;
  const rows=[...maintenanceRecords].sort((a,b)=>String(b.maintenance_date||'').localeCompare(String(a.maintenance_date||'')));
  container.innerHTML=rows.length?`<div style="overflow-x:auto"><table class="inv-table">
    <thead><tr><th>التاريخ</th><th>المعدة</th><th>نوع الصيانة</th><th>الفني</th><th>الأعمال التي تمت</th><th>التكلفة</th><th>الصيانة القادمة</th></tr></thead>
    <tbody>${rows.map(record=>`<tr>
      <td>${equipmentDateLabel(record.maintenance_date)}</td>
      <td><strong>${safeText(record.equipment_name)}</strong><div style="font-size:.7rem;color:var(--muted)">${safeText(record.equipment_code)}</div></td>
      <td>${safeText(MAINTENANCE_TYPE_LABELS[record.maintenance_type]||record.maintenance_type)}</td>
      <td>${safeText(record.technician||'—')}</td>
      <td>${safeText(record.work_done)}${record.invoice_number?`<div style="font-size:.7rem;color:var(--muted)">فاتورة/أمر شغل: ${safeText(record.invoice_number)}</div>`:''}</td>
      <td><strong>${Number(record.cost||0).toLocaleString('ar-EG',{maximumFractionDigits:2})} ج</strong></td>
      <td>${equipmentDateLabel(record.next_maintenance_date)}</td>
    </tr>`).join('')}</tbody>
  </table></div>`:'<div class="empty"><div class="icon">🔧</div><div>لا توجد عمليات صيانة مسجلة</div></div>';
}
function normalizeEquipmentStatus(value){
  const clean=normalizeHeader(value);
  if(clean.includes('خارج')||clean.includes('outofservice'))return 'out_of_service';
  if(clean.includes('تحتالصيانه')||clean.includes('تحتالصيانة')||clean.includes('undermaintenance'))return 'under_maintenance';
  if(clean.includes('تحتاج')||clean.includes('محتاج')||clean.includes('needsmaintenance'))return 'needs_maintenance';
  return 'operational';
}
function parseExcelDateValue(value){
  if(!value)return null;
  if(value instanceof Date&&!Number.isNaN(value.getTime()))return toDateStr(value);
  if(typeof value==='number'&&window.XLSX?.SSF){
    const parsed=XLSX.SSF.parse_date_code(value);
    if(parsed)return `${parsed.y}-${String(parsed.m).padStart(2,'0')}-${String(parsed.d).padStart(2,'0')}`;
  }
  const text=String(value).trim();
  const iso=text.match(/^(\d{4})[-/](\d{1,2})[-/](\d{1,2})/);
  if(iso)return `${iso[1]}-${String(iso[2]).padStart(2,'0')}-${String(iso[3]).padStart(2,'0')}`;
  const local=text.match(/^(\d{1,2})[-/](\d{1,2})[-/](\d{4})/);
  if(local)return `${local[3]}-${String(local[2]).padStart(2,'0')}-${String(local[1]).padStart(2,'0')}`;
  return null;
}
function parseEquipmentRowsFromSheet(ws){
  const rows=XLSX.utils.sheet_to_json(ws,{header:1,defval:'',blankrows:false});
  const headerIndex=rows.findIndex(row=>{
    const cells=row.map(normalizeHeader);
    return cells.some(cell=>cell.includes('اسمالمعده')||cell.includes('اسمالمعدة')||cell.includes('equipmentname'))
      && cells.some(cell=>cell.includes('كودالمعده')||cell.includes('كودالمعدة')||cell.includes('equipmentcode')||cell==='الكود');
  });
  if(headerIndex<0)return [];
  const headers=rows[headerIndex];
  const indexes={
    code:columnIndexes(headers,['كود المعدة','الكود','equipment code','equipment_code','code']),
    name:columnIndexes(headers,['اسم المعدة','المعدة','equipment name','equipment_name','name']),
    category:columnIndexes(headers,['النوع','التصنيف','category','type']),
    serial:columnIndexes(headers,['الرقم المسلسل','السيريال','serial number','serial_number','serial']),
    location:columnIndexes(headers,['المكان','الموقع الداخلي','location','area']),
    status:columnIndexes(headers,['الحالة','status','condition']),
    purchase:columnIndexes(headers,['تاريخ الشراء','purchase date','purchase_date']),
    last:columnIndexes(headers,['آخر صيانة','تاريخ آخر صيانة','last maintenance','last_maintenance_date']),
    next:columnIndexes(headers,['الصيانة القادمة','تاريخ الصيانة القادمة','next maintenance','next_maintenance_date']),
    cost:columnIndexes(headers,['تكلفة الصيانة','إجمالي المصروف','maintenance cost','maintenance_total_cost','cost']),
    notes:columnIndexes(headers,['الملاحظات','notes','remarks'])
  };
  return rows.slice(headerIndex+1).map((row,index)=>{
    const name=String(firstValue(row,indexes.name)||'').trim();
    if(!name)return null;
    return {
      equipment_code:String(firstValue(row,indexes.code)||`EQ-${Date.now()}-${index+1}`).trim().toUpperCase(),
      equipment_name:name,
      category:String(firstValue(row,indexes.category)||'').trim(),
      serial_number:String(firstValue(row,indexes.serial)||'').trim(),
      location:String(firstValue(row,indexes.location)||'غير محدد').trim(),
      status:normalizeEquipmentStatus(firstValue(row,indexes.status)),
      purchase_date:parseExcelDateValue(firstValue(row,indexes.purchase)),
      last_maintenance_date:parseExcelDateValue(firstValue(row,indexes.last)),
      next_maintenance_date:parseExcelDateValue(firstValue(row,indexes.next)),
      maintenance_total_cost:parseImportedNumber(firstValue(row,indexes.cost)),
      notes:String(firstValue(row,indexes.notes)||'').trim()
    };
  }).filter(Boolean);
}
function importEquipmentFile(input){
  if(!requirePermission('can_manage_equipment','⚠️ ليست لديك صلاحية رفع كشف المعدات')){input.value='';return;}
  const file=input.files&&input.files[0];if(!file)return;
  if(!ensureExcelReady()){input.value='';return;}
  const reader=new FileReader();
  reader.onload=async event=>{
    try{
      const workbook=XLSX.read(new Uint8Array(event.target.result),{type:'array',cellDates:true});
      const rows=parseEquipmentRowsFromSheet(workbook.Sheets[workbook.SheetNames[0]]);
      if(!rows.length){showToast('لم يتم العثور على أعمدة كود المعدة واسم المعدة','error');return;}
      const now=new Date().toISOString();
      const payload=rows.map(row=>({
        ...row,
        site:currentUser.site,
        updated_by:currentUser.name,
        updated_at:now,
        created_by:currentUser.name,
        created_at:now,
        maintenance_requested_date:row.status==='needs_maintenance'?todayStr():null
      }));
      const {error}=await supabase_client.from('equipment').upsert(payload,{onConflict:'site,equipment_code'});
      if(error){showToast('تعذر حفظ كشف Excel: '+error.message,'error');return;}
      await loadEquipmentData();
      showToast(`✅ تم رفع وتحديث ${rows.length.toLocaleString('ar-EG')} معدة`,'success');
    }catch(error){
      console.error(error);showToast('❌ تعذر قراءة ملف Excel','error');
    }finally{input.value='';}
  };
  reader.readAsArrayBuffer(file);
}
function writeEquipmentWorkbook(equipment,maintenance,filename){
  if(!ensureExcelReady())return;
  const wb=XLSX.utils.book_new();
  const equipmentRows=[
    ['I-BUILD — كشف المعدات والصيانة'],
    ['تاريخ التصدير',new Date().toLocaleString('ar-EG')],
    [],
    ['كود المعدة','اسم المعدة','النوع','الرقم المسلسل','المكان','الموقع','الحالة','تاريخ الشراء','آخر صيانة','الصيانة القادمة','إجمالي مصروف الصيانة','الملاحظات']
  ];
  equipment.forEach(item=>equipmentRows.push([
    item.equipment_code,item.equipment_name,item.category||'',item.serial_number||'',item.location||'',item.site||'',
    equipmentStatusLabel(item.status),item.purchase_date||'',item.last_maintenance_date||'',item.next_maintenance_date||'',
    equipmentCostTotal(item.id,maintenance),item.notes||''
  ]));
  const equipmentSheet=XLSX.utils.aoa_to_sheet(equipmentRows);
  equipmentSheet['!cols']=[{wch:15},{wch:24},{wch:16},{wch:18},{wch:20},{wch:15},{wch:18},{wch:14},{wch:14},{wch:16},{wch:18},{wch:35}];
  XLSX.utils.book_append_sheet(wb,equipmentSheet,'كشف المعدات');
  const maintenanceRows=[
    ['I-BUILD — سجل عمليات الصيانة'],[],
    ['التاريخ','كود المعدة','اسم المعدة','الموقع','نوع الصيانة','الفني/الشركة','الأعمال التي تمت','التكلفة بالجنيه','رقم الفاتورة/أمر الشغل','النتيجة','الصيانة القادمة','سجلها']
  ];
  maintenance.forEach(record=>maintenanceRows.push([
    record.maintenance_date,record.equipment_code,record.equipment_name,record.site||'',
    MAINTENANCE_TYPE_LABELS[record.maintenance_type]||record.maintenance_type,record.technician||'',record.work_done||'',
    Number(record.cost)||0,record.invoice_number||'',equipmentStatusLabel(record.result_status),record.next_maintenance_date||'',record.recorded_by||''
  ]));
  const maintenanceSheet=XLSX.utils.aoa_to_sheet(maintenanceRows);
  maintenanceSheet['!cols']=[{wch:14},{wch:15},{wch:24},{wch:15},{wch:14},{wch:22},{wch:40},{wch:16},{wch:22},{wch:18},{wch:16},{wch:18}];
  XLSX.utils.book_append_sheet(wb,maintenanceSheet,'سجل الصيانة');
  XLSX.writeFile(wb,filename);
  showToast('✅ تم تنزيل ملف المعدات والصيانة','success');
}
function exportEquipmentExcel(){
  writeEquipmentWorkbook(equipmentRecords,maintenanceRecords,`iBUILD_المعدات_${currentUser?.site||''}_${todayStr()}.xlsx`);
}
async function renderManagerEquipment(){
  const container=document.getElementById('manager-equipment-container');
  if(!container||!supabase_client)return;
  const site=document.getElementById('manager-equipment-site')?.value||'';
  let equipmentQuery=supabase_client.from('equipment').select('*').order('equipment_name',{ascending:true});
  let maintenanceQuery=supabase_client.from('maintenance_logs').select('*').order('maintenance_date',{ascending:false});
  if(site){equipmentQuery=equipmentQuery.eq('site',site);maintenanceQuery=maintenanceQuery.eq('site',site);}
  const [equipmentResult,maintenanceResult]=await Promise.all([equipmentQuery,maintenanceQuery]);
  if(equipmentResult.error||maintenanceResult.error){
    container.innerHTML='<div class="empty"><div class="icon">⚠️</div><div>تعذر تحميل كشف المعدات</div></div>';return;
  }
  managerEquipmentRecords=equipmentResult.data||[];
  managerMaintenanceRecords=maintenanceResult.data||[];
  const needs=managerEquipmentRecords.filter(item=>['needs_maintenance','under_maintenance'].includes(item.status)).length;
  const overdue=managerEquipmentRecords.filter(isMaintenanceOverdue).length;
  const cost=managerEquipmentRecords.reduce((sum,item)=>sum+equipmentCostTotal(item.id,managerMaintenanceRecords),0);
  container.innerHTML=`<div class="equipment-kpis">
    <div class="equipment-kpi"><strong>${managerEquipmentRecords.length.toLocaleString('ar-EG')}</strong><span>إجمالي المعدات</span></div>
    <div class="equipment-kpi"><strong style="color:#a55e00">${needs.toLocaleString('ar-EG')}</strong><span>تحتاج صيانة</span></div>
    <div class="equipment-kpi"><strong style="color:var(--red)">${overdue.toLocaleString('ar-EG')}</strong><span>صيانة متأخرة</span></div>
    <div class="equipment-kpi"><strong>${cost.toLocaleString('ar-EG',{maximumFractionDigits:2})} ج</strong><span>إجمالي المصروف</span></div>
  </div>
  <div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>الموقع</th><th>المعدة</th><th>المكان</th><th>الحالة</th><th>آخر صيانة</th><th>القادمة</th><th>المصروف</th></tr></thead>
  <tbody>${managerEquipmentRecords.length?managerEquipmentRecords.map(item=>`<tr>
    <td>${safeText(item.site||'—')}</td>
    <td><strong>${safeText(item.equipment_name)}</strong><div style="font-size:.7rem;color:var(--muted)">${safeText(item.equipment_code)}</div></td>
    <td>${safeText(item.location||'—')}</td><td>${equipmentStatusBadge(item.status)}</td>
    <td>${equipmentDateLabel(item.last_maintenance_date)}</td>
    <td class="${isMaintenanceOverdue(item)?'maintenance-due':''}">${equipmentDateLabel(item.next_maintenance_date)}</td>
    <td><strong>${equipmentCostTotal(item.id,managerMaintenanceRecords).toLocaleString('ar-EG',{maximumFractionDigits:2})} ج</strong></td>
  </tr>`).join(''):'<tr><td colspan="7"><div class="empty"><div>لا توجد معدات مسجلة</div></div></td></tr>'}</tbody></table></div>`;
}
function exportManagerEquipmentExcel(){
  writeEquipmentWorkbook(managerEquipmentRecords,managerMaintenanceRecords,`iBUILD_المعدات_كل-المواقع_${todayStr()}.xlsx`);
}

// ===== MEAL TABS =====
function switchMeal(m,btn){
  if(m==='inventory' && !requirePermission('can_edit_inventory','⚠️ المدير لم يفتح لك صلاحية تعديل المخزن')) return;
  if(m==='purchase-request' && !hasPermission('can_add_invoice') && IBF_BOOT.user?.roleKey!=='ibf_storekeeper'){
    showToast('⚠️ ليس لديك صلاحية طلبات الشراء','error');return;
  }
  if(m==='invoice' && !requirePermission('can_add_invoice','⚠️ المدير لم يفتح لك صلاحية إضافة الفواتير')) return;
  if(m==='returns' && !requirePermission('can_submit_meals','⚠️ تسجيل المرتجعات متاح للمشرف فقط')) return;
  if(m==='cleaning' && !requirePermission('can_submit_meals','⚠️ المدير لم يفتح لك صلاحية صرف النظافة')) return;
  if(m==='operation' && !requirePermission('can_submit_meals','⚠️ المدير لم يفتح لك صلاحية صرف التشغيل')) return;
  if(m==='quality' && !requirePermission('can_manage_quality','⚠️ المدير لم يفتح لك صلاحية الجودة والصور')) return;
  if(m==='hr' && !requirePermission('can_manage_hr','⚠️ المدير لم يفتح لك صلاحية HR والحضور')) return;
  if(m==='equipment' && !requirePermission('can_manage_equipment','⚠️ المدير لم يفتح لك صلاحية المعدات والصيانة')) return;
  if(m==='hr-overview' && !requirePermission('can_manage_hr','⚠️ المدير لم يفتح لك صلاحية HR')) return;
  document.querySelectorAll('.meal-tab').forEach(x=>x.classList.remove('active'));
  document.querySelectorAll('.meal-content').forEach(x=>x.classList.remove('active'));
  btn.classList.add('active');
  document.getElementById('meal-'+m).classList.add('active');
  if(m==='inventory'){
    document.getElementById('inv-date').value = todayStr();
    loadInventory();
  }
  if(m==='invoice'){
    document.getElementById('invoice-date').value = todayStr();
    ensureInvoiceTemplate();
    renderInvoiceTable();
    renderSuppliersMaster();
  }
  if(m==='purchase-request'){document.getElementById('pr-date').value=todayStr();renderPurchaseRequestItems();renderPurchaseRequests();loadPurchaseRequestCatalog();}
  if(m==='returns'){document.getElementById('rw-date').value=todayStr();loadPurchaseRequestCatalog().then(renderReturnWaste).catch(()=>renderReturnWaste());renderReturnWaste();}
  if(m==='cashbox') loadCashbox();
  if(m==='recipe'){
    document.getElementById('recipe-date').value=todayStr();
    renderRationSelector();
  }
  if(m==='quality'){
    document.getElementById('quality-date').value=todayStr();
    loadQualityChecks();
  }
  if(m==='hr') loadHrData();
  if(m==='equipment') loadEquipmentData();
  if(m==='hr-overview'){
    const field=document.getElementById('hro-date');
    if(field&&!field.value)field.value=todayStr();
    renderHrOverview();
  }
  if(['breakfast','lunch','dinner'].includes(m)){
    syncStaffCountToMeals();
    updateMealHeadcount(m);
  }
}

// ===== SUPPLIERS MASTER =====
function renderSuppliersMaster(){
  ensureMasterData();
  const db=loadLocalDb(),suppliers=(db.suppliers||[]).filter(item=>item.active!==false).sort((a,b)=>a.name.localeCompare(b.name,'ar'));
  const select=document.getElementById('invoice-supplier-master');
  if(select){const current=select.value;select.innerHTML='<option value="">اختر موردًا مسجلًا</option>'+suppliers.map(item=>`<option value="${safeText(item.id)}">${safeText(item.name)} — ${item.paymentType==='credit'?'آجل':'نقدي'}</option>`).join('');if(suppliers.some(item=>String(item.id)===current))select.value=current;}
  renderPurchaseSupplierOptions();
  renderCashboxSupplierOptions();
  const list=document.getElementById('supplier-master-list');
  if(list)list.innerHTML=suppliers.length?`<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>المورد</th><th>التعامل</th><th>التليفون</th><th>الحساب</th><th>الرقم الضريبي</th><th>الملاحظات</th><th></th></tr></thead><tbody>${suppliers.map(item=>`<tr><td><strong>${safeText(item.name)}</strong></td><td>${item.paymentType==='credit'?'آجل':'نقدي'}</td><td>${safeText(item.phone||'-')}</td><td>${safeText(item.account||'-')}</td><td>${safeText(item.taxNumber||'-')}</td><td>${safeText(item.notes||'-')}</td><td><button class="action-btn" onclick="editSupplierMaster(${safeText(JSON.stringify(item.id))})">تعديل</button></td></tr>`).join('')}</tbody></table></div>`:'<div class="data-integrity-note">لم يتم تسجيل موردين بعد. أضف المورد مرة واحدة ثم اختره في الفواتير.</div>';
}
function saveSupplierMaster(){
  const id=document.getElementById('supplier-master-id').value,name=document.getElementById('supplier-master-name').value.trim(),paymentType=document.getElementById('supplier-master-type').value,phone=document.getElementById('supplier-master-phone').value.trim(),account=document.getElementById('supplier-master-account').value.trim(),taxNumber=document.getElementById('supplier-master-tax').value.trim(),notes=document.getElementById('supplier-master-notes').value.trim();
  if(!name){showToast('أدخل اسم المورد','error');return;}
  const db=loadLocalDb();db.suppliers=db.suppliers||[];
  const duplicate=db.suppliers.find(item=>item.name.toLowerCase()===name.toLowerCase()&&String(item.id)!==String(id));
  if(duplicate){showToast('المورد مسجل من قبل','error');return;}
  if(id){const index=db.suppliers.findIndex(item=>String(item.id)===String(id));if(index>=0)db.suppliers[index]={...db.suppliers[index],name,paymentType,phone,account,taxNumber,notes,updatedAt:new Date().toISOString()};}
  else db.suppliers.push({id:'SUP-'+Date.now(),name,paymentType,phone,account,taxNumber,notes,active:true,createdAt:new Date().toISOString()});
  saveLocalDb(db);
  ['supplier-master-id','supplier-master-name','supplier-master-phone','supplier-master-account','supplier-master-tax','supplier-master-notes'].forEach(field=>document.getElementById(field).value='');document.getElementById('supplier-master-type').value='cash';renderSuppliersMaster();showToast('✅ تم حفظ بيانات المورد','success');
}
function editSupplierMaster(id){
  const supplier=(loadLocalDb().suppliers||[]).find(item=>String(item.id)===String(id));if(!supplier)return;
  document.getElementById('supplier-master-id').value=supplier.id;document.getElementById('supplier-master-name').value=supplier.name;document.getElementById('supplier-master-type').value=supplier.paymentType||'cash';document.getElementById('supplier-master-phone').value=supplier.phone||'';document.getElementById('supplier-master-account').value=supplier.account||'';document.getElementById('supplier-master-tax').value=supplier.taxNumber||'';document.getElementById('supplier-master-notes').value=supplier.notes||'';
}
function applySupplierToInvoice(){
  const id=document.getElementById('invoice-supplier-master').value,supplier=(loadLocalDb().suppliers||[]).find(item=>String(item.id)===String(id));if(!supplier)return;
  document.getElementById('invoice-supplier-name').value=supplier.name;document.getElementById('invoice-supplier-phone').value=supplier.phone||'';document.getElementById('invoice-supplier-account').value=supplier.account||'';document.getElementById('invoice-payment-type').value=supplier.paymentType||'cash';handlePaymentTypeChange();
}

// ===== PURCHASE REQUESTS & SIGNATURES =====
let purchaseRequestItems=[];
let purchaseInventoryCatalog=[];
function purchaseDepartmentLabel(value){return departmentLabel(value);}
function isCleaningInventoryItem(name){
  const value=normalizeSearchText(name||'');
  return ['صابون','كلور','ديتول','منظف','مطهر','معطر','مزيل','مسحوق','اريال','داوني','منعم','شاور يد','مقشه','مقشة','شرشوب','مساحه','مساحة','جردل','جاروف','فرشه','فرشة','اسفنج','ليفه','ليفة','سلك مواعين','فوط','مناديل','اكياس زباله','اكياس زبالة','كمام','جوانتي','ديسبنسر','بيروسول','فلاش','كروكس','اكياس شحن','مبيد','مكنسه','مكنسة'].some(keyword=>value.includes(normalizeSearchText(keyword)));
}
function isOperationInventoryItem(name){
  const value=normalizeSearchText(name||'');
  if(OPERATION_ITEMS.some(item=>normalizeSearchText(item.name)===value))return true;
  return ['فحم','سمن','بهارات','فلفل اسود','كمون','كزبره','كركم','قرفه','ورق لورا','مكعبات مرقه','خميره','بيكنج','نشا','بقسماط','انبوبه غاز','غاز تعبئه','ولاعه','فويل','ورق زبده','اطباق فوم','شرائط تغليف','اكياس حفظ'].some(keyword=>value.includes(normalizeSearchText(keyword)));
}
function inventoryDepartment(name){
  if(isCleaningInventoryItem(name))return 'cleaning';
  if(isOperationInventoryItem(name))return 'operation';
  return 'food';
}
function departmentLabel(value){return value==='cleaning'?'النظافة':value==='operation'?'التشغيل':'التغذية';}
function renderPurchaseSupplierOptions(){
  const select=document.getElementById('pr-supplier');if(!select)return;
  const current=select.value,suppliers=(loadLocalDb().suppliers||[]).filter(item=>item.active!==false).sort((a,b)=>String(a.name||'').localeCompare(String(b.name||''),'ar'));
  select.innerHTML='<option value="">اختر المورد المسجل</option>'+suppliers.map(item=>`<option value="${safeText(item.id)}">${safeText(item.name)} — ${item.paymentType==='credit'?'آجل':'نقدي'}</option>`).join('');
  if(suppliers.some(item=>String(item.id)===String(current)))select.value=current;
}
async function loadPurchaseRequestCatalog(){
  if(!currentUser?.site||!supabase_client)return;
  renderPurchaseSupplierOptions();
  const {data,error}=await supabase_client.from('inventory').select('*').eq('site',currentUser.site).order('stock_date',{ascending:false});
  if(error){showToast('تعذر تحميل رصيد المخزن','error');return;}
  const latest=new Map();
  (data||[]).forEach(row=>{
    const key=normalizeSearchText(row.item_name||'');
    if(key&&!latest.has(key))latest.set(key,row);
  });
  const source=[...INVENTORY_BASE_ITEMS,...CLEANING_ITEMS,...OPERATION_ITEMS];
  const catalog=new Map();
  source.forEach(item=>{
    const key=normalizeSearchText(item.name||'');if(!key||catalog.has(key))return;
    const row=latest.get(key);
    catalog.set(key,{id:row?.id||'',name:item.name,unit:row?.unit||item.unit||'قطعة',currentStock:Number(row?.closing_stock??0),price:Number(row?.price??0),department:row?.department||inventoryDepartment(item.name)});
  });
  latest.forEach((row,key)=>{
    catalog.set(key,{id:row.id||'',name:row.item_name,unit:row.unit||'قطعة',currentStock:Number(row.closing_stock??0),price:Number(row.price??0),department:row.department||inventoryDepartment(row.item_name)});
  });
  purchaseInventoryCatalog=[...catalog.values()].sort((a,b)=>String(a.name).localeCompare(String(b.name),'ar'));
  changePurchaseDepartment(false);
}
function changePurchaseDepartment(clearMismatched=true){
  const department=document.getElementById('pr-department')?.value||'food';
  if(clearMismatched&&purchaseRequestItems.some(item=>(item.department||'food')!==department)){
    purchaseRequestItems=[];renderPurchaseRequestItems();showToast('تم مسح الأصناف السابقة لأن طلب الشراء يجب أن يكون لقسم واحد','success');
  }
  const select=document.getElementById('pr-inventory-item');if(select){
    const rows=purchaseInventoryCatalog.filter(item=>item.department===department);
    select.innerHTML=`<option value="">اختر صنف ${purchaseDepartmentLabel(department)} من المخزن</option>`+rows.map(item=>`<option value="${safeText(item.name)}">${safeText(item.name)} — الرصيد ${Number(item.currentStock||0).toLocaleString('ar-EG')} ${safeText(item.unit)}</option>`).join('');
  }
  ['pr-current-stock','pr-item-unit','pr-item-qty','pr-item-est-price','pr-item-note'].forEach(id=>{const field=document.getElementById(id);if(field)field.value='';});
  const warehouseSelect=document.getElementById('pr-warehouse');
  if(warehouseSelect){
    const options=[...warehouseSelect.options];
    const preferred=department==='cleaning'?options.find(option=>normalizeSearchText(option.textContent).includes('نظاف')):options.find(option=>!normalizeSearchText(option.textContent).includes('نظاف'));
    if(preferred)warehouseSelect.value=preferred.value;
  }
}
function selectPurchaseInventoryItem(){
  const name=document.getElementById('pr-inventory-item')?.value||'',item=purchaseInventoryCatalog.find(row=>row.name===name);
  document.getElementById('pr-current-stock').value=item?`${Number(item.currentStock||0).toLocaleString('ar-EG')} ${item.unit}`:'';
  document.getElementById('pr-item-unit').value=item?.unit||'';
  document.getElementById('pr-item-est-price').value=item?.price>0?item.price:'';
}
function addPurchaseRequestItem(){
  const name=document.getElementById('pr-inventory-item').value,item=purchaseInventoryCatalog.find(row=>row.name===name),department=document.getElementById('pr-department').value,qty=parseFloat(document.getElementById('pr-item-qty').value)||0,estimatedPrice=parseFloat(document.getElementById('pr-item-est-price').value)||0,note=document.getElementById('pr-item-note').value.trim();
  if(!item||qty<=0){showToast('اختر الصنف من المخزن وأدخل الكمية المطلوبة','error');return;}
  const existing=purchaseRequestItems.find(row=>row.name===item.name&&row.department===department);
  if(existing){existing.qty+=qty;existing.currentStock=item.currentStock;existing.estimatedPrice=estimatedPrice||existing.estimatedPrice||0;existing.note=note||existing.note;}
  else purchaseRequestItems.push({inventoryItemId:item.id||'',name:item.name,unit:item.unit||'قطعة',department,departmentLabel:purchaseDepartmentLabel(department),currentStock:Number(item.currentStock||0),qty,estimatedPrice,note});
  ['pr-inventory-item','pr-current-stock','pr-item-unit','pr-item-qty','pr-item-est-price','pr-item-note'].forEach(id=>document.getElementById(id).value='');renderPurchaseRequestItems();
}
function removePurchaseRequestItem(index){purchaseRequestItems.splice(index,1);renderPurchaseRequestItems();}
function renderPurchaseRequestItems(){
  const tbody=document.getElementById('pr-items-tbody');if(!tbody)return;
  tbody.innerHTML=purchaseRequestItems.length?purchaseRequestItems.map((item,index)=>`<tr><td>${index+1}</td><td>${safeText(item.departmentLabel||purchaseDepartmentLabel(item.department))}</td><td><strong>${safeText(item.name)}</strong></td><td>${Number(item.currentStock||0).toLocaleString('ar-EG')} ${safeText(item.unit)}</td><td><strong>${Number(item.qty||0).toLocaleString('ar-EG')}</strong></td><td>${safeText(item.unit)}</td><td>${Number(item.estimatedPrice||0).toFixed(2)} ج</td><td>${(Number(item.qty||0)*Number(item.estimatedPrice||0)).toFixed(2)} ج</td><td>${safeText(item.note||'-')}</td><td><button class="action-btn danger" onclick="removePurchaseRequestItem(${index})">حذف</button></td></tr>`).join(''):'<tr><td colspan="10" style="text-align:center;color:var(--muted);padding:1.5rem">اختر القسم ثم أضف الأصناف من المخزن</td></tr>';
  const total=purchaseRequestItems.reduce((sum,item)=>sum+(Number(item.qty||0)*Number(item.estimatedPrice||0)),0);const totalEl=document.getElementById('pr-estimated-total');if(totalEl)totalEl.textContent=total.toFixed(2)+' جنيه';
}
function purchaseWorkflowHtml(request){
  const step=(label,status)=>`<span class="workflow-step ${status==='approved'?'done':status==='rejected'?'rejected':''}">${status==='approved'?'✅':status==='rejected'?'❌':'⏳'} ${label}</span>`;
  return `<div class="workflow-strip">${step('المشرف',request.supervisorApproval)}${step('الشيف',request.chefApproval)}${step('أمين المخزن',request.warehouseApproval)}${step('المشتريات',request.purchasingApproval)}${step('المدير',request.managerApproval)}</div>`;
}
// كود مرجعي مميّز للموقع في أرقام المستندات.
// كان يأخذ أول حرفين من الاسم، فكل موقع يبدأ بـ«ال» ينتج نفس البادئة
// (الأقصر والمنيا والغردقة والإسكندرية → «ال»). نستخدم كود الموقع المعرّف.
function siteRefCode(siteName){
  const site=(loadLocalDb().sites||[]).find(item=>item.name===siteName);
  const code=String(site?.code||'').replace(/^SITE-/i,'').trim();
  if(code)return code.toUpperCase();
  const clean=String(siteName||'').replace(/^(ال|الـ)/,'').trim();
  return (clean||String(siteName||'موقع')).slice(0,3);
}
function purchaseRequestStatus(request){const stages=[request.chefApproval,request.warehouseApproval,request.purchasingApproval,request.managerApproval];if(stages.includes('rejected'))return 'مرفوض';if(stages.every(status=>status==='approved'))return 'معتمد نهائيًا';return 'بانتظار الاعتماد';}
function submitPurchaseRequest(){
  if(!currentUser){showToast('سجل دخول المشرف أولاً','error');return;}if(!purchaseRequestItems.length){showToast('أضف صنفًا واحدًا على الأقل','error');return;}
  const date=document.getElementById('pr-date').value||todayStr(),neededDate=document.getElementById('pr-needed-date').value||date,department=document.getElementById('pr-department').value,departmentLabel=purchaseDepartmentLabel(department),warehouse=document.getElementById('pr-warehouse').value,supplierId=document.getElementById('pr-supplier').value,supplier=(loadLocalDb().suppliers||[]).find(item=>String(item.id)===String(supplierId)),priority=document.getElementById('pr-priority').value,reason=document.getElementById('pr-reason').value.trim();
  if(!supplier){showToast('اختر المورد من الموردين المسجلين','error');return;}
  if(!reason){showToast('اكتب سبب طلب الشراء','error');return;}
  const db=loadLocalDb();db.purchase_requests=db.purchase_requests||[];const id='PR-'+Date.now();
  const estimatedTotal=purchaseRequestItems.reduce((sum,item)=>sum+(Number(item.qty||0)*Number(item.estimatedPrice||0)),0);
  db.purchase_requests.push({id,requestNo:`${siteRefCode(currentUser.site)}-${date.replaceAll('-','')}-${String(Date.now()).slice(-5)}`,site:currentUser.site,requestDate:date,neededDate,department,departmentLabel,warehouse,supplierId,supplierName:supplier.name,purchasingSupplier:supplier.name,priority,reason,items:purchaseRequestItems.map(item=>({...item})),estimatedTotal,supervisorName:currentUser.name,supervisorApproval:'approved',supervisorTime:new Date().toISOString(),chefApproval:'pending',warehouseApproval:'pending',purchasingApproval:'pending',managerApproval:'pending',status:'بانتظار الاعتماد',createdAt:new Date().toISOString()});
  saveLocalDb(db);purchaseRequestItems=[];document.getElementById('pr-reason').value='';renderPurchaseRequestItems();renderPurchaseRequests();showToast('✅ تم إرسال طلب الشراء لسلسلة الاعتماد','success');
}
function purchaseRequestCard(request,mode){
  const role=IBF_BOOT.user?.roleKey||'',requestArg=safeText(JSON.stringify(request.id));let actions='';
  if(mode==='chef'&&request.chefApproval==='pending')actions=`<div class="ops-actions"><button class="chef-approve-btn" onclick="approvePurchaseRequestByChef(${requestArg},'approved')">اعتماد الشيف</button><button class="chef-reject-btn" onclick="approvePurchaseRequestByChef(${requestArg},'rejected')">رفض</button></div>`;
  else if(mode==='supervisor'&&role==='ibf_storekeeper'&&request.chefApproval==='approved'&&request.warehouseApproval==='pending')actions=`<div class="ops-actions"><button class="chef-approve-btn" onclick="approvePurchaseRequestByWarehouse(${requestArg},'approved')">اعتماد أمين المخزن</button><button class="chef-reject-btn" onclick="approvePurchaseRequestByWarehouse(${requestArg},'rejected')">رفض</button></div>`;
  else if(mode==='supervisor'&&role==='ibf_purchasing'&&request.warehouseApproval==='approved'&&request.purchasingApproval==='pending')actions=`<div class="ops-actions"><button class="chef-approve-btn" onclick="approvePurchaseRequestByPurchasing(${requestArg},'approved')">اعتماد المشتريات</button><button class="chef-reject-btn" onclick="approvePurchaseRequestByPurchasing(${requestArg},'rejected')">رفض</button></div>`;
  else if(mode==='manager'&&request.purchasingApproval==='approved'&&request.managerApproval==='pending')actions=`<div class="ops-actions"><button class="chef-approve-btn" onclick="approvePurchaseRequestByManager(${requestArg},'approved')">الاعتماد النهائي</button><button class="chef-reject-btn" onclick="approvePurchaseRequestByManager(${requestArg},'rejected')">رفض</button></div>`;
  const status=purchaseRequestStatus(request),estimatedTotal=(request.items||[]).reduce((sum,item)=>sum+(Number(item.qty||0)*Number(item.estimatedPrice||0)),0);
  return `<div class="ops-card"><div class="ops-card-head"><div><div class="ops-card-title">طلب ${safeText(request.requestNo)} — ${safeText(request.site)}</div><div class="ops-card-meta">${safeText(request.departmentLabel||purchaseDepartmentLabel(request.department))} · ${safeText(request.requestDate)} · احتياج ${safeText(request.neededDate)} · ${safeText(request.warehouse)} · ${safeText(request.priority)}</div></div><span class="status-badge ${status==='معتمد نهائيًا'?'approved':status==='مرفوض'?'rejected':'pending'}">${status}</span></div>${purchaseWorkflowHtml(request)}<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>الصنف</th><th>الرصيد بالمخزن</th><th>المطلوب شراء</th><th>الوحدة</th><th>السعر التقديري</th></tr></thead><tbody>${(request.items||[]).map(item=>`<tr><td><strong>${safeText(item.name)}</strong></td><td>${item.currentStock===undefined?'غير مسجل':Number(item.currentStock||0).toLocaleString('ar-EG')}</td><td><strong>${Number(item.qty||0).toLocaleString('ar-EG')}</strong></td><td>${safeText(item.unit)}</td><td>${Number(item.estimatedPrice||0).toFixed(2)} ج</td></tr>`).join('')}</tbody></table></div><div class="ops-card-meta">المورد المختار: <strong>${safeText(request.supplierName||request.purchasingSupplier||'غير محدد')}</strong> · التكلفة التقديرية: <strong>${estimatedTotal.toFixed(2)} ج</strong> · السبب: ${safeText(request.reason)} · المشرف: ${safeText(request.supervisorName)}</div>${request.rejectionReason?`<div class="data-integrity-note" style="border-color:var(--red)">سبب الرفض: ${safeText(request.rejectionReason)}</div>`:''}${actions}<button class="action-btn" style="margin-top:.6rem" onclick="printPurchaseRequest(${requestArg})">🖨️ طباعة النموذج</button></div>`;
}
function renderPurchaseRequests(){const box=document.getElementById('purchase-request-list');if(!box||!currentUser)return;const rows=(loadLocalDb().purchase_requests||[]).filter(item=>item.site===currentUser.site).sort((a,b)=>String(b.createdAt).localeCompare(String(a.createdAt)));box.innerHTML=rows.length?rows.map(item=>purchaseRequestCard(item,'supervisor')).join(''):'<div class="empty"><div class="icon">📝</div><div>لا توجد طلبات بعد</div></div>';}
function renderChefPurchaseRequests(){const box=document.getElementById('chef-purchase-requests');if(!box||!currentChef)return;const date=document.getElementById('chef-filter-date')?.value||todayStr(),rows=(loadLocalDb().purchase_requests||[]).filter(item=>item.site===currentChef.site&&item.requestDate===date).sort((a,b)=>String(b.createdAt).localeCompare(String(a.createdAt)));box.innerHTML=rows.length?rows.map(item=>purchaseRequestCard(item,'chef')).join(''):'<div class="empty"><div class="icon">📝</div><div>لا توجد طلبات شراء لهذا اليوم</div></div>';}
function approvePurchaseRequestByChef(id,decision){const db=loadLocalDb(),row=(db.purchase_requests||[]).find(item=>String(item.id)===String(id));if(!row)return;const reason=decision==='rejected'?(prompt('اكتب سبب رفض طلب الشراء')||''):'';if(decision==='rejected'&&!reason)return;row.chefApproval=decision;row.chefName=currentChef?.name||'الشيف';row.chefTime=new Date().toISOString();row.rejectionReason=reason||row.rejectionReason||'';row.status=purchaseRequestStatus(row);saveLocalDb(db);renderChefPurchaseRequests();showToast(decision==='approved'?'✅ تم اعتماد طلب الشراء من الشيف':'تم رفض طلب الشراء','success');}
function renderManagerPurchaseRequests(){const box=document.getElementById('manager-purchase-requests');if(!box)return;const site=document.getElementById('filter-site')?.value||'',rows=(loadLocalDb().purchase_requests||[]).filter(item=>!site||item.site===site).sort((a,b)=>String(b.createdAt).localeCompare(String(a.createdAt)));box.innerHTML=rows.length?rows.map(item=>purchaseRequestCard(item,'manager')).join(''):'<div class="empty"><div class="icon">📝</div><div>لا توجد طلبات شراء</div></div>';}
function approvePurchaseRequestByWarehouse(id,decision){const db=loadLocalDb(),row=(db.purchase_requests||[]).find(item=>String(item.id)===String(id));if(!row)return;if(row.chefApproval!=='approved'){showToast('يجب اعتماد الشيف أولاً','error');return;}const reason=decision==='rejected'?(prompt('اكتب سبب رفض المخزن')||''):'';if(decision==='rejected'&&!reason)return;const warehouse=(db.warehouses||[]).find(item=>item.site===row.site);row.warehouseApproval=decision;row.warehouseName=IBF_BOOT.user?.name||warehouse?.manager||'أمين المخزن';row.warehouseTime=new Date().toISOString();row.rejectionReason=reason||row.rejectionReason||'';row.status=purchaseRequestStatus(row);saveLocalDb(db);renderPurchaseRequests();showToast(decision==='approved'?'✅ تم اعتماد المخزن والطلب بانتظار المشتريات':'تم رفض الطلب من المخزن','success');}
function approvePurchaseRequestByPurchasing(id,decision){const db=loadLocalDb(),row=(db.purchase_requests||[]).find(item=>String(item.id)===String(id));if(!row)return;if(row.warehouseApproval!=='approved'){showToast('يجب اعتماد أمين المخزن أولاً','error');return;}const reason=decision==='rejected'?(prompt('اكتب سبب رفض المشتريات')||''):'';if(decision==='rejected'&&!reason)return;const notes=decision==='approved'?(prompt(`ملاحظات المشتريات للمورد ${row.supplierName||row.purchasingSupplier||'المحدد'} (اختياري)`)||''):'';row.purchasingApproval=decision;row.purchasingName=IBF_BOOT.user?.name||'المشتريات';row.purchasingTime=new Date().toISOString();row.purchasingSupplier=row.supplierName||row.purchasingSupplier||'';row.purchasingNotes=notes;row.rejectionReason=reason||row.rejectionReason||'';row.status=purchaseRequestStatus(row);saveLocalDb(db);renderPurchaseRequests();showToast(decision==='approved'?'✅ تم اعتماد المشتريات والطلب بانتظار المدير':'تم رفض الطلب من المشتريات','success');}
function approvePurchaseRequestByManager(id,decision){const db=loadLocalDb(),row=(db.purchase_requests||[]).find(item=>String(item.id)===String(id));if(!row)return;if(row.purchasingApproval!=='approved'){showToast('يجب اعتماد المشتريات أولاً','error');return;}const reason=decision==='rejected'?(prompt('اكتب سبب رفض المدير')||''):'';if(decision==='rejected'&&!reason)return;row.managerApproval=decision;row.managerName=IBF_BOOT.user?.name||'المدير';row.managerTime=new Date().toISOString();row.rejectionReason=reason||row.rejectionReason||'';row.status=purchaseRequestStatus(row);saveLocalDb(db);renderManagerPurchaseRequests();showToast(decision==='approved'?'✅ تم الاعتماد النهائي لطلب الشراء':'تم رفض الطلب من المدير','success');}
function printPurchaseRequest(id){
  const row=(loadLocalDb().purchase_requests||[]).find(item=>String(item.id)===String(id));if(!row)return;const popup=window.open('','_blank','width=900,height=700');if(!popup){showToast('اسمح بالنوافذ المنبثقة للطباعة','error');return;}
  const approvalLabel=status=>status==='approved'?'معتمد':status==='rejected'?'مرفوض':'بانتظار';
  const estimatedTotal=(row.items||[]).reduce((sum,item)=>sum+(Number(item.qty||0)*Number(item.estimatedPrice||0)),0);
  popup.document.write(`<!doctype html><html lang="ar" dir="rtl"><head><meta charset="utf-8"><title>طلب شراء ${safeText(row.requestNo)}</title><style>body{font-family:Arial;padding:30px;color:#17251f}h1{color:#16835b}table{width:100%;border-collapse:collapse;margin:20px 0}th,td{border:1px solid #ccc;padding:8px;text-align:right}.sign{display:grid;grid-template-columns:repeat(5,1fr);gap:14px;margin-top:45px}.sign div{border-top:1px solid #333;padding-top:8px;text-align:center;font-size:12px}@media print{body{padding:10px}}</style></head><body><h1>I-BUILD — طلب شراء ${safeText(row.departmentLabel||purchaseDepartmentLabel(row.department))}</h1><p>رقم الطلب: ${safeText(row.requestNo)} | الموقع: ${safeText(row.site)} | التاريخ: ${safeText(row.requestDate)} | تاريخ الاحتياج: ${safeText(row.neededDate)}</p><p>المخزن: ${safeText(row.warehouse)} | المورد: ${safeText(row.supplierName||row.purchasingSupplier||'غير محدد')} | الأولوية: ${safeText(row.priority)} | السبب: ${safeText(row.reason)}</p><table><thead><tr><th>#</th><th>الصنف</th><th>رصيد المخزن</th><th>المطلوب شراء</th><th>الوحدة</th><th>سعر تقديري</th><th>الإجمالي</th><th>المواصفات</th></tr></thead><tbody>${(row.items||[]).map((item,index)=>`<tr><td>${index+1}</td><td>${safeText(item.name)}</td><td>${item.currentStock===undefined?'غير مسجل':Number(item.currentStock||0).toLocaleString('ar-EG')}</td><td>${item.qty}</td><td>${safeText(item.unit)}</td><td>${Number(item.estimatedPrice||0).toFixed(2)}</td><td>${(Number(item.qty||0)*Number(item.estimatedPrice||0)).toFixed(2)}</td><td>${safeText(item.note||'')}</td></tr>`).join('')}</tbody><tfoot><tr><th colspan="6">الإجمالي التقديري</th><th colspan="2">${estimatedTotal.toFixed(2)} جنيه</th></tr></tfoot></table>${row.purchasingSupplier?`<p>المورد المختار: ${safeText(row.purchasingSupplier)} | ملاحظات المشتريات: ${safeText(row.purchasingNotes||'-')}</p>`:''}<div class="sign"><div>المشرف<br>${safeText(row.supervisorName)}<br>${approvalLabel(row.supervisorApproval)}</div><div>الشيف<br>${safeText(row.chefName||'-')}<br>${approvalLabel(row.chefApproval)}</div><div>أمين المخزن<br>${safeText(row.warehouseName||'-')}<br>${approvalLabel(row.warehouseApproval)}</div><div>المشتريات<br>${safeText(row.purchasingName||'-')}<br>${approvalLabel(row.purchasingApproval)}</div><div>المدير<br>${safeText(row.managerName||'-')}<br>${approvalLabel(row.managerApproval)}</div></div><script>window.onload=()=>window.print()<\/script></body></html>`);popup.document.close();
}

// ===== RETURNS, RETURN STORE & WASTE =====
// ===== RETURNS STORE =====
// مخزن المرتجعات دفتر حركة: كل يوم يفتح برصيد أمس، والمرتجع يزيده والتحويل ينقصه.
function returnsCatalog(){
  if(purchaseInventoryCatalog.length) return purchaseInventoryCatalog;
  return [...INVENTORY_BASE_ITEMS,...CLEANING_ITEMS,...OPERATION_ITEMS]
    .map(item=>({name:item.name,unit:item.unit,price:0,department:inventoryDepartment(item.name)}));
}
function renderReturnItemOptions(){
  const select=document.getElementById('rw-item-name');if(!select)return;
  const current=select.value,rows=returnsCatalog();
  select.innerHTML='<option value="">اختر الصنف من المخزن</option>'+rows.map(item=>`<option value="${safeText(item.name)}" data-unit="${safeText(item.unit||'قطعة')}" data-price="${Number(item.price||0)}">${safeText(item.name)} — ${departmentLabel(item.department||inventoryDepartment(item.name))}</option>`).join('');
  if([...select.options].some(option=>option.value===current))select.value=current;
}
function selectReturnItem(){
  const select=document.getElementById('rw-item-name'),option=select?.selectedOptions?.[0];
  const unitField=document.getElementById('rw-unit');
  if(unitField)unitField.value=option?.dataset.unit||'';
}
function clearReturnFilters(){
  ['rw-search','rw-from','rw-to'].forEach(id=>{const field=document.getElementById(id);if(field)field.value='';});
  const type=document.getElementById('rw-filter-type');if(type)type.value='';
  renderReturnWaste();
}
function returnsRows(){
  const site=currentUser?.site||'';
  return (loadLocalDb().returns_waste||[]).filter(item=>!site||item.site===site);
}
function filteredReturnsRows(){
  const search=normalizeSearchText(document.getElementById('rw-search')?.value||'');
  const from=document.getElementById('rw-from')?.value||'';
  const to=document.getElementById('rw-to')?.value||'';
  const type=document.getElementById('rw-filter-type')?.value||'';
  return returnsRows().filter(item=>{
    if(type&&item.type!==type)return false;
    if(from&&String(item.date||'')<from)return false;
    if(to&&String(item.date||'')>to)return false;
    if(search&&!normalizeSearchText(item.itemName||'').includes(search))return false;
    return true;
  }).sort((a,b)=>String(b.createdAt).localeCompare(String(a.createdAt)));
}
// رصيد مخزن المرتجعات لكل صنف حتى تاريخ معيّن (شامل)
function returnsBalanceAsOf(date){
  const balance={};
  returnsRows().forEach(item=>{
    if(item.type==='waste')return;
    if(date&&String(item.date||'')>date)return;
    const key=item.itemName+'|'+(item.unit||'');
    if(!balance[key])balance[key]={name:item.itemName,unit:item.unit||'',qty:0,price:Number(item.unitPrice)||0};
    balance[key].qty+=(item.type==='transfer'?-1:1)*(parseFloat(item.qty)||0);
    if(Number(item.unitPrice))balance[key].price=Number(item.unitPrice);
  });
  return balance;
}
function returnsAvailable(name,unit,date){
  const key=name+'|'+(unit||'');
  return returnsBalanceAsOf(date)[key]?.qty||0;
}
async function saveReturnWaste(){
  if(!currentUser){showToast('سجل دخول المشرف أولاً','error');return;}
  if(IBF_BOOT.user?.roleKey!=='ibf_supervisor'&&IBF_BOOT.user?.role!=='manager'){showToast('تسجيل المرتجعات متاح للمشرف فقط','error');return;}
  const date=document.getElementById('rw-date').value||todayStr(),type=document.getElementById('rw-type').value,meal=document.getElementById('rw-meal').value,name=document.getElementById('rw-item-name').value.trim(),unit=document.getElementById('rw-unit').value.trim()||'قطعة',qty=parseFloat(document.getElementById('rw-qty').value)||0,reason=document.getElementById('rw-reason').value.trim(),condition=document.getElementById('rw-condition').value;
  if(!name||qty<=0||!reason){showToast('اختر الصنف وأدخل الكمية والسبب','error');return;}
  let price=0;if(supabase_client){const {data}=await supabase_client.from('inventory').select('*').eq('site',currentUser.site).eq('item_name',name).eq('stock_date',date).single();price=parseFloat(data?.price)||0;}
  if(!price)price=Number(document.getElementById('rw-item-name')?.selectedOptions?.[0]?.dataset.price)||0;
  const db=loadLocalDb();db.returns_waste=db.returns_waste||[];db.returns_waste.push({id:'RW-'+Date.now(),site:currentUser.site,date,type,meal,itemName:name,unit,qty,reason,condition,unitPrice:price,totalCost:qty*price,supervisorName:currentUser.name,createdAt:new Date().toISOString()});saveLocalDb(db);
  ['rw-qty','rw-reason'].forEach(id=>document.getElementById(id).value='');
  document.getElementById('rw-item-name').value='';document.getElementById('rw-unit').value='';
  renderReturnWaste();showToast(type==='return'?'✅ تم الإضافة إلى مخزن المرتجعات':'✅ تم تسجيل الهدر وتكلفته','success');
}
function transferFromReturns(name,unit){
  if(!currentUser){showToast('سجل دخول المشرف أولاً','error');return;}
  if(!canTransferStock()){showToast('⚠️ التحويل من اختصاص الإدارة العليا فقط','error');return;}
  const date=document.getElementById('rw-date').value||todayStr();
  const available=returnsAvailable(name,unit,date);
  if(available<=0){showToast('لا يوجد رصيد لهذا الصنف في مخزن المرتجعات','error');return;}
  const qty=parseFloat(prompt(`الكمية المراد تحويلها من "${name}" — الرصيد المتاح ${available} ${unit}`)||'')||0;
  if(qty<=0)return;
  if(qty>available){showToast(`الكمية أكبر من الرصيد المتاح (${available} ${unit})`,'error');return;}
  const db=loadLocalDb();db.returns_waste=db.returns_waste||[];
  const price=returnsBalanceAsOf(date)[name+'|'+unit]?.price||0;
  db.returns_waste.push({id:'RW-'+Date.now(),site:currentUser.site,date,type:'transfer',meal:'تحويل',itemName:name,unit,qty,reason:'تحويل إلى مخزن الموقع',condition:'صالح لإعادة الصرف',unitPrice:price,totalCost:qty*price,supervisorName:currentUser.name,createdAt:new Date().toISOString()});
  saveLocalDb(db);renderReturnWaste();
  showToast(`✅ تم تحويل ${qty} ${unit} من "${name}" إلى مخزن الموقع`,'success');
}
function renderReturnWaste(){
  const logBox=document.getElementById('return-waste-list'),stockBox=document.getElementById('return-stock-list');
  if(!logBox&&!stockBox)return;
  renderReturnItemOptions();
  const date=document.getElementById('rw-date')?.value||todayStr();
  const rows=filteredReturnsRows();
  const canTransfer=canTransferStock();

  // حركة اليوم: رصيد أمس + وارد اليوم − محوَّل اليوم = رصيد اليوم
  const previous=returnsBalanceAsOf(toDateStr(new Date(new Date(date).getTime()-86400000)));
  const today=returnsBalanceAsOf(date);
  if(stockBox){
    const keys=[...new Set([...Object.keys(previous),...Object.keys(today)])].filter(key=>(today[key]?.qty||0)!==0||(previous[key]?.qty||0)!==0);
    stockBox.innerHTML=keys.length?`<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>الصنف</th><th>رصيد أمس</th><th>وارد اليوم</th><th>محوَّل اليوم</th><th>الرصيد</th>${canTransfer?'<th></th>':''}</tr></thead><tbody>${keys.map(key=>{
      const row=today[key]||previous[key];
      const opening=previous[key]?.qty||0,closing=today[key]?.qty||0;
      const dayRows=returnsRows().filter(item=>item.itemName===row.name&&(item.unit||'')===(row.unit||'')&&item.date===date);
      const inQty=dayRows.filter(item=>item.type==='return').reduce((sum,item)=>sum+(parseFloat(item.qty)||0),0);
      const outQty=dayRows.filter(item=>item.type==='transfer').reduce((sum,item)=>sum+(parseFloat(item.qty)||0),0);
      return `<tr><td><strong>${safeText(row.name)}</strong> <span style="font-size:.7rem;color:var(--muted)">${safeText(row.unit)}</span></td><td>${opening}</td><td style="color:var(--green)">${inQty}</td><td style="color:var(--red)">${outQty}</td><td><strong>${closing}</strong></td>${canTransfer?`<td><button class="action-btn" onclick="transferFromReturns(${safeText(JSON.stringify(row.name))},${safeText(JSON.stringify(row.unit||''))})" ${closing<=0?'disabled':''}>🔐 تحويل</button></td>`:''}</tr>`;
    }).join('')}</tbody></table></div>`:'<div class="empty"><div>لا يوجد رصيد مرتجعات</div></div>';
  }

  const icon=type=>type==='return'?'↩️':type==='transfer'?'🔐':'🗑️';
  if(logBox)logBox.innerHTML=rows.length?`<div class="master-list">${rows.slice(0,60).map(item=>`<div class="master-row"><div><strong>${icon(item.type)} ${safeText(item.itemName)}</strong><span>${safeText(item.date)} · ${safeText(item.reason)} · ${safeText(item.condition)}</span></div><code>${item.qty} ${safeText(item.unit)}</code></div>`).join('')}</div>`:'<div class="empty"><div>لا توجد حركات مطابقة</div></div>';

  const summary=document.getElementById('rw-filter-summary');
  if(summary){
    const returned=rows.filter(item=>item.type==='return'),waste=rows.filter(item=>item.type==='waste'),transfers=rows.filter(item=>item.type==='transfer');
    const wasteCost=waste.reduce((sum,item)=>sum+(parseFloat(item.totalCost)||0),0);
    summary.textContent=`النتائج: ${rows.length} حركة · مرتجعات ${returned.length} · هدر ${waste.length} (${wasteCost.toFixed(2)} ج) · تحويلات ${transfers.length}`;
  }
}
function exportReturnsExcel(){
  if(!ensureExcelReady())return;
  const rows=filteredReturnsRows();
  if(!rows.length){showToast('لا توجد حركات مطابقة للفلتر','error');return;}
  const from=document.getElementById('rw-from')?.value||'',to=document.getElementById('rw-to')?.value||'';
  const wb=XLSX.utils.book_new();
  const log=[['I-BUILD — سجل المرتجعات والهدر'],['الموقع: '+(currentUser?.site||''),'الفترة: '+(from||'البداية')+' إلى '+(to||'النهاية')],[],
    ['التاريخ','النوع','الوجبة','الصنف','الوحدة','الكمية','سعر الوحدة','التكلفة','السبب','الحالة','المسؤول']];
  const label=type=>type==='return'?'مرتجع':type==='transfer'?'تحويل من المرتجعات':'هدر';
  rows.forEach(item=>log.push([item.date,label(item.type),item.meal,item.itemName,item.unit,item.qty,Number(item.unitPrice||0).toFixed(2),Number(item.totalCost||0).toFixed(2),item.reason,item.condition,item.supervisorName]));
  const ws=XLSX.utils.aoa_to_sheet(log);
  ws['!cols']=[{wch:12},{wch:18},{wch:12},{wch:22},{wch:10},{wch:10},{wch:12},{wch:12},{wch:26},{wch:18},{wch:18}];
  XLSX.utils.book_append_sheet(wb,ws,'السجل');

  const balance=returnsBalanceAsOf(to||'');
  const stock=[['رصيد مخزن المرتجعات'],['حتى تاريخ: '+(to||todayStr())],[],['الصنف','الوحدة','الرصيد','سعر الوحدة','القيمة']];
  let total=0;
  Object.values(balance).filter(item=>item.qty!==0).forEach(item=>{const value=item.qty*(item.price||0);total+=value;stock.push([item.name,item.unit,item.qty,Number(item.price||0).toFixed(2),value.toFixed(2)]);});
  stock.push([]);stock.push(['','','','إجمالي القيمة',total.toFixed(2)+' جنيه']);
  const ws2=XLSX.utils.aoa_to_sheet(stock);
  ws2['!cols']=[{wch:22},{wch:10},{wch:12},{wch:12},{wch:14}];
  XLSX.utils.book_append_sheet(wb,ws2,'رصيد المرتجعات');
  XLSX.writeFile(wb,`iBUILD_مرتجعات_${currentUser?.site||''}_${from||'الكل'}_${to||'الكل'}.xlsx`);
  showToast('✅ تم تنزيل كشف المرتجعات Excel','success');
}
function renderManagerReturnsWaste(){const box=document.getElementById('manager-return-waste');if(!box)return;const site=document.getElementById('filter-site')?.value||'',date=document.getElementById('filter-date')?.value||todayStr(),rows=(loadLocalDb().returns_waste||[]).filter(item=>(!site||item.site===site)&&item.date===date),returns=rows.filter(item=>item.type==='return'),waste=rows.filter(item=>item.type==='waste'),transfers=rows.filter(item=>item.type==='transfer'),wasteCost=waste.reduce((sum,item)=>sum+(parseFloat(item.totalCost)||0),0);const typeLabel=type=>type==='return'?'مرتجع':type==='transfer'?'تحويل':'هدر';box.innerHTML=`<div class="stats-row" style="grid-template-columns:repeat(4,1fr);margin-bottom:1rem"><div class="stat-card"><div class="stat-num">${returns.length}</div><div class="stat-lbl">حركات مرتجعات</div></div><div class="stat-card"><div class="stat-num">${waste.length}</div><div class="stat-lbl">حركات هدر</div></div><div class="stat-card"><div class="stat-num">${transfers.length}</div><div class="stat-lbl">تحويلات من المرتجعات</div></div><div class="stat-card"><div class="stat-num">${wasteCost.toFixed(2)} ج</div><div class="stat-lbl">تكلفة الهدر</div></div></div>${rows.length?`<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>الموقع</th><th>النوع</th><th>الصنف</th><th>الكمية</th><th>السبب</th><th>التكلفة</th></tr></thead><tbody>${rows.map(item=>`<tr><td>${safeText(item.site)}</td><td>${typeLabel(item.type)}</td><td>${safeText(item.itemName)}</td><td>${item.qty} ${safeText(item.unit)}</td><td>${safeText(item.reason)}</td><td>${Number(item.totalCost||0).toFixed(2)} ج</td></tr>`).join('')}</tbody></table></div>`:'<div class="empty"><div>لا توجد حركات في هذا التاريخ</div></div>'}`;}

// ===== SUBMIT CLEANING =====
async function submitCleaning(){
  if(!supabase_client){showToast('⚠️ الاتصال بـ Supabase مش مكتمل','error');return;}
  if(!currentUser){showToast('⚠️ سجل دخولك أولاً','error');return;}
  if(!requirePermission('can_submit_meals','⚠️ المدير لم يفتح لك صلاحية صرف النظافة')) return;
  const inputs=document.querySelectorAll('#grid-cleaning input');
  const items=[];
  inputs.forEach(inp=>{if(+inp.value>0)items.push({name:inp.dataset.item,unit:inp.dataset.unit,qty:+inp.value});});
  if(!items.length){showToast('⚠️ أدخل كمية واحدة على الأقل','error');return;}
  const btn=document.getElementById('btn-cleaning');
  btn.disabled=true;btn.innerHTML='<span class="spinner"></span> جاري الحفظ...';
  const {error}=await supabase_client.from('meal_logs').insert({
    meal:'مخزن النظافة', meal_key:'cleaning', items:items,
    students_count:0, admins_count:0, meals_total:0, staff_present:0,
    supervisor_name:currentUser.name, site:currentUser.site,
    log_date:todayStr(), log_time:new Date().toLocaleTimeString('ar-EG')
  });
  btn.disabled=false;btn.innerHTML='✅ تسجيل طلب صرف النظافة';
  if(error){showToast('❌ خطأ: '+error.message,'error');return;}
  inputs.forEach(inp=>inp.value=0);
  showToast(`✅ تم إرسال طلب صرف النظافة للشيف - ${currentUser.site}`,'success');
}

// ===== SUBMIT OPERATION SUPPLIES =====
async function submitOperation(){
  if(!supabase_client){showToast('⚠️ الاتصال بقاعدة البيانات مش مكتمل','error');return;}
  if(!currentUser){showToast('⚠️ سجل دخولك أولاً','error');return;}
  if(!requirePermission('can_submit_meals','⚠️ المدير لم يفتح لك صلاحية صرف التشغيل')) return;
  const inputs=document.querySelectorAll('#grid-operation input');
  const items=[];
  inputs.forEach(inp=>{if(+inp.value>0)items.push({name:inp.dataset.item,unit:inp.dataset.unit,qty:+inp.value});});
  if(!items.length){showToast('⚠️ أدخل كمية واحدة على الأقل','error');return;}
  const btn=document.getElementById('btn-operation');
  btn.disabled=true;btn.innerHTML='<span class="spinner"></span> جاري الحفظ...';
  const {error}=await supabase_client.from('meal_logs').insert({
    meal:'مخزن التشغيل', meal_key:'operation', items:items,
    students_count:0, admins_count:0, meals_total:0, staff_present:0,
    supervisor_name:currentUser.name, site:currentUser.site,
    log_date:todayStr(), log_time:new Date().toLocaleTimeString('ar-EG')
  });
  btn.disabled=false;btn.innerHTML='✅ تسجيل صرف التشغيل';
  if(error){showToast('❌ خطأ: '+error.message,'error');return;}
  inputs.forEach(inp=>inp.value=0);
  showToast(`✅ تم تسجيل صرف التشغيل - ${currentUser.site}`,'success');
}

// ===== SUBMIT =====
async function submitMeal(mealKey,mealLabel){
  if(!supabase_client){showToast('⚠️ الاتصال بـ Supabase مش مكتمل','error');return;}
  if(!currentUser){showToast('⚠️ سجل دخولك أولاً','error');return;}
  if(!requirePermission('can_submit_meals','⚠️ المدير لم يفتح لك صلاحية تسجيل الوجبات')) return;
  const recipeCalculation=validateInlineRecipe(mealKey);
  if(!recipeCalculation)return;
  const inputs=document.querySelectorAll(`#grid-${mealKey} input`);
  const items=[];
  inputs.forEach(inp=>{if(+inp.value>0)items.push({name:inp.dataset.item,unit:inp.dataset.unit,qty:+inp.value});});
  if(!items.length){showToast('⚠️ أدخل كمية واحدة على الأقل','error');return;}
  const students=parseInt(document.getElementById('students-'+mealKey).value)||0;
  const admins=parseInt(document.getElementById('admins-'+mealKey).value)||0;
  const mealsCount=parseInt(document.getElementById('meals-count-'+mealKey).value)||0;
  const staffCount=parseInt(document.getElementById('staff-count-'+mealKey).value)||0;
  if(mealsCount<1){showToast('⚠️ أدخل عدد الوجبات الكلي','error');return;}
  if(students+admins!==mealsCount){showToast('إجمالي الوجبات يجب أن يساوي الطلاب + الإداريين','error');return;}
  const btn=document.getElementById('btn-'+mealKey);
  btn.disabled=true;btn.innerHTML='<span class="spinner"></span> جاري الحفظ...';
  const {error}=await supabase_client.from('meal_logs').insert({
    meal:mealLabel, meal_key:mealKey, items:items,
    students_count:students, admins_count:admins,
    meals_total:mealsCount, staff_present:staffCount,
    recipe:recipeCalculation.rows,
    alternative_allocations:inlineChoiceAllocations[mealKey],
    supervisor_name:currentUser.name, site:currentUser.site,
    log_date:todayStr(), log_time:new Date().toLocaleTimeString('ar-EG')
  });
  btn.disabled=false;btn.innerHTML=`✅ تسجيل صرف ${mealLabel}`;
  if(error){showToast('❌ خطأ: '+error.message,'error');return;}
  inputs.forEach(inp=>inp.value=0);
  ['students','admins','staff-count'].forEach(f=>document.getElementById(f+'-'+mealKey).value='');
  inlineChoiceAllocations[mealKey]={};
  inlineMealTotals[mealKey]=0;
  updateMealHeadcount(mealKey);
  syncStaffCountToMeals();
  showToast(`✅ تم إرسال صرف ${mealLabel} للشيف لاعتماده - ${currentUser.site}`,'success');
}

async function addMealItemsToInventory(items){
  if(!supabase_client || !currentUser || !items.length) return;
  const date = todayStr();
  for(const item of items){
    const {data:existing} = await supabase_client.from('inventory')
      .select('*').eq('site', currentUser.site).eq('item_name', item.name).eq('stock_date', date).single();
    if(existing){
      await supabase_client.from('inventory')
        .update({
          dispensed: (existing.dispensed||0) + item.qty,
          closing_stock: (existing.opening_stock||0) + (existing.additions||0) - ((existing.dispensed||0) + item.qty),
          last_updated: new Date().toISOString()
        })
        .eq('id', existing.id);
    } else {
      await supabase_client.from('inventory').insert({
        site: currentUser.site,
        item_name: item.name,
        unit: item.unit,
        price: 0,
        opening_stock: 0,
        additions: 0,
        dispensed: item.qty,
        closing_stock: -item.qty,
        stock_date: date,
        last_updated: new Date().toISOString()
      });
    }
  }
}

async function addApprovedLogToInventory(log){
  if(!supabase_client||!log||(log.inventory_posted===true)) return;
  const date=log.log_date||todayStr();
  for(const item of (log.items||[])){
    const {data:existing}=await supabase_client.from('inventory')
      .select('*').eq('site',log.site).eq('item_name',item.name).eq('stock_date',date).single();
    if(existing){
      const dispensed=(existing.dispensed||0)+(parseFloat(item.qty)||0);
      await supabase_client.from('inventory').update({dispensed,closing_stock:(existing.opening_stock||0)+(existing.additions||0)+(existing.transfer_in||0)-dispensed-(existing.transfer_out||0),last_updated:new Date().toISOString()}).eq('id',existing.id);
    }else{
      const qty=parseFloat(item.qty)||0;
      await supabase_client.from('inventory').insert({site:log.site,item_name:item.name,unit:item.unit||'قطعة',price:0,opening_stock:0,additions:0,dispensed:qty,closing_stock:-qty,stock_date:date,last_updated:new Date().toISOString()});
    }
  }
  await supabase_client.from('meal_logs').update({inventory_posted:true}).eq('id',log.id);
}

async function renderWarehouseApprovals(){
  const box=document.getElementById('warehouse-approval-container');if(!box||!supabase_client)return;
  const date=document.getElementById('filter-date')?.value||todayStr(),site=document.getElementById('filter-site')?.value||'';
  let query=supabase_client.from('meal_logs').select('*').eq('log_date',date).order('created_at',{ascending:false});if(site)query=query.eq('site',site);
  const {data,error}=await query;if(error){box.innerHTML='<div class="empty"><div>تعذر تحميل اعتمادات المخزن</div></div>';return;}
  const rows=(data||[]).filter(log=>log.chef_status==='approved'&&log.inventory_posted!==true&&log.warehouse_status!=='rejected');
  box.innerHTML=rows.length?rows.map(log=>`<div class="ops-card"><div class="ops-card-head"><div><div class="ops-card-title">${safeText(log.meal)} — ${safeText(log.site)}</div><div class="ops-card-meta">المشرف: ${safeText(log.supervisor_name)} · اعتماد الشيف: ${safeText(log.chef_name||'الشيف')} · ${safeText(log.log_time)}</div></div><span class="status-badge pending">بانتظار المخزن</span></div><div class="chef-items-list">${(log.items||[]).map(item=>`<div class="chef-item-tag">${safeText(item.name)} <strong>${item.qty}</strong> ${safeText(item.unit)}</div>`).join('')}</div><div class="ops-actions"><button class="chef-approve-btn" onclick="approveWarehouseIssue(${safeText(JSON.stringify(log.id))},this)">✅ اعتماد الصرف والخصم</button><button class="chef-reject-btn" onclick="rejectWarehouseIssue(${safeText(JSON.stringify(log.id))})">رفض المخزن</button></div></div>`).join(''):'<div class="empty"><div class="icon">✅</div><div>لا توجد طلبات بانتظار اعتماد المخزن</div></div>';
}
async function approveWarehouseIssue(id,button){
  if(!supabase_client)return;
  if(button){button.disabled=true;button.textContent='جاري فحص الرصيد والترحيل...';}
  try{
    if(IBF_BOOT.restUrl){
      await apiFetch('warehouse-issue',{method:'POST',body:JSON.stringify({id})});
    }else{
      const {data:log,error}=await supabase_client.from('meal_logs').select('*').eq('id',id).single();
      if(error||!log)throw new Error('تعذر تحميل طلب الصرف');
      if(log.chef_status!=='approved')throw new Error('يجب اعتماد الشيف أولاً');
      if(log.inventory_posted===true)throw new Error('تم خصم طلب الصرف من قبل');
      const check=await validateWarehouseStock(log);
      if(!check.ok)throw new Error(check.message);
      await addApprovedLogToInventory(log);
      const result=await supabase_client.from('meal_logs').update({warehouse_approved:true,warehouse_status:'approved',warehouse_name:'أمين المخزن',warehouse_time:new Date().toLocaleTimeString('ar-EG')}).eq('id',id);
      if(result.error)throw new Error(result.error.message||'تعذر حفظ اعتماد المخزن');
    }
    showToast('✅ تم اعتماد الصرف وخصمه من المخزن','success');
    renderWarehouseApprovals();renderDailyCost();
  }catch(error){
    if(button){button.disabled=false;button.textContent='✅ اعتماد الصرف والخصم';}
    showToast('❌ '+(error.message||'تعذر اعتماد الصرف'),'error');
  }
}
async function validateWarehouseStock(log){
  const required={};
  (log.items||[]).forEach(item=>{required[item.name]=(required[item.name]||0)+(parseFloat(item.qty)||0);});
  const {data,error}=await supabase_client.from('inventory').select('*').eq('site',log.site).eq('stock_date',log.log_date||todayStr());
  if(error)return {ok:false,message:'تعذر فحص رصيد المخزن'};
  for(const [name,qty] of Object.entries(required)){
    const row=(data||[]).find(item=>item.item_name===name),available=row?computeClosing(row):0;
    if(qty>available+0.000001)return {ok:false,message:`الرصيد غير كافٍ للصنف ${name}: المتاح ${available} والمطلوب ${qty}`};
  }
  return {ok:true};
}
async function rejectWarehouseIssue(id){
  const reason=prompt('اكتب سبب رفض أمين المخزن')||'';if(!reason)return;const result=await supabase_client.from('meal_logs').update({warehouse_approved:false,warehouse_status:'rejected',warehouse_notes:reason,warehouse_time:new Date().toLocaleTimeString('ar-EG')}).eq('id',id);if(result.error){showToast('تعذر حفظ الرفض','error');return;}showToast('تم رفض الصرف من المخزن','info');renderWarehouseApprovals();
}
async function renderDailyCost(){
  const box=document.getElementById('daily-cost-container');if(!box||!supabase_client)return;const date=document.getElementById('filter-date')?.value||todayStr(),site=document.getElementById('filter-site')?.value||'';
  let query=supabase_client.from('inventory').select('*').eq('stock_date',date);if(site)query=query.eq('site',site);const {data,error}=await query;if(error){box.innerHTML='<div class="empty"><div>تعذر حساب التكلفة</div></div>';return;}
  const rows=data||[],dispensedCost=rows.reduce((sum,row)=>sum+(parseFloat(row.dispensed)||0)*(parseFloat(row.price)||0),0),wasteRows=(loadLocalDb().returns_waste||[]).filter(item=>item.date===date&&item.type==='waste'&&(!site||item.site===site)),wasteCost=wasteRows.reduce((sum,item)=>sum+(parseFloat(item.totalCost)||0),0),totalMeals=(lastLogs||[]).filter(log=>!site||log.site===site).reduce((sum,log)=>sum+(parseFloat(log.meals_total)||0),0),costPerMeal=totalMeals>0?(dispensedCost+wasteCost)/totalMeals:0;
  box.innerHTML=`<div class="stats-row" style="grid-template-columns:repeat(4,1fr);margin-bottom:.8rem"><div class="stat-card"><div class="stat-num">${dispensedCost.toFixed(2)} ج</div><div class="stat-lbl">تكلفة المنصرف بمتوسط السعر</div></div><div class="stat-card"><div class="stat-num">${wasteCost.toFixed(2)} ج</div><div class="stat-lbl">تكلفة الهدر</div></div><div class="stat-card"><div class="stat-num">${totalMeals}</div><div class="stat-lbl">عدد الوجبات</div></div><div class="stat-card"><div class="stat-num">${costPerMeal.toFixed(2)} ج</div><div class="stat-lbl">متوسط تكلفة الوجبة</div></div></div><div class="data-integrity-note">يتم احتساب قيمة المنصرف باستخدام متوسط سعر الصنف المرجح عند إضافة كل فاتورة مشتريات.</div>`;
}

async function getMealDispensedTotals(date, site){
  if(!supabase_client) return {};
  let q = supabase_client.from('meal_logs').select('*').eq('log_date', date);
  if(site) q = q.eq('site', site);
  const {data, error} = await q;
  if(error || !data) return {};
  const totals = {};
  data.forEach(log=>{
    if(log.inventory_posted!==true) return;
    (log.items||[]).forEach(item=>{
      const key = item.name;
      if(!totals[key]) totals[key] = {qty:0, unit:item.unit||''};
      totals[key].qty += parseFloat(item.qty)||0;
      if(item.unit) totals[key].unit = item.unit;
    });
  });
  return totals;
}

// ===== DASHBOARD =====
async function renderDashboard(){
  if(!supabase_client) return;
  const date=document.getElementById('filter-date').value||todayStr();
  const site=document.getElementById('filter-site').value;
  let query=supabase_client.from('meal_logs').select('*').eq('log_date',date).order('created_at',{ascending:false});
  if(site) query=query.eq('site',site);
  const {data,error}=await query;
  if(error){showToast('خطأ في جلب البيانات','error');return;}
  lastLogs=data||[];
  document.getElementById('stat-students').textContent=lastLogs.reduce((s,l)=>s+(l.students_count||0),0);
  document.getElementById('stat-admins').textContent=lastLogs.reduce((s,l)=>s+(l.admins_count||0),0);
  document.getElementById('stat-meals').textContent=lastLogs.reduce((s,l)=>s+(l.meals_total||0),0);
  document.getElementById('stat-sites').textContent=new Set(lastLogs.map(l=>l.site)).size;
  renderDailyCost();
  Promise.resolve(renderProfitability()).catch(err=>console.error(err));
  renderWarehouseApprovals();
  renderManagerPurchaseRequests();
  renderManagerCashboxApprovals();
  renderManagerReturnsWaste();
  renderSupplierAccounts();
  renderPermissionsPanel();
  const container=document.getElementById('log-container');
  if(!lastLogs.length){
    container.innerHTML='<div class="empty"><div class="icon">📭</div><div>لا يوجد تسجيلات</div></div>';
    renderManagerInventory();
    return;
  }

  // group by site then meal
  const siteGroups={};
  lastLogs.forEach(log=>{
    if(!siteGroups[log.site]) siteGroups[log.site]={};
    if(!siteGroups[log.site][log.meal]) siteGroups[log.site][log.meal]=[];
    siteGroups[log.site][log.meal].push(log);
  });
  const mealEmoji={فطار:'🌅',غداء:'☀️',عشاء:'🌙','مخزن النظافة':'🧹'};
  let html='';
  for(const [site,meals] of Object.entries(siteGroups)){
    html+=`<div style="margin-bottom:2rem;"><div style="font-size:1rem;font-weight:900;color:var(--blue);margin-bottom:1rem;padding-bottom:.5rem;border-bottom:1px solid var(--border);">📍 ${site}</div>`;
    for(const [meal,entries] of Object.entries(meals)){
      const s=entries.reduce((a,e)=>a+(e.students_count||0),0);
      const adm=entries.reduce((a,e)=>a+(e.admins_count||0),0);
      const mt=entries.reduce((a,e)=>a+(e.meals_total||0),0);
      const sp=entries.reduce((a,e)=>a+(e.staff_present||0),0);
      const totals={};
      entries.forEach(e=>(e.items||[]).forEach(it=>{if(!totals[it.name])totals[it.name]={qty:0,unit:it.unit};totals[it.name].qty+=it.qty;}));
      html+=`<div class="meal-group">
        <div class="meal-group-header">
          <span class="badge badge-${meal}">${mealEmoji[meal]||''} ${meal}</span>
          <div style="display:flex;gap:.4rem;flex-wrap:wrap">
            <div class="tag">👨‍🎓 طلاب <strong>${s}</strong></div>
            <div class="tag">👔 إداريين <strong>${adm}</strong></div>
            <div class="tag">🍽️ وجبات <strong>${mt}</strong></div>
            <div class="tag">👥 أصناف موجودة <strong>${sp}</strong></div>
          </div>
        </div>
        <table class="log-table">
          <thead><tr><th>الوقت</th><th>المشرف</th><th>الصنف</th><th>الكمية</th><th>الوحدة</th><th>الشيف</th><th>المخزن</th></tr></thead>
          <tbody>`;
      entries.forEach(log=>{
        const chefSt=log.chef_status==='approved'?'✅':log.chef_status==='rejected'?'❌':'⏳';
        (log.items||[]).forEach((item,i)=>{
          html+=`<tr>
            <td>${i===0?`<span style="color:var(--muted);font-size:.8rem">${log.log_time}</span>`:''}</td>
            <td>${i===0?log.supervisor_name:''}</td>
            <td>${item.name}</td>
            <td><strong style="color:var(--green)">${item.qty}</strong></td>
            <td style="color:var(--muted)">${item.unit}</td>
            <td>${i===0?('<span title="'+(log.chef_notes||'')+'">' + chefSt + '</span>'):''}</td>
            <td>${i===0?(log.inventory_posted===true?'✅':log.warehouse_status==='rejected'?'❌':'⏳'):''}</td>
          </tr>`;
        });
      });
      html+=`</tbody></table>
        <div style="margin-top:.6rem;padding:.6rem 1rem;background:var(--card2);border-radius:8px;font-size:.8rem;color:var(--muted);">
          📊 ملخص: `;
      for(const [name,{qty,unit}] of Object.entries(totals)){
        html+=`<span style="margin-left:.8rem;color:var(--text)">${name} <strong style="color:var(--green)">${qty}</strong> ${unit}</span>`;
      }
      html+=`</div></div>`;
    }
    html+=`</div>`;
  }
  container.innerHTML=html;
  renderManagerInventory();
}

async function renderPermissionsPanel(){
  const container=document.getElementById('permissions-container');
  if(!container || !supabase_client) return;
  const codes=Object.keys(SUPERVISORS);
  const rows=[];
  for(const code of codes){
    const user=SUPERVISORS[code];
    const p=await fetchPermissionFor(code);
    rows.push({code,user,p});
  }
  container.innerHTML=`<div style="overflow-x:auto"><table class="inv-table">
    <thead><tr><th>المشرف</th><th>الموقع</th><th>تسجيل الوجبات</th><th>تعديل المخزن</th><th>إضافة فواتير</th><th>الجودة</th><th>HR والحضور</th><th>المعدات والصيانة</th><th>حفظ</th></tr></thead>
    <tbody>
      ${rows.map(({code,user,p})=>`<tr>
        <td><strong>${user.name}</strong><div style="font-size:.72rem;color:var(--muted)">${code}</div></td>
        <td>${user.site}</td>
        <td><input type="checkbox" id="perm-${code}-meals" ${p.can_submit_meals?'checked':''}></td>
        <td><input type="checkbox" id="perm-${code}-inventory" ${p.can_edit_inventory?'checked':''}></td>
        <td><input type="checkbox" id="perm-${code}-invoice" ${p.can_add_invoice?'checked':''}></td>
        <td><input type="checkbox" id="perm-${code}-quality" ${p.can_manage_quality?'checked':''}></td>
        <td><input type="checkbox" id="perm-${code}-hr" ${p.can_manage_hr?'checked':''}></td>
        <td><input type="checkbox" id="perm-${code}-equipment" ${p.can_manage_equipment?'checked':''}></td>
        <td><button class="action-btn" onclick="saveSupervisorPermissions('${code}')">حفظ</button></td>
      </tr>`).join('')}
    </tbody>
  </table></div>`;
}

async function saveSupervisorPermissions(code){
  if(!supabase_client) return;
  const user=SUPERVISORS[code];
  const row={
    supervisor_code:code,
    supervisor_name:user.name,
    site:user.site,
    can_submit_meals:document.getElementById(`perm-${code}-meals`).checked,
    can_edit_inventory:document.getElementById(`perm-${code}-inventory`).checked,
    can_add_invoice:document.getElementById(`perm-${code}-invoice`).checked,
    can_manage_quality:document.getElementById(`perm-${code}-quality`).checked,
    can_manage_hr:document.getElementById(`perm-${code}-hr`).checked,
    can_manage_equipment:document.getElementById(`perm-${code}-equipment`).checked,
    updated_at:new Date().toISOString()
  };
  const {error}=await supabase_client.from('app_permissions').upsert(row,{onConflict:'supervisor_code'});
  if(error){showToast('❌ خطأ في حفظ الصلاحيات: '+error.message,'error');return;}
  showToast('✅ تم حفظ صلاحيات '+user.name,'success');
}

// ===== SECTION RESET =====
// الجداول المحلية تُحفظ عبر snapshot، والباقي عبر REST
const RESET_TARGETS={
  meal_logs:{label:'سجل صرف الوجبات',dateField:'log_date',local:false},
  inventory:{label:'كشف المخزن',dateField:'stock_date',local:false},
  invoices:{label:'فواتير المشتريات',dateField:'invoice_date',local:false},
  menus:{label:'المنيو والريسيبي',dateField:'service_date',local:false},
  cashbox_transactions:{label:'حركات الخزنة',dateField:'transaction_date',local:false},
  quality_checks:{label:'فحوصات الجودة',dateField:'check_date',local:false},
  attendance:{label:'الحضور والانصراف',dateField:'work_date',local:false},
  employees:{label:'كشف العاملين',dateField:'',local:false},
  equipment:{label:'كشف المعدات',dateField:'',local:false},
  maintenance_logs:{label:'سجل الصيانة',dateField:'maintenance_date',local:false},
  stock_transfers:{label:'سجل التحويلات',dateField:'date',local:false},
  purchase_requests:{label:'طلبات الشراء',dateField:'requestDate',local:true},
  returns_waste:{label:'المرتجعات والهدر',dateField:'date',local:true},
  suppliers:{label:'دليل الموردين',dateField:'',local:true}
};
// نسخة احتياطية تُنزَّل تلقائيًا قبل أي حذف — فشلها يوقف الحذف
function downloadResetBackup(type,label,victims,scope,date,site){
  try{
    const payload={
      app:'I-BUILD Food Operations',
      version:2,
      kind:'section_reset_backup',
      section:type,
      sectionLabel:label,
      scope:scope==='day'?`تاريخ ${date}`:'كل التواريخ',
      site:site||'كل المواقع',
      recordCount:victims.length,
      exportedAt:new Date().toISOString(),
      exportedBy:IBF_BOOT.user?.name||IBF_BOOT.user?.username||'',
      data:{[type]:victims}
    };
    const blob=new Blob([JSON.stringify(payload,null,2)],{type:'application/json;charset=utf-8'});
    const url=URL.createObjectURL(blob),link=document.createElement('a');
    link.href=url;
    link.download=`I-BUILD_نسخة_قبل_الرسيت_${type}_${date}.json`;
    document.body.appendChild(link);link.click();link.remove();
    setTimeout(()=>URL.revokeObjectURL(url),1000);
    return true;
  }catch(error){
    console.error(error);
    return false;
  }
}
function resetScopeChoice(label,total,dayCount,date){
  const answer=(prompt(`رسيت ${label}\n\nاكتب 1 لمسح بيانات ${date} فقط (${dayCount} سجل)\nاكتب 2 لمسح كل بيانات القسم (${total} سجل)\n\nاترك الخانة فارغة للإلغاء`)||'').trim();
  const normalized=normalizeDigits(answer);
  if(normalized==='1')return'day';
  if(normalized==='2')return'all';
  return null;
}
async function resetSection(type){
  const target=RESET_TARGETS[type];if(!target){showToast('قسم غير معروف','error');return;}
  if(!canTransferStock()&&!IBF_BOOT.user?.permissions?.can_manage_users){showToast('⚠️ الرسيت للمدير فقط','error');return;}
  const date=document.getElementById('filter-date')?.value||todayStr();
  const site=document.getElementById('filter-site')?.value||'';

  // 1) اعرض العدد قبل أي حذف
  let rows=[];
  if(target.local){
    rows=(loadLocalDb()[type]||[]).filter(row=>!site||row.site===site);
  }else{
    if(!supabase_client){showToast('الاتصال بقاعدة البيانات مش مكتمل','error');return;}
    let query=supabase_client.from(type).select('*');
    if(site)query=query.eq('site',site);
    const {data,error}=await query;
    if(error){showToast('تعذر قراءة بيانات القسم: '+error.message,'error');return;}
    rows=data||[];
  }
  const inDay=target.dateField?rows.filter(row=>String(row[target.dateField]||'')===date):[];
  if(!rows.length){showToast(`لا توجد بيانات في ${target.label}`,'error');return;}

  // 2) اختيار النطاق
  let scope='all';
  if(target.dateField){
    scope=resetScopeChoice(target.label,rows.length,inDay.length,date);
    if(!scope)return;
  }else{
    if(!confirm(`رسيت ${target.label}\nسيُمسح ${rows.length} سجل${site?' لموقع '+site:''} بالكامل.\nهذا القسم بلا تاريخ فلا يمكن حصر المسح بيوم واحد.`))return;
  }
  const victims=scope==='day'?inDay:rows;
  if(!victims.length){showToast('لا توجد سجلات في هذا النطاق','error');return;}

  // 3) تأكيد صريح بالعدد
  const scopeLabel=scope==='day'?`تاريخ ${date}`:'كل التواريخ';
  if(!confirm(`⚠️ تأكيد نهائي\n\nالقسم: ${target.label}\nالنطاق: ${scopeLabel}${site?'\nالموقع: '+site:'\nالموقع: كل المواقع'}\nعدد السجلات: ${victims.length}\n\nالحذف نهائي ولا يمكن التراجع عنه. متأكد؟`))return;

  // 4) نسخة احتياطية إجبارية قبل الحذف. الخادم يفرض صلاحية الحذف.
  if(!downloadResetBackup(type,target.label,victims,scope,date,site)){
    showToast('❌ تعذر تنزيل النسخة الاحتياطية — أُلغي المسح حفاظًا على البيانات','error');
    return;
  }
  if(!confirm(`✅ تم تنزيل نسخة احتياطية بـ ${victims.length} سجل في مجلد التنزيلات.\n\nتأكد من وصول الملف ثم اضغط موافق لتنفيذ المسح.`))return;

  // 5) التنفيذ
  if(target.local){
    const db=loadLocalDb();
    const doomed=new Set(victims.map(row=>String(row.id)));
    db[type]=(db[type]||[]).filter(row=>!doomed.has(String(row.id)));
    saveLocalDb(db);
  }else{
    for(const row of victims){
      const {error}=await supabase_client.from(type).delete().eq('id',row.id);
      if(error){showToast('توقف المسح عند خطأ: '+error.message,'error');break;}
    }
  }
  showToast(`✅ تم مسح ${victims.length} سجل من ${target.label}`,'success');
  Promise.resolve(renderDashboard()).catch(err=>console.error(err));
}
function resetButton(type){
  const target=RESET_TARGETS[type];
  return `<button class="action-btn danger" onclick="resetSection('${type}')" title="مسح بيانات ${target?.label||''} بباسورد">🗑️ رسيت</button>`;
}

async function clearToday(){
  if(!supabase_client) return;
  const date=document.getElementById('filter-date').value||todayStr();
  const site=document.getElementById('filter-site').value;
  if(!confirm(`هتمسح تسجيلات ${date}${site?' - '+site:''}؟`)) return;
  let q=supabase_client.from('meal_logs').delete().eq('log_date',date);
  if(site) q=q.eq('site',site);
  const {error}=await q;
  if(error){showToast('خطأ في المسح','error');return;}
  showToast('تم المسح','info'); renderDashboard();
}

// ===== CHEF ACCESS =====
function requestChefAccess(btn){
  chefBtn=btn;
  document.getElementById('chef-pwd-overlay').style.display='flex';
  document.getElementById('chef-pwd-input').value='';
  document.getElementById('chef-pwd-error').style.display='none';
  setTimeout(()=>document.getElementById('chef-pwd-input').focus(),100);
}
function checkChefPassword(){
  const val=document.getElementById('chef-pwd-input').value.trim();
  const chef=CHEFS[val];
  if(chef){
    document.getElementById('chef-pwd-overlay').style.display='none';
    currentChef=chef;
    document.getElementById('chef-name-display').textContent='👨‍🍳 '+chef.name;
    document.getElementById('chef-site-display').textContent='📍 موقع '+chef.site;
    document.getElementById('chef-filter-date').value=todayStr();
    document.querySelectorAll('.page').forEach(x=>x.classList.remove('active'));
    document.querySelectorAll('.nav-btn').forEach(x=>x.classList.remove('active'));
    document.getElementById('page-chef').classList.add('active');
    chefBtn.classList.add('active');
    renderChefPage();
  }else{
    document.getElementById('chef-pwd-error').style.display='block';
    document.getElementById('chef-pwd-input').value='';
    document.getElementById('chef-pwd-input').focus();
  }
}
function doChefLogout(){
  if(IBF_BOOT.logoutUrl){window.location.href=IBF_BOOT.logoutUrl;return;}
  document.body.classList.remove('manager-mode');
  currentChef=null;
  document.querySelectorAll('.page').forEach(x=>x.classList.remove('active'));
  document.querySelectorAll('.nav-btn').forEach(x=>x.classList.remove('active'));
  document.getElementById('page-login').classList.add('active');
  document.getElementById('btn-supervisor').classList.add('active');
}

// ===== CHEF PAGE =====
async function renderChefPage(){
  if(!supabase_client||!currentChef) return;
  const date=document.getElementById('chef-filter-date').value||todayStr();
  const {data,error}=await supabase_client.from('meal_logs').select('*').eq('log_date',date).eq('site',currentChef.site).order('created_at',{ascending:false});
  if(error){showToast('خطأ في جلب البيانات','error');return;}
  const logs=data||[];
  const pending=logs.filter(l=>!l.chef_status).length;
  const approved=logs.filter(l=>l.chef_status==='approved').length;
  const rejected=logs.filter(l=>l.chef_status==='rejected').length;
  document.getElementById('chef-stat-pending').textContent=pending;
  document.getElementById('chef-stat-approved').textContent=approved;
  document.getElementById('chef-stat-rejected').textContent=rejected;
  const container=document.getElementById('chef-logs-container');
  renderChefPurchaseRequests();
  if(!logs.length){container.innerHTML='<div class="empty"><div class="icon">📭</div><div>لا يوجد تسجيلات لهذا اليوم</div></div>';return;}
  const mealEmoji={فطار:'🌅',غداء:'☀️',عشاء:'🌙','مخزن النظافة':'🧹'};
  const statusMap={approved:{label:'✅ تمت الموافقة',cls:'approved'},rejected:{label:'❌ مرفوض',cls:'rejected'}};
  let html='';
  logs.forEach(log=>{
    const st=log.chef_status;
    const stInfo=statusMap[st]||null;
    html+=`<div class="chef-log-card ${st||''}" id="card-${safeDomId(log.id)}">
      <div class="chef-log-header">
        <div>
          <div class="chef-log-title">${mealEmoji[log.meal]||''} ${log.meal} - ${log.supervisor_name}</div>
          <div class="chef-log-meta">🕐 ${log.log_time} &nbsp;|&nbsp; 👨‍🎓 ${log.students_count||0} طالب &nbsp;|&nbsp; 👔 ${log.admins_count||0} إداري &nbsp;|&nbsp; 🍽️ ${log.meals_total||0} وجبة</div>
        </div>
        ${stInfo?`<span class="status-badge ${stInfo.cls}">${stInfo.label}</span>`:'<span class="status-badge pending">⏳ في الانتظار</span>'}
      </div>
      <div class="chef-items-list">
        ${(log.items||[]).map(it=>`<div class="chef-item-tag">${it.name} <strong>${it.qty}</strong> ${it.unit}</div>`).join('')}
      </div>
      ${!st?`
      <div class="chef-actions">
        <button class="chef-approve-btn" onclick="chefApprove(${safeText(JSON.stringify(log.id))}, this)">✅ موافقة على الصرف</button>
        <button class="chef-reject-btn" onclick="chefReject(${safeText(JSON.stringify(log.id))}, this)">❌ رفض</button>
      </div>`:''}
      <textarea class="chef-notes-input" id="notes-${log.id}" rows="2" placeholder="ملاحظات الشيف... (مشاكل، أصناف مش اتصرفت، أي تعليق)">${log.chef_notes||''}</textarea>
      ${!st||st?`<button onclick="saveChefNotes(${safeText(JSON.stringify(log.id))})" style="margin-top:.4rem;padding:.4rem .9rem;border-radius:8px;border:1.5px solid var(--blue);background:none;color:var(--blue);font-family:'Cairo',sans-serif;font-size:.8rem;font-weight:700;cursor:pointer;">💾 حفظ الملاحظات</button>`:''}
    </div>`;
  });
  container.innerHTML=html;
}

function safeDomId(id){return String(id).replace(/[^a-zA-Z0-9_-]/g,'_');}
function markChefCardStatus(id, status, label){
  const card=document.getElementById('card-'+safeDomId(id));
  if(!card) return;
  card.classList.remove('approved','rejected');
  card.classList.add(status);
  const badge=card.querySelector('.status-badge');
  if(badge){badge.className='status-badge '+status; badge.textContent=label;}
  const actions=card.querySelector('.chef-actions');
  if(actions) actions.remove();
}

async function chefApprove(id, btn){
  if(!supabase_client){showToast('⚠️ مش متصل','error');return;}
  const notes=document.getElementById('notes-'+id)?.value||'';
  if(btn){btn.disabled=true;btn.textContent='جاري الموافقة...';}
  try{
    if(IBF_BOOT.restUrl){
      await apiFetch('chef-decision',{method:'POST',body:JSON.stringify({id,decision:'approved',notes})});
    }else{
      const {error}=await supabase_client.from('meal_logs').update({chef_status:'approved',chef_notes:notes,chef_name:currentChef?.name||'الشيف',chef_time:new Date().toLocaleTimeString('ar-EG'),warehouse_approved:false}).eq('id',id);
      if(error)throw new Error(error.message);
    }
    markChefCardStatus(id,'approved','✅ تمت الموافقة');
    showToast('✅ وافق الشيف — الطلب بانتظار اعتماد أمين المخزن','success');
    setTimeout(()=>renderChefPage(),500);
  }catch(err){
    console.error(err);
    if(btn){btn.disabled=false;btn.textContent='✅ موافقة على الصرف';}
    showToast('❌ '+(err.message||'تعذر حفظ موافقة الشيف'),'error');
  }
}
async function chefReject(id, btn){
  if(!supabase_client){showToast('⚠️ مش متصل','error');return;}
  const notes=document.getElementById('notes-'+id)?.value||'';
  if(!notes){showToast('⚠️ اكتب سبب الرفض في الملاحظات','error');return;}
  if(btn){btn.disabled=true;btn.textContent='جاري الرفض...';}
  try{
    if(IBF_BOOT.restUrl){
      await apiFetch('chef-decision',{method:'POST',body:JSON.stringify({id,decision:'rejected',notes})});
    }else{
      const {error}=await supabase_client.from('meal_logs').update({chef_status:'rejected',chef_notes:notes,chef_name:currentChef?.name||'الشيف',chef_time:new Date().toLocaleTimeString('ar-EG')}).eq('id',id);
      if(error)throw new Error(error.message);
    }
    markChefCardStatus(id,'rejected','❌ مرفوض');
    showToast('تم الرفض وحفظه','info');
    setTimeout(()=>renderChefPage(),500);
  }catch(err){
    console.error(err);
    if(btn){btn.disabled=false;btn.textContent='❌ رفض';}
    showToast('❌ '+(err.message||'تعذر حفظ رفض الشيف'),'error');
  }
}
async function saveChefNotes(id){
  if(!supabase_client){showToast('⚠️ مش متصل','error');return;}
  const notes=document.getElementById('notes-'+id)?.value||'';
  try{
    const {error}=await supabase_client
      .from('meal_logs')
      .update({chef_notes:notes})
      .eq('id',id);
    if(error){showToast('❌ خطأ: '+error.message,'error');return;}
    showToast('✅ تم حفظ الملاحظات','success');
  }catch(err){
    console.error(err);
    showToast('❌ حفظ الملاحظات تعطل. اعمل Refresh وجرب تاني','error');
  }
}

function ensureExcelReady(){
  if(window.XLSX) return true;
  showToast('⚠️ مكتبة Excel غير متاحة حالياً. تأكد من اتصال الإنترنت ثم افتح الصفحة مرة أخرى.','error');
  return false;
}

// ===== INVENTORY =====
let invItems = []; // {id, item_name, unit, price, opening_stock, additions, closing_stock}

async function loadInventory() {
  if(!supabase_client||!currentUser||!currentUser.site) return;
  invItems = []; renderInventoryTable(); // clear stale data immediately
  const date = document.getElementById('inv-date').value || todayStr();
  const dispensedTotals = await getMealDispensedTotals(date, currentUser.site);

  // Try to load today's inventory
  let {data} = await supabase_client.from('inventory')
    .select('*').eq('site', currentUser.site).eq('stock_date', date);

  if(!data || !data.length) {
    // Load the most recent closing before this date, even if the previous day has no record.
    let {data: historicalRows} = await supabase_client.from('inventory')
      .select('*').eq('site', currentUser.site).order('stock_date',{ascending:false});
    const latestByItem=new Map();
    (historicalRows||[]).filter(row=>String(row.stock_date||'')<date).forEach(row=>{
      if(!latestByItem.has(row.item_name))latestByItem.set(row.item_name,row);
    });
    const yData=[...latestByItem.values()];

    if(yData && yData.length) {
      invItems = yData.map(r=>({
        id: null, item_name: r.item_name, unit: r.unit,
        price: r.price, opening_stock: r.closing_stock,
        additions: 0, dispensed: 0, transfer_in: 0, transfer_out: 0, closing_stock: r.closing_stock,
        department: r.department || inventoryDepartment(r.item_name)
      }));
    } else {
      // Load default items from the three department catalogs
      invItems = [...INVENTORY_BASE_ITEMS, ...CLEANING_ITEMS, ...OPERATION_ITEMS].map(it=>({
        id:null, item_name:it.name, unit:it.unit, price:0,
        opening_stock:0, additions:0, dispensed:0, transfer_in:0, transfer_out:0, closing_stock:0,
        department: inventoryDepartment(it.name)
      }));
    }
  } else {
    invItems = data.map(r=>({
      id:r.id, item_name:r.item_name, unit:r.unit, price:r.price,
      opening_stock:r.opening_stock, additions:r.additions, dispensed:r.dispensed||0,
      transfer_in:r.transfer_in||0, transfer_out:r.transfer_out||0, closing_stock:r.closing_stock,
      department: r.department || inventoryDepartment(r.item_name)
    }));
  }
  Object.entries(dispensedTotals).forEach(([name, info])=>{
    let row = invItems.find(item=>item.item_name===name);
    if(!row){
      row = {id:null, item_name:name, unit:info.unit, price:0, opening_stock:0, additions:0, dispensed:0, transfer_in:0, transfer_out:0, closing_stock:0, department:inventoryDepartment(name)};
      invItems.push(row);
    }
    row.unit = row.unit || info.unit;
    row.dispensed = info.qty;
    row.closing_stock = computeClosing(row);
  });
  renderInventoryTable();
}

function renderInventoryTable() {
  const tbody = document.getElementById('inv-tbody');
  const searchValue = normalizeSearchText(document.getElementById('inv-search')?.value || '');
  const departmentValue = document.getElementById('inv-department')?.value || '';
  const visibleItems = invItems
    .map((item, i)=>({item, i}))
    .filter(({item})=>{
      if(departmentValue && (item.department || inventoryDepartment(item.item_name)) !== departmentValue) return false;
      if(!searchValue) return true;
      return normalizeSearchText(`${item.item_name || ''} ${item.unit || ''}`).includes(searchValue);
    });
  tbody.innerHTML = visibleItems.map(({item, i}) => {
    const closing = computeClosing(item);
    const closingClass = closing < 0 ? 'negative' : '';
    const dept = item.department || inventoryDepartment(item.item_name);
    const deptColor = dept==='cleaning' ? 'var(--blue)' : dept==='operation' ? '#a9741a' : 'var(--green)';
    return `<tr>
      <td><strong>${item.item_name}</strong><br><span style="font-size:.7rem;color:${deptColor}">${departmentLabel(dept)}</span></td>
      <td>${item.unit||''}</td>
      <td><input class="inv-input" style="width:80px" type="number" min="0" step="0.01" value="${item.opening_stock||0}"
        onchange="updateInvItem(${i},'opening_stock',this.value)" placeholder="0"></td>
      <td><input class="inv-input" style="width:80px;border-color:rgba(11,143,163,.5)" type="number" min="0" step="0.01" value="${item.additions||0}"
        onchange="updateInvItem(${i},'additions',this.value)" placeholder="0" readonly></td>
      <td><input class="inv-input" style="width:80px;border-color:rgba(200,74,74,.4)" type="number" min="0" step="0.01" value="${item.dispensed||0}"
        onchange="updateInvItem(${i},'dispensed',this.value)" placeholder="0" readonly title="يُحسب تلقائياً من صرف الوجبات"></td>
      <td style="white-space:nowrap;font-size:.78rem">${(parseFloat(item.transfer_in)||0)?`<span style="color:var(--green)">+${item.transfer_in}</span>`:''}${(parseFloat(item.transfer_in)||0)&&(parseFloat(item.transfer_out)||0)?' / ':''}${(parseFloat(item.transfer_out)||0)?`<span style="color:var(--red)">−${item.transfer_out}</span>`:''}${!(parseFloat(item.transfer_in)||0)&&!(parseFloat(item.transfer_out)||0)?'<span style="color:var(--muted)">—</span>':''}</td>
      <td><span class="inv-closing ${closingClass}">${closing.toFixed(2)}</span></td>
    </tr>`;
  }).join('');
  if(!visibleItems.length){
    tbody.innerHTML = '<tr><td colspan="7" class="empty">لا توجد أصناف مطابقة للبحث</td></tr>';
  }
  document.getElementById('inv-grand-total').textContent = invItems.length + ' صنف';
  const searchCount = document.getElementById('inv-search-count');
  if(searchCount) searchCount.textContent = searchValue ? `${visibleItems.length} / ${invItems.length}` : '';
}

// ===== PURCHASE CASHBOX =====
let cashboxSnapshot={opening:0,deposits:0,expenses:0,balance:0,transactions:[]};
function isCashInType(type){return ['deposit','receivable'].includes(type);}
function cashboxTypeLabel(type){return {deposit:'دائن — إيداع',receivable:'دائن — تحصيل',expense:'مدين — صرف',supplier_payment:'مدين — سداد مورد'}[type]||type;}
function cashboxApprovalStatus(row){
  if(isCashInType(row.type))return row.approval_status==='rejected'?'rejected':'approved';
  return row.approval_status||'approved';
}
function cashboxApprovalLabel(row){return {approved:'معتمد',pending:'بانتظار الإدارة',rejected:'مرفوض'}[cashboxApprovalStatus(row)]||'بانتظار الإدارة';}
function cashboxCountsInBalance(row){return cashboxApprovalStatus(row)==='approved';}
function renderCashboxSupplierOptions(){
  const suppliers=(loadLocalDb().suppliers||[]).filter(item=>item.active!==false).sort((a,b)=>String(a.name||'').localeCompare(String(b.name||''),'ar'));
  const options=suppliers.map(item=>`<option value="${safeText(item.name)}">${safeText(item.paymentType==='credit'?'آجل':'نقدي')}</option>`).join('');
  const cashboxList=document.getElementById('cashbox-supplier-options'),managerList=document.getElementById('manager-supplier-options');
  if(cashboxList)cashboxList.innerHTML=options;if(managerList)managerList.innerHTML=options;
}
function cashboxSiteCode(site){
  const master=(loadLocalDb().sites||[]).find(item=>item.name===site),raw=String(master?.code||'SITE').replace(/^SITE-/i,'');
  return raw.replace(/[^A-Za-z0-9]/g,'').slice(0,6).toUpperCase()||'SITE';
}
function generateCashboxReference(snapshot,date,site){
  const prefix=`CB-${cashboxSiteCode(site)}-${String(date).replaceAll('-','')}-`;
  const sequence=(snapshot.transactions||[]).filter(row=>String(row.reference||'').startsWith(prefix)).reduce((max,row)=>{
    const match=String(row.reference||'').match(/-(\d{4})$/);return Math.max(max,match?Number(match[1]):0);
  },0)+1;
  return prefix+String(sequence).padStart(4,'0');
}
async function getCashboxSnapshot(site){
  const {data:account}=await supabase_client.from('cashbox_accounts').select('*').eq('site',site).single();
  const {data:transactions}=await supabase_client.from('cashbox_transactions').select('*').eq('site',site).order('created_at',{ascending:false});
  const opening=parseFloat(account?.opening_balance)||0;
  const rows=transactions||[];
  const deposits=rows.filter(row=>isCashInType(row.type)&&cashboxCountsInBalance(row)).reduce((sum,row)=>sum+(parseFloat(row.amount)||0),0);
  const expenses=rows.filter(row=>!isCashInType(row.type)&&cashboxCountsInBalance(row)).reduce((sum,row)=>sum+(parseFloat(row.amount)||0),0);
  return {opening,deposits,expenses,balance:opening+deposits-expenses,transactions:rows};
}
function getFilteredCashboxTransactions(){
  const from=document.getElementById('cashbox-filter-from')?.value||'',to=document.getElementById('cashbox-filter-to')?.value||'',type=document.getElementById('cashbox-filter-type')?.value||'',supplier=normalizeSearchText(document.getElementById('cashbox-filter-supplier')?.value||'');
  return (cashboxSnapshot.transactions||[]).filter(row=>(!from||String(row.transaction_date||'')>=from)&&(!to||String(row.transaction_date||'')<=to)&&(!type||row.type===type)&&(!supplier||normalizeSearchText(row.supplier||'').includes(supplier)));
}
function renderCashboxTransactions(){
  const tbody=document.getElementById('cashbox-tbody');if(!tbody)return;
  const rows=getFilteredCashboxTransactions(),approvedIn=rows.filter(row=>isCashInType(row.type)&&cashboxApprovalStatus(row)==='approved').reduce((sum,row)=>sum+Number(row.amount||0),0),approvedOut=rows.filter(row=>!isCashInType(row.type)&&cashboxApprovalStatus(row)==='approved').reduce((sum,row)=>sum+Number(row.amount||0),0),pendingOut=rows.filter(row=>!isCashInType(row.type)&&cashboxApprovalStatus(row)==='pending').reduce((sum,row)=>sum+Number(row.amount||0),0);
  const summary=document.getElementById('cashbox-filter-summary');if(summary)summary.innerHTML=`الحركات المطابقة: <strong>${rows.length.toLocaleString('ar-EG')}</strong> · الداخل المعتمد: <strong style="color:var(--green)">${approvedIn.toFixed(2)} ج</strong> · الصرف المعتمد: <strong style="color:var(--red)">${approvedOut.toFixed(2)} ج</strong> · بانتظار الاعتماد: <strong style="color:#a55e00">${pendingOut.toFixed(2)} ج</strong>`;
  tbody.innerHTML=rows.length?rows.map(row=>{
    const status=cashboxApprovalStatus(row),isIn=isCashInType(row.type),amountColor=status==='pending'?'#a55e00':isIn?'var(--green)':'var(--red)';
    return `<tr><td>${safeText(row.transaction_date||'')}</td><td><span class="badge ${isIn?'approved':'rejected'}">${cashboxTypeLabel(row.type)}</span></td><td><strong>${safeText(row.reference||'-')}</strong>${row.source_reference?`<div style="font-size:.7rem;color:var(--muted)">فاتورة ${safeText(row.source_reference)}</div>`:''}</td><td><span class="status-badge ${status}">${cashboxApprovalLabel(row)}</span></td><td>${safeText(row.category||'-')}</td><td>${safeText(row.supplier||'-')}</td><td>${safeText(row.notes||'-')}</td><td style="font-weight:900;color:${amountColor}">${isIn?'+':'-'}${Number(row.amount||0).toFixed(2)} ج</td><td>${safeText(row.approved_by||row.rejected_by||'-')}</td></tr>`;
  }).join(''):'<tr><td colspan="9" style="text-align:center;padding:2rem;color:var(--muted)">لا توجد حركات مطابقة</td></tr>';
}
async function loadCashbox(){
  if(!currentUser||!supabase_client) return;
  const from=document.getElementById('cashbox-filter-from'),to=document.getElementById('cashbox-filter-to');if(from&&!from.value)from.value=todayStr();if(to&&!to.value)to.value=todayStr();
  renderCashboxSupplierOptions();
  cashboxSnapshot=await getCashboxSnapshot(currentUser.site);
  document.getElementById('cashbox-opening').textContent=cashboxSnapshot.opening.toFixed(2)+' ج';
  document.getElementById('cashbox-in').textContent=cashboxSnapshot.deposits.toFixed(2)+' ج';
  document.getElementById('cashbox-out').textContent=cashboxSnapshot.expenses.toFixed(2)+' ج';
  document.getElementById('cashbox-balance').textContent=cashboxSnapshot.balance.toFixed(2)+' ج';
  document.getElementById('cashbox-opening-input').value=cashboxSnapshot.opening;
  document.getElementById('cashbox-reference').value=generateCashboxReference(cashboxSnapshot,todayStr(),currentUser.site);
  renderCashboxTransactions();
}
async function saveCashboxOpening(){
  if(!currentUser){showToast('سجل دخول المشرف أولاً','error');return;}
  const opening=parseFloat(document.getElementById('cashbox-opening-input').value);
  if(!Number.isFinite(opening)||opening<0){showToast('أدخل رصيد افتتاحي صحيح','error');return;}
  const {error}=await supabase_client.from('cashbox_accounts').upsert({site:currentUser.site,opening_balance:opening,updated_at:new Date().toISOString()},{onConflict:'site'});
  if(error){showToast('تعذر حفظ رصيد الخزنة','error');return;}
  await loadCashbox();showToast('✅ تم حفظ الرصيد الافتتاحي','success');
}
async function saveCashboxTransaction(){
  if(!currentUser){showToast('سجل دخول المشرف أولاً','error');return;}
  const type=document.getElementById('cashbox-type').value;
  const amount=parseFloat(document.getElementById('cashbox-amount').value)||0;
  const category=document.getElementById('cashbox-category').value.trim();
  const supplierInput=document.getElementById('cashbox-supplier').value.trim(),supplierRecord=(loadLocalDb().suppliers||[]).find(item=>normalizeSearchText(item.name)===normalizeSearchText(supplierInput)),supplier=supplierRecord?.name||supplierInput;
  const notes=document.getElementById('cashbox-notes').value.trim();
  if(amount<=0||!category){showToast('أدخل المبلغ والتصنيف','error');return;}
  if(type==='supplier_payment'&&!supplierRecord){showToast('ابحث عن المورد واختره من الموردين المسجلين','error');return;}
  const snapshot=await getCashboxSnapshot(currentUser.site);
  if(!isCashInType(type)&&amount>snapshot.balance){showToast('رصيد الخزنة لا يكفي لتنفيذ الصرف','error');return;}
  const reference=generateCashboxReference(snapshot,todayStr(),currentUser.site),approvalStatus=isCashInType(type)?'approved':'pending',now=new Date().toISOString();
  const {data,error}=await supabase_client.from('cashbox_transactions').insert({site:currentUser.site,type,amount,category,reference,supplier_id:supplierRecord?.id||'',supplier,notes,transaction_date:todayStr(),supervisor_name:currentUser.name,requested_by:currentUser.name,requested_at:now,approval_status:approvalStatus,approved_by:isCashInType(type)?currentUser.name:'',approved_at:isCashInType(type)?now:''});
  if(error){showToast('تعذر حفظ حركة الخزنة','error');return;}
  const saved=Array.isArray(data)?data[0]:data,actualReference=saved?.reference||reference;
  ['cashbox-amount','cashbox-category','cashbox-supplier','cashbox-notes'].forEach(id=>document.getElementById(id).value='');
  await loadCashbox();showToast(isCashInType(type)?`✅ تم تسجيل الحركة بالمستند ${actualReference}`:`✅ تم إرسال المستند ${actualReference} لاعتماد الإدارة`,'success');
}
async function recordCashboxExpenseFromInvoice(reference,amount,supplier,date){
  const snapshot=await getCashboxSnapshot(currentUser.site);
  if(amount>snapshot.balance) return {ok:false,message:`رصيد الخزنة غير كافٍ. المتاح ${snapshot.balance.toFixed(2)} جنيه`};
  if(snapshot.transactions.some(row=>String(row.source_reference||'').toLowerCase()===String(reference).toLowerCase())) return {ok:false,message:'تم إنشاء طلب سداد لهذه الفاتورة من قبل'};
  const supplierRecord=(loadLocalDb().suppliers||[]).find(item=>normalizeSearchText(item.name)===normalizeSearchText(supplier)),documentNo=generateCashboxReference(snapshot,date,currentUser.site),now=new Date().toISOString();
  const {data,error}=await supabase_client.from('cashbox_transactions').insert({site:currentUser.site,type:'supplier_payment',amount,category:'فاتورة مشتريات',reference:documentNo,source_reference:reference,supplier_id:supplierRecord?.id||'',supplier,notes:`طلب سداد الفاتورة ${reference}`,transaction_date:date,supervisor_name:currentUser.name,requested_by:currentUser.name,requested_at:now,approval_status:'pending'});
  const saved=Array.isArray(data)?data[0]:data;
  return error?{ok:false,message:'تعذر إرسال صرف الفاتورة للاعتماد'}:{ok:true,reference:saved?.reference||documentNo};
}
function handleCashboxTypeChange(){
  const type=document.getElementById('cashbox-type')?.value,supplier=document.getElementById('cashbox-supplier'),category=document.getElementById('cashbox-category');
  if(supplier)supplier.placeholder=type==='supplier_payment'?'ابحث واختر المورد المسجل — مطلوب':'ابحث عن المورد (اختياري)';
  if(type==='supplier_payment'&&category&&!category.value)category.value='سداد مورد';
}
function resetCashboxFilters(){
  document.getElementById('cashbox-filter-from').value=todayStr();document.getElementById('cashbox-filter-to').value=todayStr();document.getElementById('cashbox-filter-type').value='';document.getElementById('cashbox-filter-supplier').value='';renderCashboxTransactions();
}
function exportCashboxExcel(){
  if(!ensureExcelReady())return;const rows=getFilteredCashboxTransactions();if(!rows.length){showToast('لا توجد حركات مطابقة للتنزيل','error');return;}
  const from=document.getElementById('cashbox-filter-from').value||'',to=document.getElementById('cashbox-filter-to').value||'',wb=XLSX.utils.book_new(),data=[['I-BUILD — كشف خزنة المشتريات'],['الموقع: '+(currentUser?.site||''),'الفترة: '+from+' إلى '+to],[],['التاريخ','نوع الحركة','رقم المستند','حالة الاعتماد','التصنيف','المورد','ملاحظات','المبلغ','طالب الحركة','اعتمدها']];
  rows.forEach(row=>data.push([row.transaction_date||'',cashboxTypeLabel(row.type),row.reference||'',cashboxApprovalLabel(row),row.category||'',row.supplier||'',row.notes||'',Number(row.amount||0),row.requested_by||row.supervisor_name||'',row.approved_by||row.rejected_by||'']));
  const approvedIn=rows.filter(row=>isCashInType(row.type)&&cashboxApprovalStatus(row)==='approved').reduce((sum,row)=>sum+Number(row.amount||0),0),approvedOut=rows.filter(row=>!isCashInType(row.type)&&cashboxApprovalStatus(row)==='approved').reduce((sum,row)=>sum+Number(row.amount||0),0),pending=rows.filter(row=>!isCashInType(row.type)&&cashboxApprovalStatus(row)==='pending').reduce((sum,row)=>sum+Number(row.amount||0),0);
  data.push([],['','','','','','','إجمالي الداخل المعتمد',approvedIn],['','','','','','','إجمالي الصرف المعتمد',approvedOut],['','','','','','','صرف بانتظار الاعتماد',pending],['','','','','','','الرصيد الحالي للخزنة',cashboxSnapshot.balance]);
  const ws=XLSX.utils.aoa_to_sheet(data);ws['!cols']=[{wch:12},{wch:20},{wch:24},{wch:18},{wch:20},{wch:24},{wch:28},{wch:14},{wch:20},{wch:20}];XLSX.utils.book_append_sheet(wb,ws,'الخزنة');XLSX.writeFile(wb,`iBUILD_خزنة_${currentUser?.site||''}_${from}_${to}.xlsx`);showToast('✅ تم تنزيل كشف الخزنة Excel','success');
}
async function renderManagerCashboxApprovals(){
  const box=document.getElementById('manager-cashbox-approvals');if(!box||!supabase_client)return;const site=document.getElementById('filter-site')?.value||'';
  let query=supabase_client.from('cashbox_transactions').select('*').order('created_at',{ascending:false});if(site)query=query.eq('site',site);const {data,error}=await query;if(error){box.innerHTML='<div class="empty"><div>تعذر تحميل طلبات صرف الخزنة</div></div>';return;}
  const rows=(data||[]).filter(row=>!isCashInType(row.type)&&cashboxApprovalStatus(row)==='pending');
  box.innerHTML=rows.length?`<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>الموقع</th><th>التاريخ</th><th>المستند</th><th>النوع</th><th>المورد / البيان</th><th>طالب الصرف</th><th>المبلغ</th><th>قرار الإدارة</th></tr></thead><tbody>${rows.map(row=>{const rowArg=safeText(JSON.stringify(row.id));return `<tr><td>${safeText(row.site)}</td><td>${safeText(row.transaction_date)}</td><td><strong>${safeText(row.reference)}</strong>${row.source_reference?`<div style="font-size:.7rem;color:var(--muted)">فاتورة ${safeText(row.source_reference)}</div>`:''}</td><td>${cashboxTypeLabel(row.type)}</td><td><strong>${safeText(row.supplier||row.category||'-')}</strong><div style="font-size:.72rem;color:var(--muted)">${safeText(row.category||'')} · ${safeText(row.notes||'')}</div></td><td>${safeText(row.requested_by||row.supervisor_name||'-')}</td><td style="color:var(--red);font-weight:900">${Number(row.amount||0).toFixed(2)} ج</td><td><div class="ops-actions"><button class="chef-approve-btn" onclick="approveCashboxTransaction(${rowArg},'approved')">اعتماد الصرف</button><button class="chef-reject-btn" onclick="approveCashboxTransaction(${rowArg},'rejected')">رفض</button></div></td></tr>`}).join('')}</tbody></table></div>`:'<div class="empty"><div class="icon">✅</div><div>لا توجد مصروفات معلقة للاعتماد</div></div>';
}
async function approveCashboxTransaction(id,decision){
  const {data:row,error:loadError}=await supabase_client.from('cashbox_transactions').select('*').eq('id',id).single();if(loadError||!row){showToast('تعذر العثور على حركة الخزنة','error');return;}if(cashboxApprovalStatus(row)!=='pending'){showToast('تم اتخاذ قرار على هذه الحركة من قبل','error');return;}
  if(decision==='approved'){const snapshot=await getCashboxSnapshot(row.site);if(Number(row.amount||0)>snapshot.balance){showToast(`رصيد خزنة ${row.site} غير كافٍ. المتاح ${snapshot.balance.toFixed(2)} جنيه`,'error');return;}}
  const reason=decision==='rejected'?(prompt('اكتب سبب رفض الصرف')||''):'';if(decision==='rejected'&&!reason)return;const now=new Date().toISOString(),manager=IBF_BOOT.user?.name||'المدير',payload={approval_status:decision,approval_notes:reason,approved_by:decision==='approved'?manager:'',approved_at:decision==='approved'?now:'',rejected_by:decision==='rejected'?manager:'',rejected_at:decision==='rejected'?now:''};
  const {error}=await supabase_client.from('cashbox_transactions').update(payload).eq('id',id);if(error){showToast('تعذر حفظ اعتماد الخزنة','error');return;}
  if(row.source_reference){await supabase_client.from('invoices').update({cash_approval_status:decision,cash_approval_by:manager,cash_approval_at:now}).eq('site',row.site).eq('invoice_number',row.source_reference);}
  await renderManagerCashboxApprovals();await renderSupplierAccounts();showToast(decision==='approved'?'✅ تم اعتماد الصرف وخصمه وترحيله لحساب المورد':'تم رفض طلب الصرف','success');
}

// ===== INVOICE =====
let invoiceItems = [];

function getInvoiceTemplateItems(){
  const mealLabels={breakfast:'فطار',lunch:'غداء',dinner:'عشاء'};
  const seen={};
  const rows=[];
  ['breakfast','lunch','dinner'].forEach(meal=>{
    MEALS[meal].forEach(item=>{
      const key=item.name;
      if(seen[key]){
        seen[key].meal_group += ' / '+mealLabels[meal];
        return;
      }
      const row={name:item.name, unit:item.unit||'قطعة', qty:0, price:0, total:0, meal_group:mealLabels[meal], template:true};
      seen[key]=row;
      rows.push(row);
    });
  });
  return rows;
}

function ensureInvoiceTemplate(){
  if(invoiceItems.length) return;
  invoiceItems = getInvoiceTemplateItems();
}

function resetInvoiceTemplate(){
  invoiceItems = getInvoiceTemplateItems();
  renderInvoiceTable();
}

function getActiveInvoiceItems(){
  return invoiceItems.filter(item=>(parseFloat(item.qty)||0)>0);
}

function upsertInvoiceItem(name, unit, qty, price=0, mealGroup='إضافي'){
  const cleanName = String(name||'').trim();
  if(!cleanName) return false;
  const existing = invoiceItems.find(item=>item.name===cleanName);
  if(existing){
    existing.qty = (parseFloat(existing.qty)||0) + (parseFloat(qty)||0);
    existing.unit = existing.unit || unit || 'قطعة';
    if(price) existing.price = price;
    existing.total = existing.qty * (parseFloat(existing.price)||0);
  }else{
    invoiceItems.push({
      name: cleanName,
      unit: unit || 'قطعة',
      qty: parseFloat(qty)||0,
      price: parseFloat(price)||0,
      total: (parseFloat(qty)||0) * (parseFloat(price)||0),
      meal_group: mealGroup,
      template: false
    });
  }
  return true;
}

function addInvoiceItem(){
  if(!requirePermission('can_add_invoice','⚠️ المدير لم يفتح لك صلاحية إضافة الفواتير')) return;
  ensureInvoiceTemplate();
  const name = document.getElementById('inv-item-name').value.trim();
  const unit = document.getElementById('inv-item-unit').value.trim() || 'قطعة';
  const qty = parseFloat(document.getElementById('inv-item-qty').value)||0;
  const price = parseFloat(document.getElementById('inv-item-price').value)||0;
  if(!name){showToast('أدخل اسم الصنف','error');return;}
  if(!qty){showToast('أدخل الكمية','error');return;}
  upsertInvoiceItem(name, unit, qty, price);
  document.getElementById('inv-item-name').value='';
  document.getElementById('inv-item-unit').value='';
  document.getElementById('inv-item-qty').value='';
  document.getElementById('inv-item-price').value='';
  renderInvoiceTable();
}

function renderInvoiceTable(){
  ensureInvoiceTemplate();
  const tbody = document.getElementById('invoice-tbody');
  const paymentType = document.getElementById('invoice-payment-type')?.value || 'cash';
  let total=0;
  tbody.innerHTML = invoiceItems.map((item,i)=>{
    const t = item.qty * item.price;
    total += t;
    return `<tr>
      <td>${i+1}</td>
      <td><span class="tag">${item.meal_group||'إضافي'}</span></td>
      <td><strong>${item.name}</strong></td>
      <td>${item.unit}</td>
      <td><input class="inv-input" style="width:80px" type="number" min="0" step="0.01" value="${item.qty}" onchange="updateInvoiceItem(${i},'qty',this.value)"></td>
      <td><input class="inv-input" style="width:90px" type="number" min="0" step="0.01" value="${item.price}" onchange="updateInvoiceItem(${i},'price',this.value)"></td>
      <td class="inv-cost">${t.toFixed(2)} ج</td>
      <td>${item.template?'':`<button onclick="removeInvoiceItem(${i})" style="background:none;border:none;color:var(--red);cursor:pointer;font-size:1.1rem;">🗑️</button>`}</td>
    </tr>`;
  }).join('');
  document.getElementById('invoice-total').textContent = total.toFixed(2) + ' جنيه';
  const paidEl=document.getElementById('invoice-paid-amount');
  if(paymentType==='cash'){
    paidEl.value = total.toFixed(2);
    paidEl.readOnly = true;
  }else{
    paidEl.readOnly = false;
  }
  const paid=parseFloat(paidEl.value)||0;
  const remaining=Math.max(total-paid,0);
  document.getElementById('invoice-remaining').textContent = remaining.toFixed(2) + ' جنيه';
}

function handlePaymentTypeChange(){
  renderInvoiceTable();
}

function updateInvoiceItem(i, field, val){
  if(!invoiceItems[i]) return;
  invoiceItems[i][field] = parseFloat(val)||0;
  invoiceItems[i].total = (parseFloat(invoiceItems[i].qty)||0) * (parseFloat(invoiceItems[i].price)||0);
  renderInvoiceTable();
}

function removeInvoiceItem(i){
  invoiceItems.splice(i,1);
  renderInvoiceTable();
}

async function saveInvoice(){
  if(!requirePermission('can_add_invoice','⚠️ المدير لم يفتح لك صلاحية إضافة الفواتير')) return;
  if(!supabase_client||!currentUser){showToast('⚠️ سجل دخولك أولاً','error');return;}
  const activeItems = getActiveInvoiceItems();
  if(!activeItems.length){showToast('⚠️ أدخل كمية لصنف واحد على الأقل','error');return;}
  const date = document.getElementById('invoice-date').value || todayStr();
  const invoiceNumber = document.getElementById('invoice-number').value.trim();
  const supplierName = document.getElementById('invoice-supplier-name').value.trim();
  const supplierPhone = document.getElementById('invoice-supplier-phone').value.trim();
  const supplierAccount = document.getElementById('invoice-supplier-account').value.trim();
  const paymentType = document.getElementById('invoice-payment-type').value;
  const total = activeItems.reduce((s,i)=>s+(i.qty*i.price),0);
  const paidAmount = paymentType==='cash' ? total : (parseFloat(document.getElementById('invoice-paid-amount').value)||0);
  const remainingAmount = total;
  let cashDocumentNo='';
  if(!invoiceNumber){showToast('⚠️ أدخل رقم الفاتورة أو المستند','error');return;}
  if(!supplierName){showToast('⚠️ أدخل اسم المورد','error');return;}
  if(paidAmount>total){showToast('المدفوع لا يمكن أن يزيد عن إجمالي الفاتورة','error');return;}
  if(paidAmount>0){
    const cashResult=await recordCashboxExpenseFromInvoice(invoiceNumber,paidAmount,supplierName,date);
    if(!cashResult.ok){showToast(cashResult.message,'error');return;}
    cashDocumentNo=cashResult.reference||'';
  }

  // Save invoice to Supabase
  const {error} = await supabase_client.from('invoices').insert({
    site: currentUser.site,
    invoice_number: invoiceNumber,
    supervisor_name: currentUser.name,
    supplier_name: supplierName,
    supplier_phone: supplierPhone,
    supplier_account: supplierAccount,
    payment_type: paymentType,
    paid_amount: 0,
    requested_paid_amount: paidAmount,
    remaining_amount: remainingAmount,
    cash_document_no: cashDocumentNo,
    cash_approval_status: paidAmount>0?'pending':'not_required',
    items: activeItems,
    total_amount: total,
    invoice_date: date,
    created_at: new Date().toISOString()
  });
  if(error){
    showToast('❌ تعذر حفظ الفاتورة، ولم تتم إضافة أي كمية للمخزن: '+error.message,'error');
    return;
  }

  // Add items to inventory as additions
  for(const item of activeItems){
    const {data:existing} = await supabase_client.from('inventory')
      .select('*').eq('site', currentUser.site).eq('item_name', item.name).eq('stock_date', date).single();

    if(existing){
      const currentQty=Math.max(computeClosing(existing),0),incomingQty=parseFloat(item.qty)||0;
      const weightedPrice=(currentQty+incomingQty)>0?((currentQty*(parseFloat(existing.price)||0))+(incomingQty*(parseFloat(item.price)||0)))/(currentQty+incomingQty):(parseFloat(item.price)||0);
      await supabase_client.from('inventory')
        .update({
          additions: (existing.additions||0) + item.qty,
          price: weightedPrice,
          closing_stock: computeClosing(existing) + incomingQty,
          last_updated: new Date().toISOString()
        })
        .eq('id', existing.id);
    } else {
      await supabase_client.from('inventory').insert({
        site: currentUser.site, item_name: item.name, unit: item.unit,
        price: item.price, opening_stock: 0, additions: item.qty,
        closing_stock: item.qty, stock_date: date,
        last_updated: new Date().toISOString()
      });
    }
  }

  const localDb=loadLocalDb();localDb.suppliers=localDb.suppliers||[];
  if(!localDb.suppliers.some(item=>item.name.toLowerCase()===supplierName.toLowerCase()))localDb.suppliers.push({id:'SUP-'+Date.now(),name:supplierName,paymentType,phone:supplierPhone,account:supplierAccount,active:true,createdAt:new Date().toISOString()});
  saveLocalDb(localDb);renderSuppliersMaster();

  showToast(paidAmount>0?`✅ تم حفظ الفاتورة وإرسال الصرف بالمستند ${cashDocumentNo} لاعتماد الإدارة`:'✅ تم حفظ الفاتورة وإضافة الأصناف للمخزن','success');
  invoiceItems = [];
  document.getElementById('invoice-supplier-name').value='';
  document.getElementById('invoice-number').value='';
  document.getElementById('invoice-supplier-phone').value='';
  document.getElementById('invoice-supplier-account').value='';
  document.getElementById('invoice-payment-type').value='cash';
  document.getElementById('invoice-paid-amount').value='';
  resetInvoiceTemplate();
}

function exportInvoiceExcel(){
  if(!ensureExcelReady()) return;
  const activeItems = getActiveInvoiceItems();
  if(!activeItems.length){showToast('الفاتورة فارغة','error');return;}
  const date = document.getElementById('invoice-date').value || todayStr();
  const invoiceNumber = document.getElementById('invoice-number').value.trim();
  const supplierName = document.getElementById('invoice-supplier-name').value.trim();
  const supplierPhone = document.getElementById('invoice-supplier-phone').value.trim();
  const supplierAccount = document.getElementById('invoice-supplier-account').value.trim();
  const paymentType = document.getElementById('invoice-payment-type').value==='cash'?'كاش':'آجل';
  const paidAmount = parseFloat(document.getElementById('invoice-paid-amount').value)||0;
  const wb = XLSX.utils.book_new();
  const data = [
    ['I-BUILD - فاتورة مشتريات'],
    ['رقم الفاتورة: '+invoiceNumber, 'الموقع: '+(currentUser?.site||''), 'التاريخ: '+date, 'المشرف: '+(currentUser?.name||'')],
    ['المورد: '+supplierName, 'تليفون المورد: '+supplierPhone, 'حساب المورد: '+supplierAccount, 'نوع الدفع: '+paymentType, 'المدفوع: '+paidAmount.toFixed(2)],
    [],
    ['#','الصنف','الوحدة','الكمية','السعر','الإجمالي']
  ];
  let total=0;
  activeItems.forEach((item,i)=>{
    const t=item.qty*item.price;
    total+=t;
    data.push([i+1, item.name, item.unit, item.qty, item.price.toFixed(2), t.toFixed(2)]);
  });
  data.push([]);
  data.push(['','','','','إجمالي الفاتورة', total.toFixed(2)+' جنيه']);
  const ws = XLSX.utils.aoa_to_sheet(data);
  ws['!cols']=[{wch:5},{wch:20},{wch:10},{wch:10},{wch:12},{wch:15}];
  XLSX.utils.book_append_sheet(wb, ws, 'فاتورة مشتريات');
  XLSX.writeFile(wb, `iBUILD_فاتورة_${currentUser?.site||''}_${date}.xlsx`);
  showToast('✅ تم تحميل الفاتورة','success');
}

function updateInvItem(i, field, val) {
  invItems[i][field] = parseFloat(val)||0;
  renderInventoryTable();
}

function addInventoryItem() {
  if(!requirePermission('can_edit_inventory','⚠️ المدير لم يفتح لك صلاحية تعديل المخزن')) return;
  const name = document.getElementById('inv-new-name').value.trim();
  const unit = document.getElementById('inv-new-unit').value.trim() || 'قطعة';
  if(!name){showToast('أدخل اسم الصنف','error');return;}
  if(invItems.find(x=>x.item_name===name)){showToast('الصنف موجود بالفعل','error');return;}
  const department = document.getElementById('inv-department')?.value || inventoryDepartment(name);
  invItems.push({id:null, item_name:name, unit, department, price:0, opening_stock:0, additions:0, dispensed:0, closing_stock:0});
  document.getElementById('inv-new-name').value='';
  document.getElementById('inv-new-unit').value='';
  renderInventoryTable();
  showToast('✅ تم إضافة '+name,'success');
}

function normalizeSearchText(value){
  return String(value||'')
    .trim()
    .toLowerCase()
    .replace(/[أإآ]/g,'ا')
    .replace(/ى/g,'ي')
    .replace(/ة/g,'ه')
    .replace(/\s+/g,' ');
}
function normalizeHeader(value){
  return normalizeSearchText(value).replace(/[^\w\u0600-\u06FF]+/g,'');
}
function parseImportedNumber(value){
  if(typeof value === 'number') return Number.isFinite(value) ? value : 0;
  const normalized = String(value||'')
    .replace(/[٠-٩]/g, d=>'٠١٢٣٤٥٦٧٨٩'.indexOf(d))
    .replace(/[۰-۹]/g, d=>'۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
    .replace(/,/g,'')
    .replace(/٫/g,'.')
    .replace(/٬/g,'')
    .replace(/[^\d.-]/g,'')
    .trim();
  const number = parseFloat(normalized);
  return Number.isFinite(number) ? number : 0;
}
function headerMatches(header, names){
  const cleanHeader = normalizeHeader(header);
  return names.some(name=>cleanHeader.includes(normalizeHeader(name)));
}
function findImportHeaderRow(rows){
  let best = {index:-1, score:0};
  rows.forEach((row, index)=>{
    const cells = row.map(normalizeHeader);
    const score =
      (cells.some(cell=>cell.includes('اسمالصنف') || cell.includes('الصنف')) ? 3 : 0) +
      (cells.some(cell=>cell.includes('رصيداول') || cell.includes('رصيدأول') || cell.includes('رصيدالبداي') || cell.includes('الكميه') || cell.includes('كميه') || cell === 'الكميه') ? 2 : 0) +
      (cells.some(cell=>cell.includes('الوحده') || cell.includes('وحدهافتراضيه') || cell.includes('وحدهقياس')) ? 1 : 0) +
      (cells.some(cell=>cell.includes('سعر') || cell.includes('التكلفه')) ? 1 : 0);
    if(score > best.score) best = {index, score};
  });
  // fallback: if no row scored >=3, try any row with a صنف column
  if(best.score < 3){
    const fallback = rows.findIndex(row=>row.map(normalizeHeader).some(cell=>cell.includes('الصنف')));
    if(fallback >= 0) return fallback;
    return -1;
  }
  return best.index;
}
function columnIndexes(headers, names){
  return headers
    .map((header, index)=>headerMatches(header, names) ? index : -1)
    .filter(index=>index >= 0);
}
function firstValue(row, indexes){
  for(const index of indexes){
    const value = row[index];
    if(String(value||'').trim() !== '') return value;
  }
  return '';
}
function looksLikeUnit(value){
  const clean = normalizeHeader(value);
  return ['قطعه','قطعة','كيلو','كجم','جم','جرام','لتر','علبه','كرتونه','فرخه','رغيف','طبق'].some(unit=>clean.includes(normalizeHeader(unit)));
}
function findFallbackName(row, nameIndexes, unitIndexes){
  const blocked = new Set([...unitIndexes]);
  const preferred = [];
  nameIndexes.forEach(index=>{
    for(let offset=-3; offset<=3; offset++) preferred.push(index + offset);
  });
  const indexes = [...new Set([...preferred, ...row.map((_, index)=>index)])]
    .filter(index=>index >= 0 && index < row.length && !blocked.has(index));
  for(const index of indexes){
    const value = String(row[index]||'').trim();
    if(!value || looksLikeUnit(value) || parseImportedNumber(value)) continue;
    if(/[A-Za-z\u0600-\u06FF]/.test(value) && !headerMatches(value, ['رصيد','سعر','اجمالي','الاضافات','الصرف','كود','الوحدة','مخزن'])) return value;
  }
  return '';
}
function findFallbackUnit(row, unitIndexes){
  const direct = String(firstValue(row, unitIndexes)||'').trim();
  if(direct) return direct;
  const found = row.find(value=>looksLikeUnit(value));
  return String(found||'').trim();
}
function parseInventoryRowsFromSheet(ws){
  const rows = XLSX.utils.sheet_to_json(ws, {header:1, defval:'', blankrows:false});
  const headerIndex = findImportHeaderRow(rows);
  if(headerIndex < 0) return [];
  const headers = rows[headerIndex];
  const nameIndexes = columnIndexes(headers, ['اسم الصنف','الصنف','item name','item','name','item_name']);
  const unitIndexes = columnIndexes(headers, ['الوحدة الافتراضية','الوحدة','وحدة','unit']);
  const openingIndexes = columnIndexes(headers, ['رصيد اول المده','رصيد اول المدة','رصيد أول المدة','رصيد','الكمية','كمية','opening','opening_stock','qty','quantity']);
  const priceIndexes = columnIndexes(headers, ['سعر الوحدة','السعر','price','cost']);
  return rows.slice(headerIndex + 1).map(row=>{
    const name = String(firstValue(row, nameIndexes) || findFallbackName(row, nameIndexes, unitIndexes)).trim();
    const unit = findFallbackUnit(row, unitIndexes) || 'قطعة';
    const opening = parseImportedNumber(firstValue(row, openingIndexes));
    const price = parseImportedNumber(firstValue(row, priceIndexes));
    return {name, unit, opening, price};
  }).filter(row=>row.name);
}
async function importManagerSiteInventory(input){
  const site=document.getElementById('ga-inventory-upload-site')?.value||'';
  const date=document.getElementById('ga-inventory-upload-date')?.value||todayStr();
  const file=input.files&&input.files[0];
  if(!site){showToast('اختر الموقع الذي سيتم رفع المخزون إليه','error');input.value='';return;}
  if(!file)return;
  if(!ensureExcelReady()){input.value='';return;}
  const reader=new FileReader();
  reader.onload=async event=>{
    try{
      const wb=XLSX.read(new Uint8Array(event.target.result),{type:'array',codepage:65001,raw:false});
      const rows=parseInventoryRowsFromSheet(wb.Sheets[wb.SheetNames[0]]);
      if(!rows.length)throw new Error('لم يُعثر على أصناف. تأكد من وجود عمود «اسم الصنف»');
      const {data:existing,error:loadError}=await supabase_client.from('inventory').select('*').eq('site',site).eq('stock_date',date);
      if(loadError)throw new Error(loadError.message||'تعذر قراءة مخزن الموقع');
      const byName=new Map((existing||[]).map(row=>[row.item_name,row]));
      const payload=rows.map(row=>{
        const old=byName.get(row.name)||{};
        const item={
          ...old,site,item_name:row.name,unit:row.unit||old.unit||'قطعة',
          department:old.department||inventoryDepartment(row.name),price:Number(row.price)||0,
          opening_stock:Number(row.opening)||0,additions:Number(old.additions)||0,
          dispensed:Number(old.dispensed)||0,transfer_in:Number(old.transfer_in)||0,
          transfer_out:Number(old.transfer_out)||0,stock_date:date,last_updated:new Date().toISOString()
        };
        item.closing_stock=computeClosing(item);
        return item;
      });
      const {error}=await supabase_client.from('inventory').upsert(payload,{onConflict:'site,item_name,stock_date'});
      if(error)throw new Error(error.message||'تعذر حفظ ملف المخزن');
      showToast(`✅ تم رفع ${payload.length} صنف إلى مخزن ${site}`,'success');
      await renderGeneralAdmin();
    }catch(error){
      console.error(error);showToast('❌ '+(error.message||'تعذر رفع ملف Excel'),'error');
    }finally{input.value='';}
  };
  reader.readAsArrayBuffer(file);
}
function importInventoryFile(input){
  if(!requirePermission('can_edit_inventory','⚠️ المدير لم يفتح لك صلاحية تعديل المخزن')) { input.value=''; return; }
  const file = input.files && input.files[0];
  if(!file) return;
  if(!ensureExcelReady()) { input.value=''; return; }
  const reader = new FileReader();
  reader.onload = (event)=>{
    try{
      const data = new Uint8Array(event.target.result);
      // codepage 65001 = UTF-8. بدونها يقرأ SheetJS ملفات CSV العربية غير المبدوءة
      // بعلامة BOM على أنها Latin-1 فتتحول الترويسة إلى رموز مشوّهة ويظهر الملف فارغًا.
      const wb = XLSX.read(data, {type:'array', codepage:65001, raw:false});
      const ws = wb.Sheets[wb.SheetNames[0]];
      const rows = parseInventoryRowsFromSheet(ws);
      if(!rows.length){showToast('لم يُعثر على أصناف في الملف. تأكد من وجود عمود «اسم الصنف»','error'); return;}
      let imported = 0;
      rows.forEach(row=>{
        const name = row.name;
        const unit = row.unit || 'قطعة';
        const opening = row.opening;
        const price = row.price;
        if(!name) return;
        let item = invItems.find(x=>x.item_name===name);
        if(!item){
          item = {id:null, item_name:name, unit, price:0, opening_stock:0, additions:0, dispensed:0, closing_stock:0};
          invItems.push(item);
        }
        item.unit = unit || item.unit;
        item.opening_stock = opening;
        if(price) item.price = price;
        item.closing_stock = computeClosing(item);
        imported++;
      });
      renderInventoryTable();
      if(imported){
        showToast(`✅ تم تحميل ${imported} صنف من الملف`,'success');
      }else{
        showToast('⚠️ لم يتم العثور على أصناف. تأكد من وجود عمود باسم الصنف أو item','error');
      }
    }catch(err){
      console.error(err);
      showToast('❌ تعذر قراءة ملف Excel','error');
    }finally{
      input.value='';
    }
  };
  reader.readAsArrayBuffer(file);
}

async function saveInventory() {
  if(!requirePermission('can_edit_inventory','⚠️ المدير لم يفتح لك صلاحية تعديل المخزن')) return;
  if(!supabase_client||!currentUser||!currentUser.site){showToast('⚠️ سجل دخولك أولاً','error');return;}
  const date = document.getElementById('inv-date').value || todayStr();
  const rows = invItems.filter(item=>String(item.item_name||'').trim()).map(item => ({
    site: currentUser.site,
    item_name: String(item.item_name||'').trim(),
    unit: item.unit || '',
    department: item.department || inventoryDepartment(item.item_name),
    price: parseFloat(item.price)||0,
    opening_stock: parseFloat(item.opening_stock)||0,
    additions: parseFloat(item.additions)||0,
    dispensed: parseFloat(item.dispensed)||0,
    transfer_in: parseFloat(item.transfer_in)||0,
    transfer_out: parseFloat(item.transfer_out)||0,
    closing_stock: computeClosing(item),
    stock_date: date,
    last_updated: new Date().toISOString()
  }));

  // Upsert all rows
  const {error} = await supabase_client.from('inventory')
    .upsert(rows, {onConflict:'site,item_name,stock_date'});
  if(error){
    console.warn('Inventory upsert failed, saving row by row', error);
    const fallback = await saveInventoryRowsOneByOne(rows);
    if(!fallback.ok){showToast('❌ خطأ في حفظ المخزن: '+fallback.message,'error');return;}
  }
  showToast('✅ تم حفظ المخزن','success');
}

async function saveInventoryRowsOneByOne(rows){
  try{
    for(const row of rows){
      const {data:existing} = await supabase_client.from('inventory')
        .select('*')
        .eq('site', row.site)
        .eq('item_name', row.item_name)
        .eq('stock_date', row.stock_date)
        .single();
      if(existing && existing.id){
        const {error}=await supabase_client.from('inventory').update(row).eq('id', existing.id);
        if(error) return {ok:false, message:error.message};
      }else{
        const {error}=await supabase_client.from('inventory').insert(row);
        if(error) return {ok:false, message:error.message};
      }
    }
    return {ok:true};
  }catch(err){
    return {ok:false, message:err.message || 'تعذر الحفظ'};
  }
}

function exportInventoryExcel() {
  if(!ensureExcelReady()) return;
  if(!invItems.length){showToast('لا يوجد بيانات','error');return;}
  const date = document.getElementById('inv-date').value || todayStr();
  const departmentValue = document.getElementById('inv-department')?.value || '';
  const exportItems = invItems.filter(item=>!departmentValue || (item.department||inventoryDepartment(item.item_name))===departmentValue);
  if(!exportItems.length){showToast('لا توجد أصناف في هذا القسم','error');return;}
  const wb = XLSX.utils.book_new();
  const data = [
    ['I-BUILD - تقرير المخزن اليومي'],
    ['الموقع: '+(currentUser?.site||''), 'التاريخ: '+date, 'القسم: '+(departmentValue?departmentLabel(departmentValue):'كل الأقسام')],
    [],
    ['الصنف','القسم','الوحدة','السعر','رصيد أول المدة','الإضافات','المنصرف','رصيد آخر المدة','تكلفة المنصرف']
  ];
  let total = 0;
  exportItems.forEach(item => {
    const closing = computeClosing(item);
    const cost = (parseFloat(item.dispensed)||0)*(parseFloat(item.price)||0);
    total += cost;
    data.push([item.item_name, departmentLabel(item.department||inventoryDepartment(item.item_name)), item.unit, item.price, item.opening_stock, item.additions, item.dispensed||0, closing.toFixed(2), cost.toFixed(2)]);
  });
  data.push([]);
  data.push(['','','','','','','','إجمالي التكلفة', total.toFixed(2)+' جنيه']);
  const ws = XLSX.utils.aoa_to_sheet(data);
  ws['!cols']=[{wch:20},{wch:12},{wch:10},{wch:10},{wch:15},{wch:12},{wch:12},{wch:15},{wch:15}];
  XLSX.utils.book_append_sheet(wb, ws, 'المخزن');
  XLSX.writeFile(wb, `iBUILD_مخزن_${departmentValue?departmentLabel(departmentValue)+'_':''}${currentUser?.site||''}_${date}.xlsx`);
  showToast('✅ تم تحميل Excel','success');
}

async function renderManagerInventory() {
  if(!supabase_client) return;
  const date = document.getElementById('filter-date').value || todayStr();
  const site = document.getElementById('inv-site-filter').value;
  const container = document.getElementById('manager-inv-container');
  if(!site){
    container.innerHTML='<div class="empty"><div class="icon">📦</div><div>اختر موقعاً لعرض مخزنه</div></div>';
    return;
  }
  let q = supabase_client.from('inventory').select('*').eq('stock_date', date).eq('site', site);
  const {data, error} = await q;
  if(error){showToast('خطأ','error');return;}
  const dispensedTotalsBySite = {};
  let logsQ = supabase_client.from('meal_logs').select('*').eq('log_date', date);
  if(site) logsQ = logsQ.eq('site', site);
  const {data: mealLogs} = await logsQ;
  (mealLogs||[]).forEach(log=>{
    if(log.inventory_posted!==true) return;
    const logSite = log.site || '';
    if(!dispensedTotalsBySite[logSite]) dispensedTotalsBySite[logSite] = {};
    (log.items||[]).forEach(item=>{
      const bucket = dispensedTotalsBySite[logSite];
      if(!bucket[item.name]) bucket[item.name] = {qty:0, unit:item.unit||''};
      bucket[item.name].qty += parseFloat(item.qty)||0;
      if(item.unit) bucket[item.name].unit = item.unit;
    });
  });
  const mergedData = [...(data||[])];
  Object.entries(dispensedTotalsBySite).forEach(([s, totals])=>{
    Object.entries(totals).forEach(([name, info])=>{
      let row = mergedData.find(r=>r.site===s && r.item_name===name);
      if(!row){
        row = {site:s, item_name:name, unit:info.unit, price:0, opening_stock:0, additions:0, dispensed:0, closing_stock:0, stock_date:date};
        mergedData.push(row);
      }
      row.unit = row.unit || info.unit;
      row.dispensed = info.qty;
      row.closing_stock = computeClosing(row);
    });
  });
  if(!mergedData.length){container.innerHTML='<div class="empty"><div class="icon">📦</div><div>لا يوجد بيانات مخزن لهذا اليوم</div></div>';return;}

  // group by site
  const sites = {};
  mergedData.forEach(r=>{if(!sites[r.site])sites[r.site]=[];sites[r.site].push(r);});
  let html='';
  for(const [s, items] of Object.entries(sites)){
    const totalCost = items.reduce((a,r)=>a+((r.closing_stock||0)*(r.price||0)),0);
    html+=`<div style="margin-bottom:1.5rem">
      <div style="font-size:.95rem;font-weight:900;color:var(--blue);margin-bottom:.6rem">📍 ${s}</div>
      <div style="overflow-x:auto"><table class="inv-table">
        <thead><tr><th>الصنف</th><th>الوحدة</th><th>السعر</th><th>رصيد أول المدة</th><th>إضافات</th><th>المنصرف</th><th>رصيد آخر المدة</th><th>تكلفة الرصيد</th></tr></thead>
        <tbody>`;
    items.forEach(r=>{
      const cost=(r.closing_stock||0)*(r.price||0);
      html+=`<tr>
        <td>${r.item_name}</td><td>${r.unit||''}</td>
        <td>${r.price||0} ج</td>
        <td>${r.opening_stock||0}</td><td style="color:var(--blue)">${r.additions||0}</td>
        <td style="color:var(--red)">${r.dispensed||0}</td>
        <td class="inv-closing ${(r.closing_stock||0)<0?'negative':''}">${r.closing_stock||0}</td>
        <td class="inv-cost">${cost.toFixed(2)} ج</td>
      </tr>`;
    });
    html+=`</tbody><tfoot><tr class="inv-total-row"><td colspan="7" style="text-align:left">💰 إجمالي تكلفة الرصيد</td><td class="inv-cost">${totalCost.toFixed(2)} جنيه</td></tr></tfoot>
      </table></div></div>`;
  }
  container.innerHTML=html;
}

async function renderSupplierAccounts(){
  const container=document.getElementById('supplier-accounts-container');
  if(!container || !supabase_client) return;
  renderCashboxSupplierOptions();
  const site=document.getElementById('filter-site')?.value||'',month=document.getElementById('supplier-month-filter')?.value||'',search=normalizeSearchText(document.getElementById('manager-supplier-search')?.value||'');
  let invoiceQuery=supabase_client.from('invoices').select('*'),paymentQuery=supabase_client.from('cashbox_transactions').select('*');
  if(site){invoiceQuery=invoiceQuery.eq('site',site);paymentQuery=paymentQuery.eq('site',site);}
  const [invoiceResult,paymentResult]=await Promise.all([invoiceQuery,paymentQuery]);
  if(invoiceResult.error||paymentResult.error){container.innerHTML='<div class="empty"><div class="icon">⚠️</div><div>تعذر تحميل حسابات الموردين</div></div>';return;}
  const invoices=(invoiceResult.data||[]).filter(inv=>!month||String(inv.invoice_date||'').startsWith(month));
  const payments=(paymentResult.data||[]).filter(row=>row.type==='supplier_payment'&&cashboxApprovalStatus(row)==='approved'&&(!month||String(row.transaction_date||'').startsWith(month)));
  const supplierMaster=(loadLocalDb().suppliers||[]).filter(item=>item.active!==false);
  const groups={};
  const ensureSupplier=(name,meta={})=>{
    const display=String(name||'مورد غير محدد').trim()||'مورد غير محدد',key=normalizeSearchText(display);
    if(!groups[key])groups[key]={name:display,phone:meta.phone||'',account:meta.account||'',paymentType:meta.paymentType||'',total:0,invoicePaid:0,cashboxPaid:0,cash:0,credit:0,count:0,sites:new Set(),invoices:[],payments:[]};
    groups[key].phone=groups[key].phone||meta.phone||'';groups[key].account=groups[key].account||meta.account||'';groups[key].paymentType=groups[key].paymentType||meta.paymentType||'';
    return groups[key];
  };
  supplierMaster.forEach(item=>ensureSupplier(item.name,{phone:item.phone,account:item.account,paymentType:item.paymentType}));
  invoices.forEach(inv=>{
    const row=ensureSupplier(inv.supplier_name,{phone:inv.supplier_phone,account:inv.supplier_account,paymentType:inv.payment_type});
    const total=Number(inv.total_amount)||0,legacyPaid=Number(inv.paid_amount)||0;
    row.total+=total;row.invoicePaid+=legacyPaid;row.count+=1;row.sites.add(inv.site||'');
    if(inv.payment_type==='credit')row.credit+=total;else row.cash+=total;
    row.invoices.push({number:inv.invoice_number||'',date:inv.invoice_date||'',site:inv.site||'',type:inv.payment_type==='credit'?'آجل':'كاش',total,legacyPaid,linkedPaid:0});
  });
  payments.forEach(payment=>{
    const row=ensureSupplier(payment.supplier);
    const amount=Number(payment.amount)||0;
    row.cashboxPaid+=amount;row.sites.add(payment.site||'');row.payments.push({date:payment.transaction_date||'',site:payment.site||'',reference:payment.reference||'',source:payment.source_reference||'',amount,approvedBy:payment.approved_by||''});
    if(payment.source_reference){
      const invoice=row.invoices.find(item=>String(item.number)===String(payment.source_reference)&&(!payment.site||item.site===payment.site));
      if(invoice)invoice.linkedPaid+=amount;
    }
  });
  const entries=Object.values(groups).filter(row=>(row.count||row.payments.length)&&(!search||normalizeSearchText(row.name).includes(search))).sort((a,b)=>a.name.localeCompare(b.name,'ar'));
  if(!entries.length){container.innerHTML='<div class="empty"><div class="icon">📒</div><div>لا توجد حسابات موردين مطابقة للفترة والبحث</div></div>';return;}
  entries.forEach(row=>{row.paid=row.invoicePaid+row.cashboxPaid;row.remaining=Math.max(row.total-row.paid,0);});
  container.innerHTML=`<div class="data-integrity-note">دفعات الخزنة لا تظهر في حساب المورد ولا تخصم من رصيد الخزنة إلا بعد اعتماد الإدارة.</div>
  <div style="overflow-x:auto"><table class="inv-table">
    <thead><tr><th>المورد</th><th>التليفون</th><th>حساب المورد</th><th>التعامل</th><th>المواقع</th><th>الفواتير</th><th>إجمالي الفواتير</th><th>دفعات قديمة</th><th>دفعات الخزنة المعتمدة</th><th>إجمالي المدفوع</th><th>المتبقي</th></tr></thead>
    <tbody>${entries.map(row=>`<tr>
      <td><strong>${safeText(row.name)}</strong></td><td>${safeText(row.phone||'-')}</td><td>${safeText(row.account||'-')}</td>
      <td>${row.cash>0&&row.credit>0?'كاش / آجل':row.credit>0?'آجل':row.cash>0?'كاش':safeText(row.paymentType==='credit'?'آجل':'كاش')}</td>
      <td>${safeText(Array.from(row.sites).filter(Boolean).join('، ')||'-')}</td><td>${row.count}</td>
      <td class="inv-cost">${row.total.toFixed(2)} ج</td><td>${row.invoicePaid.toFixed(2)} ج</td>
      <td style="color:var(--green);font-weight:900">${row.cashboxPaid.toFixed(2)} ج</td><td style="color:var(--green);font-weight:900">${row.paid.toFixed(2)} ج</td>
      <td class="${row.remaining>0?'inv-closing negative':'inv-closing'}">${row.remaining.toFixed(2)} ج</td>
    </tr>`).join('')}</tbody>
  </table></div>
  <div style="margin-top:1.2rem;font-size:.95rem;font-weight:900;color:var(--blue)">كشف تفصيلي لكل مورد</div>
  ${entries.map(row=>`<div style="margin-top:1rem;border:1.5px solid var(--border);border-radius:10px;padding:1rem;background:#fff;">
    <div style="display:flex;justify-content:space-between;gap:.8rem;flex-wrap:wrap;margin-bottom:.7rem;">
      <div><div style="font-weight:900;color:var(--green);font-size:1rem">${safeText(row.name)}</div><div style="font-size:.8rem;color:var(--muted)">تليفون: ${safeText(row.phone||'-')} | حساب: ${safeText(row.account||'-')}</div></div>
      <div style="display:flex;gap:.4rem;flex-wrap:wrap"><span class="tag">فواتير <strong>${row.total.toFixed(2)} ج</strong></span><span class="tag">مدفوع معتمد <strong>${row.paid.toFixed(2)} ج</strong></span><span class="tag">متبقي <strong>${row.remaining.toFixed(2)} ج</strong></span></div>
    </div>
    ${row.invoices.length?`<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>رقم الفاتورة</th><th>التاريخ</th><th>الموقع</th><th>التعامل</th><th>الإجمالي</th><th>المدفوع المعتمد</th><th>المتبقي</th></tr></thead><tbody>${row.invoices.map(inv=>{const paid=inv.legacyPaid+inv.linkedPaid,remaining=Math.max(inv.total-paid,0);return `<tr><td>${safeText(inv.number||'-')}</td><td>${safeText(inv.date)}</td><td>${safeText(inv.site||'-')}</td><td>${inv.type}</td><td class="inv-cost">${inv.total.toFixed(2)} ج</td><td style="color:var(--green);font-weight:900">${paid.toFixed(2)} ج</td><td class="${remaining>0?'inv-closing negative':'inv-closing'}">${remaining.toFixed(2)} ج</td></tr>`}).join('')}</tbody></table></div>`:'<div class="data-integrity-note">لا توجد فواتير لهذا المورد في الفترة.</div>'}
    <div style="margin-top:.8rem;font-weight:900;color:var(--green)">دفعات الخزنة المعتمدة</div>
    ${row.payments.length?`<div style="overflow-x:auto"><table class="inv-table"><thead><tr><th>التاريخ</th><th>الموقع</th><th>رقم مستند الخزنة</th><th>الفاتورة المرتبطة</th><th>المبلغ</th><th>اعتمدها</th></tr></thead><tbody>${row.payments.map(payment=>`<tr><td>${safeText(payment.date)}</td><td>${safeText(payment.site||'-')}</td><td><strong>${safeText(payment.reference)}</strong></td><td>${safeText(payment.source||'-')}</td><td style="color:var(--green);font-weight:900">${payment.amount.toFixed(2)} ج</td><td>${safeText(payment.approvedBy||'-')}</td></tr>`).join('')}</tbody></table></div>`:'<div class="data-integrity-note">لا توجد دفعات خزنة معتمدة في الفترة.</div>'}
  </div>`).join('')}`;
}

// ===== BACKUP & RESTORE =====
async function exportBackup(){
  try{
    const payload=IBF_BOOT.restUrl?await apiFetch('export',{method:'GET'}):{app:'I-BUILD Food Operations',version:2,exportedAt:new Date().toISOString(),data:loadLocalDb()};
    const blob=new Blob([JSON.stringify(payload,null,2)],{type:'application/json;charset=utf-8'});
    const url=URL.createObjectURL(blob),link=document.createElement('a');
    link.href=url;link.download=`I-BUILD_Server_Backup_${todayStr()}.json`;link.click();URL.revokeObjectURL(url);
    showToast('✅ تم تنزيل نسخة بيانات الخادم','success');
  }catch(error){showToast('تعذر تنزيل النسخة الاحتياطية','error');}
}
function importBackup(input){
  if(IBF_BOOT.restUrl){showToast('استعادة نسخة الخادم تتم بواسطة مدير النظام بعد المراجعة','error');input.value='';return;}
  const file=input.files&&input.files[0];if(!file)return;
  const reader=new FileReader();
  reader.onload=()=>{
    try{
      const payload=JSON.parse(reader.result);
      if(payload.app!=='I-BUILD Food Operations'||!payload.data||typeof payload.data!=='object') throw new Error('invalid');
      if(!confirm('سيتم استبدال البيانات المحلية الحالية بالنسخة المختارة. هل تريد المتابعة؟')){input.value='';return;}
      saveLocalDb(payload.data);showToast('✅ تمت استعادة النسخة الاحتياطية','success');setTimeout(()=>location.reload(),800);
    }catch(e){showToast('ملف النسخة الاحتياطية غير صالح','error');input.value='';}
  };
  reader.readAsText(file);
}

// ===== EXPORT EXCEL =====
function exportExcel(){
  if(!ensureExcelReady()) return;
  if(!lastLogs.length){showToast('لا يوجد بيانات','error');return;}
  const date=document.getElementById('filter-date').value||todayStr();
  const wb=XLSX.utils.book_new();
  // Summary sheet
  const sumData=[
    ['I-BUILD قطاع التغذية - تقرير صرف الوجبات'],
    ['التاريخ: '+date],
    [],
    ['الموقع','المشرف','الوجبة','عدد الطلاب','عدد الإداريين','عدد الوجبات','الأصناف الموجودة','الوقت']
  ];
  lastLogs.forEach(l=>{sumData.push([l.site,l.supervisor_name,l.meal,l.students_count||0,l.admins_count||0,l.meals_total||0,l.staff_present||0,l.log_time]);});
  const ws1=XLSX.utils.aoa_to_sheet(sumData);
  ws1['!cols']=[{wch:12},{wch:15},{wch:10},{wch:12},{wch:12},{wch:12},{wch:14},{wch:10}];
  XLSX.utils.book_append_sheet(wb,ws1,'ملخص');
  // Detail sheet
  const detData=[['الموقع','المشرف','الوجبة','الصنف','الكمية المصروفة','الوحدة','الوقت']];
  lastLogs.forEach(l=>{(l.items||[]).forEach(it=>{detData.push([l.site,l.supervisor_name,l.meal,it.name,it.qty,it.unit,l.log_time]);});});
  const ws2=XLSX.utils.aoa_to_sheet(detData);
  ws2['!cols']=[{wch:12},{wch:15},{wch:10},{wch:25},{wch:15},{wch:10},{wch:10}];
  XLSX.utils.book_append_sheet(wb,ws2,'تفاصيل الصرف');
  XLSX.writeFile(wb,`iBUILD_وجبات_${date}.xlsx`);
  showToast('✅ تم تحميل Excel','success');
}
</script>
</body>
</html>
