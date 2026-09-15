# PRD — Enterprise Internal ERP
## SAP Business One–Inspired Architecture

**Document:** `PRD.md`  
**Version:** 1.0.0  
**Status:** Draft / Foundation  
**Product Type:** Internal Single-Company ERP  
**Reference Architecture:** SAP Business One–inspired, not a SAP clone  
**Target:** Small-to-Medium / Medium Enterprise  
**Primary Objective:** End-to-end integrated ERP for one company with enterprise-grade architecture, security, workflow, accounting, inventory, asset, reporting, AI, and automation.

---

# 1. Executive Summary

ERP ini adalah sistem informasi bisnis terintegrasi untuk penggunaan internal satu perusahaan. Sistem dirancang berdasarkan konsep dan pola proses bisnis **SAP Business One**, tetapi dibangun sebagai produk internal yang lebih sederhana, modular, modern, API-first, dan dapat dikembangkan sesuai kebutuhan perusahaan.

ERP tidak dirancang sebagai SaaS multi-tenant. Fokus utama adalah:

- Satu perusahaan sebagai tenant utama.
- Struktur organisasi, cabang, department, warehouse, cost center, dan profit center dapat dikonfigurasi.
- Integrasi penuh antara procurement, inventory, sales, finance/accounting, asset, dan reporting.
- Transaction-driven accounting.
- Approval workflow yang dapat dikonfigurasi.
- Audit trail menyeluruh.
- Document management dan digital signature.
- Todo, task, memo, ticketing/helpdesk.
- AI Assistant dan automation engine dengan permission-aware access.
- REST API dan integration layer untuk sistem eksternal.
- Dockerized production deployment.
- Modern web UI dengan Next.js dan backend Laravel REST API.

Prinsip utama:

> **Enter transaction once, reuse the data everywhere.**

Setiap transaksi bisnis harus menghasilkan data operasional dan, bila relevan, dampak finansial secara terintegrasi sehingga tidak diperlukan input ulang manual.

---

# 2. Product Vision

Membangun ERP internal yang menjadi **single source of truth** bagi operasional perusahaan.

ERP harus memungkinkan management dan staff melihat hubungan:

```text
Business Activity
      ↓
Operational Transaction
      ↓
Inventory / Asset Impact
      ↓
Financial Impact
      ↓
Reporting / Analytics
      ↓
AI Insight / Automation
```

Contoh:

```text
Purchase Request
      ↓
Approval
      ↓
Purchase Order
      ↓
Goods Receipt
      ↓
Inventory Increase
      ↓
Supplier Invoice
      ↓
Accounts Payable
      ↓
Payment
      ↓
General Ledger
      ↓
Financial Reporting
```

---

# 3. Product Goals

## 3.1 Primary Goals

1. Mengintegrasikan proses bisnis perusahaan.
2. Mengurangi input data berulang.
3. Menyediakan financial accounting terintegrasi.
4. Menyediakan inventory real-time.
5. Menyediakan procurement dan sales lifecycle.
6. Menyediakan approval workflow.
7. Menyediakan audit trail.
8. Menyediakan management dashboard.
9. Menyediakan automation.
10. Menyediakan AI Assistant yang aman.
11. Menyediakan API-first architecture.
12. Menjadi fondasi digitalisasi proses bisnis perusahaan.

## 3.2 Secondary Goals

- Mengurangi ketergantungan spreadsheet.
- Meningkatkan traceability.
- Mempercepat approval.
- Meningkatkan kualitas data.
- Mempermudah audit.
- Mempermudah integrasi dengan sistem lain.

---

# 4. Non-Goals

Versi awal tidak bertujuan menjadi replika penuh SAP S/4HANA.

Tidak menjadi prioritas MVP:

- Kompleksitas global enterprise multi-country.
- Advanced manufacturing MRP tingkat SAP S/4HANA.
- Kompleksitas treasury enterprise.
- Massive multi-tenant SaaS.
- Semua fitur SAP secara 1:1.
- Semua proses harus custom-coded.

Fitur advanced dapat menjadi Phase 2/3.

---

# 5. Target Users

## 5.1 Executive

- Director
- CEO
- Owner
- Management

Kebutuhan:

- KPI
- Revenue
- Expense
- Profit
- Cash
- AR
- AP
- Inventory
- Purchasing
- Sales
- Approval

## 5.2 Department Manager

- Approval
- Department dashboard
- Budget
- Tasks
- Reports

## 5.3 Finance

- Accounting
- AR
- AP
- Payment
- Tax
- Bank
- Closing
- Financial reporting

## 5.4 Purchasing

- Supplier
- PR
- RFQ
- Quotation
- PO
- Receipt
- Purchasing reports

## 5.5 Warehouse

