-- NVOTS Database Schema
DROP DATABASE IF EXISTS nvots;
CREATE DATABASE nvots CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nvots;

-- Users table (Super Admin, Registration Admin, Verification Admin)
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) UNIQUE,
  first_name VARCHAR(100) NULL,
  phone VARCHAR(20) UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('SUPER_ADMIN','REGISTRATION_ADMIN','VERIFICATION_ADMIN','BENEFICIARY') NOT NULL,
  avatar VARCHAR(255) NULL,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  last_login TIMESTAMP NULL
) ENGINE=InnoDB;

-- Settings table (key/value for platform configuration)
CREATE TABLE settings (
  `key` VARCHAR(100) PRIMARY KEY,
  `value` TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Vehicles table
CREATE TABLE vehicles (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vin VARCHAR(50) NOT NULL UNIQUE,
  engine_number VARCHAR(50) NOT NULL,
  chassis_number VARCHAR(50) NOT NULL,
  plate_number VARCHAR(20) NOT NULL UNIQUE,
  rfid_tag VARCHAR(100) DEFAULT NULL UNIQUE,
  qr_code VARCHAR(100) DEFAULT NULL UNIQUE,
  vehicle_status ENUM('PENDING','ACTIVE','SUSPENDED','STOLEN','DECOMMISSIONED') NOT NULL DEFAULT 'PENDING',
  manufacturer VARCHAR(100),
  model VARCHAR(100),
  vehicle_variant VARCHAR(100),
  year YEAR,
  production_date DATE NULL,
  assembly_plant VARCHAR(150) NULL,
  country_of_origin VARCHAR(100) NULL,
  country_of_manufacture VARCHAR(100) NULL,
  body_type VARCHAR(50) NULL,
  fuel_type VARCHAR(30),
  engine_capacity_cc INT NULL,
  horsepower INT NULL,
  torque_nm INT NULL,
  seating_capacity INT NULL,
  door_count INT NULL,
  weight_kg DECIMAL(10,2) NULL,
  length_mm INT NULL,
  width_mm INT NULL,
  height_mm INT NULL,
  wheelbase_mm INT NULL,
  color VARCHAR(30),
  transmission VARCHAR(30),
  category VARCHAR(50),
  class VARCHAR(50),
  image_path VARCHAR(255),
  custom_fields TEXT NULL,
  is_active TINYINT(1) DEFAULT 1,
  deleted_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Owners table
CREATE TABLE owners (
  id INT AUTO_INCREMENT PRIMARY KEY,
  full_name VARCHAR(150) NOT NULL,
  phone VARCHAR(20) NOT NULL UNIQUE,
  email VARCHAR(255) UNIQUE,
  date_of_birth DATE,
  gender ENUM('Male','Female','Other'),
  nationality VARCHAR(100),
  occupation VARCHAR(100),
  nin VARCHAR(20),
  bvn VARCHAR(20),
  address TEXT,
  state VARCHAR(100),
  lga VARCHAR(100),
  passport_photo_path VARCHAR(255),
  signature_path VARCHAR(255),
  custom_fields TEXT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Ownership History (immutable chain)
CREATE TABLE ownership_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  owner_id INT NOT NULL,
  purchase_date DATE NOT NULL,
  purchase_amount DECIMAL(15,2),
  market_name VARCHAR(150),
  seller_name VARCHAR(150),
  seller_phone VARCHAR(20),
  witness_name VARCHAR(150),
  middleman_name VARCHAR(150),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT,
  CONSTRAINT fk_owner FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Ownership Transfers (each transfer creates a new record in ownership_history)
CREATE TABLE ownership_transfers (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  seller_id INT NOT NULL,
  buyer_id INT NOT NULL,
  transfer_date DATE NOT NULL,
  sale_price DECIMAL(15,2),
  market_name VARCHAR(150),
  witness_name VARCHAR(150),
  middleman_name VARCHAR(150),
  approved_by INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_transfer_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT,
  CONSTRAINT fk_seller FOREIGN KEY (seller_id) REFERENCES owners(id) ON DELETE RESTRICT,
  CONSTRAINT fk_buyer FOREIGN KEY (buyer_id) REFERENCES owners(id) ON DELETE RESTRICT,
  CONSTRAINT fk_approver FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Payments table
CREATE TABLE payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  owner_id INT NOT NULL,
  amount DECIMAL(15,2) NOT NULL,
  payment_method ENUM('CASH','BANK_TRANSFER','PAYSTACK') NOT NULL,
  collected_by INT NOT NULL,
  receipt_number VARCHAR(100),
  paystack_reference VARCHAR(150) NULL,
  receipt_file VARCHAR(255) NULL COMMENT 'Path to uploaded receipt image or PDF',
  payment_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_payment_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT,
  CONSTRAINT fk_payment_owner FOREIGN KEY (owner_id) REFERENCES owners(id) ON DELETE RESTRICT,
  CONSTRAINT fk_collector FOREIGN KEY (collected_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Verification Records
CREATE TABLE verification_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  verifier_id INT NOT NULL,
  verification_type ENUM('VEHICLE','OWNERSHIP','DOCUMENT') NOT NULL,
  status ENUM('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  notes TEXT,
  verified_at TIMESTAMP NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_ver_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE RESTRICT,
  CONSTRAINT fk_verifier FOREIGN KEY (verifier_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Audit Logs (immutable)
CREATE TABLE audit_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  action VARCHAR(255) NOT NULL,
  ip_address VARCHAR(45),
  user_agent VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_audit_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Activity Logs (general system activities)
CREATE TABLE activity_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  description TEXT NOT NULL,
  performed_by INT,
  performed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_activity_user FOREIGN KEY (performed_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Dynamic Fields (custom fields for onboarding forms)
CREATE TABLE dynamic_fields (
  id INT AUTO_INCREMENT PRIMARY KEY,
  entity ENUM('vehicle','owner') NOT NULL,
  field_name VARCHAR(100) NOT NULL,
  field_type ENUM('text','number','dropdown','date','checkbox','radio','textarea','file') NOT NULL,
  options TEXT NULL,
  is_required TINYINT(1) DEFAULT 0,
  is_active TINYINT(1) DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Notifications table
CREATE TABLE notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  recipient_id INT NOT NULL,
  type ENUM('SMS','EMAIL','DASHBOARD') NOT NULL,
  payload TEXT NOT NULL,
  is_read TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_notif_user FOREIGN KEY (recipient_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

  -- Commission Recipients (configurable payees and their allocated share)
  CREATE TABLE commission_recipients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(255) NULL,
    bank_name VARCHAR(150) NULL,
    bank_code VARCHAR(50) NULL,
    account_number VARCHAR(50) NULL,
    account_name VARCHAR(150) NULL,
    percentage_share DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    paystack_recipient_code VARCHAR(150) NULL,
    total_paid DECIMAL(15,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
  ) ENGINE=InnoDB;

  -- Commission Payout Events (a revenue distribution occasion)
  CREATE TABLE commission_payouts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    revenue_amount DECIMAL(15,2) NOT NULL,
    processed_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
  ) ENGINE=InnoDB;

  -- Individual payout items per recipient per payout event
  CREATE TABLE commission_payout_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payout_id INT NOT NULL,
    recipient_id INT NOT NULL,
    amount DECIMAL(15,2) NOT NULL,
    status ENUM('PENDING','SUCCESS','FAILED') DEFAULT 'PENDING',
    paystack_transfer_id VARCHAR(150) NULL,
    response TEXT NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_payout_event FOREIGN KEY (payout_id) REFERENCES commission_payouts(id) ON DELETE CASCADE,
    CONSTRAINT fk_payout_recipient FOREIGN KEY (recipient_id) REFERENCES commission_recipients(id) ON DELETE CASCADE
  ) ENGINE=InnoDB;

-- Correction Requests table
CREATE TABLE IF NOT EXISTS correction_requests (
        id INT AUTO_INCREMENT PRIMARY KEY,
        entity_type ENUM('vehicle', 'owner') NOT NULL,
        entity_id INT NOT NULL,
        requested_by INT NOT NULL,
        amount DECIMAL(15,2) NOT NULL,
        payment_method ENUM('CASH', 'BANK_TRANSFER') NOT NULL,
        receipt_number VARCHAR(100),
        receipt_file VARCHAR(255) NULL,
        status ENUM('PENDING', 'VERIFIED', 'REJECTED') DEFAULT 'PENDING',
        verified_by INT NULL,
        verified_at TIMESTAMP NULL,
        is_corrected TINYINT(1) DEFAULT 0,
        corrected_at TIMESTAMP NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        CONSTRAINT fk_corr_req_user FOREIGN KEY (requested_by) REFERENCES users(id) ON DELETE CASCADE,
        CONSTRAINT fk_corr_req_verifier FOREIGN KEY (verified_by) REFERENCES users(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;

-- Extended National Registry Tables
CREATE TABLE IF NOT EXISTS vehicle_accidents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  accident_date DATE NOT NULL,
  location VARCHAR(255),
  severity ENUM('MINOR','MODERATE','SEVERE','TOTAL_LOSS') DEFAULT 'MINOR',
  report_number VARCHAR(100),
  damage_summary TEXT,
  estimated_repair_cost DECIMAL(15,2) DEFAULT 0.00,
  insurance_claim_reference VARCHAR(150),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_accident_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS service_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  service_date DATE NOT NULL,
  service_type VARCHAR(100),
  service_center VARCHAR(150),
  odometer_reading_km INT,
  service_cost DECIMAL(15,2) DEFAULT 0.00,
  service_notes TEXT,
  performed_by VARCHAR(150),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_service_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS insurance_policies (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  policy_number VARCHAR(100) NOT NULL,
  provider_name VARCHAR(150),
  policy_type VARCHAR(100),
  coverage_details TEXT,
  premium_amount DECIMAL(15,2) DEFAULT 0.00,
  start_date DATE,
  end_date DATE,
  status ENUM('ACTIVE','LAPSED','CANCELLED') DEFAULT 'ACTIVE',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_insurance_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS theft_reports (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  report_date DATE NOT NULL,
  reported_by_owner_id INT,
  report_number VARCHAR(100),
  police_station VARCHAR(150),
  location VARCHAR(255),
  status ENUM('OPEN','RECOVERED','CLOSED') DEFAULT 'OPEN',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_theft_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
  CONSTRAINT fk_theft_report_owner FOREIGN KEY (reported_by_owner_id) REFERENCES owners(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS recall_notifications (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  recall_number VARCHAR(100),
  manufacturer VARCHAR(150),
  recall_date DATE NOT NULL,
  affected_components TEXT,
  status ENUM('OPEN','ACTIONED','CLOSED') DEFAULT 'OPEN',
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_recall_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS inspection_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  inspection_date DATE NOT NULL,
  inspector_name VARCHAR(150),
  inspector_agency VARCHAR(150),
  result ENUM('PASS','FAIL','CONDITIONAL') DEFAULT 'PASS',
  remarks TEXT,
  next_due_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_inspection_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS modification_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  modification_date DATE NOT NULL,
  modified_by VARCHAR(150),
  description TEXT,
  approval_reference VARCHAR(150),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_modification_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS gps_track_points (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  tracked_at DATETIME NOT NULL,
  latitude DECIMAL(10,7),
  longitude DECIMAL(10,7),
  speed_kmh DECIMAL(8,2),
  source VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_gps_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS import_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  import_country VARCHAR(100),
  export_country VARCHAR(100),
  arrival_date DATE,
  departure_date DATE,
  departure_port VARCHAR(150),
  arrival_port VARCHAR(150),
  bill_of_lading VARCHAR(150),
  customs_cleared_by VARCHAR(150),
  cleared_at DATE,
  remarks TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_import_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS valuation_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  valuation_date DATE NOT NULL,
  appraised_value DECIMAL(15,2) DEFAULT 0.00,
  appraiser_name VARCHAR(150),
  appraiser_company VARCHAR(150),
  valuation_method VARCHAR(150),
  notes TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_valuation_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehicle_documents (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  document_type ENUM('REGISTRATION','INSURANCE','IMPORT','RECALL','INSPECTION','OTHER') NOT NULL,
  document_title VARCHAR(200),
  file_path VARCHAR(255),
  uploaded_by INT,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  expires_at DATE,
  remarks TEXT,
  CONSTRAINT fk_document_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
  CONSTRAINT fk_document_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS vehicle_photos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vehicle_id INT NOT NULL,
  photo_type ENUM('FRONT','REAR','SIDE','INTERIOR','DASHBOARD','LICENSE','OTHER') NOT NULL,
  file_path VARCHAR(255),
  caption VARCHAR(255),
  uploaded_by INT,
  uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_photo_vehicle FOREIGN KEY (vehicle_id) REFERENCES vehicles(id) ON DELETE CASCADE,
  CONSTRAINT fk_photo_uploader FOREIGN KEY (uploaded_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Indexes for fast lookup
CREATE INDEX idx_vehicle_vin ON vehicles(vin);
CREATE INDEX idx_vehicle_plate ON vehicles(plate_number);
CREATE INDEX idx_vehicle_rfid ON vehicles(rfid_tag);
CREATE INDEX idx_vehicle_qrcode ON vehicles(qr_code);
CREATE INDEX idx_vehicle_status ON vehicles(vehicle_status);
CREATE INDEX idx_owner_phone ON owners(phone);
CREATE INDEX idx_owner_nin ON owners(nin);

-- Sample admin user (password: Admin@123, hashed via password_hash)
INSERT INTO users (email, first_name, phone, password_hash, role, is_active) VALUES (
  'superadmin@example.com',
  'Super',
  '08000000001',
  '$2y$10$examplehashedpasswordstring',
  'SUPER_ADMIN',
  1
);
