<?php
/**
 * WordPress Ajax Process Execution
 *
 * @package WordPress
 * @subpackage Administration
 *
 * @link https://developer.wordpress.org/plugins/javascript/ajax
 */

/**
 * Executing Ajax process.
 *
 * @since 2.1.0
 */
define( 'DOING_AJAX', true );
if ( ! defined( 'WP_ADMIN' ) ) {
	define( 'WP_ADMIN', true );
}

/** Load WordPress Bootstrap */
require_once dirname( __DIR__ ) . '/wp-load.php';

/** Allow for cross-domain requests (from the front end). */
send_origin_headers();

header( 'Content-Type: text/html; charset=' . get_option( 'blog_charset' ) );
header( 'X-Robots-Tag: noindex' );

// Require a valid action parameter.
if ( empty( $_REQUEST['action'] ) || ! is_scalar( $_REQUEST['action'] ) ) {
	wp_die( '0', 400 );
}

/** Load WordPress Administration APIs */
require_once ABSPATH . 'wp-admin/includes/admin.php';

/** Load Ajax Handlers for WordPress Core */
require_once ABSPATH . 'wp-admin/includes/ajax-actions.php';

send_nosniff_header();
nocache_headers();

/** This action is documented in wp-admin/admin.php */
do_action( 'admin_init' );

$core_actions_get = array(
	'fetch-list',
	'ajax-tag-search',
	'wp-compression-test',
	'imgedit-preview',
	'oembed-cache',
	'autocomplete-user',
	'dashboard-widgets',
	'logged-in',
	'rest-nonce',
);

$core_actions_post = array(
	'oembed-cache',
	'image-editor',
	'delete-comment',
	'delete-tag',
	'delete-link',
	'delete-meta',
	'delete-post',
	'trash-post',
	'untrash-post',
	'delete-page',
	'dim-comment',
	'add-link-category',
	'add-tag',
	'get-tagcloud',
	'get-comments',
	'replyto-comment',
	'edit-comment',
	'add-menu-item',
	'add-meta',
	'add-user',
	'closed-postboxes',
	'hidden-columns',
	'update-welcome-panel',
	'menu-get-metabox',
	'wp-link-ajax',
	'menu-locations-save',
	'menu-quick-search',
	'meta-box-order',
	'get-permalink',
	'sample-permalink',
	'inline-save',
	'inline-save-tax',
	'find_posts',
	'widgets-order',
	'save-widget',
	'delete-inactive-widgets',
	'set-post-thumbnail',
	'date_format',
	'time_format',
	'wp-remove-post-lock',
	'dismiss-wp-pointer',
	'upload-attachment',
	'get-attachment',
	'query-attachments',
	'save-attachment',
	'save-attachment-compat',
	'send-link-to-editor',
	'send-attachment-to-editor',
	'save-attachment-order',
	'media-create-image-subsizes',
	'heartbeat',
	'get-revision-diffs',
	'save-user-color-scheme',
	'update-widget',
	'query-themes',
	'parse-embed',
	'set-attachment-thumbnail',
	'parse-media-shortcode',
	'destroy-sessions',
	'install-plugin',
	'activate-plugin',
	'update-plugin',
	'crop-image',
	'generate-password',
	'save-wporg-username',
	'delete-plugin',
	'search-plugins',
	'search-install-plugins',
	'activate-plugin',
	'update-theme',
	'delete-theme',
	'install-theme',
	'get-post-thumbnail-html',
	'get-community-events',
	'edit-theme-plugin-file',
	'wp-privacy-export-personal-data',
	'wp-privacy-erase-personal-data',
	'health-check-site-status-result',
	'health-check-dotorg-communication',
	'health-check-is-in-debug-mode',
	'health-check-background-updates',
	'health-check-loopback-requests',
	'health-check-get-sizes',
	'toggle-auto-updates',
	'send-password-reset',
);

// Deprecated.
$core_actions_post_deprecated = array(
	'wp-fullscreen-save-post',
	'press-this-save-post',
	'press-this-add-category',
	'health-check-dotorg-communication',
	'health-check-is-in-debug-mode',
	'health-check-background-updates',
	'health-check-loopback-requests',
);

$core_actions_post = array_merge( $core_actions_post, $core_actions_post_deprecated );