- Item
- Stock
- Goods Receipt
- Goods Issue
- Transfer
- Stock Opname
- Batch/Serial

## 5.6 Sales

- Customer
- Quotation
- Sales Order
- Delivery
- Invoice
- Payment
- Sales reporting

## 5.7 Asset / IT

- Asset
- IT device
- Assignment
- Warranty
- Maintenance
- Depreciation
- Disposal

## 5.8 HR

- Employee
- Attendance
- Leave
- Payroll
- Organization

## 5.9 System Administrator

- Configuration
- User
- Role
- Permission
- Workflow
- Integration
- Audit
- System settings

---

# 6. Organization Model

Recommended hierarchy:

```text
Company
 ├── Branch
 │    ├── Department
 │    ├── Warehouse
 │    └── Cost Center
 │
 ├── Profit Center
 ├── Project
 └── Business Unit
```

System harus dapat menghubungkan transaksi dengan:

- Company
- Branch
- Department
- Warehouse
- Cost Center
- Profit Center
- Project

---

# 7. Core Modules

## 7.1 Core Administration

- Company Settings
- Organization
- Branch
- Department
- Warehouse
- Currency
- Tax
- Fiscal Year
- Accounting Period
- Number Series
- UOM
- Document Templates
- System Preferences

## 7.2 Security

- User
- Role
- Permission
- Permission Group
- Record-level access
- Field-level restrictions
- Approval authority
- Login history
- Session management
- MFA/2FA
- API credentials

## 7.3 Master Data

### Business Partner

- Customer
- Supplier
- Contact
- Address
- Billing address
- Shipping address
- Payment terms
- Credit limit
- Tax information

### Item

- SKU
- Item code
- Item name
- Category
- Brand
- UOM
- Barcode
- Warehouse
- Minimum stock
- Maximum stock
- Reorder point
- Batch
- Serial
- Expiry
- Cost
- Selling price
- Tax

---

# 8. Procurement Module

Recommended process:

```text
Purchase Request
        ↓
Approval
        ↓
RFQ
        ↓
Supplier Quotation
        ↓
Comparison
        ↓
Purchase Order
        ↓
Goods Receipt
        ↓
Supplier Invoice
        ↓
Payment
```

## 8.1 Purchase Request

Fields:

- PR Number
- Requester
- Department
- Cost Center
- Item/service
- Quantity
- Estimated price
- Required date
- Reason
- Attachment
- Project
- Approval status

## 8.2 RFQ

- RFQ Number
- Suppliers
- Items
- Quantity
- Required date
- Terms
- Response

## 8.3 Supplier Quotation

- Supplier
- Price
- Discount
- Tax
- Lead time
- Payment terms
- Validity

## 8.4 Purchase Order

- PO number
- Supplier
- Items
- Quantity
- Price
- Tax
- Warehouse
- Delivery date
- Payment terms
- Approval
- Attachments

## 8.5 Goods Receipt

Effects:

- Inventory increase.
- Stock ledger entry.
- Batch/serial registration.
- GR/IR accounting if configured.

## 8.6 Supplier Invoice

Effects:

- Accounts Payable.
- Tax.
- Accounting journal.

---

# 9. Inventory & Warehouse

Inventory must use an immutable stock movement ledger.

```text
Opening
Purchase Receipt
Sales Delivery
Goods Issue
Goods Receipt
Transfer
Adjustment
Return
Stock Opname
```

## 9.1 Warehouse

Support:

- Multiple warehouses
- Bin/location
- Stock availability
- Reserved stock
- Available stock
- On-order stock
- Committed stock

## 9.2 Stock Transfer

```text
Warehouse A
     ↓
Transfer Request
     ↓
Approval
     ↓
Warehouse B
```

## 9.3 Stock Opname

- Counting sheet
- Physical quantity
- System quantity
- Variance
- Adjustment
- Approval
- Audit trail

## 9.4 Costing

Recommended MVP:

- Moving Average
- Standard Cost

Future:

- FIFO

---

# 10. Sales Module

Process:

```text
Quotation
    ↓
Sales Order
    ↓
Delivery
    ↓
Customer Invoice
    ↓
Payment
```

## 10.1 Sales Quotation

- Customer
- Items
- Quantity
- Price
- Discount
- Tax
- Validity
- Terms

## 10.2 Sales Order

- Customer
- Items
- Delivery date
- Warehouse
- Payment terms
- Approval

## 10.3 Delivery

Effects:

- Inventory decrease.
- Stock ledger.
- Serial/batch deduction.

## 10.4 Customer Invoice

Effects:

- Accounts Receivable.
- Revenue.
- Tax.
- General Ledger.

---

# 11. Finance & Accounting

Finance is a core module, not an optional reporting layer.

## 11.1 Chart of Accounts

Support:

