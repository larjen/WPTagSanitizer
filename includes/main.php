<?php

class WPTagSanitizer {

    static $debug = false;
    static $plugin_name = "WPTagSanitizer";

    /*
     * Activate plugin
     */
    
    static function activation() {

        update_option(self::$plugin_name . "_MESSAGES", []);

        $jsonTable = file_get_contents(__DIR__ . DIRECTORY_SEPARATOR . "tagDictionary.json");

        update_option(self::$plugin_name . "_JSONTABLE", $jsonTable);

        $transformTable = self::getTransformTable($jsonTable);

        if ($transformTable != false) {
            update_option(self::$plugin_name . "_TRANSFORMTABLE", $transformTable);
        } else {
            update_option(self::$plugin_name . "_TRANSFORMTABLE", array());
        }

        self::add_message("Activated the plugin.");
    }

    /*
     * Deactivate plugin
     */
    
    static function deactivation() {
        delete_option(self::$plugin_name . "_MESSAGES");
        delete_option(self::$plugin_name . "_JSONTABLE");
        delete_option(self::$plugin_name . "_TRANSFORMTABLE");
    }

    /*
     * Helper function to write to log
     */
    
    static function add_message($message) {
        $messages = get_option(self::$plugin_name . "_MESSAGES");
        array_push($messages, date("Y-m-d H:i:s") . " - " . $message);

        // keep the amount of messages below 10
        if (count($messages) > 10) {
            $temp = array_shift($messages);
        }

        update_option(self::$plugin_name . "_MESSAGES", $messages);
    }

    /*
     * Transforms a jsonObject into a map of tags that can change
     */

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


        self::add_message("Tag mapping has been imported, and definitions will now be applied to future tags.");

        return $transformTable;
    }

    /*
     * Takes a string validates it against the tag cache, and returns either a
     * new string with the tag name or if not found the original string
     */

    public static function sanitizeTag($term) {

        $term = trim($term);
        $term = trim($term, ",");
        $term = trim($term, ".");
        $term = trim($term, ":");

        //if (self::$debug) {error_log('# sanitizeTag $term = ' . ($term));}
        //if (self::$debug) {error_log('# sanitizeTag $taxonomy = ' . ($taxonomy));}

        $transformTable = get_option(self::$plugin_name . "_TRANSFORMTABLE");
        $checkTerm = strtoupper($term);

        //if (self::$debug) {error_log('# sanitizeTag $transformTable = ');}
        //if (self::$debug) {error_log(print_r($transformTable));}
        //if (self::$debug) {error_log('# sanitizeTag $checkTerm = ' . ($checkTerm));}
        //if (self::$debug) {error_log('# sanitizeTag array_key_exists($checkTerm, $transformTable) = ' . (array_key_exists($checkTerm, $transformTable)));}

        if (array_key_exists($checkTerm, $transformTable)) {
            $returnTerm = $transformTable[$checkTerm];
            //if (self::$debug) {error_log('# sanitizeTag ' . $term . ' -> ' . ($returnTerm));}
            return $returnTerm;
        } else {

            // if the tag already contains an uppercase character do nothing
            if (preg_match('/[A-Z]/', $term)) {
                return $term;
            } else {
                // uppercase first letter and return the tag
                $term = ucwords($term);

                //if (self::$debug) {error_log('# sanitizeTag ' . $term . ' ');}
                return $term;
            }
        }
    }

    /*
     * Deletes a tag from WordPress 
     */
    
    static function removeTags($tags) {
        foreach ($tags as $tag_id) {
            wp_delete_term($tag_id, 'post_tag');
        }
    }

    /*
     * Scans a string for hastags and returns an array of normalized tags.
     */

    static function getTagsFromString($string) {

        $stringArray = explode(" ", $string);
        $returnArray = array();

        foreach ($stringArray as $key => $value) {
            if (substr($value, 0, 1) == "#") {

                // we found a tag, add it to tags
                $tag = substr($value, 1, (strlen($value) - 1));

                // check if last char in tag is , if it is delete it
                $tag = trim($tag);
                $tag = trim($tag, ",");
                $tag = trim($tag, ".");
                $tag = trim($tag, ":");

                // Normalize the tag
                $tag = self::sanitizeTag($tag);
                array_push($returnArray, $tag);
            }
        }
        return $returnArray;
    }

    /*
     * Whenever a post is saved, the tags from the post is sanitized in this
     * function.
     */

    static function sanitizePost($post_id) {

        //if (self::$debug) {error_log('$post_id =' . $post_id);}

        $addTagList = [];
        $removeTagList = [];

        // get all tags from the post
        $tags = wp_get_post_tags($post_id);

        foreach ($tags as $value) {
            $tag = $value->name;
            $sanitizedTag = self::sanitizeTag($tag);
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

}

// register wp hooks
add_action('save_post', 'WPTagSanitizer::sanitizePost');
