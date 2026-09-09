from zk import ZK

candidates = [
    '169.254.176.250',
    '192.168.1.1',
    '192.168.1.6',
    '192.168.1.12',
    '192.168.1.13',
    '192.168.1.14',
    '192.168.1.17',
    '192.168.1.18',
    '192.168.1.20',
    '192.168.1.26'
]

print("[*] Menguji koneksi pyzk ke seluruh perangkat aktif...")
matched = None

for ip in candidates:
    for mode in [True, False]:
        proto = "UDP" if mode else "TCP"
        try:
            zk = ZK(ip, port=4370, timeout=1.5, force_udp=mode, ommit_ping=True)
            conn = zk.connect()
            print(f"[BERHASIL!] Terhubung ke mesin ZKTeco pada IP: {ip} ({proto})")
            try:
                sn = conn.get_serialnumber()
                print(f"  -> Serial Number: {sn}")
            except Exception:
                pass
            conn.disconnect()
            matched = (ip, proto)
            break
        except Exception as e:
            # print(f"  Gagal {ip} ({proto}): {e}")
            pass
    if matched:
        break

if not matched:
    print("[NOTE] Belum ada perangkat yang merespons protokol ZKTeco di IP-IP tersebut.")
