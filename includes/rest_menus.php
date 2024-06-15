<?php
/**
 * Creates the route /wp-json/rest-buddy/v1/menu-location and returns an array of menu-location slugs.
 * Add a name parameter to return a nest array of menu items: /wp-json/rest-buddy/v1/menu-location?name=header-menu
 */
namespace Rest_Buddy;

add_action(
  'init',
  function () {
    register_nav_menus(
      array(
        'header-menu' => __('Header Menu'),
        'footer-menu' => __('Footer Menu')
      )
    );
  }
);

function rb_menunested($menu, $id = 0)
{
  $submenu = [];
  foreach ($menu as $item) {
    if ($item->menu_item_parent == $id) {
      $item->children = rb_menunested($menu, $item->ID);
      $submenu[] = $item;
    }
  }
  if (count($submenu)) {
    return $submenu;
  } else {
    return false;
  }
}

/**
 * Menu location
 */
function rb_menu_location_callback($response)
{
  $locations = get_nav_menu_locations();
  if ($response->get_param('name')) {
    $menu_name = $response->get_param('name'); //menu slug
    $menu = wp_get_nav_menu_object($locations[$menu_name]);
    $menuitems = wp_get_nav_menu_items($menu->term_id, array('order' => 'DESC'));
    foreach ($menuitems as $key => $value) {
      unset($menuitems[$key]->guid);
      unset($menuitems[$key]->post_author);
      unset($menuitems[$key]->post_date);
      unset($menuitems[$key]->post_date_gmt);
      unset($menuitems[$key]->post_title);
      unset($menuitems[$key]->post_excerpt);
      unset($menuitems[$key]->post_content);
      unset($menuitems[$key]->post_content_filtered);
      unset($menuitems[$key]->comment_status);
      unset($menuitems[$key]->ping_status);
      unset($menuitems[$key]->post_password);
      unset($menuitems[$key]->post_name);
      unset($menuitems[$key]->to_ping);
      unset($menuitems[$key]->pinged);
      unset($menuitems[$key]->post_modified);
      unset($menuitems[$key]->post_modified_gmt);
      unset($menuitems[$key]->comment_count);
      unset($menuitems[$key]->filter);
      unset($menuitems[$key]->db_id);
      unset($menuitems[$key]->post_type);
      unset($menuitems[$key]->post_mime_type);
      unset($menuitems[$key]->object_id);
      unset($menuitems[$key]->object);
      unset($menuitems[$key]->type);
      unset($menuitems[$key]->type_label);
      unset($menuitems[$key]->attr_title);
      unset($menuitems[$key]->description);
      unset($menuitems[$key]->post_parent);
    }
    $nav = rb_menunested($menuitems);
    return rest_ensure_response($nav);
  } else {
    return rest_ensure_response($locations);
  }
}

/**
 * Menu rest api
 * https://dev.to/david_woolf/how-to-create-your-own-rest-routes-in-wordpress-32og
 */
add_action(
  'rest_api_init',
  function () {
    register_rest_route(
      'rest-buddy/v1', // namespace
      'menu-location', // route
      [
        'methods'  => \WP_REST_Server::READABLE, // GET
        'callback' => 'Rest_Buddy\rb_menu_location_callback',
        'permission_callback' => '__return_true',
      ],
      // override (false)
    );
  }
);
