<?php

/**
 * Theme Name: Gutenbase
 * Author: Sean Verity
 * Text Domain: gutenbase
 * Domain Path: /languages/
 * @package gutenbase
 */

if (!function_exists('gutenbase_setup')) :
    /**
     * Sets up theme defaults and registers support for various WordPress features.
     *
     * Note that this function is hooked into the after_setup_theme hook, which
     * runs before the init hook. The init hook is too late for some features, such
     * as indicating support for post thumbnails.
     */
    function gutenbase_setup()
    {

        // Add default posts and comments RSS feed links to head.
        add_theme_support('automatic-feed-links');

        // Delete Gutenburg core block patterns.
        remove_theme_support('core-block-patterns');

        /*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
        add_theme_support('title-tag');

        /*
		 * Enable support for Post Thumbnails on posts and pages.
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
        add_theme_support('post-thumbnails');

        // This theme uses wp_nav_menu() in one location.
        register_nav_menus(array(
            'menu-1' => esc_html__('Primary', 'gutenbase'),
        ));

        /*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
        add_theme_support('html5', array(
            'search-form',
            'comment-form',
            'comment-list',
            'gallery',
            'caption',
        ));

        // Setup text-domain for translation
        load_theme_textdomain('gutenbase', get_template_directory() . '/languages');
    }
endif;

add_action('after_setup_theme', 'gutenbase_setup');

/**
 * Set the content width in pixels, based on the theme's design and stylesheet.
 *
 * Priority 0 to make it available to lower priority callbacks.
 *
 * @global int $content_width
 */
function gutenbase_content_width()
{
    // This variable is intended to be overruled from themes.
    // Open WPCS issue: {@link https://github.com/WordPress-Coding-Standards/WordPress-Coding-Standards/issues/1043}.
    // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
    $GLOBALS['content_width'] = apply_filters('gutenbase_content_width', 640);
}
add_action('after_setup_theme', 'gutenbase_content_width', 0);

/* Custom Code
これからは　カスタムファンクション
■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■■ */

/* Queueing for Header scripts and styles.
----------------------------------------------- */
remove_action('wp_print_styles', 'print_emoji_styles');
remove_action('wp_head', 'print_emoji_detection_script', 7); // Emoji に関するファイルを読み込まないように
function gutenbase_enqueue_assets() // Emoji に関するファイルを読み込まないように
{
    if (!is_admin()) {
        //== REMOVE DEFAULT JQUERY
        //Remove default JQuery and wp-embed　ー　古いjqueryのバージョンと、wp-embedを読み込まないように 
        wp_deregister_script('jquery');
        wp_deregister_script('wp-embed');

        //== CRITICAL CSS
        wp_register_style('common-style', get_template_directory_uri() . '/style.css');
        wp_enqueue_style('common-style');

        //=== FONTAWESOME
        wp_register_style('font-awesome', get_template_directory_uri() . '/vendor/fontawesome/css/all.min.css');
        wp_enqueue_style('font-awesome');

        //=== NEW JQUERY
        //Needed for Slick, not needed for Swiper
        wp_register_script('jquery-new', get_template_directory_uri() . '/vendor/jquery.min.js', array(), false, true ); //load into footer
        wp_enqueue_script('jquery-new');

        //=== SLICK
        //Scripts
        wp_register_script('slick-script', get_template_directory_uri() . '/vendor/slick/slick.js', array(), false, true ); //load into footer
        wp_enqueue_script('slick-script');
        //=== OFI
        //If IE, load OFI
        if (preg_match("~MSIE|Internet Explorer~i", $_SERVER["HTTP_USER_AGENT"]) || preg_match("~Trident/7.0(; Touch)?; rv:11.0~", $_SERVER["HTTP_USER_AGENT"])) :
            wp_register_script('ofi-script', get_template_directory_uri() . '/vendor/ofi.min.js', array(), false, true ); //load into footer
            wp_enqueue_script('ofi-script');
        endif;
    }
}
add_action('wp_enqueue_scripts', 'gutenbase_enqueue_assets', 1); //load before block editor

/* Script Preloader.
macarthur.me/posts/preloading-javascript-in-wordpress
----------------------------------------------- */
add_action('wp_head', function () {
    global $wp_scripts;
    foreach ($wp_scripts->queue as $handle) {
        $script = $wp_scripts->registered[$handle];
        $source = $script->src . ($script->ver ? "?ver={$script->ver}" : ""); // If version is set, append to end of source.
        echo "<link rel='preload' href='{$source}' as='script'/>\n"; // Spit out the tag.
    }
}, 1);

/* Script Defer/Async.
wordpress.stackexchange.com/questions/359599/add-extra-parameter-in-script-tag-using-script-loader-tag
----------------------------------------------- */
function add_attribute_to_script_tag($tag, $handle) {
    # add script handles to the array below
    $scripts_to_defer = array(
        'ofi-script',
        'jquery-new',
        'slick-script',
    );
    foreach($scripts_to_defer as $defer_script) {
       if ($defer_script === $handle) {
          return str_replace(' src', ' defer src', $tag);
       }
    }
    return $tag;
 }
 add_filter('script_loader_tag', 'add_attribute_to_script_tag', 10, 2);

/* Add ACF options page
----------------------------------------------- */
if (function_exists('acf_add_options_page')) {
    acf_add_options_page(array(
        'page_title'     => 'サイト設定',
        'menu_title'    => 'サイト設定',
        'menu_slug'     => 'site-settings',
        'capability'    => 'edit_posts',
        'redirect'        => false
    ));
    acf_add_options_page(array(
        'page_title'     => 'トップページ設定',
        'menu_title'    => 'トップページ設定',
        'menu_slug'     => 'top-settings',
        'capability'    => 'edit_posts',
        'redirect'        => false
    ));
}


/* Function for retrieving top parent ID
一番上の親のIDをえるファンクション
http://www.stevendobbelaere.be/get-the-current-pages-parent-page-id-in-wordpress/
----------------------------------------------- */
function get_top_parent_page_id()
{
    global $post;
    if ($post->ancestors) {
        return end($post->ancestors);
    } else {
        return $post->ID;
    }
}


/* Overide default <title>
----------------------------------------------- */
function theme_slug_filter_wp_title($title_parts)
{
    if (is_404()) {
        $title_parts['title'] = 'ページが見つかりません';
    }
    return $title_parts;
}
add_filter('document_title_parts', 'theme_slug_filter_wp_title');


/* Custom is_mobile() function
デバイス条件
----------------------------------------------- */
function is_mobile()
{
    $useragents = array(
        'iPhone', // iPhone
        'iPod', // iPod touch
        'Android.*Mobile', // 1.5+ Android *** Only mobile
        'Windows.*Phone', // *** Windows Phone
        'dream', // Pre 1.5 Android
        'CUPCAKE', // 1.5+ Android
        'blackberry9500', // Storm
        'blackberry9530', // Storm
        'blackberry9520', // Storm v2
        'blackberry9550', // Storm v2
        'blackberry9800', // Torch
        'webOS', // Palm Pre Experimental
        'incognito', // Other iPhone browser
        'webmate' // Other iPhone browser
    );
    $pattern = '/' . implode('|', $useragents) . '/i';
    return preg_match($pattern, $_SERVER['HTTP_USER_AGENT']);
}


/* Retrieve page URL using slug
ページスラグで URLを得るファンクション
----------------------------------------------- */
function get_url_by_slug($page_slug)
{
    $page = get_page_by_path($page_slug);
    if ($page) {
        $pageID = $page->ID;
        return get_page_link($pageID);
    } else {
        echo 'Page not found';
        return null;
    }
}


/* Dequeue loading of default Gutenburg stylesheet (for sites using legacy editors)
----------------------------------------------- */
function smartwp_remove_wp_block_library_css()
{
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
}
add_action('wp_enqueue_scripts', 'smartwp_remove_wp_block_library_css', 100);


/* Add browser type to body class
www.wpbeginner.com/wp-themes/how-to-add-user-browser-and-os-classes-in-wordpress-body-class/
----------------------------------------------- */
function mv_browser_body_class($classes)
{
    global $is_lynx, $is_gecko, $is_IE, $is_opera, $is_NS4, $is_safari, $is_chrome, $is_iphone;
    if ($is_lynx) $classes[] = 'lynx';
    elseif ($is_gecko) $classes[] = 'gecko';
    elseif ($is_opera) $classes[] = 'opera';
    elseif ($is_NS4) $classes[] = 'ns4';
    elseif ($is_safari) $classes[] = 'safari';
    elseif ($is_chrome) $classes[] = 'chrome';
    elseif ($is_IE) {
        $classes[] = 'ie';
        if (preg_match('/MSIE ([0-9]+)([a-zA-Z0-9.]+)/', $_SERVER['HTTP_USER_AGENT'], $browser_version))
            $classes[] = 'ie' . $browser_version[1];
    } else $classes[] = 'unknown';
    if ($is_iphone) $classes[] = 'iphone';
    if (stristr($_SERVER['HTTP_USER_AGENT'], "mac")) {
        $classes[] = 'osx';
    } elseif (stristr($_SERVER['HTTP_USER_AGENT'], "linux")) {
        $classes[] = 'linux';
    } elseif (stristr($_SERVER['HTTP_USER_AGENT'], "windows")) {
        $classes[] = 'windows';
    }
    return $classes;
}
add_filter('body_class', 'mv_browser_body_class');
