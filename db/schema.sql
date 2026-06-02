CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

CREATE TYPE user_role AS ENUM ('system_admin', 'operations_manager', 'supervisor');
CREATE TYPE site_status AS ENUM ('active', 'paused', 'archived');
CREATE TYPE inspection_rating AS ENUM ('excellent', 'good', 'needs_followup', 'critical');
CREATE TYPE material_tx_type AS ENUM ('stock_in', 'site_out');

CREATE TABLE users (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  full_name TEXT NOT NULL,
  email TEXT UNIQUE NOT NULL,
  phone TEXT,
  role user_role NOT NULL,
  password_hash TEXT NOT NULL,
  active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE clients (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  name TEXT NOT NULL,
  contact_name TEXT,
  phone TEXT,
  email TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE sites (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  client_id UUID REFERENCES clients(id) ON DELETE SET NULL,
  name TEXT NOT NULL,
  address TEXT NOT NULL,
  city TEXT,
  gps_lat NUMERIC(10, 7),
  gps_lng NUMERIC(10, 7),
  qr_token TEXT UNIQUE NOT NULL DEFAULT encode(gen_random_bytes(16), 'hex'),
  status site_status NOT NULL DEFAULT 'active',
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE inspections (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  site_id UUID NOT NULL REFERENCES sites(id) ON DELETE CASCADE,
  supervisor_id UUID REFERENCES users(id) ON DELETE SET NULL,
  supervisor_name TEXT NOT NULL,
  inspected_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
  gps_lat NUMERIC(10, 7),
  gps_lng NUMERIC(10, 7),
  before_photo_url TEXT,
  after_photo_url TEXT,
  rating inspection_rating NOT NULL,
  notes TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE materials (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  name TEXT NOT NULL,
  unit TEXT NOT NULL DEFAULT 'قطعة',
  min_stock NUMERIC(12, 2) NOT NULL DEFAULT 0,
  current_stock NUMERIC(12, 2) NOT NULL DEFAULT 0,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE TABLE material_transactions (
  id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
  material_id UUID NOT NULL REFERENCES materials(id) ON DELETE CASCADE,
  site_id UUID REFERENCES sites(id) ON DELETE SET NULL,
  tx_type material_tx_type NOT NULL,
  quantity NUMERIC(12, 2) NOT NULL CHECK (quantity > 0),
  delivered_by TEXT NOT NULL,
  received_by TEXT NOT NULL,
  delivery_photo_url TEXT,
  notes TEXT,
  created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);

CREATE INDEX idx_sites_client_id ON sites(client_id);
CREATE INDEX idx_inspections_site_date ON inspections(site_id, inspected_at DESC);
CREATE INDEX idx_inspections_rating ON inspections(rating);
CREATE INDEX idx_material_transactions_material ON material_transactions(material_id, created_at DESC);
CREATE INDEX idx_material_transactions_site ON material_transactions(site_id, created_at DESC);
