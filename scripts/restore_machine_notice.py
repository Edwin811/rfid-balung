import os
import sys
import json
from zk import ZK
from zk.const import CMD_OPTIONS_WRQ, CMD_REFRESHOPTION

IP_MESIN = os.getenv('ZKT_IP', '169.254.176.250')
PORT = int(os.getenv('ZKT_PORT', 4370))

def restore_lcd_banner():
    zk = ZK(IP_MESIN, port=PORT, timeout=8, force_udp=False)
    conn = None
    try:
        conn = zk.connect()
        conn.disable_device()

        original_text = "DESA BALUNGLOR"
        param_payload = f"CompanyName={original_text}\x00".encode('utf-8')
        conn._ZK__send_command(CMD_OPTIONS_WRQ, param_payload)
        
        try:
            conn._ZK__send_command(CMD_REFRESHOPTION)
        except Exception:
            pass

        conn.enable_device()
        print(json.dumps({'status': 'success', 'message': f'Banner LCD mesin ZKTeco berhasil dikembalikan ke semula: "{original_text}"'}))
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
    restore_lcd_banner()
