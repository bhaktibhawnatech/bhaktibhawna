<?php
/**
 * Homepage v2 — Bhakti Bhawna
 * Photography-forward, temple-devotional.
 * Pure PHP. No Elementor.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

get_header();

$lang = bb_current_lang();
$uploads_base = home_url( '/wp-content/uploads' );

/* ---- Hero carousel slides (hand-picked from uploads) ---- */
$hero_slides = array(
    array(
        'img'   => $uploads_base . '/2023/09/Krishna-Bhajan-Lyrics-bhaktibhawna.jpg',
        'eyebrow' => array( 'en' => '🕉 Krishna Bhakti', 'hi' => '🕉 कृष्ण भक्ति', 'mr' => '🕉 कृष्ण भक्ती' ),
    ),
    array(
        'img'   => $uploads_base . '/2023/09/hanuman-ji-bhajan-lyrics.jpg',
        'eyebrow' => array( 'en' => '🕉 Hanuman Bhakti', 'hi' => '🕉 हनुमान भक्ति', 'mr' => '🕉 हनुमान भक्ती' ),
    ),
    array(
        'img'   => $uploads_base . '/2023/10/shiv-ji-ki-aarti-lyrics.jpg',
        'eyebrow' => array( 'en' => '🕉 Shiv Bhakti', 'hi' => '🕉 शिव भक्ति', 'mr' => '🕉 शिव भक्ती' ),
    ),
    array(
        'img'   => $uploads_base . '/2023/09/ganesh-bhajan-lyrics.jpg',
        'eyebrow' => array( 'en' => '🕉 Ganesh Bhakti', 'hi' => '🕉 गणेश भक्ति', 'mr' => '🕉 गणेश भक्ती' ),
    ),
);

/* ---- Featured Aartis (real post-like cards with images) ---- */
$featured_aartis = array(
    array( 'img' => $uploads_base . '/2023/10/Shiv-Chalisa.jpg',                    'en' => 'Shiv Chalisa',      'hi' => 'शिव चालीसा',     'url' => home_url( '/shiv-chalisa/' ) ),
    array( 'img' => $uploads_base . '/2023/10/Hanuman-ji-.jpg',                     'en' => 'Hanuman Aarti',     'hi' => 'हनुमान आरती',     'url' => home_url( '/hanuman-aarti/' ) ),
    array( 'img' => $uploads_base . '/2023/10/Ganesha-ji-.jpg',                     'en' => 'Ganesh Aarti',      'hi' => 'गणेश आरती',       'url' => home_url( '/ganesh-aarti/' ) ),
    array( 'img' => $uploads_base . '/2023/10/laxmi-ji-ki-aarti-lyrics.jpg',        'en' => 'Laxmi Aarti',       'hi' => 'लक्ष्मी आरती',    'url' => home_url( '/laxmi-aarti/' ) ),
);

/* ---- Category tiles (photo bg) ---- */
$categories = array(
    array( 'slug' => 'aarti',   'img' => $uploads_base . '/2023/09/hanuman-ji-bhajan-lyrics.jpg',   'en' => 'Aarti',      'hi' => 'आरती' ),
    array( 'slug' => 'chalisa', 'img' => $uploads_base . '/2023/10/Shiv-Chalisa.jpg',               'en' => 'Chalisa',    'hi' => 'चालीसा' ),
    array( 'slug' => 'puja',    'img' => $uploads_base . '/2023/09/ganesh-bhajan-lyrics.jpg',       'en' => 'Puja Vidhi', 'hi' => 'पूजा विधि' ),
    array( 'slug' => 'astro',   'img' => $uploads_base . '/2025/01/krishnaya-vasudevaya-mantra.jpg','en' => 'Astro',      'hi' => 'ज्योतिष' ),
    array( 'slug' => 'vastu',   'img' => $uploads_base . '/2023/10/shiv-ji-ki-aarti-lyrics.jpg',    'en' => 'Vastu',      'hi' => 'वास्तु' ),
    array( 'slug' => 'yantra',  'img' => $uploads_base . '/2023/11/tulsi-aarti-lyrics.jpg',         'en' => 'Yantra',     'hi' => 'यंत्र' ),
);

