<?php
function lottozahlen_generieren() {
    $zahlen = range(1, 42);  
    shuffle($zahlen);         
    $gewinnzahlen = array_slice($zahlen, 0, 6); 
    sort($gewinnzahlen);      

    $zusatzzahl = rand(1, 6); 

    return ["zahlen" => $gewinnzahlen, "zusatzzahl" => $zusatzzahl];
}

$lotto = lottozahlen_generieren();
?>

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schweizer Lottozahlen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            text-align: center;
            background-color: #f4f4f4;
            margin: 50px;
        }
        .container {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            display: inline-block;
        }
        h1 {
            color: #333;
        }
        .zahlen {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin: 20px 0;
        }
        .zahl {
            background: #ffcc00;
            color: #333;
            font-size: 20px;
            font-weight: bold;
            padding: 15px;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 0 5px rgba(0, 0, 0, 0.3);
        }
        .zusatzzahl {
            background: #ff3333;
            color: white;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Schweizer Lottozahlen</h1>
    <div class="zahlen">
        <?php foreach ($lotto["zahlen"] as $zahl): ?>
            <div class="zahl"><?= $zahl ?></div>
        <?php endforeach; ?>
    </div>
    <h2>Zusatzzahl</h2>
    <div class="zahlen">
        <div class="zahl zusatzzahl"><?= $lotto["zusatzzahl"] ?></div>
    </div>
</div>

</body>
</html>
