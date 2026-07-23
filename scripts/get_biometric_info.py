#!/usr/bin/env python3
# -*- font-encoding: utf-8 -*-

import os
import sys
from zk import ZK
from datetime import datetime

# Konfigurasi perangkat
IP_MESIN = os.getenv('ZKT_IP', '169.254.176.250')
PORT = int(os.getenv('ZKT_PORT', 4370))
PASSWORD = int(os.getenv('ZKT_PASSWORD', 0))

def get_biometrics():
    zk = ZK(IP_MESIN, port=PORT, timeout=10, password=PASSWORD, force_udp=True, ommit_ping=True)
    conn = None

    try:
        print(f"===============================================================")
        print(f"      INSPEKSI DETAIL DATA BIOMETRIK MESIN ZKTECO              ")
        print(f"      IP Mesin: {IP_MESIN}:{PORT}                               ")
        print(f"===============================================================\n")

        print(f"[*] Menghubungkan ke Mesin ZKTeco {IP_MESIN}:{PORT}...")
        conn = zk.connect()
        print("[OK] Terhubung ke mesin ZKTeco!")

        # Informasi Firmware & Mesin
        try:
            sn = conn.get_serialnumber()
            fw = conn.get_firmware_version()
            dev_name = conn.get_device_name()
            print(f"\n[INFO MESIN]")
            print(f"  --> Nama Perangkat : {dev_name if dev_name else 'ZKTeco Biometric'}")
            print(f"  --> Serial Number  : {sn}")
            print(f"  --> Versi Firmware : {fw}")
        except Exception as e_info:
            print(f"[NOTE] Warning info mesin: {e_info}")

        # Ambil Data Pengguna
        print(f"\n[*] Mengambil daftar Pengguna dari memori mesin...")
        users = conn.get_users()
        user_dict = {u.uid: u for u in users}
        user_id_map = {str(u.user_id): u for u in users}
        print(f"[OK] Total Pengguna Terdaftar: {len(users)}")

        # Ambil Data Biometrik Sidik Jari (Templates)
        print(f"\n[*] Mengambil Template Biometrik Sidik Jari (conn.get_templates())...")
        templates = []
        try:
            templates = conn.get_templates()
            print(f"[OK] Total Template Sidik Jari Terdaftar di Mesin: {len(templates)}")
        except Exception as e_tpl:
            print(f"[WARNING] Gagal mengambil template biometrik: {e_tpl}")

        # Peta template per user
        user_finger_map = {}
        for tpl in templates:
            uid = tpl.uid
            if uid not in user_finger_map:
                user_finger_map[uid] = []
            user_finger_map[uid].append(tpl)

        # Tampilkan Tabel Detail Biometrik
        print("\n" + "=" * 80)
        print(f"{'User ID':<8} | {'Nama Pengguna':<22} | {'Card UID':<14} | {'Biometrik Sidik Jari':<25}")
        print("=" * 80)

        for u in users:
            tpls = user_finger_map.get(u.uid, [])
            if tpls:
                finger_details = f"{len(tpls)} Jari (" + ", ".join([f"Jari-{t.fid}" for t in tpls]) + ")"
            else:
                finger_details = "Belum Ada (Hanya RFID/PIN)"

            card_str = str(u.card).zfill(10) if u.card else '-'
            print(f"{u.user_id:<8} | {u.name:<22} | {card_str:<14} | {finger_details:<25}")

        print("=" * 80)

        # Ambil Total Log Presensi Biometrik
        print(f"\n[*] Mengambil Log Presensi Biometrik Terbaru...")
        attendances = conn.get_attendance()
        print(f"[OK] Total Riwayat Log Presensi Biometrik di Mesin: {len(attendances)}")

        print("\n-- 5 Transaksi Presensi Biometrik Terakhir --")
        for log in attendances[-5:]:
            usr = user_id_map.get(str(log.user_id))
            nama_usr = usr.name if usr else 'Unknown'
            print(f"  User ID: {log.user_id:<4} | Nama: {nama_usr:<20} | Waktu: {log.timestamp} | Status: {log.status}")

        print("\n[SUCCESS] Pembacaan Data Biometrik Mesin Selesai!")

    except Exception as e:
        print(f"[ERROR] Pembacaan biometrik gagal: {e}")
    finally:
        if conn:
            conn.disconnect()
            print("\n[*] Koneksi ditutup.")

if __name__ == "__main__":
    get_biometrics()
