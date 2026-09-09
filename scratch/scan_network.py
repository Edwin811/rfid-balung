import socket
from concurrent.futures import ThreadPoolExecutor

def check_zk(ip):
    # Coba koneksi UDP port 4370
    sock = socket.socket(socket.AF_INET, socket.SOCK_DGRAM)
    sock.settimeout(0.4)
    # ZKTeco connect command packet (CMD_CONNECT: 1000)
    packet = bytearray([0x50, 0x50, 0x00, 0x00, 0xe8, 0x03, 0x00, 0x00, 0x00, 0x00, 0x00, 0x00])
    try:
        sock.sendto(packet, (ip, 4370))
        data, _ = sock.recvfrom(1024)
        if len(data) >= 8:
            return ip, 'UDP'
    except Exception:
        pass
    finally:
        sock.close()

    # Coba koneksi TCP port 4370
    sock_tcp = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
    sock_tcp.settimeout(0.3)
    try:
        sock_tcp.connect((ip, 4370))
        return ip, 'TCP'
    except Exception:
        pass
    finally:
        sock_tcp.close()

    return None

targets = ['169.254.176.250'] + [f'192.168.1.{i}' for i in range(1, 255)]

print("[*] Memindai mesin ZKTeco di jaringan router...")
found = []
with ThreadPoolExecutor(max_workers=50) as executor:
    results = executor.map(check_zk, targets)
    for r in results:
        if r:
            found.append(r)
            print(f"[FOUND] Ditemukan Mesin ZKTeco di IP: {r[0]} (Protokol: {r[1]})")

if not found:
    print("[NOTE] Mesin belum merespons di port 4370 pada subnet 192.168.1.x maupun 169.254.176.250.")