/* ---- Mandala SVG markup (reused) ---- */
$mandala_svg = '<svg class="bb-mandala-divider__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2" aria-hidden="true"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="6"/><circle cx="12" cy="12" r="2" fill="currentColor"/><path d="M12 2 V5 M12 19 V22 M2 12 H5 M19 12 H22 M4.93 4.93 L7 7 M17 17 L19.07 19.07 M19.07 4.93 L17 7 M7 17 L4.93 19.07"/></svg>';
?>

<!-- ============================================================
     HERO B — Today widget (panchang summary, no carousel)
     CWV-friendly: text-first LCP, no hero image, min-height reserved
     ============================================================ -->
<?php
/* Default city for hero panchang = New Delhi. Real per-user location lives on /panchang/ tool itself. */
$hero_city = bb_popular_cities()['new-delhi'];
$hero_args = array(
    'lat' => $hero_city['lat'], 'lng' => $hero_city['lng'], 'tz' => $hero_city['tz'],
    'bb_lang' => 'en',  // English keys; we translate locally
);
$hero_panchang   = BB_Prokerala_API::panchang( $hero_args );
$hero_choghadiya = BB_Prokerala_API::choghadiya( $hero_args );

$hero_tithi     = $hero_panchang['tithi'][0]['name']     ?? '—';
$hero_nakshatra = $hero_panchang['nakshatra'][0]['name'] ?? '—';
$hero_yoga      = $hero_panchang['yoga'][0]['name']      ?? '—';
$hero_vaara     = $hero_panchang['vaara']                ?? '—';
$hero_sunrise   = $hero_panchang['sunrise'] ?? null;
$hero_sunset    = $hero_panchang['sunset']  ?? null;

/* Hindi/MR/GU translations for the 4 fact labels — keep hero language-pure */
$hero_labels = array(
    'today_panchang' => array( 'en' => 'Today\'s Panchang', 'hi' => 'आज का पंचांग', 'mr' => 'आजचा पंचांग', 'gu' => 'આજનો પંચાંગ' ),
    'tithi'    => array( 'en' => 'Tithi',     'hi' => 'तिथि',     'mr' => 'तिथी',     'gu' => 'તિથિ' ),
    'nakshatra'=> array( 'en' => 'Nakshatra', 'hi' => 'नक्षत्र',  'mr' => 'नक्षत्र',  'gu' => 'નક્ષત્ર' ),
    'yoga'     => array( 'en' => 'Yoga',      'hi' => 'योग',      'mr' => 'योग',      'gu' => 'યોગ' ),
    'vaara'    => array( 'en' => 'Vaara',     'hi' => 'वार',      'mr' => 'वार',      'gu' => 'વાર' ),
    'sunrise'  => array( 'en' => 'Sunrise',   'hi' => 'सूर्योदय', 'mr' => 'सूर्योदय', 'gu' => 'સૂર્યોદય' ),
    'sunset'   => array( 'en' => 'Sunset',    'hi' => 'सूर्यास्त', 'mr' => 'सूर्यास्त', 'gu' => 'સૂર્યાસ્ત' ),
    'now_label'=> array( 'en' => 'Now', 'hi' => 'अभी का मुहूर्त', 'mr' => 'सध्याचा मुहूर्त', 'gu' => 'વર્તમાન મુહૂર્ત' ),
    'cta_full' => array( 'en' => 'Full Panchang', 'hi' => 'पूरा पंचांग', 'mr' => 'पूर्ण पंचांग', 'gu' => 'સંપૂર્ણ પંચાંગ' ),
    'cta_cgh'  => array( 'en' => 'Choghadiya',    'hi' => 'चौघड़िया',     'mr' => 'चौघडिया',      'gu' => 'ચોઘડિયું' ),
    'cta_hora' => array( 'en' => 'Hora',          'hi' => 'होरा',         'mr' => 'होरा',         'gu' => 'હોરા' ),
    'aaj'      => array( 'en' => 'Auspicious',    'hi' => 'शुभ',          'mr' => 'शुभ',           'gu' => 'શુભ' ),
    'mid'      => array( 'en' => 'Neutral',       'hi' => 'सामान्य',      'mr' => 'सामान्य',       'gu' => 'સામાન્ય' ),
    'bad'      => array( 'en' => 'Inauspicious',  'hi' => 'अशुभ',         'mr' => 'अशुभ',          'gu' => 'અશુભ' ),
);
$hl = function( $k ) use ( $hero_labels, $lang ) { return $hero_labels[ $k ][ $lang ] ?? $hero_labels[ $k ]['en']; };