- Account code
- Account name
- Account type
- Parent account
- Active/inactive
- Control account
- Tax account
- Cost center requirement

## 11.2 General Ledger

Every relevant business transaction can create accounting entries.

Example:

```text
Goods Receipt
    ↓
Inventory Dr
    ↓
GR/IR Cr
```

Invoice:

```text
GR/IR Dr
Tax Dr
    ↓
Accounts Payable Cr
```

Sales:

```text
Accounts Receivable Dr
    ↓
Revenue Cr
Tax Cr
```

## 11.3 Accounts Payable

- Supplier invoice
- Due date
- Payment
- Aging
- Credit note
- Debit note

## 11.4 Accounts Receivable

- Customer invoice
- Due date
- Receipt
- Aging
- Credit note
- Collection status

## 11.5 Cash & Bank

- Cash account
- Bank account
- Receipt
- Payment
- Transfer
- Reconciliation

## 11.6 Tax

Indonesia-first configuration:

- PPN/VAT
- Tax code
- Tax rate
- Input tax
- Output tax
- Tax reporting support

Tax rules must remain configurable rather than hard-coded.

## 11.7 Period Closing

Support:

```text
Open
↓
Period Processing
↓
Review
↓
Close
↓
Locked
```

Closed financial periods cannot be changed through normal CRUD operations.

---

# 12. Fixed Asset Management

Asset lifecycle:

```text
Purchase
 ↓
Capitalization
 ↓
Assignment
 ↓
Maintenance
 ↓
Transfer
 ↓
Depreciation
 ↓
Disposal
```

Fields:

- Asset code
- Asset category
- Purchase date
- Acquisition cost
- Useful life
- Depreciation method
- Residual value
- Location
- Employee
- Department
- Warranty
- Serial number
- Status

MVP depreciation:

- Straight Line

Future:

- Declining Balance
- Tax depreciation rules

---

# 13. IT Asset Management

The ERP should absorb the useful scope of the previous iNV IT concept.

Supported:

- Laptop
- Desktop
- Monitor
- Printer
- Server
- Network device
- Mobile device
- License
- SIM
- Peripheral

Fields:

```text
Asset
├── Serial Number
├── IMEI
├── IP Address
├── MAC Address
├── OS
├── Software
├── Warranty
├── Assigned User
├── Department
└── Location
```

---

# 14. Monitoring Module

Basic infrastructure monitoring:

- Device
- Server
- IP Address
- Port
- Ping
- Uptime
- Status
- Last check
- Response time

Monitoring target types:

- Server
- Router
- Switch
- Access Point
- Printer
- Application endpoint
- Generic IP

MVP monitoring:

```text
Ping
Port Check
Uptime
Availability
```

Future:

- CPU
- RAM
- Disk
- SNMP
- Agent
- Metrics
- Alerting

---

# 15. Maintenance

Asset maintenance:

- Maintenance schedule
- Preventive maintenance
- Corrective maintenance
- Maintenance request
- Technician
- Cost
- Parts
- Service history
- Warranty

---

# 16. HR Module

Recommended initial scope:

- Employee
- Department
- Position
- Employee assignment
- Attendance
- Leave
- Payroll foundation

Advanced HR can be Phase 2.

---

# 17. CRM

Basic CRM:

- Lead
- Customer
- Contact
- Opportunity
- Activity
- Follow-up
- Sales pipeline

Future:

- Campaign
- Marketing automation
- Customer portal

---

# 18. Helpdesk / Ticketing

Ticket lifecycle:

```text
Open
 ↓
Assigned
 ↓
In Progress
 ↓
Pending
 ↓
Resolved
 ↓
Closed
```

Ticket fields:

- Ticket number
- Requester
- Category
- Priority
- Assignee
- Department
- SLA
- Status
- Description
- Attachment
- Related asset
- Related customer
- Related document

---

# 19. Todo / Task

Task system:

- Personal task
- Team task
- Department task
- Related transaction
- Related ticket
- Due date
- Priority
- Assignee
- Checklist
- Reminder
- Status

---

# 20. Memo / Notes

Support:

- Personal memo
- Department memo
- Shared memo
- Transaction note
- Internal note
- Rich text
- Attachment
- Tag
- Search

---

# 21. Document Management

Documents can be attached to any business object.

Example:

```text
Purchase Order
├── Supplier Quotation
├── Contract
├── Delivery Document
├── Invoice
└── Signed Document
```

Requirements:

- Upload
- Preview
- Download
- Versioning
- Access control
- Document type
- Metadata
- Retention
- Audit trail

---

# 22. Digital Signature & Share Link

The system must support secure document sharing.

Flow:

```text
Document
   ↓
Generate Share Link
   ↓
Recipient Access
   ↓
Review
   ↓
Digital Signature
   ↓
Signed
   ↓
Audit Trail
```

