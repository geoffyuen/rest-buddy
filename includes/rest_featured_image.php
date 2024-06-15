<?php
namespace Rest_Buddy;

add_action(
    'rest_api_init',
    function () {
        register_rest_field(
            array('post'),
            'fimg_url',
            array(
                'get_callback'    => function ($object, $field_name, $request) {
                    if ($object['featured_media']) {
                        $img = wp_get_attachment_image_src($object['featured_media'], 'app-thumb');
                        return $img[0];
                    }
                    return false;
                },
                'update_callback' => null,
                'schema'          => null,
            )
        );
    }
);
