<?php
namespace ShermanCore\Modules\MSDS;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * WooCommerce Product MSDS tab implementation.
 *
 * This mirrors the legacy plugin behavior so existing meta keys and Dynamic Tags keep working.
 */
final class ProductMSDS {

    public const META_PDF_ID    = '_ps_msds_pdf_id';
    public const META_FILE_NAME = '_ps_msds_file_name';
    public const META_URL       = '_ps_msds_url';
    public const META_AVAILABLE = '_ps_msds_available';
    public const META_CHECK_NOW = '_ps_msds_check_now';

    public static function init(): void {
        if ( ! is_admin() ) {
            return;
        }

        // Product edit screen UI.
        add_filter( 'woocommerce_product_data_tabs', [ __CLASS__, 'add_msds_tab' ] );
        add_action( 'woocommerce_product_data_panels', [ __CLASS__, 'render_msds_panel' ] );
        add_action( 'woocommerce_process_product_meta', [ __CLASS__, 'save_msds_meta' ], 10, 2 );

        // Assets (wp.media uploader).
        add_action( 'admin_enqueue_scripts', [ __CLASS__, 'enqueue_admin_assets' ] );
    }

    public static function add_msds_tab( array $tabs ): array {
        $tabs['ps_msds'] = [
            'label'    => __( 'Product MSDS', 'sherman-core' ),
            'target'   => 'ps_msds_product_data',
            'class'    => [],
            'priority' => 60,
        ];

        return $tabs;
    }

    public static function enqueue_admin_assets( string $hook ): void {
        // Only on product edit/new screens.
        if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) {
            return;
        }

        $screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
        if ( ! $screen || $screen->post_type !== 'product' ) {
            return;
        }

        // Media uploader.
        wp_enqueue_media();

        wp_enqueue_script(
            'sherman-core-msds-admin',
            SHERMAN_CORE_NEXT_URL . 'assets/admin/msds.js',
            [ 'jquery' ],
            SHERMAN_CORE_NEXT_VERSION,
            true
        );

