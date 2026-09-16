# MVP Implementation Plan — MarsERP

Derived from `PRD.md` §49 (MVP Scope) and §57 (Navigation), adapted to the Laravel 13 + Filament v4 monolith decided in [ARCHITECTURE-DECISIONS.md](ARCHITECTURE-DECISIONS.md).

Status legend: `[ ]` not started · `[F]` frontend (migration+model+Filament resource) built · `[B]` backend/business-logic hardened (approval engine wiring, accounting postings, ledger automation, tests).

---

## Phase 1 — Foundation

**Navigation group:** Administration

| Entity | Table | Filament Resource | Notes (PRD ref) |
|---|---|---|---|
| Company | `core_companies` | CompanyResource | §6, §7.1 |
| Branch | `core_branches` | BranchResource | §6 |
| Department | `core_departments` | DepartmentResource | §6 |
| CostCenter | `core_cost_centers` | CostCenterResource | §6 |
| ProfitCenter | `core_profit_centers` | ProfitCenterResource | §6 |
| Currency | `core_currencies` | CurrencyResource | §7.1 |
| FiscalYear | `core_fiscal_years` | FiscalYearResource | §7.1, §11.7 |
| AccountingPeriod | `core_accounting_periods` | AccountingPeriodResource | §7.1, §11.7 |
| NumberSeries | `core_number_series` | NumberSeriesResource | §36 |
| User | `users` (+`security_user_profiles`) | User (Filament built-in via Shield) | §7.2 |
| Role / Permission | spatie tables | Shield-generated | §7.2 |
| ApprovalWorkflow / ApprovalStep | `workflow_rules`, `workflow_steps` | WorkflowRuleResource | §23 |
| AuditLog | `audit_logs` | AuditLogResource (read-only) | §31 |
| SystemPreference | `core_system_preferences` | Settings page (Filament) | §7.1 |

## Phase 2 — Procurement

**Navigation group:** Purchasing (+ Master Data: Business Partner)

| Entity | Table | Filament Resource |
|---|---|---|
| BusinessPartner (customer/supplier) | `master_business_partners` | BusinessPartnerResource (scoped "Supplier" view) |
| PurchaseRequest (+lines) | `procurement_purchase_requests`, `_lines` | PurchaseRequestResource |
| RFQ (+lines) | `procurement_rfqs`, `_lines` | RfqResource |
| SupplierQuotation (+lines) | `procurement_supplier_quotations`, `_lines` | SupplierQuotationResource |
| PurchaseOrder (+lines) | `procurement_purchase_orders`, `_lines` | PurchaseOrderResource |
| GoodsReceipt (+lines) | `inventory_goods_receipts`, `_lines` | GoodsReceiptResource |
| SupplierInvoice (+lines) | `finance_supplier_invoices`, `_lines` | SupplierInvoiceResource |

## Phase 3 — Inventory

**Navigation group:** Inventory (+ Master Data: Items)

| Entity | Table | Filament Resource |
|---|---|---|
| ItemCategory | `master_item_categories` | ItemCategoryResource |
| UOM | `master_uoms` | UomResource |
| Item | `master_items` | ItemResource |
| Warehouse | `master_warehouses` | WarehouseResource |
| StockLedgerEntry (immutable) | `inventory_stock_ledger` | StockLedgerResource (read-only) |
| StockTransfer (+lines) | `inventory_stock_transfers`, `_lines` | StockTransferResource |
| StockAdjustment (+lines) | `inventory_stock_adjustments`, `_lines` | StockAdjustmentResource |
| StockOpname (+lines) | `inventory_stock_opnames`, `_lines` | StockOpnameResource |
| BatchSerial | `inventory_batch_serials` | BatchSerialResource |

## Phase 4 — Sales

**Navigation group:** Sales

| Entity | Table | Filament Resource |
|---|---|---|
| SalesQuotation (+lines) | `sales_quotations`, `_lines` | SalesQuotationResource |
| SalesOrder (+lines) | `sales_orders`, `_lines` | SalesOrderResource |
| Delivery (+lines) | `sales_deliveries`, `_lines` | DeliveryResource |
| CustomerInvoice (+lines) | `sales_customer_invoices`, `_lines` | CustomerInvoiceResource |
| IncomingPayment | `finance_incoming_payments` | IncomingPaymentResource |

## Phase 5 — Finance

**Navigation group:** Finance

| Entity | Table | Filament Resource |
|---|---|---|
| ChartOfAccount | `finance_chart_of_accounts` | ChartOfAccountResource |
| TaxCode | `finance_tax_codes` | TaxCodeResource |
| JournalEntry (+lines) | `finance_journal_entries`, `_lines` | JournalEntryResource |
| BankAccount | `finance_bank_accounts` | BankAccountResource |
| CashBankTransaction | `finance_cash_bank_transactions` | CashBankTransactionResource |
| OutgoingPayment | `finance_outgoing_payments` | OutgoingPaymentResource |
| PeriodClosing | uses `core_accounting_periods.status` | PeriodClosingResource (action page) |

## Phase 6 — Asset

**Navigation group:** Asset

