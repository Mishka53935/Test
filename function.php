<?php
/**
 * Twenty Twenty-Four functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Twenty Twenty-Four
 * @since Twenty Twenty-Four 1.0
 */

/**
 * Register block styles.
 */

if ( ! function_exists( 'twentytwentyfour_block_styles' ) ) :
	/**
	 * Register custom block styles
	 *
	 * @since Twenty Twenty-Four 1.0
	 * @return void
	 */
	function twentytwentyfour_block_styles() {

		register_block_style(
			'core/details',
			array(
				'name'         => 'arrow-icon-details',
				'label'        => __( 'Arrow icon', 'twentytwentyfour' ),
				/*
				 * Styles for the custom Arrow icon style of the Details block
				 */
				'inline_style' => '
				.is-style-arrow-icon-details {
					padding-top: var(--wp--preset--spacing--10);
					padding-bottom: var(--wp--preset--spacing--10);
				}

				.is-style-arrow-icon-details summary {
					list-style-type: "\2193\00a0\00a0\00a0";
				}

				.is-style-arrow-icon-details[open]>summary {
					list-style-type: "\2192\00a0\00a0\00a0";
				}',
			)
		);
		register_block_style(
			'core/post-terms',
			array(
				'name'         => 'pill',
				'label'        => __( 'Pill', 'twentytwentyfour' ),
				/*
				 * Styles variation for post terms
				 * https://github.com/WordPress/gutenberg/issues/24956
				 */
				'inline_style' => '
				.is-style-pill a,
				.is-style-pill span:not([class], [data-rich-text-placeholder]) {
					display: inline-block;
					background-color: var(--wp--preset--color--base-2);
					padding: 0.375rem 0.875rem;
					border-radius: var(--wp--preset--spacing--20);
				}

				.is-style-pill a:hover {
					background-color: var(--wp--preset--color--contrast-3);
				}',
			)
		);
		register_block_style(
			'core/list',
			array(
				'name'         => 'checkmark-list',
				'label'        => __( 'Checkmark', 'twentytwentyfour' ),
				/*
				 * Styles for the custom checkmark list block style
				 * https://github.com/WordPress/gutenberg/issues/51480
				 */
				'inline_style' => '
				ul.is-style-checkmark-list {
					list-style-type: "\2713";
				}

				ul.is-style-checkmark-list li {
					padding-inline-start: 1ch;
				}',
			)
		);
		register_block_style(
			'core/navigation-link',
			array(
				'name'         => 'arrow-link',
				'label'        => __( 'With arrow', 'twentytwentyfour' ),
				/*
				 * Styles for the custom arrow nav link block style
				 */
				'inline_style' => '
				.is-style-arrow-link .wp-block-navigation-item__label:after {
					content: "\2197";
					padding-inline-start: 0.25rem;
					vertical-align: middle;
					text-decoration: none;
					display: inline-block;
				}',
			)
		);
		register_block_style(
			'core/heading',
			array(
				'name'         => 'asterisk',
				'label'        => __( 'With asterisk', 'twentytwentyfour' ),
				'inline_style' => "
				.is-style-asterisk:before {
					content: '';
					width: 1.5rem;
					height: 3rem;
					background: var(--wp--preset--color--contrast-2, currentColor);
					clip-path: path('M11.93.684v8.039l5.633-5.633 1.216 1.23-5.66 5.66h8.04v1.737H13.2l5.701 5.701-1.23 1.23-5.742-5.742V21h-1.737v-8.094l-5.77 5.77-1.23-1.217 5.743-5.742H.842V9.98h8.162l-5.701-5.7 1.23-1.231 5.66 5.66V.684h1.737Z');
					display: block;
				}

				/* Hide the asterisk if the heading has no content, to avoid using empty headings to display the asterisk only, which is an A11Y issue */
				.is-style-asterisk:empty:before {
					content: none;
				}

				.is-style-asterisk:-moz-only-whitespace:before {
					content: none;
				}

				.is-style-asterisk.has-text-align-center:before {
					margin: 0 auto;
				}

				.is-style-asterisk.has-text-align-right:before {
					margin-left: auto;
				}

				.rtl .is-style-asterisk.has-text-align-left:before {
					margin-right: auto;
				}",
			)
		);
	}
