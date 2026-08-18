<?php
include 'auth_check.php';
include 'config.php';
include 'includes/header.php';

if (!eams_is_staff()) {
    echo "<div class='alert alert-danger'>Access denied.</div>";
    include 'includes/footer.php';
    exit;
}

$activities = [];
$res = $conn->query('SELECT id, title, location, teacher, date FROM activities a WHERE ' . eams_visibility_sql('a') . ' ORDER BY date DESC');
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $activities[] = $row;
    }
}

$students = [];
$res2 = $conn->query('SELECT id, student_id, name_en, name_cn, class, gender FROM students ORDER BY class, student_id');
if ($res2) {
    while ($row = $res2->fetch_assoc()) {
        $students[] = $row;
    }
}
?>

<style>
.fb-template-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.type-selector {
    background: linear-gradient(135deg, #0b3954 0%, #087e8b 100%);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
}

.type-btn {
    background: rgba(255, 255, 255, 0.18);
    border: 2px solid rgba(255, 255, 255, 0.35);
    color: #fff;
    border-radius: 22px;
    padding: 10px 26px;
    margin: 0 8px;
    transition: all 0.25s ease;
}

.type-btn:hover,
.type-btn.active {
    background: #fff;
    color: #0b3954;
    transform: translateY(-2px);
}

.form-card {
    background: #fff;
    border-radius: 20px;
    padding: 28px;
    box-shadow: 0 12px 28px rgba(0, 0, 0, 0.1);
    margin-bottom: 28px;
}

.form-control,
.form-select {
    border-radius: 12px;
    border: 2px solid #e5e8ef;
    padding: 11px 14px;
}

.form-control:focus,
.form-select:focus {
    border-color: #087e8b;
    box-shadow: 0 0 0 0.2rem rgba(8, 126, 139, 0.18);
}

.student-selection {
    background: #f8fafc;
    border-radius: 14px;
    padding: 18px;
    margin-top: 20px;
    border: 2px dashed #d9dee8;
}

.student-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
    gap: 12px;
    margin-top: 12px;
    max-height: 340px;
    overflow: auto;
}

.student-item {
    background: #fff;
    border-radius: 12px;
    padding: 12px;
    border: 2px solid #e5e8ef;
    transition: all 0.2s ease;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 10px;
}

.student-item:hover {
    border-color: #087e8b;
}

.student-item.selected {
    background: #087e8b;
    color: #fff;
    border-color: #087e8b;
}

.student-item .meta {
    font-size: 0.85rem;
    opacity: 0.85;
}

.student-item.selected .meta {
    opacity: 0.95;
}

