// Chandra Bala (Moon's strength, by Janma Rashi) and Tara Bala (Star strength,
// by Janma Nakshatra). Output shapes match what BB_Prokerala_API::chandra_bala()
// and ::tara_bala() returned, so template-panchang.php works unchanged.
//
// Reference: Brihat Parashara Hora Shastra; Drik convention.

import { getSwe, FLAGS } from "./swe-init.js";
import { dayTimes } from "./sun-times.js";
import { localMidnightJD, jdToISOInTZ } from "./julian-iso.js";

const RASHI_NAMES = ["Mesha","Vrishabha","Mithuna","Karka","Simha","Kanya",
                     "Tula","Vrishchika","Dhanu","Makara","Kumbha","Meena"];

const NAKSHATRA_NAMES = ["Ashwini","Bharani","Krittika","Rohini","Mrigashira","Ardra","Punarvasu","Pushya","Ashlesha","Magha","Purva Phalguni","Uttara Phalguni","Hasta","Chitra","Swati","Vishakha","Anuradha","Jyeshtha","Mula","Purva Ashadha","Uttara Ashadha","Shravana","Dhanishta","Shatabhisha","Purva Bhadrapada","Uttara Bhadrapada","Revati"];

// Tara names by relative position (1-9). Cycle of 9 repeats 3× over 27 nakshatras.
const TARA_NAMES = ["Janma","Sampat","Vipat","Kshema","Pratyak","Sadhaka","Vadha","Mitra","Param Mitra"];

// Tara classification: Good = auspicious for activities, Bad = avoid, Mid = self.
//   Janma:       Mid (own star — major activities discouraged but not strictly bad)
//   Sampat:      Good (wealth)
//   Vipat:       Bad  (danger)
//   Kshema:      Good (well-being)
//   Pratyak:     Bad  (obstacle)
//   Sadhaka:     Good (accomplishment)
//   Vadha:       Bad  (destruction)
//   Mitra:       Good (friend)
//   Param Mitra: Good (close friend)
const TARA_TYPES = ["Mid","Good","Bad","Good","Bad","Good","Bad","Good","Good"];

function siderealLong(swe, jd, planet) {
  return swe.calc_ut(jd, planet, FLAGS.SIDEREAL | FLAGS.SWIEPH)[0];
}

/** Find next JD where Moon's sidereal longitude reaches the next multiple of stepDeg. */
function findNextMoonBoundary(swe, jdStart, stepDeg) {
  const start = siderealLong(swe, jdStart, swe.SE_MOON);
  const targetDelta = stepDeg - (start % stepDeg) || stepDeg;
  let prevJd = jdStart, prevRaw = start, accumulated = 0;
  for (let i = 1; i <= 96; i++) { // up to 4 days
    const jd = jdStart + i / 24;
    const raw = siderealLong(swe, jd, swe.SE_MOON);
    let step = raw - prevRaw;
    if (step < -1) step += 360;
    accumulated += step;
    if (accumulated >= targetDelta) {
      let a = prevJd, b = jd, accA = accumulated - step;
      for (let it = 0; it < 50 && b - a > 1e-7; it++) {
        const m = (a + b) / 2;
        const rm = siderealLong(swe, m, swe.SE_MOON);
        let stepM = rm - prevRaw;
        if (stepM < -1) stepM += 360;
        if (accA + stepM < targetDelta) a = m; else b = m;
      }
      return (a + b) / 2;
    }
    prevJd = jd; prevRaw = raw;
  }
  return null;
}

/* ---------------------------------------------------------------- Chandra Bala
 * For each of 12 Janma Rashis, classify Moon's current position by relative house.
 * Standard rule: 1, 3, 6, 7, 10, 11 = favorable; 2, 5, 9 = neutral; 4, 8, 12 = bad.
 * We return ONE period covering "now until Moon exits current rashi", listing
 * the favorable Janma Rashis as chips — matches template-panchang.php's render.
 */
const CHANDRA_FAVORABLE_REL = new Set([1, 3, 6, 7, 10, 11]);

export async function computeChandraBala({ date, lat, lng, tz }) {
  const swe = await getSwe();
  const jdMid = localMidnightJD(date, tz);
  const t = await dayTimes(jdMid, lat, lng);
  const jdRef = t.sunrise || jdMid;

  const moonLong = siderealLong(swe, jdRef, swe.SE_MOON);
  const currentRashi = Math.floor(moonLong / 30) + 1; // 1-12

  const favorable = [];
  for (let janma = 1; janma <= 12; janma++) {
    const rel = ((currentRashi - janma + 12) % 12) + 1;
    if (CHANDRA_FAVORABLE_REL.has(rel)) {
      favorable.push({ name: RASHI_NAMES[janma - 1] });
    }
  }

  const endJD = findNextMoonBoundary(swe, jdRef, 30);

  return {
    chandra_bala: [
      {
        end:   endJD ? jdToISOInTZ(endJD, tz) : null,
        rasis: favorable,
      },
    ],
  };
}

/* -------------------------------------------------------------------- Tara Bala
 * For each of 27 Janma Nakshatras, compute relative position to current Moon
 * nakshatra → maps to one of 9 Taras. Group all 27 by their Tara, return 9 groups.
 */
export async function computeTaraBala({ date, lat, lng, tz }) {
  const swe = await getSwe();
  const jdMid = localMidnightJD(date, tz);
  const t = await dayTimes(jdMid, lat, lng);
  const jdRef = t.sunrise || jdMid;

  const moonLong = siderealLong(swe, jdRef, swe.SE_MOON);
  const currentNak = Math.floor(moonLong / (360 / 27)) + 1; // 1-27

  // Build 9 buckets
  const buckets = Array.from({ length: 9 }, () => []);
  for (let janma = 1; janma <= 27; janma++) {
    const rel = ((currentNak - janma + 27) % 27) + 1; // 1-27
    const taraIdx = ((rel - 1) % 9); // 0-8
    buckets[taraIdx].push({ name: NAKSHATRA_NAMES[janma - 1] });
  }

  const endJD = findNextMoonBoundary(swe, jdRef, 360 / 27);
  const endISO = endJD ? jdToISOInTZ(endJD, tz) : null;

  const tara_bala = [];
  for (let i = 0; i < 9; i++) {
    tara_bala.push({
      name:        TARA_NAMES[i],
      type:        TARA_TYPES[i],
      end:         endISO,
      nakshatras:  buckets[i],
    });
  }

  return { tara_bala };
}
