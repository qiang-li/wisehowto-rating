<?php
/**
 * 数据库操作层
 * 职责：建表、写入评分、查询平均分、检查重复评分
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WiseHowTo_Rating_DB {

    const TABLE = 'wisehowto_ratings';

    /**
     * 插件激活时创建自定义表
     */
    public static function create_table(): void {
        global $wpdb;

        $table      = $wpdb->prefix . self::TABLE;
        $charset    = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS {$table} (
            id          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            tool_id     BIGINT UNSIGNED NOT NULL,
            rating      TINYINT UNSIGNED NOT NULL,
            user_ip     VARCHAR(45)  DEFAULT NULL,
            cookie_hash VARCHAR(64)  DEFAULT NULL,
            created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_tool_id (tool_id)
        ) {$charset};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );
    }

    /**
     * 写入一条评分记录
     *
     * @param int    $tool_id
     * @param int    $rating       1~5
     * @param string $ip
     * @param string $cookie_hash
     * @return int|false  插入的行 ID，失败返回 false
     */
    public static function insert_rating( int $tool_id, int $rating, string $ip, string $cookie_hash ) {
        global $wpdb;

        $result = $wpdb->insert(
            $wpdb->prefix . self::TABLE,
            [
                'tool_id'     => $tool_id,
                'rating'      => $rating,
                'user_ip'     => $ip,
                'cookie_hash' => $cookie_hash,
            ],
            [ '%d', '%d', '%s', '%s' ]
        );

        return $result ? $wpdb->insert_id : false;
    }

    /**
     * 查询某工具的平均分与评分总数
     *
     * @param int $tool_id
     * @return array{avg: float, total: int}
     */
    public static function get_stats( int $tool_id ): array {
        global $wpdb;

        $table = $wpdb->prefix . self::TABLE;
        $row   = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT AVG(rating) AS avg, COUNT(*) AS total FROM {$table} WHERE tool_id = %d",
                $tool_id
            )
        );

        return [
            'avg'   => $row ? round( (float) $row->avg, 1 ) : 0.0,
            'total' => $row ? (int) $row->total : 0,
        ];
    }

    /**
     * 检查该用户（IP 或 cookie）是否已对此工具评过分
     *
     * @param int    $tool_id
     * @param string $ip
     * @param string $cookie_hash
     * @return bool
     */
    public static function has_rated( int $tool_id, string $ip, string $cookie_hash ): bool {
        global $wpdb;

        $table = $wpdb->prefix . self::TABLE;
        $count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table}
                 WHERE tool_id = %d
                   AND ( user_ip = %s OR cookie_hash = %s )",
                $tool_id,
                $ip,
                $cookie_hash
            )
        );

        return (int) $count > 0;
    }
}
