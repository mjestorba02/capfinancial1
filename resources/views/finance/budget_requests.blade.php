@extends('layouts.app')

@section('title', 'Budget Requests')

@section('content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">Budget Requests</h2>
            <p class="text-muted mb-0">Submit and manage budget requests.</p>
        </div>
        <button class="btn btn-warning text-white" data-bs-toggle="modal" data-bs-target="#addModal">
            <i class="bi bi-plus-lg me-1"></i> New Request
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <form method="GET" action="{{ route('budget_requests.index') }}" class="row g-3 mb-4 align-items-end">
        <div class="col-md-3">
            <div class="form-group mb-3">
                <label class="fw-semibold">From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group mb-3">
                <label class="fw-semibold">To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>
        </div>

        <div class="col-md-3">
            <div class="form-group mb-3">
                <label class="fw-semibold">Status</label>
                <select name="status" class="form-control">
                    <option value="">-- All --</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
        </div>

        <div class="col-md-3 d-flex gap-2 mb-3">
            <button type="submit" class="btn btn-primary flex-grow-1 mr-2">Filter</button>
            <a href="{{ route('budget_requests.index') }}" class="btn btn-secondary flex-grow-1">Reset</a>
        </div>
    </form>

    <!-- Table -->
    <div class="card shadow-sm">
        <div class="card-body">
            <h5 class="mb-3">Requests List</h5>
            <table class="table table-bordered table-hover align-middle">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Request ID</th>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Purpose</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Image</th>
                        <th>Date</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $req->request_id }}</td>
                            <td>{{ $req->employee->name ?? 'N/A' }}</td>
                            <td>{{ $req->department }}</td>
                            <td>{{ $req->purpose }}</td>
                            <td>₱{{ number_format($req->amount, 2) }}</td>
                            <td>
                                <span class="badge bg-{{ $req->status === 'Approved' ? 'success' : ($req->status === 'Pending' ? 'warning' : 'danger') }}">
                                    {{ $req->status }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($req->image_path)
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#viewImageModal{{ $req->id }}" 
                                    title="View Image" class="text-info text-decoration-none">
                                        <i class="fe fe-eye fe-18"></i>
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>{{ $req->created_at->format('Y-m-d') }}</td>
                            <td class="text-center">
                                <div class="d-flex justify-content-center align-items-center" style="gap: 12px;">

                                    @if(strtolower(trim($req->status)) === 'pending')
                                        <!-- Approve Icon -->
                                        <form method="POST" action="{{ route('budget_requests.approve', $req->id) }}" 
                                            onsubmit="return confirm('Approve this request?')" class="d-inline">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" class="bg-transparent border-0 p-0 text-success" title="Approve">
                                                <i class="fe fe-check-circle fe-18"></i>
                                            </button>
                                        </form>
                                    @endif

                                    <!-- Upload Image Icon -->
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#imageModal{{ $req->id }}" 
                                    title="Upload Image" class="text-warning text-decoration-none">
                                        <i class="fe fe-image fe-18"></i>
                                    </a>

                                    <!-- Edit Icon -->
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#editModal{{ $req->id }}" 
                                    title="Edit" class="text-white text-decoration-none">
                                        <i class="fe fe-edit fe-18"></i>
                                    </a>

                                    <!-- Delete Icon -->
                                    <!-- <form method="POST" action="{{ route('budget_requests.destroy', $req->id) }}" 
                                        onsubmit="return confirm('Delete this request?')" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="bg-transparent border-0 p-0 text-white" title="Delete">
                                            <i class="fe fe-trash fe-18"></i>
                                        </button>
                                    </form> -->
                                </div>
                            </td>
                        </tr>

                        <!-- Edit Modal -->
                        <div class="modal fade" id="editModal{{ $req->id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered">
                                <div class="modal-content p-4">
                                    <div class="modal-header border-0">
                                        <h5 class="modal-title">Edit Budget Request</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" action="{{ route('budget_requests.update', $req->id) }}">
                                        @csrf @method('PUT')
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label>Department</label>
                                                <input type="text" name="department" class="form-control" value="{{ $req->department }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Purpose</label>
                                                <textarea name="purpose" class="form-control" rows="3" required>{{ $req->purpose }}</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label>Amount</label>
                                                <input type="number" name="amount" class="form-control" value="{{ $req->amount }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label>Status</label>
                                                <select name="status" class="form-control" required>
                                                    <option value="Pending" {{ $req->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                    <option value="Approved" {{ $req->status == 'Approved' ? 'selected' : '' }}>Approved</option>
                                                    <option value="Rejected" {{ $req->status == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label>Remarks</label>
                                                <textarea name="remarks" class="form-control" rows="3">{{ $req->remarks }}</textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0">
                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Save Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted">No budget requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content p-4">
            <div class="modal-header border-0">
                <h5 class="modal-title">New Budget Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('budget_requests.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group mb-3">
                        <label class="fw-semibold">Department</label>
                        <select name="department" class="form-control" required>
                            <option value="">-- Select Department --</option>
                            <option value="Finance">Finance</option>
                            <option value="Human Resources">Human Resources</option>
                            <option value="Procurement">Procurement</option>
                            <option value="Logistics">Logistics</option>
                            <option value="Operations">Operations</option>
                            <option value="IT and Systems">IT and Systems</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Administration">Administration</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label>Purpose</label>
                        <textarea name="purpose" class="form-control" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="fw-semibold">Amount</label>
                        <input 
                            type="number" 
                            name="amount" 
                            class="form-control" 
                            required 
                            min="1" 
                            max="5000000" 
                            step="0.01" 
                            placeholder="Enter amount (₱)"
                        >
                        <small class="text-muted">Maximum allowed amount: ₱5,000,000</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-white">Submit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 🔹 Image Upload & View Modals (one for each budget request) -->
@foreach($requests as $req)
<div class="modal fade" id="imageModal{{ $req->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content p-4">
            <div class="modal-header border-0">
                <h5 class="modal-title">Manage Image - {{ $req->request_id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Current Image Preview Section -->
                @if($req->image_path)
                    <div class="mb-4 pb-3 border-bottom">
                        <label class="form-label fw-semibold mb-3">📷 Current Image</label>
                        <div class="text-center mb-3">
                            <img src="{{ asset('storage/' . $req->image_path) }}" alt="Budget Request Image" class="img-fluid rounded border" style="max-height: 350px; max-width: 100%;">
                        </div>
                        <button type="button" class="btn btn-danger w-100" onclick="deleteImage({{ $req->id }})">
                            <i class="fe fe-trash me-1"></i> Delete Image
                        </button>
                    </div>
                @endif

                <!-- Upload/Replace Section -->
                <div>
                    <label class="form-label fw-semibold mb-3">{{ $req->image_path ? '📁 Replace Image' : '📁 Upload Image' }}</label>
                    <form id="imageForm{{ $req->id }}" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <input type="file" class="form-control" id="imageInput{{ $req->id }}" name="image" accept="image/*">
                            <small class="text-muted d-block mt-2">✓ Allowed: JPG, PNG, GIF | Max: 2MB</small>
                        </div>

                        <!-- Image Preview Before Upload -->
                        <div id="previewContainer{{ $req->id }}" class="mb-3 text-center" style="display:none;">
                            <p class="text-muted small mb-2">Preview:</p>
                            <img id="previewImg{{ $req->id }}" class="img-fluid rounded border" style="max-height: 300px; max-width: 100%;">
                        </div>

                        <button type="button" class="btn btn-primary w-100" onclick="uploadImage({{ $req->id }})">
                            <i class="fe fe-upload me-1"></i> Upload Image
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View Image Modal (for table eye icon) -->
@if($req->image_path)
<div class="modal fade" id="viewImageModal{{ $req->id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title">📷 Image Preview - {{ $req->request_id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <img src="{{ asset('storage/' . $req->image_path) }}" alt="Budget Request Image" class="img-fluid rounded border" style="max-height: 500px; max-width: 100%;">
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <a href="{{ asset('storage/' . $req->image_path) }}" download class="btn btn-primary">
                    <i class="fe fe-download me-1"></i> Download
                </a>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach


<script>
// Preview image before upload
@foreach($requests as $req)
document.getElementById('imageInput{{ $req->id }}')?.addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (file) {
        const reader = new FileReader();
        reader.onload = function(event) {
            document.getElementById('previewImg{{ $req->id }}').src = event.target.result;
            document.getElementById('previewContainer{{ $req->id }}').style.display = 'block';
        };
        reader.readAsDataURL(file);
    }
});
@endforeach

// Upload image via AJAX
function uploadImage(requestId) {
    const fileInput = document.getElementById('imageInput' + requestId);
    
    if (!fileInput.files[0]) {
        alert('Please select an image');
        return;
    }

    const formData = new FormData();
    formData.append('image', fileInput.files[0]);
    formData.append('_token', document.querySelector('input[name="_token"]').value);

    fetch(`/finance/budget_requests/${requestId}/upload-image`, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Image uploaded successfully!');
            location.reload();
        } else {
            alert('Error uploading image: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error uploading image');
    });
}

// Delete image via AJAX
function deleteImage(requestId) {
    if (!confirm('Are you sure you want to delete this image?')) {
        return;
    }

    fetch(`/finance/budget_requests/${requestId}/delete-image`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Image deleted successfully!');
            location.reload();
        } else {
            alert('Error deleting image: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error deleting image');
    });
}
</script>
@endsection