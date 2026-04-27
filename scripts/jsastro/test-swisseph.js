// Validate swisseph-wasm against known Lahiri reference values, then
// compute Sun + Moon sidereal longitudes for today (2026-04-27 12:00 IST = 06:30 UT)
// to confirm output matches drikpanchang/ProKerala class of accuracy.

import SwissEph from "swisseph-wasm";

const swe = new SwissEph();
await swe.initSwissEph();

// Use Swiss Ephemeris mode (most accurate). Files at ~/sweph/src/ephe.
swe.set_ephe_path("/home/u970630969/sweph/src/ephe");
swe.set_sid_mode(swe.SE_SIDM_LAHIRI, 0, 0);

const FLAGS  = swe.SEFLG_SWIEPH;
const SIDFLG = swe.SEFLG_SWIEPH | swe.SEFLG_SIDEREAL;

function dms(d) {
  const sign = d < 0 ? "-" : "";
  d = Math.abs(d);
  const deg = Math.floor(d);
  const m = (d - deg) * 60;
  const min = Math.floor(m);
  const sec = ((m - min) * 60).toFixed(3);
  return `${sign}${deg}°${String(min).padStart(2, "0")}'${sec.padStart(6, "0")}"`;
}

function rashi(deg) {
  const names = ["Mesha","Vrishabha","Mithuna","Karka","Simha","Kanya",
                 "Tula","Vrishchika","Dhanu","Makara","Kumbha","Meena"];
  const idx = Math.floor(deg / 30);
  const within = deg - idx * 30;
  return `${names[idx]} ${dms(within)}`;
}

console.log("=".repeat(64));
console.log("Lahiri ayanamsa — SwissEph WASM");
console.log("=".repeat(64));

const epochs = [
  { name: "1956-03-21 00:00 UT (SE Lahiri ref)", jd: 2435553.5 },
  { name: "2000-01-01 12:00 UT (J2000)",         jd: 2451545.0 },
  { name: "1900-01-00 12:00 UT (IAE 1900)",      jd: 2415020.0 },
  { name: "2026-04-27 06:30 UT (12:00 IST)",     jd: swe.julday(2026, 4, 27, 6.5) },
];

for (const e of epochs) {
  const ayan = swe.get_ayanamsa(e.jd);
  console.log(`\n${e.name}`);
  console.log(`  JD       ${e.jd}`);
  console.log(`  Lahiri   ${ayan.toFixed(6)}°   ${dms(ayan)}`);
}

console.log("\n" + "=".repeat(64));
console.log("Today (2026-04-27 12:00 IST) — Sun & Moon sidereal");
console.log("=".repeat(64));

const jdToday = swe.julday(2026, 4, 27, 6.5);
const sunSid  = swe.calc_ut(jdToday, swe.SE_SUN,  SIDFLG);
const moonSid = swe.calc_ut(jdToday, swe.SE_MOON, SIDFLG);
const sunTrop  = swe.calc_ut(jdToday, swe.SE_SUN,  FLAGS);
const moonTrop = swe.calc_ut(jdToday, swe.SE_MOON, FLAGS);
const ayanToday = swe.get_ayanamsa(jdToday);

console.log(`\nJD                ${jdToday}`);
console.log(`Lahiri ayanamsa   ${ayanToday.toFixed(6)}°  ${dms(ayanToday)}`);
console.log(`Sun  tropical     ${sunTrop[0].toFixed(6)}°  ${dms(sunTrop[0])}`);
console.log(`Sun  sidereal     ${sunSid[0].toFixed(6)}°  ${rashi(sunSid[0])}`);
console.log(`Moon tropical     ${moonTrop[0].toFixed(6)}°  ${dms(moonTrop[0])}`);
console.log(`Moon sidereal     ${moonSid[0].toFixed(6)}°  ${rashi(moonSid[0])}`);

// Tithi number = floor(((moon - sun + 360) % 360) / 12) + 1
// 1-15 = Shukla Paksha, 16-30 = Krishna Paksha
const diff = ((moonSid[0] - sunSid[0]) + 360) % 360;
const tithiNum = Math.floor(diff / 12) + 1;
const tithiNames = ["Pratipada","Dwitiya","Tritiya","Chaturthi","Panchami","Shashti","Saptami","Ashtami","Navami","Dashami","Ekadashi","Dwadashi","Trayodashi","Chaturdashi","Purnima/Amavasya"];
const paksha = tithiNum <= 15 ? "Shukla" : "Krishna";
const tithiInPaksha = tithiNum <= 15 ? tithiNum : tithiNum - 15;

// Nakshatra: 27 segments of 13°20' = 800' = 800/60 ° = 13.333°
const nakshatraNum = Math.floor(moonSid[0] / (360/27)) + 1;
const nakshatraNames = ["Ashwini","Bharani","Krittika","Rohini","Mrigashira","Ardra","Punarvasu","Pushya","Ashlesha","Magha","Purva Phalguni","Uttara Phalguni","Hasta","Chitra","Swati","Vishakha","Anuradha","Jyeshtha","Mula","Purva Ashadha","Uttara Ashadha","Shravana","Dhanishta","Shatabhisha","Purva Bhadrapada","Uttara Bhadrapada","Revati"];

console.log(`\nMoon-Sun (sidereal)  ${diff.toFixed(4)}°`);
console.log(`Tithi                ${tithiNum} → ${paksha} ${tithiNames[tithiInPaksha-1]}`);
console.log(`Nakshatra            ${nakshatraNum} → ${nakshatraNames[nakshatraNum-1]}`);

swe.close();