/* Find currently-active choghadiya muhurat */
$current_cgh = null;
$now_ts = time();
if ( $hero_choghadiya && ! empty( $hero_choghadiya['muhurat'] ) ) {
    foreach ( $hero_choghadiya['muhurat'] as $m ) {
        try {
            $s = ( new DateTime( $m['start'] ) )->getTimestamp();
            $e = ( new DateTime( $m['end'] ) )->getTimestamp();
            if ( $now_ts >= $s && $now_ts < $e ) { $current_cgh = $m; break; }
        } catch ( Exception $ex ) {}
    }
}

/* Drik quality + class for hero "Aaj ka Muhurat" pill */
$cgh_quality_map = array(
    'Amrut' => array( 'k' => 'aaj', 'class' => 'good',  'name_hi' => 'अमृत', 'name_mr' => 'अमृत', 'name_gu' => 'અમૃત' ),
    'Shubh' => array( 'k' => 'aaj', 'class' => 'good',  'name_hi' => 'शुभ',  'name_mr' => 'शुभ',  'name_gu' => 'શુભ' ),
    'Labh'  => array( 'k' => 'aaj', 'class' => 'good',  'name_hi' => 'लाभ',  'name_mr' => 'लाभ',  'name_gu' => 'લાભ' ),
    'Char'  => array( 'k' => 'mid', 'class' => 'mid',   'name_hi' => 'चर',   'name_mr' => 'चर',   'name_gu' => 'ચર' ),
    'Kaal'  => array( 'k' => 'bad', 'class' => 'bad',   'name_hi' => 'काल',  'name_mr' => 'काळ',  'name_gu' => 'કાળ' ),
    'Rog'   => array( 'k' => 'bad', 'class' => 'bad',   'name_hi' => 'रोग',  'name_mr' => 'रोग',  'name_gu' => 'રોગ' ),
    'Udveg' => array( 'k' => 'bad', 'class' => 'bad',   'name_hi' => 'उद्वेग', 'name_mr' => 'उद्वेग', 'name_gu' => 'ઉદ્વેગ' ),
);

$tz_render = new DateTimeZone( $hero_city['tz'] );
$nice_t = function( $iso ) use ( $tz_render ) {
    if ( ! $iso ) return '—';
    try { $dt = new DateTime( $iso ); $dt->setTimezone( $tz_render ); return $dt->format( 'g:i A' ); }
    catch ( Exception $e ) { return '—'; }
};

$lang_url = function( $en, $hi, $mr, $gu ) use ( $lang ) {
    $m = array( 'en' => $en, 'hi' => $hi, 'mr' => $mr, 'gu' => $gu );
    return $m[ $lang ] ?? $en;
};
$loc_label = $hero_city[ 'name_' . $lang ] ?? $hero_city['name_en'];
?>

