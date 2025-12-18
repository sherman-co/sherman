<?php
namespace ShermanCore\Modules\ElementorWidgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Background;

final class ProductLoopWidget extends Widget_Base {

    public function get_name() {
        return 'sherman_product_loop';
    }

    public function get_title() {
        return __( 'Sherman Product Loop', 'sherman-core' );
    }

    public function get_icon() {
        return 'eicon-post-list';
    }

    public function get_categories() {
        return [ 'sherman-core' ];
    }

    public function get_keywords() {
        return [ 'product', 'loop', 'query', 'woocommerce', 'sherman' ];
    }

    protected function get_elementor_templates_options() {
        $options = [ '' => __( '— Select template —', 'sherman-core' ) ];

        $templates = get_posts( [
            'post_type'      => 'elementor_library',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ] );

        if ( ! empty( $templates ) && ! is_wp_error( $templates ) ) {
            foreach ( $templates as $template ) {
                $options[ $template->ID ] = $template->post_title;
            }
        }

        return $options;
    }

    protected function get_product_cat_options() {
        $options = [];

        $terms = get_terms( [
            'taxonomy'   => 'product_cat',
            'hide_empty' => false,
        ] );

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $options[ $term->term_id ] = $term->name;
            }
        }

        return $options;
    }

    protected function get_product_tag_options() {
        $options = [];

        $terms = get_terms( [
            'taxonomy'   => 'product_tag',
            'hide_empty' => false,
        ] );

        if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
            foreach ( $terms as $term ) {
                $options[ $term->term_id ] = $term->name;
            }
        }

        return $options;
    }

    protected function register_controls() {

        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Content', 'sherman-core' ),
            ]
        );

        $this->add_control(
            'template_id',
            [
                'label'       => __( 'Item Template', 'sherman-core' ),
                'type'        => Controls_Manager::SELECT,
                'options'     => $this->get_elementor_templates_options(),
                'default'     => '',
                'description' => __( 'Select an Elementor template that will be rendered for each product in the loop.', 'sherman-core' ),
            ]
        );

        $this->add_control(
            'query_source',
            [
                'label'   => __( 'Query Source', 'sherman-core' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'related_current_product',
                'options' => [
                    'related_current_product' => __( 'Related to current product (WooCommerce)', 'sherman-core' ),
                    'current_query'           => __( 'Use current WP Query', 'sherman-core' ),
                    'product_cat'             => __( 'By Product Category', 'sherman-core' ),
                    'product_tag'             => __( 'By Product Tag', 'sherman-core' ),
                    'on_sale'                 => __( 'On Sale Products', 'sherman-core' ),
                    'featured'                => __( 'Featured Products', 'sherman-core' ),
                    'manual_ids'              => __( 'Manual product IDs', 'sherman-core' ),
                ],
            ]
        );

        $this->add_control(
            'product_cats',
            [
                'label'       => __( 'Product Categories', 'sherman-core' ),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'options'     => $this->get_product_cat_options(),
                'label_block' => true,
                'condition'   => [
                    'query_source' => 'product_cat',
                ],
                'description' => __( 'Select one or more product categories.', 'sherman-core' ),
            ]
        );

        $this->add_control(
            'product_tags',
            [
                'label'       => __( 'Product Tags', 'sherman-core' ),
                'type'        => Controls_Manager::SELECT2,
                'multiple'    => true,
                'options'     => $this->get_product_tag_options(),
                'label_block' => true,
                'condition'   => [
                    'query_source' => 'product_tag',
                ],
                'description' => __( 'Select one or more product tags.', 'sherman-core' ),
            ]
        );

        $this->add_control(
            'manual_ids',
            [
                'label'       => __( 'Manual Product IDs', 'sherman-core' ),
                'type'        => Controls_Manager::TEXT,
                'placeholder' => __( 'e.g. 12, 45, 98', 'sherman-core' ),
                'condition'   => [
                    'query_source' => 'manual_ids',
                ],
            ]
        );

        $this->add_control(
            'posts_per_page',
            [
                'label'   => __( 'Products to show', 'sherman-core' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 4,
                'min'     => 1,
                'max'     => 50,
            ]
        );

        $this->add_responsive_control(
            'columns',
            [
                'label'   => __( 'Columns', 'sherman-core' ),
                'type'    => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'   => [
                    'px' => [
                        'min'  => 1,
                        'max'  => 6,
                        'step' => 1,
                    ],
                ],
                'default' => [
                    'size' => 3,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .sherman-product-loop-grid' => 'display:grid;grid-template-columns:repeat({{SIZE}},minmax(0,1fr));',
                ],
            ]
        );

        $this->add_control(
            'orderby',
            [
                'label'   => __( 'Order By', 'sherman-core' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'date',
                'options' => [
                    'date'  => __( 'Date', 'sherman-core' ),
                    'title' => __( 'Product Name (Title)', 'sherman-core' ),
                    'rand'  => __( 'Random', 'sherman-core' ),
                ],
            ]
        );

        $this->add_control(
            'order',
            [
                'label'   => __( 'Order Direction', 'sherman-core' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'DESC',
                'options' => [
                    'DESC' => __( 'Descending', 'sherman-core' ),
                    'ASC'  => __( 'Ascending', 'sherman-core' ),
                ],
            ]
        );

        $this->add_control(
            'note',
            [
                'type'            => Controls_Manager::RAW_HTML,
                'raw'             => __( 'Use this widget on product-related layouts. Inside the chosen template, use dynamic tags (PS Core) like Product Name, SKU, MSDS, etc.', 'sherman-core' ),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_wrapper',
            [
                'label' => __( 'Wrapper', 'sherman-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'wrapper_padding',
            [
                'label'      => __( 'Padding', 'sherman-core' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', 'em', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .sherman-product-loop-wrapper' =>
                        'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name'     => 'wrapper_background',
                'selector' => '{{WRAPPER}} .sherman-product-loop-wrapper',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'wrapper_border',
                'selector' => '{{WRAPPER}} .sherman-product-loop-wrapper',
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'wrapper_shadow',
                'selector' => '{{WRAPPER}} .sherman-product-loop-wrapper',
            ]
        );

        $this->add_responsive_control(
            'column_gap',
            [
                'label' => __( 'Column Gap', 'sherman-core' ),
                'type'  => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'default' => [
                    'size' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .sherman-product-loop-grid' => 'column-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_responsive_control(
            'row_gap',
            [
                'label' => __( 'Row Gap', 'sherman-core' ),
                'type'  => Controls_Manager::SLIDER,
                'size_units' => [ 'px', 'em', '%' ],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                    'em' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 10,
                    ],
                ],
                'default' => [
                    'size' => 20,
                    'unit' => 'px',
                ],
                'selectors' => [
                    '{{WRAPPER}} .sherman-product-loop-grid' => 'row-gap: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function sort_product_ids_basic( array $ids, $orderby, $order ) {
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

        usort( $ids, function( $a, $b ) use ( $map, $orderby, $order ) {
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

    protected function get_product_ids_from_settings( $settings ) {
        $source      = $settings['query_source'] ?? 'related_current_product';
        $limit       = ! empty( $settings['posts_per_page'] ) ? (int) $settings['posts_per_page'] : 4;
        $order       = ( isset( $settings['order'] ) && 'ASC' === $settings['order'] ) ? 'ASC' : 'DESC';
        $orderby     = $settings['orderby'] ?? 'date';
        $product_ids = [];

        $orderby_map = 'date';
        if ( 'title' === $orderby ) {
            $orderby_map = 'title';
        } elseif ( 'rand' === $orderby ) {
            $orderby_map = 'rand';
        }

        $current_product_id = 0;
        if ( function_exists( 'ps_core_get_current_product_id' ) ) {
            $current_product_id = (int) ps_core_get_current_product_id();
        }

        if ( 'related_current_product' === $source ) {

            if ( ! $current_product_id || ! function_exists( 'wc_get_related_products' ) ) {
                return [];
            }

            $related = wc_get_related_products( $current_product_id, $limit * 3 );
            $product_ids = $this->sort_product_ids_basic( $related, $orderby, $order );

        } elseif ( 'current_query' === $source ) {

            global $wp_query;
            if ( $wp_query instanceof \WP_Query && ! empty( $wp_query->posts ) ) {
                $ids = wp_list_pluck( $wp_query->posts, 'ID' );
                $product_ids = $this->sort_product_ids_basic( $ids, $orderby, $order );
            }

        } elseif ( 'product_cat' === $source ) {

            $cat_ids = $settings['product_cats'] ?? [];

            if ( ! empty( $cat_ids ) && is_array( $cat_ids ) ) {
                $cat_ids = array_map( 'intval', $cat_ids );
                $cat_ids = array_filter( $cat_ids );

                if ( ! empty( $cat_ids ) ) {
                    $q = new \WP_Query( [
                        'post_type'      => 'product',
                        'posts_per_page' => $limit,
                        'orderby'        => $orderby_map,
                        'order'          => $order,
                        'post_status'    => 'publish',
                        'tax_query'      => [
                            [
                                'taxonomy' => 'product_cat',
                                'field'    => 'term_id',
                                'terms'    => $cat_ids,
                            ],
                        ],
                        'no_found_rows'  => true,
                    ] );

                    if ( $q->have_posts() ) {
                        $product_ids = wp_list_pluck( $q->posts, 'ID' );
                    }

                    wp_reset_postdata();
                }
            }

        } elseif ( 'product_tag' === $source ) {

            $tag_ids = $settings['product_tags'] ?? [];

            if ( ! empty( $tag_ids ) && is_array( $tag_ids ) ) {
                $tag_ids = array_map( 'intval', $tag_ids );
                $tag_ids = array_filter( $tag_ids );

                if ( ! empty( $tag_ids ) ) {
                    $q = new \WP_Query( [
                        'post_type'      => 'product',
                        'posts_per_page' => $limit,
                        'orderby'        => $orderby_map,
                        'order'          => $order,
                        'post_status'    => 'publish',
                        'tax_query'      => [
                            [
                                'taxonomy' => 'product_tag',
                                'field'    => 'term_id',
                                'terms'    => $tag_ids,
                            ],
                        ],
                        'no_found_rows'  => true,
                    ] );

                    if ( $q->have_posts() ) {
                        $product_ids = wp_list_pluck( $q->posts, 'ID' );
                    }

                    wp_reset_postdata();
                }
            }

        } elseif ( 'on_sale' === $source ) {

            if ( function_exists( 'wc_get_product_ids_on_sale' ) ) {
                $sale_ids = wc_get_product_ids_on_sale();

                if ( ! empty( $sale_ids ) ) {
                    $q = new \WP_Query( [
                        'post_type'      => 'product',
                        'posts_per_page' => $limit,
                        'post__in'       => $sale_ids,
                        'orderby'        => $orderby_map,
                        'order'          => $order,
                        'post_status'    => 'publish',
                        'no_found_rows'  => true,
                    ] );

                    if ( $q->have_posts() ) {
                        $product_ids = wp_list_pluck( $q->posts, 'ID' );
                    }

                    wp_reset_postdata();
                }
            }

        } elseif ( 'featured' === $source ) {

            if ( function_exists( 'wc_get_featured_product_ids' ) ) {
                $featured_ids = wc_get_featured_product_ids();

                if ( ! empty( $featured_ids ) ) {
                    $q = new \WP_Query( [
                        'post_type'      => 'product',
                        'posts_per_page' => $limit,
                        'post__in'       => $featured_ids,
                        'orderby'        => $orderby_map,
                        'order'          => $order,
                        'post_status'    => 'publish',
                        'no_found_rows'  => true,
                    ] );

                    if ( $q->have_posts() ) {
                        $product_ids = wp_list_pluck( $q->posts, 'ID' );
                    }

                    wp_reset_postdata();
                }
            }

        } elseif ( 'manual_ids' === $source ) {

            if ( ! empty( $settings['manual_ids'] ) ) {
                $ids = explode( ',', $settings['manual_ids'] );
                foreach ( $ids as $id ) {
                    $id = (int) trim( $id );
                    if ( $id > 0 ) {
                        $product_ids[] = $id;
                    }
                }

                $product_ids = $this->sort_product_ids_basic( $product_ids, $orderby, $order );
            }
        }

        $product_ids = array_values( array_unique( array_map( 'intval', $product_ids ) ) );

        if ( $limit > 0 && count( $product_ids ) > $limit ) {
            $product_ids = array_slice( $product_ids, 0, $limit );
        }

        return $product_ids;
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        // Elementor check
        if ( ! class_exists( '\Elementor\Plugin' ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="sherman-product-loop-wrapper"><div class="sherman-core-widget-notice">';
                esc_html_e( 'Elementor is required for the Sherman Product Loop widget.', 'sherman-core' );
                echo '</div></div>';
            }
            return;
        }

        // WooCommerce check
        if ( ! function_exists( 'wc_get_product' ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="sherman-product-loop-wrapper"><div class="sherman-core-widget-notice">';
                esc_html_e( 'WooCommerce is required for the Sherman Product Loop widget.', 'sherman-core' );
                echo '</div></div>';
            }
            return;
        }

        $template_id = ! empty( $settings['template_id'] ) ? (int) $settings['template_id'] : 0;

        // No template selected
        if ( ! $template_id ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="sherman-product-loop-wrapper"><div class="sherman-core-widget-notice">';
                esc_html_e( 'Please select an Elementor template in the Sherman Product Loop widget settings.', 'sherman-core' );
                echo '</div></div>';
            }
            return;
        }

        $product_ids = $this->get_product_ids_from_settings( $settings );

        // No products found
        if ( empty( $product_ids ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="sherman-product-loop-wrapper"><div class="sherman-core-widget-notice">';
                esc_html_e( 'No products found for the current Sherman Product Loop query.', 'sherman-core' );
                echo '</div></div>';
            }
            return;
        }

        global $product, $post;

        $original_product = $product;
        $original_post    = $post;

        echo '<div class="sherman-product-loop-wrapper">';
        echo '<div class="sherman-product-loop-grid">';

        foreach ( $product_ids as $pid ) {
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

        echo '</div>';
        echo '</div>';

        $product = $original_product;
        $post    = $original_post;
    }

}
