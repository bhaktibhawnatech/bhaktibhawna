<?php
/**
 * Template Name: Panchang Tool
 * Daily Panchang via ProKerala. Drik-level depth, language-pure (no en/hi mix).
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
    wp_enqueue_style( 'bb-panchang', BB_URI . '/assets/css/panchang.css', array( 'bb-main' ), BB_VER );
    wp_enqueue_style( 'bb-choghadiya', BB_URI . '/assets/css/choghadiya.css', array( 'bb-panchang' ), BB_VER );
    wp_enqueue_script( 'bb-panchang', BB_URI . '/assets/js/panchang.js', array(), BB_VER, true );
}, 20 );

/* ----- Resolve location ----- */
$lang = bb_current_lang();   // 'en' | 'hi' | 'mr'
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

/* Helper: build URL preserving city, replacing date */
$build_url = function( $date = null ) use ( $selected_city_slug ) {
    $params = array( 'city' => $selected_city_slug );
    if ( $date ) $params['date'] = $date;
    return add_query_arg( $params, get_permalink() );
};

/* ----- Localized strings (LANGUAGE PURE — no mixing) ----- */
$T = array(
    'today'              => array( 'en' => 'Today',                    'hi' => 'आज',                       'mr' => 'आज',                       'gu' => 'આજે' ),
    'daily_panchang'     => array( 'en' => 'Daily Panchang',           'hi' => 'दैनिक पंचांग',              'mr' => 'दैनिक पंचांग',              'gu' => 'દૈનિક પંચાંગ' ),
    'devanagari_brand'   => array( 'en' => 'आज का पंचांग',              'hi' => 'आज का पंचांग',              'mr' => 'आजचा पंचांग',               'gu' => 'આજનો પંચાંગ' ),
    'panch_anga'         => array( 'en' => 'Five Elements',            'hi' => 'पंच अंग',                   'mr' => 'पंच अंग',                   'gu' => 'પંચ અંગ' ),
    'todays_panchang'    => array( 'en' => "Today's Panchang",         'hi' => 'आज का पंचांग',              'mr' => 'आजचा पंचांग',               'gu' => 'આજનો પંચાંગ' ),

    'tithi'              => array( 'en' => 'Tithi',                    'hi' => 'तिथि',                     'mr' => 'तिथी',                      'gu' => 'તિથિ' ),
    'nakshatra'          => array( 'en' => 'Nakshatra',                'hi' => 'नक्षत्र',                   'mr' => 'नक्षत्र',                   'gu' => 'નક્ષત્ર' ),
    'yoga'               => array( 'en' => 'Yoga',                     'hi' => 'योग',                      'mr' => 'योग',                       'gu' => 'યોગ' ),
    'karana'             => array( 'en' => 'Karana',                   'hi' => 'करण',                       'mr' => 'करण',                       'gu' => 'કરણ' ),
    'vaara'              => array( 'en' => 'Vaara',                    'hi' => 'वार',                       'mr' => 'वार',                       'gu' => 'વાર' ),

    'until'              => array( 'en' => 'until',                    'hi' => 'तक',                        'mr' => 'पर्यंत',                    'gu' => 'સુધી' ),
    'next'               => array( 'en' => 'Next',                     'hi' => 'अगला',                      'mr' => 'पुढील',                     'gu' => 'આગળ' ),
    'lord'               => array( 'en' => 'Lord',                     'hi' => 'स्वामी',                    'mr' => 'स्वामी',                    'gu' => 'સ્વામી' ),
    'day_of_week'        => array( 'en' => 'Day of week',              'hi' => 'सप्ताह का दिन',             'mr' => 'आठवड्याचा दिवस',            'gu' => 'અઠવાડિયાનો દિવસ' ),

    'sun_moon_label'     => array( 'en' => 'Sun &amp; Moon',           'hi' => 'सूर्य चन्द्र',              'mr' => 'सूर्य चंद्र',               'gu' => 'સૂર્ય ચંદ્ર' ),
    'sun_moon_heading'   => array( 'en' => 'Sunrise, Sunset &amp; Moon Timings', 'hi' => 'सूर्योदय, सूर्यास्त एवं चंद्र समय', 'mr' => 'सूर्योदय, सूर्यास्त आणि चंद्र वेळा', 'gu' => 'સૂર્યોદય, સૂર્યાસ્ત અને ચંદ્ર સમય' ),
    'sunrise'            => array( 'en' => 'Sunrise',                  'hi' => 'सूर्योदय',                  'mr' => 'सूर्योदय',                  'gu' => 'સૂર્યોદય' ),
    'sunset'             => array( 'en' => 'Sunset',                   'hi' => 'सूर्यास्त',                 'mr' => 'सूर्यास्त',                 'gu' => 'સૂર્યાસ્ત' ),
    'moonrise'           => array( 'en' => 'Moonrise',                 'hi' => 'चंद्रोदय',                  'mr' => 'चंद्रोदय',                  'gu' => 'ચંદ્રોદય' ),
    'moonset'            => array( 'en' => 'Moonset',                  'hi' => 'चंद्रास्त',                 'mr' => 'चंद्रास्त',                 'gu' => 'ચંદ્રાસ્ત' ),

    'duration_label'     => array( 'en' => 'Duration &amp; Madhyahna', 'hi' => 'समय अवधि एवं मध्याह्न',     'mr' => 'कालावधी आणि मध्याह्न',       'gu' => 'સમય અવધિ અને મધ્યાહ્ન' ),
    'duration_heading'   => array( 'en' => 'Day &amp; Night Duration', 'hi' => 'दिन और रात की अवधि',        'mr' => 'दिवस आणि रात्रीची कालावधी', 'gu' => 'દિવસ અને રાત્રિની અવધિ' ),
    'day_duration'       => array( 'en' => 'Day Duration',             'hi' => 'दिनमान',                    'mr' => 'दिनमान',                    'gu' => 'દિનમાન' ),
    'night_duration'     => array( 'en' => 'Night Duration',           'hi' => 'रात्रिमान',                 'mr' => 'रात्रीमान',                 'gu' => 'રાત્રિમાન' ),
    'madhyahna'          => array( 'en' => 'Madhyahna (Midday)',       'hi' => 'मध्याह्न',                  'mr' => 'मध्याह्न',                  'gu' => 'મધ્યાહ્ન' ),

    'samvat_label'       => array( 'en' => 'Hindu Calendar',           'hi' => 'हिन्दू कैलेंडर',            'mr' => 'हिंदू कॅलेंडर',             'gu' => 'હિંદુ કેલેન્ડર' ),
    'samvat_heading'     => array( 'en' => 'Hindu Months &amp; Years', 'hi' => 'हिन्दू मास एवं वर्ष',       'mr' => 'हिंदू मास आणि वर्ष',         'gu' => 'હિંદુ માસ અને વર્ષ' ),
    'vikram_samvat'      => array( 'en' => 'Vikram Samvat',            'hi' => 'विक्रम संवत',               'mr' => 'विक्रम संवत',                'gu' => 'વિક્રમ સંવત' ),
    'shaka_samvat'       => array( 'en' => 'Shaka Samvat',             'hi' => 'शक संवत',                   'mr' => 'शक संवत',                    'gu' => 'શક સંવત' ),
    'chandra_masa'       => array( 'en' => 'Chandra Masa',             'hi' => 'चंद्र मास',                 'mr' => 'चंद्र मास',                  'gu' => 'ચંદ્ર માસ' ),
    'kaliyuga'           => array( 'en' => 'Kaliyuga Year',            'hi' => 'कलियुग वर्ष',               'mr' => 'कलियुग वर्ष',                'gu' => 'કલિયુગ વર્ષ' ),

    'season_label'       => array( 'en' => 'Season &amp; Ayana',       'hi' => 'ऋतु एवं अयन',               'mr' => 'ऋतू आणि अयन',                'gu' => 'ઋતુ અને અયન' ),
    'season_heading'     => array( 'en' => 'Drik Ritu, Vedic Ritu &amp; Ayana', 'hi' => 'दृक ऋतु, वैदिक ऋतु एवं अयन', 'mr' => 'दृक ऋतू, वैदिक ऋतू आणि अयन', 'gu' => 'દૃક ઋતુ, વૈદિક ઋતુ અને અયન' ),
    'drik_ritu'          => array( 'en' => 'Drik Ritu',                'hi' => 'दृक ऋतु',                   'mr' => 'दृक ऋतू',                    'gu' => 'દૃક ઋતુ' ),
    'vedic_ritu'         => array( 'en' => 'Vedic Ritu',               'hi' => 'वैदिक ऋतु',                 'mr' => 'वैदिक ऋतू',                  'gu' => 'વૈદિક ઋતુ' ),
    'ayana'              => array( 'en' => 'Ayana',                    'hi' => 'अयन',                       'mr' => 'अयन',                        'gu' => 'અયન' ),

    'auspicious_label'   => array( 'en' => 'Auspicious',               'hi' => 'शुभ मुहूर्त',               'mr' => 'शुभ मुहूर्त',                'gu' => 'શુભ મુહૂર્ત' ),
    'auspicious_heading' => array( 'en' => 'Auspicious Timings',       'hi' => 'शुभ समय',                   'mr' => 'शुभ वेळा',                   'gu' => 'શુભ સમય' ),
    'inauspicious_label' => array( 'en' => 'Inauspicious',             'hi' => 'अशुभ समय',                  'mr' => 'अशुभ वेळा',                  'gu' => 'અશુભ સમય' ),
    'inauspicious_heading'=> array('en' => 'Inauspicious Timings',     'hi' => 'अशुभ समय',                  'mr' => 'अशुभ वेळा',                  'gu' => 'અશુભ સમય' ),

    'location'           => array( 'en' => 'Location',                 'hi' => 'स्थान',                     'mr' => 'स्थान',                      'gu' => 'સ્થાન' ),
    'change_city'        => array( 'en' => 'Change city',              'hi' => 'शहर बदलें',                 'mr' => 'शहर बदला',                   'gu' => 'શહેર બદલો' ),
    'go'                 => array( 'en' => 'Go',                       'hi' => 'जाएं',                      'mr' => 'जा',                         'gu' => 'જાઓ' ),

    'previous_day'       => array( 'en' => 'Previous Day',             'hi' => 'पिछला दिन',                 'mr' => 'मागील दिवस',                 'gu' => 'આગલો દિવસ' ),
    'today_btn'          => array( 'en' => 'Today',                    'hi' => 'आज',                        'mr' => 'आज',                         'gu' => 'આજે' ),
    'next_day'           => array( 'en' => 'Next Day',                 'hi' => 'अगला दिन',                  'mr' => 'पुढील दिवस',                 'gu' => 'આગામી દિવસ' ),
    'pick_date'          => array( 'en' => 'Pick a date',              'hi' => 'तारीख चुनें',               'mr' => 'दिनांक निवडा',                'gu' => 'તારીખ પસંદ કરો' ),

    'chandra_bala_label' => array( 'en' => 'Moon Strength',            'hi' => 'चंद्रबल',                    'mr' => 'चंद्रबल',                     'gu' => 'ચંદ્રબળ' ),
    'chandra_bala_heading' => array( 'en' => 'Chandra Bala — Favorable Moon Signs', 'hi' => 'चंद्रबल — शुभ चंद्र राशियाँ', 'mr' => 'चंद्रबल — शुभ चंद्र राशी', 'gu' => 'ચંદ્રબળ — શુભ ચંદ્ર રાશિઓ' ),
    'tara_bala_label'    => array( 'en' => 'Star Strength',            'hi' => 'ताराबलम्',                   'mr' => 'ताराबलम्',                    'gu' => 'તારાબળ' ),
    'tara_bala_heading'  => array( 'en' => 'Tara Bala — Favorable Nakshatras', 'hi' => 'ताराबलम् — शुभ नक्षत्र', 'mr' => 'ताराबलम् — शुभ नक्षत्र', 'gu' => 'તારાબળ — શુભ નક્ષત્ર' ),
    'favorable_until'    => array( 'en' => 'Favorable until',          'hi' => 'शुभ',                        'mr' => 'शुभ',                          'gu' => 'શુભ' ),

    'about_heading'      => array( 'en' => 'What is Panchang?',        'hi' => 'पंचांग क्या है?',           'mr' => 'पंचांग म्हणजे काय?',         'gu' => 'પંચાંગ શું છે?' ),
    'about_text'         => array(
        'en' => 'Panchang is a Hindu calendar that tracks five key elements of time: Tithi (lunar day), Nakshatra (constellation), Yoga (auspicious combination), Karana (half-tithi) and Vaara (weekday). Daily panchang is consulted for choosing auspicious times for ceremonies, travel, and daily activities. Each value changes with location and time, computed precisely from the position of the Sun and Moon.',
        'hi' => 'पंचांग एक हिन्दू कैलेंडर है जो समय के पाँच प्रमुख अंगों को दर्शाता है: तिथि (चंद्र दिवस), नक्षत्र, योग (शुभ संयोग), करण (आधी तिथि) और वार (सप्ताह का दिन)। दैनिक पंचांग का प्रयोग शुभ कार्यों, यात्रा और दैनिक गतिविधियों के लिए मुहूर्त निकालने में होता है। यह मान सूर्य और चंद्रमा की स्थिति के अनुसार स्थान और समय के साथ बदलते हैं।',
        'mr' => 'पंचांग हे हिंदू कॅलेंडर आहे जे काळाच्या पाच प्रमुख अंगांचा मागोवा घेते: तिथी (चंद्र दिवस), नक्षत्र, योग, करण आणि वार. दैनिक पंचांगाचा वापर शुभ कार्ये, प्रवास आणि दैनंदिन क्रियांसाठी मुहूर्त शोधण्यासाठी केला जातो. ही मूल्ये सूर्य आणि चंद्राच्या स्थानानुसार बदलतात.',
        'gu' => 'પંચાંગ એ હિંદુ કેલેન્ડર છે જે સમયના પાંચ મુખ્ય અંગોને દર્શાવે છે: તિથિ (ચંદ્ર દિવસ), નક્ષત્ર, યોગ (શુભ સંયોગ), કરણ (અડધી તિથિ) અને વાર (અઠવાડિયાનો દિવસ). દૈનિક પંચાંગનો ઉપયોગ શુભ કાર્યો, મુસાફરી અને દૈનિક પ્રવૃત્તિઓ માટે મુહૂર્ત શોધવા માટે થાય છે.',
    ),
);
$t = function( $key ) use ( $T, $lang ) { return $T[ $key ][ $lang ] ?? $T[ $key ]['en'] ?? ''; };

