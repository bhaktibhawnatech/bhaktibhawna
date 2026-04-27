<?php
/**
 * Template Name: Hora Tool
 * Daily Hora (planetary hours) via ProKerala. 12 day + 12 night horas.
 * Reuses bb-cgh-* CSS classes from choghadiya.css.
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
    'daily_hora'         => array( 'en' => 'Daily Hora',               'hi' => 'दैनिक होरा',                'mr' => 'दैनिक होरा',                'gu' => 'દૈનિક હોરા' ),

    'day_hora'           => array( 'en' => 'Day Hora',                 'hi' => 'दिन की होरा',               'mr' => 'दिवसाची होरा',              'gu' => 'દિવસની હોરા' ),
    'night_hora'         => array( 'en' => 'Night Hora',               'hi' => 'रात की होरा',               'mr' => 'रात्रीची होरा',             'gu' => 'રાત્રિની હોરા' ),
    'day_section_label'  => array( 'en' => 'Daytime',                  'hi' => 'दिन',                       'mr' => 'दिवस',                      'gu' => 'દિવસ' ),
    'night_section_label'=> array( 'en' => 'Nighttime',                'hi' => 'रात्रि',                    'mr' => 'रात्र',                     'gu' => 'રાત્રિ' ),

    'now'                => array( 'en' => 'Now',                      'hi' => 'अभी',                       'mr' => 'आता',                       'gu' => 'હાલ' ),
    'current_hora'       => array( 'en' => 'Current Hora',             'hi' => 'वर्तमान होरा',              'mr' => 'सध्याची होरा',              'gu' => 'વર્તમાન હોરા' ),

    'previous_day'       => array( 'en' => 'Previous Day',             'hi' => 'पिछला दिन',                 'mr' => 'मागील दिवस',                'gu' => 'આગલો દિવસ' ),
    'today_btn'          => array( 'en' => 'Today',                    'hi' => 'आज',                        'mr' => 'आज',                        'gu' => 'આજે' ),
    'next_day'           => array( 'en' => 'Next Day',                 'hi' => 'अगला दिन',                  'mr' => 'पुढील दिवस',                'gu' => 'આગામી દિવસ' ),
    'pick_date'          => array( 'en' => 'Pick a date',              'hi' => 'तारीख चुनें',               'mr' => 'दिनांक निवडा',               'gu' => 'તારીખ પસંદ કરો' ),

    /* Per-planet quality (Drik-style — overrides the API "type" field which is too coarse) */
    'q_Sun'              => array( 'en' => 'Powerful',                 'hi' => 'बलवान',                     'mr' => 'बलवान',                     'gu' => 'બળવાન' ),
    'q_Moon'             => array( 'en' => 'Gentle',                   'hi' => 'नम्र',                      'mr' => 'नम्र',                       'gu' => 'નમ્ર' ),
    'q_Mars'             => array( 'en' => 'Aggressive',               'hi' => 'आक्रामक',                    'mr' => 'आक्रमक',                     'gu' => 'આક્રમક' ),
    'q_Mercury'          => array( 'en' => 'Sharp',                    'hi' => 'तीव्र',                      'mr' => 'तीव्र',                       'gu' => 'તીવ્ર' ),
    'q_Jupiter'          => array( 'en' => 'Fruitful',                 'hi' => 'फलदायक',                     'mr' => 'फलदायक',                     'gu' => 'ફળદાયક' ),
    'q_Venus'            => array( 'en' => 'Profitable',               'hi' => 'लाभदायी',                    'mr' => 'लाभदायी',                    'gu' => 'લાભદાયી' ),
    'q_Saturn'           => array( 'en' => 'Slow',                     'hi' => 'मन्द',                       'mr' => 'मंद',                        'gu' => 'મંદ' ),

    'related_label'      => array( 'en' => 'Also See',                 'hi' => 'यह भी देखें',               'mr' => 'हे देखील पाहा',              'gu' => 'આ પણ જુઓ' ),
    'related_panchang'   => array( 'en' => 'Daily Panchang',           'hi' => 'दैनिक पंचांग',              'mr' => 'दैनिक पंचांग',              'gu' => 'દૈનિક પંચાંગ' ),
    'related_choghadiya' => array( 'en' => 'Daily Choghadiya',         'hi' => 'दैनिक चौघड़िया',             'mr' => 'दैनिक चौघडिया',             'gu' => 'દૈનિક ચોઘડિયું' ),

    'about_heading'      => array( 'en' => 'What is Hora?',            'hi' => 'होरा क्या है?',              'mr' => 'होरा म्हणजे काय?',           'gu' => 'હોરા શું છે?' ),
    'about_text'         => array(
        'en' => 'Hora is a system of dividing the day and night into 24 planetary hours, each ruled by one of the seven planets — Sun, Moon, Mars, Mercury, Jupiter, Venus, Saturn. The first hora of each day is ruled by the planet that names the weekday (Sunday → Sun, Monday → Moon, etc.), and the rest follow a fixed sequence. Each hora carries the qualities of its ruling planet — Moon hora is good for emotional matters, Mercury for communication, Jupiter for learning, Venus for relationships and arts, Saturn is generally avoided. Hora is consulted alongside Panchang and Choghadiya to fine-tune the time for any new activity.',
        'hi' => 'होरा वैदिक समय गणना की एक पद्धति है, जिसमें दिन-रात को 24 ग्रह-घंटों में बाँटा जाता है। प्रत्येक होरा सात ग्रहों — सूर्य, चंद्र, मंगल, बुध, गुरु, शुक्र, शनि — में से एक के अधीन होती है। प्रत्येक दिन की पहली होरा उस वार के स्वामी ग्रह से संबंधित होती है (रविवार — सूर्य, सोमवार — चंद्र, इत्यादि)। प्रत्येक होरा अपने ग्रह स्वामी के गुणों से प्रभावित होती है — चंद्र होरा भावनात्मक कार्यों, बुध संवाद, गुरु अध्ययन, शुक्र संबंधों एवं कलाओं के लिए शुभ मानी जाती है, जबकि शनि होरा सामान्यतः त्याज्य है। पंचांग और चौघड़िया के साथ-साथ होरा का परामर्श किसी भी नए कार्य के सूक्ष्म समय निर्धारण में किया जाता है।',
        'mr' => 'होरा ही वैदिक काल-गणनेची एक पद्धत आहे, ज्यामध्ये दिवस आणि रात्र 24 ग्रह-तासांमध्ये विभागलेले असतात. प्रत्येक होरा सात ग्रहांपैकी एका — सूर्य, चंद्र, मंगळ, बुध, गुरु, शुक्र, शनी — च्या अधिपत्याखाली असते. प्रत्येक दिवसाची पहिली होरा त्या वाराच्या स्वामी ग्रहाची असते (रविवार — सूर्य, सोमवार — चंद्र, इत्यादी). प्रत्येक होऱ्याचे स्वरूप तिच्या स्वामी ग्रहावर अवलंबून असते — चंद्र होरा भावनिक कार्यांसाठी, बुध संवादासाठी, गुरु अभ्यासासाठी, शुक्र नातेसंबंध व कलांसाठी शुभ मानली जाते; शनी होरा सहसा टाळावी. पंचांग व चौघडिया सोबत होऱ्याचा सल्ला नव्या कार्यासाठी सूक्ष्म वेळ निवडण्यासाठी घेतला जातो.',
        'gu' => 'હોરા એ વૈદિક કાળ-ગણનાની એક પદ્ધતિ છે, જેમાં દિવસ અને રાત્રિને 24 ગ્રહ-કલાકોમાં વહેંચવામાં આવે છે. દરેક હોરા સાત ગ્રહોમાંથી એક — સૂર્ય, ચંદ્ર, મંગળ, બુધ, ગુરુ, શુક્ર, શનિ — ના અધિપત્ય હેઠળ આવે છે. દરેક દિવસની પ્રથમ હોરા તે વારના સ્વામી ગ્રહની હોય છે (રવિવાર — સૂર્ય, સોમવાર — ચંદ્ર, વગેરે). દરેક હોરાનું સ્વરૂપ તેના સ્વામી ગ્રહ પર આધારિત હોય છે — ચંદ્ર હોરા ભાવનાત્મક કાર્યો માટે, બુધ સંવાદ માટે, ગુરુ અભ્યાસ માટે, શુક્ર સંબંધો અને કળાઓ માટે શુભ માનવામાં આવે છે; શનિ હોરા સામાન્ય રીતે ટાળવી જોઈએ. પંચાંગ અને ચોઘડિયા સાથે હોરાનો સંદર્ભ કોઈપણ નવા કાર્ય માટે સૂક્ષ્મ સમય નક્કી કરવા માટે લેવાય છે.',
    ),
);
$t = function( $key ) use ( $T, $lang ) { return $T[ $key ][ $lang ] ?? $T[ $key ]['en'] ?? ''; };

