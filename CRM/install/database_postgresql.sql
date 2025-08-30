-- Complete Supabase PostgreSQL Schema for RISE CRM
-- Converted from MySQL schema

-- Enable required extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- Set timezone
SET timezone = 'UTC';

-- Create tables with PostgreSQL syntax
CREATE TABLE IF NOT EXISTS activity_logs (
  id SERIAL PRIMARY KEY,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  created_by INTEGER NOT NULL,
  action VARCHAR(50) NOT NULL CHECK (action IN ('created','updated','deleted','bitbucket_notification_received','github_notification_received')),
  log_type VARCHAR(30) NOT NULL,
  log_type_title TEXT NOT NULL,
  log_type_id INTEGER NOT NULL DEFAULT 0,
  changes TEXT,
  log_for VARCHAR(30) NOT NULL DEFAULT '0',
  log_for_id INTEGER NOT NULL DEFAULT 0,
  log_for2 VARCHAR(30),
  log_for_id2 INTEGER,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_activity_logs_log_for ON activity_logs(log_for, log_for_id);
CREATE INDEX idx_activity_logs_log_for2 ON activity_logs(log_for2, log_for_id2);
CREATE INDEX idx_activity_logs_log_type ON activity_logs(log_type, log_type_id);
CREATE INDEX idx_activity_logs_created_by ON activity_logs(created_by);

CREATE TABLE IF NOT EXISTS announcements (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  created_by INTEGER NOT NULL,
  share_with TEXT,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  files TEXT NOT NULL,
  read_by TEXT,
  deleted INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX idx_announcements_created_by ON announcements(created_by);

CREATE TABLE IF NOT EXISTS article_helpful_status (
  id SERIAL PRIMARY KEY,
  article_id INTEGER NOT NULL,
  status VARCHAR(3) NOT NULL CHECK (status IN ('yes','no')),
  created_by INTEGER NOT NULL DEFAULT 0,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS attendance (
  id SERIAL PRIMARY KEY,
  status VARCHAR(20) NOT NULL DEFAULT 'incomplete' CHECK (status IN ('incomplete','pending','approved','rejected','deleted')),
  user_id INTEGER NOT NULL,
  in_time TIMESTAMP WITH TIME ZONE NOT NULL,
  out_time TIMESTAMP WITH TIME ZONE,
  checked_by INTEGER,
  note TEXT,
  checked_at TIMESTAMP WITH TIME ZONE,
  reject_reason TEXT,
  deleted INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX idx_attendance_user_id ON attendance(user_id);
CREATE INDEX idx_attendance_checked_by ON attendance(checked_by);

CREATE TABLE IF NOT EXISTS automation_settings (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  matching_type VARCHAR(20) NOT NULL CHECK (matching_type IN ('match_any','match_all')),
  event_name TEXT NOT NULL,
  conditions TEXT NOT NULL,
  actions TEXT NOT NULL,
  related_to VARCHAR(255) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active','inactive')),
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
  title TEXT NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);


CREATE TABLE IF NOT EXISTS ci_sessions (
  id VARCHAR(128) PRIMARY KEY,
  ip_address VARCHAR(45) NOT NULL,
  timestamp TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  data BYTEA NOT NULL
);

CREATE INDEX idx_ci_sessions_timestamp ON ci_sessions(timestamp);

CREATE TABLE IF NOT EXISTS client_groups (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS client_wallet (
  id SERIAL PRIMARY KEY,
  amount DOUBLE PRECISION NOT NULL,
  payment_date DATE NOT NULL,
  note TEXT,
  client_id INTEGER NOT NULL,
  created_by INTEGER DEFAULT 1,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_client_wallet_id ON client_wallet(id);

CREATE TABLE IF NOT EXISTS clients (
  id SERIAL PRIMARY KEY,
  company_name VARCHAR(150) NOT NULL,
  type VARCHAR(20) NOT NULL DEFAULT 'organization' CHECK (type IN ('organization','person')),
  address TEXT,
  city VARCHAR(50),
  state VARCHAR(50),
  zip VARCHAR(50),
  country VARCHAR(50),
  created_date TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  website TEXT,
  phone VARCHAR(20),
  currency_symbol VARCHAR(20),
  starred_by TEXT NOT NULL,
  group_ids TEXT NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  is_lead BOOLEAN NOT NULL DEFAULT FALSE,
  lead_status_id INTEGER NOT NULL,
  owner_id INTEGER NOT NULL,
  created_by INTEGER NOT NULL,
  sort INTEGER NOT NULL DEFAULT 0,
  lead_source_id INTEGER NOT NULL,
  last_lead_status TEXT NOT NULL,
  client_migration_date DATE,
  vat_number TEXT,
  gst_number TEXT,
  stripe_customer_id TEXT NOT NULL,
  stripe_card_ending_digit INTEGER NOT NULL,
  currency VARCHAR(3),
  disable_online_payment BOOLEAN NOT NULL DEFAULT FALSE,
  labels TEXT,
  managers TEXT NOT NULL
);

CREATE INDEX idx_clients_owner_id ON clients(owner_id);
CREATE INDEX idx_clients_created_by ON clients(created_by);
CREATE INDEX idx_clients_lead_source_id ON clients(lead_source_id);
CREATE INDEX idx_clients_is_lead ON clients(is_lead);

CREATE TABLE IF NOT EXISTS company (
  id SERIAL PRIMARY KEY,
  name TEXT NOT NULL,
  address TEXT NOT NULL,
  phone TEXT NOT NULL,
  email TEXT NOT NULL,
  website TEXT NOT NULL,
  vat_number TEXT NOT NULL,
  gst_number TEXT NOT NULL,
  is_default BOOLEAN NOT NULL DEFAULT FALSE,
  logo TEXT NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

-- Insert default company
INSERT INTO company (id, name, address, phone, email, website, vat_number, gst_number, is_default, logo, deleted) 
VALUES (1, 'Company Name', '', '', '', '', '', '', TRUE, '', FALSE);

CREATE TABLE IF NOT EXISTS contract_items (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  quantity DOUBLE PRECISION NOT NULL,
  unit_type VARCHAR(20) NOT NULL DEFAULT '',
  rate DOUBLE PRECISION NOT NULL,
  total DOUBLE PRECISION NOT NULL,
  sort INTEGER NOT NULL DEFAULT 0,
  contract_id INTEGER NOT NULL,
  item_id INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS contract_templates (
  id SERIAL PRIMARY KEY,
  title VARCHAR(50) NOT NULL,
  template TEXT,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

-- Insert default contract template
INSERT INTO contract_templates (id, title, template, deleted) 
VALUES (1, 'Template 3.7', '<p>&nbsp;</p>
<table class="table" style="background-color: #3d3d3d; color: #ffffff; width: 100%;">
<tbody>
<tr>
<td style="text-align: center; width: 100%;">
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<div><span style="font-size: 40px;"><strong>{CONTRACT_TITLE}</strong></span></div>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
<p>&nbsp;</p>
</td>
</tr>
</tbody>
</table>
<p style="text-align: justify;">&nbsp;</p>
<p style="text-align: justify;">This contract states the terms and conditions that shall govern the contractual agreement between {COMPANY_NAME} (the Service Provider) and {CONTRACT_TO_COMPANY_NAME} (the Client) who agrees to be bound by the terms of the contract.</p>
<p style="text-align: justify;">&nbsp;</p>
<p style="text-align: justify;">{CONTRACT_NOTE}</p>', FALSE);

CREATE TABLE IF NOT EXISTS contracts (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  client_id INTEGER NOT NULL,
  project_id INTEGER NOT NULL,
  contract_date DATE NOT NULL,
  valid_until DATE NOT NULL,
  note TEXT,
  last_email_sent_date DATE,
  status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','sent','accepted','declined')),
  tax_id INTEGER NOT NULL DEFAULT 0,
  tax_id2 INTEGER NOT NULL DEFAULT 0,
  discount_type VARCHAR(20) NOT NULL CHECK (discount_type IN ('before_tax','after_tax')),
  discount_amount DOUBLE PRECISION NOT NULL,
  discount_amount_type VARCHAR(20) NOT NULL CHECK (discount_amount_type IN ('percentage','fixed_amount')),
  content TEXT NOT NULL,
  public_key VARCHAR(10) NOT NULL,
  accepted_by INTEGER NOT NULL DEFAULT 0,
  staff_signed_by INTEGER NOT NULL DEFAULT 0,
  meta_data TEXT NOT NULL,
  files TEXT NOT NULL,
  company_id INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE TABLE IF NOT EXISTS custom_field_values (
  id SERIAL PRIMARY KEY,
  related_to_type VARCHAR(50) NOT NULL,
  related_to_id INTEGER NOT NULL,
  custom_field_id INTEGER NOT NULL,
  value TEXT NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_custom_field_values_related_to_type ON custom_field_values(related_to_type);
CREATE INDEX idx_custom_field_values_related_to_id ON custom_field_values(related_to_id);
CREATE INDEX idx_custom_field_values_custom_field_id ON custom_field_values(custom_field_id);

CREATE TABLE IF NOT EXISTS custom_fields (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  title_language_key TEXT NOT NULL,
  placeholder_language_key TEXT NOT NULL,
  show_in_embedded_form BOOLEAN NOT NULL DEFAULT FALSE,
  placeholder TEXT NOT NULL,
  template_variable_name TEXT,
  options TEXT NOT NULL,
  field_type VARCHAR(50) NOT NULL,
  related_to VARCHAR(50) NOT NULL,
  sort INTEGER NOT NULL,
  required BOOLEAN NOT NULL DEFAULT FALSE,
  add_filter BOOLEAN NOT NULL DEFAULT FALSE,
  show_in_table BOOLEAN NOT NULL DEFAULT FALSE,
  show_in_invoice BOOLEAN NOT NULL DEFAULT FALSE,
  show_in_estimate BOOLEAN NOT NULL DEFAULT FALSE,
  show_in_contract BOOLEAN NOT NULL DEFAULT FALSE,
  show_in_order BOOLEAN NOT NULL DEFAULT FALSE,
  show_in_proposal BOOLEAN NOT NULL DEFAULT FALSE,
  visible_to_admins_only BOOLEAN NOT NULL DEFAULT FALSE,
  hide_from_clients BOOLEAN NOT NULL DEFAULT FALSE,
  disable_editing_by_clients BOOLEAN NOT NULL DEFAULT FALSE,
  show_on_kanban_card BOOLEAN NOT NULL DEFAULT FALSE,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  show_in_subscription BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_custom_fields_related_to ON custom_fields(related_to);
CREATE INDEX idx_custom_fields_field_type ON custom_fields(field_type);

CREATE TABLE IF NOT EXISTS custom_widgets (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL,
  title TEXT,
  content TEXT,
  show_title BOOLEAN NOT NULL DEFAULT FALSE,
  show_border BOOLEAN NOT NULL DEFAULT FALSE,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_custom_widgets_user_id ON custom_widgets(user_id);

CREATE TABLE IF NOT EXISTS dashboards (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL,
  title TEXT,
  data TEXT,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_dashboards_user_id ON dashboards(user_id);

-- Add more tables as needed...
-- This covers the main tables from your MySQL schema



CREATE TABLE IF NOT EXISTS e_invoice_templates (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  template TEXT,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS email_templates (
  id SERIAL PRIMARY KEY,
  template_name VARCHAR(50) NOT NULL,
  email_subject TEXT NOT NULL,
  default_message TEXT NOT NULL,
  custom_message TEXT,
  template_type VARCHAR(20) NOT NULL DEFAULT 'default' CHECK (template_type IN ('default','custom')),
  language VARCHAR(50) NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS estimate_comments (
  id SERIAL PRIMARY KEY,
  created_by INTEGER NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  description TEXT NOT NULL,
  estimate_id INTEGER NOT NULL DEFAULT 0,
  files TEXT,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS estimate_forms (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  status VARCHAR(20) NOT NULL CHECK (status IN ('active','inactive')),
  assigned_to INTEGER NOT NULL,
  public BOOLEAN NOT NULL DEFAULT FALSE,
  enable_attachment BOOLEAN NOT NULL DEFAULT FALSE,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS estimate_items (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  quantity DOUBLE PRECISION NOT NULL,
  unit_type VARCHAR(20) NOT NULL DEFAULT '',
  rate DOUBLE PRECISION NOT NULL,
  total DOUBLE PRECISION NOT NULL,
  sort INTEGER NOT NULL DEFAULT 0,
  estimate_id INTEGER NOT NULL,
  item_id INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS estimate_requests (
  id SERIAL PRIMARY KEY,
  estimate_form_id INTEGER NOT NULL,
  created_by INTEGER NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  client_id INTEGER NOT NULL,
  lead_id INTEGER NOT NULL,
  assigned_to INTEGER NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'new' CHECK (status IN ('new','processing','hold','canceled','estimated')),
  files TEXT NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS estimates (
  id SERIAL PRIMARY KEY,
  client_id INTEGER NOT NULL,
  estimate_request_id INTEGER NOT NULL DEFAULT 0,
  estimate_date DATE NOT NULL,
  valid_until DATE NOT NULL,
  note TEXT,
  last_email_sent_date DATE,
  status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','sent','accepted','declined')),
  tax_id INTEGER NOT NULL DEFAULT 0,
  tax_id2 INTEGER NOT NULL DEFAULT 0,
  discount_type VARCHAR(20) NOT NULL CHECK (discount_type IN ('before_tax','after_tax')),
  discount_amount DOUBLE PRECISION NOT NULL,
  discount_amount_type VARCHAR(20) NOT NULL CHECK (discount_amount_type IN ('percentage','fixed_amount')),
  project_id INTEGER NOT NULL DEFAULT 0,
  accepted_by INTEGER NOT NULL DEFAULT 0,
  meta_data TEXT NOT NULL,
  created_by INTEGER NOT NULL,
  signature TEXT NOT NULL,
  public_key TEXT NOT NULL,
  company_id INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS event_tracker (
  id SERIAL PRIMARY KEY,
  event_type VARCHAR(255) NOT NULL,
  context VARCHAR(255) NOT NULL,
  context_id INTEGER NOT NULL,
  read_count INTEGER DEFAULT NULL,
  status VARCHAR(20) DEFAULT 'new' CHECK (status IN ('new','read')),
  last_read_time TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  logs TEXT,
  random_id VARCHAR(10) NOT NULL,
  deleted INTEGER DEFAULT 0
);



CREATE TABLE IF NOT EXISTS events (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE DEFAULT NULL,
  start_time TIME DEFAULT NULL,
  end_time TIME DEFAULT NULL,
  created_by INTEGER NOT NULL,
  location TEXT,
  client_id INTEGER NOT NULL DEFAULT 0,
  labels TEXT NOT NULL,
  share_with TEXT,
  editable_google_event BOOLEAN NOT NULL DEFAULT FALSE,
  google_event_id TEXT NOT NULL,
  deleted INTEGER NOT NULL DEFAULT 0,
  lead_id INTEGER NOT NULL DEFAULT 0,
  ticket_id INTEGER NOT NULL DEFAULT 0,
  project_id INTEGER NOT NULL DEFAULT 0,
  task_id INTEGER NOT NULL DEFAULT 0,
  proposal_id INTEGER NOT NULL DEFAULT 0,
  contract_id INTEGER NOT NULL DEFAULT 0,
  subscription_id INTEGER NOT NULL DEFAULT 0,
  invoice_id INTEGER NOT NULL DEFAULT 0,
  order_id INTEGER NOT NULL DEFAULT 0,
  estimate_id INTEGER NOT NULL DEFAULT 0,
  related_user_id INTEGER NOT NULL DEFAULT 0,
  next_recurring_time TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  no_of_cycles_completed INTEGER NOT NULL DEFAULT 0,
  snoozing_time TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  reminder_status VARCHAR(20) NOT NULL DEFAULT 'new' CHECK (reminder_status IN ('new','shown','done')),
  type VARCHAR(20) NOT NULL DEFAULT 'event' CHECK (type IN ('event','reminder')),
  color VARCHAR(15) NOT NULL,
  recurring INTEGER NOT NULL DEFAULT 0,
  repeat_every INTEGER NOT NULL DEFAULT 0,
  repeat_type VARCHAR(10) DEFAULT NULL CHECK (repeat_type IN ('days','weeks','months','years')),
  no_of_cycles INTEGER NOT NULL DEFAULT 0,
  last_start_date DATE DEFAULT NULL,
  recurring_dates TEXT NOT NULL,
  confirmed_by TEXT NOT NULL,
  rejected_by TEXT NOT NULL,
  files TEXT NOT NULL
);

CREATE INDEX idx_events_created_by ON events(created_by);
CREATE INDEX idx_events_project_id ON events(project_id);
CREATE INDEX idx_events_task_id ON events(task_id);
CREATE INDEX idx_events_recurring ON events(recurring);
CREATE INDEX idx_events_start_date ON events(start_date);
CREATE INDEX idx_events_end_date ON events(end_date);



CREATE TABLE IF NOT EXISTS expense_categories (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS expenses (
  id SERIAL PRIMARY KEY,
  expense_date DATE NOT NULL,
  category_id INTEGER NOT NULL,
  description TEXT,
  amount DOUBLE PRECISION NOT NULL,
  files TEXT NOT NULL,
  title TEXT NOT NULL,
  project_id INTEGER NOT NULL DEFAULT 0,
  user_id INTEGER NOT NULL DEFAULT 0,
  tax_id INTEGER NOT NULL DEFAULT 0,
  tax_id2 INTEGER NOT NULL DEFAULT 0,
  client_id INTEGER NOT NULL DEFAULT 0,
  recurring BOOLEAN NOT NULL DEFAULT FALSE,
  recurring_expense_id BOOLEAN NOT NULL DEFAULT FALSE,
  repeat_every INTEGER NOT NULL DEFAULT 0,
  repeat_type VARCHAR(10) DEFAULT NULL CHECK (repeat_type IN ('days','weeks','months','years')),
  no_of_cycles INTEGER NOT NULL DEFAULT 0,
  next_recurring_date DATE DEFAULT NULL,
  no_of_cycles_completed INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  created_by INTEGER NOT NULL
);

CREATE INDEX idx_expenses_category_id ON expenses(category_id);



CREATE TABLE IF NOT EXISTS file_category (
  id SERIAL PRIMARY KEY,
  name TEXT,
  type VARCHAR(20) NOT NULL DEFAULT 'project' CHECK (type IN ('project')),
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS folders (
  id SERIAL PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  folder_id VARCHAR(255) NOT NULL,
  parent_id INTEGER NOT NULL,
  level TEXT,
  created_by INTEGER DEFAULT NULL,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  permissions TEXT,
  context VARCHAR(255) NOT NULL,
  context_id INTEGER NOT NULL,
  starred_by TEXT NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS general_files (
  id SERIAL PRIMARY KEY,
  file_name TEXT NOT NULL,
  file_id TEXT,
  service_type VARCHAR(20) DEFAULT NULL,
  description TEXT,
  file_size DOUBLE PRECISION NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  client_id INTEGER NOT NULL DEFAULT 0,
  user_id INTEGER NOT NULL DEFAULT 0,
  uploaded_by INTEGER NOT NULL,
  folder_id INTEGER DEFAULT 0,
  context VARCHAR(100) NOT NULL,
  context_id INTEGER DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS help_articles (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  category_id INTEGER NOT NULL,
  created_by INTEGER NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active','inactive')),
  files TEXT NOT NULL,
  total_views INTEGER NOT NULL DEFAULT 0,
  sort INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  labels TEXT
);



CREATE TABLE IF NOT EXISTS help_categories (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  type VARCHAR(20) NOT NULL CHECK (type IN ('help','knowledge_base')),
  sort INTEGER NOT NULL,
  articles_order VARCHAR(3) NOT NULL DEFAULT '',
  status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active','inactive')),
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  related_articles TEXT,
  banner_image TEXT NOT NULL,
  banner_url TEXT
);



CREATE TABLE IF NOT EXISTS invoice_items (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  quantity DOUBLE PRECISION NOT NULL,
  unit_type VARCHAR(20) NOT NULL DEFAULT '',
  rate DOUBLE PRECISION NOT NULL,
  total DOUBLE PRECISION NOT NULL,
  sort INTEGER NOT NULL DEFAULT 0,
  invoice_id INTEGER NOT NULL,
  item_id INTEGER NOT NULL DEFAULT 0,
  taxable BOOLEAN NOT NULL DEFAULT TRUE,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS invoice_payments (
  id SERIAL PRIMARY KEY,
  amount DOUBLE PRECISION NOT NULL,
  payment_date DATE NOT NULL,
  payment_method_id INTEGER NOT NULL,
  note TEXT,
  invoice_id INTEGER NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  transaction_id TEXT,
  created_by INTEGER DEFAULT 1,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW()
);

CREATE INDEX idx_invoice_payments_id ON invoice_payments(id);



CREATE TABLE IF NOT EXISTS invoices (
  id SERIAL PRIMARY KEY,
  type VARCHAR(20) NOT NULL DEFAULT 'invoice' CHECK (type IN ('invoice','credit_note')),
  client_id INTEGER NOT NULL,
  project_id INTEGER NOT NULL DEFAULT 0,
  bill_date DATE NOT NULL,
  due_date DATE NOT NULL,
  note TEXT,
  labels TEXT,
  last_email_sent_date DATE DEFAULT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','not_paid','cancelled','credited')),
  tax_id INTEGER NOT NULL DEFAULT 0,
  tax_id2 INTEGER NOT NULL DEFAULT 0,
  tax_id3 INTEGER NOT NULL DEFAULT 0,
  recurring BOOLEAN NOT NULL DEFAULT FALSE,
  recurring_invoice_id INTEGER NOT NULL DEFAULT 0,
  repeat_every INTEGER NOT NULL DEFAULT 0,
  repeat_type VARCHAR(10) DEFAULT NULL CHECK (repeat_type IN ('days','weeks','months','years')),
  no_of_cycles INTEGER NOT NULL DEFAULT 0,
  next_recurring_date DATE DEFAULT NULL,
  no_of_cycles_completed INTEGER NOT NULL DEFAULT 0,
  due_reminder_date DATE DEFAULT NULL,
  recurring_reminder_date DATE DEFAULT NULL,
  discount_amount DOUBLE PRECISION NOT NULL,
  discount_amount_type VARCHAR(20) NOT NULL CHECK (discount_amount_type IN ('percentage','fixed_amount')),
  discount_type VARCHAR(20) NOT NULL CHECK (discount_type IN ('before_tax','after_tax')),
  cancelled_at TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  cancelled_by INTEGER NOT NULL,
  files TEXT NOT NULL,
  company_id INTEGER NOT NULL DEFAULT 0,
  estimate_id INTEGER NOT NULL DEFAULT 0,
  main_invoice_id INTEGER NOT NULL DEFAULT 0,
  subscription_id INTEGER NOT NULL DEFAULT 0,
  invoice_total DOUBLE PRECISION NOT NULL,
  invoice_subtotal DOUBLE PRECISION NOT NULL,
  discount_total DOUBLE PRECISION NOT NULL,
  tax DOUBLE PRECISION NOT NULL,
  tax2 DOUBLE PRECISION NOT NULL,
  tax3 DOUBLE PRECISION NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  order_id INTEGER NOT NULL DEFAULT 0,
  display_id TEXT NOT NULL,
  number_year INTEGER DEFAULT NULL,
  number_sequence INTEGER DEFAULT NULL,
  created_by INTEGER NOT NULL
);

CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_invoices_due_date ON invoices(due_date);
CREATE INDEX idx_invoices_client_id ON invoices(client_id);
CREATE INDEX idx_invoices_project_id ON invoices(project_id);



CREATE TABLE IF NOT EXISTS item_categories (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS items (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  unit_type VARCHAR(20) NOT NULL DEFAULT '',
  rate DOUBLE PRECISION NOT NULL,
  files TEXT NOT NULL,
  show_in_client_portal BOOLEAN NOT NULL DEFAULT FALSE,
  category_id INTEGER NOT NULL,
  taxable BOOLEAN NOT NULL DEFAULT FALSE,
  sort INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS labels (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  color VARCHAR(15) NOT NULL,
  context VARCHAR(20) DEFAULT NULL CHECK (context IN ('event','invoice','note','project','task','ticket','to_do','subscription','client','help','knowledge_base')),
  user_id INTEGER NOT NULL DEFAULT 0,
  deleted INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX idx_labels_context ON labels(context);



CREATE TABLE IF NOT EXISTS lead_source (
  id SERIAL PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  sort INTEGER NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS lead_status (
  id SERIAL PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  color VARCHAR(7) NOT NULL,
  sort INTEGER NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS leave_applications (
  id SERIAL PRIMARY KEY,
  leave_type_id INTEGER NOT NULL,
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  total_hours DECIMAL(7,2) NOT NULL,
  total_days DECIMAL(5,2) NOT NULL,
  applicant_id INTEGER NOT NULL,
  reason TEXT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','approved','rejected','canceled')),
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  created_by INTEGER NOT NULL,
  checked_at TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  checked_by INTEGER NOT NULL DEFAULT 0,
  files TEXT NOT NULL,
  deleted INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX idx_leave_applications_leave_type_id ON leave_applications(leave_type_id);
CREATE INDEX idx_leave_applications_applicant_id ON leave_applications(applicant_id);
CREATE INDEX idx_leave_applications_checked_by ON leave_applications(checked_by);



CREATE TABLE IF NOT EXISTS leave_types (
  id SERIAL PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active','inactive')),
  color VARCHAR(7) NOT NULL,
  description TEXT,
  deleted INTEGER NOT NULL DEFAULT 0
);



CREATE TABLE IF NOT EXISTS likes (
  id SERIAL PRIMARY KEY,
  project_comment_id INTEGER NOT NULL,
  created_by INTEGER NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS messages (
  id SERIAL PRIMARY KEY,
  subject VARCHAR(255) NOT NULL DEFAULT 'Untitled',
  message TEXT NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  from_user_id INTEGER NOT NULL,
  to_user_id INTEGER NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'unread' CHECK (status IN ('unread','read')),
  message_id INTEGER NOT NULL DEFAULT 0,
  deleted INTEGER NOT NULL DEFAULT 0,
  files TEXT NOT NULL,
  deleted_by_users TEXT NOT NULL
);

CREATE INDEX idx_messages_from_user_id ON messages(from_user_id);
CREATE INDEX idx_messages_to_user_id ON messages(to_user_id);



CREATE TABLE IF NOT EXISTS milestones (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  project_id INTEGER NOT NULL,
  due_date DATE NOT NULL,
  description TEXT NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS note_category (
  id SERIAL PRIMARY KEY,
  name TEXT,
  user_id INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS notes (
  id SERIAL PRIMARY KEY,
  created_by INTEGER NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  title TEXT NOT NULL,
  description TEXT,
  project_id INTEGER NOT NULL DEFAULT 0,
  client_id INTEGER NOT NULL DEFAULT 0,
  user_id INTEGER NOT NULL DEFAULT 0,
  labels TEXT,
  files TEXT NOT NULL,
  is_public BOOLEAN NOT NULL DEFAULT FALSE,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  category_id INTEGER DEFAULT 0,
  color VARCHAR(14) NOT NULL
);



CREATE TABLE IF NOT EXISTS notification_settings (
  id SERIAL PRIMARY KEY,
  event VARCHAR(250) NOT NULL,
  category VARCHAR(50) NOT NULL,
  enable_email INTEGER NOT NULL DEFAULT 0,
  enable_web INTEGER NOT NULL DEFAULT 0,
  enable_slack INTEGER NOT NULL DEFAULT 0,
  notify_to_team TEXT NOT NULL,
  notify_to_team_members TEXT NOT NULL,
  notify_to_terms TEXT NOT NULL,
  sort INTEGER NOT NULL,
  deleted INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX idx_notification_settings_event ON notification_settings(event);



CREATE TABLE IF NOT EXISTS notifications (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL,
  description TEXT NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  notify_to TEXT NOT NULL,
  read_by TEXT NOT NULL,
  event VARCHAR(250) NOT NULL,
  project_id INTEGER NOT NULL,
  task_id INTEGER NOT NULL,
  project_comment_id INTEGER NOT NULL,
  ticket_id INTEGER NOT NULL,
  ticket_comment_id INTEGER NOT NULL,
  project_file_id INTEGER NOT NULL,
  leave_id INTEGER NOT NULL,
  post_id INTEGER NOT NULL,
  to_user_id INTEGER NOT NULL,
  activity_log_id INTEGER NOT NULL,
  client_id INTEGER NOT NULL,
  lead_id INTEGER NOT NULL,
  invoice_payment_id INTEGER NOT NULL,
  invoice_id INTEGER NOT NULL,
  estimate_id INTEGER NOT NULL,
  contract_id INTEGER NOT NULL,
  order_id INTEGER NOT NULL,
  estimate_request_id INTEGER NOT NULL,
  actual_message_id INTEGER NOT NULL,
  parent_message_id INTEGER NOT NULL,
  event_id INTEGER NOT NULL,
  announcement_id INTEGER NOT NULL,
  proposal_id INTEGER NOT NULL,
  estimate_comment_id INTEGER NOT NULL,
  subscription_id INTEGER NOT NULL,
  expense_id INTEGER NOT NULL,
  proposal_comment_id INTEGER NOT NULL,
  reminder_log_id INTEGER NOT NULL,
  deleted INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX idx_notifications_user_id ON notifications(user_id);



CREATE TABLE IF NOT EXISTS order_items (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  quantity DOUBLE PRECISION NOT NULL,
  unit_type VARCHAR(20) NOT NULL DEFAULT '',
  rate DOUBLE PRECISION NOT NULL,
  total DOUBLE PRECISION NOT NULL,
  order_id INTEGER NOT NULL,
  created_by INTEGER NOT NULL,
  item_id INTEGER NOT NULL DEFAULT 0,
  sort INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  created_by_hash TEXT NOT NULL
);



CREATE TABLE IF NOT EXISTS order_status (
  id SERIAL PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  color VARCHAR(7) NOT NULL,
  sort INTEGER NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS orders (
  id SERIAL PRIMARY KEY,
  client_id INTEGER NOT NULL,
  order_date DATE NOT NULL,
  note TEXT,
  status_id INTEGER NOT NULL,
  tax_id INTEGER NOT NULL DEFAULT 0,
  tax_id2 INTEGER NOT NULL DEFAULT 0,
  discount_amount DOUBLE PRECISION NOT NULL,
  discount_amount_type VARCHAR(20) NOT NULL CHECK (discount_amount_type IN ('percentage','fixed_amount')),
  discount_type VARCHAR(20) NOT NULL CHECK (discount_type IN ('before_tax','after_tax')),
  created_by INTEGER NOT NULL DEFAULT 0,
  project_id INTEGER NOT NULL DEFAULT 0,
  files TEXT NOT NULL,
  company_id INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  created_by_hash TEXT NOT NULL
);



CREATE TABLE IF NOT EXISTS pages (
  id SERIAL PRIMARY KEY,
  title TEXT,
  content TEXT,
  slug TEXT,
  status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active','inactive')),
  internal_use_only BOOLEAN NOT NULL DEFAULT FALSE,
  visible_to_team_members_only BOOLEAN NOT NULL DEFAULT FALSE,
  visible_to_clients_only BOOLEAN NOT NULL DEFAULT FALSE,
  full_width BOOLEAN NOT NULL DEFAULT FALSE,
  hide_topbar BOOLEAN NOT NULL DEFAULT FALSE,
  deleted INTEGER NOT NULL DEFAULT 0
);



CREATE TABLE IF NOT EXISTS payment_methods (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  type VARCHAR(100) NOT NULL DEFAULT 'custom',
  description TEXT NOT NULL,
  online_payable BOOLEAN NOT NULL DEFAULT FALSE,
  available_on_invoice BOOLEAN NOT NULL DEFAULT FALSE,
  minimum_payment_amount DOUBLE PRECISION NOT NULL DEFAULT 0,
  settings TEXT NOT NULL,
  sort INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS paypal_ipn (
  id SERIAL PRIMARY KEY,
  verification_code TEXT NOT NULL,
  payment_verification_code TEXT NOT NULL,
  invoice_id INTEGER NOT NULL,
  contact_user_id INTEGER NOT NULL,
  client_id INTEGER NOT NULL,
  payment_method_id INTEGER NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS pin_comments (
  id SERIAL PRIMARY KEY,
  project_comment_id INTEGER NOT NULL DEFAULT 0,
  ticket_comment_id INTEGER NOT NULL DEFAULT 0,
  pinned_by INTEGER NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS posts (
  id SERIAL PRIMARY KEY,
  created_by INTEGER NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  description TEXT NOT NULL,
  post_id INTEGER NOT NULL,
  share_with TEXT,
  files TEXT,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS project_comments (
  id SERIAL PRIMARY KEY,
  created_by INTEGER NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  description TEXT NOT NULL,
  project_id INTEGER NOT NULL DEFAULT 0,
  comment_id INTEGER NOT NULL DEFAULT 0,
  task_id INTEGER NOT NULL DEFAULT 0,
  file_id INTEGER NOT NULL DEFAULT 0,
  customer_feedback_id INTEGER NOT NULL DEFAULT 0,
  files TEXT,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS project_files (
  id SERIAL PRIMARY KEY,
  file_name TEXT NOT NULL,
  file_id TEXT,
  service_type VARCHAR(20) DEFAULT NULL,
  description TEXT,
  file_size DOUBLE PRECISION NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  project_id INTEGER NOT NULL,
  uploaded_by INTEGER NOT NULL,
  category_id INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  folder_id INTEGER NOT NULL DEFAULT 0
);



CREATE TABLE IF NOT EXISTS project_members (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL,
  project_id INTEGER NOT NULL,
  is_leader BOOLEAN DEFAULT FALSE,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_project_members_user_id ON project_members(user_id);
CREATE INDEX idx_project_members_project_id ON project_members(project_id);



CREATE TABLE IF NOT EXISTS project_settings (
  project_id INTEGER NOT NULL,
  setting_name VARCHAR(100) NOT NULL,
  setting_value TEXT NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  PRIMARY KEY (project_id, setting_name)
);


CREATE TABLE IF NOT EXISTS project_status (
  id SERIAL PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  title_language_key TEXT NOT NULL,
  key_name VARCHAR(100) NOT NULL,
  icon VARCHAR(50) NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS project_time (
  id SERIAL PRIMARY KEY,
  project_id INTEGER NOT NULL,
  user_id INTEGER NOT NULL,
  start_time TIMESTAMP WITH TIME ZONE NOT NULL,
  end_time TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  hours DOUBLE PRECISION NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'logged' CHECK (status IN ('open','logged','approved')),
  note TEXT,
  task_id INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS projects (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  project_type VARCHAR(20) NOT NULL DEFAULT 'client_project' CHECK (project_type IN ('client_project','internal_project')),
  start_date DATE DEFAULT NULL,
  deadline DATE DEFAULT NULL,
  client_id INTEGER NOT NULL,
  created_date DATE DEFAULT NULL,
  created_by INTEGER NOT NULL DEFAULT 0,
  status VARCHAR(20) NOT NULL DEFAULT 'open' CHECK (status IN ('open','completed','hold','canceled')),
  status_id INTEGER NOT NULL DEFAULT 1,
  labels TEXT,
  price DOUBLE PRECISION NOT NULL DEFAULT 0,
  starred_by TEXT NOT NULL,
  estimate_id INTEGER NOT NULL,
  order_id INTEGER NOT NULL,
  proposal_id INTEGER DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);

CREATE INDEX idx_projects_client_id ON projects(client_id);
CREATE INDEX idx_projects_status_id ON projects(status_id);



CREATE TABLE IF NOT EXISTS proposal_comments (
  id SERIAL PRIMARY KEY,
  created_by INTEGER NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  description TEXT NOT NULL,
  proposal_id INTEGER NOT NULL DEFAULT 0,
  files TEXT,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS proposal_items (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  quantity DOUBLE PRECISION NOT NULL,
  unit_type VARCHAR(20) NOT NULL DEFAULT '',
  rate DOUBLE PRECISION NOT NULL,
  total DOUBLE PRECISION NOT NULL,
  sort INTEGER NOT NULL DEFAULT 0,
  proposal_id INTEGER NOT NULL,
  item_id INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS proposal_templates (
  id SERIAL PRIMARY KEY,
  title VARCHAR(50) NOT NULL,
  template TEXT,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS proposals (
  id SERIAL PRIMARY KEY,
  client_id INTEGER NOT NULL,
  proposal_date DATE NOT NULL,
  valid_until DATE NOT NULL,
  note TEXT,
  last_email_sent_date DATE DEFAULT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','sent','accepted','declined')),
  tax_id INTEGER NOT NULL DEFAULT 0,
  tax_id2 INTEGER NOT NULL DEFAULT 0,
  discount_type VARCHAR(20) NOT NULL CHECK (discount_type IN ('before_tax','after_tax')),
  discount_amount DOUBLE PRECISION NOT NULL,
  discount_amount_type VARCHAR(20) NOT NULL CHECK (discount_amount_type IN ('percentage','fixed_amount')),
  content TEXT NOT NULL,
  public_key VARCHAR(10) NOT NULL,
  accepted_by INTEGER NOT NULL DEFAULT 0,
  created_by INTEGER NOT NULL DEFAULT 0,
  total_views INTEGER NOT NULL DEFAULT 0,
  last_preview_seen TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  meta_data TEXT NOT NULL,
  company_id INTEGER NOT NULL DEFAULT 0,
  project_id INTEGER DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS reminder_logs (
  id SERIAL PRIMARY KEY,
  context VARCHAR(255) NOT NULL,
  context_id INTEGER NOT NULL,
  reminder_event VARCHAR(255) DEFAULT NULL,
  notification_status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (notification_status IN ('draft','completed')),
  reminder_date DATE DEFAULT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS reminder_settings (
  id SERIAL PRIMARY KEY,
  type VARCHAR(20) NOT NULL DEFAULT 'app' CHECK (type IN ('app')),
  context TEXT NOT NULL,
  reminder_event TEXT NOT NULL,
  reminder1 INTEGER DEFAULT NULL,
  reminder2 INTEGER DEFAULT NULL,
  reminder3 INTEGER DEFAULT NULL,
  reminder4 INTEGER DEFAULT NULL,
  reminder5 INTEGER DEFAULT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS roles (
  id SERIAL PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  permissions TEXT,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS settings (
  setting_name VARCHAR(100) PRIMARY KEY,
  setting_value TEXT NOT NULL,
  type VARCHAR(20) NOT NULL DEFAULT 'app',
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);


CREATE TABLE IF NOT EXISTS social_links (
  id INTEGER PRIMARY KEY,
  user_id INTEGER NOT NULL,
  facebook TEXT,
  twitter TEXT,
  linkedin TEXT,
  googleplus TEXT,
  digg TEXT,
  youtube TEXT,
  pinterest TEXT,
  instagram TEXT,
  github TEXT,
  tumblr TEXT,
  vine TEXT,
  whatsapp TEXT,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);


CREATE TABLE IF NOT EXISTS stripe_ipn (
  id SERIAL PRIMARY KEY,
  session_id TEXT NOT NULL,
  verification_code TEXT NOT NULL,
  payment_verification_code TEXT NOT NULL,
  invoice_id INTEGER NOT NULL,
  contact_user_id INTEGER NOT NULL,
  client_id INTEGER NOT NULL,
  payment_method_id INTEGER NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  setup_intent TEXT NOT NULL,
  subscription_id INTEGER NOT NULL
);



CREATE TABLE IF NOT EXISTS subscription_items (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  quantity DOUBLE PRECISION NOT NULL,
  unit_type VARCHAR(20) NOT NULL DEFAULT '',
  rate DOUBLE PRECISION NOT NULL,
  total DOUBLE PRECISION NOT NULL,
  sort INTEGER NOT NULL DEFAULT 0,
  subscription_id INTEGER NOT NULL,
  item_id INTEGER NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS subscriptions (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  client_id INTEGER NOT NULL,
  bill_date DATE DEFAULT NULL,
  end_date DATE DEFAULT NULL,
  note TEXT,
  labels TEXT NOT NULL,
  status VARCHAR(20) NOT NULL DEFAULT 'draft' CHECK (status IN ('draft','pending','active','cancelled')),
  payment_status VARCHAR(20) NOT NULL DEFAULT 'success' CHECK (payment_status IN ('success','failed')),
  tax_id INTEGER NOT NULL DEFAULT 0,
  tax_id2 INTEGER NOT NULL DEFAULT 0,
  repeat_every INTEGER NOT NULL DEFAULT 1,
  repeat_type VARCHAR(10) DEFAULT NULL CHECK (repeat_type IN ('days','weeks','months','years')),
  no_of_cycles INTEGER NOT NULL DEFAULT 0,
  next_recurring_date DATE DEFAULT NULL,
  no_of_cycles_completed INTEGER NOT NULL DEFAULT 0,
  cancelled_at TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  cancelled_by INTEGER NOT NULL,
  files TEXT NOT NULL,
  company_id INTEGER NOT NULL DEFAULT 0,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  type VARCHAR(20) NOT NULL DEFAULT 'app' CHECK (type IN ('app','stripe')),
  stripe_subscription_id TEXT NOT NULL,
  stripe_product_id TEXT NOT NULL,
  stripe_product_price_id TEXT NOT NULL
);



CREATE TABLE IF NOT EXISTS task_priority (
  id SERIAL PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  icon VARCHAR(20) NOT NULL,
  color VARCHAR(7) NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS task_status (
  id SERIAL PRIMARY KEY,
  title VARCHAR(100) NOT NULL,
  key_name VARCHAR(100) NOT NULL,
  color VARCHAR(7) NOT NULL,
  sort INTEGER NOT NULL,
  hide_from_kanban BOOLEAN NOT NULL DEFAULT FALSE,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  hide_from_non_project_related_tasks BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS tasks (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT,
  project_id INTEGER NOT NULL,
  milestone_id INTEGER NOT NULL DEFAULT 0,
  assigned_to INTEGER NOT NULL,
  deadline TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  labels TEXT,
  points INTEGER NOT NULL DEFAULT 1,
  status VARCHAR(20) NOT NULL DEFAULT 'to_do' CHECK (status IN ('to_do','in_progress','done')),
  status_id INTEGER NOT NULL,
  priority_id INTEGER NOT NULL,
  start_date TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  collaborators TEXT NOT NULL,
  sort INTEGER NOT NULL DEFAULT 0,
  recurring BOOLEAN NOT NULL DEFAULT FALSE,
  repeat_every INTEGER NOT NULL DEFAULT 0,
  repeat_type VARCHAR(10) DEFAULT NULL CHECK (repeat_type IN ('days','weeks','months','years')),
  no_of_cycles INTEGER NOT NULL DEFAULT 0,
  recurring_task_id INTEGER NOT NULL DEFAULT 0,
  no_of_cycles_completed INTEGER NOT NULL DEFAULT 0,
  created_date DATE DEFAULT NULL,
  blocking TEXT NOT NULL,
  blocked_by TEXT NOT NULL,
  parent_task_id INTEGER NOT NULL,
  next_recurring_date DATE DEFAULT NULL,
  reminder_date DATE DEFAULT NULL,
  ticket_id INTEGER NOT NULL,
  status_changed_at TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  expense_id INTEGER NOT NULL DEFAULT 0,
  subscription_id INTEGER NOT NULL DEFAULT 0,
  proposal_id INTEGER NOT NULL DEFAULT 0,
  contract_id INTEGER NOT NULL DEFAULT 0,
  order_id INTEGER NOT NULL DEFAULT 0,
  estimate_id INTEGER NOT NULL DEFAULT 0,
  invoice_id INTEGER NOT NULL DEFAULT 0,
  lead_id INTEGER NOT NULL DEFAULT 0,
  client_id INTEGER NOT NULL DEFAULT 0,
  context VARCHAR(20) NOT NULL DEFAULT 'general' CHECK (context IN ('project','client','lead','invoice','estimate','order','contract','proposal','subscription','ticket','expense','general')),
  created_by INTEGER DEFAULT NULL
);

CREATE INDEX idx_tasks_status_id ON tasks(status_id);
CREATE INDEX idx_tasks_priority_id ON tasks(priority_id);
CREATE INDEX idx_tasks_sort ON tasks(sort);
CREATE INDEX idx_tasks_project_id ON tasks(project_id);
CREATE INDEX idx_tasks_milestone_id ON tasks(milestone_id);
CREATE INDEX idx_tasks_assigned_to ON tasks(assigned_to);
CREATE INDEX idx_tasks_ticket_id ON tasks(ticket_id);
CREATE INDEX idx_tasks_client_id ON tasks(client_id);
CREATE INDEX idx_tasks_invoice_id ON tasks(invoice_id);
CREATE INDEX idx_tasks_estimate_id ON tasks(estimate_id);
CREATE INDEX idx_tasks_order_id ON tasks(order_id);
CREATE INDEX idx_tasks_contract_id ON tasks(contract_id);
CREATE INDEX idx_tasks_proposal_id ON tasks(proposal_id);
CREATE INDEX idx_tasks_subscription_id ON tasks(subscription_id);
CREATE INDEX idx_tasks_expense_id ON tasks(expense_id);
CREATE INDEX idx_tasks_lead_id ON tasks(lead_id);



CREATE TABLE IF NOT EXISTS taxes (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  percentage DOUBLE PRECISION NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  stripe_tax_id TEXT NOT NULL
);



CREATE TABLE IF NOT EXISTS team (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  members TEXT NOT NULL,
  deleted INTEGER NOT NULL DEFAULT 0
);



CREATE TABLE IF NOT EXISTS team_member_job_info (
  id SERIAL PRIMARY KEY,
  user_id INTEGER NOT NULL,
  date_of_hire DATE DEFAULT NULL,
  deleted INTEGER NOT NULL DEFAULT 0,
  salary DOUBLE PRECISION NOT NULL DEFAULT 0,
  salary_term VARCHAR(20) DEFAULT NULL
);

CREATE INDEX idx_team_member_job_info_user_id ON team_member_job_info(user_id);



CREATE TABLE IF NOT EXISTS ticket_comments (
  id SERIAL PRIMARY KEY,
  created_by INTEGER NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  description TEXT NOT NULL,
  ticket_id INTEGER NOT NULL,
  files TEXT,
  is_note BOOLEAN NOT NULL DEFAULT FALSE,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS ticket_templates (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  description TEXT NOT NULL,
  ticket_type_id INTEGER NOT NULL,
  private TEXT NOT NULL,
  created_by INTEGER NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS ticket_types (
  id SERIAL PRIMARY KEY,
  title TEXT NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE
);



CREATE TABLE IF NOT EXISTS tickets (
  id SERIAL PRIMARY KEY,
  client_id INTEGER NOT NULL,
  project_id INTEGER NOT NULL DEFAULT 0,
  ticket_type_id INTEGER NOT NULL,
  title TEXT NOT NULL,
  created_by INTEGER NOT NULL,
  requested_by INTEGER NOT NULL DEFAULT 0,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  status VARCHAR(20) NOT NULL DEFAULT 'new' CHECK (status IN ('new','client_replied','open','closed')),
  last_activity_at TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  assigned_to INTEGER NOT NULL DEFAULT 0,
  creator_name VARCHAR(100) NOT NULL,
  creator_email VARCHAR(255) NOT NULL,
  labels TEXT,
  task_id INTEGER NOT NULL,
  closed_at TIMESTAMP WITH TIME ZONE NOT NULL,
  merged_with_ticket_id INTEGER NOT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  cc_contacts_and_emails TEXT,
  client_last_activity_at TIMESTAMP WITH TIME ZONE DEFAULT NULL
);

CREATE INDEX idx_tickets_client_id ON tickets(client_id);
CREATE INDEX idx_tickets_ticket_type_id ON tickets(ticket_type_id);
CREATE INDEX idx_tickets_assigned_to ON tickets(assigned_to);



CREATE TABLE IF NOT EXISTS to_do (
  id SERIAL PRIMARY KEY,
  created_by INTEGER NOT NULL,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  title TEXT NOT NULL,
  description TEXT,
  labels TEXT,
  status VARCHAR(20) NOT NULL DEFAULT 'to_do' CHECK (status IN ('to_do','done')),
  start_date DATE DEFAULT NULL,
  deleted BOOLEAN NOT NULL DEFAULT FALSE,
  files TEXT NOT NULL,
  sort DOUBLE PRECISION NOT NULL DEFAULT 1
);

CREATE INDEX idx_to_do_created_by ON to_do(created_by);



CREATE TABLE IF NOT EXISTS users (
  id SERIAL PRIMARY KEY,
  first_name VARCHAR(50) NOT NULL,
  last_name VARCHAR(50) NOT NULL,
  user_type VARCHAR(20) NOT NULL DEFAULT 'client' CHECK (user_type IN ('staff','client','lead')),
  is_admin BOOLEAN NOT NULL DEFAULT FALSE,
  role_id INTEGER NOT NULL DEFAULT 0,
  email VARCHAR(255) NOT NULL,
  password VARCHAR(255) DEFAULT NULL,
  image TEXT,
  status VARCHAR(20) NOT NULL DEFAULT 'active' CHECK (status IN ('active','inactive')),
  message_checked_at TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  client_id INTEGER NOT NULL DEFAULT 0,
  notification_checked_at TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  is_primary_contact BOOLEAN NOT NULL DEFAULT FALSE,
  job_title VARCHAR(100) NOT NULL DEFAULT 'Untitled',
  disable_login BOOLEAN NOT NULL DEFAULT FALSE,
  note TEXT,
  address TEXT,
  alternative_address TEXT,
    phone VARCHAR(20) DEFAULT NULL,
  alternative_phone VARCHAR(20) DEFAULT NULL,
  dob DATE DEFAULT NULL,
  ssn VARCHAR(20) DEFAULT NULL,
  gender VARCHAR(10) DEFAULT NULL CHECK (gender IN ('male','female','other')),
  sticky_note TEXT,
  skype TEXT,
  language VARCHAR(50) NOT NULL,
  enable_web_notification BOOLEAN NOT NULL DEFAULT TRUE,
  enable_email_notification BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
  last_online TIMESTAMP WITH TIME ZONE DEFAULT NULL,
  requested_account_removal BOOLEAN NOT NULL DEFAULT FALSE,
  client_permissions TEXT,
  deleted INTEGER NOT NULL DEFAULT 0
);

CREATE INDEX idx_users_user_type ON users(user_type);
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_client_id ON users(client_id);
CREATE INDEX idx_users_deleted ON users(deleted);



CREATE TABLE IF NOT EXISTS verification (
  id SERIAL PRIMARY KEY,
  created_at TIMESTAMP WITH TIME ZONE NOT NULL DEFAULT NOW(),
  type VARCHAR(30) NOT NULL CHECK (type IN ('invoice_payment','reset_password','verify_email','invitation')),
  code VARCHAR(10) NOT NULL,
  params TEXT NOT NULL,
  deleted INTEGER NOT NULL DEFAULT 0
);

