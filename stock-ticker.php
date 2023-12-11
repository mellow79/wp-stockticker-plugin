<?php
/**
 * Plugin Name: Stock Ticker
 * Description: Display a stock ticker using a shortcode.
 * Version: 1.0
 * Author: Your Name
 */

// stock-ticker.php

// Function to fetch stock data from Alpha Vantage API
function get_stock_data($symbol) {
    $api_key = 'YOUR_API_KEY'; // Replace with your Alpha Vantage API key
    $endpoint = "https://www.alphavantage.co/query";
    $function = "GLOBAL_QUOTE";

    // Build API URL
    $url = "$endpoint?function=$function&symbol=$symbol&apikey=$api_key";

    // Make the API request
    $response = wp_remote_get($url);

    // Check if the request was successful
    if (is_wp_error($response)) {
        return false;
    }

    // Parse the JSON response
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    // Check if the response contains valid data
    if (isset($data['Global Quote']['05. price'])) {
        return $data['Global Quote']['05. price'];
    }

    return false;
}

// Modified shortcode function
function stock_ticker_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'symbols' => 'AAPL,GOOGL,MSFT',
            'speed'   => 'medium',
        ),
        $atts,
        'stock-ticker'
    );

    $symbols = explode(',', $atts['symbols']);
    $speed = in_array($atts['speed'], array('slow', 'medium', 'fast')) ? $atts['speed'] : 'medium';

    ob_start();
    ?>
    <div class="stock-ticker" data-symbols="<?php echo esc_attr($atts['symbols']); ?>" data-speed="<?php echo esc_attr($speed); ?>">
        <?php foreach ($symbols as $symbol) : ?>
            <span data-symbol="<?php echo esc_attr($symbol); ?>"></span>
        <?php endforeach; ?>
    </div>
    <?php
    return ob_get_clean();
}

// Enqueue scripts and styles
function enqueue_stock_ticker_assets() {
    wp_enqueue_style('stock-ticker-style', plugins_url('style.css', __FILE__));
    wp_enqueue_script('stock-ticker-script', plugins_url('script.js', __FILE__), array('jquery'), '1.0', true);
    wp_localize_script('stock-ticker-script', 'stock_ticker_data', array('api_url' => admin_url('admin-ajax.php')));
}
add_action('wp_enqueue_scripts', 'enqueue_stock_ticker_assets');

// AJAX handler for fetching stock data
function fetch_stock_data() {
    check_ajax_referer('stock_ticker_nonce', 'security');

    $symbol = isset($_POST['symbol']) ? sanitize_text_field($_POST['symbol']) : '';

    if (!empty($symbol)) {
        $price = get_stock_data($symbol);
        if ($price !== false) {
            wp_send_json_success(array('price' => $price));
        }
    }

    wp_send_json_error(array('message' => 'Failed to fetch stock data.'));
}
add_action('wp_ajax_fetch_stock_data', 'fetch_stock_data');
add_action('wp_ajax_nopriv_fetch_stock_data', 'fetch_stock_data');
