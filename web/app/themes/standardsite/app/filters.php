<?php

/**
 * Theme filters.
 */

namespace App;


use App\View\Composers\App;
use App\View\Composers\Form;
use FasadBridge\Includes\PublicSettings;
use PrekWeb\Includes\Fasad;
use PrekWeb\Includes\Helpers;

/**
 * Add "… Continued" to the excerpt.
 *
 * @return string
 */
add_filter('excerpt_more', function () {
    return sprintf(' &hellip; <a href="%s">%s</a>', get_permalink(), __('Continued', 'sage'));
});

add_filter('esc_html', function ($safe_text, $text) {
    // Allow <i> in headers from \App\Fields\SiteSettings::fields()
    if (in_array($text, ['<i>Sidhuvud</i>', '<i>Sidfot</i>'])) {
        $safe_text = $text;
    }
    return $safe_text;
}, 10, 2);

// add_action(
//     'fasad_bridge_synchronize_complete',
//     function () {
//         $common = new Common();
//         $common->saveJsonFiles();
//     }
// );
add_filter('prekweb_addOptionsPage', function ($addPage, $optionsPage) {
    if (isset($optionsPage['json']) && $optionsPage['json'] === 'site-settings.json') {
        return false;
    }
    return $addPage;
},         10, 2);

add_filter('fasad_bridge_inquiryLocalize', function($localizeObject){
    $localizeObject['data']['showingSuccessMessage'] = '<h3>' . $localizeObject['data']['showingSuccessMessage'] . '</h3>';
    $localizeObject['data']['interestSuccessMessage'] = '<h3>Tack för din intresseanmälan!</h3><p>Ansvarig mäklare kommer att kontakta dig inom kort.</p>';
    $localizeObject['data']['speculatorSuccessMessage'] = '<h3>Tack för visat intresse!</h3><p>Vi kommer att kontakta dig inom kort.</p>';
 return $localizeObject;
});
/**
 * Adding form-holder class to form selectors
 */
add_filter('fasad_bridge_formSelectors', function($selectors){
    $selectors = ['.fasad-inquiry-form-holder:not(.mailchimp)'];
    return $selectors;
});

/**
 * Hiding form after submit and showing custom text.
 */
add_filter('fasad_bridge_formCallbacks', function($callbacks){
    $callback = <<<EOT
    function(formClass, \$form, response, message){
        if (response.success) {
            let height = 0;
            if (jQuery('header').length) {
                height += jQuery('header').outerHeight();
            }
            \$formHolder = \$form.closest('.form-holder');
            \$formHolder.find('.hf-fields-wrap').hide();
            let top = \$formHolder.offset().top - 40;
            if (height > 0 && height < top) {
              top -= height;
            }
            jQuery('html,body').animate({ scrollTop: top }, 400);
        }
    }
    EOT;
    $callbacks[0] = $callback;
    return $callbacks;
}, 10);

//Filter showing and interestform from formselect
add_filter('acf/fields/post_object/query/name=form', function ($args, $field, $post_id) {
    $excluded  = [];
    $htmlForms = new \WP_Query([
                                   'post_type'      => 'html-form',
                                   'posts_per_page' => -1,
                               ]);
    if ($htmlForms->have_posts()) {
        foreach ($htmlForms->posts as $htmlFormPost) {
            if (in_array($htmlFormPost->post_name, ['interestform', 'showingform'])) {
                $excluded[] = $htmlFormPost->ID;
            }
        }
    }
    if (!empty($excluded)) {
        $args['post__not_in'] = $excluded;
    }
    return $args;
},         10, 3);
add_filter('hf_action_email_message', function ($message, $submission) {
    $inputs = [];
    if ($formId = getAttribute('form_id', $submission)) {
        if ($form = Form::getFormById($formId)) {
            $inputs = Form::formInputs($form->post_name);
        }
    }
    foreach ($inputs as $input) {
        $value   = getAttribute($input['name'], $submission->data);
        $name    = getAttribute('placeholder', $input) ?: getAttribute('name', $input);
        $message .= '<p>' . str_replace('*', '', $name) . ': ' . $value . '</p>';
    }
    return $message;
}, 10, 2);

