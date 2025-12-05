<!-- Add/Edit Record Modal -->
<div class="modal fade" id="recordModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg glass-effect">
            <div class="modal-header border-0 py-4 px-4 modal-gradient">
                <h4 class="modal-title fw-bold d-flex align-items-center">
                    <i class="fas fa-calendar-plus me-3"></i>
                    <span id="modalTitle">Add New Record</span>
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <form id="recordForm">
                <div class="modal-body px-5 py-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="fw-bold text-dark">Category ID</label>
                            <input type="number" name="category_id" class="form-control rounded-pill" required>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold text-dark">Campus ID</label>
                            <input type="number" name="campus_id" class="form-control rounded-pill" required>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold text-dark">Service Time ID</label>
                            <input type="number" name="service_time_id" class="form-control rounded-pill" required>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold text-dark">Service Date & Time</label>
                            <input type="datetime-local" name="service_date_time" class="form-control rounded-pill" required>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold text-dark">Service Timezone</label>
                            <input type="text" name="service_timezone" class="form-control rounded-pill">
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold text-dark">Value</label>
                            <input type="number" name="value" class="form-control rounded-pill">
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold text-dark">Replaces?</label>
                            <select name="replaces" class="form-select rounded-pill">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold text-dark">Event ID</label>
                            <input type="number" name="event_id" class="form-control rounded-pill">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pb-4 px-5">
                    <button type="button" class="btn btn-secondary rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 shadow-lg btn-animated">
                        <i class="fas fa-save me-2"></i> <span>Save</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Add Record Button -->
<button class="btn btn-lg btn-primary shadow-lg rounded-pill px-4" id="addRecordBtn">
    <i class="fas fa-plus me-2"></i>Add New Record
</button>

<!-- Records Table -->
<div class="table-responsive mt-4">
    <table class="table table-hover align-middle mb-0" id="recordsTable">
        <thead class="bg-light">
            <tr>
                <th>ID</th>
                <th>Category ID</th>
                <th>Campus ID</th>
                <th>Service Time ID</th>
                <th>Service Date & Time</th>
                <th>Timezone</th>
                <th>Value</th>
                <th>Replaces?</th>
                <th>Event ID</th>
                <th class="text-center">Actions</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

@push('js')
<script>

const recordModal = new bootstrap.Modal(document.getElementById('recordModal'));
const recordForm = document.getElementById('recordForm');
const modalTitle = document.getElementById('modalTitle');
const recordsTableBody = document.querySelector('#recordsTable tbody');

let editRow = null;
let recordId = 1;

document.getElementById('addRecordBtn').addEventListener('click', () => {
    modalTitle.textContent = 'Add New Record';
    recordForm.reset();
    editRow = null;
    recordModal.show();
});

recordForm.addEventListener('submit', (e) => {
    e.preventDefault();

    const formData = new FormData(recordForm);
    const record = {
        id: editRow ? editRow.dataset.id : recordId++,
        category_id: formData.get('category_id'),
        campus_id: formData.get('campus_id'),
        service_time_id: formData.get('service_time_id'),
        service_date_time: formData.get('service_date_time'),
        service_timezone: formData.get('service_timezone'),
        value: formData.get('value'),
        replaces: formData.get('replaces') === "1" ? 'Yes' : 'No',
        event_id: formData.get('event_id')
    };

    if (editRow) {
        editRow.innerHTML = createRowHTML(record);
    } else {
        const tr = document.createElement('tr');
        tr.dataset.id = record.id;
        tr.innerHTML = createRowHTML(record);
        recordsTableBody.appendChild(tr);
    }

    recordModal.hide();
});

function createRowHTML(record) {
    return `
        <td>${record.id}</td>
        <td>${record.category_id}</td>
        <td>${record.campus_id}</td>
        <td>${record.service_time_id}</td>
        <td>${record.service_date_time}</td>
        <td>${record.service_timezone}</td>
        <td>${record.value}</td>
        <td>${record.replaces}</td>
        <td>${record.event_id}</td>
        <td class="text-center">
            <button class="btn btn-sm btn-warning me-2 editBtn">Edit</button>
            <button class="btn btn-sm btn-danger deleteBtn">Delete</button>
        </td>
    `;
}

recordsTableBody.addEventListener('click', (e) => {
    const row = e.target.closest('tr');
    if (!row) return;

    if (e.target.closest('.editBtn')) {
        editRow = row;
        modalTitle.textContent = 'Edit Record';
        const cells = row.children;
        recordForm.category_id.value = cells[1].textContent;
        recordForm.campus_id.value = cells[2].textContent;
        recordForm.service_time_id.value = cells[3].textContent;
        recordForm.service_date_time.value = cells[4].textContent;
        recordForm.service_timezone.value = cells[5].textContent;
        recordForm.value.value = cells[6].textContent;
        recordForm.replaces.value = cells[7].textContent === 'Yes' ? "1" : "0";
        recordForm.event_id.value = cells[8].textContent;
        recordModal.show();
    }

    if (e.target.closest('.deleteBtn')) {
        if (confirm('Are you sure you want to delete this record?')) {
            row.remove();
        }
    }
});

</script>
@endpush