# Accounts Management Module — Function, Flow & Suggestions

This document describes the **intended function and flow** of the Accounts Management module in the admin portal and suggests improvements.

---

## 1. Module Purpose (What It Should Do)

| Area | Purpose |
|------|--------|
| **Accounts Payable** | Track money the organization **owes** (vendors, invoices). Add, edit, mark paid/unpaid, export. |
| **Accounts Receivable (Paid)** | **Read-only** view of money **received** (paid collections from the Collections module). Export for reporting. |

So: **Payables = you pay out.** **Receivables (here) = you already received (paid collections).**

---

## 2. Recommended User Flow

### Accounts Payable

1. **Land** on Accounts → see Payables table (all or filtered).
2. **Add** → "+ Add Payable" → modal (Vendor, Amount, Invoice #, Mode of payment, Due date, Status, Remarks) → Save → redirect with success.
3. **Edit** → row action (pencil) → edit modal → Update → redirect with success.
4. **Optional:** Delete payable (with confirm) — route exists; consider adding UI.
5. **Filter** (suggested): Status (Unpaid/Paid/Overdue), date range (due date), search (vendor/invoice). Backend already supports `p_search`, `p_status`, `p_from`, `p_to` — expose in the UI.
6. **Export** → "Export PDF" → download `accounts_payable.pdf`.

### Accounts Receivable (Paid Collections)

1. **Land** → see "Paid Collections" table (from Collections where status = Paid).
2. **View only** — no add/edit here (collections are created in Collections module).
3. **Filter** (suggested): Date range, search (customer/invoice). Backend supports `r_search`, `r_from`, `r_to` — expose in the UI.
4. **Export** → "Export PDF" → download `paid_collections.pdf`.

---

## 3. Suggestions for the Admin Portal

### A. UX / Flow

- **Summary cards at top**  
  You already compute `$totalPayables`, `$totalUnpaidPayables`, `$totalCollected`, etc. Show 2–4 summary cards (e.g. Total Payables, Unpaid Payables, Paid Collections This Period) so admins see the big picture before scrolling tables.

- **Expose filters**  
  Add filter form(s) for Payables (status, date range, search) and for Receivables (date range, search) so the page is usable with lots of data. Wire them to existing `AccountsController::index` query params.

- **Delete Payable**  
  You have `destroyPayable` and route `delete /accounts/payables/{payable}`. Add a delete button (with confirmation) in the Payables table so admins can remove wrong/duplicate entries.

- **Overdue automation**  
  Payables support status `Overdue`. Consider a scheduled job or observer that sets status to `Overdue` when `due_date < today` and status is still `Unpaid`.

### B. Data & Consistency

- **Receivables amount**  
  Paid collections should show the amount received. The `Collection` model has `amount_paid` and `amount_due` (no `amount`). The view was updated to use `amount_paid ?? amount_due` so the "Amount" column shows the correct value.

- **PDF export routes**  
  `export.payables.pdf` and `export.receivables.pdf` are currently **outside** the `auth` and `finance` middleware. Move them inside the same middleware group as `/accounts` so only logged-in finance users can export.

### C. Optional Enhancements

- **Link to source**  
  In Receivables table, add a link "View" to the original Collection (e.g. `route('collections.show', $item->id)`) for traceability.

- **Invoice # in Add Payable**  
  Add Payable modal could include optional "Invoice #" so it’s visible in the table without editing later.

- **Pagination**  
  You paginate both tables. Ensure "collections_page" and "payables_page" query params are preserved when using filters so pagination and filters work together.

---

## 4. One-Page Flow Summary

```
[Admin] → Finance → Accounts

  ┌─ Accounts Payable
  │   → View list (with optional filters)
  │   → Add Payable (modal)
  │   → Edit Payable (modal)
  │   → (Optional) Delete Payable
  │   → Export PDF
  │
  └─ Accounts Receivable (Paid Collections)
      → View list (read-only; optional filters)
      → Export PDF
      → (Optional) "View" link to Collection
```

---

## 5. Quick Reference (Current Implementation)

| Feature | Payables | Receivables (Paid) |
|--------|----------|--------------------|
| List | ✅ | ✅ |
| Add | ✅ (modal) | N/A (from Collections) |
| Edit | ✅ (modal) | N/A |
| Delete | ✅ (backend only) | N/A |
| Export PDF | ✅ | ✅ |
| Filters (backend) | ✅ (p_search, p_status, p_from, p_to) | ✅ (r_search, r_from, r_to) |
| Filters (UI) | ❌ suggest adding | ❌ suggest adding |
| Summary cards | ❌ (data ready in controller) | ❌ |

Implementing the suggestions above will make the module’s function and flow clearer and safer for the admin portal.