Share-link controls:

- Unique token
- Expiration
- Password optional
- View/download permission
- Signature requirement
- Revocation
- Access log

Digital signature should record:

- Signer
- Timestamp
- Document hash
- IP/session metadata where legally appropriate
- Signature status
- Audit event

Canva may be used for document/template design where useful, but the ERP must not depend on Canva for its core transaction/signature architecture.

---

# 23. Approval Workflow Engine

Approval must be configurable.

Example:

```text
IF PO < 5M
    → Manager

IF PO >= 5M AND < 100M
    → Manager
    → Finance

IF PO >= 100M
    → Manager
    → Finance
    → Director
```

Supported conditions:

- Amount
- Department
- Cost Center
- Branch
- Project
- Document type
- User role
- Item category

Workflow capabilities:

- Sequential approval
- Parallel approval
- Conditional approval
- Delegation
- Reject
- Return for revision
- Escalation
- Approval deadline
- Approval history

---

# 24. Budget Management

Support:

- Annual budget
- Department budget
- Cost center budget
- Project budget
- Expense budget
- Purchase budget

Budget control:

```text
Budget
  ↓
Committed
  ↓
Actual
  ↓
Remaining
```

System may warn or block transactions exceeding configured limits.

---

# 25. Project Accounting

Project master:

- Project code
- Project name
- Customer
- Manager
- Start/end date
- Budget
- Cost center

Track:

- Revenue
- Expense
- Purchase
- Time
- Inventory
- Asset
- Profitability

---

# 26. Manufacturing

Manufacturing is optional for MVP unless the company is manufacturing-oriented.

Future module:

- BOM
- Production Order
- Work Order
- Raw Material
- WIP
- Finished Goods
- Production Cost
- MRP
- Capacity Planning
- Quality

---

# 27. Reporting & BI

## Executive Dashboard

- Revenue
- Gross profit
- Net profit
- Cash
- AR
- AP
- Inventory value
- Sales
- Purchasing
- Budget

## Finance Dashboard

- P&L
- Balance Sheet
- Cash Flow
- AR aging
- AP aging
- Tax
- GL

## Inventory Dashboard

- Stock value
- Low stock
- Overstock
- Slow moving
- Fast moving
- Stock movement

## Purchasing Dashboard

- Purchase value
- Supplier performance
- PO outstanding
- Price comparison

## Sales Dashboard

- Sales
- Orders
- Conversion
- Customer ranking
- Product ranking

---

# 28. AI Assistant

AI must be permission-aware.

The AI must never bypass ERP permissions.

Example questions:

- "Berapa total penjualan bulan ini?"
- "Berapa outstanding invoice?"
- "Supplier mana paling kompetitif?"
- "Item mana hampir habis?"
- "Mengapa inventory item X turun?"
- "Buat draft Purchase Request."
- "Buat ringkasan laporan keuangan."
- "Tunjukkan invoice overdue."

AI actions require explicit permission and confirmation for high-impact operations.

Architecture:

```text
User
 ↓
AI Assistant
 ↓
Intent / Tool Router
 ↓
Permission Check
 ↓
ERP Service/API
 ↓
Result
 ↓
AI Response
```

AI must not directly access the database without a controlled service layer.

---

# 29. Automation Engine

Example:

```text
WHEN stock < reorder point
THEN create purchase recommendation
AND notify purchasing
```

```text
WHEN invoice overdue > 7 days
THEN create collection task
AND notify finance
```

```text
WHEN asset warranty expires < 30 days
THEN create maintenance task
```

Automation must support:

- Trigger
- Condition
- Action
- Schedule
- Notification
- Task
- Webhook
- Document generation

---

# 30. Notification

Channels:

- In-app
- Email
- WhatsApp integration in future
- Push notification in future

Events:

- Approval request
- Approval result
- Ticket assignment
- Low stock
- Overdue invoice
- Payment
- Asset warranty
- Monitoring alert

---

# 31. Audit Trail

Audit must be immutable from normal application interfaces.

Track:

- Login
- Logout
- Create
- Update
- Delete/void
- Approval
- Rejection
- Posting
- Payment
- Period closing
- Permission change
- Document access
- Signature
- API action

Audit record:

```text
Actor
Timestamp
Action
Entity
Record ID
Before
After
IP
User Agent
Request ID
```

---

# 32. Security Requirements

Enterprise-grade baseline:

- RBAC
- Record-level permission
- MFA/2FA
- Password policy
- Session control
- CSRF protection
- API authentication
- Rate limiting
- Encryption in transit
- Encryption at rest where appropriate
- Secret management
- Audit logs
- Backup
- Disaster recovery
- Secure file access
- Signed URLs
- Security headers

Principle:

> Deny by default, explicitly grant access.

