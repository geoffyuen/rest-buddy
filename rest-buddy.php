<?php
/**
 * Plugin Name: REST Buddy
 * Plugin URI: https://github.com/geoffyuen/rest-buddy
 * Description: Modifies WP REST-API post GET response to include array of blocks used on a post, acf data
 * Version: 0.0.1
 * Author: Geoff Yuen
 * Author URI: https://geoffyuen.com
 * License: GPL3
 */
// namespace Rest_Buddy;
include_once('includes/my_log_file.php');

include_once('includes/rest_featured_image.php');

// http://yaml.test/wp-json/wp/v2/pages?slug=sample-page
include_once('includes/rest_blocks.php');

// http://yaml.test/wp-json/rest-buddy/v1/menu-location
include_once('includes/rest_menus.php');

include_once('includes/settings_admin.php');

/**
 * NOTES:
 *
 * Filter fields returned:
 * https://developer.wordpress.org/rest-api/using-the-rest-api/global-parameters/#_fields
 * via: https://medium.com/@bike.challenge/creating-a-website-with-nuxt-js-and-wordpress-rest-api-cf37c6f4bc2b
 *
 * Example 1:
 * /wp/v2/posts?_fields=author,id,excerpt,title,link
 *
 * Example 2:
 * content,all_blocks <-- you need this pair because all_blocks depends on content...?
 * http://yaml.test/wp-json/wp/v2/pages?slug=sample-page&_fields=author,id,date,modified,slug,title,link,content,all_blocks
*/