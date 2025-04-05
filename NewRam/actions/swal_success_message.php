<?php
if (isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            title: 'Success!',
            text: "<?php echo $_SESSION['success']; ?>",
            icon: 'success',
            confirmButtonColor: '#28a745',
            confirmButtonText: 'OK'
        });
    </script>
    <?php unset($_SESSION['success']); ?> <!-- Clear message after displaying -->
<?php endif; ?>
