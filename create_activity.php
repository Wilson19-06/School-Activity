<?php
include 'auth_check.php';
include 'config.php';

if (!eams_is_staff()) {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    exit;
}

include 'includes/header.php';
?>

  <div class="card p-4 my-2">
    <h3 class="mb-4">Fill in Activity Details</h3>

    <form action="save_activity.php" method="POST">
      <div class="row">
        <div class="col-md-6 mb-3 input-group">
          <span class="input-group-text">Title</span>
          <input type="text" name="title" class="form-control" placeholder="Activity name" required>
        </div>

        <div class="col-md-6 mb-3 input-group">
          <span class="input-group-text">Teacher</span>
          <input type="text" name="teacher" class="form-control" placeholder="Teacher in charge" required>
        </div>

        <div class="col-md-6 mb-3 input-group">
          <span class="input-group-text">Type</span>
          <input type="text" name="activity_type" class="form-control" placeholder="Activity type">
        </div>

        <div class="col-md-6 mb-3 input-group">
          <span class="input-group-text">Location</span>
          <input type="text" name="location" class="form-control" placeholder="Location">
        </div>

        <div class="col-md-6 mb-3 input-group">
          <span class="input-group-text">Date</span>
          <input type="date" name="date" class="form-control" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label">Visibility</label>
          <select name="visibility" class="form-select">
            <option value="Public" selected>Public</option>
            <option value="Private">Private</option>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label>Objective</label>
        <textarea name="objective" class="form-control" rows="2"></textarea>
      </div>

      <div class="mb-3">
        <label>Content</label>
        <textarea name="content" class="form-control" rows="3"></textarea>
      </div>

      <div class="mb-3">
        <label>Follow Up</label>
        <textarea name="follow_up" class="form-control" rows="2"></textarea>
      </div>

      <input type="hidden" name="approved_status" value="<?= ($_SESSION['role'] === 'principal') ? 'Approved' : 'Pending' ?>">

      <div class="card bg-white text-dark mt-4">
        <div class="card-body">
          <h5 class="card-title">Select Participating Students</h5>

          <div class="input-group mb-3">
            <input type="text" id="stuSearch" class="form-control" placeholder="Enter student ID or name keyword...">
            <button class="btn btn-outline-primary" type="button" id="btnSearch">Search</button>
          </div>

          <div class="table-responsive" style="max-height:320px; overflow:auto;">
            <table class="table table-sm table-bordered align-middle text-dark" id="resultTable">
              <thead class="table-primary">
                <tr>
                  <th style="width:45px;"></th>
                  <th>Student ID</th>
                  <th>English Name</th>
                  <th>Chinese Name</th>
                  <th>Class</th>
                  <th>Gender</th>
                </tr>
              </thead>
              <tbody></tbody>
            </table>
          </div>
        </div>
      </div>

      <div class="mt-4">
        <button type="submit" class="btn btn-primary btn-lg w-100">Save Activity</button>
      </div>
    </form>
  </div>

<script>
const searchBox = document.getElementById('stuSearch');
const btnSearch = document.getElementById('btnSearch');
const tbody = document.querySelector('#resultTable tbody');

function render(rows) {
  tbody.innerHTML = rows.map(function (r) {
    return `
      <tr>
        <td><input type="checkbox" name="selected_students[]" value="${r.id}"></td>
        <td>${r.student_id}</td>
        <td>${r.name_en ?? ''}</td>
        <td>${r.name_cn ?? ''}</td>
        <td>${r.class ?? ''}</td>
        <td>${r.gender ?? ''}</td>
      </tr>
    `;
  }).join('');
}

function doSearch() {
  const q = searchBox.value.trim();
  if (!q) {
    tbody.innerHTML = '';
    return;
  }

  fetch('search_students.php?q=' + encodeURIComponent(q))
    .then(function (r) { return r.json(); })
    .then(render)
    .catch(function (err) { console.error('Search error', err); });
}

btnSearch.addEventListener('click', doSearch);
searchBox.addEventListener('keyup', function (e) {
  if (e.key === 'Enter') {
    doSearch();
  } else if (e.target.value.length === 1) {
    doSearch();
  }
});
</script>

<?php include 'includes/footer.php'; ?>