/* Translated planet names (API returns English + Vedic Sanskrit) */
$planet_names = array(
    'Sun'     => array( 'en' => 'Sun',     'hi' => 'सूर्य',  'mr' => 'सूर्य',  'gu' => 'સૂર્ય' ),
    'Moon'    => array( 'en' => 'Moon',    'hi' => 'चंद्र',   'mr' => 'चंद्र',   'gu' => 'ચંદ્ર' ),
    'Mars'    => array( 'en' => 'Mars',    'hi' => 'मंगल',   'mr' => 'मंगळ',   'gu' => 'મંગળ' ),
    'Mercury' => array( 'en' => 'Mercury', 'hi' => 'बुध',    'mr' => 'बुध',    'gu' => 'બુધ' ),
    'Jupiter' => array( 'en' => 'Jupiter', 'hi' => 'गुरु',   'mr' => 'गुरु',   'gu' => 'ગુરુ' ),
    'Venus'   => array( 'en' => 'Venus',   'hi' => 'शुक्र',  'mr' => 'शुक्र',  'gu' => 'શુક્ર' ),
    'Saturn'  => array( 'en' => 'Saturn',  'hi' => 'शनि',    'mr' => 'शनी',    'gu' => 'શનિ' ),
);
$planet_icons = array(
    'Sun' => '☀️', 'Moon' => '🌙', 'Mars' => '♂', 'Mercury' => '☿',
    'Jupiter' => '♃', 'Venus' => '♀', 'Saturn' => '♄',
);
$pname = function( $en ) use ( $planet_names, $lang ) {
    return $planet_names[ $en ][ $lang ] ?? $en;
};