add_filter('hf_form_default_messages', function($messages){
    $messages['success'] = 'Tack, vi hör av oss inom kort!';
    $messages['invalid_email'] = 'Du verkar ha angett en felaktig E-postadress';
    $messages['required_field_missing'] = 'Vänligen fyll i alla nödvändiga fält';
    $messages['error'] = 'Hoppsan, något har gått fel';
    return $messages;
});
add_filter('hf_form_default_settings', function($settings){
    $settings['hide_after_success'] = 1;
    return $settings;
});
add_filter('hf_form_message_success', function($message, $form){
    return '<h3>' . App::getOption('form.message_success', 'Tack, vi hör av oss inom kort!') . '</h3>';
},10, 2);
add_filter('hf_action_email_to', function() {
    //Acf options doesnt work here
    return get_option('options_form_email', get_bloginfo('admin_email'));
},10, 0);
add_filter('hf_action_email_from', function() {
    //Acf options doesnt work here
    return get_option('options_form_email', get_bloginfo('admin_email'));
},10, 0);
//add_filter('fasad_bridge_lifestyle_slug', '__return_true'); //using fasads own tag taxonomy

add_action('template_include', function ($template) {
    $is404 = false;
    $is301 = null;
    if (class_exists('FasadBridge\\Includes\\PublicSettings') && get_post_type() == \FasadBridge\Includes\PublicSettings::FASAD_LISTING_POST_TYPE
        && !(get_query_var(\FasadBridge\Includes\PublicSettings::FASAD_LISTING_POST_TYPE))
        && !get_query_var('taxonomy')
        && !is_search()
    ) {
        // on /objekt/ redirect
        $listingsPage = App::getListingsPage();
        $is301        = $listingsPage['url'] ?? '';
    }
    if (is_author()) {
        $is404 = true;
    }
    if (is_search()) {
        $is404 = true;
    }
    if (is_singular('post')) {
        $is404 = true;
    }
    if (is_single() && in_array(
            get_post_type(),
            [
                'attachment',
                ...(class_exists('FasadBridge\\Includes\\PublicSettings') ? [\FasadBridge\Includes\PublicSettings::FASAD_OFFICE_POST_TYPE, \FasadBridge\Includes\PublicSettings::FASAD_REALTOR_POST_TYPE] : [])
            ]
        )) {
        $is404 = true;
    }
    if (is_archive() && in_array(
            get_query_var('taxonomy'),
            [
                'fasad_listing_tag',
            ]
        )) {
        $is404 = true;
    }
    if ($is301) {
        wp_safe_redirect($is301, 301);
        exit;
    } elseif ($is404) {
        global $wp_query;
        $wp_query->set_404();
        $wp_query->is_404 = true;
        $wp_query->post_count = 0;
        $wp_query->posts = [];
        $wp_query->post = false;
        status_header(404);
        nocache_headers();
        $template = get_404_template();
    }
    return $template;
});
add_action('template_include', function ($template) {
    //Handle listing taxonomy archives
    $pagename = get_query_var('pagename');
    if (in_array($pagename, [
        'typ',
        'distrikt',
        'omrade',
        'stad',
        'kommun',
        'taggar',
    ])) {
        global $wp_query;
        if ($wp_query->is_404()) {
            //query for a page that dont exists, but this is a listings taxonomy
            $template         = str_replace('404', 'taxonomy', $template);
            $wp_query->is_404 = false;
        }
    }
    return $template;
});
/*
 * Set meta description if term_description is empty
 */
add_filter('seopress_titles_desc', function ($html) {
    if (App::isListingTax() && empty($html)) {
        $queriedObject = get_queried_object();
        $html          = "Objekt - " . get_taxonomy($queriedObject->taxonomy)->label . ': ' . $queriedObject->name;
    }
    return $html;
});

/*
 * Add sales_text and tax_label as possible variables for title/description
 * (add variables here, add values below)
 */
