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

def pull_users():
    zk = ZK(IP_MESIN, port=PORT, timeout=10, password=PASSWORD, force_udp=False, ommit_ping=True)
    conn = None

    try:
        conn = zk.connect()
        conn.disable_device()
        users = conn.get_users()

        finger_map = {}
        try:
            templates = conn.get_templates()
            for tpl in templates:
                finger_map[tpl.uid] = finger_map.get(tpl.uid, 0) + 1
        except Exception:
            pass

        result = []
        for u in users:
            card_val = str(u.card) if u.card and int(u.card) > 0 else ""
            result.append({
                'uid': u.uid,
                'user_id': str(u.user_id),
                'nama': u.name.strip() if u.name else f"Aparatur ID {u.user_id}",
                'card': card_val,
                'finger_count': finger_map.get(u.uid, 0),
                'privilege': u.privilege,
            })

        print(json.dumps({'status': 'success', 'count': len(result), 'data': result}))

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
    pull_users()