endif;

add_action( 'init', 'twentytwentyfour_block_styles' );

/**
 * Enqueue block stylesheets.
 */

if ( ! function_exists( 'twentytwentyfour_block_stylesheets' ) ) :
	/**
	 * Enqueue custom block stylesheets
	 *
	 * @since Twenty Twenty-Four 1.0
	 * @return void
	 */
	function twentytwentyfour_block_stylesheets() {
		/**
		 * The wp_enqueue_block_style() function allows us to enqueue a stylesheet
		 * for a specific block. These will only get loaded when the block is rendered
		 * (both in the editor and on the front end), improving performance
		 * and reducing the amount of data requested by visitors.
		 *
		 * See https://make.wordpress.org/core/2021/12/15/using-multiple-stylesheets-per-block/ for more info.
		 */
		wp_enqueue_block_style(
			'core/button',
			array(
				'handle' => 'twentytwentyfour-button-style-outline',
				'src'    => get_parent_theme_file_uri( 'assets/css/button-outline.css' ),
				'ver'    => wp_get_theme( get_template() )->get( 'Version' ),
				'path'   => get_parent_theme_file_path( 'assets/css/button-outline.css' ),
			)
		);
	}
endif;

add_action( 'init', 'twentytwentyfour_block_stylesheets' );

/**
 * Register pattern categories.
 */

if ( ! function_exists( 'twentytwentyfour_pattern_categories' ) ) :
	/**
	 * Register pattern categories
	 *
	 * @since Twenty Twenty-Four 1.0
	 * @return void
	 */
	function twentytwentyfour_pattern_categories() {

		register_block_pattern_category(
			'page',
			array(
				'label'       => _x( 'Pages', 'Block pattern category' ),
				'description' => __( 'A collection of full page layouts.' ),
			)
		);
	}
endif;

add_action( 'init', 'twentytwentyfour_pattern_categories' );




////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////













/////////////////////////////////////////////////////////////////////////////////////////////













/* Основные обработчики бронирований */
if (!defined('ABSPATH')) exit; // Защита от прямого доступа

// Константы для уведомлений
define('TELEGRAM_BOT_TOKEN', '7623757721:AAElzsJ9ajJq_d1ATbI5VFBoiAF0aKyJpcE');
define('ADMIN_CHAT_ID', '930819186');

/* Основные обработчики бронирований */
add_action('wp_ajax_get_time_slots', 'get_time_slots');
add_action('wp_ajax_nopriv_get_time_slots', 'get_time_slots');
add_action('wp_ajax_submit_booking', 'submit_booking');
add_action('wp_ajax_nopriv_submit_booking', 'submit_booking');
add_action('wp_ajax_nopriv_update_booking', 'update_booking');
add_action('wp_ajax_nopriv_create_booking', 'create_booking');

// Добавляем новые AJAX обработчики
add_action('wp_ajax_delete_booking', 'delete_booking');
add_action('wp_ajax_nopriv_delete_booking', 'delete_booking');
add_action('wp_ajax_update_booking', 'update_booking');
add_action('wp_ajax_create_booking', 'create_booking');

function verify_booking_access() {
    // Получаем ID страницы админки из запроса
    $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
    
    // Проверяем пароль страницы
    if (post_password_required($page_id) && !has_post_password_been_entered($page_id)) {
        wp_send_json_error('Доступ запрещен. Введите пароль страницы.', 403);
    }
}

