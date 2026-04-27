// Full Panchang via SwissEph — computes 5 elements with end-times,
// sun/moon rise-set, and auspicious/inauspicious periods.
//
// Output JSON shape matches BB_Prokerala_API::panchang() so existing
// template-panchang.php works without any modification.
//
// Validated 2026-04-27 Delhi against drikpanchang:
//   Tithi: Shukla Ekadashi (drik: Ekadashi)         ✓
//   Nakshatra: Purva Phalguni                       ✓
//   Yoga: Dhruva                                    ✓
//   Karana: Vanija (early), Vishti (mid)            ✓

import { getSwe, FLAGS } from "./swe-init.js";
import { dayTimes } from "./sun-times.js";
import { localMidnightJD, jdToISOInTZ, jdToDate } from "./julian-iso.js";

/* --------------------------------------------------------------- name tables */

const TITHI_NAMES = ["Pratipada","Dwitiya","Tritiya","Chaturthi","Panchami","Shashthi","Saptami","Ashtami","Navami","Dashami","Ekadashi","Dwadashi","Trayodashi","Chaturdashi"]; // 14 names; 15th = Purnima/Amavasya

const NAKSHATRA_NAMES = ["Ashwini","Bharani","Krittika","Rohini","Mrigashira","Ardra","Punarvasu","Pushya","Ashlesha","Magha","Purva Phalguni","Uttara Phalguni","Hasta","Chitra","Swati","Vishakha","Anuradha","Jyeshtha","Mula","Purva Ashadha","Uttara Ashadha","Shravana","Dhanishta","Shatabhisha","Purva Bhadrapada","Uttara Bhadrapada","Revati"];

// Vimshottari Dasha lords by nakshatra (cycle of 9, repeats 3 times across 27)
const NAK_LORD_CYCLE = ["Ketu","Venus","Sun","Moon","Mars","Rahu","Jupiter","Saturn","Mercury"];

const YOGA_NAMES = ["Vishkambha","Priti","Ayushman","Saubhagya","Shobhana","Atiganda","Sukarma","Dhriti","Shoola","Ganda","Vriddhi","Dhruva","Vyaghata","Harshana","Vajra","Siddhi","Vyatipata","Variyana","Parigha","Shiva","Siddha","Sadhya","Shubha","Shukla","Brahma","Indra","Vaidhriti"];

// Karana 1-indexed; 1=Kimstughna (fixed first), 2-57=cycle of 7, 58=Shakuni, 59=Chatushpada, 60=Naga
const KARANA_NAMES = (() => {
  const c = ["Bava","Balava","Kaulava","Taitila","Garaja","Vanija","Vishti"];
  const a = ["", "Kimstughna"];
  for (let k = 2; k <= 57; k++) a[k] = c[(k - 2) % 7];
  a[58] = "Shakuni"; a[59] = "Chatushpada"; a[60] = "Naga";
  return a;
})();

const VAARA_NAMES = ["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"];

// Drik Ritu (apparent season by Sun's tropical longitude). Each = 60° span.
//   Vasanta (spring), Greeshma (summer), Varsha (rains), Sharad (autumn), Hemanta (pre-winter), Shishira (winter).
const DRIK_RITU = ["Vasanta","Greeshma","Varsha","Sharad","Hemanta","Shishira"]; // starts 0° Aries (Mar equinox)

// Vedic Ritu (sidereal — by Sun's sidereal longitude in tropical-equiv segments, but offset).
// Common convention: Vedic months Madhu/Madhava=Vasanta, Shukra/Shuchi=Greeshma, etc.
// We use Sun's *sidereal* longitude in 60° segments starting at sidereal 0° (= Mesha).
const VEDIC_RITU = ["Vasanta","Greeshma","Varsha","Sharad","Hemanta","Shishira"];

/* --------------------------------------------------------------- helpers */

function siderealLong(swe, jd, planet) {
  const r = swe.calc_ut(jd, planet, FLAGS.SIDEREAL | FLAGS.SWIEPH);
  return r[0];
}

