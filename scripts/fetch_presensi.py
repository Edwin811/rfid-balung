#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import json
import sys
import time
import requests
from datetime import datetime
from zk import ZK

# Auto-load konfigurasi dari file .env proyek
def load_env():
    base_dir = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
    env_file = os.path.join(base_dir, '.env')
    if os.path.exists(env_file):
        try:
            with open(env_file, 'r', encoding='utf-8') as f:
                for line in f:
                    line = line.strip()
                    if line and not line.startswith('#') and '=' in line:
                        parts = line.split('=', 1)
                        key = parts[0].strip()
                        val = parts[1].strip().strip('"\'')
                        os.environ[key] = val  # Selalu perbarui agar perubahan IP langsung terbaca
        except Exception:
            pass

def get_config():
    load_env()
    ip = os.getenv('ZKT_IP', '192.168.1.23')
    port = int(os.getenv('ZKT_PORT', 4370))
    password = int(os.getenv('ZKT_PASSWORD', 0))
    api_url = os.getenv('LARAVEL_API_URL', 'http://127.0.0.1:8000/api/adms/attendance')
    secret_key = os.getenv('ADMS_SECRET_KEY', 'balunglor_secret_2026')
    return ip, port, password, api_url, secret_key

def get_connection(timeout=4):
    """
    Koneksi cerdas ke mesin ZKTeco:
    Mencoba protokol TCP dan UDP.
    Mengabaikan ICMP OS ping (ommit_ping=True) agar tidak terhambat firewall Windows.
    """
    ip, port, password, _, _ = get_config()
    last_err = None
    # Coba TCP lalu UDP
    for force_udp in [False, True]:
        try:
            zk = ZK(ip, port=port, timeout=timeout, password=password, force_udp=force_udp, ommit_ping=True)
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
    _, _, _, api_url, secret_key = get_config()
    targets = [
        api_url,
        'http://127.0.0.1:8000/api/adms/attendance',
        'http://localhost:8000/api/adms/attendance',
        'http://127.0.0.1/api/adms/attendance',
        'http://localhost/api/adms/attendance'
    ]
    unique_targets = list(dict.fromkeys(targets))

    for target_url in unique_targets:
        try:
            res = requests.post(target_url, json={
                'secret_key': secret_key,
                'logs': logs
            }, headers={'Content-Type': 'application/json'}, timeout=4)

            if res.status_code == 200:
                print(f"[{datetime.now().strftime('%H:%M:%S')}] [OK] {len(logs)} log presensi sukses terkirim ke Laravel ({target_url})")
                sys.stdout.flush()
                return True
        except Exception:
            continue
    print(f"[{datetime.now().strftime('%H:%M:%S')}] [WARNING] Gagal mengirim {len(logs)} log presensi ke Laravel API")
    sys.stdout.flush()
    return False

def kirim_heartbeat():
    """Mengirim ping heartbeat ke Laravel API agar web dashboard selalu tahu status koneksi online"""
    _, _, _, api_url, secret_key = get_config()
    base_api = api_url.replace('/attendance', '/heartbeat')
    targets = [
        base_api,
        'http://127.0.0.1:8000/api/adms/heartbeat',
        'http://localhost:8000/api/adms/heartbeat',
        'http://127.0.0.1/api/adms/heartbeat',
        'http://localhost/api/adms/heartbeat'
    ]
    unique_targets = list(dict.fromkeys(targets))

    for target_url in unique_targets:
        try:
            res = requests.post(target_url, json={'secret_key': secret_key}, headers={'Content-Type': 'application/json'}, timeout=1)
            if res.status_code == 200:
                return True
        except Exception:
            continue
    return False

