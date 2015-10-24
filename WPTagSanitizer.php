<?php

/*
  Plugin Name: WPTagSanitizer
  Plugin URI: https://github.com/larjen/WPTagSanitizer
  Description: A WordPress plugin that sanitizes tags in posts.
  Author: Lars Jensen
  Version: 1.0.0
  Author URI: http://exenova.dk/
 */

class WPTagSanitizer {

    static $debug = false;
    static $plugin_name = "WPTagSanitizer";

    static function activation() {

        update_option("WPTagSanitizer_MESSAGES", []);

        $jsonTable = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "tagDictionary.json");

        update_option("WPTagSanitizer_JSONTABLE", $jsonTable);

        $transformTable = self::getTransformTable($jsonTable);

        if ($transformTable != false) {
            update_option("WPTagSanitizer_TRANSFORMTABLE", $transformTable);
        } else {
            update_option("WPTagSanitizer_TRANSFORMTABLE", array());
        }

        self::add_message("Activated the plugin.");
    }

    static function deactivation() {
        
    }

    static function getTransformTable($jsonObject) {

        if (empty($jsonObject)) {
            self::add_message("Empty JSON supplied, deactivate and reactivate the plugin to reset..");
            return false;
        }

        $jsonTable = json_decode($jsonObject);

        if ($jsonTable == NULL) {
            self::add_message("Invalid JSON supplied, deactivate and reactivate the plugin to reset..");
            return false;
        }

        $transformTable = [];

        foreach ($jsonTable as $key => $value) {

            // the key should now be the value
            foreach ($value as $tag) {
                $transformTable[strtoupper($tag)] = $key;
            }
        }
        return $transformTable;
    }

    static function add_message($message) {

        $messages = get_option("WPTagSanitizer_MESSAGES");
        array_push($messages, date("Y-m-d H:i:s") . " - " . $message);

        // keep the amount of messages below 10
        if (count($messages) > 100) {
            $temp = array_shift($messages);
        }

        update_option("WPTagSanitizer_MESSAGES", $messages);
    }

    public static function sanitizeTags($term) {

        $term = trim($term);
        $term = trim($term, ",");
        $term = trim($term, ".");
        $term = trim($term, ":");

        //if (self::$debug) {error_log('# sanitizeTags $term = ' . ($term));}
        //if (self::$debug) {error_log('# sanitizeTags $taxonomy = ' . ($taxonomy));}

        $transformTable = get_option("WPTagSanitizer_TRANSFORMTABLE");
        $checkTerm = strtoupper($term);

        //if (self::$debug) {error_log('# sanitizeTags $transformTable = ');}
        //if (self::$debug) {error_log(print_r($transformTable));}
        //if (self::$debug) {error_log('# sanitizeTags $checkTerm = ' . ($checkTerm));}
        //if (self::$debug) {error_log('# sanitizeTags array_key_exists($checkTerm, $transformTable) = ' . (array_key_exists($checkTerm, $transformTable)));}

        if (array_key_exists($checkTerm, $transformTable)) {
            $returnTerm = $transformTable[$checkTerm];
            //if (self::$debug) {error_log('# sanitizeTags ' . $term . ' -> ' . ($returnTerm));}
            return $returnTerm;
        } else {
            
            // if the tag already contains an uppercase character do nothing
            if(preg_match('/[A-Z]/', $term)){
                return $term;
            } else {
                // uppercase first letter and return the tag
                $term = ucwords($term);
            
                //if (self::$debug) {error_log('# sanitizeTags ' . $term . ' ');}
                return $term;
            }
        }
    }

    static function removeTags($tags) {
        foreach ($tags as $tag_id) {
            wp_delete_term($tag_id, 'post_tag');
        }
    }

    static function sanitizePost($post_id) {

        //if (self::$debug) {error_log('$post_id =' . $post_id);}

        $addTagList = [];
        $removeTagList = [];

        // get all tags from the post
        $tags = wp_get_post_tags($post_id);

        foreach ($tags as $value) {
            $tag = $value->name;
            $sanitizedTag = self::sanitizeTags($tag);
            if ($sanitizedTag != $tag) {
                //if (self::$debug) {error_log('tag that needs to change detected from ' . $tag . ' to ' . $sanitizedTag);}
                array_push($removeTagList, $value->term_id);
                array_push($addTagList, $sanitizedTag);
            } else {
                array_push($addTagList, $tag);
            }
        }

        // if there is tags in the removeTagList, we need to rebuild the tag list in the post
        if (!empty($removeTagList)) {

            // unhook this function so it doesn't loop infinitely
            remove_action('save_post', 'WPTagSanitizer::sanitizePost');

            // delete unwanted tags
            self::removeTags($removeTagList);

            // build the post
            $post = array(
                'ID' => $post_id,
                'tags_input' => $addTagList //For tags.
            );

            // update the post, which calls save_post again
            wp_update_post($post);


            // re-hook this function
            add_action('save_post', 'WPTagSanitizer::sanitizePost');
        }
    }

    static function plugin_menu() {
        add_management_page('WPTagSanitizer', 'WPTagSanitizer', 'activate_plugins', 'WPTagSanitizer', array('WPTagSanitizer', 'plugin_options'));
    }

    static function plugin_options() {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }

        if (isset($_POST["WPTagSanitizer_JSONTABLE"])) {
            $textAreaValue = stripslashes($_POST["WPTagSanitizer_JSONTABLE"]);
            $transformTable = self::getTransformTable($textAreaValue);

            if ($transformTable != false) {
                update_option("WPTagSanitizer_JSONTABLE", $textAreaValue);
                update_option("WPTagSanitizer_TRANSFORMTABLE", $transformTable);
            }
        } else {
            $textAreaValue = get_option("WPTagSanitizer_JSONTABLE");
        }

        // debug
        if (self::$debug) {
            echo '<pre>';
            echo 'get_option("WPTagSanitizer_MESSAGES")=' . print_r(get_option("WPTagSanitizer_MESSAGES")) . PHP_EOL;
            echo 'get_option("WPTagSanitizer_JSONTABLE")=' . print_r(get_option("WPTagSanitizer_JSONTABLE")) . PHP_EOL;
            echo 'get_option("WPTagSanitizer_TRANSFORMTABLE")=' . print_r(get_option("WPTagSanitizer_TRANSFORMTABLE")) . PHP_EOL;
            echo '</pre>';
        }

        $messages = get_option("WPTagSanitizer_MESSAGES");

        while (!empty($messages)) {
            $message = array_shift($messages);
            echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>' . $message . '</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Afvis denne meddelelse.</span></button></div>';
        }

        // since the messages has been shown, purge them.
        update_option("WPTagSanitizer_MESSAGES", []);

        // print the admin page
        echo '<div class="wrap">';
        echo '<h2>WPTagSanitizer</h2>';
        echo '<p>New tags will be normalized according to these rules. Normalization is case insensitive, however the resulting tag is not.';
        echo '<form method="post" action="">';
        echo '<textarea name="WPTagSanitizer_JSONTABLE" style="width: 50%;height: 400px;">' . $textAreaValue . '</textarea>';
        echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>';
        echo '</form></div>';
    }

}

// register activation and deactivation
register_activation_hook(__FILE__, 'WPTagSanitizer::activation');
register_deactivation_hook(__FILE__, 'WPTagSanitizer::deactivation');

// register wp hooks
add_action('admin_menu', 'WPTagSanitizer::plugin_menu');
add_action('save_post', 'WPTagSanitizer::sanitizePost');