        wp_localize_script(
            'sherman-core-msds-admin',
            'ShermanCoreMSDS',
            [
                'metaKeyPdfId' => self::META_PDF_ID,
                'strings' => [
                    'selectTitle' => __( 'Select MSDS PDF', 'sherman-core' ),
                    'selectButton' => __( 'Use this file', 'sherman-core' ),
                    'noFile' => __( 'No file selected.', 'sherman-core' ),
                ],
            ]
        );
    }

    public static function render_msds_panel(): void {
        global $post;
        if ( ! $post || $post->post_type !== 'product' ) {
            return;
        }

        $product_id = (int) $post->ID;

        $pdf_id    = (int) get_post_meta( $product_id, self::META_PDF_ID, true );
        $file_name = (string) get_post_meta( $product_id, self::META_FILE_NAME, true );
        $msds_url  = (string) get_post_meta( $product_id, self::META_URL, true );
        $available = (string) get_post_meta( $product_id, self::META_AVAILABLE, true );
        $check_now = (string) get_post_meta( $product_id, self::META_CHECK_NOW, true );

        if ( $available === '' ) {
            $available = __( 'Available', 'sherman-core' );
        }
        if ( $check_now === '' ) {
            $check_now = __( 'Check Now', 'sherman-core' );
        }

        $pdf_url   = $pdf_id ? wp_get_attachment_url( $pdf_id ) : '';
        $pdf_title = $pdf_id ? get_the_title( $pdf_id ) : '';

        ?>
        <div id="ps_msds_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <p class="form-field">
                    <label for="<?php echo esc_attr( self::META_PDF_ID ); ?>">
                        <?php esc_html_e( 'PDF File', 'sherman-core' ); ?>
                    </label>

                    <input type="hidden"
                           id="<?php echo esc_attr( self::META_PDF_ID ); ?>"
                           name="<?php echo esc_attr( self::META_PDF_ID ); ?>"
                           value="<?php echo esc_attr( (string) $pdf_id ); ?>" />

                    <button type="button" class="button ps-msds-pdf-upload">
                        <?php esc_html_e( 'Select PDF', 'sherman-core' ); ?>
                    </button>

                    <button type="button" class="button ps-msds-pdf-remove" <?php disabled( ! $pdf_id ); ?>>
                        <?php esc_html_e( 'Remove', 'sherman-core' ); ?>
                    </button>

                    <span class="ps-msds-pdf-file-display" style="margin-left:8px;">
                        <?php if ( $pdf_url ) : ?>
                            <a href="<?php echo esc_url( $pdf_url ); ?>" target="_blank" rel="noopener noreferrer">
                                <?php echo esc_html( $pdf_title ?: basename( $pdf_url ) ); ?>
                            </a>
                        <?php else : ?>
                            <span class="description"><?php esc_html_e( 'No file selected.', 'sherman-core' ); ?></span>
                        <?php endif; ?>
                    </span>

                    <span class="description" style="display:block;margin-top:4px;">
                        <?php esc_html_e( 'Upload or select the MSDS PDF file related to this product.', 'sherman-core' ); ?>
                    </span>
                </p>

                <?php
                if ( function_exists( 'woocommerce_wp_text_input' ) ) {
                    woocommerce_wp_text_input( [
                        'id'          => self::META_FILE_NAME,
                        'label'       => __( 'File Name', 'sherman-core' ),
                        'desc_tip'    => true,
                        'description' => __( 'Optional display name for the MSDS file.', 'sherman-core' ),
                        'value'       => $file_name,
                    ] );

                    woocommerce_wp_text_input( [
                        'id'          => self::META_URL,
                        'label'       => __( 'MSDS URL', 'sherman-core' ),
                        'desc_tip'    => true,
                        'description' => __( 'Direct URL to external MSDS page or file.', 'sherman-core' ),
                        'value'       => $msds_url,
                        'type'        => 'url',
                    ] );

                    woocommerce_wp_text_input( [
                        'id'          => self::META_AVAILABLE,
                        'label'       => __( 'Available Text', 'sherman-core' ),
                        'desc_tip'    => true,
                        'description' => __( 'Text label for "Available" status (default: "Available").', 'sherman-core' ),
                        'value'       => $available,
                    ] );

                    woocommerce_wp_text_input( [
                        'id'          => self::META_CHECK_NOW,
                        'label'       => __( 'Check Now Text', 'sherman-core' ),
                        'desc_tip'    => true,
                        'description' => __( 'Text label for "Check Now" action (default: "Check Now").', 'sherman-core' ),
                        'value'       => $check_now,
                    ] );
                }
                ?>
            </div>
        </div>
        <?php
    }

    public static function save_msds_meta( int $post_id, $post ): void {
        if ( ! $post || ( isset( $post->post_type ) && $post->post_type !== 'product' ) ) {
            return;
        }

        // PDF ID
        if ( isset( $_POST[ self::META_PDF_ID ] ) ) {
            $pdf_id = (int) $_POST[ self::META_PDF_ID ];
            if ( $pdf_id > 0 ) {
                update_post_meta( $post_id, self::META_PDF_ID, $pdf_id );
            } else {
                delete_post_meta( $post_id, self::META_PDF_ID );
            }
        }

        // File Name
        if ( isset( $_POST[ self::META_FILE_NAME ] ) ) {
            $file_name = sanitize_text_field( wp_unslash( $_POST[ self::META_FILE_NAME ] ) );
            if ( $file_name !== '' ) {
                update_post_meta( $post_id, self::META_FILE_NAME, $file_name );
            } else {
                delete_post_meta( $post_id, self::META_FILE_NAME );
            }
        }

        // URL
        if ( isset( $_POST[ self::META_URL ] ) ) {
            $url = esc_url_raw( wp_unslash( $_POST[ self::META_URL ] ) );
            if ( $url !== '' ) {
                update_post_meta( $post_id, self::META_URL, $url );
            } else {
                delete_post_meta( $post_id, self::META_URL );
            }
        }

        // Available
        if ( isset( $_POST[ self::META_AVAILABLE ] ) ) {
            $available = sanitize_text_field( wp_unslash( $_POST[ self::META_AVAILABLE ] ) );
            if ( $available !== '' ) {
                update_post_meta( $post_id, self::META_AVAILABLE, $available );
            } else {
                delete_post_meta( $post_id, self::META_AVAILABLE );
            }
        }

        // Check Now
        if ( isset( $_POST[ self::META_CHECK_NOW ] ) ) {
            $check_now = sanitize_text_field( wp_unslash( $_POST[ self::META_CHECK_NOW ] ) );
            if ( $check_now !== '' ) {
                update_post_meta( $post_id, self::META_CHECK_NOW, $check_now );
            } else {
                delete_post_meta( $post_id, self::META_CHECK_NOW );
            }
        }
    }
}
