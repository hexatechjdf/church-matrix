<table class="table table-bordered">
    <thead>
        <tr>
            <th>User</th>
            <th>Assign Campus</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
            <tr data-user-id="{{ $user->location }}">
                <td>{{ $user->name }}</td>
                <td>
                    <select name="campus_id" class="form-select campus-select" required>
                        <option value="">Select Campus</option>
                        @foreach ($campuses as $campus)
                            <option value="{{ $campus->campus_unique_id }}"
                                {{ $user->location == $campus->location_id ? 'selected' : '' }}>
                                {{ $campus->name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td>
                    <button class="btn btn-primary btn-sm save-user-campus">Save</button>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>
