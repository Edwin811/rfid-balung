import subprocess
from concurrent.futures import ThreadPoolExecutor

def ping(ip):
    res = subprocess.call(f"ping -n 1 -w 200 {ip}", stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL, shell=True)
    if res == 0:
        return ip
    return None

targets = [f'192.168.1.{i}' for i in range(1, 255)]
print("[*] Mencari seluruh perangkat yang terhubung ke router saat ini...")
found = []
with ThreadPoolExecutor(max_workers=50) as executor:
    results = executor.map(ping, targets)
    for r in results:
        if r:
            found.append(r)
            print(f"  -> Perangkat aktif ditemukan: {r}")

print(f"[OK] Total {len(found)} perangkat aktif di router.")
