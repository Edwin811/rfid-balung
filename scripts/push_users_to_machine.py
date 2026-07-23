#!/usr/bin/env python3
# -*- font-encoding: utf-8 -*-

import os
import sys
import requests
from zk import ZK
from datetime import datetime

# Konfigurasi perangkat & Laravel API
IP_MESIN = os.getenv('ZKT_IP', '169.254.176.250')
PORT = int(os.getenv('ZKT_PORT', 4370))
PASSWORD = int(os.getenv('ZKT_PASSWORD', 0))
API_URL = os.getenv('LARAVEL_API_PEGAWAI', 'http://127.0.0.1:8000/api/adms/pegawai-list')

def push_users():
    zk = ZK(IP_MESIN, port=PORT, timeout=10, password=PASSWORD, force_udp=True, ommit_ping=True)
    conn = None

    try:
        print(f"[*] Mengambil data Aparatur Desa dari Website Laravel ({API_URL})...")
        res = requests.get(API_URL, timeout=5)
        if res.status_code != 200:
            print(f"[ERROR] Gagal mengambil data pegawai dari website. Status: {res.status_code}")
            return

        pegawai_list = res.json().get('data', [])
        print(f"[OK] Ditemukan {len(pegawai_list)} Aparatur Desa untuk di-push ke mesin.")

        print(f"\n[*] Menghubungkan ke Mesin ZKTeco {IP_MESIN}:{PORT}...")
        conn = zk.connect()
        print("[OK] Terhubung ke mesin ZKTeco!")

        # Kunci mesin sementara & deteksi user_packet_size via get_users
        conn.disable_device()
        print("[OK] Membaca struktur memori pengguna mesin...")

        existing_users_map = {}
        try:
            existing_users = conn.get_users()
            print(f"[OK] Total Pengguna Terdaftar di Mesin Fisik: {len(existing_users)}")
            for u in existing_users:
                existing_users_map[str(u.user_id)] = u
        except Exception as e_get:
            print(f"[NOTE] Warning get_users: {e_get}")

        success_count = 0
        for index, p in enumerate(pegawai_list):
            user_id_str = str(p['id_mesin'])
            nama = str(p['nama_lengkap'])[:24]
            card_uid_raw = p.get('uid_kartu', '')
            card_num = 0

            if card_uid_raw and str(card_uid_raw).isdigit():
                card_num = int(card_uid_raw)
            elif card_uid_raw and str(card_uid_raw).replace('RF', '').isdigit():
                card_num = int(str(card_uid_raw).replace('RF', ''))

            # Tentukan internal UID dari mesin jika sudah ada
            if user_id_str in existing_users_map:
                target_uid = existing_users_map[user_id_str].uid
            else:
                target_uid = int(user_id_str) if user_id_str.isdigit() else (index + 1)

            print(f"  --> Pushing User ID: {user_id_str:<4} (UID: {target_uid}) | Nama: {nama:<20} | Card UID: {card_num}")
            
            try:
                # Set User dengan tuple data yang valid untuk ZKTeco TFT/Magic MP340
                conn.set_user(
                    uid=target_uid,
                    name=nama,
                    privilege=0,
                    password='',
                    group_id='',
                    user_id=user_id_str,
                    card=card_num
                )
                print(f"      [SUCCESS] Berhasil update User ID {user_id_str} ({nama}) di mesin!")
                success_count += 1
            except Exception as e_user:
                print(f"      [WARNING] Gagal push ID {user_id_str}: {e_user}")

        print(f"\n[SUMMARY] Selesai mengunggah {success_count} / {len(pegawai_list)} Aparatur Desa ke memori mesin ZKTeco!")

    except Exception as e:
        print(f"[ERROR] Sync ke mesin gagal: {e}")
    finally:
        if conn:
            try:
                conn.enable_device()
                conn.refresh_data()
            except Exception:
                pass
            conn.disconnect()
            print("[*] Koneksi ditutup.")

if __name__ == "__main__":
    push_users()