function tropicalLong(swe, jd, planet) {
  const r = swe.calc_ut(jd, planet, FLAGS.SWIEPH);
  return r[0];
}

/** Find next time (after jdStart) when phase = (planetA - planetB + 360) % 360
 *  crosses the next multiple of stepDeg. Returns JD, or null if not found in 4 days.
 *
 *  Phase always increases monotonically for Moon-Sun, Moon, Moon+Sun, so we
 *  simply walk hourly until we pass the target, then bisect.
 */
function findNextPhaseBoundary(swe, jdStart, planetA, planetB, stepDeg) {
  const phaseFn = (jd) => {
    const a = siderealLong(swe, jd, planetA);
    const b = planetB === null ? 0 : siderealLong(swe, jd, planetB);
    const sign = planetB === null || planetA === planetB ? 1 : 1;
    // for "yoga" we want sum, signaled by passing planetB as -1 sentinel? — see callers.
    return ((a - b) + 720) % 360;
  };
  const startPhase = phaseFn(jdStart);
  const targetPhase = (Math.floor(startPhase / stepDeg) + 1) * stepDeg; // may be 360
  const targetMod   = targetPhase >= 360 ? 0 : targetPhase;

  // Walk hourly; track unwrapped phase (continuously increasing)
  let prevJd = jdStart, prevUnwrapped = startPhase, prevRaw = startPhase;
  for (let i = 1; i <= 96; i++) {
    const jd = jdStart + i / 24;
    const raw = phaseFn(jd);
    let unwrapped = raw + (prevUnwrapped - prevRaw);
    if (unwrapped < prevUnwrapped - 0.5) unwrapped += 360; // wrap
    const targetUnwrapped = targetPhase >= 360
        ? prevUnwrapped + ( (360 - prevRaw) % 360 )
        : (targetPhase >= startPhase ? targetPhase
                                     : targetPhase + 360 * Math.floor(prevUnwrapped / 360 + 1));
    if (unwrapped >= targetUnwrapped) {
      // Bisect [prevJd, jd]
      let a = prevJd, b = jd;
      for (let it = 0; it < 50 && b - a > 1e-7; it++) {
        const m = (a + b) / 2;
        const r = phaseFn(m);
        let u = r;
        if (r < prevRaw - 0.5) u += 360;
        u += Math.floor((prevUnwrapped - prevRaw) / 360) * 360;
        if (u < targetUnwrapped) a = m; else b = m;
      }
      return (a + b) / 2;
    }
    prevJd = jd; prevUnwrapped = unwrapped; prevRaw = raw;
  }
  return null;
}

// Cleaner specialized boundary finders for each element. Use raw phase; phase
// always advances forward, so we bisect for first time phase ≥ target after jdStart.

function findEndJD(jdStart, currentPhase, stepDeg, phaseFn) {
  const targetRaw = (Math.floor(currentPhase / stepDeg) + 1) * stepDeg; // may be exactly stepDeg*30=360 etc.
  const targetMod = targetRaw % 360;
  // distance to advance, in degrees:
  const targetDelta = stepDeg - (currentPhase % stepDeg) || stepDeg;
  // hourly scan up to 96h
  let prevJd = jdStart, prevRaw = currentPhase, accumulated = 0;
  for (let i = 1; i <= 96; i++) {
    const jd = jdStart + i / 24;
    const raw = phaseFn(jd);
    let step = raw - prevRaw;
    if (step < -1) step += 360; // wrap detected
    accumulated += step;
    if (accumulated >= targetDelta) {
      // Bisect this hour
      let a = prevJd, b = jd, accA = accumulated - step;
      for (let it = 0; it < 50 && b - a > 1e-7; it++) {
        const m = (a + b) / 2;
        const rm = phaseFn(m);
        let stepM = rm - prevRaw;
        if (stepM < -1) stepM += 360;
        const accM = accA + stepM;
        if (accM < targetDelta) a = m; else b = m;
      }
      return (a + b) / 2;
    }
    prevJd = jd; prevRaw = raw;
  }
  return null;
}

