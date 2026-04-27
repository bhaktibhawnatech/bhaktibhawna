<?php
/**
 * Template Name: Choghadiya Tool
 * Daily Choghadiya via ProKerala. 8 day + 8 night muhurats. Language-pure (no en/hi mix).
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ----- Aggressive plugin dequeue ----- */
add_action( 'wp_enqueue_scripts', function () {
    $drop_styles = array(
        'elementor-frontend', 'elementor-icons',
        'elementor-gf-local-roboto', 'elementor-gf-local-robotoslab',
        'rt-fontawsome', 'rt-tpg', 'the-post-grid',
        'sgpb-frontend-css', 'sgpbPublicStyles',
        'really-simple-ssl', 'wpforms-base', 'yarppRelatedCss',
    );
    $drop_scripts = array( 'elementor-frontend', 'elementor-frontend-modules', 'elementor-webpack-runtime', 'rt-tpg-public-script', 'sgpb-public' );
    foreach ( $drop_styles as $h )  { wp_dequeue_style( $h );  wp_deregister_style( $h ); }
    foreach ( $drop_scripts as $h ) { wp_dequeue_script( $h ); wp_deregister_script( $h ); }
    global $wp_styles;
    if ( isset( $wp_styles->registered ) ) {
        foreach ( $wp_styles->registered as $h => $obj ) {
            if ( strpos( $h, 'elementor-post-' ) === 0 ) wp_dequeue_style( $h );
        }
    }
}, 9999 );

add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style( 'bb-panchang',   BB_URI . '/assets/css/panchang.css',   array( 'bb-main' ), BB_VER );
    wp_enqueue_style( 'bb-choghadiya', BB_URI . '/assets/css/choghadiya.css', array( 'bb-panchang' ), BB_VER );
}, 20 );

/* ----- Resolve location ----- */
$lang = bb_current_lang();
$cities = bb_popular_cities();

$selected_city_slug = isset( $_GET['city'] ) ? sanitize_key( $_GET['city'] ) : 'new-delhi';
if ( ! isset( $cities[ $selected_city_slug ] ) ) $selected_city_slug = 'new-delhi';
$city = $cities[ $selected_city_slug ];

$lat = isset( $_GET['lat'] ) ? (float) $_GET['lat'] : $city['lat'];
$lng = isset( $_GET['lng'] ) ? (float) $_GET['lng'] : $city['lng'];
$tz  = isset( $_GET['tz'] )  ? sanitize_text_field( $_GET['tz'] ) : $city['tz'];
$location_label = $city[ 'name_' . $lang ] ?? $city['name_en'];

/* ----- Date navigation ----- */
$date_param = isset( $_GET['date'] ) ? sanitize_text_field( $_GET['date'] ) : '';
$is_custom_date = ( $date_param && preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date_param ) );

try {
    $tz_obj = new DateTimeZone( $tz );
    $current_date = $is_custom_date
        ? new DateTime( $date_param . ' 12:00:00', $tz_obj )
        : new DateTime( 'now', $tz_obj );
    $today = new DateTime( 'now', $tz_obj );
    $today->setTime( 0, 0, 0 );
    $cd = clone $current_date; $cd->setTime( 0, 0, 0 );
    $is_today = ( $cd->format( 'Y-m-d' ) === $today->format( 'Y-m-d' ) );
    $prev_date = ( clone $current_date )->modify( '-1 day' )->format( 'Y-m-d' );
    $next_date = ( clone $current_date )->modify( '+1 day' )->format( 'Y-m-d' );
    $current_date_str = $current_date->format( 'Y-m-d' );
} catch ( Exception $e ) {
    $current_date = null; $is_today = true; $prev_date = $next_date = $current_date_str = '';
}

$build_url = function( $date = null ) use ( $selected_city_slug ) {
    $params = array( 'city' => $selected_city_slug );
    if ( $date ) $params['date'] = $date;
    return add_query_arg( $params, get_permalink() );
};

