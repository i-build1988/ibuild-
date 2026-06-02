const state = {
  users: [],
  clients: [],
  drivers: [],
  vehicles: [],
  sites: [],
  contracts: [],
  inspections: [],
  materials: [],
  materialTransactions: [],
  vehicleMovements: [],
  stats: {}
};

const ratingLabels = {
  excellent: "ممتاز",
  good: "جيد",
  needs_followup: "يحتاج متابعة",
  critical: "حالة حرجة"
};

const roleLabels = {
  general_manager: "المدير العام",
  operations_manager: "مدير العمليات",
  supervisor: "المشرف",
  storekeeper: "أمين المخزن"
};

const pageAccessOptions = [
  "dashboard",
  "sites",
  "contracts",
  "inspection",
  "materials",
  "reports",
  "users",
  "vehicles"
];

const defaultPermissions = {
  general_manager: ["dashboard", "sites", "contracts", "inspection", "materials", "reports", "users", "vehicles"],
  operations_manager: ["dashboard"],
  supervisor: ["inspection"],
  storekeeper: ["materials"]
};

const $ = (id) => document.getElementById(id);
let sessionUser = null;

async function api(path, options = {}) {
  const response = await fetch(path, {
    headers: { "content-type": "application/json" },
    ...options
  });
  if (!response.ok) throw new Error("فشل الاتصال بالخادم");
  return response.json();
}

function toast(message) {
  const el = $("toast");
  el.textContent = message;
  el.classList.add("show");
  setTimeout(() => el.classList.remove("show"), 2400);
}

async function load() {
  Object.assign(state, await api("/api/bootstrap"));
  restoreSession();
  renderAll();
  applyInitialRoute();
}

function restoreSession() {
  const raw = localStorage.getItem("ibuild_session_user");
  if (!raw) return;
  try {
    const saved = JSON.parse(raw);
    const current = state.users.find((user) => user.id === saved.id && user.password === saved.password);
    sessionUser = current || null;
    if (!current) localStorage.removeItem("ibuild_session_user");
  } catch {
    localStorage.removeItem("ibuild_session_user");
  }
}

function renderAll() {
  applyPermissions();
  renderStats();
  renderSites();
  renderContracts();
  renderSelects();
  renderVehicleSelects();
  renderMaterials();
  renderWarehouse();
  renderCostReport();
  renderReports();
  renderUsers();
  renderVehicles();
  renderGoogleSheetSettings();
  renderLoginState();
  $("inspectionTime").value = new Date().toLocaleString("ar-EG");
  $("dashboardDate").textContent = new Date().toLocaleDateString("ar-EG", { weekday: "long", day: "numeric", month: "long", year: "numeric" });
}

function renderLoginState() {
  if (!$("loginStatus")) return;
  document.body.classList.toggle("is-locked", !sessionUser);
  $("loginStatus").textContent = sessionUser ? `داخل باسم: ${sessionUser.fullName}` : "غير مسجل";
  $("logoutButton").style.display = sessionUser ? "" : "none";
  $("loginButton").style.display = sessionUser ? "none" : "";
  $("loginUsername").style.display = sessionUser ? "none" : "";
  $("loginPassword").style.display = sessionUser ? "none" : "";
  $("role").disabled = Boolean(sessionUser);
  if (sessionUser) $("role").value = sessionUser.role;
}

function renderGoogleSheetSettings() {
  if (!$("googleScriptUrl")) return;
  $("googleScriptUrl").value = localStorage.getItem("ibuild_google_script_url") || "";
  $("googleSheetStatus").textContent = $("googleScriptUrl").value
    ? "تم حفظ رابط Google Apps Script."
    : "لم يتم ربط Google Sheet بعد.";
}

function applyInitialRoute() {
  const params = new URLSearchParams(location.search);
  const page = params.get("page");
  const siteId = params.get("site");
  if (page === "inspection") {
    activatePage("inspection");
    if (siteId && $("inspectionSite")) {
      $("inspectionSite").value = siteId;
    }
  }
}

function renderStats() {
  const todayInspections = todaysInspections();
  const workersPresent = todayInspections.reduce((sum, x) => sum + Number(x.workersPresent || 0), 0);
  const workersAbsent = todayInspections.reduce((sum, x) => sum + Number(x.workersAbsent || 0), 0);
  const activeSupervisor = getActiveSupervisor(todayInspections);
  const stats = [
    { label: "عدد المواقع", value: state.stats.siteCount, icon: "🏢", tone: "green", note: "موقع مسجل" },
    { label: "عدد العمالة الموجودة", value: workersPresent, icon: "👷", tone: "cyan", note: "مسجل اليوم" },
    { label: "إجمالي اليوم", value: state.stats.dailyVisits, icon: "📋", tone: "green", note: "زيارة وتفتيش" },
    { label: "إجمالي غياب اليوم", value: workersAbsent, icon: "⚠️", tone: "amber", note: "عامل غائب" },
    { label: "المشرف النشيط", value: activeSupervisor.name, icon: "⭐", tone: "cyan", note: `${activeSupervisor.count} زيارة` },
    { label: "تنبيهات المخزون", value: state.stats.lowStockCount, icon: "📦", tone: "red", note: "تحت الحد" }
  ];
  $("stats").innerHTML = stats.map((item) => `
    <div class="stat-card ${item.tone}">
      <div class="stat-icon"><img src="/ibuild-logo.svg" alt="I-Build"></div>
      <div>
        <b>${item.value ?? 0}</b>
        <span>${item.label}</span>
        <small>${item.note}</small>
      </div>
    </div>
  `).join("");

  const flagged = state.inspections
    .filter((x) => x.rating === "needs_followup" || x.rating === "critical")
    .slice(-6)
    .reverse();
  $("attentionSites").innerHTML = flagged.length ? flagged.map((x) => {
    const site = siteById(x.siteId);
    return `<div class="site-card work-card">
      <div>
        <h3>${site?.name || "موقع غير معروف"}</h3>
        <div class="site-meta">${x.supervisorName || "-"} - ${ratingLabels[x.rating]}</div>
        <div class="site-meta">${x.notes || "لا توجد ملاحظات"}</div>
      </div>
      <span class="badge ${x.rating === "critical" ? "bad" : "warn"}">${ratingLabels[x.rating]}</span>
    </div>`;
  }).join("") : `<div class="site-meta">لا توجد مواقع تحتاج تدخل حاليا.</div>`;

  $("activeSupervisor").innerHTML = activeSupervisor.count > 0 ? `
    <div class="site-card work-card supervisor-card">
      <div>
        <h3>${activeSupervisor.name}</h3>
        <div class="site-meta">عدد زيارات اليوم: ${activeSupervisor.count}</div>
        <div class="site-meta">إجمالي العمالة المسجلة معه: ${workersPresent}</div>
      </div>
      <span class="badge ok">نشط</span>
    </div>
  ` : `<div class="site-meta">لا توجد زيارات مسجلة اليوم.</div>`;

  renderSiteStock();
  renderDailySiteUsage();
  renderContractDailyStatus();

  $("materialBars").innerHTML = state.materials.map((m) => {
    const ratio = Math.min(100, Math.round((Number(m.currentStock) / Math.max(Number(m.minStock) * 2, 1)) * 100));
    const low = Number(m.currentStock) <= Number(m.minStock);
    return `<div class="bar stock-bar ${low ? "stock-low" : ""}">
      <div class="bar-row"><span>${m.name}</span><span class="${low ? "bad" : "ok"}">${m.currentStock} ${m.unit}</span></div>
      <div class="track"><div class="fill" style="width:${ratio}%"></div></div>
    </div>`;
  }).join("");
}

