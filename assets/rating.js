/**
 * WiseHowTo Rating Widget — 前端交互
 * 依赖：jQuery（WordPress 内置）
 */

( function ( $ ) {
    'use strict';

    $( document ).ready( function () {

        // ---------------------------------------------------------------
        // 星级 hover 效果
        // ---------------------------------------------------------------
        $( document ).on( 'mouseenter', '.whtr-star', function () {
            var value = $( this ).data( 'value' );
            highlightStars( $( this ).closest( '.whtr-stars-interactive' ), value );
        } );

        $( document ).on( 'mouseleave', '.whtr-stars-interactive', function () {
            highlightStars( $( this ), 0 );
        } );

        // ---------------------------------------------------------------
        // 点击提交评分
        // ---------------------------------------------------------------
        $( document ).on( 'click', '.whtr-star', function () {
            var $widget  = $( this ).closest( '.whtr-widget' );
            var toolId   = $widget.data( 'tool-id' );
            var rating   = $( this ).data( 'value' );
            var $input   = $widget.find( '.whtr-input' );
            var $feedback = $widget.find( '.whtr-feedback' );

            $input.find( '.whtr-star' ).prop( 'disabled', true );
            $feedback.text( '提交中…' ).removeClass( 'whtr-error whtr-success' );

            $.post( whtrData.ajaxUrl, {
                action:  'whtr_submit',
                nonce:   whtrData.nonce,
                tool_id: toolId,
                rating:  rating,
            } )
            .done( function ( response ) {
                if ( response.success ) {
                    var data = response.data;
                    // 更新平均分显示
                    $widget.find( '.whtr-avg' ).text( data.avg_rating.toFixed(1) );
                    $widget.find( '.whtr-total' ).text( '（' + data.total + ' 人评分）' );
                    $feedback
                        .text( '感谢您的评分！' )
                        .addClass( 'whtr-success' );
                    $input.find( '.whtr-stars-interactive' ).hide();
                } else {
                    $feedback
                        .text( response.data.message || '提交失败，请稍后再试。' )
                        .addClass( 'whtr-error' );
                    $input.find( '.whtr-star' ).prop( 'disabled', false );
                }
            } )
            .fail( function () {
                $feedback
                    .text( '网络错误，请检查连接后重试。' )
                    .addClass( 'whtr-error' );
                $input.find( '.whtr-star' ).prop( 'disabled', false );
            } );
        } );

        // ---------------------------------------------------------------
        // 辅助：高亮星星
        // ---------------------------------------------------------------
        function highlightStars( $container, value ) {
            $container.find( '.whtr-star' ).each( function () {
                $( this ).toggleClass( 'whtr-star-hover', $( this ).data( 'value' ) <= value );
            } );
        }

    } );

} )( jQuery );