/* ----- Localized strings (LANGUAGE PURE — no mixing) ----- */
$T = array(
    'today'              => array( 'en' => 'Today',                    'hi' => 'आज',                        'mr' => 'आज',                        'gu' => 'આજે' ),
    'daily_choghadiya'   => array( 'en' => 'Daily Choghadiya',         'hi' => 'दैनिक चौघड़िया',             'mr' => 'दैनिक चौघडिया',             'gu' => 'દૈનિક ચોઘડિયું' ),

    'day_choghadiya'     => array( 'en' => 'Day Choghadiya',           'hi' => 'दिन का चौघड़िया',           'mr' => 'दिवसाचा चौघडिया',           'gu' => 'દિવસનું ચોઘડિયું' ),
    'night_choghadiya'   => array( 'en' => 'Night Choghadiya',         'hi' => 'रात का चौघड़िया',           'mr' => 'रात्रीचा चौघडिया',           'gu' => 'રાત્રિનું ચોઘડિયું' ),
    'day_section_label'  => array( 'en' => 'Daytime',                  'hi' => 'दिन',                       'mr' => 'दिवस',                      'gu' => 'દિવસ' ),
    'night_section_label'=> array( 'en' => 'Nighttime',                'hi' => 'रात्रि',                    'mr' => 'रात्र',                     'gu' => 'રાત્રિ' ),

    'now'                => array( 'en' => 'Now',                      'hi' => 'अभी',                       'mr' => 'आता',                       'gu' => 'હાલ' ),
    'current_muhurat'    => array( 'en' => 'Current Muhurat',          'hi' => 'वर्तमान मुहूर्त',            'mr' => 'सध्याचा मुहूर्त',            'gu' => 'વર્તમાન મુહૂર્ત' ),

    'location'           => array( 'en' => 'Location',                 'hi' => 'स्थान',                     'mr' => 'स्थान',                      'gu' => 'સ્થાન' ),
    'previous_day'       => array( 'en' => 'Previous Day',             'hi' => 'पिछला दिन',                 'mr' => 'मागील दिवस',                 'gu' => 'આગલો દિવસ' ),
    'today_btn'          => array( 'en' => 'Today',                    'hi' => 'आज',                        'mr' => 'आज',                         'gu' => 'આજે' ),
    'next_day'           => array( 'en' => 'Next Day',                 'hi' => 'अगला दिन',                  'mr' => 'पुढील दिवस',                 'gu' => 'આગામી દિવસ' ),
    'pick_date'          => array( 'en' => 'Pick a date',              'hi' => 'तारीख चुनें',               'mr' => 'दिनांक निवडा',                'gu' => 'તારીખ પસંદ કરો' ),

    /* Per-muhurat Drik-style quality (overrides API "type" — API marks Char as "Good" but
     * traditional Drik convention has Char as सामान्य/Neutral, NOT auspicious. */
    'q_Amrut'            => array( 'en' => 'Best',                     'hi' => 'सर्वोत्तम',                 'mr' => 'सर्वोत्तम',                  'gu' => 'સર્વોત્તમ' ),
    'q_Shubh'            => array( 'en' => 'Auspicious',               'hi' => 'उत्तम',                      'mr' => 'उत्तम',                      'gu' => 'ઉત્તમ' ),
    'q_Labh'             => array( 'en' => 'Profit',                   'hi' => 'उन्नति',                     'mr' => 'उन्नती',                     'gu' => 'ઉન્નતિ' ),
    'q_Char'             => array( 'en' => 'Neutral',                  'hi' => 'सामान्य',                    'mr' => 'सामान्य',                    'gu' => 'સામાન્ય' ),
    'q_Kaal'             => array( 'en' => 'Loss',                     'hi' => 'हानि',                       'mr' => 'हानी',                       'gu' => 'હાનિ' ),
    'q_Rog'              => array( 'en' => 'Inauspicious',             'hi' => 'अमंगल',                      'mr' => 'अमंगळ',                      'gu' => 'અમંગલ' ),
    'q_Udveg'            => array( 'en' => 'Bad',                     'hi' => 'अशुभ',                       'mr' => 'अशुभ',                       'gu' => 'અશુભ' ),

    'related_label'      => array( 'en' => 'Also See',                 'hi' => 'यह भी देखें',                'mr' => 'हे देखील पाहा',               'gu' => 'આ પણ જુઓ' ),
    'related_panchang'   => array( 'en' => 'Daily Panchang',           'hi' => 'दैनिक पंचांग',                'mr' => 'दैनिक पंचांग',                'gu' => 'દૈનિક પંચાંગ' ),

    'about_heading'      => array( 'en' => 'What is Choghadiya?',      'hi' => 'चौघड़िया क्या है?',          'mr' => 'चौघडिया म्हणजे काय?',         'gu' => 'ચોઘડિયું શું છે?' ),
    'about_text'         => array(
        'en' => 'Choghadiya is a Vedic Hindu calendar of muhurats (auspicious time slots) used to choose the right time for any new activity. The day and night are each divided into 8 parts (called Choghadiyas) of about 1 hour 30 minutes. Each muhurat is classified as Amrut, Shubh or Labh (highly auspicious), Char (good for travel), or Kaal, Rog, Udveg (inauspicious — to be avoided). Choghadiya is consulted especially for travel, business, signing contracts, and starting any new venture.',
        'hi' => 'चौघड़िया वैदिक हिन्दू मुहूर्त गणना है, जिसका उपयोग किसी भी नए कार्य के लिए शुभ समय चुनने में होता है। दिन और रात को 8-8 भागों में बाँटा जाता है, प्रत्येक भाग लगभग 1 घंटा 30 मिनट का होता है। मुहूर्तों को अमृत, शुभ, लाभ (सर्वाधिक शुभ), चर (यात्रा हेतु अच्छा), तथा काल, रोग, उद्वेग (अशुभ — त्याज्य) में वर्गीकृत किया जाता है। यात्रा, व्यापार, अनुबंध तथा नए कार्यों के आरंभ हेतु चौघड़िया विशेष रूप से देखा जाता है।',
        'mr' => 'चौघडिया हे वैदिक हिंदू मुहूर्त गणनेचे एक रूप आहे, ज्याचा वापर कोणत्याही नव्या कार्यासाठी शुभ वेळ निवडण्यासाठी केला जातो. दिवस आणि रात्र प्रत्येकी 8 भागांत विभागली जाते, प्रत्येक भाग सुमारे 1 तास 30 मिनिटांचा असतो. मुहूर्तांचे वर्गीकरण अमृत, शुभ, लाभ (अत्यंत शुभ), चर (प्रवासासाठी चांगला), आणि काळ, रोग, उद्वेग (अशुभ — टाळावे) असे केले जाते. प्रवास, व्यवसाय, करार आणि नव्या उपक्रमासाठी चौघडिया विशेष उपयुक्त आहे.',
        'gu' => 'ચોઘડિયું એ વૈદિક હિંદુ મુહૂર્ત ગણનાનું એક રૂપ છે, જેનો ઉપયોગ કોઈપણ નવા કાર્ય માટે શુભ સમય પસંદ કરવા થાય છે. દિવસ અને રાત્રિ બંને 8-8 ભાગોમાં વહેંચાય છે, દરેક ભાગ આશરે 1 કલાક 30 મિનિટનો હોય છે. મુહૂર્તોને અમૃત, શુભ, લાભ (અત્યંત શુભ), ચર (પ્રવાસ માટે સારું), અને કાળ, રોગ, ઉદ્વેગ (અશુભ — ટાળવા જેવા) એમ વર્ગીકૃત કરાય છે. પ્રવાસ, વ્યાપાર, કરાર અને નવા કાર્યો માટે ચોઘડિયું ખાસ જોવાય છે.',
    ),
);
$t = function( $key ) use ( $T, $lang ) { return $T[ $key ][ $lang ] ?? $T[ $key ]['en'] ?? ''; };