<section class="bb-hero-today" aria-label="<?php echo esc_attr( $hl( 'today_panchang' ) ); ?>">
    <div class="bb-container">
        <div class="bb-hero-today__head">
            <span class="bb-hero-today__date">
                📅 <?php echo esc_html( date_i18n( 'l, j F Y' ) ); ?> · 📍 <?php echo esc_html( $loc_label ); ?>
            </span>
            <h1 class="bb-hero-today__title"><?php echo esc_html( $hl( 'today_panchang' ) ); ?></h1>
        </div>

        <div class="bb-hero-today__grid">
            <div class="bb-hero-today__fact">
                <span class="bb-hero-today__fact-label"><?php echo esc_html( $hl( 'tithi' ) ); ?></span>
                <strong class="bb-hero-today__fact-value"><?php echo esc_html( $hero_tithi ); ?></strong>
            </div>
            <div class="bb-hero-today__fact">
                <span class="bb-hero-today__fact-label"><?php echo esc_html( $hl( 'nakshatra' ) ); ?></span>
                <strong class="bb-hero-today__fact-value"><?php echo esc_html( $hero_nakshatra ); ?></strong>
            </div>
            <div class="bb-hero-today__fact">
                <span class="bb-hero-today__fact-label"><?php echo esc_html( $hl( 'yoga' ) ); ?></span>
                <strong class="bb-hero-today__fact-value"><?php echo esc_html( $hero_yoga ); ?></strong>
            </div>
            <div class="bb-hero-today__fact">
                <span class="bb-hero-today__fact-label"><?php echo esc_html( $hl( 'vaara' ) ); ?></span>
                <strong class="bb-hero-today__fact-value"><?php echo esc_html( $hero_vaara ); ?></strong>
            </div>
        </div>

        <div class="bb-hero-today__sun">
            <span>🌅 <?php echo esc_html( $hl( 'sunrise' ) ); ?>: <strong><?php echo esc_html( $nice_t( $hero_sunrise ) ); ?></strong></span>
            <span>🌇 <?php echo esc_html( $hl( 'sunset' ) ); ?>: <strong><?php echo esc_html( $nice_t( $hero_sunset ) ); ?></strong></span>
        </div>

        <?php if ( $current_cgh ) :
            $q = $cgh_quality_map[ $current_cgh['name'] ] ?? null;
            $cgh_name_local = $q ? ( $lang === 'en' ? $current_cgh['name'] : ( $q[ 'name_' . $lang ] ?? $current_cgh['name'] ) ) : $current_cgh['name'];
            $q_class = $q ? $q['class'] : 'mid';
            $q_label = $q ? $hl( $q['k'] ) : $hl( 'mid' );
        ?>
        <div class="bb-hero-today__now bb-hero-today__now--<?php echo esc_attr( $q_class ); ?>">
            <span class="bb-hero-today__now-label">✨ <?php echo esc_html( $hl( 'now_label' ) ); ?></span>
            <span class="bb-hero-today__now-name"><?php echo esc_html( $cgh_name_local ); ?></span>
            <span class="bb-hero-today__now-quality"><?php echo esc_html( $q_label ); ?></span>
            <span class="bb-hero-today__now-time">
                <?php echo esc_html( $nice_t( $current_cgh['start'] ) ); ?> — <?php echo esc_html( $nice_t( $current_cgh['end'] ) ); ?>
            </span>
        </div>
        <?php endif; ?>

        <div class="bb-hero-today__ctas">
            <a class="bb-btn bb-btn--gold" href="<?php echo esc_url( home_url( $lang_url( '/en/panchang/', '/hi/aaj-ka-panchang/', '/mr/aajcha-panchang/', '/gu/aaj-no-panchang/' ) ) ); ?>">
                📅 <?php echo esc_html( $hl( 'cta_full' ) ); ?>
            </a>
            <a class="bb-btn bb-btn--ghost" href="<?php echo esc_url( home_url( $lang_url( '/en/choghadiya/', '/hi/aaj-ka-choghadiya/', '/mr/aajcha-choghadiya/', '/gu/aaj-nu-choghadiya/' ) ) ); ?>">
                ⏳ <?php echo esc_html( $hl( 'cta_cgh' ) ); ?>
            </a>
            <a class="bb-btn bb-btn--ghost" href="<?php echo esc_url( home_url( $lang_url( '/en/hora/', '/hi/aaj-ka-hora/', '/mr/aajcha-hora/', '/gu/aaj-no-hora/' ) ) ); ?>">
                🪐 <?php echo esc_html( $hl( 'cta_hora' ) ); ?>
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     QUICK TOOLS STRIP
     ============================================================ -->
