<?php
namespace ShermanCore\Modules\ElementorWidgets;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Shared service for the Sherman Product Loop widget.
 * Used by both the widget renderer and the AJAX endpoint.
 */
final class ProductLoopService {

    public static function sanitize_settings( array $raw ): array {
        $out = [];

        $out['query_source'] = isset( $raw['query_source'] ) ? sanitize_key( (string) $raw['query_source'] ) : 'related_current_product';
        $out['orderby']      = isset( $raw['orderby'] ) ? sanitize_key( (string) $raw['orderby'] ) : 'date';
        $out['order']        = ( isset( $raw['order'] ) && 'ASC' === strtoupper( (string) $raw['order'] ) ) ? 'ASC' : 'DESC';

        $out['posts_per_page'] = isset( $raw['posts_per_page'] ) ? max( 1, min( 100, (int) $raw['posts_per_page'] ) ) : 4;
        $out['max_pages']      = isset( $raw['max_pages'] ) ? max( 0, min( 200, (int) $raw['max_pages'] ) ) : 0;

        $out['product_cats'] = [];
        if ( ! empty( $raw['product_cats'] ) && is_array( $raw['product_cats'] ) ) {
            $out['product_cats'] = array_values( array_filter( array_map( 'intval', $raw['product_cats'] ) ) );
        }

        $out['product_tags'] = [];
        if ( ! empty( $raw['product_tags'] ) && is_array( $raw['product_tags'] ) ) {
            $out['product_tags'] = array_values( array_filter( array_map( 'intval', $raw['product_tags'] ) ) );
        }

        $out['manual_ids'] = isset( $raw['manual_ids'] ) ? sanitize_text_field( (string) $raw['manual_ids'] ) : '';

        return $out;
    }

    private static function sort_product_ids_basic( array $ids, string $orderby, string $order ): array {
        $ids = array_values( array_filter( array_map( 'intval', $ids ) ) );
        if ( empty( $ids ) ) {
            return [];
        }

        if ( 'rand' === $orderby ) {
            shuffle( $ids );
            return $ids;
        }

        $posts = get_posts( [
            'post_type'      => 'product',
            'post__in'       => $ids,
            'posts_per_page' => -1,
            'orderby'        => 'post__in',
        ] );

        if ( empty( $posts ) ) {
            return $ids;
        }

        $map = [];
        foreach ( $posts as $p ) {
            $map[ $p->ID ] = [
                'title' => (string) $p->post_title,
                'date'  => (string) $p->post_date,
            ];
        }

        usort( $ids, static function( $a, $b ) use ( $map, $orderby, $order ) {
            $va = $map[ $a ][ $orderby ] ?? '';
            $vb = $map[ $b ][ $orderby ] ?? '';

            if ( $va === $vb ) {
                return 0;
            }
            if ( 'ASC' === $order ) {
                return ( $va < $vb ) ? -1 : 1;
            }
            return ( $va > $vb ) ? -1 : 1;
        } );

        return $ids;
    }

