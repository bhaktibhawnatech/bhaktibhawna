# Bhakti Bhawna

WordPress + Node platform powering [bhaktibhawna.com](https://bhaktibhawna.com) — devotional content (aarti / chalisa / puja vidhi) and accurate astrology tools (panchang / choghadiya / hora, with kundli / milan planned), in 4 languages: English, Hindi, Marathi, Gujarati.

**License:** [GNU Affero General Public License v3.0](LICENSE) — see `LICENSE` and `NOTICE`.
This software runs Swiss Ephemeris (Astrodienst, AGPL-3.0). Operating it as a network service triggers AGPL Section 13: the complete corresponding source code of the running version is published in this repository.

## Stack

- **WordPress** 6.8.5 + custom child theme of `hello-elementor` (Elementor itself is removed)
- **PHP** 8.3 (Hostinger CloudLinux)
- **Polylang** (free) for 4-language content; URL-based language detection
- **Node 22** as a long-running daemon for astro calculations
- **Swiss Ephemeris** via [`swisseph-wasm`](https://github.com/prolaxu/swisseph-wasm) — drik-grade precision
- **Google Gemini 2.5 Flash** (free tier) for optional AI-personalised interpretations (not yet wired)

## Architecture

```
┌─────────────────────┐  wp_remote_post  ┌──────────────────────────┐
│  WordPress / PHP    │ ───────────────► │  Node daemon             │
│  BB_Astro class     │                  │  127.0.0.1:8917          │
│  24h transient cache│                  │  swisseph-wasm           │
└─────────────────────┘ ◄─────────────── │  WASM loaded once at boot│
                          JSON           │  RSS ~80 MB              │
                                         └──────────────────────────┘
```

The Node daemon owns one persistent SwissEph WASM instance. PHP makes localhost HTTP calls — no per-request process spawning (which OOMs on shared hosting LVE caps).

## Repo layout

```
.
├── LICENSE                       # AGPL-3.0 full text
├── NOTICE                        # third-party attributions
├── README.md
├── theme/                        # WordPress child theme — drop into wp-content/themes/bhaktibhawna/
│   ├── functions.php
│   ├── header.php · footer.php · front-page.php
│   ├── single.php · archive.php · page.php · 404.php · search.php · index.php
│   ├── template-panchang.php · template-choghadiya.php · template-hora.php
│   ├── template-parts/
│   ├── assets/{css,js,img}/
│   └── tools/
│       ├── class-bb-astro.php       # ← daemon HTTP client + transient cache
│       ├── class-prokerala-api.php  # legacy fallback (kept until ProKerala creds removed)
│       └── helpers.php              # samvat / tithi / city list / etc.
├── scripts/
│   ├── jsastro/                  # Node astro daemon
│   │   ├── astro-server.js          # HTTP daemon entry — routes /hora /choghadiya /panchang /ritu
│   │   ├── start-server.sh          # idempotent launcher (health-check → relaunch via nohup setsid)
│   │   ├── package.json
│   │   ├── lib/
│   │   │   ├── swe-init.js          # singleton SwissEph WASM (Lahiri sidereal)
│   │   │   ├── sun-times.js         # iterative sunrise/sunset/moon-rise/moon-set
│   │   │   ├── julian-iso.js        # JD ↔ ISO 8601 with TZ offset
│   │   │   ├── hora.js              # planetary-hour computation
│   │   │   ├── choghadiya.js        # 16-muhurat computation
│   │   │   └── panchang.js          # 5-element panchang + auspicious/inauspicious + ritu
│   │   └── hora.js                  # CLI wrapper around lib/hora.js (for ad-hoc testing)
│   ├── bb-create-cgh.php            # WP-CLI: creates 4-lang choghadiya pages
│   ├── bb-create-hora.php           # WP-CLI: creates 4-lang hora pages
│   ├── bb-create-mr-gu-home.php     # WP-CLI: MR/GU home page translations
│   ├── bb-strip-elementor.php       # WP-CLI: removes _elementor_* postmeta
│   └── optimize_logo.py             # PNG → transparent WebP/PNG resizer
└── docs/                         # plans, decisions, audits
```

## Drik-grade accuracy

Validated against drikpanchang.com for 2026-04-27 New Delhi:

| Field | Output | drikpanchang | Δ |
|---|---|---|---|
| Sunrise | 05:44:10 | 05:44 | 10 sec |
| Sunset  | 18:53:52 | 18:54 | 8 sec |
| Tithi end | 18:16 | 18:15 | 1 min |
| Nakshatra end | 21:18 | 21:18 | exact |
| Yoga end | 21:35 | 21:36 | 1 min |
| Karana end | 06:08 | 06:07 | 1 min |

Sunrise uses an iterative root-find on the Sun's apparent altitude (target -0.8333° = upper limb + standard refraction) — `swe_rise_trans` in the WASM build returns malloc'd garbage and is unused.

## Brand

- Saffron `#E55A14` · Sindoori `#C72A2A` · Maroon `#7A1A35` · Gold `#E0AC10` · Cream `#FBE5C5` · Vermillion `#C8102E`
- DM Sans (Latin) + Tiro Devanagari Hindi (hi / mr / gu)
- Container max 1440px

## Setup

### Prerequisites
- WordPress 6.8+ with Polylang
- PHP 8.1+ with `proc_open` and `curl` enabled
- Node 22+ available as a binary on the server
- Ability to run a long-lived process on `127.0.0.1` (most shared hosts allow this; Hostinger does)

### Theme deploy
```bash
rsync -az theme/ user@host:path/to/wp-content/themes/bhaktibhawna/
```

### Astro daemon deploy
```bash
rsync -az scripts/jsastro/ user@host:~/sweph/jsastro/
ssh user@host
cd ~/sweph/jsastro && /path/to/node/22/npm install
chmod +x start-server.sh
./start-server.sh                                     # initial bring-up
curl -s http://127.0.0.1:8917/health                  # verify
```

The `start-server.sh` script is idempotent — it health-checks first, only relaunches if the daemon is down. Schedule it as a cron heartbeat (every 5 min on Hostinger hPanel; `crontab` CLI is not available on shared):

```cron
*/5 * * * * /home/your-user/sweph/jsastro/start-server.sh > /dev/null 2>&1
```

### Configuration

Two paths are hard-coded for this deployment and must be changed for your own:

- `scripts/jsastro/lib/swe-init.js` → `EPHE_PATH`
- `scripts/jsastro/start-server.sh` → `NODE`, `APP_DIR`
- `theme/tools/class-bb-astro.php` → none (uses `127.0.0.1:8917`)

Secrets (DB password, Gemini key, ProKerala creds while still active) live in `wp-config.php`, which is **gitignored** — you must populate it on each deployment.

## Source disclosure (AGPL Section 13)

This site links to its source from the footer. If you fork or redeploy, you must do the same — provide a clearly-visible link to the running version's complete corresponding source.

If you don't want to publish your modifications, Astrodienst sells a commercial Swiss Ephemeris license that releases you from the AGPL requirements.

## Memory / project status

Day-to-day status, decisions, and validated learnings live in the developer's local memory store (not in this repo). The repo is a snapshot of code; the memory is the running narrative. If you need historical context, ask.
