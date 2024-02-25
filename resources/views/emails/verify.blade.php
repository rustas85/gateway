<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Код верификации</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f4f4f4;
            color: #333;
        }

        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        h1 {
            color: #444;
        }

        p {
            font-size: 16px;
            line-height: 1.5;
        }

        .code {
            font-weight: bold;
            font-size: 24px;
            color: #007BFF;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Подтверждения электронной почты</h1>
        <p>Спасибо за регистрацию. Пожалуйста, введите следующий код подтверждения, чтобы завершить проверку электронной почты:</p>
        <p class="code">{{ $verificationCode }}</p>
        <p>Если вы не запрашивали это письмо, вы можете смело его игнорировать..</p>
    </div>
</body>

</html>