<?php
$bb_lang = bb_current_lang();
$panchang_urls = array(
    'en' => '/en/panchang/',
    'hi' => '/hi/aaj-ka-panchang/',
    'mr' => '/mr/aajcha-panchang/',
    'gu' => '/gu/aaj-no-panchang/',
);
$choghadiya_urls = array(
    'en' => '/en/choghadiya/',
    'hi' => '/hi/aaj-ka-choghadiya/',
    'mr' => '/mr/aajcha-choghadiya/',
    'gu' => '/gu/aaj-nu-choghadiya/',
);
$hora_urls = array(
    'en' => '/en/hora/',
    'hi' => '/hi/aaj-ka-hora/',
    'mr' => '/mr/aajcha-hora/',
    'gu' => '/gu/aaj-no-hora/',
);
$panchang_href   = home_url( $panchang_urls[ $bb_lang ] ?? $panchang_urls['en'] );
$choghadiya_href = home_url( $choghadiya_urls[ $bb_lang ] ?? $choghadiya_urls['en'] );
$hora_href       = home_url( $hora_urls[ $bb_lang ] ?? $hora_urls['en'] );
?>
<section class="bb-quick-tools" aria-label="Quick tools">
    <div class="bb-container">
        <div class="bb-quick-tools__grid">
            <a class="bb-quick-tool" href="<?php echo esc_url( $panchang_href ); ?>">
                <span class="bb-quick-tool__icon">📅</span>
                <span class="bb-quick-tool__label">
                    <span class="bb-quick-tool__title"><?php bb_t( array( 'en' => 'Panchang', 'hi' => 'पंचांग', 'mr' => 'पंचांग', 'gu' => 'પંચાંગ' ) ); ?></span>
                    <span class="bb-quick-tool__sub"><?php bb_t( array( 'en' => "Today's details", 'hi' => 'आज का विवरण', 'mr' => 'आजचे तपशील', 'gu' => 'આજનું વિવરણ' ) ); ?></span>
                </span>
            </a>
            <a class="bb-quick-tool" href="<?php echo esc_url( $choghadiya_href ); ?>">
                <span class="bb-quick-tool__icon">⏳</span>
                <span class="bb-quick-tool__label">
                    <span class="bb-quick-tool__title"><?php bb_t( array( 'en' => 'Choghadiya', 'hi' => 'चौघड़िया', 'mr' => 'चौघडिया', 'gu' => 'ચોઘડિયું' ) ); ?></span>
                    <span class="bb-quick-tool__sub"><?php bb_t( array( 'en' => 'Auspicious muhurats', 'hi' => 'शुभ मुहूर्त', 'mr' => 'शुभ मुहूर्त', 'gu' => 'શુભ મુહૂર્ત' ) ); ?></span>
                </span>
            </a>
            <a class="bb-quick-tool" href="<?php echo esc_url( $hora_href ); ?>">
                <span class="bb-quick-tool__icon">🪐</span>
                <span class="bb-quick-tool__label">
                    <span class="bb-quick-tool__title"><?php bb_t( array( 'en' => 'Hora', 'hi' => 'होरा', 'mr' => 'होरा', 'gu' => 'હોરા' ) ); ?></span>
                    <span class="bb-quick-tool__sub"><?php bb_t( array( 'en' => 'Planetary hours', 'hi' => 'ग्रह घंटे', 'mr' => 'ग्रह तास', 'gu' => 'ગ્રહ કલાકો' ) ); ?></span>
                </span>
            </a>
            <a class="bb-quick-tool" href="<?php echo esc_url( home_url( '/rashifal/' ) ); ?>">
                <span class="bb-quick-tool__icon">⭐</span>
                <span class="bb-quick-tool__label">
                    <span class="bb-quick-tool__title"><?php bb_t( array( 'en' => 'Rashifal', 'hi' => 'राशिफल', 'mr' => 'राशीभविष्य', 'gu' => 'રાશિફળ' ) ); ?></span>
                    <span class="bb-quick-tool__sub"><?php bb_t( array( 'en' => 'Daily horoscope', 'hi' => 'दैनिक राशिफल', 'mr' => 'दैनिक राशिभविष्य', 'gu' => 'દૈનિક રાશિફળ' ) ); ?></span>
                </span>
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     FEATURED AARTIS — 4 photo cards
     ============================================================ -->
