<?php
$current_page = basename($_SERVER['PHP_SELF'] ?? '');
if (!in_array($current_page, ['login.php','admin_login.php','principal_login.php','register.php'], true)):
?>
  </div>
</main>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('table.mobile-card-table').forEach(function (table) {
    var labels = Array.from(table.querySelectorAll('thead th')).map(function (th) {
      return th.textContent.trim();
    });

    if (!labels.length) return;

    table.querySelectorAll('tbody tr').forEach(function (row) {
      Array.from(row.children).forEach(function (cell, idx) {
        if (!cell.hasAttribute('data-label')) {
          cell.setAttribute('data-label', labels[idx] || 'Value');
        }
      });
    });
  });
});
</script>
</body>
</html>
