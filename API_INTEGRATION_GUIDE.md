# 🔗 Finance APIs - External System Integration

## Overview

These APIs allow external systems to send disbursement and collection data to the financial management system. The data is validated and automatically inserted into the database.

## Base URL

```
http://your-domain.com/api
```

## Authentication

Currently, these APIs are **open** (no authentication required). For production, consider adding:
- API key validation
- OAuth2
- JWT tokens

To add authentication, modify `routes/api.php`:
```php
Route::middleware('api.key')->group(function () {
    // API routes here
});
```

---

## 1. Disbursements API

### Receive Disbursement

**Endpoint**: `POST /api/finance/disbursements/receive`

**Description**: Receive and store disbursement data from external systems.

#### Request Headers
```
Content-Type: application/json
```

#### Request Body
```json
{
  "voucher_no": "DV-2026-001",
  "vendor": "ABC Supplies Inc.",
  "category": "Office Supplies",
  "amount": 2500.50,
  "status": "Approved",
  "disbursement_date": "2026-02-03",
  "external_id": "EXT-DV-12345",
  "remarks": "Monthly supply order"
}
```

#### Field Descriptions

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `voucher_no` | String | ✅ | Unique voucher number |
| `vendor` | String | ✅ | Vendor/supplier name |
| `category` | String | ✅ | Disbursement category |
| `amount` | Decimal | ✅ | Amount to be disbursed |
| `status` | String | ❌ | Pending, Approved, Processed, Cancelled (default: Pending) |
| `disbursement_date` | Date | ❌ | Format: YYYY-MM-DD (default: today) |
| `external_id` | String | ❌ | ID from external system for tracking |
| `remarks` | String | ❌ | Additional notes |

#### Response (Success - 201)
```json
{
  "success": true,
  "message": "Disbursement received and stored successfully",
  "data": {
    "id": 1,
    "voucher_no": "DV-2026-001",
    "amount": 2500.50,
    "status": "Approved",
    "created_at": "2026-02-03T10:30:00Z"
  }
}
```

#### Response (Validation Error - 422)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "voucher_no": ["The voucher_no has already been taken"],
    "amount": ["The amount must be at least 0.01"]
  }
}
```

#### Response (Server Error - 500)
```json
{
  "success": false,
  "message": "Error processing disbursement",
  "error": "Error message details"
}
```

#### Example Request (cURL)
```bash
curl -X POST http://localhost:8000/api/finance/disbursements/receive \
  -H "Content-Type: application/json" \
  -d '{
    "voucher_no": "DV-2026-001",
    "vendor": "ABC Supplies Inc.",
    "category": "Office Supplies",
    "amount": 2500.50,
    "status": "Approved",
    "disbursement_date": "2026-02-03"
  }'
```

#### Example Request (JavaScript/Fetch)
```javascript
const response = await fetch('http://localhost:8000/api/finance/disbursements/receive', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    voucher_no: 'DV-2026-001',
    vendor: 'ABC Supplies Inc.',
    category: 'Office Supplies',
    amount: 2500.50,
    status: 'Approved',
    disbursement_date: '2026-02-03'
  })
});

const data = await response.json();
console.log(data);
```

---

### Get Disbursement Status

**Endpoint**: `GET /api/finance/disbursements/status/{id}`

**Description**: Retrieve disbursement details by ID.

#### Parameters
- `id` (integer, required): Disbursement ID

#### Response (Success - 200)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "voucher_no": "DV-2026-001",
    "vendor": "ABC Supplies Inc.",
    "amount": 2500.50,
    "status": "Approved",
    "disbursement_date": "2026-02-03",
    "created_at": "2026-02-03T10:30:00Z",
    "updated_at": "2026-02-03T10:30:00Z"
  }
}
```

#### Response (Not Found - 404)
```json
{
  "success": false,
  "message": "Disbursement not found"
}
```

#### Example Request (cURL)
```bash
curl -X GET http://localhost:8000/api/finance/disbursements/status/1
```

