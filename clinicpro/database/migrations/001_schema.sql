-- ClinicPro SaaS — Full Database Schema
-- MySQL 8.0+ | InnoDB | utf8mb4
-- Run this file once on fresh install

SET FOREIGN_KEY_CHECKS = 0;
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ============================================================
-- DATABASE
-- ============================================================
CREATE DATABASE IF NOT EXISTS `clinicpro`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `clinicpro`;

-- ============================================================
-- LICENSE KEYS
-- ============================================================
CREATE TABLE IF NOT EXISTS `license_keys` (
  `id`            INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `license_key`   VARCHAR(64)  NOT NULL UNIQUE,
  `domain`        VARCHAR(255) DEFAULT NULL,
  `ip_address`    VARCHAR(45)  DEFAULT NULL,
  `install_hash`  VARCHAR(64)  DEFAULT NULL,
  `plan`          ENUM('starter','professional','enterprise') NOT NULL DEFAULT 'starter',
  `max_doctors`   INT UNSIGNED NOT NULL DEFAULT 1,
  `max_patients`  INT UNSIGNED NOT NULL DEFAULT 500,
  `status`        ENUM('inactive','active','suspended','expired') NOT NULL DEFAULT 'inactive',
  `activated_at`  DATETIME     DEFAULT NULL,
  `expiry_date`   DATE         NOT NULL,
  `created_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- CLINICS (Tenants)
-- ============================================================
CREATE TABLE IF NOT EXISTS `clinics` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `license_id`      INT UNSIGNED NOT NULL,
  `name`            VARCHAR(255) NOT NULL,
  `slug`            VARCHAR(100) NOT NULL UNIQUE,
  `tagline`         VARCHAR(255) DEFAULT NULL,
  `logo`            VARCHAR(500) DEFAULT NULL,
  `favicon`         VARCHAR(500) DEFAULT NULL,
  `address`         TEXT         DEFAULT NULL,
  `city`            VARCHAR(100) DEFAULT NULL,
  `state`           VARCHAR(100) DEFAULT NULL,
  `pincode`         VARCHAR(10)  DEFAULT NULL,
  `country`         VARCHAR(100) DEFAULT 'India',
  `phone`           VARCHAR(20)  DEFAULT NULL,
  `email`           VARCHAR(255) DEFAULT NULL,
  `website`         VARCHAR(255) DEFAULT NULL,
  `gstin`           VARCHAR(20)  DEFAULT NULL,
  `registration_no` VARCHAR(50)  DEFAULT NULL,
  `currency`        VARCHAR(5)   NOT NULL DEFAULT 'INR',
  `currency_symbol` VARCHAR(5)   NOT NULL DEFAULT '₹',
  `timezone`        VARCHAR(50)  NOT NULL DEFAULT 'Asia/Kolkata',
  `theme_color`     VARCHAR(20)  NOT NULL DEFAULT '#0d6efd',
  `status`          ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`license_id`) REFERENCES `license_keys`(`id`) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- ROLES & PERMISSIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `roles` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`  INT UNSIGNED NOT NULL,
  `name`       VARCHAR(50)  NOT NULL,
  `slug`       VARCHAR(50)  NOT NULL,
  `is_system`  TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_clinic_role` (`clinic_id`, `slug`),
  FOREIGN KEY (`clinic_id`) REFERENCES `clinics`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `permissions` (
  `id`     INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `module` VARCHAR(50)  NOT NULL,
  `action` VARCHAR(50)  NOT NULL,
  `label`  VARCHAR(100) DEFAULT NULL,
  UNIQUE KEY `uq_module_action` (`module`, `action`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `role_permissions` (
  `role_id`       INT UNSIGNED NOT NULL,
  `permission_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`role_id`, `permission_id`),
  FOREIGN KEY (`role_id`)       REFERENCES `roles`(`id`)       ON DELETE CASCADE,
  FOREIGN KEY (`permission_id`) REFERENCES `permissions`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- USERS
