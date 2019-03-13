<?php
class jetpack_data_retrieve
{ 
    /**
     * Get Jetpack data from database, and retrieve social media url's
     */
    function jetpack_database_fetch(){
        $social_data = [];
        global $wpdb;
        $results = $wpdb->get_results( "SELECT * FROM `{$wpdb->prefix}options` WHERE option_name = 'jetpack_options'");
        // get object property out of the array
        $get_data = $results[0]->option_value;
        // Deserialize the data from database
        $php_data = unserialize($get_data);
        // Loop thrue the array, and get the url data
        if ($php_data['publicize_connections']['facebook'] != null) {
            foreach($php_data['publicize_connections']['facebook'] as $value) {
                $temp = $value['connection_data']['meta']['link'];
                array_push($social_data, $temp);
            }
        }
        if ($php_data['publicize_connections']['twitter'] != null) {        
            foreach($php_data['publicize_connections']['twitter'] as $value){
                $temp = $value['connection_data']['meta']['link'];
                array_push($social_data, $temp);
            }
        }
        // Deprecated
        if ($php_data['publicize_connections']['google_plus'] != null){
            foreach($php_data['publicize_connections']['google_plus'] as $value){
                $temp = $value['external_id'];
                $temp = 'https://plus.google.com/' . $temp;
                array_push($social_data, $temp); 
            }
        }

        return $social_data;
    }
    /**
     * Get the Yoast meta data out of wordpress
     */
    function yoast_meta_data_fetch(){
        // Yoast meta description
        $yoast = get_post_meta(get_the_ID(), '_yoast_wpseo_metadesc', true); 
        return $yoast;
    }
} // End of class