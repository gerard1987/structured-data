<?php
/*
Plugin Name:  Automatic yoast meta data
Plugin URI:   http://www.waydesign.nl/plugins
Description:  Get's the meta description out of a .docx file, and writes it to Yoast meta (If no Yoast meta data exists)
Version:      0.3
Author:       Gerard de way
Author URI:   http://www.waydesign.nl
Text Domain:  wporg
Domain Path:  /languages
*/
class automatic_yoast_meta_data
{
    // Plugin install
    static function install() {
        // do not generate any output here
    }    
    public function __construct(){
        add_action('wp_head', array($this, 'automatic_yoast_meta_data'), 100);
        add_filter('wpseo_metadesc', array($this, 'yoast_add_keywords'), 10, 1);
    }

    /**
     * Convert .docx file's to text
     */
    function readDocx($filePath) {
        // Create new ZIP archive
        $zip = new ZipArchive;
        $dataFile = 'word/document.xml';
        // Open received archive file
        if (true === $zip->open($filePath)) {
            // If done, search for the data file in the archive
            if (($index = $zip->locateName($dataFile)) !== false) {
                // If found, read it to the string
                $data = $zip->getFromIndex($index);
                // Close archive file
                $zip->close();
                // Load XML from a string
                // Skip errors and warnings
                $xml = new DOMDocument();
                $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING);
                // Return data without XML formatting tags
    
                $contents = explode('\n',strip_tags($xml->saveXML()));
                $text = '';
                foreach($contents as $i=>$content) {
                    $text .= $contents[$i];
                }
                return $text;
            }
            $zip->close();
        }
        // In case of failure return empty string
        return "";
    }

    /**
     * Retrieve meta description out of file, and write it to the Yoast meta description
     */
    function automatic_yoast_meta_data(){
    if (is_page() || is_singular()) {
        // Set variables for file location
        $currentDirectory = getcwd();
        $currentPage = $_SERVER['REQUEST_URI'];
        $currentFolder = '/teksten/meta/metadata.docx';
        $file = $currentDirectory . $currentFolder;

        // Set regex var, for stripping out page name
        $temp_trimmed = preg_replace('/[\/]/', '', $currentPage);
        $page_name_trimmed = preg_replace('/[-]/', ' ', $temp_trimmed);
        $page_name_trimmed = ucwords($page_name_trimmed);

        // Convert the document to text, and to html after that.
        $automatic_yoast_meta_data = new automatic_yoast_meta_data();
        $converted_document = $automatic_yoast_meta_data->readDocx($file);
        
        $html_document = html_entity_decode ($converted_document);
        
        // Retrieve the content and explode the array
        global $final_output;
        $text = explode('[placeholder dynamic text]', $html_document);
        foreach ($text as $item){
            if (!empty($page_name_trimmed) && strpos($item, $page_name_trimmed) !== false){
                $final_output = $item;
            }
        }
    }
        // function Return
        return $final_output;
    }

    // $final = automatic_yoast_meta_data();
    // Write to Yoast meta data
    function yoast_add_keywords( $str ) {
    if (is_page() || is_singular()) {        
        $automatic_yoast_meta_data = new automatic_yoast_meta_data;
        $final = $automatic_yoast_meta_data->automatic_yoast_meta_data();

        $yoast = get_post_meta(get_the_ID(), '_yoast_wpseo_metadesc', true);
        $newStr = $str;
        if (!empty($yoast)){
            $final = $yoast;
        } else {
            $final = $final;
        }
        $newStr = $final;
    }
        return $newStr;
    }

} // End of class
// $automatic_yoast_meta_data = new automatic_yoast_meta_data();