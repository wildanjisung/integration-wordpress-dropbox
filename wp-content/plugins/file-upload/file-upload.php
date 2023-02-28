<?php
/*
Plugin Name: File Upload
Plugin URI: https://example.com/plugins/file-upload/
Description: Allows users to upload files to a server.
Version: 1.0.1
Author: wildanjisung
Author URI: https://example.com/
License: GPL2
*/

// Add shortcode to display file upload form
function my_plugin_upload_file_to_dropbox() {
    $access_token = get_option( 'dropbox_access_token' );
    // Get the file path and name from the form submission
    $file_path = $_FILES['my_file']['tmp_name'];
    $file_name = $_FILES['my_file']['name'];

    // Prepare the cURL request
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://content.dropboxapi.com/2/files/upload');
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $access_token,
        'Dropbox-API-Arg: {"path": "/'.$file_name.'"}',
        'Content-Type: application/octet-stream'
    ));
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, file_get_contents($file_path));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Send the cURL request and get the response
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Check for errors
    if ($http_code == 200) {
        // File uploaded successfully
        echo 'File uploaded successfully!';
    } else {
        // Error uploading file
        echo 'Error uploading file: ' . $response;
    }

    // Close the cURL request
    curl_close($ch);
}

// Add a shortcode to display the file upload form
function my_plugin_file_upload_form_shortcode() {
    return '<form method="post" enctype="multipart/form-data">
                <input type="file" name="my_file">
                <input type="submit" value="Upload File">
            </form>';
}
add_shortcode('my_plugin_file_upload_form', 'my_plugin_file_upload_form_shortcode');

// Handle the file upload form submission
function my_plugin_handle_file_upload_form_submission() {
    if (isset($_FILES['my_file'])) {
        my_plugin_upload_file_to_dropbox();
    }
}
add_action('init', 'my_plugin_handle_file_upload_form_submission');

//-------------------------------------------------------------------

// Add a setup page to the WordPress admin area
function hello_world_admin_menu() {
    add_menu_page(
        'Dropbox File Upload Settings',
        'Dropbox File Upload',
        'manage_options',
        'dropbox-file-upload-settings',
        'dropbox_file_upload_admin_page'
    );
}
add_action( 'admin_menu', 'hello_world_admin_menu' );

function dropbox_file_upload_admin_page() {
    echo '<div class="wrap">';
    echo '<h2>Dropbox File Upload Settings</h2>';
    echo '<p>Enter your setting below:</p>';
    echo '<form method="post" action="options.php">';
    settings_fields( 'dropbox-file-upload-settings-group' );
    do_settings_sections( 'dropbox-file-upload-settings-group' );
    echo '<table class="form-table">';
    echo '<tr valign="top">';
    echo '<th scope="row">Dropbox Access Token</th>';
    echo '<td><input type="text" name="dropbox_access_token" value="' . esc_attr( get_option( 'dropbox_access_token' ) ) . '" /></td>';
    echo '</tr>';
    echo '</table>';
    echo '<p class="submit"><input type="submit" class="button-primary" value="Save Changes" /></p>';
    echo '</form>';
    echo '</div>';
}

// Register a settings field for the name input
function dropbox_file_upload_register_settings() {
    register_setting( 'dropbox-file-upload-settings-group', 'dropbox_access_token' );
}
add_action( 'admin_init', 'dropbox_file_upload_register_settings' );


