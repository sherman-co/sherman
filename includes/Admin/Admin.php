<?php
namespace ShermanCore\Admin;

use ShermanCore\Core\ModuleRegistry;
use ShermanCore\Core\Settings;

final class Admin {

    public const MENU_SLUG = 'sherman-core-next';

    public function init(): void {
        add_action( 'admin_menu', [ $this, 'register_menu' ] );
        add_action( 'admin_init', [ $this, 'register_settings' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function register_menu(): void {
        add_menu_page(
            __( 'Sherman Core', 'sherman-core' ),
            __( 'Sherman Core', 'sherman-core' ),
            'manage_options',
            self::MENU_SLUG,
            [ $this, 'render_page' ],
            'dashicons-admin-generic',
            56
        );
    }

    public function register_settings(): void {
        register_setting(
            'sherman_core_settings_group',
            Settings::OPTION_NAME,
            [
                'type'              => 'array',
                'sanitize_callback' => [ Settings::class, 'sanitize' ],
                'default'           => Settings::defaults(),
            ]
        );
    }

    public function enqueue_assets( string $hook ): void {
        if ( $hook !== 'toplevel_page_' . self::MENU_SLUG ) { return; }

        wp_enqueue_style(
            'sherman-core-admin',
            SHERMAN_CORE_NEXT_URL . 'assets/admin/admin.css',
            [],
            SHERMAN_CORE_NEXT_VERSION
        );

        wp_enqueue_script(
            'sherman-core-admin',
            SHERMAN_CORE_NEXT_URL . 'assets/admin/admin.js',
            [],
            SHERMAN_CORE_NEXT_VERSION,
            true
        );
    }

    public function render_page(): void {
        if ( ! current_user_can( 'manage_options' ) ) { return; }

        $settings   = Settings::get_all();
        $categories = ModuleRegistry::categories();
        $modules    = ModuleRegistry::sort_modules( ModuleRegistry::modules() );

        uasort( $categories, static function ( $a, $b ) {
            return (int) ( $a['order'] ?? 100 ) <=> (int) ( $b['order'] ?? 100 );
        } );
        ?>
        <div class="wrap sherman-core-admin">
            <h1><?php echo esc_html__( 'Sherman Core', 'sherman-core' ); ?></h1>

            <div class="sherman-core-admin__topbar">
                <input type="search" class="sherman-core-admin__search" placeholder="<?php echo esc_attr__( 'Search modules and settings…', 'sherman-core' ); ?>" />
                <label class="sherman-core-admin__toggle-advanced">
                    <input type="hidden" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[ui][show_advanced]" value="no" form="sherman-core-settings-form">
                    <input type="checkbox" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[ui][show_advanced]" value="yes" form="sherman-core-settings-form" <?php checked( ( $settings['ui']['show_advanced'] ?? 'no' ), 'yes' ); ?>>
                    <?php echo esc_html__( 'Show advanced', 'sherman-core' ); ?>
                </label>
            </div>

            <div class="sherman-core-admin__layout">
                <aside class="sherman-core-admin__sidebar" aria-label="<?php echo esc_attr__( 'Settings categories', 'sherman-core' ); ?>">
                    <ul class="sherman-core-admin__nav">
                        <?php foreach ( $categories as $cat_id => $cat ) : ?>
                            <li><a href="#cat-<?php echo esc_attr( $cat_id ); ?>" class="sherman-core-admin__navlink"><?php echo esc_html( $cat['title'] ); ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </aside>

                <main class="sherman-core-admin__content">
                    <form method="post" action="options.php" id="sherman-core-settings-form">
                        <?php settings_fields( 'sherman_core_settings_group' ); ?>

                        <?php foreach ( $categories as $cat_id => $cat ) : ?>
                            <section class="sherman-core-admin__category" id="cat-<?php echo esc_attr( $cat_id ); ?>">
                                <h2 class="sherman-core-admin__category-title"><?php echo esc_html( $cat['title'] ); ?></h2>

                                <?php
                                $cat_modules = array_filter( $modules, static function ( $m ) use ( $cat_id ) {
                                    return ( $m->manifest()['category'] ?? '' ) === $cat_id;
                                } );
                                ?>

                                <?php if ( empty( $cat_modules ) ) : ?>
                                    <p class="description"><?php echo esc_html__( 'No modules in this category yet.', 'sherman-core' ); ?></p>
                                <?php else : ?>
                                    <div class="sherman-core-admin__modules">
                                        <?php foreach ( $cat_modules as $module ) : ?>
                                            <?php
                                            $mf     = $module->manifest();
                                            $mid    = $module->id();
                                            $ok     = $module->dependencies_ok();
                                            $en     = $settings['modules'][ $mid ]['enabled'] ?? 'no';
                                            $deps   = $mf['dependencies'] ?? [];
                                            $search = strtolower( ( $mf['title'] ?? '' ) . ' ' . ( $mf['description'] ?? '' ) . ' ' . implode( ' ', (array) ( $mf['keywords'] ?? [] ) ) );
                                            ?>
                                            <div class="sherman-core-module" data-module-id="<?php echo esc_attr( $mid ); ?>" data-search="<?php echo esc_attr( $search ); ?>">
                                                <div class="sherman-core-module__header">
                                                    <div class="sherman-core-module__meta">
                                                        <h3 class="sherman-core-module__title"><?php echo esc_html( $mf['title'] ?? $mid ); ?></h3>
                                                        <?php if ( ! empty( $mf['description'] ) ) : ?>
                                                            <p class="sherman-core-module__desc"><?php echo esc_html( $mf['description'] ); ?></p>
                                                        <?php endif; ?>

                                                        <?php if ( ! empty( $deps ) ) : ?>
                                                            <div class="sherman-core-module__badges">
                                                                <?php foreach ( (array) $deps as $d ) : ?>
                                                                    <span class="sherman-core-badge"><?php echo esc_html( $d ); ?></span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php endif; ?>

                                                        <?php if ( ! $ok ) : ?>
                                                            <div class="notice notice-warning inline"><p><?php echo esc_html__( 'Dependencies are missing. This module cannot run right now.', 'sherman-core' ); ?></p></div>
                                                        <?php endif; ?>
                                                    </div>

                                                    <div class="sherman-core-module__actions">
                                                        <input type="hidden" name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[modules][<?php echo esc_attr( $mid ); ?>][enabled]" value="no">
                                                        <label class="sherman-core-switch">
                                                            <input type="checkbox"
                                                                name="<?php echo esc_attr( Settings::OPTION_NAME ); ?>[modules][<?php echo esc_attr( $mid ); ?>][enabled]"
                                                                value="yes"
                                                                <?php checked( $en, 'yes' ); ?>
                                                                <?php disabled( ! $ok ); ?>
                                                            >
                                                            <span class="sherman-core-switch__slider" aria-hidden="true"></span>
                                                            <span class="screen-reader-text"><?php echo esc_html__( 'Enable module', 'sherman-core' ); ?></span>
                                                        </label>
                                                        <button type="button" class="button sherman-core-module__toggle" aria-expanded="false"><?php echo esc_html__( 'Configure', 'sherman-core' ); ?></button>
                                                    </div>
                                                </div>

                                                <div class="sherman-core-module__body" hidden>
                                                    <?php $this->render_module_settings( $mf, $settings ); ?>
                                                </div>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                            </section>
                        <?php endforeach; ?>

                        <?php submit_button( __( 'Save changes', 'sherman-core' ) ); ?>
                    </form>
                </main>
            </div>
        </div>
        <?php
    }

    private function render_module_settings( array $manifest, array $settings ): void {
        $module_id = $manifest['id'] ?? '';
        if ( $module_id === 'templates' ) {
            $this->render_templates_settings( $settings );
            return;
        }

        $groups = (array) ( $manifest['settings_groups'] ?? [] );
        if ( empty( $groups ) ) {
            echo '<p class="description">' . esc_html__( 'This module has no configurable settings yet.', 'sherman-core' ) . '</p>';
            return;
        }
        foreach ( $groups as $group_id => $group ) {
            $is_advanced = (bool) ( $group['advanced'] ?? false );
            $show_adv = ( $settings['ui']['show_advanced'] ?? 'no' ) === 'yes';
            if ( $is_advanced && ! $show_adv ) { continue; }

            echo '<div class="sherman-core-group" data-group-id="' . esc_attr( $group_id ) . '">';
            echo '<h4 class="sherman-core-group__title">' . esc_html( $group['title'] ?? $group_id ) . '</h4>';
            if ( ! empty( $group['description'] ) ) {
                echo '<p class="sherman-core-group__desc">' . esc_html( $group['description'] ) . '</p>';
            }
            echo '<p class="description">' . esc_html__( 'Fields will be added here as the module is migrated.', 'sherman-core' ) . '</p>';
            echo '</div>';
        }
    }

    private function render_templates_settings( array $settings ): void {
        $single_en = $settings['modules']['templates']['single_product']['enabled'] ?? 'no';
        $single_id = (int) ( $settings['modules']['templates']['single_product']['template_id'] ?? 0 );
        $archive_en = $settings['modules']['templates']['archive_product']['enabled'] ?? 'no';
        $archive_id = (int) ( $settings['modules']['templates']['archive_product']['template_id'] ?? 0 );

        $header_en = $settings['modules']['templates']['header']['enabled'] ?? 'no';
        $header_id = (int) ( $settings['modules']['templates']['header']['template_id'] ?? 0 );
        $header_scope = (string) ( $settings['modules']['templates']['header']['scope'] ?? 'woocommerce' );

        $footer_en = $settings['modules']['templates']['footer']['enabled'] ?? 'no';
        $footer_id = (int) ( $settings['modules']['templates']['footer']['template_id'] ?? 0 );
        $footer_scope = (string) ( $settings['modules']['templates']['footer']['scope'] ?? 'woocommerce' );

        // Elementor templates (Elementor Library).
        $options = [ 0 => __( '— Select template —', 'sherman-core' ) ];
        $templates = get_posts( [
            'post_type'      => 'elementor_library',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ] );
        foreach ( (array) $templates as $tpl ) {
            $options[ (int) $tpl->ID ] = $tpl->post_title;
        }

        echo '<div class="sherman-core-group" data-group-id="basics">';
        echo '<h4 class="sherman-core-group__title">' . esc_html__( 'WooCommerce templates', 'sherman-core' ) . '</h4>';
        echo '<p class="sherman-core-group__desc">' . esc_html__( 'Select Elementor templates for WooCommerce single product and product archive pages.', 'sherman-core' ) . '</p>';

        echo '<table class="form-table" role="presentation"><tbody>';

        // Single product.
        echo '<tr><th scope="row">' . esc_html__( 'Single product', 'sherman-core' ) . '</th><td>';
        echo '<input type="hidden" name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][single_product][enabled]" value="no">';
        echo '<label><input type="checkbox" name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][single_product][enabled]" value="yes" ' . checked( $single_en, 'yes', false ) . '> ' . esc_html__( 'Enable override', 'sherman-core' ) . '</label>';
        echo '<br><br>';
        echo '<select name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][single_product][template_id]">';
        foreach ( $options as $id => $title ) {
            echo '<option value="' . esc_attr( (string) $id ) . '" ' . selected( (int) $id, $single_id, false ) . '>' . esc_html( $title ) . '</option>';
        }
        echo '</select>';
        echo '</td></tr>';

        // Product archive.
        echo '<tr><th scope="row">' . esc_html__( 'Product archive', 'sherman-core' ) . '</th><td>';
        echo '<input type="hidden" name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][archive_product][enabled]" value="no">';
        echo '<label><input type="checkbox" name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][archive_product][enabled]" value="yes" ' . checked( $archive_en, 'yes', false ) . '> ' . esc_html__( 'Enable override', 'sherman-core' ) . '</label>';
        echo '<br><br>';
        echo '<select name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][archive_product][template_id]">';
        foreach ( $options as $id => $title ) {
            echo '<option value="' . esc_attr( (string) $id ) . '" ' . selected( (int) $id, $archive_id, false ) . '>' . esc_html( $title ) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . esc_html__( 'Applies to Shop, product category, and product tag archives.', 'sherman-core' ) . '</p>';
        echo '</td></tr>';

        // Header override.
        echo '<tr><th scope="row">' . esc_html__( 'Header override', 'sherman-core' ) . '</th><td>';
        echo '<input type="hidden" name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][header][enabled]" value="no">';
        echo '<label><input type="checkbox" name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][header][enabled]" value="yes" ' . checked( $header_en, 'yes', false ) . '> ' . esc_html__( 'Enable override', 'sherman-core' ) . '</label>';
        echo '<br><br>';
        echo '<select name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][header][template_id]">';
        foreach ( $options as $id => $title ) {
            echo '<option value="' . esc_attr( (string) $id ) . '" ' . selected( (int) $id, $header_id, false ) . '>' . esc_html( $title ) . '</option>';
        }
        echo '</select>';
        echo '<br><br>';
        echo '<label>' . esc_html__( 'Scope', 'sherman-core' ) . ': ';
        echo '<select name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][header][scope]">';
        echo '<option value="woocommerce" ' . selected( $header_scope, 'woocommerce', false ) . '>' . esc_html__( 'WooCommerce pages only', 'sherman-core' ) . '</option>';
        echo '<option value="global" ' . selected( $header_scope, 'global', false ) . '>' . esc_html__( 'Entire site', 'sherman-core' ) . '</option>';
        echo '</select>';
        echo '</label>';
        echo '<p class="description">' . esc_html__( 'Replaces the theme header with an Elementor template within overridden pages. If you choose “Entire site”, you may conflict with your theme/Elementor Theme Builder.', 'sherman-core' ) . '</p>';
        echo '</td></tr>';

        // Footer override.
        echo '<tr><th scope="row">' . esc_html__( 'Footer override', 'sherman-core' ) . '</th><td>';
        echo '<input type="hidden" name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][footer][enabled]" value="no">';
        echo '<label><input type="checkbox" name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][footer][enabled]" value="yes" ' . checked( $footer_en, 'yes', false ) . '> ' . esc_html__( 'Enable override', 'sherman-core' ) . '</label>';
        echo '<br><br>';
        echo '<select name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][footer][template_id]">';
        foreach ( $options as $id => $title ) {
            echo '<option value="' . esc_attr( (string) $id ) . '" ' . selected( (int) $id, $footer_id, false ) . '>' . esc_html( $title ) . '</option>';
        }
        echo '</select>';
        echo '<br><br>';
        echo '<label>' . esc_html__( 'Scope', 'sherman-core' ) . ': ';
        echo '<select name="' . esc_attr( Settings::OPTION_NAME ) . '[modules][templates][footer][scope]">';
        echo '<option value="woocommerce" ' . selected( $footer_scope, 'woocommerce', false ) . '>' . esc_html__( 'WooCommerce pages only', 'sherman-core' ) . '</option>';
        echo '<option value="global" ' . selected( $footer_scope, 'global', false ) . '>' . esc_html__( 'Entire site', 'sherman-core' ) . '</option>';
        echo '</select>';
        echo '</label>';
        echo '<p class="description">' . esc_html__( 'Replaces the theme footer with an Elementor template within overridden pages. If you choose “Entire site”, you may conflict with your theme/Elementor Theme Builder.', 'sherman-core' ) . '</p>';
        echo '</td></tr>';

        echo '</tbody></table>';

        echo '</div>';
    }
}