/* Per-planet quality label (Drik convention — Sun=Powerful, Moon=Gentle, etc.) */
$quality_label = function( $planet_en ) use ( $t ) {
    return $t( 'q_' . $planet_en );
};

/* Per-planet CSS color class — each planet gets its own visual identity */
$planet_class = array(
    'Sun'     => 'bb-hora--sun',
    'Moon'    => 'bb-hora--moon',
    'Mars'    => 'bb-hora--mars',
    'Mercury' => 'bb-hora--mercury',
    'Jupiter' => 'bb-hora--jupiter',
    'Venus'   => 'bb-hora--venus',
    'Saturn'  => 'bb-hora--saturn',
);
$pclass = function( $planet_en ) use ( $planet_class ) {
    return $planet_class[ $planet_en ] ?? '';
};

/* ----- API call -----
 * Force la=en so planet name keys ('Sun', 'Moon', etc.) stay stable.
 * We do all translation ourselves via $T / $planet_names / $planet_class.
 */
$api_args = array(
    'lat' => $lat, 'lng' => $lng, 'tz' => $tz,
    'bb_lang' => 'en',
    'date' => $current_date_str,
);
$hora     = BB_Prokerala_API::hora( $api_args );
$panchang = BB_Prokerala_API::panchang( $api_args );
$sunrise  = $panchang['sunrise']  ?? null;
$sunset   = $panchang['sunset']   ?? null;

get_header();

$tz_obj = new DateTimeZone( $tz ?: 'Asia/Kolkata' );
$nice_time = function ( $iso ) use ( $tz_obj ) {
    if ( ! $iso ) return '—';
    try { $dt = new DateTime( $iso ); $dt->setTimezone( $tz_obj ); return $dt->format( 'g:i A' );
    } catch ( Exception $e ) { return '—'; }
};

$now_ts = $is_today ? time() : 0;
$is_now = function( $start_iso, $end_iso ) use ( $now_ts ) {
    if ( ! $now_ts || ! $start_iso || ! $end_iso ) return false;
    try {
        $s = ( new DateTime( $start_iso ) )->getTimestamp();
        $e = ( new DateTime( $end_iso ) )->getTimestamp();
        return ( $now_ts >= $s && $now_ts < $e );
    } catch ( Exception $e ) { return false; }
};