---

# 33. API Architecture

Backend must expose REST APIs.

Recommended structure:

```text
/api/v1/auth
/api/v1/users
/api/v1/items
/api/v1/customers
/api/v1/suppliers
/api/v1/purchase-requests
/api/v1/purchase-orders
/api/v1/goods-receipts
/api/v1/sales-orders
/api/v1/deliveries
/api/v1/invoices
/api/v1/payments
/api/v1/inventory
/api/v1/assets
/api/v1/tickets
/api/v1/workflows
/api/v1/reports
/api/v1/ai
```

Requirements:

- Versioning
- Authentication
- Authorization
- Validation
- Pagination
- Filtering
- Sorting
- Idempotency where necessary
- Standard errors
- Request ID
- OpenAPI documentation

---

# 34. Integration Layer

Future integrations:

- Banking
- Payment gateway
- Tax services
- E-commerce
- Marketplace
- POS
- WhatsApp
- Email
- Google Workspace
- Microsoft 365
- Attendance/biometric
- External monitoring
- SAP Business One
- Other ERP

Integration architecture:

```text
ERP
 │
Integration Layer
 ├── REST API
 ├── Webhook
 ├── Queue
 ├── Import/Export
 └── Connector
```

---

# 35. Import / Export

Support:

- CSV
- XLSX
- PDF
- JSON
- API

Import must provide:

1. Template.
2. Upload.
3. Validation.
4. Preview.
5. Error report.
6. Confirmation.
7. Transaction.
8. Import log.

---

# 36. Number Series

Configurable per document type and optionally per branch.

Examples:

```text
PR-2026-000001
PO-2026-000001
GR-2026-000001
SO-2026-000001
INV-2026-000001
PAY-2026-000001
AST-2026-000001
```

Posted document numbers should not be casually modified.

---

# 37. Document Lifecycle

Documents should have states.

Generic model:

```text
Draft
 ↓
Submitted
 ↓
Pending Approval
 ↓
Approved
 ↓
Posted
 ↓
Closed
```

Correction should use proper business documents such as:

- Cancel
- Reverse
- Credit Note
- Debit Note
- Adjustment

rather than destructive database manipulation.

---

# 38. Data Integrity Principles

Critical financial and inventory records must use transaction-safe operations.

Requirements:

- Database transactions
- Immutable ledger
- Referential integrity
- Unique business keys
- Foreign keys
- Validation
- Concurrency control
- Idempotency
- Soft delete only where appropriate
- Void/reversal for posted transactions

---

# 39. Recommended Database Domain Architecture

Avoid putting the entire ERP blindly into one generic schema.

Recommended PostgreSQL logical domains:

```text
core
master
security
workflow
procurement
inventory
sales
finance
asset
maintenance
crm
hr
helpdesk
document
project
reporting
integration
automation
audit
```

The exact PostgreSQL implementation may use schemas or a carefully bounded table namespace, but domain boundaries must remain explicit.

---

# 40. Technology Stack

## Backend

- Laravel 13
- PHP 8.2+
- REST API

## Frontend

- Next.js
- App Router
- TypeScript
- Modern component system
- Responsive enterprise UI

## Database

- PostgreSQL 16+

## Cache / Queue

- Redis

## Infrastructure

- Docker
- Docker Compose for initial deployment
- Nginx
- Linux/Ubuntu Server

## Storage

Object/file storage abstraction should support:

- Local storage
- S3-compatible storage in future

---

# 41. UI/UX Requirements

ERP UI should be information-dense but not visually outdated.

Requirements:

- Responsive
- Desktop-first
- Mobile-friendly for approvals/tasks
- Global search
- Command/search interface
- Data tables
- Filters
- Saved views
- Bulk actions
- Keyboard-friendly workflows
- Clear status badges
- Timeline/history
- Dashboard widgets
- Consistent form system

Each document should clearly show:

```text
Header
Status
Main Data
Lines
Totals
Attachments
Workflow
Activity
Audit
Related Documents
```

---

# 42. Global Search

Search across:

- Customers
- Suppliers
- Items
- Documents
- Invoices
- Orders
- Assets
- Tickets
- Employees
- Projects

Future:

- Semantic AI search.

---

# 43. Related Documents

ERP must provide document flow.

Example:

```text
PR
 ↓
RFQ
 ↓
Supplier Quotation
 ↓
PO
 ↓
GR
 ↓
AP Invoice
 ↓
Payment
```

Users must be able to navigate backward/forward through the chain.

---

# 44. Master Data Governance

Master data changes require controlled access.

Important master data:

- Item
- Customer
- Supplier
- Chart of Accounts
- Tax
- Warehouse
- Employee
- Asset

Critical changes should create audit events.

---

# 45. Performance Requirements

Initial target:

