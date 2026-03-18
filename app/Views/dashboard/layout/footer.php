<footer class="mt-5 py-4 border-top bg-white">
    <div class="container-fluid px-4 text-center text-muted small">
        &copy; 2026 Pharos Education. All rights reserved.
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" integrity="sha384-geWF76RCwLtnZ8qwWowPQNguL3RmwHVBC9FhGdlKrxdiJJigb/j/68SIy3Te4Bkz" crossorigin="anonymous"></script>
<script>
    $('#bookingForm').submit(function(e) {
            e.preventDefault();
            let btn = $(this).find('button');
            btn.prop('disabled', true).text('Booking...');
            $.post('<?= base_url("dashboard/bookAppointment") ?>', $(this).serialize(), function(res) {
                alert(res.msg);
                location.reload();
            }, 'json');
        });
</script>
</body>
</html>