/* Translated muhurat names per language (API always returns English names) */
$muhurat_names = array(
    'Amrut'  => array( 'en' => 'Amrut',  'hi' => 'अमृत',     'mr' => 'अमृत',     'gu' => 'અમૃત' ),
    'Shubh'  => array( 'en' => 'Shubh',  'hi' => 'शुभ',      'mr' => 'शुभ',      'gu' => 'શુભ' ),
    'Labh'   => array( 'en' => 'Labh',   'hi' => 'लाभ',      'mr' => 'लाभ',      'gu' => 'લાભ' ),
    'Char'   => array( 'en' => 'Char',   'hi' => 'चर',       'mr' => 'चर',       'gu' => 'ચર' ),
    'Kaal'   => array( 'en' => 'Kaal',   'hi' => 'काल',      'mr' => 'काळ',      'gu' => 'કાળ' ),
    'Rog'    => array( 'en' => 'Rog',    'hi' => 'रोग',      'mr' => 'रोग',      'gu' => 'રોગ' ),
    'Udveg'  => array( 'en' => 'Udveg',  'hi' => 'उद्वेग',    'mr' => 'उद्वेग',    'gu' => 'ઉદ્વેગ' ),
);
$mname = function( $en ) use ( $muhurat_names, $lang ) {
    return $muhurat_names[ $en ][ $lang ] ?? $en;
};

