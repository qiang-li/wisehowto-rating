<?php
/**
 * AJAX 请求处理层
 * 职责：验证 nonce、校验参数、调用 DB 层、返回 JSON
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WiseHowTo_Rating_Ajax {

    public static function init(): void {
        // 未登录用户也可以评分
        add_action( 'wp_ajax_whtr_submit',        [ __CLASS__, 'handle_submit' ] );
        add_action( 'wp_ajax_nopriv_whtr_submit', [ __CLASS__, 'handle_submit' ] );
    }

    /**
     * 处理评分提交
     */
    public static function handle_submit(): void {

        // 1. 验证 nonce（防 CSRF）
        if ( ! check_ajax_referer( 'whtr_nonce', 'nonce', false ) ) {
            wp_send_json_error( [ 'message' => '请求无效，请刷新页面后重试。' ], 403 );
        }

        // 2. 校验 tool_id
        $tool_id = isset( $_POST['tool_id'] ) ? (int) $_POST['tool_id'] : 0;
        if ( $tool_id <= 0 ) {
            wp_send_json_error( [ 'message' => '无效的工具 ID。' ], 400 );
        }

        // 3. 校验 rating（必须是 1~5 的整数）
        $rating = isset( $_POST['rating'] ) ? (int) $_POST['rating'] : 0;
        if ( $rating < 1 || $rating > 5 ) {
            wp_send_json_error( [ 'message' => '评分必须在 1 到 5 之间。' ], 400 );
        }

        // 4. 获取用户标识
        $ip          = self::get_user_ip();
        $cookie_hash = self::get_cookie_hash();

        // 5. 检查是否已评过
        if ( WiseHowTo_Rating_DB::has_rated( $tool_id, $ip, $cookie_hash ) ) {
            wp_send_json_error( [ 'message' => '您已经评过分了，感谢参与！' ], 409 );
        }

        // 6. 写入评分
        $inserted = WiseHowTo_Rating_DB::insert_rating( $tool_id, $rating, $ip, $cookie_hash );
        if ( ! $inserted ) {
            wp_send_json_error( [ 'message' => '评分提交失败，请稍后再试。' ], 500 );
        }

        // 7. 返回最新统计数据
        $stats = WiseHowTo_Rating_DB::get_stats( $tool_id );
        wp_send_json_success( [
            'avg_rating' => $stats['avg'],
            'total'      => $stats['total'],
        ] );
    }

    // -------------------------------------------------------------------------
    // 私有辅助方法
    // -------------------------------------------------------------------------

    private static function get_user_ip(): string {
        $keys = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        ];
        foreach ( $keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip = trim( explode( ',', $_SERVER[ $key ] )[0] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }
        return '0.0.0.0';
    }

    private static function get_cookie_hash(): string {
        $key = 'whtr_uid';
        if ( empty( $_COOKIE[ $key ] ) ) {
            $uid = wp_generate_uuid4();
            setcookie( $key, $uid, time() + YEAR_IN_SECONDS, '/', '', is_ssl(), true );
            return hash( 'sha256', $uid );
        }
        return hash( 'sha256', sanitize_text_field( $_COOKIE[ $key ] ) );
    }
}
