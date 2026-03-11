# Stored Procedures Documentation

This document lists all stored procedures referenced by Python integrations in this repository, including GI and PROD variants.

## Source Coverage

- Code scanned: `GI-Python/*.py`, `python-prod/*.py`
- SQL scanned: `import_gi_prod_23.sql`

## Important Note About SQL Dump

`import_gi_prod_23.sql` does **not** include `CREATE PROCEDURE` definitions. It appears to be a table schema/data dump. Procedure definitions must be maintained separately (or exported with routines enabled).

## Stored Procedures Inventory

| Procedure | Schema in Call | Signature Observed in Code | Where Called | Purpose (Inferred) |
|---|---|---|---|---|
| `sp_migrate_data_from_pim_v11` | default DB connection (`import_gi_prod` in current PROD script) | `sp_migrate_data_from_pim_v11(0)` and `sp_migrate_data_from_pim_v11(1)` | `python-prod/import_pim_server.py`, `GI-Python/import_pim_server.py`, `GI-Python/import_pim_local.py`, `GI-Python/import_pim_tst.py` | Migrate imported PIM payload from staging/import table(s) into portal structures |
| `sp_ImportPricesFromSAP_v4` | `import_gi_prod` | `import_gi_prod.sp_ImportPricesFromSAP_v4(1)` | `python-prod/MovePricesFromSTGToProd.py`, `GI-Python/MovePricesFromSTGToProd_DEV.py` | Sync staged SAP pricing into production-facing price tables |
| `sp_ImportPricesFromSAP` | `import_gi` | `import_gi.sp_ImportPricesFromSAP(1)` | `GI-Python/MovePricesFromSTGToProd.py` | Legacy/QA variant of SAP pricing sync |
| `sp_insertStocksIntoPortal` | default DB connection (`import_gi_prod` or `import_gi` depending on script) | `sp_insertStocksIntoPortal()` (executed as `call sp_insertStocksIntoPortal;`) | `python-prod/import_stocks.py`, `GI-Python/import_stocks.py` | Move staged stock feed into portal stock tables |
| `sp_SyncInvoices` | default DB connection (`import_gi_prod` or `import_gi`) | `sp_SyncInvoices()` and `sp_SyncInvoices(1)` | `python-prod/import_invoices.py`, `GI-Python/import_invoices.py` | Sync staged invoice metadata/files into portal invoice model |
| `sp_UPSERT_Product_to_PIM` | default DB connection (`stg_pim_gi`) | `sp_UPSERT_Product_to_PIM()` (executed as `call sp_UPSERT_Product_to_PIM;`) | `GI-Python/import_stg_to_pim.py`, `GI-Python/import_stg_to_pim_server.py` | Upsert staged product records back into PIM |

## Legacy/Commented Procedure References

| Procedure | Status | File |
|---|---|---|
| `sp_migrate_data_from_pim_v10` | Present only in logs/comments or commented-out call | `python-prod/import_pim_server.py`, `GI-Python/import_pim_server.py`, `GI-Python/import_pim_local.py`, `GI-Python/import_pim_tst.py` |

## Parameter Usage Summary

- `sp_migrate_data_from_pim_v11`: observed with `0` and `1`
- `sp_ImportPricesFromSAP_v4`: observed with `1`
- `sp_ImportPricesFromSAP`: observed with `1`
- `sp_SyncInvoices`: observed with no arg and with `1`
- `sp_insertStocksIntoPortal`: no arguments observed
- `sp_UPSERT_Product_to_PIM`: no arguments observed

## Procedure Call Locations (Quick Index)

- `python-prod/import_pim_server.py`: `sp_migrate_data_from_pim_v11`
- `python-prod/MovePricesFromSTGToProd.py`: `import_gi_prod.sp_ImportPricesFromSAP_v4(1)`
- `python-prod/import_stocks.py`: `sp_insertStocksIntoPortal`
- `python-prod/import_invoices.py`: `sp_SyncInvoices()`
- `GI-Python/import_pim_server.py`: `sp_migrate_data_from_pim_v11`
- `GI-Python/import_pim_local.py`: `sp_migrate_data_from_pim_v11`
- `GI-Python/import_pim_tst.py`: `sp_migrate_data_from_pim_v11(1)`
- `GI-Python/MovePricesFromSTGToProd.py`: `import_gi.sp_ImportPricesFromSAP(1)`
- `GI-Python/MovePricesFromSTGToProd_DEV.py`: `import_gi_prod.sp_ImportPricesFromSAP_v4(1)`
- `GI-Python/import_stocks.py`: `sp_insertStocksIntoPortal`
- `GI-Python/import_invoices.py`: `sp_SyncInvoices(1)`
- `GI-Python/import_stg_to_pim.py`: `sp_UPSERT_Product_to_PIM`
- `GI-Python/import_stg_to_pim_server.py`: `sp_UPSERT_Product_to_PIM`
