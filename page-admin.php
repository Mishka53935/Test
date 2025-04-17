<?php
/* 
Template Name: Admin Bookings Page 
Template Post Type: page
*/

// Получаем ID текущей страницы
$admin_page_id = get_the_ID();

wp_nonce_field('booking_actions', 'booking_nonce', false);

// Убрана проверка авторизации через WP Users
function custom_password_form() {
    global $post;
    return '
    <div style="max-width:400px; margin:100px auto; padding:20px; text-align:center;">
        <h3>Доступ только для администраторов</h3>
        <form action="' . esc_url(site_url('wp-login.php?action=postpass', 'login_post')) . '" method="post">
            <input type="password" name="post_password" placeholder="Пароль" style="padding:8px; margin:10px; width:200px;">
            <button type="submit" style="padding:8px 20px; background:#007cba; color:white; border:none; border-radius:4px;">Войти</button>
        </form>
    </div>';
}
add_filter('the_password_form', 'custom_password_form');

if (post_password_required()) {
    echo get_the_password_form();
    return;
}

remove_all_actions('wp_head');
remove_all_actions('wp_footer');

global $wpdb;

// Получаем услуги для селектора
$services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}booking_services WHERE active = 1");

// Обработка действий (убраны проверки прав)
if (isset($_POST['action'])) {
    check_admin_referer('booking_actions');
    
    if ($_POST['action'] == 'delete_booking') {
        $wpdb->delete("{$wpdb->prefix}booking_orders", ['id' => intval($_POST['id'])]);
    }
    elseif ($_POST['action'] == 'update_booking') {
        $data = [
            'service_id' => intval($_POST['service_id']),
            'booking_date' => sanitize_text_field($_POST['booking_date']),
            'start_time' => sanitize_text_field($_POST['start_time']),
            'end_time' => sanitize_text_field($_POST['end_time']),
            'client_name' => sanitize_text_field($_POST['client_name']),
            'client_phone' => preg_replace('/[^0-9]/', '', $_POST['client_phone']),
            'client_email' => sanitize_email($_POST['client_email'])
        ];
        $wpdb->update("{$wpdb->prefix}booking_orders", $data, ['id' => intval($_POST['id'])]);
    }
    wp_redirect(get_permalink());
    exit;
}

// Получаем текущий месяц
$current_month = isset($_GET['month']) 
    ? DateTime::createFromFormat('Y-m', sanitize_text_field($_GET['month']))
    : new DateTime();
$current_month->modify('first day of this month');

$start_date = $current_month->format('Y-m-01');
$end_date = $current_month->format('Y-m-t');

