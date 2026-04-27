<?php
/**
 * Tool helpers — calculated fields, city list, language strings.
 */
if ( ! defined( 'ABSPATH' ) ) exit;

/* ---------------------------------------------------------------------
 * Popular Indian cities for tool location selector
 * Coordinates: lat, lng, IANA timezone (mostly Asia/Kolkata)
 * ------------------------------------------------------------------- */
function bb_popular_cities() {
    return array(
        'new-delhi'    => array( 'name_en' => 'New Delhi',    'name_hi' => 'नई दिल्ली',  'name_mr' => 'नवी दिल्ली',  'name_gu' => 'નવી દિલ્હી',  'lat' => 28.6139, 'lng' => 77.2090, 'tz' => 'Asia/Kolkata' ),
        'mumbai'       => array( 'name_en' => 'Mumbai',       'name_hi' => 'मुंबई',       'name_mr' => 'मुंबई',       'name_gu' => 'મુંબઈ',         'lat' => 19.0760, 'lng' => 72.8777, 'tz' => 'Asia/Kolkata' ),
        'bengaluru'    => array( 'name_en' => 'Bengaluru',    'name_hi' => 'बेंगलुरु',    'name_mr' => 'बंगळुरु',     'name_gu' => 'બેંગલુરુ',       'lat' => 12.9716, 'lng' => 77.5946, 'tz' => 'Asia/Kolkata' ),
        'kolkata'      => array( 'name_en' => 'Kolkata',      'name_hi' => 'कोलकाता',     'name_mr' => 'कोलकाता',     'name_gu' => 'કોલકાતા',       'lat' => 22.5726, 'lng' => 88.3639, 'tz' => 'Asia/Kolkata' ),
        'chennai'      => array( 'name_en' => 'Chennai',      'name_hi' => 'चेन्नई',      'name_mr' => 'चेन्नई',      'name_gu' => 'ચેન્નઈ',         'lat' => 13.0827, 'lng' => 80.2707, 'tz' => 'Asia/Kolkata' ),
        'hyderabad'    => array( 'name_en' => 'Hyderabad',    'name_hi' => 'हैदराबाद',    'name_mr' => 'हैदराबाद',    'name_gu' => 'હૈદરાબાદ',       'lat' => 17.3850, 'lng' => 78.4867, 'tz' => 'Asia/Kolkata' ),
        'pune'         => array( 'name_en' => 'Pune',         'name_hi' => 'पुणे',        'name_mr' => 'पुणे',        'name_gu' => 'પુણે',          'lat' => 18.5204, 'lng' => 73.8567, 'tz' => 'Asia/Kolkata' ),
        'ahmedabad'    => array( 'name_en' => 'Ahmedabad',    'name_hi' => 'अहमदाबाद',    'name_mr' => 'अहमदाबाद',    'name_gu' => 'અમદાવાદ',       'lat' => 23.0225, 'lng' => 72.5714, 'tz' => 'Asia/Kolkata' ),
        'jaipur'       => array( 'name_en' => 'Jaipur',       'name_hi' => 'जयपुर',       'name_mr' => 'जयपूर',       'name_gu' => 'જયપુર',         'lat' => 26.9124, 'lng' => 75.7873, 'tz' => 'Asia/Kolkata' ),
        'lucknow'      => array( 'name_en' => 'Lucknow',      'name_hi' => 'लखनऊ',        'name_mr' => 'लखनौ',        'name_gu' => 'લખનૌ',          'lat' => 26.8467, 'lng' => 80.9462, 'tz' => 'Asia/Kolkata' ),
        'kanpur'       => array( 'name_en' => 'Kanpur',       'name_hi' => 'कानपुर',      'name_mr' => 'कानपूर',      'name_gu' => 'કાનપુર',        'lat' => 26.4499, 'lng' => 80.3319, 'tz' => 'Asia/Kolkata' ),
        'nagpur'       => array( 'name_en' => 'Nagpur',       'name_hi' => 'नागपुर',      'name_mr' => 'नागपूर',      'name_gu' => 'નાગપુર',        'lat' => 21.1458, 'lng' => 79.0882, 'tz' => 'Asia/Kolkata' ),
        'indore'       => array( 'name_en' => 'Indore',       'name_hi' => 'इंदौर',       'name_mr' => 'इंदौर',       'name_gu' => 'ઇંદોર',         'lat' => 22.7196, 'lng' => 75.8577, 'tz' => 'Asia/Kolkata' ),
        'patna'        => array( 'name_en' => 'Patna',        'name_hi' => 'पटना',        'name_mr' => 'पाटणा',       'name_gu' => 'પટના',          'lat' => 25.5941, 'lng' => 85.1376, 'tz' => 'Asia/Kolkata' ),
        'bhopal'       => array( 'name_en' => 'Bhopal',       'name_hi' => 'भोपाल',       'name_mr' => 'भोपाळ',       'name_gu' => 'ભોપાલ',         'lat' => 23.2599, 'lng' => 77.4126, 'tz' => 'Asia/Kolkata' ),
        'surat'        => array( 'name_en' => 'Surat',        'name_hi' => 'सूरत',        'name_mr' => 'सुरत',        'name_gu' => 'સુરત',          'lat' => 21.1702, 'lng' => 72.8311, 'tz' => 'Asia/Kolkata' ),
        'vadodara'     => array( 'name_en' => 'Vadodara',     'name_hi' => 'वडोदरा',      'name_mr' => 'वडोदरा',      'name_gu' => 'વડોદરા',         'lat' => 22.3072, 'lng' => 73.1812, 'tz' => 'Asia/Kolkata' ),
        'rajkot'       => array( 'name_en' => 'Rajkot',       'name_hi' => 'राजकोट',      'name_mr' => 'राजकोट',      'name_gu' => 'રાજકોટ',         'lat' => 22.3039, 'lng' => 70.8022, 'tz' => 'Asia/Kolkata' ),
        'dwarka'       => array( 'name_en' => 'Dwarka',       'name_hi' => 'द्वारका',     'name_mr' => 'द्वारका',     'name_gu' => 'દ્વારકા',        'lat' => 22.2394, 'lng' => 68.9678, 'tz' => 'Asia/Kolkata' ),
        'somnath'      => array( 'name_en' => 'Somnath',      'name_hi' => 'सोमनाथ',      'name_mr' => 'सोमनाथ',      'name_gu' => 'સોમનાથ',         'lat' => 20.8880, 'lng' => 70.4011, 'tz' => 'Asia/Kolkata' ),
        'varanasi'     => array( 'name_en' => 'Varanasi',     'name_hi' => 'वाराणसी',     'name_mr' => 'वाराणसी',     'name_gu' => 'વારાણસી',       'lat' => 25.3176, 'lng' => 82.9739, 'tz' => 'Asia/Kolkata' ),
        'prayagraj'    => array( 'name_en' => 'Prayagraj',    'name_hi' => 'प्रयागराज',   'name_mr' => 'प्रयागराज',   'name_gu' => 'પ્રયાગરાજ',     'lat' => 25.4358, 'lng' => 81.8463, 'tz' => 'Asia/Kolkata' ),
        'mathura'      => array( 'name_en' => 'Mathura',      'name_hi' => 'मथुरा',       'name_mr' => 'मथुरा',       'name_gu' => 'મથુરા',         'lat' => 27.4924, 'lng' => 77.6737, 'tz' => 'Asia/Kolkata' ),
        'vrindavan'    => array( 'name_en' => 'Vrindavan',    'name_hi' => 'वृंदावन',     'name_mr' => 'वृंदावन',     'name_gu' => 'વૃંદાવન',       'lat' => 27.5806, 'lng' => 77.7006, 'tz' => 'Asia/Kolkata' ),
        'haridwar'     => array( 'name_en' => 'Haridwar',     'name_hi' => 'हरिद्वार',    'name_mr' => 'हरिद्वार',    'name_gu' => 'હરિદ્વાર',      'lat' => 29.9457, 'lng' => 78.1642, 'tz' => 'Asia/Kolkata' ),
        'rishikesh'    => array( 'name_en' => 'Rishikesh',    'name_hi' => 'ऋषिकेश',      'name_mr' => 'ऋषिकेश',      'name_gu' => 'ઋષિકેશ',         'lat' => 30.0869, 'lng' => 78.2676, 'tz' => 'Asia/Kolkata' ),
        'ujjain'       => array( 'name_en' => 'Ujjain',       'name_hi' => 'उज्जैन',      'name_mr' => 'उज्जैन',      'name_gu' => 'ઉજ્જૈન',        'lat' => 23.1765, 'lng' => 75.7885, 'tz' => 'Asia/Kolkata' ),
        'tirupati'     => array( 'name_en' => 'Tirupati',     'name_hi' => 'तिरुपति',     'name_mr' => 'तिरुपती',     'name_gu' => 'તિરુપતિ',       'lat' => 13.6288, 'lng' => 79.4192, 'tz' => 'Asia/Kolkata' ),
        'amritsar'     => array( 'name_en' => 'Amritsar',     'name_hi' => 'अमृतसर',      'name_mr' => 'अमृतसर',      'name_gu' => 'અમૃતસર',        'lat' => 31.6340, 'lng' => 74.8723, 'tz' => 'Asia/Kolkata' ),
        'chandigarh'   => array( 'name_en' => 'Chandigarh',   'name_hi' => 'चंडीगढ़',     'name_mr' => 'चंदीगड',      'name_gu' => 'ચંદીગઢ',         'lat' => 30.7333, 'lng' => 76.7794, 'tz' => 'Asia/Kolkata' ),
        'dehradun'     => array( 'name_en' => 'Dehradun',     'name_hi' => 'देहरादून',    'name_mr' => 'डेहराडून',    'name_gu' => 'દેહરાદૂન',       'lat' => 30.3165, 'lng' => 78.0322, 'tz' => 'Asia/Kolkata' ),
    );
}