    /**
     * Returns: [ 'ids' => int[], 'current_page' => int, 'max_pages' => int, 'has_more' => bool ]
     */
    public static function query_ids_for_page( array $raw_settings, int $paged ): array {
        $settings = self::sanitize_settings( $raw_settings );

        $source   = $settings['query_source'];
        $per_page = (int) $settings['posts_per_page'];
        $paged    = max( 1, (int) $paged );

        $order   = $settings['order'];
        $orderby = $settings['orderby'];

        $orderby_map = 'date';
        if ( 'title' === $orderby ) {
            $orderby_map = 'title';
        } elseif ( 'rand' === $orderby ) {
            $orderby_map = 'rand';
        }

        $max_pages_setting = (int) $settings['max_pages'];

        // Default return
        $result = [
            'ids'          => [],
            'current_page' => $paged,
            'max_pages'    => 1,
            'has_more'     => false,
        ];

        // Helper: apply max_pages_setting
        $apply_max_pages_setting = static function( int $max_pages ) use ( $max_pages_setting ): int {
            if ( $max_pages_setting > 0 ) {
                return min( $max_pages, $max_pages_setting );
            }
            return $max_pages;
        };

        if ( 'related_current_product' === $source ) {
            if ( ! function_exists( 'wc_get_related_products' ) ) {
                return $result;
            }

            $current_product_id = 0;
            if ( function_exists( 'ps_core_get_current_product_id' ) ) {
                $current_product_id = (int) ps_core_get_current_product_id();
            }
            if ( ! $current_product_id ) {
                return $result;
            }

            $fetch_pages  = $max_pages_setting > 0 ? $max_pages_setting : 10;
            $fetch_pages  = max( 1, min( 20, (int) $fetch_pages ) );
            $fetch_limit  = max( $per_page, $per_page * $fetch_pages );

            $related      = wc_get_related_products( $current_product_id, $fetch_limit );
            $all_ids      = self::sort_product_ids_basic( $related, $orderby, $order );
            $total        = count( $all_ids );
            $max_pages    = ( $per_page > 0 ) ? (int) ceil( $total / $per_page ) : 1;
            $max_pages    = max( 1, $apply_max_pages_setting( $max_pages ) );
            $offset       = ( $paged - 1 ) * $per_page;
            $page_ids     = array_slice( $all_ids, $offset, $per_page );

            $result['ids']       = $page_ids;
            $result['max_pages'] = $max_pages;
            $result['has_more']  = $paged < $max_pages;
            return $result;
        }

        if ( 'manual_ids' === $source ) {
            $ids = [];
            if ( ! empty( $settings['manual_ids'] ) ) {
                foreach ( explode( ',', (string) $settings['manual_ids'] ) as $id ) {
                    $id = (int) trim( (string) $id );
                    if ( $id > 0 ) {
                        $ids[] = $id;
                    }
                }
            }
            $all_ids   = self::sort_product_ids_basic( $ids, $orderby, $order );
            $total     = count( $all_ids );
            $max_pages = ( $per_page > 0 ) ? (int) ceil( $total / $per_page ) : 1;
            $max_pages = max( 1, $apply_max_pages_setting( $max_pages ) );
            $offset    = ( $paged - 1 ) * $per_page;

            $result['ids']       = array_slice( $all_ids, $offset, $per_page );
            $result['max_pages'] = $max_pages;
            $result['has_more']  = $paged < $max_pages;
            return $result;
        }

        // current_query: attempt to clone main query vars (best-effort)
        if ( 'current_query' === $source ) {
            global $wp_query;
            if ( $wp_query instanceof \WP_Query ) {
                $vars = $wp_query->query_vars;
                // Force to products
                $vars['post_type']      = 'product';
                $vars['post_status']    = 'publish';
                $vars['posts_per_page'] = $per_page;
                $vars['paged']          = $paged;
                unset( $vars['p'], $vars['page_id'], $vars['name'] );

                $q = new \WP_Query( $vars );
                $result['ids']       = ! empty( $q->posts ) ? wp_list_pluck( $q->posts, 'ID' ) : [];
                $result['max_pages'] = max( 1, $apply_max_pages_setting( (int) $q->max_num_pages ) );
                $result['has_more']  = $paged < $result['max_pages'];
                wp_reset_postdata();
                return $result;
            }
            return $result;
        }

        // Query-based sources
        $args = [
            'post_type'      => 'product',
            'post_status'    => 'publish',
            'posts_per_page' => $per_page,
            'orderby'        => $orderby_map,
            'order'          => $order,
            'paged'          => $paged,
            'no_found_rows'  => false,
        ];

        if ( 'product_cat' === $source ) {
            if ( empty( $settings['product_cats'] ) ) {
                return $result;
            }
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $settings['product_cats'],
                ],
            ];
        } elseif ( 'product_tag' === $source ) {
            if ( empty( $settings['product_tags'] ) ) {
                return $result;
            }
            $args['tax_query'] = [
                [
                    'taxonomy' => 'product_tag',
                    'field'    => 'term_id',
                    'terms'    => $settings['product_tags'],
                ],
            ];
        } elseif ( 'on_sale' === $source ) {
            if ( function_exists( 'wc_get_product_ids_on_sale' ) ) {
                $sale_ids = wc_get_product_ids_on_sale();
                $args['post__in'] = ! empty( $sale_ids ) ? $sale_ids : [ 0 ];
            }
        } elseif ( 'featured' === $source ) {
            if ( function_exists( 'wc_get_featured_product_ids' ) ) {
                $featured_ids = wc_get_featured_product_ids();
                $args['post__in'] = ! empty( $featured_ids ) ? $featured_ids : [ 0 ];
            }
        }

        $q = new \WP_Query( $args );
        $ids = ! empty( $q->posts ) ? wp_list_pluck( $q->posts, 'ID' ) : [];

        $max_pages = max( 1, (int) $q->max_num_pages );
        $max_pages = $apply_max_pages_setting( $max_pages );

        $result['ids']       = $ids;
        $result['max_pages'] = $max_pages;
        $result['has_more']  = $paged < $max_pages;

        wp_reset_postdata();
        return $result;
    }

    public static function render_items_html( array $product_ids, int $template_id ): string {
        if ( ! class_exists( '\\Elementor\\Plugin' ) || ! function_exists( 'wc_get_product' ) ) {
            return '';
        }

        $template_id = (int) $template_id;
        if ( $template_id <= 0 ) {
            return '';
        }

        global $product, $post;
        $original_product = $product;
        $original_post    = $post;

        ob_start();
        foreach ( $product_ids as $pid ) {
            $pid = (int) $pid;
            if ( $pid <= 0 ) {
                continue;
            }
            $wc_product = wc_get_product( $pid );
            $post_obj   = get_post( $pid );
            if ( ! $wc_product || ! $post_obj ) {
                continue;
            }

            $product = $wc_product;
            $post    = $post_obj;
            setup_postdata( $post );

            echo '<div class="sherman-product-loop-item">';
            echo \Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $template_id );
            echo '</div>';
        }

        wp_reset_postdata();

        $product = $original_product;
        $post    = $original_post;

        return (string) ob_get_clean();
    }
}
