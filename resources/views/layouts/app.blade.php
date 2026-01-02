<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MarketCuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { background-color: #f8f9fa; }

        .table-custom thead th {
            background-color: #660404 !important;
            color: white !important;
            border: none !important;
            padding: 15px !important;
        }

        .bg-concho { background-color: #660404 !important; }
        .btn-concho { background-color: #660404; color: white; border: none; }
        .btn-concho:hover { background-color: #4d0303; color: white; }

        nav svg { max-width: 20px; }
    </style>
</head>
<body>
@yield('content')
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