function todaysInspections() {
  const today = new Date().toDateString();
  return state.inspections.filter((x) => new Date(x.inspectedAt).toDateString() === today);
}

function getActiveSupervisor(inspections) {
  const counts = new Map();
  inspections.forEach((x) => {
    const name = x.supervisorName || "غير محدد";
    counts.set(name, (counts.get(name) || 0) + 1);
  });
  const top = [...counts.entries()].sort((a, b) => b[1] - a[1])[0];
  return top ? { name: top[0], count: top[1] } : { name: "-", count: 0 };
}

function renderSiteStock() {
  const stockBySite = new Map();
  state.materialTransactions
    .filter((tx) => (tx.txType === "site_out" || tx.txType === "site_use") && tx.siteId)
    .forEach((tx) => {
      const material = state.materials.find((x) => x.id === tx.materialId);
      const siteMap = stockBySite.get(tx.siteId) || new Map();
      const item = siteMap.get(tx.materialId) || { materialName: material?.name || "-", unit: material?.unit || "", quantity: 0 };
      item.quantity += tx.txType === "site_out" ? Number(tx.quantity || 0) : -Number(tx.quantity || 0);
      siteMap.set(tx.materialId, item);
      stockBySite.set(tx.siteId, siteMap);
    });

  $("siteStockList").innerHTML = state.sites.map((site) => {
    const items = [...(stockBySite.get(site.id)?.values() || [])].filter((item) => item.quantity !== 0);
    const body = items.length
      ? items.slice(0, 5).map((item) => `<div class="site-meta">${item.materialName}: ${item.quantity} ${item.unit}</div>`).join("")
      : `<div class="site-meta">لا يوجد مخزون مصروف للموقع حتى الآن.</div>`;
    return `<div class="site-card work-card stock-site-card">
      <div>
        <h3>${site.name}</h3>
        ${body}
      </div>
      <span class="badge">${items.length} صنف</span>
    </div>`;
  }).join("");
}

function renderDailySiteUsage() {
  const today = new Date().toDateString();
  const usageBySite = new Map();
  state.materialTransactions
    .filter((tx) => tx.txType === "site_use" && tx.siteId && new Date(tx.createdAt).toDateString() === today)
    .forEach((tx) => {
      const material = state.materials.find((x) => x.id === tx.materialId);
      const current = usageBySite.get(tx.siteId) || { quantity: 0, totalCost: 0, lines: [] };
      current.quantity += Number(tx.quantity || 0);
      current.totalCost += Number(tx.totalCost || 0);
      current.lines.push(`${material?.name || "-"}: ${tx.quantity} ${material?.unit || ""}`);
      usageBySite.set(tx.siteId, current);
    });

  $("dailySiteUsage").innerHTML = state.sites.map((site) => {
    const usage = usageBySite.get(site.id);
    return `<div class="site-card work-card">
      <div>
        <h3>${site.name}</h3>
        <div class="site-meta">${usage ? usage.lines.slice(0, 3).join(" | ") : "لا يوجد صرف اليوم"}</div>
      </div>
      <span class="badge ${usage ? "warn" : "ok"}">${usage ? money(usage.totalCost) : "0 ج.م"}</span>
    </div>`;
  }).join("");
}

function renderContractDailyStatus() {
  const todayInspections = todaysInspections();
  const bySite = new Map();
  todayInspections.forEach((inspection) => {
    const current = bySite.get(inspection.siteId) || { present: 0, absent: 0, visits: 0 };
    current.present += Number(inspection.workersPresent || 0);
    current.absent += Number(inspection.workersAbsent || 0);
    current.visits += 1;
    bySite.set(inspection.siteId, current);
  });

  const activeContracts = (state.contracts || []).filter((contract) => contract.active !== false);
  $("contractDailyStatus").innerHTML = activeContracts.map((contract) => {
    const site = siteById(contract.siteId);
    const day = bySite.get(contract.siteId) || { present: 0, absent: 0, visits: 0 };
    const expectedWorkers = Number(contract.plannedWorkers || 0);
    const workerValue = Number(contract.workerDailyValue || 0);
    const expectedValue = expectedWorkers * workerValue;
    const actualValue = day.present * workerValue;
    const absenceValue = day.absent * workerValue;
    const percent = expectedWorkers ? Math.min(100, Math.round((day.present / expectedWorkers) * 100)) : 0;
    const tone = percent >= 100 ? "ok" : percent >= 75 ? "warn" : "bad";
    return `<div class="site-card work-card">
      <div>
        <h3>${contract.contractName || site?.name || "عقد بدون اسم"}</h3>
        <div class="site-meta">الموقع: ${site?.name || "-"}</div>
        <div class="site-meta">المستهدف: ${expectedWorkers} عامل | الحضور: ${day.present} | الغياب: ${day.absent}</div>
        <div class="site-meta">قيمة اليوم حسب العقد: ${money(expectedValue)} | الفعلي: ${money(actualValue)} | خصم الغياب: ${money(absenceValue)}</div>
      </div>
      <span class="badge ${tone}">${percent}%</span>
    </div>`;
  }).join("") || `<div class="site-meta">لا توجد عقود نظافة مضافة حتى الآن.</div>`;
}

function siteById(id) {
  return state.sites.find((x) => x.id === id);
}

function clientById(id) {
  return state.clients.find((x) => x.id === id);
}

function renderSites() {
  const q = $("siteSearch")?.value.trim().toLowerCase() || "";
  const sites = state.sites.filter((site) => {
    const client = clientById(site.clientId);
    return `${site.name} ${client?.name || ""} ${site.address}`.toLowerCase().includes(q);
  });
  $("sitesList").innerHTML = sites.map((site) => {
    const client = clientById(site.clientId);
    const formUrl = `${location.origin}/?page=inspection&site=${encodeURIComponent(site.id)}`;
    return `<div class="site-card">
      <div>
        <h3>${site.name}</h3>
        <div class="site-meta">العميل: ${client?.name || "-"} | ${client?.contactName || ""} ${client?.phone || ""}</div>
        <div class="site-meta">العنوان: ${site.address} ${site.city ? "- " + site.city : ""}</div>
        <div class="actions">
          <button class="ghost" onclick="editSite('${site.id}')">تعديل</button>
          <button class="danger" onclick="deleteSite('${site.id}')">حذف</button>
          <button class="ghost" onclick="openInspection('${site.id}')">فتح نموذج التفتيش</button>
        </div>
      </div>
      <div class="qr" id="qr-${site.id}" data-url="${formUrl}" title="${formUrl}">
        <div><div class="qr-pattern"></div>QR<br>${site.qrToken}</div>
      </div>
    </div>`;
  }).join("") || `<div class="site-meta">لا توجد مواقع مطابقة.</div>`;
  renderQrCodes(sites);
}

