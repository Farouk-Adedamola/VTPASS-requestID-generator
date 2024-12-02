<?php

if(isset($_POST["generate"])){
    $serviceID = trim(htmlspecialchars($_POST["service"]));
    $variationCode = trim(htmlspecialchars($_POST["variation"]));
    $phone = trim(htmlspecialchars($_POST["phone"]));

    date_default_timezone_set('Africa/Lagos');

    // 1. Get the service variations
    $variationsUrl = "https://sandbox.vtpass.com/api/service-variations?serviceID=" . $serviceID;
    $variationsResponse = callVTPassAPI($variationsUrl);
    $variationsData = json_decode($variationsResponse, true);

    // 2. Make the purchase
    $purchaseUrl = "https://sandbox.vtpass.com/api/pay";
    $purchaseData = array(
        "request_id" => generateRequestID(),
        "serviceID" => $serviceID,
        "variation_code" => $variationCode,
        "amount" => $variationsData["content"]["varations"][array_search($variationCode, array_column($variationsData["content"]["varations"], 'variation_code'))]["variation_amount"],
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
    <title>VTPass WAEC Result Checker PIN Generation</title>
     <link rel="stylesheet" href="./styles.css">

</head>
<body>
    <h1>VTPass WAEC Result Checker PIN Generation</h1>
    <form action="" method="POST">
        <select name="service" required>
            <option value="">Select a Service</option>
            <option value="waec">WAEC Result Checker PIN</option>
        </select>
        <select name="variation" required>
            <option value="">Select a Variation</option>
        </select>
        <input type="text" name="phone" required placeholder="Enter Phone Number">
        <input type="submit" name="generate" value="Generate PIN">
    </form>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var serviceSelect = document.querySelector("select[name='service']");
            var variationSelect = document.querySelector("select[name='variation']");

            serviceSelect.addEventListener("change", function() {
                var selectedService = this.value;
                if (selectedService) {
                    fetchVariations(selectedService, variationSelect);
                } else {
                    variationSelect.innerHTML = "<option value=''>Select a Variation</option>";
                }
            });
        });

        function fetchVariations(serviceID, variationSelect) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", "https://sandbox.vtpass.com/api/service-variations?serviceID=" + serviceID, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    var data = JSON.parse(xhr.responseText);
                    var variations = data.content.varations;
                    variationSelect.innerHTML = "<option value=''>Select a Variation</option>";
                    variations.forEach(function(variation) {
                        var option = document.createElement("option");
                        option.value = variation.variation_code;
                        option.text = variation.name + " - " + variation.variation_amount;
                        variationSelect.add(option);
                    });
                }
            };
            xhr.send();
        }
    </script>
</body>
</html>