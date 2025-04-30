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
function get_currency_rates(array $config = []) {
    // Установка значений по умолчанию
    $defaults = [
        'cache_file' => __DIR__ . '/currency_cache.json',
        'cache_time' => 3, // 1 час
        'currencies' => [
            'USD' => ['name' => 'Доллар США', 'flag' => '🇺🇸'],
            'EUR' => ['name' => 'Евро', 'flag' => '🇪🇺'],
            'GBP' => ['name' => 'Фунт стерлингов', 'flag' => '🇬🇧'],
            'CNY' => ['name' => 'Китайский юань', 'flag' => '🇨🇳'],
            'JPY' => ['name' => 'Японская иена', 'flag' => '🇯🇵'],
            'CAD' => ['name' => 'Канадский доллар', 'flag' => '🇨🇦'],
            'IDR' => ['name' => 'Индонезийская рупия', 'flag' => '🇮🇩'],
            'INR' => ['name' => 'Индийская рупия', 'flag' => '🇮🇳']
        ]
    ];

    $config = array_merge($defaults, $config);

    // Функция для парсинга курсов валют
    $parse_currency_rates = function() {
        $url = 'https://www.cbr.ru/scripts/XML_daily.asp?date_req=' . date('d/m/Y');

        try {
            $options = [
                'http' => [
                    'method' => 'GET',
                    'timeout' => 15,
                    'header' => "User-Agent: Mozilla/5.0\r\n"
                ]
            ];
            $context = stream_context_create($options);

            $xml = file_get_contents($url, false, $context);
            if ($xml === false) {
                $error = error_get_last();
                throw new Exception("Ошибка запроса: " . ($error['message'] ?? 'неизвестная ошибка'));
            }

            if (empty($xml)) {
                throw new Exception("Пустой ответ от сервера");
            }

            if (strpos($xml, '<Valute') === false) {
                throw new Exception("Некорректный формат XML");
            }

            $pattern = '/
               <Valute\b[^>]*>
               .*?<CharCode>(USD|EUR|GBP|CNY|JPY|CAD|IDR|INR)<\/CharCode>
               .*?<Value>([\d,]+)<\/Value>
               /xis';
            preg_match_all($pattern, $xml, $matches, PREG_SET_ORDER);

            if (empty($matches)) {
                throw new Exception("Не найдены данные о валютах в XML");
            }

            $rates = [];
            foreach ($matches as $match) {
                $rates[$match[1]] = (float)str_replace(',', '.', $match[2]);
            }

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

    // Работа с кэшем
    $data = [];
    $useCache = false;

    if (file_exists($config['cache_file']) && (time() - filemtime($config['cache_file']) < $config['cache_time'])) {
        $cached = json_decode(file_get_contents($config['cache_file']), true);
        if (json_last_error() === JSON_ERROR_NONE && !empty($cached['rates'])) {
            $data = $cached;
            $useCache = true;
        }
    }

    if (!$useCache) {
        $data = $parse_currency_rates();

        if (empty($data['error'])) {
            file_put_contents($config['cache_file'], json_encode($data), LOCK_EX);
        }
    }

    // Генерация HTML
    ob_start();
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Курсы валют ЦБ РФ</title>
        <meta charset="UTF-8">

        <style>
            /* Стили для заголовка блока */
            .header {
                text-align: left;    /* Выравнивание текста по левому краю */
                margin-bottom: 8px; /* Отступ снизу */
                font-size: 13px;    /* Размер шрифта немного больше обычного */
            }

            /* Основной контейнер для списка валют */
            .currency-list {
                max-width: 280px;    /* Максимальная ширина блока */
                margin: 0;           /* Убираем автоматические отступы по бокам */
                padding: 3px;        /* Внутренние отступы */
                border: 1px solid #e0e0e0;  /* Тонкая серая рамка */
                border-radius: 5px;  /* Скругленные углы */
                background: #f9f9f9; /* Светло-серый фон */
            }

            /* Стиль для каждого элемента валюты */
            .currency-item {
                display: flex;       /* Используем flex-раскладку */
                align-items: center; /* Выравниваем элементы по вертикали */
                padding: 5px 5px;    /* Внутренние отступы: 8px сверху/снизу, 5px слева/справа */
                border-bottom: 1px solid #eee;  /* Разделительная линия между элементами */
                font-size: 0.9em;    /* Чуть уменьшенный размер шрифта */
            }

            /* Убираем разделитель у последнего элемента */
            .currency-item:last-child {
                border-bottom: none;
            }

            /* Стили для флага валюты */
            .currency-flag {
                margin-right: 8px;   /* Отступ справа от флага */
                font-size: 20px;     /* Размер emoji-флагов */
                width: 24px;         /* Фиксированная ширина для выравнивания */
                text-align: center;  /* Центрируем флаг по горизонтали */
            }

            /* Стили для названия валюты */
            .currency-name {
                flex-grow: 1;        /* Занимает все доступное пространство */
                white-space: nowrap; /* Запрещаем перенос текста */
                overflow: hidden;    /* Скрываем текст, выходящий за границы */
                text-overflow: ellipsis; /* Обрезаем текст с многоточием */
            }

            /* Стили для кода валюты в скобках */
            .currency-name small {
                color: #777;         /* Серый цвет для кода */
                font-size: 0.8em;    /* Чуть меньший размер шрифта */
            }

            /* Стили для значения курса */
            .currency-rate {
                font-weight: bold;   /* Полужирное начертание */
                margin-left: 10px;   /* Отступ слева */
                white-space: nowrap; /* Запрещаем перенос курса */
                color: #2a6496;      /* Синий цвет для курса */
            }

            /* Стили для информации о кэшировании */
            .cache-info {
                text-align: left;    /* Выравнивание по левому краю */
                color: #666;         /* Серый цвет текста */
                margin-top: 10px;    /* Отступ сверху */
                font-size: 0.8em;    /* Уменьшенный размер шрифта */
            }

            /* Стили для сообщений об ошибках и пустых состояниях */
            .empty-message, .error-message {
                text-align: left;    /* Выравнивание по левому краю */
                color: #d9534f;     /* Красный цвет для ошибок */
                padding: 10px;      /* Внутренние отступы */
                font-size: 0.9em;   /* Чуть уменьшенный размер шрифта */
            }
        </style>

    </head>
    <body>
    <div class="header">
        <h1>Курсы валют ЦБ РФ</h1>
        <p>Актуальные курсы на <?= date('d.m.Y') ?></p>
    </div>

    <?php if (!empty($data['rates'])): ?>
        <?php asort($data['rates']); ?>
        <div class="currency-list">
            <?php foreach ($data['rates'] as $code => $rate): ?>
                <?php if (isset($config['currencies'][$code])): ?>
                    <div class="currency-item">
                        <span class="currency-flag"><?= $config['currencies'][$code]['flag'] ?></span>
                        <span class="currency-name">
                                <?= htmlspecialchars($config['currencies'][$code]['name']) ?>
                                <small>(<?= htmlspecialchars($code) ?>)</small>
                            </span>
                        <span class="currency-rate">
                                <?= number_format($rate, 2) ?> руб.
                            </span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <p class="cache-info">
            Обновлено: <?= date('H:i:s', $data['updated_at']) ?>
            <?= $useCache ? '(из кэша)' : '(актуальные данные)' ?>
        </p>
    <?php else: ?>
        <p class="empty-message">Не удалось загрузить курсы валют.</p>
    <?php endif; ?>

    <?php if (!empty($data['error'])): ?>
        <p class="error-message">Ошибка: <?= htmlspecialchars($data['error']) ?></p>
    <?php endif; ?>
    </body>
    </html>
    <?php
    return ob_get_clean();
}
?>