// Register core Ajax calls.
if ( ! empty( $_GET['action'] ) && in_array( $_GET['action'], $core_actions_get, true ) ) {
	add_action( 'wp_ajax_' . $_GET['action'], 'wp_ajax_' . str_replace( '-', '_', $_GET['action'] ), 1 );
}

if ( ! empty( $_POST['action'] ) && in_array( $_POST['action'], $core_actions_post, true ) ) {
	add_action( 'wp_ajax_' . $_POST['action'], 'wp_ajax_' . str_replace( '-', '_', $_POST['action'] ), 1 );
}

add_action( 'wp_ajax_nopriv_generate-password', 'wp_ajax_nopriv_generate_password' );

add_action( 'wp_ajax_nopriv_heartbeat', 'wp_ajax_nopriv_heartbeat', 1 );

// Register Plugin Dependencies Ajax calls.
add_action( 'wp_ajax_check_plugin_dependencies', array( 'WP_Plugin_Dependencies', 'check_plugin_dependencies_during_ajax' ) );

$action = $_REQUEST['action'];

if ( is_user_logged_in() ) {
	// If no action is registered, return a Bad Request response.
	if ( ! has_action( "wp_ajax_{$action}" ) ) {
		wp_die( '0', 400 );
	}

	/**
	 * Fires authenticated Ajax actions for logged-in users.
	 *
	 * The dynamic portion of the hook name, `$action`, refers
	 * to the name of the Ajax action callback being fired.
	 *
	 * @since 2.1.0
	 */
	do_action( "wp_ajax_{$action}" );
} else {
	// If no action is registered, return a Bad Request response.
	if ( ! has_action( "wp_ajax_nopriv_{$action}" ) ) {
		wp_die( '0', 400 );
	}

	/**
	 * Fires non-authenticated Ajax actions for logged-out users.
	 *
	 * The dynamic portion of the hook name, `$action`, refers
	 * to the name of the Ajax action callback being fired.
	 *
	 * @since 2.8.0
	 */
	do_action( "wp_ajax_nopriv_{$action}" );
}

// Default status.
wp_die( '0' );













// admin-ajax.php
// Обработчик AJAX-запросов для управления заявками
// Поместите файл в корень или в папку с WP, и убедитесь, что путь к нему совпадает с admin_url('admin-ajax.php')

if (!defined('ABSPATH')) {
    define('ABSPATH', dirname(__FILE__) . '/');
}
// Настройки БД
$db_host = 'localhost';
$db_user = 'DB_USER';
$db_pass = 'DB_PASSWORD';
$db_name = 'DB_NAME';

header('Content-Type: application/json; charset=utf-8');
session_start();

// Авторизация по сессии (пример)
if (empty($_SESSION['user_logged_in']) || $_SESSION['user_role'] !== 'administrator') {
    http_response_code(403);
    echo json_encode(['success' => false, 'data' => 'Доступ запрещён']);
    exit;
}

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'data' => 'Ошибка БД']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $query);

switch ($method) {
    case 'GET':
        // Получаем заявки на дату
        $date = $mysqli->real_escape_string($query['date'] ?? '');
        $stmt = $mysqli->prepare(
            "SELECT o.id, o.service_id, o.booking_date, o.start_time, o.end_time,
                    o.client_name, o.client_phone, o.client_email, o.price,
                    s.name AS service_name
             FROM wp_booking_orders o
             JOIN wp_booking_services s ON o.service_id = s.id
             WHERE o.booking_date = ?"
        );
        $stmt->bind_param('s', $date);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        echo json_encode(['success' => true, 'data' => $rows]);
        break;

    case 'POST':
        // Получаем свободные слоты через существующий AJAX-обработчик из functions.php
        // Перенаправляем запрос к WordPress admin-ajax
        // Для простоты можно сделать сюда proxy, но лучше вызывать функцию get_time_slots напрямую из WP
        http_response_code(400);
        echo json_encode(['success' => false, 'data' => 'Неверный метод POST для этого эндпоинта']);
        break;

    case 'PUT':
    case 'DELETE':
        http_response_code(405);
        echo json_encode(['success' => false, 'data' => 'Метод не поддерживается']);
        break;

    default:
        http_response_code(405);
        echo json_encode(['success' => false, 'data' => 'Метод не поддерживается']);
}

$mysqli->close();