/* ---------------------------------------------------------------------
 * Calculated panchang fields
 * ------------------------------------------------------------------- */

/** Day duration string from sunrise/sunset ISO timestamps. */
function bb_day_duration( $sunrise_iso, $sunset_iso ) {
    if ( ! $sunrise_iso || ! $sunset_iso ) return '—';
    try {
        $rise = new DateTime( $sunrise_iso );
        $set  = new DateTime( $sunset_iso );
        $diff = $rise->diff( $set );
        return sprintf( '%dh %02dm', $diff->h, $diff->i );
    } catch ( Exception $e ) { return '—'; }
}

/** Night duration = 24h - day duration */
function bb_night_duration( $sunrise_iso, $sunset_iso ) {
    if ( ! $sunrise_iso || ! $sunset_iso ) return '—';
    try {
        $rise = new DateTime( $sunrise_iso );
        $set  = new DateTime( $sunset_iso );
        $secs = $set->getTimestamp() - $rise->getTimestamp();
        $night_secs = 86400 - $secs;
        $h = floor( $night_secs / 3600 );
        $m = floor( ( $night_secs % 3600 ) / 60 );
        return sprintf( '%dh %02dm', $h, $m );
    } catch ( Exception $e ) { return '—'; }
}

/** Madhyahna (midday) = midpoint between sunrise and sunset. Returns DateTime in IST. */
function bb_madhyahna( $sunrise_iso, $sunset_iso, $tz = 'Asia/Kolkata' ) {
    if ( ! $sunrise_iso || ! $sunset_iso ) return null;
    try {
        $rise = ( new DateTime( $sunrise_iso ) )->getTimestamp();
        $set  = ( new DateTime( $sunset_iso ) )->getTimestamp();
        $mid  = (int) ( ( $rise + $set ) / 2 );
        $dt = new DateTime( '@' . $mid );
        $dt->setTimezone( new DateTimeZone( $tz ) );
        return $dt;
    } catch ( Exception $e ) { return null; }
}