/* ----- API calls (date-aware) ----- */
$api_args = array(
    'lat' => $lat, 'lng' => $lng, 'tz' => $tz, 'bb_lang' => $lang,
    'date' => $current_date_str,
);
$panchang     = BB_Prokerala_API::panchang( $api_args );
$ritu         = BB_Prokerala_API::ritu( $api_args );
$chandra_bala = BB_Prokerala_API::chandra_bala( $api_args );
$tara_bala    = BB_Prokerala_API::tara_bala( $api_args );

get_header();

/* ----- Render helpers ----- */
$tz_obj = new DateTimeZone( $tz ?: 'Asia/Kolkata' );
$nice_time = function ( $iso ) use ( $tz_obj ) {
    if ( ! $iso ) return '—';
    try { $dt = new DateTime( $iso ); $dt->setTimezone( $tz_obj ); return $dt->format( 'g:i A' );
    } catch ( Exception $e ) { return '—'; }
};
$first = function ( $arr ) { return is_array( $arr ) && isset( $arr[0] ) ? $arr[0] : null; };

/* Calculated fields */
$sunrise  = $panchang['sunrise']  ?? null;
$sunset   = $panchang['sunset']   ?? null;
$moonrise = $panchang['moonrise'] ?? null;
$moonset  = $panchang['moonset']  ?? null;

