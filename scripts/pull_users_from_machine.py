#!/usr/bin/env python3
# -*- coding: utf-8 -*-

import os
import sys
import json
from zk import ZK

IP_MESIN = os.getenv('ZKT_IP', '169.254.176.250')
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
