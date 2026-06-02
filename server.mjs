import http from "node:http";
import { readFile, writeFile, mkdir } from "node:fs/promises";
import { existsSync, createReadStream } from "node:fs";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const publicDir = path.join(__dirname, "public");
const dataDir = path.join(__dirname, "data");
const dataFile = path.join(dataDir, "db.json");
const port = Number(process.env.PORT || 4180);
const pageIds = ["dashboard", "sites", "contracts", "inspection", "materials", "reports", "users", "vehicles"];
const defaultPagesByRole = {
  general_manager: pageIds,
  operations_manager: ["dashboard"],
  supervisor: ["inspection"],
  storekeeper: ["materials"]
};

const seed = {
  users: [
    { id: "u1", fullName: "المدير العام", username: "general", password: "123456", role: "general_manager", active: true },
    { id: "u2", fullName: "مدير العمليات", username: "operations", password: "123456", role: "operations_manager", active: true },
    { id: "u3", fullName: "مشرف النظافة", username: "supervisor", password: "123456", role: "supervisor", active: true },
    { id: "u4", fullName: "أمين المخزن", username: "storekeeper", password: "123456", role: "storekeeper", active: true }
  ],
  clients: [
    { id: "c1", name: "عميل تجريبي", contactName: "مسؤول الموقع", phone: "01000000000", email: "client@example.com" }
  ],
  drivers: [
    { id: "d1", name: "سائق تجريبي", phone: "01000000000", active: true }
  ],
  vehicles: [
    { id: "car1", vehicleNo: "نقل 1254", type: "نقل", oilEveryKm: 5000, currentKm: 12000, lastOilKm: 10000, active: true }
  ],
  sites: [
    { id: "s1", clientId: "c1", name: "المقر الإداري", address: "القاهرة الجديدة", city: "القاهرة", status: "active", qrToken: "site-s1" },
    { id: "s2", clientId: "c1", name: "فرع التجمع", address: "التجمع الخامس", city: "القاهرة", status: "active", qrToken: "site-s2" }
  ],
  contracts: [
    { id: "ct1", siteId: "s1", contractName: "عقد نظافة المقر الإداري", workerDailyValue: 250, plannedWorkers: 4, startDate: "2026-06-01", endDate: "2026-12-31", active: true }
  ],
  inspections: [
    { id: "i1", siteId: "s1", supervisorName: "مشرف النظافة", inspectedAt: new Date().toISOString(), rating: "excellent", notes: "الحالة ممتازة" },
    { id: "i2", siteId: "s2", supervisorName: "مشرف النظافة", inspectedAt: new Date().toISOString(), rating: "needs_followup", notes: "يحتاج متابعة في منطقة المدخل" }
  ],
  materials: [
    { id: "m1", name: "مطهر أرضيات", unit: "لتر", minStock: 20, currentStock: 45 },
    { id: "m2", name: "أكياس قمامة", unit: "رول", minStock: 30, currentStock: 18 }
  ],
  materialTransactions: [
    { id: "t1", materialId: "m1", siteId: "s1", txType: "site_out", quantity: 5, deliveredBy: "المخزن", receivedBy: "مشرف النظافة", createdAt: new Date().toISOString() }
  ],
  vehicleMovements: [
    { id: "v1", vehicleId: "car1", vehicleNo: "نقل 1254", driverId: "d1", driverName: "سائق تجريبي", destination: "المقر الإداري", mission: "توريد خامات", fuelLiters: 20, fuelCost: 0, maintenance: "", oilChanged: false, oilKm: 0, currentKm: 12050, status: "in_progress", notes: "", createdAt: new Date().toISOString() }
  ]
};

async function ensureDb() {
  await mkdir(dataDir, { recursive: true });
  if (!existsSync(dataFile)) {
    await writeJson(seed);
  }
}

async function readJson() {
  await ensureDb();
  const raw = (await readFile(dataFile, "utf8")).replace(/^\uFEFF/, "");
  const db = JSON.parse(raw);
  return normalizeDb(db);
}

