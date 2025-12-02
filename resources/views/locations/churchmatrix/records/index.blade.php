<div class="row">
    <div class="col-sm-12">
        <div class="page-title-box">
            <div class="float-right">
                <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#addEventModal">
                    <i class="fas fa-plus"></i> Add Record
                </button>
            </div>
            <h4 class="page-title">Records List</h4>
        </div>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">All Records</h5>
    </div>

    <div class="card-body">

        @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <table class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>category_id</th>
                    <th>campus_id</th>
                    <th>service_time_id</th>
                    <th>service_date_time</th>
                    <th>service_timezone</th>
                    <th>value</th>
                    <th>replaces</th>
                    <th>event_id</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>1</td>
                    <td>C001</td>
                    <td>101</td>
                    <td>ST001</td>
                    <td>2025-12-01 09:00</td>
                    <td>PST</td>
                    <td>100</td>
                    <td>None</td>
                    <td>E001</td>
                </tr>
                <tr>
                    <td>2</td>
                    <td>C002</td>
                    <td>102</td>
                    <td>ST002</td>
                    <td>2025-12-03 11:00</td>
                    <td>EST</td>
                    <td>200</td>
                    <td>E001</td>
                    <td>E002</td>
                </tr>
                <tr>
                    <td>3</td>
                    <td>C003</td>
                    <td>103</td>
                    <td>ST003</td>
                    <td>2025-12-05 14:00</td>
                    <td>CST</td>
                    <td>150</td>
                    <td>E002</td>
                    <td>E003</td>
                </tr>
                <tr>
                    <td>4</td>
                    <td>C004</td>
                    <td>104</td>
                    <td>ST004</td>
                    <td>2025-12-07 18:00</td>
                    <td>MST</td>
                    <td>250</td>
                    <td>None</td>
                    <td>E004</td>
                </tr>
            </tbody>
        </table>


    </div>
</div>

@include('records.add')