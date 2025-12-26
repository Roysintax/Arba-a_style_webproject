        </div>
    </div>
    
    <script>
        // Confirm delete
        function confirmDelete(message = 'Apakah Anda yakin ingin menghapus?') {
            return confirm(message);
        }
        
        // Image preview
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>