| Entity | Table | Filament Resource |
|---|---|---|
| AssetCategory | `asset_categories` | AssetCategoryResource |
| FixedAsset | `asset_fixed_assets` | FixedAssetResource |
| AssetDepreciationEntry | `asset_depreciation_entries` | (nested / relation manager) |
| ITAsset | `asset_it_assets` | ItAssetResource |
| MaintenanceRequest | `maintenance_requests` | MaintenanceRequestResource |

## Phase 7 — Productivity

**Navigation group:** Productivity, Helpdesk, Document

| Entity | Table | Filament Resource |
|---|---|---|
| Todo | `productivity_todos` | TodoResource |
| Memo | `productivity_memos` | MemoResource |
| Document | `document_files` | DocumentResource |
| ShareLink | `document_share_links` | ShareLinkResource |
| DigitalSignature | `document_signatures` | (relation manager on Document) |
| Ticket | `helpdesk_tickets` | TicketResource |
| KnowledgeBase Article | `helpdesk_kb_articles` | KbArticleResource |

## Phase 8 — Reporting

**Navigation group:** Reports (Filament Dashboard + custom Pages/Widgets, read from data built in phases 1–7. No new master tables.)

- Executive Dashboard widgets (Revenue, Gross/Net Profit, Cash, AR/AP, Inventory value, Sales, Purchasing, Budget)
- Finance Dashboard (Trial Balance, AR/AP aging, GL)
- Inventory Dashboard (Stock value, low stock, movement)
- Purchasing Dashboard (PO outstanding, supplier performance)
- Sales Dashboard (sales, orders, product/customer ranking)

---

## Explicitly deferred (post-MVP, PRD §50/§51 "Phase 2/3")

Not built in this pass — navigation placeholders only, no CRUD yet: **CRM**, **HR/Payroll**, **Project Accounting**, **Budget Management**, **Manufacturing**, **AI Assistant / Automation Engine** (execution layer), **external Integrations**.

---

## Status (2026-09-14)

Frontend pass **complete** for all 8 phases: 49 Filament v4 resources built and verified running
(Docker: `docker compose up -d`, app at http://localhost:8080/admin). RBAC via
spatie/laravel-permission + filament-shield (`super_admin` role bypasses all checks for now;
602 granular permissions were generated by `shield:generate` and are ready to assign to the
placeholder Finance/Purchasing/Warehouse/Sales/Director roles once per-role scoping is designed).
Demo data seeded (`php artisan db:seed`): 1 company, sample master data, one seeded document per
transactional module, executive/pending-approvals dashboard widgets.

Login: `admin@marserp.test` / `password`.

Not yet done (tracked for the next, backend-hardening pass): per-resource policy registration for
non-super-admin roles, REST API (`/api/v1/*`, PRD §33), approval-workflow execution engine,
accounting-posting/stock-ledger automation, AI ERP tool-calling, CRM/HR/Project modules.

## Status (2026-09-15) — visual polish + AI Assistant pass

- **Custom Filament theme** compiled and live (copper/slate brand palette from the supplied logo,
  logo/favicon wired via `->brandLogo()`/`->favicon()`, roomier cards/sidebar, dark mode disabled).
  See ARCHITECTURE-DECISIONS.md #9.
- **Two dashboard `ChartWidget`s** added (Sales Trend line chart, Purchasing vs Sales bar chart),
  reading real seeded data.
- **AI Provider settings** (`Administration → AI Providers`): multi-provider CRUD, encrypted API
  key, masked in the table, "only one active" enforced at the model layer. See decision #10.
- **"ERP AI" full-page chat** at `/ai` (own layout, outside the panel, opens in a new tab from a
  new "AI & Automation" nav group): real OpenAI-compatible streaming chat, conversation history
  persisted per user (`ai_conversations`/`ai_messages`). Verified end-to-end against a local mock
  SSE server. See decisions #10–11 for the Guzzle `StreamHandler` + nginx buffering fix that real
  streaming required.
- **Infolist "View" pages** added for the 6 core transactional documents (PO, GR, Supplier
  Invoice, SO, Delivery, Customer Invoice) — Header/Lines/Totals/Attachments/Activity. See
  decision #12.
- Old orphaned Docker named volumes removed; Postgres/Redis now bind-mount to
  `docker/postgres/data_/` and `docker/redis/data_/` (gitignored).

## Execution order for this build

1. Docs (this file + architecture decisions) ✅
2. Docker/PostgreSQL/Redis/Nginx infra
3. Laravel base: Filament v4 install, spatie/laravel-permission + filament-shield, base auth/panel
4. Phase 1 migrations + models + resources (Foundation/Administration)
5. Phase 2 → 8, in order, migrations + models + resources per table above
6. Seeders (demo Company + sample master data + demo users/roles) so every screen is reviewable with real-looking data
7. Smoke test in browser (login, navigate every nav group, open a couple of resources) + screenshot handoff

Business-logic hardening (accounting postings, stock ledger automation, approval routing execution, period-lock enforcement, reconciliation reports) is **out of scope for this pass** and continues phase-by-phase in follow-up sessions, per [ARCHITECTURE-DECISIONS.md](ARCHITECTURE-DECISIONS.md) decision #6.
