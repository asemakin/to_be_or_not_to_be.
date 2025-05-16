<?php
/**
 * Функция для получения и отображения курсов валют с сайта ЦБ РФ
 *
 * @param array $config Конфигурационные параметры:
 *   - cache_file (string) - путь к файлу кэша
 *   - cache_time (int) - время жизни кэша в секундах
 *   - currencies (array) - массив валют для отображения (коды и данные)
 * @return string HTML-код с таблицей курсов валют
  */
declare(strict_types=1);
   // Объявление функции с необязательным параметром $config (массив настроек)

   function get_currency_rates_2(array $config = [])
 {

   /*****************************************************************
   * БЛОК 1: УСТАНОВКА ЗНАЧЕНИЙ ПО УМОЛЧАНИЮ
   *****************************************************************/

   // Массив значений по умолчанию
    $defaults = [
      // Файл для кэширования данных (хранится в той же папке, что и скрипт)
      'cache_file' => __DIR__ . '/currency_cache.json',

      // Время жизни кэша в часах
      'cache_time' => 3, // 1 час

      // Список поддерживаемых валют с дополнительной информацией
      'currencies' => [
        'USD' => ['name' => 'Доллар США', 'flag' => '🇺🇸'],
        'EUR' => ['name' => 'Евро', 'flag' => '🇪🇺'],
        'GBP' => ['name' => 'Фунт стерлингов', 'flag' => '🇬🇧'],
        'CNY' => ['name' => 'Китайский юань', 'flag' => '🇨🇳'],
        'JPY' => ['name' => 'Японская иена', 'flag' => '🇯🇵'],
        'CAD' => ['name' => 'Канадский доллар', 'flag' => '🇨🇦'],
        'IDR' => ['name' => 'Индонезийская рупия', 'flag' => '🇮🇩'],
        'INR' => ['name' => 'Индийская рупия', 'flag' => '🇮🇳'],
        'BRL' => ['name' => 'Бразильский реал', 'flag' => '🇧🇷'],
        'KRW' => ['name' => 'Южнокорейская вона', 'flag' => '🇰🇷'],
        'ZAR' => ['name' => 'Южноафриканский рэнд', 'flag' => '🇿🇦'],
        'SGD' => ['name' => 'Сингапурский доллар', 'flag' => '🇸🇬'],
        'BYN' => ['name' => 'Белорусский рубль', 'flag' => '🇧🇾']
      ]
        ];

         // Объединение пользовательских настроек с настройками по умолчанию
         // Пользовательские настройки перезаписывают значения по умолчанию
         $config = array_merge($defaults, $config);

         /*****************************************************************
         * БЛОК 2: ФУНКЦИЯ ДЛЯ ПОЛУЧЕНИЯ КУРСОВ ВАЛЮТ
         *****************************************************************/

         // Объявляем анонимную функцию для парсинга курсов валют

     $parse_currency_rates = function() use ($config) {
         // URL для запроса курсов валют на текущую дату
         $url = 'https://www.cbr.ru/scripts/XML_daily.asp?date_req=' . date('d/m/Y');

         try {
             // Настройки HTTP-запроса
             $options = [
                 'http' => [
                     'method' => 'GET',
                     'timeout' => 15,
                     'header' => "User-Agent: Mozilla/5.0\r\n"
                 ]
             ];
             $context = stream_context_create($options);

             // Получаем XML-данные с сервера ЦБ
             $xml = file_get_contents($url, false, $context);

             if ($xml === false) {
                 throw new Exception("Ошибка запроса: " . (error_get_last()['message'] ?? 'неизвестная ошибка'));
             }

             if (empty($xml)) {
                 throw new Exception("Пустой ответ от сервера");
             }

             if (strpos($xml, '<Valute') === false) {
                 throw new Exception("Некорректный формат XML");
             }

             // Регулярное выражение для парсинга XML
             $pattern = '/<Valute\b[^>]*>.*?
                          <CharCode>(USD|EUR|GBP|CNY|JPY|CAD|IDR|INR|BRL|KRW|ZAR|SGD|BYN)<\/CharCode>.*?
                          <Value>([\d,]+)<\/Value>/xis';
             preg_match_all($pattern, $xml, $matches, PREG_SET_ORDER);

             if (empty($matches)) {
                 throw new Exception("Не найдены данные о валютах в XML");
             }

             // Коэффициенты пересчета для валют (ЦБ РФ публикует курсы не для 1 единицы)
             $denominators = [
                 'IDR' => 1000,  // Индонезийская рупия (за 1000 единиц)
                 'KRW' => 1000,  // Южнокорейская вона (за 1000 единиц)
                 'JPY' => 100,   // Японская иена (за 100 единиц)
                 'INR' => 100,
                 'BYN' => 10, // Белорусский рубль (за 10 единиц)
                 'ZAR' => 10
                 // Для остальных валют используется коэффициент 1
             ];

             $rates = [];
             foreach ($matches as $match) {
                 $code = $match[1];  // Код валюты (например, 'USD', 'EUR')
                 $value = (float)str_replace(',', '.', $match[2]);  // Значение курса

                 // Пересчитываем курс на 1 единицу валюты с учетом коэффициента
                 $rates[$code] = $value / ($denominators[$code] ?? 1);

                 /*
                 Пример:
                 - Для IDR ЦБ дает курс 5.8 за 1000 рупий
                 - После пересчета: 5.8 / 1000 = 0.0058 за 1 рупию
                 - Для USD коэффициент 1, поэтому курс остается без изменений
                 */
             }

             // Проверяем наличие обязательных валют
             $required = ['USD', 'EUR'];
             foreach ($required as $currency) {
                 if (!isset($rates[$currency])) {
                     throw new Exception("Отсутствует курс $currency");
                 }
             }

             return [
                 'rates' => $rates,
                 'updated_at' => time(),
                 'error' => null
             ];

         } catch (Exception $e) {
             return [
                 'rates' => [],
                 'updated_at' => time(),
                 'error' => $e->getMessage()
             ];
         }
     };
         /************************************************************
          * РАБОТА С КЭШЕМ
         ************************************************************/

      // Инициализируем массив для хранения данных
      $data = [];

      // Флаг, указывающий использовать ли кэш
      $useCache = false;

      // Проверяем, существует ли файл кэша и не устарел ли он
      // file_exists - проверяет существование файла
      // filemtime - возвращает время последнего изменения файла
      // $config['cache_time'] - время жизни кэша в часах
       if (file_exists($config['cache_file']) &&
          (time() - filemtime($config['cache_file']) < $config['cache_time']))
      {

        // Читаем содержимое файла кэша и декодируем JSON
        $cached = json_decode(file_get_contents($config['cache_file']), true);

        // Проверяем:
        // 1. Что JSON был успешно декодирован (json_last_error() === JSON_ERROR_NONE)
        // 2. Что в кэше есть данные о курсах валют
        if (json_last_error() === JSON_ERROR_NONE && !empty($cached['rates']))
        {
          $data = $cached;
          $useCache = true; // Устанавливаем флаг использования кэша
        }
      }

        // Если кэш не используется (устарел или невалиден)
        if (!$useCache)
        {
          // Получаем свежие курсы валют
          $data = $parse_currency_rates();

          // Если нет ошибок - сохраняем данные в кэш
          if (empty($data['error']))
          {
            // file_put_contents - записывает данные в файл
            // json_encode - преобразует массив в JSON
            // LOCK_EX - эксклюзивная блокировка файла на время записи
            file_put_contents($config['cache_file'], json_encode($data), LOCK_EX);
          }
        }

      /************************************************************
      * ГЕНЕРАЦИЯ HTML
      ************************************************************/

     // Включаем буферизацию вывода
     // ob_start - начинает буферизацию вывода, весь последующий вывод будет накапливаться в буфере
     // вместо немедленной отправки в браузер
     ob_start();
     ?>

    <!DOCTYPE html>
  <html>
    <head>
        <title>Курсы валют ЦБ РФ</title>
        <meta charset="UTF-8">

        <style>
            /* [1] ОБЩИЙ ФИКСИРОВАННЫЙ КОНТЕЙНЕР ДЛЯ ВСЕГО ВИДЖЕТА */
            /* Создаем обертку для всего виджета курсов валют */
            .currency-widget {
                position: fixed;        /* Фиксируем позицию на экране */
                bottom: 20px;          /* Отступ от нижнего края экрана */
                right: 20px;            /* Отступ от левого края экрана */
                max-width: 325px;      /* Максимальная ширина виджета */
                height: auto;         /* Автоматическая высота */
                max-height: 350px;    /* Максимальная высота */
                overflow-y: auto;     /* Вертикальный скролл при необходимости */
                z-index: 1000;         /* Поднимаем над другими элементами */
                background: #f9f9f9;   /* Цвет фона */
                border: 1px solid #e0e0e0; /* Граница виджета */
                border-radius: 5px;    /* Скругление углов */
                padding: 10px;         /* Внутренние отступы */
                box-shadow: 0 2px 5px rgba(0,0,0,0.1); /* Тень для выделения */
                cursor: grab; /* Курсор при наведении */
                user-select: none; /* Запрещаем выделение текста при перетаскивании */
                transition: box-shadow 0.2s; /* Плавное изменение тени */
            }

            .currency-widget:active {
                cursor: grabbing; /* Курсор при перетаскивании */
            }

            /* [2] СТИЛИ ЗАГОЛОВКА ВИДЖЕТА */
            .header {
                text-align: left;      /* Выравнивание текста по левому краю */
                margin-bottom: 8px;    /* Отступ снизу */
                font-size: 13px;       /* Размер шрифта */
            }

            /* [3] СТИЛИ СПИСКА ВАЛЮТ */
            .currency-list {
                margin: 0;             /* Убираем внешние отступы */
                padding: 0;            /* Убираем внутренние отступы */
            }

            /* [4] СТИЛИ ОТДЕЛЬНОЙ ВАЛЮТЫ В СПИСКЕ */
            .currency-item {
                display: flex;         /* Используем flex-раскладку */
                align-items: center;   /* Выравниваем элементы по вертикали */
                padding: 5px;          /* Внутренние отступы */
                border-bottom: 1px solid #eee; /* Разделительная линия */
                font-size: 0.9em;      /* Размер шрифта */
            }

            /* Убираем разделитель у последнего элемента */
            .currency-item:last-child {
                border-bottom: none;
            }

            /* [5] СТИЛИ ДЛЯ ФЛАГА ВАЛЮТЫ */
            .currency-flag {
                margin-right: 8px;    /* Отступ справа */
                font-size: 20px;      /* Размер emoji-флагов */
                width: 24px;          /* Фиксированная ширина */
                text-align: center;   /* Центрирование по горизонтали */
            }

            /* [6] СТИЛИ ДЛЯ НАЗВАНИЯ ВАЛЮТЫ */
            .currency-name {
                flex-grow: 1;         /* Занимает все доступное пространство */
                white-space: nowrap;  /* Запрещаем перенос текста */
                overflow: hidden;     /* Скрываем текст за границами */
                text-overflow: ellipsis; /* Добавляем многоточие */
            }

            /* Стили для кода валюты в скобках */
            .currency-name small {
                color: #777;          /* Серый цвет */
                font-size: 0.8em;     /* Уменьшенный размер шрифта */
            }

            /* [7] СТИЛИ ДЛЯ ЗНАЧЕНИЯ КУРСА */
            .currency-rate {
                font-weight: bold;    /* Полужирное начертание */
                margin-left: 10px;    /* Отступ слева */
                white-space: nowrap;  /* Запрещаем перенос */
                color: #2a6496;       /* Синий цвет */
            }

            /* [8] СТИЛИ ДЛЯ ИНФОРМАЦИИ О КЭШИРОВАНИИ */
            .cache-info {
                text-align: left;     /* Выравнивание по левому краю */
                color: #666;          /* Серый цвет текста */
                margin-top: 10px;     /* Отступ сверху */
                font-size: 0.8em;    /* Уменьшенный размер шрифта */
            }

            /* [9] СТИЛИ ДЛЯ СООБЩЕНИЙ ОБ ОШИБКАХ */
            .empty-message, .error-message {
                text-align: left;     /* Выравнивание по левому краю */
                color: #d9534f;      /* Красный цвет для ошибок */
                padding: 10px;       /* Внутренние отступы */
                font-size: 0.9em;    /* Размер шрифта */
            }
        </style>
    </head>
   <body>

     <?php
      /*
         echo '<pre>';
         print_r($data['rates']); // проверка API ЦБ РФ предоставляет ли курс для этой валюты.
         echo '</pre>';
      */
     ?>

      <!-- [10] ОСНОВНОЙ КОНТЕНТ СТРАНИЦЫ (будет скролиться) -->
      <!-- Здесь должен быть ваш основной контент страницы -->

      <!-- [11] ФИКСИРОВАННЫЙ ВИДЖЕТ КУРСОВ ВАЛЮТ -->
     <div class="currency-widget" id="draggable-widget">
        <!-- Заголовок виджета -->
        <div class="header">
            <h1>Курсы валют ЦБ РФ</h1>
            <p>Актуальные курсы на <?= date('d.m.Y') ?></p>
        </div>

        <?php
          // [12] ПРОВЕРКА НАЛИЧИЯ ДАННЫХ О КУРСАХ
          if (!empty($data['rates'])):
        ?>
            <?php
              // Сортируем курсы валют по возрастанию
              asort($data['rates']);
            ?>

            <!-- Список валют -->
            <div class="currency-list">
                <?php foreach ($data['rates'] as $code => $rate): ?>
                    <?php if (isset($config['currencies'][$code])): ?>
                        <!-- Отдельная валюта -->
                        <div class="currency-item">
                            <!-- Флаг валюты -->
                            <span class="currency-flag"><?= $config['currencies'][$code]['flag'] ?></span>

                            <!-- Название и код валюты -->
                            <span class="currency-name">
                              <?= htmlspecialchars($config['currencies'][$code]['name']) ?>
                              <small>(<?= htmlspecialchars($code) ?>)</small>
                            </span>

                            <!-- Значение курса -->
                            <span class="currency-rate">
                              <?= number_format($rate, 2) ?> руб.
                            </span>
                        </div>
                    <?php endif; ?>
                  <?php endforeach; ?>
                </div>

                 <!-- Информация об обновлении -->
                <p class="cache-info">
                  Обновлено: <?= date('H:i:s', $data['updated_at']) ?>
                  <?= $useCache ? '(из кэша)' : '(актуальные данные)' ?>
                </p>
               <?php else: ?>
                <!-- Сообщение об ошибке загрузки -->
               <p class="empty-message">Не удалось загрузить курсы валют.</p>
               <?php endif; ?>

               <?php if (!empty($data['error'])): ?>
               <!-- Сообщение об ошибке -->
               <p class="error-message">Ошибка: <?= htmlspecialchars($data['error']) ?></p>
               <?php endif; ?>
            </div>

       <script>

            // Ждем полной загрузки DOM-дерева перед выполнением скрипта
          document.addEventListener('DOMContentLoaded', function()
          {
            // Получаем ссылку на перетаскиваемый виджет по его ID
            const widget = document.getElementById('draggable-widget');

            // Флаг, указывающий, происходит ли в данный момент перетаскивание
            let isDragging = false;

            // Переменные для хранения смещения курсора относительно элемента
            let offsetX, offsetY;

            // Переменные для хранения начальной позиции Y и высоты виджета
            let startY, widgetHeight;

             // Обработчик события нажатия кнопки мыши на виджете (начало перетаскивания)
              widget.addEventListener
                  ('mousedown', function(e)
               {
                     // Устанавливаем флаг перетаскивания в true
                     isDragging = true;

                     // Вычисляем смещение курсора относительно левого верхнего угла виджета:
                     // e.clientX/Y - координаты курсора в момент клика
                     // widget.getBoundingClientRect().left/top - позиция виджета относительно viewport
                     offsetX = e.clientX - widget.getBoundingClientRect().left;
                     offsetY = e.clientY - widget.getBoundingClientRect().top;

                     // Запоминаем начальную позицию Y (для ограничения перемещения)
                     startY = e.clientY;

                     // Сохраняем текущую высоту виджета (чтобы он не растягивался)
                     widgetHeight = widget.offsetHeight;

                     // Меняем курсор на "grabbing" (закрытая рука) для визуальной обратной связи
                     widget.style.cursor = 'grabbing';

                     // Увеличиваем тень виджета при перетаскивании для эффекта "поднятия"
                     widget.style.boxShadow = '0 4px 10px rgba(0,0,0,0.2)';

                      // Предотвращаем стандартное поведение (например, выделение текста)
                      e.preventDefault();
               }   );

                    // Обработчик движения мыши по документу (само перетаскивание)
                    document.addEventListener
                    ('mousemove', function(e)
                 {
                       // Если перетаскивание не активно - выходим из функции
                       if (!isDragging) return;

                       // Вычисляем новую позицию X:
                       // e.clientX - текущая позиция курсора по X
                       // offsetX - смещение курсора относительно виджета
                       const x = e.clientX - offsetX;

                       // Вычисляем новую позицию Y с ограничениями:
                       // Math.min выбирает минимальное значение из двух вариантов:
                       // 1. e.clientY - offsetY - естественная позиция по Y
                       // 2. window.innerHeight - widgetHeight - 20 - максимальная позиция (высота окна минус высота виджета и 20px отступ)
                        const y = Math.min
                       (
                         e.clientY - offsetY,
                         window.innerHeight - widgetHeight - 20
                       );

                        // Применяем вычисленные координаты к виджету
                        widget.style.left = x + 'px';
                        widget.style.top = y + 'px';
                 }   );

                 // Обработчик отпускания кнопки мыши (конец перетаскивания)
                document.addEventListener
                  ('mouseup', function()
                {
                    // Сбрасываем флаг перетаскивания
                    isDragging = false;

                    // Возвращаем курсор в состояние "grab" (открытая рука)
                    widget.style.cursor = 'grab';

                    // Возвращаем тень к исходному значению
                    widget.style.boxShadow = '0 2px 5px rgba(0,0,0,0.1)';
                } );

                   // Инициализация - устанавливаем курсор "grab" при загрузке
                   widget.style.cursor = 'grab';
          } );

        </script>

    </body>
  </html>
        <?php
        // [13] ВОЗВРАЩАЕМ СОБРАННЫЙ HTML
        return ob_get_clean();
}
        ?>

