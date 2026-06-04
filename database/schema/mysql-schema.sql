/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
DROP TABLE IF EXISTS `activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level` tinyint unsigned NOT NULL DEFAULT '2' COMMENT '1=debug 2=info 3=warning 4=error 5=critical',
  `organization_id` bigint unsigned DEFAULT NULL COMMENT 'Tenant context — NULL khi CLI/system job',
  `module` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actor_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Snapshot tên actor tại thời điểm log',
  `actor_ip` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'IPv4 hoặc IPv6',
  `request_id` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Snapshot label của subject (name/title/email)',
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `event` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `causer_id` bigint unsigned DEFAULT NULL,
  `attribute_changes` json DEFAULT NULL,
  `properties` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subject` (`subject_type`,`subject_id`),
  KEY `causer` (`causer_type`,`causer_id`),
  KEY `activity_log_log_name_index` (`log_name`),
  KEY `idx_org_created` (`organization_id`,`created_at`),
  KEY `idx_level` (`level`,`created_at`),
  KEY `idx_module_action` (`module`,`action`,`created_at`),
  KEY `idx_request` (`request_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_log_alert_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log_alert_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `module` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `action` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `level_min` tinyint unsigned DEFAULT NULL,
  `condition_type` tinyint unsigned NOT NULL COMMENT '1=first_occurrence 2=count_threshold',
  `threshold_count` smallint unsigned DEFAULT NULL,
  `window_minutes` smallint unsigned DEFAULT NULL,
  `notify_channel` tinyint unsigned NOT NULL COMMENT '1=email 2=database',
  `notify_target` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cooldown_minutes` smallint unsigned NOT NULL DEFAULT '60',
  `last_triggered_at` datetime DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_active` (`is_active`,`module`,`action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_log_contexts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log_contexts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_id` bigint unsigned NOT NULL,
  `key_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value_type` tinyint unsigned NOT NULL DEFAULT '1' COMMENT '1=string 2=integer 3=decimal 4=boolean 5=datetime',
  `val_string` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `val_integer` bigint DEFAULT NULL,
  `val_decimal` decimal(20,6) DEFAULT NULL,
  `val_boolean` tinyint(1) DEFAULT NULL,
  `val_datetime` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_log` (`log_id`),
  KEY `idx_key_integer` (`key_name`,`val_integer`),
  KEY `idx_key_string` (`key_name`,`val_string`(64))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `activity_log_http`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `activity_log_http` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `log_id` bigint unsigned NOT NULL,
  `http_method` tinyint unsigned NOT NULL COMMENT '1=GET 2=POST 3=PUT 4=PATCH 5=DELETE 6=HEAD 7=OPTIONS',
  `url` varchar(2000) COLLATE utf8mb4_unicode_ci NOT NULL,
  `route_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status_code` smallint unsigned DEFAULT NULL,
  `duration_ms` smallint unsigned DEFAULT NULL,
  `user_agent` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_log` (`log_id`),
  KEY `idx_route` (`route_name`),
  KEY `idx_status` (`status_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessment_config_snapshots`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessment_config_snapshots` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assessment_code` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` int unsigned NOT NULL,
  `has_scoring` tinyint(1) NOT NULL DEFAULT '0',
  `aggregation_model` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `classification_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `passing_score` decimal(5,2) DEFAULT NULL,
  `label_pass` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `label_fail` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `change_note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assessment_config_snapshots_assessment_code_version_unique` (`assessment_code`,`version`),
  KEY `assessment_config_snapshots_assessment_code_index` (`assessment_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessment_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessment_domains` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK logic tới surveys.assessment_code',
  `domain_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. workflow, sales',
  `label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. Quy trình & Vận hành',
  `weight` decimal(5,4) NOT NULL COMMENT 'Trọng số 0.0000–1.0000, tổng = 1.0000',
  `min_score` int NOT NULL COMMENT 'Raw score thấp nhất lý thuyết',
  `max_score` int NOT NULL COMMENT 'Raw score cao nhất lý thuyết',
  `sort_order` tinyint NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assessment_domains_assessment_code_domain_code_unique` (`assessment_code`,`domain_code`),
  KEY `assessment_domains_assessment_code_index` (`assessment_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessment_results`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessment_results` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subject_type` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FQCN của model subject',
  `subject_id` bigint unsigned NOT NULL COMMENT 'PK của subject model',
  `overall_score` decimal(5,2) DEFAULT NULL,
  `maturity_level` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'band_code hoặc persona_code',
  `assessment_code` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `public_token` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Token cho phép xem kết quả công khai không cần đăng nhập',
  `weight_version` smallint unsigned NOT NULL DEFAULT '1',
  `calculated_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ar_subject` (`subject_type`,`subject_id`),
  UNIQUE KEY `assessment_results_public_token_unique` (`public_token`),
  KEY `idx_ar_code` (`assessment_code`),
  KEY `idx_ar_code_band` (`assessment_code`,`maturity_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `assessments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `assessments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK logic tới surveys.assessment_code',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1.0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `has_scoring` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'FALSE → bỏ qua toàn bộ engine, chỉ lưu câu trả lời',
  `aggregation_model` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'flat_sum' COMMENT 'flat_sum | weighted_domain | sectioned',
  `classification_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none | score_band | pass_fail | persona_match',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `assessments_assessment_code_unique` (`assessment_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `branches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `branches` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '/',
  `depth` tinyint unsigned NOT NULL DEFAULT '0',
  `manager_id` bigint unsigned DEFAULT NULL,
  `order_column` int unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'branch',
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `tax_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fax` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province_code` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward_code` char(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lat` decimal(10,7) DEFAULT NULL,
  `lng` decimal(10,7) DEFAULT NULL,
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opened_at` date DEFAULT NULL,
  `closed_at` date DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_branch_code` (`organization_id`,`code`),
  UNIQUE KEY `branches_uuid_unique` (`uuid`),
  KEY `branches_created_by_foreign` (`created_by`),
  KEY `branches_updated_by_foreign` (`updated_by`),
  KEY `idx_branches_org_status` (`organization_id`,`status`),
  KEY `idx_branches_org_type` (`organization_id`,`type`),
  KEY `idx_branches_org_path` (`organization_id`,`path`),
  KEY `idx_branches_parent` (`parent_id`),
  KEY `branches_province_code_foreign` (`province_code`),
  KEY `branches_ward_code_foreign` (`ward_code`),
  KEY `idx_branches_manager` (`manager_id`),
  CONSTRAINT `branches_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `branches_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `branches_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `branches_province_code_foreign` FOREIGN KEY (`province_code`) REFERENCES `provinces` (`province_code`) ON DELETE SET NULL,
  CONSTRAINT `branches_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `branches_ward_code_foreign` FOREIGN KEY (`ward_code`) REFERENCES `wards` (`ward_code`) ON DELETE SET NULL,
  CONSTRAINT `fk_branches_manager` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_branch_depth` CHECK (((`depth` >= 0) and (`depth` <= 2))),
  CONSTRAINT `chk_branch_status` CHECK ((`status` in (_utf8mb4'active',_utf8mb4'inactive',_utf8mb4'closed'))),
  CONSTRAINT `chk_branch_type` CHECK ((`type` in (_utf8mb4'headquarters',_utf8mb4'regional_office',_utf8mb4'branch',_utf8mb4'store',_utf8mb4'warehouse')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` bigint NOT NULL,
  PRIMARY KEY (`key`),
  KEY `cache_locks_expiration_index` (`expiration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `path` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '/',
  `depth` tinyint unsigned NOT NULL DEFAULT '0',
  `head_id` bigint unsigned DEFAULT NULL,
  `deputy_head_id` bigint unsigned DEFAULT NULL,
  `order_column` int unsigned NOT NULL DEFAULT '0',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `function` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `merged_into_id` bigint unsigned DEFAULT NULL,
  `budget_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `headcount_limit` smallint unsigned DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `internal_phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `internal_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `effective_from` date DEFAULT NULL,
  `effective_to` date DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_dept_code` (`organization_id`,`code`),
  UNIQUE KEY `departments_uuid_unique` (`uuid`),
  KEY `departments_created_by_foreign` (`created_by`),
  KEY `departments_updated_by_foreign` (`updated_by`),
  KEY `idx_depts_org_status` (`organization_id`,`status`),
  KEY `idx_depts_branch` (`branch_id`,`status`),
  KEY `idx_depts_org_path` (`organization_id`,`path`),
  KEY `idx_depts_parent` (`parent_id`),
  KEY `idx_depts_merged` (`merged_into_id`),
  KEY `fk_departments_deputy` (`deputy_head_id`),
  KEY `idx_depts_head` (`head_id`),
  CONSTRAINT `departments_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `departments_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `departments_merged_into_id_foreign` FOREIGN KEY (`merged_into_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `departments_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `departments_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `departments_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_departments_deputy` FOREIGN KEY (`deputy_head_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_departments_head` FOREIGN KEY (`head_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_dept_depth` CHECK (((`depth` >= 0) and (`depth` <= 2))),
  CONSTRAINT `chk_dept_func` CHECK (((`function` is null) or (`function` in (_utf8mb4'sales',_utf8mb4'marketing',_utf8mb4'finance',_utf8mb4'hr',_utf8mb4'it',_utf8mb4'operations',_utf8mb4'customer_service',_utf8mb4'legal',_utf8mb4'rd',_utf8mb4'other')))),
  CONSTRAINT `chk_dept_merged` CHECK (((`merged_into_id` is not null) or (`status` <> _utf8mb4'merged'))),
  CONSTRAINT `chk_dept_status` CHECK ((`status` in (_utf8mb4'active',_utf8mb4'inactive',_utf8mb4'merged')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employee_departments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_departments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `primary_lock` bigint unsigned GENERATED ALWAYS AS (if(((`is_primary` = 1) and (`left_at` is null)),`employee_id`,NULL)) VIRTUAL,
  `role_in_dept` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `joined_at` date DEFAULT NULL,
  `left_at` date DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_emp_dept_active` (`employee_id`,`department_id`,`left_at`),
  UNIQUE KEY `uq_primary_active` (`primary_lock`),
  KEY `idx_emp_depts_emp` (`employee_id`,`left_at`),
  KEY `idx_emp_depts_dept` (`department_id`,`is_primary`),
  CONSTRAINT `fk_emp_dept_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_emp_dept_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_emp_dept_role` CHECK (((`role_in_dept` is null) or (`role_in_dept` in (_utf8mb4'contributor',_utf8mb4'reviewer',_utf8mb4'lead',_utf8mb4'coordinator'))))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employee_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employee_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `changed_by` bigint unsigned DEFAULT NULL,
  `change_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `old_branch_id` bigint unsigned DEFAULT NULL,
  `new_branch_id` bigint unsigned DEFAULT NULL,
  `old_department_id` bigint unsigned DEFAULT NULL,
  `new_department_id` bigint unsigned DEFAULT NULL,
  `old_job_title_id` bigint unsigned DEFAULT NULL,
  `new_job_title_id` bigint unsigned DEFAULT NULL,
  `old_manager_id` bigint unsigned DEFAULT NULL,
  `new_manager_id` bigint unsigned DEFAULT NULL,
  `old_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_status` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `old_employment_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `new_employment_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `effective_date` date NOT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `employee_history_changed_by_foreign` (`changed_by`),
  KEY `idx_emp_hist_org` (`organization_id`,`change_type`,`effective_date`),
  KEY `idx_emp_hist_employee` (`employee_id`,`effective_date`),
  KEY `idx_emp_hist_new_dept` (`new_department_id`,`effective_date`),
  KEY `idx_emp_hist_new_branch` (`new_branch_id`,`effective_date`),
  KEY `fk_eh_old_branch` (`old_branch_id`),
  KEY `fk_eh_old_dept` (`old_department_id`),
  KEY `fk_eh_old_title` (`old_job_title_id`),
  KEY `fk_eh_new_title` (`new_job_title_id`),
  KEY `fk_eh_old_mgr` (`old_manager_id`),
  KEY `fk_eh_new_mgr` (`new_manager_id`),
  CONSTRAINT `employee_history_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employee_history_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `employee_history_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_eh_new_branch` FOREIGN KEY (`new_branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_eh_new_dept` FOREIGN KEY (`new_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_eh_new_mgr` FOREIGN KEY (`new_manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_eh_new_title` FOREIGN KEY (`new_job_title_id`) REFERENCES `job_titles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_eh_old_branch` FOREIGN KEY (`old_branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_eh_old_dept` FOREIGN KEY (`old_department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_eh_old_mgr` FOREIGN KEY (`old_manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_eh_old_title` FOREIGN KEY (`old_job_title_id`) REFERENCES `job_titles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_eh_change_type` CHECK ((`change_type` in (_utf8mb4'hire',_utf8mb4'branch_transfer',_utf8mb4'dept_transfer',_utf8mb4'promotion',_utf8mb4'demotion',_utf8mb4'manager_change',_utf8mb4'leave',_utf8mb4'return_from_leave',_utf8mb4'resign',_utf8mb4'terminate')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `employees`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `employees` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned NOT NULL,
  `department_id` bigint unsigned NOT NULL,
  `job_title_id` bigint unsigned DEFAULT NULL,
  `manager_id` bigint unsigned DEFAULT NULL,
  `employee_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `full_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gender` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `national_id` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tax_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `locale` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `avatar_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `employment_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'full_time',
  `hired_at` date DEFAULT NULL,
  `left_at` date DEFAULT NULL,
  `snap_branch_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snap_dept_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snap_job_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snap_job_level` tinyint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_employee_code` (`organization_id`,`employee_code`),
  UNIQUE KEY `uq_employee_email` (`organization_id`,`email`),
  UNIQUE KEY `employees_uuid_unique` (`uuid`),
  UNIQUE KEY `uq_employee_user` (`organization_id`,`user_id`),
  KEY `employees_created_by_foreign` (`created_by`),
  KEY `employees_updated_by_foreign` (`updated_by`),
  KEY `idx_employees_org_status` (`organization_id`,`status`),
  KEY `idx_employees_branch` (`branch_id`,`status`),
  KEY `idx_employees_dept` (`department_id`,`status`),
  KEY `idx_employees_manager` (`manager_id`),
  KEY `idx_employees_user` (`user_id`),
  KEY `idx_employees_job_title` (`job_title_id`),
  KEY `idx_employees_hired_at` (`hired_at`),
  CONSTRAINT `employees_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `employees_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `employees_job_title_id_foreign` FOREIGN KEY (`job_title_id`) REFERENCES `job_titles` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `employees_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `employees_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_employees_manager` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_employee_gender` CHECK (((`gender` is null) or (`gender` in (_utf8mb4'male',_utf8mb4'female',_utf8mb4'other')))),
  CONSTRAINT `chk_employee_status` CHECK ((`status` in (_utf8mb4'active',_utf8mb4'on_leave',_utf8mb4'resigned',_utf8mb4'terminated'))),
  CONSTRAINT `chk_employee_type` CHECK ((`employment_type` in (_utf8mb4'full_time',_utf8mb4'part_time',_utf8mb4'contract',_utf8mb4'intern')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `features`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `features` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `plan_id` bigint unsigned NOT NULL,
  `name` json NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` json DEFAULT NULL,
  `value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `resettable_period` smallint unsigned NOT NULL DEFAULT '0',
  `resettable_interval` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'month',
  `sort_order` mediumint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `features_plan_id_slug_unique` (`plan_id`,`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `job_titles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_titles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_id` bigint unsigned NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'staff',
  `level` tinyint unsigned NOT NULL DEFAULT '1',
  `description` text COLLATE utf8mb4_unicode_ci,
  `is_system` tinyint NOT NULL DEFAULT '0',
  `is_locked` tinyint NOT NULL DEFAULT '0',
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_job_title_code` (`organization_id`,`code`),
  UNIQUE KEY `job_titles_uuid_unique` (`uuid`),
  KEY `idx_job_titles_org` (`organization_id`,`is_active`),
  KEY `idx_job_titles_level` (`organization_id`,`level`),
  CONSTRAINT `job_titles_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_job_title_category` CHECK ((`category` in (_utf8mb4'executive',_utf8mb4'manager',_utf8mb4'supervisor',_utf8mb4'staff',_utf8mb4'intern',_utf8mb4'consultant'))),
  CONSTRAINT `chk_job_title_level` CHECK ((`level` between 1 and 20))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` smallint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kc_access_controls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kc_access_controls` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `target_type` enum('user','role','dept') COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_id` bigint unsigned NOT NULL,
  `permission` enum('view','edit','manage') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'view',
  `granted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `granted_by` bigint unsigned NOT NULL,
  `expired_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kc_access` (`item_id`,`target_type`,`target_id`),
  UNIQUE KEY `kc_access_controls_uuid_unique` (`uuid`),
  KEY `kc_access_controls_granted_by_foreign` (`granted_by`),
  KEY `idx_kc_access_target` (`target_type`,`target_id`),
  KEY `idx_kc_access_item` (`item_id`),
  KEY `kc_access_controls_target_type_index` (`target_type`),
  KEY `kc_access_controls_target_id_index` (`target_id`),
  CONSTRAINT `kc_access_controls_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `kc_access_controls_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `kc_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kc_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kc_categories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_id` bigint unsigned NOT NULL,
  `parent_id` bigint unsigned DEFAULT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `icon` varchar(80) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color_hex` char(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kc_cat_org_slug` (`organization_id`,`slug`),
  UNIQUE KEY `kc_categories_uuid_unique` (`uuid`),
  KEY `kc_categories_parent_id_foreign` (`parent_id`),
  KEY `kc_categories_created_by_foreign` (`created_by`),
  KEY `kc_categories_updated_by_foreign` (`updated_by`),
  KEY `idx_kc_cat_sort` (`organization_id`,`parent_id`,`sort_order`),
  KEY `idx_kc_cat_active` (`organization_id`,`is_active`),
  CONSTRAINT `kc_categories_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `kc_categories_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `kc_categories_parent_id_foreign` FOREIGN KEY (`parent_id`) REFERENCES `kc_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `kc_categories_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kc_feedbacks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kc_feedbacks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `rating` smallint DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `is_helpful` tinyint(1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kc_feedback_user` (`item_id`,`user_id`),
  UNIQUE KEY `kc_feedbacks_uuid_unique` (`uuid`),
  KEY `kc_feedbacks_user_id_foreign` (`user_id`),
  KEY `idx_kc_feedback_item` (`item_id`),
  CONSTRAINT `kc_feedbacks_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `kc_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kc_feedbacks_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kc_item_attachments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kc_item_attachments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_url` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_type` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_size_kb` int unsigned NOT NULL,
  `storage_provider` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'local',
  `storage_key` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` int NOT NULL DEFAULT '0',
  `uploaded_by` bigint unsigned NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kc_item_attachments_uuid_unique` (`uuid`),
  KEY `kc_item_attachments_uploaded_by_foreign` (`uploaded_by`),
  KEY `idx_kc_attach_sort` (`item_id`,`sort_order`),
  CONSTRAINT `kc_item_attachments_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `kc_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kc_item_attachments_uploaded_by_foreign` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kc_item_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kc_item_tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `tag_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kc_item_tag` (`item_id`,`tag_id`),
  UNIQUE KEY `kc_item_tags_uuid_unique` (`uuid`),
  KEY `idx_kc_item_tag_tag` (`tag_id`),
  CONSTRAINT `kc_item_tags_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `kc_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kc_item_tags_tag_id_foreign` FOREIGN KEY (`tag_id`) REFERENCES `kc_tags` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kc_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kc_items` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `category_id` bigint unsigned NOT NULL,
  `organization_id` bigint unsigned NOT NULL,
  `title` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(320) COLLATE utf8mb4_unicode_ci NOT NULL,
  `summary` text COLLATE utf8mb4_unicode_ci,
  `content` longtext COLLATE utf8mb4_unicode_ci,
  `type` enum('document','sop','video','form','faq','case_study','policy') COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('draft','pending_review','approved','rejected','archived') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `visibility` enum('public','internal','restricted','private') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal',
  `language` char(5) COLLATE utf8mb4_unicode_ci DEFAULT 'vi',
  `view_count` int unsigned NOT NULL DEFAULT '0',
  `download_count` int unsigned NOT NULL DEFAULT '0',
  `is_featured` tinyint(1) NOT NULL DEFAULT '0',
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `owner_id` bigint unsigned NOT NULL,
  `approved_by` bigint unsigned DEFAULT NULL,
  `approved_at` timestamp NULL DEFAULT NULL,
  `version` int unsigned NOT NULL DEFAULT '1',
  `effective_date` timestamp NULL DEFAULT NULL,
  `expired_date` timestamp NULL DEFAULT NULL,
  `created_by` bigint unsigned NOT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kc_item_org_slug` (`organization_id`,`slug`),
  UNIQUE KEY `kc_items_uuid_unique` (`uuid`),
  KEY `kc_items_category_id_foreign` (`category_id`),
  KEY `kc_items_owner_id_foreign` (`owner_id`),
  KEY `kc_items_approved_by_foreign` (`approved_by`),
  KEY `kc_items_created_by_foreign` (`created_by`),
  KEY `kc_items_updated_by_foreign` (`updated_by`),
  KEY `idx_kc_item_org_cat` (`organization_id`,`category_id`),
  KEY `idx_kc_item_homepage` (`organization_id`,`status`,`is_featured`),
  KEY `idx_kc_item_expiry` (`organization_id`,`expired_date`,`status`),
  KEY `kc_items_type_index` (`type`),
  KEY `kc_items_status_index` (`status`),
  KEY `kc_items_expired_date_index` (`expired_date`),
  CONSTRAINT `kc_items_approved_by_foreign` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `kc_items_category_id_foreign` FOREIGN KEY (`category_id`) REFERENCES `kc_categories` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `kc_items_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `kc_items_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `kc_items_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `kc_items_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kc_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kc_tags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_id` bigint unsigned NOT NULL,
  `name` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(90) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color_hex` char(7) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kc_tag_org_slug` (`organization_id`,`slug`),
  UNIQUE KEY `kc_tags_uuid_unique` (`uuid`),
  KEY `idx_kc_tag_org` (`organization_id`),
  CONSTRAINT `kc_tags_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kc_version_histories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kc_version_histories` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `version_number` int unsigned NOT NULL,
  `title_snapshot` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `content_snapshot` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `change_summary` text COLLATE utf8mb4_unicode_ci,
  `changed_by` bigint unsigned NOT NULL,
  `changed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_kc_ver_item_version` (`item_id`,`version_number`),
  UNIQUE KEY `kc_version_histories_uuid_unique` (`uuid`),
  KEY `kc_version_histories_changed_by_foreign` (`changed_by`),
  KEY `idx_kc_ver_item` (`item_id`),
  CONSTRAINT `kc_version_histories_changed_by_foreign` FOREIGN KEY (`changed_by`) REFERENCES `users` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `kc_version_histories_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `kc_items` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `kc_view_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kc_view_logs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `item_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `viewed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `kc_view_logs_uuid_unique` (`uuid`),
  KEY `idx_kc_viewlog_item` (`item_id`,`viewed_at`),
  KEY `idx_kc_viewlog_user` (`user_id`,`viewed_at`),
  KEY `kc_view_logs_viewed_at_index` (`viewed_at`),
  CONSTRAINT `kc_view_logs_item_id_foreign` FOREIGN KEY (`item_id`) REFERENCES `kc_items` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kc_view_logs_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lead_activities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_activities` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint unsigned NOT NULL,
  `organization_id` int unsigned NOT NULL,
  `type` tinyint unsigned NOT NULL,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `outcome` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `scheduled_at` datetime DEFAULT NULL,
  `completed_at` datetime DEFAULT NULL,
  `duration_minutes` smallint unsigned DEFAULT NULL,
  `attendee_count` tinyint unsigned DEFAULT NULL,
  `actor_id` bigint unsigned DEFAULT NULL,
  `actor_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_activity_lead` (`lead_id`,`created_at`),
  KEY `idx_activity_org_type` (`organization_id`,`type`,`created_at`),
  KEY `idx_activity_scheduled` (`scheduled_at`,`completed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lead_contacts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_contacts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int unsigned NOT NULL,
  `full_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone_alt` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `company` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `job_title` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward_code` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ward_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district_code` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `district_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province_code` varchar(8) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `province_name` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country_code` char(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VN',
  `dedup_hash` char(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lead_count` smallint unsigned NOT NULL DEFAULT '0',
  `created_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_contact_org_dedup` (`organization_id`,`dedup_hash`),
  KEY `idx_contact_email` (`organization_id`,`email`),
  KEY `idx_contact_phone` (`organization_id`,`phone`),
  KEY `idx_contact_full_name` (`organization_id`,`full_name`),
  KEY `idx_contact_company` (`organization_id`,`company`(32)),
  KEY `idx_contact_province` (`organization_id`,`province_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lead_meta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_meta` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint unsigned NOT NULL,
  `key_name` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value_type` tinyint unsigned NOT NULL DEFAULT '1',
  `val_string` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `val_integer` bigint DEFAULT NULL,
  `val_decimal` decimal(20,6) DEFAULT NULL,
  `val_boolean` tinyint(1) DEFAULT NULL,
  `val_datetime` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_meta_lead_key` (`lead_id`,`key_name`),
  KEY `idx_meta_key_string` (`key_name`,`val_string`(64)),
  KEY `idx_meta_key_integer` (`key_name`,`val_integer`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lead_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_notes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint unsigned NOT NULL,
  `organization_id` int unsigned NOT NULL,
  `content` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_pinned` tinyint(1) NOT NULL DEFAULT '0',
  `author_id` bigint unsigned DEFAULT NULL,
  `author_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_note_lead` (`lead_id`,`is_pinned`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lead_pipeline_stages`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_pipeline_stages` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int unsigned DEFAULT NULL,
  `is_global` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gray',
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `is_won` tinyint(1) NOT NULL DEFAULT '0',
  `is_lost` tinyint(1) NOT NULL DEFAULT '0',
  `probability` tinyint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stage_org_code` (`organization_id`,`code`),
  KEY `idx_stage_org_order` (`organization_id`,`sort_order`,`is_active`),
  KEY `idx_stage_global_order` (`is_global`,`sort_order`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lead_sources`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_sources` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int unsigned DEFAULT NULL,
  `is_global` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `icon` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `color` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gray',
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_source_org_code` (`organization_id`,`code`),
  KEY `idx_source_org_order` (`organization_id`,`sort_order`,`is_active`),
  KEY `idx_source_global_order` (`is_global`,`sort_order`,`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lead_stage_history`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_stage_history` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `lead_id` bigint unsigned NOT NULL,
  `organization_id` int unsigned NOT NULL,
  `stage_from_id` smallint unsigned DEFAULT NULL,
  `stage_to_id` smallint unsigned NOT NULL,
  `stage_from_label` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stage_to_label` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `changed_by` bigint unsigned DEFAULT NULL,
  `changed_by_name` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `changed_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_stage_history_lead` (`lead_id`,`changed_at`),
  KEY `idx_stage_history_org` (`organization_id`,`changed_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lead_tag_definitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_tag_definitions` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int unsigned NOT NULL,
  `name` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `color` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'gray',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_tag_org_name` (`organization_id`,`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `lead_tag_map`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `lead_tag_map` (
  `lead_id` bigint unsigned NOT NULL,
  `tag_id` smallint unsigned NOT NULL,
  PRIMARY KEY (`lead_id`,`tag_id`),
  KEY `idx_tag_map_tag` (`tag_id`,`lead_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `leads`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leads` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` int unsigned NOT NULL,
  `contact_id` bigint unsigned NOT NULL,
  `contact_name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contact_phone` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contact_company` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stage_id` smallint unsigned NOT NULL,
  `stage_changed_at` datetime DEFAULT NULL,
  `source_id` smallint unsigned DEFAULT NULL,
  `source_detail` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `assigned_to` bigint unsigned DEFAULT NULL,
  `assigned_at` datetime DEFAULT NULL,
  `expected_value` decimal(15,2) DEFAULT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `expected_close_date` date DEFAULT NULL,
  `actual_close_date` date DEFAULT NULL,
  `actual_value` decimal(15,2) DEFAULT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `survey_response_id` bigint unsigned DEFAULT NULL,
  `survey_band_code` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `survey_score` decimal(5,2) DEFAULT NULL,
  `lead_score` tinyint unsigned NOT NULL DEFAULT '0',
  `score_updated_at` datetime DEFAULT NULL,
  `status` tinyint unsigned NOT NULL DEFAULT '1',
  `last_activity_at` datetime DEFAULT NULL,
  `activity_count` int unsigned NOT NULL DEFAULT '0',
  `idempotent_key` char(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_lead_idempotent` (`idempotent_key`),
  KEY `idx_lead_list_view` (`organization_id`,`status`,`stage_id`,`updated_at`),
  KEY `idx_lead_kanban` (`organization_id`,`stage_id`,`status`,`lead_score`),
  KEY `idx_lead_my_leads` (`assigned_to`,`organization_id`,`status`,`stage_id`),
  KEY `idx_lead_closing_soon` (`organization_id`,`expected_close_date`,`status`),
  KEY `idx_lead_stale` (`organization_id`,`last_activity_at`,`status`),
  KEY `idx_lead_hot` (`organization_id`,`lead_score`,`status`),
  KEY `idx_lead_source` (`organization_id`,`source_id`,`created_at`),
  KEY `idx_lead_survey` (`survey_response_id`),
  KEY `idx_lead_contact` (`contact_id`),
  KEY `idx_lead_value` (`organization_id`,`expected_value`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `maturity_levels`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `maturity_levels` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `level_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. DIGITAL_FOUNDATION',
  `label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. Nền tảng số cơ bản',
  `description` text COLLATE utf8mb4_unicode_ci,
  `min_score` decimal(5,2) NOT NULL COMMENT 'Overall score tối thiểu để đạt level này',
  `max_score` decimal(5,2) NOT NULL COMMENT 'Overall score tối đa',
  `sort_order` tinyint NOT NULL DEFAULT '0',
  `lead_temperature` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cold' COMMENT 'hot | warm | cold — dùng để classify lead',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `maturity_levels_assessment_code_level_code_unique` (`assessment_code`,`level_code`),
  KEY `maturity_levels_assessment_code_index` (`assessment_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `media`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `media` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `collection_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `file_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `mime_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `disk` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `conversions_disk` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size` bigint unsigned NOT NULL,
  `manipulations` json NOT NULL,
  `custom_properties` json NOT NULL,
  `generated_conversions` json NOT NULL,
  `responsive_images` json NOT NULL,
  `order_column` int unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `media_uuid_unique` (`uuid`),
  KEY `media_model_type_model_id_index` (`model_type`,`model_id`),
  KEY `media_order_column_index` (`order_column`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `organization_id` bigint unsigned DEFAULT NULL,
  UNIQUE KEY `model_has_permissions_permission_model_type_primary` (`organization_id`,`permission_id`,`model_id`,`model_type`),
  KEY `model_has_permissions_model_id_model_type_index` (`model_id`,`model_type`),
  KEY `model_has_permissions_permission_id_foreign` (`permission_id`),
  KEY `model_has_permissions_team_foreign_key_index` (`organization_id`),
  CONSTRAINT `model_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `model_has_roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `model_has_roles` (
  `role_id` bigint unsigned NOT NULL,
  `model_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model_id` bigint unsigned NOT NULL,
  `organization_id` bigint unsigned DEFAULT NULL,
  UNIQUE KEY `model_has_roles_role_model_type_primary` (`organization_id`,`role_id`,`model_id`,`model_type`),
  KEY `model_has_roles_model_id_model_type_index` (`model_id`,`model_type`),
  KEY `model_has_roles_role_id_foreign` (`role_id`),
  KEY `model_has_roles_team_foreign_key_index` (`organization_id`),
  CONSTRAINT `model_has_roles_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `org_chart_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `org_chart_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `view_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tree',
  `group_by` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'department',
  `scope_branch_id` bigint unsigned DEFAULT NULL,
  `show_avatar` tinyint NOT NULL DEFAULT '1',
  `show_job_title` tinyint NOT NULL DEFAULT '1',
  `show_employee_code` tinyint NOT NULL DEFAULT '0',
  `show_department` tinyint NOT NULL DEFAULT '1',
  `show_branch` tinyint NOT NULL DEFAULT '0',
  `max_depth` tinyint unsigned NOT NULL DEFAULT '5',
  `expand_by_default` tinyint NOT NULL DEFAULT '0',
  `is_default` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `default_lock` bigint unsigned GENERATED ALWAYS AS (if((`is_default` = 1),`organization_id`,NULL)) VIRTUAL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_default_config` (`default_lock`),
  KEY `org_chart_configs_organization_id_foreign` (`organization_id`),
  KEY `org_chart_configs_created_by_foreign` (`created_by`),
  KEY `org_chart_configs_scope_branch_id_foreign` (`scope_branch_id`),
  CONSTRAINT `org_chart_configs_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `org_chart_configs_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `org_chart_configs_scope_branch_id_foreign` FOREIGN KEY (`scope_branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_occ_group_by` CHECK ((`group_by` in (_utf8mb4'department',_utf8mb4'branch',_utf8mb4'job_title',_utf8mb4'manager'))),
  CONSTRAINT `chk_occ_view_type` CHECK ((`view_type` in (_utf8mb4'tree',_utf8mb4'flat_list',_utf8mb4'matrix')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organization_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organization_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public UUID — expose ra ngoài, không phải PK',
  `order_column` int unsigned DEFAULT NULL COMMENT 'Thứ tự sắp xếp — Spatie Sortable / ORDER BY',
  `organization_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role` enum('owner','admin','manager','member') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member' COMMENT 'Vai trò trong tổ chức',
  `joined_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm gia nhập',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization_members_organization_id_user_id_unique` (`organization_id`,`user_id`),
  UNIQUE KEY `organization_members_uuid_unique` (`uuid`),
  KEY `organization_members_user_id_foreign` (`user_id`),
  KEY `organization_members_order_column_index` (`order_column`),
  KEY `organization_members_role_index` (`role`),
  CONSTRAINT `organization_members_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `organization_members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organization_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organization_settings` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public UUID — expose ra ngoài, không phải PK',
  `order_column` int unsigned DEFAULT NULL COMMENT 'Thứ tự sắp xếp — Spatie Sortable / ORDER BY',
  `organization_id` bigint unsigned NOT NULL,
  `key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Khóa cài đặt',
  `value` text COLLATE utf8mb4_unicode_ci COMMENT 'Giá trị (serialized nếu cần)',
  `type` enum('string','integer','boolean','json','float') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'string' COMMENT 'Kiểu dữ liệu giá trị',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organization_settings_organization_id_key_unique` (`organization_id`,`key`),
  UNIQUE KEY `organization_settings_uuid_unique` (`uuid`),
  KEY `organization_settings_order_column_index` (`order_column`),
  KEY `organization_settings_key_index` (`key`),
  CONSTRAINT `organization_settings_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `organizations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `organizations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lead_assessment_code` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Assessment code dùng để chấm điểm lead sâu. NULL = tắt.',
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `settings` json DEFAULT NULL,
  `owner_id` bigint unsigned DEFAULT NULL COMMENT 'ID người sở hữu — không có FK ở DB',
  `tax_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã số thuế doanh nghiệp',
  `phone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Số điện thoại liên hệ',
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email liên hệ của tổ chức',
  `website` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Website của tổ chức',
  `industry` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Ngành nghề kinh doanh',
  `address` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Địa chỉ đầy đủ',
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Thành phố',
  `country` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VN' COMMENT 'Mã quốc gia ISO 3166-1',
  `postal_code` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã bưu chính',
  `description` text COLLATE utf8mb4_unicode_ci COMMENT 'Mô tả về tổ chức',
  `logo_path` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Đường dẫn đến file logo',
  `province_code` char(2) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Tỉnh/thành phố — FK tới provinces.province_code',
  `ward_code` char(5) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Phường/xã — FK tới wards.ward_code',
  `full_address` text COLLATE utf8mb4_unicode_ci COMMENT 'Địa chỉ đầy đủ kết hợp (số nhà + phường/xã + tỉnh)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `organizations_slug_unique` (`slug`),
  UNIQUE KEY `organizations_uuid_unique` (`uuid`),
  KEY `organizations_ward_code_foreign` (`ward_code`),
  KEY `organizations_province_code_ward_code_status_created_at_index` (`province_code`,`ward_code`,`status`,`created_at`),
  KEY `organizations_owner_id_index` (`owner_id`),
  KEY `organizations_industry_index` (`industry`),
  KEY `organizations_country_index` (`country`),
  CONSTRAINT `organizations_province_code_foreign` FOREIGN KEY (`province_code`) REFERENCES `provinces` (`province_code`) ON DELETE SET NULL,
  CONSTRAINT `organizations_ward_code_foreign` FOREIGN KEY (`ward_code`) REFERENCES `wards` (`ward_code`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pain_point_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pain_point_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `pain_point_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. sales_leakage',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `required_flags` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Comma-separated flags, prefix ! = NOT. Tất cả AND. e.g. LEAD_LOSS,!HAS_CRM',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pain_point_rules_assessment_code_pain_point_code_unique` (`assessment_code`,`pain_point_code`),
  KEY `pain_point_rules_assessment_code_index` (`assessment_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `pass_fail_configs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pass_fail_configs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK logic tới assessments.assessment_code',
  `passing_score` decimal(5,2) NOT NULL,
  `label_pass` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pass',
  `label_fail` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Fail',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `pass_fail_configs_assessment_code_unique` (`assessment_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `performance_reviews`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `performance_reviews` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `reviewer_id` bigint unsigned NOT NULL,
  `template_id` bigint unsigned NOT NULL,
  `period` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'draft',
  `overall_score` decimal(5,2) DEFAULT NULL,
  `overall_rating` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `strengths` text COLLATE utf8mb4_unicode_ci,
  `improvements` text COLLATE utf8mb4_unicode_ci,
  `goals_next_period` text COLLATE utf8mb4_unicode_ci,
  `employee_comment` text COLLATE utf8mb4_unicode_ci,
  `snap_branch_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snap_dept_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snap_job_title` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `snap_job_level` tinyint unsigned DEFAULT NULL,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `acknowledged_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_review_period` (`employee_id`,`template_id`,`period`),
  UNIQUE KEY `performance_reviews_uuid_unique` (`uuid`),
  KEY `performance_reviews_template_id_foreign` (`template_id`),
  KEY `idx_reviews_org_period` (`organization_id`,`period`,`status`),
  KEY `idx_reviews_employee` (`employee_id`,`period`),
  KEY `idx_reviews_reviewer` (`reviewer_id`),
  CONSTRAINT `performance_reviews_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `performance_reviews_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `performance_reviews_reviewer_id_foreign` FOREIGN KEY (`reviewer_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `performance_reviews_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `review_templates` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_pr_no_self` CHECK ((`employee_id` <> `reviewer_id`)),
  CONSTRAINT `chk_pr_rating` CHECK (((`overall_rating` is null) or (`overall_rating` in (_utf8mb4'excellent',_utf8mb4'good',_utf8mb4'average',_utf8mb4'below_average',_utf8mb4'poor')))),
  CONSTRAINT `chk_pr_status` CHECK ((`status` in (_utf8mb4'draft',_utf8mb4'submitted',_utf8mb4'acknowledged',_utf8mb4'finalized',_utf8mb4'cancelled')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `permissions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `permissions_name_guard_name_unique` (`name`,`guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `persona_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `persona_conditions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `persona_id` bigint unsigned NOT NULL,
  `target_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'domain | section | overall | signal_flag',
  `target_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'domain_code | section_code | "overall" | flag_code',
  `operator` varchar(5) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT '< | <= | = | >= | >',
  `threshold_value` decimal(5,2) DEFAULT NULL COMMENT 'Ngưỡng cho domain/section/overall',
  `flag_value` tinyint(1) DEFAULT NULL COMMENT 'Giá trị mong đợi cho signal_flag',
  `sort_order` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `persona_conditions_persona_id_index` (`persona_id`),
  CONSTRAINT `persona_conditions_persona_id_foreign` FOREIGN KEY (`persona_id`) REFERENCES `personas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `personas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK logic tới assessments.assessment_code',
  `persona_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_persona` (`assessment_code`,`persona_code`),
  KEY `personas_assessment_code_index` (`assessment_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `plans`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `plans` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` json NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` json DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `signup_fee` decimal(8,2) NOT NULL DEFAULT '0.00',
  `currency` varchar(3) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trial_period` smallint unsigned NOT NULL DEFAULT '0',
  `trial_interval` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'day',
  `invoice_period` smallint unsigned NOT NULL DEFAULT '0',
  `invoice_interval` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'month',
  `grace_period` smallint unsigned NOT NULL DEFAULT '0',
  `grace_interval` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'day',
  `prorate_day` tinyint unsigned DEFAULT NULL,
  `prorate_period` tinyint unsigned DEFAULT NULL,
  `prorate_extend_due` tinyint unsigned DEFAULT NULL,
  `active_subscribers_limit` smallint unsigned DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plans_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `process_approval_flow_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `process_approval_flow_steps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `process_approval_flow_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `permissions` json DEFAULT NULL,
  `order` int DEFAULT NULL,
  `action` enum('APPROVE','VERIFY','CHECK') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'APPROVE',
  `active` tinyint NOT NULL DEFAULT '1',
  `tenant_id` varchar(38) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `process_approval_flow_steps_process_approval_flow_id_foreign` (`process_approval_flow_id`),
  KEY `process_approval_flow_steps_role_id_index` (`role_id`),
  KEY `process_approval_flow_steps_order_index` (`order`),
  KEY `process_approval_flow_steps_tenant_id_index` (`tenant_id`),
  CONSTRAINT `process_approval_flow_steps_process_approval_flow_id_foreign` FOREIGN KEY (`process_approval_flow_id`) REFERENCES `process_approval_flows` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `process_approval_flows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `process_approval_flows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approvable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `process_approval_statuses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `process_approval_statuses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `approvable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approvable_id` bigint unsigned NOT NULL,
  `steps` json DEFAULT NULL,
  `status` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Created',
  `creator_id` bigint unsigned DEFAULT NULL,
  `tenant_id` varchar(38) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `process_approval_statuses_approvable_type_approvable_id_index` (`approvable_type`,`approvable_id`),
  KEY `process_approval_statuses_tenant_id_index` (`tenant_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `process_approvals`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `process_approvals` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `approvable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `approvable_id` bigint unsigned NOT NULL,
  `process_approval_flow_step_id` bigint unsigned DEFAULT NULL,
  `approval_action` varchar(12) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Approved',
  `approver_name` text COLLATE utf8mb4_unicode_ci,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `user_id` bigint unsigned NOT NULL,
  `tenant_id` varchar(38) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `process_approvals_approvable_type_approvable_id_index` (`approvable_type`,`approvable_id`),
  KEY `process_approvals_process_approval_flow_step_id_foreign` (`process_approval_flow_step_id`),
  KEY `process_approvals_user_id_foreign` (`user_id`),
  KEY `process_approvals_tenant_id_index` (`tenant_id`),
  CONSTRAINT `process_approvals_process_approval_flow_step_id_foreign` FOREIGN KEY (`process_approval_flow_step_id`) REFERENCES `process_approval_flow_steps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `process_approvals_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `project_members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `project_members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `project_id` bigint unsigned NOT NULL,
  `employee_id` bigint unsigned NOT NULL,
  `role` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'member',
  `is_lead` tinyint NOT NULL DEFAULT '0',
  `contribution_pct` tinyint unsigned DEFAULT NULL,
  `joined_at` date DEFAULT NULL,
  `left_at` date DEFAULT NULL,
  `note` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_project_member` (`project_id`,`employee_id`,`left_at`),
  KEY `idx_pm_project` (`project_id`,`left_at`),
  KEY `idx_pm_employee` (`employee_id`,`left_at`),
  CONSTRAINT `project_members_employee_id_foreign` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `project_members_project_id_foreign` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_pm_contribution` CHECK (((`contribution_pct` is null) or ((`contribution_pct` >= 0) and (`contribution_pct` <= 100)))),
  CONSTRAINT `chk_pm_role` CHECK ((`role` in (_utf8mb4'lead',_utf8mb4'member',_utf8mb4'advisor',_utf8mb4'stakeholder')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `projects` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_id` bigint unsigned NOT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `owner_id` bigint unsigned NOT NULL,
  `code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `category` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planning',
  `priority` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'medium',
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `completed_at` timestamp NULL DEFAULT NULL,
  `budget` decimal(15,2) DEFAULT NULL,
  `currency` char(3) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VND',
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_project_code` (`organization_id`,`code`),
  UNIQUE KEY `projects_uuid_unique` (`uuid`),
  KEY `projects_created_by_foreign` (`created_by`),
  KEY `projects_updated_by_foreign` (`updated_by`),
  KEY `idx_projects_org_status` (`organization_id`,`status`),
  KEY `idx_projects_org_priority` (`organization_id`,`status`,`priority`),
  KEY `idx_projects_branch` (`branch_id`),
  KEY `idx_projects_dept` (`department_id`),
  KEY `idx_projects_owner` (`owner_id`),
  KEY `idx_projects_dates` (`start_date`,`end_date`),
  CONSTRAINT `projects_branch_id_foreign` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_department_id_foreign` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `projects_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `projects_owner_id_foreign` FOREIGN KEY (`owner_id`) REFERENCES `employees` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `projects_updated_by_foreign` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `chk_proj_dates` CHECK (((`end_date` is null) or (`start_date` is null) or (`end_date` >= `start_date`))),
  CONSTRAINT `chk_proj_priority` CHECK ((`priority` in (_utf8mb4'low',_utf8mb4'medium',_utf8mb4'high',_utf8mb4'critical'))),
  CONSTRAINT `chk_proj_status` CHECK ((`status` in (_utf8mb4'planning',_utf8mb4'active',_utf8mb4'on_hold',_utf8mb4'completed',_utf8mb4'cancelled')))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `provinces`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `provinces` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public UUID — expose ra ngoài, không phải PK',
  `order_column` int unsigned DEFAULT NULL COMMENT 'Thứ tự sắp xếp — Spatie Sortable / ORDER BY',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên tỉnh/thành phố',
  `short_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên ngắn gọn của tỉnh/thành phố',
  `logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Logo tỉnh',
  `province_code` char(2) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã tỉnh/thành phố',
  `place_type` enum('thanh-pho','tinh') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'tinh' COMMENT 'Loại: Thành phố Trung Ương hoặc Tỉnh',
  `region_id` bigint unsigned NOT NULL,
  `country` varchar(2) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'VN' COMMENT 'Mã quốc gia',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Trạng thái hoạt động',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `provinces_province_code_unique` (`province_code`),
  UNIQUE KEY `provinces_uuid_unique` (`uuid`),
  KEY `provinces_region_id_index` (`region_id`),
  KEY `provinces_order_column_index` (`order_column`),
  KEY `provinces_name_index` (`name`),
  KEY `provinces_place_type_index` (`place_type`),
  KEY `provinces_country_index` (`country`),
  KEY `provinces_is_active_index` (`is_active`),
  CONSTRAINT `provinces_region_id_foreign` FOREIGN KEY (`region_id`) REFERENCES `regions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `recommendation_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `recommendation_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `recommendation_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. crm_setup',
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `trigger_domain` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Domain code trigger rule này',
  `threshold_score` decimal(5,2) NOT NULL COMMENT 'Trigger khi normalized_domain_score < threshold',
  `priority` tinyint NOT NULL DEFAULT '1' COMMENT '1 = cao nhất',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `recommendation_rules_assessment_code_recommendation_code_unique` (`assessment_code`,`recommendation_code`),
  KEY `recommendation_rules_assessment_code_index` (`assessment_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `regions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `regions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public UUID — expose ra ngoài, không phải PK',
  `order_column` int unsigned DEFAULT NULL COMMENT 'Thứ tự sắp xếp — Spatie Sortable / ORDER BY',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên vùng',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `regions_uuid_unique` (`uuid`),
  KEY `regions_order_column_index` (`order_column`),
  KEY `regions_name_index` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `result_classifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `result_classifications` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `result_id` bigint unsigned NOT NULL,
  `classification_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'score_band | pass_fail | persona_match | none',
  `band_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dùng khi classification_type = score_band',
  `passed` tinyint(1) DEFAULT NULL COMMENT 'Dùng khi classification_type = pass_fail',
  `persona_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Dùng khi classification_type = persona_match',
  `match_score` decimal(5,2) DEFAULT NULL COMMENT 'Tỉ lệ điều kiện thỏa / tổng điều kiện (persona_match)',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `result_classifications_result_id_unique` (`result_id`),
  KEY `result_classifications_result_id_band_code_index` (`result_id`,`band_code`),
  CONSTRAINT `result_classifications_result_id_foreign` FOREIGN KEY (`result_id`) REFERENCES `assessment_results` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `result_domain_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `result_domain_scores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `result_id` bigint unsigned NOT NULL,
  `domain_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `raw_score` int NOT NULL,
  `normalized_score` decimal(5,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `result_domain_scores_result_id_domain_code_unique` (`result_id`,`domain_code`),
  CONSTRAINT `result_domain_scores_result_id_foreign` FOREIGN KEY (`result_id`) REFERENCES `assessment_results` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `result_pain_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `result_pain_points` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `result_id` bigint unsigned NOT NULL,
  `pain_point_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `result_pain_points_result_id_pain_point_code_unique` (`result_id`,`pain_point_code`),
  CONSTRAINT `result_pain_points_result_id_foreign` FOREIGN KEY (`result_id`) REFERENCES `assessment_results` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `result_question_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `result_question_scores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `result_id` bigint unsigned NOT NULL,
  `question_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'field_key của câu hỏi',
  `feature_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'feature_code từ score_rules',
  `raw_score` int NOT NULL COMMENT 'Score trước khi cap',
  `final_score` int NOT NULL COMMENT 'Score sau cap = Fi',
  `selected_options` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Comma-separated option values đã chọn',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rqs` (`result_id`,`question_code`),
  KEY `result_question_scores_result_id_index` (`result_id`),
  CONSTRAINT `result_question_scores_result_id_foreign` FOREIGN KEY (`result_id`) REFERENCES `assessment_results` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `result_question_selected_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `result_question_selected_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `question_score_id` bigint unsigned NOT NULL,
  `option_key` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `result_question_selected_options_question_score_id_index` (`question_score_id`),
  CONSTRAINT `result_question_selected_options_question_score_id_foreign` FOREIGN KEY (`question_score_id`) REFERENCES `result_question_scores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `result_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `result_recommendations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `result_id` bigint unsigned NOT NULL,
  `recommendation_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `priority` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `result_recommendations_result_id_recommendation_code_unique` (`result_id`,`recommendation_code`),
  CONSTRAINT `result_recommendations_result_id_foreign` FOREIGN KEY (`result_id`) REFERENCES `assessment_results` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `result_roadmap_phases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `result_roadmap_phases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `result_id` bigint unsigned NOT NULL,
  `phase_id` bigint unsigned NOT NULL,
  `sort_order` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `result_roadmap_phases_result_id_phase_id_unique` (`result_id`,`phase_id`),
  KEY `result_roadmap_phases_phase_id_foreign` (`phase_id`),
  CONSTRAINT `result_roadmap_phases_phase_id_foreign` FOREIGN KEY (`phase_id`) REFERENCES `roadmap_phases` (`id`) ON DELETE CASCADE,
  CONSTRAINT `result_roadmap_phases_result_id_foreign` FOREIGN KEY (`result_id`) REFERENCES `assessment_results` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `result_signal_flags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `result_signal_flags` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `result_id` bigint unsigned NOT NULL,
  `flag_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. HAS_CRM',
  `flag_value` tinyint(1) NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `result_signal_flags_result_id_flag_code_unique` (`result_id`,`flag_code`),
  CONSTRAINT `result_signal_flags_result_id_foreign` FOREIGN KEY (`result_id`) REFERENCES `assessment_results` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `review_criteria`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `review_criteria` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `template_id` bigint unsigned NOT NULL,
  `criteria_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `criteria_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `max_score` tinyint unsigned NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_criteria_key` (`template_id`,`criteria_key`),
  KEY `idx_criteria_template` (`template_id`,`sort_order`),
  CONSTRAINT `review_criteria_template_id_foreign` FOREIGN KEY (`template_id`) REFERENCES `review_templates` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_rc_max_score` CHECK (((`max_score` >= 1) and (`max_score` <= 10))),
  CONSTRAINT `chk_rc_weight` CHECK (((`weight` > 0) and (`weight` <= 100)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `review_scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `review_scores` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `review_id` bigint unsigned NOT NULL,
  `criteria_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `criteria_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `score` decimal(5,2) NOT NULL,
  `weight` decimal(5,2) NOT NULL,
  `max_score` tinyint unsigned NOT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_score_criteria` (`review_id`,`criteria_key`),
  CONSTRAINT `review_scores_review_id_foreign` FOREIGN KEY (`review_id`) REFERENCES `performance_reviews` (`id`) ON DELETE CASCADE,
  CONSTRAINT `chk_rs_score` CHECK (((`score` >= 0) and (`score` <= `max_score`))),
  CONSTRAINT `chk_rs_weight` CHECK (((`weight` > 0) and (`weight` <= 100)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `review_templates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `review_templates` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `organization_id` bigint unsigned NOT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `period_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'quarterly',
  `apply_to_function` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rating_scale` tinyint unsigned NOT NULL DEFAULT '5',
  `is_system` tinyint NOT NULL DEFAULT '0',
  `is_locked` tinyint NOT NULL DEFAULT '0',
  `is_active` tinyint NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `review_templates_uuid_unique` (`uuid`),
  KEY `review_templates_created_by_foreign` (`created_by`),
  KEY `idx_review_templates_org` (`organization_id`,`is_active`),
  CONSTRAINT `review_templates_created_by_foreign` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `review_templates_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `chk_rt_period_type` CHECK ((`period_type` in (_utf8mb4'monthly',_utf8mb4'quarterly',_utf8mb4'semi_annual',_utf8mb4'annual',_utf8mb4'probation',_utf8mb4'custom'))),
  CONSTRAINT `chk_rt_rating_scale` CHECK ((`rating_scale` in (5,10)))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roadmap_milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roadmap_milestones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `phase_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `roadmap_milestones_phase_id_index` (`phase_id`),
  CONSTRAINT `roadmap_milestones_phase_id_foreign` FOREIGN KEY (`phase_id`) REFERENCES `roadmap_phases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roadmap_phases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roadmap_phases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `maturity_level` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Khớp với maturity_levels.level_code',
  `band_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Gắn phase theo score_band.band_code — mới theo spec. NULL = dùng maturity_level (cũ)',
  `phase_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `duration_weeks` tinyint DEFAULT NULL COMMENT 'Thời gian dự kiến (tuần)',
  `sort_order` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_roadmap_phase` (`assessment_code`,`maturity_level`,`phase_code`),
  KEY `roadmap_phases_assessment_code_maturity_level_index` (`assessment_code`,`maturity_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `role_has_permissions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `role_has_permissions` (
  `permission_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  PRIMARY KEY (`permission_id`,`role_id`),
  KEY `role_has_permissions_role_id_foreign` (`role_id`),
  CONSTRAINT `role_has_permissions_permission_id_foreign` FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
  CONSTRAINT `role_has_permissions_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `guard_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `roles_organization_id_name_guard_name_unique` (`organization_id`,`name`,`guard_name`),
  KEY `roles_team_foreign_key_index` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `score_bands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `score_bands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'FK logic tới assessments.assessment_code',
  `band_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'e.g. MANUAL_OPERATION, AI_READY',
  `label` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `min_score` decimal(5,2) NOT NULL COMMENT 'Ngưỡng hiện hành (dưới, inclusive)',
  `max_score` decimal(5,2) NOT NULL COMMENT 'Ngưỡng hiện hành (trên, inclusive)',
  `default_min` decimal(5,2) NOT NULL COMMENT 'Ngưỡng gốc (để reset)',
  `default_max` decimal(5,2) NOT NULL COMMENT 'Ngưỡng gốc (để reset)',
  `is_dynamic` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'TRUE = ngưỡng có thể điều chỉnh tự động',
  `lead_temperature` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'cold' COMMENT 'hot | warm | cold — mirror với maturity_levels',
  `sort_order` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_score_band` (`assessment_code`,`band_code`),
  KEY `score_bands_assessment_code_index` (`assessment_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `score_rule_numeric_ranges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `score_rule_numeric_ranges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` bigint unsigned NOT NULL,
  `min_value` decimal(10,2) DEFAULT NULL COMMENT 'NULL = không giới hạn dưới',
  `max_value` decimal(10,2) DEFAULT NULL COMMENT 'NULL = không giới hạn trên',
  `score` int NOT NULL,
  `signal_flag` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `score_rule_numeric_ranges_rule_id_index` (`rule_id`),
  CONSTRAINT `score_rule_numeric_ranges_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `score_rules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `score_rule_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `score_rule_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `rule_id` bigint unsigned NOT NULL,
  `option_value` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Khớp với survey_field_options.option_value',
  `option_label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '' COMMENT 'Nhãn hiển thị của option',
  `score` int NOT NULL DEFAULT '0',
  `signal_flag` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Flag emit khi option này được chọn',
  `sort_order` tinyint NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `score_rule_options_rule_id_option_value_unique` (`rule_id`,`option_value`),
  CONSTRAINT `score_rule_options_rule_id_foreign` FOREIGN KEY (`rule_id`) REFERENCES `score_rules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `score_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `score_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `field_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Khớp với survey_fields.field_key',
  `domain_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feature_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Định danh đặc trưng Fi — cầu nối với feature_weights. Mặc định = field_key',
  `question_scoring_type` varchar(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'none | boolean | single_choice | multi_choice | numeric_range. NULL = dùng condition_type',
  `min_score_cap` int DEFAULT NULL COMMENT 'multi_choice: chặn dưới tổng score',
  `max_score_cap` int DEFAULT NULL COMMENT 'multi_choice: chặn trên tổng score',
  `signal_flag` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'e.g. HAS_CRM — nullable nếu không emit flag',
  `score_if_true` int NOT NULL DEFAULT '0' COMMENT 'Dùng khi condition_type = boolean',
  `score_if_false` int NOT NULL DEFAULT '0' COMMENT 'Dùng khi condition_type = boolean',
  `condition_type` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'boolean' COMMENT 'boolean | single_choice | multi_choice',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `section_id` bigint unsigned DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_score_rule` (`assessment_code`,`field_key`,`domain_code`),
  KEY `score_rules_assessment_code_domain_code_index` (`assessment_code`,`domain_code`),
  KEY `score_rules_section_id_foreign` (`section_id`),
  CONSTRAINT `score_rules_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `survey_sections` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `scoring_feedback`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scoring_feedback` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `result_id` bigint unsigned NOT NULL,
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `predicted_band` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `actual_band` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `predicted_score` decimal(5,2) DEFAULT NULL,
  `actual_score` decimal(5,2) DEFAULT NULL,
  `feedback_source` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'admin_review | observed_outcome | user_self_report',
  `is_processed` tinyint(1) NOT NULL DEFAULT '0',
  `submitted_by` bigint unsigned DEFAULT NULL,
  `submitted_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `scoring_feedback_assessment_code_is_processed_index` (`assessment_code`,`is_processed`),
  KEY `scoring_feedback_result_id_foreign` (`result_id`),
  CONSTRAINT `scoring_feedback_result_id_foreign` FOREIGN KEY (`result_id`) REFERENCES `assessment_results` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `snapshot_bands`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snapshot_bands` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_id` bigint unsigned NOT NULL,
  `band_code` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `min_score` decimal(5,2) NOT NULL DEFAULT '0.00',
  `max_score` decimal(5,2) NOT NULL DEFAULT '100.00',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `snapshot_bands_snapshot_id_index` (`snapshot_id`),
  CONSTRAINT `snapshot_bands_snapshot_id_foreign` FOREIGN KEY (`snapshot_id`) REFERENCES `assessment_config_snapshots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `snapshot_domains`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snapshot_domains` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_id` bigint unsigned NOT NULL,
  `domain_code` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `weight` decimal(8,4) NOT NULL DEFAULT '0.0000',
  `min_score` smallint NOT NULL DEFAULT '0',
  `max_score` smallint NOT NULL DEFAULT '100',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `snapshot_domains_snapshot_id_index` (`snapshot_id`),
  CONSTRAINT `snapshot_domains_snapshot_id_foreign` FOREIGN KEY (`snapshot_id`) REFERENCES `assessment_config_snapshots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `snapshot_pain_points`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snapshot_pain_points` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_id` bigint unsigned NOT NULL,
  `pain_point_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `required_flags` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `snapshot_pain_points_snapshot_id_index` (`snapshot_id`),
  CONSTRAINT `snapshot_pain_points_snapshot_id_foreign` FOREIGN KEY (`snapshot_id`) REFERENCES `assessment_config_snapshots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `snapshot_persona_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snapshot_persona_conditions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_persona_id` bigint unsigned NOT NULL,
  `target_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `target_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `operator` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `threshold_value` decimal(8,4) DEFAULT NULL,
  `flag_value` tinyint DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `snapshot_persona_conditions_snapshot_persona_id_index` (`snapshot_persona_id`),
  CONSTRAINT `snapshot_persona_conditions_snapshot_persona_id_foreign` FOREIGN KEY (`snapshot_persona_id`) REFERENCES `snapshot_personas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `snapshot_personas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snapshot_personas` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_id` bigint unsigned NOT NULL,
  `persona_code` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `snapshot_personas_snapshot_id_index` (`snapshot_id`),
  CONSTRAINT `snapshot_personas_snapshot_id_foreign` FOREIGN KEY (`snapshot_id`) REFERENCES `assessment_config_snapshots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `snapshot_recommendations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snapshot_recommendations` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_id` bigint unsigned NOT NULL,
  `recommendation_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `trigger_domain` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `threshold_score` decimal(5,2) NOT NULL DEFAULT '50.00',
  `priority` smallint unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `snapshot_recommendations_snapshot_id_index` (`snapshot_id`),
  CONSTRAINT `snapshot_recommendations_snapshot_id_foreign` FOREIGN KEY (`snapshot_id`) REFERENCES `assessment_config_snapshots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `snapshot_roadmap_milestones`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snapshot_roadmap_milestones` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_phase_id` bigint unsigned NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `snapshot_roadmap_milestones_snapshot_phase_id_index` (`snapshot_phase_id`),
  CONSTRAINT `snapshot_roadmap_milestones_snapshot_phase_id_foreign` FOREIGN KEY (`snapshot_phase_id`) REFERENCES `snapshot_roadmap_phases` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `snapshot_roadmap_phases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snapshot_roadmap_phases` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_id` bigint unsigned NOT NULL,
  `band_code` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phase_code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `duration_weeks` smallint unsigned DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `snapshot_roadmap_phases_snapshot_id_index` (`snapshot_id`),
  CONSTRAINT `snapshot_roadmap_phases_snapshot_id_foreign` FOREIGN KEY (`snapshot_id`) REFERENCES `assessment_config_snapshots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `snapshot_rule_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snapshot_rule_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_rule_id` bigint unsigned NOT NULL,
  `option_value` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `option_label` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score` int NOT NULL DEFAULT '0',
  `signal_flag` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `snapshot_rule_options_snapshot_rule_id_index` (`snapshot_rule_id`),
  CONSTRAINT `snapshot_rule_options_snapshot_rule_id_foreign` FOREIGN KEY (`snapshot_rule_id`) REFERENCES `snapshot_rules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `snapshot_rule_ranges`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snapshot_rule_ranges` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_rule_id` bigint unsigned NOT NULL,
  `min_value` decimal(10,2) DEFAULT NULL,
  `max_value` decimal(10,2) DEFAULT NULL,
  `score` int NOT NULL DEFAULT '0',
  `signal_flag` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `snapshot_rule_ranges_snapshot_rule_id_index` (`snapshot_rule_id`),
  CONSTRAINT `snapshot_rule_ranges_snapshot_rule_id_foreign` FOREIGN KEY (`snapshot_rule_id`) REFERENCES `snapshot_rules` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `snapshot_rules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `snapshot_rules` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `snapshot_id` bigint unsigned NOT NULL,
  `field_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `domain_code` varchar(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `feature_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `signal_flag` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `score_if_true` int NOT NULL DEFAULT '0',
  `score_if_false` int NOT NULL DEFAULT '0',
  `question_scoring_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `condition_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none',
  `min_score_cap` int DEFAULT NULL,
  `max_score_cap` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `snapshot_rules_snapshot_id_index` (`snapshot_id`),
  CONSTRAINT `snapshot_rules_snapshot_id_foreign` FOREIGN KEY (`snapshot_id`) REFERENCES `assessment_config_snapshots` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `submission_behavior_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `submission_behavior_log` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `response_id` bigint unsigned NOT NULL,
  `question_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'field_key của câu hỏi liên quan',
  `event_type` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'view | answer | change | skip | back | time_spent',
  `event_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Giá trị kèm theo event (e.g. thời gian ms, option chọn)',
  `sequence_no` int NOT NULL COMMENT 'Thứ tự event trong session',
  `occurred_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `submission_behavior_log_response_id_sequence_no_index` (`response_id`,`sequence_no`),
  KEY `submission_behavior_log_question_code_index` (`question_code`),
  KEY `behavior_logs_response_occurred_idx` (`response_id`,`occurred_at`),
  CONSTRAINT `submission_behavior_log_response_id_foreign` FOREIGN KEY (`response_id`) REFERENCES `survey_responses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subscription_usage`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscription_usage` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscription_id` bigint unsigned NOT NULL,
  `feature_id` bigint unsigned NOT NULL,
  `used` smallint unsigned NOT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `valid_until` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `subscriptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `subscriptions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `subscriber_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subscriber_id` bigint unsigned NOT NULL,
  `plan_id` bigint unsigned NOT NULL,
  `name` json NOT NULL,
  `slug` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` json DEFAULT NULL,
  `timezone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trial_ends_at` datetime DEFAULT NULL,
  `starts_at` datetime DEFAULT NULL,
  `ends_at` datetime DEFAULT NULL,
  `canceled_at` datetime DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `subscriptions_subscriber_type_subscriber_id_index` (`subscriber_type`,`subscriber_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_answers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public UUID — expose ra ngoài, không phải PK',
  `order_column` int unsigned DEFAULT NULL COMMENT 'Thứ tự sắp xếp — Spatie Sortable / ORDER BY',
  `response_id` bigint unsigned NOT NULL,
  `field_id` bigint unsigned NOT NULL,
  `row_key` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `option_id` bigint unsigned DEFAULT NULL,
  `value_string` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Text ngắn — indexable',
  `value_text` text COLLATE utf8mb4_unicode_ci COMMENT 'Textarea dài — không index',
  `value_number` decimal(15,2) DEFAULT NULL COMMENT 'Giá trị số',
  `value_date` date DEFAULT NULL COMMENT 'Giá trị ngày',
  `value_bool` tinyint(1) DEFAULT NULL COMMENT 'Giá trị Có/Không',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_answers_uuid_unique` (`uuid`),
  KEY `survey_answers_option_id_foreign` (`option_id`),
  KEY `survey_answers_field_id_option_id_index` (`field_id`,`option_id`),
  KEY `survey_answers_field_id_value_number_index` (`field_id`,`value_number`),
  KEY `survey_answers_field_id_value_bool_index` (`field_id`,`value_bool`),
  KEY `survey_answers_field_id_value_string_index` (`field_id`,`value_string`),
  KEY `survey_answers_response_id_field_id_index` (`response_id`,`field_id`),
  KEY `survey_answers_order_column_index` (`order_column`),
  KEY `sa_response_field_row_idx` (`response_id`,`field_id`,`row_key`),
  CONSTRAINT `survey_answers_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `survey_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `survey_answers_option_id_foreign` FOREIGN KEY (`option_id`) REFERENCES `survey_field_options` (`id`) ON DELETE SET NULL,
  CONSTRAINT `survey_answers_response_id_foreign` FOREIGN KEY (`response_id`) REFERENCES `survey_responses` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_drafts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_drafts` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` bigint unsigned NOT NULL,
  `respondent_ref` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `answers` json NOT NULL,
  `current_section` int unsigned NOT NULL DEFAULT '0',
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_drafts_survey_id_respondent_ref_unique` (`survey_id`,`respondent_ref`),
  KEY `survey_drafts_respondent_ref_index` (`respondent_ref`),
  CONSTRAINT `survey_drafts_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_field_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_field_conditions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `field_id` bigint unsigned NOT NULL,
  `depends_on_field_id` bigint unsigned NOT NULL,
  `operator` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_value` json NOT NULL,
  `action` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'show',
  `sort_order` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_field_conditions_depends_on_field_id_foreign` (`depends_on_field_id`),
  KEY `survey_field_conditions_field_id_index` (`field_id`),
  CONSTRAINT `survey_field_conditions_depends_on_field_id_foreign` FOREIGN KEY (`depends_on_field_id`) REFERENCES `survey_fields` (`id`) ON DELETE CASCADE,
  CONSTRAINT `survey_field_conditions_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `survey_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_field_options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_field_options` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public UUID — expose ra ngoài, không phải PK',
  `order_column` int unsigned DEFAULT NULL COMMENT 'Thứ tự sắp xếp — Spatie Sortable / ORDER BY',
  `field_id` bigint unsigned NOT NULL,
  `option_value` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Giá trị machine: chatgpt',
  `label` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nhãn hiển thị: ChatGPT',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0' COMMENT 'Thứ tự hiển thị',
  `is_other` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Lựa chọn Khác — cho phép nhập tay',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_field_options_uuid_unique` (`uuid`),
  KEY `survey_field_options_field_id_sort_order_index` (`field_id`,`sort_order`),
  KEY `survey_field_options_order_column_index` (`order_column`),
  CONSTRAINT `survey_field_options_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `survey_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_field_rows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_field_rows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `field_id` bigint unsigned NOT NULL,
  `row_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sort_order` smallint unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_field_rows_field_id_row_key_unique` (`field_id`,`row_key`),
  KEY `survey_field_rows_field_id_sort_order_index` (`field_id`,`sort_order`),
  CONSTRAINT `survey_field_rows_field_id_foreign` FOREIGN KEY (`field_id`) REFERENCES `survey_fields` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_fields`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_fields` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public UUID — expose ra ngoài, không phải PK',
  `order_column` int unsigned DEFAULT NULL COMMENT 'Thứ tự sắp xếp — Spatie Sortable / ORDER BY',
  `survey_id` bigint unsigned NOT NULL,
  `section_id` bigint unsigned DEFAULT NULL,
  `parent_field_id` bigint unsigned DEFAULT NULL,
  `field_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Machine key: company_name, ai_tools_used',
  `label` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nhãn hiển thị câu hỏi',
  `field_type` tinyint unsigned NOT NULL COMMENT 'Loại field (enum số hóa)',
  `value_kind` tinyint unsigned NOT NULL COMMENT 'Cột lưu giá trị sẽ dùng',
  `is_required` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Bắt buộc điền',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Field đang active',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0' COMMENT 'Thứ tự hiển thị',
  `rule_min` int DEFAULT NULL COMMENT 'Validation: giá trị tối thiểu',
  `rule_max` int DEFAULT NULL COMMENT 'Validation: giá trị tối đa',
  `rule_max_select` smallint DEFAULT NULL COMMENT 'Giới hạn số lựa chọn multi-choice',
  `placeholder` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Placeholder text',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_fields_survey_id_field_key_unique` (`survey_id`,`field_key`),
  UNIQUE KEY `survey_fields_uuid_unique` (`uuid`),
  KEY `survey_fields_section_id_foreign` (`section_id`),
  KEY `survey_fields_survey_id_section_id_sort_order_index` (`survey_id`,`section_id`,`sort_order`),
  KEY `survey_fields_parent_field_id_index` (`parent_field_id`),
  KEY `survey_fields_order_column_index` (`order_column`),
  CONSTRAINT `survey_fields_parent_field_id_foreign` FOREIGN KEY (`parent_field_id`) REFERENCES `survey_fields` (`id`) ON DELETE SET NULL,
  CONSTRAINT `survey_fields_section_id_foreign` FOREIGN KEY (`section_id`) REFERENCES `survey_sections` (`id`) ON DELETE SET NULL,
  CONSTRAINT `survey_fields_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_responses` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public UUID — expose ra ngoài, không phải PK',
  `order_column` int unsigned DEFAULT NULL COMMENT 'Thứ tự sắp xếp — Spatie Sortable / ORDER BY',
  `survey_id` bigint unsigned NOT NULL,
  `respondent_ref` varchar(190) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email/phone match CRM',
  `respondent_ip` varbinary(16) DEFAULT NULL COMMENT 'Binary IP — INET6_ATON (16 bytes)',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '0=partial 1=complete',
  `submitted_at` timestamp NULL DEFAULT NULL COMMENT 'Thời điểm nộp hoàn tất',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_responses_uuid_unique` (`uuid`),
  KEY `survey_responses_survey_id_status_submitted_at_index` (`survey_id`,`status`,`submitted_at`),
  KEY `survey_responses_order_column_index` (`order_column`),
  KEY `survey_responses_respondent_ref_index` (`respondent_ref`),
  KEY `survey_responses_cursor_idx` (`survey_id`,`deleted_at`,`submitted_at`),
  KEY `survey_responses_survey_respondent_idx` (`survey_id`,`respondent_ref`),
  CONSTRAINT `survey_responses_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_results`;
/*!50001 DROP VIEW IF EXISTS `survey_results`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `survey_results` AS SELECT 
 1 AS `id`,
 1 AS `response_id`,
 1 AS `overall_score`,
 1 AS `maturity_level`,
 1 AS `assessment_code`,
 1 AS `weight_version`,
 1 AS `calculated_at`,
 1 AS `created_at`,
 1 AS `updated_at`*/;
SET character_set_client = @saved_cs_client;
DROP TABLE IF EXISTS `survey_sections`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_sections` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public UUID — expose ra ngoài, không phải PK',
  `order_column` int unsigned DEFAULT NULL COMMENT 'Thứ tự sắp xếp — Spatie Sortable / ORDER BY',
  `survey_id` bigint unsigned NOT NULL,
  `section_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Code định danh section — dùng cho sectioned aggregation',
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Gắn section vào assessment — dùng cho sectioned aggregation',
  `min_score` int NOT NULL DEFAULT '0' COMMENT 'Raw score thấp nhất lý thuyết (dùng cho normalize)',
  `max_score` int NOT NULL DEFAULT '100' COMMENT 'Raw score cao nhất lý thuyết (dùng cho normalize)',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tiêu đề section',
  `icon` varchar(16) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Icon hiển thị',
  `sort_order` smallint unsigned NOT NULL DEFAULT '0' COMMENT 'Thứ tự hiển thị',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_sections_uuid_unique` (`uuid`),
  KEY `survey_sections_survey_id_sort_order_index` (`survey_id`,`sort_order`),
  KEY `survey_sections_order_column_index` (`order_column`),
  CONSTRAINT `survey_sections_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public UUID — expose ra ngoài, không phải PK',
  `order_column` int unsigned DEFAULT NULL COMMENT 'Thứ tự sắp xếp — Spatie Sortable / ORDER BY',
  `survey_id` bigint unsigned NOT NULL,
  `name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tiêu đề',
  `token` varchar(80) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Lưu hash, hiển thị plaintext 1 lần',
  `token_encrypted` text COLLATE utf8mb4_unicode_ci COMMENT 'Token Encrypted',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Trạng thái hoạt động',
  `last_used_at` timestamp NULL DEFAULT NULL COMMENT 'Cập nhật mỗi lần token được dùng',
  `expires_at` timestamp NULL DEFAULT NULL COMMENT 'Cập nhật null là không hết hạn',
  `usage_limit` int unsigned DEFAULT NULL,
  `usage_count` int unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `survey_tokens_token_unique` (`token`),
  UNIQUE KEY `survey_tokens_uuid_unique` (`uuid`),
  KEY `survey_tokens_survey_id_index` (`survey_id`),
  KEY `survey_tokens_survey_id_is_active_index` (`survey_id`,`is_active`),
  KEY `survey_tokens_order_column_index` (`order_column`),
  KEY `survey_tokens_is_active_index` (`is_active`),
  CONSTRAINT `survey_tokens_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `survey_webhooks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `survey_webhooks` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `survey_id` bigint unsigned NOT NULL,
  `url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `secret` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `events` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `survey_webhooks_survey_id_index` (`survey_id`),
  CONSTRAINT `survey_webhooks_survey_id_foreign` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surveys` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public UUID — expose ra ngoài, không phải PK',
  `order_column` int unsigned DEFAULT NULL COMMENT 'Thứ tự sắp xếp — Spatie Sortable / ORDER BY',
  `title` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tiêu đề khảo sát',
  `slug` varchar(160) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Slug URL — unique',
  `assessment_code` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Mã định danh cho Scoring Engine — nullable nếu survey không có scoring',
  `lead_notify_email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Email nhận alert khi hot lead nộp bài — để trống = không gửi',
  `status` tinyint unsigned NOT NULL DEFAULT '0' COMMENT '0=draft 1=active 2=closed',
  `allow_multiple_responses` tinyint(1) NOT NULL DEFAULT '1',
  `version` smallint unsigned NOT NULL DEFAULT '1' COMMENT 'Phiên bản khảo sát',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `surveys_slug_unique` (`slug`),
  UNIQUE KEY `surveys_uuid_unique` (`uuid`),
  UNIQUE KEY `surveys_assessment_code_unique` (`assessment_code`),
  KEY `surveys_order_column_index` (`order_column`),
  KEY `surveys_status_index` (`status`),
  KEY `surveys_created_at_idx` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `user_role_scopes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_role_scopes` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `user_id` bigint unsigned NOT NULL,
  `role_id` bigint unsigned NOT NULL,
  `scope_branch_id` bigint unsigned DEFAULT NULL,
  `scope_dept_id` bigint unsigned DEFAULT NULL,
  `granted_by` bigint unsigned DEFAULT NULL,
  `granted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  `note` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_scope` (`user_id`,`role_id`,`scope_branch_id`,`scope_dept_id`),
  KEY `user_role_scopes_role_id_foreign` (`role_id`),
  KEY `user_role_scopes_scope_dept_id_foreign` (`scope_dept_id`),
  KEY `user_role_scopes_granted_by_foreign` (`granted_by`),
  KEY `idx_role_scopes_user` (`organization_id`,`user_id`),
  KEY `idx_role_scopes_branch` (`scope_branch_id`,`scope_dept_id`),
  KEY `idx_role_scopes_expires` (`expires_at`),
  CONSTRAINT `user_role_scopes_granted_by_foreign` FOREIGN KEY (`granted_by`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `user_role_scopes_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_role_scopes_role_id_foreign` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `user_role_scopes_scope_branch_id_foreign` FOREIGN KEY (`scope_branch_id`) REFERENCES `branches` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `user_role_scopes_scope_dept_id_foreign` FOREIGN KEY (`scope_dept_id`) REFERENCES `departments` (`id`) ON DELETE RESTRICT,
  CONSTRAINT `user_role_scopes_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `two_factor_secret` text COLLATE utf8mb4_unicode_ci,
  `two_factor_recovery_codes` text COLLATE utf8mb4_unicode_ci,
  `two_factor_confirmed_at` timestamp NULL DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `organization_id` bigint unsigned DEFAULT NULL,
  `branch_id` bigint unsigned DEFAULT NULL,
  `department_id` bigint unsigned DEFAULT NULL,
  `department_legacy` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Phòng ban: hr, sales, ops, marketing',
  `last_active_at` timestamp NULL DEFAULT NULL COMMENT 'Lần hoạt động cuối cùng',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Trạng thái tài khoản',
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_organization_id_index` (`organization_id`),
  KEY `users_department_index` (`department_legacy`),
  KEY `users_is_active_index` (`is_active`),
  KEY `fk_users_branch` (`branch_id`),
  KEY `fk_users_department` (`department_id`),
  CONSTRAINT `fk_users_branch` FOREIGN KEY (`branch_id`) REFERENCES `branches` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_users_department` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE SET NULL,
  CONSTRAINT `users_organization_id_foreign` FOREIGN KEY (`organization_id`) REFERENCES `organizations` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `wards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `wards` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` char(36) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Public UUID — expose ra ngoài, không phải PK',
  `order_column` int unsigned DEFAULT NULL COMMENT 'Thứ tự sắp xếp — Spatie Sortable / ORDER BY',
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tên phường/xã',
  `ward_code` char(5) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Mã phường/xã',
  `place_type` enum('phuong','xa','dac-khu') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'xa' COMMENT 'Loại: phường, xã, đặc khu',
  `province_code` char(2) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Tỉnh/thành phố liên kết — FK tới provinces.province_code',
  `is_active` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Trạng thái hoạt động',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `wards_ward_code_unique` (`ward_code`),
  UNIQUE KEY `wards_uuid_unique` (`uuid`),
  KEY `wards_province_code_index` (`province_code`),
  KEY `wards_order_column_index` (`order_column`),
  KEY `wards_name_index` (`name`),
  KEY `wards_place_type_index` (`place_type`),
  KEY `wards_is_active_index` (`is_active`),
  CONSTRAINT `wards_province_code_foreign` FOREIGN KEY (`province_code`) REFERENCES `provinces` (`province_code`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `workflow_conditions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_conditions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workflow_id` bigint unsigned NOT NULL,
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `field` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `operator` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value_type` tinyint unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workflow_conditions_workflow_id_sort_order_index` (`workflow_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `workflow_execution_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_execution_steps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `execution_id` bigint unsigned NOT NULL,
  `step_id` bigint unsigned NOT NULL,
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `action_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` tinyint unsigned NOT NULL,
  `error_message` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duration_ms` smallint unsigned DEFAULT NULL,
  `attempts` tinyint unsigned NOT NULL DEFAULT '1',
  `executed_at` datetime(3) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workflow_execution_steps_execution_id_sort_order_index` (`execution_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `workflow_executions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_executions` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workflow_id` bigint unsigned NOT NULL,
  `organization_id` bigint unsigned NOT NULL,
  `run_id` char(36) COLLATE utf8mb4_unicode_ci NOT NULL,
  `trigger_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_module` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_type` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `subject_id` bigint unsigned DEFAULT NULL,
  `actor_id` bigint unsigned DEFAULT NULL,
  `context` json DEFAULT NULL,
  `status` tinyint unsigned NOT NULL,
  `skip_reason` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `condition_result` tinyint(1) DEFAULT NULL,
  `steps_total` tinyint unsigned NOT NULL DEFAULT '0',
  `steps_success` tinyint unsigned NOT NULL DEFAULT '0',
  `steps_failed` tinyint unsigned NOT NULL DEFAULT '0',
  `steps_scheduled` tinyint unsigned NOT NULL DEFAULT '0',
  `duration_ms` smallint unsigned DEFAULT NULL,
  `triggered_at` datetime(3) NOT NULL,
  `executed_at` datetime(3) DEFAULT NULL,
  `finished_at` datetime(3) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `workflow_executions_run_id_unique` (`run_id`),
  KEY `workflow_executions_workflow_id_triggered_at_index` (`workflow_id`,`triggered_at`),
  KEY `workflow_executions_organization_id_triggered_at_index` (`organization_id`,`triggered_at`),
  KEY `workflow_executions_status_triggered_at_index` (`status`,`triggered_at`),
  KEY `workflow_executions_subject_type_subject_id_triggered_at_index` (`subject_type`,`subject_id`,`triggered_at`),
  KEY `workflow_executions_organization_id_index` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `workflow_step_headers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_step_headers` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `step_id` bigint unsigned NOT NULL,
  `header_key` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `header_value` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `workflow_step_headers_step_id_index` (`step_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `workflow_steps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_steps` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workflow_id` bigint unsigned NOT NULL,
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `action_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_to` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_subject` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email_template` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notif_title` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notif_body` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `notif_target` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `update_model` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `update_field` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `update_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_url` varchar(2000) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `webhook_method` tinyint unsigned DEFAULT NULL,
  `webhook_secret` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lead_status` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lead_source` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `lead_assigned_to` bigint unsigned DEFAULT NULL,
  `user_tag` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_status` varchar(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `delay_minutes` smallint unsigned NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `workflow_steps_workflow_id_sort_order_index` (`workflow_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `workflow_trigger_params`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflow_trigger_params` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `workflow_id` bigint unsigned NOT NULL,
  `param_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `param_value` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `param_type` tinyint unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_trigger_params` (`workflow_id`,`param_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
DROP TABLE IF EXISTS `workflows`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `workflows` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `organization_id` bigint unsigned NOT NULL,
  `name` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `trigger_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `condition_match` tinyint unsigned NOT NULL DEFAULT '1',
  `cooldown_type` tinyint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `priority` tinyint unsigned NOT NULL DEFAULT '5',
  `run_count` int unsigned NOT NULL DEFAULT '0',
  `last_run_at` datetime DEFAULT NULL,
  `last_run_status` tinyint unsigned DEFAULT NULL,
  `created_by` bigint unsigned DEFAULT NULL,
  `updated_by` bigint unsigned DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_org_trigger` (`organization_id`,`trigger_type`,`is_active`),
  KEY `idx_org_priority` (`organization_id`,`is_active`,`priority`),
  KEY `workflows_organization_id_index` (`organization_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;
/*!50001 DROP VIEW IF EXISTS `survey_results`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_unicode_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`root`@`localhost` SQL SECURITY DEFINER */
/*!50001 VIEW `survey_results` AS select `assessment_results`.`id` AS `id`,`assessment_results`.`subject_id` AS `response_id`,`assessment_results`.`overall_score` AS `overall_score`,`assessment_results`.`maturity_level` AS `maturity_level`,`assessment_results`.`assessment_code` AS `assessment_code`,`assessment_results`.`weight_version` AS `weight_version`,`assessment_results`.`calculated_at` AS `calculated_at`,`assessment_results`.`created_at` AS `created_at`,`assessment_results`.`updated_at` AS `updated_at` from `assessment_results` where (`assessment_results`.`subject_type` = 'Modules\\Survey\\Models\\SurveyResponse') */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (1,'0001_01_01_000000_create_organizations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (2,'0001_01_01_000000_create_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (3,'0001_01_01_000001_create_cache_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (4,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (5,'0002_01_01_000000_create_process_approval_flows_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (6,'0002_01_01_000001_create_process_approval_flow_steps_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (7,'0002_01_01_000002_create_process_approvals_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (8,'0002_01_01_000003_create_process_approval_statuses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (9,'0002_01_01_000004_add_tenant_ids_to_approval_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (10,'2026_05_13_020040_create_permission_tables',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (11,'2026_05_13_020054_create_activity_log_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (12,'2026_05_13_020107_create_media_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (13,'2026_05_13_064723_add_two_factor_columns_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (14,'2026_05_13_064803_create_personal_access_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (15,'2026_05_13_080046_create_plans_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (16,'2026_05_13_080047_create_plan_features_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (17,'2026_05_13_080048_create_plan_subscriptions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (18,'2026_05_13_080049_create_plan_subscription_usage_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (19,'2026_05_13_080050_remove_unique_slug_on_subscriptions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (20,'2026_05_13_080051_update_unique_keys_on_features_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (21,'2026_05_13_080052_remove_cancels_at_from_subscriptions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (22,'2026_05_22_045848_000001_create_regions_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (23,'2026_05_22_045848_000002_create_organization_members_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (24,'2026_05_22_045848_000003_create_organization_settings_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (25,'2026_05_22_045848_000004_create_surveys_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (26,'2026_05_22_045848_000005_create_provinces_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (27,'2026_05_22_045848_000006_create_survey_sections_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (28,'2026_05_22_045848_000007_create_survey_responses_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (29,'2026_05_22_045848_000008_create_survey_tokens_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (30,'2026_05_22_045848_000009_create_wards_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (31,'2026_05_22_045848_000010_create_survey_fields_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (32,'2026_05_22_045848_000011_create_survey_field_options_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (33,'2026_05_22_045848_000012_create_survey_answers_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (34,'2026_05_22_045848_000013_add_owner_id_and_tax_code_and_phone_to_organizations_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (35,'2026_05_22_045848_000014_add_organization_id_and_department_and_last_active_at_to_users_table',1);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (36,'2026_05_23_000001_add_listing_indexes_to_survey_tables',2);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (37,'2026_05_23_000000_000013_add_assessment_code_to_surveys_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (38,'2026_05_23_000000_000014_create_assessment_domains_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (39,'2026_05_23_000000_000015_create_maturity_levels_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (40,'2026_05_23_000000_000016_create_score_rules_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (41,'2026_05_23_000000_000017_create_score_rule_options_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (42,'2026_05_23_000000_000018_create_pain_point_rules_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (43,'2026_05_23_000000_000019_create_recommendation_rules_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (44,'2026_05_23_000000_000020_create_roadmap_phases_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (45,'2026_05_23_000000_000021_create_roadmap_milestones_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (46,'2026_05_23_000000_000022_create_survey_results_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (47,'2026_05_23_000000_000023_create_result_domain_scores_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (48,'2026_05_23_000000_000024_create_result_signal_flags_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (49,'2026_05_23_000000_000025_create_result_pain_points_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (50,'2026_05_23_000000_000026_create_result_recommendations_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (51,'2026_05_23_000000_000027_create_result_roadmap_phases_table',3);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (52,'2026_05_23_000000_000028_add_lead_temperature_to_scoring_tables',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (53,'2026_05_23_000000_000029_create_assessments_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (54,'2026_05_23_000000_000030_add_scoring_columns_to_score_rules_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (55,'2026_05_23_000000_000031_create_score_rule_numeric_ranges_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (56,'2026_05_23_000000_000032_add_option_label_to_score_rule_options',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (57,'2026_05_23_000000_000033_add_scoring_fields_to_survey_sections',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (58,'2026_05_23_000000_000034_create_feature_weights_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (59,'2026_05_23_000000_000035_create_feature_weight_history_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (60,'2026_05_23_000000_000036_create_score_bands_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (61,'2026_05_23_000000_000037_create_pass_fail_configs_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (62,'2026_05_23_000000_000038_create_personas_tables',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (63,'2026_05_23_000000_000039_create_submission_behavior_log_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (64,'2026_05_23_000000_000040_add_weight_version_to_survey_results',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (65,'2026_05_23_000000_000041_create_result_question_scores_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (66,'2026_05_23_000000_000042_create_result_classifications_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (67,'2026_05_23_000000_000043_add_band_code_to_roadmap_phases',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (68,'2026_05_23_000000_000044_create_scoring_feedback_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (69,'2026_05_23_000000_000045_create_tuning_tables',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (70,'2026_05_23_000000_000046_create_feedback_sources_config_table',4);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (71,'2026_05_23_000000_000047_create_job_positions_table',5);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (72,'2026_05_23_000000_000048_create_result_job_positions_table',6);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (73,'2026_05_23_000000_000049_add_behavior_columns_to_score_rules',7);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (74,'2026_05_24_000000_000050_drop_unused_scoring_infrastructure',8);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (75,'2026_05_25_000000_000051_make_score_rules_domain_code_nullable',9);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (76,'2026_05_25_000000_000052_make_pain_rec_label_nullable',10);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (77,'2026_05_25_000000_000053_drop_job_position_tables',11);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (78,'2026_05_25_000000_000054_add_missing_indexes',12);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (79,'2026_05_25_000000_000055_create_survey_drafts_table',13);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (80,'2026_05_25_000000_000056_create_survey_field_conditions_table',14);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (81,'2026_05_25_000000_000057_add_usage_limit_to_survey_tokens',15);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (82,'2026_05_25_000000_000058_add_allow_multiple_responses_to_surveys',16);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (83,'2026_05_25_000000_000059_create_assessment_config_snapshot_tables',17);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (84,'2026_05_25_000000_000060_create_survey_webhooks_table',18);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (85,'1_create_process_approval_flows_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (86,'2_create_process_approval_flow_steps_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (87,'3_create_process_approvals_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (88,'4_create_process_approval_statuses_table',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (89,'5_add_tenant_ids_to_approval_tables',19);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (90,'2026_05_25_000000_000061_create_survey_field_rows_table',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (91,'2026_05_25_000000_000062_add_row_key_to_survey_answers',20);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (92,'2026_05_26_000001_add_custom_columns_to_activity_log',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (93,'2026_05_26_000002_create_activity_log_contexts_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (94,'2026_05_26_000003_create_activity_log_http_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (95,'2026_05_26_000004_create_activity_log_alert_rules_table',21);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (96,'2026_01_01_000001_create_workflows_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (97,'2026_01_01_000002_create_workflow_trigger_params_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (98,'2026_01_01_000003_create_workflow_conditions_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (99,'2026_01_01_000004_create_workflow_steps_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (100,'2026_01_01_000005_create_workflow_step_headers_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (101,'2026_01_01_000006_create_workflow_executions_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (102,'2026_01_01_000007_create_workflow_execution_steps_table',22);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (103,'2026_05_28_000001_create_lead_pipeline_stages_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (104,'2026_05_28_000002_create_lead_sources_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (105,'2026_05_28_000003_create_lead_contacts_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (106,'2026_05_28_000004_create_leads_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (107,'2026_05_28_000005_create_lead_tag_definitions_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (108,'2026_05_28_000006_create_lead_tag_map_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (109,'2026_05_28_000007_create_lead_activities_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (110,'2026_05_28_000008_create_lead_notes_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (111,'2026_05_28_000009_create_lead_stage_history_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (112,'2026_05_28_000010_create_lead_meta_table',23);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (113,'2026_05_29_000000_000063_extract_assessment_results_table',24);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (114,'2026_05_29_000000_000064_add_lead_assessment_code_to_organizations',25);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (115,'2026_05_29_000000_000065_add_submitted_fields_to_scoring_feedback',26);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (116,'2026_05_29_000000_000065_create_result_question_selected_options_table',27);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (117,'2026_05_29_000000_000066_add_public_token_to_assessment_results_table',28);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (118,'2026_06_01_000001_add_context_to_workflow_executions_table',29);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (119,'2026_06_01_000002_add_soft_deletes_to_workflows_table',30);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (120,'2026_06_03_000001_create_branches_table',31);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (121,'2026_06_03_000002_create_departments_table',32);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (122,'2026_06_03_000003_create_job_titles_table',33);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (123,'2026_06_03_000004_create_employees_table',34);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (124,'2026_06_03_000005_alter_branches_add_manager_id',35);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (125,'2026_06_03_000006_alter_departments_add_head_ids',36);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (126,'2026_06_03_000007_alter_users_add_branch_department',37);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (127,'2026_06_03_000008_create_employee_departments_table',38);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (128,'2026_06_03_000009_create_employee_history_table',39);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (129,'2026_06_03_000010_create_user_role_scopes_table',40);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (130,'2026_06_03_000011_create_review_templates_table',41);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (131,'2026_06_03_000012_create_review_criteria_table',42);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (132,'2026_06_03_000013_create_performance_reviews_table',43);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (133,'2026_06_03_000014_create_review_scores_table',44);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (134,'2026_06_03_000015_create_projects_table',45);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (135,'2026_06_03_000016_create_project_members_table',46);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (136,'2026_06_03_000017_create_org_chart_configs_table',47);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (137,'2026_06_03_200001_create_kc_categories_table',48);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (138,'2026_06_03_200002_create_kc_items_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (139,'2026_06_03_200003_create_kc_item_attachments_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (140,'2026_06_03_200004_create_kc_tags_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (141,'2026_06_03_200005_create_kc_item_tags_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (142,'2026_06_03_200006_create_kc_version_histories_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (143,'2026_06_03_200007_create_kc_access_controls_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (144,'2026_06_03_200008_create_kc_feedbacks_table',49);
INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES (145,'2026_06_03_200009_create_kc_view_logs_table',49);