$bookings_count = $wpdb->get_results($wpdb->prepare("
    SELECT 
        DATE_FORMAT(booking_date, '%%Y-%%m-%%d') as day,
        COUNT(*) as count
    FROM {$wpdb->prefix}booking_orders
    WHERE booking_date BETWEEN %s AND %s
    GROUP BY day
", $start_date, $end_date), OBJECT_K);

$bookings = $wpdb->get_results("
    SELECT 
        o.*,
        s.name as service_name 
    FROM {$wpdb->prefix}booking_orders o
    LEFT JOIN {$wpdb->prefix}booking_services s 
    ON o.service_id = s.id
    ORDER BY o.booking_date ASC, o.start_time ASC
");

$grouped = [];
foreach ($bookings as $booking) {
    $date = $booking->booking_date;
    $grouped[$date][] = $booking;
}

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель бронирований</title>
    <?php wp_head(); ?>
    <style>
    .booking-admin-container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 20px;
    }

    .calendar-wrapper {
        margin: 2rem 0;
        background: white;
        padding: 1.5rem;
        border-radius: 12px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }

    .calendar-nav {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .calendar-nav button {
        background: #3498db;
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: 6px;
        cursor: pointer;
        transition: background 0.3s;
    }

    .calendar-nav button:hover {
        background: #2980b9;
    }

    .calendar-table {
        width: 100%;
        border-collapse: collapse;
    }

    .calendar-table th,
    .calendar-table td {
        padding: 1rem;
        text-align: center;
        border: 1px solid #eee;
        height: 60px;
    }

    .calendar-table th {
        background: #f8f9fa;
        font-weight: 500;
    }

    .calendar-day {
        cursor: pointer;
        transition: background 0.2s;
        position: relative;
    }

    .calendar-day:hover {
        background: #f8f9fa;
    }

    .calendar-day.active {
        background: #3498db;
        color: white;
    }

    .event-count {
        font-size: 0.8em;
        color: #666;
        position: absolute;
        bottom: 2px;
        right: 2px;
    }

    .calendar-day.active .event-count {
        color: white;
    }

    .other-month {
        background: #fafafa;
        color: #999;
    }

    .today {
        background: #e3f2fd;
    }

    #noBookingsMessage {
        display: none;
        padding: 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        color: #666;
        text-align: center;
        margin: 2rem 0;
    }

    .bookings-list {
        display: flex;
        flex-direction: column;
        gap: 2rem;
    }

    .booking-day-card {
        background: #fff;
        border-radius: 12px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        padding: 1.5rem;
    }

    .booking-date-header {
        font-size: 1.5rem;
        color: #2c3e50;
        margin-bottom: 1.5rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid #3498db;
    }

    .booking-item {
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        background: #f8f9fa;
        border-radius: 8px;
        transition: transform 0.2s;
        border: 1px solid #eee;
    }

    .booking-item:hover {
        transform: translateY(-2px);
        box-shadow: 0 3px 8px rgba(0, 0, 0, 0.08);
    }

    .client-info div {
        margin: 0.8rem 0;
        font-size: 1rem;
    }

    .info-label {
        color: #3498db;
        font-weight: 500;
        min-width: 80px;
        display: inline-block;
    }

    @media (max-width: 768px) {
        .calendar-table td, .calendar-table th {
            padding: 0.5rem;
            font-size: 0.9rem;
        }
        
        .calendar-nav button {
            padding: 0.6rem 1rem;
        }
        
        .booking-admin-container {
            padding: 0 15px;
        }
    }
        /* Модальные окна */
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
    }
    
    .modal-content {
        background: white;
        max-width: 600px;
        margin: 2rem auto;
        padding: 2rem;
        border-radius: 8px;
    }
    
    .action-buttons {
        margin-top: 1rem;
        display: flex;
        gap: 1rem;
    }
    
    .btn {
        padding: 0.5rem 1rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    
    .btn-edit {
        background: #3498db;
        color: white;
    }
    
    .btn-delete {
        background: #e74c3c;
        color: white;
    }
    
    .btn-new {
        background: #2ecc71;
        color: white;
        margin-bottom: 1rem;
    }
    .modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 1000;
        overflow-y: auto;
    }

    .modal-content {
        background: white;
        max-width: 600px;
        margin: 2rem auto;
        padding: 2rem;
        border-radius: 8px;
    }

    .form-row {
        margin-bottom: 1rem;
    }

    .form-row label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }

    .form-row input,
    .form-row select {
        width: 100%;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .action-buttons {
        margin-top: 1.5rem;
        display: flex;
        gap: 1rem;
    }

    .btn {
        padding: 0.6rem 1.2rem;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: opacity 0.2s;
    }

    .btn:hover {
        opacity: 0.9;
    }

    .btn-edit {
        background: #3498db;
        color: white;
    }

    .btn-delete {
        background: #e74c3c;
        color: white;
    }

    .btn-new {
        background: #2ecc71;
        color: white;
        margin-bottom: 1rem;
    }

    .btn-cancel {
        background: #95a5a6;
        color: white;
    }

    .booking-item {
        position: relative;
        padding: 1.5rem;
        margin-bottom: 1rem;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .booking-actions {
        position: absolute;
        top: 1rem;
        right: 1rem;
        display: flex;
        gap: 0.5rem;
    }
    .time-slots-container {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
    gap: 0.5rem;
    margin: 1rem 0;
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
    
    .time-slot.disabled {
        background: #eee;
        cursor: not-allowed;
        opacity: 0.6;
}
    </style>
</head>
<body style="margin:0; background:#f0f0f1;">

<div class="booking-admin-container">
    <button class="btn btn-new" onclick="openCreateModal()">+ Новая запись</button>
    
    <div class="calendar-wrapper">
        <div class="calendar-nav">
            <button onclick="changeMonth(-1)">&larr; Предыдущий</button>
            <h3><?= $current_month->format('F Y') ?></h3>
            <button onclick="changeMonth(1)">Следующий &rarr;</button>
        </div>
        
        <table class="calendar-table">
            <tr>
                <th>Пн</th><th>Вт</th><th>Ср</th><th>Чт</th><th>Пт</th><th>Сб</th><th>Вс</th>
            </tr>
            <?php
            $day = 1;
            $first_day = (int)$current_month->format('N');
            $total_days = (int)$current_month->format('t');
            $currentDate = new DateTime();
            
            for ($i = 0; $i < 6; $i++) {
                echo '<tr>';
                for ($j = 1; $j <= 7; $j++) {
                    $cell_class = [];
                    $date_str = null;
                    
                    if (($i === 0 && $j < $first_day) || $day > $total_days) {
                        $diff = $day > $total_days ? $day - $total_days : -($first_day - $j);
                        $temp_date = clone $current_month;
                        $temp_date->modify($diff . ' days');
                        $date_str = $temp_date->format('Y-m-d');
                        $cell_class[] = 'other-month';
                    } else {
                        $date_str = $current_month->format('Y-m-') . str_pad($day, 2, '0', STR_PAD_LEFT);
                        if ($date_str === $currentDate->format('Y-m-d')) {
                            $cell_class[] = 'today';
                        }
                        $day++;
                    }

                    $count = $bookings_count[$date_str]->count ?? 0;
                    $display_day = $date_str ? explode('-', $date_str)[2] : '';
                    
                    echo '<td class="calendar-day ' . implode(' ', $cell_class) . '" 
                         data-date="' . $date_str . '"
                         onclick="selectDate(this)">';
                    
                    echo '<div>' . $display_day . '</div>';
                    if ($count > 0) {
                        echo '<span class="event-count">(' . $count . ')</span>';
                    }
                    
                    echo '</td>';
                }
                echo '</tr>';
                if ($day > $total_days) break;
            }
            ?>
        </table>
    </div>

    <!-- Модальное окно редактирования -->
<div id="editModal" class="modal">
    <div class="modal-content">
        <h3>Редактирование записи</h3>
        <form method="post" id="editForm">
            <?php wp_nonce_field('booking_actions'); ?>
            <input type="hidden" name="action" value="update_booking">
            <input type="hidden" name="id" id="editId">
            
            <div class="form-row">
                <label>Дата:</label>
                <input type="date" id="editBookingDate" required>
            </div>

            <div class="form-row">
                <label>Услуга:</label>
                <select id="editService" required>
                    <?php foreach($services as $service): ?>
                        <option value="<?= $service->id ?>" 
                            data-duration="<?= $service->duration ?>"
                            data-break="<?= $service->break ?>"
                            data-weekday-price="<?= $service->weekday_price ?>" 
                            data-weekend-price="<?= $service->weekend_price ?>">
                            <?= $service->name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <label>Доступное время:</label>
                <div id="editTimeSlots" class="time-slots-container"></div>
            </div>

            <div class="form-row">
                <label>Имя клиента:</label>
                <input type="text" name="client_name" id="editName" required>
            </div>

            <div class="form-row">
                <label>Телефон:</label>
                <input type="tel" name="client_phone" id="editPhone" required>
            </div>

            <div class="form-row">
                <label>Email:</label>
                <input type="email" name="client_email" id="editEmail" required>
            </div>

            <input type="hidden" name="booking_date" id="editSelectedDate">
            <input type="hidden" name="start_time" id="editStartTime">
            <input type="hidden" name="end_time" id="editEndTime">
            <input type="hidden" name="service_id" id="editSelectedService">

            <div class="action-buttons">
                <button type="submit" class="btn btn-edit">Сохранить</button>
                <button type="button" class="btn btn-cancel" onclick="closeModals()">Отмена</button>
            </div>
        </form>
    </div>
</div>

   <!-- Модальное окно создания -->
<div id="createModal" class="modal">
    <div class="modal-content">
        <h3>Новая запись</h3>
        <form method="post" id="createForm">
            <?php wp_nonce_field('booking_actions'); ?>
            <input type="hidden" name="action" value="create_booking">
            
            <div class="form-row">
                <label>Дата:</label>
                <input type="date" id="createBookingDate" required>
            </div>

            <div class="form-row">
                <label>Услуга:</label>
                <select id="createService" required>
                    <?php foreach($services as $service): ?>
                        <option value="<?= $service->id ?>" 
                            data-duration="<?= $service->duration ?>"
                            data-break="<?= $service->break ?>"
                            data-weekday-price="<?= $service->weekday_price ?>" 
                            data-weekend-price="<?= $service->weekend_price ?>">
                            <?= $service->name ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <label>Доступное время:</label>
                <div id="createTimeSlots" class="time-slots-container"></div>
            </div>

            <div class="form-row">
                <label>Имя клиента:</label>
                <input type="text" name="client_name" id="createName" required>
            </div>

            <div class="form-row">
                <label>Телефон:</label>
                <input type="tel" name="client_phone" id="createPhone" required>
            </div>

            <div class="form-row">
                <label>Email:</label>
                <input type="email" name="client_email" id="createEmail" required>
            </div>

            <input type="hidden" name="booking_date" id="selectedBookingDate">
            <input type="hidden" name="start_time" id="selectedStartTime">
            <input type="hidden" name="end_time" id="selectedEndTime">
            <input type="hidden" name="service_id" id="selectedServiceId">

            <div class="action-buttons">
                <button type="submit" class="btn btn-new">Создать</button>
                <button type="button" class="btn btn-cancel" onclick="closeModals()">Отмена</button>
            </div>
        </form>
    </div>
</div>
</div>


    
    <div class="bookings-list">
        <div id="noBookingsMessage" style="display: none;">
            Нет записей на выбранную дату
        </div>
    <?php if (!empty($grouped)): ?>
        <?php foreach ($grouped as $date => $items): ?>
            <div class="booking-day-card" data-date="<?= esc_attr($date) ?>">
                <div class="booking-date-header"><?= date('d.m.Y', strtotime($date)) ?></div>
                <?php foreach ($items as $booking): ?>
                    <div class="booking-item" data-id="<?= $booking->id ?>">
                        <div class="booking-actions">
                            <button class="btn btn-edit" onclick="openEditModal(<?= $booking->id ?>)">✏️</button>
                            <button class="btn btn-delete" onclick="deleteBooking(<?= $booking->id ?>)">🗑️</button>
                        </div>
                        <div class="client-info">
                            <div><span class="info-label">Время:</span><?= $booking->start_time ?> - <?= $booking->end_time ?></div>
                            <div><span class="info-label">Имя:</span><?= esc_html($booking->client_name) ?></div>
                            <div><span class="info-label">Телефон:</span><?= esc_html($booking->client_phone) ?></div>
                            <div><span class="info-label">Email:</span><?= esc_html($booking->client_email) ?></div>
                            <div><span class="info-label">Услуга:</span><?= esc_html($booking->service_name) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div id="noBookingsMessage" style="display: block;">
            Нет активных записей
        </div>
    <?php endif; ?>
</div>
</div>

<script>

var adminPageID = <?php echo $admin_page_id; ?>;
var bookingNonce = '<?php echo wp_create_nonce('booking_actions'); ?>';

document.addEventListener('DOMContentLoaded', () => {
    let selectedDate = null;
    
    // Обработчик кликов по календарю
    document.querySelector('.calendar-table').addEventListener('click', (e) => {
        const cell = e.target.closest('.calendar-day');
        if(cell) selectDate(cell);
    });

    // Навигация по месяцам
        window.changeMonth = function(offset) {
        const url = new URL(window.location.href);
        const currentMonth = url.searchParams.get('month') || '<?= $current_month->format('Y-m') ?>';
        const newDate = new Date(currentMonth + '-01');
        newDate.setMonth(newDate.getMonth() + offset);
        url.searchParams.set('month', newDate.toISOString().slice(0,7));
        window.location.href = url.href;
    };
    
    window.selectDate = function(cell) {
        const date = cell.dataset.date;
        if(!date) return;
    
        document.querySelectorAll('.calendar-day').forEach(c => {
            c.classList.remove('active');
        });
        
        cell.classList.add('active');
        selectedDate = date;
        
        // Показываем/скрываем записи
    let hasBookings = false;
    document.querySelectorAll('.booking-day-card').forEach(card => {
        if(card.dataset.date === date) {
            card.style.display = 'block';
            hasBookings = true;
        } else {
            card.style.display = 'none';
        }
    });
    
    const noBookingsMsg = document.getElementById('noBookingsMessage');
    if(noBookingsMsg) {
        noBookingsMsg.style.display = hasBookings ? 'none' : 'block';
    }
        
        document.getElementById('noBookingsMessage').style.display = 
            document.querySelector('.booking-day-card[data-date="' + date + '"]') ? 'none' : 'block';
    };

    // Инициализация текущей даты
    const todayCell = document.querySelector('.calendar-day.today');
    if(todayCell) selectDate(todayCell);
});

async function openEditModal(id) {
    try {
        const response = await fetch(
            `/wp-json/bookings/v1/booking/${id}?page_id=${adminPageID}`
        );
        if(!response.ok) {
            if(response.status === 401) {
                alert('Требуется авторизация!');
                window.location.reload();
                return;
            }
            throw new Error(`Ошибка HTTP: ${response.status}`);
        }
        const booking = await response.json();
        
        // Дебаггинг
        console.log('Received booking data:', booking);
        
        // Заполнение полей
        document.getElementById('editId').value = booking.id;
        document.getElementById('editBookingDate').value = booking.booking_date;
        document.getElementById('editService').value = booking.service_id;
        document.getElementById('editName').value = booking.client_name;
        document.getElementById('editPhone').value = booking.client_phone;
        document.getElementById('editEmail').value = booking.client_email;
        
        // Загрузка временных слотов
        await loadTimeSlots(
            booking.booking_date, 
            booking.service_id, 
            document.getElementById('editTimeSlots'),
            booking.start_time,
            booking.end_time
        );
        
        document.getElementById('editModal').style.display = 'block';
    } catch (error) {
        console.error('Error:', error);
        alert(error.message);
    }
}

// Общая функция загрузки слотов с учетом текущего времени
async function loadTimeSlots(date, serviceId, container, currentStart = null, currentEnd = null) {
    const service = document.querySelector(`#editService option[value="${serviceId}"]`);
    
    const response = await fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action: 'get_time_slots',
            service_id: serviceId,
            date: date
        })
    });
    
    const data = await response.json();
    
    if (data.success) {
        container.innerHTML = data.data.slots.map(slot => {
            // Проверяем, совпадает ли слот с текущим временем брони
            const isCurrent = currentStart === slot.start && currentEnd === slot.end;
            return `
                <div class="time-slot ${isCurrent ? 'selected' : ''}" 
                     data-start="${slot.start}" 
                     data-end="${slot.end}"
                     ${isCurrent ? 'style="background: #ff9800"' : ''}>
                    ${slot.start}
                </div>
            `;
        }).join('');
    }
}

