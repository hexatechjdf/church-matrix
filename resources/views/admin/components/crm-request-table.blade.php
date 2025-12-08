<table class="table table-bordered">
    <thead>
        <tr>
            <th>User ID</th>
            <th>Auth User</th>
            <th>Auth Key</th>
            <th>Action</th>
        </tr>
    </thead>

    <tbody>
        @forelse ($tokens as $t)
            <tr>
                <td>{{ $t->user_id }}</td>
                <td>{{ $t->access_token }}</td>
                <td>{{ $t->refresh_token }}</td>
                <td>
                    <button class="action-btn btn btn-success btn-sm"
                        data-url="{{ route('church-matrix.accept.request', $t->id) }}"
                        data-message="You want to accept this token?" data-success="Token accepted successfully"
                        data-function="fetchTokens">
                        Accept
                    </button>
                    <button class="action-btn btn btn-danger btn-sm"
                        data-url="{{ route('church-matrix.test.request', $t->id) }}"
                        data-message="You want to test this token?" data-success="Token validated successfully">
                        Test
                    </button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center">No records found</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="d-flex justify-content-center">
    {!! $tokens->links() !!}
</div>
