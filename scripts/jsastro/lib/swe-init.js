// Shared SwissEph WASM init. Returns a singleton instance with Lahiri
// sidereal mode set and ephemeris path pointing to the cloned SE source on
// this server. Lahiri is locked per locked design decisions.

import SwissEph from "swisseph-wasm";
import { fileURLToPath } from "url";
import { dirname, resolve } from "path";

const __dirname = dirname(fileURLToPath(import.meta.url));
const EPHE_PATH = process.env.SWEPH_EPHE_PATH || resolve(__dirname, "../../src/ephe");

let _swe = null;

export async function getSwe() {
  if (_swe) return _swe;
  const swe = new SwissEph();
  await swe.initSwissEph();
  swe.set_ephe_path(EPHE_PATH);
  swe.set_sid_mode(swe.SE_SIDM_LAHIRI, 0, 0);
  _swe = swe;
  return swe;
}

export const FLAGS = {
  SWIEPH:     2,
  EQUATORIAL: 2048,
  SIDEREAL:   65536,
  TOPOCTR:    32768,
  SPEED:      256,
};
