#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import json
import sys
import time
import requests
from datetime import datetime
from zk import ZK

IP_MESIN = os.getenv('ZKT_IP', '192.168.1.23')
PORT = int(os.getenv('ZKT_PORT', 4370))
PASSWORD = int(os.getenv('ZKT_PASSWORD', 0))
API_URL = os.getenv('LARAVEL_API_URL', 'http://127.0.0.1:8000/api/adms/attendance')
SECRET_KEY = os.getenv('ADMS_SECRET_KEY', 'balunglor_secret_2026')

def get_connection(timeout=4):
    """
    Koneksi cerdas ke mesin ZKTeco:
    Mencoba protokol TCP dan UDP.
    Mengabaikan ICMP OS ping (ommit_ping=True) agar tidak terhambat firewall Windows.
    """
    last_err = None
    # Coba TCP lalu UDP
    for force_udp in [False, True]:
        try:
            zk = ZK(IP_MESIN, port=PORT, timeout=timeout, password=PASSWORD, force_udp=force_udp, ommit_ping=True)
            conn = zk.connect()
            return zk, conn
        except Exception as e:
            last_err = e
    raise last_err

def fetch_attendance():
    """
    Mode sekali tarik (digunakan oleh perintah artisan rfid:sync)
    Output berupa JSON ke stdout.
    """
    zk = None
    conn = None
    records = []

    try:
        zk, conn = get_connection(timeout=5)
        conn.disable_device()
        attendances = conn.get_attendance()

        for log in attendances:
            # Status 4 = Kartu RFID, Status 1 / lainnya = Sidik Jari (Biometrik)
            tipe = 'rfid' if str(log.status) == '4' else 'fingerprint'

            records.append({
                'user_id': str(log.user_id),
                'timestamp': log.timestamp.strftime('%Y-%m-%d %H:%M:%S'),
                'status': log.status,
                'punch': getattr(log, 'punch', 0),
                'tipe_verifikasi': tipe
            })

        print(json.dumps({'status': 'success', 'count': len(records), 'data': records}))

    except Exception as e:
        print(json.dumps({'status': 'error', 'message': str(e)}))
    finally:
        if conn:
            try:
                conn.enable_device()
            except Exception:
                pass
            try:
                conn.disconnect()
            except Exception:
                pass

def kirim_ke_laravel(logs):
    """Mengirim log presensi langsung ke API Laravel ADMS"""
    for target_url in [API_URL, 'http://127.0.0.1/api/adms/attendance', 'http://localhost:8000/api/adms/attendance']:
        try:
            res = requests.post(target_url, json={
                'secret_key': SECRET_KEY,
                'logs': logs
            }, headers={'Content-Type': 'application/json'}, timeout=4)

            if res.status_code == 200:
                return True
        except Exception:
            continue
    return False

def daemon_mode():
    """
    Background Daemon Mode:
    1. Mencoba terus terhubung ke mesin (auto-reconnect jika mesin mati).
    2. Saat terhubung pertama kali: sinkronkan semua data presensi di memori mesin.
    3. Standby live capture: setiap scan jari/kartu langsung terkirim ke Laravel secara instan (< 0.5 detik).
    """
    print(f"[*] Menjalankan ZKTeco Sync Daemon untuk mesin {IP_MESIN}:{PORT}...")
    while True:
        zk = None
        conn = None
        try:
            zk, conn = get_connection(timeout=5)
            print(f"[{datetime.now().strftime('%H:%M:%S')}] [OK] Terhubung ke mesin ZKTeco!")

            # 1. Ambil dan kirim semua data presensi yang ada di memori mesin
            try:
                attendances = conn.get_attendance()
                if attendances:
                    logs_payload = []
                    for log in attendances:
                        tipe = 'rfid' if str(log.status) == '4' else 'fingerprint'
                        logs_payload.append({
                            'user_id': str(log.user_id),
                            'timestamp': log.timestamp.strftime('%Y-%m-%d %H:%M:%S'),
                            'status': log.status,
                            'punch': getattr(log, 'punch', 0),
                            'tipe_verifikasi': tipe
                        })
                    kirim_ke_laravel(logs_payload)
                    print(f"[{datetime.now().strftime('%H:%M:%S')}] [OK] Berhasil menyinkronkan {len(logs_payload)} rekaman presensi.")
            except Exception as e_att:
                print(f"[NOTE] Gagal sinkron awal presensi: {e_att}")

            # 2. Live stream event capture
            print(f"[{datetime.now().strftime('%H:%M:%S')}] [OK] Standby mendengarkan scan presensi secara langsung...")
            for event in conn.live_capture():
                if event is None:
                    continue

                uid_str = str(event.user_id)
                ts_str = event.timestamp.strftime('%Y-%m-%d %H:%M:%S')
                tipe = 'rfid' if str(event.status) == '4' else 'fingerprint'

                kirim_ke_laravel([{
                    'user_id': uid_str,
                    'timestamp': ts_str,
                    'status': event.status,
                    'punch': getattr(event, 'punch', 0),
                    'tipe_verifikasi': tipe
                }])
                print(f"[{datetime.now().strftime('%H:%M:%S')}] [SCAN INSTAN] User ID {uid_str} pada {ts_str}")

        except Exception as e:
            # Mesin sedang mati atau kabel dilepas; tunggu 5 detik lalu coba lagi
            time.sleep(5)
        finally:
            if conn:
                try:
                    conn.disconnect()
                except Exception:
                    pass

if __name__ == '__main__':
    if '--daemon' in sys.argv or '--live' in sys.argv or '--stream' in sys.argv:
        daemon_mode()
    else:
        fetch_attendance()
