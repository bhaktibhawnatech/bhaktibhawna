// Two more attempts at swisseph sunrise:
//  (A) rise_trans_true_hor with realistic atmospheric pressure/temp
//  (B) iterative root-finding using Sun's altitude (works because calc_ut works)
import SwissEph from "swisseph-wasm";
import { fileURLToPath } from "url";
import { dirname, resolve } from "path";

const __dirname = dirname(fileURLToPath(import.meta.url));
const EPHE_PATH = process.env.SWEPH_EPHE_PATH || resolve(__dirname, "../src/ephe");

const swe = new SwissEph();
await swe.initSwissEph();
swe.set_ephe_path(EPHE_PATH);

const LAT = 28.6139, LNG = 77.2090, ALT_M = 216;  // Delhi elevation 216m
const jdStart = 2461157.27;  // 2026-04-26 18:30 UT = 2026-04-27 00:00 IST

const fmtIST = (jd) => {
  const ms = (jd - 2440587.5) * 86400 * 1000;
  return new Intl.DateTimeFormat("en-IN", {
    timeZone: "Asia/Kolkata",
    hour: "2-digit", minute: "2-digit", second: "2-digit", hourCycle: "h23",
  }).format(new Date(ms));
};

// (A) try rise_trans_true_hor with various flags
console.log("(A) rise_trans_true_hor attempts:");
for (const f of [1, 1|2, 1|4, 1|256, 1|512]) {
  const r = swe.rise_trans_true_hor(jdStart, swe.SE_SUN, LNG, LAT, ALT_M, f);
  console.log(`  flags=${f}:`, r ? `JD=${r[0]} → ${r[0] > 1 ? fmtIST(r[0]) : "garbage"}` : "null");
}

// (B) iterative: find JD where Sun's apparent altitude = -0.8333° (upper limb + refraction)
// Sun altitude formula:
//   alt = arcsin( sin(lat)·sin(dec) + cos(lat)·cos(dec)·cos(H) )
// where H = LST - RA (local hour angle)
//   LST = sidtime(jd_ut) [in hours] · 15  + observer_lng [deg]
//   RA, dec from calc_ut with SEFLG_EQUATORIAL
console.log("\n(B) Iterative Sun altitude method:");

const SEFLG_EQUATORIAL = 2048;
const RAD = Math.PI / 180;
const TARGET_ALT = -0.8333; // upper limb + standard refraction

function sunAltitude(jd) {
  const eq = swe.calc_ut(jd, swe.SE_SUN, SEFLG_EQUATORIAL | swe.SEFLG_SWIEPH);
  const ra = eq[0];   // degrees
  const dec = eq[1];  // degrees
  const gst = swe.sidtime(jd) * 15; // gst in degrees
  const lst = gst + LNG;
  let H = (lst - ra) * RAD;
  return Math.asin(Math.sin(LAT*RAD)*Math.sin(dec*RAD) + Math.cos(LAT*RAD)*Math.cos(dec*RAD)*Math.cos(H)) / RAD;
}

// Search 04:00 IST (-1.5h UT) to 08:00 IST (+2.5h UT) for sunrise crossing
// 04:00 IST = JD jdStart + 4/24
function findRise(jd0, jd1) {
  // Bisection
  let a = jd0, b = jd1;
  for (let i = 0; i < 60; i++) {
    const m = (a + b) / 2;
    const altA = sunAltitude(a) - TARGET_ALT;
    const altM = sunAltitude(m) - TARGET_ALT;
    if (Math.sign(altA) === Math.sign(altM)) a = m; else b = m;
    if (Math.abs(b - a) < 1e-7) break; // ~10 ms precision
  }
  return (a + b) / 2;
}

const jdRise = findRise(jdStart + 4/24, jdStart + 8/24);
const jdSet  = findRise(jdStart + 16/24, jdStart + 20/24);
console.log(`  sunrise   JD=${jdRise.toFixed(7)}  IST=${fmtIST(jdRise)}`);
console.log(`  sunset    JD=${jdSet.toFixed(7)}  IST=${fmtIST(jdSet)}`);
console.log(`  drik ref  sunrise=05:48 IST  sunset=18:54 IST`);

swe.close();