// В файле functions.php ДОБАВЬТЕ:
add_action('rest_api_init', function() {
    register_rest_route('bookings/v1', '/booking/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'get_booking_details',
        'permission_callback' => '__return_true' // Разрешаем доступ всем
    ]);
});

add_filter('rest_authentication_errors', function($result) {
    $route = isset($_REQUEST['rest_route']) ? $_REQUEST['rest_route'] : '';
    
    // Разрешаем доступ к API бронирований если есть пароль
    if (strpos($route, '/bookings/v1/') === 0) {
        $page_id = isset($_GET['page_id']) ? intval($_GET['page_id']) : 0;
        if (!post_password_required($page_id) || has_post_password_been_entered($page_id)) {
            return true;
        }
        return new WP_Error('rest_forbidden', 'Требуется пароль страницы', ['status' => 403]);
    }
    
    return $result;
});

function get_booking_details($request) {
    global $wpdb;
    
     // Проверка доступа через параметр page_id
    $page_id = $request->get_param('page_id');
    if (post_password_required($page_id) && !has_post_password_been_entered($page_id)) {
        return new WP_Error('rest_forbidden', 'Требуется пароль', ['status' => 403]);
    }
    
    try {
        $id = $request['id'];
        if(!is_numeric($id)) {
            throw new Exception('Неверный ID записи');
        }
        
        $booking = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}booking_orders WHERE id = %d",
            $id
        ));
        
        if(!$booking) {
            throw new Exception('Запись не найдена', 404);
        }
        
        return $booking;
        
    } catch (Exception $e) {
        error_log('Booking API Error: ' . $e->getMessage());
        return new WP_Error('error', $e->getMessage(), ['status' => $e->getCode() ?: 500]);
    }
    
    // Добавляем название услуги
    $service = $wpdb->get_row($wpdb->prepare(
        "SELECT name FROM {$wpdb->prefix}booking_services WHERE id = %d",
        $booking->service_id
    ));
    
    $booking->service_name = $service ? $service->name : 'Неизвестная услуга';
    
    return $booking;
}

function create_booking() {
    global $wpdb;
    
    // Проверка nonce
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'booking_actions')) {
        wp_send_json_error('Неверный запрос', 403);
    }
    
    // Проверка доступа через пароль страницы
    verify_booking_access();

    // Валидация данных
    $required = [
        'service_id', 
        'booking_date', 
        'start_time', 
        'end_time', 
        'client_name', 
        'client_phone', 
        'client_email'
    ];
    
    foreach ($required as $field) {
        if(empty($_POST[$field])) {
            wp_send_json_error("Не заполнено поле: $field", 400);
        }
    }

    // Проверка доступности времени
       $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT o.id 
         FROM {$wpdb->prefix}booking_orders o
         LEFT JOIN {$wpdb->prefix}booking_services s 
             ON o.service_id = s.id
         WHERE o.booking_date = %s 
         AND (
             (ADDTIME(o.end_time, SEC_TO_TIME(s.break * 60)) > %s 
             AND %s > ADDTIME(o.start_time, SEC_TO_TIME(-s.break * 60))
         )",
        $_POST['booking_date'],
        $_POST['start_time'],
        $_POST['end_time']
    ));

    if($exists) {
        wp_send_json_error('Это время уже занято', 400);
    }

    // Создание записи
    $data = [
        'service_id' => intval($_POST['service_id']),
        'booking_date' => sanitize_text_field($_POST['booking_date']),
        'start_time' => sanitize_text_field($_POST['start_time']),
        'end_time' => sanitize_text_field($_POST['end_time']),
        'client_name' => sanitize_text_field($_POST['client_name']),
        'client_phone' => preg_replace('/[^0-9]/', '', $_POST['client_phone']),
        'client_email' => sanitize_email($_POST['client_email']),
        'created_at' => current_time('mysql'),
        'price' => floatval($_POST['price'])
    ];

    $result = $wpdb->insert("{$wpdb->prefix}booking_orders", $data);

    if($result === false) {
        wp_send_json_error('Ошибка базы данных: ' . $wpdb->last_error, 500);
    }

    wp_send_json_success(['id' => $wpdb->insert_id]);
}

