<?php

if(isset($_POST["topup"])){
$operator = trim(htmlspecialchars($_POST["operator"]));
$customer = trim(htmlspecialchars($_POST["phone"]));
$amount = trim(htmlspecialchars($_POST["amount"]));

//Integrate VTPass
date_default_timezone_set('Africa/Lagos');

//Get the current timestamp
 $current_time = new DateTime();
 $formated_time = $current_time->format("YmdHi");

 //Generate More alpha-numeric character
 $additional_chars = "89htyyo";

 //Concatenate the two variables for the request IDs above ($formated_time and additional chars)
 $request_id = $formated_time . $additional_chars;
 while(strlen($request_id) < 12){
     $request_id .= "x";
 }

 //API Integration Proper
 $apiUrl = "https://sandbox.vtpass.com/api/pay";

 $data = array(
     "request_id" => $request_id,
     "serviceID" => $operator,
     "amount" => $amount,
     "phone" => $customer
 );

$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
 "api-key: " . getenv('API_KEY'),
        "secret-key: " . getenv('SECRET_KEY'),
    "Content-Type: application/json"
));
// After executing the request
$response = curl_exec($ch);
$result = json_decode($response);
//print_r($response);
if($result->content->transactions->status === "delivered"){
   header("Location: success.php"); 
   exit;
}
else{
    die("Error: The transaction Failed!");
}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>VTPass Airtime and Data</title>
    <link rel="stylesheet" href="./styles.css">
</head>
<body>
    <h1>VTPass Airtime and Data</h1>
    <form action="" method="POST">
    <select name="operator" required>
        <option>select a Network</option>
        <option value="mtn">MTN Airtime</option>
        <option value="airtel">Airtel Airtime</option>
        <option value="glo">Glo Airtime</option>
        <option value="9mobile">9Mobile Airtime</option>
    </select>

    <input type="number" name="amount" required placeholder="How much airtime do you want?">
    <input type="number" name="phone" required placeholder="Enter the phone number that will receive this airtime">
    <input type="submit" name="topup" required value="Top-Up">
</body>
</html>
