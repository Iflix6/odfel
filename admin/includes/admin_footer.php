<!-- Page content ends here -->
</div>
    
    <script>
        function toggleAdminUserMenu() {
            document.getElementById('adminUserDropdown').classList.toggle('show');
        }
        
        // Close dropdown when clicking outside
        window.addEventListener('click', function(e) {
            if (!e.target.matches('.admin-user-menu button') && !e.target.matches('.admin-user-menu i')) {
                document.getElementById('adminUserDropdown').classList.remove('show');
            }
        });
    </script>
</body>
</html>