/* ---------------------------------------------------------------------
 * Hindu calendar — Vikram Samvat, Shaka Samvat, Kaliyuga
 * ------------------------------------------------------------------- */

/**
 * Vikram Samvat = Gregorian + 56 (before Chaitra) or +57 (after Chaitra/March-April).
 * Approximate; precise needs lunar month boundary. Good enough for most users.
 */
function bb_vikram_samvat( $date = null ) {
    $ts = $date ? strtotime( $date ) : time();
    $y  = (int) date( 'Y', $ts );
    $m  = (int) date( 'n', $ts );
    return ( $m >= 4 ) ? ( $y + 57 ) : ( $y + 56 );
}

/** Shaka Samvat = Gregorian - 78 (after March 22) or -79 before. */
function bb_shaka_samvat( $date = null ) {
    $ts = $date ? strtotime( $date ) : time();
    $y  = (int) date( 'Y', $ts );
    $m  = (int) date( 'n', $ts );
    $d  = (int) date( 'j', $ts );
    if ( $m > 3 || ( $m === 3 && $d >= 22 ) ) return $y - 78;
    return $y - 79;
}

/** Kaliyuga year = current Gregorian year + 3102 (year 1 = 3102 BCE) */
function bb_kaliyuga_year( $date = null ) {
    $ts = $date ? strtotime( $date ) : time();
    return (int) date( 'Y', $ts ) + 3102;
}

