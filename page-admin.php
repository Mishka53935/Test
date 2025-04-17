<?php
/*
Template Name: Page Admin
*/
// Защита: только авторизированный админ может
if ( ! is_user_logged_in() || ! current_user_can('manage_options') ) {
    wp_die('Доступ запрещен');
}

global $wpdb;
// Получаем все услуги (для JS‑навигации, если нужно)
$services = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}booking_services WHERE active = 1", ARRAY_A);

// Получаем все заявки и группируем их по дате
$all = $wpdb->get_results("
    SELECT o.*, s.name AS service_name, s.duration
    FROM {$wpdb->prefix}booking_orders o
    LEFT JOIN {$wpdb->prefix}booking_services s
      ON o.service_id = s.id
    ORDER BY o.booking_date, o.start_time
", ARRAY_A);

$grouped = [];
foreach ( $all as $b ) {
    $grouped[ $b['booking_date'] ][] = $b;
}


?>

<style>
/* ---- Базовые стили ---- */
#admin-bookings { max-width:800px; margin:0 auto; font-family:Arial,sans-serif; }
#calendar { display:flex; flex-wrap:wrap; width:350px; margin-bottom:20px; }
#calendar div { width:14.28%; padding:10px; box-sizing:border-box; text-align:center; cursor:pointer; }
#calendar .today { font-weight:bold; }
#calendar .selected { background:#eef; }
#bookings-list .booking-item { padding:8px; border:1px solid #ccc; margin-bottom:5px; cursor:pointer; }
#edit-panel { display:none; margin-top:10px; }
#edit-panel select, #edit-panel button { margin-right:5px; padding:5px 10px; }
#error-msg { color:red; margin-top:10px; }
</style>

<div id="admin-bookings">
  <h1>Админ‑панель бронирований</h1>

  <!-- Простой календарь на текущий месяц -->
  <div id="calendar"></div>

  <!-- Здесь будет список заявок -->
  <div id="bookings-list">Выберите дату в календаре</div>

  <!-- Панель переноса -->
  <div id="edit-panel">
    <label>Новое время:
      <select id="time-slots"></select>
    </label>
    <button id="transfer-btn">Перенести</button>
  </div>

  <div id="error-msg"></div>
</div>

<!-- Подключаем jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
jQuery(function($){
  const ajaxUrl = '<?php echo admin_url("admin-ajax.php"); ?>';
  const bookingNonce  = '<?php echo esc_js( wp_create_nonce("booking_actions") ); ?>';
  const bookingsByDate= <?php echo wp_json_encode($grouped); ?>;
  let selectedDate    = null;
  let editingBooking  = null;

  // 1) Рендерим календарь текущего месяца
  (function renderCalendar(){
    const today = new Date();
    const year  = today.getFullYear();
    const month = today.getMonth();  // 0‑Jan
    const first = new Date(year, month, 1).getDay(); // 0‑Sun
    const daysInMonth = new Date(year, month+1, 0).getDate();
    // выравниваем неделя так, чтобы 1й день был под Пн
    let offset = (first + 6) % 7;
    for(let i=0; i<offset; i++){
      $('#calendar').append('<div></div>');
    }
    for(let d=1; d<=daysInMonth; d++){
      const ds = year+'-'+String(month+1).padStart(2,'0')+'-'+String(d).padStart(2,'0');
      const cls = ds === today.toISOString().slice(0,10) ? 'today' : '';
      $('#calendar').append('<div class="'+cls+'" data-date="'+ds+'">'+d+'</div>');
    }
  })();

  // 2) По клику на дату показываем заявки
  $('#calendar').on('click','div[data-date]', function(){
    $('#calendar div').removeClass('selected');
    $(this).addClass('selected');
    selectedDate = $(this).data('date');
    const items = bookingsByDate[selectedDate] || [];
    renderList(items);
    $('#edit-panel').hide();
    $('#error-msg').empty();
  });

  function renderList(items){
    const $L = $('#bookings-list').empty();
    if(!items.length){
      return $L.append('<p>Нет записей на выбранную дату</p>');
    }
    items.forEach(b => {
      $L.append(
        `<div class="booking-item"
              data-id="${b.id}"
              data-service-id="${b.service_id}"
              data-duration="${b.duration}">
          ${b.start_time}–${b.end_time}: ${b.client_name} (${b.service_name})
        </div>`
      );
    });
  }

  // 3) По клику на заявку запрашиваем свободные слоты
  $('#bookings-list').on('click','.booking-item', function(){
    editingBooking = {
      id: $(this).data('id'),
      service_id: $(this).data('service-id'),
      duration: $(this).data('duration'),
      client_name: $(this).text() // если нужно
      // ...можно ещё хранить phone/email, если потребуется
    };
    $('#error-msg').empty();
    $.post(ajaxUrl, {
      action:       'get_time_slots',
      security:     bookingNonce,
      service_id:   editingBooking.service_id,
      date:         selectedDate
    }, function(res){
      if(!res.success){
        return $('#error-msg').text(res.data);
      }
      const $sel = $('#time-slots').empty();
      res.data.slots.forEach(s => {
        $('<option>').val(s.start).text(s.start+' – '+s.end).appendTo($sel);
      });
      $('#edit-panel').show();
    }, 'json')
    .fail(xhr => {
      $('#error-msg').text('Ошибка получения слотов: '+xhr.status);
    });
  });

  // 4) Перенос заявки
  $('#transfer-btn').on('click', function(){
    const newStart = $('#time-slots').val();
    // вычислим end по duration
    const [h,m] = newStart.split(':').map(Number);
    let dt = new Date(0,0,0,h,m);
    dt.setMinutes(dt.getMinutes() + parseInt(editingBooking.duration));
    const newEnd = String(dt.getHours()).padStart(2,'0')+':'+String(dt.getMinutes()).padStart(2,'0');

    $.post(ajaxUrl, {
      action:        'update_booking',
      security:      bookingNonce,
      id:            editingBooking.id,
      service_id:    editingBooking.service_id,
      booking_date:  selectedDate,
      start_time:    newStart,
      end_time:      newEnd,
      // подставляем старые данные клиента, чтобы не затирать
      client_name:   editingBooking.client_name,
      client_phone:  editingBooking.client_phone || '',
      client_email:  editingBooking.client_email || ''
    }, function(res){
        console.log(res);
      if(!res.success){
        return $('#error-msg').text(res.data);
      }
      // Перезагрузим страницу, чтобы обновить календарь и список
      location.reload();
    }, 'json')
    .fail(xhr => {
      $('#error-msg').text('Ошибка переноса: '+xhr.status);
    });
  });

});
</script>

<?php

