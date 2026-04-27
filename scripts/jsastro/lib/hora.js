// Pure compute function for Hora — used by both CLI (hora.js) and HTTP daemon (astro-server.js).
// Returns the same JSON shape that BB_Prokerala_API::hora() returned, so existing
// templates work unchanged.

import { dayTimes } from "./sun-times.js";
import { localMidnightJD, jdToISOInTZ, jdToDate } from "./julian-iso.js";

const DAY_LORDS = ["Sun", "Moon", "Mars", "Mercury", "Jupiter", "Venus", "Saturn"];

const CHALDEAN_NEXT = {
  "Saturn":  "Jupiter",
  "Jupiter": "Mars",
  "Mars":    "Sun",
  "Sun":     "Venus",
  "Venus":   "Mercury",
  "Mercury": "Moon",
  "Moon":    "Saturn",
};

function getDayOfWeekInTZ(date, tz) {
  const wd = new Intl.DateTimeFormat("en-US", { timeZone: tz, weekday: "short" }).format(date);
  return ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"].indexOf(wd);
}

export async function computeHora({ date, lat, lng, tz }) {
  const jdMid = localMidnightJD(date, tz);
  const t = await dayTimes(jdMid, lat, lng);
  if (!t.sunrise || !t.sunset || !t.nextSunrise) {
    throw new Error(`Sunrise/sunset compute failed for date=${date} lat=${lat} lng=${lng}`);
  }

  const dayDur   = t.sunset - t.sunrise;
  const nightDur = t.nextSunrise - t.sunset;
  const dayHora   = dayDur / 12;
  const nightHora = nightDur / 12;

  const dow = getDayOfWeekInTZ(jdToDate(t.sunrise), tz);
  let curr = DAY_LORDS[dow];

  const horaTiming = [];
  for (let i = 0; i < 12; i++) {
    horaTiming.push({
      start:  jdToISOInTZ(t.sunrise + i * dayHora,       tz),
      end:    jdToISOInTZ(t.sunrise + (i + 1) * dayHora, tz),
      is_day: 1,
      hora:   { name: curr },
    });
    curr = CHALDEAN_NEXT[curr];
  }
  for (let i = 0; i < 12; i++) {
    horaTiming.push({
      start:  jdToISOInTZ(t.sunset + i * nightHora,       tz),
      end:    jdToISOInTZ(t.sunset + (i + 1) * nightHora, tz),
      is_day: 0,
      hora:   { name: curr },
    });
    curr = CHALDEAN_NEXT[curr];
  }

  return {
    hora_timing: horaTiming,
    sunrise: jdToISOInTZ(t.sunrise, tz),
    sunset:  jdToISOInTZ(t.sunset,  tz),
  };
}