<section class="bb-section bb-featured">
    <div class="bb-container">
        <div class="bb-section-head">
            <span class="bb-eyebrow"><?php bb_t( array( 'en' => 'Most Read', 'hi' => 'सर्वाधिक पढ़ी गई', 'mr' => 'सर्वाधिक वाचलेले' ) ); ?></span>
            <h2><?php bb_t( array( 'en' => 'Featured Aartis &amp; Chalisas', 'hi' => 'विशेष आरती और चालीसा', 'mr' => 'विशेष आरती आणि चालीसा' ) ); ?></h2>
            <div class="bb-mandala-divider"><?php echo $mandala_svg; ?></div>
        </div>

        <div class="bb-featured__grid">
            <?php foreach ( $featured_aartis as $a ) : ?>
                <a class="bb-featured-card" href="<?php echo esc_url( $a['url'] ); ?>">
                    <div class="bb-featured-card__img">
                        <img src="<?php echo esc_url( $a['img'] ); ?>" alt="<?php echo esc_attr( $a['en'] ); ?>" loading="lazy">
                    </div>
                    <div class="bb-featured-card__body">
                        <h3 class="bb-featured-card__title"><?php echo esc_html( $a['en'] ); ?></h3>
                        <span class="bb-featured-card__sub bb-devanagari"><?php echo esc_html( $a['hi'] ); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     PANCHANG TEASER CARD (ornate)
     ============================================================ -->
<section class="bb-panchang-wrap">
    <div class="bb-container">
        <div class="bb-panchang-card">
            <div>
                <div class="bb-panchang-card__date"><?php echo esc_html( date_i18n( 'l, j F Y' ) ); ?></div>
                <h2>
                    <span class="bb-devanagari">आज का पंचांग</span>
                    <?php bb_t( array( 'en' => 'Today’s Panchang, at a glance', 'hi' => 'आज का संपूर्ण पंचांग', 'mr' => 'आजचे संपूर्ण पंचांग' ) ); ?>
                </h2>
                <p>
                    <?php bb_t( array(
                        'en' => 'Tithi, Nakshatra, Yoga, Karana, sunrise and sunset — computed for your location to plan the day auspiciously.',
                        'hi' => 'तिथि, नक्षत्र, योग, करण, सूर्योदय और सूर्यास्त — आज का शुभ दिन बिताने के लिए आपके स्थान के अनुसार।',
                        'mr' => 'तिथी, नक्षत्र, योग, करण, सूर्योदय आणि सूर्यास्त — तुमच्या स्थानानुसार शुभ दिवसासाठी.',
                    ) ); ?>
                </p>
                <a class="bb-btn bb-btn--gold" href="<?php echo esc_url( home_url( '/panchang/' ) ); ?>">
                    <?php bb_t( array( 'en' => 'View Full Panchang', 'hi' => 'पूरा पंचांग देखें', 'mr' => 'संपूर्ण पंचांग पहा' ) ); ?>
                </a>
            </div>

            <dl class="bb-panchang-card__facts">
                <div class="bb-panchang-card__fact">
                    <dt>Tithi</dt><dd>—</dd>
                </div>
                <div class="bb-panchang-card__fact">
                    <dt>Nakshatra</dt><dd>—</dd>
                </div>
                <div class="bb-panchang-card__fact">
                    <dt><?php bb_t( array( 'en' => 'Sunrise', 'hi' => 'सूर्योदय', 'mr' => 'सूर्योदय' ) ); ?></dt><dd>—</dd>
                </div>
                <div class="bb-panchang-card__fact">
                    <dt><?php bb_t( array( 'en' => 'Sunset', 'hi' => 'सूर्यास्त', 'mr' => 'सूर्यास्त' ) ); ?></dt><dd>—</dd>
                </div>
            </dl>
        </div>
    </div>
</section>

<!-- ============================================================
     CATEGORY TILES — with photo backgrounds
     ============================================================ -->
