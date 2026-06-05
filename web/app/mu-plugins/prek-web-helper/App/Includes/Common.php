<?php

namespace PrekWebHelper\Includes;

class Common
{
    protected $loader;
    private array $scripts = [];

    public function __construct(Loader $loader)
    {
        $this->loader = $loader;
    }

    public function run()
    {
        //$this->setupScripts();
        $this->uploads();
        $this->removeEmoji();
        $this->removeGutenberg();
        $this->cron();
        $this->log();
        $this->prekAvatar();

        $this->loader->addAction('wp_head', $this, 'wpHeadAddFasadData');
    }

    /*
     * Disabled until we have something useful to put in prekwebhelper.js
     *
    private function setupScripts()
    {
        $this->loader->addAction('wp_enqueue_scripts', $this, 'doEnqueueScripts');
    }

    public function doEnqueueScripts()
    {
        $deps = ['jquery'];
        $script = new \stdClass();
        $script->handle = 'prekwebhelper/prekwebhelper.js';
        $script->file   = 'prekwebhelper.js';
        $script->deps   = $deps;
        $script->inline = PHP_EOL . "Prek.WebHelper.init();";

        $this->scripts[] = apply_filters('prek_web_helper_scripts', $script);

        $pluginData = \PrekWebHelper\PrekWebHelper::getInstance()->getPluginData();
        foreach ($this->scripts as $script) {
            wp_enqueue_script($script->handle, plugins_url('assets/scripts/' . $script->file, dirname(__FILE__)), $script->deps, $pluginData->version ?? 1.0, true);

            if (!empty($script->localize)) {
                wp_localize_script( $script->handle, $script->localize['objectName'], $script->localize['data']);
            }
            if (!empty($script->inline)) {
                wp_add_inline_script($script->handle, $script->inline);
            }
        }
    }
    */

    private function uploads()
    {
        $this->loader->addFilter('upload_mimes', $this, 'overrideFiletypes');
        $this->loader->addFilter('sanitize_file_name', $this, 'sanitizeFilename');
    }
    public function overrideFiletypes($mimes)
    {
        $mimes['svg'] = 'image/svg+xml';
        return $mimes;
    }

    // Sanitize file upload filenames
    public function sanitizeFilename($filename)
    {

        $sanitizedFilename = remove_accents($filename); // Convert to ASCII

        // Standard replacements
        $invalid = [
            ' '   => '-',
            '%20' => '-',
            '_'   => '-'
        ];
        $sanitizedFilename = str_replace(array_keys($invalid), array_values($invalid), $sanitizedFilename);

        $sanitizedFilename = preg_replace('/[^A-Za-z0-9-\. ]/', '', $sanitizedFilename); // Remove all non-alphanumeric except .
        $sanitizedFilename = preg_replace('/\.(?=.*\.)/', '', $sanitizedFilename); // Remove all but last .
        $sanitizedFilename = preg_replace('/-+/', '-', $sanitizedFilename); // Replace any more than one - in a row
        $sanitizedFilename = str_replace('-.', '.', $sanitizedFilename); // Remove last - if at the end
        $sanitizedFilename = strtolower($sanitizedFilename); // Lowercase

        // Rename ACF json files like group-5d0751e33da9b.json to group_5d0751e33da9b.json
        $sanitizedFilename = preg_replace('/group-(.*)\.json/', 'group_$1.json', $sanitizedFilename);

        return $sanitizedFilename;
    }

    private function removeEmoji()
    {
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
    }

    /**
     * Remove CSS and SVG that are not removed by Classic Editor or Classic Widgets
     */
    private function removeGutenberg()
    {
        add_action('wp_enqueue_scripts', function () {
            wp_dequeue_style('global-styles');
            wp_dequeue_style('wp-block-library');
            wp_dequeue_style('wp-block-library-theme');
        });
        add_action('init', function () {
            remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
            remove_action('wp_footer', 'wp_enqueue_global_styles', 1);
        });
        remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
    }