/* Vela note translations */
$vela_names = array(
    'Kaal Vela'  => array( 'en' => 'Kaal Vela',  'hi' => 'काल वेला',  'mr' => 'काळ वेळा',  'gu' => 'કાળ વેળા' ),
    'Vaar Vela'  => array( 'en' => 'Vaar Vela',  'hi' => 'वार वेला',  'mr' => 'वार वेळा',  'gu' => 'વાર વેળા' ),
    'Kaal Ratri' => array( 'en' => 'Kaal Ratri', 'hi' => 'काल रात्रि', 'mr' => 'काळ रात्र', 'gu' => 'કાળ રાત્રિ' ),
);
$vname = function( $en ) use ( $vela_names, $lang ) {
    if ( ! $en ) return '';
    return $vela_names[ $en ][ $lang ] ?? $en;
};

/* Per-muhurat quality label (Drik convention — overrides API type) */
$quality_label = function( $name_en ) use ( $t ) {
    return $t( 'q_' . $name_en );
};

/* Per-muhurat CSS class — Drik tradition:
 *   Auspicious (green/gold): Amrut, Shubh, Labh
 *   Neutral (grey):          Char  (API marks this Good — wrong by Drik)
 *   Inauspicious (red):      Kaal, Rog, Udveg
 */
$muhurat_class_map = array(
    'Amrut' => 'bb-cgh--good',  'Shubh' => 'bb-cgh--good',  'Labh' => 'bb-cgh--good',
    'Char'  => 'bb-cgh--mid',
    'Kaal'  => 'bb-cgh--bad',   'Rog'   => 'bb-cgh--bad',   'Udveg' => 'bb-cgh--bad',
);
$mclass = function( $name_en ) use ( $muhurat_class_map ) {
    return $muhurat_class_map[ $name_en ] ?? 'bb-cgh--mid';
};

/* ----- API call -----
 * Force la=en so muhurat name keys ('Amrut', 'Shubh', etc.) stay stable.
 * We translate locally via $T / $muhurat_names / $muhurat_class_map.
 */
$api_args = array(
    'lat' => $lat, 'lng' => $lng, 'tz' => $tz,
    'bb_lang' => 'en',
    'date' => $current_date_str,
);
$choghadiya = BB_Astro::choghadiya( $api_args );
$sunrise    = $choghadiya['sunrise'] ?? null;
$sunset     = $choghadiya['sunset']  ?? null;

get_header();

$tz_obj = new DateTimeZone( $tz ?: 'Asia/Kolkata' );
$nice_time = function ( $iso ) use ( $tz_obj ) {
    if ( ! $iso ) return '—';
    try { $dt = new DateTime( $iso ); $dt->setTimezone( $tz_obj ); return $dt->format( 'g:i A' );
    } catch ( Exception $e ) { return '—'; }
};

/* "now" reference for current-muhurat highlight (only on today) */
$now_ts = $is_today ? time() : 0;
$is_now = function( $start_iso, $end_iso ) use ( $now_ts ) {
    if ( ! $now_ts || ! $start_iso || ! $end_iso ) return false;
    try {
        $s = ( new DateTime( $start_iso ) )->getTimestamp();
        $e = ( new DateTime( $end_iso ) )->getTimestamp();
        return ( $now_ts >= $s && $now_ts < $e );
    } catch ( Exception $e ) { return false; }
};

/* Split muhurats into day / night */
$day_muhurats = array();
$night_muhurats = array();
if ( $choghadiya && ! empty( $choghadiya['muhurat'] ) ) {
    foreach ( $choghadiya['muhurat'] as $m ) {
        if ( ! empty( $m['is_day'] ) ) $day_muhurats[] = $m;
        else $night_muhurats[] = $m;
    }
}

/* Find the current muhurat (if today) */
$current = null;
if ( $is_today && $choghadiya && ! empty( $choghadiya['muhurat'] ) ) {
    foreach ( $choghadiya['muhurat'] as $m ) {
        if ( $is_now( $m['start'] ?? null, $m['end'] ?? null ) ) { $current = $m; break; }
    }
}
?>