<section class="bb-section bb-categories">
    <div class="bb-container">
        <div class="bb-section-head">
            <span class="bb-eyebrow"><?php bb_t( array( 'en' => 'Explore', 'hi' => 'खोजें', 'mr' => 'शोधा' ) ); ?></span>
            <h2><?php bb_t( array( 'en' => 'Everything for your daily devotion', 'hi' => 'आपकी दैनिक भक्ति के लिए सब कुछ', 'mr' => 'तुमच्या दैनिक भक्तीसाठी सर्व काही' ) ); ?></h2>
            <div class="bb-mandala-divider"><?php echo $mandala_svg; ?></div>
        </div>

        <div class="bb-categories__grid">
            <?php foreach ( $categories as $c ) : ?>
                <a class="bb-cat-tile" href="<?php echo esc_url( home_url( '/' . $c['slug'] . '/' ) ); ?>"
                   style="background-image:url('<?php echo esc_url( $c['img'] ); ?>');">
                    <div class="bb-cat-tile__inner">
                        <span class="bb-cat-tile__name"><?php echo esc_html( $c['en'] ); ?></span>
                        <span class="bb-cat-tile__name-hi"><?php echo esc_html( $c['hi'] ); ?></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     LATEST POSTS
     ============================================================ -->
<section class="bb-section bb-posts">
    <div class="bb-container">
        <div class="bb-section-head">
            <span class="bb-eyebrow"><?php bb_t( array( 'en' => 'Fresh from the blog', 'hi' => 'ब्लॉग से नया', 'mr' => 'ब्लॉग मधून नवीन' ) ); ?></span>
            <h2><?php bb_t( array( 'en' => 'Stories, wisdom and practice', 'hi' => 'कहानियाँ, ज्ञान और साधना', 'mr' => 'कथा, ज्ञान आणि साधना' ) ); ?></h2>
            <div class="bb-mandala-divider"><?php echo $mandala_svg; ?></div>
        </div>

        <div class="bb-posts__grid">
            <?php
            $posts_q = new WP_Query( array(
                'post_type'      => 'post',
                'posts_per_page' => 6,
                'post_status'    => 'publish',
                'ignore_sticky_posts' => 1,
            ) );
            if ( $posts_q->have_posts() ) : while ( $posts_q->have_posts() ) : $posts_q->the_post();
                $cats = get_the_category();
                $cat_name = $cats ? $cats[0]->name : '';
            ?>
                <a class="bb-post-card" href="<?php the_permalink(); ?>">
                    <div class="bb-post-card__img">
                        <?php if ( has_post_thumbnail() ) {
                            the_post_thumbnail( 'medium_large', array( 'loading' => 'lazy', 'alt' => get_the_title() ) );
                        } else { ?>
                            <img src="<?php echo esc_url( $uploads_base . '/2023/09/Krishna-Bhajan-Lyrics-bhaktibhawna.jpg' ); ?>" alt="<?php echo esc_attr( get_the_title() ); ?>" loading="lazy">
                        <?php } ?>
                    </div>
                    <div class="bb-post-card__body">
                        <?php if ( $cat_name ) : ?>
                            <span class="bb-post-card__cat"><?php echo esc_html( $cat_name ); ?></span>
                        <?php endif; ?>
                        <h3 class="bb-post-card__title"><?php the_title(); ?></h3>
                        <span class="bb-post-card__meta">
                            🕉 <?php echo bb_reading_time(); ?> <?php bb_t( array( 'en' => 'min read', 'hi' => 'मिनट पढ़ें', 'mr' => 'मिनिट वाचा' ) ); ?>
                        </span>
                    </div>
                </a>
            <?php endwhile; wp_reset_postdata(); endif; ?>
        </div>

        <div style="text-align:center; margin-top: var(--bb-sp-4);">
            <a class="bb-btn bb-btn--maroon" href="<?php echo esc_url( home_url( '/blog/' ) ); ?>">
                <?php bb_t( array( 'en' => 'Read all articles', 'hi' => 'सभी लेख पढ़ें', 'mr' => 'सर्व लेख वाचा' ) ); ?>
            </a>
        </div>
    </div>
</section>

<!-- ============================================================
     TRUST STRIP
     ============================================================ -->
