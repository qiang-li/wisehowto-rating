<?php
/**
 * Plugin Name: WiseHowTo Rating Widget
 * Plugin URI:  https://wisehowto.com
 * Description: 为 AI 工具详情页提供用户评分功能。
 * Version:     0.1.0
 * Author:      WiseHowTo
 * License:     MIT
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'WHTR_VERSION',  '0.1.0' );
define( 'WHTR_DIR',      plugin_dir_path( __FILE__ ) );
define( 'WHTR_URL',      plugin_dir_url( __FILE__ ) );

require_once WHTR_DIR . 'includes/class-rating-db.php';
require_once WHTR_DIR . 'includes/class-rating-ajax.php';
require_once WHTR_DIR . 'includes/class-rating-widget.php';

// 激活时建表
register_activation_hook( __FILE__, [ 'WiseHowTo_Rating_DB', 'create_table' ] );

// 初始化各模块
add_action( 'init', [ 'WiseHowTo_Rating_Ajax',   'init' ] );
add_action( 'init', [ 'WiseHowTo_Rating_Widget',  'init' ] );

// 注册前端资源
add_action( 'wp_enqueue_scripts', function () {
    wp_enqueue_style(
        'whtr-style',
        WHTR_URL . 'assets/rating.css',
        [],
        WHTR_VERSION
    );
    wp_enqueue_script(
        'whtr-script',
        WHTR_URL . 'assets/rating.js',
        [ 'jquery' ],
        WHTR_VERSION,
        true
    );
    wp_localize_script( 'whtr-script', 'whtrData', [
        'ajaxUrl' => admin_url( 'admin-ajax.php' ),
        'nonce'   => wp_create_nonce( 'whtr_nonce' ),
    ] );
} );