.preview-card {
    background: linear-gradient(135deg, #0b3954 0%, #087e8b 100%);
    border-radius: 20px;
    padding: 28px;
    color: #fff;
    box-shadow: 0 14px 32px rgba(0, 0, 0, 0.2);
    margin-top: 24px;
}

.preview-image {
    max-width: 100%;
    border-radius: 12px;
    margin-top: 16px;
    box-shadow: 0 8px 18px rgba(0, 0, 0, 0.25);
}

.btn-custom {
    background: linear-gradient(135deg, #0b3954 0%, #087e8b 100%);
    border: none;
    border-radius: 22px;
    padding: 10px 26px;
    color: #fff;
    font-weight: 600;
}

.btn-custom:hover {
    color: #fff;
    opacity: 0.92;
}

.btn-outline-custom {
    background: transparent;
    border: 2px solid #fff;
    color: #fff;
    border-radius: 22px;
    padding: 10px 26px;
    font-weight: 600;
}

.btn-outline-custom:hover {
    background: #fff;
    color: #0b3954;
}

.hidden {
    display: none;
}
</style>

<div class="fb-template-container">
    <div class="text-center mb-4">
        <h1 class="display-6 fw-bold text-primary">Facebook Activity Share Template</h1>
        <p class="lead text-muted">Create polished social media text and image previews for school activities.</p>
    </div>

    <div class="type-selector text-center">
        <h4 class="text-white mb-3">Template Type</h4>
        <button type="button" class="btn type-btn active" data-type="notice">Activity Notice</button>
        <button type="button" class="btn type-btn" data-type="award">Award Winners</button>
    </div>

    <div class="form-card">
        <form id="fbForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="activity_id" class="form-label fw-bold">Activity</label>
                    <select class="form-select" id="activity_id" name="activity_id" required>
                        <option value="">Select an activity...</option>
                        <?php foreach ($activities as $activity): ?>
                            <option value="<?= (int)$activity['id'] ?>"
                                    data-location="<?= htmlspecialchars($activity['location']) ?>"
                                    data-teacher="<?= htmlspecialchars($activity['teacher']) ?>"
                                    data-date="<?= htmlspecialchars($activity['date']) ?>">
                                <?= htmlspecialchars($activity['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-2 mb-3">
                    <label for="location" class="form-label fw-bold">Location</label>
                    <input type="text" class="form-control" id="location" name="location" readonly>
                </div>

                <div class="col-md-2 mb-3">
                    <label for="teacher" class="form-label fw-bold">Teacher</label>
                    <input type="text" class="form-control" id="teacher" name="teacher" readonly>
                </div>

                <div class="col-md-2 mb-3">
                    <label for="date" class="form-label fw-bold">Date</label>
                    <input type="date" class="form-control" id="date" name="date" readonly>
                </div>
            </div>

            <div class="mb-3">
                <label for="content" class="form-label fw-bold">Content</label>
                <textarea class="form-control" id="content" name="content" rows="5" placeholder="Write your post content..."></textarea>
            </div>

            <div class="mb-3">
                <label for="image" class="form-label fw-bold">Image (optional)</label>
                <div class="input-group">
                    <input type="file" class="form-control" id="image" name="image" accept="image/*" hidden>
                    <button type="button" class="btn btn-outline-secondary" id="chooseImageBtn">Choose File</button>
                    <span class="input-group-text" id="fileLabel">No file selected</span>
                </div>
            </div>

            <div id="studentSelection" class="student-selection hidden">
                <h5 class="mb-3">Select Awarded Students</h5>
                <div class="mb-2">
                    <input type="text" id="studentSearch" class="form-control" placeholder="Search student by name, class, or ID...">
                </div>
                <div class="student-grid" id="studentGrid">
                    <?php foreach ($students as $student): ?>
                        <?php
                        $displayName = $student['name_en'] ?: $student['name_cn'];
                        $meta = trim(($student['class'] ?: '-') . ' | ' . ($student['gender'] ?: '-'));
                        ?>
                        <div class="student-item"
                             data-student-id="<?= (int)$student['id'] ?>"
                             data-search="<?= strtolower(htmlspecialchars(($student['student_id'] ?? '') . ' ' . ($student['name_en'] ?? '') . ' ' . ($student['name_cn'] ?? '') . ' ' . ($student['class'] ?? '') . ' ' . ($student['gender'] ?? ''))) ?>"
                             data-name="<?= htmlspecialchars($displayName) ?>"
                             data-student-code="<?= htmlspecialchars($student['student_id']) ?>"
                             data-meta="<?= htmlspecialchars($meta) ?>">
                            <div>
                                <div class="fw-bold"><?= htmlspecialchars($displayName) ?> <span class="badge bg-info"><?= htmlspecialchars($student['student_id']) ?></span></div>
                                <div class="meta"><?= htmlspecialchars($meta) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <?php if (empty($students)): ?>
                    <p class="text-muted text-center mt-3 mb-0">No students found.</p>
                <?php endif; ?>
            </div>

            <div class="text-center mt-4">
                <button type="submit" class="btn btn-custom">Generate Preview</button>
            </div>
        </form>
    </div>

    <div id="previewCard" class="preview-card hidden">
        <h3 class="text-center mb-4">Preview</h3>
        <div id="previewContent"></div>
        <div class="text-center mt-4">
            <button type="button" class="btn btn-outline-custom" id="copyTextBtn">Copy Text</button>
            <button type="button" class="btn btn-outline-custom" id="exportImageBtn">Export Image</button>
        </div>
    </div>
</div>

<script>
let currentType = 'notice';
let selectedStudents = [];
let selectedImageDataUrl = '';

const typeButtons = document.querySelectorAll('.type-btn');
const studentSelection = document.getElementById('studentSelection');
const studentItems = document.querySelectorAll('.student-item');
const studentSearch = document.getElementById('studentSearch');

const activitySelect = document.getElementById('activity_id');
const locationInput = document.getElementById('location');
const teacherInput = document.getElementById('teacher');
const dateInput = document.getElementById('date');
const contentInput = document.getElementById('content');

const previewCard = document.getElementById('previewCard');
const previewContent = document.getElementById('previewContent');

const imageInput = document.getElementById('image');
const chooseImageBtn = document.getElementById('chooseImageBtn');
const fileLabel = document.getElementById('fileLabel');

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value;
    return div.innerHTML;
}

function getSelectedActivityTitle() {
    const option = activitySelect.options[activitySelect.selectedIndex];
    return option && option.value ? option.text : '';
}

function getSelectedStudentDetails() {
    const details = [];
    selectedStudents.forEach(function (studentId) {
        const item = document.querySelector('.student-item[data-student-id="' + studentId + '"]');
        if (item) {
            details.push({
                name: item.dataset.name,
                code: item.dataset.studentCode,
                meta: item.dataset.meta
            });
        }
    });
    return details;
}

function buildPostText() {
    const title = getSelectedActivityTitle();
    const location = locationInput.value;
    const teacher = teacherInput.value;
    const date = dateInput.value;
    const body = contentInput.value.trim();

    if (currentType === 'notice') {
        return [
            title,
            '',
            'Location: ' + location,
            'Teacher: ' + teacher,
            'Date: ' + date,
            '',
            body,
            '',
            'Educational Activity Management System'
        ].join('\n');
    }

    const winners = getSelectedStudentDetails();
    const winnerLines = winners.length
        ? winners.map(function (w) { return '- ' + w.name + ' (' + w.code + ') | ' + w.meta; }).join('\n')
        : '- No students selected';

    return [
        title + ' - Award Winners',
        '',
        'Location: ' + location,
        'Teacher: ' + teacher,
        'Date: ' + date,
        '',
        body,
        '',
        'Award Winners:',
        winnerLines,
        '',
        'Educational Activity Management System'
    ].join('\n');
}

function updatePreview() {
    const title = getSelectedActivityTitle();
    const location = locationInput.value;
    const teacher = teacherInput.value;
    const date = dateInput.value;
    const body = contentInput.value.trim();

    let html = '';

    if (currentType === 'notice') {
        html = `
            <div class="text-center">
                <h2 class="mb-3">${escapeHtml(title || 'Activity Notice')}</h2>
                <p class="mb-1"><strong>Location:</strong> ${escapeHtml(location)}</p>
                <p class="mb-1"><strong>Teacher:</strong> ${escapeHtml(teacher)}</p>
                <p class="mb-3"><strong>Date:</strong> ${escapeHtml(date)}</p>
                <p class="mb-3">${escapeHtml(body).replace(/\n/g, '<br>')}</p>
                <p class="mb-0"><strong>Educational Activity Management System</strong></p>
            </div>
        `;
    } else {
        const winners = getSelectedStudentDetails();
        const winnersHtml = winners.length
            ? winners.map(function (w) {
                return `<p class="mb-1">- ${escapeHtml(w.name)} (${escapeHtml(w.code)}) | ${escapeHtml(w.meta)}</p>`;
            }).join('')
            : '<p class="mb-1">- No students selected</p>';

        html = `
            <div class="text-center">
                <h2 class="mb-3">${escapeHtml(title || 'Award Winners')}</h2>
                <p class="mb-1"><strong>Location:</strong> ${escapeHtml(location)}</p>
                <p class="mb-1"><strong>Teacher:</strong> ${escapeHtml(teacher)}</p>
                <p class="mb-3"><strong>Date:</strong> ${escapeHtml(date)}</p>
                <p class="mb-3">${escapeHtml(body).replace(/\n/g, '<br>')}</p>
                <div class="mb-3">
                    <h5>Award Winners</h5>
                    ${winnersHtml}
                </div>
                <p class="mb-0"><strong>Educational Activity Management System</strong></p>
            </div>
        `;
    }

    previewContent.innerHTML = html;

    if (selectedImageDataUrl) {
        const img = document.createElement('img');
        img.className = 'preview-image';
        img.src = selectedImageDataUrl;
        img.alt = 'Selected image preview';
        previewContent.appendChild(img);
    }
}

function refreshStudentSelectionUI() {
    studentItems.forEach(function (item) {
        const id = item.dataset.studentId;
        if (selectedStudents.includes(id)) {
            item.classList.add('selected');
        } else {
            item.classList.remove('selected');
        }
    });
}

function loadTemplate(type) {
    if (type === 'notice') {
        contentInput.value = [
            'We are excited to announce this activity.',
            '',
            'Highlights:',
            '- Engaging learning experience',
            '- Team participation',
            '- Positive school spirit'
        ].join('\n');
    } else {
        contentInput.value = [
            'Congratulations to all awarded students.',
            '',
            'Thank you for your dedication and excellent performance.'
        ].join('\n');
    }
}

activitySelect.addEventListener('change', function () {
    const option = activitySelect.options[activitySelect.selectedIndex];
    if (option && option.value) {
        locationInput.value = option.dataset.location || '';
        teacherInput.value = option.dataset.teacher || '';
        dateInput.value = option.dataset.date || '';
    } else {
        locationInput.value = '';
        teacherInput.value = '';
        dateInput.value = '';
    }
    updatePreview();
});

contentInput.addEventListener('input', updatePreview);

chooseImageBtn.addEventListener('click', function () {
    imageInput.click();
});

imageInput.addEventListener('change', function (event) {
    const file = event.target.files[0];
    if (!file) {
        selectedImageDataUrl = '';
        fileLabel.textContent = 'No file selected';
        updatePreview();
        return;
    }

    fileLabel.textContent = file.name.length > 28 ? (file.name.slice(0, 28) + '...') : file.name;

    const reader = new FileReader();
    reader.onload = function (e) {
        selectedImageDataUrl = e.target.result;
        updatePreview();
    };
    reader.readAsDataURL(file);
});

typeButtons.forEach(function (btn) {
    btn.addEventListener('click', function () {
        typeButtons.forEach(function (b) { b.classList.remove('active'); });
        btn.classList.add('active');
        currentType = btn.dataset.type;

        if (currentType === 'award') {
            studentSelection.classList.remove('hidden');
        } else {
            studentSelection.classList.add('hidden');
            selectedStudents = [];
            refreshStudentSelectionUI();
        }

        loadTemplate(currentType);
        updatePreview();
    });
});

studentItems.forEach(function (item) {
    item.addEventListener('click', function () {
        const id = item.dataset.studentId;
        if (selectedStudents.includes(id)) {
            selectedStudents = selectedStudents.filter(function (x) { return x !== id; });
        } else {
            selectedStudents.push(id);
        }
        refreshStudentSelectionUI();
        updatePreview();
    });
});

if (studentSearch) {
    studentSearch.addEventListener('input', function () {
        const kw = studentSearch.value.trim().toLowerCase();
        studentItems.forEach(function (item) {
            const text = item.dataset.search || '';
            item.style.display = text.includes(kw) ? '' : 'none';
        });
    });
}

document.getElementById('fbForm').addEventListener('submit', function (e) {
    e.preventDefault();
    updatePreview();
    previewCard.classList.remove('hidden');
});

document.getElementById('copyTextBtn').addEventListener('click', function () {
    const text = buildPostText();
    navigator.clipboard.writeText(text).then(function () {
        alert('Text copied to clipboard.');
    });
});

document.getElementById('exportImageBtn').addEventListener('click', function () {
    const title = getSelectedActivityTitle() || (currentType === 'notice' ? 'Activity Notice' : 'Award Winners');
    const location = locationInput.value;
    const teacher = teacherInput.value;
    const date = dateInput.value;
    const body = contentInput.value.trim();

    const canvas = document.createElement('canvas');
    const ctx = canvas.getContext('2d');
    const width = 1200;
    const height = 900;
    canvas.width = width;
    canvas.height = height;

    const gradient = ctx.createLinearGradient(0, 0, width, height);
    gradient.addColorStop(0, '#0b3954');
    gradient.addColorStop(1, '#087e8b');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, width, height);

    ctx.strokeStyle = 'rgba(255,255,255,0.35)';
    ctx.lineWidth = 3;
    ctx.strokeRect(26, 26, width - 52, height - 52);

    ctx.fillStyle = '#ffffff';
    ctx.textAlign = 'center';
    ctx.font = 'bold 48px Arial';
    ctx.fillText(title, width / 2, 90);

    ctx.font = '24px Arial';
    ctx.fillText('Location: ' + location, width / 2, 145);
    ctx.fillText('Teacher: ' + teacher, width / 2, 180);
    ctx.fillText('Date: ' + date, width / 2, 215);

    ctx.textAlign = 'left';
    ctx.font = '20px Arial';
    const maxWidth = width - 140;
    let y = 275;

    body.split('\n').forEach(function (line) {
        let current = '';
        for (let i = 0; i < line.length; i++) {
            const test = current + line[i];
            if (ctx.measureText(test).width > maxWidth && current !== '') {
                ctx.fillText(current, 70, y);
                y += 30;
                current = line[i];
            } else {
                current = test;
            }
        }
        if (current) {
            ctx.fillText(current, 70, y);
            y += 30;
        } else {
            y += 30;
        }
    });

    if (currentType === 'award') {
        y += 15;
        ctx.font = 'bold 24px Arial';
        ctx.fillText('Award Winners:', 70, y);
        y += 34;
        ctx.font = '20px Arial';
        const winners = getSelectedStudentDetails();
        if (winners.length === 0) {
            ctx.fillText('- No students selected', 80, y);
            y += 28;
        } else {
            winners.forEach(function (w) {
                ctx.fillText('- ' + w.name + ' (' + w.code + ') | ' + w.meta, 80, y);
                y += 28;
            });
        }
    }

    ctx.textAlign = 'center';
    ctx.font = 'bold 22px Arial';
    ctx.fillText('Educational Activity Management System', width / 2, height - 55);

    canvas.toBlob(function (blob) {
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = 'facebook_template_' + Date.now() + '.png';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
});

loadTemplate('notice');
updatePreview();
</script>

<?php include 'includes/footer.php'; ?>