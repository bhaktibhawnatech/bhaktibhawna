const { meanLahiri, trueLahiri, degToDms } = require("./lahiri");
const julian = require("astronomia/julian");

// Reference values from SwissEph swetest:
//   $ swetest -bj2435553.5 -sid1 -ay
//   ayanamsha = 23°15' 0.6580"  (SE definition exact)
//   $ swetest -bj2451545 -sid1 -ay
//   ayanamsha = 23°51'11.0"  (J2000)
const cases = [
  { name: "1956-03-21 00:00 UT (Lahiri ref epoch)", jd: 2435553.5,        expectMean: 23.2501828 },
  { name: "2000-01-01 12:00 UT (J2000)",            jd: 2451545.0,        expectMean: 23.853056 },
  { name: "1900-01-00 12:00 UT (IAE 1900 anchor)",  jd: 2415020.0,        expectMean: 22.460492 },
  { name: "2026-04-27 06:30 UT (12:00 IST today)",  jd: julian.CalendarGregorianToJD(2026, 4, 27 + 6.5/24), expectMean: null },
];

console.log("Lahiri ayanamsa validation\n" + "=".repeat(60));
for (const c of cases) {
  const m = meanLahiri(c.jd);
  const t = trueLahiri(c.jd);
  console.log(`\n${c.name}`);
  console.log(`  JD            ${c.jd}`);
  console.log(`  mean Lahiri   ${m.toFixed(6)}°   ${degToDms(m)}`);
  console.log(`  true Lahiri   ${t.toFixed(6)}°   ${degToDms(t)}   (apparent, with nutation)`);
  if (c.expectMean !== null) {
    const delta = (m - c.expectMean) * 3600;
    console.log(`  expected mean ${c.expectMean.toFixed(6)}°   delta ${delta.toFixed(2)}\"`);
  }
}