/* --------------------------------------------------------------- panchang elements at jd */

function tithiAt(swe, jd) {
  const sun  = siderealLong(swe, jd, swe.SE_SUN);
  const moon = siderealLong(swe, jd, swe.SE_MOON);
  const phase = ((moon - sun) + 720) % 360;
  const tithiNum = Math.floor(phase / 12) + 1; // 1-30
  return { num: tithiNum, phase, sun, moon };
}

function nakshatraAt(swe, jd) {
  const moon = siderealLong(swe, jd, swe.SE_MOON);
  const num = Math.floor(moon / (360 / 27)) + 1; // 1-27
  return { num, moon };
}

function yogaAt(swe, jd) {
  const sun  = siderealLong(swe, jd, swe.SE_SUN);
  const moon = siderealLong(swe, jd, swe.SE_MOON);
  const phase = ((moon + sun) + 720) % 360;
  const num = Math.floor(phase / (360 / 27)) + 1; // 1-27
  return { num, phase };
}

function karanaAt(swe, jd) {
  const sun  = siderealLong(swe, jd, swe.SE_SUN);
  const moon = siderealLong(swe, jd, swe.SE_MOON);
  const phase = ((moon - sun) + 720) % 360;
  const num = Math.floor(phase / 6) + 1; // 1-60
  return { num, phase };
}

/* --------------------------------------------------------------- periods (auspicious / inauspicious) */

// Each period is the Nth eighth of the day (sunrise→sunset divided in 8).
// Index 1 = first eighth = (sunrise to sunrise + dayDur/8).
const RAHU_KAAL  = [8, 2, 7, 5, 6, 4, 3]; // by JS getDay() Sun..Sat
const YAMAGANDA  = [5, 4, 3, 2, 1, 7, 6];
const GULIKA     = [7, 6, 5, 4, 3, 2, 1];

function periodFor(sunriseJD, dayDur, eighthIdx, name, tz) {
  const eighth = dayDur / 8;
  const start = sunriseJD + (eighthIdx - 1) * eighth;
  const end   = sunriseJD +  eighthIdx      * eighth;
  return { name, start: jdToISOInTZ(start, tz), end: jdToISOInTZ(end, tz) };
}

/* --------------------------------------------------------------- main entry */