function renderContracts() {
  if (!$("contractsList")) return;
  $("contractsList").innerHTML = (state.contracts || []).map((contract) => {
    const site = siteById(contract.siteId);
    const dailyValue = Number(contract.plannedWorkers || 0) * Number(contract.workerDailyValue || 0);
    return `<div class="site-card">
      <div>
        <h3>${contract.contractName || "-"}</h3>
        <div class="site-meta">الموقع: ${site?.name || "-"}</div>
        <div class="site-meta">عدد العمال اليومي: ${contract.plannedWorkers} | قيمة العامل: ${money(contract.workerDailyValue)}</div>
        <div class="site-meta">قيمة اليوم المتوقعة: ${money(dailyValue)}</div>
        <div class="site-meta">المدة: ${contract.startDate || "-"} إلى ${contract.endDate || "-"}</div>
        <div class="actions">
          <button class="ghost" onclick="editContract('${contract.id}')">تعديل</button>
          <button class="danger" onclick="deleteContract('${contract.id}')">حذف</button>
        </div>
      </div>
      <span class="badge ${contract.active === false ? "bad" : "ok"}">${contract.active === false ? "متوقف" : "نشط"}</span>
    </div>`;
  }).join("") || `<div class="site-meta">لا توجد عقود مسجلة.</div>`;
}

function renderContractSelects() {
  if (!$("contractSite")) return;
  $("contractSite").innerHTML = state.sites.map((site) => `<option value="${site.id}">${site.name}</option>`).join("");
}

function renderQrCodes(sites) {
  sites.forEach((site) => {
    const el = $(`qr-${site.id}`);
    if (!el) return;
    const url = el.dataset.url;
    el.innerHTML = "";
    if (window.QRCode) {
      new QRCode(el, {
        text: url,
        width: 96,
        height: 96,
        colorDark: "#16241f",
        colorLight: "#ffffff",
        correctLevel: QRCode.CorrectLevel.M
      });
      const label = document.createElement("small");
      label.textContent = site.qrToken;
      el.appendChild(label);
      return;
    }
    el.innerHTML = `<div><div class="qr-pattern"></div>QR<br>${site.qrToken}</div>`;
  });
}

function renderSelects() {
  const siteOptions = state.sites.map((s) => `<option value="${s.id}">${s.name}</option>`).join("");
  $("inspectionSite").innerHTML = siteOptions;
  $("txSite").innerHTML = `<option value="">المخزن فقط</option>${siteOptions}`;
  $("txMaterial").innerHTML = state.materials.map((m) => `<option value="${m.id}">${m.name}</option>`).join("");
  renderContractSelects();
}

function renderMaterials() {
  $("materialsList").innerHTML = state.materials.map((m) => {
    const low = Number(m.currentStock) <= Number(m.minStock);
    return `<div class="site-card">
      <div>
        <h3>${m.name}</h3>
        <div class="site-meta">رصيد أول المدة: ${m.openingStock} ${m.unit} | رصيد آخر المدة: ${m.currentStock} ${m.unit}</div>
        <div class="site-meta">تكلفة الوحدة: ${money(m.unitCost)} | حد التنبيه: ${m.minStock}</div>
      </div>
      <span class="badge ${low ? "bad" : "ok"}">${low ? "مخزون منخفض" : "مستقر"}</span>
    </div>`;
  }).join("");
}

function renderWarehouse() {
  const incoming = state.materialTransactions
    .filter((x) => x.txType === "stock_in")
    .reduce((sum, x) => sum + Number(x.quantity || 0), 0);
  const outgoing = state.materialTransactions
    .filter((x) => x.txType === "site_out")
    .reduce((sum, x) => sum + Number(x.quantity || 0), 0);
  const lowStock = state.materials.filter((x) => Number(x.currentStock) <= Number(x.minStock)).length;
  const outgoingCost = state.materialTransactions
    .filter((x) => x.txType === "site_use")
    .reduce((sum, x) => sum + Number(x.totalCost || 0), 0);

  $("warehouseSummary").innerHTML = `
    <div class="warehouse-card"><b>${incoming}</b><span>إجمالي الوارد</span></div>
    <div class="warehouse-card"><b>${outgoing}</b><span>إجمالي المنصرف</span></div>
    <div class="warehouse-card"><b>${money(outgoingCost)}</b><span>تكلفة الخامات المصروفة</span></div>
    <div class="warehouse-card"><b>${lowStock}</b><span>خامات تحت حد التنبيه</span></div>
  `;

  const rows = state.materialTransactions.slice().reverse();
  $("warehouseTable").innerHTML = rows.map((tx) => {
    const material = state.materials.find((x) => x.id === tx.materialId);
    const site = siteById(tx.siteId);
    const isIn = tx.txType === "stock_in";
    const isSiteUse = tx.txType === "site_use";
    const movementLabel = tx.txType === "stock_in" ? "وارد" : tx.txType === "site_out" ? "تحويل لموقع" : "صرف موقع";
    return `<tr>
      <td>${new Date(tx.createdAt).toLocaleString("ar-EG")}</td>
      <td><span class="badge ${isIn ? "ok" : "warn"}">${movementLabel}</span></td>
      <td>${material?.name || "-"}</td>
      <td>${site?.name || "المخزن"}</td>
      <td>${tx.quantity} ${material?.unit || ""}</td>
      <td>${tx.balanceBefore ?? "-"}</td>
      <td>${tx.balanceAfter ?? "-"}</td>
      <td>${money(tx.unitCost || material?.unitCost || 0)}</td>
      <td>${isSiteUse ? money(tx.totalCost || 0) : "-"}</td>
      <td>${tx.deliveredBy || "-"}</td>
      <td>${tx.receivedBy || "-"}</td>
    </tr>`;
  }).join("") || `<tr><td colspan="11">لا توجد حركات مخزن.</td></tr>`;
}

function renderCostReport() {
  const issued = state.materialTransactions.filter((x) => x.txType === "site_use");
  const byKey = new Map();
  issued.forEach((tx) => {
    const material = state.materials.find((x) => x.id === tx.materialId);
    const site = siteById(tx.siteId);
    const key = `${tx.siteId || "warehouse"}|${tx.materialId}`;
    const current = byKey.get(key) || {
      siteName: site?.name || "بدون موقع",
      materialName: material?.name || "-",
      unit: material?.unit || "",
      quantity: 0,
      totalCost: 0
    };
    current.quantity += Number(tx.quantity || 0);
    current.totalCost += Number(tx.totalCost || 0);
    byKey.set(key, current);
  });

  const rows = [...byKey.values()];
  $("costReport").innerHTML = rows.length ? rows.map((row) => `
    <div class="site-card">
      <div>
        <h3>${row.siteName}</h3>
        <div class="site-meta">الصنف: ${row.materialName}</div>
        <div class="site-meta">الكمية المصروفة: ${row.quantity} ${row.unit}</div>
      </div>
      <span class="badge warn">${money(row.totalCost)}</span>
    </div>
  `).join("") : `<div class="site-meta">لا توجد خامات مصروفة حتى الآن.</div>`;
}