<!-- ============== HERO ============== -->
<section class="bb-panchang-hero">
    <div class="bb-container">
        <div class="bb-panchang-hero__inner">
            <span class="bb-eyebrow">
                <?php
                if ( $is_today ) {
                    echo esc_html( $t( 'today' ) . ' · ' . date_i18n( 'l, j F Y' ) );
                } else {
                    echo esc_html( date_i18n( 'l, j F Y', $current_date->getTimestamp() ) );
                }
                ?>
            </span>
            <h1 class="bb-panchang-hero__title">
                <?php echo esc_html( $t( 'daily_choghadiya' ) ); ?>
            </h1>
            <p class="bb-panchang-hero__sub">📍 <?php echo esc_html( $location_label ); ?></p>
        </div>
    </div>
</section>

<!-- ============== CITY + DATE NAVIGATION ============== -->
<section class="bb-toolbar">
    <div class="bb-container">
        <div class="bb-toolbar__row">
            <form class="bb-toolbar__city" method="get" action="<?php echo esc_url( get_permalink() ); ?>">
                <label class="bb-toolbar__label" for="bb-city-select">🌍</label>
                <select name="city" id="bb-city-select" class="bb-toolbar__select" onchange="this.form.submit()">
                    <?php foreach ( $cities as $slug => $c ) :
                        $name = $c[ 'name_' . $lang ] ?? $c['name_en'];
                        $sel = ( $slug === $selected_city_slug ) ? ' selected' : '';
                    ?>
                        <option value="<?php echo esc_attr( $slug ); ?>"<?php echo $sel; ?>><?php echo esc_html( $name ); ?></option>
                    <?php endforeach; ?>
                </select>
                <?php if ( $is_custom_date ) : ?>
                    <input type="hidden" name="date" value="<?php echo esc_attr( $current_date_str ); ?>">
                <?php endif; ?>
            </form>

            <div class="bb-toolbar__date">
                <a class="bb-toolbar__nav-btn" href="<?php echo esc_url( $build_url( $prev_date ) ); ?>" aria-label="<?php echo esc_attr( $t( 'previous_day' ) ); ?>">
                    ← <span class="bb-toolbar__nav-text"><?php echo esc_html( $t( 'previous_day' ) ); ?></span>
                </a>
                <form class="bb-toolbar__date-picker" method="get" action="<?php echo esc_url( get_permalink() ); ?>">
                    <input type="hidden" name="city" value="<?php echo esc_attr( $selected_city_slug ); ?>">
                    <input type="date" name="date" value="<?php echo esc_attr( $current_date_str ); ?>" class="bb-toolbar__date-input" onchange="this.form.submit()" aria-label="<?php echo esc_attr( $t( 'pick_date' ) ); ?>">
                </form>
                <?php if ( ! $is_today ) : ?>
                    <a class="bb-toolbar__today-btn" href="<?php echo esc_url( $build_url() ); ?>"><?php echo esc_html( $t( 'today_btn' ) ); ?></a>
                <?php endif; ?>
                <a class="bb-toolbar__nav-btn" href="<?php echo esc_url( $build_url( $next_date ) ); ?>" aria-label="<?php echo esc_attr( $t( 'next_day' ) ); ?>">
                    <span class="bb-toolbar__nav-text"><?php echo esc_html( $t( 'next_day' ) ); ?></span> →
                </a>
            </div>
        </div>
    </div>
</section>

<?php if ( ! $choghadiya || empty( $choghadiya['muhurat'] ) ) : ?>
    <section class="bb-section"><div class="bb-container" style="text-align:center;padding-block:3rem;">
        <h2><?php echo esc_html( $lang === 'hi' ? 'चौघड़िया अस्थायी रूप से अनुपलब्ध' : ( $lang === 'mr' ? 'चौघडिया तात्पुरते अनुपलब्ध' : ( $lang === 'gu' ? 'ચોઘડિયું હાલ ઉપલબ્ધ નથી' : 'Choghadiya temporarily unavailable' ) ) ); ?></h2>
        <a class="bb-btn bb-btn--primary" href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( $lang === 'hi' ? 'पुनः प्रयास' : ( $lang === 'mr' ? 'पुन्हा प्रयत्न' : ( $lang === 'gu' ? 'ફરી પ્રયાસ કરો' : 'Try Again' ) ) ); ?></a>
    </div></section>
