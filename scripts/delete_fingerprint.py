#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import sys
import json
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
                        if key not in os.environ or not os.environ[key]:
                            os.environ[key] = val
        except Exception:
            pass

load_env()

IP_MESIN = os.getenv('ZKT_IP', '192.168.1.23')
PORT = int(os.getenv('ZKT_PORT', 4370))
PASSWORD = int(os.getenv('ZKT_PASSWORD', 0))

def delete_fingerprint(target_user_id):
    zk = ZK(IP_MESIN, port=PORT, timeout=10, password=PASSWORD, force_udp=True, ommit_ping=True)
    conn = None

    try:
        conn = zk.connect()
        conn.disable_device()

        users = conn.get_users()
        target_user = None
        for u in users:
            if str(u.user_id) == str(target_user_id):
                target_user = u
                break

        if not target_user:
            print(json.dumps({
                'status': 'error',
                'message': f"Aparatur dengan ID Mesin '{target_user_id}' tidak ditemukan di memori mesin biometrik."
            }))
            return

        target_uid = target_user.uid
        deleted_count = 0

        # Ambil template sidik jari dari mesin
        try:
            templates = conn.get_templates()
            for tpl in templates:
                if tpl.uid == target_uid:
                    try:
                        res = conn.delete_user_template(uid=target_uid, temp_id=tpl.fid, user_id=str(target_user_id))
                        if res:
                            deleted_count += 1
                    except Exception:
                        try:
                            res = conn.delete_user_template(uid=target_uid, temp_id=tpl.fid)
                            if res:
                                deleted_count += 1
                        except Exception:
                            pass
        except Exception:
            # Fallback jika get_templates error: coba hapus fid 0 sampai 9 untuk target_uid
            for fid in range(10):
                try:
                    res = conn.delete_user_template(uid=target_uid, temp_id=fid, user_id=str(target_user_id))
                    if res:
                        deleted_count += 1
                except Exception:
                    pass

        conn.refresh_data()

        print(json.dumps({
            'status': 'success',
            'deleted_count': deleted_count,
            'user_id': str(target_user_id),
            'nama': target_user.name,
            'message': f"Berhasil menghapus {deleted_count} template sidik jari milik {target_user.name} (ID: {target_user_id}) dari mesin biometrik."
        }))

    except Exception as e:
        print(json.dumps({'status': 'error', 'message': str(e)}))
    finally:
        if conn:
            try:
                conn.enable_device()
            except Exception:
                pass
            conn.disconnect()

if __name__ == '__main__':
    if len(sys.argv) < 2:
        print(json.dumps({'status': 'error', 'message': 'Parameter ID Mesin tidak diberikan.'}))
        sys.exit(1)

    target_id = sys.argv[1]
    delete_fingerprint(target_id)
