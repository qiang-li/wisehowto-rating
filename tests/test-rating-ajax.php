<?php
/**
 * 测试：WiseHowTo_Rating_Ajax
 *
 * 运行：./vendor/bin/phpunit tests/test-rating-ajax.php
 */

use PHPUnit\Framework\TestCase;
use Brain\Monkey;
use Brain\Monkey\Functions;

class Test_Rating_Ajax extends TestCase {

    protected function setUp(): void {
        parent::setUp();
        Monkey\setUp();
        $_POST = [];
    }

    protected function tearDown(): void {
        Monkey\tearDown();
        Mockery::close();
        $_POST = [];
        parent::tearDown();
    }

    // =========================================================
    // nonce 校验
    // =========================================================

    /**
     * nonce 无效时应返回 403，且不继续执行后续逻辑
     */
    public function test_invalid_nonce_returns_403(): void {
        Functions\expect( 'check_ajax_referer' )
            ->once()
            ->with( 'whtr_nonce', 'nonce', false )
            ->andReturn( false );

        Functions\expect( 'wp_send_json_error' )
            ->once()
            ->with( Mockery::type( 'array' ), 403 );

        WiseHowTo_Rating_Ajax::handle_submit();
    }

    // =========================================================
    // 参数校验
    // =========================================================

    /**
     * tool_id 为 0（未传）时应返回 400
     */
    public function test_invalid_tool_id_returns_400(): void {
        Functions\expect( 'check_ajax_referer' )->once()->andReturn( true );

        $_POST['tool_id'] = '0';
        $_POST['rating']  = '3';

        Functions\expect( 'wp_send_json_error' )
            ->once()
            ->with( Mockery::type( 'array' ), 400 );

        WiseHowTo_Rating_Ajax::handle_submit();
    }

    /**
     * rating = 0（未传）时应返回 400
     */
    public function test_missing_rating_returns_400(): void {
        Functions\expect( 'check_ajax_referer' )->once()->andReturn( true );

        $_POST['tool_id'] = '10';
        // 不传 rating

        Functions\expect( 'wp_send_json_error' )
            ->once()
            ->with( Mockery::type( 'array' ), 400 );

        WiseHowTo_Rating_Ajax::handle_submit();
    }

    /**
     * rating = 6（超出范围）时应返回 400
     */
    public function test_rating_out_of_range_returns_400(): void {
        Functions\expect( 'check_ajax_referer' )->once()->andReturn( true );

        $_POST['tool_id'] = '10';
        $_POST['rating']  = '6';

        Functions\expect( 'wp_send_json_error' )
            ->once()
            ->with( Mockery::type( 'array' ), 400 );

        WiseHowTo_Rating_Ajax::handle_submit();
    }

    /**
     * rating = -1（负数）时应返回 400
     */
    public function test_negative_rating_returns_400(): void {
        Functions\expect( 'check_ajax_referer' )->once()->andReturn( true );

        $_POST['tool_id'] = '10';
        $_POST['rating']  = '-1';

        Functions\expect( 'wp_send_json_error' )
            ->once()
            ->with( Mockery::type( 'array' ), 400 );

        WiseHowTo_Rating_Ajax::handle_submit();
    }

    // =========================================================
    // 重复评分
    // =========================================================

    /**
     * 已评分用户再次提交时应返回 409
     */
    public function test_duplicate_rating_returns_409(): void {
        Functions\expect( 'check_ajax_referer' )->once()->andReturn( true );
        Functions\expect( 'sanitize_text_field' )->andReturnFirstArg();
        Functions\expect( 'is_ssl' )->andReturn( false );

        $_POST['tool_id'] = '10';
        $_POST['rating']  = '4';
        $_COOKIE['whtr_uid'] = 'existing-uid';
        $_SERVER['REMOTE_ADDR'] = '1.2.3.4';

        // mock DB 层：has_rated 返回 true
        $mockDB = Mockery::mock( 'alias:WiseHowTo_Rating_DB' );
        $mockDB->shouldReceive( 'has_rated' )
            ->once()
            ->andReturn( true );

        Functions\expect( 'wp_send_json_error' )
            ->once()
            ->with( Mockery::type( 'array' ), 409 );

        WiseHowTo_Rating_Ajax::handle_submit();
    }

    // =========================================================
    // 正常提交
    // =========================================================

    /**
     * 合法提交应写入数据库并返回最新统计数据
     */
    public function test_successful_submit_returns_stats(): void {
        Functions\expect( 'check_ajax_referer' )->once()->andReturn( true );
        Functions\expect( 'sanitize_text_field' )->andReturnFirstArg();
        Functions\expect( 'is_ssl' )->andReturn( false );

        $_POST['tool_id'] = '10';
        $_POST['rating']  = '5';
        $_COOKIE['whtr_uid'] = 'new-user-uid';
        $_SERVER['REMOTE_ADDR'] = '5.6.7.8';

        $mockDB = Mockery::mock( 'alias:WiseHowTo_Rating_DB' );
        $mockDB->shouldReceive( 'has_rated' )->once()->andReturn( false );
        $mockDB->shouldReceive( 'insert_rating' )->once()->andReturn( 42 );
        $mockDB->shouldReceive( 'get_stats' )->once()->andReturn( [
            'avg'   => 4.8,
            'total' => 15,
        ] );

        Functions\expect( 'wp_send_json_success' )
            ->once()
            ->with( Mockery::on( function ( $data ) {
                return $data['avg_rating'] === 4.8
                    && $data['total']      === 15;
            } ) );

        WiseHowTo_Rating_Ajax::handle_submit();
    }

    /**
     * 数据库写入失败时应返回 500
     */
    public function test_db_failure_returns_500(): void {
        Functions\expect( 'check_ajax_referer' )->once()->andReturn( true );
        Functions\expect( 'sanitize_text_field' )->andReturnFirstArg();
        Functions\expect( 'is_ssl' )->andReturn( false );

        $_POST['tool_id'] = '10';
        $_POST['rating']  = '3';
        $_COOKIE['whtr_uid'] = 'some-uid';
        $_SERVER['REMOTE_ADDR'] = '5.6.7.8';

        $mockDB = Mockery::mock( 'alias:WiseHowTo_Rating_DB' );
        $mockDB->shouldReceive( 'has_rated' )->once()->andReturn( false );
        $mockDB->shouldReceive( 'insert_rating' )->once()->andReturn( false ); // 写入失败

        Functions\expect( 'wp_send_json_error' )
            ->once()
            ->with( Mockery::type( 'array' ), 500 );

        WiseHowTo_Rating_Ajax::handle_submit();
    }
}
