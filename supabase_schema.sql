-- =============================================
-- APLIKASI UANG KAS - Skema Supabase (PostgreSQL)
-- Jalankan di: Supabase Dashboard > SQL Editor > New Query
-- =============================================

-- 1. EXTENSION
create extension if not exists "uuid-ossp";

-- 2. TABEL USERS (anggota / pengurus)
create table if not exists public.users (
  id uuid primary key default uuid_generate_v4(),
  nama text not null,
  email text unique,
  no_wa text,
  alamat text,
  role text not null default 'anggota' check (role in ('admin','bendahara','anggota')),
  status text not null default 'aktif' check (status in ('aktif','nonaktif','keluar')),
  tanggal_gabung date default current_date,
  created_at timestamptz default now()
);

-- 3. TABEL KATEGORI
create table if not exists public.kategori (
  id bigint generated always as identity primary key,
  nama text not null,
  tipe text not null check (tipe in ('masuk','keluar','iuran')),
  deskripsi text,
  ikon text default 'tag',
  created_at timestamptz default now(),
  unique(nama, tipe)
);

-- 4. TABEL PEMBAYARAN_KAS (iuran tiap anggota per periode)
create table if not exists public.pembayaran_kas (
  id bigint generated always as identity primary key,
  user_id uuid not null references public.users(id) on delete cascade,
  periode text not null, -- format YYYY-MM, contoh: 2026-09
  jumlah numeric(15,2) not null default 0 check (jumlah >= 0),
  tanggal_bayar date,
  status text not null default 'belum' check (status in ('belum','lunas','sebagian')),
  metode text default 'cash' check (metode in ('cash','transfer','qris','ewallet')),
  catatan text,
  dicatat_oleh uuid references public.users(id),
  created_at timestamptz default now(),
  unique(user_id, periode)
);
create index if not exists idx_pembayaran_periode on public.pembayaran_kas(periode);
create index if not exists idx_pembayaran_user on public.pembayaran_kas(user_id);
create index if not exists idx_pembayaran_status on public.pembayaran_kas(status);

-- 5. TABEL KAS_MASUK
create table if not exists public.kas_masuk (
  id bigint generated always as identity primary key,
  tanggal date not null default current_date,
  kategori_id bigint references public.kategori(id) on delete set null,
  jumlah numeric(15,2) not null check (jumlah > 0),
  keterangan text,
  sumber text default 'lainnya' check (sumber in ('iuran','donasi','usaha','lainnya')),
  pembayaran_id bigint references public.pembayaran_kas(id) on delete set null,
  bukti_url text,
  dicatat_oleh uuid references public.users(id),
  created_at timestamptz default now()
);
create index if not exists idx_masuk_tanggal on public.kas_masuk(tanggal desc);
create index if not exists idx_masuk_kategori on public.kas_masuk(kategori_id);

-- 6. TABEL KAS_KELUAR
create table if not exists public.kas_keluar (
  id bigint generated always as identity primary key,
  tanggal date not null default current_date,
  kategori_id bigint references public.kategori(id) on delete set null,
  jumlah numeric(15,2) not null check (jumlah > 0),
  keterangan text not null,
  penerima text,
  bukti_url text,
  dicatat_oleh uuid references public.users(id),
  created_at timestamptz default now()
);
create index if not exists idx_keluar_tanggal on public.kas_keluar(tanggal desc);

-- 7. TABEL PENGATURAN (yang KURANG dari desain kamu)
create table if not exists public.pengaturan (
  id int primary key generated always as identity,
  kunci text unique not null,
  nilai text not null,
  updated_at timestamptz default now()
);

-- 8. TABEL LOG_AKTIVITAS (audit, yang KURANG)
create table if not exists public.log_aktivitas (
  id bigint generated always as identity primary key,
  aksi text not null,
  tabel_ref text,
  data_id text,
  user_id uuid references public.users(id),
  detail jsonb,
  created_at timestamptz default now()
);