- Standard page response under 2 seconds for normal operations.
- API response under 1 second for ordinary CRUD/query operations where practical.
- Pagination required for large datasets.
- Background jobs for heavy processing.
- Reports should use optimized queries or reporting structures.
- Long-running jobs must not block web requests.

---

# 46. Reliability

Target:

- Transaction integrity over raw speed.
- Safe retry.
- Queue retry.
- Failed job tracking.
- Health checks.
- Monitoring.
- Structured logs.
- Database backup.
- Restore testing.

---

# 47. Backup & Disaster Recovery

Minimum:

- Daily database backup.
- Configurable retention.
- Backup verification.
- File backup.
- Restore procedure.
- Disaster recovery documentation.

Future target:

- Point-in-time recovery.
- Offsite backup.
- Automated restore test.

---

# 48. Observability

System should provide:

- Application logs
- API logs
- Queue logs
- Audit logs
- Database monitoring
- Error tracking
- Health endpoint
- Job monitoring

Future:

- OpenTelemetry
- Metrics
- Distributed tracing

---

# 49. MVP Scope

MVP should prioritize the transaction core.

## Phase 1 — Foundation

- Company
- Organization
- User
- Role
- Permission
- Security
- Master Data
- Workflow
- Audit

## Phase 2 — Procurement

- Supplier
- PR
- RFQ
- Supplier Quotation
- PO
- GR

## Phase 3 — Inventory

- Item
- Warehouse
- Stock Ledger
- Transfer
- Stock Opname
- Batch/Serial

## Phase 4 — Sales

- Customer
- Quotation
- SO
- Delivery
- Invoice

## Phase 5 — Finance

- COA
- GL
- AR
- AP
- Payment
- Tax
- Period Closing
- P&L
- Balance Sheet

## Phase 6 — Asset

- Fixed Asset
- Depreciation
- IT Asset
- Maintenance

## Phase 7 — Productivity

- Todo
- Memo
- Ticket
- Document
- Share Link
- Digital Signature

## Phase 8 — Reporting

- Executive Dashboard
- Finance
- Inventory
- Purchasing
- Sales

---

# 50. Phase 2

- CRM
- HR
- Payroll
- Budget
- Project Accounting
- Advanced maintenance
- Advanced reports
- Notification integrations
- External integrations

---

# 51. Phase 3

- Manufacturing
- BOM
- MRP
- Quality
- Advanced warehouse
- Advanced planning
- Advanced AI
- Predictive analytics

---

# 52. AI & Automation Roadmap

## AI Level 1

Read-only assistant:

- Search
- Summarize
- Explain
- Report

## AI Level 2

Recommendation:

- Reorder recommendation
- Supplier recommendation
- Cash-flow insight
- Collection priority
- Inventory optimization

## AI Level 3

Controlled action:

```text
AI proposes
 ↓
Permission check
 ↓
Human confirmation
 ↓
ERP transaction
```

No autonomous high-risk financial posting without explicit policy and approval.

---

# 53. Key Business Rules

1. Posted transactions are not hard deleted.
2. Financial transactions require proper accounting impact.
3. Inventory movement must always have a traceable source.
4. Every approval must be auditable.
5. Closed accounting periods are locked.
6. Permission must be checked server-side.
7. AI must use the same authorization model as users.
8. Critical documents must have immutable history.
9. Number series must be controlled.
10. Stock quantity must come from the stock ledger, not arbitrary manual updates.
11. Financial balances must be reproducible from ledger transactions.
12. Reversal is preferred over destructive editing.
13. Every external integration must be observable.
14. Background jobs must be retry-safe.

---

# 54. Acceptance Criteria — Core ERP

The ERP is considered functionally viable when:

- A company can be configured.
- Users and roles can be created.
- Permissions work server-side.
- Items, customers, and suppliers can be maintained.
- Purchase Request can be created and approved.
- PO can be generated.
- Goods Receipt increases inventory.
- Sales Order can be created.
- Delivery decreases inventory.
- Invoice creates AR/AP correctly.
- Payments update outstanding balances.
- Accounting entries can be traced.
- Financial reports reconcile with ledger data.
- Inventory can be traced through stock movements.
- Asset lifecycle can be tracked.
- Approval history is auditable.
- Documents can be attached.
- Share links are secured.
- Audit trail exists.
- Backup and restore procedure is tested.
- API documentation exists.

---

# 55. Reconciliation Requirements

Critical reconciliation reports:

## Inventory

```text
Stock Ledger
      =
Warehouse Stock
      =
Inventory Valuation
```

## Finance

```text
Subledger AR
      =
AR Control Account

Subledger AP
      =
AP Control Account

Inventory Valuation
      =
Inventory GL Account
```

Differences must be detectable.

---

# 56. Reporting Requirements

