// Saturn sidereal sign-ingress walker. Used by the Sade Sati timeline.
//
// Walks Saturn's sidereal longitude day-by-day across a configurable span and
// records every sign boundary crossing — direct or retrograde. Each ingress is
// bisected down to ~1-second precision so the JD is reliable for date display.
//
// Output shape designed to be small and stable: PHP composes Sade Sati phases
// (per natal Moon sign) by walking this list.
//
// Reference: standard Lahiri sidereal ingress definition. Saturn's mean motion
// ~0.034°/day means a day-step is sufficient to never miss a boundary
// crossing, even during retrograde stations.

import { getSwe, FLAGS } from "./swe-init.js";

const SE_SATURN = 6;

function jdFromIsoDate(isoDate) {
  // isoDate is "YYYY-MM-DD" interpreted as 00:00 UT.
  const [y, m, d] = isoDate.split("-").map(Number);
  const date = new Date(Date.UTC(y, m - 1, d));
  return 2440587.5 + date.getTime() / 86400000;
}

function jdToIsoUtc(jd) {
  const ms = (jd - 2440587.5) * 86400000;
  const date = new Date(ms);
  return {
    date_iso: date.toISOString().slice(0, 10),
    datetime_utc: date.toISOString().replace(".000Z", "Z"),
  };
}

function siderealLng(swe, jd) {
  // calc_ut returns [lng, lat, dist, lng_speed, lat_speed, dist_speed]
  const r = swe.calc_ut(jd, SE_SATURN, FLAGS.SIDEREAL | FLAGS.SWIEPH | FLAGS.SPEED);
  return { lng: r[0], speed: r[3] };
}

function signOf(lng) {
  return Math.floor(((lng % 360) + 360) % 360 / 30);
}

// Bisect between jdLo (sign=signLo) and jdHi (sign≠signLo) until time-window
// is < 1 second. Returns refined jd of the boundary crossing.
function bisectIngress(swe, jdLo, jdHi, signLo) {
  const oneSec = 1 / 86400;
  while (jdHi - jdLo > oneSec) {
    const mid = (jdLo + jdHi) / 2;
    const s = signOf(siderealLng(swe, mid).lng);
    if (s === signLo) jdLo = mid;
    else jdHi = mid;
  }
  return jdHi; // first jd where sign === to_sign
}

export async function computeSaturnTimeline(args) {
  const swe = await getSwe();
  const startJd = jdFromIsoDate(args.start || "1950-01-01");
  const endJd   = jdFromIsoDate(args.end   || "2080-01-01");
  if (endJd <= startJd) throw new Error("end must be after start");
  if (endJd - startJd > 200 * 365.25) throw new Error("span too large (>200y)");

  const ingresses = [];
  let prev = siderealLng(swe, startJd);
  let prevSign = signOf(prev.lng);

  for (let jd = startJd + 1; jd <= endJd; jd += 1) {
    const cur = siderealLng(swe, jd);
    const curSign = signOf(cur.lng);
    if (curSign !== prevSign) {
      const jdCross = bisectIngress(swe, jd - 1, jd, prevSign);
      const at = siderealLng(swe, jdCross);
      const iso = jdToIsoUtc(jdCross);
      ingresses.push({
        jd: +jdCross.toFixed(6),
        date_iso: iso.date_iso,
        datetime_utc: iso.datetime_utc,
        from_sign: prevSign,
        to_sign: signOf(at.lng),
        motion: at.speed >= 0 ? "direct" : "retrograde",
      });
      prevSign = signOf(at.lng);
    } else {
      prevSign = curSign;
    }
  }

  return {
    span: { start: args.start, end: args.end, days: Math.round(endJd - startJd) },
    count: ingresses.length,
    ingresses,
    version: "0.1.0",
  };
}
