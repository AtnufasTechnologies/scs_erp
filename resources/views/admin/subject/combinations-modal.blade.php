<div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead class="bg-gradient" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff;">
            <tr>
                <th>Program</th>
                <th>Details</th>
            </tr>
        </thead>
        <tbody>
            @forelse($combinations as $combination)
                <tr>
                    <td>{{ $combination->student_program->program_name ?? '-' }}</td>
                    <td>
                        <!-- Add more details as needed -->
                        <span class="badge bg-primary">ID: {{ $combination->student_program->id ?? '-' }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center">No combinations found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