$day_dur  = bb_day_duration( $sunrise, $sunset );
$nite_dur = bb_night_duration( $sunrise, $sunset );
$mid      = bb_madhyahna( $sunrise, $sunset, $tz );

$vs    = bb_vikram_samvat();
$ss    = bb_shaka_samvat();
$ky    = bb_kaliyuga_year();
$cmasa = bb_chandra_masa( $lang );
$svat  = bb_samvatsara( $vs );

/* Translated muhurat names per language */
$muhurat_names = array(
    'Abhijit Muhurat' => array( 'en' => 'Abhijit Muhurat', 'hi' => 'अभिजित मुहूर्त', 'mr' => 'अभिजित मुहूर्त', 'gu' => 'અભિજિત મુહૂર્ત' ),
    'Amrit Kaal'      => array( 'en' => 'Amrit Kaal',      'hi' => 'अमृत काल',       'mr' => 'अमृत काळ',        'gu' => 'અમૃત કાળ' ),
    'Brahma Muhurat'  => array( 'en' => 'Brahma Muhurat',  'hi' => 'ब्रह्म मुहूर्त', 'mr' => 'ब्रह्म मुहूर्त',  'gu' => 'બ્રહ્મ મુહૂર્ત' ),
    'Rahu'            => array( 'en' => 'Rahu Kaal',       'hi' => 'राहु काल',       'mr' => 'राहू काळ',         'gu' => 'રાહુ કાળ' ),
    'Yamaganda'       => array( 'en' => 'Yamaganda',       'hi' => 'यमगंड',          'mr' => 'यमगंड',            'gu' => 'યમગંડ' ),
    'Gulika'          => array( 'en' => 'Gulika Kaal',     'hi' => 'गुलिक काल',      'mr' => 'गुलिक काळ',        'gu' => 'ગુલિક કાળ' ),
    'Dur Muhurat'     => array( 'en' => 'Dur Muhurat',     'hi' => 'दुर्मुहूर्त',    'mr' => 'दुर्मुहूर्त',      'gu' => 'દુર્મુહૂર્ત' ),
    'Varjyam'         => array( 'en' => 'Varjyam',         'hi' => 'वर्ज्यम्',       'mr' => 'वर्ज्यम्',         'gu' => 'વર્જ્યમ્' ),
);
$mname = function( $en ) use ( $muhurat_names, $lang ) {
    return $muhurat_names[ $en ][ $lang ] ?? $en;
};
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
                <?php echo esc_html( $t( 'daily_panchang' ) ); ?>
            </h1>
            <p class="bb-panchang-hero__sub">📍 <?php echo esc_html( $location_label ); ?></p>
        </div>
    </div>
