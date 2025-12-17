@extends('layouts.location')

@section('title', 'Settings')


@section('content')
    @php($is_show = true)
    <div class="settings-container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0">Settings</h1>
                <p class="text-muted mb-0">Configure your account preferences and integrations</p>
            </div>
            <div> @include('button.index', ['churchmatrixbtn' => $is_show]) </div>
        </div>

        @if ($is_show)
            @include('components.church-matrix-keys')
            {{-- @if ($user->role == 0)
                <div class="col-lg-12">
                    <div class="card-modern">
                        <div class="card-header-modern warning  bg-warning">
                            <h5>Mappings</h5>
                        </div>
                        <div class="card-body-modern" id="user-campus-form-wrapper">


                        </div>
                    </div>
                </div>
            @endif --}}
        @else
            <div class="alert alert-warning d-flex align-items-center" role="alert">
                <i class="fa-solid fa-triangle-exclamation me-2"></i>
                <div>
                    <strong>Access Restricted</strong><br>
                    No campus has been added to your location.
                    Please contact your admin.
                </div>
            </div>
        @endif

    </div>
@endsection

@push('script')
    @include('components.submit-form')
    <script>
        // $(document).ready(function() {
        //         @if ($user->role == 0)
        //             function getForm() {
        //                 $.get('{{ route('locations.churchmatrix.getUserCampusForm') }}', function(html) {
        //                     $('#user-campus-form-wrapper').html(html);
        //                 });
        //             }

        //             getForm();

        //             $(document).on('click', '.save-user-campus', function(e) {
        //                 e.preventDefault();
        //                 let $row = $(this).closest('tr');
        //                 let user_id = $row.data('user-id');
        //                 let campus_id = $row.find('.campus-select').val();

        //                 if (!campus_id) {
        //                     alert('Please select a campus.');
        //                     return;
        //                 }

        //                 $.post('{{ route('locations.churchmatrix.saveUserCampusAjax') }}', {
        //                     user_id: user_id,
        //                     campus_id: campus_id
        //                 }, function(res) {
        //                     if (res.success) {
        //                         alert(res.message);
        //                     }
        //                 });
        //             });

        //         });
        // @endif
    </script>
@endpush
