// Julian Day <-> ISO 8601 with timezone offset (matches ProKerala's format).
// Uses Intl.DateTimeFormat to compute correct TZ offset incl. DST transitions.

const JD_UNIX_EPOCH = 2440587.5; // JD of 1970-01-01 00:00 UTC

export function jdToDate(jd) {
  return new Date((jd - JD_UNIX_EPOCH) * 86400 * 1000);
}

export function dateToJd(date) {
  return date.getTime() / (86400 * 1000) + JD_UNIX_EPOCH;
}

/** Returns the offset in minutes from UTC for the given Date in the given IANA zone. */
export function tzOffsetMinutes(date, tz) {
  const parts = new Intl.DateTimeFormat("en-US", {
    timeZone: tz,
    year: "numeric", month: "2-digit", day: "2-digit",
    hour: "2-digit", minute: "2-digit", second: "2-digit",
    hourCycle: "h23",
  }).formatToParts(date);
  const get = (t) => parseInt(parts.find(p => p.type === t).value, 10);
  const wallUtcMs = Date.UTC(get("year"), get("month") - 1, get("day"),
                              get("hour"), get("minute"), get("second"));
  return Math.round((wallUtcMs - date.getTime()) / 60000);
}

function pad(n, w = 2) { return String(Math.abs(n)).padStart(w, "0"); }

/** Format a Date as ISO 8601 in the given IANA zone, e.g. "2026-04-27T05:44:10+05:30". */
export function dateToISOInTZ(date, tz) {
  const off = tzOffsetMinutes(date, tz);
  const local = new Date(date.getTime() + off * 60000);
  const sign = off >= 0 ? "+" : "-";
  const offH = Math.floor(Math.abs(off) / 60);
  const offM = Math.abs(off) % 60;
  // Use UTC getters because we shifted local into UTC frame
  return `${local.getUTCFullYear()}-${pad(local.getUTCMonth() + 1)}-${pad(local.getUTCDate())}T`
       + `${pad(local.getUTCHours())}:${pad(local.getUTCMinutes())}:${pad(local.getUTCSeconds())}`
       + `${sign}${pad(offH)}:${pad(offM)}`;
}

/** Convenience: JD (UT) → ISO 8601 in zone */
export function jdToISOInTZ(jd, tz) {
  return dateToISOInTZ(jdToDate(jd), tz);
}

/** Convenience: parse ISO date string (or yyyy-mm-dd) and return JD at midnight in given tz */
export function localMidnightJD(dateStr, tz) {
  // dateStr expected "YYYY-MM-DD"
  const [y, m, d] = dateStr.split("-").map(Number);
  // Construct UTC date for midnight, then shift by tz offset
  const guess = new Date(Date.UTC(y, m - 1, d, 0, 0, 0));
  const off = tzOffsetMinutes(guess, tz);
  // Local midnight in tz = UTC midnight - offset
  const utcMs = guess.getTime() - off * 60000;
  return dateToJd(new Date(utcMs));
}
