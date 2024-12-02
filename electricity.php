<?php

if(isset($_POST["pay"])){
    $serviceID = trim(htmlspecialchars($_POST["service"]));
    $billersCode = trim(htmlspecialchars($_POST["meter"]));
    $type = trim(htmlspecialchars($_POST["type"]));
    $amount = trim(htmlspecialchars($_POST["amount"]));
    $phone = trim(htmlspecialchars($_POST["phone"]));

    date_default_timezone_set('Africa/Lagos');

    // 1. Verify the meter number
    $verifyUrl = "https://sandbox.vtpass.com/api/merchant-verify";
    $verifyData = array(
        "billersCode" => $billersCode,
        "serviceID" => $serviceID,
        "type" => $type
    );
    $verifyResponse = callVTPassAPI($verifyUrl, $verifyData);
    $verifyData = json_decode($verifyResponse, true);

    // 2. Make the purchase
    $purchaseUrl = "https://sandbox.vtpass.com/api/pay";
    $purchaseData = array(
        "request_id" => generateRequestID(),
        "serviceID" => $serviceID,
        "billersCode" => $billersCode,
        "variation_code" => $type, // Use 'prepaid' or 'postpaid'
        "amount" => $amount,
        "phone" => $phone
    );
    $purchaseResponse = callVTPassAPI($purchaseUrl, $purchaseData);
    $purchaseData = json_decode($purchaseResponse, true);

    // Display the response
    print_r($purchaseData);
}

function callVTPassAPI($url, $data = null) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $data ? "POST" : "GET");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data ? json_encode($data) : null);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
       "api-key: " . getenv('API_KEY'),
        "secret-key: " . getenv('SECRET_KEY'),
        "Content-Type: application/json"
    ));

    $response = curl_exec($ch);
    curl_close($ch);
    return $response;
}

function generateRequestID() {
    date_default_timezone_set('Africa/Lagos');
    $current_time = new DateTime();
    $formated_time = $current_time->format("YmdHi");
    $additional_chars = "89htyyo";
    $request_id = $formated_time . $additional_chars;
    while(strlen($request_id) < 12){
        $request_id .= "x";
    }
    return $request_id;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VTPass Electricity Bill Payment</title>
        <link rel="stylesheet" href="./styles.css">

</head>
<body>
    <h1>VTPass Electricity Bill Payment</h1>
    <form action="" method="POST">
        <select name="service" required>
            <option value="">Select a Service</option>
            <option value="ikeja-electric">Ikeja Electric</option>
            <option value="kadeco">Kaduna Electric</option>
            <option value="aedc">Abuja Electricity Distribution Company</option>
        </select>
        <input type="text" name="meter" required placeholder="Enter Meter Number">
        <select name="type" required>
            <option value="">Select Type</option>
            <option value="prepaid">Prepaid</option>
            <option value="postpaid">Postpaid</option>
        </select>
        <input type="number" name="amount" required placeholder="Enter Amount">
        <input type="text" name="phone" required placeholder="Enter Phone Number">
        <input type="submit" name="pay" value="Make Payment">
    </form>
</body>
</html>