# Purchase Order Attachments Feature

## Overview
Enhanced Purchase Order attachment system to allow uploading images and documents **even after PO is received**. Previously, attachments could only be added during draft/edit stage.

## Implementation Date
June 27, 2026

## Problem Solved
- Users couldn't add additional documentation (invoices, delivery notes, photos) after receiving a PO
- Once PO status was "received", it was locked from any attachment changes
- Need to maintain complete documentation trail for completed POs

## New Features

### 1. Add Attachments Anytime
- **Works for all PO statuses**: draft, sent, partially_received, **received**
- Upload multiple files at once (up to 10 files)
- Supported formats: Images (JPG, PNG, GIF, WebP), PDF, Word, Excel
- Maximum file size: 10MB per file

### 2. Delete Attachments
- Remove individual attachments with confirmation
- Automatically deletes file from storage
- Works for any PO status

### 3. Enhanced Attachment Display
- Shows file count badge
- Displays upload timestamp for each file
- Icons based on file type (image, PDF, document)
- File size shown in KB
- View and Delete buttons for each attachment

## User Interface

### Add Attachments Section
Located at the bottom of PO detail page, always visible regardless of status:

```
┌─────────────────────────────────────────┐
│ 📎 បន្ថែមឯកសារភ្ជាប់ថ្មី              │
├─────────────────────────────────────────┤
│ ជ្រើសរើសឯកសារ (រូបភាព, PDF, Excel, Word) │
│ [Choose Files] (No file chosen)        │
│ ℹ️ អាចបន្ថែមបានច្រើនឯកសារក្នុងពេលតែមួយ  │
│                                         │
│ [📤 បន្ថែមឯកសារ (Upload)]               │
│ ✅ អាចបន្ថែមឯកសារបានទោះបីជា PO         │
│    ត្រូវបានទទួលរួចក៏ដោយ                │
└─────────────────────────────────────────┘
```

### Attachment List Display
```
┌─────────────────────────────────────────┐
│ 📎 ឯកសារភ្ជាប់                    [3]  │
├─────────────────────────────────────────┤
│ 🖼️ invoice_001.jpg                      │
│    245.3 KB · 27/06/2026 09:42      👁️ 🗑️│
│                                         │
│ 📄 delivery_note.pdf                    │
│    156.8 KB · 27/06/2026 10:15      👁️ 🗑️│
│                                         │
│ 🖼️ goods_received.jpg                   │
│    389.1 KB · 27/06/2026 14:20      👁️ 🗑️│
└─────────────────────────────────────────┘
```

## Technical Implementation

### Backend Changes

#### PurchaseOrderController.php - New Methods

**1. addAttachments()**
```php
public function addAttachments(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
{
    // Validates up to 10 files
    // Adds to existing attachments array
    // Stores upload timestamp
    // Works for ANY PO status
}
```

**2. removeAttachment()**
```php
public function removeAttachment(Request $request, PurchaseOrder $purchaseOrder): RedirectResponse
{
    // Validates index parameter
    // Deletes file from storage/po-attachments
    // Removes from attachments array
    // Updates database
}
```

### Routes Added
```php
Route::post('/{purchaseOrder}/attachments', 
    [PurchaseOrderController::class, 'addAttachments'])
    ->name('purchase-orders.attachments.add');

Route::delete('/{purchaseOrder}/attachments', 
    [PurchaseOrderController::class, 'removeAttachment'])
    ->name('purchase-orders.attachments.remove');
```

### View Changes

**purchase-orders/show.blade.php**
- Added attachment count badge
- Added upload timestamp display
- Added delete button for each attachment
- Added new "Add Attachments" panel (always visible)
- Form with file input (multiple files)
- Success message about working after received status

## Database Storage

Attachments are stored in `purchase_orders.attachments` JSON column:
```json
[
  {
    "path": "po-attachments/abc123.jpg",
    "original_name": "invoice_001.jpg",
    "size": 251238,
    "mime": "image/jpeg",
    "uploaded_at": "2026-06-27 09:42:19"
  }
]
```

## Usage Examples

### Scenario 1: Add Invoice After Receiving
1. PO is marked as "received"
2. Physical invoice arrives later
3. Navigate to PO detail page
4. Scroll to "បន្ថែមឯកសារភ្ជាប់ថ្មី" section
5. Click "Choose Files" and select invoice scan
6. Click "បន្ថែមឯកសារ (Upload)"
7. Invoice is added and timestamp recorded

### Scenario 2: Add Delivery Photos
1. Goods are received
2. Take photos of delivered items
3. Open PO detail page on mobile
4. Upload multiple photos at once
5. All photos stored with timestamps

### Scenario 3: Remove Wrong File
1. Accidentally uploaded wrong document
2. Click trash icon next to file
3. Confirm deletion
4. File removed from storage and database

## Benefits

✅ **Complete Documentation**: Maintain full paper trail for auditing
✅ **Flexible**: Add documents at any stage of PO lifecycle
✅ **No Restrictions**: Works even after PO is fully received
✅ **Multi-file Upload**: Save time by uploading multiple files at once
✅ **Organized**: Files stored with timestamps and original names
✅ **Safe Deletion**: Remove incorrect files with confirmation
✅ **Professional**: Clean UI with file type icons and sizes

## File Validation

**Allowed Types**:
- Images: jpg, jpeg, png, gif, webp
- Documents: pdf, doc, docx, xls, xlsx

**Size Limits**:
- Per file: 10MB maximum
- Per upload: 10 files maximum

**Storage Location**:
- `storage/app/public/po-attachments/`
- Accessible via: `storage/po-attachments/filename`

## Security Considerations

✅ **Validation**: All files validated for type and size
✅ **Storage**: Files stored in Laravel's storage system
✅ **Access Control**: Files only accessible through authenticated routes
✅ **Confirmation**: Delete actions require confirmation
✅ **Logging**: All uploads timestamped for audit trail

## Success Messages

**Upload Success**:
```
បានបន្ថែមឯកសារ 3 ទៅកាន់ PO PO-2026-0002
(Added 3 files to PO PO-2026-0002)
```

**Delete Success**:
```
បានលុបឯកសារ
(File deleted)
```

**Error Messages**:
```
រកមិនឃើញឯកសារ
(File not found)
```

## Use Cases

1. **Invoices**: Add supplier invoices after receiving goods
2. **Delivery Notes**: Attach delivery documentation
3. **Photos**: Document condition of received items
4. **Quality Reports**: Attach inspection reports
5. **Correspondence**: Store email confirmations
6. **Certificates**: Attach quality certificates or test reports

## Future Enhancements (Optional)

- [ ] Image gallery/lightbox view
- [ ] Download all attachments as ZIP
- [ ] Attachment categories/tags
- [ ] Image thumbnails in list
- [ ] Drag-and-drop upload
- [ ] Attachment comments/notes

## Status
✅ **COMPLETE** - Fully functional for all PO statuses
