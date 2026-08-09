import os
import sys
import json
from zk import ZK
from zk.const import CMD_OPTIONS_WRQ, CMD_REFRESHOPTION

IP_MESIN = os.getenv('ZKT_IP', '169.254.176.250')
PORT = int(os.getenv('ZKT_PORT', 4370))

def set_lcd_banner(banner_text):
    zk = ZK(IP_MESIN, port=PORT, timeout=8, force_udp=False)
    conn = None
    try:
        conn = zk.connect()
        conn.disable_device()

        param_payload = f"CompanyName={banner_text}\x00".encode('utf-8')
        res = conn._ZK__send_command(CMD_OPTIONS_WRQ, param_payload)
        
        try:
            conn._ZK__send_command(CMD_REFRESHOPTION)
        except Exception:
            pass

        conn.enable_device()
        print(json.dumps({'status': 'success', 'message': f'Banner LCD mesin ZKTeco berhasil diupdate via script menjadi: "{banner_text}"'}))
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
    text = sys.argv[1] if len(sys.argv) > 1 else "DESA BALUNGLOR: 08.00 - 16.00"
    set_lcd_banner(text)