add_filter('seopress_titles_template_variables_array', function ($values) {
    $values[] = '%%sales_text%%';
    $values[] = '%%tax_label%%';
    return $values;
});

/*
 * Add sales_text and tax_label as possible variables for title/description
 * (add values here, add variables above)
 */
add_filter('seopress_titles_template_replace_array', function ($values) {
    $tmpValues['sales_text'] = '';
    $tmpValues['tax_label'] = '';
    if (App::isListingOrPreview()) {
        $listing = Fasad::expandObject();
        if (!empty($listing) && is_object($listing) && !is_tax()) {
            $tmpValues['sales_text'] = $listing->salesText;
        }
    }
    if (App::isListingTax()) {
        $tmpValues['tax_label'] = get_taxonomy(get_queried_object()->taxonomy)->label;
    }
    return array_values(array_merge($values, $tmpValues));
});

add_filter('option_seopress_titles_option_name', function ($value) {
    if (isset($value['seopress_titles_single_titles'])) {
        foreach ($value['seopress_titles_single_titles'] as $type => &$item) {
            if ($type === 'fasad_listing') {
                if (isset($item['title'])) {
                    $item['title'] = "%%post_title%% %%sep%% %%sitetitle%%";
                }
                if (isset($item['description'])) {
                    $item['description'] = "%%sales_text%%";
                }
            }
        }
    }
    if (isset($value['seopress_titles_tax_titles'])) {
        foreach ($value['seopress_titles_tax_titles'] as $tax => &$item) {
            if (App::isListingTax($tax)) {
                if (isset($item['title'])/* && $item['title'] === ''*/) {
                    $item['title'] = "Objekt %%sep%% %%tax_label%%: %%term_title%% %%sep%% %%sitetitle%%";
                }
                if (isset($item['description']) && $item['description'] === '') {
                    $item['description'] = "%%term_description%%";
                }
            }
        }
    }
    return $value;
});

add_filter('option_seopress_xml_sitemap_option_name', function ($value) {
    $taxonomies = [
        'fasad_listing_type',
        'fasad_listing_district',
        'fasad_listing_districtinfo',
        'fasad_listing_city',
        'fasad_listing_commune',
    ];

    $posts = [
        'page',
        ...(class_exists('FasadBridge\\Includes\\PublicSettings') ? [PublicSettings::FASAD_LISTING_POST_TYPE] : []),
    ];

    $value['seopress_xml_sitemap_post_types_list'] = [];
    $value['seopress_xml_sitemap_taxonomies_list'] = [];
    foreach ($posts as $post) {
        $value['seopress_xml_sitemap_post_types_list'][$post] = ['include' => 1];
    }
    foreach ($taxonomies as $taxonomy) {
        $value['seopress_xml_sitemap_taxonomies_list'][$taxonomy] = ['include' => 1];
    }
    return $value;
});

add_filter('fasad_bridge_register_district_info_taxonomy', '__return_true');
add_action('wp_head', function () {
    $metaTags = [];
    $favicon  = \App\View\Composers\App::getOption('favicon');
    if ($favicon && class_exists('PrekWebHelper\\PrekWebHelper')) {
        $prekWebHelper = \PrekWebHelper\PrekWebHelper::getInstance();
        foreach (['16', '32', '192'] as $size) {
            if ($favicon['width'] >= $size && $favicon['height'] >= $size) {
                $metaTags[] = [
                    'rel'   => 'icon',
                    'type'  => $favicon['mime_type'],
                    'sizes' => $size . '*' . $size,
                    'href'  => $prekWebHelper->image->processImage(wp_get_attachment_image_url($favicon['ID'], 'full'), $size, $size),
                ];
            }
        }
        $size = 180;
        if ($favicon['width'] >= $size && $favicon['height'] >= $size) {
            $metaTags[] = [
                'rel'   => 'apple-touch-icon',
                'sizes' => $size . '*' . $size,
                'href'  => $prekWebHelper->image->processImage(wp_get_attachment_image_url($favicon['ID'], 'full'), $size, $size),
            ];
        }
    }
    if (empty($metaTags)):
        $metaTags = [
            $metaTags[] = [
                'rel'   => 'icon',
                'type'  => 'image/png',
                'sizes' => '16x16',
                'href'  => \Roots\asset('images/favicons/favicon-16x16.png'),
            ],
            $metaTags[] = [
                'rel'   => 'icon',
                'type'  => 'image/png',
                'sizes' => '32x32',
                'href'  => \Roots\asset('images/favicons/favicon-32x32.png'),
            ],
            [
                'rel'   => 'apple-touch-icon',
                'sizes' => '180x180',
                'href'  => \Roots\asset('images/favicons/apple-touch-icon.png'),
            ],
            $metaTags[] = [
                'rel'  => 'manifest',
                'href' => \Roots\asset('images/favicons/site.webmanifest'),
            ],
        ];
        ?>
    <?php endif; ?>
    <?php foreach ($metaTags as $metaTag): ?>
        <link <?= \App\attributesToString($metaTag); ?> />
    <?php endforeach; ?>
    <?php
});


