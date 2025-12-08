@extends('layouts.location')

@section('title', 'Settings')

@push('css')
    <link rel="stylesheet" href="{{ asset('css/setting_integration.css') }}">
    <style>
        .settings-container {
            max-width: 1300px;
        }

        .settings-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .modules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .module-card {
            background: #fff;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
            cursor: pointer;
            position: relative;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .module-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .module-card.active {
            border-color: #10b981;
            background: #f0fdf4;
        }

        .stats-badge {
            position: absolute;
            top: 12px;
            right: 12px;
            background: #10b981;
            color: white;
            font-size: 0.75rem;
            padding: 0.25rem 0.65rem;
            border-radius: 9999px;
        }

        .icon-wrapper {
            width: 56px;
            height: 56px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }

        .icon-wrapper.events {
            background: #ede9fe;
        }

        .icon-wrapper.service {
            background: #fce7f3;
        }

        .icon-wrapper.records {
            background: #dbeafe;
        }

        .module-icon {
            font-size: 1.75rem;
        }

        .icon-wrapper.events .module-icon {
            color: #8b5cf6;
        }

        .icon-wrapper.service .module-icon {
            color: #ec4899;
        }

        .icon-wrapper.records .module-icon {
            color: #3b82f6;
        }

        .module-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin: 0 0 0.5rem 0;
        }

        .module-desc {
            color: #6b7280;
            font-size: 0.875rem;
            margin: 0;
        }

        .module-arrow {
            position: absolute;
            bottom: 1.5rem;
            right: 1.5rem;
            color: #9ca3af;
        }

        .module-content-area {
            display: none;
            background: #fff;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.08);
        }

        .module-content-area.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
@endpush

@section('content')

    <div class="settings-container">

        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-0">Settings</h1>
                <p class="text-muted mb-0">Configure your account preferences and integrations</p>
            </div>
            <div> @include('button.index') </div>
        </div>

        <!-- Modules Grid -->




{{--
        <div id="events-content" class="module-content-area active">
            @include('locations.churchmatrix.events.index')
        </div>

        <div id="service-times-content" class="module-content-area">
            @include('locations.churchmatrix.service_times.index')
        </div>

        <div id="records-content" class="module-content-area">
            @include('locations.churchmatrix.records.index')
        </div> --}}

    </div>

    </div>

@endsection

@push('script')
    <script>
        let currenttab = 'events';
        let currentPageUrl = `{{ route('locations.churchmatrix.events.index') }}`;
        let target = 'events-tab'
        let selected_campus = '';
        let nextPage = null;
        let prevPage = null;



    </script>
    <script>
        $(document).ready(function(){
            selected_campus = $('#campus_id').val();

        })

        // function fetchEvents(currentPageUrl) {
        //     $.ajax({
        //         url: url,
        //         type: "GET",
        //         success: function(res) {
        //             let $tbody = $('#events-table tbody');
        //             $tbody.empty();

        //             if(res.data.length === 0){
        //                 $tbody.append('<tr><td colspan="3" class="text-center">No events found</td></tr>');
        //             } else {
        //                 res.data.forEach(event => {
        //                     $tbody.append(`
        //                         <tr class="border-start border-4 border-primary" data-event-id="${event.id}">
        //                             <td>
        //                                 <div class="d-flex align-items-center">
        //                                     <h6 class="mb-0 fw-bold">${event.name}</h6>
        //                                 </div>
        //                             </td>
        //                             <td class="text-muted"><i class="fas fa-calendar me-1"></i>${event.created_at}</td>
        //                             <td class="text-center">
        //                                 <button class="btn btn-sm rounded-circle shadow-sm me-2"
        //                                     onclick="editEvent(${event.id}, '${event.name.replace(/'/g, "\\'")}')">
        //                                     <i class="fas fa-edit"></i>
        //                                 </button>
        //                                 <button class="btn btn-sm rounded-circle shadow-sm"
        //                                     onclick="deleteEvent(${event.id}, '${event.name.replace(/'/g, "\\'")}')">
        //                                     <i class="fas fa-trash"></i>
        //                                 </button>
        //                             </td>
        //                         </tr>
        //                     `);
        //                 });
        //             }

        //             // Update pagination
        //             nextPage = res.next ? res.next : null;
        //             prevPage = res.prev ? res.prev : null;

        //             $('#next-btn').prop('disabled', !nextPage);
        //             $('#prev-btn').prop('disabled', !prevPage);
        //         },
        //         error: function() {
        //             toastr.error("Failed to load events");
        //         }
        //     });
        // }

        // $('#next-btn').on('click', function() {
        //     if(nextPage) fetchEvents(currentPageUrl + '?page=' + nextPage);
        // });

        // $('#prev-btn').on('click', function() {
        //     if(prevPage) fetchEvents(currentPageUrl + '?page=' + prevPage);
        // });

        // // Initial fetch
        // $(document).ready(function() {
        //     fetchEvents();
        // });
    </script>


@endpush