</section>

<!-- ============== CITY + DATE NAVIGATION ============== -->
<section class="bb-toolbar">
    <div class="bb-container">
        <div class="bb-toolbar__row">
            <!-- City -->
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

            <!-- Date navigation -->
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

<?php if ( ! $panchang ) : ?>
    <section class="bb-section"><div class="bb-container" style="text-align:center;padding-block:3rem;">
        <h2><?php echo esc_html( $lang === 'hi' ? 'पंचांग अस्थायी रूप से अनुपलब्ध' : ( $lang === 'mr' ? 'पंचांग तात्पुरते अनुपलब्ध' : 'Panchang temporarily unavailable' ) ); ?></h2>
        <a class="bb-btn bb-btn--primary" href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( $lang === 'hi' ? 'पुनः प्रयास' : ( $lang === 'mr' ? 'पुन्हा प्रयत्न' : 'Try Again' ) ); ?></a>
    </div></section>
<?php else :

    $tithi      = $first( $panchang['tithi'] ?? array() );
    $tithi_next = isset( $panchang['tithi'][1] ) ? $panchang['tithi'][1] : null;
    $nakshatra  = $first( $panchang['nakshatra'] ?? array() );
    $nakshatra_next = isset( $panchang['nakshatra'][1] ) ? $panchang['nakshatra'][1] : null;
    $yoga       = $first( $panchang['yoga'] ?? array() );
    $karana     = $first( $panchang['karana'] ?? array() );
    $vaara      = $panchang['vaara'] ?? '';
    $auspicious = $panchang['auspicious_period'] ?? array();
    $inauspicious = $panchang['inauspicious_period'] ?? array();