#### Example Request (JavaScript/Fetch)
```javascript
const response = await fetch('http://localhost:8000/api/finance/disbursements/status/1');
const data = await response.json();
console.log(data);
```

---

## 2. Collections API

### Receive Collection

**Endpoint**: `POST /api/finance/collections/receive`

**Description**: Receive and store collection/payment data from external systems.

#### Request Headers
```
Content-Type: application/json
```

#### Request Body
```json
{
  "customer_name": "XYZ Corporation",
  "invoice_number": "INV-2026-0001",
  "amount_due": 5000.00,
  "amount_paid": 5000.00,
  "status": "Paid",
  "payment_date": "2026-02-03",
  "employee_id": 1,
  "external_id": "EXT-COLL-54321",
  "remarks": "Payment received via bank transfer"
}
```

#### Field Descriptions

| Field | Type | Required | Notes |
|-------|------|----------|-------|
| `customer_name` | String | ✅ | Customer/payer name |
| `invoice_number` | String | ✅ | Unique invoice number |
| `amount_due` | Decimal | ✅ | Total amount due |
| `amount_paid` | Decimal | ✅ | Amount paid (can be partial) |
| `status` | String | ❌ | Pending, Partial, Paid, Overdue, Cancelled (auto-determined if not provided) |
| `payment_date` | Date | ❌ | Format: YYYY-MM-DD (default: today) |
| `employee_id` | Integer | ❌ | Employee ID who recorded the collection |
| `external_id` | String | ❌ | ID from external system for tracking |
| `remarks` | String | ❌ | Additional notes |

#### Response (Success - 201)
```json
{
  "success": true,
  "message": "Collection received and stored successfully",
  "data": {
    "id": 1,
    "invoice_number": "INV-2026-0001",
    "customer_name": "XYZ Corporation",
    "amount_paid": 5000.00,
    "status": "Paid",
    "created_at": "2026-02-03T10:30:00Z"
  }
}
```

#### Response (Validation Error - 422)
```json
{
  "success": false,
  "message": "Validation error",
  "errors": {
    "invoice_number": ["The invoice_number has already been taken"],
    "amount_paid": ["The amount_paid must be at least 0"]
  }
}
```

#### Response (Server Error - 500)
```json
{
  "success": false,
  "message": "Error processing collection",
  "error": "Error message details"
}
```

#### Example Request (cURL)
```bash
curl -X POST http://localhost:8000/api/finance/collections/receive \
  -H "Content-Type: application/json" \
  -d '{
    "customer_name": "XYZ Corporation",
    "invoice_number": "INV-2026-0001",
    "amount_due": 5000.00,
    "amount_paid": 5000.00,
    "status": "Paid",
    "payment_date": "2026-02-03"
  }'
```

#### Example Request (JavaScript/Fetch)
```javascript
const response = await fetch('http://localhost:8000/api/finance/collections/receive', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    customer_name: 'XYZ Corporation',
    invoice_number: 'INV-2026-0001',
    amount_due: 5000.00,
    amount_paid: 5000.00,
    status: 'Paid',
    payment_date: '2026-02-03'
  })
});

const data = await response.json();
console.log(data);
```

---

### Get Collection Status

**Endpoint**: `GET /api/finance/collections/status/{id}`

**Description**: Retrieve collection details by ID.

#### Parameters
- `id` (integer, required): Collection ID

