// CLI wrapper around lib/hora.js — kept for ad-hoc testing.
// Daemon at astro-server.js is the production path.
import { computeHora } from "./lib/hora.js";

const arg = process.argv[2];
if (!arg) {
  console.error('Usage: node hora.js \'{"date":"YYYY-MM-DD","lat":..,"lng":..,"tz":"..."}\'');
  process.exit(2);
}
try {
  const result = await computeHora(JSON.parse(arg));
  process.stdout.write(JSON.stringify(result));
} catch (e) {
  console.error("hora.js error:", e.stack || e.message || e);
  process.exit(1);
}