function normalizeDb(db) {
  db.users = (db.users || []).map((user) => {
    const role = user.role === "system_admin" ? "general_manager" : user.role;
    const pages = sanitizePages(user.pages, role);
    return {
      username: user.email || user.username || "",
      password: user.password || "123456",
      active: true,
      ...user,
      role,
      pages
    };
  });
  ensureUser(db, { id: "u1", fullName: "المدير العام", username: "general", password: "123456", role: "general_manager", active: true });
  ensureUser(db, { id: "u2", fullName: "مدير العمليات", username: "operations", password: "123456", role: "operations_manager", active: true });
  ensureUser(db, { id: "u3", fullName: "مشرف النظافة", username: "supervisor", password: "123456", role: "supervisor", active: true });
  ensureUser(db, { id: "u4", fullName: "أمين المخزن", username: "storekeeper", password: "123456", role: "storekeeper", active: true });
  db.materials = (db.materials || []).map((material) => {
    const currentStock = Number(material.currentStock ?? material.openingStock ?? 0);
    return {
      openingStock: currentStock,
      unitCost: 0,
      minStock: 0,
      unit: "قطعة",
      ...material,
      currentStock: Number(material.currentStock ?? currentStock),
      openingStock: Number(material.openingStock ?? currentStock),
      unitCost: Number(material.unitCost ?? 0),
      minStock: Number(material.minStock ?? 0)
    };
  });
  db.materialTransactions = (db.materialTransactions || []).map((tx) => ({
    issueNo: "",
    unitCost: 0,
    totalCost: 0,
    balanceBefore: null,
    balanceAfter: null,
    siteBalanceBefore: null,
    siteBalanceAfter: null,
    ...tx,
    quantity: Number(tx.quantity ?? 0),
    unitCost: Number(tx.unitCost ?? 0),
    totalCost: Number(tx.totalCost ?? 0)
  }));
  db.drivers = db.drivers || [];
  db.vehicles = db.vehicles || [];
  db.contracts = (db.contracts || []).map((contract) => ({
    contractName: "",
    workerDailyValue: 0,
    plannedWorkers: 0,
    startDate: "",
    endDate: "",
    active: true,
    ...contract,
    workerDailyValue: Number(contract.workerDailyValue || 0),
    plannedWorkers: Number(contract.plannedWorkers || 0)
  }));
  db.vehicleMovements = (db.vehicleMovements || []).map((movement) => ({
    vehicleId: "",
    vehicleNo: "",
    driverId: "",
    driverName: "",
    destination: "",
    mission: "",
    fuelLiters: 0,
    fuelCost: 0,
    maintenance: "",
    oilChanged: false,
    oilKm: 0,
    currentKm: 0,
    status: "in_progress",
    notes: "",
    createdAt: new Date().toISOString(),
    ...movement
  }));
  return db;
}

function ensureUser(db, user) {
  if (!db.users.some((item) => item.role === user.role)) {
    db.users.push({ ...user, pages: sanitizePages(user.pages, user.role) });
  }
}

function sanitizePages(pages, role) {
  if (role === "general_manager") return [...pageIds];
  const cleaned = Array.isArray(pages) ? pages.filter((page) => pageIds.includes(page)) : [];
  return cleaned.length ? [...new Set(cleaned)] : [...(defaultPagesByRole[role] || [])];
}

async function writeJson(data) {
  await writeFile(dataFile, JSON.stringify(data, null, 2), "utf8");
}

function sendJson(res, status, payload) {
  res.writeHead(status, { "content-type": "application/json; charset=utf-8" });
  res.end(JSON.stringify(payload));
}

function id(prefix) {
  return `${prefix}${Date.now().toString(36)}${Math.random().toString(36).slice(2, 7)}`;
}

async function body(req) {
  const chunks = [];
  for await (const chunk of req) chunks.push(chunk);
  const raw = Buffer.concat(chunks).toString("utf8");
  return raw ? JSON.parse(raw) : {};
}

