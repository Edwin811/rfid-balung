#!/usr/bin/env python3
# -*- font-encoding: utf-8 -*-

import os
import sys
import time
import json
import requests
from zk import ZK
from datetime import datetime

# Konfigurasi perangkat & Laravel API
IP_MESIN = os.getenv('ZKT_IP', '169.254.176.250')
PORT = int(os.getenv('ZKT_PORT', 4370))
PASSWORD = int(os.getenv('ZKT_PASSWORD', 0))
API_URL = os.getenv('LARAVEL_API_URL', 'http://127.0.0.1:8000/api/adms/attendance')
SECRET_KEY = os.getenv('ADMS_SECRET_KEY', 'balunglor_secret_2026')
POLL_INTERVAL = int(os.getenv('ZKT_POLL_INTERVAL', 3))

def format_card_uid(card_val):
    """Format nomor kartu RFID ke format 10-digit (misal: 0012345678)"""
    if not card_val or str(card_val).strip() in ['0', '']:
        return '-'
    try:
        card_str = str(card_val).strip()
        if card_str.isdigit():
            return card_str.zfill(10)
        return card_str
    except Exception:
        return str(card_val)

def kirim_ke_laravel(logs):
    """Mengirim log presensi ke Laravel Web Portal via API"""
    try:
        res = requests.post(API_URL, json={
            'secret_key': SECRET_KEY,
            'logs': logs
        }, headers={'Content-Type': 'application/json'}, timeout=5)

        if res.status_code == 200:
            json_res = res.json()
            print(f"[{datetime.now().strftime('%H:%M:%S')}] [SUCCESS] Terkirim ke Website! (Processed: {json_res.get('processed', 0)})")
        else:
            print(f"[{datetime.now().strftime('%H:%M:%S')}] [WARNING] Response HTTP {res.status_code}: {res.text}")
    except Exception as e:
        print(f"[{datetime.now().strftime('%H:%M:%S')}] [API ERROR] Gagal mengirim ke Laravel: {e}")

def mode_live_stream():
    """Mode Live Event Streaming (< 0.5 detik instant sync saat sidik jari/kartu ditempelkan)"""
    print(f"===================================================================")
    print(f"    MODE REAL-TIME LIVE CAPTURE (INSTANT SYNC < 0.5 DETIK)         ")
    print(f"    IP Mesin   : {IP_MESIN}:{PORT}                                 ")
    print(f"    Status     : Mendengarkan event scan mesin secara langsung...   ")
    print(f"===================================================================\n")

    while True:
        zk = ZK(IP_MESIN, port=PORT, timeout=5, password=PASSWORD, force_udp=True, ommit_ping=True)
        conn = None
        try:
            conn = zk.connect()
            print(f"[{datetime.now().strftime('%H:%M:%S')}] [OK] Terhubung! Mengambil data peta kartu RFID...")

            # Ambil peta kartu pengguna
            user_cards = {}
            try:
                users = conn.get_users()
                print(f"[{datetime.now().strftime('%H:%M:%S')}] [OK] Total pengguna di mesin: {len(users)}")
                for u in users:
                    card_uid = format_card_uid(u.card)
                    user_cards[str(u.user_id)] = card_uid
                    print(f"    --> ID: {u.user_id:<4} | Nama: {u.name:<18} | Card UID (10-Digit): {card_uid}")
            except Exception as e_u:
                print(f"[NOTE] Tidak dapat mengambil daftar user: {e_u}")

            print(f"\n[{datetime.now().strftime('%H:%M:%S')}] [OK] Standby mendengarkan scan sidik jari/kartu RFID...")
            
            # Mendengarkan event scan mesin secara live (real-time stream)
            for event in conn.live_capture():
                if event is None:
                    continue

                uid_str = str(event.user_id)
                card_number = user_cards.get(uid_str, '-')
                ts_str = event.timestamp.strftime('%Y-%m-%d %H:%M:%S')

                print(f"\n[{datetime.now().strftime('%H:%M:%S')}] [INSTANT SCAN EVENT DETECTED!]")
                print(f"  --> User ID    : {uid_str}")
                print(f"  --> Card UID   : {card_number}")
                print(f"  --> Waktu      : {ts_str}")
                print(f"  --> Status     : {event.status}")

                # Langsung kirim ke Laravel dalam < 0.5 detik
                kirim_ke_laravel([{
                    'user_id': uid_str,
                    'card_uid': card_number,
                    'timestamp': ts_str,
                    'status': event.status,
                    'punch': getattr(event, 'punch', 0)
                }])

        except Exception as e:
            print(f"[{datetime.now().strftime('%H:%M:%S')}] [LIVE ERROR] Koneksi terputus: {e}. Mencoba menghubungkan kembali dalam 3 detik...")
            time.sleep(3)
        finally:
            if conn:
                try:
                    conn.disconnect()
                except Exception:
                    pass

def ambil_dan_kirim_data():
    """Mode Sync Sekali (Full Sync All Logs)"""
    zk = ZK(IP_MESIN, port=PORT, timeout=5, password=PASSWORD, force_udp=True, ommit_ping=True)
    conn = None

    try:
        print(f"[{datetime.now().strftime('%H:%M:%S')}] [*] Menghubungkan ke {IP_MESIN}:{PORT} via UDP...")
        conn = zk.connect()
        print(f"[{datetime.now().strftime('%H:%M:%S')}] [OK] Terhubung ke mesin ZKTeco!")

        # Peta kartu pengguna
        user_cards = {}
        try:
            users = conn.get_users()
            print(f"[{datetime.now().strftime('%H:%M:%S')}] [OK] Total Pengguna di Mesin: {len(users)}")
            print("-" * 65)
            print(f"{'User ID':<8} | {'Nama Pengguna':<25} | {'Card UID (10-Digit)':<15}")
            print("-" * 65)
            for u in users:
                c_uid = format_card_uid(u.card)
                user_cards[str(u.user_id)] = c_uid
                print(f"{u.user_id:<8} | {u.name:<25} | {c_uid:<15}")
            print("-" * 65)
        except Exception as e_users:
            print(f"[NOTE] Gagal membaca user: {e_users}")

        attendances = conn.get_attendance()
        print(f"\n[{datetime.now().strftime('%H:%M:%S')}] [OK] Total log presensi di mesin: {len(attendances)}")

        payload_logs = []
        for log in attendances:
            uid_str = str(log.user_id)
            c_uid = user_cards.get(uid_str, '-')
            payload_logs.append({
                'user_id': uid_str,
                'card_uid': c_uid,
                'timestamp': log.timestamp.strftime('%Y-%m-%d %H:%M:%S'),
                'status': log.status,
                'punch': log.punch
            })

        if payload_logs:
            kirim_ke_laravel(payload_logs)

    except Exception as e:
        print(f"[{datetime.now().strftime('%H:%M:%S')}] [ERROR] {e}")
    finally:
        if conn:
            conn.disconnect()

def main():
    if '--live' in sys.argv or '--stream' in sys.argv or os.getenv('ZKT_LIVE', 'false').lower() == 'true':
        mode_live_stream()
    elif '--loop' in sys.argv:
        while True:
            ambil_dan_kirim_data()
            time.sleep(POLL_INTERVAL)
    else:
        ambil_dan_kirim_data()

if __name__ == "__main__":
    main()
