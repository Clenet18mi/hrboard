<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; font-size: 12px; }
        .header { border-bottom: 2px solid #4f46e5; pb: 10px; margin-bottom: 20px; }
        .logo { font-size: 24px; font-weight: bold; color: #4f46e5; }
        .company-info { float: right; text-align: right; font-size: 10px; color: #666; }
        .footer { position: fixed; bottom: 0; width: 100%; text-align: center; font-size: 10px; color: #999; border-top: 1px solid #eee; padding-top: 10px; }
        .title { font-size: 18px; font-weight: bold; margin-bottom: 20px; text-align: center; text-transform: uppercase; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { background-color: #f9fafb; border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
        td { border: 1px solid #e5e7eb; padding: 8px; }
        .section-title { font-weight: bold; background-color: #f3f4f6; padding: 5px 10px; margin-top: 15px; margin-bottom: 10px; border-left: 4px solid #4f46e5; }
        .clearfix::after { content: ""; clear: both; display: table; }
    </style>
</head>
<body>
    <div class="header clearfix">
        <div style="float: left;">
            <div class="logo">HRBoard</div>
            <div>Gestion des Ressources Humaines</div>
        </div>
        <div class="company-info">
            <strong>HRBoard Corp.</strong><br>
            123 Avenue de Laravel<br>
            75000 Paris, France<br>
            contact@hrboard.com
        </div>
    </div>

    <div class="content">
        @yield('content')
    </div>

    <div class="footer">
        Document généré le {{ date('d/m/Y H:i') }} - HRBoard
    </div>
</body>
</html>
