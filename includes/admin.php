<?php

class WPTagSanitizerAdmin extends WPTagSanitizer {

    static function plugin_menu() {
        add_management_page(self::$plugin_name, self::$plugin_name, 'activate_plugins', 'WPTagSanitizerAdmin', array('WPTagSanitizerAdmin', 'plugin_options'));
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
        echo '<h2>'.self::$plugin_name.'</h2>';
        echo '<p>New tags will be normalized according to these rules. Normalization is case insensitive, however the resulting tag is not.';
        echo '<form method="post" action="">';
        echo '<textarea name="WPTagSanitizer_JSONTABLE" style="width: 50%;height: 400px;">' . $textAreaValue . '</textarea>';
        echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>';
        echo '</form></div>';
    }

}

// register wp hooks
add_action('admin_menu', 'WPTagSanitizerAdmin::plugin_menu');
