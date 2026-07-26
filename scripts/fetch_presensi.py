import json
import sys
from zk import ZK

IP_MESIN = '169.254.176.250'
PORT = 4370

def fetch_attendance():
    zk = ZK(IP_MESIN, port=PORT, timeout=10, force_udp=False)
    conn = None
    records = []

    try:
        conn = zk.connect()
        conn.disable_device()
        attendances = conn.get_attendance()

        for log in attendances:
            # Status 4 = RFID Card, Status 1 = Fingerprint / Biometrik
            tipe = 'rfid' if str(log.status) == '4' else 'fingerprint'

            records.append({
                'user_id': str(log.user_id),
                'timestamp': log.timestamp.strftime('%Y-%m-%d %H:%M:%S'),
                'status': log.status,
                'punch': log.punch,
                'tipe_verifikasi': tipe
            })

        print(json.dumps({'status': 'success', 'count': len(records), 'data': records}))

    except Exception as e:
        print(json.dumps({'status': 'error', 'message': str(e)}))
    finally:
        if conn:
            conn.enable_device()
            conn.disconnect()

if __name__ == '__main__':
    fetch_attendance()