function deleteBooking(id) {
    if (confirm('Вы уверены?')) {
        fetch('/wp-admin/admin-ajax.php', {
            method: 'POST',
            body: new URLSearchParams({
                action: 'delete_booking',
                id: id,
                page_id: adminPageID,
                security: bookingNonce
            })
        }).then(() => {
            location.reload();
        });
    }
}

function closeModals() {
    document.querySelectorAll('.modal').forEach(m => m.style.display = 'none');
}

// Обновленный обработчик формы редактирования
document.getElementById('editForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('security', '<?= wp_create_nonce("booking_actions") ?>');

    if (!document.querySelector('#editTimeSlots .time-slot.selected')) {
        alert('Выберите время!');
        return;
    }

    fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Изменения сохранены!');
            location.reload();
        } else {
            alert('Ошибка: ' + (data.data || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка сети');
    });
});

function openCreateModal() {
    // Сброс предыдущих значений
    document.getElementById('createBookingDate').value = '';
    document.getElementById('createService').value = '';
    document.getElementById('createName').value = '';
    document.getElementById('createPhone').value = '';
    document.getElementById('createEmail').value = '';
    document.getElementById('createTimeSlots').innerHTML = '';
    
    document.getElementById('createModal').style.display = 'block';
}

// Общая функция для загрузки слотов
function loadTimeSlots(date, serviceId, targetElement) {
    const service = document.querySelector(`#createService option[value="${serviceId}"]`);
    
    fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: new URLSearchParams({
            action: 'get_time_slots',
            service_id: serviceId,
            date: date
        })
    })
    .then(r => r.json())
    .then(data => {
        if(data.success) {
            targetElement.innerHTML = data.data.slots.map(slot => `
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

// Обработчики событий для модального окна редактирования
document.getElementById('editBookingDate').addEventListener('change', function() {
    const serviceId = document.getElementById('editService').value;
    loadTimeSlots(
        this.value, 
        serviceId,
        document.getElementById('editTimeSlots'),
        document.getElementById('editStartTime').value,
        document.getElementById('editEndTime').value
    );
});

document.getElementById('editService').addEventListener('change', function() {
    const date = document.getElementById('editBookingDate').value;
    if(date) {
        loadTimeSlots(
            date, 
            this.value,
            document.getElementById('editTimeSlots'),
            document.getElementById('editStartTime').value,
            document.getElementById('editEndTime').value
        );
    }
});
document.getElementById('editTimeSlots').addEventListener('click', (e) => {
    if(e.target.classList.contains('time-slot')) {
        document.querySelectorAll('#editTimeSlots .time-slot').forEach(s => {
            s.classList.remove('selected');
            s.style.backgroundColor = '';
        });
        
        e.target.classList.add('selected');
        e.target.style.backgroundColor = '#2196F3';
        
        // Обновляем скрытые поля
        document.getElementById('editSelectedDate').value = document.getElementById('editBookingDate').value;
        document.getElementById('editStartTime').value = e.target.dataset.start;
        document.getElementById('editEndTime').value = e.target.dataset.end;
        document.getElementById('editSelectedService').value = document.getElementById('editService').value;
    }
});

// Обработчики событий для модального окна создания
document.getElementById('createBookingDate').addEventListener('change', function() {
    const serviceId = document.getElementById('createService').value;
    loadTimeSlots(this.value, serviceId, document.getElementById('createTimeSlots'));
});

document.getElementById('createService').addEventListener('change', function() {
    const date = document.getElementById('createBookingDate').value;
    if(date) {
        loadTimeSlots(date, this.value, document.getElementById('createTimeSlots'));
    }
});

document.getElementById('createTimeSlots').addEventListener('click', (e) => {
    if(e.target.classList.contains('time-slot')) {
        document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
        e.target.classList.add('selected');
        
        // Заполняем скрытые поля
        document.getElementById('selectedBookingDate').value = document.getElementById('createBookingDate').value;
        document.getElementById('selectedStartTime').value = e.target.dataset.start;
        document.getElementById('selectedEndTime').value = e.target.dataset.end;
        document.getElementById('selectedServiceId').value = document.getElementById('createService').value;
    }
});

// Обновленный обработчик отправки формы
document.getElementById('createForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append('security', '<?= wp_create_nonce("booking_actions") ?>');

    if (!document.querySelector('#createTimeSlots .time-slot.selected')) {
        alert('Выберите время!');
        return;
    }

    fetch('/wp-admin/admin-ajax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Запись успешно создана! ID: ' + data.data.id);
            location.reload();
        } else {
            alert('Ошибка: ' + (data.data || 'Неизвестная ошибка'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Ошибка сети');
    });
});
</script>


</body>
</html>