/* Split into day / night */
$day_horas = array();
$night_horas = array();
if ( $hora && ! empty( $hora['hora_timing'] ) ) {
    foreach ( $hora['hora_timing'] as $h ) {
        if ( ! empty( $h['is_day'] ) ) $day_horas[] = $h;
        else $night_horas[] = $h;
    }
}

/* Find current hora */
$current = null;
if ( $is_today && $hora && ! empty( $hora['hora_timing'] ) ) {
    foreach ( $hora['hora_timing'] as $h ) {
        if ( $is_now( $h['start'] ?? null, $h['end'] ?? null ) ) { $current = $h; break; }
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
                <?php echo esc_html( $t( 'daily_hora' ) ); ?>
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

<?php if ( ! $hora || empty( $hora['hora_timing'] ) ) : ?>
    <section class="bb-section"><div class="bb-container" style="text-align:center;padding-block:3rem;">
        <h2><?php echo esc_html( $lang === 'hi' ? 'होरा अस्थायी रूप से अनुपलब्ध' : ( $lang === 'mr' ? 'होरा तात्पुरते अनुपलब्ध' : ( $lang === 'gu' ? 'હોરા હાલ ઉપલબ્ધ નથી' : 'Hora temporarily unavailable' ) ) ); ?></h2>
        <a class="bb-btn bb-btn--primary" href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( $lang === 'hi' ? 'पुनः प्रयास' : ( $lang === 'mr' ? 'पुन्हा प्रयत्न' : ( $lang === 'gu' ? 'ફરી પ્રયાસ કરો' : 'Try Again' ) ) ); ?></a>
    </div></section>
<?php else : ?>

<section class="bb-section bb-section--tight">
    <div class="bb-container">

        <?php /* ============== CURRENT HORA (only today) ============== */ ?>
        <?php if ( $current ) :
            $cur_pn = $current['hora']['name'] ?? '';
        ?>
        <div class="bb-cgh-current bb-hora-current <?php echo esc_attr( $pclass( $cur_pn ) ); ?>">
            <span class="bb-cgh-current__label"><?php echo esc_html( $t( 'current_hora' ) ); ?></span>
            <h2 class="bb-cgh-current__name">
                <?php echo esc_html( ( $planet_icons[ $cur_pn ] ?? '' ) . ' ' . $pname( $cur_pn ) ); ?>
            </h2>
            <p class="bb-cgh-current__type"><?php echo esc_html( $quality_label( $cur_pn ) ); ?></p>
            <p class="bb-cgh-current__time">
                <strong><?php echo esc_html( $nice_time( $current['start'] ) ); ?></strong>
                — <strong><?php echo esc_html( $nice_time( $current['end'] ) ); ?></strong>
            </p>
        </div>
        <?php endif; ?>

        <?php /* ============== DAY HORA ============== */ ?>
        <?php if ( ! empty( $day_horas ) ) : ?>
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow">☀️ <?php echo esc_html( $t( 'day_section_label' ) ); ?>
                <?php if ( $sunrise && $sunset ) : ?>
                    · <?php echo esc_html( $nice_time( $sunrise ) ); ?> – <?php echo esc_html( $nice_time( $sunset ) ); ?>
                <?php endif; ?>
            </span>
            <h2><?php echo esc_html( $t( 'day_hora' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>
        <div class="bb-cgh-grid">
            <?php foreach ( $day_horas as $i => $h ) :
                $pn = $h['hora']['name'] ?? '';
                $is_curr = $is_now( $h['start'] ?? null, $h['end'] ?? null );
            ?>
                <article class="bb-cgh-tile <?php echo esc_attr( $pclass( $pn ) ); ?><?php echo $is_curr ? ' bb-cgh-tile--now' : ''; ?>">
                    <span class="bb-cgh-tile__index"><?php echo (int) ( $i + 1 ); ?></span>
                    <h3 class="bb-cgh-tile__name">
                        <?php echo esc_html( ( $planet_icons[ $pn ] ?? '' ) . ' ' . $pname( $pn ) ); ?>
                    </h3>
                    <span class="bb-cgh-tile__type"><?php echo esc_html( $quality_label( $pn ) ); ?></span>
                    <span class="bb-cgh-tile__time">
                        <?php echo esc_html( $nice_time( $h['start'] ) ); ?>
                        <br>—<br>
                        <?php echo esc_html( $nice_time( $h['end'] ) ); ?>
                    </span>
                    <?php if ( $is_curr ) : ?>
                        <span class="bb-cgh-tile__now-badge"><?php echo esc_html( $t( 'now' ) ); ?></span>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php /* ============== NIGHT HORA ============== */ ?>
        <?php if ( ! empty( $night_horas ) ) : ?>
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow">🌙 <?php echo esc_html( $t( 'night_section_label' ) ); ?>
                <?php if ( $sunset ) : ?>
                    · <?php echo esc_html( $nice_time( $sunset ) ); ?> – <?php echo esc_html( $nice_time( $night_horas[ count( $night_horas ) - 1 ]['end'] ?? null ) ); ?>
                <?php endif; ?>
            </span>
            <h2><?php echo esc_html( $t( 'night_hora' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>
        <div class="bb-cgh-grid">
            <?php foreach ( $night_horas as $i => $h ) :
                $pn = $h['hora']['name'] ?? '';
                $is_curr = $is_now( $h['start'] ?? null, $h['end'] ?? null );
            ?>
                <article class="bb-cgh-tile <?php echo esc_attr( $pclass( $pn ) ); ?><?php echo $is_curr ? ' bb-cgh-tile--now' : ''; ?>">
                    <span class="bb-cgh-tile__index"><?php echo (int) ( $i + 1 ); ?></span>
                    <h3 class="bb-cgh-tile__name">
                        <?php echo esc_html( ( $planet_icons[ $pn ] ?? '' ) . ' ' . $pname( $pn ) ); ?>
                    </h3>
                    <span class="bb-cgh-tile__type"><?php echo esc_html( $quality_label( $pn ) ); ?></span>
                    <span class="bb-cgh-tile__time">
                        <?php echo esc_html( $nice_time( $h['start'] ) ); ?>
                        <br>—<br>
                        <?php echo esc_html( $nice_time( $h['end'] ) ); ?>
                    </span>
                    <?php if ( $is_curr ) : ?>
                        <span class="bb-cgh-tile__now-badge"><?php echo esc_html( $t( 'now' ) ); ?></span>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php /* Single ad slot — after main content, before related */ ?>
        <?php bb_ad_slot( BB_AD_SLOT_TOOL ); ?>

        <?php /* ============== RELATED — link to Panchang + Choghadiya ============== */ ?>
        <?php
        $panchang_urls = array(
            'en' => '/en/panchang/',           'hi' => '/hi/aaj-ka-panchang/',
            'mr' => '/mr/aajcha-panchang/',    'gu' => '/gu/aaj-no-panchang/',
        );
        $cgh_urls = array(
            'en' => '/en/choghadiya/',         'hi' => '/hi/aaj-ka-choghadiya/',
            'mr' => '/mr/aajcha-choghadiya/',  'gu' => '/gu/aaj-nu-choghadiya/',
        );
        $panchang_href = $panchang_urls[ $lang ] ?? $panchang_urls['en'];
        $cgh_href = $cgh_urls[ $lang ] ?? $cgh_urls['en'];
        ?>
        <div class="bb-cgh-related">
            <span class="bb-eyebrow"><?php echo esc_html( $t( 'related_label' ) ); ?></span>
            <div style="display:flex; flex-wrap:wrap; gap:0.75rem; justify-content:center;">
                <a class="bb-cgh-related-card" href="<?php echo esc_url( $panchang_href ); ?>">
                    <span class="bb-cgh-related-card__icon">📿</span>
                    <span class="bb-cgh-related-card__name"><?php echo esc_html( $t( 'related_panchang' ) ); ?></span>
                    <span class="bb-cgh-related-card__arrow">→</span>
                </a>
                <a class="bb-cgh-related-card" href="<?php echo esc_url( $cgh_href ); ?>">
                    <span class="bb-cgh-related-card__icon">⏳</span>
                    <span class="bb-cgh-related-card__name"><?php echo esc_html( $t( 'related_choghadiya' ) ); ?></span>
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
