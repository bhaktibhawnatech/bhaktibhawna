// suncalc validation: Delhi 2026-04-27 sunrise expected ~05:48 IST, sunset ~18:54 IST
// Drikpanchang reference: sunrise 05:48 IST, sunset 18:54 IST.
import SunCalc from "suncalc";

const fmt = (d) => new Intl.DateTimeFormat("en-IN", {
  timeZone: "Asia/Kolkata",
  hour: "2-digit", minute: "2-digit", second: "2-digit", hourCycle: "h23",
}).format(d);

// Use noon local IST (06:30 UT) as reference — guarantees same calendar day in suncalc
const date = new Date(Date.UTC(2026, 3, 27, 6, 30));  // 2026-04-27 06:30 UT = 12:00 IST
const times = SunCalc.getTimes(date, 28.6139, 77.2090);
const moon  = SunCalc.getMoonTimes(date, 28.6139, 77.2090);

console.log("Delhi 2026-04-27 (drik ref: sunrise 05:48, sunset 18:54)\n");
console.log(`sunrise         ${fmt(times.sunrise)}`);
console.log(`solarNoon       ${fmt(times.solarNoon)}`);
console.log(`sunset          ${fmt(times.sunset)}`);
console.log(`dawn (civil)    ${fmt(times.dawn)}`);
console.log(`dusk (civil)    ${fmt(times.dusk)}`);
console.log(`moonrise        ${moon.rise ? fmt(moon.rise) : "—"}`);
console.log(`moonset         ${moon.set ? fmt(moon.set) : "—"}`);
