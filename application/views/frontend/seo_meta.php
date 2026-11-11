<?php
/*
 * Search and sharing tags for the public website: page title, meta description,
 * canonical URL, Open Graph / Twitter tags and Restaurant structured data.
 * Target searches: "seafood restaurant in Coron", "best seafood restaurant in Coron Palawan".
 */
$seo_company   = getMainCompany();
$seo_name      = html_entity_decode(isset($site_name) && $site_name ? $site_name : 'Tawhay By Crabs and Ribs', ENT_QUOTES, 'UTF-8');
$seo_page      = strtolower((string)$this->uri->segment(1));
$seo_banner    = isset($seo_company->main_banner_section) && $seo_company->main_banner_section ? json_decode($seo_company->main_banner_section) : '';
$seo_image     = $seo_banner && !empty($seo_banner->main_banner) ? base_url() . 'uploads/banner_section/' . $seo_banner->main_banner : '';
$seo_index     = true;

$seo_pages = array(
    ''               => array($seo_name . ' | Seafood Restaurant in Coron, Palawan', 'Seafood restaurant in Coron, Palawan serving fresh crabs, butter garlic crab, sizzling seafood, back ribs and Filipino favourites. Dine in, order online or reserve a table.'),
    'about-us'       => array('About Us | ' . $seo_name . ', Coron Seafood Restaurant', 'Meet ' . $seo_name . ', a seafood restaurant on the National Highway in Coron, Palawan, known for fresh crabs, seafood and tender ribs made for sharing.'),
    'online-order'   => array('Menu & Order Online | Seafood in Coron | ' . $seo_name, 'See the full menu of ' . $seo_name . ' in Coron, Palawan: crabs, seafood, ribs, family meals, coffee and cocktails. Order online for pickup or delivery.'),
    'reservation'    => array('Reserve a Table | Seafood Restaurant Coron | ' . $seo_name, 'Book a table at ' . $seo_name . ', a seafood restaurant in Coron, Palawan. Great for families and groups.'),
    'contact-us'     => array('Contact & Location | ' . $seo_name . ', Coron', 'Find ' . $seo_name . ' on the National Highway, Brgy. 1, Coron, Palawan. Call 0954 220 7334 or email info@tawhay.app.'),
    'privacy-policy' => array('Privacy Policy | ' . $seo_name, 'How ' . $seo_name . ' collects, uses and protects your personal information.'),
    'photo-credits'  => array('Photo Credits | ' . $seo_name, 'Credits for menu photos used on the ' . $seo_name . ' website.'),
);

if (isset($seo_pages[$seo_page])) {
    list($seo_title, $seo_description) = $seo_pages[$seo_page];
} elseif ($seo_page == 'food-details' && isset($food_details) && $food_details) {
    $seo_dish        = html_entity_decode($food_details->name, ENT_QUOTES, 'UTF-8');
    $seo_title       = $seo_dish . ' | Seafood & Grill in Coron | ' . $seo_name;
    $seo_dish_desc   = trim(html_entity_decode((string)$food_details->description, ENT_QUOTES, 'UTF-8'));
    $seo_description = $seo_dish . ($seo_dish_desc ? ': ' . rtrim($seo_dish_desc, '.') . '.' : '.') . ' Order online or dine in at ' . $seo_name . ', seafood restaurant in Coron, Palawan.';
    $seo_outlet      = getOutletById($this->session->userdata('online_selected_outlet_updated'));
    $seo_large       = $seo_outlet && $seo_outlet->large_imgs ? (array)json_decode($seo_outlet->large_imgs) : array();
    if (!empty($seo_large['large_' . $food_details->id])) {
        $seo_image = base_url() . 'uploads/website/' . $seo_large['large_' . $food_details->id];
    } elseif ($food_details->photo) {
        $seo_image = base_url() . 'images/' . $food_details->photo;
    }
} else {
    // account, cart and checkout pages are not useful in search results
    $seo_title       = $seo_name;
    $seo_description = $seo_pages[''][1];
    $seo_index       = false;
}
$seo_url = base_url() . ltrim($this->uri->uri_string(), '/');
?>
  <title><?php echo escape_output($seo_title); ?></title>
  <meta name="description" content="<?php echo escape_output($seo_description); ?>">
<?php if ($seo_index): ?>
  <link rel="canonical" href="<?php echo escape_output($seo_url); ?>">
<?php else: ?>
  <meta name="robots" content="noindex, follow">
<?php endif; ?>
  <meta property="og:type" content="<?php echo $seo_page == '' ? 'restaurant.restaurant' : 'website'; ?>">
  <meta property="og:site_name" content="<?php echo escape_output($seo_name); ?>">
  <meta property="og:title" content="<?php echo escape_output($seo_title); ?>">
  <meta property="og:description" content="<?php echo escape_output($seo_description); ?>">
  <meta property="og:url" content="<?php echo escape_output($seo_url); ?>">
<?php if ($seo_image): ?>
  <meta property="og:image" content="<?php echo escape_output($seo_image); ?>">
<?php endif; ?>
  <meta property="og:locale" content="en_PH">
  <meta name="twitter:card" content="summary_large_image">
<?php
if ($seo_page == '' || $seo_page == 'contact-us' || $seo_page == 'about-us') {
    $seo_social = isset($seo_company->social_media) && $seo_company->social_media ? json_decode($seo_company->social_media) : '';
    $seo_same   = array();
    foreach (array('facebook_link', 'google_link', 'twitter_link') as $seo_key) {
        if ($seo_social && !empty($seo_social->$seo_key) && strpos($seo_social->$seo_key, 'http') === 0) {
            $seo_same[] = $seo_social->$seo_key;
        }
    }
    $seo_phone = preg_replace('/\D/', '', (string)$seo_company->phone);
    if (strpos($seo_phone, '0') === 0) {
        $seo_phone = '+63' . substr($seo_phone, 1);
    }
    $seo_schema = array(
        '@context'            => 'https://schema.org',
        '@type'               => 'SeafoodRestaurant',
        'name'                => $seo_name,
        'url'                 => base_url(),
        'description'         => $seo_pages[''][1],
        'servesCuisine'       => array('Seafood', 'Filipino', 'Grill'),
        'priceRange'          => '₱₱',
        'telephone'           => $seo_phone,
        'email'               => 'info@tawhay.app',
        'address'             => array(
            '@type'           => 'PostalAddress',
            'streetAddress'   => 'National Highway, Brgy. 1',
            'addressLocality' => 'Coron',
            'addressRegion'   => 'Palawan',
            'postalCode'      => '5316',
            'addressCountry'  => 'PH',
        ),
        'geo'                 => array('@type' => 'GeoCoordinates', 'latitude' => 11.9997094, 'longitude' => 120.2078386),
        'hasMenu'             => base_url() . 'online-order',
        'acceptsReservations' => true,
    );
    if ($seo_image) {
        $seo_schema['image'] = $seo_image;
    }
    if ($seo_same) {
        $seo_schema['sameAs'] = $seo_same;
    }
    echo '  <script type="application/ld+json">' . json_encode($seo_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . "</script>\n";
}
?>
