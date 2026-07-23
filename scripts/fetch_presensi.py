# ambil_data

from zk import ZK
from datetime import datetime

# Konfigurasi perangkat
IP_MESIN = '169.254.176.250'
PORT = 4370
PASSWORD = 0  # Kata sandi default

def ambil_data():
    # Inisialisasi koneksi dengan protokol UDP
    zk = ZK(IP_MESIN, port=PORT, timeout=10, password=PASSWORD, force_udp=True)
    conn = None

    try:
        print(f"[*] Menghubungkan ke {IP_MESIN}:{PORT} via UDP...")
        conn = zk.connect()
        print("[OK] Berhasil terhubung!")

        # Ambil data pengguna
        print("\n[*] Mengambil data pengguna...")
        users = conn.get_users()
        print(f"[OK] Total pengguna: {len(users)}")
        for user in users[:10]:
            print(f"    ID: {user.user_id} | Nama: {user.name}")

        # Ambil log kehadiran
        print("\n[*] Mengambil log presensi...")
        attendances = conn.get_attendance()
        print(f"[OK] Total log presensi: {len(attendances)}")

        print("\n-- Log Presensi Terbaru (10 data) --")
        for log in attendances[-10:]:
            print(f"  User: {log.user_id} | Waktu: {log.timestamp} | Status: {log.status}")

        # Simpan ke file
        with open("presensi_log.txt", "w") as f:
            f.write(f"Log Presensi - {datetime.now()}\n")
            f.write("="*50 + "\n")
            for log in attendances:
                f.write(f"{log.user_id},{log.timestamp},{log.status},{log.punch}\n")

        print(f"\n[OK] Data tersimpan di presensi_log.txt")

    except Exception as e:
        print(f"[ERROR] {e}")
    finally:
        if conn:
            conn.disconnect()
            print("\n[*] Koneksi ditutup.")

if __name__ == "__main__":
    ambil_data()