<?php else : ?>

<section class="bb-section bb-section--tight">
    <div class="bb-container">

        <?php /* ============== CURRENT MUHURAT (only today) ============== */ ?>
        <?php if ( $current ) : ?>
        <?php
        /* Drik-style: derive class from muhurat NAME, not API type — Char is mid not good */
        $cur_class_map = array(
            'Amrut' => 'good', 'Shubh' => 'good', 'Labh' => 'good',
            'Char'  => 'mid',
            'Kaal'  => 'bad',  'Rog'   => 'bad',  'Udveg' => 'bad',
        );
        $cur_class = $cur_class_map[ $current['name'] ] ?? 'mid';
        ?>
        <div class="bb-cgh-current bb-cgh-current--<?php echo esc_attr( $cur_class ); ?>">
            <span class="bb-cgh-current__label"><?php echo esc_html( $t( 'current_muhurat' ) ); ?></span>
            <h2 class="bb-cgh-current__name"><?php echo esc_html( $mname( $current['name'] ) ); ?></h2>
            <p class="bb-cgh-current__type"><?php echo esc_html( $quality_label( $current["name"] ) ); ?></p>
            <p class="bb-cgh-current__time">
                <strong><?php echo esc_html( $nice_time( $current['start'] ) ); ?></strong>
                — <strong><?php echo esc_html( $nice_time( $current['end'] ) ); ?></strong>
            </p>
            <?php if ( ! empty( $current['vela'] ) ) : ?>
                <p class="bb-cgh-current__vela">⚠️ <?php echo esc_html( $vname( $current['vela'] ) ); ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php /* ============== DAY CHOGHADIYA ============== */ ?>
        <?php if ( ! empty( $day_muhurats ) ) : ?>
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow">☀️ <?php echo esc_html( $t( 'day_section_label' ) ); ?>
                <?php if ( $sunrise && $sunset ) : ?>
                    · <?php echo esc_html( $nice_time( $sunrise ) ); ?> – <?php echo esc_html( $nice_time( $sunset ) ); ?>
                <?php endif; ?>
            </span>
            <h2><?php echo esc_html( $t( 'day_choghadiya' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>
        <div class="bb-cgh-grid">
            <?php foreach ( $day_muhurats as $i => $m ) :
                $is_curr = $is_now( $m['start'] ?? null, $m['end'] ?? null );
            ?>
                <article class="bb-cgh-tile <?php echo esc_attr( $mclass( $m["name"] ) ); ?><?php echo $is_curr ? ' bb-cgh-tile--now' : ''; ?>">
                    <span class="bb-cgh-tile__index"><?php echo (int) ( $i + 1 ); ?></span>
                    <h3 class="bb-cgh-tile__name"><?php echo esc_html( $mname( $m['name'] ) ); ?></h3>
                    <span class="bb-cgh-tile__type"><?php echo esc_html( $quality_label( $m["name"] ) ); ?></span>
                    <span class="bb-cgh-tile__time">
                        <?php echo esc_html( $nice_time( $m['start'] ) ); ?>
                        <br>—<br>
                        <?php echo esc_html( $nice_time( $m['end'] ) ); ?>
                    </span>
                    <?php if ( ! empty( $m['vela'] ) ) : ?>
                        <span class="bb-cgh-tile__vela">⚠️ <?php echo esc_html( $vname( $m['vela'] ) ); ?></span>
                    <?php endif; ?>
                    <?php if ( $is_curr ) : ?>
                        <span class="bb-cgh-tile__now-badge"><?php echo esc_html( $t( 'now' ) ); ?></span>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php /* ============== NIGHT CHOGHADIYA ============== */ ?>
        <?php if ( ! empty( $night_muhurats ) ) : ?>
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow">🌙 <?php echo esc_html( $t( 'night_section_label' ) ); ?>
                <?php if ( $sunset ) : ?>
                    · <?php echo esc_html( $nice_time( $sunset ) ); ?> – <?php echo esc_html( $nice_time( $night_muhurats[ count( $night_muhurats ) - 1 ]['end'] ?? null ) ); ?>
                <?php endif; ?>
            </span>
            <h2><?php echo esc_html( $t( 'night_choghadiya' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>
        <div class="bb-cgh-grid">
            <?php foreach ( $night_muhurats as $i => $m ) :
                $is_curr = $is_now( $m['start'] ?? null, $m['end'] ?? null );
            ?>
                <article class="bb-cgh-tile <?php echo esc_attr( $mclass( $m["name"] ) ); ?><?php echo $is_curr ? ' bb-cgh-tile--now' : ''; ?>">
                    <span class="bb-cgh-tile__index"><?php echo (int) ( $i + 1 ); ?></span>
                    <h3 class="bb-cgh-tile__name"><?php echo esc_html( $mname( $m['name'] ) ); ?></h3>
                    <span class="bb-cgh-tile__type"><?php echo esc_html( $quality_label( $m["name"] ) ); ?></span>
                    <span class="bb-cgh-tile__time">
                        <?php echo esc_html( $nice_time( $m['start'] ) ); ?>
                        <br>—<br>
                        <?php echo esc_html( $nice_time( $m['end'] ) ); ?>
                    </span>
                    <?php if ( ! empty( $m['vela'] ) ) : ?>
                        <span class="bb-cgh-tile__vela">⚠️ <?php echo esc_html( $vname( $m['vela'] ) ); ?></span>
                    <?php endif; ?>
                    <?php if ( $is_curr ) : ?>
                        <span class="bb-cgh-tile__now-badge"><?php echo esc_html( $t( 'now' ) ); ?></span>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php /* Single ad slot — after main content, before related */ ?>
        <?php bb_ad_slot( BB_AD_SLOT_TOOL ); ?>

        <?php /* ============== RELATED — link to Panchang + Hora ============== */ ?>
        <?php
        $panchang_urls = array(
            'en' => '/en/panchang/',           'hi' => '/hi/aaj-ka-panchang/',
            'mr' => '/mr/aajcha-panchang/',    'gu' => '/gu/aaj-no-panchang/',
        );
        $hora_urls = array(
            'en' => '/en/hora/',               'hi' => '/hi/aaj-ka-hora/',
            'mr' => '/mr/aajcha-hora/',        'gu' => '/gu/aaj-no-hora/',
        );
        $panchang_href = $panchang_urls[ $lang ] ?? $panchang_urls['en'];
        $hora_href     = $hora_urls[ $lang ]     ?? $hora_urls['en'];
        $hora_label = array(
            'en' => 'Daily Hora', 'hi' => 'दैनिक होरा', 'mr' => 'दैनिक होरा', 'gu' => 'દૈનિક હોરા',
        );
        ?>
        <div class="bb-cgh-related">
            <span class="bb-eyebrow"><?php echo esc_html( $t( 'related_label' ) ); ?></span>
            <div style="display:flex; flex-wrap:wrap; gap:0.75rem; justify-content:center;">
                <a class="bb-cgh-related-card" href="<?php echo esc_url( $panchang_href ); ?>">
                    <span class="bb-cgh-related-card__icon">📿</span>
                    <span class="bb-cgh-related-card__name"><?php echo esc_html( $t( 'related_panchang' ) ); ?></span>
                    <span class="bb-cgh-related-card__arrow">→</span>
                </a>
                <a class="bb-cgh-related-card" href="<?php echo esc_url( $hora_href ); ?>">
                    <span class="bb-cgh-related-card__icon">🪐</span>
                    <span class="bb-cgh-related-card__name"><?php echo esc_html( $hora_label[ $lang ] ?? $hora_label['en'] ); ?></span>
                    <span class="bb-cgh-related-card__arrow">→</span>
                </a>
            </div>
        </div>

        <?php /* ============== ABOUT (SEO content) ============== */ ?>
        <div class="bb-panchang-about">
            <h2><?php echo esc_html( $t( 'about_heading' ) ); ?></h2>
            <p><?php echo esc_html( $t( 'about_text' ) ); ?></p>
        </div>

    </div>
</section>

<!-- ========== Editor content (if present) ========== -->
<?php if ( have_posts() ) : while ( have_posts() ) : the_post();
    $content = get_the_content();
    if ( trim( $content ) !== '' ) : ?>
    <section class="bb-section" style="background: var(--bb-ivory);">
        <div class="bb-container" style="max-width:820px;"><?php the_content(); ?></div>
    </section>
    <?php endif;
endwhile; endif; ?>

<?php endif; ?>

<?php get_footer(); ?>
