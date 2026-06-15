-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 12, 2026 at 11:31 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `timberinventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `audit_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `table_name` varchar(100) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `record_id` bigint(20) UNSIGNED DEFAULT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `action_time` datetime NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `audit_log`
--

INSERT INTO `audit_log` (`audit_id`, `tenant_id`, `branch_id`, `table_name`, `action_type`, `record_id`, `old_value`, `new_value`, `user_id`, `ip_address`, `action_time`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'database_seed', 'full_system_demo_seed', NULL, NULL, '{\"status\":\"Seeded full demo transactions\"}', 1, '127.0.0.1', '2026-06-08 06:35:00', NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(2, 1, 1, 'grn_master', 'create', 2, NULL, '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"supplier_id\":\"1\",\"grn_no\":\"11\",\"grn_date\":\"2026-06-09\",\"invoice_no\":\"121\",\"vehicle_no\":\"1222\",\"status\":\"Draft\",\"remarks\":null,\"total_qty\":105,\"total_amount\":8300,\"updated_by\":2,\"updated_at\":\"2026-06-09T10:37:46.605419Z\",\"created_by\":2,\"created_at\":\"2026-06-09T10:37:46.605461Z\"},\"details\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":2,\"uom_id\":5,\"location_id\":4,\"qty\":50,\"rate\":100,\"amount\":5000,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-09T10:37:46.605106Z\",\"updated_at\":\"2026-06-09T10:37:46.605141Z\"},{\"tenant_id\":1,\"branch_id\":1,\"item_id\":1,\"uom_id\":5,\"location_id\":2,\"qty\":55,\"rate\":60,\"amount\":3300,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-09T10:37:46.605230Z\",\"updated_at\":\"2026-06-09T10:37:46.605264Z\"}]}', 2, '127.0.0.1', '2026-06-09 10:37:46', 2, 2, '2026-06-09 05:07:46', '2026-06-09 05:07:46', NULL),
(3, 1, 1, 'grn_master', 'create', 3, NULL, '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"supplier_id\":\"1\",\"grn_no\":\"GRN-2026-000003\",\"grn_date\":\"2026-06-09\",\"invoice_no\":null,\"vehicle_no\":null,\"purchase_order_ref\":null,\"warehouse_location_id\":5,\"received_by\":null,\"status\":\"Draft\",\"remarks\":\"4465\",\"total_qty\":100,\"total_amount\":5000,\"discount_amount\":0,\"tax_amount\":0,\"freight_charges\":0,\"other_charges\":0,\"grand_total\":5000,\"attachments\":null,\"updated_by\":2,\"updated_at\":\"2026-06-09T12:06:24.301468Z\",\"created_by\":2,\"created_at\":\"2026-06-09T12:06:24.301506Z\"},\"details\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":7,\"uom_id\":1,\"location_id\":5,\"ordered_qty\":0,\"received_qty\":100,\"rejected_qty\":0,\"accepted_qty\":100,\"qty\":100,\"rate\":50,\"discount_amount\":0,\"tax_amount\":0,\"amount\":5000,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-09T12:06:24.299471Z\",\"updated_at\":\"2026-06-09T12:06:24.299508Z\"}]}', 2, '127.0.0.1', '2026-06-09 12:06:24', 2, 2, '2026-06-09 06:36:24', '2026-06-09 06:36:24', NULL),
(4, 1, 1, 'grn_master', 'post', 3, '{\"grn_id\":3,\"tenant_id\":1,\"branch_id\":1,\"supplier_id\":1,\"grn_no\":\"GRN-2026-000003\",\"grn_date\":\"2026-06-09\",\"invoice_no\":null,\"vehicle_no\":null,\"purchase_order_ref\":null,\"warehouse_location_id\":5,\"received_by\":null,\"status\":\"Draft\",\"remarks\":\"4465\",\"total_qty\":\"100.000\",\"total_amount\":\"5000.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"freight_charges\":\"0.00\",\"other_charges\":\"0.00\",\"grand_total\":\"5000.00\",\"attachments\":null,\"posted_at\":null,\"posted_by\":null,\"cancelled_at\":null,\"cancelled_by\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-09 12:06:24\",\"updated_at\":\"2026-06-09 12:06:24\",\"deleted_at\":null}', '{\"status\":\"Posted\"}', 2, '127.0.0.1', '2026-06-09 12:07:37', 2, 2, '2026-06-09 06:37:37', '2026-06-09 06:37:37', NULL),
(5, 1, 1, 'grn_master', 'create', 4, NULL, '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"supplier_id\":\"1\",\"grn_no\":\"GRN-2026-000004\",\"grn_date\":\"2026-06-10\",\"invoice_no\":null,\"vehicle_no\":null,\"purchase_order_ref\":null,\"warehouse_location_id\":5,\"received_by\":null,\"status\":\"Draft\",\"remarks\":\"as\",\"total_qty\":1,\"total_amount\":11,\"discount_amount\":0,\"tax_amount\":0,\"freight_charges\":0,\"other_charges\":0,\"grand_total\":11,\"attachments\":null,\"updated_by\":2,\"updated_at\":\"2026-06-10T08:06:36.275137Z\",\"created_by\":2,\"created_at\":\"2026-06-10T08:06:36.275191Z\"},\"details\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":5,\"uom_id\":3,\"location_id\":5,\"ordered_qty\":0,\"received_qty\":1,\"rejected_qty\":0,\"accepted_qty\":1,\"qty\":1,\"rate\":11,\"discount_amount\":0,\"tax_amount\":0,\"amount\":11,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-10T08:06:36.272972Z\",\"updated_at\":\"2026-06-10T08:06:36.273009Z\"}]}', 2, '127.0.0.1', '2026-06-10 08:06:36', 2, 2, '2026-06-10 02:36:36', '2026-06-10 02:36:36', NULL),
(6, 1, 1, 'grn_master', 'create', 5, NULL, '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"supplier_id\":\"1\",\"grn_no\":\"GRN-2026-000005\",\"grn_date\":\"2026-06-10\",\"invoice_no\":null,\"vehicle_no\":null,\"purchase_order_ref\":null,\"warehouse_location_id\":5,\"received_by\":null,\"status\":\"Draft\",\"remarks\":\"sd\",\"total_qty\":2,\"total_amount\":20,\"discount_amount\":0,\"tax_amount\":0,\"freight_charges\":0,\"other_charges\":0,\"grand_total\":20,\"attachments\":null,\"updated_by\":2,\"updated_at\":\"2026-06-10T08:08:11.454568Z\",\"created_by\":2,\"created_at\":\"2026-06-10T08:08:11.454615Z\"},\"details\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":7,\"uom_id\":1,\"location_id\":5,\"ordered_qty\":0,\"received_qty\":2,\"rejected_qty\":0,\"accepted_qty\":2,\"qty\":2,\"rate\":10,\"discount_amount\":0,\"tax_amount\":0,\"amount\":20,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-10T08:08:11.452370Z\",\"updated_at\":\"2026-06-10T08:08:11.452409Z\"}]}', 2, '127.0.0.1', '2026-06-10 08:08:11', 2, 2, '2026-06-10 02:38:11', '2026-06-10 02:38:11', NULL),
(7, 1, 1, 'grn_master', 'post', 4, '{\"grn_id\":4,\"tenant_id\":1,\"branch_id\":1,\"supplier_id\":1,\"grn_no\":\"GRN-2026-000004\",\"grn_date\":\"2026-06-10\",\"invoice_no\":null,\"vehicle_no\":null,\"purchase_order_ref\":null,\"warehouse_location_id\":5,\"received_by\":null,\"status\":\"Draft\",\"remarks\":\"as\",\"total_qty\":\"1.000\",\"total_amount\":\"11.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"freight_charges\":\"0.00\",\"other_charges\":\"0.00\",\"grand_total\":\"11.00\",\"attachments\":null,\"posted_at\":null,\"posted_by\":null,\"cancelled_at\":null,\"cancelled_by\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-10 08:06:36\",\"updated_at\":\"2026-06-10 08:06:36\",\"deleted_at\":null}', '{\"status\":\"Posted\"}', 2, '127.0.0.1', '2026-06-10 08:19:02', 2, 2, '2026-06-10 02:49:02', '2026-06-10 02:49:02', NULL),
(8, 1, 1, 'grn_master', 'create', 6, NULL, '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"supplier_id\":\"1\",\"grn_no\":\"GRN-2026-000006\",\"grn_date\":\"2026-06-10\",\"invoice_no\":null,\"vehicle_no\":null,\"purchase_order_ref\":null,\"warehouse_location_id\":5,\"received_by\":null,\"status\":\"Draft\",\"remarks\":\"22\",\"total_qty\":22,\"total_amount\":484,\"discount_amount\":0,\"tax_amount\":0,\"freight_charges\":0,\"other_charges\":0,\"grand_total\":484,\"attachments\":null,\"updated_by\":2,\"updated_at\":\"2026-06-10T09:17:36.522876Z\",\"created_by\":2,\"created_at\":\"2026-06-10T09:17:36.522914Z\"},\"details\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":6,\"uom_id\":1,\"location_id\":5,\"ordered_qty\":0,\"received_qty\":22,\"rejected_qty\":0,\"accepted_qty\":22,\"qty\":22,\"rate\":22,\"discount_amount\":0,\"tax_amount\":0,\"amount\":484,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-10T09:17:36.521170Z\",\"updated_at\":\"2026-06-10T09:17:36.521199Z\"}]}', 2, '127.0.0.1', '2026-06-10 09:17:36', 2, 2, '2026-06-10 03:47:36', '2026-06-10 03:47:36', NULL),
(9, 1, 1, 'grn_master', 'create', 7, NULL, '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"supplier_id\":\"1\",\"grn_no\":\"GRN-2026-000007\",\"grn_date\":\"2026-06-10\",\"invoice_no\":null,\"vehicle_no\":null,\"purchase_order_ref\":null,\"warehouse_location_id\":5,\"received_by\":null,\"status\":\"Draft\",\"remarks\":\"22\",\"total_qty\":22,\"total_amount\":484,\"discount_amount\":0,\"tax_amount\":0,\"freight_charges\":0,\"other_charges\":0,\"grand_total\":484,\"attachments\":null,\"updated_by\":2,\"updated_at\":\"2026-06-10T09:17:48.598900Z\",\"created_by\":2,\"created_at\":\"2026-06-10T09:17:48.598947Z\"},\"details\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":5,\"uom_id\":3,\"location_id\":5,\"ordered_qty\":0,\"received_qty\":22,\"rejected_qty\":0,\"accepted_qty\":22,\"qty\":22,\"rate\":22,\"discount_amount\":0,\"tax_amount\":0,\"amount\":484,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-10T09:17:48.596738Z\",\"updated_at\":\"2026-06-10T09:17:48.596775Z\"}]}', 2, '127.0.0.1', '2026-06-10 09:17:48', 2, 2, '2026-06-10 03:47:48', '2026-06-10 03:47:48', NULL),
(10, 1, 1, 'bom_master', 'create', 3, NULL, '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"bom_no\":\"BOM-2026-000001\",\"bom_name\":\"aa\",\"pallet_model_id\":2,\"version_no\":\"V1\",\"is_active\":true,\"status\":\"Active\",\"revision_note\":\"aa\",\"updated_by\":2,\"updated_at\":\"2026-06-11T05:32:40.839539Z\",\"created_by\":2,\"created_at\":\"2026-06-11T05:32:40.839577Z\"},\"materials\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":3,\"uom_id\":2,\"required_qty\":110,\"wastage_percent\":2,\"remarks\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-11T05:32:40.839330Z\",\"updated_at\":\"2026-06-11T05:32:40.839367Z\"},{\"tenant_id\":1,\"branch_id\":1,\"item_id\":1,\"uom_id\":2,\"required_qty\":100,\"wastage_percent\":2,\"remarks\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-11T05:32:40.839450Z\",\"updated_at\":\"2026-06-11T05:32:40.839469Z\"}]}', 2, '127.0.0.1', '2026-06-11 05:32:40', 2, 2, '2026-06-11 00:02:40', '2026-06-11 00:02:40', NULL),
(11, 1, 1, 'bom_master', 'update', 3, '{\"bom_id\":3,\"tenant_id\":1,\"branch_id\":1,\"bom_no\":\"BOM-2026-000001\",\"bom_name\":\"aa\",\"pallet_model_id\":2,\"version_no\":\"V1\",\"is_active\":1,\"status\":\"Active\",\"system_protected\":0,\"revision_note\":\"aa\",\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-11 05:32:40\",\"updated_at\":\"2026-06-11 05:32:40\",\"deleted_at\":null}', '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"bom_no\":\"BOM-2026-000001\",\"bom_name\":\"aa\",\"pallet_model_id\":2,\"version_no\":\"V1\",\"is_active\":true,\"status\":\"Active\",\"revision_note\":\"aa\",\"updated_by\":2,\"updated_at\":\"2026-06-11T05:33:03.922219Z\"},\"materials\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":3,\"uom_id\":2,\"required_qty\":110,\"wastage_percent\":2,\"remarks\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-11T05:33:03.922106Z\",\"updated_at\":\"2026-06-11T05:33:03.922141Z\"}]}', 2, '127.0.0.1', '2026-06-11 05:33:03', 2, 2, '2026-06-11 00:03:03', '2026-06-11 00:03:03', NULL),
(12, 1, 1, 'production_master', 'create', 2, NULL, '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"production_no\":\"PROD-2026-000002\",\"production_date\":\"2026-06-11\",\"bom_id\":3,\"pallet_model_id\":2,\"produced_item_id\":7,\"team_id\":3,\"fg_location_id\":5,\"produced_qty\":10,\"production_cost\":250,\"status\":\"Draft\",\"remarks\":null,\"updated_by\":2,\"updated_at\":\"2026-06-11T05:54:25.671005Z\",\"created_by\":2,\"created_at\":\"2026-06-11T05:54:25.671055Z\"},\"consumptions\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":3,\"uom_id\":\"2\",\"location_id\":4,\"required_qty\":1122,\"consumed_qty\":1122,\"remarks\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-11T05:54:25.671136Z\",\"updated_at\":\"2026-06-11T05:54:25.671151Z\"}],\"wastages\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":2,\"location_id\":\"5\",\"qty\":1,\"wastage_type\":\"Scrap\",\"remarks\":\"aa\",\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-11T05:54:25.671220Z\",\"updated_at\":\"2026-06-11T05:54:25.671234Z\"}]}', 2, '127.0.0.1', '2026-06-11 05:54:25', 2, 2, '2026-06-11 00:24:25', '2026-06-11 00:24:25', NULL),
(13, 1, 1, 'wastage_stock', 'create', 2, NULL, '{\"transaction_date\":\"2026-06-11\",\"item_id\":\"8\",\"location_id\":\"4\",\"wastage_type\":\"Reusable\",\"generated_qty\":\"1\",\"source_reference\":\"a\",\"remarks\":null}', 2, '127.0.0.1', '2026-06-11 07:40:55', 2, 2, '2026-06-11 02:10:55', '2026-06-11 02:10:55', NULL),
(14, 1, 1, 'bom_master', 'create', 4, NULL, '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"bom_no\":\"BOM-2026-000002\",\"bom_name\":\"aaa\",\"pallet_model_id\":1,\"version_no\":\"V1\",\"is_active\":true,\"status\":\"Active\",\"revision_note\":\"aa\",\"updated_by\":2,\"updated_at\":\"2026-06-11T08:17:44.628067Z\",\"created_by\":2,\"created_at\":\"2026-06-11T08:17:44.628105Z\"},\"materials\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":3,\"uom_id\":1,\"required_qty\":100,\"wastage_percent\":2,\"remarks\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-11T08:17:44.627864Z\",\"updated_at\":\"2026-06-11T08:17:44.627900Z\"},{\"tenant_id\":1,\"branch_id\":1,\"item_id\":1,\"uom_id\":5,\"required_qty\":100,\"wastage_percent\":2,\"remarks\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-11T08:17:44.627979Z\",\"updated_at\":\"2026-06-11T08:17:44.627997Z\"}]}', 2, '127.0.0.1', '2026-06-11 08:17:44', 2, 2, '2026-06-11 02:47:44', '2026-06-11 02:47:44', NULL),
(15, 1, 1, 'grn_master', 'create', 8, NULL, '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"supplier_id\":\"3\",\"grn_no\":\"GRN-2026-000008\",\"grn_date\":\"2026-06-11\",\"invoice_no\":null,\"vehicle_no\":null,\"purchase_order_ref\":null,\"warehouse_location_id\":5,\"received_by\":null,\"status\":\"Draft\",\"remarks\":\"asaa\",\"total_qty\":100,\"total_amount\":5000,\"discount_amount\":0,\"tax_amount\":0,\"freight_charges\":0,\"other_charges\":0,\"grand_total\":5000,\"attachments\":null,\"updated_by\":2,\"updated_at\":\"2026-06-11T13:02:48.320717Z\",\"created_by\":2,\"created_at\":\"2026-06-11T13:02:48.320765Z\"},\"details\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":7,\"uom_id\":1,\"location_id\":5,\"ordered_qty\":0,\"received_qty\":100,\"rejected_qty\":0,\"accepted_qty\":100,\"qty\":100,\"rate\":50,\"discount_amount\":0,\"tax_amount\":0,\"amount\":5000,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-11T13:02:48.318489Z\",\"updated_at\":\"2026-06-11T13:02:48.318523Z\"}]}', 2, '127.0.0.1', '2026-06-11 13:02:48', 2, 2, '2026-06-11 07:32:48', '2026-06-11 07:32:48', NULL),
(16, 1, 1, 'wastage_stock', 'create', 3, NULL, '{\"transaction_date\":\"2026-06-11\",\"item_id\":\"9\",\"location_id\":\"5\",\"wastage_type\":\"Reusable\",\"generated_qty\":\"1\",\"source_reference\":\"fg\",\"remarks\":\"fg\"}', 2, '127.0.0.1', '2026-06-11 13:18:23', 2, 2, '2026-06-11 07:48:23', '2026-06-11 07:48:23', NULL),
(17, 1, 1, 'grn_master', 'post', 8, '{\"grn_id\":8,\"tenant_id\":1,\"branch_id\":1,\"supplier_id\":3,\"grn_no\":\"GRN-2026-000008\",\"grn_date\":\"2026-06-11\",\"invoice_no\":null,\"vehicle_no\":null,\"purchase_order_ref\":null,\"warehouse_location_id\":5,\"received_by\":null,\"status\":\"Draft\",\"remarks\":\"asaa\",\"total_qty\":\"100.000\",\"total_amount\":\"5000.00\",\"discount_amount\":\"0.00\",\"tax_amount\":\"0.00\",\"freight_charges\":\"0.00\",\"other_charges\":\"0.00\",\"grand_total\":\"5000.00\",\"attachments\":null,\"posted_at\":null,\"posted_by\":null,\"cancelled_at\":null,\"cancelled_by\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-11 13:02:48\",\"updated_at\":\"2026-06-11 13:02:48\",\"deleted_at\":null}', '{\"status\":\"Posted\"}', 2, '127.0.0.1', '2026-06-11 13:43:16', 2, 2, '2026-06-11 08:13:16', '2026-06-11 08:13:16', NULL),
(18, 1, 1, 'production_master', 'create', 3, NULL, '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"production_no\":\"PROD-2026-000003\",\"production_date\":\"2026-06-12\",\"bom_id\":4,\"pallet_model_id\":1,\"produced_item_id\":7,\"team_id\":1,\"fg_location_id\":1,\"produced_qty\":10,\"production_cost\":0,\"status\":\"Draft\",\"remarks\":null,\"updated_by\":2,\"updated_at\":\"2026-06-12T08:47:32.103851Z\",\"created_by\":2,\"created_at\":\"2026-06-12T08:47:32.103912Z\"},\"consumptions\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":3,\"uom_id\":\"1\",\"location_id\":6,\"required_qty\":1000,\"consumed_qty\":1000,\"wastage_qty\":0,\"remarks\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-12T08:47:32.104022Z\",\"updated_at\":\"2026-06-12T08:47:32.104040Z\"},{\"tenant_id\":1,\"branch_id\":1,\"item_id\":1,\"uom_id\":\"5\",\"location_id\":6,\"required_qty\":1000,\"consumed_qty\":1000,\"wastage_qty\":0,\"remarks\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-12T08:47:32.104109Z\",\"updated_at\":\"2026-06-12T08:47:32.104125Z\"}],\"wastages\":[]}', 2, '127.0.0.1', '2026-06-12 08:47:32', 2, 2, '2026-06-12 03:17:32', '2026-06-12 03:17:32', NULL),
(19, 1, 1, 'production_master', 'create', 4, NULL, '{\"master\":{\"tenant_id\":1,\"branch_id\":1,\"production_no\":\"PROD-2026-000004\",\"production_date\":\"2026-06-12\",\"bom_id\":4,\"pallet_model_id\":1,\"produced_item_id\":13,\"team_id\":3,\"fg_location_id\":6,\"produced_qty\":1,\"production_cost\":0,\"status\":\"Draft\",\"remarks\":null,\"updated_by\":2,\"updated_at\":\"2026-06-12T08:59:33.183400Z\",\"created_by\":2,\"created_at\":\"2026-06-12T08:59:33.183462Z\"},\"consumptions\":[{\"tenant_id\":1,\"branch_id\":1,\"item_id\":3,\"uom_id\":\"1\",\"location_id\":1,\"required_qty\":100,\"consumed_qty\":100,\"wastage_qty\":0,\"remarks\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-12T08:59:33.183562Z\",\"updated_at\":\"2026-06-12T08:59:33.183579Z\"},{\"tenant_id\":1,\"branch_id\":1,\"item_id\":1,\"uom_id\":\"5\",\"location_id\":1,\"required_qty\":100,\"consumed_qty\":100,\"wastage_qty\":0,\"remarks\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-12T08:59:33.183665Z\",\"updated_at\":\"2026-06-12T08:59:33.183682Z\"}],\"wastages\":[]}', 2, '127.0.0.1', '2026-06-12 08:59:33', 2, 2, '2026-06-12 03:29:33', '2026-06-12 03:29:33', NULL),
(20, 1, 1, 'production_master', 'post', 4, '{\"production_id\":4,\"tenant_id\":1,\"branch_id\":1,\"production_no\":\"PROD-2026-000004\",\"production_date\":\"2026-06-12\",\"bom_id\":4,\"pallet_model_id\":1,\"produced_item_id\":13,\"team_id\":3,\"fg_location_id\":6,\"produced_qty\":\"1.000\",\"production_cost\":\"0.00\",\"status\":\"Draft\",\"remarks\":null,\"posted_at\":null,\"posted_by\":null,\"cancelled_at\":null,\"cancelled_by\":null,\"cancellation_reason\":null,\"created_by\":2,\"updated_by\":2,\"created_at\":\"2026-06-12 08:59:33\",\"updated_at\":\"2026-06-12 08:59:33\",\"deleted_at\":null}', '{\"status\":\"Posted\"}', 2, '127.0.0.1', '2026-06-12 08:59:39', 2, 2, '2026-06-12 03:29:39', '2026-06-12 03:29:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bom_master`
--

CREATE TABLE `bom_master` (
  `bom_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bom_no` varchar(50) DEFAULT NULL,
  `bom_name` varchar(255) DEFAULT NULL,
  `pallet_model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `finished_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `version_no` varchar(20) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `system_protected` tinyint(1) NOT NULL DEFAULT 0,
  `revision_note` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bom_master`
--

INSERT INTO `bom_master` (`bom_id`, `tenant_id`, `branch_id`, `bom_no`, `bom_name`, `pallet_model_id`, `finished_item_id`, `version_no`, `is_active`, `status`, `system_protected`, `revision_note`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, NULL, NULL, 1, NULL, 'V1', 1, 'Active', 0, 'Initial demo BOM', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(2, 1, NULL, NULL, NULL, 2, NULL, 'V1', 1, 'Active', 0, 'Initial demo BOM', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(3, 1, 1, 'BOM-2026-000001', 'aa', 2, NULL, 'V1', 1, 'Active', 0, 'aa', 2, 2, '2026-06-11 00:02:40', '2026-06-11 00:03:03', NULL),
(4, 1, 1, 'BOM-2026-000002', 'aaa', 1, NULL, 'V1', 1, 'Active', 0, 'aa', 2, 2, '2026-06-11 02:47:44', '2026-06-11 02:47:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `bom_material`
--

CREATE TABLE `bom_material` (
  `bom_material_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `bom_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `uom_id` bigint(20) UNSIGNED DEFAULT NULL,
  `required_qty` decimal(18,3) NOT NULL,
  `wastage_percent` decimal(8,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bom_material`
--

INSERT INTO `bom_material` (`bom_material_id`, `tenant_id`, `branch_id`, `bom_id`, `item_id`, `uom_id`, `required_qty`, `wastage_percent`, `remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 1, 1, NULL, 0.850, 4.00, NULL, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(2, 1, NULL, 1, 4, NULL, 0.120, 1.00, NULL, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(3, 1, NULL, 1, 3, NULL, 0.200, 2.00, NULL, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(4, 1, NULL, 2, 2, NULL, 1.100, 5.00, NULL, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(5, 1, NULL, 2, 5, NULL, 0.180, 1.00, NULL, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(6, 1, NULL, 2, 3, NULL, 0.300, 2.00, NULL, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(9, 1, 1, 3, 3, 2, 110.000, 2.00, NULL, 2, 2, '2026-06-11 00:03:03', '2026-06-11 00:03:03', NULL),
(10, 1, 1, 4, 3, 1, 100.000, 2.00, NULL, 2, 2, '2026-06-11 02:47:44', '2026-06-11 02:47:44', NULL),
(11, 1, 1, 4, 1, 5, 100.000, 2.00, NULL, 2, 2, '2026-06-11 02:47:44', '2026-06-11 02:47:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `branch_master`
--

CREATE TABLE `branch_master` (
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_code` varchar(20) DEFAULT NULL,
  `branch_name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `branch_master`
--

INSERT INTO `branch_master` (`branch_id`, `tenant_id`, `branch_code`, `branch_name`, `address`, `city`, `state`, `country`, `mobile`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 'MAIN', 'Main Branch', 'Main Factory Yard', 'Pune', 'Maharashtra', 'India', '9000000001', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(2, 1, 'WH', 'Factory Branch', 'Warehouse Yard', 'Pune', 'Maharashtra', 'India', '9000000002', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `cache`
--

CREATE TABLE `cache` (
  `key` varchar(255) NOT NULL,
  `value` mediumtext NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `cache_locks`
--

CREATE TABLE `cache_locks` (
  `key` varchar(255) NOT NULL,
  `owner` varchar(255) NOT NULL,
  `expiration` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `challan_master`
--

CREATE TABLE `challan_master` (
  `challan_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `challan_no` varchar(50) NOT NULL,
  `challan_date` date NOT NULL,
  `customer_id` bigint(20) UNSIGNED DEFAULT NULL,
  `vehicle_no` varchar(50) DEFAULT NULL,
  `driver_name` varchar(255) DEFAULT NULL,
  `destination` varchar(255) DEFAULT NULL,
  `total_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `challan_master`
--

INSERT INTO `challan_master` (`challan_id`, `tenant_id`, `branch_id`, `challan_no`, `challan_date`, `customer_id`, `vehicle_no`, `driver_name`, `destination`, `total_qty`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'CH-TEST-001', '2026-06-08', 2, 'MH14CD2222', 'Vijay Kale', 'Alpha Packaging Pune', 12.000, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `challan_team_detail`
--

CREATE TABLE `challan_team_detail` (
  `detail_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `challan_id` bigint(20) UNSIGNED NOT NULL,
  `pallet_model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `qty` decimal(18,3) NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `challan_team_detail`
--

INSERT INTO `challan_team_detail` (`detail_id`, `tenant_id`, `branch_id`, `challan_id`, `pallet_model_id`, `team_id`, `qty`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 1, 1, 12.000, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `erp_modules`
--

CREATE TABLE `erp_modules` (
  `module_id` bigint(20) UNSIGNED NOT NULL,
  `module_code` varchar(50) NOT NULL,
  `module_name` varchar(120) NOT NULL,
  `parent_module_id` bigint(20) UNSIGNED DEFAULT NULL,
  `icon` varchar(80) DEFAULT NULL,
  `display_order` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `route` varchar(150) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `description` varchar(255) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `erp_modules`
--

INSERT INTO `erp_modules` (`module_id`, `module_code`, `module_name`, `parent_module_id`, `icon`, `display_order`, `route`, `status`, `description`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'INVENTORY', 'Inventory', NULL, 'boxes', 10, NULL, 'Active', NULL, NULL, NULL, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(2, 'MASTERS', 'Masters', NULL, 'database', 20, NULL, 'Active', NULL, NULL, NULL, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(3, 'PRODUCTION', 'Production', NULL, 'factory', 30, NULL, 'Active', NULL, NULL, NULL, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(4, 'DISPATCH', 'Dispatch', NULL, 'truck', 40, NULL, 'Active', NULL, NULL, NULL, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(5, 'REPORTS', 'Reports', NULL, 'bar-chart-3', 50, NULL, 'Active', NULL, NULL, NULL, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(6, 'ADMINISTRATION', 'Administration', NULL, 'settings', 60, NULL, 'Active', NULL, NULL, NULL, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(7, 'FINANCE', 'Finance', NULL, 'wallet', 70, NULL, 'Active', NULL, NULL, NULL, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(8, 'SETTINGS', 'Settings', NULL, 'settings-2', 80, '/settings', 'Active', NULL, NULL, NULL, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `grn_detail`
--

CREATE TABLE `grn_detail` (
  `grn_detail_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `grn_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `uom_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `ordered_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `received_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `rejected_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `accepted_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `qty` decimal(18,3) NOT NULL,
  `rate` decimal(18,2) NOT NULL,
  `discount_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grn_detail`
--

INSERT INTO `grn_detail` (`grn_detail_id`, `tenant_id`, `branch_id`, `grn_id`, `item_id`, `uom_id`, `location_id`, `ordered_qty`, `received_qty`, `rejected_qty`, `accepted_qty`, `qty`, `rate`, `discount_amount`, `tax_amount`, `amount`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 1, NULL, 1, 0.000, 0.000, 0.000, 0.000, 75.000, 825.00, 0.00, 0.00, 61875.00, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(2, 1, 1, 1, 4, NULL, 1, 0.000, 0.000, 0.000, 0.000, 20.000, 98.00, 0.00, 0.00, 1960.00, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(3, 1, 1, 2, 2, 5, 4, 0.000, 0.000, 0.000, 0.000, 50.000, 100.00, 0.00, 0.00, 5000.00, 2, 2, '2026-06-09 05:07:46', '2026-06-09 05:07:46', NULL),
(4, 1, 1, 2, 1, 5, 2, 0.000, 0.000, 0.000, 0.000, 55.000, 60.00, 0.00, 0.00, 3300.00, 2, 2, '2026-06-09 05:07:46', '2026-06-09 05:07:46', NULL),
(5, 1, 1, 3, 7, 1, 5, 0.000, 100.000, 0.000, 100.000, 100.000, 50.00, 0.00, 0.00, 5000.00, 2, 2, '2026-06-09 06:36:24', '2026-06-09 06:36:24', NULL),
(6, 1, 1, 4, 5, 3, 5, 0.000, 1.000, 0.000, 1.000, 1.000, 11.00, 0.00, 0.00, 11.00, 2, 2, '2026-06-10 02:36:36', '2026-06-10 02:36:36', NULL),
(7, 1, 1, 5, 7, 1, 5, 0.000, 2.000, 0.000, 2.000, 2.000, 10.00, 0.00, 0.00, 20.00, 2, 2, '2026-06-10 02:38:11', '2026-06-10 02:38:11', NULL),
(8, 1, 1, 6, 6, 1, 5, 0.000, 22.000, 0.000, 22.000, 22.000, 22.00, 0.00, 0.00, 484.00, 2, 2, '2026-06-10 03:47:36', '2026-06-10 03:47:36', NULL),
(9, 1, 1, 7, 5, 3, 5, 0.000, 22.000, 0.000, 22.000, 22.000, 22.00, 0.00, 0.00, 484.00, 2, 2, '2026-06-10 03:47:48', '2026-06-10 03:47:48', NULL),
(10, 1, 1, 8, 7, 1, 5, 0.000, 100.000, 0.000, 100.000, 100.000, 50.00, 0.00, 0.00, 5000.00, 2, 2, '2026-06-11 07:32:48', '2026-06-11 07:32:48', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `grn_master`
--

CREATE TABLE `grn_master` (
  `grn_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `supplier_id` bigint(20) UNSIGNED DEFAULT NULL,
  `grn_no` varchar(50) NOT NULL,
  `grn_date` date NOT NULL,
  `invoice_no` varchar(50) DEFAULT NULL,
  `vehicle_no` varchar(50) DEFAULT NULL,
  `purchase_order_ref` varchar(80) DEFAULT NULL,
  `warehouse_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `received_by` varchar(120) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Draft',
  `remarks` text DEFAULT NULL,
  `total_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `total_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `tax_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `freight_charges` decimal(18,2) NOT NULL DEFAULT 0.00,
  `other_charges` decimal(18,2) NOT NULL DEFAULT 0.00,
  `grand_total` decimal(18,2) NOT NULL DEFAULT 0.00,
  `attachments` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`attachments`)),
  `posted_at` timestamp NULL DEFAULT NULL,
  `posted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `grn_master`
--

INSERT INTO `grn_master` (`grn_id`, `tenant_id`, `branch_id`, `supplier_id`, `grn_no`, `grn_date`, `invoice_no`, `vehicle_no`, `purchase_order_ref`, `warehouse_location_id`, `received_by`, `status`, `remarks`, `total_qty`, `total_amount`, `discount_amount`, `tax_amount`, `freight_charges`, `other_charges`, `grand_total`, `attachments`, `posted_at`, `posted_by`, `cancelled_at`, `cancelled_by`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 'GRN-TEST-001', '2026-06-05', 'PIN-INV-1001', 'MH12AB1234', NULL, NULL, NULL, 'Draft', NULL, 0.000, 0.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(2, 1, 1, 1, '11', '2026-06-09', '121', '1222', NULL, NULL, NULL, 'Draft', NULL, 105.000, 8300.00, 0.00, 0.00, 0.00, 0.00, 0.00, NULL, NULL, NULL, NULL, NULL, 2, 2, '2026-06-09 05:07:46', '2026-06-09 05:07:46', NULL),
(3, 1, 1, 1, 'GRN-2026-000003', '2026-06-09', NULL, NULL, NULL, 5, NULL, 'Posted', '4465', 100.000, 5000.00, 0.00, 0.00, 0.00, 0.00, 5000.00, NULL, '2026-06-09 06:37:37', 2, NULL, NULL, 2, 2, '2026-06-09 06:36:24', '2026-06-09 06:37:37', NULL),
(4, 1, 1, 1, 'GRN-2026-000004', '2026-06-10', NULL, NULL, NULL, 5, NULL, 'Posted', 'as', 1.000, 11.00, 0.00, 0.00, 0.00, 0.00, 11.00, NULL, '2026-06-10 02:49:02', 2, NULL, NULL, 2, 2, '2026-06-10 02:36:36', '2026-06-10 02:49:02', NULL),
(5, 1, 1, 1, 'GRN-2026-000005', '2026-06-10', NULL, NULL, NULL, 5, NULL, 'Draft', 'sd', 2.000, 20.00, 0.00, 0.00, 0.00, 0.00, 20.00, NULL, NULL, NULL, NULL, NULL, 2, 2, '2026-06-10 02:38:11', '2026-06-10 02:38:11', NULL),
(6, 1, 1, 1, 'GRN-2026-000006', '2026-06-10', NULL, NULL, NULL, 5, NULL, 'Draft', '22', 22.000, 484.00, 0.00, 0.00, 0.00, 0.00, 484.00, NULL, NULL, NULL, NULL, NULL, 2, 2, '2026-06-10 03:47:36', '2026-06-10 03:47:36', NULL),
(7, 1, 1, 1, 'GRN-2026-000007', '2026-06-10', NULL, NULL, NULL, 5, NULL, 'Draft', '22', 22.000, 484.00, 0.00, 0.00, 0.00, 0.00, 484.00, NULL, NULL, NULL, NULL, NULL, 2, 2, '2026-06-10 03:47:48', '2026-06-10 03:47:48', NULL),
(8, 1, 1, 3, 'GRN-2026-000008', '2026-06-11', NULL, NULL, NULL, 5, NULL, 'Posted', 'asaa', 100.000, 5000.00, 0.00, 0.00, 0.00, 0.00, 5000.00, NULL, '2026-06-11 08:13:16', 2, NULL, NULL, 2, 2, '2026-06-11 07:32:48', '2026-06-11 08:13:16', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `item_master`
--

CREATE TABLE `item_master` (
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `item_code` varchar(50) DEFAULT NULL,
  `item_name` varchar(255) NOT NULL,
  `item_type` enum('Raw Material','Semi Product','Finish Product','Wastage','Scrap','Consumable') NOT NULL,
  `material_type_id` bigint(20) UNSIGNED DEFAULT NULL,
  `uom_id` bigint(20) UNSIGNED DEFAULT NULL,
  `length_mm` decimal(18,3) DEFAULT NULL,
  `width_mm` decimal(18,3) DEFAULT NULL,
  `thickness_mm` decimal(18,3) DEFAULT NULL,
  `cft_factor` decimal(18,6) DEFAULT NULL,
  `minimum_stock` decimal(18,3) NOT NULL DEFAULT 0.000,
  `opening_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `opening_rate` decimal(18,2) NOT NULL DEFAULT 0.00,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `item_master`
--

INSERT INTO `item_master` (`item_id`, `tenant_id`, `branch_id`, `item_code`, `item_name`, `item_type`, `material_type_id`, `uom_id`, `length_mm`, `width_mm`, `thickness_mm`, `cft_factor`, `minimum_stock`, `opening_qty`, `opening_rate`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'RM-PINE-1356-22', 'Pine Wood 1356x22', 'Raw Material', 1, 5, NULL, NULL, NULL, NULL, 10.000, 500.000, 820.00, 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(2, 1, NULL, 'RM-PINE-906-22', 'Pine Wood 906x22', 'Raw Material', 1, 5, NULL, NULL, NULL, NULL, 10.000, 420.000, 790.00, 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(3, 1, NULL, 'RM-PLY-SHEET', 'Plywood Sheet', 'Raw Material', 2, 1, NULL, NULL, NULL, NULL, 10.000, 250.000, 550.00, 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(4, 1, NULL, 'RM-NAIL-75', 'Nail 75mm', 'Consumable', 3, 3, NULL, NULL, NULL, NULL, 10.000, 180.000, 95.00, 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(5, 1, NULL, 'RM-SCREW-50', 'Screw 50mm', 'Consumable', 4, 3, NULL, NULL, NULL, NULL, 10.000, 120.000, 140.00, 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(6, 1, NULL, 'FG-P001', 'Pallet P001', 'Finish Product', 1, 1, NULL, NULL, NULL, NULL, 10.000, 40.000, 1200.00, 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(7, 1, NULL, 'FG-P002', 'Pallet P002', 'Finish Product', 1, 1, NULL, NULL, NULL, NULL, 10.000, 25.000, 1550.00, 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(8, 1, NULL, 'WST-REUSABLE-WOOD', 'Reusable Wood Wastage', 'Wastage', 7, 3, NULL, NULL, NULL, NULL, 0.000, 0.000, 0.00, 'Active', NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(9, 1, NULL, '11121', '11121 qwqswq', 'Finish Product', NULL, 6, 1.000, 1.000, 1.000, 1.000000, 11.000, 0.000, 0.00, 'Active', 2, 2, '2026-06-11 07:12:36', '2026-06-11 07:12:59', NULL),
(10, 1, NULL, 'RM-PLY-001', 'Plywood 18mm', 'Raw Material', 1, 5, 2440.000, 1220.000, 18.000, 1.000000, 10.000, 0.000, 0.00, 'Active', 2, 2, '2026-06-11 23:14:14', '2026-06-11 23:15:09', NULL),
(11, 1, NULL, 'FG-PALLET-001', 'Standard Pallet', 'Finish Product', 1, 1, 1200.000, 1000.000, 150.000, 1.000000, 0.000, 0.000, 0.00, 'Active', 2, 2, '2026-06-11 23:14:14', '2026-06-11 23:15:09', NULL),
(12, 1, NULL, 'FG-PALLET-002', 'Standard Pallet2', 'Finish Product', 1, 1, 1200.000, 1000.000, 150.000, 1.000000, 0.000, 0.000, 0.00, 'Active', 2, 2, '2026-06-11 23:15:09', '2026-06-11 23:15:09', NULL),
(13, 1, NULL, 'FG-PALLET-003', 'Standard Pallet3', 'Finish Product', 1, 1, 1200.000, 1000.000, 150.000, 1.000000, 0.000, 0.000, 0.00, 'Active', 2, 2, '2026-06-11 23:15:09', '2026-06-11 23:15:09', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `jobs`
--

CREATE TABLE `jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `queue` varchar(255) NOT NULL,
  `payload` longtext NOT NULL,
  `attempts` tinyint(3) UNSIGNED NOT NULL,
  `reserved_at` int(10) UNSIGNED DEFAULT NULL,
  `available_at` int(10) UNSIGNED NOT NULL,
  `created_at` int(10) UNSIGNED NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_batches`
--

CREATE TABLE `job_batches` (
  `id` varchar(255) NOT NULL,
  `name` varchar(255) NOT NULL,
  `total_jobs` int(11) NOT NULL,
  `pending_jobs` int(11) NOT NULL,
  `failed_jobs` int(11) NOT NULL,
  `failed_job_ids` longtext NOT NULL,
  `options` mediumtext DEFAULT NULL,
  `cancelled_at` int(11) DEFAULT NULL,
  `created_at` int(11) NOT NULL,
  `finished_at` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `material_type_master`
--

CREATE TABLE `material_type_master` (
  `material_type_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `material_type_code` varchar(20) DEFAULT NULL,
  `material_type_name` varchar(100) NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `material_type_master`
--

INSERT INTO `material_type_master` (`material_type_id`, `tenant_id`, `branch_id`, `material_type_code`, `material_type_name`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'WOOD', 'WOOD', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(2, 1, NULL, 'PLYWOOD', 'PLYWOOD', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(3, 1, NULL, 'NAIL', 'NAIL', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(4, 1, NULL, 'SCREW', 'SCREW', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(5, 1, NULL, 'PACKING', 'PACKING', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(6, 1, NULL, 'CONSUMABLE', 'CONSUMABLE', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(7, 1, NULL, 'SCRAP', 'SCRAP', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(8, 1, NULL, 'cvc', 'cvcv', 2, 2, '2026-06-11 07:06:41', '2026-06-11 07:06:41', NULL),
(9, 1, NULL, 'sd', 'sd1', 2, 2, '2026-06-11 07:13:21', '2026-06-11 07:13:42', '2026-06-11 07:13:42');

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '0001_01_01_000000_create_users_table', 1),
(2, '0001_01_01_000001_create_cache_table', 1),
(3, '0001_01_01_000002_create_jobs_table', 1),
(4, '2026_06_06_083056_create_personal_access_tokens_table', 1),
(5, '2026_06_06_090000_create_suresh_timber_erp_tables', 1),
(6, '2026_06_06_170000_create_user_preferences_table', 1),
(7, '2026_06_09_000001_add_grn_workflow_fields', 2),
(8, '2026_06_09_000003_add_erp_grn_transaction_fields', 3),
(9, '2026_06_09_000004_add_inventory_control_workflow_fields', 4),
(10, '2026_06_09_000005_sync_inventory_navigation_permissions', 5),
(11, '2026_06_09_000006_add_role_management_metadata', 6),
(12, '2026_06_09_000007_add_party_state_permission_metadata', 7),
(13, '2026_06_09_000008_create_erp_modules_table', 8),
(14, '2026_06_09_000009_sync_module_management_permissions', 8),
(15, '2026_06_10_000001_add_production_core_fields', 9),
(16, '2026_06_10_000002_sync_production_core_permissions', 9),
(17, '2026_06_11_000001_add_location_status', 10),
(18, '2026_06_11_000002_add_wastage_phase_two_tables', 11),
(19, '2026_06_11_000003_backfill_wastage_stock_locations', 12),
(20, '2026_06_12_000001_add_stock_type_and_consumption_wastage', 13),
(21, '2026_06_12_000002_use_finished_item_for_bom', 14);

-- --------------------------------------------------------

--
-- Table structure for table `pallet_model_master`
--

CREATE TABLE `pallet_model_master` (
  `pallet_model_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `model_code` varchar(50) DEFAULT NULL,
  `model_name` varchar(255) NOT NULL,
  `length` decimal(18,3) DEFAULT NULL,
  `width` decimal(18,3) DEFAULT NULL,
  `height` decimal(18,3) DEFAULT NULL,
  `wood_type` varchar(100) DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pallet_model_master`
--

INSERT INTO `pallet_model_master` (`pallet_model_id`, `tenant_id`, `branch_id`, `model_code`, `model_name`, `length`, `width`, `height`, `wood_type`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'P001', 'Pallet P001', 1200.000, 1000.000, 140.000, 'Pine', NULL, 2, '2026-06-08 01:04:44', '2026-06-12 03:43:21', '2026-06-12 03:43:21'),
(2, 1, NULL, 'P002', 'Pallet P002', 1300.000, 1100.000, 150.000, 'Pine', NULL, 2, '2026-06-08 01:04:44', '2026-06-12 03:43:14', '2026-06-12 03:43:14'),
(3, 1, NULL, 'as', 'as', 1.000, 1.000, 1.000, 'sdas', 2, 2, '2026-06-11 07:17:51', '2026-06-12 03:43:10', '2026-06-12 03:43:10');

-- --------------------------------------------------------

--
-- Table structure for table `party_master`
--

CREATE TABLE `party_master` (
  `party_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `party_code` varchar(30) DEFAULT NULL,
  `party_name` varchar(255) NOT NULL,
  `party_type` enum('Customer','Supplier','Both') NOT NULL,
  `gst_no` varchar(30) DEFAULT NULL,
  `pan_no` varchar(20) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `state` varchar(100) DEFAULT NULL,
  `country` varchar(100) DEFAULT NULL,
  `credit_days` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `credit_limit` decimal(18,2) NOT NULL DEFAULT 0.00,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `party_master`
--

INSERT INTO `party_master` (`party_id`, `tenant_id`, `branch_id`, `party_code`, `party_name`, `party_type`, `gst_no`, `pan_no`, `remarks`, `contact_person`, `mobile`, `email`, `address`, `city`, `state`, `country`, `credit_days`, `credit_limit`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'SUP-PINE', 'Pine Wood Traders', 'Supplier', '27AAAAA0000A1Z5', NULL, NULL, 'Ramesh Patil', '9000000101', 'supplier@sureshtimber.test', 'Timber Market Yard', 'Pune', 'Maharashtra', 'India', 15, 250000.00, 'Active', NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(2, 1, 1, 'CUS-ALPHA', 'Alpha Packaging Pvt Ltd', 'Customer', '27BBBBB1111B1Z2', NULL, NULL, 'Amit Shah', '9000000201', 'alpha@sureshtimber.test', 'MIDC Industrial Area', 'Pune', 'Maharashtra', 'India', 30, 500000.00, 'Active', NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(3, 1, 1, 'S00001', 'aaaaa', 'Supplier', 'as', 'as', 'as', 'as', 'as', 'as', 'as', NULL, 'Andhra Pradesh', NULL, 0, 0.00, 'Active', 2, 2, '2026-06-11 07:16:00', '2026-06-11 07:16:19', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `password_reset_tokens`
--

CREATE TABLE `password_reset_tokens` (
  `login_id` varchar(255) NOT NULL,
  `token` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `permissions`
--

CREATE TABLE `permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(150) NOT NULL,
  `main_module` varchar(80) DEFAULT NULL,
  `sub_module` varchar(120) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `guard_name` varchar(50) NOT NULL DEFAULT 'api',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `permissions`
--

INSERT INTO `permissions` (`id`, `name`, `main_module`, `sub_module`, `action`, `description`, `guard_name`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'dashboard.view', 'Administration', 'Dashboard', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(2, 'roles.view', 'Administration', 'Roles', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(3, 'roles.create', 'Administration', 'Roles', 'Create', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(4, 'roles.update', 'Administration', 'Roles', 'Update', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(5, 'roles.delete', 'Administration', 'Roles', 'Delete', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(6, 'permissions.view', 'Administration', 'Permissions', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(7, 'permissions.create', 'Administration', 'Permissions', 'Create', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(8, 'permissions.update', 'Administration', 'Permissions', 'Update', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(9, 'permissions.delete', 'Administration', 'Permissions', 'Delete', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(10, 'role-permissions.manage', 'Administration', 'Roles', 'Manage', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(11, 'masters.view', 'Masters', 'Masters', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(12, 'masters.manage', 'Masters', 'Masters', 'Manage', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(13, 'inventory.view', 'Inventory', 'Inventory', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(14, 'inventory.manage', 'Inventory', 'Inventory', 'Manage', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(15, 'purchase-grn.view', 'Inventory', 'GRN', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(16, 'purchase-grn.manage', 'Inventory', 'GRN', 'Manage', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(17, 'bom.view', 'Production', 'BOM', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(18, 'bom.manage', 'Production', 'BOM', 'Manage', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(19, 'production.view', 'Production', 'Production', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(20, 'production.manage', 'Production', 'Production', 'Manage', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(21, 'dispatch.view', 'Dispatch', 'Dispatch', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(22, 'dispatch.manage', 'Dispatch', 'Dispatch', 'Manage', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(23, 'accounts.view', 'Finance', 'Accounts', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(24, 'accounts.manage', 'Finance', 'Accounts', 'Manage', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(25, 'reports.view', 'Reports', 'Reports', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(26, 'audit.view', 'Administration', 'Audit', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(27, 'users.view', 'Administration', 'Users', 'View', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(28, 'users.manage', 'Administration', 'Users', 'Manage', NULL, 'api', '2026-06-08 01:05:00', '2026-06-09 08:21:54', NULL),
(29, 'stock-ledger.view', 'Inventory', 'Stock Ledger', 'View', NULL, 'api', '2026-06-09 07:00:26', '2026-06-09 08:21:54', NULL),
(30, 'stock-summary.view', 'Inventory', 'Stock Summary', 'View', NULL, 'api', '2026-06-09 07:00:26', '2026-06-09 08:21:54', NULL),
(31, 'stock-verification.view', 'Inventory', 'Stock Verification', 'View', NULL, 'api', '2026-06-09 07:00:26', '2026-06-09 08:21:54', NULL),
(32, 'stock-verification.create', 'Inventory', 'Stock Verification', 'Create', NULL, 'api', '2026-06-09 07:00:26', '2026-06-09 08:21:54', NULL),
(33, 'stock-verification.edit', 'Inventory', 'Stock Verification', 'Edit', NULL, 'api', '2026-06-09 07:00:26', '2026-06-09 08:21:54', NULL),
(34, 'stock-verification.submit', 'Inventory', 'Stock Verification', 'Submit', NULL, 'api', '2026-06-09 07:00:26', '2026-06-09 08:21:54', NULL),
(35, 'stock-verification.approve', 'Inventory', 'Stock Verification', 'Approve', NULL, 'api', '2026-06-09 07:00:26', '2026-06-09 08:21:54', NULL),
(36, 'stock-verification.cancel', 'Inventory', 'Stock Verification', 'Cancel', NULL, 'api', '2026-06-09 07:00:26', '2026-06-09 08:21:54', NULL),
(37, 'modules.view', 'Administration', 'Modules', 'View', NULL, 'api', '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(38, 'modules.create', 'Administration', 'Modules', 'Create', NULL, 'api', '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(39, 'modules.update', 'Administration', 'Modules', 'Update', NULL, 'api', '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(40, 'modules.delete', 'Administration', 'Modules', 'Delete', NULL, 'api', '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(41, 'production.post', NULL, NULL, NULL, NULL, 'api', '2026-06-11 00:01:12', '2026-06-11 00:01:12', NULL),
(42, 'production.cancel', NULL, NULL, NULL, NULL, 'api', '2026-06-11 00:01:12', '2026-06-11 00:01:12', NULL),
(43, 'wastage.view', NULL, NULL, NULL, NULL, 'api', '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(44, 'wastage.manage', NULL, NULL, NULL, NULL, 'api', '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(45, 'wastage.post', NULL, NULL, NULL, NULL, 'api', '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(46, 'wastage.cancel', NULL, NULL, NULL, NULL, 'api', '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(47, 'wastage-reuse.view', NULL, NULL, NULL, NULL, 'api', '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(48, 'wastage-reuse.manage', NULL, NULL, NULL, NULL, 'api', '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(49, 'wastage-reuse.post', NULL, NULL, NULL, NULL, 'api', '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(50, 'wastage-reuse.cancel', NULL, NULL, NULL, NULL, 'api', '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` text NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `personal_access_tokens`
--

INSERT INTO `personal_access_tokens` (`id`, `tokenable_type`, `tokenable_id`, `name`, `token`, `abilities`, `last_used_at`, `expires_at`, `created_at`, `updated_at`) VALUES
(1, 'App\\Models\\User', 1, 'erp-api-token', '9ea760ee67627db6defc091160d907067db81aa2021a7b22372751c5b1c6d52e', '[\"*\"]', '2026-06-08 01:05:33', NULL, '2026-06-08 01:05:31', '2026-06-08 01:05:33'),
(2, 'App\\Models\\User', 2, 'erp-api-token', '0ca4b8265b5725306367ce5f227f4077019ab67c94e39dc77416cb093904e6fd', '[\"*\"]', '2026-06-08 01:05:41', NULL, '2026-06-08 01:05:39', '2026-06-08 01:05:41'),
(3, 'App\\Models\\User', 3, 'erp-api-token', '3e98af18996fa4c79d82107f330f123f1c72151b3d05ac133fcec8b225ca2cde', '[\"*\"]', '2026-06-08 01:05:47', NULL, '2026-06-08 01:05:45', '2026-06-08 01:05:47'),
(4, 'App\\Models\\User', 4, 'erp-api-token', '33ab951914c1bbe7e48e5cc598faf67c96e188c75ebcfc1feeaffdae9fff724a', '[\"*\"]', '2026-06-08 01:05:53', NULL, '2026-06-08 01:05:51', '2026-06-08 01:05:53'),
(5, 'App\\Models\\User', 5, 'erp-api-token', '47b88f169b462974460ee15e13ee2be8821d6bede0966e99ff292ff5cad7dd91', '[\"*\"]', '2026-06-08 01:05:59', NULL, '2026-06-08 01:05:57', '2026-06-08 01:05:59'),
(6, 'App\\Models\\User', 6, 'erp-api-token', 'b5af8eceb18dd8719750b03311b5aa85d47455036d7c5e3e12e244a777935ea4', '[\"*\"]', '2026-06-08 01:06:05', NULL, '2026-06-08 01:06:03', '2026-06-08 01:06:05'),
(8, 'App\\Models\\User', 1, 'erp-api-token', '296f1281d82dc021eefb06060ea54d167375542ebc389caedb10bf8c0c18ee4b', '[\"*\"]', '2026-06-08 01:36:26', NULL, '2026-06-08 01:33:14', '2026-06-08 01:36:26'),
(9, 'App\\Models\\User', 2, 'erp-api-token', '6dcf7e32b14484ef3e93eeb01a3ceb48b8f3c5e9541905234212485e77259a1c', '[\"*\"]', '2026-06-09 09:19:26', NULL, '2026-06-09 04:35:11', '2026-06-09 09:19:26'),
(10, 'App\\Models\\User', 2, 'erp-api-token', 'd30b0f42d9b22bf875c05bd9d3723326e5b68453d3d38b7773874d3fb6ac452c', '[\"*\"]', '2026-06-10 03:50:29', NULL, '2026-06-10 01:58:02', '2026-06-10 03:50:29'),
(12, 'App\\Models\\User', 2, 'erp-api-token', 'f21a42c0dccdaf91e5b2376afa30a14393cda8f7c374836caebec5d5fbe7f2cd', '[\"*\"]', '2026-06-11 08:25:56', NULL, '2026-06-11 06:24:49', '2026-06-11 08:25:56'),
(14, 'App\\Models\\User', 2, 'erp-api-token', '6016bcfbf6d97131a5be7b6fe27a7998d20066482274bb61ef38918e8d306ec8', '[\"*\"]', '2026-06-12 03:58:47', NULL, '2026-06-12 00:35:04', '2026-06-12 03:58:47');

-- --------------------------------------------------------

--
-- Table structure for table `production_consumption`
--

CREATE TABLE `production_consumption` (
  `consumption_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `production_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `uom_id` bigint(20) UNSIGNED DEFAULT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `required_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `consumed_qty` decimal(18,3) NOT NULL,
  `wastage_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `production_consumption`
--

INSERT INTO `production_consumption` (`consumption_id`, `tenant_id`, `branch_id`, `production_id`, `item_id`, `uom_id`, `location_id`, `required_qty`, `consumed_qty`, `wastage_qty`, `remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 1, NULL, NULL, 0.000, 15.300, 0.000, NULL, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(2, 1, 1, 2, 3, 2, 4, 1122.000, 1122.000, 0.000, NULL, 2, 2, '2026-06-11 00:24:25', '2026-06-11 00:24:25', NULL),
(3, 1, 1, 3, 3, 1, 6, 1000.000, 1000.000, 0.000, NULL, 2, 2, '2026-06-12 03:17:32', '2026-06-12 03:17:32', NULL),
(4, 1, 1, 3, 1, 5, 6, 1000.000, 1000.000, 0.000, NULL, 2, 2, '2026-06-12 03:17:32', '2026-06-12 03:17:32', NULL),
(5, 1, 1, 4, 3, 1, 1, 100.000, 100.000, 0.000, NULL, 2, 2, '2026-06-12 03:29:33', '2026-06-12 03:29:33', NULL),
(6, 1, 1, 4, 1, 5, 1, 100.000, 100.000, 0.000, NULL, 2, 2, '2026-06-12 03:29:33', '2026-06-12 03:29:33', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `production_master`
--

CREATE TABLE `production_master` (
  `production_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `production_no` varchar(50) NOT NULL,
  `production_date` date NOT NULL,
  `bom_id` bigint(20) UNSIGNED DEFAULT NULL,
  `pallet_model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `produced_item_id` bigint(20) UNSIGNED DEFAULT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `fg_location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `produced_qty` decimal(18,3) NOT NULL,
  `production_cost` decimal(18,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'Draft',
  `remarks` text DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `posted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `production_master`
--

INSERT INTO `production_master` (`production_id`, `tenant_id`, `branch_id`, `production_no`, `production_date`, `bom_id`, `pallet_model_id`, `produced_item_id`, `team_id`, `fg_location_id`, `produced_qty`, `production_cost`, `status`, `remarks`, `posted_at`, `posted_by`, `cancelled_at`, `cancelled_by`, `cancellation_reason`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'PROD-TEST-001', '2026-06-07', NULL, 1, NULL, 1, NULL, 18.000, 21600.00, 'Draft', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(2, 1, 1, 'PROD-2026-000002', '2026-06-11', 3, 2, 7, 3, 5, 10.000, 250.00, 'Draft', NULL, NULL, NULL, NULL, NULL, NULL, 2, 2, '2026-06-11 00:24:25', '2026-06-11 00:24:25', NULL),
(3, 1, 1, 'PROD-2026-000003', '2026-06-12', 4, 1, 7, 1, 1, 10.000, 0.00, 'Draft', NULL, NULL, NULL, NULL, NULL, NULL, 2, 2, '2026-06-12 03:17:32', '2026-06-12 03:17:32', NULL),
(4, 1, 1, 'PROD-2026-000004', '2026-06-12', 4, 1, 13, 3, 6, 1.000, 0.00, 'Posted', NULL, '2026-06-12 03:29:39', 2, NULL, NULL, NULL, 2, 2, '2026-06-12 03:29:33', '2026-06-12 03:29:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `production_output`
--

CREATE TABLE `production_output` (
  `output_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `production_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `qty` decimal(18,3) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `production_output`
--

INSERT INTO `production_output` (`output_id`, `tenant_id`, `branch_id`, `production_id`, `item_id`, `location_id`, `qty`, `remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 6, NULL, 18.000, NULL, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(2, 1, 1, 4, 13, 6, 1.000, NULL, 2, 2, '2026-06-12 03:29:39', '2026-06-12 03:29:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `production_wastage`
--

CREATE TABLE `production_wastage` (
  `wastage_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `production_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `qty` decimal(18,3) NOT NULL,
  `wastage_type` varchar(30) NOT NULL DEFAULT 'Scrap',
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `production_wastage`
--

INSERT INTO `production_wastage` (`wastage_id`, `tenant_id`, `branch_id`, `production_id`, `item_id`, `location_id`, `qty`, `wastage_type`, `remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 8, NULL, 1.100, 'Scrap', NULL, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(2, 1, 1, 2, 2, 5, 1.000, 'Scrap', 'aa', 2, 2, '2026-06-11 00:24:25', '2026-06-11 00:24:25', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `guard_name` varchar(50) NOT NULL DEFAULT 'api',
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `tenant_id`, `branch_id`, `name`, `description`, `guard_name`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'Super Admin', NULL, 'api', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(2, 1, NULL, 'Admin', NULL, 'api', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(3, 1, NULL, 'Manager', NULL, 'api', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(4, 1, NULL, 'Store', NULL, 'api', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(5, 1, NULL, 'Production', NULL, 'api', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(6, 1, NULL, 'Accounts', NULL, 'api', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_master`
--

CREATE TABLE `role_master` (
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `role_name` varchar(100) NOT NULL,
  `guard_name` varchar(50) NOT NULL DEFAULT 'api',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_master`
--

INSERT INTO `role_master` (`role_id`, `tenant_id`, `branch_id`, `role_name`, `guard_name`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'Super Admin', 'api', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(2, 1, NULL, 'Admin', 'api', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(3, 1, NULL, 'Manager', 'api', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(4, 1, NULL, 'Store', 'api', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(5, 1, NULL, 'Production', 'api', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(6, 1, NULL, 'Accounts', 'api', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `role_permissions`
--

CREATE TABLE `role_permissions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `role_id` bigint(20) UNSIGNED NOT NULL,
  `permission_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `role_permissions`
--

INSERT INTO `role_permissions` (`id`, `role_id`, `permission_id`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(2, 1, 2, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(3, 1, 3, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(4, 1, 4, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(5, 1, 5, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(6, 1, 6, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(7, 1, 7, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(8, 1, 8, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(9, 1, 9, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(10, 1, 10, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(11, 1, 11, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(12, 1, 12, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(13, 1, 13, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(14, 1, 14, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(15, 1, 15, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(16, 1, 16, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(17, 1, 17, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(18, 1, 18, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(19, 1, 19, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(20, 1, 20, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(21, 1, 21, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(22, 1, 22, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(23, 1, 23, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(24, 1, 24, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(25, 1, 25, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(26, 1, 26, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(27, 2, 1, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(28, 2, 2, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(29, 2, 3, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(30, 2, 4, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(31, 2, 5, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(32, 2, 6, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(33, 2, 7, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(34, 2, 8, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(35, 2, 9, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(36, 2, 10, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(37, 2, 11, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(38, 2, 12, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(39, 2, 13, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(40, 2, 14, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(41, 2, 15, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(42, 2, 16, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(43, 2, 17, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(44, 2, 18, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(45, 2, 19, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(46, 2, 20, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(47, 2, 21, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(48, 2, 22, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(49, 2, 23, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(50, 2, 24, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(51, 2, 25, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(52, 2, 26, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(53, 3, 1, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(54, 3, 11, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(55, 3, 13, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(56, 3, 15, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(57, 3, 17, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(58, 3, 19, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(59, 3, 21, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(60, 3, 25, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(61, 5, 1, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(62, 5, 13, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(63, 5, 17, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(64, 5, 18, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(65, 5, 19, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(66, 5, 20, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(67, 5, 25, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(68, 4, 1, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(69, 4, 11, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(70, 4, 13, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(71, 4, 14, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(72, 4, 15, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(73, 4, 16, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(74, 4, 21, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(75, 4, 22, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(76, 4, 25, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(77, 6, 1, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(78, 6, 15, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(79, 6, 23, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(80, 6, 24, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(81, 6, 25, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(82, 6, 26, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(95, 1, 27, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(96, 1, 28, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(123, 2, 27, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(124, 2, 28, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(169, 1, 29, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(170, 1, 30, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(171, 1, 31, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(172, 1, 32, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(173, 1, 33, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(174, 1, 34, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(175, 1, 35, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(176, 1, 36, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(177, 2, 29, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(178, 2, 30, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(179, 2, 31, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(180, 2, 32, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(181, 2, 33, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(182, 2, 34, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(183, 2, 35, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(184, 2, 36, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(185, 3, 29, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(186, 3, 30, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(187, 3, 31, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(188, 4, 29, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(189, 4, 30, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(190, 4, 31, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(191, 4, 32, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(192, 4, 33, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(193, 4, 34, '2026-06-09 07:00:26', '2026-06-09 07:00:26', NULL),
(194, 2, 38, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(195, 2, 40, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(196, 2, 39, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(197, 2, 37, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(198, 1, 38, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(199, 1, 40, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(200, 1, 39, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(201, 1, 37, '2026-06-09 08:50:41', '2026-06-09 08:50:41', NULL),
(202, 2, 42, '2026-06-11 00:01:12', '2026-06-11 00:01:12', NULL),
(203, 2, 41, '2026-06-11 00:01:12', '2026-06-11 00:01:12', NULL),
(204, 5, 42, '2026-06-11 00:01:12', '2026-06-11 00:01:12', NULL),
(205, 5, 41, '2026-06-11 00:01:12', '2026-06-11 00:01:12', NULL),
(206, 1, 42, '2026-06-11 00:01:12', '2026-06-11 00:01:12', NULL),
(207, 1, 41, '2026-06-11 00:01:12', '2026-06-11 00:01:12', NULL),
(208, 2, 50, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(209, 2, 48, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(210, 2, 49, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(211, 2, 47, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(212, 2, 46, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(213, 2, 44, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(214, 2, 45, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(215, 2, 43, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(216, 5, 50, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(217, 5, 48, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(218, 5, 49, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(219, 5, 47, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(220, 5, 46, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(221, 5, 44, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(222, 5, 45, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(223, 5, 43, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(224, 4, 50, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(225, 4, 48, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(226, 4, 49, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(227, 4, 47, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(228, 4, 46, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(229, 4, 44, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(230, 4, 45, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(231, 4, 43, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(232, 1, 50, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(233, 1, 48, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(234, 1, 49, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(235, 1, 47, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(236, 1, 46, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(237, 1, 44, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(238, 1, 45, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL),
(239, 1, 43, '2026-06-11 01:44:08', '2026-06-11 01:44:08', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sessions`
--

CREATE TABLE `sessions` (
  `id` varchar(255) NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `payload` longtext NOT NULL,
  `last_activity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `sessions`
--

INSERT INTO `sessions` (`id`, `user_id`, `ip_address`, `user_agent`, `payload`, `last_activity`) VALUES
('e9Ts3xPZWC7Mq1mqpUOkXNFJUNyVY2urHIkfkkvv', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiNHVudDc3QlR4WlNiUElheEZJNDl2cHR0c3ZHNjVLRjczRGt4S203VCI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1780901985),
('VsMR8Ctvjlr35TXWAZBnu1xbPfjJDQ8pnmWrecHv', NULL, '127.0.0.1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/149.0.0.0 Safari/537.36', 'YTozOntzOjY6Il90b2tlbiI7czo0MDoiY2gxMHdVaVg5NjF2QTFzYUxLMkZJSHlZOHlyeURPZGtQd2FYb2VPciI7czo5OiJfcHJldmlvdXMiO2E6Mjp7czozOiJ1cmwiO3M6MjE6Imh0dHA6Ly8xMjcuMC4wLjE6ODAwMCI7czo1OiJyb3V0ZSI7Tjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==', 1780901162);

-- --------------------------------------------------------

--
-- Table structure for table `state_master`
--

CREATE TABLE `state_master` (
  `state_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `state_name` varchar(100) NOT NULL,
  `state_code` varchar(20) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `state_master`
--

INSERT INTO `state_master` (`state_id`, `tenant_id`, `state_name`, `state_code`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, NULL, 'Andaman and Nicobar Islands', 'AN', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(2, NULL, 'Andhra Pradesh', 'AP', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(3, NULL, 'Arunachal Pradesh', 'AR', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(4, NULL, 'Assam', 'AS', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(5, NULL, 'Bihar', 'BR', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(6, NULL, 'Chandigarh', 'CH', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(7, NULL, 'Chhattisgarh', 'CT', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(8, NULL, 'Dadra and Nagar Haveli and Daman and Diu', 'DN', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(9, NULL, 'Delhi', 'DL', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(10, NULL, 'Goa', 'GA', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(11, NULL, 'Gujarat', 'GJ', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(12, NULL, 'Haryana', 'HR', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(13, NULL, 'Himachal Pradesh', 'HP', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(14, NULL, 'Jammu and Kashmir', 'JK', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(15, NULL, 'Jharkhand', 'JH', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(16, NULL, 'Karnataka', 'KA', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(17, NULL, 'Kerala', 'KL', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(18, NULL, 'Ladakh', 'LA', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(19, NULL, 'Lakshadweep', 'LD', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(20, NULL, 'Madhya Pradesh', 'MP', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(21, NULL, 'Maharashtra', 'MH', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(22, NULL, 'Manipur', 'MN', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(23, NULL, 'Meghalaya', 'ML', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(24, NULL, 'Mizoram', 'MZ', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(25, NULL, 'Nagaland', 'NL', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(26, NULL, 'Odisha', 'OD', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(27, NULL, 'Puducherry', 'PY', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(28, NULL, 'Punjab', 'PB', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(29, NULL, 'Rajasthan', 'RJ', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(30, NULL, 'Sikkim', 'SK', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(31, NULL, 'Tamil Nadu', 'TN', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(32, NULL, 'Telangana', 'TG', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(33, NULL, 'Tripura', 'TR', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(34, NULL, 'Uttar Pradesh', 'UP', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(35, NULL, 'Uttarakhand', 'UT', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL),
(36, NULL, 'West Bengal', 'WB', 'Active', NULL, NULL, '2026-06-09 08:21:54', '2026-06-09 08:21:54', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustment_detail`
--

CREATE TABLE `stock_adjustment_detail` (
  `detail_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `adjustment_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `adjustment_qty` decimal(18,3) NOT NULL,
  `adjustment_type` varchar(20) NOT NULL DEFAULT 'Excess',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_adjustment_detail`
--

INSERT INTO `stock_adjustment_detail` (`detail_id`, `tenant_id`, `branch_id`, `adjustment_id`, `item_id`, `location_id`, `adjustment_qty`, `adjustment_type`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 6, NULL, 1.000, 'Excess', NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_adjustment_master`
--

CREATE TABLE `stock_adjustment_master` (
  `adjustment_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `adjustment_no` varchar(50) DEFAULT NULL,
  `adjustment_date` date NOT NULL,
  `verification_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_type` varchar(50) DEFAULT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_adjustment_master`
--

INSERT INTO `stock_adjustment_master` (`adjustment_id`, `tenant_id`, `branch_id`, `adjustment_no`, `adjustment_date`, `verification_id`, `reference_type`, `reference_id`, `remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, NULL, '2026-06-08', NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_ledger`
--

CREATE TABLE `stock_ledger` (
  `ledger_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `stock_type` varchar(30) NOT NULL DEFAULT 'Fresh',
  `transaction_date` datetime NOT NULL,
  `transaction_type` varchar(50) NOT NULL,
  `reference_id` bigint(20) UNSIGNED DEFAULT NULL,
  `reference_type` varchar(100) DEFAULT NULL,
  `qty_in` decimal(18,3) NOT NULL DEFAULT 0.000,
  `qty_out` decimal(18,3) NOT NULL DEFAULT 0.000,
  `balance_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `rate` decimal(18,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_ledger`
--

INSERT INTO `stock_ledger` (`ledger_id`, `tenant_id`, `branch_id`, `item_id`, `location_id`, `stock_type`, `transaction_date`, `transaction_type`, `reference_id`, `reference_type`, `qty_in`, `qty_out`, `balance_qty`, `rate`, `amount`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 1, 'Fresh', '2026-06-08 06:34:44', 'Opening', NULL, NULL, 500.000, 0.000, 500.000, 0.00, 0.00, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(2, 1, 1, 2, 1, 'Fresh', '2026-06-08 06:34:44', 'Opening', NULL, NULL, 420.000, 0.000, 420.000, 0.00, 0.00, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(3, 1, 1, 3, 1, 'Fresh', '2026-06-08 06:34:44', 'Opening', NULL, NULL, 250.000, 0.000, 250.000, 0.00, 0.00, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(4, 1, 1, 4, 1, 'Fresh', '2026-06-08 06:34:44', 'Opening', NULL, NULL, 180.000, 0.000, 180.000, 0.00, 0.00, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(5, 1, 1, 5, 1, 'Fresh', '2026-06-08 06:34:44', 'Opening', NULL, NULL, 120.000, 0.000, 120.000, 0.00, 0.00, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(6, 1, 1, 6, 3, 'Fresh', '2026-06-08 06:34:44', 'Opening', NULL, NULL, 40.000, 0.000, 40.000, 0.00, 0.00, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(7, 1, 1, 7, 3, 'Fresh', '2026-06-08 06:34:44', 'Opening', NULL, NULL, 25.000, 0.000, 25.000, 0.00, 0.00, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(8, 1, 1, 1, 1, 'Fresh', '2026-06-08 06:35:00', 'GRN', 1, 'grn_master', 75.000, 0.000, 575.000, 0.00, 0.00, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(9, 1, 1, 4, 1, 'Fresh', '2026-06-08 06:35:00', 'GRN', 1, 'grn_master', 20.000, 0.000, 200.000, 0.00, 0.00, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(10, 1, 1, 1, 1, 'Fresh', '2026-06-08 06:35:00', 'Production', 1, 'production_master', 0.000, 15.300, 559.700, 0.00, 0.00, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(11, 1, 1, 6, 3, 'Fresh', '2026-06-08 06:35:00', 'Production', 1, 'production_master', 18.000, 0.000, 58.000, 0.00, 0.00, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(12, 1, 1, 8, 4, 'Fresh', '2026-06-08 06:35:00', 'Wastage', 1, 'production_master', 1.100, 0.000, 1.100, 0.00, 0.00, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(13, 1, 1, 6, 3, 'Fresh', '2026-06-08 06:35:00', 'Dispatch', 1, 'challan_master', 0.000, 12.000, 46.000, 0.00, 0.00, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(14, 1, 1, 6, 3, 'Fresh', '2026-06-08 06:35:00', 'Adjustment', 1, 'stock_adjustment_master', 1.000, 0.000, 47.000, 0.00, 0.00, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(15, 1, 1, 7, 5, 'Fresh', '2026-06-09 00:00:00', 'GRN', 3, 'GRN', 100.000, 0.000, 100.000, 50.00, 5000.00, 2, 2, '2026-06-09 06:37:37', '2026-06-09 06:37:37', NULL),
(16, 1, 1, 5, 5, 'Fresh', '2026-06-10 00:00:00', 'GRN', 4, 'GRN', 1.000, 0.000, 1.000, 11.00, 11.00, 2, 2, '2026-06-10 02:49:02', '2026-06-10 02:49:02', NULL),
(17, 1, 1, 7, 5, 'Fresh', '2026-06-11 00:00:00', 'GRN', 8, 'GRN', 100.000, 0.000, 200.000, 50.00, 5000.00, 2, 2, '2026-06-11 08:13:16', '2026-06-11 08:13:16', NULL),
(18, 1, 1, 4, 1, 'Fresh', '2026-06-12 07:38:56', 'Stock Import', 1001, 'STOCK_IMPORT', 10.000, 0.000, 210.000, 125.50, 1255.00, 2, 2, '2026-06-12 02:08:56', '2026-06-12 02:08:56', NULL),
(19, 1, 1, 4, 6, 'Fresh', '2026-06-12 07:38:56', 'Stock Import', 260612073856, 'STOCK_IMPORT', 5.000, 0.000, 5.000, 5552.00, 27760.00, 2, 2, '2026-06-12 02:08:56', '2026-06-12 02:08:56', NULL),
(20, 1, 1, 3, 1, 'Fresh', '2026-06-12 00:00:00', 'Production Consumption', 4, 'Production', 0.000, 100.000, 150.000, 0.00, 0.00, 2, 2, '2026-06-12 03:29:39', '2026-06-12 03:29:39', NULL),
(21, 1, 1, 1, 1, 'Fresh', '2026-06-12 00:00:00', 'Production Consumption', 4, 'Production', 0.000, 100.000, 459.700, 0.00, 0.00, 2, 2, '2026-06-12 03:29:39', '2026-06-12 03:29:39', NULL),
(22, 1, 1, 13, 6, 'Fresh', '2026-06-12 00:00:00', 'Production Output', 4, 'Production', 1.000, 0.000, 1.000, 0.00, 0.00, 2, 2, '2026-06-12 03:29:39', '2026-06-12 03:29:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_summary`
--

CREATE TABLE `stock_summary` (
  `stock_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `stock_type` varchar(30) NOT NULL DEFAULT 'Fresh',
  `stock_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `avg_rate` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_summary`
--

INSERT INTO `stock_summary` (`stock_id`, `tenant_id`, `branch_id`, `item_id`, `location_id`, `stock_type`, `stock_qty`, `avg_rate`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 1, 'Fresh', 459.700, 825.00, NULL, 2, '2026-06-08 01:05:00', '2026-06-12 03:29:39', NULL),
(2, 1, 1, 2, 1, 'Fresh', 420.000, 790.00, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(3, 1, 1, 3, 1, 'Fresh', 150.000, 550.00, NULL, 2, '2026-06-08 01:04:44', '2026-06-12 03:29:39', NULL),
(4, 1, 1, 4, 1, 'Fresh', 210.000, 99.31, NULL, 2, '2026-06-08 01:05:00', '2026-06-12 02:08:56', NULL),
(5, 1, 1, 5, 1, 'Fresh', 120.000, 140.00, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(6, 1, 1, 6, 3, 'Fresh', 47.000, 1200.00, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(7, 1, 1, 7, 3, 'Fresh', 25.000, 1550.00, NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(8, 1, 1, 8, 4, 'Fresh', 1.100, 0.00, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(9, 1, 1, 7, 5, 'Fresh', 200.000, 50.00, 2, 2, '2026-06-09 06:37:37', '2026-06-11 08:13:16', NULL),
(10, 1, 1, 5, 5, 'Fresh', 1.000, 11.00, 2, 2, '2026-06-10 02:49:02', '2026-06-10 02:49:02', NULL),
(11, 1, 1, 4, 6, 'Fresh', 5.000, 5552.00, 2, 2, '2026-06-12 02:08:56', '2026-06-12 02:08:56', NULL),
(12, 1, 1, 13, 6, 'Fresh', 1.000, 0.00, 2, 2, '2026-06-12 03:29:39', '2026-06-12 03:29:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_verification_detail`
--

CREATE TABLE `stock_verification_detail` (
  `detail_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `verification_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `uom_id` bigint(20) UNSIGNED DEFAULT NULL,
  `system_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `physical_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `variance_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `variance_type` varchar(20) NOT NULL DEFAULT 'Matched',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_verification_detail`
--

INSERT INTO `stock_verification_detail` (`detail_id`, `tenant_id`, `branch_id`, `verification_id`, `item_id`, `location_id`, `uom_id`, `system_qty`, `physical_qty`, `variance_qty`, `variance_type`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 6, NULL, NULL, 46.000, 47.000, 1.000, 'Matched', NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `stock_verification_master`
--

CREATE TABLE `stock_verification_master` (
  `verification_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `verification_no` varchar(50) NOT NULL,
  `verification_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Draft',
  `remarks` text DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `submitted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `approved_by` bigint(20) UNSIGNED DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `stock_verification_master`
--

INSERT INTO `stock_verification_master` (`verification_id`, `tenant_id`, `branch_id`, `location_id`, `verification_no`, `verification_date`, `status`, `remarks`, `submitted_at`, `submitted_by`, `approved_at`, `approved_by`, `cancelled_at`, `cancelled_by`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, NULL, 'SV-TEST-001', '2026-06-08', 'Draft', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `storage_location_master`
--

CREATE TABLE `storage_location_master` (
  `location_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `location_code` varchar(30) DEFAULT NULL,
  `location_name` varchar(100) NOT NULL,
  `location_type` enum('RM','WIP','FG','WASTAGE','SCRAP') NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'Active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `storage_location_master`
--

INSERT INTO `storage_location_master` (`location_id`, `tenant_id`, `branch_id`, `location_code`, `location_name`, `location_type`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'Factory-Fresh-01', 'Factory-Fresh-01', 'RM', 'Active', NULL, 2, '2026-06-08 01:04:44', '2026-06-12 01:15:20', NULL),
(2, 1, 1, 'WIP', 'WIP', 'WIP', 'Active', NULL, 2, '2026-06-08 01:04:44', '2026-06-11 08:08:33', '2026-06-11 08:08:33'),
(3, 1, 1, 'FG', 'FG', 'FG', 'Active', NULL, 2, '2026-06-08 01:04:44', '2026-06-11 08:08:28', '2026-06-11 08:08:28'),
(4, 1, 1, 'WASTAGE', 'WASTAGE', 'WASTAGE', 'Active', NULL, 2, '2026-06-08 01:04:44', '2026-06-11 08:08:22', '2026-06-11 08:08:22'),
(5, 1, 1, 'SCRAP', 'SCRAP', 'SCRAP', 'Active', NULL, 2, '2026-06-08 01:04:44', '2026-06-11 08:08:16', '2026-06-11 08:08:16'),
(6, 1, 1, 'Factory-Wastage-01', 'Factory-Wastage-01', 'RM', 'Active', 2, 2, '2026-06-12 01:15:47', '2026-06-12 01:15:47', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `team_ledger`
--

CREATE TABLE `team_ledger` (
  `ledger_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `pallet_model_id` bigint(20) UNSIGNED DEFAULT NULL,
  `transaction_type` enum('Production','Dispatch') NOT NULL,
  `transaction_date` date NOT NULL,
  `qty` decimal(18,3) NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_ledger`
--

INSERT INTO `team_ledger` (`ledger_id`, `tenant_id`, `branch_id`, `team_id`, `pallet_model_id`, `transaction_type`, `transaction_date`, `qty`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 1, 'Production', '2026-06-07', 18.000, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(2, 1, 1, 1, 1, 'Dispatch', '2026-06-08', 12.000, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL),
(3, 1, 1, 3, 1, 'Production', '2026-06-12', 1.000, 2, 2, '2026-06-12 03:29:39', '2026-06-12 03:29:39', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `team_master`
--

CREATE TABLE `team_master` (
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `team_code` varchar(30) DEFAULT NULL,
  `team_name` varchar(100) NOT NULL,
  `contractor_name` varchar(255) DEFAULT NULL,
  `rate_per_pallet` decimal(18,2) NOT NULL DEFAULT 0.00,
  `tds_percent` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_master`
--

INSERT INTO `team_master` (`team_id`, `tenant_id`, `branch_id`, `team_code`, `team_name`, `contractor_name`, `rate_per_pallet`, `tds_percent`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 'TEAM-01', 'TEAM-01', 'Contractor 1', 25.00, 1.00, 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(2, 1, 1, 'TEAM-02', 'TEAM-02', 'Contractor 2', 30.00, 1.00, 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(3, 1, 1, 'TEAM-03', 'TEAM-03', 'Contractor 3', 35.00, 1.00, 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(4, 1, 1, 'ss', 'aa', 'aa', 1.00, 1.00, 'Active', 2, 2, '2026-06-11 07:17:12', '2026-06-11 07:17:31', '2026-06-11 07:17:31');

-- --------------------------------------------------------

--
-- Table structure for table `team_payment_summary`
--

CREATE TABLE `team_payment_summary` (
  `payment_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED NOT NULL,
  `payment_month` tinyint(3) UNSIGNED NOT NULL,
  `payment_year` smallint(5) UNSIGNED NOT NULL,
  `dispatch_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `gross_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `tds_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `net_payable` decimal(18,2) NOT NULL DEFAULT 0.00,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `team_payment_summary`
--

INSERT INTO `team_payment_summary` (`payment_id`, `tenant_id`, `branch_id`, `team_id`, `payment_month`, `payment_year`, `dispatch_qty`, `gross_amount`, `tds_amount`, `net_payable`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 1, 6, 2026, 12.000, 300.00, 3.00, 297.00, NULL, NULL, '2026-06-08 01:05:00', '2026-06-08 01:05:00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tenant_master`
--

CREATE TABLE `tenant_master` (
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_code` varchar(20) DEFAULT NULL,
  `tenant_name` varchar(255) NOT NULL,
  `company_name` varchar(255) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tenant_master`
--

INSERT INTO `tenant_master` (`tenant_id`, `tenant_code`, `tenant_name`, `company_name`, `address`, `mobile`, `email`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 'SURESH', 'Suresh Timber', 'Suresh Timber', 'Main Industrial Area', '9000000000', 'admin@sureshtimber.test', 'Active', '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `uom_master`
--

CREATE TABLE `uom_master` (
  `uom_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `uom_code` varchar(20) DEFAULT NULL,
  `uom_name` varchar(50) NOT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `uom_master`
--

INSERT INTO `uom_master` (`uom_id`, `tenant_id`, `branch_id`, `uom_code`, `uom_name`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'PCS', 'PCS', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(2, 1, NULL, 'NOS', 'NOS', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(3, 1, NULL, 'KG', 'KG', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(4, 1, NULL, 'BOX', 'BOX', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(5, 1, NULL, 'CFT', 'CFT', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(6, 1, NULL, 'MTR', 'MTR', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(7, 1, NULL, 'a', 'a1', 2, 2, '2026-06-11 07:13:50', '2026-06-11 07:14:04', '2026-06-11 07:14:04');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `login_id` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `full_name` varchar(255) NOT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `remember_token` varchar(100) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `tenant_id`, `branch_id`, `login_id`, `password`, `employee_code`, `full_name`, `mobile`, `email`, `role_id`, `status`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 'superadmin', '$2y$12$Y9AVH5mRIPe9o4lm5mxXFeh9wLkq7ZmZW4MwQFoFhbwN5.fEdC0K6', 'SA001', 'Super Admin', '9000000000', 'superadmin@sureshtimber.test', 1, 'Active', NULL, '2026-06-08 01:04:46', '2026-06-08 01:04:46', NULL),
(2, 1, 1, 'admin', '$2y$12$e8zpmtXpMGdw3MXeBCfu.eHqZdTPe.pP8yQUHkguPWFCM3aBkBFv6', 'AD001', 'Admin', '9000000000', 'admin@sureshtimber.test', 2, 'Active', NULL, '2026-06-08 01:04:48', '2026-06-08 01:04:48', NULL),
(3, 1, 1, 'manager', '$2y$12$ONZXaIov43UegRrMbtyWEu3K2uAmeKPOobLG2lLat.hP1HtlfNOGy', 'MG001', 'Manager', '9000000000', 'manager@sureshtimber.test', 3, 'Active', NULL, '2026-06-08 01:04:51', '2026-06-08 01:04:51', NULL),
(4, 1, 2, 'store', '$2y$12$6hRnwvKlnuk/YwET4FMK1eKtJeaTe8mvKOqIIkp02nWp5TRH6TE.6', 'ST001', 'Store', '9000000000', 'store@sureshtimber.test', 4, 'Active', NULL, '2026-06-08 01:04:53', '2026-06-08 01:04:53', NULL),
(5, 1, 1, 'production', '$2y$12$LUfHWP47nC6Ofd0xCYi4m.J./WWDzqaEvCGgMB/P2FsXtwJEMpriq', 'PR001', 'Production', '9000000000', 'production@sureshtimber.test', 5, 'Active', NULL, '2026-06-08 01:04:56', '2026-06-08 01:04:56', NULL),
(6, 1, 1, 'accounts', '$2y$12$laYgssO4rd831B4RdGgKmeY.bAvxs1v.G86pIxu60xCPfA2cTF9LS', 'AC001', 'Accounts', '9000000000', 'accounts@sureshtimber.test', 6, 'Active', NULL, '2026-06-08 01:04:58', '2026-06-08 01:04:58', NULL),
(7, 1, NULL, '1111111111', '$2y$12$YvVUjH0E1BBuxgriT1H7wOf0JbMokw9j2YdWnBUxRslJ/MRU/v/Zm', '1111111111', '1111111111', '3333333333', NULL, 6, 'Active', NULL, '2026-06-11 07:29:08', '2026-06-11 07:29:24', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_master`
--

CREATE TABLE `user_master` (
  `user_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED DEFAULT NULL,
  `branch_id` bigint(20) UNSIGNED DEFAULT NULL,
  `role_id` bigint(20) UNSIGNED DEFAULT NULL,
  `login_id` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `employee_code` varchar(50) DEFAULT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `mobile` varchar(20) DEFAULT NULL,
  `email` varchar(150) DEFAULT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `user_master`
--

INSERT INTO `user_master` (`user_id`, `tenant_id`, `branch_id`, `role_id`, `login_id`, `password`, `employee_code`, `full_name`, `mobile`, `email`, `status`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, NULL, 1, 'superadmin', '$2y$12$7CLKl2Q5SckISn447MgpRu42i0SpTnHTA96bzpbBdZ.WzctPMnALi', 'SA001', 'Super Admin', '9000000000', 'superadmin@sureshtimber.test', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(2, 1, 1, 2, 'admin', '$2y$12$7ddd3/4.HCXiyefINTA/sOLeUvqNsXuOAkqR/uKLE3/IvOPxg3Ud2', 'AD001', 'Admin', '9000000000', 'admin@sureshtimber.test', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(3, 1, 1, 3, 'manager', '$2y$12$Sra86M2ci2j4g0qPhfcK8.4gN/I2uazd5TW5tRB3UWxw1ktN9P3VG', 'MG001', 'Manager', '9000000000', 'manager@sureshtimber.test', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(4, 1, 2, 4, 'store', '$2y$12$evzTF4g6TkraEHtGjd5yQehJZx1hQGduKPgRCf8r9MWDmfqpTvYZa', 'ST001', 'Store', '9000000000', 'store@sureshtimber.test', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(5, 1, 1, 5, 'production', '$2y$12$ZGf9otG.y0ohXYRYklN.be07S6Ur9K7APId3mChCw3lZy7FVW9CT2', 'PR001', 'Production', '9000000000', 'production@sureshtimber.test', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL),
(6, 1, 1, 6, 'accounts', '$2y$12$va.GdN6aIHFugh5vxmibkuIKm0JEYIyTsLTMPqddkeLxrFAJS8rpO', 'AC001', 'Accounts', '9000000000', 'accounts@sureshtimber.test', 'Active', NULL, NULL, '2026-06-08 01:04:44', '2026-06-08 01:04:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `user_preferences`
--

CREATE TABLE `user_preferences` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `user_id` bigint(20) UNSIGNED DEFAULT NULL,
  `theme_color` varchar(30) NOT NULL DEFAULT 'blue',
  `sidebar_theme` varchar(30) NOT NULL DEFAULT 'dark',
  `header_theme` varchar(30) NOT NULL DEFAULT 'light',
  `dark_mode` varchar(30) NOT NULL DEFAULT 'light',
  `layout_mode` varchar(30) NOT NULL DEFAULT 'standard',
  `card_style` varchar(30) NOT NULL DEFAULT 'modern',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wastage_reuse_master`
--

CREATE TABLE `wastage_reuse_master` (
  `reuse_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `reuse_no` varchar(50) NOT NULL,
  `reuse_date` date NOT NULL,
  `source_wastage_stock_id` bigint(20) UNSIGNED NOT NULL,
  `source_item_id` bigint(20) UNSIGNED NOT NULL,
  `source_location_id` bigint(20) UNSIGNED NOT NULL,
  `consumed_qty` decimal(18,3) NOT NULL,
  `produced_item_id` bigint(20) UNSIGNED NOT NULL,
  `destination_location_id` bigint(20) UNSIGNED NOT NULL,
  `team_id` bigint(20) UNSIGNED DEFAULT NULL,
  `produced_qty` decimal(18,3) NOT NULL,
  `production_cost` decimal(18,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) NOT NULL DEFAULT 'Draft',
  `remarks` text DEFAULT NULL,
  `posted_at` timestamp NULL DEFAULT NULL,
  `posted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wastage_stock`
--

CREATE TABLE `wastage_stock` (
  `wastage_stock_id` bigint(20) UNSIGNED NOT NULL,
  `tenant_id` bigint(20) UNSIGNED NOT NULL,
  `branch_id` bigint(20) UNSIGNED NOT NULL,
  `item_id` bigint(20) UNSIGNED NOT NULL,
  `location_id` bigint(20) UNSIGNED DEFAULT NULL,
  `wastage_type` enum('Reusable','Non-Reusable','Scrap') NOT NULL,
  `source_module` varchar(50) NOT NULL DEFAULT 'Manual',
  `source_id` bigint(20) UNSIGNED DEFAULT NULL,
  `source_reference` varchar(100) DEFAULT NULL,
  `transaction_date` date DEFAULT NULL,
  `generated_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `available_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `used_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `balance_qty` decimal(18,3) NOT NULL DEFAULT 0.000,
  `status` varchar(20) NOT NULL DEFAULT 'Posted',
  `posted_at` timestamp NULL DEFAULT NULL,
  `posted_by` bigint(20) UNSIGNED DEFAULT NULL,
  `cancelled_at` timestamp NULL DEFAULT NULL,
  `cancelled_by` bigint(20) UNSIGNED DEFAULT NULL,
  `cancellation_reason` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_by` bigint(20) UNSIGNED DEFAULT NULL,
  `updated_by` bigint(20) UNSIGNED DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `wastage_stock`
--

INSERT INTO `wastage_stock` (`wastage_stock_id`, `tenant_id`, `branch_id`, `item_id`, `location_id`, `wastage_type`, `source_module`, `source_id`, `source_reference`, `transaction_date`, `generated_qty`, `available_qty`, `used_qty`, `balance_qty`, `status`, `posted_at`, `posted_by`, `cancelled_at`, `cancelled_by`, `cancellation_reason`, `remarks`, `created_by`, `updated_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
(1, 1, 1, 8, 4, 'Reusable', 'Manual', NULL, NULL, NULL, 1.100, 1.100, 0.000, 1.100, 'Posted', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-06-08 01:05:00', '2026-06-11 02:18:57', NULL),
(2, 1, 1, 8, 4, 'Reusable', 'Manual', 2, 'a', '2026-06-11', 1.000, 0.000, 0.000, 0.000, 'Draft', NULL, NULL, NULL, NULL, NULL, NULL, 2, 2, '2026-06-11 02:10:55', '2026-06-11 02:10:55', NULL),
(3, 1, 1, 9, 5, 'Reusable', 'Manual', 3, 'fg', '2026-06-11', 1.000, 0.000, 0.000, 0.000, 'Draft', NULL, NULL, NULL, NULL, NULL, 'fg', 2, 2, '2026-06-11 07:48:23', '2026-06-11 07:48:23', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`audit_id`),
  ADD KEY `audit_log_tenant_id_foreign` (`tenant_id`),
  ADD KEY `audit_log_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `bom_master`
--
ALTER TABLE `bom_master`
  ADD PRIMARY KEY (`bom_id`),
  ADD UNIQUE KEY `bom_tenant_branch_no_unique` (`tenant_id`,`branch_id`,`bom_no`),
  ADD UNIQUE KEY `bom_tenant_model_version_unique` (`tenant_id`,`branch_id`,`pallet_model_id`,`version_no`),
  ADD KEY `bom_master_branch_id_foreign` (`branch_id`),
  ADD KEY `bom_master_pallet_model_id_foreign` (`pallet_model_id`),
  ADD KEY `bom_master_finished_item_id_foreign` (`finished_item_id`);

--
-- Indexes for table `bom_material`
--
ALTER TABLE `bom_material`
  ADD PRIMARY KEY (`bom_material_id`),
  ADD KEY `bom_material_tenant_id_foreign` (`tenant_id`),
  ADD KEY `bom_material_branch_id_foreign` (`branch_id`),
  ADD KEY `bom_material_bom_id_foreign` (`bom_id`),
  ADD KEY `bom_material_item_id_foreign` (`item_id`),
  ADD KEY `bom_material_uom_id_foreign` (`uom_id`);

--
-- Indexes for table `branch_master`
--
ALTER TABLE `branch_master`
  ADD PRIMARY KEY (`branch_id`),
  ADD KEY `branch_master_tenant_id_foreign` (`tenant_id`);

--
-- Indexes for table `cache`
--
ALTER TABLE `cache`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `cache_locks`
--
ALTER TABLE `cache_locks`
  ADD PRIMARY KEY (`key`);

--
-- Indexes for table `challan_master`
--
ALTER TABLE `challan_master`
  ADD PRIMARY KEY (`challan_id`),
  ADD KEY `challan_master_tenant_id_foreign` (`tenant_id`),
  ADD KEY `challan_master_branch_id_foreign` (`branch_id`),
  ADD KEY `challan_master_customer_id_foreign` (`customer_id`);

--
-- Indexes for table `challan_team_detail`
--
ALTER TABLE `challan_team_detail`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `challan_team_detail_tenant_id_foreign` (`tenant_id`),
  ADD KEY `challan_team_detail_branch_id_foreign` (`branch_id`),
  ADD KEY `challan_team_detail_challan_id_foreign` (`challan_id`),
  ADD KEY `challan_team_detail_pallet_model_id_foreign` (`pallet_model_id`),
  ADD KEY `challan_team_detail_team_id_foreign` (`team_id`);

--
-- Indexes for table `erp_modules`
--
ALTER TABLE `erp_modules`
  ADD PRIMARY KEY (`module_id`),
  ADD UNIQUE KEY `erp_modules_module_code_unique` (`module_code`),
  ADD KEY `erp_modules_parent_module_id_foreign` (`parent_module_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `grn_detail`
--
ALTER TABLE `grn_detail`
  ADD PRIMARY KEY (`grn_detail_id`),
  ADD KEY `grn_detail_tenant_id_foreign` (`tenant_id`),
  ADD KEY `grn_detail_branch_id_foreign` (`branch_id`),
  ADD KEY `grn_detail_grn_id_foreign` (`grn_id`),
  ADD KEY `grn_detail_item_id_foreign` (`item_id`),
  ADD KEY `grn_detail_location_id_foreign` (`location_id`),
  ADD KEY `grn_detail_uom_id_foreign` (`uom_id`);

--
-- Indexes for table `grn_master`
--
ALTER TABLE `grn_master`
  ADD PRIMARY KEY (`grn_id`),
  ADD UNIQUE KEY `grn_tenant_branch_no_unique` (`tenant_id`,`branch_id`,`grn_no`),
  ADD KEY `grn_master_branch_id_foreign` (`branch_id`),
  ADD KEY `grn_master_supplier_id_foreign` (`supplier_id`),
  ADD KEY `grn_master_warehouse_location_id_foreign` (`warehouse_location_id`);

--
-- Indexes for table `item_master`
--
ALTER TABLE `item_master`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `item_master_tenant_id_foreign` (`tenant_id`),
  ADD KEY `item_master_branch_id_foreign` (`branch_id`),
  ADD KEY `item_master_material_type_id_foreign` (`material_type_id`),
  ADD KEY `item_master_uom_id_foreign` (`uom_id`);

--
-- Indexes for table `jobs`
--
ALTER TABLE `jobs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `jobs_queue_index` (`queue`);

--
-- Indexes for table `job_batches`
--
ALTER TABLE `job_batches`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `material_type_master`
--
ALTER TABLE `material_type_master`
  ADD PRIMARY KEY (`material_type_id`),
  ADD KEY `material_type_master_tenant_id_foreign` (`tenant_id`),
  ADD KEY `material_type_master_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pallet_model_master`
--
ALTER TABLE `pallet_model_master`
  ADD PRIMARY KEY (`pallet_model_id`),
  ADD KEY `pallet_model_master_tenant_id_foreign` (`tenant_id`),
  ADD KEY `pallet_model_master_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `party_master`
--
ALTER TABLE `party_master`
  ADD PRIMARY KEY (`party_id`),
  ADD KEY `party_master_tenant_id_foreign` (`tenant_id`),
  ADD KEY `party_master_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `password_reset_tokens`
--
ALTER TABLE `password_reset_tokens`
  ADD PRIMARY KEY (`login_id`);

--
-- Indexes for table `permissions`
--
ALTER TABLE `permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `permissions_name_unique` (`name`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  ADD KEY `personal_access_tokens_expires_at_index` (`expires_at`);

--
-- Indexes for table `production_consumption`
--
ALTER TABLE `production_consumption`
  ADD PRIMARY KEY (`consumption_id`),
  ADD KEY `production_consumption_tenant_id_foreign` (`tenant_id`),
  ADD KEY `production_consumption_branch_id_foreign` (`branch_id`),
  ADD KEY `production_consumption_production_id_foreign` (`production_id`),
  ADD KEY `production_consumption_item_id_foreign` (`item_id`),
  ADD KEY `production_consumption_uom_id_foreign` (`uom_id`),
  ADD KEY `production_consumption_location_id_foreign` (`location_id`);

--
-- Indexes for table `production_master`
--
ALTER TABLE `production_master`
  ADD PRIMARY KEY (`production_id`),
  ADD UNIQUE KEY `production_tenant_branch_no_unique` (`tenant_id`,`branch_id`,`production_no`),
  ADD KEY `production_master_branch_id_foreign` (`branch_id`),
  ADD KEY `production_master_pallet_model_id_foreign` (`pallet_model_id`),
  ADD KEY `production_master_team_id_foreign` (`team_id`),
  ADD KEY `production_master_bom_id_foreign` (`bom_id`),
  ADD KEY `production_master_produced_item_id_foreign` (`produced_item_id`),
  ADD KEY `production_master_fg_location_id_foreign` (`fg_location_id`);

--
-- Indexes for table `production_output`
--
ALTER TABLE `production_output`
  ADD PRIMARY KEY (`output_id`),
  ADD KEY `production_output_tenant_id_foreign` (`tenant_id`),
  ADD KEY `production_output_branch_id_foreign` (`branch_id`),
  ADD KEY `production_output_production_id_foreign` (`production_id`),
  ADD KEY `production_output_item_id_foreign` (`item_id`),
  ADD KEY `production_output_location_id_foreign` (`location_id`);

--
-- Indexes for table `production_wastage`
--
ALTER TABLE `production_wastage`
  ADD PRIMARY KEY (`wastage_id`),
  ADD KEY `production_wastage_tenant_id_foreign` (`tenant_id`),
  ADD KEY `production_wastage_branch_id_foreign` (`branch_id`),
  ADD KEY `production_wastage_production_id_foreign` (`production_id`),
  ADD KEY `production_wastage_item_id_foreign` (`item_id`),
  ADD KEY `production_wastage_location_id_foreign` (`location_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `roles_tenant_name_unique` (`tenant_id`,`name`),
  ADD KEY `roles_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `role_master`
--
ALTER TABLE `role_master`
  ADD PRIMARY KEY (`role_id`),
  ADD KEY `role_master_tenant_id_foreign` (`tenant_id`),
  ADD KEY `role_master_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `role_permissions_role_id_permission_id_unique` (`role_id`,`permission_id`),
  ADD KEY `role_permissions_permission_id_foreign` (`permission_id`);

--
-- Indexes for table `sessions`
--
ALTER TABLE `sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sessions_user_id_index` (`user_id`),
  ADD KEY `sessions_last_activity_index` (`last_activity`);

--
-- Indexes for table `state_master`
--
ALTER TABLE `state_master`
  ADD PRIMARY KEY (`state_id`),
  ADD UNIQUE KEY `state_master_tenant_id_state_name_unique` (`tenant_id`,`state_name`);

--
-- Indexes for table `stock_adjustment_detail`
--
ALTER TABLE `stock_adjustment_detail`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `stock_adjustment_detail_tenant_id_foreign` (`tenant_id`),
  ADD KEY `stock_adjustment_detail_branch_id_foreign` (`branch_id`),
  ADD KEY `stock_adjustment_detail_adjustment_id_foreign` (`adjustment_id`),
  ADD KEY `stock_adjustment_detail_item_id_foreign` (`item_id`),
  ADD KEY `stock_adjustment_detail_location_id_foreign` (`location_id`);

--
-- Indexes for table `stock_adjustment_master`
--
ALTER TABLE `stock_adjustment_master`
  ADD PRIMARY KEY (`adjustment_id`),
  ADD KEY `stock_adjustment_master_tenant_id_foreign` (`tenant_id`),
  ADD KEY `stock_adjustment_master_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `stock_ledger`
--
ALTER TABLE `stock_ledger`
  ADD PRIMARY KEY (`ledger_id`),
  ADD KEY `stock_ledger_tenant_id_foreign` (`tenant_id`),
  ADD KEY `stock_ledger_branch_id_foreign` (`branch_id`),
  ADD KEY `stock_ledger_item_id_foreign` (`item_id`),
  ADD KEY `stock_ledger_location_id_foreign` (`location_id`);

--
-- Indexes for table `stock_summary`
--
ALTER TABLE `stock_summary`
  ADD PRIMARY KEY (`stock_id`),
  ADD UNIQUE KEY `stock_summary_unique` (`tenant_id`,`branch_id`,`item_id`,`location_id`),
  ADD KEY `stock_summary_branch_id_foreign` (`branch_id`),
  ADD KEY `stock_summary_item_id_foreign` (`item_id`),
  ADD KEY `stock_summary_location_id_foreign` (`location_id`);

--
-- Indexes for table `stock_verification_detail`
--
ALTER TABLE `stock_verification_detail`
  ADD PRIMARY KEY (`detail_id`),
  ADD KEY `stock_verification_detail_tenant_id_foreign` (`tenant_id`),
  ADD KEY `stock_verification_detail_branch_id_foreign` (`branch_id`),
  ADD KEY `stock_verification_detail_verification_id_foreign` (`verification_id`),
  ADD KEY `stock_verification_detail_item_id_foreign` (`item_id`),
  ADD KEY `stock_verification_detail_location_id_foreign` (`location_id`),
  ADD KEY `stock_verification_detail_uom_id_foreign` (`uom_id`);

--
-- Indexes for table `stock_verification_master`
--
ALTER TABLE `stock_verification_master`
  ADD PRIMARY KEY (`verification_id`),
  ADD UNIQUE KEY `stock_verification_no_unique` (`tenant_id`,`branch_id`,`verification_no`),
  ADD KEY `stock_verification_master_branch_id_foreign` (`branch_id`),
  ADD KEY `stock_verification_master_location_id_foreign` (`location_id`);

--
-- Indexes for table `storage_location_master`
--
ALTER TABLE `storage_location_master`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `storage_location_master_tenant_id_foreign` (`tenant_id`),
  ADD KEY `storage_location_master_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `team_ledger`
--
ALTER TABLE `team_ledger`
  ADD PRIMARY KEY (`ledger_id`),
  ADD KEY `team_ledger_tenant_id_foreign` (`tenant_id`),
  ADD KEY `team_ledger_branch_id_foreign` (`branch_id`),
  ADD KEY `team_ledger_team_id_foreign` (`team_id`),
  ADD KEY `team_ledger_pallet_model_id_foreign` (`pallet_model_id`);

--
-- Indexes for table `team_master`
--
ALTER TABLE `team_master`
  ADD PRIMARY KEY (`team_id`),
  ADD KEY `team_master_tenant_id_foreign` (`tenant_id`),
  ADD KEY `team_master_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `team_payment_summary`
--
ALTER TABLE `team_payment_summary`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `team_payment_summary_tenant_id_foreign` (`tenant_id`),
  ADD KEY `team_payment_summary_branch_id_foreign` (`branch_id`),
  ADD KEY `team_payment_summary_team_id_foreign` (`team_id`);

--
-- Indexes for table `tenant_master`
--
ALTER TABLE `tenant_master`
  ADD PRIMARY KEY (`tenant_id`),
  ADD UNIQUE KEY `tenant_master_tenant_code_unique` (`tenant_code`);

--
-- Indexes for table `uom_master`
--
ALTER TABLE `uom_master`
  ADD PRIMARY KEY (`uom_id`),
  ADD KEY `uom_master_tenant_id_foreign` (`tenant_id`),
  ADD KEY `uom_master_branch_id_foreign` (`branch_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `users_tenant_login_unique` (`tenant_id`,`login_id`);

--
-- Indexes for table `user_master`
--
ALTER TABLE `user_master`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `user_master_tenant_login_unique` (`tenant_id`,`login_id`),
  ADD KEY `user_master_branch_id_foreign` (`branch_id`),
  ADD KEY `user_master_role_id_foreign` (`role_id`);

--
-- Indexes for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_preferences_user_id_unique` (`user_id`);

--
-- Indexes for table `wastage_reuse_master`
--
ALTER TABLE `wastage_reuse_master`
  ADD PRIMARY KEY (`reuse_id`),
  ADD UNIQUE KEY `wastage_reuse_tenant_branch_no_unique` (`tenant_id`,`branch_id`,`reuse_no`),
  ADD KEY `wastage_reuse_master_branch_id_foreign` (`branch_id`),
  ADD KEY `wastage_reuse_master_source_wastage_stock_id_foreign` (`source_wastage_stock_id`),
  ADD KEY `wastage_reuse_master_source_item_id_foreign` (`source_item_id`),
  ADD KEY `wastage_reuse_master_source_location_id_foreign` (`source_location_id`),
  ADD KEY `wastage_reuse_master_produced_item_id_foreign` (`produced_item_id`),
  ADD KEY `wastage_reuse_master_destination_location_id_foreign` (`destination_location_id`),
  ADD KEY `wastage_reuse_master_team_id_foreign` (`team_id`);

--
-- Indexes for table `wastage_stock`
--
ALTER TABLE `wastage_stock`
  ADD PRIMARY KEY (`wastage_stock_id`),
  ADD KEY `wastage_stock_tenant_id_foreign` (`tenant_id`),
  ADD KEY `wastage_stock_branch_id_foreign` (`branch_id`),
  ADD KEY `wastage_stock_item_id_foreign` (`item_id`),
  ADD KEY `wastage_stock_location_id_foreign` (`location_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `audit_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `bom_master`
--
ALTER TABLE `bom_master`
  MODIFY `bom_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `bom_material`
--
ALTER TABLE `bom_material`
  MODIFY `bom_material_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `branch_master`
--
ALTER TABLE `branch_master`
  MODIFY `branch_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `challan_master`
--
ALTER TABLE `challan_master`
  MODIFY `challan_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `challan_team_detail`
--
ALTER TABLE `challan_team_detail`
  MODIFY `detail_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `erp_modules`
--
ALTER TABLE `erp_modules`
  MODIFY `module_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `grn_detail`
--
ALTER TABLE `grn_detail`
  MODIFY `grn_detail_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `grn_master`
--
ALTER TABLE `grn_master`
  MODIFY `grn_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `item_master`
--
ALTER TABLE `item_master`
  MODIFY `item_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `jobs`
--
ALTER TABLE `jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `material_type_master`
--
ALTER TABLE `material_type_master`
  MODIFY `material_type_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `pallet_model_master`
--
ALTER TABLE `pallet_model_master`
  MODIFY `pallet_model_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `party_master`
--
ALTER TABLE `party_master`
  MODIFY `party_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `permissions`
--
ALTER TABLE `permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `production_consumption`
--
ALTER TABLE `production_consumption`
  MODIFY `consumption_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `production_master`
--
ALTER TABLE `production_master`
  MODIFY `production_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `production_output`
--
ALTER TABLE `production_output`
  MODIFY `output_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `production_wastage`
--
ALTER TABLE `production_wastage`
  MODIFY `wastage_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `role_master`
--
ALTER TABLE `role_master`
  MODIFY `role_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `role_permissions`
--
ALTER TABLE `role_permissions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=240;

--
-- AUTO_INCREMENT for table `state_master`
--
ALTER TABLE `state_master`
  MODIFY `state_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `stock_adjustment_detail`
--
ALTER TABLE `stock_adjustment_detail`
  MODIFY `detail_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_adjustment_master`
--
ALTER TABLE `stock_adjustment_master`
  MODIFY `adjustment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_ledger`
--
ALTER TABLE `stock_ledger`
  MODIFY `ledger_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `stock_summary`
--
ALTER TABLE `stock_summary`
  MODIFY `stock_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `stock_verification_detail`
--
ALTER TABLE `stock_verification_detail`
  MODIFY `detail_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `stock_verification_master`
--
ALTER TABLE `stock_verification_master`
  MODIFY `verification_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `storage_location_master`
--
ALTER TABLE `storage_location_master`
  MODIFY `location_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `team_ledger`
--
ALTER TABLE `team_ledger`
  MODIFY `ledger_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `team_master`
--
ALTER TABLE `team_master`
  MODIFY `team_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `team_payment_summary`
--
ALTER TABLE `team_payment_summary`
  MODIFY `payment_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tenant_master`
--
ALTER TABLE `tenant_master`
  MODIFY `tenant_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `uom_master`
--
ALTER TABLE `uom_master`
  MODIFY `uom_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `user_master`
--
ALTER TABLE `user_master`
  MODIFY `user_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `user_preferences`
--
ALTER TABLE `user_preferences`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wastage_reuse_master`
--
ALTER TABLE `wastage_reuse_master`
  MODIFY `reuse_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wastage_stock`
--
ALTER TABLE `wastage_stock`
  MODIFY `wastage_stock_id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `audit_log_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `bom_master`
--
ALTER TABLE `bom_master`
  ADD CONSTRAINT `bom_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bom_master_finished_item_id_foreign` FOREIGN KEY (`finished_item_id`) REFERENCES `item_master` (`item_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bom_master_pallet_model_id_foreign` FOREIGN KEY (`pallet_model_id`) REFERENCES `pallet_model_master` (`pallet_model_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bom_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `bom_material`
--
ALTER TABLE `bom_material`
  ADD CONSTRAINT `bom_material_bom_id_foreign` FOREIGN KEY (`bom_id`) REFERENCES `bom_master` (`bom_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bom_material_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `bom_material_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `item_master` (`item_id`),
  ADD CONSTRAINT `bom_material_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bom_material_uom_id_foreign` FOREIGN KEY (`uom_id`) REFERENCES `uom_master` (`uom_id`) ON DELETE SET NULL;

--
-- Constraints for table `branch_master`
--
ALTER TABLE `branch_master`
  ADD CONSTRAINT `branch_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `challan_master`
--
ALTER TABLE `challan_master`
  ADD CONSTRAINT `challan_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `challan_master_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `party_master` (`party_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `challan_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `challan_team_detail`
--
ALTER TABLE `challan_team_detail`
  ADD CONSTRAINT `challan_team_detail_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `challan_team_detail_challan_id_foreign` FOREIGN KEY (`challan_id`) REFERENCES `challan_master` (`challan_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `challan_team_detail_pallet_model_id_foreign` FOREIGN KEY (`pallet_model_id`) REFERENCES `pallet_model_master` (`pallet_model_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `challan_team_detail_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `team_master` (`team_id`),
  ADD CONSTRAINT `challan_team_detail_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `erp_modules`
--
ALTER TABLE `erp_modules`
  ADD CONSTRAINT `erp_modules_parent_module_id_foreign` FOREIGN KEY (`parent_module_id`) REFERENCES `erp_modules` (`module_id`) ON DELETE SET NULL;

--
-- Constraints for table `grn_detail`
--
ALTER TABLE `grn_detail`
  ADD CONSTRAINT `grn_detail_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grn_detail_grn_id_foreign` FOREIGN KEY (`grn_id`) REFERENCES `grn_master` (`grn_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grn_detail_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `item_master` (`item_id`),
  ADD CONSTRAINT `grn_detail_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `storage_location_master` (`location_id`),
  ADD CONSTRAINT `grn_detail_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grn_detail_uom_id_foreign` FOREIGN KEY (`uom_id`) REFERENCES `uom_master` (`uom_id`) ON DELETE SET NULL;

--
-- Constraints for table `grn_master`
--
ALTER TABLE `grn_master`
  ADD CONSTRAINT `grn_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grn_master_supplier_id_foreign` FOREIGN KEY (`supplier_id`) REFERENCES `party_master` (`party_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `grn_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `grn_master_warehouse_location_id_foreign` FOREIGN KEY (`warehouse_location_id`) REFERENCES `storage_location_master` (`location_id`) ON DELETE SET NULL;

--
-- Constraints for table `item_master`
--
ALTER TABLE `item_master`
  ADD CONSTRAINT `item_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `item_master_material_type_id_foreign` FOREIGN KEY (`material_type_id`) REFERENCES `material_type_master` (`material_type_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `item_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `item_master_uom_id_foreign` FOREIGN KEY (`uom_id`) REFERENCES `uom_master` (`uom_id`) ON DELETE SET NULL;

--
-- Constraints for table `material_type_master`
--
ALTER TABLE `material_type_master`
  ADD CONSTRAINT `material_type_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `material_type_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `pallet_model_master`
--
ALTER TABLE `pallet_model_master`
  ADD CONSTRAINT `pallet_model_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pallet_model_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `party_master`
--
ALTER TABLE `party_master`
  ADD CONSTRAINT `party_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `party_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `production_consumption`
--
ALTER TABLE `production_consumption`
  ADD CONSTRAINT `production_consumption_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_consumption_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `item_master` (`item_id`),
  ADD CONSTRAINT `production_consumption_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `storage_location_master` (`location_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `production_consumption_production_id_foreign` FOREIGN KEY (`production_id`) REFERENCES `production_master` (`production_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_consumption_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_consumption_uom_id_foreign` FOREIGN KEY (`uom_id`) REFERENCES `uom_master` (`uom_id`) ON DELETE SET NULL;

--
-- Constraints for table `production_master`
--
ALTER TABLE `production_master`
  ADD CONSTRAINT `production_master_bom_id_foreign` FOREIGN KEY (`bom_id`) REFERENCES `bom_master` (`bom_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `production_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_master_fg_location_id_foreign` FOREIGN KEY (`fg_location_id`) REFERENCES `storage_location_master` (`location_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `production_master_pallet_model_id_foreign` FOREIGN KEY (`pallet_model_id`) REFERENCES `pallet_model_master` (`pallet_model_id`),
  ADD CONSTRAINT `production_master_produced_item_id_foreign` FOREIGN KEY (`produced_item_id`) REFERENCES `item_master` (`item_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `production_master_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `team_master` (`team_id`),
  ADD CONSTRAINT `production_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `production_output`
--
ALTER TABLE `production_output`
  ADD CONSTRAINT `production_output_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_output_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `item_master` (`item_id`),
  ADD CONSTRAINT `production_output_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `storage_location_master` (`location_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `production_output_production_id_foreign` FOREIGN KEY (`production_id`) REFERENCES `production_master` (`production_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_output_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `production_wastage`
--
ALTER TABLE `production_wastage`
  ADD CONSTRAINT `production_wastage_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_wastage_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `item_master` (`item_id`),
  ADD CONSTRAINT `production_wastage_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `storage_location_master` (`location_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `production_wastage_production_id_foreign` FOREIGN KEY (`production_id`) REFERENCES `production_master` (`production_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `production_wastage_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `roles`
--
ALTER TABLE `roles`
  ADD CONSTRAINT `roles_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `roles_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE SET NULL;

--
-- Constraints for table `role_master`
--
ALTER TABLE `role_master`
  ADD CONSTRAINT `role_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `role_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE SET NULL;

--
-- Constraints for table `role_permissions`
--
ALTER TABLE `role_permissions`
  ADD CONSTRAINT `role_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `role_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `state_master`
--
ALTER TABLE `state_master`
  ADD CONSTRAINT `state_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_adjustment_detail`
--
ALTER TABLE `stock_adjustment_detail`
  ADD CONSTRAINT `stock_adjustment_detail_adjustment_id_foreign` FOREIGN KEY (`adjustment_id`) REFERENCES `stock_adjustment_master` (`adjustment_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_detail_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_detail_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `item_master` (`item_id`),
  ADD CONSTRAINT `stock_adjustment_detail_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `storage_location_master` (`location_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_adjustment_detail_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_adjustment_master`
--
ALTER TABLE `stock_adjustment_master`
  ADD CONSTRAINT `stock_adjustment_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_adjustment_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_ledger`
--
ALTER TABLE `stock_ledger`
  ADD CONSTRAINT `stock_ledger_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_ledger_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `item_master` (`item_id`),
  ADD CONSTRAINT `stock_ledger_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `storage_location_master` (`location_id`),
  ADD CONSTRAINT `stock_ledger_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_summary`
--
ALTER TABLE `stock_summary`
  ADD CONSTRAINT `stock_summary_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_summary_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `item_master` (`item_id`),
  ADD CONSTRAINT `stock_summary_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `storage_location_master` (`location_id`),
  ADD CONSTRAINT `stock_summary_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_verification_detail`
--
ALTER TABLE `stock_verification_detail`
  ADD CONSTRAINT `stock_verification_detail_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_verification_detail_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `item_master` (`item_id`),
  ADD CONSTRAINT `stock_verification_detail_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `storage_location_master` (`location_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_verification_detail_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_verification_detail_uom_id_foreign` FOREIGN KEY (`uom_id`) REFERENCES `uom_master` (`uom_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_verification_detail_verification_id_foreign` FOREIGN KEY (`verification_id`) REFERENCES `stock_verification_master` (`verification_id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_verification_master`
--
ALTER TABLE `stock_verification_master`
  ADD CONSTRAINT `stock_verification_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `stock_verification_master_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `storage_location_master` (`location_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `stock_verification_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `storage_location_master`
--
ALTER TABLE `storage_location_master`
  ADD CONSTRAINT `storage_location_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `storage_location_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `team_ledger`
--
ALTER TABLE `team_ledger`
  ADD CONSTRAINT `team_ledger_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_ledger_pallet_model_id_foreign` FOREIGN KEY (`pallet_model_id`) REFERENCES `pallet_model_master` (`pallet_model_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `team_ledger_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `team_master` (`team_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_ledger_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `team_master`
--
ALTER TABLE `team_master`
  ADD CONSTRAINT `team_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `team_payment_summary`
--
ALTER TABLE `team_payment_summary`
  ADD CONSTRAINT `team_payment_summary_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_payment_summary_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `team_master` (`team_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `team_payment_summary_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `uom_master`
--
ALTER TABLE `uom_master`
  ADD CONSTRAINT `uom_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `uom_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_master`
--
ALTER TABLE `user_master`
  ADD CONSTRAINT `user_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_master_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `role_master` (`role_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `user_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE SET NULL;

--
-- Constraints for table `user_preferences`
--
ALTER TABLE `user_preferences`
  ADD CONSTRAINT `user_preferences_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `wastage_reuse_master`
--
ALTER TABLE `wastage_reuse_master`
  ADD CONSTRAINT `wastage_reuse_master_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wastage_reuse_master_destination_location_id_foreign` FOREIGN KEY (`destination_location_id`) REFERENCES `storage_location_master` (`location_id`),
  ADD CONSTRAINT `wastage_reuse_master_produced_item_id_foreign` FOREIGN KEY (`produced_item_id`) REFERENCES `item_master` (`item_id`),
  ADD CONSTRAINT `wastage_reuse_master_source_item_id_foreign` FOREIGN KEY (`source_item_id`) REFERENCES `item_master` (`item_id`),
  ADD CONSTRAINT `wastage_reuse_master_source_location_id_foreign` FOREIGN KEY (`source_location_id`) REFERENCES `storage_location_master` (`location_id`),
  ADD CONSTRAINT `wastage_reuse_master_source_wastage_stock_id_foreign` FOREIGN KEY (`source_wastage_stock_id`) REFERENCES `wastage_stock` (`wastage_stock_id`),
  ADD CONSTRAINT `wastage_reuse_master_team_id_foreign` FOREIGN KEY (`team_id`) REFERENCES `team_master` (`team_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wastage_reuse_master_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;

--
-- Constraints for table `wastage_stock`
--
ALTER TABLE `wastage_stock`
  ADD CONSTRAINT `wastage_stock_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branch_master` (`branch_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `wastage_stock_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `item_master` (`item_id`),
  ADD CONSTRAINT `wastage_stock_location_id_foreign` FOREIGN KEY (`location_id`) REFERENCES `storage_location_master` (`location_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `wastage_stock_tenant_id_foreign` FOREIGN KEY (`tenant_id`) REFERENCES `tenant_master` (`tenant_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
