// Try the EXACT params from the bundled demo first, to confirm rise_trans works at all.
import SwissEph from "swisseph-wasm";
const swe = new SwissEph();
await swe.initSwissEph();

console.log("Test 1 — exact demo params: London, JD 2460218.5, RISE only");
const r1 = swe.rise_trans(2460218.5, 0, -0.1278, 51.5074, 0, 1);
console.log("  result:", r1, r1 ? `JD=${r1[0]}` : "null");

// Set ephe path AFTER init
swe.set_ephe_path("/home/u970630969/sweph/src/ephe");
console.log("\nTest 2 — same params, after set_ephe_path");
const r2 = swe.rise_trans(2460218.5, 0, -0.1278, 51.5074, 0, 1);
console.log("  result:", r2, r2 ? `JD=${r2[0]}` : "null");

console.log("\nTest 3 — Delhi 2026-04-27, RISE | SWIEPH");
const r3 = swe.rise_trans(2461157.27, 0, 77.2090, 28.6139, 0, 1 | 2);
console.log("  result:", r3, r3 ? `JD=${r3[0]}` : "null");

console.log("\nTest 4 — Delhi 2026-04-27, RISE | MOSEPH");
const r4 = swe.rise_trans(2461157.27, 0, 77.2090, 28.6139, 0, 1 | 4);
console.log("  result:", r4, r4 ? `JD=${r4[0]}` : "null");

console.log("\nTest 5 — set_topo first, then rise_trans Delhi");
swe.set_topo(77.2090, 28.6139, 0);
const r5 = swe.rise_trans(2461157.27, 0, 77.2090, 28.6139, 0, 1);
console.log("  result:", r5, r5 ? `JD=${r5[0]}` : "null");

// Also: print all available swe.* methods to find any sun-times alternative
console.log("\nAvailable methods (filter 'rise|sun|trans'):");
console.log(Object.getOwnPropertyNames(Object.getPrototypeOf(swe)).filter(n => /rise|sun|trans|cross/i.test(n)));

swe.close();
