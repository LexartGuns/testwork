<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Testwork</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
    // п. 2.8 . Метод Confirm

    //формирование данных для функции Confirm
    //вводные данные из примеров описания merchant_api_protocol_eacq.pdf (что получилось найти)
    $Items = (object) array(
        'Name' => 'Наименование товара 1',
        'Price' => 10000,
        'Quantity' => 1.00,
        'Amount' => 10000,
        'Tax' => 'vat10',
        'Ean13' => '303130323930303030630333435'
    );
    
    $Receipt = (object) array(
        "Email" => "a@test.ru",
        "Phone" => "+79031234567",
        "Taxation" => "osn",
        "Items"  => $Items
    );

    $params = array(
        'TerminalKey' => 'TinkoffBankTest',
        'PaymentId' => 10063,
        'Token' => '71199b37f207f0c4f721a37cdcc71dfcea880b4a4b85e3cf852c5dc1e99a8d6',
        'IP' => '200.300.100.500',
        'Amount' => 10000,
        'Receipt' => $Receipt,
        'Shops' => '',
        'Receipts' => '',
        'Route' => '',
        'Source' => ''
    );

    function Confirm ($params) {
        $startCurl = curl_init();
        curl_setopt_array($startCurl, array(
            CURLOPT_URL => 'https://rest-api-test.tinkoff.ru/v2/Confirm',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($params)
        ));

        $response = curl_exec($startCurl);
        curl_close($startCurl);
        $response = json_decode($response, true);

        return $response;
    };
    //получаем результат функции
    $resConfirm = Confirm ($params);
    //проверяем обобрен платеж или нет и присваивае результат в переменную $res
    if ($resConfirm['Success'] == true) $res = 'Одобрено'; else $res = 'Отклонено';

?>
    <h1>Тестовый стенд</h1>
    
    <div class="peyment"></div>
    <script type="text/javascript">
        //сообщение пользователю о результате запроса на оплату
        let warning = '<?php echo $res;?>',
            returnResult = document.querySelector(".peyment");
        
        returnResult.innerHTML = warning;
    </script>
</body>
</html>