function contentType(filePath) {
  const ext = path.extname(filePath).toLowerCase();
  return {
    ".html": "text/html; charset=utf-8",
    ".css": "text/css; charset=utf-8",
    ".js": "text/javascript; charset=utf-8",
    ".svg": "image/svg+xml; charset=utf-8",
    ".json": "application/json; charset=utf-8"
  }[ext] || "application/octet-stream";
}

async function api(req, res, url) {
  const db = await readJson();

  if (req.method === "GET" && url.pathname === "/api/bootstrap") {
    const today = new Date().toDateString();
    const dailyVisits = db.inspections.filter((x) => new Date(x.inspectedAt).toDateString() === today).length;
    const interventionSites = db.inspections.filter((x) => x.rating === "needs_followup" || x.rating === "critical").length;
    const lowStock = db.materials.filter((x) => Number(x.currentStock) <= Number(x.minStock));
    return sendJson(res, 200, {
      ...db,
      stats: {
        siteCount: db.sites.length,
        supervisorCount: db.users.filter((x) => x.role === "supervisor").length,
        dailyVisits,
        interventionSites,
        materialCount: db.materials.length,
        lowStockCount: lowStock.length
      }
    });
  }

  if (req.method === "POST" && url.pathname === "/api/users") {
    const input = await body(req);
    if (!input.fullName || !input.username || !input.password || !input.role) {
      return sendJson(res, 400, { error: "missing_user_fields" });
    }
    if (db.users.some((x) => x.username === input.username)) {
      return sendJson(res, 409, { error: "username_exists" });
    }
    const user = {
      id: id("u"),
      fullName: input.fullName,
      username: input.username,
      password: input.password,
      role: input.role,
      active: input.active !== false,
      pages: sanitizePages(input.pages, input.role)
    };
    db.users.push(user);
    await writeJson(db);
    return sendJson(res, 201, user);
  }

  if (req.method === "PUT" && url.pathname.startsWith("/api/users/")) {
    const userId = url.pathname.split("/").pop();
    const input = await body(req);
    const index = db.users.findIndex((x) => x.id === userId);
    if (index === -1) return sendJson(res, 404, { error: "user_not_found" });
    const nextRole = input.role || db.users[index].role;
    db.users[index] = {
      ...db.users[index],
      ...input,
      pages: sanitizePages(input.pages ?? db.users[index].pages, nextRole)
    };
    await writeJson(db);
    return sendJson(res, 200, db.users[index]);
  }

  if (req.method === "DELETE" && url.pathname.startsWith("/api/users/")) {
    const userId = url.pathname.split("/").pop();
    db.users = db.users.filter((x) => x.id !== userId);
    await writeJson(db);
    return sendJson(res, 200, { ok: true });
  }

  if (req.method === "POST" && url.pathname === "/api/sites") {
    const input = await body(req);
    let clientId = input.clientId;
    if (!clientId && input.clientName) {
      const client = {
        id: id("c"),
        name: input.clientName,
        contactName: input.clientContact || "",
        phone: "",
        email: ""
      };
      db.clients.push(client);
      clientId = client.id;
    }
    const site = {
      id: id("s"),
      qrToken: id("qr"),
      status: "active",
      clientId,
      name: input.name,
      address: input.address,
      city: input.city || ""
    };
    db.sites.push(site);
    await writeJson(db);
    return sendJson(res, 201, site);
  }

  if (req.method === "PUT" && url.pathname.startsWith("/api/sites/")) {
    const siteId = url.pathname.split("/").pop();
    const input = await body(req);
    const index = db.sites.findIndex((x) => x.id === siteId);
    if (index === -1) return sendJson(res, 404, { error: "site_not_found" });
    db.sites[index] = { ...db.sites[index], ...input };
    await writeJson(db);
    return sendJson(res, 200, db.sites[index]);
  }

  if (req.method === "DELETE" && url.pathname.startsWith("/api/sites/")) {
    const siteId = url.pathname.split("/").pop();
    db.sites = db.sites.filter((x) => x.id !== siteId);
    await writeJson(db);
    return sendJson(res, 200, { ok: true });
  }

  if (req.method === "POST" && url.pathname === "/api/contracts") {
    const input = await body(req);
    const contract = {
      id: id("ct"),
      siteId: input.siteId || "",
      contractName: input.contractName || "",
      workerDailyValue: Number(input.workerDailyValue || 0),
      plannedWorkers: Number(input.plannedWorkers || 0),
      startDate: input.startDate || "",
      endDate: input.endDate || "",
      active: input.active !== false
    };
    db.contracts.push(contract);
    await writeJson(db);
    return sendJson(res, 201, contract);
  }

  if (req.method === "PUT" && url.pathname.startsWith("/api/contracts/")) {
    const contractId = url.pathname.split("/").pop();
    const input = await body(req);
    const index = db.contracts.findIndex((x) => x.id === contractId);
    if (index === -1) return sendJson(res, 404, { error: "contract_not_found" });
    db.contracts[index] = {
      ...db.contracts[index],
      ...input,
      workerDailyValue: Number(input.workerDailyValue ?? db.contracts[index].workerDailyValue),
      plannedWorkers: Number(input.plannedWorkers ?? db.contracts[index].plannedWorkers)
    };
    await writeJson(db);
    return sendJson(res, 200, db.contracts[index]);
  }

  if (req.method === "DELETE" && url.pathname.startsWith("/api/contracts/")) {
    const contractId = url.pathname.split("/").pop();
    db.contracts = db.contracts.filter((x) => x.id !== contractId);
    await writeJson(db);
    return sendJson(res, 200, { ok: true });
  }

  if (req.method === "POST" && url.pathname === "/api/inspections") {
    const input = await body(req);
    const inspection = { id: id("i"), inspectedAt: new Date().toISOString(), ...input };
    db.inspections.push(inspection);
    await writeJson(db);
    return sendJson(res, 201, inspection);
  }

  if (req.method === "POST" && url.pathname === "/api/materials") {
    const input = await body(req);
    const openingStock = Number(input.openingStock ?? input.currentStock ?? 0);
    const material = {
      id: id("m"),
      unit: "قطعة",
      minStock: 0,
      unitCost: 0,
      ...input,
      openingStock,
      currentStock: Number(input.currentStock ?? openingStock),
      minStock: Number(input.minStock ?? 0),
      unitCost: Number(input.unitCost ?? 0)
    };
    db.materials.push(material);
    await writeJson(db);
    return sendJson(res, 201, material);
  }

  if (req.method === "POST" && url.pathname === "/api/material-transactions") {
    const input = await body(req);
    const tx = { id: id("t"), createdAt: new Date().toISOString(), ...input };
    const mat = db.materials.find((x) => x.id === tx.materialId);
    if (!mat) return sendJson(res, 404, { error: "material_not_found" });
    const quantity = Number(tx.quantity || 0);
    const balanceBefore = Number(mat.currentStock || 0);
    const unitCost = Number(tx.unitCost || mat.unitCost || 0);
    const siteBalanceBefore = getSiteMaterialBalance(db, tx.siteId, tx.materialId);
    if (tx.txType === "stock_in") mat.currentStock = balanceBefore + quantity;
    if (tx.txType === "site_out") mat.currentStock = balanceBefore - quantity;
    if (tx.txType === "site_use") mat.currentStock = balanceBefore;
    if (tx.txType === "stock_in" && unitCost > 0) mat.unitCost = unitCost;
    tx.issueNo = tx.issueNo || (tx.txType === "stock_in" ? `IN-${Date.now()}` : `ISS-${Date.now()}`);
    tx.quantity = quantity;
    tx.unitCost = unitCost;
    tx.totalCost = tx.txType === "site_use" ? quantity * unitCost : 0;
    tx.balanceBefore = balanceBefore;
    tx.balanceAfter = Number(mat.currentStock || 0);
    tx.siteBalanceBefore = tx.siteId ? siteBalanceBefore : null;
    tx.siteBalanceAfter = tx.siteId
      ? siteBalanceBefore + (tx.txType === "site_out" ? quantity : tx.txType === "site_use" ? -quantity : 0)
      : null;
    db.materialTransactions.push(tx);
    await writeJson(db);
    return sendJson(res, 201, tx);
  }

  if (req.method === "POST" && url.pathname === "/api/vehicle-movements") {
    const input = await body(req);
    const movement = {
      id: id("v"),
      createdAt: new Date().toISOString(),
      vehicleId: input.vehicleId || "",
      vehicleNo: input.vehicleNo || "",
      driverId: input.driverId || "",
      driverName: input.driverName || "",
      destination: input.destination || "",
      mission: input.mission || "",
      fuelLiters: Number(input.fuelLiters || 0),
      fuelCost: Number(input.fuelCost || 0),
      maintenance: input.maintenance || "",
      oilChanged: Boolean(input.oilChanged),
      oilKm: Number(input.oilKm || 0),
      currentKm: Number(input.currentKm || 0),
      status: input.status || "in_progress",
      notes: input.notes || ""
    };
    if (movement.vehicleId) {
      const vehicle = db.vehicles.find((x) => x.id === movement.vehicleId);
      if (vehicle) {
        vehicle.currentKm = movement.currentKm || vehicle.currentKm || 0;
        if (movement.oilChanged) vehicle.lastOilKm = movement.oilKm || movement.currentKm || vehicle.currentKm || 0;
      }
    }
    db.vehicleMovements.push(movement);
    await writeJson(db);
    return sendJson(res, 201, movement);
  }

  if (req.method === "PUT" && url.pathname.startsWith("/api/vehicle-movements/")) {
    const movementId = url.pathname.split("/").pop();
    const input = await body(req);
    const index = db.vehicleMovements.findIndex((x) => x.id === movementId);
    if (index === -1) return sendJson(res, 404, { error: "movement_not_found" });
    db.vehicleMovements[index] = { ...db.vehicleMovements[index], ...input };
    await writeJson(db);
    return sendJson(res, 200, db.vehicleMovements[index]);
  }

  if (req.method === "POST" && url.pathname === "/api/drivers") {
    const input = await body(req);
    const driver = { id: id("d"), name: input.name || "", phone: input.phone || "", active: true };
    db.drivers.push(driver);
    await writeJson(db);
    return sendJson(res, 201, driver);
  }

  if (req.method === "POST" && url.pathname === "/api/vehicles") {
    const input = await body(req);
    const vehicle = {
      id: id("car"),
      vehicleNo: input.vehicleNo || "",
      type: input.type || "",
      oilEveryKm: Number(input.oilEveryKm || 5000),
      currentKm: Number(input.currentKm || 0),
      lastOilKm: Number(input.lastOilKm || 0),
      active: true
    };
    db.vehicles.push(vehicle);
    await writeJson(db);
    return sendJson(res, 201, vehicle);
  }

  return sendJson(res, 404, { error: "not_found" });
}