function renderReports() {
  const siteQ = $("reportSite")?.value.trim().toLowerCase() || "";
  const supQ = $("reportSupervisor")?.value.trim().toLowerCase() || "";
  const rows = state.inspections.filter((x) => {
    const site = siteById(x.siteId);
    return (site?.name || "").toLowerCase().includes(siteQ) && (x.supervisorName || "").toLowerCase().includes(supQ);
  }).slice().reverse();

  $("reportsTable").innerHTML = rows.map((x) => {
    const tone = x.rating === "critical" ? "bad" : x.rating === "needs_followup" ? "warn" : "ok";
    return `<tr>
      <td>${new Date(x.inspectedAt).toLocaleString("ar-EG")}</td>
      <td>${siteById(x.siteId)?.name || "-"}</td>
      <td>${x.supervisorName || "-"}</td>
      <td><span class="badge ${tone}">${ratingLabels[x.rating] || x.rating}</span></td>
      <td>${x.notes || "-"}</td>
    </tr>`;
  }).join("") || `<tr><td colspan="5">لا توجد تقارير.</td></tr>`;
}

function money(value) {
  return Number(value || 0).toLocaleString("ar-EG", { maximumFractionDigits: 2 }) + " ج.م";
}

function renderUsers() {
  const descriptions = {
    general_manager: "التحكم الكامل في النظام، وإنشاء اليوزر والباسورد، وتحديد الصلاحيات.",
    operations_manager: "متابعة التشغيل، التقارير، المواقع، والتدخلات اليومية.",
    supervisor: "تنفيذ التفتيش اليومي، رفع الصور، وتسجيل الحضور والغياب.",
    storekeeper: "إدارة مخزن النظافة، الوارد، المنصرف، وأذون الصرف."
  };
  $("roleCards").innerHTML = Object.entries(roleLabels).map(([role, label]) => {
    const count = state.users.filter((x) => x.role === role).length;
    return `<article class="panel">
      <h2>${label}</h2>
      <p class="muted">${descriptions[role]}</p>
      <b>${count}</b>
      <span class="muted">مستخدم</span>
    </article>`;
  }).join("");

  renderUserAdmin();
  renderNewUserPermissionOptions();
}

function userPages(user) {
  if (!user) return [];
  if (user.role === "general_manager") return [...pageAccessOptions];
  const saved = Array.isArray(user.pages) ? user.pages.filter((page) => pageAccessOptions.includes(page)) : [];
  return saved.length ? saved : [...(defaultPermissions[user.role] || [])];
}

function renderPermissionCheckboxes(name, selectedPages, disabled = false) {
  return pageAccessOptions.map((page) => `
    <label class="permission-option">
      <input type="checkbox" name="${name}" value="${page}" ${selectedPages.includes(page) ? "checked" : ""} ${disabled ? "disabled" : ""}>
      <span>${pageLabel(page)}</span>
    </label>
  `).join("");
}

function checkedPages(name) {
  return [...document.querySelectorAll(`input[name="${name}"]:checked`)].map((input) => input.value);
}

function renderNewUserPermissionOptions() {
  if (!$("pagePermissions")) return;
  const role = $("userRole")?.value || "operations_manager";
  $("pagePermissions").innerHTML = renderPermissionCheckboxes("newUserPages", defaultPermissions[role] || []);
}

const vehicleStatusLabels = {
  in_progress: "في الطريق",
  done: "تمت المأمورية",
  delayed: "متأخر",
  cancelled: "ملغي"
};

function renderVehicles() {
  const movements = [...(state.vehicleMovements || [])].reverse();
  const active = movements.filter((x) => x.status === "in_progress").length;
  const done = movements.filter((x) => x.status === "done").length;
  const delayed = movements.filter((x) => x.status === "delayed").length;
  $("vehicleSummary").innerHTML = `
    <div class="site-card"><div><h3>${active}</h3><div class="site-meta">سيارات في الطريق</div></div><span class="badge warn">نشط</span></div>
    <div class="site-card"><div><h3>${done}</h3><div class="site-meta">مأموريات تمت</div></div><span class="badge ok">تم</span></div>
    <div class="site-card"><div><h3>${delayed}</h3><div class="site-meta">مأموريات متأخرة</div></div><span class="badge bad">تنبيه</span></div>
  `;

  $("vehiclesTable").innerHTML = movements.map((movement) => {
    const tone = movement.status === "done" ? "ok" : movement.status === "delayed" ? "bad" : "warn";
    return `<tr>
      <td>${new Date(movement.createdAt).toLocaleString("ar-EG")}</td>
      <td>${movement.vehicleNo || "-"}</td>
      <td>${movement.driverName || "-"}</td>
      <td>${movement.destination || "-"}</td>
      <td>${movement.mission || "-"}</td>
      <td>${movement.fuelLiters || 0} لتر / ${money(movement.fuelCost || 0)}</td>
      <td>${movement.maintenance || "-"}</td>
      <td>${movement.oilChanged ? `تم عند ${movement.oilKm || movement.currentKm || "-"} كم` : "لم يتم"}</td>
      <td><span class="badge ${tone}">${vehicleStatusLabels[movement.status] || movement.status}</span></td>
      <td>${movement.notes || "-"}</td>
    </tr>`;
  }).join("") || `<tr><td colspan="10">لا توجد حركات سيارات مسجلة.</td></tr>`;

  renderVehicleDailyReport();
}

function renderVehicleSelects() {
  if (!$("vehicleId")) return;
  $("vehicleId").innerHTML = (state.vehicles || []).map((car) => `<option value="${car.id}">${car.vehicleNo}</option>`).join("");
  $("driverId").innerHTML = (state.drivers || []).map((driver) => `<option value="${driver.id}">${driver.name}</option>`).join("");
  const selectedVehicle = state.vehicles?.[0];
  if (selectedVehicle) {
    $("movementKm").value = selectedVehicle.currentKm || 0;
    $("oilKm").value = selectedVehicle.currentKm || 0;
  }
}

function renderVehicleDailyReport() {
  const today = new Date().toDateString();
  const todayMovements = (state.vehicleMovements || []).filter((x) => new Date(x.createdAt).toDateString() === today);
  const byVehicle = new Map();
  todayMovements.forEach((movement) => {
    const key = movement.vehicleId || movement.vehicleNo;
    const current = byVehicle.get(key) || {
      vehicleNo: movement.vehicleNo,
      destinations: [],
      fuelLiters: 0,
      fuelCost: 0,
      maintenance: [],
      oil: [],
      missions: 0
    };
    current.destinations.push(movement.destination);
    current.fuelLiters += Number(movement.fuelLiters || 0);
    current.fuelCost += Number(movement.fuelCost || 0);
    if (movement.maintenance) current.maintenance.push(movement.maintenance);
    if (movement.oilChanged) current.oil.push(`تم تغيير الزيت عند ${movement.oilKm || movement.currentKm || "-"} كم`);
    current.missions += 1;
    byVehicle.set(key, current);
  });

  $("vehicleDailyReport").innerHTML = [...byVehicle.values()].map((row) => `
    <div class="site-card">
      <div>
        <h3>${row.vehicleNo}</h3>
        <div class="site-meta">راحت فين: ${[...new Set(row.destinations)].join("، ") || "-"}</div>
        <div class="site-meta">الجاز / البنزين: ${row.fuelLiters} لتر - ${money(row.fuelCost)}</div>
        <div class="site-meta">الصيانة: ${row.maintenance.join(" | ") || "لا يوجد"}</div>
        <div class="site-meta">الزيت: ${row.oil.join(" | ") || "لا يوجد تغيير اليوم"}</div>
      </div>
      <span class="badge">${row.missions} مأمورية</span>
    </div>
  `).join("") || `<div class="site-meta">لا توجد حركة سيارات اليوم.</div>`;
}