def daemon_mode():
    """
    Background Daemon Mode (Realtime Hybrid):
    1. Terus terhubung ke mesin dengan auto-reconnect jika offline/kabel lepas.
    2. Saat terhubung: sinkronkan semua riwayat presensi di memori mesin ke web.
    3. Loop monitoring realtime (interval 2 detik):
       - Memeriksa adanya log tap/scan baru di memori mesin.
       - Scan baru langsung di-push ke Laravel API secara instan (< 2 detik).
       - Menjaga koneksi tetap hidup tanpa membebani memori mesin.
    """
    while True:
        ip, port, _, _, _ = get_config()
        print(f"[{datetime.now().strftime('%H:%M:%S')}] [*] Menjalankan ZKTeco Sync Daemon untuk mesin {ip}:{port}...")
        sys.stdout.flush()

        zk = None
        conn = None
        try:
            zk, conn = get_connection(timeout=5)
            print(f"[{datetime.now().strftime('%H:%M:%S')}] [OK] Terhubung ke mesin ZKTeco {ip}!")
            sys.stdout.flush()
            kirim_heartbeat()

            known_keys = set()

            # 1. Sinkronisasi awal: Ambil semua data presensi di memori mesin
            try:
                attendances = conn.get_attendance()
                if attendances:
                    logs_payload = []
                    for log in attendances:
                        ts_str = log.timestamp.strftime('%Y-%m-%d %H:%M:%S')
                        uid_str = str(log.user_id)
                        key = f"{uid_str}_{ts_str}"
                        known_keys.add(key)
                        tipe = 'rfid' if str(log.status) == '4' else 'fingerprint'
                        logs_payload.append({
                            'user_id': uid_str,
                            'timestamp': ts_str,
                            'status': log.status,
                            'punch': getattr(log, 'punch', 0),
                            'tipe_verifikasi': tipe
                        })
                    kirim_ke_laravel(logs_payload)
                    print(f"[{datetime.now().strftime('%H:%M:%S')}] [OK] Sinkronisasi awal berhasil: {len(logs_payload)} log diproses.")
                    sys.stdout.flush()
            except Exception as e_att:
                print(f"[{datetime.now().strftime('%H:%M:%S')}] [NOTE] Sinkronisasi awal presensi: {e_att}")
                sys.stdout.flush()

            # 2. Polling loop realtime (setiap 2 detik)
            print(f"[{datetime.now().strftime('%H:%M:%S')}] [OK] Standby mendengarkan scan presensi secara realtime...")
            sys.stdout.flush()

            # Siapkan file heartbeat agar Laravel tahu daemon aktif & terhubung
            heartbeat_file = os.path.join(
                os.path.dirname(os.path.dirname(os.path.abspath(__file__))),
                'storage', 'logs', 'zk_daemon_heartbeat.txt'
            )

            consecutive_errors = 0
            check_env_counter = 0

            while True:
                time.sleep(2)

                # 1. Tulis file heartbeat lokal
                try:
                    os.makedirs(os.path.dirname(heartbeat_file), exist_ok=True)
                    with open(heartbeat_file, 'w') as hf:
                        hf.write(datetime.now().strftime('%Y-%m-%d %H:%M:%S'))
                except Exception:
                    pass

                # 2. Kirim HTTP heartbeat langsung ke Laravel API
                kirim_heartbeat()

                # Cek secara berkala apakah IP mesin diubah di .env
                check_env_counter += 1
                if check_env_counter >= 5:
                    check_env_counter = 0
                    current_env_ip, current_env_port, _, _, _ = get_config()
                    if current_env_ip != ip or current_env_port != port:
                        print(f"[{datetime.now().strftime('%H:%M:%S')}] [CONFIG] Terdeteksi perubahan IP mesin di .env: {ip} -> {current_env_ip}. Menghubungkan ulang...")
                        sys.stdout.flush()
                        break

                try:
                    current_attendances = conn.get_attendance()
                    consecutive_errors = 0
                    new_logs = []

                    for log in current_attendances:
                        ts_str = log.timestamp.strftime('%Y-%m-%d %H:%M:%S')
                        uid_str = str(log.user_id)
                        key = f"{uid_str}_{ts_str}"

                        if key not in known_keys:
                            known_keys.add(key)
                            tipe = 'rfid' if str(log.status) == '4' else 'fingerprint'
                            new_logs.append({
                                'user_id': uid_str,
                                'timestamp': ts_str,
                                'status': log.status,
                                'punch': getattr(log, 'punch', 0),
                                'tipe_verifikasi': tipe
                            })
                            print(f"[{datetime.now().strftime('%H:%M:%S')}] [SCAN REALTIME] User ID {uid_str} ({tipe}) pada {ts_str}")
                            sys.stdout.flush()

                    if new_logs:
                        kirim_ke_laravel(new_logs)

                except Exception as poll_err:
                    consecutive_errors += 1
                    if consecutive_errors >= 3:
                        print(f"[{datetime.now().strftime('%H:%M:%S')}] [NOTE] Koneksi terputus: {poll_err}. Menghubungkan ulang...")
                        sys.stdout.flush()
                        break

        except Exception as e:
            print(f"[{datetime.now().strftime('%H:%M:%S')}] [WARNING] Menghubungkan ke mesin {ip}... ({e})")
            sys.stdout.flush()
            time.sleep(3)
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