    public function wpHeadAddFasadData()
    {
        $listingId = null;
        $isSingleListing = false;
        $hasStarter = \PrekWebHelper\Includes\Helpers::hasStarter();
        $hasBridge = \PrekWebHelper\Includes\Helpers::hasBridge();
        if ($hasStarter) {
            $fasadStarter = \FasadStarter::getInstance();
            $isSingleListing = is_singular($fasadStarter->cptName);
            $isPreview       = $fasadStarter->isPreview;
            if ($isSingleListing || $isPreview) {
                if ($wpId = get_the_ID()) {
                    $listingId = $fasadStarter->getFasadId($wpId);
                } else {
                    global $wp;
                    $listingId = !empty($wp->query_vars[$fasadStarter->cptName]) ? $wp->query_vars[$fasadStarter->cptName] : null;
                }
            }
        } elseif ($hasBridge && class_exists('PrekWeb\\Includes\\Fasad')) {
            $isSingleListing = \PrekWeb\Includes\Fasad::isSingleListing();
            if ($isSingleListing) {
                if ($wpId = get_the_ID()) {
                    $listingId = \PrekWeb\Includes\Fasad::getFasadId($wpId);
                } else {
                    $listingId = \PrekWeb\Includes\Fasad::isPreview();
                }
            }
        }
        $listingId = apply_filters('prek_web_helper_listing_id', $listingId, $hasStarter, $hasBridge, $isSingleListing);
        if ($listingId) {
            echo '<meta name="listing" content="' . $listingId . '" />' . PHP_EOL;
        }
    }

    /**
     * Drop in replacement for the deprecated get_page_by_title()
     *
     * @param string $page_title
     * @param $output
     * @param string $post_type
     * @return array|null
     */
    public static function getPageByTitle($page_title, $output = OBJECT, $post_type = 'page')
    {
        $query = new \WP_Query(
            [
                'post_type'              => $post_type,
                'title'                  => $page_title,
                'post_status'            => 'all',
                'posts_per_page'         => 1,
                'no_found_rows'          => true,
                'ignore_sticky_posts'    => true,
                'update_post_term_cache' => false,
                'update_post_meta_cache' => false,
                'orderby'                => 'date',
                'order'                  => 'ASC',
            ]
        );

        if (!empty($query->post)) {
            $_post = $query->post;

            if (ARRAY_A === $output) {
                return $_post->to_array();
            } elseif (ARRAY_N === $output) {
                return array_values($_post->to_array());
            }

            return $_post;
        }

        return null;
    }

    /*
     * Handle requests to cron on staging
     */
    private function cron()
    {
        add_filter('cron_request', function ($request, $doing_wp_cron = true) {
            if (
                function_exists('wp_get_environment_type') &&
                function_exists('env') &&
                wp_get_environment_type() === 'staging' &&
                Helpers::getEnv('HTACCESS_USER') &&
                Helpers::getEnv('HTACCESS_PASS')
            ) {
                $request['url'] = str_replace('http://', 'http://' . Helpers::getEnv('HTACCESS_USER') . ':' . Helpers::getEnv('HTACCESS_PASS') . '@', $request['url']);
            }
            return $request;
        }, 10, 2);
    }

    private function log()
    {
        add_filter('doing_it_wrong_trigger_error', function($trigger, $function_name, $message, $version) {
            /* Avoid this error flooding the logs in prod */
            if ($function_name === '_load_textdomain_just_in_time' && wp_get_environment_type() !== 'development') {
                return false;
            }
            return $trigger;
        }, 10, 4);
    }

    private function prekAvatar()
    {
        $this->loader->addAction('get_avatar', $this, 'prekAvatarAction', 10, 6);
    }

    public function prekAvatarAction(string $avatar, $id_or_email, int $size, string $default, string $alt, array $args)
    {

        $email = '';
        if (is_numeric($id_or_email)) {
            $user = get_user_by('id', $id_or_email);
            $email = $user->data->user_email;
        } elseif (is_string($id_or_email)){
            $email = $id_or_email;
        } elseif ($id_or_email instanceof \WP_User){
            $email = $id_or_email->data->user_email;
        }

        if (preg_match('/@prek\.se$/', $email)) {
            $avatar = '<img src="' . plugin_dir_url(__DIR__) . 'assets/images/avatar.png" alt="prek-web-helper" class="avatar avatar-32 photo" height="32" width="32" loading="lazy">';
        }
        return $avatar;
    }

    /*
     * Return builddata if exists
     */
    public function getBuildData()
    {
        $data  = [
            'id'   => '',
            'time' => '',
        ];
        $build = get_option('build');
        if (!is_array($build)) {
            return false;
        }
        foreach ($data as $key => $value) {
            if (!empty($build[$key])) {
                $data[$key] = $build[$key];
            }
        }
        return $data;
    }

    /*
     * Return version based on build, for use with enqueue_script, defaults to last modified time of file
     * example: wp_enqueue_script($handle, $src, $deps, PrekWebHelper::getInstance()->common->scriptVersion());
     */
    public function scriptVersion($file = __FILE__)
    {
        return self::buildVersion() !== 0 ? self::buildVersion() : filemtime(__FILE__);
    }

    /*
     * Return version based on build, defaults to 0
     */
    public function buildVersion()
    {
        $buildData = self::getBuildData();
        return ($buildData && !empty($buildData['id'])) ? $buildData['id'] : 0;
    }
}