add_filter('pto/get_options', function ($options) {
    $options['autosort']                         = 0;
    $options['navigation_sort_apply']            = 0;
    $options['show_reorder_interfaces']          = [
        'post'                => 'hide',
        'attachment'          => 'hide',
        'wp_block'            => 'hide',
        'wp_navigation'       => 'hide',
        'fasad_listing'       => 'hide',
        'fasad_protected'     => 'hide',
        'fasad_office'        => 'hide',
        'fasad_realtor'       => 'hide',
        'cp_listing'          => 'hide',
        'fasad_coworker'      => 'show',
        'fasad_office_extend' => 'show',
    ];
    $options['allow_reorder_default_interfaces'] = [
        'post'                => 'no',
        'page'                => 'no',
        'attachment'          => 'no',
        'wp_block'            => 'no',
        'wp_navigation'       => 'no',
        'acf-taxonomy'        => 'no',
        'acf-post-type'       => 'no',
        'acf-ui-options-page' => 'no',
        'acf-field-group'     => 'no',
        'fasad_listing'       => 'no',
        'fasad_protected'     => 'no',
        'fasad_office'        => 'no',
        'fasad_realtor'       => 'no',
        'cp_listing'          => 'no',
        'fasad_coworker'      => 'yes',
        'fasad_office_extend' => 'yes',
    ];
    return $options;
});

add_filter('body_class', function ($classes) {
    $classes[] = App::getOption('font-family', 'pt-sans');
    return $classes;
});

add_filter('prek_web_helper_form_debug_level', function ($level) {
    if (class_exists('\PrekWebHelper\Includes\Form')) {
        return \PrekWebHelper\Includes\Form::DEBUG_LOG_SLACK;
    }
    return $level;
});

add_filter('prekweb-coworkerFasadSequence', '__return_false');

add_filter('fasad_customerpages_blockedListings', function($blockedListings) {
   return [
       '1558987',
       '1558981',
   ];
});

function areaDecimals($areaUnit)
{
    $areaUnit['decimals'] = 2;
    return $areaUnit;
}
add_filter('fasad_bridge_livingAreaStrUnits', __NAMESPACE__ . '\areaDecimals');
add_filter('fasad_bridge_plotAreaStrUnits',   __NAMESPACE__ . '\areaDecimals');
add_filter('fasad_bridge_waterAreaStrUnits',  __NAMESPACE__ . '\areaDecimals');
add_filter('fasad_bridge_localAreaStrUnits',  __NAMESPACE__ . '\areaDecimals');
add_filter('fasad_bridge_livingAreaStrUnits', __NAMESPACE__ . '\areaDecimals');