function getSiteMaterialBalance(db, siteId, materialId) {
  if (!siteId || !materialId) return 0;
  return (db.materialTransactions || []).reduce((sum, tx) => {
    if (tx.siteId !== siteId || tx.materialId !== materialId) return sum;
    if (tx.txType === "site_out") return sum + Number(tx.quantity || 0);
    if (tx.txType === "site_use") return sum - Number(tx.quantity || 0);
    return sum;
  }, 0);
}

const server = http.createServer(async (req, res) => {
  try {
    const url = new URL(req.url, `http://${req.headers.host}`);
    if (url.pathname.startsWith("/api/")) return api(req, res, url);

    const safePath = url.pathname === "/" ? "/index.html" : decodeURIComponent(url.pathname);
    const filePath = path.normalize(path.join(publicDir, safePath));
    if (!filePath.startsWith(publicDir) || !existsSync(filePath)) {
      res.writeHead(404, { "content-type": "text/plain; charset=utf-8" });
      return res.end("Not found");
    }
    res.writeHead(200, { "content-type": contentType(filePath) });
    createReadStream(filePath).pipe(res);
  } catch (error) {
    sendJson(res, 500, { error: "server_error", message: error.message });
  }
});

server.listen(port, "127.0.0.1", () => {
  console.log(`I-BUILD Cleaning System: http://127.0.0.1:${port}`);
});
