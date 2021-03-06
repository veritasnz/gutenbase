<?php

/**
 * Theme Name: Boilerplate
 * Author: Sean Verity
 * @package boilerplate
 */

$bodyClass = array();

// Add .is-mobile to body classes
if (wp_is_mobile()) :
    $bodyClass[] = 'is-mobile';
endif;

// Add page slug to body classes
if (is_page()) :
    $page_slug = sanitize_post($GLOBALS['wp_the_query']->get_queried_object())->post_name;
    $bodyClass[] = 'page-' . $page_slug;
endif;

?>

<!doctype html>
<html <?php language_attributes(); ?>>

<head>
    <meta charset="<?php bloginfo("charset"); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="profile" href="https://gmpg.org/xfn/11">

    <?php wp_head(); ?>
</head>

<body <?php body_class($bodyClass); ?>>


    <?php
    get_template_part('parts/desktop-header', null, array('type' => 'fixed'));
    get_template_part('parts/desktop-header', null, array('type' => 'sticky'));

    get_template_part('parts/mobile-header');
    ?>