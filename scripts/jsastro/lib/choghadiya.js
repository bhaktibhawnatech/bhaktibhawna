// Choghadiya muhurat sequence. Day = sunrise→sunset divided into 8 muhurats;
// night = sunset→next-sunrise divided into 8. Sequence cycles through 7 names
// (Udveg, Char, Labh, Amrit, Kaal, Shubh, Rog) starting at the day-lord's index.
// Night starts 5 positions ahead in the cycle.
//
// Output JSON shape matches BB_Prokerala_API::choghadiya() exactly.

import { dayTimes } from "./sun-times.js";
import { localMidnightJD, jdToISOInTZ, jdToDate } from "./julian-iso.js";

const CYCLE = ["Udveg", "Char", "Labh", "Amrit", "Kaal", "Shubh", "Rog"];

// Index in CYCLE for day-1st-muhurat by JS getDay() (0=Sun..6=Sat)
const DAY_START_IDX = [0, 3, 6, 2, 5, 1, 4]; // Sun=Udveg, Mon=Amrit, Tue=Rog, Wed=Labh, Thu=Shubh, Fri=Char, Sat=Kaal

function dowInTZ(date, tz) {
  const wd = new Intl.DateTimeFormat("en-US", { timeZone: tz, weekday: "short" }).format(date);
  return ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"].indexOf(wd);
}

export async function computeChoghadiya({ date, lat, lng, tz }) {
  const jdMid = localMidnightJD(date, tz);
  const t = await dayTimes(jdMid, lat, lng);
  if (!t.sunrise || !t.sunset || !t.nextSunrise) {
    throw new Error(`Sunrise/sunset compute failed for date=${date}`);
  }

  const dayDur   = t.sunset - t.sunrise;
  const nightDur = t.nextSunrise - t.sunset;
  const dayMuh   = dayDur / 8;
  const nightMuh = nightDur / 8;

  const dow = dowInTZ(jdToDate(t.sunrise), tz);
  const dayStartIdx   = DAY_START_IDX[dow];
  const nightStartIdx = (dayStartIdx + 5) % 7;

  const muhurat = [];
  for (let i = 0; i < 8; i++) {
    muhurat.push({
      start:  jdToISOInTZ(t.sunrise + i * dayMuh,       tz),
      end:    jdToISOInTZ(t.sunrise + (i + 1) * dayMuh, tz),
      is_day: 1,
      name:   CYCLE[(dayStartIdx + i) % 7],
    });
  }
  for (let i = 0; i < 8; i++) {
    muhurat.push({
      start:  jdToISOInTZ(t.sunset + i * nightMuh,       tz),
      end:    jdToISOInTZ(t.sunset + (i + 1) * nightMuh, tz),
      is_day: 0,
      name:   CYCLE[(nightStartIdx + i) % 7],
    });
  }

  return {
    muhurat,
    sunrise: jdToISOInTZ(t.sunrise, tz),
    sunset:  jdToISOInTZ(t.sunset,  tz),
  };
}