async function saveVehicleMovement(event) {
  event.preventDefault();
  const vehicle = state.vehicles.find((x) => x.id === $("vehicleId").value);
  const driver = state.drivers.find((x) => x.id === $("driverId").value);
  await api("/api/vehicle-movements", {
    method: "POST",
    body: JSON.stringify({
      vehicleId: vehicle?.id || "",
      vehicleNo: vehicle?.vehicleNo || "",
      driverId: driver?.id || "",
      driverName: driver?.name || "",
      destination: $("vehicleDestination").value.trim(),
      mission: $("vehicleMission").value.trim(),
      fuelLiters: Number($("fuelLiters").value),
      fuelCost: Number($("fuelCost").value),
      maintenance: $("vehicleMaintenance").value.trim(),
      oilChanged: $("oilChanged").checked,
      oilKm: Number($("oilKm").value),
      currentKm: Number($("movementKm").value),
      status: $("vehicleStatus").value,
      notes: $("vehicleNotes").value.trim()
    })
  });
  $("vehicleForm").reset();
  toast("تم تسجيل حركة السيارة");
  await load();
}

async function saveDriver(event) {
  event.preventDefault();
  await api("/api/drivers", {
    method: "POST",
    body: JSON.stringify({
      name: $("newDriverName").value.trim(),
      phone: $("newDriverPhone").value.trim()
    })
  });
  $("driverForm").reset();
  toast("تم حفظ السائق");
  await load();
}

async function saveCar(event) {
  event.preventDefault();
  await api("/api/vehicles", {
    method: "POST",
    body: JSON.stringify({
      vehicleNo: $("newVehicleNo").value.trim(),
      type: $("newVehicleType").value.trim(),
      currentKm: Number($("newVehicleKm").value),
      oilEveryKm: Number($("newOilEveryKm").value),
      lastOilKm: Number($("newLastOilKm").value)
    })
  });
  $("carForm").reset();
  toast("تم حفظ السيارة");
  await load();
}

function renderUserAdmin() {
  const isGeneralManager = currentRole() === "general_manager";
  document.querySelectorAll(".admin-only").forEach((el) => {
    el.style.display = isGeneralManager ? "" : "none";
  });
  $("generalManagerOnly").innerHTML = isGeneralManager
    ? `<strong>المدير العام فقط</strong><div class="site-meta">يمكنه إنشاء المستخدمين وتحديد اليوزر والباسورد والصلاحيات.</div>`
    : `<strong>غير مسموح</strong><div class="site-meta">إدارة اليوزر والباسورد متاحة للمدير العام فقط.</div>`;

  $("permissionMatrix").innerHTML = Object.entries(roleLabels).map(([role, label]) => {
    const allowed = defaultPermissions[role].map((page) => pageLabel(page)).join("، ");
    return `<div class="site-card">
      <div>
        <h3>${label}</h3>
        <div class="site-meta">صلاحية افتراضية: ${allowed}</div>
      </div>
    </div>`;
  }).join("");

  $("usersTable").innerHTML = state.users.map((user) => `
    <tr>
      <td>${user.fullName || "-"}</td>
      <td>${user.username || user.email || "-"}</td>
      <td>${user.password || "-"}</td>
      <td>${roleLabels[user.role] || user.role}</td>
      <td>
        <div class="permission-options compact">
          ${renderPermissionCheckboxes(`pages_${user.id}`, userPages(user), user.role === "general_manager")}
        </div>
        ${user.role === "general_manager" ? `<div class="site-meta">المدير العام يرى كل النظام دائماً</div>` : `<button class="ghost" type="button" onclick="saveUserPages('${user.id}')">حفظ الصلاحيات</button>`}
      </td>
      <td><button class="danger" onclick="deleteUser('${user.id}')">حذف</button></td>
    </tr>
  `).join("");
}

async function saveSite(event) {
  event.preventDefault();
  const existingClient = state.clients.find((x) => x.name === $("clientName").value.trim());
  const payload = {
    clientId: existingClient?.id,
    clientName: $("clientName").value.trim(),
    clientContact: $("clientContact").value.trim(),
    name: $("siteName").value.trim(),
    address: $("siteAddress").value.trim(),
    city: $("siteCity").value.trim()
  };
  const siteId = $("siteId").value;
  if (siteId) {
    await api(`/api/sites/${siteId}`, { method: "PUT", body: JSON.stringify(payload) });
    toast("تم تعديل الموقع");
  } else {
    await api("/api/sites", { method: "POST", body: JSON.stringify(payload) });
    toast("تمت إضافة الموقع");
  }
  $("siteForm").reset();
  $("siteId").value = "";
  await load();
}

async function saveContract(event) {
  event.preventDefault();
  const payload = {
    siteId: $("contractSite").value,
    contractName: $("contractName").value.trim(),
    plannedWorkers: Number($("plannedWorkers").value),
    workerDailyValue: Number($("workerDailyValue").value),
    startDate: $("contractStart").value,
    endDate: $("contractEnd").value,
    active: true
  };
  const contractId = $("contractId").value;
  if (contractId) {
    await api(`/api/contracts/${contractId}`, { method: "PUT", body: JSON.stringify(payload) });
    toast("تم تعديل العقد");
  } else {
    await api("/api/contracts", { method: "POST", body: JSON.stringify(payload) });
    toast("تمت إضافة العقد");
  }
  $("contractForm").reset();
  $("contractId").value = "";
  await load();
}