/* ---------------------------------------------------------------------
 * Hindu lunar month (Chandra Masa) — derived from gregorian month
 * Approximate. Full accuracy needs amanta/purnimanta calendars.
 * ------------------------------------------------------------------- */
function bb_chandra_masa( $lang = 'en', $date = null ) {
    $ts = $date ? strtotime( $date ) : time();
    $m  = (int) date( 'n', $ts );
    $months = array(
        // Roughly: solar month → Hindu lunar month
        'en' => array( 1 => 'Magh', 'Phalgun', 'Chaitra', 'Vaishakh', 'Jyeshtha', 'Ashadh', 'Shravan', 'Bhadrapada', 'Ashwin', 'Kartik', 'Margashirsha', 'Paush' ),
        'hi' => array( 1 => 'माघ', 'फाल्गुन', 'चैत्र', 'वैशाख', 'ज्येष्ठ', 'आषाढ़', 'श्रावण', 'भाद्रपद', 'आश्विन', 'कार्तिक', 'मार्गशीर्ष', 'पौष' ),
        'mr' => array( 1 => 'माघ', 'फाल्गुन', 'चैत्र', 'वैशाख', 'ज्येष्ठ', 'आषाढ', 'श्रावण', 'भाद्रपद', 'आश्विन', 'कार्तिक', 'मार्गशीर्ष', 'पौष' ),
        'gu' => array( 1 => 'મહા', 'ફાગણ', 'ચૈત્ર', 'વૈશાખ', 'જેઠ', 'અષાઢ', 'શ્રાવણ', 'ભાદરવો', 'આસો', 'કારતક', 'માગસર', 'પોષ' ),
    );
    $arr = $months[ $lang ] ?? $months['en'];
    return $arr[ $m ] ?? '';
}

/* ---------------------------------------------------------------------
 * Vikram Samvat naming (60-year cycle)
 * ------------------------------------------------------------------- */
function bb_samvatsara( $vs_year ) {
    $names = array( 'Prabhava', 'Vibhava', 'Shukla', 'Pramoda', 'Prajapati', 'Angirasa', 'Shrimukha', 'Bhava', 'Yuva', 'Dhata',
                    'Ishvara', 'Bahudhanya', 'Pramathin', 'Vikrama', 'Vrisha', 'Chitrabhanu', 'Subhanu', 'Tarana', 'Parthiva', 'Vyaya',
                    'Sarvajit', 'Sarvadhari', 'Virodhi', 'Vikrita', 'Khara', 'Nandana', 'Vijaya', 'Jaya', 'Manmatha', 'Durmukha',
                    'Hevilambi', 'Vilambi', 'Vikari', 'Sharvari', 'Plava', 'Shubhakrit', 'Shobhakrit', 'Krodhi', 'Vishvavasu', 'Parabhava',
                    'Plavanga', 'Kilaka', 'Saumya', 'Sadharana', 'Virodhikrit', 'Paridhavi', 'Pramadi', 'Ananda', 'Rakshasa', 'Nala',
                    'Pingala', 'Kalayukta', 'Siddharthi', 'Raudra', 'Durmati', 'Dundubhi', 'Rudhirodgari', 'Raktakshi', 'Krodhana', 'Akshaya' );
    // VS 1 corresponds to roughly Prabhava — but cycles vary. Use modulo for approximate.
    $idx = ( $vs_year - 14 ) % 60;
    if ( $idx < 0 ) $idx += 60;
    return $names[ $idx ] ?? '';
}