-- ============================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id`                  INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`           INT UNSIGNED NOT NULL,
  `role_id`             INT UNSIGNED NOT NULL,
  `name`                VARCHAR(150) NOT NULL,
  `email`               VARCHAR(255) NOT NULL,
  `phone`               VARCHAR(20)  DEFAULT NULL,
  `password`            VARCHAR(255) NOT NULL,
  `avatar`              VARCHAR(500) DEFAULT NULL,
  `is_super_admin`      TINYINT(1)   NOT NULL DEFAULT 0,
  `status`              ENUM('active','inactive','locked') NOT NULL DEFAULT 'active',
  `email_verified_at`   DATETIME     DEFAULT NULL,
  `remember_token`      VARCHAR(100) DEFAULT NULL,
  `failed_attempts`     TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `locked_until`        DATETIME     DEFAULT NULL,
  `last_login_at`       DATETIME     DEFAULT NULL,
  `last_login_ip`       VARCHAR(45)  DEFAULT NULL,
  `two_factor_secret`   VARCHAR(100) DEFAULT NULL,
  `two_factor_enabled`  TINYINT(1)   NOT NULL DEFAULT 0,
  `created_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_email` (`email`),
  FOREIGN KEY (`clinic_id`) REFERENCES `clinics`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`role_id`)   REFERENCES `roles`(`id`)   ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- DOCTORS
-- ============================================================
CREATE TABLE IF NOT EXISTS `doctors` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`        INT UNSIGNED NOT NULL,
  `user_id`          INT UNSIGNED DEFAULT NULL,
  `name`             VARCHAR(150) NOT NULL,
  `specialization`   VARCHAR(150) DEFAULT NULL,
  `qualification`    VARCHAR(255) DEFAULT NULL,
  `registration_no`  VARCHAR(50)  DEFAULT NULL,
  `email`            VARCHAR(255) DEFAULT NULL,
  `phone`            VARCHAR(20)  DEFAULT NULL,
  `avatar`           VARCHAR(500) DEFAULT NULL,
  `experience_years` TINYINT UNSIGNED DEFAULT 0,
  `consultation_fee` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `commission_type`  ENUM('fixed','percentage') DEFAULT 'fixed',
  `commission_value` DECIMAL(10,2) DEFAULT 0.00,
  `bio`              TEXT         DEFAULT NULL,
  `signature`        VARCHAR(500) DEFAULT NULL,
  `status`           ENUM('active','inactive','on_leave') NOT NULL DEFAULT 'active',
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`clinic_id`) REFERENCES `clinics`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `doctor_schedules` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `doctor_id`   INT UNSIGNED NOT NULL,
  `day_of_week` TINYINT UNSIGNED NOT NULL COMMENT '0=Sun,1=Mon,...,6=Sat',
  `start_time`  TIME NOT NULL,
  `end_time`    TIME NOT NULL,
  `slot_mins`   TINYINT UNSIGNED NOT NULL DEFAULT 15,
  `max_slots`   TINYINT UNSIGNED NOT NULL DEFAULT 20,
  `is_active`   TINYINT(1) NOT NULL DEFAULT 1,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `doctor_leaves` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `doctor_id`   INT UNSIGNED NOT NULL,
  `leave_date`  DATE         NOT NULL,
  `reason`      VARCHAR(255) DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`doctor_id`) REFERENCES `doctors`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PATIENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `patients` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`        INT UNSIGNED NOT NULL,
  `patient_id`       VARCHAR(20)  NOT NULL COMMENT 'Like PT-000001',
  `name`             VARCHAR(150) NOT NULL,
  `dob`              DATE         DEFAULT NULL,
  `age`              TINYINT UNSIGNED DEFAULT NULL,
  `gender`           ENUM('male','female','other') NOT NULL DEFAULT 'male',
  `blood_group`      ENUM('A+','A-','B+','B-','AB+','AB-','O+','O-','unknown') DEFAULT 'unknown',
  `phone`            VARCHAR(20)  NOT NULL,
  `alternate_phone`  VARCHAR(20)  DEFAULT NULL,
  `email`            VARCHAR(255) DEFAULT NULL,
  `address`          TEXT         DEFAULT NULL,
  `city`             VARCHAR(100) DEFAULT NULL,
  `state`            VARCHAR(100) DEFAULT NULL,
  `pincode`          VARCHAR(10)  DEFAULT NULL,
  `emergency_name`   VARCHAR(150) DEFAULT NULL,
  `emergency_phone`  VARCHAR(20)  DEFAULT NULL,
  `emergency_relation` VARCHAR(50) DEFAULT NULL,
  `insurance_provider` VARCHAR(150) DEFAULT NULL,
  `insurance_number`   VARCHAR(100) DEFAULT NULL,
  `insurance_expiry`   DATE         DEFAULT NULL,
  `avatar`           VARCHAR(500) DEFAULT NULL,
  `notes`            TEXT         DEFAULT NULL,
  `status`           ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `registered_by`    INT UNSIGNED DEFAULT NULL,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_clinic_patient` (`clinic_id`, `patient_id`),
  INDEX `idx_phone` (`phone`),
  INDEX `idx_name`  (`name`),
  FOREIGN KEY (`clinic_id`)    REFERENCES `clinics`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`registered_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- APPOINTMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `appointments` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`       INT UNSIGNED NOT NULL,
  `appointment_no`  VARCHAR(20)  NOT NULL,
  `patient_id`      INT UNSIGNED NOT NULL,
  `doctor_id`       INT UNSIGNED NOT NULL,
  `appointment_date` DATE        NOT NULL,
  `appointment_time` TIME        NOT NULL,
  `token_no`        SMALLINT UNSIGNED DEFAULT NULL,
  `type`            ENUM('opd','online','walkin','followup') NOT NULL DEFAULT 'opd',
  `reason`          TEXT         DEFAULT NULL,
  `status`          ENUM('scheduled','confirmed','waiting','in_progress','completed','cancelled','no_show') NOT NULL DEFAULT 'scheduled',
  `cancelled_reason` TEXT        DEFAULT NULL,
  `consulted_at`    DATETIME     DEFAULT NULL,
  `booked_by`       INT UNSIGNED DEFAULT NULL,
  `notes`           TEXT         DEFAULT NULL,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_clinic_appt` (`clinic_id`, `appointment_no`),
  INDEX `idx_date`   (`appointment_date`),
  INDEX `idx_status` (`status`),
  FOREIGN KEY (`clinic_id`)  REFERENCES `clinics`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`)  ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`)  REFERENCES `doctors`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`booked_by`)  REFERENCES `users`(`id`)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- OPD VISITS
