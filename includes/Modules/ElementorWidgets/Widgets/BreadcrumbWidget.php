<?php
namespace ShermanCore\Modules\ElementorWidgets\Widgets;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

final class BreadcrumbWidget extends Widget_Base {

    public function get_name() {
        return 'sherman_breadcrumb';
    }

    public function get_title() {
        return __( 'Sherman Breadcrumb', 'sherman-core' );
    }

    public function get_icon() {
        return 'eicon-breadcrumbs';
    }

    public function get_categories() {
        // همون دسته‌ای که در پلاگین ثبت کردیم
        return [ 'sherman-core' ];
    }

    public function get_keywords() {
        return [ 'breadcrumb', 'woocommerce', 'product', 'sherman' ];
    }

    protected function register_controls() {

        // تب Content (فعلاً فقط یک کنترل ساده)
        $this->start_controls_section(
            'section_content',
            [
                'label' => __( 'Content', 'sherman-core' ),
            ]
        );

        $this->add_control(
            'show_home',
            [
                'label'        => __( 'Show Home link', 'sherman-core' ),
                'type'         => Controls_Manager::SWITCHER,
                'label_on'     => __( 'Yes', 'sherman-core' ),
                'label_off'    => __( 'No', 'sherman-core' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->end_controls_section();

        // تب Style
        $this->start_controls_section(
            'section_style',
            [
                'label' => __( 'Breadcrumb', 'sherman-core' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'alignment',
            [
                'label'   => __( 'Alignment', 'sherman-core' ),
                'type'    => Controls_Manager::CHOOSE,
                'options' => [
                    'flex-start' => [
                        'title' => __( 'Left', 'sherman-core' ),
                        'icon'  => 'eicon-text-align-left',
                    ],
                    'center'     => [
                        'title' => __( 'Center', 'sherman-core' ),
                        'icon'  => 'eicon-text-align-center',
                    ],
                    'flex-end'   => [
                        'title' => __( 'Right', 'sherman-core' ),
                        'icon'  => 'eicon-text-align-right',
                    ],
                ],
                'default'   => 'flex-start',
                'selectors' => [
                    '{{WRAPPER}} .sherman-breadcrumb-wrapper' => 'display:flex; justify-content: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name'     => 'typography',
                'selector' => '{{WRAPPER}} .sherman-breadcrumb',
            ]
        );

        $this->add_control(
            'color',
            [
                'label'     => __( 'Text Color', 'sherman-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sherman-breadcrumb, {{WRAPPER}} .sherman-breadcrumb a' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'link_hover_color',
            [
                'label'     => __( 'Link Hover Color', 'sherman-core' ),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .sherman-breadcrumb a:hover' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( ! function_exists( 'woocommerce_breadcrumb' ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="sherman-breadcrumb-wrapper"><div class="sherman-core-widget-notice">';
                esc_html_e( 'WooCommerce is not active. Breadcrumb cannot be displayed.', 'sherman-core' );
                echo '</div></div>';
            }
            return;
        }
        // اگر ووکامرس فعال نیست، پیام بده
        if ( ! function_exists( 'woocommerce_breadcrumb' ) ) {
            echo '<div class="sherman-breadcrumb-wrapper">';
            echo '<div class="sherman-breadcrumb">';
            esc_html_e( 'WooCommerce is not active. Breadcrumb cannot be displayed.', 'sherman-core' );
            echo '</div></div>';
            return;
        }

        $show_home = ( isset( $settings['show_home'] ) && 'yes' === $settings['show_home'] );

        $args = [
            'delimiter'   => ' &nbsp;/&nbsp; ',
            'wrap_before' => '<nav class="sherman-breadcrumb" aria-label="Breadcrumb">',
            'wrap_after'  => '</nav>',
        ];

        if ( ! $show_home ) {
            // اگر نخواهیم "Home" را نشان دهیم، می‌توانیم فیلتر کنیم
            add_filter( 'woocommerce_breadcrumb_home_url', '__return_false' );
        }

        echo '<div class="sherman-breadcrumb-wrapper">';

        ob_start();
        woocommerce_breadcrumb( $args );
        $html = ob_get_clean();

        echo $html;

        echo '</div>';

        if ( ! $show_home ) {
            remove_filter( 'woocommerce_breadcrumb_home_url', '__return_false' );
        }
    }
}
