# BTC application

Скопіюйте з репозиторію додаток 
```bash
git clone https://github.com/nick-mad/btc_service.git
cd btc_service 
```

Для запуску додатку в Docker потрібно виконати 
```bash
docker build -t btc_service .
docker run -p 80:80 --env MAIL_SERVER=your_mail_server --env MAIL_PORT=your_mail_port --env MAIL_USERNAME=your_mail_username --env MAIL_PASSWORD=your_mail_password btc_service
```
Замніть Значення `MAIL_SERVER`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_USERNAME` на відповідні параметри вашого SMTP серверу

Після цього додаток буде доступний за посиланням `http://localhost/` 

`api/rate` Отримати поточний курс BTC до UAH

Отримуємо курс з стороннього сервісу, якщо цей сервіс недоступний, то намагаэмось отримати дані з іншого сервісу.
Якщо не вдалося отримати поточний курс, то віддаємо помилку 400 Invalid status value.

Щоб на якості роботи сервісу не відображалась латентність стороннього сервісу або його доступність,
можливо б краще було отримувати курс по крону та записувати у файл і віддавати його з файлу.
Або можна було б організувати своєрідний кеш. Отримувати курс з стороннього сервісу і також писати в файл. 
А при повторному зверненні віддавати дані з файлу, поки різниця дати поточного часу і часу створення файлу не буде більше 5 хв.

`api/subscribe` Підписати email на отримання поточного курсу

Для визначення чи є email у файлі, читаємо його по рядкам, доки не знайдемо потрібний email. 
Якщо email знайшли, то не потрібно читати файл до кінця і віддаємо true.
При переборі файла по рядкам не потрібно завантажувати весь файл у память. 
Перебір зроблений на генераторі, так зручніше, генератор в репозиторії і вже з репозиторію маємо до email доступ

Якщо email не знайшли, то приводимо його до нижнього регистру.
Перевіряємо на валідність, і якщо валідний - то записуємо. 
Якщо він не валідний то, навіть, якщо його записати, то на нього все одно не буде доставлено листа.

`api/sendEmails` Відправити листа з поточним курсом на всі підписані електронні пошти.

Читаємо файл по рядку з email'ами і по кожному відправляємо через smtp листи (в данній реалізації).
Для додатку який передбачається використовувати в контейнеру потрібно використовувати сторонні служби доставки листів.
Так як, налаштування поштового серверу складні для того, щоб листи були доставленими (потрібно прописувати записи в зворотніх зонах і т.д.).
Тому потрібно використовувати сторонні сервіси або налаштований сервер smtp.
Для відправки листів використовуємо `symfony/mailer` в якому реалізовано відправку через різні сторонні сервіси.
Тому не складе проблему перемкнутися на інший, потрібний варіант транспорту листів.

Для реалізації додатку було використано фреймворк slimphp, з використанням skeleton-api. 
Каркас реалізовує invokable контролери, та роботу з сущностями на основі патерну reposotory.
В цьому skeleton було закладено структура саме для такої реалізації, тому так і зробив.
А фреймворк цей вибрав, бо в ньому не було нічого зайвого, тільки робота з request і response і routing.

Можна було вибрати ще або Lumen або скласти к компонентів symfony, але то мені здалося зайвим.

Також можна було реалізувати все в одному файлі, але хотілося показати якесь розуміння OOP, та розуміння побудови сучасного додатку на php.

```php
<?php

$parseUrl = isset($_SERVER['REQUEST_URI']) ? parse_url($_SERVER['REQUEST_URI']) : [];
$path = isset($parseUrl['path']) ? trim($parseUrl['path'], '/') : '';
$file = 'emails.txt';
$header[] = 'Content-Type: application/json';
$content = '';

switch ($path) {
    case 'api/rate':
        $rate = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=uah');
        $rate = json_decode($rate, true, 512);
        if (!empty($rate['bitcoin']['uah'])) {
            $content = (string)$rate['bitcoin']['uah'];
        } else {
            $header[] = 'HTTP/1.1 400 Bad Request';
            $content = 'Invalid status value';
        }
        break;
        
    case 'api/subscribe':
        if (
            isset($_SERVER['REQUEST_METHOD']) &&
            $_SERVER['REQUEST_METHOD'] === 'POST' &&
            !empty($_POST['email']) &&
            filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)
        ) {
            $newEmail = $_POST['email'];
            $emails = [];
            if (is_file($file)) {
                $emails = file($file);
                $emails = array_map('trim', $emails);
            }
            if (in_array($newEmail, $emails)) {
                $header[] = 'HTTP/1.1 409 Conflict';
            } else {
                file_put_contents($file, $newEmail . PHP_EOL, FILE_APPEND);
                $content = 'email added';
            }
        }
        break;
        
    case 'api/sendEmails':
        if (
            isset($_SERVER['REQUEST_METHOD']) && 
            $_SERVER['REQUEST_METHOD'] === 'POST' && 
            is_file($file)
        ) {
            $rate = file_get_contents('https://api.coingecko.com/api/v3/simple/price?ids=bitcoin&vs_currencies=uah');
            $rate = json_decode($rate, true, 512);
            if (!empty($rate['bitcoin']['uah'])) {
                $rate = (string)$rate['bitcoin']['uah'];
                $emails = file($file);
                $emails = array_map('trim', $emails);
                foreach ($emails as $email) {
                    mail($email, 'rate btc', $rate);
                }
                $content = 'emails send';
            }
        }
        break;

    default:
        $header[] = 'HTTP/1.1 404 Not Found';
        break;
}

foreach ($header as $item) {
    header($item);
}

if (!empty($content)) {
    echo json_encode($content);
}
```