add_filter("fasad_bridge_facts", function ($facts, $listing) {
    unset($facts['country']);
    unset($facts['zipcode']);
    unset($facts['valueYear']);

    $removeCommune = (!empty($facts['city']["value"]) && !empty($facts['commune']["value"]) && $facts['city']["value"] === $facts['commune']["value"]);
    if ($removeCommune) {
        unset($facts['commune']);
    }

    if (!empty($facts['areasComment'])) {
        $facts['areas']['areasComment'] = $facts['areasComment'];
        unset($facts['areasComment']);
    }

    // Use correct area unit from CRM (like "ha"), not hardcoded kvm from Prek Web
    // Will only work if all areas of the same type have the same unit
    /*foreach ($listing->size->area->areas as $area) {
        if ($area->type === 'Tomtarea') {
            $facts['plotAreaUnit'] = [
                'value' => strtolower($area->unit)
            ];
        }
    }*/

    if (
        !Fasad::isHouse($facts['formOfOwnership']['value']) &&
        !Fasad::isFarm($facts['formOfOwnership']['value'])
    ) {
        unset($facts['noOfPledge']);
        unset($facts['totalMortgagesSum']);
    }

    if (
        !empty($listing->economy->operatingCost) &&
        !empty($listing->economy->operatingCost->operatingCosts)
    ) {
        $totalOperatingCosts = $facts['totalOperatingCosts'] ?? [];
        unset($facts['totalOperatingCosts']);

        $facts['operatingCostsHeader'] = [
            'label' => 'Driftkostnader',
            'value' => ''
        ];
        foreach ($listing->economy->operatingCost->operatingCosts as $operatingCost) {
            if (!property_exists($operatingCost, 'amountMonth') && property_exists($operatingCost, 'amountYear')) {
                $operatingCost->amountMonth = round($operatingCost->amountYear / 12);
            }
            $facts['operatingCosts_' . $operatingCost->alias . '_m'] = [
                'label' => $operatingCost->alias,
                'value' => number_format($operatingCost->amountMonth, 0, ',', ' ') . ' kr/mån',
            ];
            //$facts['operatingCosts_' . $operatingCost->alias . '_y'] = [
            //    'label' => $operatingCost->alias,
            //    'value' => \PrekWebHelper\Includes\Helpers::numberFormat($operatingCost->amountYear, 1, 'kr/år', ' '),
            //];
        }

        // Put total last
        $totalOperatingCosts['operatingCost_totalAmountMonth']['label'] = 'Totalt per mån';
        $totalOperatingCosts['operatingCost_totalAmountYear']['label']  = 'Totalt per år';
        $facts['totalOperatingCosts'] = $totalOperatingCosts;
    }

    return $facts;
}, 10, 2);


// Registrera rewrite-regel för fasad_listing
// Hantera /objekt/[slug] URL:er
add_filter('query_vars', function($vars) {
    $vars[] = 'fasad_listing';
    return $vars;
});

add_action('init', function() {
    add_rewrite_rule(
        'objekt/([^/]+)/?$',
        'index.php?fasad_listing=$matches[1]',
        'top'
    );
}, 20);

// Tvinga rätt post_type när fasad_listing query-var finns
add_action('pre_get_posts', function($query) {
    if (!$query->is_main_query()) return;
    $fasad_slug = $query->get('fasad_listing');
    if (!empty($fasad_slug) && $fasad_slug !== '1') {
        $query->set('post_type', 'fasad_listing');
        $query->set('name', $fasad_slug);
        $query->is_single = true;
        $query->is_404 = false;
    }
});


// Tvinga single-fasad_listing template när fasad_listing query-var finns
add_filter('template_include', function($template) {
    $fasad_slug = get_query_var('fasad_listing');
    if (!empty($fasad_slug) && $fasad_slug !== '1' && strlen($fasad_slug) > 2) {
        global $wp_query;
        $wp_query->is_single = true;
        $wp_query->is_singular = true;
        $wp_query->is_404 = false;
    }
    return $template;
}, 99);
// Debug sync
add_action('pre_get_posts', function($query) {
    if ($query->is_main_query()) {
        $pn = $query->query['pagename'] ?? 'NOT SET';
        $name = $query->query['name'] ?? 'NOT SET';
    }
}, 5);

