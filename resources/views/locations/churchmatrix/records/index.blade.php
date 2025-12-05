<div class="container-fluid px-4 py-3">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1 text-dark fw-bold">
                <i class="text-primary me-3"></i>Service Records
            </h2>
            <p class="text-muted mb-0">Manage all service records</p>
        </div>

       <button class="btn btn-lg btn-primary shadow-lg rounded-pill px-4"
        data-bs-toggle="modal"
        data-bs-target="#recordModal">
    <i class="fas fa-plus me-2"></i>Add New Record
</button>

    </div>

    <div class="card border-0 shadow-lg rounded-4 overflow-hidden"
        style="background: rgba(255,255,255,0.95); backdrop-filter: blur(10px);">

        <div class="card-header bg-gradient-primary text-white border-0 py-4">
            <h4 class="mb-0 fw-bold">
                <i class="me-3"></i>All Service Records
            </h4>
        </div>

        <div class="card-body p-0">

            <div id="recordsTableContainer">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>ID</th>
                            <th>Category ID</th>
                            <th>Campus ID</th>
                            <th>Service Time ID</th>
                            <th>Service Date Time</th>
                            <th>Service Timezone</th>
                            <th>Value</th>
                            <th>Replaces?</th>
                            <th>Event ID</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        <!-- Example Row -->
                        <tr class="border-start border-4 border-primary" style="transition: all 0.3s;">
                            <td>1</td>
                            <td>1</td>
                            <td>1</td>
                            <td>1</td>
                            <td>2012-03-27 17:00</td>
                            <td>Central Time (US & Canada)</td>
                            <td>20</td>
                            <td>Yes</td>
                            <td>1</td>
                            <td class="text-center">
                                <button class="btn btn-sm rounded-circle shadow-sm me-2">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button class="btn btn-sm rounded-circle shadow-sm">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>

                        <!-- Add more rows as needed -->

                    </tbody>
                </table>
            </div>

        </div>
    </div>
</div>


