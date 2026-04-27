// Lahiri (Chitra Paksha) ayanamsa.
// Anchored at SwissEph SE_SIDM_LAHIRI reference: JD 2435553.5 (1956-03-21 00:00 UT)
// with value 23°15'00.658" = 23.2501827778°. Drift = IAU 2006 general precession
// in longitude (Capitaine et al. 2003, A&A 412, 567).

const nutation = require("astronomia/nutation");

const REF_JD    = 2435553.5;
const REF_VALUE = 23.2501827778;
const REF_T     = (REF_JD - 2451545.0) / 36525;

function precessionLongitudeArcsec(T) {
  return 5028.796195   * T
       + 1.1054348     * T * T
       + 0.00007964    * T * T * T
       - 0.000023857   * T * T * T * T
       - 0.0000000383  * T * T * T * T * T;
}

const REF_PA = precessionLongitudeArcsec(REF_T);

function meanLahiri(jd) {
  const T  = (jd - 2451545.0) / 36525;
  const pA = precessionLongitudeArcsec(T);
  return REF_VALUE + (pA - REF_PA) / 3600;
}

function trueLahiri(jd) {
  const T = (jd - 2451545.0) / 36525;
  const [dpsiRad] = nutation.nutation(T);
  return meanLahiri(jd) + dpsiRad * 180 / Math.PI;
}

function degToDms(d) {
  const sign = d < 0 ? "-" : "";
  d = Math.abs(d);
  const deg = Math.floor(d);
  const mFloat = (d - deg) * 60;
  const min = Math.floor(mFloat);
  const sec = ((mFloat - min) * 60).toFixed(3);
  return `${sign}${deg}°${String(min).padStart(2, "0")}'${sec.padStart(6, "0")}"`;
}

module.exports = { meanLahiri, trueLahiri, degToDms };