function delete_booking() {
    global $wpdb;
    // Проверка nonce
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'booking_actions')) {
        wp_send_json_error('Неверный запрос', 403);
    }
    // Проверка доступа через пароль страницы
    verify_booking_access();


    $id = intval($_POST['id']);
    $result = $wpdb->delete(
        "{$wpdb->prefix}booking_orders", 
        ['id' => $id], 
        ['%d']
    );

    if($result === false) {
        wp_send_json_error('Ошибка удаления');
    }
    
    wp_send_json_success();
}

function update_booking() {
    
    file_put_contents(__DIR__ . '/debug.log', print_r($_POST, true), FILE_APPEND);
    wp_send_json_success('Запрос получен');

    
     if (!wp_verify_nonce($_POST['security'], 'booking_actions')) {
        wp_send_json_error('Неверный nonce');
    }
    global $wpdb;
    
    // Проверка nonce
    if (!isset($_POST['security']) || !wp_verify_nonce($_POST['security'], 'booking_actions')) {
        wp_send_json_error('Неверный запрос', 403);
    }
    
    // Проверка доступа через пароль страницы
    verify_booking_access();

    // Валидация данных
    $required = [
        'service_id', 
        'booking_date', 
        'start_time', 
        'end_time', 
        'client_name', 
        'client_phone', 
        'client_email'
    ];
    
    foreach ($required as $field) {
        if(empty($_POST[$field])) {
            wp_send_json_error("Не заполнено поле: $field", 400);
        }
    }

    // Проверка доступности времени (исключая текущую запись)
    $exists = $wpdb->get_var($wpdb->prepare(
    "SELECT o.id 
     FROM {$wpdb->prefix}booking_orders o
     LEFT JOIN {$wpdb->prefix}booking_services s 
         ON o.service_id = s.id
     WHERE o.booking_date = %s 
     AND (
         (ADDTIME(o.end_time, SEC_TO_TIME(s.break * 60)) > %s 
         AND %s > ADDTIME(o.start_time, SEC_TO_TIME(-s.break * 60))
     )",
    $_POST['booking_date'],
    $_POST['start_time'],
    $_POST['end_time']
));

    if($exists) {
        wp_send_json_error('Это время уже занято', 400);
    }

    // Обновление записи
    $data = [
        'service_id' => intval($_POST['service_id']),
        'booking_date' => sanitize_text_field($_POST['booking_date']),
        'start_time' => sanitize_text_field($_POST['start_time']),
        'end_time' => sanitize_text_field($_POST['end_time']),
        'client_name' => sanitize_text_field($_POST['client_name']),
        'client_phone' => preg_replace('/[^0-9]/', '', $_POST['client_phone']),
        'client_email' => sanitize_email($_POST['client_email'])
    ];

    $result = $wpdb->update(
        "{$wpdb->prefix}booking_orders",
        $data,
        ['id' => intval($_POST['id'])],
        ['%d', '%s', '%s', '%s', '%s', '%s', '%s'],
        ['%d']
    );

    if($result === false) {
        wp_send_json_error('Ошибка базы данных: ' . $wpdb->last_error, 500);
    }

    wp_send_json_success(['message' => 'Запись успешно обновлена']);
}

// =============================================
// БАЗОВЫЕ ФУНКЦИИ БРОНИРОВАНИЯ
// =============================================

function get_time_slots() {
    global $wpdb;
    
    $service_id = intval($_POST['service_id']);
    $date = sanitize_text_field($_POST['date']);
    
    // Проверка даты
    $selected_date = strtotime($date);
    $today = strtotime(date('Y-m-d 00:00:00'));
    
    if ($selected_date < $today) {
        wp_send_json_error('Нельзя выбрать прошедшую дату');
    }
    
    // Получаем данные услуги
    $service = $wpdb->get_row($wpdb->prepare(
        "SELECT duration, break, weekday_price, weekend_price 
         FROM {$wpdb->prefix}booking_services 
         WHERE id = %d", 
        $service_id
    ));
    
    if(!$service) {
        wp_send_json_error('Услуга не найдена');
    }
    
    $day_of_week = date('w', strtotime($date));
    $is_weekend = ($day_of_week == 0 || $day_of_week == 6);
    $price = $is_weekend ? $service->weekend_price : $service->weekday_price;
    
    // Получаем существующие брони с их перерывами
    $bookings = $wpdb->get_results($wpdb->prepare(
        "SELECT o.start_time, o.end_time, s.break 
         FROM {$wpdb->prefix}booking_orders o
         LEFT JOIN {$wpdb->prefix}booking_services s ON o.service_id = s.id
         WHERE o.booking_date = %s", 
        $date
    ));
    
    $slots = [];
    $start_time = strtotime("10:00");
    $end_time = strtotime("22:00");
    $service_duration = $service->duration * 60;
    $service_break = $service->break * 60;
    
    while($start_time <= $end_time - $service_duration) {
        $slot_end = $start_time + $service_duration;
        $total_occupied = $slot_end + $service_break;
        $available = true;
        
        // Проверяем пересечение с учетом перерывов
        foreach($bookings as $booking) {
            $busy_start = strtotime($booking->start_time);
            $busy_end = strtotime($booking->end_time) + ($booking->break * 60);
            
            $overlap = ($start_time < $busy_end) && ($total_occupied > $busy_start);
            
            if($overlap) {
                $available = false;
                break;
            }
        }
        
        if($available && $slot_end <= $end_time) {
            $slots[] = [
                'start' => date('H:i', $start_time),
                'end' => date('H:i', $slot_end),
                'price' => $price
            ];
        }
        
        $start_time += 1800; // Шаг 30 минут
    }
    
    wp_send_json_success([
        'slots' => $slots,
        'price' => $price,
        'is_weekend' => $is_weekend
    ]);
}

function submit_booking() {
    error_log('Received service_id: ' . print_r($_POST['service_id'], true));
    
    global $wpdb;
    
    // Измененный список обязательных полей
    $required = [
        'service_id', 
        'booking_date', // Изменено с date
        'start', 
        'end', 
        'name', 
        'phone', 
        'email'
    ];
    foreach($required as $field) {
        if(empty($_POST[$field])) {
            wp_send_json_error("Не заполнено поле: " . $field);
        }
    }

    // Получаем данные услуги
    $service = $wpdb->get_row($wpdb->prepare(
        "SELECT name, duration, break, weekday_price, weekend_price 
         FROM {$wpdb->prefix}booking_services 
         WHERE id = %d", 
        intval($_POST['service_id'])
    ));
    
    if(!$service) {
        wp_send_json_error('Услуга не найдена');
    }

    $data = [
        'service_id' => intval($_POST['service_id']),
        'booking_date' => sanitize_text_field($_POST['booking_date']),
        'start_time' => sanitize_text_field($_POST['start']),
        'end_time' => sanitize_text_field($_POST['end']),
        'client_name' => sanitize_text_field($_POST['name']),
        'client_phone' => preg_replace('/[^0-9]/', '', $_POST['phone']),
        'client_email' => sanitize_email($_POST['email']),
        'created_at' => current_time('mysql')
    ];

    // Проверка на существующие бронирования
    $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT o.id 
             FROM {$wpdb->prefix}booking_orders o
             LEFT JOIN {$wpdb->prefix}booking_services s 
                 ON o.service_id = s.id
             WHERE o.booking_date = %s 
             AND (
                 (ADDTIME(o.end_time, SEC_TO_TIME(s.break * 60)) > %s 
                 AND %s > ADDTIME(o.start_time, SEC_TO_TIME(-s.break * 60))
             )",
            $_POST['booking_date'],
            $_POST['start_time'],
            $_POST['end_time']
        ));
    
    if($exists) {
        wp_send_json_error('Это время уже занято');
    }

    $result = $wpdb->insert("{$wpdb->prefix}booking_orders", $data);
    if($result === false) {
        error_log('Ошибка БД: ' . $wpdb->last_error);
        wp_send_json_error('Ошибка сохранения записи');
    }

    // Отправка уведомления в Telegram (единственный вызов)
    $telegram_message = "✅ Новая запись!\n\n"
        . "📌 Услуга: {$service->name}\n"
        . "⏱ Длительность: {$service->duration} мин.\n"
        . "⏳ Перерыв: {$service->break} мин.\n"
        . "👤 Имя: {$_POST['name']}\n"
        . "📞 Телефон: " . preg_replace('/[^0-9]/', '', $_POST['phone']) . "\n"
        . "📧 Email: {$_POST['email']}\n"
        . "📅 Дата: {$_POST['booking_date']}\n"
        . "⏰ Время: {$_POST['start']}-{$_POST['end']}\n"
        . "💵 Стоимость: {$_POST['price']} руб.";

    send_telegram_message(ADMIN_CHAT_ID, $telegram_message);
    
    wp_send_json_success(['booking_id' => $wpdb->insert_id]);

}