-- ============================================================
CREATE TABLE IF NOT EXISTS `opd_visits` (
  `id`               INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`        INT UNSIGNED NOT NULL,
  `appointment_id`   INT UNSIGNED DEFAULT NULL,
  `patient_id`       INT UNSIGNED NOT NULL,
  `doctor_id`        INT UNSIGNED NOT NULL,
  `visit_date`       DATE         NOT NULL,
  `chief_complaint`  TEXT         DEFAULT NULL,
  `symptoms`         TEXT         DEFAULT NULL,
  `examination`      TEXT         DEFAULT NULL,
  `diagnosis`        TEXT         DEFAULT NULL,
  `icd_code`         VARCHAR(20)  DEFAULT NULL,
  `treatment_plan`   TEXT         DEFAULT NULL,
  `notes`            TEXT         DEFAULT NULL,
  `follow_up_date`   DATE         DEFAULT NULL,
  `follow_up_notes`  TEXT         DEFAULT NULL,
  `vitals_bp`        VARCHAR(20)  DEFAULT NULL,
  `vitals_pulse`     TINYINT UNSIGNED DEFAULT NULL,
  `vitals_temp`      DECIMAL(4,1) DEFAULT NULL,
  `vitals_weight`    DECIMAL(5,2) DEFAULT NULL,
  `vitals_height`    DECIMAL(5,2) DEFAULT NULL,
  `vitals_spo2`      TINYINT UNSIGNED DEFAULT NULL,
  `vitals_rr`        TINYINT UNSIGNED DEFAULT NULL,
  `created_by`       INT UNSIGNED DEFAULT NULL,
  `created_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`       DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`clinic_id`)      REFERENCES `clinics`(`id`)      ON DELETE CASCADE,
  FOREIGN KEY (`appointment_id`) REFERENCES `appointments`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`patient_id`)     REFERENCES `patients`(`id`)     ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`)      REFERENCES `doctors`(`id`)      ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- MEDICINES
