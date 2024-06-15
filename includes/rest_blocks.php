<?php
namespace Rest_Buddy;

add_filter('acf/pre_save_block', function ($attributes) {
  if (empty($attributes['anchor'])) {
    $attributes['anchor'] = 'acf-block-' . uniqid('', true);
  }
  return $attributes;
});

function restbuddy_get_image($id)
{
  /**
   * This will return all image sizes, width, height
   * - custom sizes will be under `sizes`
   * - only the partial path is available for the untouched image so you will need to prepend with site url/wp-content/uploads/
   * - sizes only have the filename
   * - ...Wordpress is curiously dumb
   */
  $image = wp_get_attachment_metadata($id, TRUE);
  // this adds the alt text
  $image['alt'] = get_post_meta($id, '_wp_attachment_image_alt', TRUE);

  // Get full urls for image sizes:
  $image['url'] = wp_get_attachment_image_src($id, 'full')[0];

  foreach ($image['sizes'] as $size => &$value) {
    $image['sizes'][$size]['url'] = wp_get_attachment_image_src($id, $size)[0];
  }
  return $image;
}

// https://stackoverflow.com/questions/70852092/acf-gutemberg-rest-api
add_action(
  'rest_api_init',
  function () {

    if (!function_exists('use_block_editor_for_post_type')) {
      require ABSPATH . 'wp-admin/includes/post.php';
    }

    // Surface all Gutenberg blocks in the WordPress REST API
    $post_types = get_post_types_by_support(['editor']);
    foreach ($post_types as $post_type) {
      if (use_block_editor_for_post_type($post_type)) {
        // https://developer.wordpress.org/reference/functions/register_rest_field/

        register_rest_field($post_type, 'contentraw', ['get_callback' => function (array $post) {
          return $post['content']['raw'];
        }]);

        register_rest_field(
          $post_type,
          'all_blocks', // don't call this 'blocks' or else you can't edit them. I don't get it either.
          [
            'get_callback' => function (array $post) {
              // get the blocks
              $blocks = parse_blocks($post['content']['raw']);


              // Remove the null blocks - why does wordpress have these??!?
              // This breaks the WP editor - do not do this...
              // Will have to filter this out on the frontend.
              // $blocks = array_filter($blocks, function ($var) {
              // 	return !empty($var['blockName']);
              // });

              // run get_field on acf fields
              foreach ($blocks as &$block) {
                if (array_key_exists('data', $block['attrs']) and $block['blockName']) {

                  // https://support.advancedcustomfields.com/forums/topic/how-to-get-repeater-block-data-back-into-an-array/
                  // $block['attrs']['data']['id'] = acf_get_block_id( $block['attrs']['data'] ); // null
                  // acf_setup_meta( $block['attrs']['data'], $post['id'], true );
                  acf_setup_meta($block['attrs']['data'], $block['attrs']['anchor'], true);

                  foreach ($block['attrs']['data'] as $fieldname => &$value) {

                    // $block['attrs']['data'][$fieldname] = wp_kses_post(get_field($value));
                    if (true) {
                      // Go thru all the acf fields and _fields and apply get_field:
                      $value = get_field($value, $post['id']); // no, also no without post.id
                      $block['myacf'] = get_fields(); // false

                      // $value = $post['id']; // yes post ID is correct
                      // $value = class_exists('ACF'); // yes ACF is active
                      // $value = get_field($fieldname); // no
                    }

                    if (false && is_string($value) and str_starts_with($value, 'field_')) {
                      $realfield = substr($fieldname, 1); // remove the underscore to get the real fieldname
                      // $block['attrs']['data'][$realfield] = wp_kses_post( get_field( $block['attrs']['data'][$realfield] ) );
                      $block['attrs']['data']['acf' . $fieldname] = get_field($value);
                    }

                    // So now we have to enforce rules on field names so we know what to do:
                    // ACF Image field
                    if (false && str_starts_with($fieldname, 'image')) {
                      $block['acf_image'] = get_field($block['attrs']['data'][$fieldname]);
                      $block['attrs']['data'][$fieldname] = restbuddy_get_image($value);
                      // $realfield = substr($fieldname, 1);
                    }
                    acf_reset_meta($block['attrs']['anchor']);
                    // ACF Gallery field

                    // ACF File

                    // ACF Post relationship

                    // ACF Post Obj
                  }
                }
              }
              return $blocks;
            },
          ]
        );
      }
    }

    // If we want to have a flag existence of blocks like that other plugin...
    // register_rest_field($post_type, 'has_blocks', ['get_callback' => function (array $post) {
    // 	$blocks = parse_blocks($post['content']['raw']);
    // 	$blocks = array_filter($blocks, function ($var) {
    // 		return !empty($var['blockName']);
    // 	});
    // 	return count($blocks) > 0;
    // }]);

    // Featured images for pages and posts
    add_action('rest_api_init', function () {
      register_rest_field('pages', 'featured_image_src', array(
        'get_callback' => function ($post_arr) {
          $image_src_arr = wp_get_attachment_image_src($post_arr['featured_media'], 'medium');

          return $image_src_arr[0];
        },
        'update_callback' => null,
        'schema' => null
      ));
    });
    add_action('rest_api_init', function () {
      register_rest_field('post', 'featured_image_src', array(
        'get_callback' => function ($post_arr) {
          $image_src_arr = wp_get_attachment_image_src($post_arr['featured_media'], 'medium');

          return $image_src_arr[0];
        },
        'update_callback' => null,
        'schema' => null
      ));
    });
  }
);
