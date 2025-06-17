<?php
/*
Plugin Name: Divi Images ALT
Description: Rajoute les balises ALT aux images des modules Divi
Author: bullWeb - Beecom'
Version: 1.0
Author URI: https://www.beecommunication.fr/
*/

/* Automatically set the image Title & Alt-Text upon upload*/
add_action( 'add_attachment', 'my_set_image_meta_upon_image_upload' );
function my_set_image_meta_upon_image_upload( $post_ID ) {
    // Check if uploaded file is an image, else do nothing
    if ( wp_attachment_is_image( $post_ID ) ) {
        $my_image_title = get_post( $post_ID )->post_title;
        // Sanitize the title:  remove hyphens, underscores & extra spaces:
        $my_image_title = preg_replace( '%\s*[-_\s]+\s*%', ' ',  $my_image_title );
        // Sanitize the title:  capitalize first letter of every word (other letters lower case):
        $my_image_title = ucwords( strtolower( $my_image_title ) );
        // Create an array with the image meta (Title, Caption, Description) to be updated
        $my_image_meta = array(
            'ID'        => $post_ID,            // Specify the image (ID) to be updated
            'post_title'    => $my_image_title,     // Set image Title to sanitized title
        );
        // Set the image Alt-Text
        update_post_meta( $post_ID, '_wp_attachment_image_alt', $my_image_title );
        // Set the image meta (e.g. Title, Excerpt, Content)
        wp_update_post( $my_image_meta );
    }
}
/* Fetch image alt text from media library */
function get_image_alt_text($image_url) {
    if ( ! $image_url ) return '';
    if ( '/' === $image_url[0] )
    $post_id = attachment_url_to_postid(home_url() . $image_url);
    else
    $post_id = attachment_url_to_postid($image_url);
    $alt_text = get_post_meta($post_id, '_wp_attachment_image_alt', true);
    if ( '' === $alt_text )
    $alt_text = get_the_title($post_id);
    return $alt_text;
}
/* Update image alt text in module properties */
function update_module_alt_text( $attrs, $unprocessed_attrs, $slug ) {
    if ( ( $slug === 'et_pb_image' || $slug === 'et_pb_fullwidth_image' ) && '' === $attrs['alt'] )
        $attrs['alt'] = get_image_alt_text($attrs['src']);
    elseif ( $slug === 'et_pb_blurb' && 'off' === $attrs['use_icon'] && '' === $attrs['alt'] )
        $attrs['alt'] = get_image_alt_text($attrs['image']);
    elseif ( $slug === 'et_pb_slide' && '' !== $attrs['image'] && '' === $attrs['image_alt'] )
        $attrs['image_alt'] = get_image_alt_text($attrs['image']);
    elseif ( $slug === 'et_pb_fullwidth_header' ) {
        if ( '' !== $attrs['logo_image_url'] && '' === $attrs['logo_alt_text'] )
            $attrs['logo_alt_text'] = get_image_alt_text($attrs['logo_image_url']);
        if ( '' !== $attrs['header_image_url'] && '' === $attrs['image_alt_text'] )
            $attrs['image_alt_text'] = get_image_alt_text($attrs['header_image_url']);
    }
    return $attrs;
}
add_filter( 'et_pb_module_shortcode_attributes', 'update_module_alt_text', 20, 3 );