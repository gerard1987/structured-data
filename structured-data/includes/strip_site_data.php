<?php
class strip_site_data
{
    /**
     * Get an array of a csv file of city names 
     * to determine wether its a valid city name
     */
    function check_location(){

        // Set variables for location
        $currentDirectory = getcwd();
        $Loc_url = plugin_dir_url( __FILE__ ) . 'steden.csv';
        // Turn the csv file into a array
        $csv = array_map('str_getcsv', file($Loc_url));
    
        // Remove whitespace's and set case insensitive for check in array
        $new_csv = [];
        foreach ($csv as $item) {
            $new_item = trim(strtolower($item[0]));
            array_push($new_csv, $new_item);
        }

        return $new_csv;
    }

    /**
     * Set values for file locations, 
     * and format the data for use in the Json.
     */
    function strip_url_data(){
        // Set location variables
        $currentPage = $_SERVER['REQUEST_URI'];
        $currentDirectory = getcwd();
        $currentHome = get_home_url();

        // Get the current logo, and remove caching for the logo
        $custom_logo_id = get_theme_mod( 'custom_logo');
        add_filter( 'jetpack_photon_override_image_downsize', '__return_true' );
        $image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
        remove_filter( 'jetpack_photon_override_image_downsize', '__return_true' );
        $image = $image[0];

        if (!isset($image) || trim($image) === ''){
            $image = '';
            // Image is empty or null. 
        }

        // Strip url of mime type and format for email and site name
        $homeReplaced = str_replace('https://', '', $currentHome);
        $withoutExtension = substr($homeReplaced, 0, strrpos($homeReplaced, "."));
        $homeTrimmed = preg_replace("/[\W\-]/", ' ', $withoutExtension);

        // Get site title
        $site_info = get_bloginfo('name');
        if (!empty($site_info)){
            $homeFormatted = $site_info;
        } elseif (empty($site_info)){
            $homeFormatted = ucfirst($homeTrimmed);
        }

        // Strip the location name after the slash, and replace slashes for area served
        $preg_result = [];
        preg_match('/-(.*)/', $currentPage, $preg_result);
        $loc_name = str_replace('.php', '', $preg_result[1]);
        $loc_Trimmed = preg_replace("/[\W\-]/", ' ', $loc_name);
        $locFormatted = ucfirst($loc_Trimmed);
        
        // Check wether the location slug of the page, exist in the location csv
        $strip_site_data = new strip_site_data();
        $new_csv = $strip_site_data->check_location();

        if (in_array(trim(strtolower($loc_Trimmed)), $new_csv)) {
            $locFormatted = trim(ucfirst($loc_Trimmed)) . ', ';
        } else {
            $locFormatted = '';
        }
        // Return associated array for multiple function calls
        return array('locFormatted' => $locFormatted,
                     'loc_Trimmed' => $loc_Trimmed,
                     'homeFormatted' => $homeFormatted,
                     'currentHome' => $currentHome,
                     'image' => $image,
                     'homeReplaced' => $homeReplaced,
        );
    }
}// End of class
