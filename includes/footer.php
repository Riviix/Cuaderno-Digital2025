    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-content">
            <div class="footer-info">
                <p>&copy; <?php echo date('Y'); ?> E.E.S.T. N°2 "Educación y Trabajo" - Cuaderno Digital</p>
                <p>Sistema de Gestión Escolar</p>
            </div>
            <div class="footer-links">
                <a href="about.php">Acerca de</a>
                <a href="contact.php">Contacto</a>
                <a href="help.php">Ayuda</a>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Configuración de seguridad
        document.addEventListener('DOMContentLoaded', function() {
            // Prevenir clickjacking
            if (window.self !== window.top) {
                window.top.location = window.self.location;
            }
            
            // Configurar timeouts de sesión
            let sessionTimeout = <?php echo 28800; ?>; // 8 horas en segundos
            let warningTime = 300; // 5 minutos antes de expirar
            
            function checkSession() {
                fetch('check_session.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        csrf_token: '<?php echo $csrfToken; ?>'
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (!data.valid) {
                        alert('Tu sesión ha expirado. Serás redirigido al login.');
                        window.location.href = 'login.php';
                    }
                })
                .catch(error => {
                    console.error('Error checking session:', error);
                });
            }
            
            // Verificar sesión cada 5 minutos
            setInterval(checkSession, 300000);
            
            // Mostrar advertencia antes de que expire la sesión
            setTimeout(() => {
                if (confirm('Tu sesión expirará pronto. ¿Deseas continuar?')) {
                    checkSession();
                }
            }, (sessionTimeout - warningTime) * 1000);
        });
        
        // Función para mostrar/ocultar loading
        function showLoading() {
            document.body.classList.add('loading');
        }
        
        function hideLoading() {
            document.body.classList.remove('loading');
        }
        
        // Interceptar envíos de formularios para mostrar loading
        document.addEventListener('submit', function(e) {
            if (e.target.tagName === 'FORM') {
                showLoading();
            }
        });
        
        // Interceptar clicks en enlaces para mostrar loading
        document.addEventListener('click', function(e) {
            if (e.target.tagName === 'A' && e.target.href && !e.target.href.includes('#')) {
                showLoading();
            }
        });
        
        // Función para validar formularios del lado del cliente
        function validateForm(form) {
            const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.classList.add('error');
                    isValid = false;
                } else {
                    input.classList.remove('error');
                }
            });
            
            return isValid;
        }
        
        // Aplicar validación a todos los formularios
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                if (!validateForm(this)) {
                    e.preventDefault();
                    alert('Por favor, completa todos los campos requeridos.');
                }
            });
        });
        
        // Función para confirmar acciones destructivas
        function confirmAction(message) {
            return confirm(message || '¿Estás seguro de que quieres realizar esta acción?');
        }
        
        // Aplicar confirmación a enlaces de eliminación
        document.querySelectorAll('a[data-confirm]').forEach(link => {
            link.addEventListener('click', function(e) {
                if (!confirmAction(this.dataset.confirm)) {
                    e.preventDefault();
                }
            });
        });
        
        // Función para mostrar notificaciones
        function showNotification(message, type = 'info') {
            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
                <span>${message}</span>
                <button onclick="this.parentElement.remove()">&times;</button>
            `;
            
            document.body.appendChild(notification);
            
            // Auto-remover después de 5 segundos
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 5000);
        }
        
        // Exponer funciones globalmente
        window.App = {
            showLoading,
            hideLoading,
            validateForm,
            confirmAction,
            showNotification
        };
    </script>
    
    <!-- Estilos para notificaciones -->
    <style>
        .notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 5px;
            color: white;
            z-index: 1000;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 400px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            animation: slideIn 0.3s ease-out;
        }
        
        .notification-success {
            background-color: #28a745;
        }
        
        .notification-error {
            background-color: #dc3545;
        }
        
        .notification-info {
            background-color: #17a2b8;
        }
        
        .notification button {
            background: none;
            border: none;
            color: white;
            font-size: 18px;
            cursor: pointer;
            margin-left: auto;
        }
        
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        
        .loading {
            position: relative;
        }
        
        .loading::after {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255,255,255,0.8);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .loading::before {
            content: 'Cargando...';
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 10000;
            background: #333;
            color: white;
            padding: 10px 20px;
            border-radius: 5px;
        }
        
        .error {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }
    </style>
</body>
</html>