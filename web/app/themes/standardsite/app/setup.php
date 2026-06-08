<?php

/**
 * Theme setup.
 */

namespace App;

// ACF-fält hanteras via ACF Composer i app/Fields/

use App\View\Composers\App;

use function Roots\bundle;

/**
 * Register the theme assets.
 *
 * @return void
 */
add_action('wp_enqueue_scripts', function () {
    bundle('app')->enqueue()->localize('prekApiSettings', [
        '_fasad_lastsync' => get_option( '_fasad_lastsync', '' ),
        'uploads_path'    => wp_upload_dir()['baseurl'],
//        'template_path'   => get_template_directory_uri(),
    ]);
}, 100);

add_action('wp_footer', function () {
    //custom color
    $classNames        = [
                           'theme-custom',
                           'theme-custom-header',
                           'theme-custom-header-menu',
                           'theme-custom-footer',
                           'theme-custom-footer-menu'
                       ];
    $themeColors       = [];
    $themeColorsOption = App::getOption('theme-colors');
    /*
     * $themeColorsOption:
     * [
     *   'theme-colors_theme-custom-background' => '{hex}',
     *   'theme-colors_theme-custom-color' => '{hex}',
     * ]
     */
    foreach ($classNames as $className) {
        $types = ['background', 'color', 'border-top-color'];
        foreach ($types as $typeKey) {
            $optionKey = 'theme-colors_' . $className . '-' . $typeKey;
            if (!empty($themeColorsOption[$optionKey])) {
                $themeColors[$className][$typeKey] = $themeColorsOption[$optionKey];
            }
        }
    }
    /*
     * $themeColors:
     * [
     *   'theme-custom' => [
     *     'background' => '{hex}'
     *     'color' => '{hex}'
     *   ]
     * ]
     */
    if (!empty($themeColors)) {
        $custom_css = '';
        foreach ($themeColors as $className => $colors) {
            foreach ($colors as $key => $color) {
                $attr       = ('background' === $key) ? $key . '-color' : $key;
                $custom_css .= "." . $className . "-" . $key . " {" . PHP_EOL;
                $custom_css .= $attr . ": " . $color . ";" . PHP_EOL;
                $custom_css .= "}" . PHP_EOL;
            }
        }
        echo "<style>" . $custom_css . "</style>";
    }

    // Hamburger icon gets same color as menu text
    if (!empty($themeColors['theme-custom-header-menu']['color'])) {
        echo "<style>#burger-navigation-wrapper .burger-navigation-trigger span {background: " . $themeColors['theme-custom-header-menu']['color'] . ";}</style>";
    }

    echo "<style>.slick-loading .slick-list {background-image: url('".\Roots\asset('images/slick-ajax-loader.gif')."');}</style>";
}, 101);

/**
 * Register the theme assets with the block editor.
 *
 * @return void
 */
add_action('enqueue_block_editor_assets', function () {
    bundle('editor')->enqueue();
}, 100);

/**
 * Register the initial theme setup.
 *
 * @return void
 */
add_action('after_setup_theme', function () {
    /**
     * Enable features from the Soil plugin if activated.
     *
     * @link https://roots.io/plugins/soil/
     */
    add_theme_support('soil', [
        'clean-up',
        'nav-walker',
        'nice-search',
        'relative-urls',
    ]);

    /**
     * Disable full-site editing support.
     *
     * @link https://wptavern.com/gutenberg-10-5-embeds-pdfs-adds-verse-block-color-options-and-introduces-new-patterns
     */
    remove_theme_support('block-templates');

    /**
     * Register the navigation menus.
     *
     * @link https://developer.wordpress.org/reference/functions/register_nav_menus/
     */
    register_nav_menus([
        'primary_navigation' => __('Primary Navigation', 'sage'),
        'footer_navigation' => __('Footer Navigation', 'sage'),
    ]);

    /**
     * Disable the default block patterns.
     *
     * @link https://developer.wordpress.org/block-editor/developers/themes/theme-support/#disabling-the-default-block-patterns
     */
    remove_theme_support('core-block-patterns');

    /**
     * Enable plugins to manage the document title.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#title-tag
     */
    add_theme_support('title-tag');

    /**
     * Enable post thumbnail support.
     *
     * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
     */
    add_theme_support('post-thumbnails');

    /**
     * Enable responsive embed support.
     *
     * @link https://developer.wordpress.org/block-editor/how-to-guides/themes/theme-support/#responsive-embedded-content
     */
    add_theme_support('responsive-embeds');

    /**
     * Enable HTML5 markup support.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#html5
     */
    add_theme_support('html5', [
        'caption',
        'comment-form',
        'comment-list',
        'gallery',
        'search-form',
        'script',
        'style',
    ]);

    /**
     * Enable selective refresh for widgets in customizer.
     *
     * @link https://developer.wordpress.org/reference/functions/add_theme_support/#customize-selective-refresh-widgets
     */
    add_theme_support('customize-selective-refresh-widgets');
}, 20);

