<?php
/**
 * Shortcode 渲染层
 * 用法：[wisehowto_rating tool_id="123"]
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class WiseHowTo_Rating_Widget {

    public static function init(): void {
        add_shortcode( 'wisehowto_rating', [ __CLASS__, 'render' ] );
    }

    /**
     * 渲染评分组件 HTML
     *
     * @param array $atts  Shortcode 属性
     * @return string
     */
    public static function render( array $atts ): string {
        $atts = shortcode_atts( [ 'tool_id' => 0 ], $atts, 'wisehowto_rating' );

        $tool_id = (int) $atts['tool_id'];
        if ( $tool_id <= 0 ) {
            return '<!-- WiseHowTo Rating: 缺少有效的 tool_id -->';
        }

        $stats   = WiseHowTo_Rating_DB::get_stats( $tool_id );
        $avg     = $stats['avg'];
        $total   = $stats['total'];

        ob_start();
        ?>
        <div class="whtr-widget" data-tool-id="<?php echo esc_attr( $tool_id ); ?>">

            <!-- 平均分展示 -->
            <div class="whtr-summary">
                <span class="whtr-avg"><?php echo esc_html( number_format( $avg, 1 ) ); ?></span>
                <span class="whtr-stars-display">
                    <?php echo self::render_static_stars( $avg ); ?>
                </span>
                <span class="whtr-total">
                    （<?php echo esc_html( $total ); ?> 人评分）
                </span>
            </div>

            <!-- 交互式评分区 -->
            <div class="whtr-input" role="group" aria-label="为此工具评分">
                <p class="whtr-label">为这个工具评分：</p>
                <div class="whtr-stars-interactive">
                    <?php for ( $i = 1; $i <= 5; $i++ ) : ?>
                        <button
                            type="button"
                            class="whtr-star"
                            data-value="<?php echo esc_attr( $i ); ?>"
                            aria-label="<?php echo esc_attr( $i ); ?> 星"
                        >★</button>
                    <?php endfor; ?>
                </div>
                <p class="whtr-feedback" aria-live="polite"></p>
            </div>

        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * 生成静态星级 HTML（用于展示平均分）
     */
    private static function render_static_stars( float $avg ): string {
        $html = '';
        for ( $i = 1; $i <= 5; $i++ ) {
            $class = $i <= round( $avg ) ? 'whtr-star-filled' : 'whtr-star-empty';
            $html .= '<span class="' . esc_attr( $class ) . '">★</span>';
        }
        return $html;
    }
}