-- =============================================
-- DATA AWAL
-- =============================================
insert into public.kategori (nama, tipe, deskripsi) values
  ('Iuran Wajib', 'masuk', 'Iuran rutin anggota'),
  ('Donasi', 'masuk', 'Sumbangan sukarela'),
  ('Usaha Bersama', 'masuk', 'Hasil usaha kas'),
  ('Operasional', 'keluar', 'ATK, cetak, dll'),
  ('Kegiatan', 'keluar', 'Acara / rapat'),
  ('Bantuan Sosial', 'keluar', 'Santunan / duka'),
  ('Iuran Bulanan', 'iuran', 'Tagihan iuran periode berjalan')
on conflict (nama, tipe) do nothing;

insert into public.pengaturan (kunci, nilai) values
  ('nama_kas', 'Kas RT 05'),
  ('nominal_iuran', '20000'),
  ('jatuh_tempo', '10'),
  ('wa_bendahara', '6281234567890')
on conflict (kunci) do nothing;

-- =============================================
-- TRIGGER: pembayaran lunas -> otomatis masuk kas_masuk
-- =============================================
create or replace function public.sync_iuran_ke_kas()
returns trigger language plpgsql as $$
begin
  if NEW.status = 'lunas' and (OLD.status is distinct from 'lunas') then
    insert into public.kas_masuk (tanggal, jumlah, keterangan, sumber, pembayaran_id, dicatat_oleh)
    values (
      coalesce(NEW.tanggal_bayar, current_date),
      NEW.jumlah,
      'Iuran ' || NEW.periode,
      'iuran',
      NEW.id,
      NEW.dicatat_oleh
    );
  end if;
  return NEW;
end;
$$;

drop trigger if exists trg_sync_iuran on public.pembayaran_kas;
create trigger trg_sync_iuran
  after insert or update on public.pembayaran_kas
  for each row execute function public.sync_iuran_ke_kas();

-- =============================================
-- VIEW: rekap saldo + tunggakan
-- =============================================
create or replace view public.v_saldo as
select
  (select coalesce(sum(jumlah),0) from public.kas_masuk) as total_masuk,
  (select coalesce(sum(jumlah),0) from public.kas_keluar) as total_keluar,
  (select coalesce(sum(jumlah),0) from public.kas_masuk) -
  (select coalesce(sum(jumlah),0) from public.kas_keluar) as saldo;

create or replace view public.v_tunggakan as
select u.id, u.nama, u.no_wa, p.periode, p.jumlah, p.status
from public.users u
join public.pembayaran_kas p on p.user_id = u.id
where p.status != 'lunas';

-- =============================================
-- ROW LEVEL SECURITY (aktifkan agar REST API bisa diakses)
-- Untuk tahap awal / aplikasi PHP dengan publishable key:
-- =============================================
alter table public.users enable row level security;
alter table public.kategori enable row level security;
alter table public.kas_masuk enable row level security;
alter table public.kas_keluar enable row level security;
alter table public.pembayaran_kas enable row level security;
alter table public.pengaturan enable row level security;
alter table public.log_aktivitas enable row level security;

-- Hapus policy lama jika ada
drop policy if exists "public read write" on public.users;
drop policy if exists "public read write" on public.kategori;
drop policy if exists "public read write" on public.kas_masuk;
drop policy if exists "public read write" on public.kas_keluar;
drop policy if exists "public read write" on public.pembayaran_kas;
drop policy if exists "public read write" on public.pengaturan;
drop policy if exists "public read write" on public.log_aktivitas;

-- Buat policy terbuka (ganti dengan auth.uid() jika sudah pakai Supabase Auth)
create policy "public read write" on public.users for all using (true) with check (true);
create policy "public read write" on public.kategori for all using (true) with check (true);
create policy "public read write" on public.kas_masuk for all using (true) with check (true);
create policy "public read write" on public.kas_keluar for all using (true) with check (true);
create policy "public read write" on public.pembayaran_kas for all using (true) with check (true);
create policy "public read write" on public.pengaturan for all using (true) with check (true);
create policy "public read write" on public.log_aktivitas for all using (true) with check (true);

-- Storage untuk bukti (jalankan manual jika butuh):
-- insert into storage.buckets (id, name, public) values ('bukti-kas','bukti-kas', true)
-- on conflict (id) do nothing;
