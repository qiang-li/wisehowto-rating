<?php
/**
 * PHPUnit Bootstrap
 * 定义 WordPress 环境桩，加载 Composer autoload 和插件类文件
 */

// 1. 定义 WordPress 核心常量，防止类文件里的 exit 触发
define( 'ABSPATH',       __DIR__ . '/' );
define( 'WPINC',         'wp-includes' );
define( 'YEAR_IN_SECONDS', 31536000 );

// 2. Composer autoload（PHPUnit + Brain\Monkey + Mockery）
require_once dirname( __DIR__ ) . '/vendor/autoload.php';

// 3. 加载插件类文件
require_once dirname( __DIR__ ) . '/includes/class-rating-db.php';
require_once dirname( __DIR__ ) . '/includes/class-rating-ajax.php';
require_once dirname( __DIR__ ) . '/includes/class-rating-widget.php';