-- ============================================================
CREATE TABLE IF NOT EXISTS `medicines` (
  `id`           INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`    INT UNSIGNED NOT NULL,
  `name`         VARCHAR(200) NOT NULL,
  `generic_name` VARCHAR(200) DEFAULT NULL,
  `category`     VARCHAR(100) DEFAULT NULL,
  `type`         ENUM('tablet','capsule','syrup','injection','cream','drops','inhaler','other') DEFAULT 'tablet',
  `unit`         VARCHAR(20)  DEFAULT 'mg',
  `manufacturer` VARCHAR(150) DEFAULT NULL,
  `barcode`      VARCHAR(50)  DEFAULT NULL,
  `hsn_code`     VARCHAR(20)  DEFAULT NULL,
  `gst_percent`  DECIMAL(5,2) NOT NULL DEFAULT 12.00,
  `purchase_price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `selling_price`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `mrp`          DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `status`       ENUM('active','inactive') NOT NULL DEFAULT 'active',
  `created_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`clinic_id`) REFERENCES `clinics`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `medicine_stock` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`       INT UNSIGNED NOT NULL,
  `medicine_id`     INT UNSIGNED NOT NULL,
  `batch_no`        VARCHAR(50)  DEFAULT NULL,
  `expiry_date`     DATE         DEFAULT NULL,
  `quantity`        INT NOT NULL DEFAULT 0,
  `purchase_price`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `selling_price`   DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at`      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`clinic_id`)   REFERENCES `clinics`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`medicine_id`) REFERENCES `medicines`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- PRESCRIPTIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `prescriptions` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`      INT UNSIGNED NOT NULL,
  `prescription_no` VARCHAR(20) NOT NULL,
  `visit_id`       INT UNSIGNED DEFAULT NULL,
  `patient_id`     INT UNSIGNED NOT NULL,
  `doctor_id`      INT UNSIGNED NOT NULL,
  `prescription_date` DATE      NOT NULL,
  `diagnosis`      TEXT         DEFAULT NULL,
  `notes`          TEXT         DEFAULT NULL,
  `follow_up_date` DATE         DEFAULT NULL,
  `status`         ENUM('active','dispensed','cancelled') NOT NULL DEFAULT 'active',
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_clinic_rx` (`clinic_id`, `prescription_no`),
  FOREIGN KEY (`clinic_id`)  REFERENCES `clinics`(`id`)    ON DELETE CASCADE,
  FOREIGN KEY (`visit_id`)   REFERENCES `opd_visits`(`id`) ON DELETE SET NULL,
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`)  REFERENCES `doctors`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `prescription_items` (
  `id`              INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `prescription_id` INT UNSIGNED NOT NULL,
  `medicine_id`     INT UNSIGNED DEFAULT NULL,
  `medicine_name`   VARCHAR(200) NOT NULL,
  `dosage`          VARCHAR(100) DEFAULT NULL,
  `frequency`       VARCHAR(100) DEFAULT NULL,
  `duration`        VARCHAR(100) DEFAULT NULL,
  `route`           VARCHAR(50)  DEFAULT NULL,
  `instructions`    TEXT         DEFAULT NULL,
  `quantity`        SMALLINT UNSIGNED DEFAULT 1,
  `sort_order`      TINYINT UNSIGNED  DEFAULT 0,
  FOREIGN KEY (`prescription_id`) REFERENCES `prescriptions`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`medicine_id`)     REFERENCES `medicines`(`id`)     ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- LAB TESTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `lab_tests_master` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`   INT UNSIGNED NOT NULL,
  `name`        VARCHAR(200) NOT NULL,
  `short_name`  VARCHAR(50)  DEFAULT NULL,
  `category`    VARCHAR(100) DEFAULT NULL,
  `price`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `turnaround`  VARCHAR(50)  DEFAULT NULL COMMENT 'e.g. 2 hours',
  `normal_range` TEXT        DEFAULT NULL,
  `unit`        VARCHAR(30)  DEFAULT NULL,
  `status`      ENUM('active','inactive') NOT NULL DEFAULT 'active',
  FOREIGN KEY (`clinic_id`) REFERENCES `clinics`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lab_orders` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`   INT UNSIGNED NOT NULL,
  `order_no`    VARCHAR(20)  NOT NULL,
  `patient_id`  INT UNSIGNED NOT NULL,
  `doctor_id`   INT UNSIGNED NOT NULL,
  `visit_id`    INT UNSIGNED DEFAULT NULL,
  `order_date`  DATE         NOT NULL,
  `status`      ENUM('ordered','sample_collected','processing','completed','cancelled') NOT NULL DEFAULT 'ordered',
  `notes`       TEXT         DEFAULT NULL,
  `total_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_clinic_order` (`clinic_id`, `order_no`),
  FOREIGN KEY (`clinic_id`)  REFERENCES `clinics`(`id`)    ON DELETE CASCADE,
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`doctor_id`)  REFERENCES `doctors`(`id`)    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `lab_order_items` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `order_id`    INT UNSIGNED NOT NULL,
  `test_id`     INT UNSIGNED NOT NULL,
  `result`      TEXT         DEFAULT NULL,
  `reference`   TEXT         DEFAULT NULL,
  `unit`        VARCHAR(30)  DEFAULT NULL,
  `status`      ENUM('pending','completed') NOT NULL DEFAULT 'pending',
  `result_date` DATETIME     DEFAULT NULL,
  `price`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  FOREIGN KEY (`order_id`) REFERENCES `lab_orders`(`id`)      ON DELETE CASCADE,
  FOREIGN KEY (`test_id`)  REFERENCES `lab_tests_master`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- BILLING & INVOICES
-- ============================================================
CREATE TABLE IF NOT EXISTS `invoices` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`      INT UNSIGNED NOT NULL,
  `invoice_no`     VARCHAR(20)  NOT NULL,
  `patient_id`     INT UNSIGNED NOT NULL,
  `doctor_id`      INT UNSIGNED DEFAULT NULL,
  `visit_id`       INT UNSIGNED DEFAULT NULL,
  `invoice_date`   DATE         NOT NULL,
  `due_date`       DATE         DEFAULT NULL,
  `subtotal`       DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `discount_type`  ENUM('fixed','percentage') DEFAULT 'fixed',
  `discount_value` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount_amount` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `tax_amount`     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total_amount`   DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `paid_amount`    DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `balance_amount` DECIMAL(12,2) NOT NULL DEFAULT 0.00,
  `status`         ENUM('draft','pending','partial','paid','cancelled','refunded') NOT NULL DEFAULT 'draft',
  `notes`          TEXT         DEFAULT NULL,
  `created_by`     INT UNSIGNED DEFAULT NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_clinic_invoice` (`clinic_id`, `invoice_no`),
  INDEX `idx_patient` (`patient_id`),
  INDEX `idx_date`    (`invoice_date`),
  FOREIGN KEY (`clinic_id`)  REFERENCES `clinics`(`id`)    ON DELETE CASCADE,
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `invoice_items` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `invoice_id`  INT UNSIGNED NOT NULL,
  `item_type`   ENUM('consultation','procedure','medicine','lab','other') NOT NULL DEFAULT 'consultation',
  `description` VARCHAR(300) NOT NULL,
  `quantity`    DECIMAL(10,2) NOT NULL DEFAULT 1,
  `unit_price`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `discount`    DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `gst_percent` DECIMAL(5,2)  NOT NULL DEFAULT 0.00,
  `gst_amount`  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `total`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `sort_order`  TINYINT UNSIGNED DEFAULT 0,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `payments` (
  `id`             INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`      INT UNSIGNED NOT NULL,
  `invoice_id`     INT UNSIGNED NOT NULL,
  `payment_no`     VARCHAR(20)  NOT NULL,
  `amount`         DECIMAL(12,2) NOT NULL,
  `method`         ENUM('cash','card','upi','netbanking','cheque','insurance','other') NOT NULL DEFAULT 'cash',
  `reference_no`   VARCHAR(100) DEFAULT NULL,
  `payment_date`   DATE         NOT NULL,
  `notes`          TEXT         DEFAULT NULL,
  `created_by`     INT UNSIGNED DEFAULT NULL,
  `created_at`     DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_clinic_payment` (`clinic_id`, `payment_no`),
  FOREIGN KEY (`clinic_id`)  REFERENCES `clinics`(`id`)   ON DELETE CASCADE,
  FOREIGN KEY (`invoice_id`) REFERENCES `invoices`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- MEDICAL RECORDS / DOCUMENTS
-- ============================================================
CREATE TABLE IF NOT EXISTS `medical_documents` (
  `id`          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`   INT UNSIGNED NOT NULL,
  `patient_id`  INT UNSIGNED NOT NULL,
  `visit_id`    INT UNSIGNED DEFAULT NULL,
  `type`        ENUM('xray','lab_report','prescription','insurance','other') NOT NULL DEFAULT 'other',
  `title`       VARCHAR(255) NOT NULL,
  `file_path`   VARCHAR(500) NOT NULL,
  `file_size`   INT UNSIGNED DEFAULT NULL,
  `mime_type`   VARCHAR(100) DEFAULT NULL,
  `notes`       TEXT         DEFAULT NULL,
  `uploaded_by` INT UNSIGNED DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`clinic_id`)  REFERENCES `clinics`(`id`)    ON DELETE CASCADE,
  FOREIGN KEY (`patient_id`) REFERENCES `patients`(`id`)   ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SETTINGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `settings` (
  `id`         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`  INT UNSIGNED NOT NULL,
  `key`        VARCHAR(100) NOT NULL,
  `value`      TEXT         DEFAULT NULL,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uq_clinic_setting` (`clinic_id`, `key`),
  FOREIGN KEY (`clinic_id`) REFERENCES `clinics`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- AUDIT LOGS
-- ============================================================
CREATE TABLE IF NOT EXISTS `audit_logs` (
  `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`   INT UNSIGNED DEFAULT NULL,
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `action`      VARCHAR(50)  NOT NULL,
  `module`      VARCHAR(50)  NOT NULL,
  `record_id`   INT UNSIGNED DEFAULT NULL,
  `old_data`    JSON         DEFAULT NULL,
  `new_data`    JSON         DEFAULT NULL,
  `ip_address`  VARCHAR(45)  DEFAULT NULL,
  `user_agent`  VARCHAR(300) DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_module` (`module`, `record_id`),
  INDEX `idx_user`   (`user_id`),
  INDEX `idx_date`   (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- NOTIFICATIONS
-- ============================================================
CREATE TABLE IF NOT EXISTS `notifications` (
  `id`          BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `clinic_id`   INT UNSIGNED NOT NULL,
  `user_id`     INT UNSIGNED DEFAULT NULL,
  `type`        VARCHAR(50)  NOT NULL,
  `title`       VARCHAR(255) NOT NULL,
  `message`     TEXT         DEFAULT NULL,
  `data`        JSON         DEFAULT NULL,
  `is_read`     TINYINT(1)   NOT NULL DEFAULT 0,
  `read_at`     DATETIME     DEFAULT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_user_read` (`user_id`, `is_read`),
  FOREIGN KEY (`clinic_id`) REFERENCES `clinics`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- SEEDS — Default data
-- ============================================================

-- Permissions
INSERT INTO `permissions` (`module`, `action`, `label`) VALUES
('patients','create','Create Patients'),
('patients','read','View Patients'),
('patients','update','Edit Patients'),
('patients','delete','Delete Patients'),
('patients','export','Export Patients'),
('doctors','create','Create Doctors'),
('doctors','read','View Doctors'),
('doctors','update','Edit Doctors'),
('doctors','delete','Delete Doctors'),
('appointments','create','Create Appointments'),
('appointments','read','View Appointments'),
('appointments','update','Edit Appointments'),
('appointments','cancel','Cancel Appointments'),
('billing','create','Create Invoices'),
('billing','read','View Invoices'),
('billing','update','Edit Invoices'),
('billing','print','Print Invoices'),
('billing','refund','Refund'),
('prescriptions','create','Write Prescriptions'),
('prescriptions','read','View Prescriptions'),
('prescriptions','print','Print Prescriptions'),
('opd','create','Create OPD Visit'),
('opd','read','View OPD'),
('opd','update','Edit OPD'),
('lab','create','Create Lab Orders'),
('lab','read','View Lab'),
('lab','results','Enter Lab Results'),
('pharmacy','dispense','Dispense Medicines'),
('pharmacy','stock','Manage Stock'),
('reports','view','View Reports'),
('reports','export','Export Reports'),
('settings','manage','Manage Settings'),
('users','manage','Manage Users');

-- Default license (for demo install)
INSERT INTO `license_keys` (`license_key`,`plan`,`max_doctors`,`max_patients`,`status`,`expiry_date`) VALUES
('DEMO-CLINIC-PRO-2024-XXXX','enterprise',999,999999,'active','2099-12-31');

SET FOREIGN_KEY_CHECKS = 1;
