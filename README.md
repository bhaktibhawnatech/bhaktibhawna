# Bhakti Bhawna

WordPress redesign + astrology tools platform for [bhaktibhawna.com](https://bhaktibhawna.com).

Devotional content (aarti / chalisa / puja vidhi) + accurate astro tools (panchang / choghadiya / hora / kundli / milan), in 4 languages (English, Hindi, Marathi, Gujarati).

## Stack

- **WordPress** 6.8.5 + custom theme (no Elementor, no page builder)
- **PHP 8.3** with `exec()` for Swiss Ephemeris binary calls
- **Polylang** (free) for 4-language content
- **Swiss Ephemeris** for astro calculations (replaces ProKerala)
- **Google Gemini Flash** (free tier) for optional AI personalised readings
- **Hostinger Mumbai** shared hosting + LiteSpeed Cache (post-migration)

## Repo layout

```
.
├── theme/                    # Custom WP theme — drop-in to wp-content/themes/bhaktibhawna/
│   ├── functions.php
│   ├── header.php / footer.php / front-page.php
│   ├── single.php / archive.php / page.php
│   ├── template-{panchang,choghadiya,hora}.php
│   ├── assets/{css,js,img}/
│   ├── template-parts/
│   └── tools/
│       ├── class-bb-astro.php   # Swiss Ephemeris wrapper (replaces ProKerala)
│       └── helpers.php
├── scripts/                  # One-off PHP scripts run via `wp eval-file`
│   ├── bb-create-cgh.php          # creates 4-lang choghadiya pages
│   ├── bb-create-hora.php         # creates 4-lang hora pages
│   ├── bb-create-mr-gu-home.php   # creates MR/GU home page translations
│   ├── bb-strip-elementor.php     # bulk-removes _elementor_* postmeta
│   └── optimize_logo.py           # PNG → transparent WebP+PNG resizer
└── docs/                     # Plans, decisions, audits
```

## Brand

- Saffron `#E55A14` · Sindoori `#C72A2A` · Maroon `#7A1A35` · Gold `#E0AC10` · Cream `#FBE5C5` · Vermillion accent `#C8102E`
- DM Sans (English) + Tiro Devanagari Hindi (hi/mr/gu)
- Container max 1440px

## Deploy to staging

```bash
rsync -az theme/ user@host:domains/bhaktibhawna.com/public_html/staging/wp-content/themes/bhaktibhawna/
```

## Notes

- ProKerala API was used Apr 24-27, hit 5K free tier limit; migrating to Swiss Ephemeris.
- Live site untouched. Migration plan in `docs/`.