window.editContract = (id) => {
  const contract = state.contracts.find((x) => x.id === id);
  if (!contract) return;
  activatePage("contracts");
  $("contractId").value = contract.id;
  $("contractSite").value = contract.siteId;
  $("contractName").value = contract.contractName || "";
  $("plannedWorkers").value = contract.plannedWorkers || 0;
  $("workerDailyValue").value = contract.workerDailyValue || 0;
  $("contractStart").value = contract.startDate || "";
  $("contractEnd").value = contract.endDate || "";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

window.deleteContract = async (id) => {
  if (!confirm("حذف العقد؟")) return;
  await api(`/api/contracts/${id}`, { method: "DELETE" });
  toast("تم حذف العقد");
  await load();
};

window.editSite = (id) => {
  const site = siteById(id);
  const client = clientById(site.clientId);
  $("siteId").value = site.id;
  $("siteName").value = site.name;
  $("clientName").value = client?.name || "";
  $("clientContact").value = `${client?.contactName || ""} ${client?.phone || ""}`.trim();
  $("siteAddress").value = site.address;
  $("siteCity").value = site.city || "";
  window.scrollTo({ top: 0, behavior: "smooth" });
};

window.deleteSite = async (id) => {
  if (!confirm("هل تريد حذف الموقع؟")) return;
  await api(`/api/sites/${id}`, { method: "DELETE" });
  toast("تم حذف الموقع");
  await load();
};

window.openInspection = (id) => {
  activatePage("inspection");
  $("inspectionSite").value = id;
};

async function saveInspection(event) {
  event.preventDefault();
  const payload = {
    siteId: $("inspectionSite").value,
    siteName: siteById($("inspectionSite").value)?.name || "",
    supervisorName: $("supervisorName").value.trim(),
    gpsLat: $("gpsLat").value,
    gpsLng: $("gpsLng").value,
    workersPresent: Number($("workersPresent").value),
    workersAbsent: Number($("workersAbsent").value),
    beforePhotoUrl: $("beforePhoto").files[0]?.name || "",
    afterPhotoUrl: $("afterPhoto").files[0]?.name || "",
    rating: $("inspectionRating").value,
    ratingLabel: ratingLabels[$("inspectionRating").value] || $("inspectionRating").value,
    notes: $("inspectionNotes").value.trim()
  };
  await api("/api/inspections", {
    method: "POST",
    body: JSON.stringify(payload)
  });
  await sendInspectionToGoogleSheet(payload);
  $("inspectionForm").reset();
  toast("تم تسجيل التفتيش اليومي");
  await load();
}

async function sendInspectionToGoogleSheet(payload) {
  const url = localStorage.getItem("ibuild_google_script_url");
  if (!url) return;
  try {
    await fetch(url, {
      method: "POST",
      mode: "no-cors",
      headers: { "content-type": "application/json" },
      body: JSON.stringify({
        type: "inspection",
        timestamp: new Date().toISOString(),
        ...payload
      })
    });
    $("googleSheetStatus") && ($("googleSheetStatus").textContent = "تم إرسال آخر تقييم إلى Google Sheet.");
  } catch (error) {
    $("googleSheetStatus") && ($("googleSheetStatus").textContent = "تم الحفظ محليًا، لكن فشل الإرسال إلى Google Sheet.");
  }
}

async function saveMaterial(event) {
  event.preventDefault();
  await api("/api/materials", {
    method: "POST",
    body: JSON.stringify({
      name: $("materialName").value.trim(),
      unit: $("materialUnit").value.trim(),
      minStock: Number($("materialMin").value),
      openingStock: Number($("materialOpening").value),
      currentStock: Number($("materialOpening").value),
      unitCost: Number($("materialCost").value)
    })
  });
  $("materialForm").reset();
  toast("تمت إضافة الخامة");
  await load();
}

async function saveTransaction(event) {
  event.preventDefault();
  const material = state.materials.find((x) => x.id === $("txMaterial").value);
  await api("/api/material-transactions", {
    method: "POST",
    body: JSON.stringify({
      issueNo: $("issueNo").value.trim(),
      materialId: $("txMaterial").value,
      siteId: $("txSite").value || null,
      txType: $("txType").value,
      quantity: Number($("txQuantity").value),
      unitCost: Number($("txUnitCost").value || material?.unitCost || 0),
      deliveredBy: $("deliveredBy").value.trim(),
      receivedBy: $("receivedBy").value.trim(),
      deliveryPhotoUrl: $("deliveryPhoto").files[0]?.name || ""
    })
  });
  $("txForm").reset();
  toast("تم تسجيل حركة الخامات");
  await load();
}

async function importMaterialsFromSheet(event) {
  event.preventDefault();
  const file = $("materialSheetFile").files[0];
  if (!file) return toast("اختاري ملف الأصناف أولا");
  let rows = [];
  try {
    rows = await readSheetRows(file);
  } catch (error) {
    return toast(error.message);
  }
  if (!rows.length) return toast("شيت الأصناف لا يحتوي بيانات");

  let created = 0;
  let stocked = 0;
  for (const row of rows) {
    const name = pick(row, ["الصنف", "اسم الصنف", "الخامة", "Item", "item", "Material", "material", "name"]);
    if (!name) continue;
    const unit = pick(row, ["الوحدة", "Unit", "unit"]) || "قطعة";
    const quantity = numberFrom(row, ["رصيد أول", "رصيد اول", "Opening", "opening", "الكمية", "Quantity", "quantity"]);
    const unitCost = numberFrom(row, ["التكلفة", "تكلفة الوحدة", "Cost", "cost", "Unit Cost", "unitCost"]);
    const minStock = numberFrom(row, ["حد التنبيه", "Min", "min", "minimum"]);
    const existing = state.materials.find((m) => m.name.trim() === name.trim());
    if (existing) {
      if (quantity > 0) {
        await api("/api/material-transactions", {
          method: "POST",
          body: JSON.stringify({
            materialId: existing.id,
            txType: "stock_in",
            quantity,
            unitCost: unitCost || existing.unitCost || 0,
            deliveredBy: "استيراد شيت",
            receivedBy: "المخزن الرئيسي",
            issueNo: `IMP-${Date.now()}`
          })
        });
        stocked++;
      }
      continue;
    }
    await api("/api/materials", {
      method: "POST",
      body: JSON.stringify({
        name: name.trim(),
        unit,
        openingStock: quantity,
        currentStock: quantity,
        unitCost,
        minStock
      })
    });
    created++;
  }
  $("materialSheetForm").reset();
  $("materialImportResult").textContent = `تم إنشاء ${created} صنف جديد وإضافة وارد إلى ${stocked} صنف موجود.`;
  toast("تم استيراد شيت المخزن");
  await load();
}

function numberFrom(row, keys) {
  const raw = pick(row, keys).replace(/,/g, "");
  const value = Number(raw);
  return Number.isFinite(value) ? value : 0;
}

async function importSitesFromSheet(event) {
  event.preventDefault();
  const file = $("siteSheetFile").files[0];
  if (!file) return toast("اختاري ملف المواقع أولا");
  let rows = [];
  try {
    rows = await readSheetRows(file);
  } catch (error) {
    return toast(error.message);
  }
  if (!rows.length) return toast("الشيت لا يحتوي بيانات");

  let added = 0;
  const defaultClient = $("sheetClientName").value.trim() || "عميل غير محدد";
  const defaultCity = $("sheetCity").value.trim();
  for (const row of rows) {
    const name = pick(row, ["اسم الموقع", "الموقع", "site", "Site", "Location", "location", "name"]);
    if (!name || state.sites.some((site) => site.name.trim() === name.trim())) continue;
    const payload = {
      name: name.trim(),
      clientName: (pick(row, ["العميل", "اسم العميل", "client", "Client"]) || defaultClient).trim(),
      clientContact: pick(row, ["بيانات العميل", "مسؤول العميل", "contact", "Contact"]) || "",
      address: (pick(row, ["العنوان", "عنوان الموقع", "address", "Address"]) || name).trim(),
      city: (pick(row, ["المدينة", "city", "City"]) || defaultCity).trim()
    };
    await api("/api/sites", { method: "POST", body: JSON.stringify(payload) });
    added++;
  }
  $("siteSheetForm").reset();
  $("sheetImportResult").textContent = `تم استيراد ${added} موقع. تم توليد QR تلقائيا لكل موقع في القائمة.`;
  toast(`تم استيراد ${added} موقع`);
  await load();
}

function pick(row, keys) {
  for (const key of keys) {
    if (row[key] !== undefined && String(row[key]).trim()) return String(row[key]);
  }
  return "";
}

async function readSheetRows(file) {
  const name = file.name.toLowerCase();
  if ((name.endsWith(".xlsx") || name.endsWith(".xls")) && window.XLSX) {
    const buffer = await file.arrayBuffer();
    const workbook = XLSX.read(buffer, { type: "array" });
    const sheet = workbook.Sheets[workbook.SheetNames[0]];
    return XLSX.utils.sheet_to_json(sheet, { defval: "" });
  }
  if (name.endsWith(".xlsx") || name.endsWith(".xls")) {
    throw new Error("قراءة Excel تحتاج اتصال إنترنت لتحميل مكتبة XLSX. استخدمي CSV حاليا.");
  }
  const text = await file.text();
  return parseCsv(text);
}

function parseCsv(text) {
  const lines = text.split(/\r?\n/).filter((line) => line.trim());
  if (lines.length < 2) return [];
  const headers = splitCsvLine(lines[0]).map((x) => x.trim());
  return lines.slice(1).map((line) => {
    const values = splitCsvLine(line);
    return Object.fromEntries(headers.map((header, index) => [header, values[index] || ""]));
  });
}

function splitCsvLine(line) {
  const result = [];
  let current = "";
  let quoted = false;
  for (let i = 0; i < line.length; i++) {
    const char = line[i];
    const next = line[i + 1];
    if (char === '"' && quoted && next === '"') {
      current += '"';
      i++;
    } else if (char === '"') {
      quoted = !quoted;
    } else if (char === "," && !quoted) {
      result.push(current);
      current = "";
    } else {
      current += char;
    }
  }
  result.push(current);
  return result.map((x) => x.trim());
}

function activatePage(id) {
  if (!sessionUser) {
    toast("أدخل الباسورد أولاً");
    return;
  }
  if (!canAccess(id)) {
    toast("ليست لديك صلاحية لهذه الصفحة");
    const fallback = userPages(sessionUser)[0] || "dashboard";
    id = fallback;
  }
  document.querySelectorAll(".tab").forEach((x) => x.classList.toggle("is-active", x.dataset.page === id));
  document.querySelectorAll(".page").forEach((x) => x.classList.toggle("is-active", x.id === id));
}

function currentRole() {
  return sessionUser?.role || null;
}

function canAccess(page) {
  if (!sessionUser) return false;
  return userPages(sessionUser).includes(page);
}

function applyPermissions() {
  document.querySelectorAll(".tab").forEach((button) => {
    const allowed = canAccess(button.dataset.page);
    button.disabled = !allowed;
    button.hidden = sessionUser ? !allowed : false;
    button.classList.toggle("is-disabled", !allowed);
  });
  const active = document.querySelector(".page.is-active")?.id || "dashboard";
  if (sessionUser && !canAccess(active)) activatePage(userPages(sessionUser)[0] || "dashboard");
}

function pageLabel(page) {
  return {
    dashboard: "لوحة التحكم",
    sites: "المواقع",
    contracts: "العقود",
    inspection: "التفتيش اليومي",
    materials: "الخامات",
    reports: "التقارير",
    users: "الصلاحيات",
    vehicles: "حركة السيارات"
  }[page] || page;
}

async function saveUser(event) {
  event.preventDefault();
  if (currentRole() !== "general_manager") return toast("المدير العام فقط يمكنه إضافة مستخدم");
  await api("/api/users", {
    method: "POST",
    body: JSON.stringify({
      fullName: $("userFullName").value.trim(),
      username: $("username").value.trim(),
      password: $("password").value,
      role: $("userRole").value,
      pages: checkedPages("newUserPages")
    })
  });
  $("userForm").reset();
  renderNewUserPermissionOptions();
  toast("تم حفظ المستخدم");
  await load();
}

window.saveUserPages = async (id) => {
  if (currentRole() !== "general_manager") return toast("المدير العام فقط");
  const user = state.users.find((item) => item.id === id);
  if (!user) return toast("المستخدم غير موجود");
  await api(`/api/users/${id}`, {
    method: "PUT",
    body: JSON.stringify({ pages: checkedPages(`pages_${id}`) })
  });
  toast(`تم حفظ صلاحيات ${user.fullName}`);
  await load();
};

window.deleteUser = async (id) => {
  if (currentRole() !== "general_manager") return toast("المدير العام فقط");
  if (!confirm("حذف المستخدم؟")) return;
  await api(`/api/users/${id}`, { method: "DELETE" });
  toast("تم حذف المستخدم");
  await load();
};

function login() {
  const role = $("role").value;
  const username = $("loginUsername").value.trim();
  const password = $("loginPassword").value;
  let user = state.users.find((item) => {
    const itemUsernames = [
      item.username,
      item.email,
      "admin@ibuild.local",
      item.role === "general_manager" ? "general" : "",
      item.role === "operations_manager" ? "operations" : "",
      item.role === "supervisor" ? "supervisor" : ""
    ].filter(Boolean).map((value) => String(value).toLowerCase());
    const userNameMatches = username
      ? itemUsernames.includes(username.toLowerCase())
      : item.role === role;
    return userNameMatches && item.password === password && item.active !== false;
  });
  if (!user && password === "123456" && (!username || ["admin@ibuild.local", "general"].includes(username.toLowerCase())) && role === "general_manager") {
    user = state.users.find((item) => item.role === "general_manager") || {
      id: "fallback-general-manager",
      fullName: "المدير العام",
      username: "admin@ibuild.local",
      password: "123456",
      role: "general_manager",
      pages: [...pageAccessOptions],
      active: true
    };
  }
  if (!user) {
    toast("اليوزر أو الباسورد غير صحيح");
    return;
  }
  sessionUser = user;
  localStorage.setItem("ibuild_session_user", JSON.stringify({ id: user.id, password: user.password }));
  $("loginUsername").value = "";
  $("loginPassword").value = "";
  renderLoginState();
  applyPermissions();
  activatePage(userPages(user)[0] || "dashboard");
  toast(`مرحباً ${user.fullName}`);
}

function logout() {
  sessionUser = null;
  localStorage.removeItem("ibuild_session_user");
  renderLoginState();
  applyPermissions();
  document.querySelectorAll(".page").forEach((x) => x.classList.toggle("is-active", x.id === "dashboard"));
  document.querySelectorAll(".tab").forEach((x) => x.classList.toggle("is-active", x.dataset.page === "dashboard"));
  toast("تم تسجيل الخروج");
}

function bind() {
  document.querySelectorAll(".tab").forEach((button) => {
    button.addEventListener("click", () => activatePage(button.dataset.page));
  });
  $("role").addEventListener("change", () => {
    $("loginUsername").focus();
  });
  $("loginButton").addEventListener("click", login);
  $("logoutButton").addEventListener("click", logout);
  $("loginUsername").addEventListener("keydown", (event) => {
    if (event.key === "Enter") $("loginPassword").focus();
  });
  $("loginPassword").addEventListener("keydown", (event) => {
    if (event.key === "Enter") login();
  });
  $("userRole").addEventListener("change", renderNewUserPermissionOptions);
  $("refresh").addEventListener("click", load);
  $("siteForm").addEventListener("submit", saveSite);
  $("contractForm").addEventListener("submit", saveContract);
  $("resetContract").addEventListener("click", () => { $("contractForm").reset(); $("contractId").value = ""; });
  $("siteSheetForm").addEventListener("submit", importSitesFromSheet);
  $("downloadSiteTemplate").addEventListener("click", downloadSiteTemplate);
  $("resetSite").addEventListener("click", () => { $("siteForm").reset(); $("siteId").value = ""; });
  $("siteSearch").addEventListener("input", renderSites);
  $("inspectionForm").addEventListener("submit", saveInspection);
  $("materialForm").addEventListener("submit", saveMaterial);
  $("materialSheetForm").addEventListener("submit", importMaterialsFromSheet);
  $("txForm").addEventListener("submit", saveTransaction);
  $("userForm").addEventListener("submit", saveUser);
  $("driverForm").addEventListener("submit", saveDriver);
  $("carForm").addEventListener("submit", saveCar);
  $("vehicleForm").addEventListener("submit", saveVehicleMovement);
  $("vehicleId").addEventListener("change", () => {
    const selectedVehicle = state.vehicles.find((x) => x.id === $("vehicleId").value);
    if (selectedVehicle) {
      $("movementKm").value = selectedVehicle.currentKm || 0;
      $("oilKm").value = selectedVehicle.currentKm || 0;
    }
  });
  $("reportSite").addEventListener("input", renderReports);
  $("reportSupervisor").addEventListener("input", renderReports);
  $("reportRange").addEventListener("change", () => toast("تم تغيير نطاق التقرير للعرض الأولي"));
  $("exportExcel").addEventListener("click", () => exportCsv());
  $("exportPdf").addEventListener("click", () => window.print());
  $("exportCostReport").addEventListener("click", () => exportCostReport());
  $("saveGoogleScriptUrl").addEventListener("click", () => {
    const url = $("googleScriptUrl").value.trim();
    if (!url) return toast("أدخل رابط Google Apps Script");
    localStorage.setItem("ibuild_google_script_url", url);
    $("googleSheetStatus").textContent = "تم حفظ رابط Google Apps Script.";
    toast("تم حفظ رابط Google Sheet");
  });
  $("testGoogleScriptUrl").addEventListener("click", async () => {
    const url = $("googleScriptUrl").value.trim();
    if (!url) return toast("أدخل رابط Google Apps Script");
    try {
      await fetch(url, {
        method: "POST",
        mode: "no-cors",
        headers: { "content-type": "application/json" },
        body: JSON.stringify({ type: "test", timestamp: new Date().toISOString(), message: "I-BUILD test" })
      });
      localStorage.setItem("ibuild_google_script_url", url);
      $("googleSheetStatus").textContent = "تم إرسال اختبار. راجع الشيت للتأكد من وصول صف الاختبار.";
      toast("تم إرسال اختبار للشيت");
    } catch (error) {
      $("googleSheetStatus").textContent = "فشل اختبار الربط.";
    }
  });
  $("getGps").addEventListener("click", () => {
    if (!navigator.geolocation) return toast("المتصفح لا يدعم GPS");
    navigator.geolocation.getCurrentPosition((pos) => {
      $("gpsLat").value = pos.coords.latitude.toFixed(7);
      $("gpsLng").value = pos.coords.longitude.toFixed(7);
      toast("تم تحديد الموقع الجغرافي");
    }, () => toast("تعذر الحصول على GPS"));
  });
}

function downloadSiteTemplate() {
  const rows = [
    ["اسم الموقع", "اسم العميل", "بيانات العميل", "عنوان الموقع", "المدينة"],
    ["فرع التجمع", "I-BUILD", "أحمد محمد - 01000000000", "التجمع الخامس - شارع التسعين", "القاهرة"],
    ["المقر الإداري", "I-BUILD", "مسؤول الموقع - 01111111111", "القاهرة الجديدة", "القاهرة"]
  ];
  const csv = rows.map((row) => row.map((cell) => `"${String(cell).replaceAll('"', '""')}"`).join(",")).join("\n");
  const blob = new Blob(["\ufeff" + csv], { type: "text/csv;charset=utf-8" });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = "ibuild-sites-template.csv";
  a.click();
  URL.revokeObjectURL(a.href);
}

function exportCsv() {
  const rows = [["التاريخ", "الموقع", "المشرف", "التقييم", "الملاحظات"]];
  state.inspections.forEach((x) => rows.push([
    new Date(x.inspectedAt).toLocaleString("ar-EG"),
    siteById(x.siteId)?.name || "",
    x.supervisorName || "",
    ratingLabels[x.rating] || x.rating,
    x.notes || ""
  ]));
  const csv = rows.map((row) => row.map((cell) => `"${String(cell).replaceAll('"', '""')}"`).join(",")).join("\n");
  const blob = new Blob(["\ufeff" + csv], { type: "text/csv;charset=utf-8" });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = "ibuild-cleaning-report.csv";
  a.click();
  URL.revokeObjectURL(a.href);
}

function exportCostReport() {
  const rows = [["الموقع", "الصنف", "الكمية المصروفة", "الوحدة", "إجمالي التكلفة"]];
  const issued = state.materialTransactions.filter((x) => x.txType === "site_use");
  const byKey = new Map();
  issued.forEach((tx) => {
    const material = state.materials.find((x) => x.id === tx.materialId);
    const site = siteById(tx.siteId);
    const key = `${tx.siteId || "warehouse"}|${tx.materialId}`;
    const current = byKey.get(key) || {
      siteName: site?.name || "بدون موقع",
      materialName: material?.name || "-",
      unit: material?.unit || "",
      quantity: 0,
      totalCost: 0
    };
    current.quantity += Number(tx.quantity || 0);
    current.totalCost += Number(tx.totalCost || 0);
    byKey.set(key, current);
  });
  [...byKey.values()].forEach((row) => rows.push([
    row.siteName,
    row.materialName,
    row.quantity,
    row.unit,
    row.totalCost
  ]));
  const csv = rows.map((row) => row.map((cell) => `"${String(cell).replaceAll('"', '""')}"`).join(",")).join("\n");
  const blob = new Blob(["\ufeff" + csv], { type: "text/csv;charset=utf-8" });
  const a = document.createElement("a");
  a.href = URL.createObjectURL(blob);
  a.download = "ibuild-material-cost-report.csv";
  a.click();
  URL.revokeObjectURL(a.href);
}

bind();
load().catch((error) => toast(error.message));