<section class="bb-trust">
    <div class="bb-container bb-trust__inner">
        <div class="bb-trust__heading">
            <h2><?php bb_t( array( 'en' => 'Trusted by thousands of devotees daily', 'hi' => 'प्रतिदिन हजारों भक्तों द्वारा विश्वसनीय', 'mr' => 'दररोज हजारो भक्तांनी विश्वास ठेवलेले' ) ); ?></h2>
        </div>
        <div class="bb-trust__grid">
            <div class="bb-trust__item">
                <div class="bb-trust__num">6,000+</div>
                <span class="bb-trust__label"><?php bb_t( array( 'en' => 'Aartis, Chalisas &amp; Articles', 'hi' => 'आरती, चालीसा और लेख', 'mr' => 'आरती, चालीसा आणि लेख' ) ); ?></span>
            </div>
            <div class="bb-trust__item">
                <div class="bb-trust__num">3</div>
                <span class="bb-trust__label"><?php bb_t( array( 'en' => 'Languages', 'hi' => 'भाषाएँ', 'mr' => 'भाषा' ) ); ?></span>
            </div>
            <div class="bb-trust__item">
                <div class="bb-trust__num">100%</div>
                <span class="bb-trust__label"><?php bb_t( array( 'en' => 'Authentic content', 'hi' => 'प्रामाणिक सामग्री', 'mr' => 'अस्सल सामग्री' ) ); ?></span>
            </div>
            <div class="bb-trust__item">
                <div class="bb-trust__num">24/7</div>
                <span class="bb-trust__label"><?php bb_t( array( 'en' => 'Always accessible', 'hi' => 'सदा उपलब्ध', 'mr' => 'नेहमी उपलब्ध' ) ); ?></span>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     SHLOKA / QUOTE
     ============================================================ -->
<section class="bb-section bb-shloka">
    <div class="bb-container bb-shloka__inner">
        <div class="bb-shloka__om">ॐ</div>
        <div class="bb-mandala-divider"><?php echo $mandala_svg; ?></div>
        <blockquote class="bb-shloka__verse">
            वासुदेवसुतं देवं कंसचाणूरमर्दनम्।<br>
            देवकीपरमानन्दं कृष्णं वन्दे जगद्गुरुम्॥
        </blockquote>
        <p class="bb-shloka__translation">
            <?php bb_t( array(
                'en' => 'I bow to Lord Krishna — son of Vasudev, the destroyer of Kamsa and Chanura, the greatest joy of Devaki, teacher of the world.',
                'hi' => 'मैं वसुदेव के पुत्र, कंस और चाणूर के संहारक, देवकी के परम आनंद, जगद्गुरु भगवान श्रीकृष्ण को नमन करता हूँ।',
                'mr' => 'मी वसुदेवाच्या पुत्राला, कंस आणि चाणूराचा संहारक, देवकीच्या परम आनंदाला, जगद्गुरू भगवान श्रीकृष्णाला नमन करतो.',
            ) ); ?>
        </p>
    </div>
</section>

<!-- ============================================================
     CTA BAND
     ============================================================ -->
<section class="bb-cta-band">
    <div class="bb-container bb-cta-band__inner">
        <h2><?php bb_t( array( 'en' => 'Begin your day with devotion', 'hi' => 'अपने दिन की शुरुआत भक्ति से करें', 'mr' => 'तुमच्या दिवसाची सुरुवात भक्तीने करा' ) ); ?></h2>
        <p>
            <?php bb_t( array(
                'en' => 'Explore thousands of Aartis, Chalisas, Puja vidhi and astro tools — in Hindi, English and Marathi.',
                'hi' => 'हजारों आरती, चालीसा, पूजा विधि और ज्योतिष उपकरणों का अन्वेषण करें — हिंदी, अंग्रेज़ी और मराठी में।',
                'mr' => 'हजारो आरती, चालीसा, पूजा विधी आणि ज्योतिष साधने एक्सप्लोर करा — हिंदी, इंग्रजी आणि मराठीत.',
            ) ); ?>
        </p>
        <a class="bb-btn bb-btn--gold" href="<?php echo esc_url( home_url( '/aarti/' ) ); ?>">
            <?php bb_t( array( 'en' => 'Explore the Site', 'hi' => 'साइट एक्सप्लोर करें', 'mr' => 'साइट एक्सप्लोर करा' ) ); ?>
        </a>
    </div>
</section>

<?php get_footer(); ?>
