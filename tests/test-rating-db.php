<?php
/**
 * 测试：WiseHowTo_Rating_DB
 *
 * 运行：./vendor/bin/phpunit tests/test-rating-db.php
 */

require_once dirname( __DIR__ ) . '/includes/class-rating-db.php';

use PHPUnit\Framework\TestCase;
use Brain\Monkey;

class Test_Rating_DB extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        Mockery::close();
        parent::tearDown();
    }

    // =========================================================
    // get_stats()
    // =========================================================

    /**
     * 工具没有任何评分时，应返回 avg=0.0, total=0
     */
    public function test_get_stats_returns_zero_when_no_ratings(): void {
        $mockWpdb = Mockery::mock( 'wpdb' );
        $mockWpdb->prefix = 'wp_';
        $mockWpdb->shouldReceive( 'prepare' )->once()->andReturn( 'SQL' );
        $mockWpdb->shouldReceive( 'get_row' )->once()->with( 'SQL' )->andReturn( null );

        global $wpdb;
        $wpdb = $mockWpdb;

        $stats = WiseHowTo_Rating_DB::get_stats( 1 );

        $this->assertSame( 0.0, $stats['avg'] );
        $this->assertSame( 0,   $stats['total'] );
    }

    /**
     * 有评分时应返回正确的平均值（1 位小数）与总数
     */
    public function test_get_stats_returns_correct_avg_and_total(): void {
        $fakeRow        = new stdClass();
        $fakeRow->avg   = '4.333333';
        $fakeRow->total = '3';

        $mockWpdb = Mockery::mock( 'wpdb' );
        $mockWpdb->prefix = 'wp_';
        $mockWpdb->shouldReceive( 'prepare' )->once()->andReturn( 'SQL' );
        $mockWpdb->shouldReceive( 'get_row' )->once()->andReturn( $fakeRow );

        global $wpdb;
        $wpdb = $mockWpdb;

        $stats = WiseHowTo_Rating_DB::get_stats( 42 );

        $this->assertSame( 4.3, $stats['avg'] );   // round(4.333, 1) = 4.3
        $this->assertSame( 3,   $stats['total'] );
    }

    // =========================================================
    // has_rated()
    // =========================================================

    /**
     * 相同 IP 已评过分时应返回 true
     */
    public function test_has_rated_returns_true_for_same_ip(): void {
        $mockWpdb = Mockery::mock( 'wpdb' );
        $mockWpdb->prefix = 'wp_';
        $mockWpdb->shouldReceive( 'prepare' )->once()->andReturn( 'SQL' );
        $mockWpdb->shouldReceive( 'get_var' )->once()->andReturn( '1' );

        global $wpdb;
        $wpdb = $mockWpdb;

        $this->assertTrue( WiseHowTo_Rating_DB::has_rated( 1, '127.0.0.1', 'anyhash' ) );
    }

    /**
     * 相同 cookie_hash 已评过分时应返回 true
     */
    public function test_has_rated_returns_true_for_same_cookie(): void {
        $mockWpdb = Mockery::mock( 'wpdb' );
        $mockWpdb->prefix = 'wp_';
        $mockWpdb->shouldReceive( 'prepare' )->once()->andReturn( 'SQL' );
        $mockWpdb->shouldReceive( 'get_var' )->once()->andReturn( '1' );

        global $wpdb;
        $wpdb = $mockWpdb;

        $this->assertTrue( WiseHowTo_Rating_DB::has_rated( 1, '9.9.9.9', 'known-cookie-hash' ) );
    }

    /**
     * 全新用户（IP 和 cookie 均未出现过）应返回 false
     */
    public function test_has_rated_returns_false_for_new_user(): void {
        $mockWpdb = Mockery::mock( 'wpdb' );
        $mockWpdb->prefix = 'wp_';
        $mockWpdb->shouldReceive( 'prepare' )->once()->andReturn( 'SQL' );
        $mockWpdb->shouldReceive( 'get_var' )->once()->andReturn( '0' );

        global $wpdb;
        $wpdb = $mockWpdb;

        $this->assertFalse( WiseHowTo_Rating_DB::has_rated( 1, '1.2.3.4', 'new-hash' ) );
    }

    // =========================================================
    // insert_rating()
    // =========================================================

    /**
     * 正常插入时应返回新行的 ID（大于 0）
     */
    public function test_insert_rating_returns_insert_id_on_success(): void {
        $mockWpdb            = Mockery::mock( 'wpdb' );
        $mockWpdb->prefix    = 'wp_';
        $mockWpdb->insert_id = 7;
        $mockWpdb->shouldReceive( 'insert' )
            ->once()
            ->with(
                'wp_wisehowto_ratings',
                Mockery::type( 'array' ),
                Mockery::type( 'array' )
            )
            ->andReturn( 1 );

        global $wpdb;
        $wpdb = $mockWpdb;

        $this->assertSame( 7, WiseHowTo_Rating_DB::insert_rating( 10, 5, '1.1.1.1', 'abc123' ) );
    }

    /**
     * $wpdb->insert 失败时应返回 false
     */
    public function test_insert_rating_returns_false_on_db_error(): void {
        $mockWpdb         = Mockery::mock( 'wpdb' );
        $mockWpdb->prefix = 'wp_';
        $mockWpdb->shouldReceive( 'insert' )->once()->andReturn( false );

        global $wpdb;
        $wpdb = $mockWpdb;

        $this->assertFalse( WiseHowTo_Rating_DB::insert_rating( 10, 5, '1.1.1.1', 'abc123' ) );
    }
}