/**
 * Register the theme sidebars.
 *
 * @return void
 */
add_action('widgets_init', function () {
    $config = [
        'before_widget' => '<section class="widget %1$s %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3>',
        'after_title' => '</h3>',
    ];

    // do we want these later?
//    register_sidebar([
//        'name' => __('Primary', 'sage'),
//        'id' => 'sidebar-primary',
//    ] + $config);
//
//    register_sidebar([
//        'name' => __('Footer', 'sage'),
//        'id' => 'sidebar-footer',
//    ] + $config);
});

//function addPageToMenu($pageId, $pageTitle, $menuId, $parent = 0)
//{
//    wp_update_nav_menu_item(
//        $menuId,
//        0,
//        [
//            'menu-item-title'     => $pageTitle,
//            'menu-item-object'    => 'page',
//            'menu-item-object-id' => $pageId,
//            'menu-item-type'      => 'post_type',
//            'menu-item-status'    => 'publish',
//            'menu-item-parent-id' => $parent
//        ]
//    );
//}

/**
 * Performance: preload, preconnect och font-display optimeringar.
 */
add_action('wp_head', function () {
    // Preconnect till Google Fonts
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";

    // Google Fonts asynkront (icke-blockerande)
    echo '<link rel="preload" href="https://fonts.googleapis.com/css2?family=Cormorant:ital,wght@0,300;0,400;0,500;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap" as="style" onload="this.onload=null;this.rel=\'stylesheet\'">' . "\n";
    echo '<noscript><link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant:ital,wght@0,300;0,400;0,500;1,300;1,400&family=DM+Sans:opsz,wght@9..40,300;9..40,400;9..40,500&display=swap"></noscript>' . "\n";

    // Preload hero-bild (LCP-fix)
    $hero1 = content_url('uploads/oscars-hero1.jpg');
    echo '<link rel="preload" as="image" href="' . esc_url($hero1) . '" fetchpriority="high">' . "\n";

    // Preload CSS
    echo '<style>
        /* Font-display swap inline för snabbare text-rendering */
        @font-face { font-display: swap; }
    </style>' . "\n";
}, 1);

/**
 * Performance: ta bort onödiga WordPress-skript och stilar.
 */
add_action('wp_enqueue_scripts', function () {
    // Ta bort jQuery Migrate (inte nödvändig i produktion)
    // wp_deregister_script('jquery-migrate');

    // Ta bort Gutenberg block library CSS om det inte används
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('global-styles');
}, 100);


/**
 * Custom post type: Journal
 */
add_action('init', function () {
    register_post_type('journal', [
        'labels' => [
            'name'          => 'Journal',
            'singular_name' => 'Journalartikel',
            'add_new_item'  => 'Lägg till artikel',
            'edit_item'     => 'Redigera artikel',
        ],
        'public'       => true,
        'show_ui'      => true,
        'show_in_menu' => true,
        'supports'     => ['title', 'editor', 'thumbnail', 'excerpt'],
        'menu_icon'    => 'dashicons-book-alt',
        'has_archive'  => false,
        'rewrite'      => ['slug' => 'journal'],
    ]);
});

/**
 * Flush rewrite rules när nya CPTs registreras
 */
add_action('after_switch_theme', function () {
    flush_rewrite_rules();
});

add_action('init', function () {
    if (get_option('oscars_rewrite_flushed') !== '1') {
        flush_rewrite_rules();
        update_option('oscars_rewrite_flushed', '1');
    }
});

/**
 * ACF JSON — spara och ladda fältgrupper från temats acf-json-mapp
 */
add_filter('acf/settings/save_json', function () {
    return get_stylesheet_directory() . '/acf-json';
});

add_filter('acf/settings/load_json', function ($paths) {
    $paths[] = get_stylesheet_directory() . '/acf-json';
    return $paths;
});

/**
 * Visa lokalt registrerade ACF-fältgrupper i wp-admin
 */
add_filter('acf/settings/show_admin', '__return_true');


/**
 * Registrera ACF Composer Fields från temats app-mapp
 */
add_action('init', function () {
    if (class_exists('\Log1x\AcfComposer\AcfComposer')) {
        app(\Log1x\AcfComposer\AcfComposer::class)->registerPath(
            get_stylesheet_directory() . '/app'
        );
    }
}, 99);

/**
 * Visa ACF Composer lokala fältgrupper i wp-admin
 */
add_filter('acf/settings/show_admin', '__return_true');


/**
 * ACF Options-sida för webbplatsinställningar
 */
add_action('init', function () {
    if (function_exists('acf_add_options_page')) {
        acf_add_options_page([
            'page_title' => 'Webbplatsinställningar',
            'menu_title' => 'Inställningar',
            'menu_slug'  => 'acf-options',
            'capability' => 'edit_posts',
            'redirect'   => false,
            'icon_url'   => 'dashicons-admin-settings',
            'position'   => 2,
        ]);
    }
}, 20);

