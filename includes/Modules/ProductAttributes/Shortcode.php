<?php
namespace ShermanCore\Modules\ProductAttributes;

final class Shortcode {

    public const STYLE_HANDLE = 'sherman-product-attributes';

    public static function register_assets(): void {
        wp_register_style(
            self::STYLE_HANDLE,
            SHERMAN_CORE_NEXT_URL . 'assets/frontend/product-attributes.css',
            [],
            SHERMAN_CORE_NEXT_VERSION
        );

        if ( function_exists( 'is_product' ) && is_product() ) {
            wp_enqueue_style( self::STYLE_HANDLE );
        }
    }

    public static function init(): void {
        add_shortcode( 'sherman_product_attributes', [ __CLASS__, 'render' ] );
    }

    public static function render( $atts = [] ): string {
        if ( ! ( class_exists( '\\WooCommerce' ) || function_exists( 'WC' ) ) || ! function_exists( 'is_product' ) || ! is_product() ) {
            return '<p>This shortcode works only on product pages.</p>';
        }

        $product = function_exists( 'wc_get_product' ) ? wc_get_product( get_the_ID() ) : null;
        if ( ! $product ) {
            return '<p>Product not found.</p>';
        }

        $atts = shortcode_atts( [
            'max' => 9,
        ], $atts, 'sherman_product_attributes' );

        $max = absint( $atts['max'] ?? 9 );
        if ( $max < 1 ) { $max = 1; }
        if ( $max > 50 ) { $max = 50; }

        $attributes = $product->get_attributes();
        $attribute_data = [];

        foreach ( $attributes as $attribute ) {
            if ( count( $attribute_data ) >= $max ) {
                break;
            }

            if ( method_exists( $attribute, 'is_taxonomy' ) && $attribute->is_taxonomy() ) {
                $terms = wp_get_post_terms( $product->get_id(), $attribute->get_name(), [ 'fields' => 'names' ] );
                $value = ! empty( $terms ) ? implode( ', ', $terms ) : '-';
            } else {
                $opts = method_exists( $attribute, 'get_options' ) ? $attribute->get_options() : [];
                $value = ! empty( $opts ) ? implode( ', ', $opts ) : '-';
            }

            $attribute_data[] = [
                'name'  => function_exists( 'wc_attribute_label' ) ? wc_attribute_label( $attribute->get_name() ) : $attribute->get_name(),
                'value' => $value,
            ];
        }

        if ( empty( $attribute_data ) ) {
            return '<p>This product has no attributes.</p>';
        }

        ob_start();
        ?>
        <div class="sherman-product-attributes-grid">
            <?php foreach ( $attribute_data as $data ) : ?>
                <div class="sherman-attribute-box">
                    <div class="sherman-attribute-name"><?php echo esc_html( $data['name'] ); ?></div>
                    <div class="sherman-attribute-value"><?php echo esc_html( $data['value'] ); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return (string) ob_get_clean();
    }
}
