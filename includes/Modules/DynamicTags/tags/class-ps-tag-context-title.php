<?php
namespace PS_Core\Dynamic_Tags;

use Elementor\Core\DynamicTags\Tag;
use Elementor\Modules\DynamicTags\Module;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * PS Context Title
 *
 * Returns a context-aware title based on the current request.
 * Examples:
 * - Product category archive: term name (e.g., "Minerals")
 * - Product tag archive: term name
 * - Shop page: shop page title
 * - Singular pages/posts/products: post title
 */
class Tag_Context_Title extends Tag {

    public function get_name() {
        return 'ps-context-title';
    }

    public function get_title() {
        return __( 'PS Context Title', 'sherman-core' );
    }

    public function get_group() {
        return 'ps-core';
    }

    public function get_categories() {
        return [ Module::TEXT_CATEGORY ];
    }

    public function render() {
        $title = $this->resolve_title();
        if ( ! $title ) {
            return;
        }
        echo esc_html( $title );
    }

    private function resolve_title(): string {
        // 1) Taxonomy archives (including WooCommerce product_cat/product_tag).
        $qo = get_queried_object();
        if ( $qo && $qo instanceof \WP_Term ) {
            return (string) $qo->name;
        }

        // 2) WooCommerce shop.
        if ( function_exists( 'is_shop' ) && is_shop() ) {
            if ( function_exists( 'wc_get_page_id' ) ) {
                $shop_id = (int) wc_get_page_id( 'shop' );
                if ( $shop_id > 0 ) {
                    $t = get_the_title( $shop_id );
                    if ( $t ) {
                        return (string) $t;
                    }
                }
            }
            return __( 'Shop', 'sherman-core' );
        }

        // 3) Search.
        if ( is_search() ) {
            $q = get_search_query();
            if ( $q ) {
                return sprintf( __( 'Search: %s', 'sherman-core' ), $q );
            }
            return __( 'Search', 'sherman-core' );
        }

        // 4) Blog home.
        if ( is_home() ) {
            $page_for_posts = (int) get_option( 'page_for_posts' );
            if ( $page_for_posts > 0 ) {
                $t = get_the_title( $page_for_posts );
                if ( $t ) {
                    return (string) $t;
                }
            }
            return __( 'Blog', 'sherman-core' );
        }

        // 5) Singular.
        if ( is_singular() ) {
            $id = get_queried_object_id();
            if ( $id ) {
                $t = get_the_title( $id );
                if ( $t ) {
                    return (string) $t;
                }
            }
        }

        // 6) Fallback to WP document title.
        $doc = wp_get_document_title();
        return $doc ? (string) $doc : '';
    }
}
