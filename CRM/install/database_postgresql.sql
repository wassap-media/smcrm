-- PostgreSQL compatible database schema for RISE CRM
-- Converted from MySQL to PostgreSQL

-- Set timezone
SET timezone = 'UTC';

-- Create tables with PostgreSQL syntax
CREATE TABLE IF NOT EXISTS activity_logs (
  id SERIAL PRIMARY KEY,
  created_at TIMESTAMP NOT NULL,
  created_by INTEGER NOT NULL,
  action VARCHAR(50) CHECK (action IN ('created','updated','deleted','bitbucket_notification_received','github_notification_received')) NOT NULL,
  log_type VARCHAR(30) NOT NULL,
  log_type_title TEXT NOT NULL,
  log_type_id INTEGER NOT NULL DEFAULT 0,
  changes TEXT,
  log_for VARCHAR(30) NOT NULL DEFAULT '0',
  log_for_id INTEGER NOT NULL DEFAULT 0,
  log_for2 VARCHAR(30) DEFAULT NULL,
  log_for_id2 INTEGER DEFAULT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_activity_logs_log_for ON activity_logs(log_for, log_for_id);
CREATE INDEX IF NOT EXISTS idx_activity_logs_log_for2 ON activity_logs(log_for2, log_for_id2);
CREATE INDEX IF NOT EXISTS idx_activity_logs_log_type ON activity_logs(log_type, log_type_id);
CREATE INDEX IF NOT EXISTS idx_activity_logs_created_by ON activity_logs(created_by);

CREATE TABLE IF NOT EXISTS announcements (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  created_by INTEGER NOT NULL,
  share_with TEXT,
  created_at TIMESTAMP NOT NULL,
  files TEXT NOT NULL,
  read_by TEXT,
  deleted INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_announcements_created_by ON announcements(created_by);

CREATE TABLE IF NOT EXISTS article_helpful_status (
  id SERIAL PRIMARY KEY,
  article_id INTEGER NOT NULL,
  status VARCHAR(3) CHECK (status IN ('yes','no')) NOT NULL,
  created_by INTEGER NOT NULL DEFAULT 0,
  created_at TIMESTAMP NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS attendance (
  id SERIAL PRIMARY KEY,
  status VARCHAR(20) CHECK (status IN ('incomplete','pending','approved','rejected','deleted')) NOT NULL DEFAULT 'incomplete',
  user_id INTEGER NOT NULL,
  in_time TIMESTAMP NOT NULL,
  out_time TIMESTAMP DEFAULT NULL,
  checked_by INTEGER DEFAULT NULL,
  note TEXT,
  checked_at TIMESTAMP DEFAULT NULL,
  reject_reason TEXT,
  deleted INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX IF NOT EXISTS idx_attendance_user_id ON attendance(user_id);
CREATE INDEX IF NOT EXISTS idx_attendance_checked_by ON attendance(checked_by);

CREATE TABLE IF NOT EXISTS automation_settings (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  matching_type VARCHAR(20) CHECK (matching_type IN ('match_any','match_all')) NOT NULL,
  event_name TEXT NOT NULL,
  conditions TEXT NOT NULL,
  actions TEXT NOT NULL,
  related_to VARCHAR(255) NOT NULL,
  status VARCHAR(20) CHECK (status IN ('active','inactive')) NOT NULL DEFAULT 'active',
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS checklist_groups (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  checklists TEXT NOT NULL,
  deleted INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS checklist_items (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  is_checked INTEGER NOT NULL DEFAULT 0,
  task_id INTEGER NOT NULL DEFAULT 0,
  sort DOUBLE PRECISION NOT NULL DEFAULT 1,
  deleted INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS checklist_template (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL
);

-- Add more tables as needed...
-- This is a basic conversion - you may need to add more tables and adjust data types
