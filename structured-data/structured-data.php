<?php
/*
Plugin Name:  Advanza structured data
Plugin URI:   http://www.waydesign.nl/plugins
Description:  Generates a Json file with dynamic based data, from url. retrieves meta data from Yoast and social media from jetpack database
Version:      1.2
Author:       Gerard de way
Author URI:   http://www.waydesign.nl
Text Domain:  wporg
Domain Path:  /languages
*/

// Plugin updater
require 'plugin-update-checker/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
	'https://advandm297.297.axc.nl/api/advanza-structured-data.json',
	__FILE__,
	'advanza_structured_data_updater'
);

// Define global constants
$plugin_dir = plugin_dir_path( __FILE__ );


// Includes
include_once $plugin_dir . 'includes/strip_site_data.php';
include_once $plugin_dir . 'includes/jetpack_data_retrieve.php';
include_once $plugin_dir . 'includes/automatic_yoast_meta_data.php';


class advanza_structured_data
{
    // Plugin install
    static function install() {
        // do not generate any output here
    }
    public function __construct(){
        add_action('wp_footer', array($this, 'advanza_structured_data'));
    }
    /**
     * Create Json data in the footer, strip url for Json location information.
     * Import Yoast meta data and Jetpack social media data from classes
     */
    function advanza_structured_data(){
        // Build Json file
        $data = '{
            "@context": "http:\/\/schema.org",
            "@type": "Organization",
            "name": "example Offertes",
            "url": "https:\/\/example.com",
            "sameAs": ["https:\/\/www.facebook.com\/example", "https:\/\/twitter.com\/example", "https:\/\/www.linkedin.com\/company\/example-offertes", "https:\/\/plus.google.com\/example"],
            "logo": "https:\/\/example-offertes.com\/wp-content\/uploads\/2018\/03\/example-offertes.svg",
            "description": "Altijd de beste example offertes uit uw omgeving.",
            "address": {
                "@type": "PostalAddress",
                "addressCountry": "Netherlands"
            },
            "areaServed": "",
            "contactPoint": {
                "@type": "ContactPoint",
                "contactType": "Sales",
                "email": "info@example-offertes.com",
                "url": "https:\/\/example-offertes.com"
            }
        }';
    
        // Decode the json data in php readable code
        $jsonString = json_decode($data, true);

        // Get the data out of strip data class
        $strip_site_data = new strip_site_data();
        $stripped_url_data = $strip_site_data->strip_url_data();
        
        // Write the dynamic input from yoast and jetpack to the Json file, and append it to the page
        $jetpack_data_retrieve = new jetpack_data_retrieve();
        // Yoast meta data from .docx
        $automatic_yoast_meta_data = new automatic_yoast_meta_data();
        $yoast_data = $automatic_yoast_meta_data->automatic_yoast_meta_data();
        // Yoast meta data from wordpress
        $yoast_wp_data = $jetpack_data_retrieve->yoast_meta_data_fetch();
        // Check wether there is meta data in wp, if not try to get it out of the .docx
        $description = $yoast_wp_data;
        if (empty($yoast_wp_data)){
            $description = $yoast_data;
        }
        if (is_null($description)) {
            $description = '';
        }

        $jsonString['areaServed'] = $stripped_url_data['locFormatted'] . "Netherlands";
        $jsonString['name'] =  $stripped_url_data['homeFormatted'];
        $jsonString['url'] = $stripped_url_data['currentHome'];
        $jsonString['logo'] = $stripped_url_data['image'];
        $jsonString['description'] = $description;
        $jsonString['sameAs'] = $jetpack_data_retrieve->jetpack_database_fetch();
        $jsonString['contactPoint']['email'] = 'info@' . $stripped_url_data['homeReplaced'];
        $jsonString['contactPoint']['url'] = $stripped_url_data['currentHome'];

        // Re-encode the data
        $newJsonString = json_encode($jsonString, true);

        // Rewrite the data to readable data
        $new_data = '<script type="application/ld+json">' . strval($newJsonString) . '</script>';

        // Write the updated json file to the content and check if it is a page
        if (is_page() || is_singular()) {
            print $new_data;
        }
    }
} // End of class

// Declare instances

$advanza_structured_data = new advanza_structured_data();

// Report simple running errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);
    // add_action('wp_footer', 'advanza_structured_data', 100);
?>