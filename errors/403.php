<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>403 - Acceso Denegado</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .error-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            text-align: center;
            padding: 2rem;
        }
        
        .error-code {
            font-size: 6rem;
            font-weight: bold;
            color: #e74c3c;
            margin-bottom: 1rem;
        }
        
        .error-message {
            font-size: 1.5rem;
            color: #2c3e50;
            margin-bottom: 2rem;
        }
        
        .error-description {
            color: #7f8c8d;
            margin-bottom: 2rem;
            max-width: 600px;
        }
        
        .back-button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        
        .back-button:hover {
            background-color: #2980b9;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-code">403</div>
        <h1 class="error-message">Acceso Denegado</h1>
        <p class="error-description">
            No tienes permisos para acceder a este recurso. 
            Si crees que esto es un error, contacta al administrador del sistema.
        </p>
        <a href="../index.php" class="back-button">Volver al Inicio</a>
    </div>
</body>
</html> 