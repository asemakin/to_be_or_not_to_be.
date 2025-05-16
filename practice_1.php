<?php
require_once("currency_functions_2.php");
echo get_currency_rates_2();

echo "<br>";
echo "<br>";


// 1. КЛАСС (Class) - "Шаблон" или "чертёж" для создания объектов.
// Название класса принято писать с большой буквы (Calculator)
class Calculator
{
    private function applyStyle($text) {     // Метод для добавления стилей к тексту

        return '<span style="font-family: cursive; font-size: 15px; color: #3f51b5;">'
            . htmlspecialchars($text) . '</span>';
    }
    // 2. МЕТОДЫ (Methods) - Функции внутри класса.
    // Названия методов пишутся в camelCase (add, multiply и т.д.)
    public function add($a, $b) {           // Метод для сложения

        return $this->applyStyle($a + $b);
    }
    public function multiply($a, $b) {      // Метод для умножения

        return $this->applyStyle($a * $b);
    }
    public function subtract($a, $b) {      // Метод для вычитания

        return $this->applyStyle($a - $b);
    }
    public function divide($a, $b) {        // Метод для деления

        return $this->applyStyle($a / $b);
    }
    public function power($a, $b) {         // Метод для возведения в степень

        return $this->applyStyle($a ** $b);
    }
}

// 3. ОБЪЕКТ (Object) - "Экземпляр" класса (конкретная реализация).
// Создаётся с помощью ключевого слова "new"
$calc = new Calculator();                 // $calc - это переменная, содержащая объект класса Calculator

// 4. ВЫЗОВ МЕТОДОВ - обращение к методам объекта через "->"
echo $calc->add(2, 3) . "<br>";      // 5 (вызываем метод add() у объекта $calc)
echo $calc->multiply(4, 5) . "<br>"; // 20
echo $calc->subtract(15, 8) . "<br>"; // 7
echo $calc->divide(9, 3) . "<br>";    // 3
echo $calc->power(2, 8);     // 256

echo "<br>";
echo "<br>";

// Альтернативный вариант через цикл
$operations = [             // Массив операций для выполнения
    ['add', 2, 3],
    ['multiply', 4, 5],
    ['subtract', 15, 8],
    ['divide', 9, 3],
    ['power', 2, 8]
];

foreach ($operations as $operation) {        // Выполняем операции в цикле
    $method = $operation[0];
    $a = $operation[1];
    $b = $operation[2];

    echo $calc->$method($a, $b) . "<br>";
}

echo "<br>";
echo "<br>";

class Television   // Класс "Телевизор" (чертёж)
{
    public $brand;
    public function turnOn() {
        $text = "Телевизор {$this->brand} включён!";
        $styledText = '<span style="font-family: cursive; font-size: 15px; color: #3f51b5;">'
            . htmlspecialchars($text).'</span>'; // Сохраняем в переменную с рукописным стилем
        return $styledText;
    }
}
// Объект (конкретный телевизор)
$myTV = new Television();
$myTV->brand = "Samsung";
$result = $myTV->turnOn(); // Выведет: "Телевизор Samsung включён!"
echo $result;

echo "<br>";
echo "<br>";

    class Microwave                      // 1. Объявление класса "Микроволновка"
  {
    public $brand;                       // 2. Свойство (характеристика) - бренд
    public function heatFood($time) {    // 3. Метод (действие) - нагреть еду
        $text = "Микроволновка {$this->brand} греет еду {$time} секунд!";
        $styledText = '<span style="font-family: cursive; font-size: 15px; color: #3f51b5;">'
                      . htmlspecialchars($text).'</span>'; // Сохраняем в переменную с рукописным стилем
        return $styledText;
    }
}
$myMic = new Microwave();                // 4. Создание объекта (конкретной микроволновки)
$myMic->brand = "LG";// 5. Установка свойства (бренд LG)
// Текст с оформлением сохранен в $result
$result = $myMic->heatFood(30);     // 6. Вызов метода (результат: "Микроволновка LG греет еду 30 секунд !")
echo $result;                                 // Выводим (например, в HTML)

echo "<br>";
echo "<br>";

   class Kettle   // Объявление класса "Чайник" (Kettle) - это шаблон для создания объектов-чайников
 {
                                           // Публичное свойство (переменная объекта) - объём воды в чайнике
                                           // public означает, что свойство доступно извне класса
    public $volume;                        // По умолчанию public для простоты изучения
    public function setVolume($value) {    // Метод для безопасной установки значения объёма, $value - передаваемое значение объёма
        if ($value > 0) {                  // Проверка: если значение больше 0
            $this->volume = $value;        // Устанавливаем значение свойства volume для текущего объекта ($this)
        } else {
            echo "Ошибка: Объём должен быть положительным!";   // Если значение отрицательное или 0 - выводим сообщение об ошибке
        }
    }
    public function boilWater($time) {                     // Метод для кипячения воды, $time - время кипячения в минутах
        $minuteWord = ($time == 1) ? 'минуту' : 'минуты';  // Тернарный оператор для правильного склонения слова "минута"

        // Выводим сообщение с подставленными значениями:
        // {$this->volume} - объём из текущего объекта
        // {$time} - переданное время
        // {$minuteWord} - правильно склонённое слово
        $text = "Чайник кипятит {$this->volume} литра воды за {$time} {$minuteWord}";
        $styledText = '<span style="font-family: cursive; font-size: 15px; color: #3f51b5;">'
            . htmlspecialchars($text).'</span>'; // Сохраняем в переменную с рукописным стилем
        return $styledText;
    }
 }
         // ===== ИСПОЛЬЗОВАНИЕ КЛАССА ===== //

