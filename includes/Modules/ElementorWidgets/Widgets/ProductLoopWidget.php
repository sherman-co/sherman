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
use Elementor\Group_Control_Typography;

use ShermanCore\Modules\ElementorWidgets\ProductLoopService;

final class ProductLoopWidget extends Widget_Base {

    public function get_style_depends() {
        return [ 'sherman-core-product-loop' ];
    }

    public function get_script_depends() {
        return [ 'sherman-core-product-loop' ];
    }

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
                'label'   => __( 'Products per page', 'sherman-core' ),
                'type'    => Controls_Manager::NUMBER,
                'default' => 4,
                'min'     => 1,
                'max'     => 50,
            ]
        );

        $this->add_control(
            'pagination_mode',
            [
                'label'   => __( 'Pagination Mode', 'sherman-core' ),
                'type'    => Controls_Manager::SELECT,
                'default' => 'none',
                'options' => [
                    'none'     => __( 'None', 'sherman-core' ),
                    'numbers'  => __( 'Numbers', 'sherman-core' ),
                    'load_more'=> __( 'Load More Button', 'sherman-core' ),
                    'infinite' => __( 'Infinite Scroll', 'sherman-core' ),
                ],
            ]
        );

        $this->add_control(
            'max_pages',
            [
                'label'       => __( 'Max Pages', 'sherman-core' ),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 0,
                'min'         => 0,
                'max'         => 200,
                'description' => __( '0 means no limit. Useful for Load More / Infinite to prevent endless scrolling.', 'sherman-core' ),
                'condition'   => [
                    'pagination_mode!' => 'none',
                ],
            ]
        );

        $this->add_control(
            'seo_fallback_pagination',
            [
                'label'        => __( 'SEO Fallback Pagination', 'sherman-core' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => __( 'When enabled, hidden pagination links are rendered even for Load More / Infinite.', 'sherman-core' ),
                'condition'    => [
                    'pagination_mode' => [ 'load_more', 'infinite' ],
                ],
            ]
        );

        $this->add_control(
            'url_sync',
            [
                'label'        => __( 'URL Sync (History API)', 'sherman-core' ),
                'type'         => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default'      => 'yes',
                'description'  => __( 'Updates the URL as pages are loaded (Load More / Infinite).', 'sherman-core' ),
                'condition'    => [
                    'pagination_mode' => [ 'load_more', 'infinite' ],
                ],
            ]
        );

        $this->add_control(
            'load_more_text',
            [
                'label'     => __( 'Load More Button Text', 'sherman-core' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => __( 'Load more', 'sherman-core' ),
                'condition' => [
                    'pagination_mode' => 'load_more',
                ],
            ]
        );

        $this->add_control(
            'loading_text',
            [
                'label'     => __( 'Loading Text', 'sherman-core' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => __( 'Loading…', 'sherman-core' ),
                'condition' => [
                    'pagination_mode' => [ 'load_more', 'infinite' ],
                ],
            ]
        );

        $this->add_control(
            'no_more_text',
            [
                'label'     => __( 'No More Products Text', 'sherman-core' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => __( 'No more products.', 'sherman-core' ),
                'condition' => [
                    'pagination_mode' => [ 'load_more', 'infinite' ],
                ],
            ]
        );

        $this->add_control(
            'scroll_threshold',
            [
                'label'       => __( 'Infinite Scroll Threshold (px)', 'sherman-core' ),
                'type'        => Controls_Manager::NUMBER,
                'default'     => 200,
                'min'         => 0,
                'max'         => 2000,
                'description' => __( 'How early (in px) to start loading before reaching the end.', 'sherman-core' ),
                'condition'   => [
                    'pagination_mode' => 'infinite',
                ],
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

        // Pagination styles
        $this->start_controls_section(
            'section_style_pagination',
            [
                'label' => __( 'Pagination', 'sherman-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
                'condition' => [
                    'pagination_mode!' => 'none',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'pagination_typography',
                'selector' => '{{WRAPPER}} .sherman-product-loop-pagination, {{WRAPPER}} .sherman-product-loop-load-more, {{WRAPPER}} .sherman-product-loop-status',
            ]
        );

        $this->add_control(
            'pagination_spacing',
            [
                'label' => __( 'Top Spacing', 'sherman-core' ),
                'type'  => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [ 'min' => 0, 'max' => 80 ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .sherman-product-loop-pagination, {{WRAPPER}} .sherman-product-loop-controls' => 'margin-top: {{SIZE}}px;',
                ],
            ]
        );

        $this->end_controls_section();

		$this->start_controls_section(
			'section_style_pagination',
			[
				'label' => __( 'Pagination & Load More', 'sherman-core' ),
				'tab'   => Controls_Manager::TAB_STYLE,
				'condition' => [
					'pagination_mode!' => 'none',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'pagination_typography',
				'label'    => __( 'Pagination Typography', 'sherman-core' ),
				'selector' => '{{WRAPPER}} .sherman-product-loop-pagination .page-numbers',
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'loadmore_typography',
				'label'    => __( 'Load More Typography', 'sherman-core' ),
				'selector' => '{{WRAPPER}} .sherman-product-loop-load-more',
			]
		);

		$this->add_responsive_control(
			'loadmore_padding',
			[
				'label'      => __( 'Load More Padding', 'sherman-core' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .sherman-product-loop-load-more' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Background::get_type(),
			[
				'name'     => 'loadmore_background',
				'selector' => '{{WRAPPER}} .sherman-product-loop-load-more',
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'loadmore_border',
				'selector' => '{{WRAPPER}} .sherman-product-loop-load-more',
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'loadmore_shadow',
				'selector' => '{{WRAPPER}} .sherman-product-loop-load-more',
			]
		);

		$this->end_controls_section();
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

        $pagination_mode = isset( $settings['pagination_mode'] ) ? (string) $settings['pagination_mode'] : 'none';

        $sanitized = ProductLoopService::sanitize_settings( $settings );

        $paged = 1;
        if ( 'numbers' === $pagination_mode ) {
            $paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
        }

        $result = ProductLoopService::query_products( $sanitized, $paged );

        if ( empty( $result['ids'] ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="sherman-product-loop-wrapper"><div class="sherman-core-widget-notice">';
                esc_html_e( 'No products found for the current Sherman Product Loop query.', 'sherman-core' );
                echo '</div></div>';
            }
            return;
        }

        $seo_fallback = ( isset( $settings['seo_fallback_pagination'] ) && 'yes' === $settings['seo_fallback_pagination'] );
        $url_sync     = ( isset( $settings['url_sync'] ) && 'yes' === $settings['url_sync'] );

        // Enable pretty URL sync only on product archives (safer than forcing it everywhere).
        $is_archive_ctx = ( function_exists( 'is_shop' ) && is_shop() ) || is_post_type_archive( 'product' ) || is_tax( 'product_cat' ) || is_tax( 'product_tag' );
        $pretty         = (bool) get_option( 'permalink_structure' );

        $config = [
            'template_id'      => $template_id,
            'settings'         => $sanitized,
            'mode'             => $pagination_mode,
            'max_pages'        => (int) $result['max_pages'],
            'paged'            => (int) $result['current_page'],
            'has_more'         => (bool) $result['has_more'],
            'seo_fallback'     => $seo_fallback,
            'url_sync'         => $url_sync,
            'url_sync_pretty'  => ( $url_sync && $pretty && $is_archive_ctx ),
            'base_url'         => get_pagenum_link( 1 ),
            'loading_text'     => isset( $settings['loading_text'] ) ? (string) $settings['loading_text'] : __( 'Loading…', 'sherman-core' ),
            'no_more_text'     => isset( $settings['no_more_text'] ) ? (string) $settings['no_more_text'] : __( 'No more products.', 'sherman-core' ),
            'load_more_text'   => isset( $settings['load_more_text'] ) ? (string) $settings['load_more_text'] : __( 'Load more', 'sherman-core' ),
            'scroll_threshold' => isset( $settings['scroll_threshold'] ) ? (int) $settings['scroll_threshold'] : 200,
        ];

        $wrapper_attrs = '';
        if ( in_array( $pagination_mode, [ 'load_more', 'infinite' ], true ) ) {
            $wrapper_attrs = ' data-sherman-loop="' . esc_attr( wp_json_encode( $config ) ) . '"';
        }

        echo '<div class="sherman-product-loop-wrapper"' . $wrapper_attrs . '>';
        echo '<div class="sherman-product-loop-grid">';
        echo ProductLoopService::render_items_html( $result['ids'], $template_id );
        echo '</div>';

        // Pagination UI
        if ( 'numbers' === $pagination_mode ) {
            echo $this->render_pagination_links( (int) $result['max_pages'], (int) $result['current_page'], false );
        } elseif ( 'load_more' === $pagination_mode ) {
            echo '<div class="sherman-product-loop-controls">';
            echo '<button type="button" class="sherman-product-loop-load-more">' . esc_html( $config['load_more_text'] ) . '</button>';
            echo '<div class="sherman-product-loop-status" aria-live="polite"></div>';
            echo '</div>';
            echo '<div class="sherman-product-loop-sentinel" aria-hidden="true"></div>';

            if ( $seo_fallback ) {
                echo $this->render_pagination_links( (int) $result['max_pages'], (int) $result['current_page'], true );
            }
        } elseif ( 'infinite' === $pagination_mode ) {
            echo '<div class="sherman-product-loop-controls">';
            echo '<div class="sherman-product-loop-status" aria-live="polite"></div>';
            echo '</div>';
            echo '<div class="sherman-product-loop-sentinel" aria-hidden="true"></div>';

            if ( $seo_fallback ) {
                echo $this->render_pagination_links( (int) $result['max_pages'], (int) $result['current_page'], true );
            }
        }

        echo '</div>';
    }

    private function render_pagination_links( int $total_pages, int $current, bool $seo_fallback ): string {
        $total_pages = max( 1, (int) $total_pages );
        $current     = max( 1, (int) $current );

        if ( $total_pages <= 1 ) {
            return '';
        }

        $links = paginate_links( [
            'base'      => str_replace( 999999999, '%#%', esc_url( get_pagenum_link( 999999999 ) ) ),
            'format'    => '',
            'current'   => $current,
            'total'     => $total_pages,
            'type'      => 'list',
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
        ] );

        if ( empty( $links ) ) {
            return '';
        }

        $cls = $seo_fallback ? 'sherman-product-loop-pagination sherman-product-loop-pagination--seo-fallback' : 'sherman-product-loop-pagination';
        return '<nav class="' . esc_attr( $cls ) . '" aria-label="Pagination">' . $links . '</nav>';
    }

}
