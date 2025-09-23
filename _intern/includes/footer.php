    </main>

    <script src="../js/script.js"></script>
    <script src="js/backend.js"></script>
    <script>
        // Sidebar toggle functionality
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebarOverlay');
            const isActive = sidebar.classList.contains('active');
            
            if (isActive) {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            } else {
                sidebar.classList.add('active');
                overlay.classList.add('active');
            }
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.querySelector('.sidebar-toggle');
            const overlay = document.getElementById('sidebarOverlay');
            
            if (window.innerWidth <= 1023 && sidebar.classList.contains('active')) {
                if (!sidebar.contains(event.target) && !toggle.contains(event.target)) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                }
            }
        });
        
        // Close sidebar on ESC key
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape' && window.innerWidth <= 1023) {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                if (sidebar.classList.contains('active')) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                }
            }
        });
        
        // Hide loading overlay
        window.addEventListener('load', function() {
            const overlay = document.getElementById('loadingOverlay');
            if (overlay) {
                overlay.style.opacity = '0';
                setTimeout(() => overlay.style.display = 'none', 300);
            }
        });
        
        // Logout function
        function logout() {
            if (confirm('Estàs segur que vols tancar la sessió?')) {
                window.location.href = 'logout.php';
            }
        }

        // Smooth transitions for responsive changes
        let resizeTimer;
        window.addEventListener('resize', function() {
            clearTimeout(resizeTimer);
            resizeTimer = setTimeout(function() {
                const sidebar = document.getElementById('sidebar');
                const overlay = document.getElementById('sidebarOverlay');
                
                if (window.innerWidth >= 1024) {
                    sidebar.classList.remove('active');
                    overlay.classList.remove('active');
                }
            }, 250);
        });
    </script>
</body>
</html>