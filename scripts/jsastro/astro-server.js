// BB Astro daemon — single Node process, loads SwissEph WASM once at startup,
// listens on 127.0.0.1:8917, serves astro routes for the WordPress site.
//
// Lifecycle: started via start-server.sh (nohup setsid). PID at PID_FILE,
// stdout/stderr at LOG_FILE.
//
// PHP side: BB_Astro::run() calls http://127.0.0.1:8917/<route> via wp_remote_post.
//
// Routes:
//   GET  /health                — `{ ok: true, pid, uptime, version }`
//   POST /hora      body JSON   — calls computeHora(args)
//   (more routes added as tools are migrated)

import http from "http";
import { getSwe } from "./lib/swe-init.js";
import { computeHora } from "./lib/hora.js";
import { computeChoghadiya } from "./lib/choghadiya.js";
import { computePanchang, computeRitu } from "./lib/panchang.js";
import { computeChandraBala, computeTaraBala } from "./lib/balas.js";

const HOST = "127.0.0.1";
const PORT = parseInt(process.env.BB_ASTRO_PORT || "8917", 10);
const VERSION = "0.3.0";
const STARTED_AT = Date.now();

const ROUTES = {
  "/hora":         computeHora,
  "/choghadiya":   computeChoghadiya,
  "/panchang":     computePanchang,
  "/ritu":         computeRitu,
  "/chandra-bala": computeChandraBala,
  "/tara-bala":    computeTaraBala,
};

console.log(`[bb-astro] booting v${VERSION} pid=${process.pid}`);
await getSwe();
console.log(`[bb-astro] WASM ready, RSS=${(process.memoryUsage().rss / 1024 / 1024).toFixed(1)}MB`);

function readBody(req) {
  return new Promise((resolve, reject) => {
    let chunks = [];
    let bytes = 0;
    req.on("data", (c) => {
      bytes += c.length;
      if (bytes > 10240) { reject(new Error("body too large")); req.destroy(); }
      chunks.push(c);
    });
    req.on("end", () => resolve(Buffer.concat(chunks).toString("utf8")));
    req.on("error", reject);
  });
}

const server = http.createServer(async (req, res) => {
  const t0 = Date.now();
  try {
    if (req.method === "GET" && req.url === "/health") {
      res.writeHead(200, { "content-type": "application/json" });
      res.end(JSON.stringify({
        ok: true, pid: process.pid, version: VERSION,
        uptime_ms: Date.now() - STARTED_AT,
        rss_mb: +(process.memoryUsage().rss / 1024 / 1024).toFixed(1),
      }));
      return;
    }
    const handler = ROUTES[req.url];
    if (!handler || req.method !== "POST") {
      res.writeHead(404, { "content-type": "application/json" });
      res.end(JSON.stringify({ error: "not_found", route: req.url }));
      return;
    }
    const body = await readBody(req);
    let args;
    try { args = JSON.parse(body); }
    catch { res.writeHead(400); res.end(JSON.stringify({ error: "bad_json" })); return; }

    const result = await handler(args);
    res.writeHead(200, { "content-type": "application/json" });
    res.end(JSON.stringify(result));
    console.log(`[bb-astro] ${req.url} ok ${Date.now() - t0}ms`);
  } catch (e) {
    console.error(`[bb-astro] ${req.url} err ${Date.now() - t0}ms:`, e.stack || e.message);
    if (!res.headersSent) {
      res.writeHead(500, { "content-type": "application/json" });
      res.end(JSON.stringify({ error: "internal", message: String(e.message || e) }));
    }
  }
});

server.listen(PORT, HOST, () => {
  console.log(`[bb-astro] listening on http://${HOST}:${PORT}`);
});

// Graceful shutdown on SIGTERM/SIGINT (so cron-restart can land cleanly)
for (const sig of ["SIGTERM", "SIGINT"]) {
  process.on(sig, () => {
    console.log(`[bb-astro] ${sig} received, closing`);
    server.close(() => process.exit(0));
    setTimeout(() => process.exit(1), 5000).unref();
  });
}
