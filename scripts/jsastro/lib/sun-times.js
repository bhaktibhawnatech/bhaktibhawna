// Sunrise / sunset / moonrise / moonset via iterative root-finding on
// apparent altitude (with refraction + semidiameter [+ parallax for Moon]).
//
// Validated 2026-04-27 Delhi against drikpanchang:
//   sunrise 05:44:10 vs drik 05:44 (10 sec)
//   sunset  18:53:52 vs drik 18:54 (8 sec)

import { getSwe, FLAGS } from "./swe-init.js";

const RAD = Math.PI / 180;
const SUN_TARGET_ALT  = -0.8333;   // upper limb + std refraction
const MOON_TARGET_ALT = +0.1167;   // -refraction -semidiam +parallax (geocentric)

function altitude(swe, jd, planet, lat, lng) {
  // Geocentric equatorial coords of the body
  const eq = swe.calc_ut(jd, planet, FLAGS.EQUATORIAL | FLAGS.SWIEPH);
  const ra = eq[0], dec = eq[1];
  const gstDeg = swe.sidtime(jd) * 15;
  let H = (gstDeg + lng - ra) * RAD;
  return Math.asin(
    Math.sin(lat * RAD) * Math.sin(dec * RAD) +
    Math.cos(lat * RAD) * Math.cos(dec * RAD) * Math.cos(H)
  ) / RAD;
}

/** Find JD where altitude crosses target between [jdA, jdB]. Bisection, ~10ms precision. */
function bisect(swe, planet, lat, lng, target, jdA, jdB) {
  let a = jdA, b = jdB;
  let altA = altitude(swe, a, planet, lat, lng) - target;
  let altB = altitude(swe, b, planet, lat, lng) - target;
  if (Math.sign(altA) === Math.sign(altB)) return null; // no crossing in window
  for (let i = 0; i < 60; i++) {
    const m = (a + b) / 2;
    const altM = altitude(swe, m, planet, lat, lng) - target;
    if (Math.sign(altA) === Math.sign(altM)) { a = m; altA = altM; }
    else                                     { b = m; altB = altM; }
    if (Math.abs(b - a) < 1e-7) break; // ~10 ms
  }
  return (a + b) / 2;
}

/** Scan day [jdLocalMidnight, jdLocalMidnight+1] in 30-min steps for sign-change. */
function scanRiseSet(swe, planet, lat, lng, target, jdStart) {
  const STEP = 0.5 / 24; // 30 minutes
  let prevJd = jdStart, prevAlt = altitude(swe, prevJd, planet, lat, lng) - target;
  let rise = null, set = null;
  for (let i = 1; i <= 48 && (!rise || !set); i++) {
    const jd = jdStart + i * STEP;
    const alt = altitude(swe, jd, planet, lat, lng) - target;
    if (Math.sign(prevAlt) !== Math.sign(alt)) {
      const cross = bisect(swe, planet, lat, lng, target, prevJd, jd);
      // rising if altitude went from negative to positive, else setting
      if (prevAlt < 0 && alt >= 0 && !rise) rise = cross;
      else if (prevAlt >= 0 && alt < 0 && !set) set = cross;
    }
    prevJd = jd; prevAlt = alt;
  }
  return { rise, set };
}

/** Sun rise/set for a 24h window starting at jdLocalMidnight (JD UT of local midnight). */
export async function sunRiseSet(jdLocalMidnight, lat, lng) {
  const swe = await getSwe();
  return scanRiseSet(swe, swe.SE_SUN, lat, lng, SUN_TARGET_ALT, jdLocalMidnight);
}

/** Moon rise/set; uses topocentric parallax built into MOON_TARGET_ALT for geocentric coords. */
export async function moonRiseSet(jdLocalMidnight, lat, lng) {
  const swe = await getSwe();
  return scanRiseSet(swe, swe.SE_MOON, lat, lng, MOON_TARGET_ALT, jdLocalMidnight);
}

/** Combined: returns { sunrise, sunset, nextSunrise, moonrise, moonset } as JD UT.
 *  nextSunrise is needed to compute night-hora duration. */
export async function dayTimes(jdLocalMidnight, lat, lng) {
  const today = await sunRiseSet(jdLocalMidnight, lat, lng);
  const tomorrow = await sunRiseSet(jdLocalMidnight + 1, lat, lng);
  const moon = await moonRiseSet(jdLocalMidnight, lat, lng);
  return {
    sunrise:     today.rise,
    sunset:      today.set,
    nextSunrise: tomorrow.rise,
    moonrise:    moon.rise,
    moonset:     moon.set,
  };
}