// =============================================
// ФОРМА БРОНИРОВАНИЯ (frontend)
// =============================================


add_shortcode('booking_form', function() {
    global $wpdb;
    $services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}booking_services");
    ob_start(); ?>
    
    <style>
    .booking-form {
        max-width: 800px;
        margin: 2rem auto;
        padding: 2rem;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .form-group {
        margin-bottom: 1.5rem;
    }
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #333;
    }
    input, select {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 1rem;
    }
    #time-slots {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
        gap: 0.5rem;
        margin: 1.5rem 0;
    }
    .time-slot {
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
    }
    .time-slot.selected {
        background: #2196F3;
        color: white;
        border-color: #2196F3;
    }
    #submit-booking {
        width: 100%;
        padding: 1rem;
        background: #4CAF50;
        color: white;
        border: none;
        border-radius: 4px;
        font-size: 1.1rem;
        cursor: pointer;
        margin-top: 1rem;
    }
    .service-info {
    margin: 15px 0;
    padding: 12px;
    background: #f8f9fa;
    border-radius: 6px;
    font-size: 16px;
    font-weight: 500;
}
.service-info div {
    margin: 5px 0;
}
#price-value {
    color: #2196F3;
    font-weight: 600;
}
    </style>

    <div class="booking-form">
        <div class="form-group">
            <label for="booking-date">Дата:</label>
            <input type="date" id="booking-date" min="<?= date('Y-m-d') ?>" required>
        </div>
        
        <div class="form-group">
            <label for="booking-service">Услуга:</label>
            <select id="booking-service" required>
                <?php foreach($services as $service): ?>
                <option value="<?= $service->id ?>" 
                    data-duration="<?= $service->duration ?>"
                    data-break="<?= $service->break ?>"
                    data-weekday-price="<?= $service->weekday_price ?>" 
                    data-weekend-price="<?= $service->weekend_price ?>">
                    <?= $service->name ?> 
                    (Длительность: <?= $service->duration ?> мин,
                     Перерыв: <?= $service->break ?> мин)
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="service-info">
            <div>Стоимость: <span id="price-value">0</span> руб.</div>
        </div>

        <div class="form-group">
            <label>Доступное время:</label>
            <div id="time-slots"></div>
        </div>

        <div class="form-group">
            <input type="text" id="client-name" placeholder="Ваше имя" required>
        </div>
        
        <div class="form-group">
            <input type="tel" id="client-phone" placeholder="Телефон" required>
        </div>
        
        <div class="form-group">
            <input type="email" id="client-email" required placeholder="Email">
        </div>

        <button id="submit-booking">Забронировать</button>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const dateInput = document.getElementById('booking-date');
        const serviceSelect = document.getElementById('booking-service');
        const timeSlots = document.getElementById('time-slots');
        
        function updatePriceDisplay() {
            if(!dateInput.value) return;
            
            const date = new Date(dateInput.value);
            const isWeekend = [0, 6].includes(date.getDay()); // 0 - воскресенье, 6 - суббота
            
            const serviceOption = serviceSelect.options[serviceSelect.selectedIndex];
            const price = isWeekend 
                ? serviceOption.dataset.weekendPrice 
                : serviceOption.dataset.weekdayPrice;
            
            document.getElementById('price-value').textContent = price;
        }

        function loadSlots() {
             const service = {
                duration: parseInt(document.querySelector('#booking-service').dataset.duration),
                break: parseInt(document.querySelector('#booking-service').dataset.break)
            };
            if(!dateInput.value) return;
            
            fetch('/wp-admin/admin-ajax.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: new URLSearchParams({
                    action: 'get_time_slots',
                    service_id: serviceSelect.value,
                    date: dateInput.value
                })
            })
            .then(r => r.json())
            .then(data => {
                if(data.success) {
                    timeSlots.innerHTML = data.data.slots.map(slot => `
                        <div class="time-slot" 
                             data-start="${slot.start}" 
                             data-end="${slot.end}"
                             data-price="${slot.price}">
                            ${slot.start}
                        </div>
                    `).join('');
                }
            });
        }

        dateInput.addEventListener('change', () => {
            updatePriceDisplay();
            loadSlots();
        });
        
        serviceSelect.addEventListener('change', () => {
            if(dateInput.value) {
                updatePriceDisplay();
                loadSlots();
            }
        });

        timeSlots.addEventListener('click', (e) => {
            if(e.target.classList.contains('time-slot')) {
                document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
                e.target.classList.add('selected');
            }
        });

        document.getElementById('submit-booking').addEventListener('click', () => {
            const selected = document.querySelector('.time-slot.selected');
            if(!selected) return alert('Выберите время!');
            
        const price = document.getElementById('price-value').textContent;

    
        const formData = {
            action: 'submit_booking',
            service_id: serviceSelect.value,
            booking_date: dateInput.value, // Изменено с date на booking_date
            start: selected.dataset.start,
            end: selected.dataset.end,
            name: document.getElementById('client-name').value.trim(),
            phone: document.getElementById('client-phone').value.replace(/\D/g, ''),
            email: document.getElementById('client-email').value.trim(),
            price: price
        };

    fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: new URLSearchParams(formData)
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            alert('Бронирование успешно! ID: ' + data.data.booking_id);
            location.reload();
        } else {
            alert('Ошибка: ' + data.data);
        }
            });
        });

        // Маска для телефона
        document.getElementById('client-phone').addEventListener('input', function(e) {
            let x = e.target.value.replace(/\D/g, '').match(/(\d{0,1})(\d{0,3})(\d{0,3})(\d{0,2})(\d{0,2})/);
            e.target.value = !x[2] ? x[1] : '+7 (' + x[2] + ') ' + x[3] + (x[4] ? '-' + x[4] : '') + (x[5] ? '-' + x[5] : '');
        });
            updatePriceDisplay();

    });
    </script>
    
    <?php
    return ob_get_clean();
});


///////////////////////////////////////////////////////////////



// Исправленная функция отправки сообщения Телеграм
function send_telegram_message($chat_id, $text) {
    $url = "https://api.telegram.org/bot".TELEGRAM_BOT_TOKEN."/sendMessage";
    wp_remote_post($url, [
        'body' => [
            'chat_id' => $chat_id,
            'text' => $text
        ]
    ]);
}