// Hantera _sync endpoint direkt
add_action('template_redirect', function() {
    global $wp;
    $request = trim($wp->request, '/');
    
    if ($request === '_sync') {
        status_header(200);
        header('Content-Type: text/plain; charset=utf-8');
        echo "PREK Sync starting...\n";
        flush();
        
        $plugins_dir = WP_CONTENT_DIR . '/plugins/';
        
        // Ladda alla plugins autoloaders
        foreach (['fasad-bridge', 'fasad-api-connect', 'prek-web'] as $plugin) {
            $autoload = $plugins_dir . $plugin . '/vendor/autoload.php';
            if (file_exists($autoload)) {
                require_once $autoload;
                echo "Loaded: $plugin\n";
            }
        }
        
        // Ladda plugin-huvudfiler
        foreach (['fasad-bridge/FasadBridge.php', 'fasad-api-connect/FasadApiConnect.php', 'prek-web/PrekWeb.php'] as $plugin_file) {
            $file = $plugins_dir . $plugin_file;
            if (file_exists($file) && !class_exists(basename(dirname($file)))) {
                require_once $file;
            }
        }
        
        if (class_exists('FasadBridge\\Includes\\PublicSettings')) {
            echo "Running sync...\n";
            flush();
            // Rensa lock innan sync
            delete_option('fasad-sync-lock');
            $public = new \FasadBridge\Includes\PublicSettings('fasad-bridge', '1.0.0');
            // force='all' tvingar uppdatering av alla objekt
            $force = isset($_GET['force']) ? 'all' : false;
            $public->doSync(['force' => $force, 'lock' => false]);
            echo "Sync complete.\n";
        } else {
            echo "FasadBridge not found.\n";
        }
        die();
    }
}, 1);

// Uppdatera _fasad_lastsync efter sync
add_action('fasad_bridge_post_sync', function($params, $syncResult) {
    update_option('_fasad_lastsync', time());
}, 10, 2);

// Registrera FasadBridge cron-schema
add_action('init', function() {
    $hookName = 'sync_all_listings';
    
    // Schemalägg daglig sync kl 05:00
    if (!wp_next_scheduled($hookName)) {
        $timezone = new \DateTimeZone('Europe/Stockholm');
        $date = new \DateTime('now', $timezone);
        $date->setTime(5, 0);
        wp_schedule_event($date->getTimestamp(), 'daily', $hookName);
    }
    
    // Koppla cron-hook
    add_action($hookName, function() {
        $plugins_dir = WP_CONTENT_DIR . '/plugins/';
        foreach (['fasad-bridge', 'fasad-api-connect', 'prek-web'] as $plugin) {
            $autoload = $plugins_dir . $plugin . '/vendor/autoload.php';
            if (file_exists($autoload)) require_once $autoload;
        }
        if (class_exists('FasadBridge\\Includes\\PublicSettings')) {
            delete_option('fasad-sync-lock');
            $public = new \FasadBridge\Includes\PublicSettings('fasad-bridge', '1.0.0');
            $public->registerCustomPostTypes();
            $public->doSync(['lock' => false, 'output' => 'log', 'source' => 'cron']);
        }
    });
}, 20);

// Stäng av _load_textdomain_just_in_time warnings från Acorn
add_filter('acorn/exceptions/ignore', function($exceptions) {
    return true; // Ignorera alla E_USER_NOTICE/E_USER_WARNING
});

// Ignorera textdomain warnings
add_action('init', function() {
    set_error_handler(function($errno, $errstr) {
        if (strpos($errstr, '_load_textdomain_just_in_time') !== false) {
            return true; // Ignorera
        }
        return false;
    }, E_USER_NOTICE | E_USER_WARNING | E_USER_DEPRECATED);
}, 1);