Mandatory reports:

### Finance

- General Ledger
- Trial Balance
- P&L
- Balance Sheet
- Cash Flow
- AR Aging
- AP Aging
- Tax
- Journal

### Inventory

- Stock Card
- Stock Balance
- Stock Valuation
- Stock Movement
- Low Stock
- Stock Opname Variance

### Purchasing

- Purchase Summary
- Supplier Performance
- PO Outstanding
- Purchase Price History

### Sales

- Sales Summary
- Customer Sales
- Product Sales
- Invoice Outstanding
- Sales Margin

### Asset

- Asset Register
- Depreciation
- Asset Movement
- Maintenance Cost

---

# 57. ERP Navigation

Recommended main navigation:

```text
Dashboard

Master Data
├── Business Partner
├── Items
├── Chart of Accounts
├── Tax
├── Warehouse
├── UOM
└── Price Lists

Purchasing
├── Purchase Request
├── RFQ
├── Supplier Quotation
├── Purchase Order
├── Goods Receipt
└── Supplier Invoice

Sales
├── Quotation
├── Sales Order
├── Delivery
├── Customer Invoice
└── Incoming Payment

Inventory
├── Stock
├── Transfer
├── Adjustment
├── Stock Opname
└── Batch/Serial

Finance
├── Journal
├── General Ledger
├── AR
├── AP
├── Banking
├── Tax
└── Period Closing

Asset
├── Fixed Asset
├── IT Asset
├── Maintenance
└── Depreciation

CRM
├── Leads
├── Opportunities
├── Customers
└── Activities

HR
├── Employee
├── Attendance
├── Leave
└── Payroll

Project
├── Projects
├── Budget
└── Project Cost

Helpdesk
├── Tickets
├── SLA
└── Knowledge Base

Productivity
├── Todo
├── Memo
└── Documents

Reports
├── Executive
├── Finance
├── Sales
├── Purchasing
├── Inventory
└── Asset

AI & Automation
├── AI Assistant
├── Recommendations
├── Automations
└── AI Activity

Administration
├── Company
├── Users
├── Roles
├── Permissions
├── Workflow
├── Number Series
├── Integrations
├── Notifications
└── Audit Log
```

---

# 58. Architecture Principle

ERP harus dibangun sebagai **modular monolith terlebih dahulu**, bukan microservices sejak awal.

Recommended:

```text
Next.js
   ↓
Laravel REST API
   ↓
Domain/Application Services
   ↓
PostgreSQL
   ↓
Redis / Queue
```

Domain boundaries tetap harus jelas sehingga modul dapat dipisahkan menjadi service di masa depan jika benar-benar diperlukan.

---

# 59. Domain Architecture

Recommended bounded domains:

```text
Core
Security
Master Data
Workflow
Procurement
Inventory
Sales
Finance
Asset
Maintenance
CRM
HR
Project
Helpdesk
Document
Reporting
Integration
Automation
AI
Audit
```

Cross-domain transaction examples:

```text
Procurement
    ↓
Inventory
    ↓
Finance
```

```text
Sales
    ↓
Inventory
    ↓
Finance
```

```text
Asset
    ↓
Finance
    ↓
Maintenance
```

---

# 60. Development Principles

Development must follow:

1. Domain-first.
2. API-first.
3. Security-first.
4. Transaction integrity-first.
5. Auditability-first.
6. Configuration over hardcoding.
7. Reusable components.
8. Testable business rules.
9. Migration-safe database evolution.
10. Backward-compatible APIs where practical.

---

# 61. Testing Strategy

Testing layers:

```text
Unit Test
   ↓
Feature Test
   ↓
API Test
   ↓
Integration Test
   ↓
Database Test
   ↓
Browser/UI Test
   ↓
End-to-End Business Flow
```

Mandatory E2E scenarios:

### Procure-to-Pay

```text
PR
→ Approval
→ PO
→ GR
→ Invoice
→ Payment
→ GL
```

### Order-to-Cash

```text
Quotation
→ SO
→ Delivery
→ Invoice
→ Payment
→ GL
```

### Inventory

```text
Receipt
→ Transfer
→ Delivery
→ Opname
→ Adjustment
```

### Asset

```text
Purchase
→ Capitalization
→ Assignment
→ Depreciation
→ Disposal
```

---

# 62. Browser Testing

Testing must not stop at backend tests.

Browser tests must validate:

- Login
- Navigation
- Form submission
- Table filtering
- Search
- Approval
- Buttons
- Modals
- Upload
- Share links
- Signature
- Dashboard
- Error handling

Critical transaction flows must be tested from the actual UI.

---

# 63. Security Testing

Required:

- Authentication tests
- Authorization tests
- Permission bypass tests
- IDOR tests
- Input validation
- File upload security
- API abuse
- Rate limiting
- Session security
- Audit integrity

