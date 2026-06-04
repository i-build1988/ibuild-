create table if not exists public.ibuild_store (
  key text primary key,
  data jsonb not null,
  updated_at timestamptz not null default now()
);

alter table public.ibuild_store disable row level security;
