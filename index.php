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
    //формирование Token для метода Init, согласно алгоритму формирования на странице https://www.tinkoff.ru/kassa/develop/api/request-sign/
    $PASSWORD_KEY = 'Password';
    $PASSWORD_VALUE = '12345678';
    $paramsForQuery = array(
        'TerminalKey' => 'TinkoffBankTest',
        'Amount' => 10000,
        'OrderId' => 21050,
        'Description' => 'Подарочная карта на 1000 рублей',
        'DATA' => array(
            'Phone' => '+71234567890',
            'Email' => 'a@test.com'
        ),
        'Receipt' => array(
            'Email' => 'a@test.ru',
            'Phone' => '+79031234567',
            'Taxation' => 'osn',
            'Items' => array(
                    'Name' => 'Наименование товара 1',
                    'Price' => 10000,
                    'Quantity' =>  1.00,
                    'Amount' => 10000,
                    'Tax' => 'vat10',
                    'Ean13' => '0123456789'
                )
        )
    );

    function getToken ($paramsForQuery, $PASSWORD_KEY, $PASSWORD_VALUE) {
        $paramsForToken = array();
        foreach($paramsForQuery as $key => $value) {
            if ($key != 'Shops' && $key != 'Receipt' && $key != 'DATA') {
                $paramsForToken[$key] = $value;
            }
        }
        $paramsForToken[$PASSWORD_KEY] = $PASSWORD_VALUE;
        ksort($paramsForToken);
        $konkat = '';
        foreach($paramsForToken as $value) {
            $konkat = $konkat.(string)$value;
        }
        return hash('sha256', $konkat);
    }

    $Token = getToken ($paramsForQuery, $PASSWORD_KEY, $PASSWORD_VALUE);

    //п. 2.3. Метод Init

    //тестовые данные
    $paramsForInit = array(
        'TerminalKey' => 'TinkoffBankTest',
        'Amount' => 10000,
        'OrderId' => 21050,
        'Description' => 'Подарочная карта на 1000 рублей',
        'Token' => $Token,
        'DATA' => array(
            'Phone' => '+71234567890',
            'Email' => 'a@test.com'
        ),
        'Receipt' => array(
            'Email' => 'a@test.ru',
            'Phone' => '+79031234567',
            'Taxation' => 'osn',
            'Items' => array(
                    'Name' => 'Наименование товара 1',
                    'Price' => 10000,
                    'Quantity' =>  1.00,
                    'Amount' => 10000,
                    'Tax' => 'vat10',
                    'Ean13' => '0123456789'
                )
        )
    );
    
    function Init ($paramsForInit) {
        $header = array('Content Type' => 'application/json');
        $startCurl = curl_init();
        curl_setopt_array($startCurl, array(
            CURLOPT_HTTPHEADER => $header,
            CURLOPT_URL => 'https://rest-api-test.tinkoff.ru/v2/Init/',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($paramsForInit)
        ));

        $response = curl_exec($startCurl);
        curl_close($startCurl);
        $response = json_decode($response, true);

        return $response;
    };
    
    //после вызова функции разбираем принятые данные и в зависимости от полученных данных следуем "Схеме проведения платежа"


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
        'Token' => $Token,
        'IP' => '200.300.100.500',
        'Amount' => 10000,
        'Receipt' => $Receipt,
        'Shops' => '',
        'Receipts' => '',
        'Route' => '',
        'Source' => ''
    );

    function Confirm ($params) {
        $header = array('Content Type' => 'application/json');
        $startCurl = curl_init();
        curl_setopt_array($startCurl, array(
            CURLOPT_HTTPHEADER => $header,
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