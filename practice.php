<?php

declare(strict_types=1);

    $products = ['Шины', 'Масло', 'Свечи зажигания', 'Придахронители' ];

echo '<pre>';
print_r($products);
echo '</pre>';

    $prices = ['Шины'=> 100, 'Масло'=> 10, 'Свечи зажигания'=> 4, 'Придахронители'=> 15];

echo '<pre>';
print_r($prices);
echo '</pre>';

    for ($i = 0; $i < 4; $i = $i + 1)
    {
        echo $products[$i];
        if ($i < 3) echo ", ";
    }

echo "<br>";
echo "<br>";

    foreach($products as $index => $current)
    {
        echo $current;
        if ($index < 3) echo ", ";
    }

echo "<br>";
echo "<br>";

     foreach ($prices as $key => $value)
     {
         echo $key." - ".$value."<br />";
     }

echo "<br>";
echo "<br>";

    $Products = [
     ['TIR', 'Шины', 100],
     ['OIL', 'Масло', 10],
     ['SPK', 'Свечи зажигания', 4],
     ['PRE', 'Придахронители', 15]  // Новый товар
     ];

echo '<pre>';
print_r($Products);
echo '</pre>';


      foreach ($Products as $Product)
      {
         echo "Артикул: {$Product[0]}, Название: {$Product[1]}, Цена: {$Product[2]} $ .<br>";
      }

echo "<br>";
echo "<br>";


function load_orders($file) {
    $orders = [];
    $current = [];

    foreach (file($file) as $line) {
        $line = trim($line);
        if (empty($line)) {
            if (!empty($current)) {
                $orders[] = $current;
                $current = [];
            }
            continue;
        }

        $data = str_getcsv($line);
        if (count($data) >= 2) {
            $current[trim($data[0])] = trim($data[1]);
        }
    }

    if (!empty($current)) $orders[] = $current;
    return $orders;
}

// Использование
$orders = load_orders('orders.csv');
echo '<pre>';
print_r($orders);
echo '</pre>';


// Пример строки для проверки
$text = "Мой email: example@mail.com, а другой - test123@domain.org, 
         warnawa@jmail.com, andrey.semakin.73@jmail.com Некорректный: user@.com";

// Регулярное выражение для поиска email-адресов
$pattern = '/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/';

// Поиск всех совпадений
preg_match_all($pattern, $text, $matches);

// Вывод результатов
echo "<h2>Пример регулярного выражения в PHP</h2>";
echo "<p><strong>Исходный текст:</strong><br>" . htmlspecialchars($text) . "</p>";
echo "<p><strong>Регулярное выражение:</strong><br><code>" . htmlspecialchars($pattern) . "</code></p>";

if (!empty($matches[0])) {
    echo "<p><strong>Найденные email-адреса:</strong></p>";
    echo "<ul>";
    foreach ($matches[0] as $email) {
        echo "<li>" . htmlspecialchars($email) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Email-адреса не найдены.</p>";
}

echo "<br>";
echo "<br>";

// Пример строки с email-адресами
//$text = "Контакты: support@site.com, клиент - user123@gmail.com. Некорректные: invalid@, @test.ru, no-email-here";

// Разбиваем текст на отдельные слова/фрагменты
$words = preg_split('/[\s,;:]+/', $text);

// Массив для валидных email-адресов
$valid_emails = [];

// Проверяем каждый фрагмент
foreach ($words as $word) {
    // Очищаем фрагмент от лишних символов
    $clean_word = trim($word, ' .,;:!?');

    // Проверяем валидность email
    if (filter_var($clean_word, FILTER_VALIDATE_EMAIL)) {
        $valid_emails[] = $clean_word;
    }
}

// Выводим результат
echo "<h2>Проверка email через filter_var()</h2>";
echo "<p><strong>Исходный текст:</strong><br>" . htmlspecialchars($text) . "</p>";

if (!empty($valid_emails)) {
    echo "<p><strong>Валидные email-адреса:</strong></p>";
    echo "<ul>";
    foreach ($valid_emails as $email) {
        echo "<li>" . htmlspecialchars($email) . "</li>";
    }
    echo "</ul>";
} else {
    echo "<p>Валидные email-адреса не найдены.</p>";
}
echo "<br>";
echo "<br>";

function my_function() {
    echo "'Моя пиздатая функция'";
}
my_function();

echo "<br>";
echo "<br>";

require_once("currency_functions_2.php");
echo get_currency_rates_2();

echo "<br>";
echo "<br>";

$data = ['Первая порция данных', 'Вторая порция данных', 'Третья порция данных'];
function create_table(array $data)
{
    echo '<table border="1">'; // border для видимости таблицы
    foreach ($data as $value) {
        echo "<tr><td>$value</td></tr>";
    }
    echo '</table>';
}

create_table($data); // Вызов функции

echo "<br>";
echo "<br>";

$my_data = ['Первая порция данных', 'Вторая порция данных', 'Третья порция данных'];
$my_header = 'Данные';
$my_caption = 'Данные о чем-нибудь';
function create_table_1(array $data, string $header = null, string $caption = null)
{
    echo '<table border="1">';  // Добавим border для наглядности

    if ($caption) {
        echo "<caption>$caption</caption>";
    }

    if ($header) {
        echo "<tr><th>$header</th></tr>";
    }

    foreach ($data as $value) {
        echo "<tr><td>$value</td></tr>";
    }

    echo '</table>';
}
create_table_1($my_data, $my_header, $my_caption);

echo "<br>";
echo "<br>";

function increment(&$value, $amount = 1) {
    $value = $value + $amount; // Работает с оригиналом!
}
$value = 10;
increment($value);
echo $value; // Выведет 11 (оригинал изменился)

echo "<br>";
echo "<br>";

function increment_1($value, $amount = 1) {
    return $value + $amount; // Возвращает новое значение
}
$value = 10;
$newValue = increment_1($value);
echo $newValue; // 11
echo "<br>";
echo $value;    // 10 (не изменился)

echo "<br>";
echo "<br>";

// Умножение каждого элемента массива на 2 (через array_map)
$numbers = [1, 2, 3];
$doubled = array_map(function($n) { return $n * 2; }, $numbers);

print_r($doubled); // [2, 4, 6]