export async function computePanchang({ date, lat, lng, tz }) {
  const swe = await getSwe();
  const jdMid = localMidnightJD(date, tz);
  const t = await dayTimes(jdMid, lat, lng);
  if (!t.sunrise || !t.sunset) {
    throw new Error(`Sunrise/sunset compute failed for date=${date}`);
  }

  // Evaluate panchang elements AT SUNRISE — drik convention.
  const jdRef = t.sunrise;

  // Tithi
  const ti = tithiAt(swe, jdRef);
  const tithiEnd = findEndJD(jdRef, ti.phase, 12,
    (jd) => ((siderealLong(swe, jd, swe.SE_MOON) - siderealLong(swe, jd, swe.SE_SUN)) + 720) % 360);
  const paksha = ti.num <= 15 ? "Shukla" : "Krishna";
  const idxIn = ti.num <= 15 ? ti.num : ti.num - 15;
  const tithiName = idxIn === 15 ? (paksha === "Shukla" ? "Purnima" : "Amavasya") : TITHI_NAMES[idxIn - 1];

  // Nakshatra
  const nk = nakshatraAt(swe, jdRef);
  const nakEnd = findEndJD(jdRef, nk.moon, 360 / 27,
    (jd) => siderealLong(swe, jd, swe.SE_MOON));
  const nakLord = NAK_LORD_CYCLE[(nk.num - 1) % 9];

  // Yoga
  const yg = yogaAt(swe, jdRef);
  const yogaEnd = findEndJD(jdRef, yg.phase, 360 / 27,
    (jd) => ((siderealLong(swe, jd, swe.SE_MOON) + siderealLong(swe, jd, swe.SE_SUN)) + 720) % 360);

  // Karana (6° step on Moon-Sun)
  const kr = karanaAt(swe, jdRef);
  const karanaEnd = findEndJD(jdRef, kr.phase, 6,
    (jd) => ((siderealLong(swe, jd, swe.SE_MOON) - siderealLong(swe, jd, swe.SE_SUN)) + 720) % 360);
  const karanaName = KARANA_NAMES[kr.num] ?? "—";

  // Vaara
  const dow = (() => {
    const wd = new Intl.DateTimeFormat("en-US", { timeZone: tz, weekday: "short" }).format(jdToDate(jdRef));
    return ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"].indexOf(wd);
  })();

  // Periods
  const dayDur   = t.sunset - t.sunrise;
  const nightDur = (t.nextSunrise || t.sunset + 0.5) - t.sunset;

  // Abhijit Muhurat: 1/15 of day centered on solar noon (≈ 8th of 15 muhurts)
  const noon = (t.sunrise + t.sunset) / 2;
  const muhurtDay = dayDur / 15;
  const abhijit = {
    name: "Abhijit Muhurat",
    start: jdToISOInTZ(noon - muhurtDay / 2, tz),
    end:   jdToISOInTZ(noon + muhurtDay / 2, tz),
  };

  // Brahma Muhurat: 96 min before sunrise to 48 min before sunrise
  const brahma = {
    name: "Brahma Muhurat",
    start: jdToISOInTZ(t.sunrise - 96/1440, tz),
    end:   jdToISOInTZ(t.sunrise - 48/1440, tz),
  };

  const auspicious = [brahma, abhijit];

  const inauspicious = [
    periodFor(t.sunrise, dayDur, RAHU_KAAL[dow],  "Rahu Kaal",  tz),
    periodFor(t.sunrise, dayDur, YAMAGANDA[dow],  "Yamaganda",  tz),
    periodFor(t.sunrise, dayDur, GULIKA[dow],     "Gulika",     tz),
  ];

  // Ritu (drik = tropical sun in 6 segments of 60°; vedic = sidereal sun in same)
  const sunTrop = tropicalLong(swe, jdRef, swe.SE_SUN);
  const sunSid  = siderealLong(swe, jdRef, swe.SE_SUN);
  const drikRitu  = DRIK_RITU[Math.floor(sunTrop / 60) % 6];
  const vedicRitu = VEDIC_RITU[Math.floor(sunSid  / 60) % 6];

  return {
    tithi:      [{ name: tithiName, paksha, end: jdToISOInTZ(tithiEnd, tz) }],
    nakshatra:  [{ name: NAKSHATRA_NAMES[nk.num - 1], lord: { name: nakLord }, end: jdToISOInTZ(nakEnd, tz) }],
    yoga:       [{ name: YOGA_NAMES[yg.num - 1], end: jdToISOInTZ(yogaEnd, tz) }],
    karana:     [{ name: karanaName, end: jdToISOInTZ(karanaEnd, tz) }],
    vaara:      VAARA_NAMES[dow],

    sunrise:  jdToISOInTZ(t.sunrise,  tz),
    sunset:   jdToISOInTZ(t.sunset,   tz),
    moonrise: t.moonrise ? jdToISOInTZ(t.moonrise, tz) : null,
    moonset:  t.moonset  ? jdToISOInTZ(t.moonset,  tz) : null,

    auspicious_period:   auspicious,
    inauspicious_period: inauspicious,

    ritu: { drik: drikRitu, vedic: vedicRitu },
  };
}

// Wrapper for the legacy /astrology/ritu endpoint (template uses keys drik_ritu/vedic_ritu).
export async function computeRitu({ date, lat, lng, tz }) {
  const p = await computePanchang({ date, lat, lng, tz });
  return {
    drik_ritu:  { name: p.ritu.drik },
    vedic_ritu: { name: p.ritu.vedic },
  };
}