$myKettle = new Kettle();                 // Создаём новый объект (экземпляр) класса Kettle
$myKettle->setVolume(1.5);          // Устанавливаем объём через метод setVolume (корректное значение) Метод проверит значение и установит 1.5
$result = $myKettle->boilWater(2);  // Вызываем метод кипячения с указанием времени, Выведет: "Чайник кипятит 1.5 литра воды за 2 минуты"
echo $result;

echo "<br>";
echo "<br>";

class Transport               // Родительский класс (суперкласс, базовый класс)
{
    public $color;            // свойство цвета
    public function move() {    // Метод езды

        echo "Транспорт движется";
    }
}

class Car extends Transport   // Дочерний класс (подкласс, производный класс) для автомобиля
{
    public $brand;            // новое свойство - бренд автомобиля
    public function beep() {    // Новый метод - сигналить

        $text = " и сигналит би-бип !";
        $styledText = '<span style="font-family: cursive; font-size: 15px; color: #3f51b5;">'
            . htmlspecialchars($text).'</span>'; // Сохраняем в переменную с рукописным стилем
        return $styledText;
    }

    public function move() {   // Переопределяем метод move() родителя

        $text = "{$this->color} автомобиль {$this->brand} едет по дороге";
        $styledText = '<span style="font-family: cursive; font-size: 15px; color: #3f51b5;">'
            . htmlspecialchars($text).'</span>'; // Сохраняем в переменную с рукописным стилем
        return $styledText;
    }
}

class Bicycle extends Transport   // Дочерний класс для велосипеда
{
    public $type;                 // новое свойство - тип велосипеда
    public function move() {        // Переопределяем метод move() родителя

        $text = "{$this->type} {$this->color} велосипед катится по тропинке";
        $styledText = '<span style="font-family: cursive; font-size: 15px; color: #3f51b5;">'
            . htmlspecialchars($text).'</span>'; // Сохраняем в переменную с рукописным стилем
        return $styledText;
    }
}

// Использование классов
$myCar = new Car();
$myCar->color = "красный";         // свойство унаследовано от Transport
$myCar->brand = "' Toyota '";      // собственное свойство Car
$result_1 = $myCar->move();        // вызовет переопределённый метод
$result = $myCar->beep();          // вызовет метод только Car
echo $result_1;
echo $result;

echo "<br>";
echo "<br>";

$myBike = new Bicycle();
$myBike->color = "синий";    // свойство унаследовано от Transport
$myBike->type = "горный";    // собственное свойство Bicycle
$result = $myBike->move();   // вызовет переопределённый метод
echo $result;

echo "<br>";
echo "<br>";

class BankAccount {                       // Класс для работы с банковским счётом

    private $balance = 0;               // Приватное свойство - баланс можно менять только через методы класса
    private function applyStyle($text) {
        return '<span style="font-family: cursive; font-size: 15px; color: #3f51b5;">'
            . htmlspecialchars($text) . '</span>';
    }
    public function deposit($sum) {       // Метод для пополнения счёта

        if ($sum > 0) {                 // Проверяем, что сумма положительная
            $this->balance = $this->balance + $sum;
            return $this->applyStyle(" Счёт пополнен на $sum руб.");
        } else {
            throw new Exception(" Ошибка: сумма должна быть больше нуля!"); // Если сумма отрицательная - выдаём ошибку
        }
    }
    public function recall($sum) {       // Метод для снятия денег

        // Проверяем что:
        // 1. Сумма положительная
        // 2. На счету достаточно денег
        if ($sum > 0 && $this->balance >= $sum) {
            $this->balance = $this->balance - $sum;
            return $this->applyStyle(" Со счёта снято $sum руб.");
        } else {
            throw new Exception(" Ошибка: недостаточно средств!");
        }
    }
    public function getBalance() {          // Метод для проверки баланса

        return $this->applyStyle(" Текущий баланс: " . $this->balance . ' руб.');
    }

    public function showError($message) {   // Добавляем публичный метод для стилизации ошибок
        return $this->applyStyle($message);
    }

}
$account = new BankAccount();                // Пример использования: создаём новый счёт

// Пополнение счёта
try {
    echo $account->deposit(5000) . "<br>";
} catch (Exception $e) {
    echo $account->showError($e->getMessage()) . "<br>";
}

// Снятие средств (первая попытка)
try {
    echo $account->recall(300) . "<br>"; // Изменил на 30 вместо 300, чтобы сначала показать успешное снятие
} catch (Exception $e) {
    echo $account->showError($e->getMessage()) . "<br>";
}

// Проверка баланса
echo $account->getBalance() . "<br>";

// Снятие средств (вторая попытка - вызовет ошибку)
try {
    echo $account->recall(800) . "<br>";
} catch (Exception $e) {
    echo $account->showError($e->getMessage()) . "<br>";
}

// Итоговый баланс
echo $account->getBalance();