?>

<section class="bb-section bb-section--tight">
    <div class="bb-container">

        <!-- ============== 5 PANCHANG ELEMENTS ============== -->
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow"><?php echo esc_html( $t( 'panch_anga' ) ); ?></span>
            <h2><?php echo esc_html( $t( 'todays_panchang' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>

        <div class="bb-panchang-grid">
            <article class="bb-panchang-tile">
                <span class="bb-panchang-tile__label"><?php echo esc_html( $t( 'tithi' ) ); ?></span>
                <h3 class="bb-panchang-tile__value"><?php echo esc_html( $tithi['name'] ?? '—' ); ?></h3>
                <span class="bb-panchang-tile__meta">
                    <?php if ( ! empty( $tithi['paksha'] ) ) echo esc_html( $tithi['paksha'] ) . '<br>'; ?>
                    <?php echo esc_html( $t( 'until' ) ); ?>
                    <strong><?php echo esc_html( $nice_time( $tithi['end'] ?? null ) ); ?></strong>
                </span>
                <?php if ( $tithi_next ) : ?>
                    <span class="bb-panchang-tile__next"><?php echo esc_html( $t( 'next' ) ); ?>: <?php echo esc_html( $tithi_next['name'] ); ?></span>
                <?php endif; ?>
            </article>

            <article class="bb-panchang-tile">
                <span class="bb-panchang-tile__label"><?php echo esc_html( $t( 'nakshatra' ) ); ?></span>
                <h3 class="bb-panchang-tile__value"><?php echo esc_html( $nakshatra['name'] ?? '—' ); ?></h3>
                <span class="bb-panchang-tile__meta">
                    <?php if ( ! empty( $nakshatra['lord']['name'] ) ) echo esc_html( $t( 'lord' ) . ': ' . $nakshatra['lord']['name'] ) . '<br>'; ?>
                    <?php echo esc_html( $t( 'until' ) ); ?>
                    <strong><?php echo esc_html( $nice_time( $nakshatra['end'] ?? null ) ); ?></strong>
                </span>
                <?php if ( $nakshatra_next ) : ?>
                    <span class="bb-panchang-tile__next"><?php echo esc_html( $t( 'next' ) ); ?>: <?php echo esc_html( $nakshatra_next['name'] ); ?></span>
                <?php endif; ?>
            </article>

            <article class="bb-panchang-tile">
                <span class="bb-panchang-tile__label"><?php echo esc_html( $t( 'yoga' ) ); ?></span>
                <h3 class="bb-panchang-tile__value"><?php echo esc_html( $yoga['name'] ?? '—' ); ?></h3>
                <span class="bb-panchang-tile__meta">
                    <?php echo esc_html( $t( 'until' ) ); ?>
                    <strong><?php echo esc_html( $nice_time( $yoga['end'] ?? null ) ); ?></strong>
                </span>
            </article>

            <article class="bb-panchang-tile">
                <span class="bb-panchang-tile__label"><?php echo esc_html( $t( 'karana' ) ); ?></span>
                <h3 class="bb-panchang-tile__value"><?php echo esc_html( $karana['name'] ?? '—' ); ?></h3>
                <span class="bb-panchang-tile__meta">
                    <?php echo esc_html( $t( 'until' ) ); ?>
                    <strong><?php echo esc_html( $nice_time( $karana['end'] ?? null ) ); ?></strong>
                </span>
            </article>

            <article class="bb-panchang-tile">
                <span class="bb-panchang-tile__label"><?php echo esc_html( $t( 'vaara' ) ); ?></span>
                <h3 class="bb-panchang-tile__value"><?php echo esc_html( $vaara ?: '—' ); ?></h3>
                <span class="bb-panchang-tile__meta"><?php echo esc_html( $t( 'day_of_week' ) ); ?></span>
            </article>
        </div>

        <!-- ============== SUN & MOON ============== -->
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow"><?php echo esc_html( $t( 'sun_moon_label' ) ); ?></span>
            <h2><?php echo wp_kses_post( $t( 'sun_moon_heading' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>

        <div class="bb-celestial-grid">
            <div class="bb-celestial-fact"><span class="bb-celestial-fact__icon">☀️</span><span class="bb-celestial-fact__label"><?php echo esc_html( $t( 'sunrise' ) ); ?></span><span class="bb-celestial-fact__value"><?php echo esc_html( $nice_time( $sunrise ) ); ?></span></div>
            <div class="bb-celestial-fact"><span class="bb-celestial-fact__icon">🌅</span><span class="bb-celestial-fact__label"><?php echo esc_html( $t( 'sunset' ) ); ?></span><span class="bb-celestial-fact__value"><?php echo esc_html( $nice_time( $sunset ) ); ?></span></div>
            <div class="bb-celestial-fact"><span class="bb-celestial-fact__icon">🌙</span><span class="bb-celestial-fact__label"><?php echo esc_html( $t( 'moonrise' ) ); ?></span><span class="bb-celestial-fact__value"><?php echo esc_html( $nice_time( $moonrise ) ); ?></span></div>
            <div class="bb-celestial-fact"><span class="bb-celestial-fact__icon">🌃</span><span class="bb-celestial-fact__label"><?php echo esc_html( $t( 'moonset' ) ); ?></span><span class="bb-celestial-fact__value"><?php echo esc_html( $nice_time( $moonset ) ); ?></span></div>
        </div>

        <!-- ============== DAY/NIGHT DURATION ============== -->
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow"><?php echo esc_html( $t( 'duration_label' ) ); ?></span>
            <h2><?php echo esc_html( $t( 'duration_heading' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>

        <div class="bb-duration-grid">
            <div class="bb-celestial-fact"><span class="bb-celestial-fact__icon">⏱️</span><span class="bb-celestial-fact__label"><?php echo esc_html( $t( 'day_duration' ) ); ?></span><span class="bb-celestial-fact__value"><?php echo esc_html( $day_dur ); ?></span></div>
            <div class="bb-celestial-fact"><span class="bb-celestial-fact__icon">🌌</span><span class="bb-celestial-fact__label"><?php echo esc_html( $t( 'night_duration' ) ); ?></span><span class="bb-celestial-fact__value"><?php echo esc_html( $nite_dur ); ?></span></div>
            <div class="bb-celestial-fact"><span class="bb-celestial-fact__icon">🕛</span><span class="bb-celestial-fact__label"><?php echo esc_html( $t( 'madhyahna' ) ); ?></span><span class="bb-celestial-fact__value"><?php echo $mid ? esc_html( $mid->format( 'g:i A' ) ) : '—'; ?></span></div>
        </div>

        <!-- ============== HINDU CALENDAR ============== -->
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow"><?php echo esc_html( $t( 'samvat_label' ) ); ?></span>
            <h2><?php echo wp_kses_post( $t( 'samvat_heading' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>

        <div class="bb-samvat-grid">
            <div class="bb-samvat-item"><span class="bb-samvat-item__label"><?php echo esc_html( $t( 'vikram_samvat' ) ); ?></span><span class="bb-samvat-item__value"><?php echo esc_html( $vs ); ?> <small style="font-weight:400; color:var(--bb-muted);"><?php echo esc_html( $svat ); ?></small></span></div>
            <div class="bb-samvat-item"><span class="bb-samvat-item__label"><?php echo esc_html( $t( 'shaka_samvat' ) ); ?></span><span class="bb-samvat-item__value"><?php echo esc_html( $ss ); ?></span></div>
            <div class="bb-samvat-item"><span class="bb-samvat-item__label"><?php echo esc_html( $t( 'chandra_masa' ) ); ?></span><span class="bb-samvat-item__value"><?php echo esc_html( $cmasa ); ?></span></div>
            <div class="bb-samvat-item"><span class="bb-samvat-item__label"><?php echo esc_html( $t( 'kaliyuga' ) ); ?></span><span class="bb-samvat-item__value"><?php echo esc_html( $ky ); ?></span></div>
        </div>

        <!-- ============== SEASON / RITU ============== -->
        <?php if ( $ritu ) : ?>
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow"><?php echo esc_html( $t( 'season_label' ) ); ?></span>
            <h2><?php echo wp_kses_post( $t( 'season_heading' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>
        <div class="bb-samvat-grid">
            <?php if ( ! empty( $ritu['drik_ritu']['name'] ) ) : ?>
                <div class="bb-samvat-item"><span class="bb-samvat-item__label"><?php echo esc_html( $t( 'drik_ritu' ) ); ?></span><span class="bb-samvat-item__value"><?php echo esc_html( $ritu['drik_ritu']['name'] ); ?></span></div>
            <?php endif; ?>
            <?php if ( ! empty( $ritu['vedic_ritu']['name'] ) ) : ?>
                <div class="bb-samvat-item"><span class="bb-samvat-item__label"><?php echo esc_html( $t( 'vedic_ritu' ) ); ?></span><span class="bb-samvat-item__value"><?php echo esc_html( $ritu['vedic_ritu']['name'] ); ?></span></div>
            <?php endif; ?>
            <?php
            // Ayana — derive from current month: Uttarayan (Jan-Jun), Dakshinayan (Jul-Dec)
            $ayana_month = (int) date( 'n' );
            $ayana_names = array(
                'en' => array( 'uttarayan' => 'Uttarayan', 'dakshinayan' => 'Dakshinayan' ),
                'hi' => array( 'uttarayan' => 'उत्तरायण', 'dakshinayan' => 'दक्षिणायन' ),
                'mr' => array( 'uttarayan' => 'उत्तरायण', 'dakshinayan' => 'दक्षिणायन' ),
                'gu' => array( 'uttarayan' => 'ઉત્તરાયણ', 'dakshinayan' => 'દક્ષિણાયન' ),
            );
            $ayana_key = ( $ayana_month >= 1 && $ayana_month <= 6 ) ? 'uttarayan' : 'dakshinayan';
            $ayana_label = $ayana_names[ $lang ][ $ayana_key ] ?? $ayana_names['en'][ $ayana_key ];
            ?>
            <div class="bb-samvat-item"><span class="bb-samvat-item__label"><?php echo esc_html( $t( 'ayana' ) ); ?></span><span class="bb-samvat-item__value"><?php echo esc_html( $ayana_label ); ?></span></div>
        </div>
        <?php endif; ?>

        <!-- ============== AUSPICIOUS ============== -->
        <?php if ( ! empty( $auspicious ) ) : ?>
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow" style="color:var(--bb-gold);"><?php echo esc_html( $t( 'auspicious_label' ) ); ?></span>
            <h2><?php echo esc_html( $t( 'auspicious_heading' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>
        <div class="bb-muhurat-grid">
            <?php foreach ( $auspicious as $m ) : ?>
                <div class="bb-muhurat-item bb-muhurat-item--good">
                    <div class="bb-muhurat-item__head">
                        <span class="bb-muhurat-item__icon">✨</span>
                        <h3 class="bb-muhurat-item__name"><?php echo esc_html( $mname( $m['name'] ) ); ?></h3>
                    </div>
                    <div class="bb-muhurat-item__times">
                        <?php foreach ( $m['period'] ?? array() as $p ) : ?>
                            <span class="bb-muhurat-item__time">
                                <strong><?php echo esc_html( $nice_time( $p['start'] ?? null ) ); ?></strong>
                                — <?php echo esc_html( $nice_time( $p['end'] ?? null ) ); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ============== INAUSPICIOUS ============== -->
        <?php if ( ! empty( $inauspicious ) ) : ?>
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow" style="color:var(--bb-sindoori-dark);"><?php echo esc_html( $t( 'inauspicious_label' ) ); ?></span>
            <h2><?php echo esc_html( $t( 'inauspicious_heading' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>
        <div class="bb-muhurat-grid">
            <?php foreach ( $inauspicious as $m ) : ?>
                <div class="bb-muhurat-item bb-muhurat-item--bad">
                    <div class="bb-muhurat-item__head">
                        <span class="bb-muhurat-item__icon">⚠️</span>
                        <h3 class="bb-muhurat-item__name"><?php echo esc_html( $mname( $m['name'] ) ); ?></h3>
                    </div>
                    <div class="bb-muhurat-item__times">
                        <?php foreach ( $m['period'] ?? array() as $p ) : ?>
                            <span class="bb-muhurat-item__time">
                                <strong><?php echo esc_html( $nice_time( $p['start'] ?? null ) ); ?></strong>
                                — <?php echo esc_html( $nice_time( $p['end'] ?? null ) ); ?>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ============== CHANDRA BALA ============== -->
        <?php if ( $chandra_bala && ! empty( $chandra_bala['chandra_bala'] ) ) : ?>
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow"><?php echo esc_html( $t( 'chandra_bala_label' ) ); ?></span>
            <h2><?php echo wp_kses_post( $t( 'chandra_bala_heading' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>
        <div class="bb-bala-list">
            <?php foreach ( $chandra_bala['chandra_bala'] as $period ) : ?>
                <div class="bb-bala-period">
                    <div class="bb-bala-period__time">
                        <span class="bb-bala-period__until"><?php echo esc_html( $t( 'favorable_until' ) ); ?></span>
                        <strong><?php echo esc_html( $nice_time( $period['end'] ?? null ) ); ?></strong>
                    </div>
                    <div class="bb-bala-period__items">
                        <?php foreach ( $period['rasis'] ?? array() as $rasi ) : ?>
                            <span class="bb-bala-chip"><?php echo esc_html( $rasi['name'] ?? '' ); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- ============== TARA BALA ============== -->
        <?php if ( $tara_bala && ! empty( $tara_bala['tara_bala'] ) ) : ?>
        <div class="bb-panchang-section-head">
            <span class="bb-eyebrow"><?php echo esc_html( $t( 'tara_bala_label' ) ); ?></span>
            <h2><?php echo wp_kses_post( $t( 'tara_bala_heading' ) ); ?></h2>
            <div class="bb-mandala-divider"></div>
        </div>
        <div class="bb-bala-list">
            <?php foreach ( $tara_bala['tara_bala'] as $period ) :
                $type = $period['type'] ?? 'Good';
                $type_class = ( $type === 'Bad' ) ? 'bb-bala-period--bad' : ( ( $type === 'Good' ) ? 'bb-bala-period--good' : 'bb-bala-period--mid' );
            ?>
                <div class="bb-bala-period <?php echo esc_attr( $type_class ); ?>">
                    <div class="bb-bala-period__time">
                        <span class="bb-bala-period__name"><?php echo esc_html( $period['name'] ?? '' ); ?></span>
                        <span class="bb-bala-period__until"><?php echo esc_html( $t( 'favorable_until' ) ); ?></span>
                        <strong><?php echo esc_html( $nice_time( $period['end'] ?? null ) ); ?></strong>
                    </div>
                    <div class="bb-bala-period__items">
                        <?php foreach ( $period['nakshatras'] ?? array() as $n ) : ?>
                            <span class="bb-bala-chip"><?php echo esc_html( $n['name'] ?? '' ); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <?php /* Single ad slot — placed after data sections, before related cross-links */ ?>
        <?php bb_ad_slot( BB_AD_SLOT_TOOL ); ?>

        <!-- ============== RELATED — link to Choghadiya + Hora ============== -->
        <?php
        $cgh_urls = array(
            'en' => '/en/choghadiya/',         'hi' => '/hi/aaj-ka-choghadiya/',
            'mr' => '/mr/aajcha-choghadiya/',  'gu' => '/gu/aaj-nu-choghadiya/',
        );
        $hora_urls = array(
            'en' => '/en/hora/',               'hi' => '/hi/aaj-ka-hora/',
            'mr' => '/mr/aajcha-hora/',        'gu' => '/gu/aaj-no-hora/',
        );
        $cgh_href  = $cgh_urls[ $lang ]  ?? $cgh_urls['en'];
        $hora_href = $hora_urls[ $lang ] ?? $hora_urls['en'];
        $rel_label = array(
            'en' => array( 'see' => 'Also See',     'cgh' => 'Daily Choghadiya', 'hora' => 'Daily Hora' ),
            'hi' => array( 'see' => 'यह भी देखें',   'cgh' => 'दैनिक चौघड़िया',     'hora' => 'दैनिक होरा' ),
            'mr' => array( 'see' => 'हे देखील पाहा', 'cgh' => 'दैनिक चौघडिया',     'hora' => 'दैनिक होरा' ),
            'gu' => array( 'see' => 'આ પણ જુઓ',     'cgh' => 'દૈનિક ચોઘડિયું',    'hora' => 'દૈનિક હોરા' ),
        );
        $rl = $rel_label[ $lang ] ?? $rel_label['en'];
        ?>
        <div class="bb-cgh-related">
            <span class="bb-eyebrow"><?php echo esc_html( $rl['see'] ); ?></span>
            <div style="display:flex; flex-wrap:wrap; gap:0.75rem; justify-content:center;">
                <a class="bb-cgh-related-card" href="<?php echo esc_url( $cgh_href ); ?>">
                    <span class="bb-cgh-related-card__icon">⏳</span>
                    <span class="bb-cgh-related-card__name"><?php echo esc_html( $rl['cgh'] ); ?></span>
                    <span class="bb-cgh-related-card__arrow">→</span>
                </a>
                <a class="bb-cgh-related-card" href="<?php echo esc_url( $hora_href ); ?>">
                    <span class="bb-cgh-related-card__icon">🪐</span>
                    <span class="bb-cgh-related-card__name"><?php echo esc_html( $rl['hora'] ); ?></span>
                    <span class="bb-cgh-related-card__arrow">→</span>
                </a>
            </div>
        </div>

        <!-- ============== ABOUT (SEO content) ============== -->
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