---

# 64. Migration Strategy

Every schema change must use versioned migrations.

Production deployment:

```text
Backup
 ↓
Migration
 ↓
Health Check
 ↓
Smoke Test
 ↓
Release
```

Rollback strategy must exist for risky releases.

---

# 65. Data Migration

Legacy data import should support:

- Customers
- Suppliers
- Items
- Opening stock
- Opening AR
- Opening AP
- Opening balances
- Assets
- Employees

Migration process:

```text
Extract
 ↓
Clean
 ↓
Map
 ↓
Validate
 ↓
Preview
 ↓
Import
 ↓
Reconcile
```

---

# 66. Opening Balance

Go-live requires controlled opening balances:

- Cash
- Bank
- AR
- AP
- Inventory
- Fixed Assets
- Accumulated Depreciation
- Other GL accounts

Opening balances must be separately identified and auditable.

---

# 67. Go-Live Strategy

Recommended:

### Stage 1

Master data.

### Stage 2

Procurement + Inventory.

### Stage 3

Sales.

### Stage 4

Finance.

### Stage 5

Asset.

### Stage 6

Advanced modules.

Do not activate all modules simultaneously unless the implementation team is sufficiently prepared.

---

# 68. Success Metrics

ERP success should be measured through:

- Reduction in spreadsheet usage.
- Reduction in duplicate data entry.
- Faster approval time.
- Inventory accuracy.
- AR collection visibility.
- AP visibility.
- Financial closing time.
- Audit traceability.
- Transaction processing time.
- User adoption.
- System uptime.

---

# 69. Critical Risks

## Risk: Scope Explosion

Mitigation:

- Modular roadmap.
- MVP boundaries.
- No unnecessary SAP cloning.

## Risk: Accounting Errors

Mitigation:

- Accounting rules.
- Reconciliation.
- Period locking.
- Finance approval.
- Automated tests.

## Risk: Inventory Inaccuracy

Mitigation:

- Immutable stock ledger.
- Controlled adjustments.
- Stock opname.
- Audit.

## Risk: Excessive Customization

Mitigation:

- Configuration-first design.
- Generic workflow.
- Generic approval engine.
- Reusable document engine.

## Risk: AI Incorrect Actions

Mitigation:

- Permission-aware tools.
- Read-only default.
- Human confirmation.
- Full audit trail.

---

# 70. Recommended Product Positioning

The product should be described internally as:

> **Enterprise Internal ERP inspired by SAP Business One**

and not:

> "SAP clone."

The target is to reproduce the **business process discipline and integration philosophy** of SAP Business One while maintaining a simpler architecture and UX suitable for the company's actual needs.

---

# 71. Final Product Blueprint

```text
                         INTERNAL ERP
                              │
       ┌──────────────────────┼──────────────────────┐
       │                      │                      │
    MASTER                 SECURITY               WORKFLOW
       │                      │                      │
       └──────────────────────┼──────────────────────┘
                              │
       ┌─────────────── CORE BUSINESS ───────────────┐
       │                                              │
  PROCUREMENT       INVENTORY       SALES          FINANCE
       │                │             │                │
       └────────────────┼─────────────┼────────────────┘
                        │
                     ASSET
                        │
                 MAINTENANCE
                        │
        ┌───────────────┼────────────────┐
        │               │                │
       CRM             HR             PROJECT
        │               │                │
        └───────────────┼────────────────┘
                        │
              DOCUMENT / SIGNATURE
                        │
             HELPDESK / TODO / MEMO
                        │
                 REPORTING / BI
                        │
                AI / AUTOMATION
                        │
                 INTEGRATION API
```

---

# 72. Final Recommendation

Build the ERP in this order:

```text
FOUNDATION
    ↓
MASTER DATA
    ↓
SECURITY + WORKFLOW
    ↓
PROCUREMENT
    ↓
INVENTORY
    ↓
SALES
    ↓
FINANCE / ACCOUNTING
    ↓
ASSET
    ↓
REPORTING
    ↓
DOCUMENT + SIGNATURE
    ↓
HELPDESK / TODO / MEMO
    ↓
CRM / HR / PROJECT
    ↓
AI + AUTOMATION
    ↓
MANUFACTURING / ADVANCED MODULES
```

The most important architectural decision is to make **Finance, Inventory, Procurement, and Sales a single integrated transaction engine**, rather than building independent CRUD modules.

The second most important decision is to make **Workflow, Permission, Audit, Document, Notification, and Number Series reusable platform capabilities** used by every module.

The third is to make **AI an authorized application layer over ERP services**, not a direct database chatbot.

This approach provides the strongest foundation for a company-internal ERP that follows SAP Business One concepts while remaining maintainable, modern, and practical to build.
