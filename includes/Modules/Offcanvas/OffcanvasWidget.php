<?php
namespace ShermanCore\Modules\Offcanvas;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;

final class OffcanvasWidget extends Widget_Base {

    public function get_name() { return 'sherman_offcanvas'; }
    public function get_title() { return __( 'Sherman Offcanvas', 'sherman-core' ); }
    public function get_icon() { return 'eicon-menu-bar'; }
    public function get_categories() { return [ 'sherman-core' ]; }

    public function get_script_depends() { return [ 'sherman-core-offcanvas' ]; }
    public function get_style_depends() { return [ 'sherman-core-offcanvas' ]; }

    protected function register_controls() {

        $this->start_controls_section( 'section_content', [ 'label' => __( 'Content', 'sherman-core' ) ] );

        $this->add_control( 'trigger_text', [
            'label' => __( 'Trigger text', 'sherman-core' ),
            'type' => Controls_Manager::TEXT,
            'default' => __( 'Open', 'sherman-core' ),
        ] );

        $this->add_control( 'trigger_icon', [
            'label' => __( 'Trigger icon', 'sherman-core' ),
            'type' => Controls_Manager::ICONS,
            'default' => [],
        ] );

        $this->add_control( 'template_id', [
            'label' => __( 'Elementor template', 'sherman-core' ),
            'type' => Controls_Manager::SELECT2,
            'options' => $this->get_elementor_templates_options(),
            'default' => '',
        ] );

        $this->end_controls_section();

        $this->start_controls_section( 'section_behavior', [ 'label' => __( 'Behavior', 'sherman-core' ) ] );

        $this->add_control( 'side', [
            'label' => __( 'Side', 'sherman-core' ),
            'type' => Controls_Manager::SELECT,
            'options' => [
                'right' => __( 'Right', 'sherman-core' ),
                'left'  => __( 'Left', 'sherman-core' ),
            ],
            'default' => 'right',
        ] );

        $this->add_control( 'prevent_scroll', [
            'label' => __( 'Prevent page scroll', 'sherman-core' ),
            'type' => Controls_Manager::SWITCHER,
            'label_on' => __( 'Yes', 'sherman-core' ),
            'label_off' => __( 'No', 'sherman-core' ),
            'return_value' => 'true',
            'default' => 'true',
        ] );

        $this->end_controls_section();

        $this->start_controls_section( 'section_style', [ 'label' => __( 'Style', 'sherman-core' ), 'tab' => Controls_Manager::TAB_STYLE ] );

        $this->add_responsive_control( 'panel_width', [
            'label' => __( 'Panel width', 'sherman-core' ),
            'type' => Controls_Manager::SLIDER,
            'size_units' => [ 'px', '%' ],
            'range' => [
                'px' => [ 'min' => 200, 'max' => 800 ],
                '%'  => [ 'min' => 20, 'max' => 100 ],
            ],
            'default' => [ 'unit' => 'px', 'size' => 420 ],
            'selectors' => [
                '{{WRAPPER}} .sle-offcanvas__panel' => 'width: {{SIZE}}{{UNIT}};',
            ],
        ] );

        $this->add_responsive_control( 'inner_padding', [
            'label' => __( 'Inner padding', 'sherman-core' ),
            'type' => Controls_Manager::DIMENSIONS,
            'size_units' => [ 'px', 'em', '%' ],
            'selectors' => [
                '{{WRAPPER}} .sle-offcanvas__inner' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
            ],
        ] );

        $this->end_controls_section();
    }

    protected function render() {
        $s = $this->get_settings_for_display();
        $id = $this->get_id();
        $side = ( $s['side'] ?? 'right' ) === 'left' ? 'left' : 'right';
        $prevent_scroll = ( $s['prevent_scroll'] ?? '' ) === 'true';

        $wrapper_attrs = sprintf(
            'data-offcanvas-id="%1$s" data-prevent-scroll="%2$s"',
            esc_attr( $id ),
            $prevent_scroll ? 'true' : 'false'
        );
        ?>
        <div class="sle-offcanvas-wrapper" <?php echo $wrapper_attrs; ?>>
            <a href="#" class="sle-offcanvas__trigger" data-sle-offcanvas-open="<?php echo esc_attr( $id ); ?>" aria-expanded="false" aria-controls="sle-offcanvas-<?php echo esc_attr( $id ); ?>">
                <?php if ( ! empty( $s['trigger_icon']['value'] ) ) : ?>
                    <span class="sle-offcanvas__trigger-icon">
                        <?php Icons_Manager::render_icon( $s['trigger_icon'], [ 'aria-hidden' => 'true' ] ); ?>
                    </span>
                <?php endif; ?>
                <span class="sle-offcanvas__trigger-text"><?php echo esc_html( $s['trigger_text'] ?? '' ); ?></span>
            </a>

            <div class="sle-offcanvas sle-offcanvas-side-<?php echo esc_attr( $side ); ?>" data-sle-offcanvas-container="<?php echo esc_attr( $id ); ?>">
                <div class="sle-offcanvas__overlay" data-sle-offcanvas-close="<?php echo esc_attr( $id ); ?>"></div>

                <div class="sle-offcanvas__panel" id="sle-offcanvas-<?php echo esc_attr( $id ); ?>" tabindex="-1" aria-hidden="true">
                    <button class="sle-offcanvas__close" type="button" data-sle-offcanvas-close="<?php echo esc_attr( $id ); ?>" aria-label="<?php echo esc_attr__( 'Close', 'sherman-core' ); ?>">×</button>
                    <div class="sle-offcanvas__inner">
                        <?php $this->render_template( $s['template_id'] ?? '' ); ?>
                    </div>
                </div>
            </div>
        </div>
        <?php
    }

    private function render_template( $template_id ): void {
        $template_id = absint( $template_id );
        if ( ! $template_id ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="sherman-core-widget-notice">' . esc_html__( 'No template selected.', 'sherman-core' ) . '</div>';
            }
            return;
        }

        if ( ! class_exists( '\\Elementor\\Plugin' ) ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="sherman-core-widget-notice">' . esc_html__( 'Elementor is not available.', 'sherman-core' ) . '</div>';
            }
            return;
        }

        $content = \Elementor\Plugin::instance()->frontend->get_builder_content_for_display( $template_id );
        if ( ! $content ) {
            if ( current_user_can( 'manage_options' ) ) {
                echo '<div class="sherman-core-widget-notice">' . esc_html__( 'Template content is empty.', 'sherman-core' ) . '</div>';
            }
            return;
        }

        echo $content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }

    private function get_elementor_templates_options(): array {
        $options = [ '' => __( '— Select template —', 'sherman-core' ) ];
        $templates = get_posts( [
            'post_type'      => 'elementor_library',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ] );
        foreach ( (array) $templates as $tpl ) {
            $options[ (string) $tpl->ID ] = $tpl->post_title;
        }
        return $options;
    }
}
