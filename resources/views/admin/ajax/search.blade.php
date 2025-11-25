{{ $users }}
<div class="col-md-12">
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="datatable" class="table">
                    <thead class="thead-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            @if (auth()->user()->role == 0)
                                <th>API Key</th>
                                <th>Status</th>
                            @endif
                            <th class="text-right">Action</th>
                        </tr>
                        <!--end tr-->
                    </thead>
                    <tbody id="mainbody">
                        @foreach ($users as $user)
                            <tr>
                                <td>{{ $user->contactName ?? 'N/A' }}</td>
                                <td>{{ $user->email ?? 'N/A' }}</td>
                                <td>{{ $user->phone ?? 'N/A' }}</td>
                                @if (auth()->user()->role == 0)
                                    <td>{{ '****' . substr($user->ghl_api_key, -4) }}</td>
                                    <td>
                                        @if ($user->is_active == 1)
                                            <a href="'.route('user.is-active', $row->id).'"
                                                onclick="event.preventDefault(); deleteMsg('{{ route('user.is-active', $user->id) }}')"><i
                                                    class="fas fa-cancel-alt text-success font-16"></i></a>
                                        @else
                                            <a href="'.route('user.is-active', $row->id).'"
                                                onclick="event.preventDefault(); deleteMsg('{{ route('user.is-active', $user->id) }}')"><i
                                                    class="fas fa-check-circle text-danger font-16"></i></a>
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    <a href="{{ route('user.edit', $user->id) }}" class="mr-2"><i
                                            class="fas fa-edit text-info font-16"></i></a>
                                    <a href="{{ route('user.delete', $user->id) }}"
                                        onclick="event.preventDefault(); deleteMsg('{{ route('user.delete', $user->id) }}')"><i
                                            class="fas fa-trash-alt text-danger font-16"></i></a>
                                    <a href="{{ route('user.report.view', $user->id) }}"
                                        class="mr-2"><i class="fas fa-file text-info font-16"></i></a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>