// Kontaktformulär - hantera POST
add_action('template_redirect', function() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
    global $wp;
    if (trim($wp->request, '/') !== 'kontakt-skicka') return;

    $namn      = sanitize_text_field($_POST['namn'] ?? '');
    $email     = sanitize_email($_POST['email'] ?? '');
    $telefon   = sanitize_text_field($_POST['telefon'] ?? '');
    $meddelande = sanitize_textarea_field($_POST['meddelande'] ?? '');
    $mottagare = sanitize_email($_POST['mottagare'] ?? get_option('admin_email'));

    if (empty($namn) || empty($email) || empty($meddelande)) {
        wp_redirect(home_url('/kontakt?error=1'));
        exit;
    }

    $subject = 'Nytt meddelande från ' . $namn . ' via PREK.se';
    $body = "Namn: $namn\n";
    $body .= "E-post: $email\n";
    if ($telefon) $body .= "Telefon: $telefon\n";
    $body .= "\nMeddelande:\n$meddelande";

    $headers = [
        'Content-Type: text/plain; charset=UTF-8',
        'From: ' . $namn . ' <' . $email . '>',
        'Reply-To: ' . $email,
    ];

    wp_mail($mottagare, $subject, $body, $headers);

    wp_redirect(home_url('/kontakt?success=1'));
    exit;
});

add_action('template_redirect', function() {
    if (!get_query_var('bildproxy')) return;
    
    $url = $_GET['url'] ?? '';
    if (empty($url)) die('No URL');
    
    // Tillåt bara FasAD-bilder
    if (!preg_match('#^https://images[0-9]*\.fasad\.eu/#', $url)) die('Invalid URL');
    
    $response = wp_remote_get($url, ['timeout' => 15]);
    if (is_wp_error($response)) die('Error');
    
    $content_type = wp_remote_retrieve_header($response, 'content-type') ?: 'image/jpeg';
    $body = wp_remote_retrieve_body($response);
    
    header('Content-Type: ' . $content_type);
    header('Cache-Control: public, max-age=86400');
    header('Access-Control-Allow-Origin: *');
    echo $body;
    die();
}, 1);

// Bildproxy för FasAD-bilder via REST API
add_action('rest_api_init', function() {
    register_rest_route('prek/v1', '/bildproxy', [
        'methods'             => 'GET',
        'callback'            => function($request) {
            $url = $request->get_param('url');
            if (empty($url) || !preg_match('#^https://images[0-9]*\.fasad\.eu/#', $url)) {
                return new \WP_Error('invalid', 'Invalid URL', ['status' => 400]);
            }
            $response = wp_remote_get($url, ['timeout' => 15]);
            if (is_wp_error($response)) {
                return new \WP_Error('fetch_error', 'Could not fetch image', ['status' => 500]);
            }
            $content_type = wp_remote_retrieve_header($response, 'content-type') ?: 'image/jpeg';
            $body = wp_remote_retrieve_body($response);
            header('Content-Type: ' . $content_type);
            header('Cache-Control: public, max-age=86400');
            echo $body;
            exit;
        },
        'permission_callback' => '__return_true',
    ]);
});

/**
 * Spekulantregister - hantera formulär
 */
add_action('template_redirect', function () {
    if (
        $_SERVER['REQUEST_METHOD'] !== 'POST' ||
        ($_POST['form_type'] ?? '') !== 'spekulant' ||
        !wp_verify_nonce($_POST['spekulant_nonce'] ?? '', 'spekulant_form')
    ) return;

    $namn   = sanitize_text_field($_POST['spekulant_namn'] ?? '');
    $email  = sanitize_email($_POST['spekulant_email'] ?? '');
    $soker  = sanitize_textarea_field($_POST['spekulant_soker'] ?? '');

    if ($namn && $email) {
        $to      = get_option('admin_email');
        $subject = 'Ny spekulantanmälan — ' . $namn;
        $body    = "Namn: {$namn}\nE-post: {$email}\n\nVad de söker:\n{$soker}";
        $headers = ['Content-Type: text/plain; charset=UTF-8', 'From: ' . get_bloginfo('name') . ' <' . $to . '>'];
        wp_mail($to, $subject, $body, $headers);
    }

    wp_redirect(add_query_arg('spekulant', 'success', get_permalink()));
    exit;
});
