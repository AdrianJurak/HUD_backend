<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f3f4f6;
            margin: 0;
            padding: 40px 20px;
        }
        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .header {
            font-size: 24px;
            font-weight: bold;
            color: #1f2937;
            margin-bottom: 10px;
        }
        .text {
            color: #4b5563;
            font-size: 16px;
            line-height: 1.5;
            margin-bottom: 30px;
        }
        .code-box {
            background-color: #f8fafc;
            border: 2px dashed #3b82f6;
            color: #1d4ed8;
            font-size: 36px;
            font-weight: 800;
            letter-spacing: 8px;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
        .footer {
            font-size: 13px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="header">Witaj w SmartHUD!</div>
    <div class="text">
        Aby dokończyć rejestrację konta, wprowadź poniższy 6-cyfrowy kod autoryzacyjny w swojej aplikacji:
    </div>

    <div class="code-box">
        {{ $token }}
    </div>

    <div class="text" style="font-size: 14px;">
        Kod wygaśnie automatycznie za 15 minut.<br>
        Jeśli to nie Ty zakładałeś konto, po prostu zignoruj tę wiadomość.
    </div>

    <div class="footer">
        Wiadomość wygenerowana automatycznie.<br>
        Zespół SmartHUD
    </div>
</div>
</body>
</html>
