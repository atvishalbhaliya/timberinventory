-- SURESH TIMBER ERP - Multi Tenant Multi Branch Schema
CREATE DATABASE IF NOT EXISTS suresh_timber_erp;
USE suresh_timber_erp;

CREATE TABLE IF NOT EXISTS tenant_master (
  tenant_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_code VARCHAR(20) UNIQUE,
  tenant_name VARCHAR(255) NOT NULL,
  status ENUM('Active','Inactive') DEFAULT 'Active',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS branch_master (
  branch_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT NOT NULL,
  branch_code VARCHAR(20),
  branch_name VARCHAR(255),
  address TEXT,
  FOREIGN KEY (tenant_id) REFERENCES tenant_master(tenant_id)
);

CREATE TABLE IF NOT EXISTS role_master (
  role_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS user_master (
  user_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  role_id BIGINT,
  username VARCHAR(100),
  password_hash VARCHAR(255),
  full_name VARCHAR(255),
  status ENUM('Active','Inactive') DEFAULT 'Active'
);

CREATE TABLE IF NOT EXISTS party_master (
  party_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  party_code VARCHAR(30),
  party_name VARCHAR(255),
  party_type ENUM('Customer','Supplier','Customer,Supplier'),
  mobile VARCHAR(20),
  email VARCHAR(150),
  address TEXT
);

CREATE TABLE IF NOT EXISTS material_type_master (
  material_type_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  material_type_code VARCHAR(20),
  material_type_name VARCHAR(100)
);

CREATE TABLE IF NOT EXISTS uom_master (
  uom_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  uom_code VARCHAR(20),
  uom_name VARCHAR(50)
);

CREATE TABLE IF NOT EXISTS item_master (
  item_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  item_code VARCHAR(50),
  item_name VARCHAR(255),
  item_type ENUM('Raw Material','Semi Product','Finish Product','Wastage','Scrap','Consumable'),
  material_type_id BIGINT,
  uom_id BIGINT,
  length_mm DECIMAL(18,3),
  width_mm DECIMAL(18,3),
  thickness_mm DECIMAL(18,3),
  minimum_stock DECIMAL(18,3),
  opening_qty DECIMAL(18,3),
  opening_rate DECIMAL(18,2)
);

CREATE TABLE IF NOT EXISTS storage_location_master (
  location_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  location_code VARCHAR(30),
  location_name VARCHAR(100),
  location_type ENUM('Raw Material','WIP','Finished Goods','Wastage','Scrap')
);

CREATE TABLE IF NOT EXISTS team_master (
  team_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  team_code VARCHAR(30),
  team_name VARCHAR(100),
  rate_per_pallet DECIMAL(18,2),
  tds_percent DECIMAL(5,2)
);

CREATE TABLE IF NOT EXISTS pallet_model_master (
  pallet_model_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  model_code VARCHAR(50),
  model_name VARCHAR(255)
);

CREATE TABLE IF NOT EXISTS bom_master (
  bom_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  pallet_model_id BIGINT,
  version_no VARCHAR(20)
);

CREATE TABLE IF NOT EXISTS bom_material (
  bom_material_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  bom_id BIGINT,
  item_id BIGINT,
  required_qty DECIMAL(18,3),
  wastage_percent DECIMAL(8,2)
);

CREATE TABLE IF NOT EXISTS grn_master (
  grn_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  supplier_id BIGINT,
  grn_no VARCHAR(50),
  grn_date DATE
);

CREATE TABLE IF NOT EXISTS grn_detail (
  grn_detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  grn_id BIGINT,
  item_id BIGINT,
  location_id BIGINT,
  qty DECIMAL(18,3),
  rate DECIMAL(18,2)
);

CREATE TABLE IF NOT EXISTS stock_ledger (
  ledger_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  item_id BIGINT,
  location_id BIGINT,
  transaction_date DATETIME,
  transaction_type VARCHAR(50),
  qty_in DECIMAL(18,3),
  qty_out DECIMAL(18,3),
  balance_qty DECIMAL(18,3)
);

CREATE TABLE IF NOT EXISTS stock_summary (
  stock_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  item_id BIGINT,
  location_id BIGINT,
  stock_qty DECIMAL(18,3),
  avg_rate DECIMAL(18,2)
);

CREATE TABLE IF NOT EXISTS production_master (
  production_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  production_no VARCHAR(50),
  production_date DATE,
  pallet_model_id BIGINT,
  team_id BIGINT,
  produced_qty DECIMAL(18,3)
);

CREATE TABLE IF NOT EXISTS production_consumption (
  consumption_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  production_id BIGINT,
  item_id BIGINT,
  consumed_qty DECIMAL(18,3)
);

CREATE TABLE IF NOT EXISTS production_output (
  output_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  production_id BIGINT,
  item_id BIGINT,
  qty DECIMAL(18,3)
);

CREATE TABLE IF NOT EXISTS production_wastage (
  wastage_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  production_id BIGINT,
  item_id BIGINT,
  qty DECIMAL(18,3)
);

CREATE TABLE IF NOT EXISTS team_ledger (
  ledger_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  team_id BIGINT,
  transaction_type ENUM('Production','Dispatch'),
  transaction_date DATE,
  qty DECIMAL(18,3)
);

CREATE TABLE IF NOT EXISTS challan_master (
  challan_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  challan_no VARCHAR(50),
  challan_date DATE,
  customer_id BIGINT,
  vehicle_no VARCHAR(50),
  total_qty DECIMAL(18,3)
);

CREATE TABLE IF NOT EXISTS challan_team_detail (
  detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  challan_id BIGINT,
  team_id BIGINT,
  qty DECIMAL(18,3)
);

CREATE TABLE IF NOT EXISTS team_payment_summary (
  payment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  team_id BIGINT,
  payment_month INT,
  payment_year INT,
  dispatch_qty DECIMAL(18,3),
  gross_amount DECIMAL(18,2),
  tds_amount DECIMAL(18,2),
  net_payable DECIMAL(18,2)
);

CREATE TABLE IF NOT EXISTS stock_verification_master (
  verification_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  verification_no VARCHAR(50),
  verification_date DATE
);

CREATE TABLE IF NOT EXISTS stock_verification_detail (
  detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  verification_id BIGINT,
  item_id BIGINT,
  system_qty DECIMAL(18,3),
  physical_qty DECIMAL(18,3),
  variance_qty DECIMAL(18,3)
);

CREATE TABLE IF NOT EXISTS stock_adjustment_master (
  adjustment_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  adjustment_date DATE
);

CREATE TABLE IF NOT EXISTS stock_adjustment_detail (
  detail_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  adjustment_id BIGINT,
  item_id BIGINT,
  adjustment_qty DECIMAL(18,3)
);

CREATE TABLE IF NOT EXISTS audit_log (
  audit_id BIGINT AUTO_INCREMENT PRIMARY KEY,
  tenant_id BIGINT,
  branch_id BIGINT,
  table_name VARCHAR(100),
  action_type VARCHAR(50),
  action_time DATETIME
);