#### Response (Success - 200)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "invoice_number": "INV-2026-0001",
    "customer_name": "XYZ Corporation",
    "amount_due": 5000.00,
    "amount_paid": 5000.00,
    "status": "Paid",
    "payment_date": "2026-02-03",
    "created_at": "2026-02-03T10:30:00Z",
    "updated_at": "2026-02-03T10:30:00Z"
  }
}
```

#### Response (Not Found - 404)
```json
{
  "success": false,
  "message": "Collection not found"
}
```

#### Example Request (cURL)
```bash
curl -X GET http://localhost:8000/api/finance/collections/status/1
```

#### Example Request (JavaScript/Fetch)
```javascript
const response = await fetch('http://localhost:8000/api/finance/collections/status/1');
const data = await response.json();
console.log(data);
```

---

## Error Codes

| Code | Meaning |
|------|---------|
| 201 | Created - Data successfully stored |
| 200 | OK - Request successful |
| 400 | Bad Request - Invalid request format |
| 404 | Not Found - Resource not found |
| 422 | Unprocessable Entity - Validation error |
| 500 | Server Error - Internal server error |

---

## Validation Rules

### Disbursements
- `voucher_no`: Required, unique, string
- `vendor`: Required, string, max 255 characters
- `category`: Required, string, max 255 characters
- `amount`: Required, numeric, minimum 0.01
- `status`: Optional, must be one of: Pending, Approved, Processed, Cancelled
- `disbursement_date`: Optional, valid date format YYYY-MM-DD
- `external_id`: Optional, unique string
- `remarks`: Optional, string

### Collections
- `customer_name`: Required, string, max 255 characters
- `invoice_number`: Required, unique, string
- `amount_due`: Required, numeric, minimum 0.01
- `amount_paid`: Required, numeric, minimum 0
- `status`: Optional, must be one of: Pending, Partial, Paid, Overdue, Cancelled
- `payment_date`: Optional, valid date format YYYY-MM-DD
- `employee_id`: Optional, must exist in users table
- `external_id`: Optional, unique string
- `remarks`: Optional, string

---

## Notes & Best Practices

1. **Unique Identifiers**: Always include `external_id` to prevent duplicate entries
2. **Date Format**: Use ISO 8601 format (YYYY-MM-DD) for dates
3. **Amount Format**: Use decimal numbers (2500.50 not "2500.50")
4. **Status Codes**: Return status codes follow HTTP standards
5. **Error Handling**: Always check `success` field in response
6. **Logging**: All API calls are logged in `storage/logs/laravel.log`
7. **Retry Logic**: Implement retry mechanism for failed requests
8. **Rate Limiting**: Consider adding rate limiting for production

---

## Testing

Use Postman, Insomnia, or any HTTP client to test these endpoints:

### Test Disbursement API
```
POST http://localhost:8000/api/finance/disbursements/receive
Content-Type: application/json

{
  "voucher_no": "TEST-DV-001",
  "vendor": "Test Vendor",
  "category": "Test Category",
  "amount": 1000.00
}
```

### Test Collection API
```
POST http://localhost:8000/api/finance/collections/receive
Content-Type: application/json

{
  "customer_name": "Test Customer",
  "invoice_number": "TEST-INV-001",
  "amount_due": 1000.00,
  "amount_paid": 500.00
}
```

---

## Integration Examples

### PHP Integration
```php
$data = [
    'voucher_no' => 'DV-2026-001',
    'vendor' => 'ABC Supplies',
    'category' => 'Office Supplies',
    'amount' => 2500.50
];

$response = Http::post('http://localhost:8000/api/finance/disbursements/receive', $data);
$result = $response->json();

if ($result['success']) {
    echo "Disbursement created: " . $result['data']['id'];
} else {
    echo "Error: " . json_encode($result['errors']);
}
```

### Python Integration
```python
import requests

data = {
    'voucher_no': 'DV-2026-001',
    'vendor': 'ABC Supplies',
    'category': 'Office Supplies',
    'amount': 2500.50
}

response = requests.post('http://localhost:8000/api/finance/disbursements/receive', json=data)
result = response.json()

if result['success']:
    print(f"Disbursement created: {result['data']['id']}")
else:
    print(f"Error: {result['errors']}")
```

### Node.js Integration
```javascript
const axios = require('axios');

const data = {
  voucher_no: 'DV-2026-001',
  vendor: 'ABC Supplies',
  category: 'Office Supplies',
  amount: 2500.50
};

axios.post('http://localhost:8000/api/finance/disbursements/receive', data)
  .then(response => {
    console.log('Disbursement created:', response.data.data.id);
  })
  .catch(error => {
    console.log('Error:', error.response.data.errors);
  });
```

---

**Version**: 1.0  
**Last Updated**: February 3, 2026  
**Status**: Production Ready
