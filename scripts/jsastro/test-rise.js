// Sanity-check: sunrise/sunset for Delhi 2026-04-27
// Expected (per drikpanchang Delhi): sunrise ~05:48 IST, sunset ~18:54 IST.

import SwissEph from "swisseph-wasm";

const swe = new SwissEph();
await swe.initSwissEph();
swe.set_ephe_path("/home/u970630969/sweph/src/ephe");

// Delhi
const LAT = 28.6139, LNG = 77.2090, ALT = 0;

// JD of 2026-04-27 00:00 UT (IST midnight = previous day 18:30 UT, but let's start from midnight UT)
// Better: start from local date midnight (00:00 IST = JD - 5.5/24)
const jdLocalMidnight = swe.julday(2026, 4, 26, 18.5); // 2026-04-26 18:30 UT = 2026-04-27 00:00 IST

const SE_CALC_RISE = 1;
const SE_CALC_SET  = 2;
const SEFLG_SWIEPH = 2;
const SE_BIT_DISC_CENTER = 256;
const SE_BIT_NO_REFRACTION = 512;

// Try a few flag combinations
for (const [name, flag] of [
  ["RISE | SWIEPH",                       SE_CALC_RISE | SEFLG_SWIEPH],
  ["RISE | SWIEPH | DISC_CENTER",         SE_CALC_RISE | SEFLG_SWIEPH | SE_BIT_DISC_CENTER],
  ["RISE | SWIEPH | DISC_CENTER | NO_REF",SE_CALC_RISE | SEFLG_SWIEPH | SE_BIT_DISC_CENTER | SE_BIT_NO_REFRACTION],
  ["RISE alone",                          SE_CALC_RISE],
]) {
  const r = swe.rise_trans(jdLocalMidnight, swe.SE_SUN, LNG, LAT, ALT, flag);
  if (!r) { console.log(`${name}: NULL`); continue; }
  const jdRise = r[0];
  const ms = (jdRise - 2440587.5) * 86400 * 1000;
  const d = new Date(ms);
  // Format in IST
  const ist = new Intl.DateTimeFormat("en-IN", {
    timeZone: "Asia/Kolkata", hour: "2-digit", minute: "2-digit", second: "2-digit", hourCycle: "h23",
  }).format(d);
  console.log(`${name}: JD=${jdRise.toFixed(6)}  IST=${ist}`);
}

// Sunset
console.log("\n--- Sunset ---");
const set = swe.rise_trans(jdLocalMidnight, swe.SE_SUN, LNG, LAT, ALT, SE_CALC_SET | SEFLG_SWIEPH);
if (set) {
  const ms = (set[0] - 2440587.5) * 86400 * 1000;
  const d = new Date(ms);
  console.log("sunset IST:", new Intl.DateTimeFormat("en-IN", {
    timeZone: "Asia/Kolkata", hour: "2-digit", minute: "2-digit", second: "2-digit", hourCycle: "h23",
  }).format(d));
}

swe.close();
