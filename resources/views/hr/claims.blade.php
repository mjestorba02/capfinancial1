@extends('layouts.app')

@section('title', 'Claims & Reimbursement')

@section('content')
<div class="row">
    <div class="col-12">

        <h4 class="mb-4">Claims & Reimbursement</h4>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Add Claim Form --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="POST" action="{{ route('claims.store') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-3">
                            <label>Employee</label>
                            <select name="user_id" class="form-control" required>
                                <option value="">-- Select --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}">{{ $emp->id }} - {{ $emp->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Type of Claim</label>
                            <input type="text" name="type_of_claim" class="form-control" placeholder="e.g. Medical, Travel" required>
                        </div>

                        <div class="form-group col-md-3">
                            <label>Attach Document</label>
                            <input type="file" name="attached_document" class="form-control-file">
                        </div>
                    </div>

                    <div class="form-row mt-3">
                        <div class="form-group col-md-2">
                            <label>Claim Date</label>
                            <input type="date" name="claim_date" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Claim Amount</label>
                            <input type="number" step="0.01" name="claim_amount" class="form-control" required>
                        </div>
                        <div class="form-group col-md-2">
                            <label>Reimbursement Amount</label>
                            <input type="number" step="0.01" name="reimbursement_amount" class="form-control">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Reimbursement Date</label>
                            <input type="date" name="reimbursement_date" class="form-control">
                        </div>
                        <div class="form-group col-md-2">
                            <label>Status</label>
                            <select name="status" class="form-control" required>
                                <option value="Pending">Pending</option>
                                <option value="Paid">Paid</option>
                                <option value="Denied">Denied</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Save Claim</button>
                </form>
            </div>
        </div>

        {{-- Claims Table --}}
        <div class="card shadow">
            <div class="card-body">
                <h5 class="mb-3">Claims List</h5>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Claim ID</th>
                                <th>Employee</th>
                                <th>Position</th>
                                <th>Type of Claim</th>
                                <th>Attached Document</th>
                                <th>Claim Date</th>
                                <th>Claim Amount</th>
                                <th>Reimbursement Amount</th>
                                <th>Reimbursement Date</th>
                                <th>Status</th>
                                <th class="fms-actions">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($claims as $claim)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $claim->claim_id }}</td>
                                <td>{{ $claim->user->name }}</td>
                                <td>{{ $claim->user->position }}</td>
                                <td>{{ $claim->type_of_claim }}</td>
                                <td>
                                    @if($claim->attached_document)
                                        <a href="{{ asset('storage/' . $claim->attached_document) }}" target="_blank">View</a>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $claim->claim_date }}</td>
                                <td>₱{{ number_format($claim->claim_amount, 2) }}</td>
                                <td>₱{{ number_format($claim->reimbursement_amount, 2) }}</td>
                                <td>{{ $claim->reimbursement_date ?? '-' }}</td>
                                <td>
                                    @php
                                        $badgeClass = match($claim->status) {
                                            'Pending' => 'warning',
                                            'Denied'  => 'danger',
                                            default   => 'success',
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }}">
                                        {{ $claim->status }}
                                    </span>
                                </td>
                                <td class="fms-actions">
                                    <div class="fms-action-group">
                                        <button class="btn btn-info btn-sm btn-view"
                                            data-claim='@json($claim)'>
                                            <i class="fe fe-eye"></i> View
                                        </button>

                                        <button class="btn btn-warning btn-sm btn-edit"
                                            data-claim='@json($claim)'>
                                            <i class="fe fe-edit"></i> Edit
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- === VIEW CLAIM MODAL === --}}
                <div id="viewClaimModal" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                        <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title">View Claim</h5>
                            <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="row">
                            <div class="col-md-6">
                                <label>Employee:</label>
                                <p id="viewEmployee"></p>
                            </div>
                            <div class="col-md-6">
                                <label>Type of Claim:</label>
                                <p id="viewType"></p>
                            </div>
                            <div class="col-md-6">
                                <label>Claim Date:</label>
                                <p id="viewDate"></p>
                            </div>
                            <div class="col-md-6">
                                <label>Claim Amount:</label>
                                <p id="viewAmount"></p>
                            </div>
                            <div class="col-md-6">
                                <label>Status:</label>
                                <p id="viewStatus"></p>
                            </div>
                            <div class="col-md-12">
                                <label>Attached Document:</label>
                                <p><a id="viewDocument" href="#" target="_blank">View File</a></p>
                            </div>
                            </div>
                        </div>
                        </div>
                    </div>
                </div>

                {{-- === EDIT CLAIM MODAL === --}}
                <div id="editClaimModal" class="modal fade" tabindex="-1" role="dialog">
                    <div class="modal-dialog modal-lg" role="document">
                        <div class="modal-content">
                        <div class="modal-header bg-warning text-dark">
                            <h5 class="modal-title">Edit Claim</h5>
                            <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                            <form id="editClaimForm" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="user_id" id="editUserId">
                                <input type="hidden" name="id" id="editClaimId">

                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Employee</label>
                                        <input type="text" id="editEmployee" class="form-control" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label>Type of Claim</label>
                                        <input type="text" id="editType" name="type_of_claim" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Claim Date</label>
                                        <input type="date" id="editDate" name="claim_date" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Claim Amount</label>
                                        <input type="number" id="editAmount" name="claim_amount" class="form-control">
                                    </div>
                                    <div class="col-md-6">
                                        <label>Status</label>
                                        <select id="editStatus" name="status" class="form-control">
                                            <option value="Pending">Pending</option>
                                            <option value="Paid">Paid</option>
                                            <option value="Denied">Denied</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-success">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  // Helpers to show/hide modals: use Bootstrap API if available, otherwise fallback to manual DOM toggling.
  function showModal(modalEl) {
    if (!modalEl) return;
    if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
      // Bootstrap 5 present
      bootstrap.Modal.getOrCreateInstance(modalEl).show();
      return;
    }
    // fallback
    modalEl.classList.add('show');
    modalEl.style.display = 'block';
    modalEl.removeAttribute('aria-hidden');
    modalEl.setAttribute('aria-modal', 'true');
    document.body.classList.add('modal-open');
    // add backdrop
    if (!document.querySelector('.modal-backdrop')) {
      const bd = document.createElement('div');
      bd.className = 'modal-backdrop fade show';
      document.body.appendChild(bd);
    }
  }

  function hideModal(modalEl) {
    if (!modalEl) return;
    if (window.bootstrap && typeof window.bootstrap.Modal === 'function') {
      const inst = bootstrap.Modal.getInstance(modalEl);
      if (inst) inst.hide();
      else bootstrap.Modal.getOrCreateInstance(modalEl).hide();
      return;
    }
    modalEl.classList.remove('show');
    modalEl.style.display = 'none';
    modalEl.setAttribute('aria-hidden', 'true');
    modalEl.removeAttribute('aria-modal');
    document.body.classList.remove('modal-open');
    // remove backdrop(s)
    document.querySelectorAll('.modal-backdrop').forEach(n => n.remove());
  }

  // Close triggers (X / buttons that have data-dismiss="modal" or .close)
  document.querySelectorAll('[data-dismiss="modal"], .close').forEach(el => {
    el.addEventListener('click', function (ev) {
      const modal = this.closest('.modal, .modal.fade');
      if (modal) hideModal(modal);
    });
  });

  // Close when clicking the backdrop (only when clicked outside content)
  document.querySelectorAll('.modal').forEach(modalEl => {
    modalEl.addEventListener('click', function (e) {
      if (e.target === modalEl) hideModal(modalEl);
    });
  });

  // ESC key closes any opened modal
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.key === 'Esc') {
      document.querySelectorAll('.modal.show, .modal[style*="display: block"]').forEach(m => hideModal(m));
    }
  });

  // === View Button logic ===
  document.querySelectorAll('.btn-view').forEach(btn => {
    btn.addEventListener('click', function () {
      let claim;
      try {
        claim = JSON.parse(this.dataset.claim);
      } catch (err) {
        console.error('Invalid JSON on data-claim', err);
        return;
      }

      document.getElementById('viewEmployee').textContent = claim.user?.name ?? '-';
      document.getElementById('viewType').textContent = claim.type_of_claim ?? '-';
      document.getElementById('viewDate').textContent = claim.claim_date ?? '-';
      document.getElementById('viewAmount').textContent = claim.claim_amount != null ? '₱' + parseFloat(claim.claim_amount).toFixed(2) : '-';
      document.getElementById('viewStatus').textContent = claim.status ?? '-';

      const viewDoc = document.getElementById('viewDocument');
      if (claim.attached_document) {
        viewDoc.href = '/storage/' + claim.attached_document;
        viewDoc.style.display = '';
      } else {
        viewDoc.href = '#';
        viewDoc.style.display = 'none';
      }

      const viewModal = document.getElementById('viewClaimModal');
      showModal(viewModal);
    });
  });

  // === Edit Button logic ===
  document.querySelectorAll('.btn-edit').forEach(btn => {
    btn.addEventListener('click', function () {
      let claim;
      try {
        claim = JSON.parse(this.dataset.claim);
      } catch (err) {
        console.error('Invalid JSON on data-claim', err);
        return;
      }

      // populate fields
      document.getElementById('editUserId').value = claim.user_id ?? '';
      document.getElementById('editClaimId').value = claim.id ?? '';
      document.getElementById('editEmployee').value = claim.user?.name ?? '';
      document.getElementById('editType').value = claim.type_of_claim ?? '';
      document.getElementById('editDate').value = claim.claim_date ?? '';
      document.getElementById('editAmount').value = claim.claim_amount ?? '';
      document.getElementById('editStatus').value = claim.status ?? 'Pending';

      // set form action to the update route (PUT)
      const editForm = document.getElementById('editClaimForm');
      if (editForm) {
        // If your routes are inside the /hr prefix, the update route is /hr/claims/{id}
        // We'll set it dynamically so the Edit form submits to the correct endpoint.
        const id = claim.id;
        // prefer named route structure if you need it, but this is straightforward:
        editForm.action = `/hr/claims/${id}`;

        // ensure _method spoof (PUT) exists
        let methodInput = editForm.querySelector('input[name="_method"]');
        if (!methodInput) {
          methodInput = document.createElement('input');
          methodInput.type = 'hidden';
          methodInput.name = '_method';
          methodInput.value = 'PUT';
          editForm.appendChild(methodInput);
        } else {
          methodInput.value = 'PUT';
        }
        // CSRF: Blade already rendered @csrf in the form so we leave it.
      }

      const editModal = document.getElementById('editClaimModal');
      showModal(editModal);
    });
  });

  // Optional: intercept edit form submit to do client-side validation (otherwise it will POST to the server)
  // Keep default submission behavior so update route / controller handles the update.
});
</script>

<!-- Minimal fallback style for backdrop if Bootstrap CSS isn't loaded -->
<style>
.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 1040;
}
.modal-open {
  overflow: hidden;
}
</style>
@endsection